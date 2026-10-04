<?php

/**
 * SprintTask.php
 *
 * @category   Model
 * @author     Vishwa ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;

class SprintTask extends Model
{
    // Setting the Table Name for insertion
    protected $table = "orvexa_sprint_task";

    //Declaring the Primary Key for the table
    protected $primaryKey = "sprint_task_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "r_sprint_id",
        "r_task_id",
    ];

    // Setting custom validation messages for the fields
    protected $validationRules = [
        'r_sprint_id' => 'required|integer',
        'r_task_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'r_sprint_id' => [
            'required' => 'The sprint ID is required.',
            'integer' => 'The sprint ID must be an integer.',
        ],
        'r_task_id' => [
            'required' => 'The scrum notes ID is required.',
            'integer' => 'The scrum notes ID must be an integer.',
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
     * Function to execute query to insert the details entered for sprint tasks to the table orvexa_sprint_tasks assigned at the time of creating the sprint.
     * @return int|bool
     */

    public function insertSelectedTasks($taskData)
    {
        if (!empty($taskData)) {
            $query = "INSERT INTO orvexa_sprint_task (r_sprint_id,
                    r_task_id)
                    VALUES (:r_sprint_id:,
                    :r_task_id:) ";
            $result = $this->db->query($query, [
                "r_sprint_id" => $taskData['r_sprint_id'],
                "r_task_id" => $taskData['r_task_id']
            ]);
            $query = "UPDATE orvexa_task
                    SET task_status = ':task_status_id:'
                    WHERE task_id = :r_task_id:
                    AND task_status IN (:task_status_id2:, :task_status_id3:)";
            $result = $this->db->query($query, [
                "task_status_id" => 8,
                "r_task_id" => $taskData['r_task_id'],
                "task_status_id2" => 1,
                "task_status_id3" => 16
            ]);
            return $result;
        }
        return false;
    }
}