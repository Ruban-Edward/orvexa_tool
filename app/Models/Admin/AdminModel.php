<?php

/**
 * AdminModel.php
 *
 * @category   Model
 * @purpose    To fetch the user related data from database
 * @created    09 July 2024
 * @author     Ruban Edward
 */

namespace App\Models\Admin;

use App\Models\BaseModel;

class AdminModel extends BaseModel
{
    /**
     * All users (active and inactive) with role, product access and last sign-in.
     * @return array
     */
    public function getUsers(): array
    {
        $sql = "SELECT
                    u.user_id,
                    u.external_employee_id AS employee_id,
                    u.external_username AS username,
                    u.first_name,
                    u.last_name,
                    u.email_id,
                    u.is_deleted,
                    u.password_reset_required,
                    u.created_date,
                    r.role_id,
                    r.role_name,
                    GROUP_CONCAT(DISTINCT p.external_project_id ORDER BY p.product_name SEPARATOR ',') AS product_ids,
                    GROUP_CONCAT(DISTINCT p.product_name ORDER BY p.product_name SEPARATOR '||') AS product_names,
                    GROUP_CONCAT(DISTINCT CONCAT(p.external_project_id, ':', COALESCE(pu.r_role_id, u.r_role_id)) SEPARATOR ',') AS product_roles,
                    MAX(login.last_login) AS last_login,
                    MAX(invite.status) AS invite_status,
                    MAX(invite.sent_date) AS invite_date
                FROM orvexa_user AS u
                LEFT JOIN orvexa_role AS r ON r.role_id = u.r_role_id
                LEFT JOIN orvexa_product_user AS pu ON pu.r_user_id = u.external_employee_id AND pu.is_deleted = 'N'
                LEFT JOIN orvexa_product AS p ON p.external_project_id = pu.r_product_id AND p.is_deleted = 'N'
                LEFT JOIN (
                    SELECT r_user_id, MAX(action_date) AS last_login
                    FROM orvexa_user_action
                    WHERE action_data = 'login'
                    GROUP BY r_user_id
                ) AS login ON login.r_user_id = u.user_id
                LEFT JOIN (
                    SELECT i.r_user_id, i.status, i.sent_date
                    FROM orvexa_user_invite i
                    INNER JOIN (SELECT r_user_id, MAX(invite_id) AS invite_id FROM orvexa_user_invite GROUP BY r_user_id) latest
                        ON latest.invite_id = i.invite_id
                ) AS invite ON invite.r_user_id = u.user_id
                GROUP BY u.user_id
                ORDER BY u.is_deleted ASC, u.first_name ASC, u.last_name ASC";

        return $this->db->query($sql)->getResultArray();
    }

    public function getUser(int $userId): ?array
    {
        return $this->db->table('orvexa_user')->where('user_id', $userId)->get()->getRowArray();
    }

    /**
     * Products an admin can grant access to.
     */
    public function getProducts(): array
    {
        return $this->db->table('orvexa_product')
            ->select('external_project_id AS product_id, product_name')
            ->where('is_deleted', 'N')
            ->orderBy('product_name')
            ->get()->getResultArray();
    }

    /**
     * Whether a unique user field is already taken (optionally ignoring one user).
     */
    public function isTaken(string $column, $value, ?int $exceptUserId = null): bool
    {
        $builder = $this->db->table('orvexa_user')->where($column, $value);
        if ($exceptUserId !== null) {
            $builder->where('user_id !=', $exceptUserId);
        }

        return $builder->countAllResults() > 0;
    }

    public function nextEmployeeId(): int
    {
        return (int) $this->db->query('SELECT COALESCE(MAX(external_employee_id), 0) + 1 AS next FROM orvexa_user')->getRow()->next;
    }

    public function countActiveSuperAdmins(?int $exceptUserId = null): int
    {
        $builder = $this->db->table('orvexa_user')->where(['r_role_id' => 1, 'is_deleted' => 'N']);
        if ($exceptUserId !== null) {
            $builder->where('user_id !=', $exceptUserId);
        }

        return $builder->countAllResults();
    }

