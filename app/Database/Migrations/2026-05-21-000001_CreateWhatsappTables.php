<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsappTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'plain_key' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'key_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'prefix' => ['type' => 'VARCHAR', 'constraint' => 16],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'last_used_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'revoked_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key_hash');
        $this->forge->createTable('wa_api_keys', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'auto_increment' => true],
            'api_key_id' => ['type' => 'BIGINT', 'null' => true],
            'to_number' => ['type' => 'VARCHAR', 'constraint' => 32],
            'message' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'attempts' => ['type' => 'INT', 'default' => 0],
            'max_attempts' => ['type' => 'INT', 'default' => 3],
            'scheduled_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'processing_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'sent_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'failed_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'last_error' => ['type' => 'TEXT', 'null' => true],
            'provider_message_id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('api_key_id', 'wa_api_keys', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('wa_message_queue', true);

        $this->forge->addField([
            'key' => ['type' => 'VARCHAR', 'constraint' => 100],
            'value' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('key');
        $this->forge->createTable('wa_settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('wa_settings', true);
        $this->forge->dropTable('wa_message_queue', true);
        $this->forge->dropTable('wa_api_keys', true);
    }
}
