<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Task dependencies ("blocked by"). A blocker counts as open while its status is one of the
 * pending task statuses (Config\SprintModelConfig::$pendingTaskStatuses).
 */
class TaskDependencyModel extends BaseModel
{
    protected $table = 'orvexa_task_dependency';
    protected $primaryKey = 'dependency_id';

    private const MAX_PER_TASK = 25;

    /** Joins that resolve a task alias to its product id (story tasks and direct backlog tasks). */
    private static function productJoin(string $alias): string
    {
        return "LEFT JOIN orvexa_user_story {$alias}_us ON {$alias}_us.user_story_id = {$alias}.r_user_story_id
                LEFT JOIN orvexa_epic {$alias}_e ON {$alias}_e.epic_id = {$alias}_us.r_epic_id
                INNER JOIN orvexa_backlog_item {$alias}_bi
                        ON {$alias}_bi.backlog_item_id = COALESCE({$alias}.r_backlog_item_id, {$alias}_e.r_backlog_item_id)";
    }

    /**
     * Tasks this task is blocked by.
     *
     * @param int[] $openStatuses
     */
    public function blockersOf(int $taskId, array $openStatuses): array
    {
        $open = implode(',', array_map('intval', $openStatuses)) ?: '0';
        $sql = "SELECT d.dependency_id, b.task_id, b.task_title, s.name AS status_name,
                       CASE WHEN b.task_status IN ($open) THEN 1 ELSE 0 END AS is_open,
                       u.first_name AS assignee_name
                FROM orvexa_task_dependency d
                INNER JOIN orvexa_task b ON b.task_id = d.r_depends_on_task_id AND b.is_deleted = 'N'
                LEFT JOIN orvexa_task_status s ON s.id = b.task_status
                LEFT JOIN orvexa_user u ON u.external_employee_id = b.assignee_id
                WHERE d.r_task_id = :taskId:
                ORDER BY is_open DESC, b.task_id";

        return $this->query($sql, ['taskId' => $taskId])->getResultArray();
    }

    /** Other active tasks of the same product that this task could depend on. */
    public function candidatesFor(int $taskId, int $productId): array
    {
        $sql = "SELECT t.task_id, t.task_title, t_bi.backlog_item_name
                FROM orvexa_task t
                " . self::productJoin('t') . "
                WHERE t_bi.r_product_id = :productId: AND t_bi.is_deleted = 'N'
                  AND t.is_deleted = 'N' AND t.task_id <> :taskId:
                  AND t.task_id NOT IN (SELECT r_depends_on_task_id FROM orvexa_task_dependency WHERE r_task_id = :taskId:)
                ORDER BY t_bi.backlog_item_name, t.task_id DESC
                LIMIT 500";

        return $this->query($sql, ['taskId' => $taskId, 'productId' => $productId])->getResultArray();
    }

    /**
     * Number of open blockers per task.
     *
     * @param int[] $taskIds
     * @param int[] $openStatuses
     * @return array<int,int>
     */
    public function openBlockerCounts(array $taskIds, array $openStatuses): array
    {
        $taskIds = array_values(array_filter(array_map('intval', $taskIds)));
        if ($taskIds === []) {
            return [];
        }
        $rows = $this->db->table($this->table . ' d')
            ->select('d.r_task_id, COUNT(*) AS open_count')
            ->join('orvexa_task b', "b.task_id = d.r_depends_on_task_id AND b.is_deleted = 'N'")
            ->whereIn('d.r_task_id', $taskIds)
            ->whereIn('b.task_status', array_map('intval', $openStatuses) ?: [0])
            ->groupBy('d.r_task_id')
            ->get()->getResultArray();

        return array_map('intval', array_column($rows, 'open_count', 'r_task_id'));
    }

    /** @return string|null error message, or null when added */
    public function addDependency(int $taskId, int $dependsOn, int $productId, int $userId): ?string
    {
        if ($taskId === $dependsOn) {
            return 'A task cannot depend on itself.';
        }
        $blocker = $this->query(
            "SELECT t.task_id FROM orvexa_task t " . self::productJoin('t') . "
             WHERE t.task_id = :id: AND t.is_deleted = 'N' AND t_bi.r_product_id = :productId:",
            ['id' => $dependsOn, 'productId' => $productId]
        )->getRowArray();
        if (! $blocker) {
            return 'Choose a task from the same product.';
        }
        $count = $this->db->table($this->table)->where('r_task_id', $taskId)->countAllResults();
        if ($count >= self::MAX_PER_TASK) {
            return 'A task can have at most ' . self::MAX_PER_TASK . ' dependencies.';
        }
        if ($this->db->table($this->table)->where(['r_task_id' => $taskId, 'r_depends_on_task_id' => $dependsOn])->countAllResults() > 0) {
            return 'This dependency already exists.';
        }
        if ($this->reaches($dependsOn, $taskId)) {
            return 'That would create a circular dependency.';
        }
        $ok = $this->db->table($this->table)->insert([
            'r_task_id'            => $taskId,
            'r_depends_on_task_id' => $dependsOn,
            'r_user_id_created'    => $userId,
            'created_date'         => date('Y-m-d H:i:s'),
        ]);

        return $ok ? null : 'The dependency could not be saved.';
    }

    public function removeDependency(int $dependencyId, int $taskId): bool
    {
        $this->db->table($this->table)->where(['dependency_id' => $dependencyId, 'r_task_id' => $taskId])->delete();

        return $this->db->affectedRows() === 1;
    }

    /** True when $target is reachable from $start by following "blocked by" links. */
    private function reaches(int $start, int $target): bool
    {
        $seen = [$start => true];
        $frontier = [$start];
        for ($depth = 0; $frontier !== [] && $depth < 50; $depth++) {
            $next = array_map('intval', array_column(
                $this->db->table($this->table)->select('r_depends_on_task_id')->whereIn('r_task_id', $frontier)->get()->getResultArray(),
                'r_depends_on_task_id'
            ));
            $frontier = [];
            foreach ($next as $id) {
                if ($id === $target) {
                    return true;
                }
                if (! isset($seen[$id])) {
                    $seen[$id] = true;
                    $frontier[] = $id;
                }
            }
        }

        return false;
    }
}
