<?php

namespace App\Controllers;

use App\Libraries\WaApiKeyService;
use App\Models\WaApiKeyModel;
use App\Models\WaMessageQueueModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class WhatsappApiController extends ResourceController
{
    public function createMessage(): ResponseInterface
    {
        $apiKey = $this->request->getHeaderLine('X-API-KEY') ?: (string) $this->request->getGet('api_key');
        if ($apiKey === '') {
            return $this->respond(['success' => false, 'message' => 'API key required'], 401);
        }

        $apiKeyRow = (new WaApiKeyModel())
            ->where('key_hash', WaApiKeyService::hash($apiKey))
            ->where('is_active', true)
            ->first();

        if (!$apiKeyRow) {
            return $this->respond(['success' => false, 'message' => 'Invalid API key'], 403);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $to = preg_replace('/[^0-9@g\.us-]/', '', (string) ($payload['to'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        if ($to === '' || $message === '') {
            return $this->respond(['success' => false, 'message' => 'Fields "to" and "message" are required'], 422);
        }

        $queueModel = new WaMessageQueueModel();
        $queueId = $queueModel->insert([
            'api_key_id' => $apiKeyRow['id'],
            'to_number' => $to,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'scheduled_at' => date('Y-m-d H:i:s'),
        ], true);

        (new WaApiKeyModel())->update($apiKeyRow['id'], ['last_used_at' => date('Y-m-d H:i:s')]);

        return $this->respondCreated([
            'success' => true,
            'queue_id' => $queueId,
            'status' => 'pending',
        ]);
    }

    public function showMessage(int $id): ResponseInterface
    {
        $apiKey = $this->request->getHeaderLine('X-API-KEY') ?: (string) $this->request->getGet('api_key');
        $apiKeyRow = (new WaApiKeyModel())
            ->where('key_hash', WaApiKeyService::hash($apiKey))
            ->where('is_active', true)
            ->first();

        if (!$apiKeyRow) {
            return $this->respond(['success' => false, 'message' => 'Invalid API key'], 403);
        }

        $message = (new WaMessageQueueModel())
            ->where('id', $id)
            ->where('api_key_id', $apiKeyRow['id'])
            ->first();

        if (!$message) {
            return $this->respond(['success' => false, 'message' => 'Message not found'], 404);
        }

        return $this->respond(['success' => true, 'data' => $message]);
    }
}
