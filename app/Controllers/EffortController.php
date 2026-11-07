<?php

/**
 * EffortController.php
 *
 * @category   Controller
 * @purpose    "Log efforts": the signed-in user's spent time, grouped by day, and the form to log
 *             daily effort against a task (optional) with Activity, Main activity and Log type.
 */

namespace App\Controllers;

use App\Models\EffortLogModel;
use App\Models\EffortMasterModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\SprintModelConfig;

class EffortController extends BaseController
{
    private const MAX_RANGE_DAYS = 366;

    public function index(): string
    {
        $userId = (int) session()->get('employee_id');
        $today  = date('Y-m-d');
        $to     = $this->validDate($this->request->getGet('to')) ?? $today;
        $from   = $this->validDate($this->request->getGet('from')) ?? date('Y-m-d', strtotime($to . ' -29 days'));
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        if ((strtotime($to) - strtotime($from)) / 86400 >= self::MAX_RANGE_DAYS) {
            $from = date('Y-m-d', strtotime($to . ' -' . (self::MAX_RANGE_DAYS - 1) . ' days'));
        }

        $effortModel = model(EffortLogModel::class);
        $efforts = $effortModel->getUserEfforts($userId, $from, $to);

        $days  = [];
        $total = 0.0;
        foreach ($efforts as $effort) {
            $day = $effort['spent_on'];
            $days[$day]['efforts'][] = $effort;
            $days[$day]['total'] = ($days[$day]['total'] ?? 0) + (float) $effort['hours'];
            $total += (float) $effort['hours'];
        }

        return $this->template_view('effort/logEfforts', [
            'days'     => $days,
            'total'    => $total,
            'from'     => $from,
            'to'       => $to,
            'today'    => $today,
            'userId'   => $userId,
            'userName' => (string) session()->get('first_name'),
            'tasks'    => $effortModel->getLoggableTasks($userId, (new SprintModelConfig())->pendingTaskStatuses),
            'masters'  => model(EffortMasterModel::class)->getActiveGrouped(),
        ], 'Log efforts', ['Log efforts' => '']);
    }

    /** Creates an effort, or updates one of the user's efforts when effort_id is posted. */
    public function save(): ResponseInterface
    {
        $userId = (int) session()->get('employee_id');
        $effortModel = model(EffortLogModel::class);
        $existing = null;
        $effortId = $this->request->getPost('effort_id');
        if ($effortId !== null && $effortId !== '') {
            $effortId = filter_var($effortId, FILTER_VALIDATE_INT);
            $existing = $effortId === false ? null : $effortModel->getOwnEffort($effortId, $userId);
            if (! $existing) {
                return $this->fail('This effort could not be found.');
            }
        }

        $spentOn = $this->validDate($this->request->getPost('spent_on'));
        if ($spentOn === null || $spentOn > date('Y-m-d')) {
            return $this->fail('Enter a valid date that is not in the future.');
        }

        $hours = $this->parseHours($this->request->getPost('hours'));
        if ($hours === null) {
            return $this->fail('Enter hours as h:mm or a number, more than 0 and no more than 24.');
        }

        $comments = $this->request->getPost('comments') ?? '';
        if (! is_string($comments)) {
            return $this->fail('Comments must be plain text.');
        }
        $comments = trim($comments);
        if (mb_strlen($comments) > 1000) {
            return $this->fail('Comments cannot exceed 1000 characters.');
        }

        $taskId = $this->request->getPost('task_id');
        $taskId = ($taskId === null || $taskId === '') ? null : $taskId;
        if ($existing && $existing['r_meeting_details_id'] !== null) {
            $taskId = null; // meeting efforts stay linked to their meeting
        } elseif ($existing && $taskId !== null && (int) $taskId === (int) $existing['r_task_id']) {
            $taskId = (int) $taskId; // unchanged task: keep it even if it is no longer assigned to the user
        } elseif ($taskId !== null) {
            if ($denied = $this->denyUnlessOwns([['task', $taskId]])) {
                return $denied;
            }
            $taskId = (int) $taskId;
        }

        $masters = model(EffortMasterModel::class);
        $ids = [];
        foreach (['activity' => 'activity_id', 'main_activity' => 'main_activity_id', 'log_type' => 'log_type_id'] as $type => $field) {
            $id = filter_var($this->request->getPost($field), FILTER_VALIDATE_INT);
            if ($id === false || ! $masters->isActive($id, $type)) {
                return $this->fail('Choose a valid ' . strtolower(EffortMasterModel::TYPES[$type]) . '.');
            }
            $ids[$type] = $id;
        }

        if ($existing) {
            $updated = $effortModel->updateOwnEffort((int) $existing['effort_log_id'], $userId, [
                'r_task_id'          => $taskId,
                'r_activity_id'      => $ids['activity'],
                'r_main_activity_id' => $ids['main_activity'],
                'r_log_type_id'      => $ids['log_type'],
                'spent_on'           => $spentOn,
                'hours'              => $hours,
                'comments'           => $comments !== '' ? $comments : null,
            ]);
            if (! $updated) {
                log_message('error', 'Failed to update effort ' . (int) $existing['effort_log_id'] . '.');

                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Effort could not be updated. Please try again.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Effort updated.']);
        }

        $saved = $effortModel->insertEfforts([[
            'r_task_id'            => $taskId,
            'r_meeting_details_id' => null,
            'r_user_id'            => $userId,
            'r_logged_by'          => $userId,
            'r_activity_id'        => $ids['activity'],
            'r_main_activity_id'   => $ids['main_activity'],
            'r_log_type_id'        => $ids['log_type'],
            'spent_on'             => $spentOn,
            'hours'                => $hours,
            'comments'             => $comments !== '' ? $comments : null,
            'created_date'         => date('Y-m-d H:i:s'),
        ]]);
        if (! $saved) {
            log_message('error', 'Failed to save effort for user ' . $userId . '.');

            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Effort could not be saved. Please try again.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Effort logged.']);
    }

    public function delete(): ResponseInterface
    {
        $effortId = filter_var($this->request->getPost('effort_id'), FILTER_VALIDATE_INT);
        $userId   = (int) session()->get('employee_id');
        if ($effortId === false || ! model(EffortLogModel::class)->deleteOwnEffort($effortId, $userId)) {
            return $this->fail('This effort could not be deleted.');
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Effort deleted.']);
    }

    private function fail(string $message): ResponseInterface
    {
        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $message]);
    }

    private function validDate($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        $date  = \DateTime::createFromFormat('!Y-m-d', $value);

        return ($date && $date->format('Y-m-d') === $value) ? $value : null;
    }

    /** Accepts "h:mm" or a decimal number of hours; returns hours rounded to 2 places, or null. */
    private function parseHours($value): ?float
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        if (preg_match('/^(\d{1,2}):([0-5]\d)$/', $value, $m)) {
            $hours = (int) $m[1] + (int) $m[2] / 60;
        } elseif (is_numeric($value)) {
            $hours = (float) $value;
        } else {
            return null;
        }
        $hours = round($hours, 2);

        return ($hours > 0 && $hours <= 24) ? $hours : null;
    }
}
