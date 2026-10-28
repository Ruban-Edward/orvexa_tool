<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\HTTP\Response;
use Config\SprintModelConfig;


class NotificationController extends BaseController
{
    public function notificationDetails(){
        $dashboardModel = model(\App\Models\Dashboard\DashboardModel::class);
        $notificationModel = model(NotificationModel::class);
        $userId  = (int) session()->get('employee_id');
        $backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $products = $backlogModel->getUserProduct($userId);
        $productId = count($products) == 0 ? [0] : array_column($products,'product_id');
        $meetings = $dashboardModel->userMeetings($userId);
        $configObject = new SprintModelConfig();
        $sprintStatus = [
            'sprintPlannedStatus' => $configObject->sprintPlannedStatus,
            'sprintStatuses' => $configObject->sprintStatuses['upcoming'],
            'product_id' => $productId
        ];
        $upomingSprints = $dashboardModel->getAllUpcomingSprints($sprintStatus);

        // Housekeeping on roughly 1 in 50 loads keeps old, read items from piling up
        if (random_int(1, 50) === 1) {
            $notificationModel->purgeOld();
        }

        $response = [
            'meetings' => $meetings,
            'sprints' => $upomingSprints,
            'activity' => $notificationModel->getRecent($userId),
            'unread' => $notificationModel->countUnread($userId),
        ];
        return $this->response->setJSON($response);
    }

    /**
     * Lightweight poll for the header badge.
     */
    public function unreadCount(): Response
    {
        $count = model(NotificationModel::class)->countUnread((int) session()->get('employee_id'));

        return $this->response->setJSON(['unread' => $count]);
    }

    /**
     * Mark one or more notifications as read; with no ids, marks all of them.
     */
    public function markRead(): Response
    {
        $ids = array_filter(array_map('intval', (array) ($this->request->getPost('ids') ?? [])));
        $model = model(NotificationModel::class);
        $userId = (int) session()->get('employee_id');
        $model->markRead($userId, $ids);

        return $this->response->setJSON(['success' => true, 'unread' => $model->countUnread($userId)]);
    }
}
