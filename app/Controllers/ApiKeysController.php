<?php

namespace App\Controllers;

use App\Libraries\WaApiKeyService;
use App\Models\PegawaiModel;
use App\Models\WaApiKeyModel;
use CodeIgniter\Controller;

class ApiKeysController extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in') || !session()->get('is_admin')) {
            return redirect()->to('auth/login');
        }

        $apiKeyModel = new WaApiKeyModel();
        $pegawaiModel = new PegawaiModel();

        // Get all API keys with pegawai info
        $keys = $apiKeyModel
            ->select('wa_api_keys.*, pegawai.nama as pegawai_nama')
            ->join('pegawai', 'pegawai.id = wa_api_keys.pegawai_id', 'left')
            ->orderBy('wa_api_keys.created_at', 'DESC')
            ->findAll();

        $pegawaiList = $pegawaiModel->orderBy('nama', 'ASC')->findAll();

        $data = [
            'keys'        => $keys,
            'pegawaiList' => $pegawaiList,
            'newKey'      => session()->getFlashdata('new_key'),
        ];

        return view('api_keys/index', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in') || !session()->get('is_admin')) {
            return redirect()->to('auth/login');
        }

        $pegawaiId = $this->request->getPost('pegawai_id');
        $name = $this->request->getPost('name') ?: 'Meeting API Key';

        if (!$pegawaiId) {
            return redirect()->back()->with('error', 'Pegawai harus dipilih');
        }

        $pegawai = (new PegawaiModel())->find($pegawaiId);
        if (!$pegawai) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        }

        $plainKey = WaApiKeyService::generateMeetingKey();
        $keyHash  = WaApiKeyService::hash($plainKey);
        $prefix   = WaApiKeyService::getPrefix($plainKey);

        $apiKeyModel = new WaApiKeyModel();
        $apiKeyModel->insert([
            'name'       => $name,
            'key_hash'   => $keyHash,
            'prefix'     => $prefix,
            'pegawai_id' => (int) $pegawaiId,
            'is_active'  => true,
        ]);

        log_message('info', 'API key generated for pegawai=' . $pegawaiId . ' by admin');

        return redirect()->back()->with('new_key', [
            'key'        => $plainKey,
            'pegawai'    => $pegawai['nama'],
        ]);
    }

    public function revoke($id = null)
    {
        if (!session()->get('logged_in') || !session()->get('is_admin')) {
            return redirect()->to('auth/login');
        }

        $apiKeyModel = new WaApiKeyModel();
        $key = $apiKeyModel->find($id);

        if (!$key) {
            return redirect()->back()->with('error', 'API key tidak ditemukan');
        }

        $apiKeyModel->update($id, [
            'is_active'  => false,
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);

        log_message('info', 'API key revoked ID=' . $id);

        return redirect()->back()->with('success', 'API key berhasil di-revoke');
    }
}
