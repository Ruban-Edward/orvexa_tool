<?php
namespace App\Controllers;

class ScrumController extends BaseController
{
    public function getProjects()
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'External external integration is disabled.',
        ]);
    }  

    /**
     * Sample mail 
     */
    public function mail()
    {
        $email = service('email');
        // $email->setFrom('');
        $email->setTo('ramaselvan161@gmail.com,rubanedward769@gmail.com');
        // $email->setCC('another@another-example.com');
        // $email->setBCC('them@their-example.com');

        $email->setSubject('Email Test');
        $email->setMessage('dummy mail to check whether it works');

        $dd = $email->send();

        if($dd == TRUE){
            echo "Successfull";
        }
        else{
            echo "Mail not sent";
        }

    }
}