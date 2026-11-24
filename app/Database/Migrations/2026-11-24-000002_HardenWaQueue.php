<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class HardenWaQueue extends Migration
{
    private const QUEUE = 'wa_message_queue';
    private const RUNS = 'wa_daily_summary_runs';
    private const LEGACY_MARKER = 'wa_daily_summary_last_date';

    public function up()
    {
        $mysql = $this->db->DBDriver === 'MySQLi';
        if ($mysql) {
            $queue = $this->db->prefixTable(self::QUEUE);
            $engine = $this->db->query(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$queue],
            )->getRowArray();
            if (strtoupper($engine['ENGINE'] ?? '') !== 'INNODB'
                && !$this->db->query('ALTER TABLE ' . $this->db->escapeIdentifiers($queue) . ' ENGINE = InnoDB')) {
                throw new RuntimeException('Could not make the WhatsApp queue transactional.');
            }
        }

        if (!$this->forge->addColumn(self::QUEUE, [
            'claim_token' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
        ])) {
            throw new RuntimeException('Could not add the WhatsApp queue claim token.');
        }
        $this->forge->addKey(['status', 'scheduled_at', 'id'], false, false, $this->db->prefixTable('wa_queue_due'));
        $this->forge->addKey(['status', 'processing_at'], false, false, $this->db->prefixTable('wa_queue_processing'));
        if (!$this->forge->processIndexes(self::QUEUE)) {
            throw new RuntimeException('Could not add WhatsApp queue indexes.');
        }

        $this->forge->addField([
            'send_day' => ['type' => 'DATE'],
            'queue_id' => ['type' => 'BIGINT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('send_day');
        if (!$this->forge->createTable(self::RUNS, false, $mysql ? ['ENGINE' => 'InnoDB'] : [])) {
            throw new RuntimeException('Could not create daily WhatsApp summary reservations.');
        }

        $legacy = $this->db->table('wa_settings')->select('value')
            ->where('key', self::LEGACY_MARKER)->get()->getRowArray();
        $day = $legacy['value'] ?? '';
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $day, $parts)
            && checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])
            && !$this->db->table(self::RUNS)->insert(['send_day' => $day, 'queue_id' => 0])) {
            throw new RuntimeException('Could not preserve the legacy daily WhatsApp summary marker.');
        }
    }

    public function down()
    {
        $latest = $this->db->table(self::RUNS)->select('send_day')->orderBy('send_day', 'DESC')->get(1)->getRowArray();
        if ($latest !== null) {
            $settings = $this->db->table('wa_settings');
            $legacy = $settings->select('value')->where('key', self::LEGACY_MARKER)->get()->getRowArray();
            $now = date('Y-m-d H:i:s');
            $fields = ['value' => $latest['send_day'], 'updated_at' => $now];
            if ($legacy === null) {
                $saved = $settings->insert($fields + ['key' => self::LEGACY_MARKER, 'created_at' => $now]);
            } elseif ($legacy['value'] !== $latest['send_day']) {
                $saved = $settings->where('key', self::LEGACY_MARKER)->update($fields);
            } else {
                $saved = true;
            }
            if (!$saved) {
                throw new RuntimeException('Could not restore the legacy daily WhatsApp summary marker.');
            }
        }

        if (!$this->forge->dropKey(self::QUEUE, $this->db->prefixTable('wa_queue_due'), false)
            || !$this->forge->dropKey(self::QUEUE, $this->db->prefixTable('wa_queue_processing'), false)
            || !$this->forge->dropColumn(self::QUEUE, 'claim_token')
            || !$this->forge->dropTable(self::RUNS)) {
            throw new RuntimeException('Could not roll back the WhatsApp queue changes.');
        }
    }
}
