<?php

namespace App\Controllers;

use App\Libraries\GowaClient;
use App\Models\WaMessageQueueModel;
use CodeIgniter\Controller;

class WhatsappController extends Controller
{
    public function index()
    {
        $client = new GowaClient();
        $status = ['status' => 'UNKNOWN'];
        $dashboardUrl = null;
        try {
            $dashboardUrl = $client->dashboardUrl();
            $status = $client->status();
        } catch (\Throwable $e) {
            $status['error'] = 'Status WhatsApp tidak dapat diambil.';
        }

        return view('whatsapp/index', [
            'pageTitle' => 'WhatsApp',
            'status' => $status,
            'deviceId' => $client->deviceId(),
            'dashboardUrl' => $dashboardUrl,
            'queue' => (new WaMessageQueueModel())->orderBy('id', 'DESC')->limit(20)->findAll(),
        ]);
    }
}
