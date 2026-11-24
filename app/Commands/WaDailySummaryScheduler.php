<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use DateTimeImmutable;
use RuntimeException;

class WaDailySummaryScheduler extends BaseCommand
{
    protected $group = 'WhatsApp';
    protected $name = 'wa:daily-summary-scheduler';
    protected $description = 'Queue tomorrow’s agenda at or after 15:00 server time each day.';
    protected $options = [
        '--time' => 'Earliest send time each day (24-hour HH:MM, default: 15:00).',
        '--sleep' => 'Seconds between checks (minimum: 10, default: 60).',
    ];

    public function run(array $params): int
    {
        $sleep = max(10, (int) ($params['sleep'] ?? 60));
        $time = (string) ($params['time'] ?? '15:00');
        if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $time)) {
            CLI::error('Use a 24-hour HH:MM value for --time.');

            return EXIT_ERROR;
        }

        $summary = new WaDailySummary($this->logger, $this->commands);
        $lastRunDay = null;
        do {
            $now = new DateTimeImmutable();
            $today = $now->format('Y-m-d');
            if ($lastRunDay !== $today && $now->format('H:i') >= $time) {
                try {
                    $summary->enqueue($now);
                    $lastRunDay = $today;
                } catch (RuntimeException $e) {
                    CLI::error('Could not queue the daily WhatsApp summary; will retry.');
                    $this->logger->error('Could not queue the daily WhatsApp summary: {exception}', ['exception' => $e]);
                }
            }

            sleep($sleep);
        } while (true);
    }
}
