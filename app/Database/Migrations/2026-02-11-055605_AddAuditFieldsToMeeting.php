<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuditFieldsToMeeting extends Migration
{
    public function up()
    {
        $this->forge->addColumn('meeting', [
            'status_changed_by' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'status_changed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_edited_by' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'last_edited_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('meeting', [
            'status_changed_by',
            'status_changed_at',
            'last_edited_by',
            'last_edited_at',
        ]);
    }
}
