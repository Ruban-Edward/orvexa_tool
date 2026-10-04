<?php


namespace App\Models\Backlog;

use CodeIgniter\Model;

use App\Models\BaseModel;

class BacklogFibonacci extends BaseModel
{
    // Table for insertion
    protected $table = "orvexa_fibonacci_settings";

    protected $primaryKey = "fibonacci_settings_id";

    protected $allowedFields = [
        "r_user_story_id",
        "fibonacci_limit"
    ];

    protected $validationRules = [
        'r_user_story_id' => 'required|integer',
        'fibonacci_limit' => 'required|integer'
    ];

    protected $validationMessages = [
        'r_user_story_id' => [
            'required' => 'The User Story ID is required.',
            'integer' => 'The User Story ID must be an integer.',
        ],
        'fibonacci_limit' => [
            'required' => 'The Fibonacci limit is required.',
            'integer' => 'The Fibonacci limit must be an integer.',
        ]
    ];

    /**
     * Retrieve the validation rules.
     * @return array
     */
    public function getFibonacciValidationRules(): array
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

    public function saveFibonacci($data)
    {
        $sql = "INSERT INTO orvexa_fibonacci_settings (
                                r_user_story_id,fibonacci_limit)
                VALUES
                    (:r_user_story_id:,:fibonacci_limit:)";

        $query = $this->db->query($sql, [
            'r_user_story_id' => $data['r_user_story_id'],
            'fibonacci_limit' => $data['fibonacci_limit']
        ]);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
