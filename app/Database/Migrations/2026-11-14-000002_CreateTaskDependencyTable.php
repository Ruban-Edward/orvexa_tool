<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Task dependencies: "r_task_id is blocked by r_depends_on_task_id". Both tasks belong to the
 * same product; cycles are rejected in TaskDependencyModel.
 */
class CreateTaskDependencyTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'dependency_id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'r_task_id'            => ['type' => 'INT'],
            'r_depends_on_task_id' => ['type' => 'INT'],
            'r_user_id_created'    => ['type' => 'INT', 'null' => true],
            'created_date'         => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('dependency_id');
        $this->forge->addUniqueKey(['r_task_id', 'r_depends_on_task_id']);
        $this->forge->addKey('r_depends_on_task_id');
        $this->forge->createTable('orvexa_task_dependency', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
    }

    public function down()
    {
        $this->forge->dropTable('orvexa_task_dependency', true);
    }
}
