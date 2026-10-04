<?php

namespace App\Models;
use Config\SprintModelConfig as Constants;


/**
 * Class CustomReportModel
 * 
 * Model for fetching data from the database for various reports.
 * 
 * @package App\Models
 */

class CustomReportModel extends BaseModel
{
    public $constants;
    public function __construct()
    {
        parent::__construct();
        $this->constants = new Constants();
    }
    /**
     * Retrieve meeting report details.
     *
     * @return array
     */
    public function getMeetReportView(): array
    {
        $query = '
        SELECT 
            mt.meeting_type_name AS "Meeting Name",
            ut.first_name AS "Creator",
            pt.product_name AS "Product Name",
            md.created_date AS "Meeting Date",
            md.meeting_start_time AS "Start Time",
            md.meeting_end_time  AS "End Time",
            md.meeting_description AS "Meeting Description"
        FROM 
            orvexa_meeting_details AS md
        INNER JOIN orvexa_meeting_type AS mt 
            ON mt.meeting_type_id = md.r_meeting_type_id AND mt.is_deleted = "N"
        INNER JOIN orvexa_user AS ut 
            ON ut.external_employee_id = md.r_user_id AND ut.is_deleted = "N"
        INNER JOIN orvexa_product AS pt 
            ON pt.external_project_id = md.r_product_id AND pt.is_deleted = "N"
        WHERE 
            md.is_deleted = "N"
        GROUP BY 
            md.meeting_details_id, mt.meeting_type_name, ut.first_name, pt.product_name, md.created_date, md.meeting_start_time, md.meeting_end_time, md.meeting_description
        ORDER BY 
            md.created_date DESC;
      
    ';

        $result = $this->db->query($query);
        return $result->getResultArray();
    }


    /**
     * Filter meeting report based on given parameters.
     *
     * @param array $params
     * @return array
     */
    public function getFilterMeetReportView(array $params, $download = null): array
    {
        // Define the initial select fields
        $selectFields = '
            mt.meeting_type_name AS "Meeting Name",
            ut.first_name AS "Creator",
            pt.product_name AS "Product Name",
            md.created_date AS "Meeting Date",
            md.meeting_start_time AS "Start Time",
            md.meeting_end_time AS "End Time",
            md.meeting_description AS "Meeting Description"';

        // Append the count of team members and their details if download is true
        if ($download) {
            $selectFields .= ',
            COUNT(DISTINCT mm.r_user_id) AS "Total Team Members",
            GROUP_CONCAT(su.first_name SEPARATOR ", ") AS "Team Members"';
        }

        // Build the query
        $query = "
            SELECT 
                $selectFields
            FROM 
                orvexa_meeting_details AS md
            INNER JOIN 
                orvexa_meeting_type AS mt ON mt.meeting_type_id = md.r_meeting_type_id AND mt.is_deleted = 'N'
            INNER JOIN 
                orvexa_user AS ut ON ut.external_employee_id = md.r_user_id AND ut.is_deleted = 'N'
            INNER JOIN 
                orvexa_product AS pt ON pt.external_project_id = md.r_product_id AND pt.is_deleted = 'N'";

        // Append additional joins for team members if download is true
        if ($download) {
            $query .= "
            LEFT JOIN 
                orvexa_meeting_members AS mm ON mm.r_meeting_details_id = md.meeting_details_id AND mm.is_deleted = 'N'
            LEFT JOIN 
                orvexa_user AS su ON su.external_employee_id = mm.r_user_id AND su.is_deleted = 'N'";
        }

        // Append the WHERE clause
        $query .= "
            WHERE 
                md.is_deleted = 'N'";

        // Add filters
        $data = [];

        if (!empty($params['fromdate'])) {
            $query .= " AND DATE_FORMAT(md.created_date,'%d-%m-%Y') >= :fromdate:";
            $data["fromdate"] = $params['fromdate'];
        }

        if (!empty($params['todate'])) {
            $query .= " AND DATE_FORMAT(md.created_date,'%d-%m-%Y') <= :todate:";
            $data["todate"] = $params['todate'];
        }

        if (!empty($params['product'])) {
            $products = is_array($params['product']) ? $params['product'] : explode(',', $params['product']);
            $products = implode(',', array_map(fn($item) => $this->db->escape($item), $products));
            $query .= " AND pt.product_name IN ($products)";
        }

        if (!empty($params['creator'])) {
            $creators = is_array($params['creator']) ? $params['creator'] : explode(',', $params['creator']);
            $creators = implode(',', array_map(fn($item) => $this->db->escape($item), $creators));
            $query .= " AND ut.first_name IN ($creators)";
        }

        if (!empty($params['meettype'])) {
            $meetingnames = is_array($params['meettype']) ? $params['meettype'] : explode(',', $params['meettype']);
            $meetingnames = implode(',', array_map(fn($item) => $this->db->escape($item), $meetingnames));
            $query .= " AND mt.meeting_type_name IN ($meetingnames)";
        }

        // Group by necessary fields
        $query .= " GROUP BY 
                        md.meeting_details_id, mt.meeting_type_name, ut.first_name, pt.product_name, md.created_date, md.meeting_start_time, md.meeting_end_time, md.meeting_description";

        // Order by date
        $query .= " ORDER BY md.created_date DESC";

        // Execute the query and return the result
        $result = $this->db->query($query, $data);

        return $result->getResultArray();
    }

