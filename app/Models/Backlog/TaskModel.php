<?php

namespace App\Models\Backlog;
use App\Models\BaseModel;



class TaskModel extends BaseModel
{
    // Table for insertion
    protected $table = "orvexa_task";

    protected $primaryKey = "task_id";

    protected $allowedFields = [
        "r_user_story_id",
        "task_title",
        "task_description",
        "external_reference_task_id",
        "assignee_id",
        "priority",
        "task_status",
        "completed_percentage",
        "tracker_id",
        "start_date",
        "end_date",
        "estimated_hours",
        "r_user_id_created",
        "r_user_id_updated",
        "created_date",
        "updated_date"
       
      ];

    protected $validationRules = [
        'r_user_story_id' => 'permit_empty|integer',
        'task_title' => 'required|min_length[3]|max_length[1000]',
        'task_description' => 'required|min_length[3]|max_length[1000]',
        'external_reference_task_id' => 'permit_empty|integer',
        'assignee_id' => 'permit_empty|integer',
        'priority' => 'required|in_list[L,M,H]',
        'task_status' => 'required|integer',
        'completed_percentage' => 'permit_empty|integer',
        'tracker_id' => 'required|integer',
        'start_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'end_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
        'estimated_hours' => 'permit_empty|integer',
        'r_user_id_created' => 'permit_empty|integer',
        'created_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]', 
        'r_user_id_updated' => 'permit_empty|integer',
        'updated_date' => 'permit_empty|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]'

    ];

