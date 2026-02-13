<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZoomFieldsToMeeting extends Migration
{
    public function up()
    {
        $this->forge->addColumn('meeting', [
            'zoom_meeting_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
            ],
            'zoom_join_url' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'zoom_meeting_id',
            ],
            'zoom_start_url' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'zoom_join_url',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('meeting', ['zoom_meeting_id', 'zoom_join_url', 'zoom_start_url']);
    }
}
