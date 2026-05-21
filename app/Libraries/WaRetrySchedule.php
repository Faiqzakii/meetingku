<?php

namespace App\Libraries;

final class WaRetrySchedule
{
    private const DELAYS = [
        1 => 60,
        2 => 600,
        3 => 1800,
    ];

    public static function delaySecondsForAttempt(int $attempt): int
    {
        return self::DELAYS[$attempt] ?? self::DELAYS[3];
    }

    public static function nextScheduledAt(int $attempt, ?int $now = null): string
    {
        $timestamp = ($now ?? time()) + self::delaySecondsForAttempt($attempt);

        return date('Y-m-d H:i:s', $timestamp);
    }
}
