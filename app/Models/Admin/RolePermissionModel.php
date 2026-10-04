<?php

/**
 * AdminModel.php
 *
 * @category   Model
 * @author     Ruban Edward
 * @created    13 July 2024
 * @purpose    To insert the permission based on users       
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    // Table name for insertion
    protected $table = "orvexa_role_permission";

    //setting the primary key to insert 
    protected $primaryKey = "role_permission_id";

    // Defining the fields that are allowed to insert
    protected $allowedFields = [
        "r_role_id",
        "r_permission_id"
    ];

    // Setting the validation rules for the model fields
    protected $validationRules = [
        'r_role_id' => 'required|integer',
        'r_permission_id' => 'required|integer'
    ];

    // Setting custom validation messages for the fields
    protected $validationMessages = [
        'r_role_id' => [
            'required' => 'The role ID is required.',
            'integer' => 'The role ID must be an integer.'
        ],
        'r_permission_id' => [
            'required' => 'The permission ID is required.',
            'integer' => 'The permission ID must be an integer.'
        ]
    ];

    /**
     * Retrieve the validation rules.
     * @return array
     */
    public function getDetailValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * Retrieve the validation messages.
     * @return array
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * To Insert the role permission for the particular user
     * @param array $data
     * @return bool
     */
    public function insertRolePermission($data): bool
    {
        $columns = ['r_role_id', 'r_permission_id'];

        // Using the insertBatch method to insert multiple data
        $result = $this->db->table('orvexa_role_permission')->insertBatch($data);

        return $result;
    }

    /**
     * To delete the permission for a particular role
     * @param array $data
     * @return bool
     */
    public function deleteRolePermission($data)
    {
        $roleId = $data[0]['r_role_id'];
        $permissionIds = array_column($data, 'r_permission_id');

        $result = $this->db->table('orvexa_role_permission')
            ->where('r_role_id', $roleId)
            ->whereIn('r_permission_id', $permissionIds)
            ->delete();
        return $result;
    }

    /**
     * Get permissions by role ID
     * @param int $roleId
     * @return array
     */
    public function getPermissionsByRole($roleId): array
    {
        // SQL query to get permissions associated with a role
        $sql = "SELECT 
                    r_permission_id
                FROM 
                    orvexa_role_permission
                WHERE 
                    r_role_id = :role_id: AND
                    is_deleted = :is_deleted:";

        // Execute the query with parameter binding
        $result = $this->db->query($sql, [
            'role_id' => $roleId,
            'is_deleted' => 'N'
        ]);

        // Return the result as an array of permission IDs
        return array_column($result->getResultArray(), 'r_permission_id');
    }
}
