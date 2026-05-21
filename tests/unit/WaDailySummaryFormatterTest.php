<?php

use App\Libraries\WaDailySummaryFormatter;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class WaDailySummaryFormatterTest extends CIUnitTestCase
{
    public function testFormatWithMeetingsListsTodayAgenda(): void
    {
        $message = WaDailySummaryFormatter::format('2026-05-21', [
            ['nama_keg' => 'Rapat Pimpinan', 'waktu_mulai' => '2026-05-21 08:00:00', 'waktu_selesai' => '2026-05-21 09:30:00', 'nama_ruangan' => 'Ruang A'],
            ['nama_keg' => 'Evaluasi', 'waktu_mulai' => '2026-05-21 10:00:00', 'waktu_selesai' => '2026-05-21 11:00:00', 'nama_ruangan' => 'Ruang B'],
        ]);

        $this->assertStringContainsString('Agenda kegiatan hari ini', $message);
        $this->assertStringContainsString('Kamis, 21 Mei 2026', $message);
        $this->assertStringContainsString('1. 08:00-09:30 — Rapat Pimpinan', $message);
        $this->assertStringContainsString('📍 Ruang A', $message);
        $this->assertStringContainsString('2. 10:00-11:00 — Evaluasi', $message);
    }

    public function testFormatWithoutMeetingsReturnsEmptyAgendaMessage(): void
    {
        $message = WaDailySummaryFormatter::format('2026-05-21', []);

        $this->assertStringContainsString('Tidak ada kegiatan terjadwal hari ini.', $message);
    }
}
