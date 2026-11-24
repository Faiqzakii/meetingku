<?php

use App\Libraries\GowaClient;
use App\Libraries\GowaDeliveryUncertain;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class GowaClientTest extends CIUnitTestCase
{
    private function client(): GowaClient
    {
        return new GowaClient('https://gowa.example.test/', 'integration-user', 'private-password', 'Meetingku');
    }

    private function expectRequest(string $method, string $path, mixed $body, int $status = 200, ?array $json = null): void
    {
        $response = new Response(config('App'));
        $response->setStatusCode($status);
        $response->setBody(is_string($body) ? $body : json_encode($body, JSON_THROW_ON_ERROR));
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->once())->method('request')
            ->with($method, 'https://gowa.example.test' . $path, $this->callback(function (array $options) use ($json): bool {
                $this->assertSame(['integration-user', 'private-password', 'basic'], $options['auth']);
                $this->assertSame('Meetingku', $options['headers']['X-Device-Id']);
                $this->assertSame('application/json', $options['headers']['Accept']);
                $this->assertFalse($options['allow_redirects']);
                $this->assertFalse($options['http_errors']);
                $this->assertTrue($options['verify']);
                $this->assertSame(30, $options['timeout']);
                $this->assertArrayNotHasKey('X-Api-Key', $options['headers']);
                if ($json === null) {
                    $this->assertArrayNotHasKey('json', $options);
                } else {
                    $this->assertSame($json, $options['json']);
                }

                return true;
            }))->willReturn($response);
        Services::injectMock('curlrequest', $http);
    }

    public static function connectionStates(): array
    {
        return [
            [true, true, 'CONNECTED'],
            [false, true, 'DISCONNECTED'],
            [false, false, 'NOT_LOGGED_IN'],
            [true, false, 'NOT_LOGGED_IN'],
        ];
    }

    /** @dataProvider connectionStates */
    public function testStatusRequiresBothConnectionAndLogin(bool $connected, bool $loggedIn, string $expected): void
    {
        $this->expectRequest('GET', '/app/status', [
            'code' => 'SUCCESS',
            'results' => ['is_connected' => $connected, 'is_logged_in' => $loggedIn, 'device_id' => 'Meetingku'],
        ]);

        $this->assertSame([
            'status' => $expected,
            'is_connected' => $connected,
            'is_logged_in' => $loggedIn,
        ], $this->client()->status());
    }

    public static function recipients(): array
    {
        return [
            ['120363425375670792@g.us', '120363425375670792@g.us'],
            ['62812345678-123456@g.us', '62812345678-123456@g.us'],
            ['62812345678@s.whatsapp.net', '62812345678@s.whatsapp.net'],
            ['62812345678@c.us', '62812345678@s.whatsapp.net'],
            ['62812345678', '62812345678@s.whatsapp.net'],
            [' +62 (812) 345-678 ', '62812345678@s.whatsapp.net'],
        ];
    }

    /** @dataProvider recipients */
    public function testSendsTextWithExplicitDeviceAndNormalizesQueueMessageId(string $to, string $phone): void
    {
        $message = "Agenda rapat besok\nPukul 09.00 WITA";
        $this->expectRequest('POST', '/send/message', [
            'code' => 'SUCCESS',
            'results' => ['message_id' => 'GOWA-MESSAGE-123', 'status' => 'Message sent'],
        ], 200, ['phone' => $phone, 'message' => $message]);

        $this->assertSame(['id' => 'GOWA-MESSAGE-123'], $this->client()->sendMessage($to, $message));
    }

    public static function invalidRecipients(): array
    {
        return [[''], ['abc123'], ['@g.us'], ['628@unknown.test'], ['--'], ['+'], ['628@c.us@other']];
    }

    /** @dataProvider invalidRecipients */
    public function testInvalidRecipientsNeverCallProvider(string $to): void
    {
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->never())->method('request');
        Services::injectMock('curlrequest', $http);
        $this->expectExceptionMessage('Invalid WhatsApp recipient.');
        $this->client()->sendMessage($to, 'Test');
    }

    public static function failedResponses(): array
    {
        return [
            [400, 'private provider error', false],
            [401, 'private provider error', false],
            [408, 'private provider error', true],
            [429, 'private provider error', false],
            [500, 'private provider error', true],
            [504, 'private provider error', true],
            [302, 'private provider redirect', true],
            [200, '<html>Login required</html>', true],
            [200, 'null', true],
            [200, '[]', true],
            [200, ['code' => 'ERROR', 'message' => 'private provider error', 'results' => ['message_id' => '123']], true],
            [200, ['code' => 'SUCCESS', 'results' => null], true],
            [200, ['code' => 'SUCCESS', 'results' => ['message_id' => '']], true],
            [200, ['code' => 'SUCCESS', 'results' => ['message_id' => 123]], true],
            [200, ['code' => 'SUCCESS', 'results' => ['message_id' => str_repeat('A', 129)]], true],
            [200, ['code' => 'SUCCESS', 'results' => ['schedule_id' => 'not-sent-yet']], true],
        ];
    }

    /** @dataProvider failedResponses */
    public function testFailedOrMalformedSendsAreNotMarkedSentAndDoNotExposeProviderErrors(int $status, mixed $body, bool $uncertain): void
    {
        $this->expectRequest('POST', '/send/message', $body, $status, [
            'phone' => '62812345678@s.whatsapp.net', 'message' => 'Test',
        ]);
        try {
            $this->client()->sendMessage('62812345678', 'Test');
            $this->fail('Expected sanitized provider exception.');
        } catch (RuntimeException $e) {
            $this->assertSame($uncertain ? GowaDeliveryUncertain::class : RuntimeException::class, $e::class);
            $this->assertSame($status >= 300
                ? 'WhatsApp provider request failed.' : 'Invalid response from WhatsApp provider.', $e->getMessage());
        }
    }

    public static function invalidStatuses(): array
    {
        return [
            [[]],
            [['is_connected' => 'true', 'is_logged_in' => true]],
            [['is_connected' => true, 'is_logged_in' => 1]],
            [['is_connected' => true, 'is_logged_in' => true, 'device_id' => 'PST']],
        ];
    }

    /** @dataProvider invalidStatuses */
    public function testMalformedOrWrongDeviceStatusIsNotShownAsConnected(array $results): void
    {
        $this->expectRequest('GET', '/app/status', ['code' => 'SUCCESS', 'results' => (object) $results]);
        $this->expectExceptionMessage('Invalid response from WhatsApp provider.');
        $this->client()->status();
    }

    public function testTransportExceptionDoesNotExposeCredentialsToQueueHistory(): void
    {
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->once())->method('request')->willThrowException(new RuntimeException('private-password'));
        Services::injectMock('curlrequest', $http);

        try {
            $this->client()->sendMessage('62812345678', 'Test');
            $this->fail('Expected sanitized provider exception.');
        } catch (GowaDeliveryUncertain $e) {
            $this->assertSame('WhatsApp provider is unavailable.', $e->getMessage());
            $this->assertNull($e->getPrevious());
        }
    }

    public static function invalidConfigurations(): array
    {
        return [
            ['https://gowa.example.test', '', 'password', 'Meetingku'],
            ['https://gowa.example.test', 'user', '', 'Meetingku'],
            ['https://gowa.example.test', 'user', 'password', ''],
            ['https://gowa.example.test', 'user:name', 'password', 'Meetingku'],
            ['https://gowa.example.test', 'user', 'password', "Meetingku\r\nOther: value"],
            ['http://gowa.example.test', 'user', 'password', 'Meetingku'],
            ['https://user:password@gowa.example.test', 'user', 'password', 'Meetingku'],
            ['https://gowa.example.test?token=password', 'user', 'password', 'Meetingku'],
        ];
    }

    /** @dataProvider invalidConfigurations */
    public function testInvalidConfigurationFailsBeforeSendingCredentials(string $url, string $user, string $password, string $device): void
    {
        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->never())->method('request');
        Services::injectMock('curlrequest', $http);
        $this->expectExceptionMessage('WhatsApp provider is not configured.');
        (new GowaClient($url, $user, $password, $device))->status();
    }

    public function testDashboardUsesGowaRootAndPreservesConfiguredBasePath(): void
    {
        $this->assertSame('https://gowa.example.test/', $this->client()->dashboardUrl());
        $this->assertSame('https://gowa.example.test/api/', (new GowaClient('https://gowa.example.test/api/'))->dashboardUrl());
    }

    public function testDeploymentEnvironmentAndDefaultMeetingkuDevice(): void
    {
        $values = [
            'GOWA_BASE_URL' => 'https://gowa.example.test/',
            'GOWA_USERNAME' => 'integration-user',
            'GOWA_PASSWORD' => 'private-password',
            'GOWA_DEVICE_ID' => 'Meetingku',
        ];
        $original = [];
        foreach ($values as $key => $value) {
            $original[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        try {
            $client = new GowaClient();
            $this->assertSame('Meetingku', $client->deviceId());
            $this->assertSame('https://gowa.kaltarastats.id/', $client->dashboardUrl());
            foreach ($values as $key => $value) {
                putenv($key . '=' . $value);
            }
            $this->expectRequest('GET', '/app/status', [
                'code' => 'SUCCESS', 'results' => ['is_connected' => true, 'is_logged_in' => true],
            ]);
            $this->assertSame('CONNECTED', (new GowaClient())->status()['status']);
        } finally {
            foreach ($original as $key => [$envValue, $serverValue, $processValue]) {
                if ($envValue !== null) {
                    $_ENV[$key] = $envValue;
                }
                if ($serverValue !== null) {
                    $_SERVER[$key] = $serverValue;
                }
                putenv($processValue === false ? $key : $key . '=' . $processValue);
            }
        }
    }
}
