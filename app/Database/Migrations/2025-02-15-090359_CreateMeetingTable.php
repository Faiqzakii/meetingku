<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
            ],
            'nama_keg' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'ruangan_id' => [
                'type' => 'INT',
            ],
            'pegawai_id' => [
                'type' => 'INT',
            ],
            'waktu_mulai' => [
                'type' => 'DATETIME',
            ],
            'waktu_selesai' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'default' => 'pending'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('ruangan_id', 'ruangan', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('meeting');
    }

    public function down()
    {
        $this->forge->dropTable('meeting');
    }
}
