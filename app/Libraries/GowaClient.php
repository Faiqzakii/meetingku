<?php

namespace App\Libraries;

use RuntimeException;

final class GowaClient
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly ?string $deviceId = null,
    ) {
    }

    public function status(): array
    {
        $result = $this->request('GET', '/app/status');
        if (!is_bool($result['is_connected'] ?? null) || !is_bool($result['is_logged_in'] ?? null)) {
            throw new RuntimeException('Invalid response from WhatsApp provider.');
        }
        if (isset($result['device_id']) && $result['device_id'] !== $this->deviceId()) {
            throw new RuntimeException('Invalid response from WhatsApp provider.');
        }

        return [
            'status' => !$result['is_logged_in'] ? 'NOT_LOGGED_IN' : ($result['is_connected'] ? 'CONNECTED' : 'DISCONNECTED'),
            'is_connected' => $result['is_connected'],
            'is_logged_in' => $result['is_logged_in'],
        ];
    }

    public function sendMessage(string $to, string $message): array
    {
        $phone = trim($to);
        if (str_ends_with($phone, '@c.us')) {
            $phone = substr($phone, 0, -strlen('@c.us')) . '@s.whatsapp.net';
        } elseif (preg_match('/^\+?[0-9\s().-]+$/', $phone)) {
            $phone = preg_replace('/\D+/', '', $phone) . '@s.whatsapp.net';
        }
        if (!preg_match('/^(?:[0-9]+@s\.whatsapp\.net|[0-9]+(?:-[0-9]+)?@g\.us)$/', $phone)) {
            throw new RuntimeException('Invalid WhatsApp recipient.');
        }

        $result = $this->request('POST', '/send/message', [
            'phone' => $phone,
            'message' => $message,
        ]);

        $id = $result['message_id'] ?? null;
        if (!is_string($id) || trim($id) === '') {
            throw new RuntimeException('Invalid response from WhatsApp provider.');
        }

        return ['id' => $id];
    }

    public function deviceId(): string
    {
        return (string) ($this->deviceId ?? env('GOWA_DEVICE_ID', 'Meetingku'));
    }

    public function dashboardUrl(): string
    {
        return $this->resolvedBaseUrl() . '/';
    }

    private function resolvedBaseUrl(): string
    {
        $baseUrl = rtrim((string) ($this->baseUrl ?? env('GOWA_BASE_URL', 'https://gowa.kaltarastats.id')), '/');
        $parts = parse_url($baseUrl);
        if ($parts === false || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new RuntimeException('WhatsApp provider is not configured.');
        }

        return $baseUrl;
    }

    private function request(string $method, string $path, array $json = []): array
    {
        $baseUrl = $this->resolvedBaseUrl();
        $username = (string) ($this->username ?? env('GOWA_USERNAME', ''));
        $password = (string) ($this->password ?? env('GOWA_PASSWORD', ''));
        $deviceId = $this->deviceId();
        if (trim($username) === '' || $password === '' || trim($deviceId) === ''
            || str_contains($username, ':') || preg_match('/[\r\n]/', $deviceId)) {
            throw new RuntimeException('WhatsApp provider is not configured.');
        }

        $options = [
            'http_errors' => false,
            'timeout' => 30,
            'allow_redirects' => false,
            'verify' => true,
            'auth' => [$username, $password, 'basic'],
            'headers' => ['Accept' => 'application/json', 'X-Device-Id' => $deviceId],
        ];
        if ($json !== []) {
            $options['json'] = $json;
        }

        try {
            $response = service('curlrequest')->request($method, $baseUrl . $path, $options);
        } catch (\Throwable $e) {
            throw new RuntimeException('WhatsApp provider is unavailable.');
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('WhatsApp provider request failed.');
        }

        $body = json_decode((string) $response->getBody());
        if (!$body instanceof \stdClass || ($body->code ?? null) !== 'SUCCESS'
            || !($body->results ?? null) instanceof \stdClass) {
            throw new RuntimeException('Invalid response from WhatsApp provider.');
        }

        return (array) $body->results;
    }
}
