<?php

/**
 * SearchController.php
 *
 * @category   Controller
 * @purpose    JSON endpoint behind the header search (Ctrl/⌘ K). Returns grouped
 *             results with ready-to-follow links.
 */

namespace App\Controllers;

use App\Models\SearchModel;
use CodeIgniter\HTTP\Response;

class SearchController extends BaseController
{
    private const MIN_LENGTH = 2;
    private const MAX_LENGTH = 100;

    public function index(): Response
    {
        $term = trim((string) $this->request->getGet('q'));
        if (mb_strlen($term) < self::MIN_LENGTH && ! ctype_digit(ltrim($term, '#'))) {
            return $this->response->setJSON(['results' => []]);
        }
        $term = mb_substr($term, 0, self::MAX_LENGTH);

        $rows = model(SearchModel::class)->search((int) session()->get('employee_id'), $term);

        $results = array_map(static function (array $row) {
            return [
                'type'     => $row['type'],
                'id'       => (int) $row['id'],
                'title'    => trim(strip_tags((string) $row['title'])),
                'subtitle' => (string) $row['subtitle'],
                'url'      => self::linkFor($row),
            ];
        }, $rows);

        return $this->response
            ->setHeader('Cache-Control', 'private, max-age=30')
            ->setJSON(['results' => $results]);
    }

    private static function linkFor(array $row): string
    {
        $pid   = $row['product_id'];
        $pblid = $row['backlog_item_id'];

        return match ($row['type']) {
            'product' => 'backlog/backlogitems?' . http_build_query(['pid' => $pid]),
            'backlog' => 'backlog/backlogitemdetails?' . http_build_query(['pid' => $pid, 'pblid' => $pblid]),
            'story'   => 'backlog/userstories?' . http_build_query(['pid' => $pid, 'pblid' => $pblid]),
            'task'    => 'backlog/tasks?' . http_build_query(['pid' => $pid, 'pblid' => $pblid, 'usid' => $row['user_story_id']]),
            'sprint'  => 'sprint/navsprintview?' . http_build_query(['sprint_id' => $row['id']]),
        };
    }
}
