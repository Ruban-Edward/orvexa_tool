<?php

namespace App\Models\Backlog;

use App\Models\BaseModel;

class BacklogModel extends BaseModel
{
    /**
     * @author Murugadass
     * @return string
     * Purpose: This function is used to return the product name of a particular product
     */

    public function getProductDetails($input): string
    {
        $sql = "SELECT 
                    product_name 
                FROM 
                    orvexa_product
                WHERE 
                    external_project_id = :p_id:";
        $query = $this->query($sql, [
            'p_id' => $input
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getRow()->product_name;
        }
        return '';
    }

    /**
     * @author Murugadass
     * @return array
     * Purpose: This function is used to return the all the products associated with a user
     */

    public function getUserProduct($user_id): array
    {
        $sql = "SELECT 
                    p.external_project_id as product_id,p.product_name 
                FROM 
                    orvexa_product p
                INNER JOIN 
                    orvexa_product_user pu ON pu.r_product_id=p.external_project_id
                INNER JOIN 
                    orvexa_user u ON u.external_employee_id = pu.r_user_id
                WHERE 
                    u.external_employee_id = :u_id:";
        $query = $this->query($sql, [
            'u_id' => $user_id
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author samuel
     * @return array
     * Purpose: This function is used to return the products details which are required porducts view page
     */

    public function getUserProductDetails($user_id): array
    {
        $sql = "SELECT 
                    p.external_project_id AS product_id,
                    p.product_name,
                    COUNT(DISTINCT bi.backlog_item_id) AS number_of_backlog_items,
                    COUNT(us.user_story_id) AS number_of_user_stories,
                    p.updated_date as last_updated,
                    COUNT(CASE WHEN bi.r_module_status_id = 11 THEN bi.backlog_item_id END) AS on_hold
                FROM 
                    orvexa_product p
                INNER JOIN 
                    orvexa_product_user pu ON pu.r_product_id = p.external_project_id AND pu.is_deleted = 'N'
                LEFT JOIN 
                    orvexa_backlog_item bi ON bi.r_product_id = pu.r_product_id AND bi.is_deleted = 'N'
                LEFT JOIN 
                    orvexa_epic e ON e.r_backlog_item_id = bi.backlog_item_id AND e.is_deleted = 'N'
                LEFT JOIN 
                    orvexa_user_story us ON us.r_epic_id = e.epic_id AND us.is_deleted = 'N'
                LEFT JOIN 
                    orvexa_module_status ms ON ms.module_status_id = bi.r_module_status_id AND ms.is_deleted = 'N'
                WHERE 
                    pu.r_user_id = :u_id:
                GROUP BY 
                    pu.r_product_id
                ORDER BY
                    last_updated DESC";
        $query = $this->query($sql, [
            'u_id' => $user_id
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Murugadass
     * @return array
     * Purpose: This function is used to return the status details particular module
     */

    public function getStatus($mId): array
    {
        $sql = "SELECT 
                    module_status_id AS status_id, 
                    s.status_name 
                FROM 
                    orvexa_module_status 
                JOIN orvexa_status s ON s.status_id = r_status_id WHERE r_module_id =:m_id:";

        $query = $this->query($sql, [
            'm_id' => $mId
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Abinandhan
     * @param int id of the product for which the refinement is done
     * @purpose for returning the items for the backlog refinement page
     */

    public function getRefinement($id)
    {
        $sql = "SELECT
                    bi.backlog_item_id,
                    bi.r_product_id,
                    bi.backlog_item_name,
                    bi.r_module_status_id,
                    sc.customer_name,
                    st.tracker,
                    bst.status_name,
                    bi.priority,
                    bi.backlog_order,
                    bi.backlog_description
                FROM 
                    orvexa_backlog_item AS bi
                INNER JOIN 
                    orvexa_customer sc ON sc.customer_id = bi.r_customer_id AND sc.is_deleted = 'N'
                INNER JOIN 
                    orvexa_trackers st ON st.tracker_id = bi.r_tracker_id
                INNER JOIN 
                    orvexa_module_status bms ON bms.module_status_id = bi.r_module_status_id AND bms.is_deleted = 'N'
                INNER JOIN 
                    orvexa_status bst ON bst.status_id = bms.r_status_id AND bst.is_deleted = 'N'
                WHERE 
                    bi.r_product_id = :pId: AND 
                    bi.is_deleted = 'N' AND 
                    bst.status_name <> 'In Sprint' AND 
                    bst.status_name <> 'completed'
                ORDER BY
                    bi.backlog_order ASC;";

        $query = $this->query($sql, [
            'pId' => $id
        ]);

        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Abinandhan
     * @return array
     * @purpose for returning backlogorder of backlog items in the backlogrefinement page
     */

    public function backlogRefinement()
    {
        $sql = "SELECT 
                    backlog_item_id,
                    backlog_order 
                FROM 
                    orvexa_backlog_item";
        $query = $this->db->query($sql);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Abinandhan
     * @param array contains the differnt items for which the priority is changed
     * @purpose updating the priority order of the backlog items
     */

    public function updateBacklogRefinement($arrayDiff): bool
    {
        $arr = [];
        foreach ($arrayDiff as $key => $value) {
            $arr['key'] = $key;
            $arr['value'] = $value;
            $sql = 'update orvexa_backlog_item set backlog_order=:value: where backlog_item_id=:key:';
            $query = $this->query($sql, ['value' => $arr['value'], 'key' => $arr['key']]);
            if (!$query) {
                return 0;
            }
        }
        return 1;
    }

    /**
     * @author Abinandhan
     * @return array returns the user action history of a product
     * @param int id of a product
     * Purpose: This function is used to display the history data in the histroy page
     */

    public function getActionHistory($pId, $pblId = null): array
    {

        $sql = "SELECT 
                    action_id,
                    r_user_id,
                    s.action_type_name AS actionName,
                    m.module_name,
                    orvexa_user.first_name AS firstName,
                    orvexa_user.last_name AS lastName,
                    action_data,
                    action_date
                FROM 
                    orvexa_user_action 
                JOIN 
                    orvexa_user ON orvexa_user.user_id = r_user_id 
                JOIN 
                    orvexa_action_type s ON s.action_type_id = r_action_type_id
                JOIN 
                    orvexa_module m ON m.module_id = r_module_id
                WHERE 
                    r_product_id=:pid: ";

        if (!is_null($pblId)) {
            $sql .= "AND 
            r_module_id>=5 AND 
            reference_id = :pblId:";
        } else {
            $sql .= "AND r_module_id=5 AND action_type_id IN (1,3) AND action_data NOT LIKE '%document%'";
        }

        $sql .= "ORDER BY 
                action_date DESC";
        $query = $this->db->query($sql, [
            'pid' => $pId,
            'pblId' => $pblId
        ]);

        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author samuel
     * @return array returns the users of a product
     * @param int id of a product
     */

    public function getUsers(): array
    {
        $sql = "SELECT 
                    external_employee_id AS user_id,
                    first_name AS user_name
                FROM 
                    orvexa_user";
        $query = $this->query($sql);
        if ($query) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author samuel
     * @return array
     * Purpose: This function is used to fetch the Customer
     */

    public function getCustomer($id)
    {
        $sql = "SELECT 
                    c.customer_name as customer_name
                FROM 
                    orvexa_customer c
                INNER JOIN 
                    orvexa_backlog_item bi ON bi.r_customer_id = c.customer_id
                INNER JOIN 
                    orvexa_epic e ON e.r_backlog_item_id = bi.backlog_item_id
                INNER JOIN 
                    orvexa_user_story us ON us.r_epic_id = e.epic_id
                WHERE 
                    us.user_story_id = :id:";

        $query = $this->query($sql, [
            'id' => $id
        ]);

        if ($query->getNumRows() > 0) {
            return $query->getRow()->customer_name;
        }
        return [];
    }

    /**
     * Summary of insertComment
     * @param mixed $data
     * @return bool
     * Purpose: This function is used to insert the comments into the database
     */

    public function insertComment($data): bool
    {

        $query = "INSERT INTO orvexa_comments (comments_id, parent_id, r_user_id, text_data, r_user_story_id, created_date, is_deleted)
            VALUES (:c_id:, :parent_id:, :r_user_id:, :text_data:, :r_user_story_id:, now(), :is_deleted:)";

        // Binding values
        $query = $this->query(
            $query,
            [
                'c_id' => $data['c_id'],
                'parent_id' => $data['parent_id'],
                'r_user_id' => $data['r_user_id'],
                'text_data' => $data['text'],
                'r_user_story_id' => $data['r_user_story_id'],
                'is_deleted' => 'N'
            ]
        );

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Summary of getComments
     * @return array
     * Purpose: This function is used to get the comment from the database
     */

    public function getComments(): array
    {
        $sql = "SELECT 
                    comments_id,
                    parent_id,
                    b.first_name AS r_user_id,
                    text_data,
                    r_user_story_id ,
                    a.created_date 
                FROM 
                    orvexa_comments a 
                JOIN 
                    orvexa_user b ON a.r_user_id=b.external_employee_id";
        $query = $this->query($sql);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * Summary of getCurrentUser
     * @param $id
     * @return mixed
     */

    public function getCurrentUser($id)
    {
        $sql = "SELECT 
                    first_name from orvexa_user where external_employee_id=:id:";
        $query = $this->query($sql, ['id' => $id]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray()[0]['first_name'];
        }
        return [];
    }

    public function addUserStoryPoint($data)
    {
        $sql = "UPDATE 
                    orvexa_user_story
                SET 
                    user_story_points  = :user_story_points:
                WHERE 
                    user_story_id = :user_story_id:";
        return $this->query($sql, [
            'user_story_points' => $data['storyPoint'],
            'user_story_id' => $data['userStoryId']
        ]);
    }

    public function getBacklogDetails($filter): array
    {
        $select = '';
        $joins = '';
        $where = '';

        if (isset($filter['id'])) {
            $select .= "
                ,bi.backlog_t_shirt_size
                ,tss.t_size_values
                ,p.product_name
                ,COUNT(CASE WHEN us.r_module_status_id = 34 THEN 1 END) AS completed_user_stories
                ,COUNT(DISTINCT us.user_story_id) AS total_user_stories
                ,COUNT(t.task_id) AS total_tasks
                ,COUNT(CASE WHEN t.task_status = 7 THEN 1 END) AS completed_tasks
            ";
        } elseif (isset($filter['refinement'])) {
            $select .= '';
        } else {
            $select .= "
                ,COUNT(CASE WHEN ust.status_name = 'New Requirement' THEN us.user_story_id END) AS new
                ,COUNT(CASE WHEN ust.status_name = 'Ready for Brainstorming' THEN us.user_story_id END) AS ready_for_brainstorming
                ,COUNT(CASE WHEN ust.status_name = 'In brainstorming' THEN us.user_story_id END) AS in_brainstorming
                ,COUNT(CASE WHEN ust.status_name = 'Completed' THEN us.user_story_id END) AS completed
                ,COUNT(CASE WHEN ust.status_name = 'In Sprint' THEN us.user_story_id END) AS in_sprint
                ,COUNT(CASE WHEN ust.status_name = 'Ready for Sprint' THEN us.user_story_id END) AS ready_for_sprint
                ,COUNT(CASE WHEN ust.status_name = 'Brainstorming completed' THEN us.user_story_id END) AS brainstorming_completed
                ,COUNT(us.user_story_id) AS total
            ";
        }

        if (isset($filter['id'])) {
            $joins = "
                LEFT JOIN orvexa_epic e ON e.r_backlog_item_id = bi.backlog_item_id AND e.is_deleted = 'N'
                LEFT JOIN orvexa_user_story us ON us.r_epic_id = e.epic_id AND us.is_deleted = 'N'
                LEFT JOIN orvexa_task t ON t.r_user_story_id = us.user_story_id AND t.is_deleted = 'N'
                LEFT JOIN orvexa_product p ON p.external_project_id = bi.r_product_id AND p.is_deleted = 'N'
                INNER JOIN orvexa_t_shirt_size tss ON tss.t_shirt_size_id = bi.backlog_t_shirt_size
            ";
        } elseif (isset($filter['refinement'])) {
            $joins = '';
        } else {
            $joins = "
                LEFT JOIN orvexa_epic e ON e.r_backlog_item_id = bi.backlog_item_id AND e.is_deleted = 'N'
                LEFT JOIN orvexa_user_story us ON us.r_epic_id = e.epic_id AND us.is_deleted = 'N'
                LEFT JOIN orvexa_module_status ums ON ums.module_status_id = us.r_module_status_id AND ums.is_deleted = 'N'
                LEFT JOIN orvexa_status ust ON ust.status_id = ums.r_status_id AND ust.is_deleted = 'N'
            ";
        }

        if (isset($filter['refinement'])) {
            $where .= "
                WHERE 
                    bi.r_product_id = :pId:
                    AND bi.is_deleted = 'N'
                    AND bst.status_name <> 'In Sprint'
                    AND bst.status_name <> 'In Sprint - partial'
                    AND bst.status_name <> 'Completed'
                ORDER BY bi.backlog_order ASC
            ";
        } elseif (isset($filter['id'])) {
            $where .= " WHERE bi.backlog_item_id = :pblId: AND bi.is_deleted = 'N' ";
        } else {
            $where .= " WHERE bi.r_product_id = :pid: AND bi.is_deleted = 'N' ";
        }

        $sql = "
            SELECT
                bi.backlog_item_id
                ,bi.r_product_id
                ,bi.backlog_item_name
                ,bi.r_tracker_id
                ,bi.r_module_status_id
                ,bi.r_customer_id
                ,st.tracker
                ,sc.customer_name
                ,bst.status_name
                ,bi.priority
                ,bi.backlog_order
                ,bi.backlog_description
                $select
            FROM orvexa_backlog_item AS bi
            $joins
            INNER JOIN orvexa_customer sc ON sc.customer_id = bi.r_customer_id AND sc.is_deleted = 'N'
            INNER JOIN orvexa_trackers st ON st.tracker_id = bi.r_tracker_id
            INNER JOIN orvexa_module_status bms ON bms.module_status_id = bi.r_module_status_id AND bms.is_deleted = 'N'
            INNER JOIN orvexa_status bst ON bst.status_id = bms.r_status_id AND bst.is_deleted = 'N'
            $where
        ";

        if (isset($filter['refinement'])) {
            $query = $this->db->query($sql, ['pId' => $filter['pid']]);
            if ($query->getNumRows() > 0) {
                return $query->getResultArray();
            }
            return [];
        }

        if (isset($filter['id'])) {
            $query = $this->db->query($sql, ['pblId' => $filter['id']]);
            if ($query->getNumRows() > 0) {
                return $query->getResultArray()[0];
            }
            return [];
        }

        $params = [];

        if (!empty($filter['priorityFilter'])) {
            $data = is_array($filter['priorityFilter']) ? $filter['priorityFilter'] : explode(',', $filter['priorityFilter']);
            $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
            $sql .= " AND bi.priority in($data)";
        }

        if (!empty($filter['statusFilter'])) {
            $data = is_array($filter['statusFilter']) ? $filter['statusFilter'] : explode(',', $filter['statusFilter']);
            $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
            $sql .= " AND bi.r_module_status_id in($data)";
        }

        if (!empty($filter['searchQuery'])) {
            $sql .= ' AND (bi.backlog_item_name LIKE :search:)';
            $params['search'] = $filter['searchQuery'] . '%';
        }

        if (!empty($filter['BtypeFilter'])) {
            $data = is_array($filter['BtypeFilter']) ? $filter['BtypeFilter'] : explode(',', $filter['BtypeFilter']);
            $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
            $sql .= " AND st.tracker_id in($data)";
        }

        if (!empty($filter['custName'])) {
            $data = is_array($filter['custName']) ? $filter['custName'] : explode(',', $filter['custName']);
            $data = implode(',', array_map(fn($item) => $this->db->escape($item), $data));
            $sql .= " AND sc.customer_id in($data)";
        }

        if (!empty($filter["pid"])) {
            $params['pid'] = $filter['pid'];
        }

        $sql .= "
            GROUP BY bi.backlog_item_id
            ORDER BY
                CASE
                    WHEN bst.status_name = 'In Sprint' THEN 0
                    WHEN bst.status_name = 'In Sprint - partial' THEN 0
                    ELSE 1
                END ASC
                ,bi.backlog_order ASC
        ";

        if (!empty($filter['sort'])) {
            $sql .= ',' . $filter['sort'];
        }

        $sql .= ' LIMIT :limit:';
        $params['limit'] = $filter['limit'] ?? 10;

        $sql .= ' OFFSET :offset:';
        $params['offset'] = $filter['offset'] ?? 0;

        $query = $this->db->query($sql, $params);

        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }

        return [];
    }

    public function getProductId($input): int
    {
        $sql = "SELECT
                external_project_id as product_id
                FROM
                orvexa_product
                WHERE
                product_name = :pName:";
        $query = $this->query($sql, [
            'pName' => $input
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getRow()->product_id;
        }
        return 0;
    }
}
