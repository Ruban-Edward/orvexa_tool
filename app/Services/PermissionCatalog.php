<?php

/**
 * PermissionCatalog.php
 *
 * @category Service
 * @purpose  Compares the stored permissions with what the tool really contains right
 *           now: the routes registered in Config/Routes.php and the has_permission()
 *           checks in the code. Each permission is then
 *             - live:    it guards an existing route or code check,
 *             - stale:   nothing in the tool uses it any more (e.g. Redmine sync),
 *           and every code check without a permission row is reported as missing
 *           (has_permission() returns false for it, so only Super Admins see that feature).
 */

namespace App\Services;

use CodeIgniter\HTTP\Method;

class PermissionCatalog
{
    private const SCAN_DIRS = ['Controllers', 'Views', 'Helpers', 'Filters', 'Services'];

    private ?array $routes = null;
    private ?array $checks = null;

    /**
     * Route patterns registered in the app, e.g. "backlog/deletetask/([0-9]+)/([0-9]+)/([0-9]+)".
     * The catch-all route that only redirects to the login page is left out.
     *
     * @return list<string>
     */
    public function routes(): array
    {
        if ($this->routes !== null) {
            return $this->routes;
        }
        $collection = service('routes')->loadRoutes();
        $patterns = [];
        foreach ([Method::GET, Method::POST, Method::PUT, Method::PATCH, Method::DELETE] as $verb) {
            foreach ($collection->getRoutes($verb, false) as $key => $handler) {
                $key = trim((string) $key, '/');
                if ($key === '' || $key[0] === '(') {
                    continue;
                }
                $patterns[$key] = true;
            }
        }

        return $this->routes = array_keys($patterns);
    }

    /**
     * Route URLs checked with has_permission('…') anywhere in the app code,
     * with the files that check them. Cached until a scanned file changes.
     *
     * @return array<string, array{url: string, files: list<string>}> keyed by lower-cased url
     */
    public function codeChecks(): array
    {
        if ($this->checks !== null) {
            return $this->checks;
        }
        $files = [];
        foreach (self::SCAN_DIRS as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(APPPATH . $dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $files[$file->getPathname()] = $file->getMTime();
                }
            }
        }
        $signature = md5(json_encode($files));
        $cached = cache('permission_catalog_checks');
        if (is_array($cached) && ($cached['signature'] ?? '') === $signature) {
            return $this->checks = $cached['checks'];
        }

        $checks = [];
        foreach (array_keys($files) as $path) {
            if (preg_match_all('/has_permission\(\s*[\'"]([A-Za-z][A-Za-z0-9_\/\-]*)[\'"]/', (string) file_get_contents($path), $matches)) {
                foreach ($matches[1] as $url) {
                    $key = strtolower(trim($url, '/'));
                    $checks[$key]['url'] ??= trim($url, '/');
                    $checks[$key]['files'][] = str_replace(APPPATH, 'app/', $path);
                }
            }
        }
        $checks = array_map(static fn ($c) => ['url' => $c['url'], 'files' => array_values(array_unique($c['files']))], $checks);
        ksort($checks);
        cache()->save('permission_catalog_checks', ['signature' => $signature, 'checks' => $checks], 86400);

