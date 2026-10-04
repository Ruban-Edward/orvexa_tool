<?php


namespace App\Models\Backlog;

use CodeIgniter\Model;

use App\Models\BaseModel;

class BacklogItemModel extends BaseModel
{
    // Table for insertion
    protected $table = "orvexa_backlog_item";

    protected $primaryKey = "backlog_item_id";

    protected $allowedFields = [
        "backlog_item_name",
        "r_tracker_id",
        "r_product_id",
        "r_customer_id",
        "r_module_status_id",
        "priority",
        "backlog_order",
        "backlog_t_shirt_size",
        "backlog_description",
        "r_user_id_created",
        "created_date"
    ];

    protected $validationRules = [
        'backlog_item_name' => 'required|min_length[3]|max_length[255]',
        'r_tracker_id' => 'required|integer',
        'r_customer_id' => 'required|integer',
        'r_module_status_id' => 'required|integer',
        'priority' => 'required|in_list[L,M,H]',
        'backlog_description' => 'required|min_length[5]|max_length[1000]'
    ];

    protected $validationMessages = [
        'backlog_item_name' => [
            'required' => 'The backlog item name is required.',
            'min_length' => 'The backlog item name must be at least 3 characters in length.',
            'max_length' => 'The backlog item name cannot exceed 255 characters in length.',
        ],
        'r_tracker_id' => [
            'required' => 'The backlog item type is required.',
            'integer' => 'The backlog item type must be an integer.',
        ],
        'r_customer_id' => [
            'required' => 'The customer ID is required.',
            'integer' => 'The customer ID must be an integer.',
        ],
        'r_module_status_id' => [
            'required' => 'The module status ID is required.',
            'integer' => 'The module status ID must be an integer.',
        ],
        'priority' => [
            'required' => 'The priority is required.',
            'isValidEnum' => 'The priority must be one of: L, M, H.',
        ],
        'backlog_description' => [
            'required' => 'The backlog description is required.',
            'min_length' => 'The backlog description must be at least 5 characters in length.',
            'max_length' => 'The backlog description cannot exceed 1000 characters in length.',
        ]
    ];

