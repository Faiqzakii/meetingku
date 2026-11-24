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
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['start_token']);
    }

    public function testCalendarOnlyProjectsHostLinkForOwnerOrAdmin(): void
    {
        $controller = (new ReflectionClass(MeetingController::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($controller, 'calendarEvent');
        $meeting = [
            'id' => 1,
            'nama_keg' => 'Rapat tim',
            'nama_ruangan' => 'Studio',
            'tipe' => 'Online',
            'status' => 'approved',
            'nama_pegawai' => 'Pemohon',
            'pegawai_id' => 25,
            'jumlah_peserta' => 5,
            'waktu_mulai' => '2026-11-24 08:00:00',
            'waktu_selesai' => '2026-11-24 09:00:00',
            'zoom_meeting_id' => '12345678901',
            'zoom_join_url' => 'https://zoom.us/j/12345678901',
            'zoom_start_url' => 'https://zoom.us/s/12345678901',
            'start_token' => 'private-host-token',
            'status_changed_by_name' => 'Administrator',
            'status_changed_at' => null,
            'last_edited_by_name' => null,
            'last_edited_at' => null,
            'created_at' => null,
        ];

        foreach ([null, 42] as $viewerId) {
            $event = $method->invoke($controller, $meeting, $viewerId, false);
            $this->assertSame('Rapat tim', $event['title']);
            $this->assertArrayNotHasKey('zoomHostUrl', $event['extendedProps']);
            $this->assertArrayNotHasKey('zoom_join_url', $event['extendedProps']);
        }
        foreach ([[25, false], [42, true]] as [$viewerId, $admin]) {
            $event = $method->invoke($controller, $meeting, $viewerId, $admin);
            $this->assertSame(base_url('zoom/start/private-host-token'), $event['extendedProps']['zoomHostUrl']);
            $this->assertArrayNotHasKey('zoom_start_url', $event['extendedProps']);
        }
    }


}
