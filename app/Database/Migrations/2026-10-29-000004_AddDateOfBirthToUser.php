<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Optional date of birth, maintained by each user on their profile page.
 */
class AddDateOfBirthToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orvexa_user', [
            'date_of_birth' => ['type' => 'DATE', 'null' => true, 'after' => 'email_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orvexa_user', 'date_of_birth');
    }
}
