<?php

namespace App\Controllers;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\App;
use Config\Services;

class AdminControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    // use FeatureTestTrait;

    protected $adminModelMock;
    protected $roleModelMock;
    protected $adminModelObj;
    protected $roleModelObj;
    protected $rolePermissionModelMock;
    protected $permissionModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock objects for the models
        $this->adminModelMock = $this->createMock(\App\Models\Admin\AdminModel::class);
        $this->roleModelMock = $this->createMock(\App\Models\Admin\RoleModel::class);
        $this->rolePermissionModelMock = $this->createMock(\App\Models\Admin\RolePermissionModel::class);
        $this->permissionModelMock = $this->createMock(\App\Models\Admin\PermissionModel::class);


        // Create an instance of the controller
        $this->controller = new \App\Controllers\AdminController();

        // Inject the mock models into the controller
        $this->setPrivateProperty($this->controller, 'adminModelObj', $this->adminModelMock);
        $this->setPrivateProperty($this->controller, 'roleModelObj', $this->roleModelMock);
        $this->setPrivateProperty($this->controller, 'rolePermissionModel', $this->rolePermissionModelMock);
        $this->setPrivateProperty($this->controller, 'permissionModelObj', $this->permissionModelMock);

        $this->session = Services::session();

        $this->session->set('logged_in', 123);
    }

    public function testManageUserPage()
    {
        // Set the URI for the request
        $result = $this->withURI('http://localhost/orvexa_tool/public/admin/manageUser')
            ->controller(\App\Controllers\AdminController::class)
            ->execute('userList');

        // Assert that the response is OK (200)
        $this->assertTrue($result->isOK());

        // Checking whether the expected output is present
        $this->assertStringContainsString('Ruban', $result->getBody());
        $this->assertStringContainsString('Anish', $result->getBody());
        $this->assertStringContainsString('Super Admin', $result->getBody());
        $this->assertStringContainsString('Developer', $result->getBody());
    }

    public function testManagePermissionPage()
    {
        // Set the URI for the request
        $result = $this->withURI('http://localhost/orvexa_tool/public/admin/setPermissionPage')
            ->controller(\App\Controllers\AdminController::class)
            ->execute('setPermissionPage');

        // Assert that the response is OK (200)
        $this->assertTrue($result->isOK());

        // Checking whether the expected output is present
        $this->assertStringContainsString('Project Manager', $result->getBody());
        $this->assertStringContainsString('View Dashboard', $result->getBody());
        $this->assertStringContainsString('Add Sprint Plan', $result->getBody());
    }

    public function testUpdateUserRole()
    {
        // Duplicate the POST data for the test
        $testData = [
            'selectUser' => 4,
            'userId' => 1,
        ];

        // creating the POST form Data to send to controller
        $uri = new URI('http://localhost/orvexa_tool/public/admin/manageUser');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        // creating connection with model to insert
        $this->adminModelMock->method('updateUserRole')
            ->with($this->equalTo($testData))
            ->willReturn(true);

        // preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(\App\Controllers\AdminController::class)
            ->execute('updateRole');

        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $result->getJSON());
    }

    public function testUpdateUserRoleInvalidInput()
    {
        // Duplicate the POST data for the test
        $testData = [
            'selectUser' => 4,
        ];

        // creating the POST form Data to send to controller
        $uri = new URI('http://localhost/orvexa_tool/public/admin/manageUser');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        // creating connection with model to insert
        $this->adminModelMock->method('updateUserRole')
            ->with($this->equalTo($testData))
            ->willReturn(true);

        // preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(\App\Controllers\AdminController::class)
            ->execute('updateRole');

        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $result->getJSON());
    }

    public function testSetPermission()
    {
        // Duplicate the POST data for the test, including invalid data
        $testData = [
            'selectUser' => 3,
            'permissions' => [
                33,
                34,
                40,
            ],
        ];

        // Creating the POST form data to send to controller
        $uri = new URI('http://localhost/orvexa_tool/public/admin/setPermissionPage');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        // Mocking model method to return true
        $this->rolePermissionModelMock->method('insertRolePermission')
            ->willReturn(true);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(\App\Controllers\AdminController::class)
            ->execute('setPermission');

        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $result->getJSON());
    }

    public function testSetPermissionInvalidInput()
    {
        // Duplicate the POST data for the test, including invalid data
        $testData = [
            'selectUser' => "abc",
            'permissions' => [
                33,
                34,
                40,
            ],
        ];

        // Creating the POST form data to send to controller
        $uri = new URI('http://localhost/orvexa_tool/public/admin/setPermissions');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        // Mocking model method to return true
        $this->rolePermissionModelMock->method('insertRolePermission')
            ->willReturn(true);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(\App\Controllers\AdminController::class)
            ->execute('setPermission');

        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $result->getJSON());
    }

    public function testSetPermissionNoRequiredField()
    {
        // Duplicate the POST data for the test, including invalid data
        $testData = [
            'permissions' => [
                33,
                34,
                40,
            ],
        ];

        // Creating the POST form data to send to controller
        $uri = new URI('http://localhost/orvexa_tool/public/admin/setPermissionPage');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        // Mocking model method to return true
        $this->rolePermissionModelMock->method('insertRolePermission')
            ->willReturn(true);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(\App\Controllers\AdminController::class)
            ->execute('setPermission');

        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode(['success' => false]), $result->getJSON());
    }

    public function testSearchRoleWithValidQuery()
    {
        // Prepare the input data
        $requestData = [
            'searchQuery' => 'ruban', // Test with a valid search query
        ];

        // Mock IncomingRequest
        $request = Services::request();
        $request->withMethod('post');
        $request->setBody(json_encode($requestData));
        $request->setHeader('Content-Type', 'application/json');

        // Mocking the response from AdminModel
        $this->adminModelMock->method('userFilter')
            ->with('ruban')
            ->willReturn(['user1', 'user2']); // Return mock data

        // Inject the mock model into the controller
        $results = $this->withRequest($request)
            ->controller(AdminController::class)
            ->execute('searchRole');

        // Assertions
        $this->assertTrue($results->isOK());
    }

    public function testSearchRoleWithInvalidQuery()
    {
        // Prepare the input data with an invalid query
        $requestData = [
            'searchQuery' => '', // Test with an empty search query
        ];

        // Mock IncomingRequest
        $request = Services::request();
        $request->withMethod('post');
        $request->setBody(json_encode($requestData));
        $request->setHeader('Content-Type', 'application/json');

        // Inject the mock request
        $results = $this->withRequest($request)
            ->controller(AdminController::class)
            ->execute('searchRole');

        // Assertions
        $this->assertTrue($results->isOK());
    }

    public function testSetNewPermissionValidInput()
    {
        // Prepare the POST data
        $postData = [
            'setPermissionButton' => 1,
            'permissionNameModal' => 'permission test',
            'moduleModel' => 1,
            'routesURLModel' => '/admin/test'
        ];

        // Mock the PermissionModel used in the controller
        $this->permissionModelMock->method('addNewPermission')
            ->willReturn(true);  // Simulate successful permission addition

        // Simulate the request
        $uri = new URI('http://localhost/orvexa_tool/public/admin/setNewPermission');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $postData);

        // Use ControllerTestTrait to simulate the controller action
        $results = $this->withRequest($request)
            ->controller(AdminController::class)
            ->execute('setNewPermission');

        // Assertions
        $this->assertTrue($results->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true, 'permission' => true,]), $results->getJSON());
    }

    public function testSetNewPermissionInvalidInput()
    {
        // Prepare POST data with missing fields to simulate validation failure
        $postData = [
            'setPermissionButton' => 'true',
            'moduleModel' => 1,
            'routesURLModel' => '/admin/view-user'
        ];

        // Mock the PermissionModel
        $this->permissionModelMock->method('addNewPermission')
            ->willReturn(false);

        // Simulate the request
        $uri = new URI('http://localhost/orvexa_tool/public/admin/setNewPermission');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $postData);

        // Use ControllerTestTrait to simulate the controller action
        $results = $this->withRequest($request)
            ->controller(AdminController::class)
            ->execute('setNewPermission');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'errors' => ['permission_name' => 'The Permission Name is required.'],
            'validation' => true,
        ]), $results->getJSON());
    }

    public function testGetSpecificPermissions()
    {
        // Prepare POST data with a specific role ID
        $postData = [
            'selectUser' => 3  // Example role ID
        ];

        // Simulate the request
        $uri = new URI('http://localhost/orvexa_tool/public/admin/getSpecificPermissions');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $postData);

        // Use ControllerTestTrait to simulate the controller action
        $results = $this->withRequest($request)
            ->controller(AdminController::class)
            ->execute('getSpecificPermissions');

        // Assertions
        $this->assertTrue($results->isOK());

        $this->assertJsonStringEqualsJsonString(json_encode([
            'permissions' => ["33", "34", "40"]
        ]), $results->getJSON());
    }

    public function testGetPermissionName()
    {
        $results = $this->controller(AdminController::class)
            ->execute('getPermissionName');

        $this->assertTrue($results->isOK());

        $this->assertStringContainsString('VIEW_PRODUCT_DASHBOARD', $results->getBody());

        // $this->assertJsonStringEqualsJsonString(json_encode(['permission_id' => 4]), $results->getJSON());
    }

    public function testDeletePermission()
    {
        $results = $this->controller(\App\Controllers\AdminController::class)
            ->execute('deletePermission', 2);

        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true, 'permission' => true,]), $results->getJSON());
    }

    public function testGetPermissionDetails()
    {
        $results = $this->controller(\App\Controllers\AdminController::class)
            ->execute('getPermissionDetails', 5);

        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(
            [
                'permission_name' => 'BACKLOGiTEM_DETAILS',
                'r_module_id' => '5',
                'routes_url' => 'backlog/backlogitemdetails'
            ]
        ), $results->getJSON());
    }

    public function testUpdatePermission()
    {
        $testData = [
            'editPermissionNameModel' => 24,
            'permissionNameModal' => 'Add Sprint Retrospective Details',
            'moduleModel' => 10,
            'routesURLModel' => 'sprint/sprintretrospective',
            'setPermissionButton' => 1
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(AdminController::class)
            ->execute('updatePermission');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true, 'permission' => true,]), $results->getJSON());
    }
}
