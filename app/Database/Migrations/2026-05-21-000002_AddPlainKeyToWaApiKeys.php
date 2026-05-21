<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlainKeyToWaApiKeys extends Migration
{
    public function up()
    {
        if ($this->hasPlainKeyColumn()) {
            return;
        }

        $fields = [
            'plain_key' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'name'],
        ];

        $this->forge->addColumn('wa_api_keys', $fields);
    }

    public function down()
    {
        if (!$this->hasPlainKeyColumn()) {
            return;
        }

        $this->forge->dropColumn('wa_api_keys', 'plain_key');
    }

    private function hasPlainKeyColumn(): bool
    {
        foreach ($this->db->getFieldData('wa_api_keys') as $field) {
            if (($field->name ?? null) === 'plain_key') {
                return true;
            }
        }

        return false;
    }
}
