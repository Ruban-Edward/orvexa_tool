<?php
/**
 * RoleModel.php
 * @author Ruban Edward
 * 
 * @performs action in the orvexa_role table
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'orvexa_role';
    protected $primaryKey = 'role_id';
    protected $allowedFields = [
        'role_name'
    ];

    protected $validationRules = [
        'role_name' => [
            'label' => 'Role Name',
            'rules' => 'required',
        ],
    ];

    protected $validationMessages = [
        'role_name' => [
            'required' => 'The Role Name is required.',
        ],
    ];

    public function getDetailValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * Method to retrieve the roles from the t_role table
     * @return array
     */
    public function getRoles(): array
    {
        // SQL query to get role details
        $sql = "SELECT
                    role_id,	
                    role_name
                FROM
                    orvexa_role
                WHERE
                    is_deleted = :is_deleted:";

        // Execute the query with parameter binding
        $result = $this->db->query($sql, [
            'is_deleted' => 'N'
        ]);

        // Return the result as an associative array
        return $result->getResultArray();
    }

    /**
     * Method to insert the new role in the table
     * @param array
     * @re
     */
    public function insertRole($name)
    {
        $sql = "INSERT INTO orvexa_role
                    (role_name)
                VALUES
                    (:role_name:)";

        $result = $this->db->query($sql, [
            "role_name" => $name['role_name']
        ]);

        return $result;
    }

    /**
     * Method to delete the existing role in the tool
     * @param int
     * @re
     */
    public function deleteRole($roleId)
    {
        $sql = "UPDATE 
                    orvexa_role
                SET
                    is_deleted = :is_deleted:
                WHERE 
                    role_id = :role_id:";

        $result = $this->db->query($sql, [
            "is_deleted" => "Y",
            "role_id" => $roleId
        ]);

        return $result;
    }
}