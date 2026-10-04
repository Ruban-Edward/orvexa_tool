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

class UserStoryController extends BaseController
{
    protected $backlogModel;
    protected $backlogItemModel;
    protected $epicModel;
    protected $userStoryModel;
    protected $userStoryConditionModel;
    protected $documentModel;
    protected $fibonacciModel;
    protected $report;
    protected $settingsConfigModelObj;

    public function __construct()
    {
        $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $this->backlogItemModel = model(\App\Models\Backlog\BacklogItemModel::class);
        $this->epicModel = model(\App\Models\Backlog\EpicModel::class);
        $this->userStoryModel = model(\App\Models\Backlog\UserStoryModel::class);
        $this->userStoryConditionModel = model(\App\Models\Backlog\UserStoryConditionModel::class);
        $this->documentModel = model(\App\Models\Backlog\DocumentModel::class);
        $this->fibonacciModel = model(\App\Models\Backlog\BacklogFibonacci::class);
        $this->report = service('generateReport');
    }

    /**
     * @author vigneshwari
     * @return string
     * Purpose: The purpose of this function is to return the user stories page of a particular backlog item
     */

     public function userStories(): string
     {
         // Retrieve the product ID and backlog item ID from the GET request
         $pId = $this->request->getGet('pid');
         $pblId = $this->request->getGet('pblid');
     
         // Validate the presence of necessary parameters
         if (!$pId || !$pblId) {
             // If parameters are missing, set breadcrumbs and render the 'No data' view
             $breadcrumbs = ['Products' => ASSERT_PATH . 'backlog/productbacklogs'];
             return $this->template_view('dashboard/NodataView', null, 'No data', $breadcrumbs);
         }
     
         // Check user authentication for the provided product and backlog item IDs
         if (!authenticateUser($pId, $pblId)) {
             // If authentication fails, set breadcrumbs and render the 'No data' view
             $breadcrumbs = [
                 'Products' => ASSERT_PATH . 'backlog/productbacklogs',
                 'Backlog items' => ASSERT_PATH . 'backlog/backlogitems?pid=' . $pId,
                 'User stories' => ''
             ];
             return $this->template_view('dashboard/NodataView', null, 'No data', $breadcrumbs);
         }
     
         // Initialize models for meeting and meeting team
         $meetingModel = model(\App\Models\Meeting\MeetingModel::class);
         $meetingTeamModel = model(\App\Models\Meeting\MeetingTeamModel::class);
     
         // Fetch data related to the backlog item and user stories
         $backlogItem = $this->backlogModel->getBacklogDetails(['id' => $pblId]);
         $users = array_column($meetingModel->getUserByProductId($pId), 'first_name');
     
         // Prepare data array to be passed to the view
         $data = [
             'product_id' => $pId,
             'status' => $this->backlogModel->getStatus(BACKLOG_MODULE),
             'backlog_item_id' => $pblId,
             'product_name' => $backlogItem['product_name'],
             'backlog_item_name' => $backlogItem['backlog_item_name'],
             'meetingLocation' => $meetingModel->getMeetingLocation(),
             'user_story_status' => $this->backlogModel->getStatus(USERSTORY_MODULE),
             'epic' => $this->epicModel->getEpic($pblId),
             'teamMembers' => $meetingTeamModel->getTeamMembers(session()->get('employee_id')),
             'epicByBrainstrom' => $this->epicModel->epicByBrainstrom($pblId),
             'story_count' => $this->userStoryModel->countUserStories($pblId),
             'userName' => $users,
             'totalcomments' => $this->backlogModel->getComments(),
             'fibonacciLimit' => $this->generateFibonacci(FIBONACCI_LIMIT),
             'totaluserstories' => $this->userStoryModel->getTotalUserStory($pblId),
             'current_user' => $this->backlogModel->getCurrentUser(session()->get('employee_id')),
         ];
     
         // Set breadcrumbs for navigation
         $breadcrumbs = [
             'Products' => ASSERT_PATH . 'backlog/productbacklogs',
             'Backlog items' => ASSERT_PATH . 'backlog/backlogitems?pid=' . $pId,
             'User stories' => ''
         ];
     
         // Render the user stories view with the prepared data and breadcrumbs
         return $this->template_view('backlog/userStories', $data, 'User Stories', $breadcrumbs);
     }
     


