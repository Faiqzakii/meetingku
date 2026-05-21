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
                'null' => true, // Initially null for existing records, or default 0
            ],
            'fasilitas' => [
                'type' => 'TEXT', // Using TEXT to be safe across DB versions, storing JSON
                'null' => true,
            ]
        ];

        $this->forge->addColumn('meeting', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('meeting', ['jumlah_peserta', 'fasilitas']);
    }
}
