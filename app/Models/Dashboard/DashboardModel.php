<?php

namespace App\Models\Dashboard;

use CodeIgniter\Model;
use App\Models\BaseModel;

/**
 * DashboardModel.php
 *
 * @category   Model
 * @author     Rahul S,Stervin Richard
 * @created    09 July 2024
 * @purpose    Manages sprint-related functionalities: fetching backlog and user story statuses, 
 *             handling sprint overdues, user meetings, and dashboard metrics retrieval.
 */

class DashboardModel extends BaseModel
{
    /**
     * @author Rahul S
     * @param array $params
     * @return array
     * Purpose: To fetch current and upcomming sprint details
     */
    public function getSprintDetails(array $params): array
    {

        $statusParams = $params['sprintStatus'];
        // Base query
        $query = "SELECT 
                    orvexa_sprint.sprint_id,
                    orvexa_sprint.sprint_name,
                    orvexa_sprint.start_date,
                    orvexa_sprint.end_date,
                    ROUND(AVG(IFNULL(orvexa_task.completed_percentage, 0)), 0) as sprint_completed,
                    orvexa_status.status_name as sprint_status,
                    orvexa_sprint.sprint_version,
                    orvexa_sprint_duration.sprint_duration_value as sprint_duration
                FROM 
                    orvexa_sprint
                INNER JOIN
                    orvexa_product ON orvexa_sprint.r_product_id = orvexa_product.product_id
                INNER JOIN 
                    orvexa_sprint_duration ON orvexa_sprint.r_sprint_duration_id = orvexa_sprint_duration.sprint_duration_id
                INNER JOIN 
                    orvexa_module_status ON orvexa_sprint.r_module_status_id = orvexa_module_status.module_status_id
                INNER JOIN 
                    orvexa_status ON orvexa_module_status.r_status_id = orvexa_status.status_id
                INNER JOIN 
                    orvexa_sprint_task ON orvexa_sprint.sprint_id = orvexa_sprint_task.r_sprint_id
                INNER JOIN 
                    orvexa_task ON orvexa_task.task_id = orvexa_sprint_task.r_task_id
                WHERE 
                    orvexa_sprint.r_product_id = ? ";

        // Add status conditions
        $statusConditions = [];
        if (isset($statusParams['ongoing'])) {
            $statusConditions[] = "orvexa_sprint.r_module_status_id IN (" . implode(",", $statusParams['ongoing']) . ")";
        }
        if (isset($statusParams['upcoming'])) {
            $statusConditions[] = "orvexa_sprint.r_module_status_id = " . $statusParams['upcoming'];
        }
        if (isset($statusParams['completed'])) {
            $statusConditions[] = "orvexa_sprint.r_module_status_id = " . $statusParams['completed'];
        }
        if (!empty($statusConditions)) {
            $query .= " AND (" . implode(" OR ", $statusConditions) . ")";
        }

        // Add group by and order by 
        $query .= "
            GROUP BY orvexa_sprint.sprint_id
            ORDER BY orvexa_sprint.sprint_version, orvexa_sprint.start_date ASC
        ";

        $result = $this->query($query, $params['productId']);
        return $result->getResultArray();
    }

    /**
     * @author Rahul S
     * @param array $params
     * @return array
     * Purpose: This function is used to fetch the sprint Id of current
     *           running sprints
     */
    public function getRunningSprintId(array $params): array
    {
        $status = $params['status'];
        $statusParams = implode(',', array_fill(0, count($status), '?'));

        $query = "SELECT 
                    sprint_id, sprint_version, sprint_name
                FROM 
                    orvexa_sprint
                WHERE 
                    r_product_id = ? 
                    AND r_module_status_id IN ($statusParams)";

        $bindings = array_merge([$params['productId']], $status);

        $result = $this->query($query, $bindings);
        return $result->getResultArray();
    }

    /**
     * @author Rahul S
     * @param array $params
     * @return array
     * Purpose: This function is used to fetch backlog status count based on completed ,
     *          onhold,not started and in progress
     */
    public function getBacklogStatusCounts(array $params): array
    {
        // This query categorizes backlog items based on their status and counts
        $backlogStatus = $params['backlogStatus'];
        $inProgressStatus = implode(',',$backlogStatus['in_progress_backlogs']);
        $query = "SELECT
                        CASE
                            WHEN b.r_module_status_id  = ? THEN 'completed_backlogs'
                            WHEN b.r_module_status_id  IN ($inProgressStatus) THEN 'in_progress_backlogs'
                            WHEN b.r_module_status_id  = ? THEN 'on_hold_backlogs'
                            ELSE 'not_started_backlogs'
                        END AS status_category,
                        COUNT(*) AS count
                    FROM
                        orvexa_backlog_item b
                    WHERE
                        b.is_deleted = 'N' AND b.r_product_id = ?
                    GROUP BY 
                        status_category";

        $statusProductMap = array_merge(
            [
                $backlogStatus['completed_backlogs'],
                $backlogStatus['on_hold_backlogs']
            ],
            [$params['productId']]
        );

        $result = $this->query($query, $statusProductMap)->getResultArray();

        $allBacklogCategories = array_keys($params['backlogStatus']);
        $backlogStatusCounts = array_fill_keys($allBacklogCategories, 0);

        // Update the counts for categories that are present in the query results
        foreach ($result as $row) {
            $backlogStatusCounts[$row['status_category']] = $row['count'];
        }
        return $backlogStatusCounts;
    }

