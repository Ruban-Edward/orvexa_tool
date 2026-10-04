<?php

/**
 * SprintModel.php
 * 
 * @category   Model
 * @author     Jeril,Jeeva,Vishva,Sivabalan
 * @created    04 July 2024
 * @purpose This Model is for handling database for overall sprint module 
 */

namespace App\Models;

use CodeIgniter\Model;
use PhpParser\Node\Stmt\Return_;

class SprintModel extends BaseModel
{
     /**
      * @author Jeril
      * Method to update sprint to running automatically when start date is reached
      */
     public function updateSprintRunning($today)
     {
          $query = "UPDATE orvexa_sprint
                    SET r_module_status_id = :id:
                    WHERE r_module_status_id = :exist_id:
                    AND start_date <= :today:";
          $this->query($query, [
               "id" => 20,
               "exist_id" => 19,
               "today" => $today
          ]);
     }

     /**
      * @author Sivabalan
      * @param array $products
      * @param int $limit
      * @param int $offset
      * @return array
      * Method to fetch data from multiple tables for sprint list page and return to the controller
      */
     public function getSprintList($products, $uId, $limit = null, $offset = null, $filter = null, $columns = null)
     {


          $placeholders = implode(',', array_fill(0, count($products), '?'));

          if ($filter) {
               $productNameFilter = $filter . '%';
               $products[] = $productNameFilter;
          }
          $query = "SELECT SQL_CALC_FOUND_ROWS 
                     orvexa_product.product_name AS product,
                     orvexa_customer.customer_name AS customer,
                     orvexa_sprint.sprint_name,
                     orvexa_sprint.start_date,
                     orvexa_sprint.end_date,
                     CONCAT(ROUND(AVG(IFNULL(orvexa_task.completed_percentage, 0)), 0), ' %') AS sprint_completed,
                     orvexa_status.status_name AS sprint_status,
                     orvexa_sprint.estimated_hrs,
                     CONCAT(uc.first_name,' ',uc.last_name) AS created_by,
                     orvexa_sprint.sprint_version,
                     orvexa_sprint_duration.sprint_duration_value AS duration,
                     orvexa_sprint.created_date,
                     uc.first_name AS user_created,
                     orvexa_sprint.updated_date,
                     uc.first_name AS user_updated,
                     orvexa_sprint.sprint_id,
                     orvexa_sprint.r_product_id AS product_id
                     FROM orvexa_sprint
                     INNER JOIN orvexa_sprint_duration ON
                     orvexa_sprint.r_sprint_duration_id = orvexa_sprint_duration.sprint_duration_id
                     INNER JOIN orvexa_customer ON
                     orvexa_sprint.r_customer_id = orvexa_customer.customer_id
                     INNER JOIN orvexa_module_status ON
                     orvexa_sprint.r_module_status_id = orvexa_module_status.module_status_id
                     INNER JOIN orvexa_status ON
                     orvexa_module_status.r_status_id = orvexa_status.status_id
                     INNER JOIN orvexa_user uc ON
                     orvexa_sprint.r_user_id_created = uc.external_employee_id
                     and
                     orvexa_sprint.r_user_id_updated = uc.external_employee_id
                     INNER JOIN orvexa_sprint_task ON
                     orvexa_sprint.sprint_id = orvexa_sprint_task.r_sprint_id
                     INNER JOIN orvexa_task ON
                     orvexa_task.task_id = orvexa_sprint_task.r_task_id
                     INNER JOIN orvexa_product ON
                     orvexa_sprint.r_product_id = orvexa_product.external_project_id
                     WHERE orvexa_product.external_project_id IN ({$placeholders})";
          if ($filter) {
               $query .= " AND orvexa_product.product_name LIKE ?";
          }
          if (!empty($columns['product_name'])) {
               $productname = $columns['product_name'];
               $query .= " AND orvexa_product.product_name IN ($productname) ";
          }
          if (!empty($columns['sprint_name'])) {
               $sprintname = $columns['sprint_name'];
               $query .= " AND orvexa_sprint.sprint_name IN ($sprintname)";
          }
          if (!empty($columns['customer'])) {
               $sprintcustomer = $columns['customer'];
               $query .= " AND orvexa_customer.customer_name IN ($sprintcustomer)";
          }
          if (!empty($columns['status_name'])) {
               $sprintstatus = $columns['status_name'];
               $query .= " AND  orvexa_status.status_name IN ($sprintstatus)";
          }
          if (!empty($columns['sprint_duration_value'])) {
               $sprintstatus = $columns['sprint_duration_value'];
               $query .= " AND   orvexa_sprint_duration.sprint_duration_value IN ($sprintstatus)";
          }
          if (!empty($columns['start_date']) && !empty($columns['end_date'])) {
               $sprintdate = $columns['start_date'];
               $sprintEnddate = $columns['end_date'];
               $query .= " AND (orvexa_sprint.start_date >= $sprintdate AND orvexa_sprint.start_date <= $sprintEnddate) OR (orvexa_sprint.end_date <= $sprintEnddate AND orvexa_sprint.end_date >= $sprintEnddate)";
          }
          // if (!empty($columns['end_date'])) {
          //      $sprintEnddate = $columns['end_date'];
          //      $query .= "";
          // }
          $query .= " AND orvexa_sprint_task.is_deleted = 'N' AND orvexa_sprint.is_deleted = 'N'
                     GROUP BY orvexa_sprint_task.r_sprint_id
                     ORDER BY orvexa_sprint.created_date DESC, orvexa_sprint.start_date, orvexa_sprint.end_date";


          if (isset($limit) && isset($offset)) {
               $query .= " LIMIT ?, ?";
          }
          // Bind limit and offset values to the parameters array
          if (isset($limit) && isset($offset)) {
               $products[] = (int) $offset; // Ensure offset is cast to integer
               $products[] = (int) $limit;
          }
          // Ensure limit is cast to integer
          $result = $this->query($query, $products);
          // Check if query executed successfully
          if (!$result) {
               // Handle error, e.g., log it, return an error response, etc.
               return [];
          }
          $totalRowsQuery = "SELECT FOUND_ROWS() AS total_rows";
          $totalRowsResult = $this->query($totalRowsQuery);
          $totalRows = $totalRowsResult->getRowArray();
          return [$result->getResultArray(), $totalRows['total_rows']];
     }

