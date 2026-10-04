<?php


namespace App\Models\Backlog;
use App\Models\BaseModel;

use CodeIgniter\Model;
use LDAP\Result;

class UserStoryModel extends BaseModel
{
    // Table for insertion
    protected $table = "orvexa_user_story";

    protected $primaryKey = "user_story_id";

    protected $allowedFields = [
        "r_epic_id",
        "as_a_an",
        "i_want",
        "so_that",
        "given",
        "us_when",
        "us_then",
        "r_module_status_id"
       
      ];

    protected $validationRules = [
        'r_epic_id' => 'required|integer',
        'as_a_an' => 'required|min_length[3]|max_length[1000]',
        'i_want' => 'required|min_length[3]|max_length[1000]',
        'so_that' => 'required|min_length[3]|max_length[1000]',
        'given' => 'required|min_length[3]|max_length[1000]',
        'us_when' => 'required|min_length[3]|max_length[1000]',
        'us_then' => 'required|min_length[3]|max_length[1000]',
        'r_module_status_id' => 'required|integer'
    ];

    protected $validationMessages = [
        
         'r_epic_id' => [
            'required' => 'The epic value is required.',
            'integer'  => 'The epic must be an integer.'
        ],
        'as_a_an' => [
            'required'   => 'The as_a/an is required.',
            'min_length' => 'The as_a/an must be at least 3 characters in length.',
            'max_length' => 'The as_a/an cannot exceed 500 characters in length.'
        ],
        
        'i_want' => [
            'required'   => 'The i_want is required.',
            'min_length' => 'The i_want must be at least 3 characters in length.',
            'max_length' => 'The i_want cannot exceed 1000 characters in length.'
        ],

        'so_that' => [
            'required'   => 'The so_that is required.',
            'min_length' => 'The so_that must be at least 3 characters in length.',
            'max_length' => 'The so_that cannot exceed 1000 characters in length.'
        ],

        'given' => [
            'required'   => 'The given is required.',
            'min_length' => 'The given must be at least 3 characters in length.',
            'max_length' => 'The given cannot exceed 1000 characters in length.'
        ],

        'us_when' => [
            'required'   => 'The us_when is required.',
            'min_length' => 'The us_when must be at least 3 characters in length.',
            'max_length' => 'The us_when cannot exceed 1000 characters in length.'
        ],

        'us_then' => [
            'required'   => 'The us_then is required.',
            'min_length' => 'The us_then must be at least 3 characters in length.',
            'max_length' => 'The us_then cannot exceed 1000 characters in length.'
        ],
        'r_module_status_id' => [
            'required' => 'The status value is required.',
            'integer'  => 'The status must be an integer.'
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

         /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to Insert a new Userstory
     */

    public function insertUserStory($data)
    {
        $sql = "INSERT INTO 
                    orvexa_user_story(
                    r_epic_id,as_a_an,i_want,so_that,given,us_when,us_then,r_module_status_id,r_user_id_created,r_user_id_updated,created_date,updated_date)
                VALUES
                    (:epicName:,:asA/An:,:iWant:,:soThat:,:given:,:usWhen:,:usThen:,:statusId:,:userId:,:userId:,NOW(),NOW())";
        
        $result = $this->db->query($sql,[
            'epicName'=>$data['r_epic_id'],
            'asA/An' => $data['as_a_an'],
            'iWant' =>$data['i_want'],
            'soThat' => $data['so_that'],
            'given' => $data['given'],
            'usWhen' => $data['us_when'],
            'usThen' => $data['us_then'],
            'userId' => $data['r_user_id_created'],
            'statusId' => $data['r_module_status_id']
        ]);
        if($result){
            return $this->insertID();
        }
    }

     /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to Update the set of contents in userstories
     */
    public function updateUserStory($data)
    {
        $sql = "UPDATE 
                    orvexa_user_story 
                SET 
                    r_epic_id = :epicName:,
                    as_a_an = :asA/An:,
                    i_want = :iWant:,
                    so_that = :soThat:,
                    given = :given:,
                    us_when = :usWhen:,
                    us_then = :usThen:,
                    r_module_status_id = :r_module_status_id: ,
                    r_user_id_updated = :userId:,
                    updated_date = NOW()
                WHERE 
                    user_story_id = :userStoryId:";

        $this->db->query($sql,[
            'userStoryId' => $data['user_story_id'],
            'epicName'=>$data['r_epic_id'],
            'asA/An' => $data['as_a_an'],
            'iWant' =>$data['i_want'],
            'soThat' => $data['so_that'],
            'given' => $data['given'],
            'usWhen' => $data['us_when'],
            'r_module_status_id' => $data['r_module_status_id'],
            'usThen' => $data['us_then'],
            'userId' => $data['r_user_id_created']
        ]);

        if($sql){
            return 1;
        }
    }
    
     /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to Update the status of the userstories
     */
    public function updateUserStoryStatus($data){
        $sql = 'UPDATE
                    orvexa_user_story
                SET
                    r_module_status_id = :module_status:
                WHERE
                    user_story_id = :r_user_story_id:';
        $this->db->query($sql,[
            'module_status'=> 15,
            'r_user_story_id'=> $data,
        ]);
    }

    /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to return the number of userstories assigned for a backlog
     */
    public function countUserStories($input)
    {
        $sql = "SELECT 
                    COUNT(us.user_story_id) AS number_of_user_stories
                FROM 
                    orvexa_backlog_item bi
                INNER JOIN 
                    orvexa_epic e ON bi.backlog_item_id = e.r_backlog_item_id
                INNER JOIN 
                    orvexa_user_story us ON e.epic_id = us.r_epic_id
                WHERE 
                    bi.backlog_item_id = :pbl_id: AND 
                    us.is_deleted = 'N'";
        $query = $this->query($sql, [
            'pbl_id' => $input
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray()[0]['number_of_user_stories'];
        }
        return [];
    }

    /**
     * @author vigneshwari
     * @return array
     * @param int id of a backlog item
     * retrives the data from the database table and send to the controller  
     */
    public function getUserStoriesByBacklogItem($pblId): array
    {
        $sql = "SELECT 
                u.user_story_id,
                bi.backlog_item_id,
                bi.backlog_item_name,
                se.r_backlog_item_id,
                se.epic_id,
                se.epic_description,
                u.as_a_an,
                u.i_want,
                u.so_that,
                u.given,
                u.us_when,
                u.us_then,
                u.user_story_points,
                s.status_name,
                u.r_module_status_id,
                u.estimated_hours,
                uc.condition_text,
                COUNT(t.task_id) as count_task
                FROM orvexa_user_story AS u
                LEFT JOIN orvexa_epic se ON se.epic_id = u.r_epic_id AND se.is_deleted = 'N'
                LEFT JOIN orvexa_backlog_item bi ON bi.backlog_item_id = se.r_backlog_item_id AND bi.is_deleted = 'N'
                LEFT JOIN orvexa_task t ON t.r_user_story_id = u.user_story_id AND t.is_deleted = 'N'
                LEFT JOIN orvexa_user_story_condition uc ON uc.r_user_story_id = u.user_story_id AND uc.is_deleted = 'N'
                INNER JOIN orvexa_module_status ms ON ms.module_status_id = bi.r_module_status_id AND ms.is_deleted = 'N'
                INNER JOIN orvexa_status s ON s.status_id = ms.r_status_id AND s.is_deleted = 'N'
                WHERE bi.backlog_item_id =:pblId: AND u.is_deleted = 'N'
                GROUP BY u.user_story_id
            ORDER BY u.updated_date DESC";

        $query = $this->query($sql, [
            'pblId' => $pblId
        ]);

        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author vigneshwari
     * @return array
     * @param int id of a user story
     * purpose for returning the detail of a particular user story
     */
    public function getUserStoryById($id): array
    {
        $sql = "SELECT 
                    u.user_story_id,
                    u.r_epic_id AS epic_id,
                    u.r_module_status_id AS status_id,
                    s.status_name AS status_name,
                    e.epic_description,
                    u.as_a_an,
                    u.i_want,
                    u.so_that,
                    u.given,
                    u.us_when,
                    u.us_then,
                    c.condition_text 
                FROM orvexa_user_story AS u
                INNER JOIN orvexa_epic AS e ON u.r_epic_id = e.epic_id 
                INNER JOIN orvexa_backlog_item AS bt ON e.r_backlog_item_id = bt.backlog_item_id
                INNER JOIN orvexa_module_status AS ms ON u.r_module_status_id = ms.module_status_id
                INNER JOIN orvexa_status AS s ON s.status_id = ms.r_status_id
                LEFT JOIN orvexa_user_story_condition AS c ON c.r_user_story_id = u.user_story_id
                WHERE u.is_deleted = 'N' AND user_story_id = :id:";
        $result = $this->query($sql, ['id' => $id]);
        if ($result->getNumRows() > 0) {
            return $result->getResultArray();
        }
        return [];
    }

    /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to return the max ID comparing all the userstories
     */
    public function getCountUserStory(): array
    {
        $sql = "SELECT 
                    MAX(user_story_id) as usCount
                FROM 
                    orvexa_user_story";

        $query = $this->query($sql);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /*
     * @author Abinandhan
     * @param array which contains the filter information
     * @return array
     * Purpose: This function is used to fetch all the userstory and display it with limit,offset and required filters 
     */

     public function getuserstories($filter): array
     {
         $sql = "SELECT 
                 u.user_story_id,
                 bi.backlog_item_id,
                 bi.backlog_item_name,
                 se.r_backlog_item_id,
                 se.epic_id,
                 se.epic_description,
                 u.as_a_an,
                 u.i_want,
                 u.so_that,
                 u.given,
                 u.us_when,
                 u.us_then,
                 u.user_story_points,
                 s.status_name,
                 u.r_module_status_id,
                 u.estimated_hours,
                 uc.condition_text,
                 COUNT(DISTINCT t.task_id) as count_task
                 FROM orvexa_user_story AS u
                 LEFT JOIN orvexa_epic se ON se.epic_id = u.r_epic_id AND se.is_deleted = 'N'
                 LEFT JOIN orvexa_backlog_item bi ON bi.backlog_item_id = se.r_backlog_item_id AND bi.is_deleted = 'N'
                 LEFT JOIN orvexa_task t ON t.r_user_story_id = u.user_story_id AND t.is_deleted = 'N'
                 LEFT JOIN orvexa_user_story_condition uc ON uc.r_user_story_id = u.user_story_id AND uc.is_deleted = 'N'
                 INNER JOIN orvexa_module_status ms ON ms.module_status_id = bi.r_module_status_id AND ms.is_deleted = 'N'
                 INNER JOIN orvexa_status s ON s.status_id = ms.r_status_id AND s.is_deleted = 'N'
                 WHERE bi.backlog_item_id =:pblId: AND u.is_deleted = 'N' ";
         $params = [];
 
         if (!empty($filter['status'])) {
             $data = is_array($filter['status']) ? $filter['status'] : explode(',', $filter['status']);
             $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
             $sql .= " AND u.r_module_status_id in($data)";
         }
 
         if (!empty($filter['epic'])) {
             $data = is_array($filter['epic']) ? $filter['epic'] : explode(',', $filter['epic']);
             $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
             $sql .= " AND se.epic_id in($data)";
         }
 
         if (!empty($filter['search'])) {
             $sql .= " AND (u.as_a_an LIKE :search: OR u.i_want LIKE :search: OR u.so_that LIKE :search:)";
             $params['search'] = '%' . $filter['search'] . '%'; // Adding wildcards to the value in the array
         }
 
         $sql.=" GROUP BY u.user_story_id ";
         
         $params['pblId'] = $filter['id'];
 
         if (!empty($filter['limit'])) {
             $sql .= " limit :limit:";
             $params['limit'] = $filter['limit'];
         } else {
             $sql .= " limit :limit:";
             $params['limit'] = 10;
 
         }
         if (!empty($filter['offset'])) {
             $sql .= " offset :offset:";
             $params['offset'] = $filter['offset'];
         } else {
             $sql .= " offset :offset:";
             $params['offset'] = 0;
 
         }
         
         $query = $this->db->query($sql, $params);
 
         if ($query->getNumRows() > 0) {
             return $query->getResultArray();
         }
         return [];
     }

    /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to return the count of user stories
     */

     public function checkUserStory($backlogItemId, $userStoryId): bool
    {
        $sql = "SELECT 
                    us.user_story_id AS count
                FROM 
                    orvexa_user_story us
                INNER JOIN 
                    orvexa_epic e ON e.epic_id = us.r_epic_id
                WHERE 
                    e.r_backlog_item_id = ? AND 
                    us.user_story_id = ? AND 
                    us.is_deleted = 'N'";
        $query = $this->db->query($sql, [$backlogItemId, $userStoryId]);

        if ($query->getNumRows() > 0) {
            $result = $query->getRow();
            return $result->count > 0;
        }
        return 0;

    }
    /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to change the status for brainstroming
     */

    public function changeStatus($before, $after, $id): bool
    {
        $sql = "UPDATE 
                    orvexa_user_story us
                INNER JOIN 
                    orvexa_epic e ON e.epic_id = us.r_epic_id
                SET 
                    us.r_module_status_id = :after:
                WHERE
                    e.r_backlog_item_id = :id: AND us.r_module_status_id=:before:";

        $this->query($sql, [
            'id' => $id,
            'before' => $before,
            'after' => $after
        ]);

        if ($sql) {
            return 1;
        }
        return 0;
    }

    /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is used to change the status
     */

    public function userStoryStatus($statusId, $id): bool
    {
        $sql = "UPDATE 
                    orvexa_user_story
                SET 
                    r_module_status_id = :status_id:
                WHERE
                    user_story_id = :id:";

        $result = $this->query($sql, [
            'id' => $id,
            'status_id' => $statusId
        ]);

        return $result;
    }

     /**
     * @author vigneshwari
     * @return array
     * Purpose: This function is to return the userstory as per epic
     */

    public function getuserstoryByepic($id)
    {
        $sql = "SELECT 
                    user_story_id,
                    CONCAT(as_a_an ,' ', i_want) AS story_name
                FROM 
                    orvexa_epic AS e
                INNER JOIN 
                    orvexa_user_story AS us 
                ON 
                    us.r_epic_id = e.epic_id
                WHERE 
                    e.epic_id = :id: AND 
                    us.r_module_status_id = 14";
        $query = $this->query($sql, [
            'id' => $id
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author 
     * @return array
     * Purpose: This function is to return the count of userstories
     */
    public function getTotalUserStory($id)
    {
        $sql = "SELECT 
                    count(user_story_id) AS count 
                FROM 
                    orvexa_user_story sus 
                JOIN 
                    orvexa_epic se ON sus.r_epic_id= se.epic_id 
                JOIN 
                    orvexa_backlog_item sbi ON sbi.backlog_item_id= se.r_backlog_item_id 
                WHERE 
                    r_backlog_item_id=:id:";
                    
        $query = $this->query($sql, ['id' => $id]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray()[0]['count'];
        }
        return [];
    }
    
}

?>