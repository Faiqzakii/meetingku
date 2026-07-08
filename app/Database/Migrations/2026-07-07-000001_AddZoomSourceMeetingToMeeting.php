<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZoomSourceMeetingToMeeting extends Migration
{
    public function up()
    {
        $this->forge->addColumn('meeting', [
            'zoom_source_meeting_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'start_token',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('meeting', ['zoom_source_meeting_id']);
    }
}
