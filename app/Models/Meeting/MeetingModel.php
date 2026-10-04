<?php

/**
 * MeetingModel.php
 *
 * @category   Model
 * @author    Sankar R, Gokul B ,Rama Selvan M, Ruban Edward R
 * @created    09 July 2024
 * @purpose    Retrives the Meeting details, Meeting Type, Meeting Location
 *             Product Details and Sprint Details
 */

namespace App\Models\Meeting;

use App\Models\BaseModel;

class MeetingModel extends BaseModel
{
    /**
     * @author Ruban Edward R
     * @return array
     * Retrieves Meeting types and displayes in the select option in scheduling Meeting
     */
    public function getMeetingType(): array
    {
        // retrieves the meeting types from table
        $sql = "SELECT
                    *
                FROM
                    orvexa_meeting_type where  is_deleted = 'N'";
        $query = $this->query($sql);
        return $query->getResultArray(); //returns result as an array format
    }

    /**
     * @author Ruban Edward R
     * @return array
     * Retrieves Meeting Location and displayes in the select option in scheduling Meeting
     */
    public function getMeetingLocation(): array
    {
        // retrieves the meeting location from table
        $sql = "SELECT
                    *
                FROM
                    orvexa_meeting_location";
        $query = $this->query($sql);
        return $query->getResultArray(); //returns result as an array format
    }

    /**
     * @author Rama Selvan M
     * @param int $id
     * @return array
     * Retrieves Meeting Details based on the ID and displays on the Modal
     */
    public function getMeetingById($id, $meetType): array
    {
        // Start with the common part of the SQL query
        $sql = "SELECT DISTINCT
                details.meeting_details_id,
                details.meeting_title,
                products.product_name,
                users.first_name,
                details.meeting_start_date,
                details.meeting_start_time,
                details.r_user_id,
                details.meeting_end_date,
                details.meeting_end_time,
                details.meeting_duration,
                details.meeting_description,
                details.meeting_link,
                details.r_user_id_created,
                details.is_logged,
                details.r_meeting_type_id,
                details.r_product_id,
                details.r_meeting_location_id,
                details.is_deleted,
                details.cancel_reason,
                details.recurrance_meeting_id";

        // Conditionally add parts of the SQL query
        if ($meetType != 1 && $meetType != 2) {
            $sql .= ", sprint.sprint_name";
            $sql .= ", details.r_sprint_id";
        }

        if ($meetType == 2) {
            $sql .= ", brainstorm.r_backlog_item_id";
        }

        $sql .= " FROM
                orvexa_meeting_details AS details
                INNER JOIN orvexa_product AS products ON products.external_project_id = details.r_product_id
                INNER JOIN orvexa_user AS users ON users.external_employee_id = details.r_user_id ";

        if ($meetType != 1 && $meetType != 2) {
            $sql .= "INNER JOIN orvexa_sprint AS sprint ON sprint.sprint_id = details.r_sprint_id";
        }

        if ($meetType == 2) {
            $sql .= "INNER JOIN orvexa_brainstorm_meeting_details AS brainstorm ON brainstorm.r_meeting_details_id = details.meeting_details_id";
        }

        $sql .= " WHERE
                details.meeting_details_id = :id:";

        // Using Bind Param for executing the query
        $result = $this->db->query($sql, ['id' => $id]);
        return $result->getResultArray(); // Returns result as an array format
    }

    /**
     * @author Rama Selvan M
     * @updated Ruban Edward
     * @return array
     * Retrieves Products to display in the schedule Meeting and also in Calendar Filter
     */
    public function getProduct($id): array
    {
        $sql = "SELECT
                    product_id,
                    external_project_id,
                    product_name
                FROM
                    orvexa_product
                    INNER JOIN orvexa_product_user ON orvexa_product.external_project_id = orvexa_product_user.r_product_id
                WHERE
                    orvexa_product_user.r_user_id = :id:";
        $query = $this->query($sql, ["id" => $id]);
        return $query->getResultArray(); //returns result as an array format
    }

    /**
     * @author Gokul B
     * @return array
     * Retrieves sprint duration details and displays in the calendar view
     */
    public function getSprintDuration(): array
    {
        $sql = "SELECT
                    sprint_duration_id,
                    sprint_duration_value
                FROM
                    orvexa_sprint_duration";
        $query = $this->query($sql);
        return $query->getResultArray(); //returns result as an array format
    }

