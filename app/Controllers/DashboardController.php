<?php

namespace App\Controllers;
use Config\SprintModelConfig;
use App\Helpers\CustomHelpers;
/**
 * UserController.php
 *
 * @category   Controller
 * @author     Rahul S,Stervin Richard
 * @created    09 July 2024
 * @purpose    Manages user profiles, dashboards, and related functionalities, including handling backlog and 
 *             meeting data for local scrum dashboards.
 */


class DashboardController extends BaseController
{

    /**
    * User::__construct()
    *
    * User Controller costructor
    */ 
    protected $dashboardModel;
    protected $backlogModel;
    protected $sprintModel;
    protected $reportModel;
    protected $config;
    protected $productId;
    protected $userId;
    protected $products;

    public function __construct()
    {
        $this->dashboardModel = model(\App\Models\Dashboard\DashboardModel::class);
        $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $this->sprintModel = model('SprintModel');
        $this->reportModel = model('CustomReportModel');
        $this->config = new SprintModelConfig();
        $this->userId  = session()->get('employee_id');
        $this->products = $this->backlogModel->getUserProduct($this->userId);
        $this->productId = array_column($this->products,'product_id');
    }

    /**
    * @author Stervin Richard 
    * User::dashboardView()
    *
    * check the user dashboard page according to the user products and redirect to that dashboard page
    */ 
    public function dashboardView()
    {
        $dashboardData = [
            'user_id' => $this->userId,
            'product_id' => $this->productId,
            'products' => $this->products
        ];
        if(count($this->products) > 1) {
            return $this->multipleProductDashboard($dashboardData);
        }
        if(count($this->products) == 1) {
            return $this->showProductDashboard($this->productId[0]);
        }
        return $this->template_view('dashboard/NodataView', 'Product', 'No Products');
    }

    /**
     * @author Stervin Richard
     * 
     * to fetch sprint and product details for
     * displaying in multiple product dashboard page for the user having multiple products     
     */
    public function multipleProductDashboard($dashboardData){
        $sprintStatuses=$this->config->sprintStatuses;
        $pendingTaskStatuses=$this->config->pendingTaskStatuses;
        $backlogStatuses=$this->config->backlogStatuses;

        $productAndSprintStatus = [
            'product_id' => $dashboardData['product_id'],
            'module_status_id' => $sprintStatuses['ongoing'],
        ];

        $productAndBacklogStatus = [
            'product_id' => $dashboardData['product_id'],
            'module_status_id' => [$backlogStatuses['completed_backlogs']]
        ];

        $dashboard = [
            'products' => $dashboardData['products'],
            'backlogs' => $this->dashboardModel->getBacklogPriority($productAndBacklogStatus),
            'sprints' => $this->dashboardModel->getSprintPerformance($productAndSprintStatus),
            'on_track_and_delay' => $this->dashboardModel->productOnTrack($productAndSprintStatus),
            'pending_task_status' => $pendingTaskStatuses
        ];
        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView'
        ];

