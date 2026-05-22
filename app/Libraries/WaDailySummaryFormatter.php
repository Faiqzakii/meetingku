<?php

namespace App\Libraries;

use IntlDateFormatter;

final class WaDailySummaryFormatter
{
    public static function format(string $date, array $meetings): string
    {
        $titleDate = self::formatDate($date);
        $lines = [
            '📋 Agenda kegiatan besok',
            $titleDate,
            '',
        ];

        if ($meetings === []) {
            $lines[] = 'Tidak ada kegiatan terjadwal besok.';

            return implode("\n", $lines);
        }

        foreach (array_values($meetings) as $index => $meeting) {
            $start = date('H:i', strtotime((string) $meeting['waktu_mulai']));
            $end = date('H:i', strtotime((string) $meeting['waktu_selesai']));
            $name = trim((string) ($meeting['nama_keg'] ?? '-'));
            $room = trim((string) ($meeting['nama_ruangan'] ?? '-'));
            $status = trim((string) ($meeting['status'] ?? ''));
            $statusText = $status !== '' ? ' [' . strtoupper($status) . ']' : '';

            $lines[] = sprintf('%d. %s-%s — %s%s', $index + 1, $start, $end, $name, $statusText);
            $lines[] = '   📍 ' . $room;
        }

        return implode("\n", $lines);
    }

    private static function formatDate(string $date): string
    {
        if (class_exists(IntlDateFormatter::class)) {
            $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Asia/Makassar');
            $formatted = $formatter->format(strtotime($date));
            if ($formatted !== false) {
                return $formatted;
            }
        }

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $timestamp = strtotime($date);

        return $days[(int) date('w', $timestamp)] . ', ' . date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}
