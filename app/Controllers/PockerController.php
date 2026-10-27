<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
class PockerController extends BaseController
{
    protected $pokerModel;
    public function __construct()
    {
        $this->pokerModel = model(\App\Models\Backlog\BacklogPoker::class);
    }

    
    /**
     * @author vishva,stervin richard
     * @return Response
     * @purpose  This function is used to insert fibonacci
     */

     public function insertPokerPlanning():Response
     {
         $inputData = $this->request->getPost();
         $data = [
             'r_user_story_id' => $inputData['fibonacciUserStoryId'],
             'r_user_id' => session('employee_id'),
             'card_points' => $inputData['fibonacciNumber'],
             'reason' => $inputData['poker_description'],
             'added_date' => date('Y-m-d H:i:s'),
         ];
         $result = $this->pokerModel->savePoker($data);
 
         if ($result) {
             return $this->response->setJSON(['success' => true, 'message' => 'Poker planning added successfully']);
         } else {
             return $this->response->setJSON(['success' => false, 'message' => 'Poker planning not added successfully']);
         }
     }
 
     /**
      * @author vishva,stervin richard
      * @return Response
      * @purpose  This function is used to getpoker planning
      */
 
     public function getPokerPlanning():Response
     {
         // jQuery omits an empty array from the POST body, so no stories arrives as null
         $userStories = (array) ($this->request->getPost('userStory') ?? []);
         $sendResult = [];
         // $productId = $this->request->getPost('product_id');
         $userId = session('employee_id');
         $conditionCheck = has_permission('backlog/revealPoker') != null ? true : false;
 
         foreach ($userStories as $key1 => $userStoryId) {
             $pokerReveal = $this->pokerModel->getPokerReveal($userStoryId);
             if ($conditionCheck) {
                 $sendResult[$key1] = $this->pokerModel->getPokerPlan($userStoryId);
                 // return $this->response->setJSON(['success' => true, 'data' => [$resultOverall]]);
             } else {
                 $result = $this->pokerModel->getPokerPlan($userStoryId, $userId);
                 if ((count($result) > 0 && $conditionCheck) || (count($pokerReveal) > 0 ? ($pokerReveal[0]['reveal'] == 'Y' ? true : false) : false)) {
                     $resultOverall = $this->pokerModel->getPokerPlan($userStoryId);
                     $sendResult[$key1] = $resultOverall;
                     // return $this->response->setJSON(['success' => true, 'data' => $resultOverall]);
                 } else {
                     $sendResult[$key1] = $result;
                     // return $this->response->setJSON(['success' => true, 'data' => $result]);
                 }
             }
         }
         return $this->response->setJSON(['success' => true, 'data' => $sendResult]);
     }
 
         /**
      * @author vishva,stervin richard
      * @return Response
      * @purpose  This function is used to update poker
      */
 
     public function updatePokerReveal():Response
     {
         $userStoryId = $this->request->getPost('userStory');
         $result = $this->pokerModel->updatePokerRevealStatus($userStoryId);
         if ($result) {
             return $this->response->setJSON(['success' => true, 'message' => $result]);
         } else {
             return $this->response->setJSON(['success' => false, 'message' => $result]);
         }
     }
}

