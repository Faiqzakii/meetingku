<?php

namespace App\Commands;

use App\Libraries\WaDailySummaryFormatter;
use App\Models\MeetingModel;
use App\Models\WaMessageQueueModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use DateTimeImmutable;

class WaDailySummary extends BaseCommand
{
    private const DEFAULT_GROUP_ID = '120363425375670792@g.us';

    protected $group = 'WhatsApp';
    protected $name = 'wa:daily-summary';
    protected $description = 'Enqueue daily meeting summary to WhatsApp group once per day.';
    protected $options = [
        '--date' => 'Agenda date (defaults to tomorrow).',
        '--group' => 'WhatsApp group ID (defaults to WA_DAILY_SUMMARY_GROUP_ID).',
        '--force' => 'Always enqueue an extra summary and count today as queued for unforced runs.',
    ];

    public function run(array $params): int
    {
        return $this->enqueue(
            new DateTimeImmutable(),
            $params['date'] ?? null,
            $params['group'] ?? null,
            array_key_exists('force', $params),
        );
    }

    public function enqueue(
        DateTimeImmutable $now,
        ?string $date = null,
        ?string $groupId = null,
        bool $force = false,
    ): int {
        $sendDay = $now->format('Y-m-d');
        $date ??= $now->modify('+1 day')->format('Y-m-d');
        $groupId ??= (string) env('WA_DAILY_SUMMARY_GROUP_ID', self::DEFAULT_GROUP_ID);
        $queue = new WaMessageQueueModel();
        if (!$force && $queue->hasDailySummary($sendDay)) {
            CLI::write('Daily summary already queued today (' . $sendDay . ')');

            return EXIT_SUCCESS;
        }

        $meetings = array_values(array_filter(
            (new MeetingModel())->getMeetingsByDateRange($date . ' 00:00:00', $date . ' 23:59:59'),
            static fn (array $meeting): bool => !in_array((string) ($meeting['status'] ?? ''), ['rejected', 'cancelled'], true),
        ));

        $queueId = $queue->enqueueDailySummary($sendDay, $groupId, WaDailySummaryFormatter::format($date, $meetings), $force);
        CLI::write($queueId === null
            ? 'Daily summary already queued today (' . $sendDay . ')'
            : 'Daily summary queued: ' . $queueId);

        return EXIT_SUCCESS;
    }
}
