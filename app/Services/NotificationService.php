<?php

/**
 * NotificationService.php
 *
 * @category Service
 * @purpose  Turns domain events (task assigned, story commented, sprint status
 *           changed) into activity notifications for the people involved.
 *           The person who triggered the event is never notified, and a failure
 *           here is logged but never breaks the action that caused it.
 */

namespace App\Services;

use App\Models\NotificationModel;
use Throwable;

class NotificationService
{
    public const TYPE_TASK_ASSIGNED  = 'task_assigned';
    public const TYPE_STORY_COMMENT  = 'story_comment';
    public const TYPE_SPRINT_STATUS  = 'sprint_status';
    public const TYPE_STORY_MENTION  = 'story_mention';

    private NotificationModel $model;

    public function __construct()
    {
        $this->model = model(NotificationModel::class);
    }

    public function taskAssigned(int $taskId, int $actorId): void
    {
        $this->safely(function () use ($taskId, $actorId) {
            $task = $this->model->getTaskContext($taskId);
            if (! $task || empty($task['assignee_id'])) {
                return;
            }
            $this->send([(int) $task['assignee_id']], $actorId, [
                'type'    => self::TYPE_TASK_ASSIGNED,
                'title'   => $this->model->getActorName($actorId) . ' assigned you a task',
                'message' => $this->clip($task['task_title']) . ' · ' . $task['product_name'],
                'link'    => 'backlog/tasks?' . http_build_query([
                    'pid'   => $task['product_id'],
                    'pblid' => $task['backlog_item_id'],
                    'usid'  => $task['user_story_id'],
                ]),
            ]);
        });
    }

    /**
     * Current assignee of a task, read before an update so a reassignment can be detected.
     */
    public function assigneeOf(int $taskId): ?int
    {
        $task = $this->model->getTaskContext($taskId);

        return empty($task['assignee_id']) ? null : (int) $task['assignee_id'];
    }

    /**
     * Notifies the story's participants of a new comment. People @mentioned in the comment (by
     * username, members of the story's product only) get a "mentioned you" notice instead.
     */
    public function storyCommented(int $storyId, int $actorId, string $text = ''): void
    {
        $this->safely(function () use ($storyId, $actorId, $text) {
            $story = $this->model->getStoryContext($storyId);
            if (! $story) {
                return;
            }
            $actor = $this->model->getActorName($actorId);
            $message = $this->clip($story['i_want']) . ' · ' . $story['product_name'];
            $link = 'backlog/userstories?' . http_build_query([
                'pid'   => $story['product_id'],
                'pblid' => $story['backlog_item_id'],
            ]);

            $mentioned = array_map('intval', array_column(
                $this->model->getMentionableUsers((int) $story['product_id'], self::mentionedUsernames($text)),
                'id'
            ));
            $this->send($mentioned, $actorId, [
                'type'    => self::TYPE_STORY_MENTION,
                'title'   => $actor . ' mentioned you in a comment',
                'message' => $this->clip($text) . ' · ' . $message,
                'link'    => $link,
            ]);
            $this->send(array_diff(array_map('intval', $story['participants']), $mentioned), $actorId, [
                'type'    => self::TYPE_STORY_COMMENT,
                'title'   => $actor . ' commented on a user story',
                'message' => $message,
                'link'    => $link,
            ]);
        });
    }

    /** @return string[] distinct usernames written as @name in the text (at most 20) */
    public static function mentionedUsernames(string $text): array
    {
        preg_match_all('/(?<![\w@.])@([A-Za-z0-9][A-Za-z0-9._-]{1,49})/', $text, $m);
        $names = array_map(static fn ($n) => rtrim($n, '.-'), $m[1]);

        return array_slice(array_values(array_unique(array_map('strtolower', $names))), 0, 20);
    }

    public function sprintStatusChanged(int $sprintId, int $actorId): void
    {
        $this->safely(function () use ($sprintId, $actorId) {
            $sprint = $this->model->getSprintContext($sprintId);
            if (! $sprint || empty($sprint['status_name'])) {
                return;
            }
            $name = trim(($sprint['sprint_name'] ?? '') ?: 'Sprint ' . $sprint['sprint_version']);
            $this->send($sprint['members'], $actorId, [
                'type'    => self::TYPE_SPRINT_STATUS,
                'title'   => $this->clip($name) . ' is now ' . $sprint['status_name'],
                'message' => $sprint['product_name'],
                'link'    => 'sprint/navsprintview?sprint_id=' . $sprintId,
            ]);
        });
    }

    private function send(array $recipients, int $actorId, array $notification): void
    {
        $recipients = array_values(array_unique(array_filter(
            array_map('intval', $recipients),
            static fn ($id) => $id > 0 && $id !== $actorId
        )));
        $this->model->insertForUsers($recipients, $notification + ['r_actor_id' => $actorId]);
    }

    private function clip(?string $text, int $length = 80): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));

        return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 1) . '…' : $text;
    }

    private function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            log_message('error', 'Notification not sent: ' . $e->getMessage());
        }
    }
}