        return $this->checks = $checks;
    }

    /**
     * Whether a permission URL points at a registered route. Matches the route
     * itself, a route with parameters after it ("backlog/deletetask" for
     * "backlog/deletetask/(:num)/…"), or a concrete URL of a parameterised route
     * ("report/backlogreport/backlog" for "report/backlogreport/(:any)").
     */
    public function routeExists(string $url): bool
    {
        $url = strtolower(trim($url, '/'));
        if ($url === '') {
            return false;
        }
        foreach ($this->routes() as $pattern) {
            $lower = strtolower($pattern);
            if ($lower === $url) {
                return true;
            }
            $static = rtrim(strtolower(strstr($pattern, '(', true) ?: $pattern), '/');
            if ($static === $url && str_contains($pattern, '(')) {
                return true;
            }
            if (str_contains($pattern, '(') && @preg_match('#^' . $pattern . '$#i', $url) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Split permissions into live and stale, and list code checks that have no permission.
     *
     * @param list<array{permission_id:int|string, permission_name:string, routes_url:string, r_module_id?:int|string}> $permissions
     */
    public function classify(array $permissions): array
    {
        $checks = $this->codeChecks();
        $live = $stale = [];
        $covered = [];
        foreach ($permissions as $permission) {
            $url = strtolower(trim((string) $permission['routes_url'], '/'));
            $inRoutes = $this->routeExists($url);
            $inCode = isset($checks[$url]);
            $covered[$url] = true;
            $permission['used_by'] = $inRoutes && $inCode ? 'route and screen' : ($inRoutes ? 'route' : ($inCode ? 'screen' : ''));
            $permission['checked_in'] = $checks[$url]['files'] ?? [];
            if ($inRoutes || $inCode) {
                $live[] = $permission;
            } else {
                $stale[] = $permission;
            }
        }

        $missing = [];
        foreach ($checks as $key => $check) {
            if (isset($covered[$key])) {
                continue;
            }
            $url = $check['url'];
            $missing[] = [
                'routes_url'   => $url,
                'files'        => $check['files'],
                'route_exists' => $this->routeExists($url),
                'suggestion'   => $this->similarStale($url, $stale),
                'module_id'    => $this->closestModule($url, $live),
                'name'         => $this->suggestName($url),
            ];
        }

        return ['live' => $live, 'stale' => $stale, 'missing' => $missing];
    }

    /**
     * Routes and checks a new permission can point at (not yet covered by one).
     *
     * @return list<string>
     */
    public function availableTargets(array $permissions): array
    {
        $covered = array_flip(array_map(static fn ($p) => strtolower(trim((string) $p['routes_url'], '/')), $permissions));
        $targets = array_column($this->codeChecks(), 'url');
        foreach ($this->routes() as $pattern) {
            // Parameterised routes are guarded by their static prefix
            $targets[] = rtrim(strstr($pattern, '(', true) ?: $pattern, '/');
        }
        $targets = array_filter(array_unique($targets), static fn ($t) => $t !== '' && !isset($covered[strtolower($t)]));
        sort($targets, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values($targets);
    }

    /**
     * A stale permission that most likely meant this check, e.g. the old
     * "backlog/backlogitemsrefinement" for "backlog/refinement".
     */
    private function similarStale(string $url, array $stale): ?array
    {
        $last = strtolower(basename($url));
        $prefix = strtolower(dirname($url));
        if ($prefix === '.') {
            return null;
        }
        foreach ($stale as $permission) {
            $candidate = strtolower(trim((string) $permission['routes_url'], '/'));
            if (dirname($candidate) === $prefix && strlen($last) >= 5 && (str_contains(basename($candidate), $last) || str_contains($last, basename($candidate)))) {
                return ['permission_id' => (int) $permission['permission_id'], 'permission_name' => $permission['permission_name'], 'routes_url' => $permission['routes_url']];
            }
        }

        return null;
    }

    /**
     * Module of the live permission whose route shares the longest prefix with $url.
     */
    private function closestModule(string $url, array $live): ?int
    {
        // Longest shared prefix wins; ties go to the module most of those routes belong to
        $scores = [];
        $bestLength = 0;
        foreach ($live as $permission) {
            if (!isset($permission['r_module_id'])) {
                continue;
            }
            $route = strtolower((string) $permission['routes_url']);
            $length = strspn(strtolower($url) ^ $route, "\0");
            if ($length > $bestLength) {
                [$bestLength, $scores] = [$length, []];
            }
            if ($length === $bestLength && $length > 0) {
                $scores[(int) $permission['r_module_id']] = ($scores[(int) $permission['r_module_id']] ?? 0) + 1;
            }
        }
        arsort($scores);

        return $scores === [] ? null : array_key_first($scores);
    }

    private function suggestName(string $url): string
    {
        $words = preg_split('/(?=[A-Z])|[_\-\/]/', basename($url)) ?: [basename($url)];

        return strtoupper(implode('_', array_filter(array_map('trim', $words))));
    }
}
