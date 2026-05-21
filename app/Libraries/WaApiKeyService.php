<?php

namespace App\Libraries;

final class WaApiKeyService
{
    public static function generatePlainKey(): string
    {
        return 'mkwa_' . bin2hex(random_bytes(32));
    }

    public static function hash(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    public static function verify(string $plainKey, string $hash): bool
    {
        return hash_equals($hash, self::hash($plainKey));
    }

    public static function getPrefix(string $plainKey): string
    {
        return substr($plainKey, 0, 8) . '...';
    }
}
