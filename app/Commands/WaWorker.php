<?php

namespace App\Commands;

use App\Libraries\WaSenderClient;
use App\Libraries\WaRetrySchedule;
use App\Models\WaMessageQueueModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WaWorker extends BaseCommand
{
    protected $group = 'WhatsApp';
    protected $name = 'wa:worker';
    protected $description = 'Process WhatsApp database queue.';

    public function run(array $params)
    {
        $once = CLI::getOption('once') !== null;
        $limit = (int) (CLI::getOption('limit') ?? 10);
        $sleep = (int) (CLI::getOption('sleep') ?? 3);

        do {
            $processed = $this->processBatch(max(1, $limit));
            CLI::write('Processed: ' . $processed);

            if ($once) {
                break;
            }

            sleep(max(1, $sleep));
        } while (true);
    }

    private function processBatch(int $limit): int
    {
        $db = db_connect();
        $queueModel = new WaMessageQueueModel();
        $client = new WaSenderClient();
        $processed = 0;

        $db->transStart();
        if ($db->DBDriver === 'Postgre') {
            $rows = $db->query(
                'SELECT * FROM wa_message_queue WHERE status = ? AND scheduled_at <= NOW() ORDER BY id ASC FOR UPDATE SKIP LOCKED LIMIT ?',
                ['pending', $limit]
            )->getResultArray();
        } else {
            $rows = $db->table('wa_message_queue')
                ->where('status', 'pending')
                ->where('scheduled_at <=', date('Y-m-d H:i:s'))
                ->orderBy('id', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        }

        foreach ($rows as $row) {
            $queueModel->update($row['id'], [
                'status' => 'processing',
                'processing_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $db->transComplete();

        foreach ($rows as $row) {
            try {
                $result = $client->sendMessage($row['to_number'], $row['message']);
                $queueModel->update($row['id'], [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'provider_message_id' => (string) ($result['messageId'] ?? ''),
                    'last_error' => null,
                ]);
            } catch (\Throwable $e) {
                $attempts = ((int) $row['attempts']) + 1;
                $failed = $attempts >= (int) $row['max_attempts'];
                $queueModel->update($row['id'], [
                    'status' => $failed ? 'failed' : 'pending',
                    'attempts' => $attempts,
                    'failed_at' => $failed ? date('Y-m-d H:i:s') : null,
                    'scheduled_at' => $failed ? $row['scheduled_at'] : WaRetrySchedule::nextScheduledAt($attempts),
                    'last_error' => $e->getMessage(),
                ]);
            }
            $processed++;
        }

        return $processed;
    }
}
