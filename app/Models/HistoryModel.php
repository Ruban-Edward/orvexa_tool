<?php

namespace App\Models;

use App\Models\BaseModel;


class HistoryModel extends BaseModel
{
    /**
     * @author Abinandhan
     * @param array contains the information to store in the action table
     * Purpose: To store the action log in the user action table
     */
    public function logActions($action)
    {
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO orvexa_user_action(r_user_id,r_action_type_id,r_module_id,r_product_id,reference_id,action_data,action_date,is_deleted)
        VALUES(:r_user_id:,:r_action_type_id:,:r_module_id:,:r_product_id:,:reference_id:,:action_data:,:action_date:,:delete:)";

        $query = $this->query($sql, [
            'r_user_id' => isset($action['r_user_id']) ? $action['r_user_id'] : ($this->getUserId(session('employee_id')))[0],
            'r_action_type_id' => $action["r_action_type_id"],
            'r_module_id' => $action["r_module_id"],
            'reference_id' => $action["reference_id"],
            'r_product_id' => $action['product_id'],
            'action_data' => $action["action_data"],
            'action_date' => $date,
            'delete' => "N"
        ]);
        if ($query) {
            $sql = "UPDATE orvexa_product 
                    SET updated_date = :action_date:
                    WHERE external_project_id = :product_id:";
            $query = $this->query($sql, [
                "action_date" => $date,
                "product_id" => $action['product_id']
            ]);
            if ($query) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}
?>