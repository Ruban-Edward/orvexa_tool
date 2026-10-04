<?php
namespace App\Controllers;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use App\Models\Backlog\BacklogModel;
use App\Controllers\BacklogController;
use CodeIgniter\Test\Mock\MockRequest;
use CodeIgniter\Test\Mock\MockResponse;
use CodeIgniter\Session\Session;
use Config\App;

class BacklogControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected $backlogModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the BacklogModel
        $this->backlogModelMock = $this->createMock(BacklogModel::class);

        // Create an instance of BacklogController
        $this->controller = new BacklogController();

        // Set the mock model to the controller's backlogModel property
        $this->setPrivateProperty($this->controller, 'backlogModel', $this->backlogModelMock);


        // Mock the session
        $this->mockSession();

        // Manually set session data after calling mockSession
        $_SESSION['employee_id'] = 31;//set the session value as invalid (not available user id) to return empty array


    }

    public function testProductsWithNoData()
    {
        // Mock the return value for getUserProductDetails to simulate no products
        $this->backlogModelMock
            ->method('getUserProductDetails')
            ->with() // pass the wrong employee id
            ->willReturn([]);

        $result = $this->withUri('http://localhost:8080/backlog/productbacklogs')
            ->controller(BacklogController::class)
            ->execute('products');

        // Check that the response is OK
        $this->assertTrue($result->isOK());

        // Check that the "No Products" message is shown
        $this->assertStringContainsString('No Products', $result->getBody());
    }

    public function testProductsWithData()
    {
        // Mock product data
        $mockProducts = [
            [
                'product_id' => 24,
                'product_name' => 'Amal Product',
                'number_of_backlog_items' => 2,
                'number_of_user_stories' => 6,
                'last_updated' => '2024-09-02 11:15:44',
                'on_hold' => 0,
            ],
            [
                'product_id' => 51,
                'product_name' => 'Sales & Marketing Support',
                'number_of_backlog_items' => 5,
                'number_of_user_stories' => 0,
                'last_updated' => '2024-08-31 15:36:31',
                'on_hold' => 0,
            ],
        ];

        // Mock the return value for getUserProductDetails to simulate having products
        $this->backlogModelMock
            ->method('getUserProductDetails')
            ->with()
            ->willReturn($mockProducts);

        $result = $this->withUri('http://localhost:8080/backlog/productbacklogs')
            ->controller(BacklogController::class)
            ->execute('products');

        // Check that the response is OK
        $this->assertTrue($result->isOK());

        // print_r($result->getBody());
        // Check that the product names are present in the response
        $this->assertStringContainsString('AMAL PRODUCT', $result->getBody());
        $this->assertStringContainsString('CMS', $result->getBody());
    }




    public function testBacklogItemsView()
    {
        $pId = ['pid' => 24];
        $uri = new URI('http://localhost:8080/backlog/backlogitems');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setGlobal('get', $pId);

        // Simulate the request and execute the controller method
        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
                ->execute('backlogItems');

        // Checking if the request was correctly executed
        $this->assertTrue($result->isOK());

        // Checking if the response body contains a specific string
        $this->assertStringContainsString("24", $result->getBody());
    }

    public function testAddBacklog()
    {
        $data = [
            'addBackLog' => 'insert',
            'pId' => 24,
            'productname' => 'Amal product',
            'backlog_item_name' => 'orvexa',
            'priority' => 'L',
            'r_tracker_id' => 11,
            'r_customer_id' => 16,
            'r_module_status_id' => 1,
            'backlog_t_shirt_size' => 15,
            'backlog_description' => 'sdfsd'
        ];

        $uri = new URI('http://localhost:8080/backlog/addbacklog');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $data);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
            ->execute('addBacklog');



        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(["message" => "Backlog added successfully", 'success' => true]), $result->getJSON());
    }

    public function testUpdateBacklog()
    {
        $arr = [
            'pid' => 24,
            'pblid' => 16
        ];
        $data = [
            'addBackLog' => 'insert',
            'pId' => 24,
            'productname' => 'Amal product',
            'backlog_item_name' => 'horizontal',
            'priority' => 'L',
            'r_tracker_id' => 11,
            'r_customer_id' => 16,
            'r_module_status_id' => 1,
            'backlog_t_shirt_size' => 15,
            'backlog_description' => 'sdfsd'
        ];

        $uri = new URI('http://localhost:8080/backlog/updatebacklog');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $data);
        $request->setGlobal('get', $arr);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
            ->execute('updateBacklog');



        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(["message" => "Backlog updated successfully", 'success' => true]), $result->getJSON());
    }

    public function testDeleteBacklog()
    {
        $arr = [
            'pid' => 24,
            'pblid' => 20
        ];

        $uri = new URI('http://localhost:8080/backlog/deletebacklogitem');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setGlobal('get', $arr);

        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
            ->execute('deleteBacklog');

        $this->assertTrue($result->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(["message" => "Backlog item has been deleted.", 'success' => true]), $result->getJSON());
    }

    public function testDeleteBacklogInSprint()
    {
        $arr = [
            'pid' => 24,
            'pblid' => 12
        ];

        $uri = new URI('http://localhost:8080/backlog/deletebacklogitem');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setGlobal('get', $arr);

        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
            ->execute('deleteBacklog');

        $this->assertTrue($result->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => false, 'message' => 'Backlog in sprint cannot be deleted']), $result->getJSON());
    }

    public function testDeleteDocument()
    {
        $data = [
            'docId' => 5,
            'pId' => 51,
            'pblId' => 5
        ];
        $uri = new URI('http://localhost:8080/backlog/deletedocument');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $data);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
            ->execute('deleteDocument');



        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true, 'message' => 'Document removed successfully']), $result->getJSON());

    }

    public function testAddEpic(){

        $arr= [
            'pid'=>51,
            'pblid'=>6
        ];
        $data = [
            'epic_description' => 'checking test cases2'
        ];
        $uri = new URI('http://localhost:8080/backlog/addepic');
        $config = new App();
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->withMethod('post');
        $request->setGlobal('post', $data);
        $request->setGlobal('get', $arr);

        // Preparing the POST to send to the function
        $result = $this->withRequest($request)
            ->controller(BacklogController::class)
            ->execute('addEpic');



        // Assert that the result of the query execution is OK
        $this->assertTrue($result->isOK());
        $this->assertJsonStringEqualsJsonString(json_encode(["message" => "Epic added successfully", 'success' => true]), $result->getJSON());
    }

}
