<?php

namespace App\Controllers;

use App\Libraries\WaApiKeyService;
use App\Libraries\WaSenderClient;
use App\Models\WaApiKeyModel;
use App\Models\WaMessageQueueModel;
use CodeIgniter\Controller;

class WhatsappController extends Controller
{
    public function index()
    {
        $status = ['connected' => false, 'state' => 'unreachable'];
        try {
            $status = (new WaSenderClient())->status();
        } catch (\Throwable $e) {
            $status['error'] = $e->getMessage();
        }

        return view('whatsapp/index', [
            'pageTitle' => 'WhatsApp',
            'status' => $status,
            'apiKeys' => (new WaApiKeyModel())->orderBy('created_at', 'DESC')->findAll(),
            'queue' => (new WaMessageQueueModel())->orderBy('id', 'DESC')->limit(20)->findAll(),
            'newApiKey' => session()->getFlashdata('newApiKey'),
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

    public function pairingCode()
    {
        $phoneNumber = preg_replace('/\D+/', '', (string) $this->request->getPost('phone_number'));
        if ($phoneNumber === '') {
            return redirect()->to(base_url('whatsapp'))->with('error', 'Nomor WA wajib diisi.');
        }

        try {
            $result = (new WaSenderClient())->requestPairingCode($phoneNumber);
            return redirect()->to(base_url('whatsapp'))->with('success', 'Pairing code: ' . ($result['pairingCode'] ?? '-'));
        } catch (\Throwable $e) {
            return redirect()->to(base_url('whatsapp'))->with('error', $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            (new WaSenderClient())->logout();
            return redirect()->to(base_url('whatsapp'))->with('success', 'WhatsApp logout berhasil.');
        } catch (\Throwable $e) {
            return redirect()->to(base_url('whatsapp'))->with('error', $e->getMessage());
        }
    }

    public function resetSession()
    {
        try {
            (new WaSenderClient())->resetSession();
            return redirect()->to(base_url('whatsapp'))->with('success', 'Session WhatsApp direset.');
        } catch (\Throwable $e) {
            return redirect()->to(base_url('whatsapp'))->with('error', $e->getMessage());
        }
    }
}
