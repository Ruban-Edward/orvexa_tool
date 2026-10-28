<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Users created (or reset) by an admin get a temporary password and must choose
 * their own at the next sign-in.
 */
class AddPasswordResetRequiredToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orvexa_user', [
            'password_reset_required' => [
                'type'       => 'ENUM',
                'constraint' => ['N', 'Y'],
                'default'    => 'N',
                'after'      => 'password',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orvexa_user', 'password_reset_required');
    }
}