    /**
     * @author Gokul B
     * @return array
     * Retrieves sprint status details and displays in the calendar view
     */
    public function getSprintStatus(): array
    {
        // Convert the constant array into a comma-separated string
        $statuses = implode(',', SPRINT_STATUS_FOR_MEETING);

        $sql = "SELECT
                ms.module_status_id,
                st.status_name
            FROM
                orvexa_module_status AS ms
            INNER JOIN
                orvexa_status AS st
            ON
                ms.r_status_id = st.status_id
            WHERE
                ms.module_status_id IN ($statuses)
            ORDER BY
                ms.module_status_id";

        $query = $this->query($sql);
        return $query->getResultArray(); // Returns result as an array format
    }

    /**
     * @author Gokul B
     * @return array
     * Retrieves sprint details and displays in the calendar view
     */
    public function ShowSprint($id): array
    {
        $sql = "SELECT
                    DISTINCT orvexa_sprint.sprint_id,
                    orvexa_sprint.sprint_name,
                    orvexa_product.product_name,
                    orvexa_sprint.start_date,
                    orvexa_sprint.end_date,
                    orvexa_status.status_name,
                    orvexa_sprint_duration.sprint_duration_value,
                    orvexa_customer.customer_name,
                    orvexa_user.first_name
                FROM
                    orvexa_sprint
                INNER JOIN orvexa_product
                    ON orvexa_sprint.r_product_id = orvexa_product.external_project_id
                INNER JOIN orvexa_module_status
                    ON orvexa_sprint.r_module_status_id = orvexa_module_status.module_status_id
                INNER JOIN orvexa_sprint_duration
                    ON orvexa_sprint.r_sprint_duration_id = orvexa_sprint_duration.sprint_duration_id
                INNER JOIN orvexa_customer
                    ON orvexa_sprint.r_customer_id = orvexa_customer.customer_id
                INNER JOIN orvexa_user
                    ON orvexa_sprint.r_user_id_created = orvexa_user.external_employee_id
                INNER JOIN orvexa_status
                    ON orvexa_module_status.r_status_id = orvexa_status.status_id
                INNER JOIN orvexa_module
                    ON orvexa_module_status.r_module_id = orvexa_module.module_id
                INNER JOIN orvexa_sprint_user
                    ON orvexa_sprint_user.r_sprint_id = orvexa_sprint.sprint_id
                WHERE
                    orvexa_module_status.module_status_id IN (19, 20, 23)
                    AND orvexa_sprint_user.r_user_id = :id:
                    AND orvexa_sprint.start_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $result = $this->query($sql, ["id" => $id]);
        return $result->getResultArray(); //returns result as an array format
    }

    /**
     * @author Gokul B
     * @return array
     * Retrieves user details depend on product and displaying model
     */
    public function getUser(): array
    {
        $sql = "SELECT DISTINCT
                    su.first_name
                FROM
                    orvexa_user AS su
                INNER JOIN orvexa_product_user AS pu
                    ON pu.r_user_id = su.external_employee_id
                INNER JOIN orvexa_product AS sp
                    ON sp.external_project_id = pu.r_product_id";
        $result = $this->query($sql);
        return $result->getResultArray(); //returns result as an array format
    }

    /**
     * @author Ruban Edward R
     * @return array
     * Retrieves user details depend on product and displaying model
     */
    public function getUserByProductId($id): array
    {
        $sql = "SELECT DISTINCT
                    su.first_name
                FROM
                    orvexa_user AS su
                INNER JOIN orvexa_product_user AS pu
                    ON pu.r_user_id = su.external_employee_id
                INNER JOIN orvexa_product AS sp
                    ON sp.external_project_id = pu.r_product_id
                WHERE
                    sp.external_project_id = :id:";
        $result = $this->query($sql, [
            "id" => $id,
        ]);
        return $result->getResultArray(); //returns result as an array format
    }