    /**
     * Retrieve sprint report details.
     *
     * @return array
     */
    public function getSprintReportView(): array
    {
        $query = '
         SELECT
            ts.sprint_name AS "Sprint Name",
            ts.sprint_version AS "Version",
            ts.sprint_goal as "Sprint Goal",
            pt.product_name AS "Product Name",
            ts.start_date AS "Start Date",
            ts.end_date AS "End Date",
            tsd.sprint_duration_value AS "Sprint Duration",
            ts.created_date As "Created Date",
            tc.customer_name AS "Customer Name",
            orvexa_status.status_name AS "Status"
        FROM orvexa_sprint AS ts
        INNER JOIN orvexa_sprint_duration AS tsd ON tsd.sprint_duration_id = ts.r_sprint_duration_id AND tsd.is_deleted = "N"
        INNER JOIN orvexa_customer AS tc ON tc.customer_id = ts.r_customer_id AND tc.is_deleted = "N"
        INNER JOIN orvexa_module_status AS tms ON tms.module_status_id = ts.r_module_status_id AND tms.is_deleted = "N"
        INNER JOIN orvexa_product AS pt ON pt.external_project_id = ts.r_product_id AND pt.is_deleted = "N"
        INNER JOIN orvexa_status AS orvexa_status ON orvexa_status.status_id = tms.r_status_id AND orvexa_status.is_deleted = "N"
        WHERE ts.is_deleted = "N"
        ORDER BY ts.created_date DESC
    ';

        $result = $this->db->query($query);
        return $result->getResultArray();
    }


