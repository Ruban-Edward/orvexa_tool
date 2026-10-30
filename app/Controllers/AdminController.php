<?php

/**
 * AdminController.php
 *
 * @category   Controller
 * @purpose    manages administrative tasks such as listing users, displaying the permission settings page
 * @author     Ruban Edward
 * @created    09 July 2024
 */

namespace App\Controllers;

use Config\SprintModelConfig;
use App\Services\InviteMailer;
use App\Services\PermissionCatalog;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Response;

class AdminController extends BaseController
{
    protected $adminModelObj;
    protected $rolePermissionModel;
    protected $users;
    protected $userModelObj;
    protected $permissionModelObj;
    protected $roleModelObj;

    /**
     * Creating an object to access AdminModel functions
     */
    public function __construct()
    {
        // Initialize models for admin and role permissions
        $this->adminModelObj = model(\App\Models\Admin\AdminModel::class);
        $this->rolePermissionModel = model(\App\Models\Admin\RolePermissionModel::class);
        $this->permissionModelObj = model(\App\Models\Admin\PermissionModel::class);
        $this->roleModelObj = model(\App\Models\Admin\RoleModel::class);
    }

    /**
     * Retrieves and displays a list of users.
     * @return string
     */
    public function userList(): string
    {
        // Set up breadcrumbs for navigation
        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView',
            'Users' => ''
        ];

        // Get the list of users, roles, and last sync time from the AdminModel
        $usersData = [
            'roles' => $this->roleModelObj->getRoles(),
            'showUser' => $this->adminModelObj->getUsers(),
            'products' => $this->adminModelObj->getProducts(),
            'mailReady' => InviteMailer::isConfigured(),
        ];

