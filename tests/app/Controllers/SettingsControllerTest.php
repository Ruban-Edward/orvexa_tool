<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;

class SettingsControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected $settingsModelMock;
    protected $settingsConfigModelMock;
    protected $roleModelMock;
    protected $meetingModelMock;
    protected $productOwnerMock;
    protected $TShirtSizeModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock objects for the models
        $this->settingsModelMock = $this->createMock(\App\Models\Admin\SettingsModel::class);
        $this->settingsConfigModelMock = $this->createMock(\App\Models\Admin\SettingConfigModel::class);
        $this->roleModelMock = $this->createMock(\App\Models\Admin\RoleModel::class);

        // Create an instance of the controller
        $this->controller = new \App\Controllers\SettingsController();

        // Inject the mock models into the controller
        $this->setPrivateProperty($this->controller, 'settingsModelObj', $this->settingsModelMock);
        $this->setPrivateProperty($this->controller, 'settingsConfigModelObj', $this->settingsConfigModelMock);
        $this->setPrivateProperty($this->controller, 'roleModelObj', $this->roleModelMock);

    }

    public function testAdminSettingsPage()
    {
        // Set the URI for the request
        $result = $this->withURI('http://localhost/orvexa_tool/public/admin/adminSettings')
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('adminSettingsPage');

        // Assert that the response is OK (200)
        $this->assertTrue($result->isOK());

        // Checking whether the expected output is present
        $this->assertStringContainsString('Manage Product Owner', $result->getBody());
    }

    public function testGetRoles()
    {
        $results = $this->controller(\App\Controllers\SettingsController::class)
            ->execute('getRoles');

        $this->assertTrue($results->isOK());

        $this->assertStringContainsString('Product Owner', $results->getBody());
        // $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testAddRole()
    {
        $testData = [
            'addRole' => 'Tester',
            'addRoleButton' => '1',
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('addRole');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testAddRoleInvalidInput()
    {
        $testData = [
            'addRoleButton' => '1',
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('addRole');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['errors' => ['role_name' => 'The Role Name is required.']]), $results->getJSON());
    }

    public function testDeleteRole()
    {
        $testData = [
            'deleteRoleSelect' => 24,
            'deleteRoleButton' => 1
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('deleteRole');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testCreateHolidays()
    {
        $testData = [
            'holidayTitle' => 'Diwali',
            'holidayDate' => '2099-09-05',
            'holidayButton' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('createHolidays');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testCreateHolidaysInvalidInput()
    {
        $testData = [
            'holidayDate' => '2099-09-05',
            'holidayButton' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('createHolidays');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['errors' => ['holiday_title' => 'The holiday title is required.']]), $results->getJSON());
    }

    public function testPokerConfigSetting()
    {
        $testData = [
            'poker' => 99,
            'setPokerLimitButton' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('pokerConfigSetting');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testPokerConfigSettingNoPokerData()
    {
        $testData = [
            'setPokerLimitButton' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('pokerConfigSetting');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['errors' => ['settings_value' => 'The Settings Value field is required.']]), $results->getJSON());
    }

    public function testPokerConfigSettingInvalid()
    {
        $testData = [
            'poker' => 100,
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('pokerConfigSetting');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => false]), $results->getJSON());
    }

    public function testGetPokerLimit()
    {
        $results = $this->controller(\App\Controllers\SettingsController::class)
            ->execute('getPokerLimit');

        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'data' => ['poker_limit' => "99"],
            'success' => true
        ]), $results->getJSON());
    }

    public function testSetProductOwner()
    {
        $testData = [
            'productSelect' => 16,
            'productUserMemberSelect' => 31,
            'setProductOwnerButton' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('setProductOwner');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true, 'message' => 'Product owner updated']), $results->getJSON());
    }

    public function testSetProductOwnerInvalidData()
    {
        $testData = [
            'productSelect' => 16,
            'setProductOwnerButton' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('setProductOwner');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['errors' => ['r_user_id' => 'The Product Owner field is required.']]), $results->getJSON());
    }

    public function testGetMembersByProductList()
    {
        $this->meetingModelMock = $this->createMock(\App\Models\Meeting\MeetingModel::class);
        $this->productOwnerMock = $this->createMock(\App\Models\Admin\ProductOwnerModel::class);

        $this->setPrivateProperty($this->controller, 'meetingModelObj', $this->meetingModelMock);
        $this->setPrivateProperty($this->controller, 'productOwnerObj', $this->productOwnerMock);

        $results = $this->controller(\App\Controllers\SettingsController::class)
            ->execute('getMembersByProductList', 16);

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertStringContainsString('31', $results->getBody());
        // $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testGetTShirtSizeByProduct()
    {
        $this->TShirtSizeModelMock = $this->createMock(\App\Models\Admin\ProductOwnerModel::class);
        $this->setPrivateProperty($this->controller, 'TShirtSizeModelObj', $this->TShirtSizeModelMock);

        $results = $this->controller(\App\Controllers\SettingsController::class)
            ->execute('getTShirtSizeByProduct', 5);

        $this->assertTrue($results->isOK());
        $this->assertStringContainsString('S1', $results->getBody());
        // $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), $results->getJSON());
    }

    public function testGetTShirtSizeByProductNoResponse()
    {
        $this->TShirtSizeModelMock = $this->createMock(\App\Models\Admin\ProductOwnerModel::class);
        $this->setPrivateProperty($this->controller, 'TShirtSizeModelObj', $this->TShirtSizeModelMock);

        $results = $this->controller(\App\Controllers\SettingsController::class)
            ->execute('getTShirtSizeByProduct', 16);

        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['values' => []]), $results->getJSON());
    }

    public function testSetTShirtSize()
    {
        $testData = [
            'parentProductSelect' => 19,
            't-shirtName' => ['small'],
            't-shirtValue' => ['1-500 hrs'],
            'setTShirtSize' => '1'
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('setTShirtSize');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'No Changes Made', 'success' => true]), $results->getJSON());
    }

    public function testSetTShirtSizeInvalidData()
    {
        $testData = [
            'parentProductSelect' => 19,
            't-shirtName' => ['small'],
            't-shirtValue' => ['1-500 hrs'],
        ];

        $uri = new URI();
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $testData);

        $results = $this->withRequest($request)
            ->controller(\App\Controllers\SettingsController::class)
            ->execute('setTShirtSize');

        // Assertions
        $this->assertTrue($results->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => false]), $results->getJSON());
    }
}