<?php

/**
 * AdminModel.php
 *
 * @category   Model
 * @purpose    To fetch the admin settings data from the database
 * @author     Ruban Edward
 */

namespace App\Models\Admin;
use App\Models\BaseModel;

class SettingsModel extends BaseModel
{
    /**
     * Gets all the product 
     * @return array
     */
    public function getAllProduct(): array
    {
        $sql = "SELECT 
                    external_project_id, 
                    product_name 
                FROM 
                    orvexa_product
                ORDER BY
                    product_name ASC";

        // Execute the query with parameter binding
        $result = $this->db->query($sql);

        // Return the result as an associative array
        return $result->getResultArray();
    }

    public function getAllParentProduct()
    {
        $sql = "SELECT 
                    external_project_id, 
                    product_name 
                FROM 
                    orvexa_product
                WHERE
                    parent_id IS NULL
                ORDER BY
                    product_name ASC";
        $result = $this->db->query($sql);
        return $result->getResultArray();
    }
}