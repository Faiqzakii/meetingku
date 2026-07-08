<?php

use App\Controllers\MeetingController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FakeMeetingRequest
{
    /** @param array<string, string> $post */
    public function __construct(private readonly array $post)
    {
    }

    /** @return array<string, string>|string|null */
    public function getPost(?string $key = null)
    {
        if ($key === null) {
            return $this->post;
        }

        return $this->post[$key] ?? null;
    }
}

/**
 * @internal
 */
final class MeetingControllerTimeRangeTest extends CIUnitTestCase
{
    private function makeController(string $waktuMulai, string $durasi): MeetingController
    {
        $reflection = new ReflectionClass(MeetingController::class);
        /** @var MeetingController $controller */
        $controller = $reflection->newInstanceWithoutConstructor();

        $requestProperty = new ReflectionProperty($controller, 'request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($controller, new FakeMeetingRequest([
            'waktu_mulai' => $waktuMulai,
            'durasi'      => $durasi,
        ]));

        return $controller;
    }

    /** @return array{0: string, 1: string} */
    private function resolveTimeRange(MeetingController $controller): array
    {
        $method = new ReflectionMethod($controller, 'resolveTimeRange');
        $method->setAccessible(true);

        /** @var array{0: string, 1: string} $result */
        $result = $method->invoke($controller);

        return $result;
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function fullDayProvider(): array
    {
        return [
            'monday'    => ['2026-07-06 09:00:00', '2026-07-06 17:00:00'],
            'tuesday'   => ['2026-07-07 09:00:00', '2026-07-07 17:00:00'],
            'wednesday' => ['2026-07-08 09:00:00', '2026-07-08 17:00:00'],
            'thursday'  => ['2026-07-09 09:00:00', '2026-07-09 17:00:00'],
            'friday'    => ['2026-07-10 09:00:00', '2026-07-10 17:30:00'],
            'saturday'  => ['2026-07-11 09:00:00', '2026-07-11 17:30:00'],
            'sunday'    => ['2026-07-12 09:00:00', '2026-07-12 17:30:00'],
        ];
    }

    /** @dataProvider fullDayProvider */
    public function testFullDayEndTimeUsesBusinessCloseTime(string $startTime, string $expectedEndTime): void
    {
        [$waktuMulai, $waktuSelesai] = $this->resolveTimeRange($this->makeController($startTime, 'Penuh'));

        $this->assertSame($startTime, $waktuMulai);
        $this->assertSame($expectedEndTime, $waktuSelesai);
    }

    public function testNumericDurationStillAddsMinutes(): void
    {
        [$waktuMulai, $waktuSelesai] = $this->resolveTimeRange($this->makeController('2026-07-10 14:15:00', '30'));

        $this->assertSame('2026-07-10 14:15:00', $waktuMulai);
        $this->assertSame('2026-07-10 14:45:00', $waktuSelesai);
    }
}
