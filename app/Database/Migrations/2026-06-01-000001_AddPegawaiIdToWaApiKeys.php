<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPegawaiIdToWaApiKeys extends Migration
{
    public function up()
    {
        $this->forge->addColumn('wa_api_keys', [
            'pegawai_id' => [
                'type'       => 'INTEGER',
                'null'       => true,
                'after'      => 'prefix',
            ],
        ]);

        // Add foreign key constraint
        $this->db->query('ALTER TABLE wa_api_keys ADD CONSTRAINT fk_wa_api_keys_pegawai FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE SET NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE wa_api_keys DROP CONSTRAINT IF EXISTS fk_wa_api_keys_pegawai');
        $this->forge->dropColumn('wa_api_keys', 'pegawai_id');
    }
}
