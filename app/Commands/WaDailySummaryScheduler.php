<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WaDailySummaryScheduler extends BaseCommand
{
    protected $group = 'WhatsApp';
    protected $name = 'wa:daily-summary-scheduler';
    protected $description = 'Run daily summary scheduler loop at 15:00 server time (sends tomorrow agenda).';

    public function run(array $params)
    {
        $sleep = max(10, (int) (CLI::getOption('sleep') ?? 60));
        $time = (string) (CLI::getOption('time') ?? '15:00');

        do {
            if (date('H:i') === $time) {
                command('wa:daily-summary');
            }

            sleep($sleep);
        } while (true);
    }
}
