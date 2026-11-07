<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEffortLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'effort_log_id'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'r_task_id'           => ['type' => 'INT', 'null' => true],
            'r_meeting_details_id' => ['type' => 'INT', 'null' => true],
            'r_user_id'           => ['type' => 'INT'],
            'r_logged_by'         => ['type' => 'INT'],
            'spent_on'            => ['type' => 'DATE'],
            'hours'               => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'comments'            => ['type' => 'TEXT', 'null' => true],
            'created_date'        => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('effort_log_id');
        $this->forge->addKey(['r_task_id', 'spent_on']);
        $this->forge->addKey('r_meeting_details_id');
        $this->forge->addKey(['r_user_id', 'spent_on']);
        $this->forge->createTable('orvexa_effort_log', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
    }

    public function down()
    {
        $this->forge->dropTable('orvexa_effort_log', true);
    }
}
