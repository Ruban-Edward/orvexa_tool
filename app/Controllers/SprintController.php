<?php

/**
 * SprintController.php
 * 
 * @category   Controller
 * @author     Jeril,Jeeva,Vishva,Sivabalan
 * @created    04 July 2024
 * @purpose This controller is for controlling the overall sprint module 
 */

namespace App\Controllers;

use App\Models\SprintModel;
use CodeIgniter\HTTP\Response;
use DateTime;
use Config\SprintModelConfig;
use App\Services\EmailService;
use App\Services\NotesService;
use Dompdf\Dompdf;
use Dompdf\Options;

class SprintController extends BaseController
{
     protected $backlogModel;
     protected $sprintModel;
     protected $historyModel;
     protected $currentDateTime;
     protected $currentDate;
     protected $userId;
     protected $notesModelObj;
     protected $sprintRestrospectiveObj;
     protected $scrumSprintModelobj;
     protected $sprintTaskModelobj;
     protected $sprintPlanningObj;
     protected $scrumDiaryObj;
     protected $sprintReviewObj;
     protected $sprintUserModelobj;
     protected $codeReviewObj;
     protected $sprintModelConfig;
     protected $emailService;
     protected $notesService;
     protected $taskModelObj;


     /**
      * Declaring object for the model classes
      */
     public function __construct()
     {
          $this->sprintModelConfig = new SprintModelConfig();
          $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
          $this->sprintModel = model(SprintModel::class);
          $this->historyModel = model(\App\Models\HistoryModel::class);
          $this->notesModelObj = model(\App\Models\Sprint\NoteModel::class);
          $this->codeReviewObj = model(\App\Models\Sprint\CodeReviewModel::class);
          $this->sprintPlanningObj = model(\App\Models\Sprint\SprintPlanning::class);
          $this->sprintUserModelobj = model(\App\Models\Sprint\SprintUser::class);
          $this->sprintTaskModelobj = model(\App\Models\Sprint\SprintTask::class);
          $this->scrumSprintModelobj = model(\App\Models\Sprint\ScrumSprintModel::class);
          $this->scrumDiaryObj = model(\App\Models\Sprint\DailyScrumModel::class);
          $this->sprintReviewObj = model(\App\Models\Sprint\SprintReview::class);
          $this->sprintRestrospectiveObj = model(\App\Models\Sprint\SprintRetrospective::class);
          $this->taskModelObj = model(\App\Models\Backlog\TaskModel::class);
          $this->emailService = new EmailService();
          $this->notesService = new NotesService();

          $this->currentDateTime = Date('Y-m-d H:i:s');
          $this->currentDate = Date("Y-m-d");
          $this->userId = session()->get('employee_id');

          $this->updateSprintStatusToRun();
     }

     function isValidDate($date)
     {
          return (strtotime($date) !== false);
     }

     public function formatDate($inputDate)
     {
          if ($this->isValidDate($inputDate)) {
               $date = new DateTime($inputDate);
               $formattedDate = $date->format('M j, Y');
               return $formattedDate;
          } else {
               return $inputDate;
          }

     }
     public function updateSprintStatusToRun()
     {
          $this->sprintModel->updateSprintRunning($this->currentDate);

          $sprintIds = $this->sprintModel->fetchRunningSprints();

          if (count($sprintIds) > 0) {
               $this->sprintModel->updateSprintTaskRunning($sprintIds);
          }
     }

     /**
      * Method to display the details of the sprint created in the sprint list page
      * @return Response|string
      */
     public function getSprintList(): Response|string
     {
          $breadcrumbs = [
               'Sprints' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint list' => ''
          ];
          $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
          $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
          $offset = ($page - 1) * $limit;
          $uId = $this->userId;
          $userProducts = $this->backlogModel->getUserProduct($uId);
          $userProducts = array_column($userProducts, 'product_id');
          if (count($userProducts) == 0) {
               return $this->template_view('dashboard/NodataView', 'Product', 'No Products');
          }
          //$totalRows=$this->sprintModel->countSprintList($userProducts,$uId);
          $tasks = $this->sprintModel->getSprintList($userProducts, $uId, $limit, $offset, null, null);
          $totalRows = $tasks[1];
          $tasks = $tasks[0];
          if (empty($tasks)) {
               return $this->template_view('sprint/sprintError', $viewData = null, 'Sprint list', $breadcrumbs);
          }

          // Prepare JSON response
          $response = [
               'status' => 'success',
               'data' => $tasks,
               //'limit' => $limit,
               'totalrows' => $totalRows,
               'current_page' => $page,
               //'per_page' => $limit,// Assuming $tasks is an array of data to send
          ];

          // If it's an AJAX request, send JSON response
          if ($this->request->isAJAX()) {
               return $this->response->setJSON($response);
          }

          //$totalRows = $this->sprintModel->countSprintList($userProducts);
          $statusList = $this->sprintModel->getSprintStatus();

          foreach ($tasks as $key => $value) {
               $customerList[] = $value['customer'];
               $productList[] = $value['product'];
               $sprintDuration[] = $value['duration'];
               $sprintStatus[] = $value['sprint_status'];
               $sprintName[] = $value['sprint_name'];
          }
          $filterList = array($customerList, $productList, $sprintDuration, $sprintStatus, $sprintName);

          foreach ($filterList as $key => $value) {
               $filterList[$key] = array_unique($value);
          }
          $viewData = [
               'tasks' => $tasks,
               'user_id' => $uId,
               'records' => $tasks,
               'count' => count($tasks),
               'filter_values' => $filterList,
               'statusList' => $statusList,
               'totalPages' => $totalRows
          ];
          return $this->template_view('sprint/sprintList', $viewData, 'Sprint list', $breadcrumbs);

     }

     /**
      * Retrieves a selection list of sprints based on the user's products and provided parameters.
      *
      * @return string JSON encoded data containing the selection list of sprints.
      */

     public function getSprintSelectionList()
     {
          $uId = $this->userId;
          $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
          $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 10;
          $offset = ($page - 1) * $limit;
          //     Fetch user products from BacklogModel
          $userProductsMultiDim = $this->backlogModel->getUserProduct($uId);
          $userProducts = array_column($userProductsMultiDim, 'product_id');
          $params = $_POST['formData'];
          $params = $this->fetchMultipleItems($params);

          $tasks = $this->sprintModel->getSprintList($userProducts, $uId, $limit, $offset, null, $params);
          $totalRows = $tasks[1];
          $tasks = $tasks[0];

          // Send JSON response
          $response = [
               'status' => 'success',
               'data' => $tasks,
               'limit' => $limit,
               'totalrows' => $totalRows,
               'current_page' => $page,
               'per_page' => $limit,// Assuming $tasks is an array of data to send
          ];
          return json_encode($response);
     }

     /**
      * Formats a list of columns for SQL queries by adding single quotes around each item.
      *
      * @param array $columnsList An array of columns or arrays of columns to be formatted.
      * @return array The formatted array with single quotes around each column name.
      */
     public function fetchMultipleItems($columnsList)
     {
          // Iterate through each item in the columns list
          foreach ($columnsList as $key => $columns) {
               // Check if the current item is an array
               if (is_array($columns)) {
                    // Add single quotes around each value in the array
                    $columnsList[$key] = array_map(function ($value) {
                         return "'" . $value . "'";
                    }, $columns);
               } else if ($columns != "") {
                    // Add single quotes around a single column if it's not an array and not empty
                    $columnsList[$key] = "'" . $columns . "'";
               }
               // If the current item is now an array, implode it into a comma-separated string
               if (is_array($columnsList[$key])) {
                    $columnsList[$key] = implode(',', $columnsList[$key]);
               }
          }
          return $columnsList;
     }

