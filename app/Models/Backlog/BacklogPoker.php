<?php


namespace App\Models\Backlog;

use CodeIgniter\Model;

use App\Models\BaseModel;

class BacklogPoker extends BaseModel
{
    // Table for insertion
    protected $table = "orvexa_poker_planning";

    protected $primaryKey = "poker_planning_id";

    protected $allowedFields = [
        "r_user_story_id",
        "r_user_id",
        "card_points",
        "added_date"
    ];

    protected $validationRules = [
        'r_user_story_id' => 'required|integer',
        'r_user_id' => 'required|integer',
        'card_points' => 'required|integer',
        'reason' => 'required|text',
        'added_date' => 'required|datetime'
    ];

    protected $validationMessages = [
        'r_user_story_id' => [
            'required' => 'The User Story ID is required.',
            'integer' => 'The User Story ID must be an integer.',
        ],
        'r_user_id' => [
            'required' => 'The User ID is required.',
            'integer' => 'The User ID must be an integer.',
        ],
        'card_points' => [
            'required' => 'The Card points is required.',
            'integer' => 'The Card points must be an integer.',
        ],
        'reason' => [
            'required' => 'The Reason is required.',
            'text' => 'The Reason must be an text.',
        ],
        'added_date' => [
            'required' => 'The Added date is required.',
            'datetime' => 'The Added date must be an datetime.',
        ]
    ];

    /**
     * Retrieve the validation rules.
     * @return array
     */
    public function getPokerValidationRules(): array
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

    public function savePoker($data)
    {
        $sql = "INSERT INTO orvexa_poker_planning (
                                r_user_story_id,r_user_id,card_points,reason, added_date)
                VALUES
                    (:r_user_story_id:,:r_user_id:,:card_points:,:reason:,:added_date:)";

        $query = $this->db->query($sql, [
            'r_user_story_id' => $data['r_user_story_id'],
            'r_user_id' => $data['r_user_id'],
            'card_points' => $data['card_points'],
            'reason' => $data['reason'],
            'added_date' => $data['added_date'],
        ]);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function getPokerReveal($userStoryId)
    {
        $query = "SELECT 
                    reveal
                  FROM 
                    orvexa_poker_planning
                  WHERE 
                    r_user_story_id = :r_user_story_id:
                  GROUP BY r_user_story_id";
        $result = $this->query($query, [
            "r_user_story_id" => $userStoryId
        ]);
        if ($result->getNumRows() > 0) {
            return $result->getResultArray();
        }
        return [];
    }

    public function getPokerPlan($userStory, $user = null)
    {
        $query = "SELECT CONCAT(uc.first_name,' ',uc.last_name) AS name,
                r_user_story_id,
                card_points,
                reason,
                added_date,
                reveal
                FROM orvexa_poker_planning
                INNER JOIN orvexa_user uc ON
                orvexa_poker_planning.r_user_id = uc.external_employee_id
                WHERE orvexa_poker_planning.r_user_story_id = :r_user_story_id:
                AND orvexa_poker_planning.is_deleted = :is_deleted:";
        if ($user != null) {
            $query .= "AND orvexa_poker_planning.r_user_id = :r_user_id:";
            $result = $this->query($query, [
                "r_user_story_id" => $userStory,
                "is_deleted" => 'N',
                "r_user_id" => $user
            ]);
            if ($result->getNumRows() > 0) {
                return $result->getResultArray();
            }
            return [];
        }
        $result = $this->query($query, [
            "r_user_story_id" => $userStory,
            "is_deleted" => 'N',
        ]);
        if ($result->getNumRows() > 0) {
            return $result->getResultArray();
        }
        return [];
    }

    public function updatePokerRevealStatus($userStoryId)
    {
        $sql = "UPDATE orvexa_poker_planning
                SET reveal = :status:
                WHERE r_user_story_id = :r_user_story_id:";
        return $this->query($sql, [
            "status" => 'Y',
            "r_user_story_id" => $userStoryId
        ]);
    }

}
