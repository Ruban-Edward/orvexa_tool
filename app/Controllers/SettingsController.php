<?php

/**
 * SettingsController.php
 *
 * @category   Controller
 * @purpose    Manages the admin settings 
 * @author     Ruban Edward
 * @created    20 August 2024
 */

namespace App\Controllers;

class SettingsController extends BaseController
{
    protected $settingsModelObj;
    protected $roleModelObj;
    protected $HolidayModelObj;
    protected $settingsConfigModelObj;
    protected $meetingModelObj;
    protected $productOwnerObj;
    protected $TShirtSizeModelObj;


    public function __construct()
    {
        $this->settingsModelObj = model(\App\Models\Admin\SettingsModel::class);
        $this->settingsConfigModelObj = model(\App\Models\Admin\SettingConfigModel::class);
    }

    /**
     * to redirect to the manage product user page settings
     * @return string
     */
    public function adminSettingsPage(): string
    {
        // Set up breadcrumbs for navigation
        $breadcrumbs = [
            'Home' => ASSERT_PATH . 'dashboard/dashboardView',
            'Admin Settings' => ASSERT_PATH . 'admin/adminSettings'
        ];

        $productData = [
            'products' => $this->settingsModelObj->getAllProduct(),
            'parentProduct' => $this->settingsModelObj->getAllParentProduct(),
            'customers' => $this->settingsModelObj->getAllCustomer(),
        ];

        // Return the view for managing users with the users data
        return $this->template_view("admin/adminSettings", $productData, "Admin Settings", $breadcrumbs);
    }

    public function addCustomer()
    {
        $customerName = trim((string) $this->request->getPost('customer_name'));
        if ($customerName === '' || mb_strlen($customerName) > 50) {
            return redirect()->to(base_url('admin/adminSettings'))->with('error', 'Customer name is required and must be 50 characters or fewer.');
        }
        if ($this->settingsModelObj->customerExists($customerName)) {
            return redirect()->to(base_url('admin/adminSettings'))->with('error', 'This customer already exists.');
        }
        $saved = $this->settingsModelObj->addCustomer($customerName);
        return redirect()->to(base_url('admin/adminSettings'))->with($saved ? 'success' : 'error', $saved ? 'Customer added successfully.' : 'Customer could not be added.');
    }

    public function toggleCustomer()
    {
        $customerId = (int) $this->request->getPost('customer_id');
        $updated = $customerId > 0 && $this->settingsModelObj->toggleCustomer($customerId);
        return redirect()->to(base_url('admin/adminSettings'))->with($updated ? 'success' : 'error', $updated ? 'Customer status updated.' : 'Customer status could not be updated.');
    }

