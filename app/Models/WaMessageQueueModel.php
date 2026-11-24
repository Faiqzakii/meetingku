<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use RuntimeException;

class WaMessageQueueModel extends Model
{
    public const CLAIM_TIMEOUT_SECONDS = 300;

    private const SUMMARY_RUNS_TABLE = 'wa_daily_summary_runs';

    protected $table = 'wa_message_queue';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'to_number', 'message', 'meta_kind', 'meta_meeting_id', 'status', 'attempts', 'max_attempts',
        'scheduled_at', 'processing_at', 'sent_at', 'failed_at', 'last_error', 'provider_message_id',
    ];

    public function claimNext(): ?array
    {
        while (true) {
            $now = date('Y-m-d H:i:s');
            $result = $this->db->table($this->table)->select('id')
                ->where('status', 'pending')->where('scheduled_at <=', $now)
                ->orderBy('scheduled_at', 'ASC')->orderBy('id', 'ASC')->get(1);
            if ($result === false) {
                throw new RuntimeException('Could not select the next WhatsApp queue item.');
            }
            $candidate = $result->getRowArray();
            if ($candidate === null) {
                return null;
            }

            $claim = $this->inTransaction(function () use ($candidate, $now): ?array {
                $claimed = $this->updateQueue(
                    $this->db->table($this->table)->where('id', $candidate['id'])
                        ->where('status', 'pending')->where('scheduled_at <=', $now),
                    [
                        'status' => 'processing',
                        'processing_at' => date('Y-m-d H:i:s'),
                        'claim_token' => bin2hex(random_bytes(16)),
                    ],
                );
                if ($claimed === 0) {
                    return null;
                }

                $result = $this->db->table($this->table)
                    ->select('id, to_number, message, attempts, max_attempts, scheduled_at, processing_at, claim_token')
                    ->where('id', $candidate['id'])->get();
                $row = $result ? $result->getRowArray() : null;
                if ($row === null) {
                    throw new RuntimeException('Could not read the claimed WhatsApp queue item.');
                }

                return $row;
            });
            if ($claim !== null) {
                return $claim;
            }
        }
    }

    public function finishClaim(array $claim, array $changes): bool
    {
        return $this->updateQueue(
            $this->db->table($this->table)->where('id', $claim['id'])
                ->where('status', 'processing')->where('claim_token', $claim['claim_token']),
            $changes + ['claim_token' => null],
        ) === 1;
    }

    public function quarantineExpiredClaims(?int $now = null): int
    {
        $cutoff = date('Y-m-d H:i:s', ($now ?? time()) - self::CLAIM_TIMEOUT_SECONDS);

        return $this->updateQueue(
            $this->db->table($this->table)->where('status', 'processing')->groupStart()
                ->where('processing_at <=', $cutoff)
                ->orWhere('processing_at', null)
                ->orWhere('claim_token', null)
                ->groupEnd(),
            [
                'status' => 'needs_review',
                'claim_token' => null,
                'last_error' => 'Worker claim expired; WhatsApp delivery outcome is unknown.',
            ],
        );
    }

    public function reconcile(int $id, bool $sent, ?string $providerId = null): bool
    {
        $now = date('Y-m-d H:i:s');
        $changes = $sent
            ? ['status' => 'sent', 'sent_at' => $now, 'last_error' => null]
            : ['status' => 'pending', 'scheduled_at' => $now, 'processing_at' => null, 'last_error' => null];
        if ($sent && $providerId !== null) {
            $changes['provider_message_id'] = $providerId;
        }

        return $this->updateQueue(
            $this->db->table($this->table)->where('id', $id)->where('status', 'needs_review'),
            $changes,
        ) === 1;
    }

    public function hasDailySummary(string $sendDay): bool
    {
        $result = $this->db->table(self::SUMMARY_RUNS_TABLE)->select('send_day')->where('send_day', $sendDay)->get(1);
        if ($result === false) {
            throw new RuntimeException('Could not check the daily WhatsApp summary.');
        }

        return $result->getRowArray() !== null;
    }

    public function enqueueDailySummary(string $sendDay, string $groupId, string $message, bool $force): int|string|null
    {
        $table = $this->db->escapeIdentifiers($this->db->prefixTable(self::SUMMARY_RUNS_TABLE));
        $onConflict = match ($this->db->DBDriver) {
            'MySQLi' => 'ON DUPLICATE KEY UPDATE send_day = send_day',
            'Postgre', 'SQLite3' => 'ON CONFLICT (send_day) DO NOTHING',
            default => throw new RuntimeException('Unsupported database driver for the daily WhatsApp summary.'),
        };

        return $this->inTransaction(function () use ($sendDay, $groupId, $message, $force, $table, $onConflict): int|string|null {
            // A null queue ID belongs to this transaction; zero records a migrated legacy marker.
            if ($this->db->query("INSERT INTO {$table} (send_day) VALUES (?) {$onConflict}", [$sendDay]) === false) {
                throw new RuntimeException('Could not reserve the daily WhatsApp summary.');
            }
            $result = $this->db->table(self::SUMMARY_RUNS_TABLE)->select('queue_id')->where('send_day', $sendDay)->get();
            $run = $result ? $result->getRowArray() : null;
            if ($run === null) {
                throw new RuntimeException('Could not read the daily WhatsApp summary reservation.');
            }
            if ($run['queue_id'] !== null && !$force) {
                return null;
            }

            $queueId = $this->insert([
                'to_number' => $groupId,
                'message' => $message,
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
                'scheduled_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$queueId) {
                throw new RuntimeException('Could not queue the daily WhatsApp summary.');
            }
            if ($run['queue_id'] === null && (!$this->db->table(self::SUMMARY_RUNS_TABLE)
                ->where('send_day', $sendDay)->where('queue_id', null)->update(['queue_id' => $queueId])
                || $this->db->affectedRows() !== 1)) {
                throw new RuntimeException('Could not queue the daily WhatsApp summary.');
            }

            return $queueId;
        });
    }

    private function updateQueue(BaseBuilder $builder, array $changes): int
    {
        $changes['updated_at'] = date('Y-m-d H:i:s');
        if (!$builder->update($changes)) {
            throw new RuntimeException('Could not update the WhatsApp queue.');
        }

        return $this->db->affectedRows();
    }

    private function inTransaction(callable $work): mixed
    {
        // A nested CI transaction can report success without committing anything.
        if ($this->db->transDepth !== 0 || !$this->db->transBegin()) {
            throw new RuntimeException('Could not start a standalone WhatsApp queue transaction.');
        }

        try {
            $result = $work();
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new RuntimeException('Could not commit the WhatsApp queue transaction.');
            }

            return $result;
        } finally {
            if ($this->db->transDepth > 0 && !$this->db->transRollback()) {
                throw new RuntimeException('Could not roll back the WhatsApp queue transaction.');
            }
            if (!$this->db->transStatus()) {
                $this->db->resetTransStatus();
            }
        }
    }
}
