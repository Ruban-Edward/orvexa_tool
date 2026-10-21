<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Passwords were stored as 32-char unsalted MD5 in a varchar(50) column.
 * bcrypt hashes are 60 characters, so the column is widened. Existing MD5
 * hashes keep working and are transparently upgraded on the user's next login.
 */
class WidenUserPasswordColumn extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `orvexa_user` MODIFY `password` VARCHAR(255) NOT NULL');
    }

    public function down()
    {
        // Intentionally a no-op: shrinking the column would truncate bcrypt hashes.
    }
}
