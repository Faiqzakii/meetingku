<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddMeetingScheduleIndex extends Migration
{
    public function up()
    {
        $this->forge->addKey(['waktu_mulai', 'id'], false, false, $this->db->prefixTable('meeting_schedule_idx'));
        if (!$this->forge->processIndexes('meeting')) {
            throw new RuntimeException('Could not create the meeting schedule index.');
        }
    }

    public function down()
    {
        if (!$this->forge->dropKey('meeting', 'meeting_schedule_idx')) {
            throw new RuntimeException('Could not remove the meeting schedule index.');
        }
    }
}