    /**
     * @author Rahul S
     * @param array $params
     * @return array 
     * Purpose: Retrieves counts of completed and remaining user stories for dashboard display.
     */
    public function getUserStorystatusCounts(array $params): array
    {
        // used to fetch counts total user stories and categorizes them as completed or remaining
        $query = "SELECT 
                    count(*) as total_user_stories,
                    SUM(CASE WHEN us.r_module_status_id = ? THEN 1 ELSE 0 END) AS completed_stories,
                    SUM(CASE WHEN us.r_module_status_id != ? THEN 1 ELSE 0 END) AS remaining_stories
                FROM 
                    orvexa_user_story us
                INNER JOIN 
                    orvexa_epic e ON us.r_epic_id = e.epic_id
                INNER JOIN 
                    orvexa_backlog_item b ON e.r_backlog_item_id = b.backlog_item_id
                WHERE 
                    us.is_deleted = 'N' AND b.r_product_id = ? ";

        $statusProductMap = [
            $params['userStoryStatus'],
            $params['userStoryStatus'],
            $params['productId']
        ];
        $result = $this->query($query, $statusProductMap);
        return $result->getResultArray();
    }

    /**
     * @author Rahul S
     * @param array $params 
     * @return array 
     * Purpose: Retrieves all the external task id of a sprint.
     */
    public function getSprintTasksId(array $params): array
    {
        $query = "SELECT st.external_reference_task_id
              FROM
                 orvexa_task st
              JOIN 
                orvexa_sprint_task sst ON sst.r_task_id = st.task_id
              JOIN 
                orvexa_sprint ss ON sst.r_sprint_id = ss.sprint_id
              WHERE 
                ss.sprint_id = ?
                AND ss.r_product_id = ?
                AND st.is_deleted = 'N'
                AND st.external_reference_task_id IS NOT NULL";

        $result = $this->query($query, [
            $params['sprintId'],
            $params['productId']
        ]);
        return $result->getResultArray();
    }

    /**
     * @author Rahul S
     * @param array $params
     * @return array 
     * Purpose: Retrieves all the user story id of a product.
     */
    public function getAlluserStoryIds(array $params): array { 
        $query = "SELECT 
                        ss.sprint_version,
                        GROUP_CONCAT(st.r_user_story_id) AS user_story_ids
                    FROM 
                        orvexa_sprint ss
                    JOIN 
                        orvexa_sprint_task sst ON ss.sprint_id = sst.r_sprint_id
                    JOIN 
                        orvexa_task st ON sst.r_task_id = st.task_id
                    WHERE 
                        ss.r_product_id = ?
                        AND ss.is_deleted = 'N' ";
        
        //Add status conditions
        if(is_array($params['status'])){
            $status = implode(',',$params['status']);
            $query .= " AND ss.r_module_status_id IN ($status)";
        } else {
            $query .= " AND ss.r_module_status_id = ?" ;
        }
        
        // Add condition for sprintId
        if (isset($params['sprintId'])) {
            $query .= " AND ss.sprint_id = ?";
        }
        
        // Group by and order by clauses
        $query .= " GROUP BY 
                        ss.sprint_id
                    ORDER BY 
                        ss.sprint_id";
                        
        if(isset($params['sprintId'])){
            $result = $this->query($query, [
                $params['productId'],
                $params['sprintId']
            ]);
            
            return $result->getResultArray();
        }
        
        $result = $this->query($query, [
            $params['productId'],
            $params['status']
        ]);
        
        return $result->getResultArray();
    }


