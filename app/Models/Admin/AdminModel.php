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
     * Method to retrieve the users from the user table
     * @return array
     */
    public function getUsers(): array
    {
        // SQL query to get user details along with their roles
        $sql = "SELECT 
                    user.user_id, 
                    user.first_name, 
                    user.last_name, 
                    user.email_id,
                    role.role_id, 
                    role.role_name 
                FROM 
                    orvexa_user AS user 
                    INNER JOIN orvexa_role AS role 
                    ON role.role_id = user.r_role_id
                WHERE 
                    user.is_deleted = :is_deleted:
                LIMIT 
                    50";
        // Execute the query with parameter binding
        $result = $this->db->query($sql, [
            'is_deleted' => 'N'
        ]);

        // Return the result as an associative array
        return $result->getResultArray();
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
                    AND LOWER(user.first_name) LIKE '%$searchQuery%' 
                    OR LOWER(user.last_name) LIKE '%$searchQuery%'";

        // Execute the query with parameter binding
        $query = $this->db->query($sql, [
            "is_deleted" => "N"
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