    /**
     * Filter sprint report based on given parameters.
     *
     * @param array $params
     * @return array
     */
    public function getFilterSprintReportView(array $params, $download = null): array
    {
        $query = '
        SELECT
            ts.sprint_name AS "Sprint Name",
            ts.sprint_version AS "Version",
            ts.sprint_goal AS "Sprint Goal",
            pt.product_name AS "Product Name",
            ts.start_date AS "Start Date",
            ts.end_date AS "End Date",
            tsd.sprint_duration_value AS "Sprint Duration",
            ts.created_date AS "Created Date",
            tc.customer_name AS "Customer Name",
            orvexa_status.status_name AS "Status"';

        if ($download) {
            $query .= ',
            CONCAT(ROUND(AVG(IFNULL(st.completed_percentage, 0)), 0), " %") AS sprint_completed,
            SUM(IFNULL(st.estimated_hours, 0)) AS total_estimated_hours,
            ts.created_date AS "Created Date",
            su.external_username AS "Created By",
            su_updated.external_username AS "Updated By",
            ts.updated_date AS "Updated Date"';
        }

        $query .= '
        FROM orvexa_sprint AS ts
            INNER JOIN orvexa_sprint_duration AS tsd ON tsd.sprint_duration_id = ts.r_sprint_duration_id AND tsd.is_deleted = "N"
            INNER JOIN orvexa_customer AS tc ON tc.customer_id = ts.r_customer_id AND tc.is_deleted = "N"
            INNER JOIN orvexa_module_status AS tms ON tms.module_status_id = ts.r_module_status_id AND tms.is_deleted = "N"
            INNER JOIN orvexa_product AS pt ON pt.external_project_id = ts.r_product_id AND pt.is_deleted = "N"
            INNER JOIN orvexa_user AS su ON su.external_employee_id = ts.r_user_id_created AND su.is_deleted = "N"
            INNER JOIN orvexa_user AS su_updated ON su_updated.external_employee_id = ts.r_user_id_updated AND su_updated.is_deleted = "N"
            INNER JOIN orvexa_status AS orvexa_status ON orvexa_status.status_id = tms.r_status_id AND orvexa_status.is_deleted = "N"
            LEFT JOIN orvexa_sprint_task AS sst ON ts.sprint_id = sst.r_sprint_id AND sst.is_deleted = "N"
            LEFT JOIN orvexa_task AS st ON st.task_id = sst.r_task_id AND st.is_deleted = "N"
            WHERE ts.is_deleted = "N"';

        $data = [];

        if (!empty($params['fromdate'])) {
            $query .= " AND DATE(ts.start_date) >= :fromdate:";
            $data["fromdate"] = date('Y-m-d', strtotime($params['fromdate']));
        }

        if (!empty($params['todate'])) {
            $query .= " AND DATE(ts.end_date) <= :todate:";
            $data["todate"] = date('Y-m-d', strtotime($params['todate']));
        }

        if (!empty($params['product'])) {
            $products = implode(',', array_map(fn($item) => $this->db->escape($item), $params['product']));
            $query .= " AND pt.product_name IN ($products)";
        }

        if (!empty($params['customer'])) {
            $customers = implode(',', array_map(fn($item) => $this->db->escape($item), $params['customer']));
            $query .= " AND tc.customer_name IN ($customers)";
        }

        if (!empty($params['status'])) {
            $statuses = implode(',', array_map(fn($item) => $this->db->escape($item), $params['status']));
            $query .= " AND orvexa_status.status_name IN ($statuses)";
        }

        $query .= " GROUP BY ts.sprint_id";
        $query .= " ORDER BY ts.created_date DESC";

        $result = $this->db->query($query, $data);
        return $result->getResultArray();
    }




    /**
     * Retrieve backlog report details.
     *
     * @return array
     */
    public function getBacklogReportView(): array
    {
        $query = '
        SELECT 
            bi.backlog_item_name AS "Backlog Name",
            pt.product_name AS "Product Name",
            ct.customer_name AS "Customer Name",
            bt.tracker AS "Backlog Type",
            bi.priority AS "Priority",
            bi.backlog_description AS "Description",
            sts.t_size_name AS "T_Shirt_Size",
            orvexa_status.status_name AS "Status"
        FROM orvexa_backlog_item AS bi
        INNER JOIN orvexa_trackers AS bt ON bt.tracker_id = bi.r_tracker_id
        INNER JOIN orvexa_product AS pt ON pt.external_project_id = bi.r_product_id AND pt.is_deleted = "N"
        INNER JOIN orvexa_customer AS ct ON ct.customer_id = bi.r_customer_id AND ct.is_deleted = "N"
        INNER JOIN orvexa_status AS orvexa_status ON orvexa_status.status_id = bi.r_module_status_id AND orvexa_status.is_deleted = "N"
        INNER JOIN orvexa_user AS su ON su.external_employee_id = bi.r_user_id_created AND su.is_deleted = "N"
        INNER JOIN orvexa_user AS su_updated ON su_updated.external_employee_id = bi.r_user_id_updated AND su_updated.is_deleted = "N"
        INNER JOIN orvexa_t_shirt_size as sts ON sts.t_shirt_size_id=bi.backlog_t_shirt_size
        WHERE bi.is_deleted = "N"
        ';

        $result = $this->db->query($query);

        return $result->getResultArray();
    }

