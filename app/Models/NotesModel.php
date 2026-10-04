<?php

/**
* NotesModel.php
*
* @category   Model
* @package    App\Models
* @author     Jeril
* @created    27 August 2024
* @purpose    Manages interactions with the `orvexa_notes` table in the database.
*             Handles CRUD operations for notes, including insertion and retrieval of notes.
*             Defines validation rules and messages for note data.
*/

namespace App\Models;

use App\Models\BaseModel;

class NotesModel extends BaseModel {

    // Setting the Table Name for insertion
    protected $table = 'orvexa_notes';

    //Declaring the Primary Key for the table
    protected $primaryKey = 'notes_id';

    //Defining the fields to insert
    protected $allowedFields = [
        'notes',
        'r_user_id',
        'created_date',
        'reference_id',
        'r_notes_type_id',
    ];

    // Setting the validation rules for the model fields
    protected $validationRules = [
        'notes' => 'required|min_length[1]|max_length[1000]',
        'r_user_id' => 'required|integer',
        'reference_id' => 'required|integer',
        'r_notes_type_id'=>'required|integer',
    ];

    // Setting custom validation messages for the fields
    protected $validationMessages = [
        'notes' => [
            'min_length' => 'The notes field must be at least 3 characters long.',
            'max_length' => 'The notes field must not exceed 1000 characters.',
        ],
        'r_user_id' => [
            'required' => 'The user ID is required.',
            'integer' => 'The user ID must be an integer.',
        ],
        'reference_id' => [
            'required' => 'The reference ID is required.',
            'integer' => 'The reference ID must be an integer'
        ],
        'r_notes_type_id'=> [
            'required' => 'The notes type ID is required.',
            'integer' => 'The notes type ID must be an integer'
        ],
    ];

    /**
    * Retrieve the validation rules.
    * @return array
    */

    public function getDetailValidationRules(): array {
        return $this->validationRules;
    }

    /**
    * Retrieve the validation messages.
    * @return array
    */

    public function getValidationMessages(): array {
        return $this->validationMessages;
    }
    /**
    * It is a common function to insert notes for the scrum diary, sprint review, sprint retrospective pages to the table orvexa_notes.
    * @return array
    */

    public function insertNotes($data) {
        $query = "INSERT INTO orvexa_notes
                    (
                    notes,
                    r_user_id,
                    created_date,
                    r_notes_type_id,
                    reference_id
                    )
                    VALUES
                    (
                    :notes:,:user_id:,Now(),
                    :notes_type:,
                    :ref_id:)
                     ";
        $result = $this->query( $query, ['notes' => $data[ 'notes' ], 'user_id' => $data[ 'r_user_id' ], 'notes_type' => $data[ 'r_notes_type_id' ], 'ref_id' => $data[ 'reference_id' ] ] );
        return $result;

    }

    public function getNotes( $data ): array {
        $query = "SELECT
                orvexa_notes.notes,
                orvexa_notes.created_date as added_date,
                r_notes_type_id";
        if ( isset( $data[ 'dailyScrum' ] ) ) {
            $query .= ",orvexa_task.task_title
                        FROM
                        orvexa_notes
                        INNER JOIN orvexa_sprint_task sst ON orvexa_notes.reference_id = sst.r_task_id
                        INNER JOIN orvexa_task ON orvexa_notes.reference_id = orvexa_task.task_id
                        WHERE sst.r_sprint_id = :sprintid:
                        AND orvexa_notes.is_deleted = 'N'
                        AND sst.is_deleted = 'N'
                        ORDER BY added_date DESC";
            $result = $this->query( $query, [ 'sprintid' => $data[ 'sprintId' ] ] );
            if ( $result->getNumRows() > 0 ) {
                return $result->getResultArray();
            }
            return [];
        }
        $query .= " FROM orvexa_notes
                    WHERE r_notes_type_id
                    IN :r_notes_type_id:
                    AND reference_id
                    = :reference_id:
                    AND orvexa_notes.is_deleted = 'N'
                    ORDER BY added_date DESC";
        $result = $this->query( $query, [
            'r_notes_type_id' => $data[ 'notes_type' ],
            'reference_id' => $data[ 'reference' ]
        ] );
        if ( $result->getNumRows() > 0 ) {
            return $result->getResultArray();
        }
        return [];
    }

    public function getDailyScrumNotes( $sprintid ): array {
        $query = "SELECT sn.notes, sn.r_user_id, sn.created_date, sn.r_notes_type_id,task_title
        FROM orvexa_notes sn
        INNER JOIN orvexa_sprint_task sst ON sn.reference_id = sst.r_task_id
        INNER JOIN orvexa_task ON sn.reference_id = orvexa_task.task_id
        WHERE sst.r_sprint_id = :sprintid:
        AND sn.is_deleted = 'N'
          AND sst.is_deleted = 'N'";
        $result = $this->query( $query, [ 'sprintid' => $sprintid ] );
        if ( $result->getNumRows() > 0 ) {
            return $result->getResultArray();
        }
        return [];
    }

}
