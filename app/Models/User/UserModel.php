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
                    password
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

        $isModernHash = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon');
        if ($isModernHash) {
            $valid = password_verify($plain, $stored);
        } else {
            // Legacy unsalted MD5 – compare in constant time, then upgrade below.
            $valid = hash_equals(strtolower($stored), md5($plain));
        }
        if (! $valid) {
            return [];
        }
        if (! $isModernHash || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $this->db->table('orvexa_user')
                ->where('user_id', $user->user_id)
                ->update(['password' => password_hash($plain, PASSWORD_DEFAULT)]);
        }
        unset($user->password);
        return [$user];
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