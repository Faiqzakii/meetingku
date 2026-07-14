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

    public function testBuildManualZoomUpdateDataUsesSharedZoomSourceButKeepsOwnToken(): void
    {
        $reflection = new ReflectionClass(MeetingController::class);
        $controller = $reflection->newInstanceWithoutConstructor();

        $method = new ReflectionMethod($controller, 'buildManualZoomUpdateData');
        $method->setAccessible(true);

        $result = $method->invoke(
            $controller,
            'https://zoom.us/j/12345678901',
            '12345678901',
            ['id' => 20, 'start_token' => 'breakout-token'],
            [
                'id' => 10,
                'zoom_meeting_id' => '12345678901',
                'zoom_join_url' => 'https://zoom.us/j/12345678901?pwd=canonical',
                'start_token' => 'source-token',
            ],
        );

        $this->assertSame('https://zoom.us/j/12345678901?pwd=canonical', $result['zoom_join_url']);
        $this->assertSame('12345678901', $result['zoom_meeting_id']);
        $this->assertSame(10, $result['zoom_source_meeting_id']);
        $this->assertSame('breakout-token', $result['start_token']);
    }

    public function testBuildManualZoomUpdateDataCreatesTokenForNewManualZoom(): void
    {
        $reflection = new ReflectionClass(MeetingController::class);
        $controller = $reflection->newInstanceWithoutConstructor();

        $method = new ReflectionMethod($controller, 'buildManualZoomUpdateData');
        $method->setAccessible(true);

        $result = $method->invoke(
            $controller,
            'https://zoom.us/j/12345678901',
            '12345678901',
            ['id' => 20, 'start_token' => null],
            null,
        );

        $this->assertSame('https://zoom.us/j/12345678901', $result['zoom_join_url']);
        $this->assertSame('12345678901', $result['zoom_meeting_id']);
        $this->assertNull($result['zoom_source_meeting_id']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{20}$/', $result['start_token']);
    }

    public function testCalendarPayloadDoesNotExposeRawZoomStartUrl(): void
    {
        $calendarView = file_get_contents(ROOTPATH . 'app/Views/meeting/calendar.php');

        $this->assertIsString($calendarView);
        $this->assertStringNotContainsString("'zoom_start_url'", $calendarView);
    }
    public function testCalendarHostButtonUsesShortlinkInsteadOfRefreshEndpoint(): void
    {
        $calendarView = file_get_contents(ROOTPATH . 'app/Views/meeting/calendar.php');

        $this->assertIsString($calendarView);
        $this->assertStringContainsString("base_url('zoom/start/')", $calendarView);
        $this->assertStringNotContainsString("base_url('meeting/refresh-zoom/')", $calendarView);
    }
    public function testUpcomingUiUsesShortlinkInsteadOfRefreshEndpoint(): void
    {
        $upcomingView = file_get_contents(ROOTPATH . 'app/Views/meeting/upcoming.php');

        $this->assertIsString($upcomingView);
        $this->assertStringContainsString("base_url('zoom/start/' . \$meeting['start_token'])", $upcomingView);
        $this->assertStringNotContainsString('meeting/refresh-zoom/', $upcomingView);
        $this->assertStringNotContainsString('data-zoom-action="refresh"', $upcomingView);
    }


}
