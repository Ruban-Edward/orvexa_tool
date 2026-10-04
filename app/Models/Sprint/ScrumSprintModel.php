<?php

/**
 * ScrumSprintModel.php
 *
 * @category   Model
 * @author     Jeeva ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;
use PhpParser\Node\Scalar\Int_;

class ScrumSprintModel extends Model
{

    // Setting the Table Name for insertion
    protected $table = "orvexa_sprint";

    //Declaring the Primary Key for the table
    protected $primaryKey = "sprint_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "sprint_name",
        "sprint_version",
        "r_product_id",
        "r_customer_id",
        "r_sprint_duration_id",
        "start_date",
        "end_date",
        "sprint_goal",
        "created_date",
        "r_user_id_created",
        "updated_date",
        "r_user_id_updated",
    ];

    // Setting custom validation messages for the fields
    protected $validationRules = [
        'sprint_name' => 'required|min_length[3]|max_length[50]',
        'sprint_version' => 'required|decimal',
        'r_product_id' => 'required|integer',
        'r_customer_id' => 'required|integer',
        'r_sprint_duration_id' => 'required|integer',
        'start_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'end_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'sprint_goal' => 'required|min_length[3]|max_length[1000]',
        'created_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]',
        'r_user_id_created' => 'permit_empty|integer',
        'updated_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]',
        'r_user_id_updated' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'sprint_name' => [
            'required' => 'The sprint name is required.',
            'min_length' => 'The sprint name must be at least 3 characters long.',
            'max_length' => 'The sprint name must not exceed 50 characters.',
        ],
        'sprint_version' => [
            'required' => 'The sprint version is required.',
            'decimal' => 'The sprint version must be a decimal number.',
        ],
        'r_product_id' => [
            'required' => 'The product ID is required.',
            'integer' => 'The product ID must be an integer.',
        ],
        'r_customer_id' => [
            'required' => 'The customer ID is required.',
            'integer' => 'The customer ID must be an integer.',
        ],
        'r_sprint_duration_id' => [
            'required' => 'The sprint duration ID is required.',
            'integer' => 'The sprint duration ID must be an integer.',
        ],
        'start_date' => [
            'required' => 'The start date is required.',
            'regex_match' => 'The start date must be in the format YYYY-MM-DD.',
        ],
        'end_date' => [
            'required' => 'The end date is required.',
            'regex_match' => 'The end date must be in the format YYYY-MM-DD.',
        ],
        'sprint_goal' => [
            'required' => 'The sprint goal is required.',
            'min_length' => 'The sprint goal must be at least 3 characters long.',
            'max_length' => 'The sprint goal must not exceed 1000 characters.',
        ],
        'created_date' => [
            'permit_empty' => 'The created date can be empty.',
            'regex_match' => 'The created date must be in the format YYYY-MM-DD HH:MM:SS if provided.',
        ],
        'r_user_id_created' => [
            'permit_empty' => 'The user ID who created can be empty.',
            'integer' => 'The user ID who created must be an integer if provided.',
        ],
        'updated_date' => [
            'permit_empty' => 'The updated date can be empty.',
            'regex_match' => 'The updated date must be in the format YYYY-MM-DD HH:MM:SS if provided.',
        ],
        'r_user_id_updated' => [
            'permit_empty' => 'The user ID who updated can be empty.',
            'integer' => 'The user ID who updated must be an integer if provided.',
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
     * Function which contains the query to insert the details entered in the create sprint page to the table orvexa_sprint
     * @return int
     */

    public function insertSprintDetails($data): int
    {
        $query = "INSERT INTO orvexa_sprint (
		    sprint_name,
		    sprint_version,
		    r_product_id,
		    start_date,
		    end_date,
		    r_sprint_duration_id,
		    r_customer_id,
		    sprint_goal,
		    created_date,
		    r_user_id_created,
            updated_date,
            r_user_id_updated
		) VALUES (
		    :sprint_name:,
		    :sprint_version:,
		    :r_product_id:,
		    :start_date:,
		    :end_date:,
		    :r_sprint_duration_id:,
		    :r_customer_id:,
		    :sprint_goal:,
		    :created_date:,
		    :r_user_id_created:,
            :updated_date:,
		    :r_user_id_updated:
		)";
        $result = $this->db->query($query, [
            'sprint_name' => $data['sprint_name'],
            'sprint_version' => $data['sprint_version'],
            'r_product_id' => $data['r_product_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'r_sprint_duration_id' => $data['r_sprint_duration_id'],
            'r_customer_id' => $data['r_customer_id'],
            'sprint_goal' => $data['sprint_goal'],
            'created_date' => $data['created_date'],
            'r_user_id_created' => $data['r_user_id_created'],
            'updated_date' => $data['created_date'],
            'r_user_id_updated' => $data['r_user_id_created']
        ]);

        return $this->db->insertID();
    }

    /**
     * Function which contains the query to update/edit the details entered in the create sprint page to the table orvexa_sprint
     * @return int
     */
    public function updateSprint($data)
    {
        $query = "UPDATE orvexa_sprint
                    SET
                    sprint_name = :sprint_name:,
                    sprint_version = :sprint_version:,
                    r_product_id = :r_product_id:,
                    start_date = :start_date:,
                    end_date = :end_date:,
                    r_sprint_duration_id = :r_sprint_duration_id:,
                    r_customer_id = :r_customer_id:,
                    sprint_goal = :sprint_goal:,
                    updated_date = :updated_date:,
                    r_user_id_updated = :r_user_id_updated:,
                    r_module_status_id = :r_module_status_id:
                    WHERE
                    sprint_id = :sprint_id:";
        $result = $this->db->query($query, [
            'sprint_id' => $data['sprint_id'],
            'sprint_name' => $data['sprint_name'],
            'sprint_version' => $data['sprint_version'],
            'r_product_id' => $data['r_product_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'r_sprint_duration_id' => $data['r_sprint_duration_id'],
            'r_customer_id' => $data['r_customer_id'],
            'sprint_goal' => $data['sprint_goal'],
            'updated_date' => $data['updated_date'],
            'r_user_id_updated' => $data['r_user_id_updated'],
            'r_module_status_id' => $data['r_module_status_id']
        ]);

        return $result;
    }
}
