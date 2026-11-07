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

class RefinementController extends BaseController
{
    protected $backlogModel;
    protected $backlogItemModel;
    protected $taskModel;
    public function __construct()
    {
        $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $this->backlogItemModel = model(\App\Models\Backlog\BacklogItemModel::class);
        $this->taskModel = model(\App\Models\Backlog\TaskModel::class);
    }
    
    /**
     * @author Abinandhan
     * @return string
     * Purpose: This function is used to return the backlogitem refinement view where user can change the priority order of a backlog item
     */
    public function refinement(): string
    {
        // Retrieve the product ID from the request's query parameters
        $pId = $this->request->getGet('pid');
        if (!$this->ownsItem('product', $pId)) {
            return $this->template_view('dashboard/NodataView', null, 'No data', ['Products' => ASSERT_PATH . 'backlog/productbacklogs']);
        }

        // Define breadcrumb navigation for the page
        $breadcrumbs = [
            'Products' => ASSERT_PATH . 'backlog/productbacklogs',
            'Backlog items' => ASSERT_PATH . 'backlog/backlogitems?pid=' . $pId,
            'Backlog refinement' => '', // Current page
        ];

        // Gather data required for the view
        $data = [
            'p_id' => $pId, // Product ID
            'productName' => $this->backlogModel->getProductDetails($pId), // Product details
            'backlogItemType' => $this->backlogModel->getBacklogDetails(['refinement' => 1, 'pid' => $pId]), // Backlog items for refinement
            'backlog_item_status' => $this->backlogModel->getStatus(BACKLOG_MODULE), // Statuses for backlog items
            'backlog_item_customer' => $this->backlogItemModel->getBacklogItemCustomer(), // Backlog item customers
            'tracker' => $this->taskModel->getTrackers(), // Trackers for tasks
        ];

        // Render the view with the provided data and breadcrumb navigation
        return $this->template_view('backlog/refinement', $data, 'Backlog Refinement', $breadcrumbs);
    }


    /**
     * @author Abinandhan
     * @return Response
     * Purpose: To change the priority of the backlog item which is changed during the refinement
     */
    public function backlogGrooming($pId)
    {
        // Get the JSON input from the request, which contains the reordered backlog items
        $jsonInput = $this->request->getJSON(true);
        $draggedData = isset($jsonInput['order']) ? $jsonInput['order'] : null;

        // Initialize an array to store the reordered backlog items
        $draggedArray = [];
        foreach ($draggedData as $item) {
            // Map each backlog item ID to its new order
            $draggedArray[$item['backlog_item_id']] = $item['backlog_order'];
        }

        // Fetch the current backlog refinement data from the model
        if ($denied = $this->denyUnlessOwns([['product', $pId]])) {
            return $denied;
        }
        $currentData = $this->backlogModel->backlogRefinement((int) $pId);

        // Initialize an array to store the current backlog item order
        $currentArray = [];
        foreach ($currentData as $item) {
            // Map each backlog item ID to its current order
            $currentArray[$item['backlog_item_id']] = $item['backlog_order'];
        }

        // Compute the difference between the dragged (new) order and the current order
        // Only items of this product can be reordered
        $draggedArray = array_intersect_key($draggedArray, $currentArray);
        $arrayDiff = array_diff_assoc($draggedArray, $currentArray);

        // Store the changes into history (for tracking purposes)
        $this->groomingBacklog($pId, $currentArray, $draggedArray);

        // Update the backlog refinement with the new order
        $data = $this->backlogModel->updateBacklogRefinement($arrayDiff);

        // Add the size of the diff array to the result for additional context
        $arraysize = sizeof($arrayDiff);
        $arrayDiff["length"] = $arraysize;

        // Return a JSON response based on whether there was any valid data to process
        if ($draggedData) {
            return $this->response->setJSON([
                'status' => 'Priority Changed',
                'data' => $arrayDiff,
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid filter data',
            ]);
        }
    }

    private function groomingBacklog($pId, $initialOrder, $finalOrder)
    {
        // Iterate through the final order of backlog items
        foreach ($finalOrder as $backlogId => $newOrder) {
            // Check if the backlog item exists in the initial order and if its order has changed
            if (isset($initialOrder[$backlogId]) && $initialOrder[$backlogId] != $newOrder) {
                // Store the change in the action table
                
                // Retrieve the old order
                $oldOrder = $initialOrder[$backlogId];

                // Create a descriptive action string showing the change
                $actionData = "Priority order changed from " . "<b>" . $oldOrder . "</b>" . " to " . "<b>" . $newOrder . "</b>";

                // Prepare the action data for logging
                $action = formActionData(__FUNCTION__, $backlogId, $pId, $actionData);

                // Trigger an event to log the action
                Events::trigger('log_actions', $action);
            }
        }
    }

}
