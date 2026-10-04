<?php

/**
 * EmailJobModel.php
 * 
 * @author   rubanedward.r <email>
 * @category Models
 * 
 * This model handles the interaction with the 'orvexa_email_jobs' database table,
 * including validation, insertion, and other CRUD operations related to email jobs.
 */

namespace App\Models;

use CodeIgniter\Model;

class EmailJobModel extends Model
{
    // The name of the table associated with this model
    protected $table = 'orvexa_email_jobs';

    // The primary key field of the table
    protected $primaryKey = "id";

    // Fields that are allowed to be inserted or updated in the table
    protected $allowedFields = [
        'email_id', 
        'file_name', 
        'contents', 
        'status'
    ];

    // Validation rules for the fields in the table
    protected $validationRules = [
        'email_id'   => 'required|valid_json',
        'file_name'  => 'required|string|max_length[255]',
        'contents'   => 'required|valid_json',
        'status'     => 'permit_empty|in_list[Y,N]',
    ];
    
    // Custom validation messages for each field
    protected $validationMessages = [
        'email_id' => [
            'required'   => 'The Email ID is required.',
            'valid_json' => 'The Email ID must be a valid JSON format.',
        ],
        'file_name' => [
            'required'   => 'The file name is required.',
            'string'     => 'The file name must be a valid string.',
            'max_length' => 'The file name cannot exceed 255 characters.',
        ],
        'contents' => [
            'required'   => 'The contents field is required.',
            'valid_json' => 'The contents must be a valid JSON format.',
        ],
        'status' => [
            'permit_empty' => 'The status is optional.',
            'in_list'      => 'The status must be either "Y" or "N".',
        ],
    ];

    /**
     * Inserts a new email job into the database.
     * 
     * @param array $emailJobData Data for the email job to be inserted.
     */
    public function insertMailJobs($emailJobData){
        // SQL query to insert a new email job
        $sql = 'INSERT INTO
                    orvexa_email_jobs
                    (
                        email_id, file_name,
                        contents, status
                    )
                VALUE
                    (
                        :email_id:, :file_name:,
                        :contents:, :status:
                    )';

        // Execute the query with the provided data
        $this->db->query($sql, [
            'email_id' => $emailJobData['email_id'],
            'file_name' => $emailJobData['file_name'],
            'contents' => $emailJobData['contents'],
            'status' => $emailJobData['status']
        ]);
    }
}
