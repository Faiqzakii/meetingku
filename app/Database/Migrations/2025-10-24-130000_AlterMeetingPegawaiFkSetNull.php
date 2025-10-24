<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMeetingPegawaiFkSetNull extends Migration
{
    public function up()
    {
        // Make meeting.pegawai_id nullable
        $this->forge->modifyColumn('meeting', [
            'pegawai_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        // Drop existing FK and recreate with ON DELETE SET NULL, ON UPDATE CASCADE
        try {
            $this->forge->dropForeignKey('meeting', 'meeting_pegawai_id_foreign');
        } catch (\Throwable $e) {
            // FK might not exist or have a different name; ignore
        }

        $this->db->query(
            'ALTER TABLE `meeting` ' .
            'ADD CONSTRAINT `meeting_pegawai_id_foreign` ' .
            'FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ' .
            'ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        // Revert FK to RESTRICT on delete (column stays nullable to avoid data loss)
        try {
            $this->forge->dropForeignKey('meeting', 'meeting_pegawai_id_foreign');
        } catch (\Throwable $e) {
        }

        $this->db->query(
            'ALTER TABLE `meeting` ' .
            'ADD CONSTRAINT `meeting_pegawai_id_foreign` ' .
            'FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ' .
            'ON DELETE RESTRICT ON UPDATE CASCADE'
        );
    }
}

