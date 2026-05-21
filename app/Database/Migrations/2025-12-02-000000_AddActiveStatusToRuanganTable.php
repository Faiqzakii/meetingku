<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActiveStatusToRuanganTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ruangan', [
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ruangan', 'is_active');
    }
}
