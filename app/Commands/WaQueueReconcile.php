<?php

namespace App\Commands;

use App\Models\WaMessageQueueModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WaQueueReconcile extends BaseCommand
{
    protected $group = 'WhatsApp';
    protected $name = 'wa:queue-reconcile';
    protected $description = 'Inspect and resolve messages with an unknown WhatsApp delivery outcome.';
    protected $usage = 'wa:queue-reconcile [list | show <id> | confirm-sent <id> | confirm-unsent-and-requeue <id>]';
    protected $arguments = [
        'list' => 'List up to 50 messages requiring review (default).',
        'show <id>' => 'Show recipient, message, and last attempt for a row requiring review.',
        'confirm-sent <id>' => 'Use only after verifying that the message was sent.',
        'confirm-unsent-and-requeue <id>' => 'Stop the workers first. Use only after verifying that the message was not sent.',
    ];
    protected $options = [
        '--after' => 'Continue listing after the supplied queue ID.',
        '--provider-id' => 'Provider message ID, if known, for confirm-sent.',
    ];

    public function run(array $params): int
    {
        $action = $params[0] ?? 'list';
        $queue = new WaMessageQueueModel();
        if ($action === 'list') {
            $after = filter_var($params['after'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($after === false || (array_key_exists('after', $params) && $params['after'] === null)) {
                CLI::error('Pass a queue ID with --after.');

                return EXIT_ERROR;
            }

            $queue->quarantineExpiredClaims();
            $rows = $queue->select('id, to_number, processing_at, last_error')->where('status', 'needs_review')
                ->where('id >', $after)->orderBy('id', 'ASC')->findAll(50);
            if ($rows === []) {
                CLI::write('No WhatsApp messages require reconciliation in this range.');
            }
            foreach ($rows as $row) {
                CLI::write(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));
            }

            return EXIT_SUCCESS;
        }

        $id = filter_var($params[1] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !in_array($action, ['show', 'confirm-sent', 'confirm-unsent-and-requeue'], true)) {
            $this->showHelp();

            return EXIT_ERROR;
        }

        $queue->quarantineExpiredClaims();
        if ($action === 'show') {
            $row = $queue->select('id, to_number, message, processing_at, attempts, last_error')
                ->where('status', 'needs_review')->find($id);
            if ($row !== null) {
                CLI::write(json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));

                return EXIT_SUCCESS;
            }
        } else {
            $sent = $action === 'confirm-sent';
            $providerId = $params['provider-id'] ?? null;
            if (array_key_exists('provider-id', $params) && (!$sent || !is_string($providerId)
                || trim($providerId) === '' || strlen($providerId) > 128)) {
                CLI::error('Pass a nonempty provider ID (up to 128 bytes) only with confirm-sent.');

                return EXIT_ERROR;
            }
            if ($queue->reconcile($id, $sent, $providerId)) {
                CLI::write($sent ? "Marked {$id} as sent." : "Requeued {$id}; it will be sent when the worker resumes.");

                return EXIT_SUCCESS;
            }
        }

        CLI::error("Queue item {$id} is not in the needs_review state.");

        return EXIT_ERROR;
    }
}
