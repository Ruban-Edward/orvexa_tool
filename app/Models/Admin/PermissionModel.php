<?php
/**
 * PermissionModel.php
 * @author Ruban Edward
 * 
 * @purpose To get the role based Permission and Strict the user
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'orvexa_permission';
    protected $primaryKey = 'permission_id';
    protected $allowedFields = [
        'permission_name',
        'r_module_id',
        'routes_url'
    ];

    protected $validationRules = [
        'permission_name' => [
            'label' => 'Permission Name',
            'rules' => 'required|min_length[3]|max_length[50]',
        ],
        'r_module_id' => [
            'label' => 'Module ID',
            'rules' => 'required|integer',
        ],
        'routes_url' => [
            'label' => 'Routes URL',
            'rules' => 'required|min_length[3]|max_length[100]',
        ],
    ];

    protected $validationMessages = [
        'permission_name' => [
            'required' => 'The Permission Name is required.',
            'max_length' => 'The Permission Name cannot exceed 50 characters.',
        ],
        'r_module_id' => [
            'integer' => 'The Module ID must be a valid integer.',
        ],
        'routes_url' => [
            'max_length' => 'The Routes URL cannot exceed 100 characters.',
        ],
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
     * Add a new permission to the database
     * @param array $data
     * @return bool
     */
    public function addNewPermission($data)
    {
        // SQL query to insert a new permission
        $sql = "INSERT INTO
                    orvexa_permission
                    (permission_name,
                    r_module_id,
                    routes_url)
                    
                VALUES
                    (:permission_name:,
                    :r_module_id:,
                    :routes_url:)";

        // Execute the query with parameter binding
        $query = $this->db->query($sql, [
            'permission_name' => $data["permission_name"],
            'r_module_id' => $data["r_module_id"],
            'routes_url' => $data["routes_url"],
        ]);

        // Return the result of the query execution
        return $query;
    }

    /**
     * gets the permission id and permission name to delete the permission
     * @return array
     */
    public function getPermissions(): array
    {
        $sql = "SELECT 
                    permission_id, 
                    permission_name 
                FROM 
                    orvexa_permission
                WHERE 
                    is_deleted = :is_deleted:
                ORDER BY 
                    permission_name ASC";

        $result = $this->db->query($sql, [
            "is_deleted" => "N"
        ]);
        // Return the result as an associative array
        return $result->getResultArray();
    }

    /**
     * Delete the specific permission
     * @param int $id
     * @return bool
     */
    public function deletePermission($id): bool
    {
        $sql = "UPDATE 
                    orvexa_permission 
                SET 
                    is_deleted = :is_deleted: 
                WHERE 
                    permission_id = :permission_id:
                ";
        $query = $this->db->query($sql, [
            "is_deleted" => "Y",
            "permission_id" => $id
        ]);

        return $query;
    }

    /**
     * get ths permission details of specific id
     * @param int
     * @return array
     */
    public function getPermissionDetailsById($permissionId): array
    {
        $sql = "SELECT 
                    permission_name, 
                    r_module_id, 
                    routes_url 
                FROM 
                    orvexa_permission 
                WHERE 
                    permission_id = :permission_id:
                    AND is_deleted = :is_deleted:";

        $query = $this->db->query($sql, [
            "is_deleted" => "N",
            "permission_id" => $permissionId
        ]);
        if ($query && $query->getNumRows() > 0) {
            return $query->getResultArray()[0];
        } else {
            return [];
        }
    }

    /**
     * updates the details for particular permission id
     * @param array
     * @return bool
     */
    public function updatePermissionById($permissionId): bool
    {
        $sql = "UPDATE 
                    orvexa_permission 
                SET 
                    permission_name = :permission_name:, 
                    r_module_id = :r_module_id:, 
                    routes_url = :routes_url: 
                WHERE 
                    permission_id = :permission_id:";
        $query = $this->db->query($sql, [
            "permission_name" => str_replace(" ", "_", strtoupper(trim($permissionId['permissionNameModal']))),
            "r_module_id" => $permissionId['moduleModel'],
            "routes_url" => $permissionId['routesURLModel'],
            "permission_id" => $permissionId['editPermissionNameModel']
        ]);

        return $query;
    }

}
