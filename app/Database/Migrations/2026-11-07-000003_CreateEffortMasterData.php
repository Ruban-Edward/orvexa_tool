<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Master data for effort logging (Activity, Main activity, Log type), managed in Admin settings,
 * and the matching references on orvexa_effort_log. Older task and meeting efforts keep NULL here.
 */
class CreateEffortMasterData extends Migration
{
    private const SEED = [
        'activity' => [
            'Analysis', 'Bug Fixing', 'Coding', 'Code Review', 'Code Integration', 'Client Call',
            'Daily Stand up Meeting', 'Brainstorming', 'Sprint planning', 'Sprint Review', 'Team Meeting',
            'Unit testing', 'Live Support', 'Training', 'Documentation', 'Team Assistance', 'K.T (Handover)',
            'Infra support', 'Security fixing', 'Appraisal Meeting', 'Employee Engagement', 'Learning',
            'Planning and Tracking', 'Technical/Functional Support', 'Gap Identification',
            'AI Tool Implementation', 'Report Generation', 'Effort Arrival/Approval',
        ],
        'main_activity' => [
            'Development', 'BugFixing', 'Meeting', 'PreSale Support', 'Customer Support', 'Code Review',
            'Documentation', 'Testing', 'Product Management', 'Project Management', 'Training / K.T',
            'Infra Support', 'Internal Process', 'Hackathon', 'Requirement Gathering', 'People Management',
            'Employee Engagement', 'Social Media', 'Database management', 'Outreach', 'Marketing Collaterals',
            'Website,SEO & ABM', 'RFP/TENDER', 'Report generation', 'Page Optimization', 'Ads', 'Backlinks',
            'Leave Availed', 'Demo', 'UI/UX-Process',
        ],
        'log_type' => ['Time'],
    ];

    public function up()
    {
        $this->forge->addField([
            'effort_master_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'master_type'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'master_name'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'sort_order'       => ['type' => 'INT', 'default' => 0],
            'is_deleted'       => ['type' => 'CHAR', 'constraint' => 1, 'default' => 'N'],
            'created_date'     => ['type' => 'DATETIME'],
            'updated_date'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('effort_master_id');
        $this->forge->addUniqueKey(['master_type', 'master_name']);
        $this->forge->createTable('orvexa_effort_master', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);

        $now  = date('Y-m-d H:i:s');
        $rows = [];
        foreach (self::SEED as $type => $names) {
            foreach ($names as $i => $name) {
                $rows[] = [
                    'master_type' => $type, 'master_name' => $name, 'sort_order' => $i + 1,
                    'is_deleted' => 'N', 'created_date' => $now, 'updated_date' => $now,
                ];
            }
        }
        $this->db->table('orvexa_effort_master')->ignore(true)->insertBatch($rows);

        $this->forge->addColumn('orvexa_effort_log', [
            'r_activity_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'r_logged_by'],
            'r_main_activity_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'r_activity_id'],
            'r_log_type_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'r_main_activity_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orvexa_effort_log', ['r_activity_id', 'r_main_activity_id', 'r_log_type_id']);
        $this->forge->dropTable('orvexa_effort_master', true);
    }
}
