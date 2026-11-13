<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Effort master data: the Activity, Main activity and Log type lists used when logging effort.
 * Removed entries are deleted from the master list.
 */
class EffortMasterModel extends BaseModel
{
    public const TYPES = [
        'activity'      => 'Activity',
        'main_activity' => 'Main activity',
        'log_type'      => 'Log type',
    ];

    protected $table = 'orvexa_effort_master';
    protected $primaryKey = 'effort_master_id';

    /** Active entries, grouped by type, in display order. */
    public function getAllGrouped(): array
    {
        $grouped = array_fill_keys(array_keys(self::TYPES), []);
        $rows = $this->db->table($this->table)->where('is_deleted', 'N')
            ->orderBy('sort_order')->orderBy('master_name')->get()->getResultArray();
        foreach ($rows as $row) {
            $grouped[$row['master_type']][] = $row;
        }

        return $grouped;
    }

    /** Active entries only, grouped by type, for the log form. */
    public function getActiveGrouped(): array
    {
        $grouped = array_fill_keys(array_keys(self::TYPES), []);
        $rows = $this->db->table($this->table)->select('effort_master_id, master_type, master_name')
            ->where('is_deleted', 'N')->orderBy('sort_order')->orderBy('master_name')->get()->getResultArray();
        foreach ($rows as $row) {
            $grouped[$row['master_type']][] = $row;
        }

        return $grouped;
    }

    public function isActive(int $id, string $type): bool
    {
        return $this->db->table($this->table)
            ->where(['effort_master_id' => $id, 'master_type' => $type, 'is_deleted' => 'N'])
            ->countAllResults() === 1;
    }

    /**
     * Reads activity_id, main_activity_id and log_type_id from posted data and checks each is an
     * active entry of its type.
     *
     * @return array{activity:int,main_activity:int,log_type:int}|string the ids, or an error message
     */
    public function validateSelection(array $post)
    {
        $ids = [];
        foreach (['activity' => 'activity_id', 'main_activity' => 'main_activity_id', 'log_type' => 'log_type_id'] as $type => $field) {
            $id = filter_var($post[$field] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || ! $this->isActive($id, $type)) {
                return 'Choose a valid ' . strtolower(self::TYPES[$type]) . '.';
            }
            $ids[$type] = $id;
        }

        return $ids;
    }

    /** @return string 'added', 'exists' or 'error' */
    public function addEntry(string $type, string $name): string
    {
        $now = date('Y-m-d H:i:s');
        $existing = $this->db->query(
            'SELECT effort_master_id, is_deleted FROM orvexa_effort_master WHERE master_type = ? AND LOWER(master_name) = LOWER(?)',
            [$type, $name]
        )->getRowArray();

        if ($existing) {
            if ($existing['is_deleted'] === 'N') {
                return 'exists';
            }
            if (! $this->db->table($this->table)->where('effort_master_id', $existing['effort_master_id'])->delete()) {
                return 'error';
            }
        }

        $next = (int) ($this->db->table($this->table)->selectMax('sort_order', 'max_order')
            ->where('master_type', $type)->get()->getRow()->max_order ?? 0) + 1;
        $inserted = $this->db->table($this->table)->insert([
            'master_type' => $type, 'master_name' => $name, 'sort_order' => $next,
            'is_deleted' => 'N', 'created_date' => $now, 'updated_date' => $now,
        ]);

        return $inserted ? 'added' : 'error';
    }

    public function removeEntry(int $id): ?array
    {
        $entry = $this->db->table($this->table)->where('effort_master_id', $id)->get()->getRowArray();
        if (! $entry || ! $this->db->table($this->table)->where('effort_master_id', $id)->delete()) {
            return null;
        }

        return $entry;
    }

    public function restoreEntry(array $entry): bool
    {
        $entry['is_deleted'] = 'N';
        $entry['updated_date'] = date('Y-m-d H:i:s');

        return $this->db->table($this->table)->insert($entry);
    }
}