    /**
     * Fetch the role for deleting the role by the adminSettings
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getRoles()
    {
        $this->roleModelObj = model(\App\Models\Admin\RoleModel::class);
        $roles = $this->roleModelObj->getRoles();
        array_shift($roles);

        return $this->response->setJSON(['role' => $roles]);
    }

    /**
     * inserting new role if need in future by the admin
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function addRole()
    {
        $this->roleModelObj = model(\App\Models\Admin\RoleModel::class);
        if ($this->request->getPost("addRoleButton")) {
            $roleData = [
                "role_name" => $this->request->getPost("addRole"),
            ];
            $checkValidations = $this->hasInvalidInput($this->roleModelObj, $roleData);
            if ($checkValidations !== true) {
                return $this->response->setJSON(['errors' => $checkValidations]);
            }
            $result = $this->roleModelObj->insertRole($roleData);
            if ($result) {
                return $this->response->setJSON(['success' => true]);
            }
        }
        return $this->response->setJSON(['success' => false]);
    }

    /**
     * deletes the role in the tool
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function deleteRole()
    {
        $this->roleModelObj = model(\App\Models\Admin\RoleModel::class);
        if ($this->request->getPost("deleteRoleButton")) {
            $result = $this->roleModelObj->deleteRole($this->request->getPost("deleteRoleSelect"));
            if ($result) {
                return $this->response->setJSON(['success' => true]);
            }
        }
        return $this->response->setJSON(['success' => false]);
    }

    /**
     * Inserting single leave on the calendar to show
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function createHolidays()
    {
        $this->HolidayModelObj = model(\App\Models\Meeting\HolidaysModel::class);

        if ($this->request->getPost('holidayButton')) {
            $holidayData = [
                "holiday_title" => ucwords($this->request->getPost("holidayTitle")),
                "holiday_start_date" => $this->request->getPost("holidayDate"),
                "holiday_end_date" => $this->request->getPost("holidayDate"),
            ];

            $checkValidations = $this->hasInvalidInput($this->HolidayModelObj, $holidayData);
            if ($checkValidations !== true) {
                return $this->response->setJSON(['errors' => $checkValidations]);
            }

            $holiday = $this->HolidayModelObj->insertHoliday($holidayData);
            if ($holiday) {
                return $this->response->setJSON(["success" => true]);
            }
        }
        return $this->response->setJSON(["success" => false]);
    }

    /**
     * This function helps in file validation of holidays uploaded CSV and insert in the table
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function holidayFileUpload()
    {
        helper('file');

        $this->HolidayModelObj = model(\App\Models\Meeting\HolidaysModel::class);

        // Check if file is uploaded
        if ($this->request->getFile('file')) {
            $file = $this->request->getFile('file');

            if ($file->isValid() && !$file->hasMoved()) {
                $fileName = $file->getRandomName(); // Use a random name for the uploaded file
                $filePath = WRITEPATH . 'uploads/' . $fileName;
                $file->move(WRITEPATH . 'uploads/', $fileName);

                // Confirm file exists
                if (file_exists($filePath)) {
                    // Read and process the file
                    $csvData = array_map('str_getcsv', file($filePath));

                    // Optionally remove the header row
                    array_shift($csvData);

                    // Process CSV data as needed
                    $holidayData = [];
                    foreach ($csvData as $row) {
                        $holidayTitle = trim($row[1]);
                        $holidayStartDate = \DateTime::createFromFormat('d-m-Y', trim($row[2]));
                        $formattedStartDate = $holidayStartDate->format('Y-m-d');
                        $createdDate = date('Y-m-d H:i:s');

                        $holidayEndDate = null;
                        $formattedEndDate = null;

                        if (isset($row[3]) && !empty(trim($row[3]))) {
                            $endDate = \DateTime::createFromFormat('d-m-Y', trim($row[3]));
                            if ($endDate !== false) {
                                $holidayEndDate = $endDate;
                                $formattedEndDate = $holidayEndDate->format('Y-m-d');
                            }
                        }

                        // If end date is not set or is the same as start date
                        if ($formattedEndDate === null || $formattedEndDate === $formattedStartDate) {
                            $existingHoliday = $this->HolidayModelObj->where('holiday_title', $holidayTitle)
                                ->where('holiday_start_date', $formattedStartDate)
                                ->first();

                            if (!$existingHoliday) {
                                $holidayData[] = [
                                    'holiday_title' => $holidayTitle,
                                    'holiday_start_date' => $formattedStartDate,
                                    'holiday_end_date' => $formattedStartDate, // Use start date as end date
                                    'created_date' => $createdDate
                                ];
                            }
                        } else {
                            $currentDate = clone $holidayStartDate;
                            // If start and end dates are different
                            while ($currentDate <= $holidayEndDate) {
                                $currentFormattedDate = $currentDate->format('Y-m-d');

                                $existingHoliday = $this->HolidayModelObj->where('holiday_title', $holidayTitle)
                                    ->where('holiday_start_date', $currentFormattedDate)
                                    ->first();

                                if (!$existingHoliday) {
                                    $holidayData[] = [
                                        'holiday_title' => $holidayTitle,
                                        'holiday_start_date' => $currentFormattedDate,
                                        'holiday_end_date' => $currentFormattedDate,
                                        'created_date' => $createdDate
                                    ];
                                }
                                $currentDate->modify('+1 day');
                            }
                        }
                    }

                    // Batch insert into the database
                    if (empty($holidayData)) {
                        return $this->response->setJSON(['status' => 'success', 'duplicate' => 'Already this file Content uploaded']);
                    } else {
                        $this->HolidayModelObj->insertBatchHoliday($holidayData);
                    }

                    // Remove the uploaded file after processing
                    unlink($filePath);

                    return $this->response->setJSON(['status' => 'success', 'message' => 'File processed successfully!']);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File not found.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'File upload failed.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No file uploaded.']);
        }
    }

    /**
     * Configures the poker points limit.
     * 
     * This method handles the request to set a new poker points limit. 
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response indicating success or failure.
     */
    public function pokerConfigSetting()
    {
        if ($this->request->getPost("setPokerLimitButton")) {

            // Prepare the poker configuration data from the POST request.
            $pokerData = [
                "settings_name" => "poker",
                "settings_value" => $this->request->getPost("poker"),
            ];

            $checkValidations = $this->hasInvalidInput($this->settingsConfigModelObj, $pokerData);
            if ($checkValidations !== true) {
                return $this->response->setJSON(['errors' => $checkValidations]);
            } else {
                $result = $this->settingsConfigModelObj->pokerConfig($pokerData);
            }
            // Return a JSON response indicating success if the save operation was successful.
            if ($result) {
                return $this->response->setJSON(['success' => true]);
            }
        }
        return $this->response->setJSON(['success' => false]);
    }

