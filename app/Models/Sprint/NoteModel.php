<?php

/**
 * NoteModel.php
 *
 * @category   Model
 * @author     Vishva ,Ruban Edward
 * @created    16 July 2024
 * @purpose          
 */

namespace App\Models\Sprint;
use CodeIgniter\Model;

class NoteModel extends Model
{

    // Setting the Table Name for insertion
    protected $table = "orvexa_notes";

    //Declaring the Primary Key for the table
    protected $primaryKey = "notes_id";

    //Defining the fields to insert
    protected $allowedFields = [
        "notes",
        "r_user_id",
        "created_date",
    ];

    // Setting the validation rules for the model fields
    protected $validationRules = [
        'notes' => 'required|min_length[1]|max_length[1000]',
        'r_user_id' => 'required|integer',
        'created_date' => 'regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]',
    ];

    // Setting custom validation messages for the fields
    protected $validationMessages = [
        'notes' => [
            'required' => 'The notes field must be filled',
            'min_length' => 'The notes field must be at least 1 characters long.',
            'max_length' => 'The notes field must not exceed 1000 characters.',
        ],
        'r_user_id' => [
            'required' => 'The user ID is required.',
            'integer' => 'The user ID must be an integer.',
        ],
        'created_date' => [
            'regex_match' => 'The created date must be in the format YYYY-MM-DD HH:MM:SS.',
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
     * It is a common function to insert notes for the scrum diary, sprint review, sprint retrospective pages to the table orvexa_notes.
     * @return int
     */

    public function insertNotes($data)
     {
          $query = "INSERT INTO orvexa_notes(notes,
				r_user_id,
				created_date)
              		VALUES (:notes:,
				:r_user_id:,
				:created_date:)";
          $result = $this->query($query, [
               'notes' => $data['notes'],
               'r_user_id' => $data['r_user_id'],
               'created_date' => $data['created_date']
          ]);
          return $this->db->insertID();
     }
}
