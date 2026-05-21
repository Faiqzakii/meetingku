<?php

namespace App\Libraries;

use RuntimeException;

final class WaSenderClient
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $secret = null,
    ) {
    }

    public function status(): array
    {
        return $this->request('GET', '/status');
    }

    public function requestPairingCode(string $phoneNumber): array
    {
        return $this->request('POST', '/pairing-code', ['phoneNumber' => $phoneNumber]);
    }

    public function sendMessage(string $to, string $message): array
    {
        return $this->request('POST', '/send-message', ['to' => $to, 'message' => $message]);
    }

    public function logout(): array
    {
        return $this->request('POST', '/logout');
    }

    public function resetSession(): array
    {
        return $this->request('POST', '/reset-session');
    }

    private function request(string $method, string $path, array $json = []): array
    {
        $baseUrl = rtrim((string) ($this->baseUrl ?? env('WA_SENDER_URL', 'http://wa-sender:3001')), '/');
        $secret = (string) ($this->secret ?? env('WA_SENDER_SECRET', ''));

        $client = service('curlrequest');
        $options = [
            'http_errors' => false,
            'timeout' => 30,
            'headers' => ['X-INTERNAL-WA-SECRET' => $secret],
        ];

        if ($json !== []) {
            $options['json'] = $json;
        }

        $response = $client->request($method, $baseUrl . $path, $options);
        $body = json_decode((string) $response->getBody(), true) ?: [];

        if ($response->getStatusCode() >= 400) {
            throw new RuntimeException((string) ($body['message'] ?? $body['error'] ?? 'WA sender request failed'));
        }

        return $body;
    }
}
