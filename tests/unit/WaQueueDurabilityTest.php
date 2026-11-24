<?php

use App\Commands\WaWorker;
use App\Database\Migrations\HardenWaQueue;
use App\Libraries\GowaClient;
use App\Models\WaMessageQueueModel;
use CodeIgniter\Database\Query;
use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Database\SQLite3\Forge;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;

/**
 * @internal
 */
final class WaQueueDurabilityTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private const GROUP_ID = '120363425375670792@g.us';

    private ?string $path = null;
    private Connection $queueDb;
    private Connection $peerDb;
    private HardenWaQueue $migration;
    private WaMessageQueueModel $queue;
    private WaMessageQueueModel $peerQueue;

    protected function setUp(): void
    {
        parent::setUp();
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped('The SQLite3 PHP extension is required for queue integration tests.');
        }

        $path = tempnam(sys_get_temp_dir(), 'wa-queue-');
        $this->assertNotFalse($path);
        $this->path = $path;
        $config = ['database' => $path, 'DBPrefix' => 'test_', 'DBDebug' => true, 'busyTimeout' => 500];
        $this->queueDb = new Connection($config);
        $this->peerDb = new Connection($config);
        $this->queue = new WaMessageQueueModel($this->queueDb);
        $this->peerQueue = new WaMessageQueueModel($this->peerDb);

        require_once APPPATH . 'Database/Migrations/2026-11-24-000002_HardenWaQueue.php';
        $this->migration = new HardenWaQueue(new Forge($this->queueDb));
        $this->queueDb->query(<<<'SQL'
            CREATE TABLE test_wa_message_queue (
                id INTEGER PRIMARY KEY AUTOINCREMENT, to_number VARCHAR(32) NOT NULL, message TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending', attempts INTEGER NOT NULL DEFAULT 0,
                max_attempts INTEGER NOT NULL DEFAULT 3, scheduled_at TIMESTAMP, processing_at TIMESTAMP,
                sent_at TIMESTAMP, failed_at TIMESTAMP, last_error TEXT, provider_message_id VARCHAR(128),
                created_at TIMESTAMP, updated_at TIMESTAMP
            )
            SQL);
        $this->queueDb->query('CREATE TABLE test_wa_settings (`key` TEXT PRIMARY KEY, value TEXT, created_at TIMESTAMP, updated_at TIMESTAMP)');
    }

    protected function tearDown(): void
    {
        if ($this->path !== null) {
            $this->peerDb->close();
            $this->queueDb->close();
            unlink($this->path);
        }
        parent::tearDown();
    }

    public function testWorkerSendsOnlyAfterCommitAndDoesNotClaimTheRestOfItsBatch(): void
    {
        $this->migration->up();
        $first = $this->insert(['message' => 'First']);
        $second = $this->insert(['message' => 'Second']);
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->exactly(2))->method('request')->willReturnCallback(function ($method, $url, $options) use ($first, $second): Response {
            $this->assertSame('POST', $method);
            $this->assertSame('https://gowa.example.test/send/message', $url);
            $this->assertSame('Meetingku', $options['headers']['X-Device-Id']);
            if ($options['json']['message'] === 'First') {
                $this->assertSame('processing', $this->peerQueue->find($first)['status']);
                $this->assertSame('pending', $this->peerQueue->find($second)['status']);
            } else {
                $this->assertSame('sent', $this->peerQueue->find($first)['status']);
                $this->assertSame('processing', $this->peerQueue->find($second)['status']);
            }

            return $this->response(200, ['code' => 'SUCCESS', 'results' => ['message_id' => 'provider-id']]);
        });
        Services::injectMock('curlrequest', $http);

        $this->assertSame(2, $this->processBatch(20));
        $this->assertSame('sent', $this->queue->find($second)['status']);
        $this->assertSame('provider-id', $this->queue->find($second)['provider_message_id']);
    }

    public function testOtherWorkerWinsAClaimBetweenSelectionAndUpdate(): void
    {
        $this->queueDb->query('PRAGMA journal_mode = WAL')->freeResult();
        $this->migration->up();
        $first = $this->insert();
        $second = $this->insert();
        $peerClaim = null;
        $interleaved = false;
        $listener = function (Query $query) use (&$peerClaim, &$interleaved): void {
            if (!$interleaved && str_contains($query->getQuery(), 'SELECT `id`') && str_contains($query->getQuery(), 'test_wa_message_queue')) {
                $interleaved = true;
                $peerClaim = $this->peerQueue->claimNext();
            }
        };
        Events::on('DBQuery', $listener);
        try {
            $claim = $this->queue->claimNext();
        } finally {
            Events::removeListener('DBQuery', $listener);
        }

        $this->assertSame($first, (int) $peerClaim['id']);
        $this->assertSame($second, (int) $claim['id']);
        $this->assertSame($peerClaim['claim_token'], $this->queue->find($first)['claim_token']);
    }

    public function testClaimCannotBeReturnedFromAnOuterTransaction(): void
    {
        $this->migration->up();
        $id = $this->insert();
        $this->assertTrue($this->queueDb->transBegin());
        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('standalone WhatsApp queue transaction');
            $this->queue->claimNext();
        } finally {
            $this->assertTrue($this->queueDb->transRollback());
            $this->assertSame('pending', $this->peerQueue->find($id)['status']);
        }
    }

    public function testExpiredAndLegacyClaimsAreQuarantinedWithoutRequeueingAndOldClaimIsFenced(): void
    {
        $legacyId = $this->insert(['status' => 'processing', 'processing_at' => date('Y-m-d H:i:s')]);
        $this->migration->up();
        $id = $this->insert(['attempts' => 1]);
        $pastClaim = $this->queue->claimNext();
        $futureId = $this->insert(['scheduled_at' => date('Y-m-d H:i:s', time() + 300)]);

        $this->assertSame($id, (int) $pastClaim['id']);
        $this->assertSame(1, $this->peerQueue->quarantineExpiredClaims());
        $this->assertSame('needs_review', $this->queue->find($legacyId)['status']);
        $this->assertSame('processing', $this->queue->find($id)['status']);
        $this->assertFalse($this->queue->reconcile($id, false));
        $this->assertSame(1, $this->peerQueue->quarantineExpiredClaims(time() + WaMessageQueueModel::CLAIM_TIMEOUT_SECONDS));
        $this->assertNull($this->peerQueue->claimNext());
        $this->assertSame('pending', $this->peerQueue->find($futureId)['status']);
        $this->assertTrue($this->queue->reconcile($legacyId, true, 'confirmed-on-provider'));
        $this->assertSame('confirmed-on-provider', $this->queue->find($legacyId)['provider_message_id']);
        $this->assertFalse($this->queue->reconcile($legacyId, false));

        $this->assertTrue($this->peerQueue->reconcile($id, false));
        $newClaim = $this->peerQueue->claimNext();
        $this->assertSame($id, (int) $newClaim['id']);
        $this->assertSame(1, (int) $newClaim['attempts']);
        $this->assertNotSame($pastClaim['claim_token'], $newClaim['claim_token']);
        $this->assertFalse($this->queue->finishClaim($pastClaim, ['status' => 'sent']));
        $this->assertTrue($this->peerQueue->finishClaim($newClaim, ['status' => 'sent']));
    }

    public function testProviderRejectionBacksOffButAmbiguousResponseRequiresManualReview(): void
    {
        $this->migration->up();
        $retry = $this->insert();
        $final = $this->insert(['attempts' => 2]);
        $uncertain = $this->insert();
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->exactly(3))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(429),
            $this->response(400),
            $this->response(504),
        );
        Services::injectMock('curlrequest', $http);

        $before = time();
        $this->assertSame(3, $this->processBatch(20));
        $row = $this->queue->find($retry);
        $this->assertSame('pending', $row['status']);
        $this->assertSame(1, (int) $row['attempts']);
        $this->assertGreaterThanOrEqual($before + 60, strtotime($row['scheduled_at']));
        $this->assertLessThanOrEqual(time() + 60, strtotime($row['scheduled_at']));
        $this->assertSame('failed', $this->queue->find($final)['status']);
        $this->assertSame(3, (int) $this->queue->find($final)['attempts']);
        $this->assertSame('needs_review', $this->queue->find($uncertain)['status']);
        $this->assertSame(0, (int) $this->queue->find($uncertain)['attempts']);
        $this->assertNull($this->queue->claimNext());
    }

    public function testFailureToRecordSentOutcomeIsNotTreatedAsAProviderRejection(): void
    {
        $this->migration->up();
        $first = $this->insert();
        $second = $this->insert();
        $this->queueDb->query(<<<'SQL'
            CREATE TRIGGER fail_sending_confirmation BEFORE UPDATE ON test_wa_message_queue
            WHEN NEW.status = 'sent' BEGIN SELECT RAISE(ABORT, 'status store unavailable'); END
            SQL);
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->once())->method('request')->willReturn(
            $this->response(200, ['code' => 'SUCCESS', 'results' => ['message_id' => 'accepted']]),
        );
        Services::injectMock('curlrequest', $http);

        try {
            $this->processBatch(20);
            $this->fail('Expected the status update to fail.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('status store unavailable', $e->getMessage());
        }
        $this->assertSame('processing', $this->peerQueue->find($first)['status']);
        $this->assertSame('pending', $this->peerQueue->find($second)['status']);
        $this->assertSame(1, $this->peerQueue->quarantineExpiredClaims(time() + WaMessageQueueModel::CLAIM_TIMEOUT_SECONDS));
        $this->assertSame('needs_review', $this->queue->find($first)['status']);
    }

    public function testSummaryDedupeSurvivesConnectionsAndForcePreservesItsOriginalSemantics(): void
    {
        $day = date('Y-m-d');
        $nextDay = date('Y-m-d', strtotime('tomorrow'));
        $this->queueDb->table('wa_settings')->insert(['key' => 'wa_daily_summary_last_date', 'value' => $day]);
        $sent = $this->insert(['status' => 'sent', 'provider_message_id' => 'legacy-provider-id']);
        $this->migration->up();

        $this->assertTrue($this->queue->hasDailySummary($day));
        $this->assertNull($this->queue->enqueueDailySummary($day, self::GROUP_ID, 'Legacy already queued', false));
        $this->assertNotNull($this->queue->enqueueDailySummary($day, self::GROUP_ID, 'Forced legacy resend', true));
        $this->assertNotNull($this->peerQueue->enqueueDailySummary($nextDay, self::GROUP_ID, 'First run is forced', true));
        $this->assertNull($this->queue->enqueueDailySummary($nextDay, self::GROUP_ID, 'Scheduled re-run', false));
        $this->assertNotNull($this->queue->enqueueDailySummary($nextDay, self::GROUP_ID, 'Another forced run', true));
        $this->assertSame(4, $this->queue->countAll());
        $this->assertSame('legacy-provider-id', $this->queue->find($sent)['provider_message_id']);
        $indexes = array_column($this->queueDb->query('PRAGMA index_list(test_wa_message_queue)')->getResultArray(), 'name');
        $this->assertContains('test_wa_queue_due', $indexes);
        $this->assertContains('test_wa_queue_processing', $indexes);

        $this->migration->down();
        $this->assertSame($nextDay, $this->queueDb->table('wa_settings')->get()->getRowArray()['value']);
        $this->assertSame(4, $this->queue->countAll());
        $this->assertSame('legacy-provider-id', $this->queue->find($sent)['provider_message_id']);
    }

    public function testFailedEnqueueRollsBackReservationSoAnotherSchedulerCanEnqueue(): void
    {
        $this->migration->up();
        $day = date('Y-m-d');
        $this->queueDb->query(<<<'SQL'
            CREATE TRIGGER fail_summary_insert BEFORE INSERT ON test_wa_message_queue
            BEGIN SELECT RAISE(ABORT, 'queue unavailable'); END
            SQL);
        try {
            $this->queue->enqueueDailySummary($day, self::GROUP_ID, 'Rolled back', false);
            $this->fail('Expected queue insertion to fail.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('daily WhatsApp summary', $e->getMessage());
        }
        $this->assertFalse($this->peerQueue->hasDailySummary($day));
        $this->assertSame(0, $this->peerQueue->countAll());
        $this->queueDb->query('DROP TRIGGER fail_summary_insert');
        $this->assertNotNull($this->peerQueue->enqueueDailySummary($day, self::GROUP_ID, 'Retry', false));
        $this->assertNull($this->queue->enqueueDailySummary($day, self::GROUP_ID, 'Duplicate', false));
        $this->assertSame(1, $this->queue->countAll());
    }

    private function insert(array $changes = []): int
    {
        return (int) $this->queue->insert($changes + [
            'to_number' => self::GROUP_ID,
            'message' => 'Meeting reminder',
            'status' => 'pending',
            'scheduled_at' => '2026-01-01 09:00:00',
        ]);
    }

    private function processBatch(int $limit): int
    {
        $worker = new WaWorker(service('logger'), service('commands'));
        $client = new GowaClient('https://gowa.example.test', 'test-user', 'test-password', 'Meetingku');

        return (new ReflectionMethod($worker, 'processBatch'))->invoke($worker, $limit, $this->queue, $client);
    }

    private function response(int $status, array $data = []): Response
    {
        return (new Response(config('App')))->setStatusCode($status)->setBody(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
