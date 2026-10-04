<?php

/**
 * MeetingTeamMemberModel.php
 *
 * @category   Model
 * @author     Hari Sankar R, Ruban Edward
 * @created   
 * @purpose    To insert the holidays details into orvexa_holidays table       
 */

namespace App\Models\Meeting;

use CodeIgniter\Model;

class HolidaysModel extends Model
{
    // Table name for insertion
    protected $table = "orvexa_holidays";

    //setting the primary key to insert 
    protected $primaryKey = "holiday_id";

    // Defining the fields that are allowed to insert
    protected $allowedFields = [
        "holiday_title",
        "holiday_start_date",
        "holiday_end_date"
    ];

    protected $validationRules = [
        'holiday_title' => 'required|min_length[3]|max_length[255]',
        'holiday_start_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]', // YYYY-MM-DD format
        'holiday_end_date' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]', // YYYY-MM-DD format
    ];

    protected $validationMessages = [
        "holiday_title" => [
            "required" => "The holiday title is required.",
            "min_length" => "The holiday title must be at least 3 characters long.",
            "max_length" => "The holiday title cannot exceed 255 characters."
        ],
        "holiday_start_date" => [
            "required" => "The holiday date is required.",
            "valid_date" => "The holiday start date must be in the format Y-m-d."
        ],
        "holiday_end_date" => [
            "required" => "The holiday date is required.",
            "valid_date" => "The holiday end date must be in the format Y-m-d."
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

    public function insertHolidayDetails($data)
    {
        $sql = "INSERT INTO orvexa_holidays(
            holiday_start_date,holiday_title,
            created_date
            )
            VALUES
            (
                :holiday_start_date:,:holiday_title:,
                NOW()
            )";
        $result = $this->db->query($sql, [
            'holiday_start_date' => $data['holiday_start_date'],
            'holiday_title' => $data['holiday_title']
        ]);
        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * get the holiday dates to display in the calendar page view
     * @return mixed
     */
    public function getHolidayDetails()
    {
        $sql = "SELECT
                    holiday_id,
                    holiday_start_date,
                    holiday_title
                FROM
                    orvexa_holidays
                    ";
        $result = $this->query($sql);
        return $result->getResultArray(); //returns result as an array format
    }

    /**
     * checks the given date is a holiday or not
     */
    public function isHoliday($date)
    {
        $sql = "SELECT 
                    holiday_start_date 
                FROM 
                    orvexa_holidays 
                WHERE 
                    holiday_start_date = :holiday_date:";

        $result = $this->query($sql, [
            "holiday_date" => $date
        ]);

        return $result->getResultArray();
    }

    /**
     * Inserting the holidays in the table
     * @param array $holidayData
     * @return bool
     */
    public function insertHoliday($holidayData)
    {
        $sql = "INSERT INTO orvexa_holidays(
                    holiday_title, holiday_start_date, 
                    holiday_end_date
                ) 
                VALUES(
                    :holiday_title:,:holiday_start_date:,
                    :holiday_end_date:
                )";

        $result = $this->query($sql, [
            "holiday_title" => $holidayData["holiday_title"],
            "holiday_start_date" => $holidayData["holiday_start_date"],
            "holiday_end_date" => $holidayData["holiday_end_date"]
        ]);

        return $result;
    }

    public function insertBatchHoliday($data)
    {
        $result = $this->db->table('orvexa_holidays')->insertBatch($data);
        return $result;
    }
}