<?php

namespace App\Jobs;

use App\Commands\MailRemainder;
use App\Models\RemainderJobModel;
use DateInterval;
use DateTime;
use Exception;
use App\Services\EmailService;

class EmailRemainder
{
    protected $custom;
    protected $emailService;
    protected $remainderModel;
    public function process()
    {
        try {
            $db = \Config\Database::connect($this->custom);

            $currentDateTime = new DateTime();
            $currentDate = $currentDateTime->format('Y-m-d');
            $currentTime = $currentDateTime->format('H:i:s');


            $next15Minutes = $currentDateTime->add(new DateInterval('PT15M'))->format('H:i:s');

            $sql = "SELECT 
                        DISTINCT (members.r_meeting_details_id) AS meet_id, 
                        user.email_id, 
                        product.product_name, 
                        details.meeting_title, 
                        details.meeting_description, 
                        details.meeting_start_date, 
                        details.meeting_start_time, 
                        details.meeting_end_time, 
                        details.meeting_link, 
                        meetType.meeting_type_name 
                    FROM 
                        orvexa_meeting_members AS members 
                        INNER JOIN orvexa_meeting_details AS details ON members.r_meeting_details_id = details.meeting_details_id 
                        INNER JOIN orvexa_user AS user ON members.r_user_id = user.external_employee_id 
                        INNER JOIN orvexa_product AS product ON details.r_product_id = product.product_id 
                        INNER JOIN orvexa_meeting_type AS meetType ON details.r_meeting_type_id = meetType.meeting_type_id 
                    WHERE 
                        details.meeting_start_date = '$currentDate'
                        AND details.meeting_start_time >= '$currentTime' 
                        AND details.meeting_start_time <= '$next15Minutes'";
            $conn = $db->query($sql);
            $data = $conn->getResultArray();

            if(!empty($data)){
                foreach ($data as $value) {
                    $body = [
                        'email_id' => $value['email_id'],
                        'fileName' => 'mailTemplate',
                        'contents' => [
                            'subject' => 'Meeting Starts at ' . $value['meeting_start_time'],
                            'Meeting_heading' => 'Meeting Remainder',
                            'meeting_title' =>  $value['meeting_title'],
                            'meeting_type' =>  $value['meeting_type_name'],
                            'host_name' => session()->get('first_name'),
                            'product' =>  $value['product_name'],
                            'meeting_description' =>  $value['meeting_description'],
                            'meeting_start_date' =>  $value['meeting_start_date'],
                            'meeting_start_time' =>  $value['meeting_start_time'],
                            'meeting_end_time' =>  $value['meeting_end_time'],
                            'meeting_link' => $value['meeting_link']
                        ]
                    ];
                    
                    $this->emailService = new EmailService();
                    $this->remainderModel = new RemainderJobModel();

                    $this->emailService->asynMail($body);
                    $this->remainderModel->insertRemainderJob($body);
                }
                return "Mail Sended Successfully";
            }
            else{
                return "No Meeting Available";
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}