     /**
      * @author Jeril
      * @return array
      * Method to fetch sprint duration values from the t_sprint_duration table
      */
     public function getSprintDuration(): array
     {
          $query = "SELECT *
                    FROM orvexa_sprint_duration
                    WHERE orvexa_sprint_duration.is_deleted = 'N'";
          $result = $this->query($query);

          return $result->getResultArray();
     }

     /**
      * @author Jeril
      * @return array
      * Method to fetch sprint activity from the t_sprint_activity table
      */
     public function getSprintActivity(): array
     {
          $query = "SELECT *
                    FROM orvexa_sprint_activity
                    WHERE orvexa_sprint_activity.is_deleted = 'N'";
          $result = $this->query($query);

          return $result->getResultArray();
     }

     /**
      * @author Jeril
      * @return array
      * Method to fetch sthe customer details
      */
     public function getCustomer(): array
     {
          $sql = "SELECT *
                    FROM orvexa_customer
                    WHERE orvexa_customer.is_deleted = 'N'";
          $query = $this->query($sql);
          if ($query->getNumRows() > 0) {
               return $query->getResultArray();
          }
          return [];
     }

     /**
      * @author Vishva
      * @param array $param
      * @return array
      * Method to fetch task ready for sprint by product wise
      */
     public function getReadyForSprintByProduct($param): array
     {
          $sql = "SELECT orvexa_product.external_project_id AS prodoct_id,
				orvexa_product.product_name, 
				orvexa_backlog_item.backlog_item_id, 
				orvexa_backlog_item.backlog_item_name,
                    orvexa_backlog_item.priority, 
				orvexa_epic.epic_id, 
				orvexa_epic.epic_description AS epic_name, 
				orvexa_user_story.user_story_id,
				CONCAT(orvexa_user_story.as_a_an,' ',
				orvexa_user_story.i_want,' ',
				orvexa_user_story.so_that) AS user_story,                     
				orvexa_task.task_id,
				orvexa_task.task_title,
                    CONCAT(orvexa_user.first_name,' ',orvexa_user.last_name) AS assignee_name,
                    orvexa_task_status.name 
				FROM orvexa_task
				INNER JOIN orvexa_user_story ON 
				orvexa_task.r_user_story_id = orvexa_user_story.user_story_id 
				INNER JOIN orvexa_epic ON 
				orvexa_user_story.r_epic_id = orvexa_epic.epic_id 
				INNER JOIN orvexa_backlog_item ON 
				orvexa_epic.r_backlog_item_id = orvexa_backlog_item.backlog_item_id 
				INNER JOIN orvexa_product ON
				orvexa_backlog_item.r_product_id = orvexa_product.external_project_id
                    INNER JOIN orvexa_task_status ON
                    orvexa_task.task_status = orvexa_task_status.id
                    INNER JOIN orvexa_user ON
                    orvexa_task.assignee_id = orvexa_user.external_employee_id
				WHERE orvexa_user_story.r_module_status_id IN :user_story:
                    AND orvexa_backlog_item.r_module_status_id IN :backlog_status:
                    AND orvexa_task.is_deleted = 'N'
                    AND orvexa_task.task_status IN :task_status_id:
                    AND orvexa_product.external_project_id = :product_id:
                    ORDER BY orvexa_backlog_item.backlog_order ASC";
          $query = $this->query($sql, [
               "user_story" => $param["userStory"],
               "backlog_status" => $param["backlog"],
               "task_status_id" => $param["task"],
               "product_id" => $param["productId"]
          ]);
          if ($query->getNumRows() > 0) {
               return $query->getResultArray();
          }
          return [];
     }

