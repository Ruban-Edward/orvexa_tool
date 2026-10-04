<?php

/**
 * @author MURUGADASS,Abinandhan,Samuel,Vigneshwari
 *
 * @modified-by MURUGADASS
 * @created-date 04-07-2024
 * @modified-date 31-07-2024
 *
 * @description: This controller is controlling the overall Backlog module
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\CustomReportController as Report;
use App\Helpers\CustomHelpers;
use App\Helpers\authenticator_helper;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

class BacklogController extends BaseController
{

    protected $backlogModel;
    protected $backlogItemModel;
    protected $epicModel;
    protected $userStoryModel;
    protected $userStoryConditionModel;
    protected $taskModel;
    protected $documentModel;
    protected $sprintModel;
    protected $report;
    protected $fibonacciModel;
    protected $pokerModel;
    protected $settingsConfigModelObj;

    public function __construct()
    {
        $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $this->backlogItemModel = model(\App\Models\Backlog\BacklogItemModel::class);
        $this->epicModel = model(\App\Models\Backlog\EpicModel::class);
        $this->userStoryModel = model(\App\Models\Backlog\UserStoryModel::class);
        $this->userStoryConditionModel = model(\App\Models\Backlog\UserStoryConditionModel::class);
        $this->taskModel = model(\App\Models\Backlog\TaskModel::class);
        $this->documentModel = model(\App\Models\Backlog\DocumentModel::class);
        $this->sprintModel = model(\App\Models\SprintModel::class);
        $this->fibonacciModel = model(\App\Models\Backlog\BacklogFibonacci::class);
        $this->pokerModel = model(\App\Models\Backlog\BacklogPoker::class);
        $this->report = service('generateReport');
    }

    /**
     * @author Murugadass
     * @return string
     * Purpose: The purpose of this function is to get the product details of a user and return it to the view
     */

    public function products(): string
    {
        $breadcrumbs = [
            'Products' => '',
        ];

        $productDetails = $this->backlogModel->getUserProductDetails(session('employee_id'));
        if (count($productDetails) == 0) {
            return $this->template_view('dashboard/NodataView', 'Product', 'No Products');
        }
        $data['product_details'] = $productDetails;

        foreach ($data['product_details'] as $key => $value) {
            $data['product_details'][$key]['last_updated'] = CustomHelpers::formatDate(date('Y-m-d', strtotime($value['last_updated'])));
        }

        return $this->template_view('backlog/products', $data, 'Products', $breadcrumbs);
    }

    /**
     * @author Abinandhan
     * @return string
     * Purpose: This function is used to return the backlogitems page of a particular product
     */

    public function backlogItems(): string
    {
        $pId = $this->request->getGet('pid');
        $breadcrumbs = [
            'Products' => ASSERT_PATH . 'backlog/productbacklogs',
            'Backlog items' => ''
        ];

        if (!$pId || !authenticateUser($pId)) {
            return $this->template_view('dashboard/NodataView', null, 'No data', $breadcrumbs);
        }

        $backlogItems = $this->backlogModel->getBacklogDetails(['pid' => $pId]);

        foreach ($backlogItems as $item) {
            if ($item['total'] == 0) {
                continue;
            }

            //to consider only the remaining status(Not completed)
            $remaining = $item['total'] - $item['completed'];

            //Changing the status of the backlog item based on the user stories
            switch (true) {
                case ($item['total'] == $item['completed']):
                    //If all the user stories are in completed status the backlog item status will be converted to Completed status
                    $this->backlogItemModel->backlogStatus(COMPLETED, $item['backlog_item_id']);
                    break;

                case ($remaining == $item['new']):
                    //If all the remaining user stories are in new reauirement status the backlog item status will be converted to user stories created
                    $this->backlogItemModel->backlogStatus(USERSTORIES_CREATED, $item['backlog_item_id']);
                    break;

                case ($item['ready_for_brainstorming'] == $remaining):
                    //If all the remaining user stories are in ready for brainstorming status the backlog item status will be converted to brainstorming
                    $this->backlogItemModel->backlogStatus(BRAINSTORMING, $item['backlog_item_id']);
                    break;

                case ($remaining == $item['in_sprint']):
                    //If all the remaining user stories are in in_sprint status the backlog item status will be converted to Completed status
                    $this->backlogItemModel->backlogStatus(IN_SPRINT, $item['backlog_item_id']);
                    break;

                case ($item['brainstorming_completed'] == $remaining):
                    //If all the remaining user stories are in brainstorming completed status the backlog item status will be converted to brainstorming Completed status
                    $this->backlogItemModel->backlogStatus(BRAINSTORMING_COMPLETED, $item['backlog_item_id']);
                    break;

                case ($remaining == $item['ready_for_sprint']):
                    //If all the remaining user stories are in ready for sprint status the backlog item status will be converted to  ready for sprint status
                    $this->backlogItemModel->backlogStatus(READY_FOR_SPRINT, $item['backlog_item_id']);
                    break;

                case ($item['in_sprint'] != 0):
                    //If some of  the user stories are in in sprint status the backlog item status will be converted to  in sprint partial status
                    $this->backlogItemModel->backlogStatus(IN_SPRINT_PARTIAL, $item['backlog_item_id']);
                    break;

                case ($item['ready_for_sprint'] != 0):
                    //If some of  the user stories are  in ready for sprint status the backlog item status will be converted to  ready for sprint status
                    $this->backlogItemModel->backlogStatus(READY_FOR_SPRINT, $item['backlog_item_id']);
                    break;

                case ($item['brainstorming_completed'] != 0):
                    //If some of  the user stories are in brainstorming completed status the backlog item status will be converted to partially brainstormed status
                    $this->backlogItemModel->backlogStatus(PARTIALLY_BRAINSTORMED, $item['backlog_item_id']);
                    break;

                case ($item['ready_for_brainstorming'] != 0):
                    //If some of  the user stories are in brainstorming completed status the backlog item status will be converted to partially brainstormed status
                    $this->backlogItemModel->backlogStatus(5, $item['backlog_item_id']);
                    break;
            }
        }

        $data = [
            'p_id' => $pId,
            'product_name' => $this->backlogModel->getProductDetails($pId),
            'tracker' => $this->taskModel->getTrackers(),
            'backlog_item_priority' => ['Low', 'Medium', 'High'],
            't_shirt_size' => $this->backlogItemModel->getTshirtSizes($pId),
            'backlog_item_customer' => $this->backlogItemModel->getBacklogItemCustomer(),
            'backlog_item_status' => $this->backlogModel->getStatus(BACKLOG_MODULE),
            'backlogItemDetails' => $backlogItems,
            'totalCount' => $this->backlogItemModel->getBacklogItemstotal($pId)
        ];
        return $this->template_view('backlog/backlogItems', $data, 'Backlog items', $breadcrumbs);
    }


    /**
     * @author Abinandhan
     * @return Response
     * Purpose: This function is used to add a backlog item
     */
    //

    public function addBacklog(): Response
    {
        // Get the employee ID from the session
        $employeeId = session('employee_id');

        // Get the product ID from the request
        $pId = $this->request->getPost('pId');

        // Get the last backlog priority order for the given product ID
        $result = $this->backlogItemModel->getLastPriorityOrder($pId);

        // Set the order to 1 if no previous backlog items exist, otherwise increment the last order by 1
        $order = 1;
        if (!empty($result))
            $order = $result[0]['backlog_order'] + 1;

        // Get all posted data and assign additional fields
        $backlog = $this->request->getPost();
        $backlog['r_product_id'] = $pId;
        $backlog['r_user_id_created'] = session('employee_id');
        $backlog['backlog_order'] = $order;

        // Validate the input data against model rules
        $validationErrors = $this->hasInvalidInput($this->backlogItemModel, $backlog);
        if ($validationErrors !== true) {
            // Return errors if validation fails
            return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);
        }

        // Insert the new backlog item into the database and get the insert ID
        $insertId = $this->backlogItemModel->insertData($backlog);

        // Check if files were uploaded in the request
        if ($this->request->getFiles()) {
            // Get document types and files from the request
            $documentTypes = $this->request->getPost('fileType');
            $files = $this->request->getFiles()['fileInput'];

            foreach ($files as $key => $file) {
                // Get the document type for the current file
                $documentType = $documentTypes[$key];
                // Define the path to save the file
                $filePath = WRITEPATH . 'uploads';
                // Get the original file name
                $fileName = $file->getName();
                // Move the uploaded file to the designated directory
                $file->move($filePath, $fileName);

                // Prepare data for the document record
                $data = [
                    'r_module_id' => BACKLOG_MODULE, // Define the module ID (backlog module in this case)
                    'r_document_type_id' => $documentType, // Document type ID
                    'r_reference_id' => $insertId, // The backlog ID (reference ID)
                    'document_name' => $fileName,
                    'document_path' => $filePath . DIRECTORY_SEPARATOR . $fileName,
                    'r_user_id_created' => $employeeId, // ID of the user who created the record
                    'is_deleted' => 'N', // Mark the record as not deleted
                ];

                // Insert the document data into the database
                $this->documentModel->insertDocument($data);

                // Retrieve the name of the document type for logging purposes
                $documentTypeName = $this->documentModel->getDocumentType($documentType);
                // Prepare log data indicating that a document was added
                $actionData = $documentTypeName[0]['document_type'] . " " . $fileName . ' is Added';
                // Form action data and trigger the logging event
                $action = formActionData(__FUNCTION__, $insertId, $pId, $actionData);
                Events::trigger('log_actions', $action);
            }
        }

        // Prepare log data for the backlog item creation
        $actionData = $this->request->getPost('backlog_item_name') . ' is added';
        // Form action data and trigger the logging event
        $action = formActionData(__FUNCTION__, $insertId, $pId, $actionData);
        Events::trigger('log_actions', $action);

        // Return a success response indicating that the backlog item was added successfully
        return $this->response->setJSON(['success' => true, 'message' => 'Backlog added successfully']);
    }

    /**
     * @author Abinandhan
     * @return Response
     * Purpose: This function is used to dalete the backlog item by Id.
     */
    public function deleteBacklog(): Response
    {
        // Get the backlog item ID and product ID from the request
        $pblId = $this->request->getGet('pblid');
        $pId = $this->request->getGet('pid');

        // Retrieve backlog details using the ID
        $data = $this->backlogModel->getBacklogDetails(['id' => $pblId]);

        // Check if the backlog item is in a sprint (status ID 9 or 10)
        if ($data["r_module_status_id"] == 9 || $data["r_module_status_id"] == 10) {
            // If the backlog item is in a sprint, prevent deletion and return an error message
            return $this->response->setJSON(['success' => false, 'message' => 'Backlog in sprint cannot be deleted']);
        }
        // If the backlog item is not in a sprint, proceed with deletion
        $this->backlogItemModel->deleteItem($pblId, ['orvexa_backlog_item', 'backlog_item_id']);

        // Log the action of deleting the backlog item
        $actionData = $data['backlog_item_name'] . ' is deleted';
        $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
        Events::trigger('log_actions', $action);

        // Return a success response indicating the backlog item has been deleted
        return $this->response->setJSON(['success' => true, 'message' => 'Backlog item has been deleted.']);
    }


    /**
     * @author Abinandhan
     * @return Response
     * Purpose: This function is used to get backlogItem details for delete and update using ajax
     */
    public function getbacklogItemById(): Response
    {
        // Get the backlog item ID from the request
        $pId = $this->request->getGet('pid');

        // Retrieve backlog details using the provided ID
        $data = $this->backlogModel->getBacklogDetails(['id' => $pId]);

        // Retrieve associated files for the backlog item from the document model
        $data['files'] = $this->documentModel->getDocumentDetails($pId, BACKLOG_MODULE);

        // Return the backlog details along with associated files as a JSON response
        return $this->response->setJSON($data);
    }


    /**
     * @author Abinandhan
     * @return Response
     * Purpose: This function is used to update a backlog item
     */
    public function updateBacklog(): Response
    {
        // Check if 'pblid' parameter is provided in the request
        if ($this->request->getGet('pblid')) {
            $pblId = $this->request->getGet('pblid');  // Get the backlog item ID
            $pId = $this->request->getGet('pid');  // Get the product ID
            $before = $this->backlogModel->getBacklogDetails(['id' => $pblId]);  // Fetch backlog details before update

            $updatebacklog = $this->request->getPost();  // Get updated backlog data from the POST request

            $updatebacklog['r_product_id'] = $pId;  // Assign product ID to the backlog data
            $updatebacklog['backlog_item_id'] = $pblId;  // Assign backlog item ID to the backlog data

            // Validate the updated backlog data
            $validationErrors = $this->hasInvalidInput($this->backlogItemModel, $updatebacklog);
            if ($validationErrors !== true) {
                return $this->response->setJSON(['success' => false, 'error' => $validationErrors]);  // Return validation errors if any
            }

            // Check if the status requires user stories and ensure they exist
            if (in_array($updatebacklog['r_module_status_id'], [5, 6, 7, 8, 9, 10, 12])) {
                if ($this->userStoryModel->countUserStories($pblId) == 0) {
                    return $this->response->setJSON(['success' => false, 'error' => "There is no user story. User stories required for this status"]);
                }
            }

            // Change user story status if the backlog status is set to 'brainstorming'
            if ($updatebacklog['r_module_status_id'] == 5) {
                // Change status of user stories linked to this backlog item
                $this->userStoryModel->changeStatus(13, 14, $pblId);
            }

            // Update the backlog item in the database
            $this->backlogItemModel->updatebacklogById($updatebacklog);

            $after = $this->backlogModel->getBacklogDetails(['id' => $pblId]);  // Fetch backlog details after update

            // Prepare a list of header names for logging changes
            $header = [
                'Backlog ID',
                'Product ID',
                'Backlog Name',
                'Tracker ID',
                'Module Status',
                'Customer ID',
                'Tracker',
                'Customer',
                'Status',
                'Priority',
                'Backlog Order',
                'Description',
                'T-Shirt Size ID',
                'T-Shirt Size',
                'Product',
                'Completed US',
                'Total US',
                'Total Tasks',
                'Completed Tasks'
            ];

            $header = array_combine(array_keys($after), $header);  // Combine header names with their corresponding keys

            // Determine which fields were changed
            $keys = array_intersect_key($before, $after);
            $differentValues = array_diff_assoc($keys, $after);
            $differentKeys = array_keys($differentValues);

            $changes = '';

            // Log any changes made to the backlog item
            if ($after != $before) {
                $changes = 'In Backlog ' . $updatebacklog['backlog_item_name'] . ', ';
            }

            foreach ($differentKeys as $value) {
                if (!is_numeric($before[$value]) && !is_numeric($after[$value])) {
                    $changes .= '<br>' . $header[$value] . ' Updated From ' . '<b>' . $before[$value] . '</b>' . ' to ' . '<b>' . $after[$value] . '</b>';
                }
            }

            // Log the changes made to the backlog item
            $actionData = (empty($changes)) ? "No changes" : trim($changes, " |");
            if (!empty($changes)) {
                $actionData = trim($changes, " |");
                $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
                Events::trigger('log_actions', $action);
            }

            // Handle file uploads associated with the backlog item
            if ($this->request->getFiles()) {
                $documentTypes = $this->request->getPost('fileType');
                $files = $this->request->getFiles()['fileInput'];

                foreach ($files as $key => $file) {
                    $documentType = $documentTypes[$key];
                    $filePath = WRITEPATH . 'uploads';  // Define the file path for uploads
                    $fileName = $file->getName();  // Get the uploaded file name
                    $file->move($filePath, $fileName);  // Move the file to the specified directory

                    // Prepare data for inserting the file details into the database
                    $data = [
                        'r_module_id' => BACKLOG_MODULE,  // module ID
                        'r_document_type_id' => $documentType,  // document type
                        'r_reference_id' => $updatebacklog['backlog_item_id'],  // backlog ID
                        'document_name' => $fileName,  // file name
                        'document_path' => $filePath . DIRECTORY_SEPARATOR . $fileName,  // file path
                        'r_user_id_created' => session('employee_id'),  // user ID of the creator
                        'is_deleted' => 'N',  // is_deleted flag
                    ];

                    // Insert the document details into the database
                    $documentTypeName = $this->documentModel->getDocumentType($documentType);
                    $result = $this->documentModel->insertDocument($data);

                    // Log the document addition action
                    $actionData = $documentTypeName[0]['document_type'] . " " . $fileName . " is added";
                    $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
                    Events::trigger('log_actions', $action);
                }
            }

            // Return a successful JSON response
            return $this->response->setJSON(['success' => true, 'message' => "Backlog updated successfully"]);
        } else {
            // Handle status update requests when 'pblid' is not provided
            $pblId = $this->request->getPost('item_id');
            $statusId = $this->request->getPost('status_id');

            // Fetch the backlog status before update
            $beforestatus = $this->backlogModel->getBacklogDetails(['id' => $pblId])['status_name'];

            // Check if the status requires user stories and ensure they exist
            if (in_array($statusId, [5, 6, 7, 8, 9, 10, 12])) {
                if ($this->userStoryModel->countUserStories($pblId) == 0) {
                    return $this->response->setJSON(['success' => false, 'message' => "There is no user story. User stories required for this status"]);
                }
            }

            // When the backlog item status is 'brainstorming', update user story status
            if ($statusId == 5) {
                // Change status of user stories linked to this backlog item
                $this->userStoryModel->changeStatus(13, 14, $pblId);
            }

            // Update the backlog item status in the database
            $res = $this->backlogItemModel->backlogStatus($statusId, $pblId);

            // Fetch the backlog status after update
            $backlog = $this->backlogModel->getBacklogDetails(['id' => $pblId]);
            $afterStatus = $backlog['status_name'];

            // Log the status update action
            $actionData = "In backlog " . $backlog['backlog_item_name'] . ", status updated from " . "<b>" . $beforestatus . "</b>" . " to " . "<b>" . $afterStatus . "</b>";
            $action = formActionData(__FUNCTION__, $pblId, $backlog['r_product_id'], $actionData);
            Events::trigger('log_actions', $action);

            // Return the appropriate JSON response based on the update result
            if ($res) {
                return $this->response->setJSON(['success' => true, 'message' => 'Status changed successfully']);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to change the status']);
        }
    }


    /**
     * @author Abinandhan
     * @return Response
     * Purpose: This function is used to get Backlogitem details with limit,offset,filters applied
     */
    public function filterBacklogItem(): Response
    {
        // Retrieve JSON input from the request and decode it into an associative array
        $jsonInput = $this->request->getJSON(true);

        // Check if 'filter' key exists in the JSON input and assign its value to $filter
        $filter = isset($jsonInput['filter']) ? $jsonInput['filter'] : null;

        // If filter criteria are provided
        if ($filter) {
            // Retrieve filtered backlog details based on the provided filter criteria
            $filteredData = $this->backlogModel->getBacklogDetails($filter);

            // Return the filtered data with a success status in JSON format
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $filteredData,
            ]);
        } else {
            // If no filter criteria are provided, return an error message in JSON format
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid filter data',
            ]);
        }
    }


    /**
     * @author Murugadass
     * @return mixed
     * Purpose: This function is to get the Backlogitem details for backlogItem Page
     */
    public function backlogItemDetails(): mixed
    {
        // Retrieve the product ID and backlog item ID from the GET request
        $pId = $this->request->getGet('pid');
        $pblId = $this->request->getGet('pblid');

        // If either the product ID or backlog item ID is missing, redirect to the 'no access' page
        if (!$pId || !$pblId) {
            return redirect()->to('/no_access');
        }

        // Authenticate the user for the given product and backlog item; if authentication fails, redirect to 'no access'
        if (!authenticateUser($pId, $pblId)) {
            return redirect()->to('/no_access');
        }

        // Fetch the backlog item details from the model based on the backlog item ID
        $data = $this->backlogModel->getBacklogDetails(['id' => $pblId]);

        // Add the product ID and backlog item ID to the data array for use in the view
        $data['pId'] = $pId;
        $data['pblId'] = $pblId;

        // Fetch the associated documents for the backlog item and add them to the data array
        $data['document'] = $this->documentModel->getDocumentDetails($pblId, BACKLOG_MODULE);

        // Define the breadcrumbs for navigation within the view
        $breadcrumbs = [
            'Products' => ASSERT_PATH . 'backlog/productbacklogs',
            'Backlog items' => ASSERT_PATH . 'backlog/backlogitems?pid=' . $pId,
            $data['backlog_item_name'] => ''
        ];

        // Render the view with the backlog item details, breadcrumbs, and page title
        return $this->template_view('backlog/backlogItemDetails', $data, 'Backlog Item Details', $breadcrumbs);
    }

    /**
     * @author Murugadass
     * @return Response
     * Purpose: This function is to view the file attached for the backlog item in BacklogItem Details
     */
    public function view($fileName): Response
    {
        // Define the allowed directory
        $allowedDir = WRITEPATH . 'uploads/';

        // Construct the full path
        $filePath = $allowedDir . $fileName;

        // Normalize the path to remove any ../ and ./ components
        $realPath = realpath($filePath);

        // Normalize the allowed directory path
        $normalizedAllowedDir = realpath($allowedDir);

        // Check if the file exists and is within the allowed directory
        if ($realPath === false || strpos($realPath, $normalizedAllowedDir) !== 0 || !file_exists($realPath) || !is_readable($realPath)) {
            return $this->response->setStatusCode(404)->setBody('File not found or access denied');
        }

        // Get the file's MIME type
        $mimeType = mime_content_type($realPath);

        // Read the file content
        $fileContent = file_get_contents($realPath);

        // Set the appropriate headers for viewing the file
        $this->response->setHeader('Content-Type', $mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . basename($realPath) . '"');

        // Output the file content
        return $this->response->setBody($fileContent);
    }

    /**
     * @author Murugadass
     * @return response
     * Purpose: The purpose of this function is to download the file attached for the backlog item
     */
    public function download($fileName): Response
    {
        // Define the allowed directory
        $allowedDir = WRITEPATH . 'uploads/';

        // Construct the full path
        $filePath = $allowedDir . $fileName;

        // Normalize the path to remove any ../ and ./ components
        $realPath = realpath($filePath);

        // Normalize the allowed directory path
        $normalizedAllowedDir = realpath($allowedDir);

        // Check if the file exists and is within the allowed directory
        if ($realPath === false || strpos($realPath, $normalizedAllowedDir) !== 0 || !file_exists($realPath) || !is_readable($realPath)) {
            return $this->response->setStatusCode(404)->setBody('File not found or access denied');
        }

        // File is valid, proceed with download
        return $this->response->download($realPath, null)
            ->setContentType(mime_content_type($realPath));
    }

    /**
     * @author Murugadass
     * @return response
     * Purpose: This function is to Delete the Uploaded Document by the user in BacklogItemDetails
     */
    public function deleteDocument(): Response
    {
        // Retrieve the document ID, product ID, and backlog item ID from the POST request
        $docId = $this->request->getPost('docId');
        $pId = $this->request->getPost('pId');
        $pblId = $this->request->getPost('pblId');

        // Fetch the document details based on the document ID
        $document = ($this->documentModel->getDocumentDetails(0, 0, $docId))[0];

        // Attempt to delete the document from the database
        $res = $this->backlogItemModel->deleteItem($docId, ['orvexa_document', 'document_id']);

        // If the document was successfully deleted, log the action and return a success response
        if ($res) {
            // Prepare the action data for logging
            $actionData = $document['document_type'] . " " . $document['document_name'] . " " . "is removed";

            // Log the action using the formActionData function and trigger the log_actions event
            $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);
            Events::trigger('log_actions', $action);

            // Return a JSON response indicating success
            return $this->response->setJSON(['success' => true, 'message' => 'Document removed successfully']);
        }
        // If deletion failed, return a JSON response indicating failure
        return $this->response->setJSON(['success' => false, 'message' => 'An error occurred while deleting document']);
    }

    /**
     * @author Murugadass
     * Purpose: This function is used to add a epic in the database
     * @return response
     */

    public function addEpic(): Response
    {
        // Load the validation service
        $validation = \Config\Services::validation();

        // Retrieve parameters from the request
        $pId = $this->request->getget('pid');  // Project ID
        $pblId = $this->request->getget('pblid');  // Backlog ID

        // Prepare data for validation and insertion
        $data = [
            'pbl_id' => $pblId,
            'epic_description' => $this->request->getPost('epic_description'),
        ];

        // Get validation rules and messages for the epic
        $epicValidationRules = $this->epicModel->getEpicValidationRules();
        $epicValidationMessages = $this->epicModel->getEpicValidationMessages();

        // Set validation rules and messages
        $validation->setRules($epicValidationRules, $epicValidationMessages);

        // Run validation on the data
        if (!$validation->run($data)) {
            // If validation fails, return errors as JSON response
            $errors = $validation->getErrors();
            return $this->response->setJSON(['success' => false, 'error' => $errors]);
        }

        // Insert the epic data into the database
        $result = $this->epicModel->insertData($data);

        // Prepare action data for logging
        $actionData = $data['epic_description'] . ' is added';
        $action = formActionData(__FUNCTION__, $pblId, $pId, $actionData);

        // Trigger an event to log the action
        Events::trigger('log_actions', $action);

        // Return success or failure response based on the insertion result
        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Epic added successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to add Epic']);
    }




    public function historyDataDetails()
    {
        // Retrieve the JSON input from the request body and decode it as an associative array
        $jsonInput = $this->request->getJSON(true);

        // Check if 'pId' exists within the JSON input, otherwise set it to null
        $pId = $jsonInput['pId'] ?? null;

        // Check if 'pId' exists within the JSON input, otherwise set it to null
        $pblId = $jsonInput['pblId'] ?? null;

        // Retrieve the action history for the provided project ID
        $history = $this->backlogModel->getActionHistory($pId, $pblId);

        // Check if the history data is available
        if ($history) {
            // If history is found, return it as a JSON response with a success status
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $history,
            ]);
        } else {
            // If no history data is found, return an error message indicating invalid data
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid data',
            ]);
        }
    }



    public function show_error($message, $status_code = 500)
    {
        http_response_code($status_code); // Set the HTTP response status code
        echo "<h1>Error: $status_code</h1>";
        echo "<p>$message</p>";
        exit; // Stop further script execution
    }

}
