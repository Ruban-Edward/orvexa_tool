<?php

/**
 * MyWorkModel.php
 *
 * @category   Model
 * @purpose    Tasks assigned to one user across every product they belong to,
 *             with the story / backlog item / sprint context needed to link back.
 */

namespace App\Models\User;

use App\Models\BaseModel;

class MyWorkModel extends BaseModel
{
    protected $table = 'orvexa_task';

    protected $primaryKey = 'task_id';

    public function countOpen(int $userId, array $openStatuses): int
    {
        return (int) $this->db->table($this->table)
            ->where(['assignee_id' => $userId, 'is_deleted' => 'N'])
            ->whereIn('task_status', $openStatuses)
            ->countAllResults();
    }

    /**
     * Open tasks, plus tasks finished in the last $doneDays days.
     *
     * @param int[] $openStatuses task statuses that count as "still to do"
     */
    public function getAssignedTasks(int $userId, array $openStatuses, int $doneDays = 30): array
    {
        $open = implode(',', array_map('intval', $openStatuses));

        $sql = "SELECT t.task_id, t.task_title, t.priority, t.task_status, ts.name AS status_name,
                       t.start_date, t.end_date, t.completed_percentage, t.estimated_hours, t.updated_date,
                       (t.task_status IN ($open)) AS is_open,
                       us.user_story_id, us.i_want,
                       bi.backlog_item_id, bi.backlog_item_name,
                       p.external_project_id AS product_id, p.product_name,
                       sp.sprint_id, sp.sprint_name, sp.sprint_version
                FROM orvexa_task t
                INNER JOIN orvexa_user_story us ON us.user_story_id = t.r_user_story_id AND us.is_deleted = 'N'
                INNER JOIN orvexa_epic e ON e.epic_id = us.r_epic_id AND e.is_deleted = 'N'
                INNER JOIN orvexa_backlog_item bi ON bi.backlog_item_id = e.r_backlog_item_id AND bi.is_deleted = 'N'
                INNER JOIN orvexa_product p ON p.external_project_id = bi.r_product_id AND p.is_deleted = 'N'
                INNER JOIN orvexa_product_user pu ON pu.r_product_id = p.external_project_id
                       AND pu.r_user_id = t.assignee_id AND pu.is_deleted = 'N'
                LEFT JOIN orvexa_task_status ts ON ts.id = t.task_status
                LEFT JOIN (
                    SELECT r_task_id, MAX(r_sprint_id) AS sprint_id
                    FROM orvexa_sprint_task
                    WHERE is_deleted = 'N'
                    GROUP BY r_task_id
                ) latest ON latest.r_task_id = t.task_id
                LEFT JOIN orvexa_sprint sp ON sp.sprint_id = latest.sprint_id AND sp.is_deleted = 'N'
                WHERE t.assignee_id = :user_id:
                  AND t.is_deleted = 'N'
                  AND (t.task_status IN ($open) OR t.updated_date >= :done_since:)
                ORDER BY is_open DESC,
                         t.end_date IS NULL, t.end_date,
                         FIELD(t.priority, 'H', 'M', 'L'),
                         t.updated_date DESC
                LIMIT 500";

        return $this->query($sql, [
            'user_id'    => $userId,
            'done_since' => date('Y-m-d H:i:s', strtotime("-{$doneDays} days")),
        ])->getResultArray();
    }
}