     /**
      * @author Vishva
      * @param int $productId
      * @return array
      * Method to fetch task in the sprint by product wise
      */

     public function getTaskSprint($productId)
     {
          $sql = "SELECT orvexa_product.external_project_id AS prodoct_id,
     			orvexa_product.product_name, 
     			orvexa_backlog_item.backlog_item_id, 
     			orvexa_backlog_item.backlog_item_name, 
     			orvexa_epic.epic_id, 
     			orvexa_epic.epic_description AS epic_name,  
     			orvexa_user_story.user_story_id,
     			CONCAT(orvexa_user_story.as_a_an,' ',
     			orvexa_user_story.i_want,' ',
     			orvexa_user_story.so_that) AS user_story,                     
     			orvexa_task.task_id,
     			orvexa_task.task_description,
                    orvexa_task_status.name 
     			FROM orvexa_task
     			INNER JOIN orvexa_user_story ON 
     			orvexa_task.r_user_story_id = orvexa_user_story.user_story_id 
     			INNER JOIN orvexa_epic ON 
     			orvexa_user_story.r_epic_id = orvexa_epic.epic_id 
     			INNER JOIN orvexa_backlog_item ON 
     			orvexa_epic.r_backlog_item_id = orvexa_backlog_item.backlog_item_id 
     			INNER JOIN orvexa_product ON
     			orvexa_backlog_item.r_product_id = orvexa_product.external_project_id 
     			WHERE orvexa_user_story.r_module_status_id IN (:status_id:, :status_id2:)
     			AND orvexa_task.task_status IN (:task_status_id:, :task_status_id2:)
     			AND orvexa_product.external_project_id = :product_id:
                    AND orvexa_task.is_deleted = 'N'";
          $query = $this->query($sql, [
               "task_status_id" => 1,
               "task_status_id2" => 2,
               "status_id" => 16,
               "status_id2" => 17,
               "product_id" => $productId
          ]);
          if ($query->getNumRows() > 0) {
               return $query->getResultArray();
          }
          return [];
     }

     /**
      * @author Vishva
      * @param int $productId
      * @return array
      * Method to fetch users by product wise
      */

