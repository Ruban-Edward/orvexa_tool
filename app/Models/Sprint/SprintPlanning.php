<?php

/**
 * SprintPlanning.php
 *
 * @category   Model
 * @author     Vishva ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;

class SprintPlanning extends Model
{

    // Setting the Table Name for insertion
    protected $table = "orvexa_sprint_planning";

    //Declaring the Primary Key for the table
    protected $primaryKey = "sprint_planning_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "r_sprint_id",
        "r_sprint_activity_id",
        "start_date",
        "end_date"
    ];

    // Setting custom validation messages for the fields
    protected $validationRules = [
        'r_sprint_id' => 'required|integer',
        'r_sprint_activity_id' => 'required|integer',
        'start_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'end_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'r_notes_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'r_sprint_id' => [
            'required' => 'The sprint ID is required.',
            'integer' => 'The sprint ID must be an integer.',
        ],
        'r_sprint_activity_id' => [
            'required' => 'The sprint activity ID is required.',
            'integer' => 'The sprint activity ID must be an integer.',
        ],
        'start_date' => [
            'required' => 'The start date is required.',
            'regex_match' => 'The start date must be in the format YYYY-MM-DD.',
        ],
        'end_date' => [
            'required' => 'The end date is required.',
            'regex_match' => 'The end date must be in the format YYYY-MM-DD.',
        ],
        'r_notes_id' => [
            'required' => 'The notes ID is required.',
            'integer' => 'The notes ID must be an integer.',
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
     * Function to execute query to insert the details entered for sprint planning to the table orvexa_sprint_planning done at the time of creating the sprint.  
     * @return int|bool
     */

    public function insertSprintPlanning($settingsData)
    {
        if (!empty($settingsData)) {
            $query = "INSERT INTO orvexa_sprint_planning (r_sprint_id,
                    r_sprint_activity_id,
                    start_date, 
                    end_date,
                    r_notes_id)
                    VALUES (:r_sprint_id:,
                    :r_sprint_activity_id:, 
                    :start_date:,
                    :end_date:,
                    :r_notes_id:)";
            $result = $this->db->query($query, [
                "r_sprint_id" => $settingsData['r_sprint_id'],
                "r_sprint_activity_id" => $settingsData['r_sprint_activity_id'],
                "start_date" => $settingsData['start_date'],
                "end_date" => $settingsData['end_date'],
                "r_notes_id" => $settingsData['r_notes_id']
            ]);
        }

        return $result;
    }
}
