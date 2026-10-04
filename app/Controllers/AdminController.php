<?php

/**
 * AdminController.php
 *
 * @category   Controller
 * @purpose    manages administrative tasks such as listing users, displaying the permission settings page
 * @author     Ruban Edward
 * @created    09 July 2024
 */

namespace App\Controllers;

use Config\SprintModelConfig;
use CodeIgniter\HTTP\Response;

class AdminController extends BaseController
{
    protected $adminModelObj;
    protected $rolePermissionModel;
    protected $users;
    protected $userModelObj;
    protected $permissionModelObj;
    protected $roleModelObj;

    /**
     * Creating an object to access AdminModel functions
     */
    public function __construct()
    {
        // Initialize models for admin and role permissions
        $this->adminModelObj = model(\App\Models\Admin\AdminModel::class);
        $this->rolePermissionModel = model(\App\Models\Admin\RolePermissionModel::class);
        $this->permissionModelObj = model(\App\Models\Admin\PermissionModel::class);
        $this->roleModelObj = model(\App\Models\Admin\RoleModel::class);
    }

    /**
     * Retrieves and displays a list of users.
     * @return string
     */
    public function userList(): string
    {
        // Set up breadcrumbs for navigation
        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView',
            'Manage User' => ASSERT_PATH . 'admin/manageUser'
        ];

        // Get the list of users, roles, and last sync time from the AdminModel
        $usersData = [
            'roles' => $this->roleModelObj->getRoles(),
            'showUser' => $this->adminModelObj->getUsers(),
            'last_sync' => $this->adminModelObj->getLastSync(),
        ];

