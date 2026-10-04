<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SprintModelConfig extends BaseConfig
{
     //For getReadyForSprintByProduct()
     public $userStoryReadyForSprint = array(17, 18);
     public $backlogReadyForSprint = array(8, 9, 10);
     public $taskReadyForSprint = array(1, 16);

     //For getTaskSprint()
     public $taskTaskSprint = array(1, 2);
     public $userStoryTaskSprint = array(17, 18);

     //For getTaskForEdit()
     public $userStoryTaskForEdit = array(17, 18);

     //For getSprintHistory()
     public $moduleHistory = array(8, 9, 10, 11, 12, 19, 20, 21);

     //For fetching notes of daily scrum
     public $dailyScrumNotes = array(1, 2);

     //For fetching notes of dreview
     public $reviewNotes = array(3, 4, 5, 6, 7, 8, 9);

     //For fetching notes of retrospective
     public $retrospectiveNotes = array("pros" => 10, "cons" => 11, "lns" => 12);

     //sprint Status for Dashboard controller
     public $sprintStatuses = [
          'ongoing' => [20, 21, 22],
          'upcoming' => 19,
          'completed' => 23
     ];

     //backlog Status for Dashboard controller
     public $backlogStatuses = [
          'completed_backlogs' => 12,
          'in_progress_backlogs' => [9, 10],
          'on_hold_backlogs' => 11,
          'not_started_backlogs' => [1, 2, 3, 4, 5, 6, 7, 8]
     ];

     //userStory Status for Dashboard controller
     public $userStoryStatuses = [
          'completed' => 18,
          'in_progress' => [15, 17],
          'not_started' => [14, 13, 16, 31, 32, 33, 34]
     ];

     //userRoles for AuthController controller and admin controller
     public $userRoles = [
          'developer' => [
               'Software Trainee',
               'Software Engineer',
               'Sr.Software Engineer',
               'Team Lead'
          ],
          'project_manager' => ['Project Manager'],
          'product_manager' => ['Product Manager'],
          'business_analyst' => ['Business Analyst']
     ];

     //userRoles Id for AuthController controller and admin controller
     public $userRoleId = [
          'developer' => 8,
          'project_manager' => 2,
          'product_manager' => 3,
          'business_analyst' => 6,
          'orvexa_admin' => 1
     ];

     //Ongoing sprint status
     public $sprintStatus = [
          'ongoing' => [20]
     ];

     //Customer type and name
     public $customers = [
          "type" => "IssueCustomField",
          "name" => "Customer"
     ];

     //sprint planning status for notification controller
     public $sprintPlannedStatus = [29];

     //Pending task status for Dashboard Controller
     public $pendingTaskStatuses = [1, 2, 4, 8, 16];

     //Task priorities
     public $priorities = [1, 2, 3];

     //User story custom field id
     public $customFieldId = 52;

     public $statusNames = [
          "Backlog" => 5,
          "Sprint" => 8,
          "Meet" => 13
     ];

     //custom time log  issues id for the meeting time log 
     public $taskDatas = [
          "task_priority" => 2,
          "task_statuses" => 2,
          "task_tracker" => 15,
     ];

}
