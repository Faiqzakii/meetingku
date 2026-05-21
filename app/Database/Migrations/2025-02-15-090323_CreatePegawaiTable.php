<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePegawaiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
               'type' => 'SERIAL',
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'nip' => [
                'type' => 'VARCHAR',
                'constraint' => '18',
                'unique' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => '25',
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'is_admin' => [
                'type' => 'BOOLEAN',
                'default' => false
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
        $this->forge->createTable('pegawai');
    }

    public function down()
    {
        $this->forge->dropTable('pegawai');
    }
}
