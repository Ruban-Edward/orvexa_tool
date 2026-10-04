<?php
/**
 * ProductOwnerModel.php
 * @author Ruban Edward
 * 
 * @purpose To handle the product owner operations
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class ProductOwnerModel extends Model
{
    protected $table = "orvexa_product_owners";
    protected $primaryKey = "product_owners_id";

    protected $allowedFields = [
        "r_product_id",
        "r_user_id"
    ];

    protected $validationRules = [
        'r_product_id' => 'required|integer',
        'r_user_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'r_product_id' => [
            'required' => 'The Product Name field is required.',
        ],
        'r_user_id' => [
            'required' => 'The Product Owner field is required.',
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
     * sets the owner to the product
     * @param array $productOwnerData
     * @return bool
     */
    public function setProductOwner($productOwnerData): bool
    {
        $sql = "INSERT INTO orvexa_product_owners (
                    r_product_id, r_user_id, created_date
                ) 
                VALUES 
                    (
                    :r_product_id:, :r_user_id:, NOW()
                    ) ON DUPLICATE KEY 
                UPDATE 
                    r_user_id = :r_user_id:, 
                    updated_date = NOW()";

        $query = $this->db->query($sql, [
            'r_product_id' => $productOwnerData['r_product_id'],
            'r_user_id' => $productOwnerData['r_user_id']
        ]);

        return $query;
    }

    /**
     * Gets the product owner if already owner as been setted
     * @param int $productid
     * @return array
     */
    public function getProductOwner($productid): array
    {
        $sql = "SELECT 
                    r_user_id 
                FROM 
                    orvexa_product_owners 
                WHERE 
                    r_product_id = :r_product_id:";

        $query = $this->db->query($sql, [
            "r_product_id" => $productid
        ]);

        if ($query && $query->getNumRows() > 0) {
            return $query->getResultArray();
        } else {
            return [];
        }
    }
}