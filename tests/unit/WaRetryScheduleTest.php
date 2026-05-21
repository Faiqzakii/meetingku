<?php

use App\Libraries\WaRetrySchedule;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class WaRetryScheduleTest extends CIUnitTestCase
{
    public function testDelaySecondsUsesIncrementalSchedule(): void
    {
        $this->assertSame(60, WaRetrySchedule::delaySecondsForAttempt(1));
        $this->assertSame(600, WaRetrySchedule::delaySecondsForAttempt(2));
        $this->assertSame(1800, WaRetrySchedule::delaySecondsForAttempt(3));
        $this->assertSame(1800, WaRetrySchedule::delaySecondsForAttempt(99));
    }
}
