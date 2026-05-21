<?php

use App\Libraries\WaApiKeyService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class WaApiKeyServiceTest extends CIUnitTestCase
{
    public function testGeneratePlainKeyUsesExpectedPrefixAndRandomLength(): void
    {
        $plainKey = WaApiKeyService::generatePlainKey();

        $this->assertStringStartsWith('mkwa_', $plainKey);
        $this->assertSame(69, strlen($plainKey));
    }

    public function testHashAndVerifyPlainKey(): void
    {
        $plainKey = 'mkwa_test_key';
        $hash = WaApiKeyService::hash($plainKey);

        $this->assertNotSame($plainKey, $hash);
        $this->assertTrue(WaApiKeyService::verify($plainKey, $hash));
        $this->assertFalse(WaApiKeyService::verify('mkwa_wrong_key', $hash));
    }

    public function testGetPrefixKeepsSafePublicIdentifier(): void
    {
        $this->assertSame('mkwa_abc...', WaApiKeyService::getPrefix('mkwa_abcdef123456'));
    }
}