    /**
     * @author Rahul S
     * @param array $params 
     * @return array 
     * Purpose: Retrieves pending task status count in the sprint grouped by priority.
     */
    public function getPendingTaskStatusCounts(array $params): array
    {
        $productId = $params['productId'];
        $sprintId = $params['sprintId'];
        $statuses = $params['status'];

        $status = "'" . implode("','", $statuses) . "'";

        $sql = "SELECT 
                    CASE 
                        WHEN st.priority = 'H' THEN 'HIGH'
                        WHEN st.priority = 'M' THEN 'MEDIUM'
                        WHEN st.priority = 'L' THEN 'LOW'
                    END as priority,
                    COUNT(*) as pending_tasks
                FROM 
                    orvexa_task st
                JOIN 
                    orvexa_sprint_task sst ON st.task_id = sst.r_task_id
                JOIN 
                    orvexa_sprint ss ON sst.r_sprint_id = ss.sprint_id
                WHERE 
                    ss.r_product_id = ?
                    AND ss.sprint_id = ?
                    AND st.task_status IN ($status)
                    AND st.is_deleted = 'N'
                    AND sst.is_deleted = 'N'
                GROUP BY 
                    st.priority
                ORDER BY 
                    FIELD(st.priority, 'H', 'M', 'L')";

        $query = $this->db->query($sql, [$productId, $sprintId]);

        return $query->getResultArray();
    }

    /**
     * @author Rahul S
     * @param array $params 
     * @return array 
     * Purpose: Retrieves pending task in the sprint grouped by priority.
     */
    public function getPendingTasks($params){

        $productId = $params['productId'];
        $sprintId = $params['sprintId'];
        $statuses = $params['status'];

        $status = "'" . implode("','", $statuses) . "'";

        $query = "SELECT 
                    st.task_title,
                    st.start_date,
                    st.end_date,
                    sts.name as status,
                    st.priority,
                    concat_ws(' ',su.first_name,su.last_name) as Assignee
                FROM 
                    orvexa_task st
                JOIN 
                    orvexa_sprint_task sst ON st.task_id = sst.r_task_id
                JOIN 
                    orvexa_sprint ss ON sst.r_sprint_id = ss.sprint_id
                JOIN
                    orvexa_task_status sts ON sts.id = st.task_status
                LEFT JOIN
                    orvexa_user su ON su.external_employee_id = st.assignee_id
                WHERE 
                    ss.r_product_id = ?
                    AND ss.sprint_id = ?
                    AND st.task_status IN ($status)
                    AND st.is_deleted = 'N'
                    AND sst.is_deleted = 'N'";

        $result = $this->db->query($query, [$productId, $sprintId]);

        return $result ->getResultArray();
    }

    /**
     * @author Rahul S
     * @param array $params 
     * @return array 
     * Purpose: Retrieves total estimated hours grouped by task of a sprint for the product.
     */
    public function getEstimatedSprintHours(array $params): array
    {
        $query = "SELECT sum(st.estimated_hours) AS estimated_hours
              FROM
                 orvexa_task st
              JOIN 
                orvexa_sprint_task sst ON sst.r_task_id = st.task_id
              JOIN 
                orvexa_sprint ss ON sst.r_sprint_id = ss.sprint_id
              WHERE 
                ss.sprint_id = ?
                AND ss.r_product_id = ?
                AND st.is_deleted = 'N'";

        $result = $this->query($query, [
            $params['sprintId'],
            $params['productId']
        ]);
        return $result->getRowArray();
    }
    
    /**
     * @author Stervin Richard
     *
     * @return array
     * get the today and tomorrow meetings for the particular user 
     */
    public function userMeetings($id): array
    {
        $sql = "SELECT 
                    d.meeting_start_date,
                    d.meeting_start_time,
                    t.meeting_type_name,
                    p.product_name,
                    d.meeting_link
                FROM 
                    orvexa_meeting_details d
                INNER JOIN 
                    orvexa_meeting_type t 
                ON 
                    d.r_meeting_type_id = t.meeting_type_id
                INNER JOIN 
                    orvexa_product p 
                ON 
                    p.external_project_id = d.r_product_id
                WHERE 
                    d.meeting_start_date 
                BETWEEN 
                    :currentDate: AND (:currentDate: + INTERVAL 2 DAY - INTERVAL 1 SECOND)
                AND 
                    d.r_user_id = :employee_id:
                AND
                    d.is_logged = 'N'
                AND
                    d.is_deleted = 'N'
                ORDER BY 
                    d.meeting_start_date ASC,
                    d.meeting_start_time ASC";
        $query = $this->db->query($sql, [
            'employee_id' => $id,
            'currentDate' => date("Y-m-d")
        ]);
        if ($query->getNumRows() > 0) {
            $meetings = $query->getResultArray();
            return $meetings;
        }
        return [];
    }

    /**
     * @author Stervin Richard
     *
     * @return array
     * get the backlog priority and priority count details for the user products and the 
     * backlog status is not completed
     */
    public function getBacklogPriority($productAndStatus): array
    {
        $sql = 'SELECT 
                    priority, 
                    count(*) AS pblsCount
                FROM 
                    orvexa_backlog_item 
                WHERE 
                    r_product_id IN :product_id:
                AND
                    r_module_status_id NOT IN :module_status_id:
                AND
                    is_deleted = "N"
                GROUP BY 
                    priority
                ORDER BY 
                    priority DESC';

        $query = $this->db->query($sql, [
            'product_id' => $productAndStatus['product_id'],
            'module_status_id' => $productAndStatus['module_status_id'],
        ]);
        if ($query->getNumRows() > 0) {
            $backlogPriority = $query->getResultArray();
            return $backlogPriority;
        }
        return [];

    }

