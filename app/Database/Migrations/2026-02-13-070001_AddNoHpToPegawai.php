<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoHpToPegawai extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pegawai', [
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'nip',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pegawai', 'no_hp');
    }
}