    /**
     * Create a user and their product access in one transaction. Returns the new user_id.
     */
    /**
     * @param array<int,int> $productRoles product id => role id
     */
    public function createUser(array $user, array $productRoles): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->transStart();
        $this->db->table('orvexa_user')->insert($user + [
            'created_date' => $now,
            'updated_date' => $now,
            'is_deleted'   => 'N',
        ]);
        $userId = (int) $this->db->insertID();
        $this->setUserProducts((int) $user['external_employee_id'], $productRoles);
        $this->db->transComplete();

        return $this->db->transStatus() ? $userId : 0;
    }

    /**
     * @param array<int,int>|null $productRoles product id => role id; null leaves access unchanged
     */
    public function updateUser(int $userId, array $fields, ?array $productRoles = null): bool
    {
        $this->db->transStart();
        $this->db->table('orvexa_user')->where('user_id', $userId)->update($fields + ['updated_date' => date('Y-m-d H:i:s')]);
        if ($productRoles !== null) {
            $this->setUserProducts((int) $this->getUser($userId)['external_employee_id'], $productRoles);
        }
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Make a user's product access exactly $productRoles (product id => role on that product),
     * re-activating earlier membership rows where possible.
     */
    public function setUserProducts(int $employeeId, array $productRoles): void
    {
        $existing = array_map('intval', array_column(
            $this->db->table('orvexa_product_user')->select('r_product_id')->where('r_user_id', $employeeId)->get()->getResultArray(),
            'r_product_id'
        ));

        $this->db->table('orvexa_product_user')->where('r_user_id', $employeeId)->update(['is_deleted' => 'Y']);
        $new = [];
        foreach ($productRoles as $productId => $roleId) {
            if (in_array((int) $productId, $existing, true)) {
                $this->db->table('orvexa_product_user')
                    ->where(['r_user_id' => $employeeId, 'r_product_id' => (int) $productId])
                    ->update(['is_deleted' => 'N', 'r_role_id' => (int) $roleId]);
            } else {
                $new[] = ['r_product_id' => (int) $productId, 'r_user_id' => $employeeId, 'r_role_id' => (int) $roleId, 'is_deleted' => 'N'];
            }
        }
        if ($new !== []) {
            $this->db->table('orvexa_product_user')->insertBatch($new);
        }
    }

    /**
     * A user's history from the action log, newest first, for the activity view.
     */
    public function getActivity(int $userId, int $beforeId = 0, int $moduleId = 0, int $limit = 30): array
    {
        $builder = $this->db->table('orvexa_user_action a')
            ->select('a.action_id, a.action_data, a.action_date, a.reference_id, m.module_id, m.module_name, t.action_type_name, p.product_name')
            ->join('orvexa_module m', 'm.module_id = a.r_module_id', 'left')
            ->join('orvexa_action_type t', 't.action_type_id = a.r_action_type_id', 'left')
            ->join('orvexa_product p', 'p.external_project_id = a.r_product_id AND a.r_product_id > 0', 'left')
            ->where('a.r_user_id', $userId)
            ->where('a.is_deleted', 'N');
        if ($beforeId > 0) {
            $builder->where('a.action_id <', $beforeId);
        }
        if ($moduleId > 0) {
            $builder->where('a.r_module_id', $moduleId);
        }

        return $builder->orderBy('a.action_id', 'DESC')->limit($limit + 1)->get()->getResultArray();
    }

    /**
     * Modules that appear in a user's history (for the activity filter).
     */
    public function getActivityModules(int $userId): array
    {
        return $this->db->table('orvexa_user_action a')
            ->select('m.module_id, m.module_name, COUNT(*) AS total')
            ->join('orvexa_module m', 'm.module_id = a.r_module_id')
            ->where(['a.r_user_id' => $userId, 'a.is_deleted' => 'N'])
            ->groupBy('m.module_id')
            ->orderBy('m.module_name')
            ->get()->getResultArray();
    }

    public function findUserByEmployeeId(int $employeeId): ?array
    {
        return $this->db->table('orvexa_user')->where('external_employee_id', $employeeId)->get()->getRowArray();
    }

    /**
     * Retrieves the Permission and Module name from the Database
     * @return array
     */
    public function getPermissions(): array
    {
        // SQL query to get permissions along with their modules
        $sql = "SELECT 
                    p.permission_id, 
                    p.permission_name, 
                    m.module_name 
                FROM 
                    orvexa_permission as p 
                    INNER JOIN orvexa_module AS m ON m.module_id = p.r_module_id 
                WHERE 
                    p.is_deleted = :is_deleted: 
                ORDER BY 
                    r_module_id ASC";

        // Execute the query with parameter binding
        $result = $this->db->query($sql, [
            'is_deleted' => 'N'
        ]);

        // Return the result as an associative array
        return $result->getResultArray();
    }

    /**
     * Update the role of a user
     * @param array $data
     * @return bool
     */
    public function updateUserRole($data): bool
    {
        // SQL query to update user role
        $sql = "UPDATE 
                    orvexa_user 
                SET 
                    r_role_id = :r_role_id: 
                WHERE 
                    user_id = :r_user_id:";

        // Execute the query with parameter binding
        $result = $this->db->query($sql, [
            'r_role_id' => $data['selectUser'],
            'r_user_id' => $data['userId'],
        ]);

        // Return the result of the query execution
        return $result;
    }

    /**
     * Filter users based on a search query
     * @param string $searchQuery
     * @return array
     */
    public function userFilter($searchQuery): array
    {
        // SQL query to filter users by first or last name
        $sql = "SELECT 
                    user.user_id, 
                    user.first_name, 
                    user.last_name, 
                    user.email_id, 
                    role.role_id, 
                    role.role_name 
                FROM 
                    orvexa_user AS user 
                    INNER JOIN orvexa_role AS role ON role.role_id = user.r_role_id 
                WHERE 
                    user.is_deleted = :is_deleted: 
                    AND (
                        LOWER(user.first_name) LIKE :search: ESCAPE '!'
                        OR LOWER(user.last_name) LIKE :search: ESCAPE '!'
                    )";

        // Bind the search term (escaping LIKE wildcards) instead of interpolating it into the SQL
        $term  = strtolower(trim((string) $searchQuery));
        $term  = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $query = $this->db->query($sql, [
            "is_deleted" => "N",
            "search"     => '%' . $term . '%',
        ]);

        // Check if any rows are returned
        if ($query->getNumRows() > 0) {
            // Return the result as an associative array
            return $query->getResultArray();
        } else {
            // Return an empty array if no rows are found
            return [];
        }
    }

    /**
     * Get the last synchronization date and time
     * @return array
     */
    public function getLastSync(): array
    {
        // SQL query to get the last sync datetime for user syncs
        $sql = "SELECT 
                    sync_datetime 
                FROM 
                    orvexa_sync_activities 
                WHERE 
                    sync_type = :sync_type: 
                ORDER BY 
                    sync_datetime DESC 
                LIMIT 
                    1";

        // Execute the query with parameter binding
        $query = $this->db->query($sql, [
            "sync_type" => "usersync"
        ]);

        // Return the result as an associative array
        return $query->getResultArray();
    }

    /**
     * Get module details from the database
     * @return array
     */
    public function getModule(): array
    {
        // SQL query to get module details
        $sql = "SELECT 
                    module_id, 
                    module_name 
                FROM 
                    orvexa_module 
                WHERE 
                    is_deleted = :is_deleted:";

        // Execute the query with parameter binding
        $query = $this->db->query($sql, [
            "is_deleted" => "N"
        ]);

        // Return the result as an associative array
        return $query->getResultArray();
    }
}