     /**
      * Retrieves tasks for a given product ID and formats them for response.
      *
      * @param int $productId The ID of the product to retrieve tasks for.
      * @return string JSON encoded response containing the success status and task data.
      */
     public function getProductTasks($productId)
     {
          // Check if product ID is provided
          if (!$productId) {
               return json_encode(['success' => false, 'message' => 'Product ID is required']);
          }
          $param = [
               "userStory" => $this->sprintModelConfig->userStoryReadyForSprint,
               "backlog" => $this->sprintModelConfig->backlogReadyForSprint,
               "task" => $this->sprintModelConfig->taskReadyForSprint,
               "productId" => $productId
          ];
          // Fetch tasks that are ready for sprint by product ID
          $tasks = $this->sprintModel->getReadyForSprintByProduct($param);

          // Format the fetched tasks for response
          $viewData = $this->formatTasks($tasks);
          $formattedTasks = $viewData;

          // Return the response as a JSON encoded string
          return json_encode(['success' => true, 'data' => $formattedTasks]);
     }
     /**
      * Formats tasks into a structured array grouped by backlog item, epic, and user story.
      *
      * @param array $tasks The array of tasks to format.
      * @return array The structured array of formatted tasks.
      */

     private function formatTasks($tasks)
     {
          $viewData = [];
          $backlogItems = [];

          // Iterate through each task
          foreach ($tasks as $item) {
               $backlogItemName = $item["backlog_item_name"];
               $backlogPriority = $item["priority"];
               $epicName = $item["epic_name"];
               $userStoryId = $item["user_story_id"];
               $userStoryName = $item["user_story"];
               $taskId = $item["task_id"];
               $taskDescription = $item["task_title"];
               $taskAssignee = $item["assignee_name"];
               $taskStatus = $item["name"];
               // Check if the backlog item already exists, if not, initialize it
               if (!isset($backlogItems[$backlogItemName])) {
                    $backlogItems[$backlogItemName] = [
                         "name" => $backlogItemName,
                         "priority" => $backlogPriority,
                         "epics" => []
                    ];
               }
               // Check if the epic already exists under the backlog item, if not, initialize it
               if (!isset($backlogItems[$backlogItemName]["epics"][$epicName])) {
                    $backlogItems[$backlogItemName]["epics"][$epicName] = [
                         "name" => $epicName,
                         "userStories" => []
                    ];
               }
               // Check if the user story already exists under the epic, if not, initialize it
               if (!isset($backlogItems[$backlogItemName]["epics"][$epicName]["userStories"][$userStoryName])) {
                    $backlogItems[$backlogItemName]["epics"][$epicName]["userStories"][$userStoryName] = [
                         "userStoryId" => $userStoryId,
                         "name" => $userStoryName,
                         "tasks" => []
                    ];
               }
               // Add the task to the respective user story
               $backlogItems[$backlogItemName]["epics"][$epicName]["userStories"][$userStoryName]["tasks"][] = array($taskId, $taskDescription, $taskStatus, $taskAssignee);
          }
          /**
           * For each backlog item, add the corresponding epics and user stories
           * to the view data array.
           */
          foreach ($backlogItems as $backlogItem) {
               $epicList = [];
               foreach ($backlogItem["epics"] as $epic) {
                    $userStoryList = [];
                    foreach ($epic["userStories"] as $userStory) {
                         $userStoryList[] = [
                              "id" => $userStory["userStoryId"],
                              "name" => $userStory["name"],
                              "tasks" => $userStory["tasks"]
                         ];
                    }
                    $epicList[] = [
                         "name" => $epic["name"],
                         "userStories" => $userStoryList
                    ];
               }
               $viewData["backlogItems"][] = [
                    "name" => $backlogItem["name"],
                    "priority" => $backlogItem["priority"],
                    "epics" => $epicList
               ];
          }
          return $viewData;
     }

     private function formatTaskForTable($tasks)
     {
          $viewData = [];
          $backlogItems = [];
          // Organize tasks into backlog items, epics, and user stories

          foreach ($tasks as $item) {
               $backlogItemName = $item["backlog_item_name"];
               $epicName = $item["epic_name"];
               $userStoryName = $item["user_story"];
               $userStoryId = $item['userstory_id'];
               $taskId = $item["task_id"];
               $tasktitle = $item["task_title"];
               $taskStatus = $item["task_status"];
               $taskCompletedPercentage = $item["completed_percentage"];
               if (!isset($backlogItems[$backlogItemName])) {
                    $backlogItems[$backlogItemName] = [
                         "backlog" => $backlogItemName,
                         "epics" => []
                    ];
               }
               if (!isset($backlogItems[$backlogItemName]["epics"][$epicName])) {
                    $backlogItems[$backlogItemName]["epics"][$epicName] = [
                         "epic" => $epicName,
                         "userStories" => []
                    ];
               }
               if (!isset($backlogItems[$backlogItemName]["epics"][$epicName]["userStories"][$userStoryName])) {
                    $backlogItems[$backlogItemName]["epics"][$epicName]["userStories"][$userStoryName] = [
                         "userStory" => $userStoryName,
                         "userStoryId" => $userStoryId,
                         "tasks" => []
                    ];
               }
               $backlogItems[$backlogItemName]["epics"][$epicName]["userStories"][$userStoryName]["tasks"][] = array("taskId" => $taskId, "task" => $tasktitle, "status" => $taskStatus, "percentage" => $taskCompletedPercentage);
          }
          // Structure the view data for sprint tasks
          foreach ($backlogItems as $backlogItem) {
               $epicList = [];
               foreach ($backlogItem["epics"] as $epic) {
                    $userStoryList = [];
                    foreach ($epic["userStories"] as $userStory) {
                         $userStoryList[] = [
                              "userStory" => $userStory["userStory"],
                              "userStoryId" => $userStory['userStoryId'],
                              "tasks" => $userStory["tasks"]
                         ];
                    }
                    $epicList[] = [
                         "epic" => $epic["epic"],
                         "userStories" => $userStoryList
                    ];
               }
               $viewData[] = [
                    "backlog" => $backlogItem["backlog"],
                    "epics" => $epicList
               ];
          }
          return $viewData;
     }

     /**
      * Retrieves members for a given product ID.
      *
      * @param int $productId The ID of the product to retrieve members for.
      * @return string JSON encoded response containing the success status and member data.
      */
     public function getProductMembers($productId)
     {
          // Check if product ID is provided
          if (!$productId) {
               return json_encode(['success' => false, 'message' => 'Product ID is required']);
          }
          // Fetch members by product ID
          $users = $this->sprintModel->getMembersByProduct($productId);
          // Return the response as a JSON encoded string
          return json_encode(['success' => true, 'data' => $users]);
     }

     /**
      * Prepares data for the "Create Sprint" view, including breadcrumb navigation and various sprint-related information.
      *
      * @return string The rendered view for creating a sprint.
      */
     public function navCreateSprint(): string|response
     {
          // Define breadcrumb navigation
          $breadcrumbs = [
               'Sprints' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint list' => ASSERT_PATH . 'sprint/sprintlist',
               'Create sprint' => ''
          ];
          // Get the current user's ID
          $uId = $this->userId;
          /**
           * Fetch product name, customer name, and sprint duration from the user
           * and store them in the view data array.
           */
          $viewData['product'] = $this->backlogModel->getUserProduct($uId);
          $viewData['customer'] = $this->sprintModel->getCustomer();
          $viewData['sprintDuration'] = $this->sprintModel->getSprintDuration();
          $viewData['sprintActivity'] = $this->sprintModel->getSprintActivity();
          $viewData['status'] = $this->sprintModel->getSprintStatus();

          // Set the edit flag in the view data to 0 (not in edit mode)
          $viewData['edit'] = 0;

          // Return the rendered view for creating a sprint
          return $this->template_view('sprint/createSprint', $viewData, 'Create sprint', $breadcrumbs);
     }

