<?php
namespace App\Models\Backlog;
use App\Models\BaseModel;

use CodeIgniter\Model;

class UserStoryConditionModel extends BaseModel
{
    // Table for insertion
    protected $table = "orvexa_user_story_condition";

    protected $primaryKey = "condition_id";

    protected $allowedFields = [
        "condition_text"
       ];

    protected $validationRules = [
        'condition_text' => 'permit_empty|min_length[3]|max_length[1000]'
        
    ];

    protected $validationMessages = [
        
        'condition_text' => [
            'min_length' => 'The condition  must be at least 3 characters in length.',
            'max_length' => 'The condition  cannot exceed 1000 characters in length.'
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


    public function insertUserStoryCondition($data)
    {
        $sql = "INSERT INTO orvexa_user_story_condition(
                                r_user_story_id,condition_text)
                VALUES
                    (:r_user_story_id:,:condition_text:)";
        
        $this->db->query($sql,[
            'r_user_story_id'=>$data['r_user_story_id'],
            'condition_text' => $data['condition_text']
         ]);

        if($sql){
            return 1;
        }
    }

    public function updateCondition($data)
    {
        $sql = "UPDATE orvexa_user_story_condition
                SET condition_text = :condition_text:
                WHERE r_user_story_id = :r_user_story_id:";
        
        $this->db->query($sql,[
            'r_user_story_id'=>$data['r_user_story_id'],
            'condition_text' => $data['condition_text']
         ]);

        if($sql){
            return 1;
        }
    }

}