     public function getMembersByProduct($productId)
     {
          $query = "SELECT orvexa_user.external_employee_id AS id,
                  CONCAT(orvexa_user.first_name,' ',orvexa_user.last_name) AS name,
                  orvexa_role.role_name
                  FROM orvexa_product_user
                  INNER JOIN orvexa_user
                  ON orvexa_user.external_employee_id = orvexa_product_user.r_user_id
                  INNER JOIN orvexa_role
                  ON orvexa_role.role_id=orvexa_user.r_role_id
                  WHERE orvexa_product_user.r_product_id = :r_product_id:
                  AND orvexa_product_user.is_deleted = 'N'";
          $result = $this->query($query, ["r_product_id" => $productId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeril
      * @param array $data
      * @return array
      * Method to fetch user stories for task inserted in sprint
      */
     public function getUserStories($data): array
     {
          $placeholders = implode(',', array_fill(0, count($data), '?'));
          $query = "SELECT DISTINCT
                    r_user_story_id AS id
                    FROM orvexa_task
                    WHERE task_id IN ({$placeholders})
                    AND orvexa_task.is_deleted = 'N'";
          $result = $this->query($query, $data);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeril
      * @param array $data
      * @param int $status_id
      * Method to update user story status from id fetched from getUserStories()
      */

     public function updateUserStory($data, $status_id)
     {
          $placeholders = implode(',', array_fill(0, count($data), '?'));
          $query = "UPDATE orvexa_user_story
                    SET r_module_status_id = ?
                    WHERE user_story_id in ({$placeholders})
                    AND orvexa_user_story.is_deleted = 'N'";
          $status[] = $status_id;
          $param = array_merge($status, $data);
          $this->query($query, $param);
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * @return array
      * Method to fetch sprint data by sprint id
      */
     public function getSprint($sprintId): array
     {
          $query = "SELECT 
				orvexa_product.product_name,
				orvexa_sprint.sprint_name,
				orvexa_sprint.start_date,
				orvexa_sprint.end_date,
                    orvexa_sprint.r_module_status_id as sprint_status_id,
				orvexa_status.status_name as sprint_status_name,
				orvexa_sprint.sprint_version,
				orvexa_sprint_duration.sprint_duration_value as sprint_duration,
				orvexa_customer.customer_name,
                    orvexa_sprint.sprint_goal,
                    CONCAT(uc.first_name,' ',uc.last_name) AS created_by,
                    orvexa_sprint.r_product_id
				FROM orvexa_sprint
				INNER JOIN orvexa_product ON
				orvexa_sprint.r_product_id = orvexa_product.external_project_id
				INNER JOIN orvexa_sprint_duration ON
				orvexa_sprint.r_sprint_duration_id = orvexa_sprint_duration.sprint_duration_id
				INNER JOIN orvexa_customer ON
				orvexa_sprint.r_customer_id = orvexa_customer.customer_id
				INNER JOIN orvexa_module_status ON
				orvexa_sprint.r_module_status_id = orvexa_module_status.module_status_id
				INNER JOIN orvexa_status ON
				orvexa_module_status.r_status_id = orvexa_status.status_id
				INNER JOIN orvexa_user uc ON
				orvexa_sprint.r_user_id_created = uc.external_employee_id
				INNER JOIN orvexa_user uu ON
				orvexa_sprint.r_user_id_updated = uu.external_employee_id
				WHERE orvexa_sprint.sprint_id = :id:
                    AND orvexa_sprint.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeril
      * @param int $sprintId
      * @return array
      * Method to fetch sprint data for edit
      */
     public function getEditSprint($sprintId): array
     {
          $query = "SELECT * FROM orvexa_sprint
                    WHERE sprint_id = :id:
                    AND orvexa_sprint.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * @return array
      * Method to fetch data of sprint planning by sprint id
      */
     public function getSprintPlanning($sprintId): array
     {
          $query = "SELECT
                    orvexa_sprint_planning.r_sprint_activity_id,
				orvexa_sprint_activity.activity,
				orvexa_sprint_planning.start_date as startDate,
				orvexa_sprint_planning.end_date as endDate,
                    orvexa_sprint_planning.r_module_status_id,
                    orvexa_notes.notes,
                    orvexa_status.status_name
				FROM orvexa_sprint_planning
				INNER JOIN orvexa_sprint_activity
				ON orvexa_sprint_planning.r_sprint_activity_id = orvexa_sprint_activity.sprint_activity_id
                    INNER JOIN orvexa_module_status ON
                    orvexa_sprint_planning.r_module_status_id = orvexa_module_status.module_status_id
                    INNER JOIN orvexa_status ON
                    orvexa_module_status.r_status_id = orvexa_status.status_id
                    INNER JOIN orvexa_notes ON
                    orvexa_sprint_planning.r_notes_id = orvexa_notes.notes_id
				WHERE r_sprint_id = :id:
                    AND orvexa_sprint_planning.is_deleted = 'N'
				ORDER BY orvexa_sprint_planning.start_date ASC";
          $result = $this->query($query, ["id" => $sprintId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * @return array
      * Method to fetch task associated with particular sprint
      */
     public function getSprintTask($sprintId)
     {
          $query = "SELECT DISTINCT
				orvexa_backlog_item.backlog_item_id, 
				orvexa_backlog_item.backlog_item_name, 
				orvexa_epic.epic_id, 
				orvexa_epic.epic_description AS epic_name, 
				orvexa_user_story.user_story_id AS userstory_id,
				CONCAT('As a an ',orvexa_user_story.as_a_an,' i want ',orvexa_user_story.i_want,' so that ',orvexa_user_story.so_that) AS user_story,                     
				orvexa_task.task_id,
                    orvexa_task.task_title,
                    IFNULL(orvexa_task.completed_percentage, 0) AS completed_percentage,
				orvexa_task.task_description,
				orvexa_task_status.name as task_status 
				FROM orvexa_sprint_task
				INNER JOIN orvexa_sprint ON
				orvexa_sprint_task.r_sprint_id = orvexa_sprint.sprint_id
				INNER JOIN orvexa_task ON
				orvexa_sprint_task.r_task_id = orvexa_task.task_id
                    INNER JOIN orvexa_task_status ON
                    orvexa_task_status.id = orvexa_task.task_status
				INNER JOIN orvexa_user_story ON 
				orvexa_task.r_user_story_id = orvexa_user_story.user_story_id 
				INNER JOIN orvexa_epic ON 
				orvexa_user_story.r_epic_id = orvexa_epic.epic_id 
				INNER JOIN orvexa_backlog_item ON 
				orvexa_epic.r_backlog_item_id = orvexa_backlog_item.backlog_item_id 
				INNER JOIN orvexa_product ON
				orvexa_backlog_item.r_product_id = orvexa_product.external_project_id 
				WHERE orvexa_sprint_task.r_sprint_id = :id:
                    AND orvexa_sprint_task.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];

     }

     /**
      * @author Sivabalan
      * @param int $sprintId
      * @return array
      * Method to fetch users associated with a sprint
      */
     public function getSprintMember($sprintId): array
     {
          $query = "SELECT DISTINCT
				orvexa_sprint_user.r_user_id AS id,
                    orvexa_user.external_employee_id AS emp_id,
                    CONCAT(orvexa_user.first_name,' ',orvexa_user.last_name) AS name,
                    orvexa_user.external_username as email_id,
                    orvexa_role.role_name
				FROM orvexa_sprint_user
				INNER JOIN orvexa_user ON
                    orvexa_sprint_user.r_user_id = orvexa_user.external_employee_id
                    INNER JOIN orvexa_role ON orvexa_role.role_id=orvexa_user.r_role_id
				WHERE orvexa_sprint_user.r_sprint_id = :id:
                    AND orvexa_sprint_user.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * @return array
      * Method to fetch data of daily scrum
      */

     public function getDailyScrum($sprintId): array
     {
          $query = "SELECT
				orvexa_daily_scrum.added_date,
				orvexa_daily_scrum.challenges,
				orvexa_notes.notes,
                    orvexa_task.task_title
				FROM orvexa_daily_scrum
				INNER JOIN orvexa_notes
				ON orvexa_daily_scrum.r_notes_id = orvexa_notes.notes_id
                    INNER JOIN orvexa_task
                    ON orvexa_daily_scrum.r_task_id = orvexa_task.task_id
				WHERE orvexa_daily_scrum.r_sprint_id = :id:
                    AND orvexa_daily_scrum.is_deleted = 'N'
				ORDER by added_date DESC";
          $result = $this->query($query, ["id" => $sprintId]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Sivabalan
      * @param int $sprintId
      * @return array
      * Method to fetch date of sprint review
      */

     public function getSprintReviewDate($sprintId): array
     {
          $query = "SELECT
				orvexa_sprint_planning.start_date AS added_date
				FROM orvexa_sprint_planning
				INNER JOIN orvexa_sprint_activity
				ON orvexa_sprint_planning.r_sprint_activity_id = orvexa_sprint_activity.sprint_activity_id
				WHERE orvexa_sprint_planning.r_sprint_id = :id:
				AND orvexa_sprint_planning.r_sprint_activity_id = :r_sprint_activity_id:
                    AND orvexa_sprint_planning.is_deleted = 'N'
                    AND orvexa_sprint_activity.is_deleted = 'N'";
          $result = $this->query($query, [
               "id" => $sprintId,
               "r_sprint_activity_id" => 12
          ]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return array(0 => array("added_date" => "Not yet planned"));
     }

     /**
      * @author Jeril
      * @param int $sprintId
      * @return array
      * Method to fetch data of sprint review
      */
     public function getSprintReview($sprintId): array
     {
          $query = "SELECT  
                    sn1.notes AS General,
                    ssr.code_review_status AS CodeReviewStatus,
                    sn2.notes AS CodeReview,
                    ssr.challenges_status AS ChallengesStatus,
                    sn3.notes AS ChallengesFaced,
                    ssr.sprint_goal_status AS SprintGoalStatus,
                    sn4.notes AS SprintGoal,
                    CONCAT(su.first_name,' ',su.last_name) as code_reviewers
                    FROM orvexa_sprint_review ssr
                    INNER JOIN orvexa_notes sn1 ON ssr.r_orvexa_notes_id = sn1.notes_id
                    INNER JOIN orvexa_notes sn2 ON ssr.r_orvexa_notes_id_cr = sn2.notes_id
                    INNER JOIN orvexa_notes sn3 ON ssr.r_orvexa_notes_id_challenges = sn3.notes_id
                    INNER JOIN orvexa_notes sn4 ON ssr.r_orvexa_notes_id_sg = sn4.notes_id
                    LEFT JOIN orvexa_code_review_users scru ON ssr.r_sprint_id = scru.r_sprint_id
                    LEFT JOIN orvexa_user su ON scru.r_user_id = su.external_employee_id
                    WHERE ssr.r_sprint_id = :id:
                    AND ssr.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          return $result->getResultArray();
     }

     /**
      * @author Vishva
      * @param int $sprintId
      * @return array
      * Method to fetch data of sprint review
      */
     public function fetchCodeReviewers($sprintId): array
     {
          $query = "SELECT  
                     CONCAT(su.first_name,' ',su.last_name) as code_reviewers
                     FROM orvexa_code_review_users scru
                     INNER JOIN orvexa_user su ON scru.r_user_id = su.external_employee_id
                     WHERE scru.r_sprint_id = :id:
                     AND scru.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          return $result->getResultArray();
     }

     /**
      * @author Vishva
      * @param int $sprintId
      * @return array
      * Method to fetch date of sprint retrospective
      */

     public function getSprintRetrospectiveDate($sprintId)
     {
          $query = "SELECT
				orvexa_sprint_planning.start_date AS added_date
				FROM orvexa_sprint_planning
				INNER JOIN orvexa_sprint_activity
				ON orvexa_sprint_planning.r_sprint_activity_id = orvexa_sprint_activity.sprint_activity_id
				WHERE orvexa_sprint_planning.r_sprint_id = :id:
				AND orvexa_sprint_planning.r_sprint_activity_id = :r_sprint_activity_id:
                    AND orvexa_sprint_planning.is_deleted = 'N'";
          $result = $this->query($query, [
               "id" => $sprintId,
               "r_sprint_activity_id" => 13
          ]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return array(0 => array("added_date" => "Not yet planned"));
     }

     /**
      * @author Vishva
      * @param int $sprintId
      * @return array
      * Method to fetch data of sprint review
      */
     public function getSprintRetrospective($sprintId)
     {
          $query = "SELECT
				orvexa_sprint_retrospective.challenge,
				orvexa_notes.notes
				FROM orvexa_sprint_retrospective
				INNER JOIN orvexa_notes
				ON orvexa_sprint_retrospective.r_notes_id = orvexa_notes.notes_id
				WHERE orvexa_sprint_retrospective.r_sprint_id = :id:
                    AND orvexa_sprint_retrospective.is_deleted = 'N'";
          $result = $this->query($query, ["id" => $sprintId]);
          return $result->getResultArray();
     }


     /**
      * @author Jeeva
      * @return array
      * Method to fetch status of sprint
      */
     public function getSprintStatus(): array
     {
          $query = "SELECT orvexa_module_status.module_status_id,
                     orvexa_status.status_name
                     FROM orvexa_module_status
                     INNER JOIN orvexa_status
                     ON orvexa_module_status.r_status_id = orvexa_status.status_id
                     WHERE orvexa_module_status.r_module_id = :r_module_id:
                     AND orvexa_module_status.is_deleted = 'N'";
          $result = $this->db->query($query, ['r_module_id' => 8]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * Method to update task status during sprint review
      */
     public function updateTaskReview($tasks, $status)
     {
          $query = "UPDATE orvexa_task
                     SET task_status = :task_status:
                     WHERE task_id = :task_id:";
          $result = $this->query($query, [
               "task_status" => $status,
               "task_id" => $tasks
          ]);
          return $result;
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * Method to remove users from a sprint
      */
     public function removeSprintUsers($sprintId)
     {
          $query = "UPDATE orvexa_sprint_user
                SET is_deleted = :is_deleted:
                WHERE r_sprint_id = :r_sprint_id:";
          $this->db->query($query, [
               "is_deleted" => "Y",
               "r_sprint_id" => $sprintId
          ]);
     }

     /**
      * @author Jeeva
      * @param int $sprintId
      * Method to remove tasks from a sprint
      */
     public function removeSprintTasks($sprintId)
     {
          $query = "UPDATE orvexa_sprint_task
                SET is_deleted = :is_deleted:
                WHERE r_sprint_id = :r_sprint_id:";
          $this->db->query($query, [
               "is_deleted" => "Y",
               "r_sprint_id" => $sprintId
          ]);
     }

     /**
      * @author Vishva
      * @param array $param
      * @return array
      * Method to fetch tasks for edit purpose
      */
     public function getTaskForEdit($param): array
     {
          $query = "SELECT orvexa_product.external_project_id AS prodoct_id,
				orvexa_product.product_name, 
				orvexa_backlog_item.backlog_item_id, 
				orvexa_backlog_item.backlog_item_name,
                    orvexa_backlog_item.priority,  
				orvexa_epic.epic_id, 
				orvexa_epic.epic_description AS epic_name, 
				orvexa_user_story.user_story_id,
				CONCAT(orvexa_user_story.as_a_an,' ',
				orvexa_user_story.i_want,' ',
				orvexa_user_story.so_that) AS user_story,                     
				orvexa_task.task_id,
				orvexa_task.task_title,
                    CONCAT(orvexa_user.first_name,' ',orvexa_user.last_name) AS assignee_name,
                    orvexa_task_status.name 
				FROM orvexa_task
				INNER JOIN orvexa_user_story ON 
				orvexa_task.r_user_story_id = orvexa_user_story.user_story_id 
				INNER JOIN orvexa_epic ON 
				orvexa_user_story.r_epic_id = orvexa_epic.epic_id 
				INNER JOIN orvexa_backlog_item ON 
				orvexa_epic.r_backlog_item_id = orvexa_backlog_item.backlog_item_id 
				INNER JOIN orvexa_product ON
				orvexa_backlog_item.r_product_id = orvexa_product.external_project_id 
                    INNER JOIN orvexa_task_status ON
                    orvexa_task.task_status = orvexa_task_status.id
                    INNER JOIN orvexa_user ON
                    orvexa_task.assignee_id = orvexa_user.external_employee_id
				WHERE orvexa_user_story.r_module_status_id IN :user_story:
                    AND orvexa_task.is_deleted = 'N'
                    AND orvexa_product.external_project_id = :r_project_id:
                    ORDER BY orvexa_backlog_item.backlog_order ASC";
          $query = $this->query($query, [
               "user_story" => $param["userStory"],
               "r_project_id" => $param["r_product_id"]
          ]);
          if ($query->getNumRows() > 0) {
               return $query->getResultArray();
          }
          return [];
     }

     /**
      * @author Vishva
      * @return array
      * Method to fetch sprint planning status
      */
     public function getSprintPlanningStatus()
     {
          $query = "SELECT orvexa_module_status.module_status_id,
                    orvexa_status.status_name
                    FROM orvexa_module_status
                    INNER JOIN orvexa_status ON
                    orvexa_module_status.r_status_id = orvexa_status.status_id
                    WHERE r_module_id = :r_module_id:";
          $result = $this->query($query, [
               "r_module_id" => 19
          ]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Vishva
      * @param $data
      * Method to update sprint planning status
      */
     public function updateSprintPlan($data)
     {
          $query = "UPDATE orvexa_sprint_planning
                    SET r_module_status_id = :r_module_status_id:
                    WHERE r_sprint_id = :r_sprint_id:
                    AND r_sprint_activity_id = :r_activity_id:";
          return $this->query($query, [
               "r_module_status_id" => $data["r_status_id"],
               "r_sprint_id" => $data["sprint_id"],
               "r_activity_id" => $data["activity_id"]
          ]);
     }

     /**
      * @author Vishva
      * @param $data
      * Method to update sprint status
      */
     public function updateSprintStatus($data)
     {
          $query = "UPDATE orvexa_sprint
                     SET r_module_status_id = :r_module_status_id:
                     WHERE sprint_id = :r_sprint_id:";
          return $this->query($query, [
               "r_module_status_id" => $data["r_status_id"],
               "r_sprint_id" => $data["sprint_id"]
          ]);
     }

     /**
      * @author Vishva
      * @param array $param
      * @return array
      * Method to fetch data for sprint history
      */
     public function getSprintHistory($param): array
     {
          $query = "SELECT 
                    CONCAT(u.first_name,' ',u.last_name) AS name,
                    orvexa_module.module_name,
                    orvexa_sprint.sprint_name,
                    orvexa_action_type.action_type_name,
                    orvexa_user_action.action_data,
                    orvexa_user_action.action_date
                    FROM orvexa_user_action
                    INNER JOIN orvexa_user u
                    ON orvexa_user_action.r_user_id = u.external_employee_id
                    INNER JOIN orvexa_action_type
                    ON orvexa_user_action.r_action_type_id = orvexa_action_type.action_type_id
                    INNER JOIN orvexa_module
                    ON orvexa_user_action.r_module_id = orvexa_module.module_id
                    INNER JOIN orvexa_sprint
                    ON orvexa_user_action.reference_id = orvexa_sprint.sprint_id
                    WHERE orvexa_user_action.r_module_id IN :module:
                    AND orvexa_user_action.reference_id = :r_sprint_id:
                    ORDER BY orvexa_user_action.action_date DESC";
          $result = $this->query($query, [
               "module" => $param["module"],
               "r_sprint_id" => $param["sprint_id"]
          ]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Gokul
      * @param $args
      * @return int|bool
      * Method to alter sprint date from the meeting module
      */
     public function alterSprintDate($args): int|bool
     {
          $query = "UPDATE orvexa_sprint
                  SET r_sprint_duration_id = :duration_id:,
                    start_date = :start:,
                    end_date = :end:,
                    r_user_id_updated = :uId:,
                    updated_date = now()
                    WHERE sprint_id = :id:";
          $result = $this->query($query, [
               'id' => $args['sprintId'],
               'duration_id' => $args['duration'],
               'start' => $args['startDate'],
               'end' => $args['endDate'],
               'uId' => $args['userId']
          ]);
          return $result;
     }


     /**
      * @author Gokul
      * @param $args
      * @return int|bool
      * Method to alter sprint status from the meeting module
      */
     public function alterSprintStatusById($args): int|bool
     {
          $query = "UPDATE orvexa_sprint
                  SET r_module_status_id = :statusId:,
                    r_user_id_updated = :uId:,
                    updated_date = now()
                    WHERE sprint_id = :id:";
          $result = $this->query($query, [
               'id' => $args['sprintId'],
               'statusId' => $args['status'],
               'uId' => $args['userId']
          ]);
          return $result;
     }

     /**
      * @author Sivabalan
      * @return array
      * Method to alter sprint status from the meeting module
      */
     public function fetchRunningSprints(): array
     {
          $query = "SELECT sprint_id
                    FROM orvexa_sprint
                    WHERE r_module_status_id = :id:";
          $result = $this->query($query, ['id' => 20]);
          if ($result->getNumRows() > 0) {
               return $result->getResultArray();
          }
          return [];
     }

     /**
      * @author Sivabalan
      * @param array $sprintIds
      * Method to alter sprint status from the meeting module
      */
     public function updateSprintTaskRunning($sprintIds)
     {
          $placeholders = implode(',', array_fill(0, count($sprintIds), '?'));
          $status[] = 2;
          $param = array_merge($status, $sprintIds, [8]);
          $query = "UPDATE orvexa_task
                    JOIN orvexa_sprint_task
                    ON orvexa_task.task_id = orvexa_sprint_task.r_task_id
                    SET orvexa_task.task_status = ?
                    WHERE orvexa_sprint_task.r_sprint_id IN ({$placeholders})
                    AND orvexa_task.task_status = ?";
          $this->query($query, $param);
     }

     /**
      * @author Sivabalan
      * @param int $taskId
      * Method to fetch sprint id from orvexa_sprint_task
      */
     public function getSprintId($taskId)
     {
          $query = "select orvexa_sprint_task.r_sprint_id from orvexa_sprint_task inner join orvexa_task on orvexa_sprint_task.r_task_id=orvexa_task.task_id where 
           external_reference_task_id=:task_id:";
          $result = $this->query($query, ["task_id" => $taskId]);
          if ($result->getNumRows() > 0) {
               $sprintId = $result->getResultArray();
               $sprintId = $sprintId[0]['r_sprint_id'];
               return $sprintId;
          }
          return 0;
     }

     /**
      * @author Sivabalan
      * @param int $sprintId
      * Method to update estimated hours in orvexa_sprint
      */
     public function updatesprintEstimationTime($sprintId)
     {
          $query = "select SUM(IFNULL(orvexa_task.estimated_hours, 0)) AS total_estd_hours
     from orvexa_task inner join orvexa_sprint_task on orvexa_task.task_id=orvexa_sprint_task.r_task_id
     where orvexa_sprint_task.r_sprint_id=:sprint_id: and orvexa_sprint_task.is_deleted ='N' ";
          $resultTemp = $this->query($query, ["sprint_id" => $sprintId]);
          if ($resultTemp) {
               $result = $resultTemp->getResultArray();
               $estimated_hours = $result[0]['total_estd_hours'];
          }
          $query = "UPDATE orvexa_sprint set estimated_hrs = :estimated_hours: where sprint_id=:sprint_id:";
          $result = $this->query($query, ["estimated_hours" => $estimated_hours, "sprint_id" => $sprintId]);
          return 0;
     }
}