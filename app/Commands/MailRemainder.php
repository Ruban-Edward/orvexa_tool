<?php

namespace App\Commands;

use App\Services\EmailRemainder;
use CodeIgniter\CLI\BaseCommand;

class MailRemainder extends BaseCommand
{

    protected $group = 'Tasks';
    protected $name = 'tasks:sync';
    protected $description = 'Remainder Mail Sending...';

    public function run(array $params)
    {
        $mailRemainder = new EmailRemainder();
        $mailResult = $mailRemainder->remainderMail();

        if ($mailResult) {
            $result = "Reminder mail sent successfully";
        } else {
            $result = "Something error";
        }
        echo $result . " and " . $mailResult;
    }

}
