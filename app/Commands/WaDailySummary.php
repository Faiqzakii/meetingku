<?php

namespace App\Commands;

use App\Libraries\WaDailySummaryFormatter;
use App\Models\MeetingModel;
use App\Models\WaMessageQueueModel;
use App\Models\WaSettingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WaDailySummary extends BaseCommand
{
    private const DEFAULT_GROUP_ID = '120363425375670792@g.us';

    protected $group = 'WhatsApp';
    protected $name = 'wa:daily-summary';
    protected $description = 'Enqueue daily meeting summary to WhatsApp group once per day.';

    public function run(array $params)
    {
        $force = CLI::getOption('force') !== null;
        $date = (string) (CLI::getOption('date') ?? date('Y-m-d'));
        $groupId = (string) (CLI::getOption('group') ?? env('WA_DAILY_SUMMARY_GROUP_ID', self::DEFAULT_GROUP_ID));
        $settingKey = 'wa_daily_summary_last_date';
        $settingModel = new WaSettingModel();
        $lastSent = $settingModel->find($settingKey)['value'] ?? null;

        if (!$force && $lastSent === $date) {
            CLI::write('Daily summary already queued for ' . $date);
            return;
        }

        $meetings = array_values(array_filter((new MeetingModel())->getMeetingsByDateRange($date . ' 00:00:00', $date . ' 23:59:59'), static function (array $meeting): bool {
            return !in_array((string) ($meeting['status'] ?? ''), ['rejected', 'cancelled'], true);
        }));

        $message = WaDailySummaryFormatter::format($date, $meetings);
        $queueId = (new WaMessageQueueModel())->insert([
            'api_key_id' => null,
            'to_number' => $groupId,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'scheduled_at' => date('Y-m-d H:i:s'),
        ], true);

        $settingModel->save([
            'key' => $settingKey,
            'value' => $date,
        ]);

        CLI::write('Daily summary queued: ' . $queueId);
    }
}