    /**
     * Filter backlog report based on given parameters.
     *
     * @param array $params
     * @param bool|null $download
     * @return array
     */
    public function getFilterBacklogReportView(array $params, $download = null): array
    {
        $query = '
       SELECT 
            bi.backlog_item_name AS "Backlog Name",
            pt.product_name AS "Product Name",
            ct.customer_name AS "Customer Name",
            bt.tracker AS "Backlog Type",
            bi.priority AS "Priority",
            bi.backlog_description AS "Description",
            sts.t_size_name AS "T_Shirt_Size",
            orvexa_status.status_name AS "Status"';

        if ($download == true) {
            $query .= ',

            bi.backlog_order AS "Backlog Order",
            su.external_username AS "Created By",
            bi.created_date AS "Created Date",
            su_updated.external_username AS "Updated By",
            bi.updated_date AS "Updated Date"
        ';
        }
        $query .= '
    FROM orvexa_backlog_item AS bi
            INNER JOIN orvexa_trackers AS bt ON bt.tracker_id = bi.r_tracker_id 
            INNER JOIN orvexa_product AS pt ON pt.external_project_id = bi.r_product_id AND pt.is_deleted = "N"
            INNER JOIN orvexa_customer AS ct ON ct.customer_id = bi.r_customer_id AND ct.is_deleted = "N"
            INNER JOIN orvexa_status AS orvexa_status ON orvexa_status.status_id = bi.r_module_status_id AND orvexa_status.is_deleted = "N"
            INNER JOIN orvexa_user AS su ON su.external_employee_id = bi.r_user_id_created AND su.is_deleted = "N"
            INNER JOIN orvexa_user AS su_updated ON su_updated.external_employee_id = bi.r_user_id_updated AND su_updated.is_deleted = "N"
            LEFT JOIN orvexa_epic e ON bi.backlog_item_id = e.r_backlog_item_id AND e.is_deleted = "N"
            LEFT JOIN orvexa_user_story us ON bi.backlog_item_id = us.r_epic_id AND us.is_deleted = "N"
            INNER JOIN orvexa_t_shirt_size as sts ON sts.t_shirt_size_id=bi.backlog_t_shirt_size
            WHERE bi.is_deleted = "N"';

        if (!empty($params['product'])) {
            $products = implode(',', array_map(fn($item) => $this->db->escape($item), $params['product']));
            $query .= " AND pt.product_name IN ($products)";
        }

        if (!empty($params['customer'])) {
            $customers = implode(',', array_map(fn($item) => $this->db->escape($item), $params['customer']));
            $query .= " AND ct.customer_name IN ($customers)";
        }

        if (!empty($params['status'])) {
            $statuses = implode(',', array_map(fn($item) => $this->db->escape($item), $params['status']));
            $query .= " AND orvexa_status.status_name IN ($statuses)";
        }
        if(!empty($params["trackername"])){
            $trackers=implode(',',array_map(fn($item)=>$this->db->escape($item),$params['trackername']));
            $query.=" AND bt.tracker IN($trackers)";

        }

        $query .= " GROUP BY bi.backlog_item_id"; 

        $result = $this->db->query($query);
    
        return $result->getResultArray();
    }

    //allreportDropDowns
    public function reportDropDowns($reportType): array
    {
        // Get the module ID from the constants
        $moduleId = $this->constants->statusNames[$reportType];

        // Define dropdown queries
        $dropdownQueries = [
            "product_result" => "SELECT DISTINCT product_name FROM orvexa_product",
            "user_result" => "SELECT DISTINCT first_name FROM orvexa_user",
            "meeting_type_result" => "SELECT DISTINCT meeting_type_name FROM orvexa_meeting_type",
            "sprint_name_result" => "SELECT DISTINCT sprint_name FROM orvexa_sprint",
            "sprint_version_result" => "SELECT DISTINCT sprint_version FROM orvexa_sprint",
            "sprint_duration_result" => "SELECT DISTINCT sprint_duration_value FROM orvexa_sprint_duration",
            "customer_name_result" => "SELECT DISTINCT customer_name FROM orvexa_customer",
            "status_result" => "
            SELECT 
            ss.status_name as status_name
            FROM orvexa_module_status as sms
            INNER JOIN orvexa_module as sm on sm.module_id=sms.r_module_id
            INNER JOIN orvexa_status as ss on ss.status_id=sms.r_status_id
            WHERE sms.r_module_id = ?
        ",
            "backlog_item_result" => "SELECT DISTINCT tracker  FROM orvexa_trackers"
        ];

        // Initialize the final result array
        $finalResult = [];

        // Execute each query and store the results
        foreach ($dropdownQueries as $key => $query) {
            if ($key === 'status_result') {
                // Bind the module ID for the status_result query to prevent SQL injection
                $finalResult[$key] = $this->db->query($query, [$moduleId])->getResultArray();
            } else {
                // Execute other queries without binding
                $finalResult[$key] = $this->db->query($query)->getResultArray();
            }
        }

        return $finalResult;
    }


}