    /**
     * @author Gokul B
     * @param int $id
     * @return array
     * Retrieves sprint details based on ID and displaying model
     */
    public function getSprintById($id): array
    {
        $sql = "SELECT
                    orvexa_sprint.sprint_id,
                    orvexa_sprint.sprint_name,
                    orvexa_product.product_name,
                    orvexa_sprint.start_date,
                    orvexa_sprint.end_date,
                    orvexa_sprint_duration.sprint_duration_value,
                    orvexa_customer.customer_name,
                    orvexa_status.status_name,
                    orvexa_user.first_name
                FROM
                    orvexa_sprint
                INNER JOIN
                    orvexa_product
                ON
                    orvexa_sprint.r_product_id = orvexa_product.external_project_id
                INNER JOIN
                    orvexa_sprint_duration
                ON
                    orvexa_sprint.r_sprint_duration_id = orvexa_sprint_duration.sprint_duration_id
                INNER JOIN
                    orvexa_customer
                ON
                    orvexa_sprint.r_customer_id = orvexa_customer.customer_id
                INNER JOIN
                    orvexa_user
                ON
                    orvexa_sprint.r_user_id_created = orvexa_user.external_employee_id
                INNER JOIN
                    orvexa_module_status
                ON
                    orvexa_module_status.module_status_id = orvexa_sprint.r_module_status_id
                INNER JOIN
                    orvexa_status
                ON
                    orvexa_module_status.r_status_id = orvexa_status.status_id
                INNER JOIN
                    orvexa_module
                ON
                    orvexa_module_status.r_module_id = orvexa_module.module_id
                WHERE
                     orvexa_sprint.sprint_id = :id:";

        //using Bind Param for executing the query
        $result = $this->query($sql, ['id' => $id]);
        return $result->getResultArray(); //returns result as an array format
    }

    /**
     * @author Gokul B
     * @param int $id
     * @return array
     * Retrieves sprint details based on Sprint ID and displaying model
     */
    public function getSprintByproduct($id): array
    {
        $sql = "SELECT
                    s.sprint_id,
                    s.sprint_name
                FROM
                    orvexa_sprint as s
                INNER JOIN
                    orvexa_product as p
                ON
                    s.r_product_id = p.external_project_id
                WHERE
                    p.external_project_id = :id:";
        $query = $this->query($sql, ['id' => $id]);
        return $query->getResultArray(); //returns result as an array format
    }

    /**
     *  @author Ruban Edward R
     * to get the members mail id
     * @param array
     * @return array
     */
    public function getUserId($membersArray): array
    {
        $placeholders = implode(',', array_fill(0, count($membersArray), '?'));

        $sql = "SELECT
                    external_employee_id,
                    email_id
                FROM
                    orvexa_user
                WHERE
                    first_name IN ({$placeholders})";
        $query = $this->query($sql, $membersArray);
        return $query->getResultArray();
    }

    /**
     * @author Hari Sankar R
     * @param mixed
     * Updated the meeting Details for cancel reason and soft deleteing the meeting
     */
    public function getMeetingMembersConflict($meetData): mixed
    {
        $sql = "SELECT
                u.first_name,
                d.meeting_start_time,
                d.meeting_end_time
            FROM
                orvexa_meeting_members AS m
            INNER JOIN
                orvexa_meeting_details AS d
                ON d.meeting_details_id = m.r_meeting_details_id
            INNER JOIN
                orvexa_user AS u
                ON u.external_employee_id = m.r_user_id
            WHERE
                d.meeting_start_date = :meeting_start_date:
                AND (
                    (d.meeting_start_time >= :meeting_start_time: AND d.meeting_end_time <= :meeting_end_time:)
                    OR
                    (d.meeting_start_time <= :meeting_start_time: AND d.meeting_end_time >= :meeting_end_time:)
                    OR
                    (d.meeting_start_time <= :meeting_start_time: AND d.meeting_end_time >= :meeting_start_time:)
                    OR
                    (d.meeting_start_time <= :meeting_end_time: AND d.meeting_end_time >= :meeting_end_time:)
                )";
        $query = $this->query($sql, [
            'meeting_start_date' => $meetData['meeting_start_date'],
            'meeting_start_time' => $meetData['meeting_start_time'],
            'meeting_end_time' => $meetData['meeting_end_time'],
        ]);
        return $query->getResultArray();
    }

