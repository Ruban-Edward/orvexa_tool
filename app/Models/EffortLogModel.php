<?php

namespace App\Models;

use App\Models\BaseModel;

class EffortLogModel extends BaseModel
{
    protected $table = 'orvexa_effort_log';
    protected $primaryKey = 'effort_log_id';
    protected $allowedFields = [
        'r_task_id',
        'r_meeting_details_id',
        'r_user_id',
        'r_logged_by',
        'r_activity_id',
        'r_main_activity_id',
        'r_log_type_id',
        'spent_on',
        'hours',
        'comments',
        'created_date',
    ];

    public function getTaskEfforts(int $taskId): array
    {
        $sql = "SELECT
                    effort.effort_log_id,
                    effort.r_user_id,
                    effort.r_logged_by,
                    effort.spent_on,
                    effort.hours,
                    effort.comments,
                    effort.created_date,
                    logged_user.first_name AS user_name,
                    logger.first_name AS logged_by_name
                FROM orvexa_effort_log effort
                LEFT JOIN orvexa_user logged_user
                    ON logged_user.external_employee_id = effort.r_user_id
                LEFT JOIN orvexa_user logger
                    ON logger.external_employee_id = effort.r_logged_by
                WHERE effort.r_task_id = :taskId:
                ORDER BY effort.spent_on DESC, effort.effort_log_id DESC";

        return $this->query($sql, ['taskId' => $taskId])->getResultArray();
    }

    public function insertEfforts(array $entries): bool
    {
        $inserted = $this->db->table($this->table)->insertBatch($entries);

        return $inserted !== false && $inserted > 0;
    }

    /**
     * One user's efforts between two dates (inclusive), newest first, with the task/meeting
     * context and the master-data labels.
     */
    public function getUserEfforts(int $userId, string $from, string $to): array
    {
        $sql = "SELECT
                    effort.effort_log_id,
                    effort.r_task_id,
                    effort.r_meeting_details_id,
                    effort.r_logged_by,
                    effort.r_activity_id,
                    effort.r_main_activity_id,
                    effort.r_log_type_id,
                    effort.spent_on,
                    effort.hours,
                    effort.comments,
                    act.master_name AS activity_name,
                    main_act.master_name AS main_activity_name,
                    log_type.master_name AS log_type_name,
                    t.task_title,
                    meet.meeting_title,
                    COALESCE(task_product.product_name, meet_product.product_name) AS product_name,
                    bi.backlog_item_id,
                    bi.r_product_id AS product_id,
                    t.r_user_story_id AS user_story_id
                FROM orvexa_effort_log effort
                LEFT JOIN orvexa_effort_master act ON act.effort_master_id = effort.r_activity_id
                LEFT JOIN orvexa_effort_master main_act ON main_act.effort_master_id = effort.r_main_activity_id
                LEFT JOIN orvexa_effort_master log_type ON log_type.effort_master_id = effort.r_log_type_id
                LEFT JOIN orvexa_task t ON t.task_id = effort.r_task_id
                LEFT JOIN orvexa_user_story us ON us.user_story_id = t.r_user_story_id
                LEFT JOIN orvexa_epic e ON e.epic_id = us.r_epic_id
                LEFT JOIN orvexa_backlog_item bi ON bi.backlog_item_id = COALESCE(t.r_backlog_item_id, e.r_backlog_item_id)
                LEFT JOIN orvexa_product task_product ON task_product.external_project_id = bi.r_product_id
                LEFT JOIN orvexa_meeting_details meet ON meet.meeting_details_id = effort.r_meeting_details_id
                LEFT JOIN orvexa_product meet_product ON meet_product.external_project_id = meet.r_product_id
                WHERE effort.r_user_id = :userId:
                  AND effort.spent_on BETWEEN :from: AND :to:
                ORDER BY effort.spent_on DESC, effort.effort_log_id DESC
                LIMIT 2000";

        return $this->query($sql, ['userId' => $userId, 'from' => $from, 'to' => $to])->getResultArray();
    }

    /**
     * Tasks a user can log effort on: assigned to them in a product they belong to, either still
     * open or touched in the last $doneDays days. Covers story tasks and direct backlog tasks.
     *
     * @param int[] $openStatuses
     */
    public function getLoggableTasks(int $userId, array $openStatuses, int $doneDays = 30): array
    {
        $open = implode(',', array_map('intval', $openStatuses));

        $sql = "SELECT t.task_id, t.task_title, p.product_name
                FROM orvexa_task t
                LEFT JOIN orvexa_user_story us ON us.user_story_id = t.r_user_story_id AND us.is_deleted = 'N'
                LEFT JOIN orvexa_epic e ON e.epic_id = us.r_epic_id AND e.is_deleted = 'N'
                INNER JOIN orvexa_backlog_item bi ON bi.backlog_item_id = COALESCE(t.r_backlog_item_id, e.r_backlog_item_id)
                       AND bi.is_deleted = 'N'
                INNER JOIN orvexa_product p ON p.external_project_id = bi.r_product_id AND p.is_deleted = 'N'
                INNER JOIN orvexa_product_user pu ON pu.r_product_id = p.external_project_id
                       AND pu.r_user_id = t.assignee_id AND pu.is_deleted = 'N'
                WHERE t.assignee_id = :userId:
                  AND t.is_deleted = 'N'
                  AND (t.task_status IN ($open) OR t.updated_date >= :doneSince:)
                ORDER BY p.product_name, t.task_id DESC
                LIMIT 500";

        return $this->query($sql, [
            'userId'    => $userId,
            'doneSince' => date('Y-m-d H:i:s', strtotime("-{$doneDays} days")),
        ])->getResultArray();
    }

    /** One effort row that belongs to the user (logged by them or for them), or null. */
    public function getOwnEffort(int $effortId, int $userId): ?array
    {
        return $this->db->table($this->table)
            ->where(['effort_log_id' => $effortId, 'r_user_id' => $userId])
            ->get()->getRowArray();
    }

    public function updateOwnEffort(int $effortId, int $userId, array $data): bool
    {
        return $this->db->table($this->table)
            ->where(['effort_log_id' => $effortId, 'r_user_id' => $userId])
            ->update($data);
    }

    /**
     * Deletes one of the user's efforts. When the last effort of a meeting is removed, the meeting
     * is marked as not logged again so its time can be logged afresh.
     */
    public function deleteOwnEffort(int $effortId, int $userId): bool
    {
        $effort = $this->getOwnEffort($effortId, $userId);
        if (! $effort) {
            return false;
        }

        $this->db->transStart();
        $this->db->table($this->table)->where('effort_log_id', $effortId)->delete();
        $meetingId = $effort['r_meeting_details_id'];
        if ($meetingId !== null
            && $this->db->table($this->table)->where('r_meeting_details_id', $meetingId)->countAllResults() === 0) {
            $this->db->table('orvexa_meeting_details')->where('meeting_details_id', $meetingId)->update(['is_logged' => 'N']);
        }
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
