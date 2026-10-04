<?php

/**
 * @author MURUGADASS,Abinandhan,Samuel,Vigneshwari
 *
 * @modified-by MURUGADASS
 * @created-date 04-07-2024
 * @modified-date 31-07-2024
 * @description: This controller is controlling the overall Backlog module
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Response;

class TaskController extends BaseController
{
    protected $backlogModel;
    protected $backlogItemModel;
    protected $userStoryModel;
    protected $taskModel;
    protected $sprintModel;

    public function __construct()
    {
        $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $this->backlogItemModel = model(\App\Models\Backlog\BacklogItemModel::class);
        $this->userStoryModel = model(\App\Models\Backlog\UserStoryModel::class);
        $this->taskModel = model(\App\Models\Backlog\TaskModel::class);
        $this->sprintModel = model(\App\Models\SprintModel::class);
    }

    /**
     * @author samuel
     * @return string
     * Purpose: The purpose of this function is to get the tasks details of a userstory and return it to the view
     */

     public function tasks(): string
     {
         // Check if a 'usid' (user story ID) is provided in the request
         if ($this->request->getGet('usid')) {
             // Define breadcrumbs for navigation
             $breadcrumbs = [
                 'Products' => ASSERT_PATH . 'backlog/productbacklogs',
                 'Backlog items' => ASSERT_PATH . 'backlog/backlogitems?pid=' . $this->request->getget('pid'),
                 'User stories' => ASSERT_PATH . 'backlog/userstories?pid=' . $this->request->getget('pid') . '&pblid=' . $this->request->getget('pblid'),
                 'tasks' => '',
             ];
     
             // Authenticate user with provided IDs
             if (authenticateUser($this->request->getget('pid'), $this->request->getGet('pblid'), $this->request->getGet('usid'))) {
                 // Retrieve details for the specified backlog item
                 $backlogItem = $this->backlogModel->getBacklogDetails(['id'=>$this->request->getget('pblid')]);
     
                 // Prepare data for the view
                 $data = [
                     'product_id' => $this->request->getget('pid'), // Product ID
                     'backlog_item_id' => $this->request->getget('pblid'), // Backlog item ID
                     'product_name' => $backlogItem['product_name'], // Product name
                     'backlog_item_name' => $backlogItem['backlog_item_name'], // Backlog item name
                     'user_story_id' => $this->request->getget('usid'), // User story ID
                     'user_story' => ($this->userStoryModel->getUserStoryById($this->request->getget('usid')))[0], // User story details
                     // 'tasks_details' => $this->backlogModel->getTasks($this->request->getget('usid')), // Optional task details (commented out)
                     'task_count' => ($this->taskModel->countTask($this->request->getget('usid'))), // Count of tasks
                     'trackers' => $this->taskModel->getTrackers(), // List of trackers
                     'users' => $this->sprintModel->getMembersByProduct($this->request->getget('pid')), // List of users by product
                     'status' => $this->taskModel->getTaskStatus(), // Task status options
                 ];
     
                 // Render the 'tasks' view with the prepared data and breadcrumbs
                 return $this->template_view('backlog/tasks', $data, 'Tasks', $breadcrumbs);
             }
         }
     
         // Define breadcrumbs for cases where no user story ID is provided
         $breadcrumbs = [
             'Products' => ASSERT_PATH . 'backlog/productbacklogs',
         ];
     
         // Render the 'No data' view indicating no available data
         return $this->template_view('dashboard/NodataView', null, 'No data', $breadcrumbs);
     }
     

    /**
     * @author samuel
     * @return response
     * Purpose: The purpose of this function is to add the tasks details to the tasks table
     */

     public function addTasks($pId, $pblid, $userStoryId): Response
     {
         // Get assignee ID, defaulting to the current session's employee ID if not provided
         $assigneeId = (empty($this->request->getPost('assignee_id')) ? session('employee_id') : $this->request->getPost('assignee_id'));
     
         // Retrieve optional task details (start date, end date, estimated hours, completed percentage)
         $startDate = !empty($this->request->getPost('start_date')) ? $this->request->getPost('start_date') : null;
         $endDate = !empty($this->request->getPost('end_date')) ? $this->request->getPost('end_date') : null;
         $estimatedHours = !empty($this->request->getPost('estimated_hours')) ? $this->request->getPost('estimated_hours') : null;
         $completedPercentage = !empty($this->request->getPost('completed_percentage')) ? $this->request->getPost('completed_percentage') : 0;
     
         // Convert priority from letter (L/M/H) to numeric value
         $priority = $this->request->getPost('priority');
         if ($priority == 'L')
             $priority = 1;
         if ($priority == 'M')
             $priority = 2;
         if ($priority == 'H')
             $priority = 3;
     
         // Get tracker ID and customer information for the user story
         $tracker = $this->taskModel->getTrackerId($pblid);
         $customer = $this->backlogModel->getCustomer($userStoryId);
     
         // Prepare the task data array
         $task = [
             'user_story_id' => $userStoryId, // User story ID
             'project_id' => $pId, // Project ID
             'task_title' => $this->request->getPost('task_title'), // Task title
             'task_description' => $this->request->getPost('task_description'), // Task description
             'task_priority' => $priority, // Task priority (numeric)
             'task_assignee' => $assigneeId, // Task assignee ID
             'task_statuses' => $this->request->getpost('task_status'), // Task status
             'task_tracker' => $tracker[0]['r_tracker_id'], // Task tracker ID
             'author_id' => session('employee_id'), // Author ID (current session's employee ID)
             'start_date' => $startDate, // Task start date
             'end_date' => $endDate, // Task end date
             'created_on' => date("Y-m-d H:i:s"), // Task creation timestamp
             'updated_on' => date("Y-m-d H:i:s"), // Task update timestamp
             'r_user_story_id' => $userStoryId, // User story reference ID
             'priority' => $this->request->getPost('priority'), // Task priority (raw value)
             'assignee_id' => $assigneeId, // Assignee ID
             'task_status' => $this->request->getpost('task_status'), // Task status
             'tracker_id' => $tracker[0]['r_tracker_id'], // Tracker ID
             'estimated_time' => $estimatedHours, // Estimated hours for the task
             'completed_percentage' => $completedPercentage, // Task completion percentage
             'created_date' => date("Y-m-d H:i:s"), // Task creation date
             'updated_date' => date("Y-m-d H:i:s"), // Task update date
         ];
     
         // Tasks are managed locally; no external external issue is created.
     
         // Prepare task data for validation and storage in the local system
         $task_data = $this->request->getpost();
         $task_data['estimated_hours'] = $estimatedHours;
         $task_data['tracker_id'] = $tracker[0]['r_tracker_id'];
         $task_data['author_id'] = session('employee_id');
         $task_data['created_on'] = date("Y-m-d H:i:s");
         $task_data['updated_on'] = date("Y-m-d H:i:s");
         $task_data['created_date'] = date("Y-m-d H:i:s");
         $task_data['updated_date'] = date("Y-m-d H:i:s");
         $task_data['start_date'] = $startDate;
         $task_data['end_date'] = $endDate;
         $task_data['external_reference_task_id'] = null;
         $task_data['r_user_story_id'] = $userStoryId;
         $task_data['completed_percentage'] = $completedPercentage;
         $task_data['assignee_id'] = $assigneeId;
     
         // Validate the task data
         $validationErrors = $this->hasInvalidInput($this->taskModel, $task_data);
         if ($validationErrors !== true) {
             return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);
         }
     
         // Determine the priority string based on the numeric value
         $priority = '';
         if (strtolower($task['task_priority']) === 'l') {
             $priority = 'Low';
         } else if (strtolower($task['task_priority']) === 'm') {
             $priority = 'Medium';
         } else {
             $priority = "High";
         }
     
         // Insert the task data into the local system
         $result = $this->taskModel->insertTasks($task_data);
         if ($result) {
             // Log the action of task creation
             $actionData = 'Task ID : ' . $result . " is added";
             $action = formActionData(__FUNCTION__, $pblid, $pId, $actionData);
             Events::trigger('log_actions', $action);
             return $this->response->setJSON(['success' => true, 'message' => 'Task Created Successfully']);
         }
     
         // Return an error if the task creation failed
         return $this->response->setJSON(['successs' => false, 'message' => 'Failed to add the task']);
     }
     

    /**
     * @author samuel
     * @return response
     * @param $task id of the task which is updated
     * Purpose: The purpose of this function is to update the tasks details of a userstory and return it to the view
     */
    public function updateTasks($pId, $pblid, $taskId): Response
    {
        // Convert task priority from letter to number
        $priority = $this->request->getPost('task_priority');
        if ($priority == 'L') {
            $priority = 1;
        } elseif ($priority == 'M') {
            $priority = 2;
        } elseif ($priority == 'H') {
            $priority = 3;
        }

        // Get the completed percentage and set to 0 if not provided
        $done_ratio = $this->request->getPost('completed_percentage') ?? 0;

        // Get the estimated hours and set to 0 if not provided
        $est_time = $this->request->getPost('estimated_hours') ?? 0;

        // Retrieve the tracker ID related to the backlog item
        $tracker = $this->taskModel->getTrackerId($pblid);

        // Fetch the task details before updating for comparison
        $before = $this->taskModel->getTaskbyTaskId($taskId)[0];

        // Prepare the data to update the task
        $updateTask = [
            'task_id' => $taskId,
            'task_title' => $this->request->getPost('task_title'),
            'task_description' => $this->request->getPost('task_description'),
            'task_tracker' => $tracker[0]['r_tracker_id'],
            'task_statuses' => $this->request->getPost('task_status'),
            'task_priority' => $priority,
            'task_assignee' => $this->request->getPost('assignee_id'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'completed_percentage' => $done_ratio,
            'estimated_time' => $est_time,
            'estimated_hours' => $est_time,
            'author_id' => session('employee_id'),
            'updated_on' => date("Y-m-d H:i:s"),
            'priority' => $this->request->getPost('priority'),
            'tracker_id' => $tracker[0]['r_tracker_id'],
            'created_date' => date("Y-m-d H:i:s"),
            'updated_date' => date("Y-m-d H:i:s"),
        ];

        // Tasks are managed locally; no external external issue is updated.

        // Prepare data for validation and updating in the database
        $task_data = $this->request->getPost();
        $task_data = array_merge($task_data, [
            'estimated_hours' => $est_time,
            'tracker_id' => $tracker[0]['r_tracker_id'],
            'author_id' => session('employee_id'),
            'created_on' => date("Y-m-d H:i:s"),
            'updated_on' => date("Y-m-d H:i:s"),
            'created_date' => date("Y-m-d H:i:s"),
            'updated_date' => date("Y-m-d H:i:s"),
            'completed_percentage' => $done_ratio,
            'task_id' => $taskId,
        ]);

        // Validate the task data
        $validationErrors = $this->hasInvalidInput($this->taskModel, $task_data);
        if ($validationErrors !== true) {
            return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);
        }

        // Update the task in the database
        $result = $this->taskModel->updateTaskById($task_data);

        // Fetch the task details after updating for comparison
        $after = $this->taskModel->getTaskbyTaskId($taskId)[0];

        // Headers for change tracking
        $header = [
            'Task Title',
            'Description',
            'Tracker',
            'Status',
            'Priority',
            'Assignee',
            'Start Date',
            'End Date',
            'Completed Percentage',
            'Estimated Hours',
        ];

        // Combine keys and headers for comparison
        $header = array_combine(array_keys($after), $header);
        $keys = array_intersect_key($before, $after);
        $differentValues = array_diff_assoc($keys, $after);
        $differentKeys = array_keys($differentValues);

        $changes = '';

        // Track the changes if any
        if ($after != $before) {
            $changes = 'In Task ' . $updateTask['task_title'] . ', ';
        }

        // Record each changed field with the before and after values
        foreach ($differentKeys as $value) {
            if (empty($before[$value])) {
                $before[$value] = 'Empty';
            }
            $changes .= '<br>' . $header[$value] . ' Updated From ' . '<b>' . $before[$value] . '</b>' . ' to ' . '<b>' . $after[$value] . '</b>';
        }

        // Update sprint estimation time if applicable
        $sprint_id = $this->sprintModel->getSprintId($task_data['task_id']);
        if ($sprint_id) {
            $this->sprintModel->updateSprintEstimationTime($sprint_id);
        }

        // Log the action and return success message
        if ($result) {
            $actionData = (empty($changes)) ? "No changes" : trim($changes, " |");
            $action = formActionData(__FUNCTION__, $pblid, $pId, $actionData);
            Events::trigger('log_actions', $action);
            return $this->response->setJSON(['success' => true, 'message' => 'Task updated successfully']);
        }

        // Return failure message if update was not successful
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update task']);
    }


    /**
     * @author Murugadass
     * @return Response
     * @param int $task id of a particular task
     * Purpose: The purpose of this function is to get the particular task for update view
     */
    public function getTaskById($taskId): Response
    {
        // Fetch task details from the database by task ID
        $taskDetailsById = $this->taskModel->getTaskById($taskId);

        // Return the task details as a JSON response
        return $this->response->setJSON($taskDetailsById);
    }

    /**
     * @author samuel
     * @return Response
     * @param int $task id of a particular task
     * Purpose: The purpose of this function is to get the tasks details of a userstory and return it to the view
     */

     public function deleteTasks($pid, $pblid, $tId): Response
     {
         // Delete the task from the local orvexa_task table.
         $res = $this->backlogItemModel->deleteItem($tId, ['orvexa_task', 'external_reference_task_id']);
     
         // Check if the deletion was successful
         if ($res) {
             // Prepare action data to log the deletion event
             $actionData = 'Task ID: ' . $tId . ' is deleted';
             
             // Format the action data for logging
             $action = formActionData(__FUNCTION__, $pblid, $pid, $actionData);
     
             // Trigger an event to log the deletion action
             Events::trigger('log_actions', $action);
     
             // Return a JSON response indicating successful deletion
             return $this->response->setJSON(['success' => true]);
         }
     
         // If the deletion failed, return a JSON response indicating failure
         return $this->response->setJSON(['success' => false]);
     }

     public function filterTasks()
     {
         // Retrieve the JSON input from the request body and decode it as an associative array
         $jsonInput = $this->request->getJSON(true);
     
         // Check if a filter exists within the JSON input, otherwise set it to null
         $filter = isset($jsonInput['filter']) ? $jsonInput['filter'] : null;
     
         // If a filter is provided, proceed with filtering tasks
         if ($filter) {
             // Fetch the filtered tasks data based on the provided filter
             $filteredData = $this->taskModel->getTasks($filter);
     
             // Check if the filtered data is an array
             if (is_array($filteredData)) {
                 // Loop through each task in the filtered data to adjust and format fields
                 foreach ($filteredData as $key => $value) {
                     // Translate priority values from codes ('H', 'M', 'L') to readable strings ('High', 'Medium', 'Low')
                     if ($value['priority'] == 'H') {
                         $filteredData[$key]['priority'] = 'High';
                     } elseif ($value['priority'] == 'M') {
                         $filteredData[$key]['priority'] = 'Medium';
                     } else {
                         $filteredData[$key]['priority'] = 'Low';
                     }
     
                     // Ensure start_date is set, otherwise assign a default value of ' -'
                     if (!isset($value['start_date'])) {
                         $filteredData[$key]['start_date'] = ' -';
                     }
     
                     // Ensure end_date is set, otherwise assign a default value of ' -'
                     if (!isset($value['end_date'])) {
                         $filteredData[$key]['end_date'] = ' -';
                     }
     
                     // Ensure completed_percentage is set, defaulting to '0' if the value is 0
                     if ($value['completed_percentage'] == 0) {
                         $filteredData[$key]['completed_percentage'] = '0';
                     }
     
                     // Ensure estimated_hours is set, otherwise assign a default value of '0'
                     if (!isset($value['estimated_hours'])) {
                         $filteredData[$key]['estimated_hours'] = '0';
                     }
                 }
             }
     
             // Return the filtered and formatted data as a JSON response
             return $this->response->setJSON([
                 'status' => 'success',
                 'data' => $filteredData,
             ]);
         } else {
             // If no filter is provided, return an error message as a JSON response
             return $this->response->setJSON([
                 'status' => 'error',
                 'message' => 'Invalid filter data',
             ]);
         }
     }
}