<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStartTokenToMeeting extends Migration
{
    public function up()
    {
        $this->forge->addColumn('meeting', [
            'start_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'unique'     => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('meeting', ['start_token']);
    }
}