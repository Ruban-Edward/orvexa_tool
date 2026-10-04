<?php

/**
 * SprintRetrospective.php
 *
 * @category   Model
 * @author     Jeril ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;

use CodeIgniter\Model;

class SprintRetrospective extends Model
{

    // Setting the Table Name for insertion
    protected $table = "orvexa_sprint_retrospective";

    //Declaring the Primary Key for the table
    protected $primaryKey = "sprint_retrospective_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "r_sprint_id",
        "challenge",
        "r_notes_id",
        "added_date",
        "r_user_id_created",
    ];

    // Setting custom validation messages for the fields
    protected $validationRules = [
        'r_sprint_id' => 'required|integer',
        'challenge'=> 'required|in_list[pros,cons,lns]',
        'r_notes_id' => 'required|integer',
        'added_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'r_user_id_created' => 'required|integer'
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
     * Function which contains the query to insert the details entered in the create sprint retrospetive to the table orvexa_sprint_retrospective by mapping with the data from the orvexa_notes table.
     * @return array
     */

    public function insertSprintRetrospective($data)
     {
          $query = "INSERT INTO orvexa_sprint_retrospective(
				r_sprint_id,
				challenge,
				r_notes_id,
				added_date,
				r_user_id_created
				) VALUES (
					:r_sprint_id:,
					:challenge:,
					:r_notes_id:,
					:added_date:,
					:r_user_id_created:
				)";
          $result = $this->query($query, [
               'r_sprint_id' => $data['r_sprint_id'],
               'challenge' => $data['challenge'],
               'r_notes_id' => $data['r_notes_id'],
               'added_date' => $data['added_date'],
               'r_user_id_created' => $data['r_user_id_created']
          ]);

          return $result;
     }
}