    /**
     * @author Stervin Richard
     *
     * @return array
     * get the sprint details with task details that mapped to this sprints
     * and only the running sprint and their task is selected
     */
    public function getSprintPerformance($productAndStatus): array
    {
        $sql = 'SELECT 
                    s.sprint_id,
                    s.sprint_version,                    
                    s.r_product_id,
                    st.r_task_id,
                    t.task_status,
                    t.completed_percentage,
                    st.is_deleted  As sprint_task_deleted,
                    t.is_deleted  As task_deleted, 
                    s.end_date,                   
                    CASE 
                        WHEN s.end_date < :currentDate: THEN 1 
                        ELSE 0 
                    END AS delay
                FROM 
                    orvexa_sprint s
                LEFT JOIN 
                    orvexa_sprint_task st
                ON 
                    s.sprint_id = st.r_sprint_id
                LEFT JOIN 
                    orvexa_task t
                ON 
                    t.task_id = st.r_task_id
                WHERE 
                    s.r_product_id IN :product_id:
                AND 
                    s.r_module_status_id IN :module_status_id:
                AND
                    s.is_deleted = "N"
                ORDER BY
                    s.end_date ASC';

        $query = $this->db->query($sql, [
            'product_id' => $productAndStatus['product_id'],
            'module_status_id' => $productAndStatus['module_status_id'],
            'currentDate' => date("Y-m-d")
        ]);
        if ($query->getNumRows() > 0) {
            $sprintPerformance = $query->getResultArray();
            return $sprintPerformance;
        }

        return [];
    }

    /**
     * @author Stervin Richard
     *
     * @return array
     * get the on-track and delayed products based on the running sprint with the 
     * end date checking with current date
     */
    public function productOnTrack($productAndStatus): array
    {
        $sql = "SELECT
                    r_product_id, 
                    SUM(CASE WHEN end_date >= :currentDate: THEN 1 ELSE 0 END) AS on_track,
                    SUM(CASE WHEN end_date < :currentDate: THEN 1 ELSE 0 END) AS delay
                FROM 
                    orvexa_sprint 
                WHERE 
                    r_module_status_id IN :module_status_id:
                AND 
                    r_product_id IN :product_id:
                AND
                    is_deleted = 'N'
                GROUP BY 
                    r_product_id";
        $query = $this->db->query($sql, [
            'product_id' => $productAndStatus['product_id'],
            'module_status_id' => $productAndStatus['module_status_id'],
            'currentDate' => date("Y-m-d")
        ]);
        if ($query->getNumRows() > 0) {
            $sprintPerformance = $query->getResultArray();
            return $sprintPerformance;
        }

        return [];

    }

    /**
     * @author Stervin Richard
     *
     * @return array
     * get all upcoming sprint planning details with their activities
     */
    public function getAllUpcomingSprints($upcomingSprintStatuses): array{
        $sql = "SELECT 
                    s.sprint_id,
                    s.sprint_version,
                    sp.start_date,
                    p.product_name,
                    sa.activity
                FROM 
                    orvexa_sprint s 
                INNER JOIN 
                    orvexa_sprint_planning sp 
                ON 
                    sp.r_sprint_id = s.sprint_id 
                INNER JOIN 
                    orvexa_product p 
                ON 
                    s.r_product_id = p.external_project_id 
                INNER JOIN 
                    orvexa_sprint_activity sa 
                ON 
                    sa.sprint_activity_id = sp.r_sprint_activity_id
                WHERE 
                    sp.r_module_status_id IN :r_module_status_id_sprint_planned:
                    AND 
                        s.r_module_status_id = :r_module_status_id_sprint:
                    AND 
                        s.r_product_id IN :product_id:
                    AND 
                        sa.is_deleted = 'N'
                    AND 
                        p.is_deleted = 'N'
                    AND 
                        sp.is_deleted = 'N'
                    AND 
                        s.is_deleted = 'N'
                    AND
                        sp.start_date >= :currentDate:
                ORDER BY
                    sp.start_date 
        ";
        $query = $this->db->query($sql,[
            "r_module_status_id_sprint" => $upcomingSprintStatuses['sprintStatuses'],
            "r_module_status_id_sprint_planned" => $upcomingSprintStatuses['sprintPlannedStatus'],
            "product_id" => $upcomingSprintStatuses['product_id'],
            'currentDate' => date("Y-m-d")
        ]);
        if ($query->getNumRows() > 0) {
            $sprintPerformance = $query->getResultArray();
            return $sprintPerformance;
        }

        return [];
    }

}
