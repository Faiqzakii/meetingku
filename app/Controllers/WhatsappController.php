<?php

namespace App\Controllers;

use App\Libraries\WaApiKeyService;
use App\Libraries\WahaClient;
use App\Models\WaApiKeyModel;
use App\Models\WaMessageQueueModel;
use CodeIgniter\Controller;

class WhatsappController extends Controller
{
    public function index()
    {
        $client = new WahaClient();
        $status = ['status' => 'UNKNOWN'];
        try {
            $status = $client->status();
        } catch (\Throwable $e) {
            $status['error'] = 'Status WhatsApp tidak dapat diambil.';
        }

        return view('whatsapp/index', [
            'pageTitle' => 'WhatsApp',
            'status' => $status,
            'sessionName' => $client->sessionName(),
            'dashboardUrl' => $client->dashboardUrl(),
            'queue' => (new WaMessageQueueModel())->orderBy('id', 'DESC')->limit(20)->findAll(),
        ]);
    }

    public function createApiKey()
    {
        $name = trim((string) $this->request->getPost('name')) ?: 'Integration';
        $plainKey = WaApiKeyService::generatePlainKey();

        (new WaApiKeyModel())->insert([
            'name' => $name,
            'plain_key' => $plainKey,
            'key_hash' => WaApiKeyService::hash($plainKey),
            'prefix' => WaApiKeyService::getPrefix($plainKey),
            'is_active' => true,
        ]);

        return redirect()->to(base_url('whatsapp'))->with('newApiKey', $plainKey);
    }

    public function revokeApiKey(int $id)
    {
        (new WaApiKeyModel())->update($id, [
            'is_active' => false,
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('whatsapp'))->with('success', 'API key revoked.');
    }

}
