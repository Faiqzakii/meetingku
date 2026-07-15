<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMeetingMetaToWaQueue extends Migration
{
    public function up()
    {
        $this->forge->addColumn('wa_message_queue', [
            'meta_kind' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
                'after'      => 'message',
            ],
            'meta_meeting_id' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'after'      => 'meta_kind',
            ],
        ]);

        $this->db->query(
            'CREATE INDEX IF NOT EXISTS wa_message_queue_meta_idx '
            . 'ON wa_message_queue (meta_kind, meta_meeting_id)'
        );
    }

    public function down()
    {
        $this->db->query('DROP INDEX IF EXISTS wa_message_queue_meta_idx');
        $this->forge->dropColumn('wa_message_queue', ['meta_meeting_id', 'meta_kind']);
    }
}