    /**
     * Retrieve the validation rules.
     * @return array
     */
    public function getDetailValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * Retrieve the validation messages.
     * @return array
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }



    public function insertData($data)
    {
        $sql = "INSERT INTO orvexa_backlog_item (
                    backlog_item_name,
                    r_tracker_id,
                    r_product_id,
                    r_customer_id,
                    r_module_status_id,
                    priority,
                    backlog_order,
                    backlog_t_shirt_size,
                    backlog_description,
                    r_user_id_created,
                    created_date,
                    r_user_id_updated,
                    updated_date)
                VALUES (:pblName:,
                    :pblType:,
                    :productId:,
                    :customerId:,
                    :statusId:,
                    :priority:,
                    :backlogOrder:,
                    :tShirtSize:,
                    :pblDescription:,
                    :userId:,
                    NOW(),
                    :userId:,
                    NOW())";

        $query = $this->db->query($sql, [
            'pblName' => $data['backlog_item_name'],
            'pblType' => $data['r_tracker_id'],
            'productId' => $data['r_product_id'],
            'customerId' => $data['r_customer_id'],
            'statusId' => $data['r_module_status_id'],
            'priority' => $data['priority'],
            'backlogOrder' => $data['backlog_order'],
            'tShirtSize' => $data['backlog_t_shirt_size'],
            'pblDescription' => $data['backlog_description'],
            'userId' => $data['r_user_id_created']
        ]);
        if ($query) {
            return $this->db->insertID();
        }
        return false;
    }


    public function updatebacklogById($data)
    {
        $sql = "UPDATE 
                    orvexa_backlog_item 
                SET 
                    backlog_item_name = :backlog_item_name:, 
                    r_tracker_id = :tracker_id:, 
                    r_customer_id = :customer_id:, 
                    r_module_status_id = :module_status:, 
                    priority = :priority:, 
                    backlog_description = :backlog_description: ,
                    backlog_t_shirt_size = :t_shirt_size:
                WHERE 
                    backlog_item_id = :backlog_item_id:";

        $query = $this->query($sql, [
            'backlog_item_id' => $data['backlog_item_id'],
            'backlog_item_name' => $data['backlog_item_name'],
            'tracker_id' => $data['r_tracker_id'],
            'customer_id' => $data['r_customer_id'],
            'module_status' => $data['r_module_status_id'],
            'priority' => $data['priority'],
            't_shirt_size' => $data['backlog_t_shirt_size'],
            'backlog_description' => $data['backlog_description']
        ]);

        if ($query) {
            return 1;
        }
    }


    public function getLastPriorityOrder($pid)
    {
        $sql = "SELECT backlog_item_id, 
                       backlog_order
                FROM 
                    orvexa_backlog_item
                WHERE backlog_item_id = (
                    SELECT MAX(backlog_item_id)
                    FROM orvexa_backlog_item
                    WHERE is_deleted = 'N' AND r_product_id = :pid:
                ) AND is_deleted = 'N' AND r_product_id = :pid:;";
        $query = $this->query($sql, ["pid" => $pid]);
        if ($query) {
            return $query->getResultArray();
        }
        return 0;
    }

    /**
     * @author Murugadass
     * @return array
     * @param int id of a particular backlog item
     * Purpose: This function is used to return the details of a particular backlog item  
     */
    public function getBacklogItemById($id): array
    {
        $sql = "SELECT
                    bi.backlog_item_id,
                    bi.r_product_id,
                    bi.backlog_item_name,
                    bi.r_tracker_id,
                    bi.r_module_status_id,
                    bi.r_customer_id,
                    bi.backlog_t_shirt_size,
                    tss.t_size_values,
                    st.tracker,
                    p.product_name, 
                    sc.customer_name,
                    bst.status_name,
                    bi.priority,
                    bi.backlog_order,
                    bi.backlog_description,
                    COUNT(CASE WHEN us.r_module_status_id = 34 THEN 1 END) AS completed_user_stories,
                    COUNT(DISTINCT us.user_story_id) AS total_user_stories,
                    COUNT(t.task_id) AS total_tasks,
                    COUNT(CASE WHEN t.task_status = 7 THEN 1 END) AS completed_tasks
                FROM orvexa_backlog_item AS bi
                LEFT JOIN orvexa_epic e ON e.r_backlog_item_id = bi.backlog_item_id AND e.is_deleted = 'N'
                LEFT JOIN orvexa_user_story us ON us.r_epic_id = e.epic_id AND us.is_deleted = 'N'
                LEFT JOIN orvexa_task t ON t.r_user_story_id = us.user_story_id AND t.is_deleted = 'N'
                LEFT JOIN orvexa_product p ON p.external_project_id = bi.r_product_id AND p.is_deleted = 'N'
                INNER JOIN orvexa_t_shirt_size tss ON tss.t_shirt_size_id = bi.backlog_t_shirt_size
                INNER JOIN orvexa_customer sc ON sc.customer_id = bi.r_customer_id 
                INNER JOIN orvexa_trackers st ON st.tracker_id = bi.r_tracker_id
                INNER JOIN orvexa_module_status bms ON bms.module_status_id = bi.r_module_status_id AND bms.is_deleted = 'N'
                INNER JOIN orvexa_status bst ON bst.status_id = bms.r_status_id AND bst.is_deleted = 'N'
                WHERE bi.backlog_item_id = :pblId: AND bi.is_deleted = 'N'";

        $query = $this->db->query($sql, ['pblId' => $id]);

        if ($query->getNumRows() > 0) {
            return $query->getResultArray()[0];
        }
        return [];
    }

    /**
     * @author Murugadass
     * @return array
     * Purpose: This function is used to return the details of all customer details backlogitems page 
     */
    public function getBacklogItemCustomer(): array
    {
        $sql = "SELECT 
                    customer_id,
                    customer_name
                FROM 
                    orvexa_customer";
        $query = $this->query($sql);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Abinandhan
     * @return array
     * Purpose: This function is used to return the details of backlog item with required filter in the backlogitems page 
     */
    public function getBacklogItems($filter, $refinement = null): array
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
                    bi.backlog_description,
                    COUNT(CASE WHEN ust.status_name = 'New Requirement' THEN us.user_story_id END) AS new,
                    COUNT(CASE WHEN ust.status_name = 'Ready for Brainstorming' THEN us.user_story_id END) AS ready_for_brainstorming,
                    COUNT(CASE WHEN ust.status_name = 'In brainstorming' THEN us.user_story_id END) AS in_brainstorming,
                    COUNT(CASE WHEN ust.status_name = 'Completed' THEN us.user_story_id END) AS completed,
                    COUNT(CASE WHEN ust.status_name = 'In Sprint' THEN us.user_story_id END) AS in_sprint,
                    COUNT(CASE WHEN ust.status_name = 'Ready for Sprint' THEN us.user_story_id END) AS ready_for_sprint,
                    COUNT(CASE WHEN ust.status_name = 'Brainstorming completed' THEN us.user_story_id END) AS brainstorming_completed,
                    COUNT(us.user_story_id) AS total
                    FROM orvexa_backlog_item AS bi
                    LEFT JOIN orvexa_epic e ON e.r_backlog_item_id = bi.backlog_item_id AND e.is_deleted = 'N'
                    LEFT JOIN orvexa_user_story us ON us.r_epic_id = e.epic_id AND us.is_deleted = 'N'
                    INNER JOIN orvexa_customer sc ON sc.customer_id = bi.r_customer_id AND sc.is_deleted = 'N'
                    INNER JOIN orvexa_trackers st ON st.tracker_id = bi.r_tracker_id
                    INNER JOIN orvexa_module_status bms ON bms.module_status_id = bi.r_module_status_id AND bms.is_deleted = 'N'
                    LEFT JOIN orvexa_module_status ums ON ums.module_status_id = us.r_module_status_id AND ums.is_deleted = 'N'
                    INNER JOIN orvexa_status bst ON bst.status_id = bms.r_status_id AND bst.is_deleted = 'N'
                    LEFT JOIN orvexa_status ust ON ust.status_id = ums.r_status_id AND ust.is_deleted = 'N'
                    WHERE bi.r_product_id = :pid: AND bi.is_deleted = 'N' ";

        $params = [];

        if ($refinement) {
            $sql .= " AND 
            bst.status_name <> 'In Sprint' AND 
            bst.status_name <> 'completed'
                GROUP BY bi.backlog_item_id
                ORDER BY
                    bi.backlog_order ASC;";
        } else {
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
                $sql .= " AND (bi.backlog_item_name LIKE :search:)";
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

            $sql .= "GROUP BY bi.backlog_item_id
                    ORDER BY
                    CASE
                        WHEN bst.status_name = 'in sprint' THEN 0
                    ELSE 1
                    END ASC ,bi.backlog_order asc";

            if (!empty($filter['sort'])) {
                $sql .= ",";
                $data = $filter['sort'];
                $sql .= $data;
            }

            if (!empty($filter['limit'])) {
                $sql .= " limit :limit:";
                $params['limit'] = $filter['limit'];

            } else {
                $sql .= " limit :limit:";
                $params['limit'] = 10;

            }
            if (!empty($filter['offset'])) {
                $sql .= " offset :offset:";
                $params['offset'] = $filter['offset'];
            } else {
                $sql .= " offset :offset:";
                $params['offset'] = 0;

            }

        }
        $params['pid'] = $filter['pid'];
        $query = $this->db->query($sql, $params);

        if ($query->getNumRows() > 0) {

            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Murugadass
     * @param int id refers the actual id of a item, array input contains the table name and the primary key which will be soft deleted
     */
    public function deleteItem($id, $input)
    {
        $table = $this->db->escapeIdentifiers($input[0]);
        $key = $this->db->escapeIdentifiers($input[1]);

        $sql = "UPDATE {$table}
                    SET is_deleted = 'Y'
                    WHERE {$key} = :id:";
        $res = $this->query($sql, [
            'id' => $id
        ]);

        return $res;
    }


    /**
     * Summary of checkBacklogItem
     * @return bool
     * Purpose: Used to check the backlog item is actually present or not for the given product id
     */
    public function checkBacklogItem($productId, $backlogItemId): bool
    {
        $sql = "SELECT 
                    COUNT(*) AS count 
                FROM 
                    orvexa_backlog_item
                WHERE 
                    r_product_id = ? AND 
                    backlog_item_id = ? AND 
                    is_deleted = 'N'";
        $query = $this->db->query($sql, [$productId, $backlogItemId]);
        $result = $query->getRow(); // get the first row of the result
        return $result->count > 0;
    }


    public function backlogStatus($statusId, $id): bool
    {
        $sql = "UPDATE 
                    orvexa_backlog_item bi
                SET 
                    bi.r_module_status_id = :status_id:
                WHERE
                    bi.backlog_item_id = :id:";

        $result = $this->query($sql, [
            'id' => $id,
            'status_id' => $statusId
        ]);
        return $result;
    }

    public function getTshirtSizes($id)
    {
        $sql = "SELECT t_shirt_size_id,t_size_name,t_size_values
                FROM 
                            orvexa_t_shirt_size ts
                JOIN orvexa_product p on p.parent_id = ts.r_product_id
                WHERE p.external_project_id = :pid:
                ORDER BY 
                    CAST(SUBSTRING_INDEX(t_size_values, ' ', 1) AS UNSIGNED);";
        $query = $this->query($sql, [
            'pid' => $id
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        } else {
            $sql = "SELECT t_shirt_size_id,t_size_name,t_size_values
                FROM 
                            orvexa_t_shirt_size ts
                WHERE ts.r_product_id = 0";
            $query = $this->query($sql, [
                'pid' => $id
            ]);
            if (count($query->getResultArray()) > 0) {
                return $query->getResultArray();
            }

        }
        return [];
    }

    public function changeBacklogStatus($before, $after, $pblId)
    {
        $sql = "UPDATE 
                    orvexa_backlog_item
                SET 
                    r_module_status_id = :after:
                WHERE 
                    backlog_item_id = :pblId: AND 
                    r_module_status_id = :before:";
        $res = $this->query($sql, [
            'after' => $after,
            'before' => $before,
            'pblId' => $pblId
        ]);
        return $res;
    }

    public function getBacklogItemstotal($pId)
    {
        $sql = "SELECT
                count(backlog_item_id) AS count
                FROM
                orvexa_backlog_item
                WHERE is_deleted='N' AND
                r_product_id=:id:";
        $query = $this->query($sql, ["id" => $pId]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }
}
