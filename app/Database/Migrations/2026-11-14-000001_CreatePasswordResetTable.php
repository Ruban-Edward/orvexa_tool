<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Self-service "Forgot password": one-time reset links. Only a SHA-256 hash of the token is
 * stored, so a database leak does not expose working links.
 */
class CreatePasswordResetTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'reset_id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'r_user_id'    => ['type' => 'INT'],
            'token_hash'   => ['type' => 'CHAR', 'constraint' => 64],
            'expires_at'   => ['type' => 'DATETIME'],
            'used_at'      => ['type' => 'DATETIME', 'null' => true],
            'request_ip'   => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_date' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('reset_id');
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addKey(['r_user_id', 'created_date']);
        $this->forge->createTable('orvexa_password_reset', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
    }

    public function down()
    {
        $this->forge->dropTable('orvexa_password_reset', true);
    }
}
