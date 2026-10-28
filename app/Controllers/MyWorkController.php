<?php

/**
 * MyWorkController.php
 *
 * @category   Controller
 * @purpose    "My work": every task assigned to the signed-in user, across products,
 *             grouped by urgency so the first question of the day has one answer.
 */

namespace App\Controllers;

use App\Models\User\MyWorkModel;
use Config\SprintModelConfig;

class MyWorkController extends BaseController
{
    public function index(): string
    {
        $userId = (int) session()->get('employee_id');
        $tasks  = model(MyWorkModel::class)->getAssignedTasks($userId, (new SprintModelConfig())->pendingTaskStatuses);

        $today   = date('Y-m-d');
        $weekEnd = date('Y-m-d', strtotime('+7 days'));
        $groups  = ['overdue' => [], 'week' => [], 'later' => [], 'done' => []];

        foreach ($tasks as $task) {
            $due = $task['end_date'] ? substr($task['end_date'], 0, 10) : null;
            $task['due']      = $due;
            $task['task_url'] = 'backlog/tasks?' . http_build_query([
                'pid'   => $task['product_id'],
                'pblid' => $task['backlog_item_id'],
                'usid'  => $task['user_story_id'],
            ]);

            if (! $task['is_open']) {
                $groups['done'][] = $task;
            } elseif ($due !== null && $due < $today) {
                $groups['overdue'][] = $task;
            } elseif ($due !== null && $due <= $weekEnd) {
                $groups['week'][] = $task;
            } else {
                $groups['later'][] = $task;
            }
        }

        $products = array_values(array_unique(array_column($tasks, 'product_name')));
        sort($products, SORT_NATURAL | SORT_FLAG_CASE);

        $breadcrumbs = ['My work' => ''];

        return $this->template_view('mywork/myWork', [
            'groups'   => $groups,
            'products' => $products,
            'today'    => $today,
        ], 'My work', $breadcrumbs);
    }
}
