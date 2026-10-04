<?php

/**
 * TShirtSizeModel.php
 *
 * @category   Model
 * @author     Ruban Edward
 * @created    26 July 2024
 * @purpose    To set the t size size for each product   
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class TShirtSizeModel extends Model
{
    protected $table = "orvexa_t_shirt_size";
    protected $primaryKey = "t_shirt_size_id";

    protected $allowedFields = [
        "r_product_id",
        "t_size_name",
        "t_size_values"
    ];

    protected $validationRules = [
        'r_product_id' => 'required|integer',
        't_size_name' => 'required',
        't_size_values' => 'required'
    ];

    protected $validationMessages = [
        'r_product_id' => [
            'required' => 'The Product Name field is required.',
        ],
        't_size_name' => [
            'required' => 'The t-shirt size name field is required.',
        ],
        't_size_values' => [
            'required' => 'The t-shirt size values field is required.',
        ],
    ];

    public function getDetailValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * insert the t shirt size for the products
     * @param array 
     * @return bool
     */
    public function insertTShirtSize($data): bool
    {
        $result = $this->db->table('orvexa_t_shirt_size')->insertBatch($data);
        return true;
    }

    /**
     * get the array of t-shirt size name and value if a product has already has
     * @param int $productId
     * @return array
     */
    public function getTShirtSize($productId): array
    {
        $sql = "SELECT 
                    r_product_id,
                    t_size_name, 
                    t_size_values 
                FROM 
                    orvexa_t_shirt_size 
                WHERE 
                    r_product_id = :r_product_id:
                ORDER BY 
                    CAST(SUBSTRING_INDEX(t_size_values, ' ', 1) AS UNSIGNED);";

        $result = $this->db->query($sql, [
            "r_product_id" => $productId
        ]);

        return $result->getResultArray();
    }

    /**
     * removes the t-shirt size if not needed in future
     * @param array $data
     * @return bool
     */
    public function deleteTShirtSize($data): bool
    {
        $tSizeNames = array_column($data, 't_size_name');
        $result = $this->db->table('orvexa_t_shirt_size')->whereIn('t_size_name', $tSizeNames)->delete();
        return true;
    }
}