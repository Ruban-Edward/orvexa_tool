<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-user activity notifications (task assigned, story comment, sprint status).
 * `r_user_id` and `r_actor_id` hold the external employee id, like every other
 * user reference in the schema.
 */
class CreateNotificationTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'notification_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'r_user_id'       => ['type' => 'INT'],
            'r_actor_id'      => ['type' => 'INT', 'null' => true],
            'type'            => ['type' => 'VARCHAR', 'constraint' => 30],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'message'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'link'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_read'         => ['type' => 'ENUM', 'constraint' => ['N', 'Y'], 'default' => 'N'],
            'created_date'    => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('notification_id');
        // Serves both the unread badge count and the newest-first list
        $this->forge->addKey(['r_user_id', 'is_read', 'created_date']);
        $this->forge->createTable('orvexa_notification', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
    }

    public function down()
    {
        $this->forge->dropTable('orvexa_notification', true);
    }
}
