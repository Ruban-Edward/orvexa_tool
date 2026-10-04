<?php

namespace App\Models\Backlog;
use App\Models\BaseModel;

use CodeIgniter\Model;

class EpicModel extends BaseModel {
    // Table for insertion
    protected $table = "orvexa_epic";

    protected $primaryKey = "epic_id";

    protected $allowedFields = [
        'r_backlog_item_id',
        'epic_description'
    ];

    protected $validationRules = [
        'epic_description' => 'required|min_length[5]'
    ];    

    
    protected $validationMessages = [
        'epic_description' => [
            'required' => 'The epic_description is required.',
            'min_length' => 'The epic_description must be at least 5 characters in length.',
            'max_length' => 'The epic_description cannot exceed 500 characters in length.'
        ]

    ];
    /**
     * Retrieve the validation rules.
     * @return array
     */
    public function getEpicValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * Retrieve the validation messages.
     * @return array
     */
    public function getEpicValidationMessages(): array
    {
        return $this->validationMessages;
    }


    /**
     * Summary of insertData
     * @param array $data
     * @return int 
     * Purpose: Used to return the id in which the epic is created
     */
    public function insertData($data):int
    {
        $sql = "INSERT INTO orvexa_epic 
                (r_backlog_item_id, epic_name, epic_description, r_user_id_created, r_user_id_updated, created_date, updated_date)
                VALUES (:pbl_id:,'epicname',:descripition:,:user_id:,:user_id:,NOW(),NOW())";
        
        $query = $this->db->query($sql, [
            'pbl_id' => $data['pbl_id'],
            'descripition'=>$data['epic_description'],
            'user_id' =>  session('employee_id'),
        ]);
        
        if($query){
            return $this->insertID();
        } else {
            return 0;
        }
    }

    /**
     * Summary of getOrCreateEpicId
     * @param mixed $description
     * @param mixed $id
     * @return mixed
     */
    public function getOrCreateEpicId($description,$id)
    {
        // First, try to select the existing epic
        $selectSql = "SELECT epic_id FROM orvexa_epic WHERE epic_description = :description: AND r_backlog_item_id = :pblID:";
        $query = $this->query($selectSql, [
            'description' => $description,
            'pblID' => $id]);
        
        $result = $query->getRow();
        
        if ($result) {
            // Epic exists, return its ID
            return $result->epic_id;
        } else {
            // Epic doesn't exist, insert it
            $insertQuery = $this->insertData(['epic_description'=>$description,'pbl_id'=>$id]);
            
            if ($insertQuery) {
                // Return the ID of the newly inserted epic
                return $this->insertID();
            } else {
                // Insert failed
                return 0;
            }
        }
    }

    /**
     * @author Murugadass
     * @return array
     * @param int id pf a backlog item
     * Purpose: This function is used to fetch the epics of a backlog item
     */
    public function getEpic($backlogItemId): array
    {
        $sql = "SELECT 
                    epic_id,
                    epic_name,
                    epic_description
                FROM 
                    orvexa_epic 
                WHERE 
                    r_backlog_item_id = :pblId: AND 
                    is_deleted = 'N'";
        $query = $this->query($sql, [
            "pblId" => $backlogItemId
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }

    /**
     * @author Murugadass
     * return array
     * Purpose: Used to return the epics which are in the status ready for sprint
     */

    public function epicByBrainstrom($backlogItemId): array
    {
        $sql = "SELECT DISTINCT 
                    (e.epic_id),
                    e.epic_description
                FROM 
                    orvexa_epic as e
                INNER JOIN 
                    orvexa_user_story AS us ON us.r_epic_id = e.epic_id
                WHERE 
                    e.r_backlog_item_id = :pblId: AND 
                    e.is_deleted = 'N' AND 
                    us.r_module_status_id = ".READY_FOR_BRAINSTORMING;
        $query = $this->query($sql, [
            "pblId" => $backlogItemId
        ]);
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }
}
