<?php

/**
 * RemainderJobModel.php
 *
 * @author    Gokul
 * @category  Model
 * @created   05 August 2024
 * @purpose  This Model is for handling database for  all the mailRemainder jobs.
 */

namespace App\Models;

use CodeIgniter\Model;

class RemainderJobModel extends Model
{
    /**
     * This function is used to insert the mailRemainder jobs.
     */
    public function insertRemainderJob($data)
    {
        $sql = "INSERT INTO
                    orvexa_remainder_jobs
                    (
                        email_id,
                        remainder_date_time
                    )
                VALUE
                    (
                        :email_id:,
                        NOW()
                    )";

        $this->db->query($sql, [
            'email_id' => $data['email_id']]);
    }

    /**
     * This function is used to delete the data from current day to before 2 days.
     */
    public function deleteJob()
    {
        $sql = "DELETE FROM
                    orvexa_remainder_jobs
                WHERE
                    remainder_date_time < NOW() - INTERVAL 2 DAY";
        $this->db->query($sql);
    }

}
