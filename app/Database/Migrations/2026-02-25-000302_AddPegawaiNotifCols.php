<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPegawaiNotifCols extends Migration
{
    public function up()
    {
        $fields = [
            'terima_notif_offline' => [
                'type'       => 'SMALLINT',
                'default'    => 0,
                'null'       => false,
            ],
            'terima_notif_zoom' => [
                'type'       => 'SMALLINT',
                'default'    => 0,
                'null'       => false,
            ],
        ];

        $this->forge->addColumn('pegawai', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pegawai', 'terima_notif_offline');
        $this->forge->dropColumn('pegawai', 'terima_notif_zoom');
    }
}