        // Return the view for managing users with the users data
        return $this->template_view("admin/manageUser", $usersData, "Users", $breadcrumbs);
    }

    /**
     * Method to display the permission setting page
     * @return string
     */
    public function setPermissionPage(): string
    {
        // Set up breadcrumbs for navigation
        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView',
            'Manage Permission' => ASSERT_PATH . 'admin/setPermissionPage'
        ];

        //removing the super admin role because not to set permission to super admin
        $roles = $this->roleModelObj->getRoles();
        array_shift($roles);

        // Compare the stored permissions with the routes and checks in the tool right now
        $permissions = $this->adminModelObj->getPermissions();
        $catalog = new PermissionCatalog();
        $state = $catalog->classify($permissions);
        $liveModules = array_flip(array_column($state['live'], 'r_module_id'));
        $staleModules = array_flip(array_column($state['stale'], 'r_module_id'));
        // A module whose only permissions are stale (e.g. Redmine sync) is gone from the tool
        $modules = array_values(array_filter($this->adminModelObj->getModule(),
            static fn ($m) => isset($liveModules[$m['module_id']]) || !isset($staleModules[$m['module_id']])));

        $permissionData = [
            'roles' => $roles,
            'permissions' => $state['live'],
            'stale' => $state['stale'],
            'missing' => $state['missing'],
            'targets' => $catalog->availableTargets($permissions),
            'module' => $modules,
            'routeCount' => count($catalog->routes()),
            'checkCount' => count($catalog->codeChecks()),
        ];

        // Return the view for setting permissions with the permission data
        return $this->template_view("admin/setPermissionPage", $permissionData, "Manage Role Permissions", $breadcrumbs);
    }

    /**
     * Insert the role permissions based on user
     * @return Response
     */
    public function setPermission(): Response
    {
        // Get the selected user and permissions from the POST request
        $selectUser = $this->request->getPost('selectUser');
        // An empty selection is valid: it removes all permissions from the role.
        $permissions = $this->request->getPost('permissions') ?? [];

        // Check if input data is valid
        if (!isset($selectUser) || !is_array($permissions)) {
            // Handle the error
            return $this->response->setJSON(['success' => false]);
        }

        // Get existing permissions for the selected user
        $existingPermissionIds = $this->rolePermissionModel->getPermissionsByRole($selectUser);

        // Determine permissions to add and delete
        $permissionsToAdd = array_diff($permissions, $existingPermissionIds);
        $permissionsToDelete = array_diff($existingPermissionIds, $permissions);

        // Validate and insert new permissions
        $validData = [];
        $deleteData = [];
        foreach ($permissionsToAdd as $permission) {
            $data = [
                'r_role_id' => $selectUser,
                'r_permission_id' => $permission
            ];

            // Check validation
            $validationErrors = $this->hasInvalidInput($this->rolePermissionModel, $data);
            if ($validationErrors) {
                $validData[] = $data;
            } else {
                return $this->response->setJSON(['success' => false, 'validation' => true, 'errors' => $validationErrors]);
            }
        }

        // Insert all valid data at once
        if (!empty($validData)) {
            $result = $this->rolePermissionModel->insertRolePermission($validData);
            if (!$result) {
                return $this->response->setJSON(['success' => false]);
            }
        }

        // Delete permissions that are no longer present
        foreach ($permissionsToDelete as $permission) {
            $data = [
                'r_role_id' => $selectUser,
                'r_permission_id' => $permission
            ];

            $deleteData[] = $data;
        }

        if (!empty($deleteData)) {
            // Delete the permission
            $result = $this->rolePermissionModel->deleteRolePermission($deleteData);
        }

        // Return success response
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Create a user, or update one when user_id is posted.
     * New users either get an emailed invite (when mail is set up and requested) or a
     * temporary password shown once to the admin; both must be changed at first sign-in.
     */
    public function saveUser(): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }

        $post = $this->request->getPost();
        $userId = (int) ($post['user_id'] ?? 0);
        $existing = $userId ? $this->adminModelObj->getUser($userId) : null;
        if ($userId && !$existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'This user no longer exists.']);
        }

        $errors = $this->validateUser($post, $existing);
        if ($errors) {
            return $this->response->setJSON(['success' => false, 'errors' => $errors]);
        }

        $roleId = (int) $post['role_id'];
        $productRoles = $this->productRolesFromPost($post, $roleId);
        $fields = [
            'first_name'        => trim($post['first_name']),
            'last_name'         => trim($post['last_name'] ?? ''),
            'external_username' => trim($post['username']),
            'email_id'          => trim($post['email_id']),
            'r_role_id'         => $roleId,
        ];

        if ($existing) {
            $this->adminModelObj->updateUser($userId, $fields, $productRoles);
            Events::trigger('log_actions', $this->userAction(2, $userId, "User {$fields['first_name']} {$fields['last_name']} was updated"));

            return $this->response->setJSON(['success' => true, 'message' => 'User updated']);
        }

        $employeeId = (int) ($post['employee_id'] ?? 0) ?: $this->adminModelObj->nextEmployeeId();
        if ($this->adminModelObj->isTaken('external_employee_id', $employeeId)) {
            return $this->response->setJSON(['success' => false, 'errors' => ['employee_id' => 'This employee ID is already in use.']]);
        }

        $invite = !empty($post['send_invite']) && InviteMailer::isConfigured();
        $password = InviteMailer::temporaryPassword();
        $newId = $this->adminModelObj->createUser($fields + [
            'external_employee_id'    => $employeeId,
            'password'                => password_hash($password, PASSWORD_DEFAULT),
            'password_reset_required' => 'Y',
        ], $productRoles);
        if (!$newId) {
            return $this->response->setJSON(['success' => false, 'message' => 'The user could not be created.']);
        }
        Events::trigger('log_actions', $this->userAction(1, $newId, "User {$fields['first_name']} {$fields['last_name']} was created"));

        if ($invite) {
            (new InviteMailer())->queue([$newId], (int) session('employee_id'));

            return $this->response->setJSON(['success' => true, 'invited' => true, 'message' => "User created. An invite is on its way to {$fields['email_id']}."]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'User created',
            'username' => $fields['external_username'],
            'password' => $password,
        ]);
    }

    /**
     * Deactivate or reactivate a user. Deactivated users cannot sign in and are
     * signed out on their next request.
     */
    public function setUserStatus(): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }
        $userId = (int) $this->request->getPost('user_id');
        $active = $this->request->getPost('active') === '1';
        $user = $this->adminModelObj->getUser($userId);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'This user no longer exists.']);
        }
        if (!$active && (int) $user['external_employee_id'] === (int) session('employee_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'You cannot deactivate your own account.']);
        }
        if (!$active && (int) $user['r_role_id'] === 1 && $this->adminModelObj->countActiveSuperAdmins($userId) === 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'At least one active Super Admin is required.']);
        }

        $this->adminModelObj->updateUser($userId, ['is_deleted' => $active ? 'N' : 'Y']);
        $blockKey = 'user_blocked_' . (int) $user['external_employee_id'];
        $active ? cache()->delete($blockKey) : cache()->save($blockKey, 1, 0);
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        Events::trigger('log_actions', $this->userAction(2, $userId, "User {$name} was " . ($active ? 'reactivated' : 'deactivated')));

        return $this->response->setJSON(['success' => true, 'message' => $active ? "{$name} can sign in again" : "{$name} was deactivated"]);
    }

    /**
     * Give a user a new temporary password: shown once to the admin, or emailed
     * to the user when send_email is posted and mail is set up.
     */
    public function resetUserPassword(): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }
        $userId = (int) $this->request->getPost('user_id');
        $user = $this->adminModelObj->getUser($userId);
        if (!$user || $user['is_deleted'] === 'Y') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only active users can have their password reset.']);
        }
        $name = trim($user['first_name'] . ' ' . $user['last_name']);

        if ($this->request->getPost('send_email') && InviteMailer::isConfigured()) {
            (new InviteMailer())->queue([$userId], (int) session('employee_id'));
            Events::trigger('log_actions', $this->userAction(2, $userId, "Sign-in details were emailed to {$name}"));

            return $this->response->setJSON(['success' => true, 'invited' => true, 'message' => "New sign-in details are on their way to {$user['email_id']}."]);
        }

        $password = InviteMailer::temporaryPassword();
        $this->adminModelObj->updateUser($userId, [
            'password'                => password_hash($password, PASSWORD_DEFAULT),
            'password_reset_required' => 'Y',
        ]);
        Events::trigger('log_actions', $this->userAction(2, $userId, "Password of {$name} was reset"));

        return $this->response->setJSON(['success' => true, 'username' => $user['external_username'], 'password' => $password]);
    }

    /**
     * Activity of one user from the action log, paged by action id.
     */
    public function userActivity($userId): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }
        $user = $this->adminModelObj->getUser((int) $userId);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'User not found.']);
        }
        $before = (int) $this->request->getGet('before');
        $module = (int) $this->request->getGet('module');
        $limit = 30;
        $rows = $this->adminModelObj->getActivity((int) $userId, $before, $module, $limit);
        $hasMore = count($rows) > $limit;
        $rows = array_slice($rows, 0, $limit);

        $items = array_map(fn ($row) => [
            'id'      => (int) $row['action_id'],
            'date'    => $row['action_date'],
            'module'  => $row['module_name'] ? ucwords($row['module_name']) : 'General',
            'type'    => $row['action_type_name'],
            'product' => $row['product_name'],
            'html'    => $this->safeActionHtml((string) $row['action_data']),
        ], $rows);

        return $this->response->setJSON([
            'success' => true,
            'items'   => $items,
            'hasMore' => $hasMore,
            'modules' => $before ? null : array_map(static fn ($m) => [
                'id'    => (int) $m['module_id'],
                'name'  => ucwords($m['module_name']),
                'total' => (int) $m['total'],
            ], $this->adminModelObj->getActivityModules((int) $userId)),
        ]);
    }

    /**
     * Downloadable CSV template for the bulk import.
     */
    public function importTemplate(): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }
        $products = array_column($this->adminModelObj->getProducts(), 'product_name');
        $example = $products ? $products[0] . (count($products) > 1 ? '; ' . $products[1] . ':Product Owner' : '') : '';
        $csv = "first_name,last_name,username,email,role,products,employee_id\n"
             . 'Asha,Kumar,asha.kumar,asha.kumar@example.com,Developer,"' . str_replace('"', '""', $example) . "\",\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="orvexa-users-template.csv"')
            ->setBody($csv);
    }

    /**
     * Step 1 of the bulk import: parse and validate the CSV, keep the valid rows
     * server-side and return a preview with a per-row verdict.
     */
    public function importPreview(): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Choose a CSV file to import.']);
        }
        if ($file->getSize() > self::IMPORT_MAX_BYTES || strtolower($file->getClientExtension()) !== 'csv') {
            return $this->response->setJSON(['success' => false, 'message' => 'Upload a .csv file of up to 1 MB.']);
        }

        $parsed = $this->parseImportCsv($file->getTempName());
        if (isset($parsed['error'])) {
            return $this->response->setJSON(['success' => false, 'message' => $parsed['error']]);
        }

        $token = bin2hex(random_bytes(16));
        $valid = array_values(array_filter($parsed, static fn ($row) => $row['errors'] === []));
        // Only the validated payload is kept server-side, so the commit can't be tampered with
        cache()->save($this->importKey($token), array_column($valid, 'payload'), 1800);

        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'rows'    => array_map(static fn ($row) => [
                'line'     => $row['line'],
                'name'     => trim($row['user']['first_name'] . ' ' . $row['user']['last_name']),
                'username' => $row['user']['external_username'],
                'email'    => $row['user']['email_id'],
                'role'     => $row['role_name'],
                'products' => $row['product_labels'],
                'errors'   => $row['errors'],
            ], $parsed),
            'valid'   => count($valid),
            'invalid' => count($parsed) - count($valid),
        ]);
    }

    /**
     * Step 2 of the bulk import: create the previewed valid rows.
     */
    public function importCommit(): Response
    {
        if ($denied = $this->denyUnlessCanManageUsers()) {
            return $denied;
        }
        $token = (string) $this->request->getPost('token');
        $rows = preg_match('/^[a-f0-9]{32}$/', $token) ? cache($this->importKey($token)) : null;
        if (!is_array($rows) || $rows === []) {
            return $this->response->setJSON(['success' => false, 'message' => 'This import has expired. Upload the file again.']);
        }
        cache()->delete($this->importKey($token));
        $invite = $this->request->getPost('send_invites') && InviteMailer::isConfigured();

        $created = $skipped = [];
        $newIds = [];
        foreach ($rows as $row) {
            // Re-check uniqueness: someone may have been added since the preview
            if ($this->adminModelObj->isTaken('external_username', $row['user']['external_username'])
                || $this->adminModelObj->isTaken('email_id', $row['user']['email_id'])
                || ($row['user']['external_employee_id'] && $this->adminModelObj->isTaken('external_employee_id', $row['user']['external_employee_id']))) {
                $skipped[] = $row['user']['external_username'];
                continue;
            }
            $user = $row['user'];
            $user['external_employee_id'] = $user['external_employee_id'] ?: $this->adminModelObj->nextEmployeeId();
            $password = InviteMailer::temporaryPassword();
            $user['password'] = password_hash($password, PASSWORD_DEFAULT);
            $user['password_reset_required'] = 'Y';
            $id = $this->adminModelObj->createUser($user, $row['products']);
            if (!$id) {
                $skipped[] = $user['external_username'];
                continue;
            }
            $newIds[] = $id;
            $created[] = [
                'name'     => trim($user['first_name'] . ' ' . $user['last_name']),
                'username' => $user['external_username'],
                'email'    => $user['email_id'],
                'password' => $invite ? null : $password,
            ];
        }

        if ($newIds) {
            Events::trigger('log_actions', $this->userAction(1, 0, count($newIds) . ' users were imported from a CSV file'));
            if ($invite) {
                (new InviteMailer())->queue($newIds, (int) session('employee_id'));
            }
        }

        return $this->response->setJSON(['success' => true, 'invited' => $invite, 'created' => $created, 'skipped' => $skipped]);
    }

    private const IMPORT_MAX_ROWS = 500;
    private const IMPORT_MAX_BYTES = 1048576;

    private function importKey(string $token): string
    {
        return 'user_import_' . (int) session('employee_id') . '_' . $token;
    }

    /**
     * Parse the import CSV into rows with normalised user data and validation errors.
     *
     * @return array<int, array>|array{error: string}
     */
    private function parseImportCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        $first = (string) fgets($handle);
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $header = array_map(static fn ($h) => strtolower(trim(str_replace([' ', '-'], '_', $h))), str_getcsv($first, $delimiter));
        $aliases = ['email_id' => 'email', 'e_mail' => 'email', 'firstname' => 'first_name', 'lastname' => 'last_name', 'user_name' => 'username', 'login' => 'username', 'employeeid' => 'employee_id'];
        $header = array_map(static fn ($h) => $aliases[$h] ?? $h, $header);
        foreach (['first_name', 'username', 'email'] as $required) {
            if (!in_array($required, $header, true)) {
                fclose($handle);

                return ['error' => "The file needs a \"{$required}\" column. Download the template to see the expected columns."];
            }
        }

        $roles = [];
        foreach ($this->roleModelObj->getRoles() as $role) {
            $roles[strtolower(trim($role['role_name']))] = ['id' => (int) $role['role_id'], 'name' => $role['role_name']];
            $roles[(string) $role['role_id']] = ['id' => (int) $role['role_id'], 'name' => $role['role_name']];
        }
        $products = [];
        foreach ($this->adminModelObj->getProducts() as $product) {
            $products[strtolower(trim($product['product_name']))] = ['id' => (int) $product['product_id'], 'name' => $product['product_name']];
        }
        $defaultRole = $roles['developer'] ?? reset($roles);

        $rows = [];
        $seen = ['username' => [], 'email' => [], 'employee_id' => []];
        $line = 1;
        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($cells, static fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }
            if (count($rows) >= self::IMPORT_MAX_ROWS) {
                fclose($handle);

                return ['error' => 'Import up to ' . self::IMPORT_MAX_ROWS . ' users at a time.'];
            }
            $value = static fn ($key) => trim((string) ($cells[array_search($key, $header, true)] ?? ''));
            $get = static fn ($key) => in_array($key, $header, true) ? $value($key) : '';
            $errors = [];

            $roleText = strtolower($get('role'));
            $role = $roleText === '' ? $defaultRole : ($roles[$roleText] ?? null);
            if (!$role) {
                $errors[] = "Unknown role \"{$get('role')}\".";
                $role = $defaultRole;
            }

            $productRoles = [];
            $labels = [];
            foreach (preg_split('/[;|]/', $get('products')) as $entry) {
                $entry = trim($entry);
                if ($entry === '') {
                    continue;
                }
                [$productName, $productRole] = array_map('trim', array_pad(explode(':', $entry, 2), 2, ''));
                $product = $products[strtolower($productName)] ?? null;
                $pRole = $productRole === '' ? $role : ($roles[strtolower($productRole)] ?? null);
                if (!$product) {
                    $errors[] = "Unknown product \"{$productName}\".";
                } elseif (!$pRole) {
                    $errors[] = "Unknown role \"{$productRole}\" for {$product['name']}.";
                } else {
                    $productRoles[$product['id']] = $pRole['id'];
                    $labels[] = $product['name'] . ($pRole['id'] !== $role['id'] ? " ({$pRole['name']})" : '');
                }
            }

            $user = [
                'first_name'           => $get('first_name'),
                'last_name'            => $get('last_name'),
                'external_username'    => $get('username'),
                'email_id'             => $get('email'),
                'r_role_id'            => $role['id'],
                'external_employee_id' => (int) $get('employee_id'),
            ];
            $validation = service('validation', null, false)->setRules([
                'first_name'           => ['label' => 'First name', 'rules' => 'required|max_length[50]'],
                'last_name'            => ['label' => 'Last name', 'rules' => 'permit_empty|max_length[50]'],
                'external_username'    => ['label' => 'Username', 'rules' => 'required|min_length[3]|max_length[50]|regex_match[/^[A-Za-z0-9._-]+$/]',
                                           'errors' => ['regex_match' => 'Username may only use letters, numbers, dots, dashes and underscores.']],
                'email_id'             => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[50]'],
            ]);
            if (!$validation->run($user)) {
                array_push($errors, ...array_values($validation->getErrors()));
            }
            if ($get('employee_id') !== '' && $user['external_employee_id'] <= 0) {
                $errors[] = 'Employee ID must be a positive number.';
            }

            $username = strtolower($user['external_username']);
            $email = strtolower($user['email_id']);
            if ($username !== '' && (isset($seen['username'][$username]) || $this->adminModelObj->isTaken('external_username', $user['external_username']))) {
                $errors[] = isset($seen['username'][$username]) ? "Username repeats line {$seen['username'][$username]}." : 'Username is already in use.';
            }
            if ($email !== '' && (isset($seen['email'][$email]) || $this->adminModelObj->isTaken('email_id', $user['email_id']))) {
                $errors[] = isset($seen['email'][$email]) ? "Email repeats line {$seen['email'][$email]}." : 'Email is already in use.';
            }
            $emp = $user['external_employee_id'];
            if ($emp > 0 && (isset($seen['employee_id'][$emp]) || $this->adminModelObj->isTaken('external_employee_id', $emp))) {
                $errors[] = isset($seen['employee_id'][$emp]) ? "Employee ID repeats line {$seen['employee_id'][$emp]}." : 'Employee ID is already in use.';
            }
            $seen['username'][$username] ??= $line;
            $seen['email'][$email] ??= $line;
            if ($emp > 0) {
                $seen['employee_id'][$emp] ??= $line;
            }

            $rows[] = [
                'line'           => $line,
                'user'           => $user,
                'payload'        => ['user' => $user, 'products' => $productRoles],
                'role_name'      => $role['name'],
                'product_labels' => $labels,
                'errors'         => array_values(array_unique($errors)),
            ];
        }
        fclose($handle);

        return $rows === [] ? ['error' => 'The file has no user rows.'] : $rows;
    }

    /**
     * @return array<int,int> product id => role id, for the checked products
     */
    private function productRolesFromPost(array $post, int $defaultRole): array
    {
        $roles = array_map('intval', array_column($this->roleModelObj->getRoles(), 'role_id'));
        $productIds = array_column($this->adminModelObj->getProducts(), 'product_id');
        $chosen = (array) ($post['product_role'] ?? []);
        $map = [];
        foreach ((array) ($post['products'] ?? []) as $productId) {
            $productId = (int) $productId;
            if (!in_array($productId, array_map('intval', $productIds), true)) {
                continue;
            }
            $role = (int) ($chosen[$productId] ?? 0);
            $map[$productId] = in_array($role, $roles, true) ? $role : $defaultRole;
        }

        return $map;
    }

    /**
     * @return array<string,string> field => message
     */
    private function validateUser(array $post, ?array $existing): array
    {
        $userId = $existing ? (int) $existing['user_id'] : null;
        $validation = service('validation')->setRules([
            'first_name'  => ['label' => 'First name', 'rules' => 'required|max_length[50]'],
            'last_name'   => ['label' => 'Last name', 'rules' => 'permit_empty|max_length[50]'],
            'username'    => ['label' => 'Username', 'rules' => 'required|min_length[3]|max_length[50]|regex_match[/^[A-Za-z0-9._-]+$/]',
                              'errors' => ['regex_match' => 'Use only letters, numbers, dots, dashes and underscores in the username.']],
            'email_id'    => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[50]'],
            'role_id'     => ['label' => 'Role', 'rules' => 'required|is_natural_no_zero'],
            'employee_id' => ['label' => 'Employee ID', 'rules' => 'permit_empty|is_natural_no_zero'],
        ]);
        if (!$validation->run($post)) {
            return $validation->getErrors();
        }

        $errors = [];
        if ($this->adminModelObj->isTaken('external_username', trim($post['username']), $userId)) {
            $errors['username'] = 'This username is already in use.';
        }
        if ($this->adminModelObj->isTaken('email_id', trim($post['email_id']), $userId)) {
            $errors['email_id'] = 'This email is already in use.';
        }
        if (!in_array($post['role_id'], array_column($this->roleModelObj->getRoles(), 'role_id'))) {
            $errors['role_id'] = 'Choose a valid role.';
        }
        if ($existing && (int) $existing['r_role_id'] !== (int) $post['role_id'] && !has_permission('admin/updaterole')) {
            $errors['role_id'] = 'You do not have permission to change roles.';
        }
        if ($existing && (int) $existing['r_role_id'] === 1 && (int) $post['role_id'] !== 1 && $existing['is_deleted'] === 'N'
            && $this->adminModelObj->countActiveSuperAdmins($userId) === 0) {
            $errors['role_id'] = 'At least one active Super Admin is required.';
        }

        return $errors;
    }

    /**
     * Log text may contain <b> and <br> (written by the app); everything else is escaped.
     */
    private function safeActionHtml(string $html): string
    {
        return preg_replace_callback(
            '#&lt;(/?)(b|strong|i|em)&gt;|&lt;br\s*/?&gt;#i',
            static fn ($m) => ($m[2] ?? '') !== '' ? '<' . $m[1] . strtolower($m[2]) . '>' : '<br>',
            esc($html)
        );
    }

    private function denyUnlessCanManageUsers(): ?Response
    {
        if (has_permission('admin/manageUser')) {
            return null;
        }

        return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'You do not have permission to manage users.']);
    }

    private function userAction(int $actionType, int $userId, string $message): array
    {
        return [
            'r_action_type_id' => $actionType,
            'r_module_id'      => 15, // manage users
            'reference_id'     => $userId,
            'product_id'       => 0,
            'action_data'      => $message,
        ];
    }

    /**
     * Update the role of a user
     * @return Response
     */
    public function updateRole(): Response
    {
        // Get user data from POST request
        $userData = $this->request->getPost();

        // Update user role using the AdminModel
        $this->adminModelObj->updateUserRole($userData);

        // Return success response
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Search for roles based on a query
     */
    public function searchRole()
    {
        // Get JSON input from the request
        $jsonInput = $this->request->getJSON(true);

        // Extract search query from JSON input
        $searchQuery = isset($jsonInput['searchQuery']) ? $jsonInput['searchQuery'] : '';

        // Check if the search query is valid
        if (!empty($searchQuery)) {
            // Filter users based on search query using the AdminModel
            $filteredData = $this->adminModelObj->userFilter($searchQuery);

            // Return filtered data
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $filteredData
            ]);
        } else {
            // Return error response if search query is invalid
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid search query'
            ]);
        }
    }

    /**
     * Synchronize user data
     * @return bool
     */
    public function userSync(): bool
    {
        // External external user synchronization is disabled.
        return false;
    }

    /**
     * Set a new permission
     */
    /**
     * Point an existing (stale) permission at a feature that is checked in the code.
     */
    public function relinkPermission(): Response
    {
        if ($denied = $this->denyUnlessCanEditPermissions()) {
            return $denied;
        }
        $permissionId = (int) $this->request->getPost('permission_id');
        $route = trim((string) $this->request->getPost('route'), '/ ');
        if ($error = $this->invalidPermissionRoute($route, $permissionId)) {
            return $this->response->setJSON(['success' => false, 'message' => $error]);
        }
        $this->db()->table('orvexa_permission')->where('permission_id', $permissionId)->update(['routes_url' => $route]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Remove permissions that nothing in the tool uses any more.
     */
    public function cleanupPermissions(): Response
    {
        if ($denied = $this->denyUnlessCanEditPermissions()) {
            return $denied;
        }
        $ids = array_filter(array_map('intval', (array) $this->request->getPost('ids')));
        $catalog = new PermissionCatalog();
        $stale = array_map('intval', array_column($catalog->classify($this->adminModelObj->getPermissions())['stale'], 'permission_id'));
        // Only permissions that are really unused can be removed this way
        $ids = array_values(array_intersect($ids, $stale));
        if ($ids === []) {
            return $this->response->setJSON(['success' => false, 'message' => 'There is nothing to remove.']);
        }
        $this->db()->table('orvexa_permission')->whereIn('permission_id', $ids)->update(['is_deleted' => 'Y']);
        $this->db()->table('orvexa_role_permission')->whereIn('r_permission_id', $ids)->update(['is_deleted' => 'Y']);

        return $this->response->setJSON(['success' => true, 'removed' => count($ids)]);
    }

    /**
     * @return string|null why the route can't be used for a permission
     */
    private function invalidPermissionRoute(string $route, int $exceptPermissionId = 0): ?string
    {
        if ($route === '') {
            return 'Choose the feature (route) this permission controls.';
        }
        $catalog = new PermissionCatalog();
        if (!$catalog->routeExists($route) && !isset($catalog->codeChecks()[strtolower($route)])) {
            return "\"{$route}\" is not a route or screen check in the tool.";
        }
        $duplicate = $this->db()->table('orvexa_permission')
            ->where('routes_url', $route)->where('is_deleted', 'N')->where('permission_id !=', $exceptPermissionId)
            ->countAllResults();

        return $duplicate ? 'Another permission already controls this feature.' : null;
    }

    private function denyUnlessCanEditPermissions(): ?Response
    {
        if (has_permission('admin/setPermissions')) {
            return null;
        }

        return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'You do not have permission to change permissions.']);
    }

    private function db()
    {
        return \Config\Database::connect();
    }

    public function setNewPermission()
    {
        if ($denied = $this->denyUnlessCanEditPermissions()) {
            return $denied;
        }
        if ($error = $this->invalidPermissionRoute(trim((string) $this->request->getPost('routesURLModel'), '/ '))) {
            return $this->response->setJSON(['validation' => true, 'errors' => ['routesURLModel' => $error]]);
        }
        // Check if the set permission button was clicked
        if ($this->request->getPost('setPermissionButton')) {
            // Prepare permission data from POST request
            $data = [
                'permission_name' => str_replace(" ", "_", strtoupper(trim($this->request->getPost('permissionNameModal')))),
                'r_module_id' => $this->request->getPost('moduleModel'),
                'routes_url' => $this->request->getPost('routesURLModel'),
            ];

            // Check validation
            $validationErrors = $this->hasInvalidInput($this->permissionModelObj, $data);
            if ($validationErrors !== true) {
                return $this->response->setJSON(['validation' => true, 'errors' => $validationErrors]);
            }
            // Add new permission using the AdminModel
            $result = $this->permissionModelObj->addNewPermission($data);

            // Return response based on the result
            if ($result) {
                return $this->response->setJSON(['success' => true, 'permission' => true]);
            } else {
                return $this->response->setJSON(['success' => false]);
            }
        }
    }

    /**
     * Get permissions by role
     */
    public function getSpecificPermissions()
    {
        // Get selected user role from POST request
        $roleId = $this->request->getPost('selectUser');

        // Get permissions for the selected role using the AdminModel
        $permissions = $this->rolePermissionModel->getPermissionsByRole($roleId);

        // Return permissions as JSON response
        return $this->response->setJSON(['permissions' => $permissions]);
    }

    /**
     * Get the permission Name to delete the permission
     */
    public function getPermissionName()
    {
        $permissionsName = $this->permissionModelObj->getPermissions();

        // Return permissions as JSON response
        return $this->response->setJSON(['permissionsName' => $permissionsName]);
    }

    /**
     * delete the permission if not wanted
     */
    public function deletePermission($id)
    {
        if ($denied = $this->denyUnlessCanEditPermissions()) {
            return $denied;
        }
        $result = $this->permissionModelObj->deletePermission($id);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'permission' => true]);
        } else {
            return $this->response->setJSON(['success' => false]);
        }
    }

    /**
     * Gets the details to show in the edit modal for permission
     */
    public function getPermissionDetails($permissionId)
    {
        $permissionDetails = $this->permissionModelObj->getPermissionDetailsById($permissionId);

        // Return permission details as JSON response
        return $this->response->setJSON($permissionDetails);
    }

    /**
     * 
     */
    public function updatePermission()
    {
        if ($denied = $this->denyUnlessCanEditPermissions()) {
            return $denied;
        }
        $route = trim((string) $this->request->getPost('routesURLModel'), '/ ');
        if ($error = $this->invalidPermissionRoute($route, (int) $this->request->getPost('editPermissionNameModel'))) {
            return $this->response->setJSON(['success' => false, 'validation' => true, 'errors' => ['routesURLModel' => $error]]);
        }

        $deleteDetails = $this->request->getPost();

        $result = $this->permissionModelObj->updatePermissionById($deleteDetails);
        if ($result) {
            return $this->response->setJSON(['success' => true, 'permission' => true]);
        } else {
            return $this->response->setJSON(['success' => false]);
        }
    }
}
