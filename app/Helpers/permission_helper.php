<?php

/**
 * @author   Ruban Edward
 * @category Helper
 * @purpose  Helper Function to show and hide buttons to user based on role
 */

// Check if the function 'has_permission' does not already exist
if (!function_exists('has_permission')) {
    function has_permission($routeUrl)
    {
        $routeUrl = trim((string) $routeUrl, '/');
        // Start the session
        $session = session();
        
        // Load the RolePermissionModel
        $rolePermissionModel = model(\App\Models\Admin\RolePermissionModel::class);
        
        // Get the user role ID from the session
        $userRoleId = $session->get('role_id');

        // If the user is an admin (role_id = 1), always return true
        if ($userRoleId == '1') {
            return true;
        } else {
            // Load the PermissionModel
            $permissionModel = model(\App\Models\Admin\PermissionModel::class);
            
            // Get the permission record for the given route URL
            $permission = $permissionModel->where('LOWER(routes_url)', strtolower($routeUrl), false)
            ->where('is_deleted', 'N')
            ->first();

            // If the permission exists
            if ($permission) {
                // Get the permission ID from the permission record
                $permissionId = $permission['permission_id'];

                // Check if the user's role has this permission
                $rolePermission = $rolePermissionModel->where('r_role_id', $userRoleId)
                    ->where('r_permission_id', $permissionId)
                    ->where('is_deleted', 'N')
                    ->first();

                // Return true if the rolePermission is found, otherwise false
                return $rolePermission !== null;
            }

            // Return false if no permission record is found for the route URL
            return false;
        }
    }
}

// Check if the function already exists to avoid redeclaration errors
if (!function_exists('format_date')) {
    function format_date($date, $format = 'd, M Y') {
        return date($format, strtotime($date));
    }
}
