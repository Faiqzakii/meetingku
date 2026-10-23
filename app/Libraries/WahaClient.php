<?php

namespace App\Libraries;

use RuntimeException;

final class WahaClient
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $apiKey = null,
        private readonly ?string $session = null,
    ) {
    }

    public function status(): array
    {
        return $this->request('GET', '/api/sessions/' . rawurlencode($this->sessionName()));
    }

    public function sendMessage(string $to, string $message): array
    {
        $chatId = $to;
        if (str_ends_with($chatId, '@s.whatsapp.net')) {
            $chatId = substr($chatId, 0, -strlen('@s.whatsapp.net')) . '@c.us';
        } elseif (!str_ends_with($chatId, '@g.us') && !str_ends_with($chatId, '@c.us')) {
            $number = preg_replace('/\D+/', '', $chatId);
            if ($number === '') {
                throw new RuntimeException('Invalid WhatsApp recipient.');
            }
            $chatId = $number . '@c.us';
        }

        $result = $this->request('POST', '/api/sendText', [
            'session' => $this->sessionName(),
            'chatId' => $chatId,
            'text' => $message,
        ]);

        $id = $result['id'] ?? null;
        if ($id instanceof \stdClass) {
            $id = $id->_serialized ?? null;
        }
        if (!is_string($id) || trim($id) === '') {
            throw new RuntimeException('Invalid response from WhatsApp provider.');
        }
        $result['id'] = $id;

        return $result;
    }

    public function sessionName(): string
    {
        return (string) ($this->session ?? env('WAHA_SESSION', 'default'));
    }

    public function dashboardUrl(): string
    {
        return $this->resolvedBaseUrl() . '/dashboard';
    }

    private function resolvedBaseUrl(): string
    {
        return rtrim((string) ($this->baseUrl ?? env('WAHA_BASE_URL', 'https://waha.kaltarastats.id')), '/');
    }

    private function request(string $method, string $path, array $json = []): array
    {
        $baseUrl = $this->resolvedBaseUrl();
        $apiKey = (string) ($this->apiKey ?? env('WAHA_API_KEY', ''));
        if (trim($baseUrl) === '' || trim($apiKey) === '' || trim($this->sessionName()) === '') {
            throw new RuntimeException('WhatsApp provider is not configured.');
        }

        $options = [
            'http_errors' => false,
            'timeout' => 30,
            'allow_redirects' => false,
            'headers' => ['X-Api-Key' => $apiKey],
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
        if (!$body instanceof \stdClass) {
            throw new RuntimeException('Invalid response from WhatsApp provider.');
        }

        return (array) $body;
    }
}