    /**
     * @author vigneshwari
     * @return Response
     * Purpose: This function is used to insert the data to the userstory table
     */

     public function addUserStory()
     {
         // Retrieve product ID from GET request and backlog item ID from POST request
         $pId = $this->request->getget('pid');
         $pblId = $this->request->getPost('pblid');
     
         // Get all POST data and add the created user ID to the user story data
         $userStory = $this->request->getpost();
         $userStory['r_user_id_created'] = $_SESSION['employee_id'];
     
         // Validate the user story data using the model's validation method
         $validationErrors = $this->hasInvalidInput($this->userStoryModel, $userStory);
         if ($validationErrors !== true) {
             // If validation fails, return the errors as a JSON response
             return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);
         }
     
         // Insert the user story into the database
         $result = $this->userStoryModel->insertUserStory($userStory);
     
         // Store the action in the log
         $actionData = 'User Story Id: ' . $result . " is added";
         $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
         Events::trigger('log_actions', $action);
     
         // Get the most recent user story ID
         $usId = $this->userStoryModel->getCountUserStory();
     
         // Prepare the user story condition data
         $userStoryCondition = $this->request->getpost();
         $userStoryCondition['r_user_story_id'] = $usId[0]['usCount'];
     
         // Insert the user story condition data
         $result = $this->userStoryCondition($userStoryCondition, false);
     
         if ($result) {
             // If successful, return a success message as a JSON response
             return $this->response->setJSON(['success' => true, 'message' => 'User story added successfully']);
         }
     
