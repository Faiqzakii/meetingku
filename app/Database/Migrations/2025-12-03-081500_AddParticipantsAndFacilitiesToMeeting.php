<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParticipantsAndFacilitiesToMeeting extends Migration
{
    public function up()
    {
        $fields = [
            'jumlah_peserta' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true, // Initially null for existing records, or default 0
                'after' => 'nama_keg'
            ],
            'fasilitas' => [
                'type' => 'TEXT', // Using TEXT to be safe across DB versions, storing JSON
                'null' => true,
                'after' => 'jumlah_peserta'
            ]
        ];

        $this->forge->addColumn('meeting', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('meeting', ['jumlah_peserta', 'fasilitas']);
    }
}
