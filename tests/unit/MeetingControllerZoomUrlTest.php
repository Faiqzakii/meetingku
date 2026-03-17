<?php

use App\Controllers\MeetingController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MeetingControllerZoomUrlTest extends CIUnitTestCase
{
    public function testExtractZoomMeetingIdFromUrl(): void
    {
        $reflection = new ReflectionClass(MeetingController::class);
        $controller = $reflection->newInstanceWithoutConstructor();

        $this->assertTrue(
            method_exists($controller, 'extractZoomMeetingIdFromUrl'),
            'Method extractZoomMeetingIdFromUrl() must exist in MeetingController',
        );

        $method = new ReflectionMethod($controller, 'extractZoomMeetingIdFromUrl');
        $method->setAccessible(true);

        $this->assertSame('12345678901', $method->invoke($controller, 'https://us02web.zoom.us/j/12345678901'));
        $this->assertSame('98765432109', $method->invoke($controller, 'https://zoom.us/wc/98765432109/join'));
        $this->assertNull($method->invoke($controller, 'https://example.com/not-zoom/12345678901'));
    }
}
