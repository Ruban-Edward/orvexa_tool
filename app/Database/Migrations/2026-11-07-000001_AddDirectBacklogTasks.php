<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddDirectBacklogTasks extends Migration
{
    public function up()
    {
        $type = $this->taskStoryColumnType();

        $this->db->query("ALTER TABLE orvexa_task MODIFY r_user_story_id {$type} NULL");
        $this->db->query("ALTER TABLE orvexa_task ADD COLUMN r_backlog_item_id {$type} NULL AFTER r_user_story_id");
    }

    public function down()
    {
        $directTasks = $this->db->table('orvexa_task')
            ->where('r_user_story_id', null)
            ->where('r_backlog_item_id IS NOT NULL', null, false)
            ->countAllResults();

        if ($directTasks > 0) {
            throw new RuntimeException('Cannot remove direct backlog task links while direct backlog tasks exist.');
        }

        $this->forge->dropColumn('orvexa_task', 'r_backlog_item_id');
    }

    private function taskStoryColumnType(): string
    {
        $column = $this->db->query("SHOW COLUMNS FROM orvexa_task LIKE 'r_user_story_id'")->getRowArray();
        $type = $column['Type'] ?? '';

        if (! preg_match('/\A(?:tinyint|smallint|mediumint|int|bigint)(?:\(\d+\))?(?: unsigned)?\z/i', $type)) {
            throw new RuntimeException('Could not safely determine the type of orvexa_task.r_user_story_id.');
        }

        return $type;
    }
}
