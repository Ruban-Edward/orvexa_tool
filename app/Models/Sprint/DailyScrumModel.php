<?php

/**
 * DailyScrumModel.php
 *
 * @category   Model
 * @author     Jeril ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;

class DailyScrumModel extends Model
{

    // Setting the Table Name for insertion
    protected $table = "orvexa_daily_scrum";

    //Declaring the Primary Key for the table
    protected $primaryKey = "daily_orvexa_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "r_sprint_id",
        "challenges",
        "r_notes_id",
        "added_date",
        "r_user_id_created",
        "r_task_id",
    ];

    // Setting the validation rules for the model fields
    protected $validationRules = [
        'r_sprint_id' => 'required|integer',
        'challenges' => 'required|in_list[N,Y]',
        'r_notes_id' => 'required|integer',
        'added_date' => 'regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'r_user_id_created' => 'required|integer',
        'r_task_id' => 'required|integer',
    ];

    // Setting custom validation messages for the fields
    protected $validationMessages = [
        'r_sprint_id' => [
            'required' => 'The sprint ID is required.',
            'integer' => 'The sprint ID must be an integer.',
        ],
        'challenges' => [
            'required' => 'The challenges field is required.',
            'min_length' => 'The challenges field must be at least 3 characters long.',
            'max_length' => 'The challenges field must not exceed 1000 characters.',
        ],
        'r_notes_id' => [
            'required' => 'The notes ID is required.',
            'integer' => 'The notes ID must be an integer.',
        ],
        'added_date' => [
            'regex_match' => 'The added date must be in the format YYYY-MM-DD',
        ],
        'r_user_id_created' => [
            'required' => 'The user ID is required.',
            'integer' => 'The user ID must be an integer.',
        ],
        'r_task_id' => [
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
     * Function which executes the query to insert the details entered by the user in the daily scrum page to the table orvexa_daily_scrum by mapping with the data from the orvexa_notes table.
     * @return array
     */
    public function insertScrumDiary($data)
    {
        $query = "INSERT INTO orvexa_daily_scrum (
				r_sprint_id,
				challenges,
				added_date,
				r_notes_id,
				r_user_id_created,
                r_task_id
				) VALUES (
					:r_sprint_id:,
					:challenges:,
					:added_date:,
					:r_notes_id:,
					:r_user_id_created:,
                    :r_task_id:
				)";
        $result = $this->query($query, [
            'r_sprint_id' => $data['r_sprint_id'],
            'challenges' => $data['challenges'],
            'added_date' => $data['added_date'],
            'r_notes_id' => $data['r_notes_id'],
            'r_user_id_created' => $data['r_user_id_created'],
            'r_task_id' => $data['r_task_id']
        ]);
        return $result;
    }
}