    protected $validationMessages = [
        
        

         'task_title' => [
            'required'   => 'The task title is required.',
            'min_length' => 'The task title must be at least 3 characters in length.',
            'max_length' => 'The task title cannot exceed 500 characters in length.'
        ],
        'task_description' => [
            'required'   => 'The task description is required.',
            'min_length' => 'The task description must be at least 3 characters in length.',
            'max_length' => 'The task description cannot exceed 500 characters in length.'
        ],

        'assignee_id' => [
            
             'integer'  => 'The Assignee must be an integer.'
        ],

        'priority' => [
            'required'   => 'The priority is required.',
            'isValidEnum'=> 'The priority must be one of: L, M, H.',
        ],

        'task_status' => [
             'required' => 'The task status value is required.',
             'integer'  => 'The task status must be an integer.'
        ],

        'completed_percentage' => [
             'integer'  => 'The completed percentage must be an integer.'
        ],


        'tracker_id' => [
             'required' => 'The tracker id  value is required.',
             'integer'  => 'The tracker id  must be an integer.'
        ],

        'start_date' => [

             'valid_date' => 'The start date must be in the format Y-m-d H:i:s.'
        ],

        'end_date' => [

             'valid_date' => 'The start date must be in the format Y-m-d H:i:s.'
        ],

        'estimated_hours' => [
             'integer'  => 'The estimated hrs must be an integer.'
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
     * @author samuel
     * @return array
     * Purpose: This function is used to Insert a new Task
     */

    public function insertTasks($args)
    {
        $sql = "INSERT INTO orvexa_task (r_user_story_id,task_title,task_description,external_reference_task_id,assignee_id,priority,tracker_id,start_date,end_date,estimated_hours,completed_percentage,task_status,r_user_id_created,r_user_id_updated,created_date,updated_date)
                VALUES (:user_story_id:,:title:,:description:,:reference_task_id:,:assignee:,:priority:,:tracker_id:,:start_date:,:end_date:,:estimated_hours:,:completed_percentage:,:status:,:author_id:,:author_id:,:created_on:,:updated_on:)  
                ";
        $this->db->query($sql, [
            'user_story_id' => $args['r_user_story_id'],
            'title' => $args['task_title'],
            'description' => $args['task_description'],
            'reference_task_id' => $args['external_reference_task_id'] ?? null,
            'assignee' => $args['assignee_id'],
            'priority' => $args['priority'],
            'status' => $args['task_status'],
            'tracker_id' => $args['tracker_id'],
            'author_id' => $args['author_id'],
            'created_on' => $args['created_date'],
            'updated_on' => $args['updated_date'],
            'start_date' => $args['start_date'],
            'end_date' => $args['end_date'],
            'estimated_hours' => $args['estimated_hours'],
            'completed_percentage' => $args['completed_percentage']
        ]);
        if ($sql) {
            return $this->insertID();
        }
    }
         /**
     * @author samuel
     * @return array
     * Purpose: This function is used to Update the set of contents in Tasks
     */
    
        public function updateTaskById($args)
        {
            $sql = "UPDATE orvexa_task SET task_title=:task_title:,task_description=:description:,assignee_id=:assignee:,priority=:priority:,task_status=:status:,tracker_id=:tracker_id:,completed_percentage=:completed_percentage:,estimated_hours=:estimated_time:,start_date=:start_date:,end_date=:end_date: 
                    WHERE task_id=:task_id: OR external_reference_task_id=:task_id:";
            $this->query($sql, [
                'task_id' => $args['task_id'],
                'task_title' => $args['task_title'],
                'description' => $args['task_description'],
                'assignee' => $args['assignee_id'],
                'priority' => $args['priority'],
                'status' => $args['task_status'],
                'tracker_id' => $args['tracker_id'],
                'start_date' => ($args['start_date']=='')?Null:$args['start_date'],
                'end_date' =>($args['end_date']=='')?Null:$args['end_date'],
                'author_id' => $args['author_id'],
                'completed_percentage' => $args['completed_percentage'],
                'estimated_time' => $args['estimated_hours'],
                'updated_on' => $args['updated_on']
            ]);
            if ($sql) {
                return 1;
            }
        }

    /**
     * @author samuel
     * @return int
     * Purpose: This function is used to get the number of tasks present in a user story
     */

     public function countTask($id)
     {
         $sql = "SELECT 
                     COUNT(task_id) as count
                 FROM 
                     orvexa_task 
                 WHERE 
                     r_user_story_id=:id: AND 
                     is_deleted = 'N'";
         $query = $this->query($sql, [
             'id' => $id
         ]);
         if ($query->getNumRows() > 0) {
             $result = $query->getResultArray();
             return $result[0]['count'];
         }
         return 0;
     }

     /**
     * @author samuel
     * @return array returns the trackers for the tash
     * Purpose: This function is used to fetch the tracker details for task
     */
    public function getTrackers(): array
    {
        $sql = "SELECT 
                    tracker_id,
                    tracker
                FROM 
                    orvexa_trackers";
        $query = $this->query($sql);
        if ($query) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author samuel
     * @return array
     * Purpose: This function is used to fetch the status of a tasks
     */
    public function getTaskStatus(): array
    {
        $sql = "SELECT 
                    id AS status_id,
                    name AS status_name
                FROM 
                    orvexa_task_status";
        $query = $this->query($sql);
        if ($query) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author samuel
     * @param int id of a task
     * @return array which contains the information of a particular task
     */

     public function getTaskById($id): array
     {
         $sql = "SELECT 
                     * 
                 From 
                     orvexa_task 
                 Where 
                     external_reference_task_id = :id:";
 
         $query = $this->query($sql, ['id' => $id]);
         if ($query) {
             return $query->getResultArray();
         }
         return [];
     }

     /**
     * @author Abinandhan
     * @return array
     * Purpose: This function is used to fetch all the tasks and display it with limit,offset and required filters 
     */
    public function getTasks($filter): array
    {
        $sql = "SELECT 
                    i.task_id,
                    i.task_title,
                    i.external_reference_task_id as external_id,
                    i.task_description,
                    i.start_date,
                    i.end_date,
                    u.first_name,
                    i.priority,
                    ts.name as task_status,
                    i.completed_percentage,
                    tr.tracker as tracker_name,
                    i.estimated_hours
                FROM 
                    orvexa_task i
                INNER JOIN 
                    orvexa_user u ON i.assignee_id = u.external_employee_id 
                INNER JOIN 
                    orvexa_task_status ts ON i.task_status = ts.id
                INNER JOIN 
                    orvexa_trackers tr ON i.tracker_id = tr.tracker_id
                INNER JOIN 
                    orvexa_user_story us ON i.r_user_story_id = us.user_story_id
                WHERE 
                    i.r_user_story_id = :id: AND i.is_deleted = 'N'";

        $params = [];

        if (!empty($filter['priorityFilter'])) {
            $data = is_array($filter['priorityFilter']) ? $filter['priorityFilter'] : explode(',', $filter['priorityFilter']);
            $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
            $sql .= " AND i.priority  in($data)";
        }


        if (!empty($filter['statusFilter'])) {
            $data = is_array($filter['statusFilter']) ? $filter['statusFilter'] : explode(',', $filter['statusFilter']);
            $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
            $sql .= " AND ts.name in($data)";
        }

        if (!empty($filter['search'])) {
            $sql .= " AND (i.task_title LIKE :search:)";
            $params['search'] = '%' . $filter['search'] . '%';
        }

        $params['id'] = $filter['UId'];

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
        $query = $this->query($sql, $params);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }
             /**
     * @author Samuel
     * @return array
     * Purpose: This function is used to get the Task using external_reference_task_id
     */

    public function getTaskbyTaskId($id)
    {
        $sql = "SELECT 
                    task_title,
                    task_description,
                    b.tracker as task_tracker,
                    c.name as task_statuses,
                    priority as task_priority,
                    d.first_name as task_assignee,
                    a.start_date,
                    a.end_date,
                    a.completed_percentage,
                    a.estimated_hours 
                FROM 
                    orvexa_task a 
                JOIN orvexa_trackers b on a.tracker_id= b.tracker_id 
                JOIN orvexa_task_status c on c.id= a.task_status 
                JOIN orvexa_user d on d.external_employee_id =a.assignee_id 
                WHERE (a.task_id=:id: OR a.external_reference_task_id=:id:) AND a.is_deleted='N'";

        $query = $this->query($sql, [
            'id' => $id
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

     /**
     * @author Samuel
     * @return array
     * Purpose: This function is used to get the Tracker assigned for a BacklogId
     */

    public function getTrackerId($id)
    {
        $sql = "SELECT 
                    r_tracker_id 
                FROM 
                    orvexa_backlog_item 
                WHERE 
                    backlog_item_id = :pblId:";
        $query = $this->query($sql, [
            'pblId' => $id
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }
}

?>