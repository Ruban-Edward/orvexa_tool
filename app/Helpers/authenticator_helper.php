<?php

    /**
     * @author Murugadass
     * @return bool
     * @purpose To authenticate and control the user to view the data only assigned for him
     */

     if(!function_exists('authenticateUser')){
        function authenticateUser($pid, $pblId = null, $usId = null, $taskId = null): bool
        {
            $backlogModel = model(\App\Models\Backlog\BacklogModel::class);
            $backlogItemModel = model(\App\Models\Backlog\BacklogItemModel::class);
            $userStoryModel = model(\App\Models\Backlog\UserStoryModel::class);
            // Validate and sanitize input parameters
            $pid = filter_var($pid, FILTER_VALIDATE_INT);
            $pblId = $pblId !== null ? filter_var($pblId, FILTER_VALIDATE_INT) : null;
            $usId = $usId !== null ? filter_var($usId, FILTER_VALIDATE_INT) : null;
            $taskId = $taskId !== null ? filter_var($taskId, FILTER_VALIDATE_INT) : null;
        
            // Return false if any of the input parameters are invalid
            if ($pid === false || ($pblId !== null && $pblId === false) || ($usId !== null && $usId === false) || ($taskId !== null && $taskId === false)) {
                return false;
            }
        
            // Fetch the list of products accessible by the user
            $products = array_column($backlogModel->getUserProduct(session('employee_id')), 'product_id');
        
            // Check if the product ID is valid and accessible by the user
            if ($pid === false || !in_array($pid, $products)) {
                return false;
            }
        
            // If only the product ID is provided (e.g., for the backlog items page), return true
            if (is_null($pblId) && is_null($usId) && is_null($taskId)) {
                return true;
            }
        
            // If only product ID and backlog item ID are provided (e.g., for the user stories page)
            if (!is_null($pblId) && is_null($usId) && is_null($taskId)) {
                // Verify if the backlog item exists for the specified product
                $backlogItemExists = $backlogItemModel->checkBacklogItem($pid, $pblId);
                return $backlogItemExists;
            }
        
            // If product ID, backlog item ID, and user story ID are provided
            if (!is_null($pid) && !is_null($pblId) && !is_null($usId) && is_null($taskId)) {
                // Verify if the backlog item exists for the specified product
                $backlogItemExists = $backlogItemModel->checkBacklogItem($pid, $pblId);
                if (!$backlogItemExists) {
                    return false;
                }
        
                // Verify if the user story exists for the specified backlog item (epic)
                $userStoryExists = $userStoryModel->checkUserStory($pblId, $usId);
                if (!$userStoryExists) {
                    return false;
                }
                return true;
            }
        
            // For other cases or invalid parameters, return false
            return false;
        }
     }

?>