    /**
     * @author Ruban Edward R
     * @param string $groupName
     * @return array
     * Retrieves the users depend upon the team and display the modal
     */
    public function getGroupDetails($groupName)
    {
        $sql = "SELECT
                    meeting_team_id
                FROM
                    orvexa_meeting_team
                WHERE
                    meeting_team_name = :groupName:";

        $query = $this->query($sql, [
            "groupName" => trim($groupName),
        ]);
        $groupId = $query->getResultArray();
        if ($groupId) {
            $sql1 = "SELECT
                        r_external_employee_id
                    FROM
                        orvexa_meeting_team_members
                    WHERE
                        r_meeting_team_id = :groupId:
                        AND is_deleted = :is_deleted:";

            $query1 = $this->query($sql1, [
                "groupId" => $groupId[0]['meeting_team_id'],
                'is_deleted' => "N",
            ]);

            return $query1->getResultArray();
        }
        return [];
    }

    /**
     * @author Ruban Edward R
     * @param int $id
     * @return array
     * Retrieves email against user Id and displaying the modal
     */
    public function getUserIdEmail($userId)
    {
        $placeholders = implode(',', array_fill(0, count($userId), '?'));

        $sql = "SELECT
                    first_name
                FROM
                    orvexa_user
                WHERE
                    external_employee_id IN ({$placeholders})";
        $query = $this->query($sql, $userId);
        return $query->getResultArray();
    }

    /**
     * @author Gokul B
     * @param int $id
     * @return array
     * Retrieves backlog, epic, and userStory Id for displaying the modal
     */
    public function getUserStoryId($data)
    {
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $sql = "SELECT
                    e.r_backlog_item_id,
                    us.r_epic_id,
                    us.user_story_id
                FROM
                    orvexa_user_story AS us
                INNER JOIN
                    orvexa_epic AS e
                    ON e.epic_id = us.r_epic_id
                WHERE
                    user_story_id IN ({$placeholders})";

        $query = $this->query($sql, $data);
        return $query->getResultArray();
    }

    /**
     * @author Hari shankar R
     * @param array $data
     * @return array
     * Retrieves team details from meeting based on meeting Id
     */
    public function getTeamDetailsById($data)
    {
        $sql = "SELECT
                sm.r_external_employee_id,
                u.first_name,
                st.meeting_team_name,
                st.r_product_id
            FROM
                orvexa_meeting_team_members as sm
            INNER JOIN orvexa_user as u on sm.r_external_employee_id=u.external_employee_id
            INNER JOIN orvexa_meeting_team as st on st.meeting_team_id=sm.r_meeting_team_id
            WHERE
                r_meeting_team_id=:r_meeting_team_id:
            AND
                sm.is_deleted='N'
        ";
        $result = $this->query($sql, ['r_meeting_team_id' => $data['r_meeting_team_id']]);
        if ($result) {
            return $result->getResultArray();
        }
        return [];
    }

    /**
     * @author Rama Selvan
     * Get members of a sprint.
     * Retrieves details of all members associated with the given sprint Id.
     * @param int $sprintId Sprint identifier.
     * @return array List of members with their details.
     */
    public function getSprintMembers($sprintId)
    {
        $sql = 'SELECT
                        DISTINCT su.first_name,
                        su.last_name,
                        su.email_id ,
                        su.external_employee_id
                    FROM
                        orvexa_sprint_user AS ssu
                    JOIN orvexa_user AS su
                    ON ssu.r_user_id = su.external_employee_id
                    WHERE
                        ssu.r_sprint_id = ?
                        AND ssu.is_deleted = "N"';

        $result = $this->query($sql, [$sprintId]);
        return $result->getResultArray();
    }

    /**
     * @author Rama Selvan
     * Get product ID by name.
     * Finds the product ID for a product matching the given name.
     * @param string $name Product name.
     * @return int|null Product ID or null if not found.
     */
    public function getProductId($name)
    {
        $sql = "SELECT
                    product_id
                FROM
                    orvexa_product
                WHERE
                    product_name LIKE :name:";

        $result = $this->query($sql, ['name' => '%' . $name . '%']);

        if ($result) {
            return $result->getRow()->product_id ?? null; // Return product_id or null if no matching product is found
        }
    }