         // If the user story condition insertion fails, return an error message as a JSON response
         return $this->response->setJSON(['success' => false, 'message' => 'User story not added']);
     }

    /**
     * @author Murugadass
     * @return Response
     * Purpose: This function is used to fetch the data from the userstory table to the update form
     */
    public function getUserStoryDetails($id): Response
    {
        // Fetch the user story details from the userStoryModel using the provided ID
        $userStory = $this->userStoryModel->getUserStoryById($id);

        // strip HTML tags from the condition_text field if needed
        // $userStory['condition_text'] = strip_tags($userStory['condition_text']);

        // Return the fetched user story details as a JSON response
        return $this->response->setJSON($userStory);
    }


    /**
     * @author vigneshwari
     * @return Response
     * Purpose: This function is used to update the userstory table
     */

     public function updateUserStory()
    {
        // Check if 'userId' is present in the POST request
        if ($this->request->getPost('userId')) {
            // Retrieve parameters from the request
            $pId = $this->request->getGet('pid');
            $pblId = $this->request->getPost('pblid');
            $usId = $this->request->getPost('userId');

            // Fetch current details of the user story before update
            $before = $this->userStoryModel->getUserStoryById($usId)[0];
            $userStory = $this->request->getPost();
            $userStory['user_story_id'] = $usId;
            $userStory['r_user_id_created'] = session('employee_id');

            // Validate the user story input
            $validationErrors = $this->hasInvalidInput($this->userStoryModel, $userStory);
            if ($validationErrors !== true) {
                return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);
            }

            // Update user story details in the database
            $res = $this->userStoryModel->updateUserStory($userStory);

            // Update associated conditions for the user story
            $userStoryCondition = $this->request->getPost();
            $userStoryCondition['r_user_story_id'] = $usId;
            $res = $this->userStoryCondition($userStoryCondition, true);

            // Check and update the backlog status based on user story status
            if ($userStory['r_module_status_id'] == 16) {
                $res = $this->backlogItemModel->changeBacklogStatus(1, 7, $pblId);
            }
            if ($userStory['r_module_status_id'] == 14) {
                $this->backlogItemModel->changeBacklogStatus(1, 5, $pblId);
            }
            if ($userStory['r_module_status_id'] == 17) {
                $this->backlogItemModel->changeBacklogStatus(1, 8, $pblId);
            }
            if ($userStory['r_module_status_id'] == 18) {
                $this->backlogItemModel->changeBacklogStatus(1, 10, $pblId);
            }

            // Fetch updated details of the user story after update
            $after = $this->userStoryModel->getUserStoryById($usId)[0];
            $header = [
                'User Story ID',
                'Epic ID',
                'Status ID',
                'Status',
                'Epic description',
                'As a / an',
                'I want',
                'So that',
                'Given',
                'When',
                'Then',
                'Condition'
            ];

            // Map the field names to header labels
            $header = array_combine(array_keys($after), $header);
            $keys = array_intersect_key($before, $after);
            $differentValues = array_diff_assoc($keys, $after);
            $differentKeys = array_keys($differentValues);

            // Build a description of the changes
            $changes = '';
            if ($after != $before) {
                $changes = 'In Userstory ID: ' . $userStory['user_story_id'] . ' ';
            }
            foreach ($differentKeys as $value) {
                if (!is_numeric($before[$value]) && !is_numeric($after[$value])) {
                    if (empty($before[$value])) {
                        $before[$value] = 'Empty';
                    }
                    $changes .= '<br>' . $header[$value] . ' Updated From ' . '<b>' . strip_tags($before[$value]) . '</b>' . ' to ' . '<b>' . strip_tags($after[$value]) . '</b>';
                }
            }

            // Log the action if there were changes
            if ($res) {
                $actionData = (empty($changes)) ? "No changes" : trim($changes, " |");
                $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
                Events::trigger('log_actions', $action);
                return $this->response->setJSON(['success' => true, 'message' => 'User story updated successfully']);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'User story not updated']);
        } else {
            // Handle the case where 'userId' is not present
            $pblId = $this->request->getPost('pbl_id');
            $storyId = $this->request->getPost('story_id');
            $statusId = $this->request->getPost('status_id');

            // Update backlog status based on user story status
            if ($statusId == 16) {
                $res = $this->backlogItemModel->changeBacklogStatus(1, 7, $pblId);
            }
            if ($statusId == 14) {
                $this->backlogItemModel->changeBacklogStatus(1, 5, $pblId);
            }
            if ($statusId == 17) {
                $this->backlogItemModel->changeBacklogStatus(1, 8, $pblId);
            }
            if ($statusId == 18) {
                $this->backlogItemModel->changeBacklogStatus(1, 10, $pblId);
            }

            // Fetch the status of the user story before and after the update
            $beforestatus = $this->userStoryModel->getUserStoryById($storyId);
            $beforestatus = $beforestatus[0]['status_name'];
            $backlog = $this->backlogModel->getBacklogDetails(['id' => $pblId]);

            // Update the user story status
            $res = $this->userStoryModel->userStoryStatus($statusId, $storyId);
            $userStory = $this->userStoryModel->getUserStoryById($storyId);
            $afterStatus = $userStory[0]['status_name'];

            // Log the status change action
            $actionData = "In Userstory ID:" . $userStory[0]['user_story_id'] . ", status updated from " . "<b>" . $beforestatus . "</b>" . " to " . "<b>" . $afterStatus . "</b>";
            $action = formActionData(__FUNCTION__, $pblId, $backlog['r_product_id'], $actionData);
            Events::trigger('log_actions', $action);

            if ($res) {
                return $this->response->setJSON(['success' => true, 'message' => 'Status changed successfully']);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update the status']);
        }
    }
     

    /**
     * @author vigneshwari
     * @return mixed
     * Purpose: This function is used to update the data to the userstory condition table
     */

    public function userStoryCondition($userStoryCondition,$update): mixed
    {

        $validationErrors = $this->hasInvalidInput($this->userStoryConditionModel, $userStoryCondition);
        if ($validationErrors !== true) {
            return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);
        }

        if ($update) {
            return $this->userStoryConditionModel->updateCondition($userStoryCondition);
        } else {
            return $this->userStoryConditionModel->insertUserStoryCondition($userStoryCondition);
        }
    }

    public function uploadUserStory(): Response
    {
        $file = $this->request->getFile('file');
        $pId = $this->request->getPost('pId');
        $pblId = $this->request->getPost('pblId');
        $flag = 0;
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = WRITEPATH . 'uploads';
            $fileName = $file->getName();
            $fileExt = $file->getExtension();
            $allowedExt = ['csv'];

            if (in_array($fileExt, $allowedExt)) {
                $filePath = $file->getTempName();

                if (($handle = fopen($filePath, 'r')) !== false) {
                    $data = [];

                    while (($line = fgetcsv($handle)) !== false) {
                        if (array_filter($line)) {
                            $data[] = array_map('strip_tags', $line);
                        }
                    }

                    fclose($handle);

                    if (!(empty($data))) {
                        $tableHeader = ['ID', 'Description', 'ID', 'As a / an', 'I want', 'So that', 'Given', 'When', 'Then'];
                        $fileHeader = array_filter(array_map('trim', $data[1]), 'strlen');

                        $tableHeader = array_map('strtolower', $tableHeader);
                        $fileHeader = array_map('strtolower', $fileHeader);

                        $fileDiff = array_diff($fileHeader, $tableHeader);
                        $tableDiff = array_diff($tableHeader, $fileHeader);
                        unset($data[0], $data[1]);
                        $userstory_count = count($data);
                        // echo $userstory_count;
                        if (empty($fileDiff) && empty($tableDiff)) {
                            $module = $epicId = $epicDescription = '';
                            $i = 1;
                            foreach ($data as $value) {
                                $duplicate = $i++;

                                if (!empty($value[0])) {
                                    $module = $value[0];
                                }

                                if (!empty($value[1])) {
                                    $epicId = $value[1];
                                    $epicDescription = $value[2];
                                    $epicId = $this->epicModel->getOrCreateEpicId($epicDescription, $pblId);
                                }
                                if (!empty($value[2])) {
                                    $epicDescription = $value[2];
                                }

                                $userStoryData = [
                                    'module' => $module,
                                    'r_epic_id' => $epicId,
                                    'epic_description' => $epicDescription,
                                    'us_id' => $value[3],
                                    'as_a_an' => $value[4],
                                    'i_want' => $value[5],
                                    'so_that' => $value[6],
                                    'conditions' => $value[7],
                                    'given' => $value[8],
                                    'us_when' => $value[9],
                                    'us_then' => $value[10],
                                    'r_module_status_id' => 13,
                                    'r_user_id_created' => session('employee_id'),
                                ];

                                $userStory = trim($value[4] . $value[5] . $value[6]);
                                $story = $this->checkuserstory($userStory, $pblId);
                                if ($story) {
                                    $flag = 1;
                                    $result = $this->userStoryModel->insertUserStory($userStoryData);
                                    $usId = $this->userStoryModel->getCountUserStory();
                                    $this->userStoryConditionModel->insertUserStoryCondition(['condition_text' => $userStoryData['conditions'], 'r_user_story_id' => $usId[0]['usCount']]);
                                    if (!$result) {
                                        return $this->response->setJSON(['error' => 'Failed to insert user story']);
                                    }
                                } else {
                                    if ($duplicate == $userstory_count && $flag == 0) {
                                        return $this->response->setJSON(['warning' => 'The file is already uploaded']);
                                    } else if ($duplicate == $userstory_count && $flag == 1) {

                                        return $this->response->setJSON(['success' => 'successfully uploaded']);
                                    }

                                }


                            }


                            $documentData = [
                                'r_module_id' => BACKLOG_MODULE, // module id
                                'r_document_type_id' => 3, //$documentType
                                'r_reference_id' => $pblId, // backlog id
                                'document_name' => $fileName,
                                'document_path' => $filePath . DIRECTORY_SEPARATOR . $fileName,
                                'r_user_id_created' => session('employee_id'), // example value
                                'is_deleted' => 'N',
                            ];

                            $res = $this->documentModel->insertDocument($documentData);
                            //To store the Action

                            if ($res) {
                                $actionData = 'User story document ' . $fileName . ' is uploaded';
                                $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
                                Events::trigger('log_actions', $action);
                                return $this->response->setJSON(['success' => true, 'message' => 'User stories imported successfully']);
                            }
                            return $this->response->setJSON(['success' => 'User stories uploaded successfully']);
                        } else {
                            return $this->response->setJSON(['error' => 'The Missing Mandatory Fields. Required fields are (ID,Description,ID,As a / an,I want,So that,Given,When,Then)']);
                        }
                    } else {
                        return $this->response->setJSON(['error' => 'The File is empty']);
                    }
                } else {
                    return $this->response->setJSON(['error' => 'Unable to open file']);
                }
            } else {
                return $this->response->setJSON(['error' => 'Invalid file type']);
            }
        } else {
            return $this->response->setJSON(['error' => 'No file uploaded or invalid file']);
        }
    }
    
    /**
     * @author samuel
     * @return bool
     * Purpose: This function is used by Upload_userstories function in B-controller to check userstory 
     */

    public function checkuserstory($data, $pblId):bool
    {
        // Retrieve all user stories associated with the specified backlog item
        $userStoryDetails = $this->userStoryModel->getUserStoriesByBacklogItem($pblId);

        // Iterate through each user story to check for duplicates
        foreach ($userStoryDetails as $value) {
            // Concatenate and trim the 'as_a_an', 'i_want', and 'so_that' fields from the user story
            $userstories = trim($value['as_a_an'] . $value['i_want'] . $value['so_that']);

            // Compare the provided user story data with the concatenated user story details
            if (trim($data) === $userstories) {
                // Return false if a duplicate user story is found
                return false;
            }
        }

        // Return true if no duplicates were found
        return true;
    }

    /**
     * @author Abinandhan
     * @return response
     * Purpose:  This function is used to get Userstories details with limit,offset,filters applied
     */
    public function filterUserStories(): Response
    {
        // Retrieve the JSON input from the request and decode it into an associative array
        $jsonInput = $this->request->getJSON(true);

        // Extract the 'filter' parameter from the JSON input, if it exists
        $filter = isset($jsonInput['filter']) ? $jsonInput['filter'] : null;

        // Check if a filter was provided
        if ($filter) {
            // Call the model method to get user stories based on the filter
            $filteredData = $this->userStoryModel->getuserstories($filter);

            // Return a JSON response with the filtered data
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $filteredData,
            ]);
        } else {
            // Return a JSON response indicating an error, with the missing filter information
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Filter parameter is missing',
            ]);
        }
    }

    /**
     * @author Murugadass
     * @return response
     * Purpose: This function is used to get the user stories of a epic
     */
    public function userstoryByEpic($id): Response
    {
        // Retrieve the user stories associated with the given epic ID from the model
        $userStoryName = $this->userStoryModel->getuserstoryByepic($id);

        // Return the user stories as a JSON response
        return $this->response->setJSON($userStoryName);
    }

    /**
     * @author vishva,stervin richard
     * @return Response
     * @purpose  This function is used to Adduserstory points for poker
     */
    public function addUserStoryPoint()
    {
        $inputData = $this->request->getPost();
        $data = [
            'userStoryId' => $inputData['userStoryId'],
            'storyPoint' => $inputData['storyPoint']
        ];
        $result = $this->backlogModel->addUserStoryPoint($data);
        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => $result]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => $result]);
        }
    }

    /**
     * @author Vigneshwari
     * @return response
     * @purpose to delete the user story
     */
    public function deleteUserStory($pId, $pblId, $usId): Response
    {
        // Retrieve the status ID of the user story to be deleted
        $userStoryStatusId = ($this->userStoryModel->getUserStoryById($usId))[0]['status_id'];

        // Check if the status ID indicates the user story is in a sprint
        if ($userStoryStatusId == 18) {
            // Return an error response if the user story is in a sprint
            return $this->response->setJSON(['success' => false, 'message' => 'Stories in sprint cannot be deleted']);
        }

        // Attempt to delete the user story from the backlog item model
        $res = $this->backlogItemModel->deleteItem($usId, ['orvexa_user_story', 'user_story_id']);

        // Prepare action data for logging the delete action
        $actionData = "User Story ID {$usId} is deleted";
        $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);

        // Trigger an event to log the action
        Events::trigger('log_actions', $action);

        // Return success or failure response based on the result of the deletion
        if ($res) {
            return $this->response->setJSON(['success' => true, 'message' => 'Deleted Successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Not Deleted']);
    }

    public function downloadUserStories()
     {
         // Retrieve the 'pblid' (backlog item ID) from the GET request parameters
         $backlogItemId = $this->request->getGet('pblid');
     
         // Fetch user stories associated with the specified backlog item ID
         $userStoryDetails = $this->userStoryModel->getUserStoriesByBacklogItem($backlogItemId);
     
         // Initialize an empty array to store formatted user stories
         $userStory = [];
     
         // Loop through each user story detail and format the data
         foreach ($userStoryDetails as $value) {
             $userStory[] = [
                 "epic_id" => $value['epic_id'],                          // Store the epic ID
                 "epic_description" => $value['epic_description'],        // Store the epic description
                 "user_story_id" => $value['user_story_id'],              // Store the user story ID
                 "as_a_an" => $value['as_a_an'],                          // Store the 'As a/an' description
                 "i_want" => $value['i_want'],                            // Store the 'I want' description
                 "so_that" => $value['so_that'],                          // Store the 'So that' description
                 "given" => $value['given'],                              // Store the 'Given' condition
                 "us_when" => $value['us_when'],                          // Store the 'When' condition
                 "us_then" => strip_tags($value['us_then']),              // Strip HTML tags from the 'Then' condition and store
                 "condition_text" => strip_tags($value['condition_text']),// Strip HTML tags from the condition text and store
             ];
         }
     
         // Generate a report for the user stories with the title "User Story"
         $this->report->generateReport("User Story", $userStory, true, null);
     }

     /**
     * Call the helper function to dowload the respective sample file 
     * @return Response
     */
    public function downloadReference($fileName):Response
    {
        helper('file_helper');
        return downloadSampleFile($fileName);
    }

    public function comments(): Response
    {
        // Retrieve the JSON input from the request body and decode it as an associative array
        $jsonInput = $this->request->getJSON(true);

        // Check if the 'data' key exists within the JSON input; otherwise, set it to null
        $array = isset($jsonInput['data']) ? $jsonInput['data'] : null;

        // Add the current user ID from the session to the 'r_user_id' field of the array
        $array['r_user_id'] = session()->get("employee_id");

        // Attempt to insert the comment into the database
        $res = $this->backlogModel->insertComment($array);

        // Retrieve the updated list of comments from the database
        $data = $this->backlogModel->getComments();

        // Check if the insertion was successful
        if ($res) {
            // If successful, return the updated list of comments as a JSON response with a success status
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $data,
            ]);
        } else {
            // If the insertion failed, return an error message with a status of 'error'
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'empty data',
            ]);
        }
    }


    public function generateFibonacci($endValue)
    {
        $this->settingsConfigModelObj = model(\App\Models\Admin\SettingConfigModel::class);
        $pokerData = $this->settingsConfigModelObj->getPokerLimit();
        $endValue = $pokerData[0]["settings_value"];
        $fibonacci = array(0, 1);
        if ($endValue == 0) {
            return [];
        } elseif ($endValue == 1) {
            // return $this->response->setJSON(['success' => true, 'message' => array($fibonacci[0])]);
            return array($fibonacci[0]);
        } elseif ($endValue == 2) {
            // return $this->response->setJSON(['success' => true, 'message' => $fibonacci]);
            return $fibonacci;
        } else {

            while ($fibonacci[count($fibonacci) - 1] <= $endValue) {
                $temp = $fibonacci[count($fibonacci) - 1] + $fibonacci[count($fibonacci) - 2];
                if ($temp <= $endValue) {
                    $fibonacci[count($fibonacci)] = $temp;
                } else {
                    break;
                }
            }
            unset($fibonacci[2]);
            $rearrangedFibonaaci = array_values($fibonacci);
        }
        // return $this->response->setJSON(['success' => true, 'message' => $rearrangedFibonaaci]);
        return $rearrangedFibonaaci;
        // return $fibonacci;
    }

    /**
     * @author vishva,stervin richard
     * @return Response
     * @purpose  This function is used to insert fibonacci
     */

    public function insertFibonacciSettings():Response
    {
        $data = $this->request->getPost();
        $result = $this->fibonacciModel->saveFibonacci($data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Fibonacci setting added successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Fibonacci setting not added successfully']);
        }
    }

}