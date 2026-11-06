<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveWhatsappApiKeys extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('wa_message_queue') && $this->db->fieldExists('api_key_id', 'wa_message_queue')) {
            // Names differ between installations and drivers; SQLite supplies synthetic names.
            foreach ($this->db->getForeignKeyData('wa_message_queue') as $foreignKey) {
                if (in_array('api_key_id', (array) $foreignKey->column_name, true)) {
                    $this->forge->dropForeignKey('wa_message_queue', $foreignKey->constraint_name);
                }
            }

            $this->forge->dropColumn('wa_message_queue', 'api_key_id');
        }

        $this->forge->dropTable('wa_api_keys', true);
    }

    public function down()
    {
        // Restore the schema only: deleted credentials and queue associations require a backup.
        if (! $this->db->tableExists('wa_api_keys')) {
            $this->forge->addField([
                'id' => [
                    'type' => $this->db->DBDriver === 'SQLite3' ? 'INTEGER' : 'BIGINT',
                    'auto_increment' => true,
                ],
                'name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'plain_key' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'key_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
                'prefix' => ['type' => 'VARCHAR', 'constraint' => 16],
                'pegawai_id' => ['type' => 'INTEGER', 'null' => true],
                'is_active' => ['type' => 'BOOLEAN', 'default' => true],
                'last_used_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'revoked_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('key_hash');
            if ($this->db->tableExists('pegawai')) {
                $this->forge->addForeignKey(
                    'pegawai_id',
                    'pegawai',
                    'id',
                    '',
                    'SET NULL',
                    $this->db->DBDriver === 'SQLite3' ? '' : 'fk_wa_api_keys_pegawai'
                );
            }
            $this->forge->createTable('wa_api_keys');
        }

        if ($this->db->tableExists('wa_message_queue') && ! $this->db->fieldExists('api_key_id', 'wa_message_queue')) {
            $this->forge->addColumn('wa_message_queue', [
                'api_key_id' => ['type' => 'BIGINT', 'null' => true],
            ]);
            $this->forge->addForeignKey('api_key_id', 'wa_api_keys', 'id', 'SET NULL', 'CASCADE');
            $this->forge->processIndexes('wa_message_queue');
        }
    }
}
