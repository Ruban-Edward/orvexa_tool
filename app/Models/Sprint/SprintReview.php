<?php

/**
 * SprintReview.php
 *
 * @category   Model
 * @author     Vishva ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;

class SprintReview extends Model
{

    // Setting the Table Name for insertion
    protected $table = "orvexa_sprint_review";

    //Declaring the Primary Key for the table
    protected $primaryKey = "sprint_review_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "r_sprint_id",
        "r_orvexa_notes_id",
        "code_review_status",
        "r_orvexa_notes_id_cr",
        "challenges_status",
        "r_orvexa_notes_id_challenges",
        "sprint_goal_status",
        "r_orvexa_notes_id_sg",
        "added_date",
        "r_user_id_created"
    ];

    // Setting custom validation messages for the fields
    protected $validationRules = [
        'r_sprint_id' => 'required|integer',
        'r_orvexa_notes_id' => 'required|integer',
        'code_review_status' => 'required|in_list[Y,N]',
        'r_orvexa_notes_id_cr' => 'required|integer',
        'challenges_status' => 'required|in_list[Y,N]',
        'r_orvexa_notes_id_challenges' => 'required|integer',
        'sprint_goal_status' => 'required|in_list[Y,N]',
        'r_orvexa_notes_id_sg' => 'required|integer',
        'added_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'r_user_id_created' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'r_sprint_id' => [
            'required' => 'The sprint ID is required.',
            'integer' => 'The sprint ID must be an integer.',
        ],
        'r_orvexa_notes_id' => [
            'required' => 'The scrum notes ID is required.',
            'integer' => 'The scrum notes ID must be an integer.',
        ],
        'code_review_status' => [
            'required' => 'The code review status is required.',
            'in_list' => 'The code review status must be either "Y" or "N".',
        ],
        'r_orvexa_notes_id_cr' => [
            'required' => 'The scrum notes ID for code review is required.',
            'integer' => 'The scrum notes ID for code review must be an integer.',
        ],
        'challenges_status' => [
            'required' => 'The challenges status is required.',
            'in_list' => 'The challenges status must be either "Y" or "N".',
        ],
        'r_orvexa_notes_id_challenges' => [
            'required' => 'The scrum notes ID for challenges is required.',
            'integer' => 'The scrum notes ID for challenges must be an integer.',
        ],
        'sprint_goal_status' => [
            'required' => 'The sprint goal status is required.',
            'in_list' => 'The sprint goal status must be either "Y" or "N".',
        ],
        'r_orvexa_notes_id_sg' => [
            'required' => 'The scrum notes ID for sprint goal is required.',
            'integer' => 'The scrum notes ID for sprint goal must be an integer.',
        ],
        'added_date' => [
            'permit_empty' => 'The added date can be empty.',
            'regex_match' => 'The added date must be in the format YYYY-MM-DD if provided.',
        ],
        'r_user_id_created' => [
            'permit_empty' => 'The user ID who created can be empty.',
            'integer' => 'The user ID who created must be an integer if provided.',
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
     * Function which contains the query to insert the details entered in the create sprint review page to the table orvexa_sprint_review by mapping with the data entered from the orvexa_notes table.
     * @return array
     */

    public function insertSprintReview($data)
    {
        $query = "INSERT INTO orvexa_sprint_review (
				r_sprint_id,
				r_orvexa_notes_id,
				code_review_status,
				r_orvexa_notes_id_cr,
				challenges_status,
				r_orvexa_notes_id_challenges,
				sprint_goal_status,
				r_orvexa_notes_id_sg,
				added_date,
				r_user_id_created
				) VALUES (
				:r_sprint_id:,
				:r_orvexa_notes_id:,
				:code_review_status:,
				:r_orvexa_notes_id_cr:,
				:challenges_status:,
				:r_orvexa_notes_id_challenges:,
				:sprint_goal_status:,
				:r_orvexa_notes_id_sg:,
				:added_date:,
				:r_user_id_created:
				)";
        $result = $this->query($query, [
            'r_sprint_id' => $data['r_sprint_id'],
            'r_orvexa_notes_id' => $data['r_orvexa_notes_id'],
            'code_review_status' => $data['code_review_status'],
            'r_orvexa_notes_id_cr' => $data['r_orvexa_notes_id_cr'],
            'challenges_status' => $data['challenges_status'],
            'r_orvexa_notes_id_challenges' => $data['r_orvexa_notes_id_challenges'],
            'sprint_goal_status' => $data['sprint_goal_status'],
            'r_orvexa_notes_id_sg' => $data['r_orvexa_notes_id_sg'],
            'added_date' => $data['added_date'],
            'r_user_id_created' => $data['r_user_id_created']
        ]);

        return $result;
    }
}
