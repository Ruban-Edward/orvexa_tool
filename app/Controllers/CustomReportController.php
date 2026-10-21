<?php

namespace App\Controllers;
use App\Helpers\CustomHelpers as DateHelper;

class CustomReportController extends BaseController
{
    private $model;
    public $dateformat;
    public $download;

    public function __construct()
    {
        $this->model = model("CustomReportModel");
        $this->download = service("generateReport");
    }

    /**
     * Download Filtered Report Data
     * 
     * This method downloads all filtered report data as an Excel file.
     * 
     * @param string $reportType Type of the report to download
     * @return mixed
     */
    /** Report types that may be requested from the URL */
    private const REPORT_TYPES = ['Meet', 'Sprint', 'Backlog'];

    /**
     * Normalises "meet", "MeetReport", "Meetreport" … to a whitelisted type
     * ("Meet", "Sprint", "Backlog") or throws a 404. The value from the URL
     * is used to build model method names, so it must never be trusted as-is.
     */
    private function resolveReportType($raw): string
    {
        $type = ucfirst(strtolower(preg_replace('/report$/i', '', (string) $raw)));
        if (! in_array($type, self::REPORT_TYPES, true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $type;
    }

    public function downloadFilterAllData($reportType)
    {
        $reportType = $this->resolveReportType($reportType) . 'Report';
        // Get POST data
        $params = $this->request->getPost();
        $method = "getFilter{$reportType}View";
        $allData = call_user_func_array([$this->model, $method], [$params, true]);
        if (empty($allData)) {
            return $this->response->setStatusCode(400)->setBody('No data to export');
        }
        $allData = DateHelper::formatDatesInArray($allData);
        $allData = DateHelper::stripTagsFromSubArrays($allData);

        // Create a new Spreadsheet object
        $this->download->generateReport($reportType, $allData, true, $params);

    }


    /**
     * Get Report Table
     * 
     * This method retrieves the report data and displays it in a table.
     * If the data exceeds 2000 records, it shows only the first 20 records and displays a message to download all data.
     * 
     * @param string $reportType Type of the report to display
     * @return string Rendered view
     */
    public function getReportTable($reportType)
    {


        $reportType = $this->resolveReportType($reportType);
        $method = "get{$reportType}ReportView";
        $dropdowns = $this->model->reportDropDowns($reportType);
        $details = call_user_func_array([$this->model, $method], []);
        $details = DateHelper::formatDatesInArray($details);
        if ($details) {
            $recordCount = count($details);
            if ($recordCount > RECORDS_LIMIT) {
                $message = "Your data is more than 5000 records. If you want, you can download all data.";
                $details = array_slice($details, 0, RECORDS_LIMIT);
            }
            $breadcrumbs = [
                'Home' => ASSERT_PATH . 'dashboard/dashboardView',
                "{$reportType} Report" => ''
            ];
            return $this->template_view('dashboard/CustomReportView', [
                'data' => $details,
                'report' => ["{$reportType}Report"],
                'drops' => $dropdowns,
                'message' => $message ?? null
            ], "{$reportType} Report", $breadcrumbs);
        } else {
            $breadcrumbs = [
                'Home' => ASSERT_PATH . 'dashboard/dashboardView',
                "No Data" => ''
            ];
            return $this->template_view('dashboard/NodataView', $view_data = "Records", $title = "{$reportType} Report", $breadcrumbs);
        }
    }

    /**
     * 
     
     * Get Filtered Report
     * 
     * This method retrieves the filtered report data based on the given parameters.
     * If the data exceeds 5000 records, it shows only the first 20 records and displays a message to download all data.
     * 
     * @param string $filterType Type of the filter to apply
     * @return string JSON encoded data
     */
    public function getFilterReport($filterType)
    {
        $filterType = $this->resolveReportType($filterType) . 'Report';
        $method = "getFilter{$filterType}View";
        $params = $this->request->getPost();
        $result = call_user_func_array([$this->model, $method], [$params]);
        $result = DateHelper::formatDatesInArray($result);
        $recordCount = count($result);

        if ($recordCount > RECORDS_LIMIT) {
            $result = array_slice($result, 0, RECORDS_LIMIT);
            $message = "Your data is more than 5000 records. If you want, you can download all data.";
        } else {
            $message = "";
        }

        $data = [
            'data' => $result,
            'message' => $message
        ];

        return $this->response->setJSON($data);
    }


}