    /**
     * Retrieves the current poker points limit.
     * This method checks if the poker points limit is already set in the table.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getPokerLimit()
    {
        $pokerData = $this->settingsConfigModelObj->getPokerLimit();

        if ($pokerData) {
            return $this->response->setJSON(['success' => true, 'data' => ['poker_limit' => $pokerData[0]['settings_value']]]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Poker limit not found.']);
        }
    }

    /**
     * Assigns a product owner.
     * This method handles the request to set a product owner by retrieving 
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function setProductOwner()
    {
        $this->productOwnerObj = model(\App\Models\Admin\ProductOwnerModel::class);
        if ($this->request->getPost('setProductOwnerButton')) {

            // Prepare the product owner data from the POST request.
            $productOwnerData = [
                "r_product_id" => $this->request->getPost("productSelect"),
                "r_user_id" => $this->request->getPost("productUserMemberSelect"),
            ];
        }

        $checkValidations = $this->hasInvalidInput($this->productOwnerObj, $productOwnerData);
        if ($checkValidations !== true) {
            return $this->response->setJSON(['errors' => $checkValidations]);
        }

        $result = $this->productOwnerObj->setProductOwner($productOwnerData);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Product owner updated']);
        }
        return $this->response->setJSON(['success' => false]);
    }

    /**
     * Retrieves the list of members associated with a specific product.
     * 
     * This method fetches the list of members linked to a given product ID 
     * and the current product owner from the database. 
     * 
     * @param int $productId 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getMembersByProductList($productId)
    {
        $this->meetingModelObj = model(\App\Models\Meeting\MeetingModel::class);
        $this->productOwnerObj = model(\App\Models\Admin\ProductOwnerModel::class);

        $data = ['external_project_id' => $productId];

        // Retrieve members associated with the product ID using the model.
        $members = $this->meetingModelObj->getMembersByProduct($data);

        // Retrieve the current owner of the product using the model.
        $currentOwner = $this->productOwnerObj->getProductOwner($productId);

        $response = [
            'members' => $members,
            'currentOwnerId' => $currentOwner ? $currentOwner[0]['r_user_id'] : null
        ];
        return $this->response->setJSON($response);
    }

    /**
     * to set the t shirt size for each parent product 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function setTShirtSize()
    {
        $this->TShirtSizeModelObj = model(\App\Models\Admin\TShirtSizeModel::class);
        if ($this->request->getPost("setTShirtSize")) {
            $productId = $this->request->getPost("parentProductSelect");

            $tshirtNames = $this->request->getPost('t-shirtName'); // This will be an array
            $tshirtValues = $this->request->getPost('t-shirtValue'); // This will also be an array

            // Initialize an array to hold batch data for insertion
            $batchData = [];

            // Combine names and values into a batch data array
            if ($tshirtNames && $tshirtValues) {
                for ($i = 0; $i < count($tshirtNames); $i++) {
                    $batchData[] = [
                        'r_product_id' => trim($productId),
                        't_size_name' => trim($tshirtNames[$i]),
                        't_size_values' => trim($tshirtValues[$i])
                    ];

                    $checkValidations = $this->hasInvalidInput($this->TShirtSizeModelObj, $batchData[$i]);
                    if ($checkValidations !== true) {
                        return $this->response->setJSON(['errors' => $checkValidations]);
                    }
                }
            }

            // Initialize flags
            $deleteSuccess = false;
            $insertSuccess = false;

            //gets the t-shirt size if already in the table for that product to avoid duplicate entry
            $existingTshirtSizArray = $this->TShirtSizeModelObj->getTShirtSize($productId);
            if (!empty($existingTshirtSizArray)) {
                //gets the new data to insert
                $insertData = array_udiff($batchData, $existingTshirtSizArray, function ($a, $b) {
                    return strcmp(serialize($a), serialize($b));
                });
                //gets the removed data from the t-shirt size
                $deleteData = array_udiff($existingTshirtSizArray, $batchData, function ($a, $b) {
                    return strcmp(serialize($a), serialize($b));
                });

                //perform deletion if any size to be remove
                if (!empty($deleteData)) {
                    $deleteSuccess = $this->TShirtSizeModelObj->deleteTShirtSize($deleteData);
                }

                // Perform insertion if any new t-shirt size comes
                if (!empty($insertData)) {
                    $insertSuccess = $this->TShirtSizeModelObj->insertTShirtSize($insertData);
                }

                $message = ($insertSuccess || $deleteSuccess) ? 'T Shirt Sizes Updated Successfully' : 'No Changes Made';
            } else {
                $insertSuccess = $this->TShirtSizeModelObj->insertTShirtSize($batchData);
                $message = $insertSuccess ? 'T Shirt Sizes Added Successfully' : '';
            }
            // Return the JSON response
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        }
        return $this->response->setJSON(['success' => false]);
    }

    /**
     * gets the t-shirt size to display if again the product is selected in admin t-shirt setting page
     * @param int $productId 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getTShirtSizeByProduct($productId)
    {
        $this->TShirtSizeModelObj = model(\App\Models\Admin\TShirtSizeModel::class);
        $TShirtSize = $this->TShirtSizeModelObj->getTShirtSize($productId);

        return $this->response->setJSON(["values" => $TShirtSize]);
    }

}