        // Return the view for managing users with the users data
        return $this->template_view("admin/manageUser", $usersData, "Manage User", $breadcrumbs);
    }

    /**
     * Method to display the permission setting page
     * @return string
     */
    public function setPermissionPage(): string
    {
        // Set up breadcrumbs for navigation
        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView',
            'Manage Permission' => ASSERT_PATH . 'admin/setPermissionPage'
        ];

        //removing the super admin role because not to set permission to super admin
        $roles = $this->roleModelObj->getRoles();
        array_shift($roles);

        // Get roles, permissions, and modules from the AdminModel
        $permissionData = [
            'roles' => $roles,
            'permissions' => $this->adminModelObj->getPermissions(),
            'module' => $this->adminModelObj->getModule(),
        ];

        // Return the view for setting permissions with the permission data
        return $this->template_view("admin/setPermissionPage", $permissionData, "Manage Role Permissions", $breadcrumbs);
    }

    /**
     * Insert the role permissions based on user
     * @return Response
     */
    public function setPermission(): Response
    {
        // Get the selected user and permissions from the POST request
        $selectUser = $this->request->getPost('selectUser');
        $permissions = $this->request->getPost('permissions');

        // Check if input data is valid
        if (!isset($selectUser) || !isset($permissions) || !is_array($permissions)) {
            // Handle the error
            return $this->response->setJSON(['success' => false]);
        }

        // Get existing permissions for the selected user
        $existingPermissionIds = $this->rolePermissionModel->getPermissionsByRole($selectUser);

        // Determine permissions to add and delete
        $permissionsToAdd = array_diff($permissions, $existingPermissionIds);
        $permissionsToDelete = array_diff($existingPermissionIds, $permissions);

        // Validate and insert new permissions
        $validData = [];
        foreach ($permissionsToAdd as $permission) {
            $data = [
                'r_role_id' => $selectUser,
                'r_permission_id' => $permission
            ];

            // Check validation
            $validationErrors = $this->hasInvalidInput($this->rolePermissionModel, $data);
            if ($validationErrors) {
                $validData[] = $data;
            } else {
                return $this->response->setJSON(['success' => false, 'validation' => true, 'errors' => $validationErrors]);
            }
        }

        // Insert all valid data at once
        if (!empty($validData)) {
            $result = $this->rolePermissionModel->insertRolePermission($validData);
            if (!$result) {
                return $this->response->setJSON(['success' => false]);
            }
        }

        // Delete permissions that are no longer present
        foreach ($permissionsToDelete as $permission) {
            $data = [
                'r_role_id' => $selectUser,
                'r_permission_id' => $permission
            ];

            $deleteData[] = $data;
        }

        if (!empty($deleteData)) {
            // Delete the permission
            $result = $this->rolePermissionModel->deleteRolePermission($deleteData);
        }

        // Return success response
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Update the role of a user
     * @return Response
     */
    public function updateRole(): Response
    {
        // Get user data from POST request
        $userData = $this->request->getPost();

        // Update user role using the AdminModel
        $this->adminModelObj->updateUserRole($userData);

        // Return success response
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Search for roles based on a query
     */
    public function searchRole()
    {
        // Get JSON input from the request
        $jsonInput = $this->request->getJSON(true);

        // Extract search query from JSON input
        $searchQuery = isset($jsonInput['searchQuery']) ? $jsonInput['searchQuery'] : '';

        // Check if the search query is valid
        if (!empty($searchQuery)) {
            // Filter users based on search query using the AdminModel
            $filteredData = $this->adminModelObj->userFilter($searchQuery);

            // Return filtered data
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $filteredData
            ]);
        } else {
            // Return error response if search query is invalid
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid search query'
            ]);
        }
    }

    /**
     * Synchronize user data
     * @return bool
     */
    public function userSync(): bool
    {
        // External external user synchronization is disabled.
        return false;
    }

    /**
     * Set a new permission
     */
    public function setNewPermission()
    {
        // Check if the set permission button was clicked
        if ($this->request->getPost('setPermissionButton')) {
            // Prepare permission data from POST request
            $data = [
                'permission_name' => str_replace(" ", "_", strtoupper(trim($this->request->getPost('permissionNameModal')))),
                'r_module_id' => $this->request->getPost('moduleModel'),
                'routes_url' => $this->request->getPost('routesURLModel'),
            ];

            // Check validation
            $validationErrors = $this->hasInvalidInput($this->permissionModelObj, $data);
            if ($validationErrors !== true) {
                return $this->response->setJSON(['validation' => true, 'errors' => $validationErrors]);
            }
            // Add new permission using the AdminModel
            $result = $this->permissionModelObj->addNewPermission($data);

            // Return response based on the result
            if ($result) {
                return $this->response->setJSON(['success' => true, 'permission' => true]);
            } else {
                return $this->response->setJSON(['success' => false]);
            }
        }
    }

    /**
     * Get permissions by role
     */
    public function getSpecificPermissions()
    {
        // Get selected user role from POST request
        $roleId = $this->request->getPost('selectUser');

        // Get permissions for the selected role using the AdminModel
        $permissions = $this->rolePermissionModel->getPermissionsByRole($roleId);

        // Return permissions as JSON response
        return $this->response->setJSON(['permissions' => $permissions]);
    }

    /**
     * Get the permission Name to delete the permission
     */
    public function getPermissionName()
    {
        $permissionsName = $this->permissionModelObj->getPermissions();

        // Return permissions as JSON response
        return $this->response->setJSON(['permissionsName' => $permissionsName]);
    }

    /**
     * delete the permission if not wanted
     */
    public function deletePermission($id)
    {
        $result = $this->permissionModelObj->deletePermission($id);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'permission' => true]);
        } else {
            return $this->response->setJSON(['success' => false]);
        }
    }

    /**
     * Gets the details to show in the edit modal for permission
     */
    public function getPermissionDetails($permissionId)
    {
        $permissionDetails = $this->permissionModelObj->getPermissionDetailsById($permissionId);

        // Return permission details as JSON response
        return $this->response->setJSON($permissionDetails);
    }

    /**
     * 
     */
    public function updatePermission()
    {

        $deleteDetails = $this->request->getPost();

        $result = $this->permissionModelObj->updatePermissionById($deleteDetails);
        if ($result) {
            return $this->response->setJSON(['success' => true, 'permission' => true]);
        } else {
            return $this->response->setJSON(['success' => false]);
        }
    }
}
