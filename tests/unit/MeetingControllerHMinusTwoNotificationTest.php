<?php

use App\Controllers\MeetingController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MeetingControllerHMinusTwoNotificationTest extends CIUnitTestCase
{
    private function invokeSchedule(MeetingController $controller, string $waktuMulai, int $now): string
    {
        $method = new ReflectionMethod($controller, 'scheduleNewMeetingGroupNotification');
        $method->setAccessible(true);

        return $method->invoke($controller, $waktuMulai, $now);
    }

    private function freshController(): MeetingController
    {
        $reflection = new ReflectionClass(MeetingController::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function testMeetingFiveDaysAwaySchedulesAtHMinusTwoNineAm(): void
    {
        // Wed 2026-07-15 10:00:00 → H-2 09:00:00 = Mon 2026-07-13 09:00:00.
        $controller = $this->freshController();
        $now        = strtotime('2026-07-10 12:00:00');
        $result     = $this->invokeSchedule($controller, '2026-07-15 10:00:00', $now);

        $this->assertSame('2026-07-13 09:00:00', $result);
    }

    public function testMeetingTwoDaysAwayAtNineAmSchedulesAtHMinusTwoNineAm(): void
    {
        // Mon 2026-07-13 09:00:00 → H-2 09:00:00 = Sat 2026-07-11 09:00:00.
        $controller = $this->freshController();
        $now        = strtotime('2026-07-09 08:00:00');
        $result     = $this->invokeSchedule($controller, '2026-07-13 09:00:00', $now);

        $this->assertSame('2026-07-11 09:00:00', $result);
    }

    public function testMeetingAlreadyInsideHMinusTwoWindowCollapsesToNow(): void
    {
        // H-2 already passed (now=H-1) → schedule = now, not the past H-2.
        $controller = $this->freshController();
        $now        = strtotime('2026-07-14 10:00:00'); // 1 day before 2026-07-15 10:00
        $result     = $this->invokeSchedule($controller, '2026-07-15 10:00:00', $now);

        $this->assertSame('2026-07-14 10:00:00', $result);
    }

    public function testMeetingTodayCollapsesToNow(): void
    {
        $controller = $this->freshController();
        $now        = strtotime('2026-07-15 07:00:00'); // 3h before 10:00
        $result     = $this->invokeSchedule($controller, '2026-07-15 10:00:00', $now);

        $this->assertSame('2026-07-15 07:00:00', $result);
    }

    public function testHMinusTwoScheduleIsAlwaysNoEarlierThanHMinusTwoNineAm(): void
    {
        // Now equals H-2 09:00 exactly → schedule = now, not later.
        $meetingTs = strtotime('2026-07-15 10:00:00');
        $hMinusTwo = strtotime('-2 days', strtotime(date('Y-m-d 09:00:00', $meetingTs)));

        $controller = $this->freshController();
        $result     = $this->invokeSchedule($controller, '2026-07-15 10:00:00', $hMinusTwo);

        $this->assertSame(date('Y-m-d H:i:s', $hMinusTwo), $result);
    }
}