        return $this->template_view('dashboard/dashboard', $dashboard, 'Dashboard',$breadcrumbs);
    }

    /**
     * @author Rahul S
     * @return string
     * Purpose: This function is used to fetch sprint and product details for
     *          displaying in dashboard 
     */
    public function showProductDashboard($productId = null) : string{
        // Get the product ID from POST request, if not provided as a parameter
        $productId = $this->request->getPost('product-id') ?? $productId;

        $allProductId = $this->productId;
        if(!in_array($productId,$allProductId)){
            return view('unauthorized');        
        }

        // Retrieve product details based on the given product ID
        $product = $this->backlogModel->getProductDetails($productId);

        // Get the sprint status configuration
        $sprintStatus = $this->config->sprintStatuses;

        // Get the running sprint ID(s) for the product based on status
        $runningSprints = $this->dashboardModel->getRunningSprintId([
            'productId' => $productId,
            'status' => $sprintStatus['ongoing']
        ]);

        $productData = [
            'productId' => $productId,
            'sprintId' => $runningSprints
        ];

        // Check if a specific sprint version is selected via POST request
        $selectedSprintVersion = $this->request->getPost('sprint_version');
        if ($selectedSprintVersion) {
            $productData['selectedSprintVersion'] = $selectedSprintVersion;
        }

        $dashboardData = $this->showSprintDashboard($productData);

        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView',
            $product => ASSERT_PATH."dashboard/showProductDashboard/$productId"
        ];

        // Render the product dashboard template with the data
        return $this->template_view('dashboard/productDashboard', $dashboardData, $product,$breadcrumbs);
    }

    public function showSprintDashboard($productData=null) : array {

        $productId = $productData['productId'];
        $allSprintId = array_column($productData['sprintId'],'sprint_id'); 
        
        // Use selected sprint version 
        $sprintId = $productData['selectedSprintVersion'] ?? $allSprintId[0] ?? null;
        
        // Get details of the current sprint and upcomming for the product
        $sprintStatus=$this->config->sprintStatuses;
        $sprintDetails = $this->dashboardModel->getSprintDetails([
            'productId'=>$productId,
            'sprintStatus'=>  $sprintStatus
        ]);

        //Get the current sprint details of selected sprint version
        $currentSprintDetails=[];
        foreach($sprintDetails as $sprint){
            if( $sprint['sprint_id'] === $sprintId ){
                $currentSprintDetails = $sprint;
                break;
            }
        }

        // Fetch counts of backlog items grouped by their status
        $backlogStatus=$this->config->backlogStatuses;
        $backlogStatusCounts = $this->dashboardModel->getBacklogStatusCounts([
            'productId'=>$productId,
            'backlogStatus'=>  $backlogStatus
        ]);
        
        // Get counts of user stories grouped by their status
        $userStoryStatus=$this->config->userStoryStatuses; 
        $userStoryStatusCounts = $this->dashboardModel->getUserStorystatusCounts([
            'productId'=>$productId,
            'userStoryStatus'=>  $userStoryStatus['completed']
        ]);

        // Retrieve pending task of sprint for a product grouped by priority
        $pendingTaskStatus = $this->config->pendingTaskStatuses;
        $pendingTaskStatusCounts = $this->dashboardModel->getPendingTaskStatusCounts([
            'productId'=> $productId,
            'sprintId'=> $sprintId,
            'status' => $pendingTaskStatus
        ]);
        
        //Get the total task hours for the product across all sprints
        $userStoryIds=$this->dashboardModel->getAlluserStoryIds([
            'productId'=> $productId,
            'status' => $sprintStatus['completed']
        ]); 
        $totalTaskHoursPerSprint = json_encode([]);
         
        // Retrieves sum of task hours in each user story of a sprint for a product
        if(!empty($sprintId) ){
            $userStories = $this->dashboardModel->getAlluserStoryIds([                      
                'productId'=> $productId,
                'status' => $sprintStatus['ongoing'],
                'sprintId' =>  $sprintId
            ]);  

            if(!empty($userStories[0]['user_story_ids'])) {
                $userStoryCompletionHours = json_encode([]); 
            }
            else {
                $userStoryCompletionHours = json_encode([]);
            }

        } else {
            $userStoryCompletionHours = json_encode([]);
        }

        // Retrieves the daily spent hours of a sprint for a product
        $issueIds=$this->dashboardModel->getSprintTasksId([
            'productId' => $productId,
            'sprintId' => $sprintId
        ]);
        $sprintHours = json_encode([]);
       
        // // Fetch data for the burndown chart for the current sprint
        // $issueIds=$this->dashboardModel->getSprintTasksId([
        //     'productId' => $productId,
        //     'sprintId' => $sprintId
        // ]);
        // $estimatedSprintHours=$this->dashboardModel->getEstimatedSprintHours([
        //     'productId' => $productId,
        //     'sprintId' => $sprintId
        // ]);
        // $burndownChartData=[
        //     'chartData'=> $sprintHours,
        //     'estimatedHours' => $estimatedSprintHours
        // ];

        // Combine all retrieved data into a single array
        $productDetails = [
            'productId' => $productId,
            'runningSprints' => $productData['sprintId'] ,
            'currentSprintDetails' => $currentSprintDetails, 
            'sprintDetails' => $sprintDetails,
            'backlogStatusCounts' => $backlogStatusCounts,
            'userStoryStatusCounts' => $userStoryStatusCounts,
            'totalTaskHoursPerSprint' => $totalTaskHoursPerSprint,
            'userStoryCompletionHours' => $userStoryCompletionHours,
            'pendingTaskStatusCounts' => $pendingTaskStatusCounts,
            'sprintHours' => $sprintHours
        ];

        return $productDetails;
        
    }
    
    // public function fetchScrumNotes() {
    //     $userId = $this->request->getPost('user_id');
    //     $notes = $this->dashboardModel->getScrumNotes($userId);
    //     return json_encode($notes);

    // }

    public function fetchMeetings() {
        $userId = $this->request->getPost('user_id');
        $meetings =  $this->dashboardModel->userMeetings($userId);
        return json_encode($meetings);
    }

    public function fetchPendingTaskBySprintId()  {
        $sprintId = $this->request->getPost('sprint_id');
        $productId = $this->request->getPost('product_id');
        $pendingTaskStatus = $this->config->pendingTaskStatuses;
        $productData = [
            'sprintId' => $sprintId,
            'productId' => $productId,
            'status' => $pendingTaskStatus
        ];
        $pendingTasks = $this->dashboardModel->getPendingTasks($productData);
        
        return json_encode($pendingTasks);
    }
}
