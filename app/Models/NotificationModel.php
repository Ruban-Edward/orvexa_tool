<?php

/**
 * NotificationModel.php
 *
 * @category   Model
 * @package    App\Models
 * @purpose    Reads and writes the per-user activity feed in `orvexa_notification`
 *             and looks up the context (recipients, names, links) for each event.
 *             All user ids are external employee ids.
 */

namespace App\Models;

class NotificationModel extends BaseModel
{
    protected $table = 'orvexa_notification';

    protected $primaryKey = 'notification_id';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'r_user_id',
        'r_actor_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'created_date',
    ];

    /**
     * Insert one notification per recipient in a single statement.
     */
    public function insertForUsers(array $userIds, array $notification): void
    {
        if ($userIds === []) {
            return;
        }
        $now  = date('Y-m-d H:i:s');
        $rows = array_map(static fn ($userId) => $notification + [
            'r_user_id'    => $userId,
            'is_read'      => 'N',
            'created_date' => $now,
        ], $userIds);

        $this->db->table($this->table)->insertBatch($rows);
    }

    public function getRecent(int $userId, int $limit = 20): array
    {
        $sql = "SELECT n.notification_id, n.type, n.title, n.message, n.link, n.is_read, n.created_date,
                       u.first_name AS actor_name
                FROM orvexa_notification n
                LEFT JOIN orvexa_user u ON u.external_employee_id = n.r_actor_id
                WHERE n.r_user_id = :user_id:
                ORDER BY n.created_date DESC, n.notification_id DESC
                LIMIT " . (int) $limit;

        return $this->query($sql, ['user_id' => $userId])->getResultArray();
    }

    public function countUnread(int $userId): int
    {
        return (int) $this->db->table($this->table)
            ->where(['r_user_id' => $userId, 'is_read' => 'N'])
            ->countAllResults();
    }

    /**
     * Mark the given notifications (or all of them when $ids is empty) as read.
     * Always scoped to the owner, so ids from another user are ignored.
     */
    public function markRead(int $userId, array $ids = []): void
    {
        $builder = $this->db->table($this->table)
            ->where(['r_user_id' => $userId, 'is_read' => 'N']);
        if ($ids !== []) {
            $builder->whereIn('notification_id', $ids);
        }
        $builder->update(['is_read' => 'Y']);
    }

    /**
     * Drop read notifications older than the retention window so the table stays small.
     */
    public function purgeOld(int $days = 60): void
    {
        $this->db->table($this->table)
            ->where('is_read', 'Y')
            ->where('created_date <', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->delete();
    }

    public function getActorName(int $userId): string
    {
        $row = $this->db->table('orvexa_user')
            ->select('first_name')
            ->where('external_employee_id', $userId)
            ->get()->getRowArray();

        return $row['first_name'] ?? 'Someone';
    }

    /**
     * Task with its story/backlog/product ids for building the tasks page link.
     */
    public function getTaskContext(int $taskId): ?array
    {
        $sql = "SELECT t.task_id, t.task_title, t.assignee_id, t.r_user_story_id AS user_story_id,
                       e.r_backlog_item_id AS backlog_item_id, bi.r_product_id AS product_id, p.product_name
                FROM orvexa_task t
                INNER JOIN orvexa_user_story us ON us.user_story_id = t.r_user_story_id
                INNER JOIN orvexa_epic e ON e.epic_id = us.r_epic_id
                INNER JOIN orvexa_backlog_item bi ON bi.backlog_item_id = e.r_backlog_item_id
                LEFT JOIN orvexa_product p ON p.external_project_id = bi.r_product_id
                WHERE t.task_id = :task_id:";

        return $this->query($sql, ['task_id' => $taskId])->getRowArray();
    }

    /**
     * User story context plus everyone involved in it: the author, task assignees
     * and earlier commenters.
     */
    public function getStoryContext(int $storyId): ?array
    {
        $sql = "SELECT us.user_story_id, us.i_want, us.r_user_id_created,
                       e.r_backlog_item_id AS backlog_item_id, bi.r_product_id AS product_id, p.product_name
                FROM orvexa_user_story us
                INNER JOIN orvexa_epic e ON e.epic_id = us.r_epic_id
                INNER JOIN orvexa_backlog_item bi ON bi.backlog_item_id = e.r_backlog_item_id
                LEFT JOIN orvexa_product p ON p.external_project_id = bi.r_product_id
                WHERE us.user_story_id = :id:";
        $story = $this->query($sql, ['id' => $storyId])->getRowArray();
        if (! $story) {
            return null;
        }

        $sql = "SELECT assignee_id AS id FROM orvexa_task
                WHERE r_user_story_id = :id: AND is_deleted = 'N' AND assignee_id IS NOT NULL
                UNION
                SELECT r_user_id FROM orvexa_comments
                WHERE r_user_story_id = :id: AND is_deleted = 'N'";
        $involved = array_column($this->query($sql, ['id' => $storyId])->getResultArray(), 'id');

        $story['participants'] = array_merge([$story['r_user_id_created']], $involved);

        return $story;
    }

    public function getSprintContext(int $sprintId): ?array
    {
        $sql = "SELECT s.sprint_id, s.sprint_name, s.sprint_version, p.product_name, st.status_name
                FROM orvexa_sprint s
                LEFT JOIN orvexa_product p ON p.external_project_id = s.r_product_id
                LEFT JOIN orvexa_module_status ms ON ms.module_status_id = s.r_module_status_id
                LEFT JOIN orvexa_status st ON st.status_id = ms.r_status_id
                WHERE s.sprint_id = :id:";
        $sprint = $this->query($sql, ['id' => $sprintId])->getRowArray();
        if (! $sprint) {
            return null;
        }

        $members = $this->db->table('orvexa_sprint_user')
            ->select('r_user_id')
            ->where(['r_sprint_id' => $sprintId, 'is_deleted' => 'N'])
            ->get()->getResultArray();
        $sprint['members'] = array_column($members, 'r_user_id');

        return $sprint;
    }
}
