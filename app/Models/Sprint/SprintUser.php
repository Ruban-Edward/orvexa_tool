<?php

/**
 * SprintUser.php
 *
 * @category   Model
 * @author     Vishva ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;

class SprintUser extends Model
{
    // Setting the Table Name for insertion
    protected $table = "orvexa_sprint_user";

    //Declaring the Primary Key for the table
    protected $primaryKey = "sprint_users_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "r_sprint_id",
        "r_user_id",
    ];

    protected $validationRules = [
        'r_sprint_id' => 'required|integer',
        'r_user_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'r_sprint_id' => [
            'required' => 'The sprint ID is required.',
            'integer' => 'The sprint ID must be an integer.',
        ],
        'r_user_id' => [
            'required' => 'The task ID is required.',
            'integer' => 'The task ID must be an integer.',
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
     * Function to execute query to insert the details entered for sprint members to the table orvexa_sprint_user selected at the time of creating the sprint.
     * @return int|bool
     */

    public function insertSelectedMembers($data)
    {
        if (!empty($data)) {
            $query = "INSERT INTO orvexa_sprint_user (r_sprint_id, r_user_id)
                      		VALUES (:r_sprint_id:, :r_user_id:)";
            $result = $this->db->query($query, [
                "r_sprint_id" => $data['r_sprint_id'],
                "r_user_id" => $data['r_user_id']
            ]);
            return $result;
        } else {
            return false;
        }
    }
}