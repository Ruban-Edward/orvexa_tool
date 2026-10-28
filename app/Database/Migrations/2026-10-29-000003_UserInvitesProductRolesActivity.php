<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * - orvexa_user_invite: email invites waiting to be sent / sent / failed. No password
 *   is stored; a temporary one is generated when the email goes out.
 * - Per-product roles: orvexa_product_user.r_role_id was never editable and holds the
 *   default (Developer) everywhere, so it starts from each user's global role.
 * - Index for the per-user activity view.
 */
class UserInvitesProductRolesActivity extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'invite_id'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'r_user_id'    => ['type' => 'INT'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['queued', 'sent', 'failed'], 'default' => 'queued'],
            'error'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'r_invited_by' => ['type' => 'INT', 'null' => true],
            'created_date' => ['type' => 'DATETIME'],
            'sent_date'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('invite_id');
        $this->forge->addKey(['status', 'invite_id']);
        $this->forge->addKey('r_user_id');
        $this->forge->createTable('orvexa_user_invite', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);

        $this->db->query('UPDATE orvexa_product_user pu
                          INNER JOIN orvexa_user u ON u.external_employee_id = pu.r_user_id
                          SET pu.r_role_id = u.r_role_id');

        $this->db->query('ALTER TABLE orvexa_user_action ADD INDEX idx_user_action_user (r_user_id, action_id)');
    }

    public function down()
    {
        $this->forge->dropTable('orvexa_user_invite', true);
        $this->db->query('ALTER TABLE orvexa_user_action DROP INDEX idx_user_action_user');
    }
}
