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
    public function getAllCustomer(): array
    {
        return $this->db->table('orvexa_customer')
            ->orderBy('customer_name', 'ASC')
            ->get()->getResultArray();
    }

    public function toggleCustomer(int $customerId): bool
    {
        $customer = $this->db->table('orvexa_customer')->select('is_deleted')->where('customer_id', $customerId)->get()->getRowArray();
        if (!$customer) return false;
        return $this->db->table('orvexa_customer')->where('customer_id', $customerId)->update([
            'is_deleted' => $customer['is_deleted'] === 'N' ? 'Y' : 'N',
            'updated_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function customerExists(string $customerName): bool
    {
        $query = $this->db->query(
            'SELECT 1 FROM orvexa_customer WHERE LOWER(customer_name) = LOWER(?) AND is_deleted = ?',
            [$customerName, 'N']
        );
        return $query->getNumRows() > 0;
    }

    public function addCustomer(string $customerName): bool
    {
        return $this->db->table('orvexa_customer')->insert([
            'customer_name' => $customerName,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'is_deleted' => 'N',
        ]);
    }
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
