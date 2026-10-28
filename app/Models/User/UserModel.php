<?php

namespace App\Models\User;

use CodeIgniter\Model;
use App\Models\BaseModel;

/**
 * Handles all the user database operations
 *
 *@Created by	Author
 *@Modified by	Author
 *@Created date 13-06-2024
 *@Modified date 13-06-2024
 */

class UserModel extends BaseModel
{
    protected $table = "users";
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'firstname',
        'lastname',
        'username',
        'password',
        'api_key'
    ];



    /**
     * UserModel::getUserLogin()
     *
     * User Model get login details
     */

    public function getUser($input): array
    {
        // Look the account up by username only; the password is verified in PHP so
        // that both modern (bcrypt/argon) hashes and legacy MD5 hashes are supported.
        $sql = "SELECT
                    user_id,
                    first_name,
                    external_employee_id,
                    r_role_id,
                    external_api_key,
                    password,
                    password_reset_required
                FROM
                    orvexa_user
                WHERE
                    external_username=:username:
                AND
                    is_deleted='N'
                LIMIT 1
        ";
        $query = $this->query($sql, ['username' => $input['username']]);
        if ($query->getNumRows() === 0) {
            return [];
        }
        $user   = $query->getRow();
        $stored = (string) $user->password;
        $plain  = (string) $input['password'];

        if (! $this->passwordMatches($plain, $stored)) {
            return [];
        }
        if (! $this->isModernHash($stored) || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $this->db->table('orvexa_user')
                ->where('user_id', $user->user_id)
                ->update(['password' => password_hash($plain, PASSWORD_DEFAULT)]);
        }
        unset($user->password);
        return [$user];
    }

    private function isModernHash(string $stored): bool
    {
        return str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon');
    }

    /**
     * Supports modern (bcrypt/argon) hashes and legacy unsalted MD5, compared in constant time.
     */
    private function passwordMatches(string $plain, string $stored): bool
    {
        return $this->isModernHash($stored)
            ? password_verify($plain, $stored)
            : hash_equals(strtolower($stored), md5($plain));
    }

    /**
     * Profile details for the signed-in user, including the role name.
     */
    public function getProfile(int $employeeId): ?array
    {
        $sql = "SELECT u.user_id, u.external_username, u.external_employee_id, u.first_name, u.last_name, u.email_id,
                       u.date_of_birth, u.created_date, r.role_name
                FROM orvexa_user u
                LEFT JOIN orvexa_role r ON r.role_id = u.r_role_id
                WHERE u.external_employee_id = :id: AND u.is_deleted = 'N'";

        return $this->query($sql, ['id' => $employeeId])->getRowArray();
    }

    /**
     * Whether a username is used by anyone other than the given user (case-insensitive,
     * like the login lookup).
     */
    public function usernameTaken(string $username, int $exceptEmployeeId): bool
    {
        return $this->db->table('orvexa_user')
            ->where('LOWER(external_username)', strtolower($username))
            ->where('external_employee_id !=', $exceptEmployeeId)
            ->countAllResults() > 0;
    }

    /**
     * Save the details a user may change themselves.
     */
    public function updateOwnDetails(int $employeeId, array $details): bool
    {
        return $this->db->table('orvexa_user')
            ->where(['external_employee_id' => $employeeId, 'is_deleted' => 'N'])
            ->update($details + ['updated_date' => date('Y-m-d H:i:s')]);
    }

    /**
     * The user's role on each product they belong to (product id => role name).
     */
    public function getProductRoles(int $employeeId): array
    {
        $rows = $this->db->table('orvexa_product_user pu')
            ->select('pu.r_product_id, r.role_name')
            ->join('orvexa_role r', 'r.role_id = pu.r_role_id')
            ->where(['pu.r_user_id' => $employeeId, 'pu.is_deleted' => 'N'])
            ->get()->getResultArray();

        return array_column($rows, 'role_name', 'r_product_id');
    }

    /**
     * Replace the password after confirming the current one.
     */
    public function changePassword(int $employeeId, string $current, string $new): bool
    {
        $row = $this->db->table('orvexa_user')
            ->select('password')
            ->where(['external_employee_id' => $employeeId, 'is_deleted' => 'N'])
            ->get()->getRowArray();
        if (! $row || ! $this->passwordMatches($current, (string) $row['password'])) {
            return false;
        }

        return $this->db->table('orvexa_user')
            ->where('external_employee_id', $employeeId)
            ->update([
                'password'                => password_hash($new, PASSWORD_DEFAULT),
                'password_reset_required' => 'N',
                'updated_date'            => date('Y-m-d H:i:s'),
            ]);
    }



    /**
     * UserModel::inserOrUpdatetUser()
     *
     * created by Stervin Richard
     * User Model insert or update user details
     */

    public function insertOrUpdatetUser($userData)
    {
        $query = "
                INSERT INTO orvexa_user (
                    external_username,
                    external_employee_id,
                    external_api_key,
                    first_name,
                    last_name,
                    email_id,
                    password,
                    r_role_id,
                    created_date,
                    is_deleted
                ) VALUES ";

        $values = [];
        $params = [];

        foreach ($userData as $index => $item) {
            $values[] = "(
            :username_{$index}:,
            :employee_id_{$index}:,
            :api_key_{$index}:,
            :first_name_{$index}:,
            :last_name_{$index}:,
            :email_id_{$index}:,
            :password_{$index}:,
            :role_id_{$index}:,
            CURRENT_TIMESTAMP,
            'N'
        )";

            $params["username_{$index}"] = $item["username"];
            $params["employee_id_{$index}"] = $item["employee_id"];
            $params["api_key_{$index}"] = $item["api_key"];
            $params["first_name_{$index}"] = $item["first_name"];
            $params["last_name_{$index}"] = $item["last_name"];
            $params["email_id_{$index}"] = $item["email_id"];
            $params["password_{$index}"] = password_hash($item["password"], PASSWORD_DEFAULT);
            $params["role_id_{$index}"] = $item["role_id"];
        }

        $query .= implode(", ", $values);

        $query .= "
                ON DUPLICATE KEY UPDATE
                    external_username = VALUES(external_username),
                    external_api_key = VALUES(external_api_key),
                    first_name = VALUES(first_name),
                    last_name = VALUES(last_name),
                    email_id = VALUES(email_id),
                    password = VALUES(password),
                    updated_date = CURRENT_TIMESTAMP,
                    is_deleted = VALUES(is_deleted)
                ";
        $result = $this->db->query($query, $params);
        return $result;
    }


}