    /**
     * @author Ruban Edward
     * @param array $data
     * @return array
     * gets the members based on product
     */
    public function getMembersByProduct($data)
    {
        $sql = "SELECT
                    u.external_employee_id,
                    u.first_name
                FROM
                    orvexa_product_user AS pu
                INNER JOIN
                    orvexa_product AS p
                ON
                    pu.r_product_id = p.external_project_id
                INNER JOIN
                    orvexa_user AS u
                ON
                    pu.r_user_id = u.external_employee_id
                WHERE
                    p.external_project_id=:external_project_id:
                ORDER BY
                    u.first_name ASC";
        $result = $this->query($sql, ['external_project_id' => $data['external_project_id']]);
        if ($result) {
            return $result->getResultArray();
        }
        return [];
    }

    /**
     * To get the product backlog name and id to show in meeting schedule
     *
     * @author Hari Sankar <email>
     * @param int $id
     * @return array
     */
    public function getbacklogByproduct($id): array
    {
        $sql = "SELECT
                    b.backlog_item_id,
                    b.backlog_item_name
                FROM
                    orvexa_backlog_item as b
                INNER JOIN
                    orvexa_product as p
                ON
                    b.r_product_id = p.external_project_id
                WHERE
                    p.external_project_id = :id:";
        $query = $this->query($sql, ['id' => $id]);
        return $query->getResultArray(); //returns result as an array format
    }

    /**
     * gets the email Id for sending the mail
     *
     * @author rubanedward <email>
     * @param int $id
     * @return array
     */
    public function getCancelEmailId($id)
    {
        $sql = 'SELECT
                    su.email_id,
                    sp.product_name,
                    md.meeting_start_date,
                    md.meeting_start_time,
                    md.meeting_end_time,
                    md.cancel_reason
                FROM
                    orvexa_user AS su
                INNER JOIN orvexa_meeting_members AS mm
                    ON su.external_employee_id = mm.r_user_id
                INNER JOIN orvexa_meeting_details AS md
                    ON md.meeting_details_id = mm.r_meeting_details_id
                INNER JOIN orvexa_product AS sp
                    ON sp.external_project_id = md.r_product_id
                WHERE
                    md.meeting_details_id = :id:';

        $query = $this->query($sql, [
            'id' => $id,
        ]);

        return $query->getResultArray();
    }

    /**
     * @author gokul.b
     * @return array
     * This function is used to get the data from current time to after 15 Mins meeting only.
     */
    public function getMeetingDetails($currentDate, $currentTime, $next15Minutes): array
    {
        $sql = "SELECT
                        DISTINCT (members.r_meeting_details_id) AS meet_id,
                        user.email_id,
                        product.product_name,
                        details.meeting_title,
                        details.meeting_description,
                        details.meeting_start_date,
                        details.meeting_start_time,
                        details.meeting_end_time,
                        details.meeting_link,
                        meetType.meeting_type_name
                    FROM
                        orvexa_meeting_members AS members
                        INNER JOIN orvexa_meeting_details AS details ON members.r_meeting_details_id = details.meeting_details_id
                        INNER JOIN orvexa_user AS user ON members.r_user_id = user.external_employee_id
                        INNER JOIN orvexa_product AS product ON details.r_product_id = product.product_id
                        INNER JOIN orvexa_meeting_type AS meetType ON details.r_meeting_type_id = meetType.meeting_type_id
                    WHERE
                        details.meeting_start_date = '$currentDate'
                        AND details.meeting_start_time >= '$currentTime'
                        AND details.meeting_start_time <= '$next15Minutes'";
        $query = $this->query($sql);
        return $query->getResultArray();
    }

    /**
     * Gets the sprint members based on the sprint ID
     * @author Hari Sankar R
     * @param int $sprintId
     * @return array
     */
    public function getSprintMembersById($sprintId){
        $sql="SELECT 
                    SU.first_name
                FROM
                    orvexa_user AS SU
                INNER JOIN orvexa_sprint_user as SS ON SS.r_user_id=SU.external_employee_id
                WHERE
                SS.r_sprint_id=:sprintId: AND SS.is_deleted='N'
            ";
        $result = $this->query($sql, ['sprintId' => $sprintId]);
        if ($result) {
            return $result->getResultArray();
        }
    }
}
