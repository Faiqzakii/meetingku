<?php

namespace App\Commands;

use App\Libraries\GowaClient;
use App\Libraries\GowaDeliveryUncertain;
use App\Libraries\WaRetrySchedule;
use App\Models\WaMessageQueueModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;

class WaWorker extends BaseCommand
{
    protected $group = 'WhatsApp';
    protected $name = 'wa:worker';
    protected $description = 'Process WhatsApp database queue.';
    protected $options = [
        '--once' => 'Process at most one batch and exit.',
        '--limit' => 'Maximum messages to claim in one batch (default: 10).',
        '--sleep' => 'Seconds between batches (default: 3).',
    ];

    public function run(array $params)
    {
        $once = array_key_exists('once', $params);
        $limit = max(1, (int) ($params['limit'] ?? 10));
        $sleep = max(1, (int) ($params['sleep'] ?? 3));
        $queue = new WaMessageQueueModel();
        $client = new GowaClient();

        do {
            $processed = $this->processBatch($limit, $queue, $client);
            CLI::write('Processed: ' . $processed);

            if ($once) {
                break;
            }

            sleep($sleep);
        } while (true);
    }

    private function processBatch(int $limit, WaMessageQueueModel $queue, GowaClient $client): int
    {
        $expired = $queue->quarantineExpiredClaims();
        if ($expired > 0) {
            CLI::error("WhatsApp messages requiring reconciliation: {$expired}. Run wa:queue-reconcile list.");
        }

        $processed = 0;
        while ($processed < $limit && ($claim = $queue->claimNext()) !== null) {
            if (strtotime($claim['processing_at']) <= time() - WaMessageQueueModel::CLAIM_TIMEOUT_SECONDS) {
                $queue->quarantineExpiredClaims();
                break;
            }

            // Only the provider call can trigger a retry, never a failure to record its result.
            try {
                $result = $client->sendMessage($claim['to_number'], $claim['message']);
                $changes = [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'provider_message_id' => $result['id'],
                    'last_error' => null,
                ];
            } catch (GowaDeliveryUncertain $e) {
                $changes = [
                    'status' => 'needs_review',
                    'last_error' => 'WhatsApp delivery outcome is unknown. ' . $e->getMessage(),
                ];
            } catch (RuntimeException $e) {
                $attempts = (int) $claim['attempts'] + 1;
                $failed = $attempts >= (int) $claim['max_attempts'];
                $changes = [
                    'status' => $failed ? 'failed' : 'pending',
                    'attempts' => $attempts,
                    'failed_at' => $failed ? date('Y-m-d H:i:s') : null,
                    'scheduled_at' => $failed ? $claim['scheduled_at'] : WaRetrySchedule::nextScheduledAt($attempts),
                    'last_error' => $e->getMessage(),
                ];
            }

            if (!$queue->finishClaim($claim, $changes) || $changes['status'] === 'needs_review') {
                CLI::error("Check WhatsApp queue item {$claim['id']}. Run wa:queue-reconcile show {$claim['id']}.");
            }
            $processed++;
        }

        return $processed;
    }
}