     /**
      * Handles the creation of a new sprint, including validation and insertion of sprint details,
      * tasks, members, and planning settings.
      *
      * @return string JSON encoded response indicating success or failure.
      */
     public function createSprint(): string|response
     {
          // Collect data from the POST request
          $data = [
               'sprint_name' => trim((string) $this->request->getPost('sprintName')),
               'sprint_version' => trim((string) $this->request->getPost('sprintVersion')),
               'r_product_id' => $this->request->getPost('productName'),
               'r_customer_id' => $this->request->getPost('customer'),
               'r_sprint_duration_id' => $this->request->getPost('sprintDuration'),
               'start_date' => $this->request->getPost('startDate'),
               'end_date' => $this->request->getPost('endDate'),
               'sprint_goal' => $this->request->getPost('sprintGoal'),
               'created_date' => date('Y-m-d H:i:s'),
               'r_user_id_created' => $this->userId,
          ];
          $checkValidations = $this->hasInvalidInput($this->scrumSprintModelobj, $data);
          if ($checkValidations !== true) {
               return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
          }

          // ---- Business rules (checked BEFORE anything is written) -------------------------
          $start = \DateTime::createFromFormat('Y-m-d', (string) $data['start_date']);
          $end   = \DateTime::createFromFormat('Y-m-d', (string) $data['end_date']);
          if (! $start || ! $end || $start->format('Y-m-d') !== $data['start_date'] || $end->format('Y-m-d') !== $data['end_date']) {
               return $this->response->setJSON(['success' => false, 'message' => 'Please enter valid start and end dates.']);
          }
          if ($end < $start) {
               return $this->response->setJSON(['success' => false, 'message' => 'The sprint end date cannot be before its start date.']);
          }
          if ($this->sprintModel->sprintExists((int) $data['r_product_id'], $data['sprint_name'], $data['sprint_version'])) {
               return $this->response->setJSON(['success' => false, 'message' => 'A sprint with this name and version already exists for the product.']);
          }

          $selectedTasks = array_values(array_filter((array) $this->request->getPost('selectedTasks')));
          if (empty($selectedTasks)) {
               return $this->response->setJSON(['success' => false, 'message' => 'Please select tasks']);
          }
          $busy = $this->sprintModel->getTasksInOpenSprints($selectedTasks);
          if (! empty($busy)) {
               $names = array_unique(array_map(static fn ($r) => $r['sprint_name'] . ' - ' . $r['sprint_version'], $busy));
               return $this->response->setJSON(['success' => false, 'message' => count($busy) . ' of the selected task(s) already belong to another open sprint (' . implode(', ', $names) . ').']);
          }

          $selectedMembers = json_decode($this->request->getPost('selectedMembers') ?? '[]', true);
          $selectedMembers = is_array($selectedMembers) ? $selectedMembers : [];
          $selectedMembers[] = $this->userId;
          $selectedMembers = array_values(array_unique($selectedMembers));

          // ---- Everything below is one transaction: a sprint is created completely or not at all
          $db = \Config\Database::connect();
          $db->transStart();

          $sprintId = $this->scrumSprintModelobj->insertSprintDetails($data);

          foreach ($selectedTasks as $taskId) {
               $taskData = [
                    'r_sprint_id' => $sprintId,
                    'r_task_id' => $taskId,
               ];
               $checkValidations = $this->hasInvalidInput($this->sprintTaskModelobj, $taskData);
               if ($checkValidations !== true) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
               }
               $this->sprintTaskModelobj->insertSelectedTasks($taskData);
          }
          $this->sprintModel->updatesprintEstimationTime($sprintId);

