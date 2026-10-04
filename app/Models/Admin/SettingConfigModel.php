<?php

/**
 * SettingConfigModel.php
 *
 * @category   Model
 * @author     Ruban Edward
 * @created    26 July 2024
 * @purpose    To set the admin configation settings    
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class SettingConfigModel extends Model
{
    // Table name for insertion
    protected $table = "orvexa_settings";

    //setting the primary key to insert 
    protected $primaryKey = "settings_id";

    // Defining the fields that are allowed to insert
    protected $allowedFields = [
        "settings_name",
        "settings_value"
    ];

    protected $validationRules = [
        'settings_name' => 'required|min_length[3]|max_length[50]',
        'settings_value' => 'required|integer',
    ];

    protected $validationMessages = [
        'settings_name' => [
            'required' => 'The Settings Name field is required.',
            'min_length' => 'The Settings Name must be at least 3 characters in length.',
            'max_length' => 'The Settings Name cannot exceed 50 characters in length.',
        ],
        'settings_value' => [
            'required' => 'The Settings Value field is required.',
            'max_length' => 'The Settings Value must be a Integer',
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
     * Sets the poker configuration
     * @param array $data
     */
    public function pokerConfig($data)
    {
        $sql = "UPDATE orvexa_settings
                SET
                    settings_value = :settings_value:
                WHERE
                    settings_name = :settings_name:";
        $result = $this->db->query($sql, [
            "settings_value" => $data["settings_value"],
            "settings_name" => $data["settings_name"],
        ]);
        return $result;
    }

    public function getPokerLimit()
    {
        $sql = "SELECT 
                    settings_value 
                FROM 
                    orvexa_settings 
                WHERE 
                    settings_name = :settings_name:";

        $result = $this->db->query($sql, [
            "settings_name" => "poker",
        ]);

        return $result->getResultArray();
    }
}