          foreach ($selectedMembers as $memberId) {
               $memberData = [
                    'r_sprint_id' => $sprintId,
                    'r_user_id' => $memberId,
               ];
               $checkValidations = $this->hasInvalidInput($this->sprintUserModelobj, $memberData);
               if ($checkValidations !== true) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
               }
               $this->sprintUserModelobj->insertSelectedMembers($memberData);
          }

          $sprintSettings = $this->request->getPost('sprintSettings');
          if (! empty($sprintSettings[0]['activity'])) {
               $i = 0;
               foreach ($sprintSettings as $value1) {
                    $notes = [
                         "notes" => !empty($value1['comments']) ? $value1['comments'] : "No comments found",
                         "r_user_id" => $this->userId,
                         "created_date" => $this->currentDateTime
                    ];
                    $checkValidations = $this->hasInvalidInput($this->notesModelObj, $notes);
                    if ($checkValidations !== true) {
                         $db->transRollback();
                         return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
                    }
                    $sprintSettings[$i]['notes_id'] = $this->notesModelObj->insertNotes($notes);
                    $i++;
               }

               foreach ($sprintSettings as $value1) {
                    $settingsData = [
                         'r_sprint_id' => $sprintId,
                         'r_sprint_activity_id' => $value1['activity'],
                         'start_date' => !empty($value1['startDate']) ? $value1['startDate'] : $data['start_date'],
                         'end_date' => !empty($value1['endDate']) ? $value1['endDate'] : $data['start_date'],
                         'r_notes_id' => $value1['notes_id'],
                    ];
                    $this->sprintPlanningObj->insertSprintPlanning($settingsData);
               }
          }

          $userStories = $this->sprintModel->getUserStories($selectedTasks);
          $this->sprintModel->updateUserStory($userStories, 18);

          $this->historyModel->logActions([
               "r_user_id" => $this->userId,
               "r_action_type_id" => 5,
               "product_id" => 0,
               "r_module_id" => 8,
               "reference_id" => $sprintId,
               "action_data" => "<b>" . esc($data["sprint_name"]) . " - " . esc($data["sprint_version"]) . "</b>",
               "action_date" => $this->currentDateTime
          ]);

          $db->transComplete();
          if ($db->transStatus() === false) {
               return $this->response->setJSON(['success' => false, 'message' => 'The sprint could not be created. Nothing was saved, please try again.']);
          }

          // Notification e-mail is best-effort and must never turn a saved sprint into an error
          try {
               $sprintData = $this->sprintModel->getSprint($sprintId);
               $this->emailService->sendMail([
                    'email_id' => array_column($this->sprintModel->getSprintMember($sprintId), "email_id"),
                    'fileName' => 'createSprint',
                    'contents' => [
                         'subject' => "Creation of new sprint " . $sprintData[0]['sprint_name'] . " - " . $sprintData[0]['sprint_version'],
                         'sprint_name' => $sprintData[0]['sprint_name'],
                         'product_name' => $sprintData[0]['product_name'],
                         'customer_name' => $sprintData[0]['customer_name'],
                         'sprint_duration' => $sprintData[0]['sprint_duration'],
                         'sprint_version' => $sprintData[0]['sprint_version'],
                         'start_date' => $sprintData[0]['start_date'],
                         'end_date' => $sprintData[0]['end_date'],
                         'sprint_goal' => strip_tags($sprintData[0]['sprint_goal'])
                    ]
               ]);
          } catch (\Throwable $e) {
               log_message('error', 'Sprint created but the notification mail failed: ' . $e->getMessage());
          }

          return $this->response->setJSON(['success' => true, 'sprint_id' => $sprintId]);
     }

     /**
      * Displays the view for a specific sprint, including details, tasks, and daily scrums.
      *
      * @param int|null $id The ID of the sprint to view.
      * @return Response|string The rendered view or JSON response for AJAX requests.
      */
     public function navSprintView(): Response|string
     {
          $uId = $this->userId;
          // Define breadcrumbs for navigation
          $breadcrumbs = [
               'Sprints' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint list' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint view' => ''
          ];
          // Get the sprint ID from the GET request
          $viewData['id'] = $this->request->getGet('sprint_id');
          $reviewStatusInput = ['notes_type' => array_values($this->sprintModelConfig->reviewNotes), 'reference' => $viewData['id']];
          $sprintReviewStatus = $this->notesService->getNotes($reviewStatusInput);
          $viewData['sprintReviewStatus'] = count($sprintReviewStatus) == 0 ? 0 : 1;
          $retrospectiveStatusInput = ['notes_type' => array_values($this->sprintModelConfig->retrospectiveNotes), 'reference' => $viewData['id']];
          $sprintRetrospectiveStatus = $this->notesService->getNotes($retrospectiveStatusInput);
          $viewData['sprintRetrospectiveStatus'] = count($sprintRetrospectiveStatus) == 0 ? 0 : 1;
          // Fetch sprint details from the model
          $viewData['sprintDetails'] = $this->sprintModel->getSprint($viewData['id']);

          if (!empty($viewData['sprintDetails'])) {
               $viewData['sprintStatus'] = $this->sprintModel->getSprintStatus();
               $userProductsMultiDim = $this->backlogModel->getUserProduct($uId);
               $userProducts = array_column($userProductsMultiDim, 'product_id');
               if (in_array($viewData['sprintDetails'][0]['r_product_id'], $userProducts)) {
                    $viewData['sprintDetails'][0]['start_date'] = $this->formatDate($viewData['sprintDetails'][0]['start_date']);
                    $viewData['sprintDetails'][0]['end_date'] = $this->formatDate($viewData['sprintDetails'][0]['end_date']);
                    // Fetch sprint tasks from the model
                    $tasks = $this->sprintModel->getSprintTask($viewData['id']);

                    $viewData['sprintTask'] = $this->formatTaskForTable($tasks);
                    // Prepare response data
                    $response = [
                         'status' => 'success',
                         'data' => $viewData['sprintTask'], // Assuming $tasks is an array of data to send
                    ];

                    // If it's an AJAX request, send JSON response
                    if ($this->request->isAJAX()) {
                         return $this->response->setJSON($response);
                    }

                    $viewData['sprintPlanningStatus'] = $this->sprintModel->getSprintPlanningStatus();

                    // Fetch daily scrums from the model
                    $notesInput = ['dailyScrum' => 1, "sprintId" => $viewData['id']];
                    $dailyScrum = $this->notesService->getNotes($notesInput);

                    $viewData['dailyScrum'] = [];
                    // Process daily scrums into a structured format
                    foreach ($dailyScrum as $value) {
                         $formattedDate = $this->formatDate($value['added_date']);
                         $challenge = $value['r_notes_type_id'] == 2 ? 'Y' : 'N';
                         $note = $value['notes'];
                         $task = $value['task_title'];

                         if (!isset($viewData['dailyScrum'][$formattedDate])) {
                              $viewData['dailyScrum'][$formattedDate] = ['N' => [], 'Y' => []];
                         }
                         $viewData['dailyScrum'][$formattedDate][$challenge][] = [$task, $note];
                    }
                    $viewData['sprintActivity'] = $this->sprintModel->getSprintActivity();

                    $viewData['sprintReviewDate'] = $this->sprintModel->getSprintReviewDate($viewData['id']);
                    // Render the sprint view template with the prepared data
                    return $this->template_view('sprint/sprintView', $viewData, 'Sprint view', $breadcrumbs);
               } else {
                    return $this->template_view('dashboard/NodataView', $viewData = null, 'Sprint view', $breadcrumbs);
               }
          } else {
               return $this->template_view('dashboard/NodataView', $viewData = null, 'Sprint view', $breadcrumbs);
          }
     }

     /**
      * Fetches and returns the sprint planning details for a specific sprint.
      *
      * @param int|null $id The ID of the sprint to retrieve planning details for.
      * @return Response|string The rendered view or JSON response for AJAX requests.
      */
     public function getSprintPlanning()
     {
          // Get the sprint ID from the GET request
          $viewData['id'] = $this->request->getGet('sprint_id');
          // This is a data endpoint; a direct browser hit goes to the sprint page instead
          if (! $this->request->isAJAX()) {
               return $this->redirectToSprintView($viewData['id']);
          }
          // Fetch sprint planning details from the model
          $viewData['sprintPlanning'] = $this->sprintModel->getSprintPlanning($viewData['id']);
          $i = 0;
          foreach ($viewData['sprintPlanning'] as $key1 => $value1) {
               $viewData['sprintPlanning'][$i]['startDate'] = $this->formatDate($value1['startDate']);
               $viewData['sprintPlanning'][$i]['endDate'] = $this->formatDate($value1['endDate']);
               $i++;
          }
          return $this->response->setJSON(['success' => true, 'data' => $viewData['sprintPlanning']]);
     }
     /**
      * Redirects a non-AJAX request for a sprint data endpoint to the sprint view (or list).
      */
     private function redirectToSprintView($sprintId)
     {
          if (is_numeric($sprintId)) {
               return redirect()->to(ASSERT_PATH . 'sprint/navsprintview?sprint_id=' . (int) $sprintId);
          }

          return redirect()->to(ASSERT_PATH . 'sprint/sprintlist');
     }

     /**
      * Fetches and returns the sprint review details for a specific sprint.
      *
      * @param int|null $id The ID of the sprint to retrieve review details for.
      * @return Response|string The rendered view or JSON response for AJAX requests.
      */
     public function getReviewDetails()
     {
          $titles = ['General', 'codeReview', 'challengeFaced', 'sprintGoal'];
          // Initialize the sprint review data array
          $viewData['sprintReview'] = [];
          // Get the sprint ID from the GET request
          $viewData['id'] = $this->request->getGet('sprint_id');

          $codeReviewers = array_column($this->sprintModel->fetchCodeReviewers($viewData['id']), "code_reviewers");
          // Fetch sprint review details from the model
          $notesInput = ['notes_type' => $this->sprintModelConfig->reviewNotes, 'reference' => $viewData['id']];
          $sprintReview = $this->notesService->getNotes($notesInput);

          $reviewDate = isset($sprintReview[0]['added_date']) ? array(array("added_date" => $sprintReview[0]['added_date'])) : $this->sprintModel->getSprintReviewDate($viewData['id']);
          $viewData['sprintReviewDate'] = $this->formatDate($reviewDate[0]['added_date']);
          // Populate the sprint review data array with fetched details
          foreach ($sprintReview as $key1 => $value1) {
               if ($key1 == 0) {
                    $viewData['sprintReview'][$titles[$key1]] = $value1['notes'];
               } elseif ($key1 == 1) {
                    $viewData['sprintReview'][$titles[$key1]] = array('status' => $value1['r_notes_type_id'] == 4 ? 'Y' : 'N', 'notes' => $value1['notes'], 'reviewers' => $codeReviewers);
               } elseif ($key1 == 2) {
                    $viewData['sprintReview'][$titles[$key1]] = array('status' => $value1['r_notes_type_id'] == 8 ? 'N' : 'Y', 'notes' => $value1['notes']);
               } elseif ($key1 == 3) {
                    $viewData['sprintReview'][$titles[$key1]] = array('status' => $value1['r_notes_type_id'] == 6 ? 'Y' : 'N', 'notes' => $value1['notes']);
               }
          }

          return $this->response->setJSON(["success" => true, "data" => $viewData]);
     }
     public function ReviewSprintPlanDetails()
     {
          $sprintSettings = $this->request->getPost();

          $i = 0;
          foreach ($sprintSettings['sprintData'] as $value1) {
               // Prepare the notes data
               $notes = [
                    "notes" => !empty($value1['comments']) ? $value1['comments'] : "No comments found",
                    "r_user_id" => $this->userId,
                    "created_date" => $this->currentDateTime
               ];
               // Validate the notes data
               $checkValidations = $this->hasInvalidInput($this->notesModelObj, $notes);
               if ($checkValidations !== true) {
                    return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
               }
               // Insert notes and get the note ID
               $sprintSettings['sprintData'][$i]['notes_id'] = $this->notesModelObj->insertNotes($notes);
               $i++;
          }

          foreach ($sprintSettings['sprintData'] as $value1) {
               $settingsData = [
                    'r_sprint_id' => $sprintSettings['sprint_id'],
                    'r_sprint_activity_id' => $value1['activityId'],
                    'start_date' => $value1['startDate'],
                    'end_date' => $value1['endDate'],
                    'r_notes_id' => $value1['notes_id']
               ];

               $checkValidations = $this->hasInvalidInput($this->sprintPlanningObj, $settingsData);
               if ($checkValidations !== true) {
                    // Collect errors if validation fails
                    return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
               }
               $results = $this->sprintPlanningObj->insertSprintPlanning($settingsData);
          }
          if ($results) {
               return $this->response->setJSON(['success' => true, 'data' => $results]);
          } else {
               return $this->response->setJSON(['success' => false, 'data' => $results]);
          }


     }
     public function getRetrospectiveDetails()
     {
          // Get the sprint ID from the GET request
          $viewData['id'] = $this->request->getGet('sprint_id');
          // This is a data endpoint; a direct browser hit goes to the sprint page instead
          if (! $this->request->isAJAX()) {
               return $this->redirectToSprintView($viewData['id']);
          }

          // Fetch sprint retrospective details from the model
          $notesInput = ['notes_type' => $this->sprintModelConfig->retrospectiveNotes, 'reference' => $viewData['id']];
          $sprintRetrospective = $this->notesService->getNotes($notesInput);

          // Fetch the sprint retrospective date from the model
          $retrospectiveDate = isset($sprintRetrospective[0]['added_date']) ? array(array("added_date" => $sprintRetrospective[0]['added_date'])) : $this->sprintModel->getSprintRetrospectiveDate($viewData['id']);

          $viewData['sprintRetrospectiveDate'] = $this->formatDate($retrospectiveDate[0]['added_date']);
          foreach ($sprintRetrospective as $key => $value) {
               // return $this->response->setJSON(['success' => true, 'data'=> $value['r_notes_type_id']]);
               $sprintRetrospective[$key]['challenge'] = array_search($value['r_notes_type_id'], $this->sprintModelConfig->retrospectiveNotes);
          }
          // Initialize the sprint retrospective data array
          $viewData['sprintRetrospective'] = [];

          // Populate the sprint retrospective data array with fetched details
          foreach ($sprintRetrospective as $value1) {
               // If the challenge does not exist in the array, initialize it
               if (!isset($viewData['sprintRetrospective'][$value1['challenge']])) {
                    $viewData['sprintRetrospective'][$value1['challenge']] = array('notes' => array());
               } elseif (!is_array($viewData['sprintRetrospective'][$value1['challenge']]['notes'])) {
                    // Ensure the notes field is an array
                    $viewData['sprintRetrospective'][$value1['challenge']]['notes'] = array();
               }
               // Append the notes to the respective challenge
               $viewData['sprintRetrospective'][$value1['challenge']]['notes'][] = $value1['notes'];
          }

          // Prepare the response data
          $response = [
               'status' => 'success',
               'data' => array($viewData['sprintRetrospective'], $viewData['sprintRetrospectiveDate']) // Assuming $tasks is an array of data to send
          ];

          return $this->response->setJSON($response);
     }

     /**
      * Method to insert scrum diary data to the table
      * @return string
      */
     public function insertScrumDiary(): response|string
     {
          // Check if the request method is POST
          if ($this->request->getMethod() == "POST") {

               $data = $this->request->getPost();
               // return $this->response->setJSON(["success" => true, "data" => $data]);
               foreach ($data["TaskId"] as $value) {
                    $data = [
                         "notes" => $this->request->getPost("general"),
                         "r_user_id" => $this->userId,
                         "r_notes_type_id" => $this->request->getPost('challenges') == 'Y' ? 2 : 1,
                         "reference_id" => $value
                    ];
                    $result = $this->notesService->insertNotes($data);
               }
               if ($result) {
                    $action = array(
                         "r_user_id" => $this->userId,
                         "r_action_type_id" => 1,
                         "product_id" => 0,
                         "r_module_id" => 11,
                         "reference_id" => $this->request->getGet('sprint_id'),
                         "action_data" => ($this->request->getPost('challenges') == 'Y') ? 'Challenges faced' : 'Review added',
                         "action_date" => $this->currentDateTime
                    );
                    $this->historyModel->logActions($action);
                    return $this->response->setJSON(["success" => true, "data" => "Scrum diary updated successfully"]);
               } else {
                    return $this->response->setJSON(["success" => false]);
               }
          }
          // Return a default value if the request method is not POST
          return $this->response->setJSON(["success" => false]);
     }

     /**
      * Displays the Sprint Review page.
      *
      * @param int $modal The modal type.
      * @return Response|string The Sprint Review view or a JSON response.
      */
     public function navSprintReview(): Response|string
     {
          // Retrieve the sprint ID from the GET request
          $viewData['id'] = $this->request->getPost('sprint_id');
          // Retrieve sprint tasks from the model
          $tasks = $this->sprintModel->getSprintTask($viewData['id']);
          // Initialize the sprint task and backlog items arrays

          $productId = array_column($this->sprintModel->getSprint($viewData['id']), 'r_product_id');
          $viewData['productUsers'] = $this->sprintModel->getMembersByProduct($productId[0]);

          $viewData['sprintTask'] = $this->formatTaskForTable($tasks);
          // Prepare the JSON response data
          $response = [
               'status' => 'success',
               'data' => $viewData['sprintTask'], // Assuming $tasks is an array of data to send
          ];

          // If it's an AJAX request, send JSON response
          if ($this->request->isAJAX()) {
               return $this->response->setJSON($response);
          }
          // Prepare breadcrumbs for the view
          $breadcrumbs = [
               'Sprints' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint List' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint View' => ASSERT_PATH . 'sprint/navsprintview?sprint_id=' . $viewData['id'],
               'Sprint Review' => ASSERT_PATH . 'sprint/navsprintreview#'
          ];

          $sprintDetails = $this->sprintModel->getSprint($viewData['id']);
          $title = "Sprint Review: <span style='font-size: 1.6rem; font-weight:100'>" . $sprintDetails[0]["sprint_name"] . " </span><span style='font-size: 1.5rem; font-weight:100'>v" . $sprintDetails[0]["sprint_version"] . "</span>";

          // Return the Sprint Review view
          return $this->template_view('sprint/sprintReview', $viewData, $title, $breadcrumbs);
     }

     /**
      * Submits the task review for selected stories.
      *
      * @return string A JSON-encoded string indicating success or failure.
      */
     public function submitTaskReview()
     {
          // Retrieve submitted data from the POST request
          $selectedStories = $this->request->getPost();
          $tasks = $selectedStories['id'];
          $status = $selectedStories['taskStatus'];
          $result = $this->sprintModel->updateTaskReview($tasks, $status);
          // Attempt to update the task review in the model
          if ($result) {
               // Return success message if the update is successful
               return json_encode($result);
          } else {
               // Return failure message if the update fails
               return json_encode("failure");
          }
     }

     /**
      * Inserts a sprint review.
      *
      * @return string The result of the navigation to the sprint review view.
      */
     public function insertSprintReview(): response|string
     {
          // $data = $this->request->getPost();
          // return $this->response->setJSON(["success" => true, "data" => $data]);
          $selectedMembers = $this->request->getPost("selectedMembers");

          if ($this->request->getPost("codeReviewStatus") == "Y") {
               if (!isset($selectedMembers)) {
                    return $this->response->setJSON(['success' => false, 'data' => 'select members']);
               }
          }
          if (isset($selectedMembers)) {
               // Validate the notes data
               foreach ($selectedMembers as $value1) {
                    $data = [
                         "r_sprint_id" => $this->request->getGet('sprint_id'),
                         "r_user_id" => $value1
                    ];
                    $checkValidations = $this->hasInvalidInput($this->codeReviewObj, $data);
                    if ($checkValidations !== true) {
                         return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
                    }
                    $result = $this->codeReviewObj->insertCodeReviewUsers($data);
               }
          }

          // Retrieve POST data for the sprint review
          $noteData = [
               "generalReview" => array($this->request->getPost("generalReview"), 3),
               "codeReview" => array($this->request->getPost("codeReview"), $this->request->getPost("codeReviewStatus") == 'Y' ? 4 : 5),
               "challengesReview" => array($this->request->getPost("challengesReview"), $this->request->getPost("challengeStatus") == 'Y' ? 9 : 8),
               "goalsReview" => array($this->request->getPost("goalsReview"), $this->request->getPost("goalsStatus") == 'Y' ? 6 : 7)
          ];

          // Iterate through each review note and validate it
          foreach ($noteData as $key1 => $value1) {
               $data = [
                    "notes" => $value1[0],
                    "r_user_id" => $this->userId,
                    "r_notes_type_id" => $value1[1],
                    "reference_id" => $this->request->getGet('sprint_id')
               ];
               $result = $this->notesService->insertNotes($data);
               if ($result == false) {
                    return $this->response->setJSON(['success' => false, 'message' => 'condition failed']);
               }

          }
          if ($result) {
               $action = array(
                    "r_user_id" => $this->userId,
                    "r_action_type_id" => 1,
                    "product_id" => 0,
                    "r_module_id" => 9,
                    "reference_id" => $this->request->getGet('sprint_id'),
                    "action_data" => " <b>" . $noteData["generalReview"][0] . "</b>",
                    "action_date" => $this->currentDateTime
               );
               $this->historyModel->logActions($action);

               $sprintData = $this->sprintModel->getSprint($this->request->getGet('sprint_id'));
               // $sprintData['review'] = $this->sprintModel->getSprintReview($this->request->getGet('sprint_id'));
               $emailData = [
                    'email_id' => array_column($this->sprintModel->getSprintMember($this->request->getGet('sprint_id')), "email_id"),
                    'fileName' => 'sprintReview',
                    'contents' => [
                         'subject' => "Sprint review of " . $sprintData[0]['sprint_name'] . " - " . $sprintData[0]['sprint_version'] . " of " . $this->currentDate,
                         'sprint_name' => $sprintData[0]['sprint_name'],
                         'general_review' => $this->request->getPost("generalReview"),
                         'code_review' => $this->request->getPost("codeReviewStatus"),
                         'challenge' => $this->request->getPost("challengeStatus"),
                         'sprint_goal' => $this->request->getPost("goalsStatus"),
                         'reviewDate' => $this->currentDate
                    ]
               ];
               // return $this->response->setJSON(["success" => true, "data" => $emailData]);
               $this->emailService->sendMail($emailData);
          } else {
               return $this->response->setJSON(['success' => false, 'message' => 'inside']);
          }

          return $this->response->setJSON(['success' => false, 'message' => 'condition failed']);
     }

     /**
      * Insert a new Sprint Retrospective entry.
      *
      * @return string Redirects to the Sprint Retrospective view with success or failure status.
      */

     public function insertSprintRetrospective(): response|string
     {
          $sprintData = [];
          $notesTemp = array("No feedbacks", "No feedbacks", "No feedbacks");
          // Check if the request method is POST
          if ($this->request->getMethod() == "POST") {
               $input = $this->request->getPost();
               $i = 0;
               // return $this->response->setJSON(["success" => true, "data" => $data["feedbacks"]]);
               foreach ($input["feedbacks"] as $key1 => $value1) {
                    if ($value1 != null) {
                         $data = [
                              "notes" => $value1,
                              "r_user_id" => $this->userId,
                              "r_notes_type_id" => $this->sprintModelConfig->retrospectiveNotes[$key1],
                              "reference_id" => $input['sprint_id']
                         ];
                         $result = $this->notesService->insertNotes($data);

                         if ($result) {
                              $type = $key1 == "lns" ? "suggestions" : $key1;

                              $action = array(
                                   "r_user_id" => $this->userId,
                                   "r_action_type_id" => 1,
                                   "product_id" => 0,
                                   "r_module_id" => 10,
                                   "reference_id" => $input["sprint_id"],
                                   "action_data" => $type . " (<b>" . $value1 . "</b>)",
                                   "action_date" => $this->currentDateTime
                              );
                              $this->historyModel->logActions($action);
                         } else {
                              return $this->response->setJSON(["success" => false, "data" => "Sprint retrospective not updated"]);
                         }
                         $notesTemp[$i] = $value1;
                         $i++;
                    }
               }
               if ($result) {
                    $sprintData = $this->sprintModel->getSprint($input["sprint_id"]);
                    // $sprintData['review'] = $this->sprintModel->getSprintReview($input["sprint_id"]);
                    $sprintData['notes'] = $notesTemp;
                    $emailData = [
                         'email_id' => array_column($this->sprintModel->getSprintMember($input["sprint_id"]), "email_id"),
                         'fileName' => 'sprintRetrospective',
                         'contents' => [
                              'subject' => "Sprint Retrospective of " . $sprintData[0]['sprint_name'] . " - " . $sprintData[0]['sprint_version'] . " of " . $this->currentDate,
                              'sprint_name' => $sprintData[0]['sprint_name'],
                              'pros' => $sprintData['notes'][0],
                              'cons' => $sprintData['notes'][1],
                              'suggestions' => $sprintData['notes'][2],
                              'reviewDate' => $this->currentDate
                         ]
                    ];
                    // return $this->response->setJSON(["success" => true, "data" => $emailData]);
                    $this->emailService->sendMail($emailData);

               } else {
                    return $this->response->setJSON(["success" => false, "data" => "Sprint retrospective not updated"]);
               }
          }
          return $this->response->setJSON(["success" => false, "data" => "Sprint retrospective not updated"]);
     }

     /**
      * Handles the editing of a sprint.
      *
      * @return string Rendered view for editing the sprint.
      */
     public function edit(): string
     {
          // Set up breadcrumbs for navigation
          $breadcrumbs = [
               'Sprints' => ASSERT_PATH . 'sprint/sprintlist',
               'Sprint list' => ASSERT_PATH . 'sprint/sprintlist',
               'edit sprint' => ''
          ];
          // Retrieve the user ID
          $uId = $this->userId;
          // Prepare view data
          $getSprint = $this->request->getPost('sprint_id');
          $viewData['edit'] = 1;
          $viewData['sprint_id'] = $getSprint;
          $viewData['sprint_data'] = $this->sprintModel->getEditSprint($getSprint);
          $viewData['product'] = $this->backlogModel->getUserProduct($uId);
          $viewData['customer'] = $this->sprintModel->getCustomer();
          $viewData['sprintDuration'] = $this->sprintModel->getSprintDuration();
          $viewData['sprintActivity'] = $this->sprintModel->getSprintActivity();

          $param = [
               "userStory" => $this->sprintModelConfig->userStoryTaskForEdit,
               "r_product_id" => $viewData['sprint_data'][0]['r_product_id']
          ];

          $tasks = $this->sprintModel->getTaskForEdit($param);

          // Format the fetched tasks for response
          $viewData['tasks'] = $this->formatTasks($tasks);

          // Add sprint status and data for editing
          $viewData['status'] = $this->sprintModel->getSprintStatus();

          $viewData['task_in_sprint_id'] = array_column($this->sprintModel->getSprintTask($this->request->getPost('sprint_id')), 'task_id');

          $viewData['member_in_sprint'] = array_column($this->sprintModel->getSprintMember($this->request->getPost('sprint_id')), 'id');
          // Render the edit sprint view with the prepared data
          return $this->template_view('sprint/createSprint', $viewData, 'Edit sprint', $breadcrumbs);
     }

     /**
      * Updates an existing sprint.
      *
      * @return string indicating success or validation errors.
      */
     public function update(): response|string
     {
          // Collect data from the request
          $data = [
               'sprint_id' => $this->request->getPost('sprint_id'),
               'sprint_name' => $this->request->getPost('sprintName'),
               'sprint_version' => $this->request->getPost('sprintVersion'),
               'r_product_id' => $this->request->getPost('productName'),
               'r_customer_id' => $this->request->getPost('customer'),
               'r_sprint_duration_id' => $this->request->getPost('sprintDuration'),
               'start_date' => $this->request->getPost('startDate'),
               'end_date' => $this->request->getPost('endDate'),
               'sprint_goal' => $this->request->getPost('default'),
               'updated_date' => $this->currentDateTime,
               'r_user_id_updated' => $this->userId,
               'r_module_status_id' => $this->request->getPost('sprintStatus')
          ];

          // Validate the data
          $checkValidations = $this->hasInvalidInput($this->scrumSprintModelobj, $data);
          if ($checkValidations !== true) {
               return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
          }
          // Update the sprint in the database
          $result = $this->scrumSprintModelobj->updateSprint($data);
          // Return a success response if update was successful

          $selectedTasks = $this->request->getPost('selectedTasks');
          $selectedMembersJson = $this->request->getPost('selectedMembers');

          $selectedMembersJson = isset($selectedMembersJson) ? $selectedMembersJson : '[]';
          $selectedMembers = json_decode($selectedMembersJson, true);

          array_push($selectedMembers, $this->userId);
          $selectedMembers = array_unique($selectedMembers);

          $taskInSprints = array_column($this->sprintModel->getSprintTask($data['sprint_id']), 'task_id');

          if (isset($selectedTasks)) {
               $eliminateTask = array_diff($taskInSprints, $selectedTasks);

               if (!empty($eliminateTask)) {
                    $this->sprintModel->updateTaskReview($eliminateTask, 16);
               }
          }

          // Check if there are selected tasks and members
          if (!empty($selectedTasks) && !empty($selectedMembers)) {
               // Get user stories associated with the selected tasks
               $userStories = array_column($this->sprintModel->getUserStories($selectedTasks), "id");

               // Update user stories
               $this->sprintModel->updateUserStory($userStories, 18);

               $this->sprintModel->removeSprintTasks($data['sprint_id']);
               // Iterate through each selected task and insert into the database
               foreach ($selectedTasks as $isSelected) {
                    if ($isSelected) {
                         $taskData = [
                              'r_sprint_id' => $this->request->getPost('sprint_id'),
                              'r_task_id' => $isSelected,
                         ];
                    }
                    // Validate the task data
                    $checkValidations = $this->hasInvalidInput($this->sprintTaskModelobj, $taskData);
                    if ($checkValidations !== true) {
                         return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
                    }
                    // Insert the selected task into the database
                    $this->sprintTaskModelobj->insertSelectedTasks($taskData);
                    $this->sprintModel->updatesprintEstimationTime($this->request->getPost('sprint_id'));
               }
               // Instantiate the SprintUser model object
               $this->sprintModel->removeSprintUsers($data['sprint_id']);
               // Iterate through each selected member and insert into the database
               foreach ($selectedMembers as $memberId) {
                    $memberData = [
                         'r_sprint_id' => $this->request->getPost('sprint_id'),
                         'r_user_id' => $memberId,
                    ];
                    // Validate the member data
                    $checkValidations = $this->hasInvalidInput($this->sprintUserModelobj, $memberData);
                    if ($checkValidations !== true) {
                         return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
                    }

                    // Insert the selected member into the database
                    $result = $this->sprintUserModelobj->insertSelectedMembers($memberData);
               }
               if ($result) {
                    $action = array(
                         "r_user_id" => $this->userId,
                         "r_action_type_id" => 6,
                         "product_id" => 0,
                         "r_module_id" => 8,
                         "reference_id" => $data["sprint_id"],
                         "action_data" => "<b>" . $this->request->getPost('editedFor') . "</b>",
                         "action_date" => $this->currentDateTime
                    );
                    $this->historyModel->logActions($action);

                    $notes = [
                         "notes" => $this->request->getPost("editedFor"),
                         "r_user_id" => $this->userId,
                         "created_date" => $this->currentDateTime
                    ];
                    // Validate the notes data
                    $checkValidations = $this->hasInvalidInput($this->notesModelObj, $notes);
                    if ($checkValidations !== true) {
                         return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
                    }
                    // Insert notes and get the note ID
                    $noteId = $this->notesModelObj->insertNotes($notes);
                    // return json_encode($result);
                    return json_encode(['success' => true]);
               } else {
                    // Return error response if insertion fails
                    return json_encode(['success' => false, 'error' => 'Failed to insert tasks']);
               }

          } else if (!isset($selectedTasks)) {
               $action = array(
                    "r_user_id" => $this->userId,
                    "r_action_type_id" => 6,
                    "product_id" => 0,
                    "r_module_id" => 8,
                    "reference_id" => $data["sprint_id"],
                    "action_data" => "<b>" . $data["sprint_name"] . " - " . $data["sprint_version"] . "</b>",
                    "action_date" => $this->currentDateTime
               );
               $this->historyModel->logActions($action);

               $notes = [
                    "notes" => $this->request->getPost("general"),
                    "r_user_id" => $this->userId,
                    "created_date" => $this->currentDateTime
               ];

               // Validate the notes data
               $checkValidations = $this->hasInvalidInput($this->notesModelObj, $notes);
               if ($checkValidations !== true) {
                    return $this->response->setJSON(['success' => false, 'data' => $checkValidations]);
               }
               // Insert notes and get the note ID
               $noteId = $this->notesModelObj->insertNotes($notes);
               return json_encode(['success' => true]);
          } else {
               return json_encode(['success' => false, 'error' => 'No tasks selected']);
          }
     }

     public function updateSprintPlan()
     {
          $data = $this->request->getPost();
          $a = $this->sprintModel->updateSprintPlan($data);
          if ($a) {
               $action = array(
                    "r_user_id" => $this->userId,
                    "r_action_type_id" => 2,
                    "product_id" => 0,
                    "r_module_id" => 19,
                    "reference_id" => $data["sprint_id"],
                    "action_data" => $data["activity_name"] . " activity status from <b>" . $data["prev_value"] . "</b> to <b>" . $data["new_value"] . "</b>",
                    "action_date" => $this->currentDateTime
               );
               $this->historyModel->logActions($action);
          }
          return json_encode($a);
     }

     public function updateSprintStatus()
     {
          $data = $this->request->getPost();
          $a = $this->sprintModel->updateSprintStatus($data);
          if ($a) {
               $action = array(
                    "r_user_id" => $this->userId,
                    "r_action_type_id" => 2,
                    "product_id" => 0,
                    "r_module_id" => 8,
                    "reference_id" => $data["sprint_id"],
                    "action_data" => "status from <b>" . $data["prev_value"] . "</b> to <b>" . $data["new_value"] . "</b>",
                    "action_date" => $this->currentDateTime
               );
               $this->historyModel->logActions($action);
               return $this->response->setJSON(['success' => true]);
          } else {
               return $this->response->setJSON(['success' => false]);
          }
     }

     public function navSprintHistory()
     {
          $id = $this->request->getGet('sprint_id');
          $param = [
               "module" => $this->sprintModelConfig->moduleHistory,
               "sprint_id" => $id
          ];
          $details = $this->sprintModel->getSprintHistory($param);

          foreach ($details as $key1 => $value1) {
               list($date, $time) = explode(' ', $details[$key1]['action_date']);
               $details[$key1]['action_date'] = $this->formatDate($date);
               $details[$key1]['action_time'] = $time;
          }
          foreach ($details as $key1 => $value1) {
               $viewData['action'][] = ucfirst($details[$key1]['action_type_name']) . " " . $details[$key1]['module_name'] . " - " . $details[$key1]['action_data'];
          }
          $viewData['users'] = array_column($details, "name");
          $viewData['date'] = array_column($details, "action_date");
          $viewData['time'] = array_column($details, "action_time");

          return $this->response->setJSON(['data' => $viewData]);
     }

     public function fetchMembers()
     {
          $sprintId = $this->request->getGet('sprint_id');
          $members = $this->sprintModel->getSprintMember($sprintId);

          $result = array_map(function ($item) {
               return [
                    "employee_id" => $item['emp_id'],
                    "member" => $item["name"],
                    "email_id" => $item["email_id"],
                    "role" => $item["role_name"]
               ];
          }, $members);

          $response = [
               'status' => 'success',
               'data' => $result,
          ];
          json_encode($members);

          if ($this->request->isAJAX()) {
               return $this->response->setJSON($response);
          }
     }

     public function fetchScrumTasks()
     {
          $id = $this->request->getPost('sprintId');

          $tasks = $this->sprintModel->getSprintTask($id);
          $finalTasks = [];

          foreach ($tasks as $key1 => $value1) {
               $finalTasks[$key1]['id'] = $value1["task_id"];
               $finalTasks[$key1]['name'] = $value1["task_title"];
          }
          $response = [
               'status' => 'success',
               'data' => $finalTasks,
          ];

          if ($this->request->isAJAX()) {
               return $this->response->setJSON($response);
          }
     }

     public function alterSprintDateById($sprintId)
     {
          $data = [
               'sprintId' => $sprintId,
               'duration' => $this->request->getPost('newDuration'),
               'startDate' => $this->request->getPost('newStartDate'),
               'endDate' => $this->request->getPost('newEndDate'),
               'userId' => $this->userId
          ];
          $this->sprintModel->alterSprintDate($data);
          return $this->response->setJSON(['success' => true, "data" => $data]);
     }

     public function changeSprintStatusById($sprintId)
     {
          $data = [
               'sprintId' => $sprintId,
               'status' => $this->request->getPost('newStatus'),
               'userId' => $this->userId
          ];
          $this->sprintModel->alterSprintStatusById($data);
          return $this->response->setJSON(['success' => true, "data" => $data]);
     }

     public function replaceEmptyValues(&$array, $replacement = '-')
     {
          foreach ($array as &$value) {
               if (is_array($value)) {
                    $this->replaceEmptyValues($value, $replacement);
               } else {
                    if (empty($value) && $value !== '0') {
                         $value = $replacement;
                    }
               }
          }
     }

     /**
      * @author     Jeril
      * @param int $sprintId - The ID of the sprint for which the PDF report is to be generated.
      * @return Response|string|void
      * Purpose: Function to generate pdf for the sprint view page. The necessary datas are collected from the sprintModel
      * 
      */
     public function generatePdf($sprintId)
     {
          $check = 0;
          $retrospectiveInput = ['notes_type' => $this->sprintModelConfig->retrospectiveNotes, 'reference' => $sprintId];
          $sprintRetrospective = $this->notesService->getNotes($retrospectiveInput);
          $dailyScrumInput = ['dailyScrum' => 1, "sprintId" => $sprintId];
          $dailyScrum = $this->notesService->getNotes($dailyScrumInput);
          foreach ($dailyScrum as $key => $value) {
               $dailyScrum[$key]['challenges'] = $value['r_notes_type_id'] == 2 ? 'Y' : 'N';
               $dailyScrum[$key]['added_date'] = $this->formatDate($value['added_date']);
          }
          foreach ($sprintRetrospective as $key => $value) {
               $sprintRetrospective[$key]['challenge'] = array_search($value['r_notes_type_id'], $this->sprintModelConfig->retrospectiveNotes);
          }
          $sprintPlanning = $this->sprintModel->getSprintPlanning($sprintId);
          foreach ($sprintPlanning as $key => $value) {
               $sprintPlanning[$key]['startDate'] = $this->formatDate($value['startDate']);
               $sprintPlanning[$key]['endDate'] = $this->formatDate($value['endDate']);
          }
          $titles = ['general', 'codeReview', 'challengeFaced', 'sprintGoal'];

          $notesInput = ['notes_type' => $this->sprintModelConfig->reviewNotes, 'reference' => $sprintId];
          $sprintReviewTemp = $this->notesService->getNotes($notesInput);

          $sprintReview = [];
          // Populate the sprint review data array with fetched details
          foreach ($sprintReviewTemp as $key1 => $value1) {
               if ($key1 == 0) {
                    $sprintReview[$titles[$key1]] = $value1['notes'];
               } elseif ($key1 == 1) {
                    $sprintReview[$titles[$key1]] = $value1['r_notes_type_id'] == 4 ? '' : $value1['notes'];
                    $sprintReview['codeReviewStatus'] = $value1['r_notes_type_id'] == 4 ? 'Y' : 'N';
               } elseif ($key1 == 2) {
                    $sprintReview[$titles[$key1]] = $value1['r_notes_type_id'] == 8 ? '' : $value1['notes'];
                    $sprintReview['challengesStatus'] = $value1['r_notes_type_id'] == 8 ? 'N' : 'Y';
               } elseif ($key1 == 3) {
                    $sprintReview[$titles[$key1]] = $value1['r_notes_type_id'] == 6 ? '' : $value1['notes'];
                    $sprintReview['sprintGoalStatus'] = $value1['r_notes_type_id'] == 6 ? 'Y' : 'N';
               }
          }

          $codeReviewers = array_column($this->sprintModel->fetchCodeReviewers($sprintId), "code_reviewers");

          if (isset($sprintReview['codeReviewStatus'])) {
               $sprintReview['codeReviewers'] = count($codeReviewers) > 0 ? implode(', ', $codeReviewers) : '';
          }
          $viewData = [
               'sprintOverview' => $this->sprintModel->getSprint($sprintId),
               'sprintMembers' => $this->sprintModel->getSprintMember($sprintId),
               'sprintPlanning' => $sprintPlanning,
               'sprintTask' => $this->sprintModel->getSprintTask($sprintId),
               'dailyScrum' => $dailyScrum,
               'sprintReviewDate' => isset($sprintReviewTemp[0]['added_date']) ? array(array("added_date" => $sprintReviewTemp[0]['added_date'])) : $this->sprintModel->getSprintReviewDate($sprintId),
               'sprintReview' => $sprintReview,
               'sprintRetrospectiveDate' => isset($sprintRetrospective[0]['added_date']) ? array(array("added_date" => $sprintRetrospective[0]['added_date'])) : $this->sprintModel->getSprintRetrospectiveDate($sprintId),
               'sprintRetrospective' => $sprintRetrospective
          ];

          $userProductsMultiDim = $this->backlogModel->getUserProduct($this->userId);
          $userProducts = array_column($userProductsMultiDim, 'product_id');
          if (isset($viewData['sprintOverview'][0]['r_product_id'])) {
               $check = in_array($viewData['sprintOverview'][0]['r_product_id'], $userProducts) ? false : true;
               if ($check) {
                    return redirect('sprint/sprintlist');
               }
          } else {
               return redirect('sprint/sprintlist');
          }

          $this->replaceEmptyValues($viewData);

          foreach ($viewData['sprintRetrospective'] as $key1 => $value1) {
               if ($value1['challenge'] == 'lns') {
                    $viewData['sprintRetrospective'][$key1]['challenge'] = 'suggestions';
               }
          }

          $viewData['sprintOverview'][0]['start_date'] = $this->formatDate($viewData['sprintOverview'][0]['start_date']);
          $viewData['sprintOverview'][0]['end_date'] = $this->formatDate($viewData['sprintOverview'][0]['end_date']);
          $viewData['sprintReviewDate'][0]['review_date'] = $this->formatDate($viewData['sprintReviewDate'][0]['added_date']);
          $viewData['sprintRetrospectiveDate'][0]['retrospective_date'] = $this->formatDate($viewData['sprintRetrospectiveDate'][0]['added_date']);

          // Load the view and pass data to it
          $html = view('layout/sprintPdfTemplate', ['viewData' => $viewData], ['saveData' => true]);

          // Initialize dompdf
          $options = new Options();
          $options->set('defaultFont', 'Arial');
          $dompdf = new Dompdf($options);
          $dompdf->loadHtml($html);

          // (Optional) Setup the paper size and orientation
          $dompdf->setPaper('A4', 'portrait');

          // Render the HTML as PDF
          $dompdf->render();

          // Output the generated PDF with name and version of the sprint displayed as the file name
          $dompdf->stream(strtolower(str_replace(' ', '_', $viewData['sprintOverview'][0]['sprint_name'])) . "" . $viewData['sprintOverview'][0]['sprint_version'] . ".pdf");
     }
}