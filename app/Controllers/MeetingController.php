<?php

namespace App\Controllers;

use App\Models\MeetingModel;
use App\Models\RuanganModel;
use App\Models\PegawaiModel;
use CodeIgniter\Controller;

class MeetingController extends Controller
{
    protected $meetingModel;
    protected $ruanganModel;
    protected $pegawaiModel;

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
        $this->ruanganModel = new RuanganModel();
        $this->pegawaiModel = new PegawaiModel();
    }

    protected function isAdmin()
    {
        return session()->get('is_admin') === true;
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $data['meetings'] = $this->meetingModel->getUpcomingMeetings();
        $data['ruangan'] = $this->ruanganModel->findAll();
        $data['isAdmin'] = $this->isAdmin();
        return view('meeting/index', $data);
    }

    public function calendar()
    {
        $data['meetings'] = $this->meetingModel->getNotRejectedMeetingWithDetails();
        $data['ruangan'] = $this->ruanganModel->findAll();
        $data['isAdmin'] = session()->get('logged_in') ? $this->isAdmin() : false;
        return view('meeting/calendar', $data);
    }

    public function upcoming()
    {
        $data['today_meetings'] = $this->meetingModel->getTodayMeetings();
        $data['upcoming_meetings'] = $this->meetingModel->getUpcomingMeetings();
        $data['ruangan'] = $this->ruanganModel->findAll();
        $data['isAdmin'] = session()->get('logged_in') ? $this->isAdmin() : false;
        return view('meeting/upcoming', $data);
    }

    public function all()
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        $startParam = $this->request->getGet('start');
        $endParam   = $this->request->getGet('end');

        // Default to this week
        $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));

        $startDate = $startParam ? date('Y-m-d 00:00:00', strtotime($startParam)) : $monday;
        $endDate   = $endParam ? date('Y-m-d 23:59:59', strtotime($endParam)) : $sunday;

        $data = [];
        $data['start']    = date('Y-m-d', strtotime($startDate));
        $data['end']      = date('Y-m-d', strtotime($endDate));
        $data['meetings'] = $this->meetingModel->getMeetingsByDateRange($startDate, $endDate);
        $data['ruangan']  = $this->ruanganModel->findAll();
        $data['isAdmin']  = true;

        return view('meeting/all', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        // Debug: Log the POST data
        log_message('debug', 'POST data: ' . print_r($this->request->getPost(), true));

        // Get start time and duration
        $waktuMulai = $this->request->getPost('waktu_mulai');
        $durasi = $this->request->getPost('durasi');

        // Convert start time to MySQL datetime format
        $waktuMulai = date('Y-m-d H:i:s', strtotime($waktuMulai));
        
        if($durasi == 'Penuh'){
            $waktuSelesai = date('Y-m-d 23:59:59', strtotime($waktuMulai));
        } else {
            // Calculate end time by adding duration (in minutes)
            $durasi = (int)$durasi;
            $waktuSelesai = date('Y-m-d H:i:s', strtotime($waktuMulai . ' + ' . $durasi . ' minutes'));
        }
        
        // Set validation rules for create
        $this->meetingModel->setValidationRules([
            'nama_keg' => 'required|min_length[3]|max_length[100]',
            'ruangan_id' => 'required|integer|is_not_unique[ruangan.id]',
            'pegawai_id' => 'required|integer|is_not_unique[pegawai.id]',
            'waktu_mulai' => 'required|valid_date[Y-m-d H:i:s]',
            'waktu_selesai' => 'required|valid_date[Y-m-d H:i:s]',
            'status' => 'required|in_list[pending,approved,rejected,cancelled]'
        ]);

        $data = [
            'nama_keg' => $this->request->getPost('nama_keg'),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'ruangan_id' => $this->request->getPost('ruangan_id'),
            'pegawai_id' => (int) session()->get('pegawai_id'),
            'status' => 'pending'
        ];

        // Debug: Log the formatted data
        log_message('debug', 'Formatted data: ' . print_r($data, true));

        // Validate the data
        if (!$this->meetingModel->validate($data)) {
            log_message('error', 'Validation errors: ' . print_r($this->meetingModel->errors(), true));
            return redirect()->back()
                ->with('errors', $this->meetingModel->errors())
                ->withInput();
        }

        // Try to insert the data
        try {
            $result = $this->meetingModel->insert($data);
            if ($result === false) {
                log_message('error', 'Insert failed: ' . print_r($this->meetingModel->errors(), true));
                return redirect()->back()
                    ->with('error', 'Gagal membuat meeting: ' . implode(', ', $this->meetingModel->errors()))
                    ->withInput();
            }
            
            // Debug: Log successful insert
            log_message('info', 'Meeting created successfully with ID: ' . $result);
            
            // Send notification via Saungwa if configured
            try {
                $saungwaEnabled   = env('saungwa.enabled', true);
                $saungwaUrl       = env('saungwa.url', 'https://app.saungwa.com/api/create-message');
                $saungwaAppKey    = env('saungwa.appkey');
                $saungwaAuthKey   = env('saungwa.authkey');
                $saungwaTo        = env('saungwa.to');
                $saungwaTemplate  = env('saungwa.template_id');

                if ($saungwaEnabled && $saungwaAppKey && $saungwaAuthKey && $saungwaTo && $saungwaTemplate) {
                    $ruangan = $this->ruanganModel->find($data['ruangan_id']);
                    $pegawai = $this->pegawaiModel->find($data['pegawai_id']);

                    $variables = [
                        '{1}' => $data['nama_keg'],
                        '{2}' => $ruangan['tipe'] ?? '',
                        '{3}' => date('d M Y H:i', strtotime($data['waktu_mulai'])) . ' - ' . date('H:i', strtotime($data['waktu_selesai'])),
                        '{4}' => $pegawai['nama'] ?? '',
                    ];

                    $postFields = [
                        'appkey'      => $saungwaAppKey,
                        'authkey'     => $saungwaAuthKey,
                        'to'          => $saungwaTo,
                        'template_id' => $saungwaTemplate,
                    ];
                    // Flatten variables for form-data submit
                    foreach ($variables as $k => $v) {
                        $postFields["variables[$k]"] = $v;
                    }

                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL            => $saungwaUrl,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING       => '',
                        CURLOPT_MAXREDIRS      => 10,
                        CURLOPT_TIMEOUT        => 10,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST  => 'POST',
                        CURLOPT_POSTFIELDS     => $postFields,
                    ]);
                    $response = curl_exec($ch);
                    $curlErr  = curl_error($ch);
                    curl_close($ch);

                    if ($curlErr) {
                        log_message('error', 'Saungwa notify failed: ' . $curlErr);
                    } else {
                        log_message('info', 'Saungwa notify response: ' . $response);
                    }
                } else {
                    log_message('debug', 'Saungwa not configured or disabled; skipping notify.');
                }
            } catch (\Throwable $tex) {
                log_message('error', 'Saungwa notify exception: ' . $tex->getMessage());
            }

            return redirect()->back()->with('success', 'Meeting berhasil dibuat');
        } catch (\Exception $e) {
            log_message('error', 'Exception during insert: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membuat meeting: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->to('/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        // Only allow editing if user is admin or the meeting creator
        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit meeting ini');
        }

        $data['meeting'] = $meeting;
        $data['ruangan'] = $this->ruanganModel->findAll();
        $data['isAdmin'] = $this->isAdmin();
        return view('meeting/edit', $data);
    }

    public function update($id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->to('/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        // Only allow updating if user is admin or the meeting creator
        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengupdate meeting ini');
        }

        // Get start time and duration
        $waktuMulai = $this->request->getPost('waktu_mulai');
        $durasi = $this->request->getPost('durasi');

        // Convert start time to MySQL datetime format
        $waktuMulai = date('Y-m-d H:i:s', strtotime($waktuMulai));
        
        if ($durasi == 'Penuh') {
            $waktuSelesai = date('Y-m-d 23:59:59', strtotime($waktuMulai));
        } else {
            // Calculate end time by adding duration (in minutes)
            $durasi = (int)$durasi;
            $waktuSelesai = date('Y-m-d H:i:s', strtotime($waktuMulai . ' + ' . $durasi . ' minutes'));
        }
        
        // Set validation rules for update
        $this->meetingModel->setValidationRules([
            'nama_keg' => 'required|min_length[3]|max_length[100]',
            'ruangan_id' => 'required|integer|is_not_unique[ruangan.id]',
            'waktu_mulai' => 'required|valid_date[Y-m-d H:i:s]',
            'waktu_selesai' => 'required|valid_date[Y-m-d H:i:s]'
        ]);

        $data = [
            'nama_keg' => $this->request->getPost('nama_keg'),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'ruangan_id' => $this->request->getPost('ruangan_id')
        ];

        // Debug: Log the data being updated
        log_message('debug', 'Updating meeting ' . $id . ' with data: ' . print_r($data, true));

        if (!$this->meetingModel->validate($data)) {
            log_message('error', 'Validation errors: ' . print_r($this->meetingModel->errors(), true));
            return redirect()->back()
                ->with('errors', $this->meetingModel->errors())
                ->withInput();
        }

        try {
            if ($this->meetingModel->update($id, $data) === false) {
                log_message('error', 'Failed to update meeting: ' . print_r($this->meetingModel->errors(), true));
                return redirect()->back()
                    ->with('error', 'Gagal mengupdate meeting: ' . implode(', ', $this->meetingModel->errors()))
                    ->withInput();
            }

            log_message('info', 'Successfully updated meeting ' . $id);

            // Send notification for update via Saungwa if configured
            try {
                $saungwaEnabled   = env('saungwa.enabled', true);
                $saungwaUrl       = env('saungwa.url', 'https://app.saungwa.com/api/create-message');
                $saungwaAppKey    = env('saungwa.appkey');
                $saungwaAuthKey   = env('saungwa.authkey');
                $saungwaTo        = env('saungwa.to');
                $saungwaTemplate  = env('saungwa.template_id_update'); // different template for update

                if ($saungwaEnabled && $saungwaAppKey && $saungwaAuthKey && $saungwaTo && $saungwaTemplate) {
                    $ruangan = $this->ruanganModel->find($data['ruangan_id']);
                    $pegawai = $this->pegawaiModel->find($meeting['pegawai_id']);

                    $variables = [
                        '{1}' => $data['nama_keg'],
                        '{2}' => $ruangan['tipe'] ?? '',
                        '{3}' => date('d M Y H:i', strtotime($data['waktu_mulai'])) . ' - ' . date('H:i', strtotime($data['waktu_selesai'])),
                        '{4}' => $pegawai['nama'] ?? '',
                    ];

                    $postFields = [
                        'appkey'      => $saungwaAppKey,
                        'authkey'     => $saungwaAuthKey,
                        'to'          => $saungwaTo,
                        'template_id' => $saungwaTemplate,
                    ];
                    foreach ($variables as $k => $v) {
                        $postFields["variables[$k]"] = $v;
                    }

                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL            => $saungwaUrl,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING       => '',
                        CURLOPT_MAXREDIRS      => 10,
                        CURLOPT_TIMEOUT        => 10,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST  => 'POST',
                        CURLOPT_POSTFIELDS     => $postFields,
                    ]);
                    $response = curl_exec($ch);
                    $curlErr  = curl_error($ch);
                    curl_close($ch);

                    if ($curlErr) {
                        log_message('error', 'Saungwa update notify failed: ' . $curlErr);
                    } else {
                        log_message('info', 'Saungwa update notify response: ' . $response);
                    }
                } else {
                    log_message('debug', 'Saungwa update not configured or disabled; skipping notify.');
                }
            } catch (\Throwable $tex) {
                log_message('error', 'Saungwa update notify exception: ' . $tex->getMessage());
            }
            return redirect()->back()->with('success', 'Meeting berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Exception during update: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengupdate meeting: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->to('/meeting/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        // Only allow deletion if user is admin or the meeting creator
        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus meeting ini');
        }

        if ($this->meetingModel->delete($id) === false) {
            return redirect()->back()->with('error', 'Gagal menghapus meeting');
        }

        return redirect()->back()->with('success', 'Meeting berhasil dihapus');
    }

    public function updateStatus($id = null)
    {
        // Debug: Log the request data
        log_message('debug', 'Status update request for meeting ' . $id . ': ' . print_r($this->request->getPost(), true));

        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengubah status meeting');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->to('/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        $status = $this->request->getPost('status');
        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            log_message('error', 'Invalid status: ' . $status);
            return redirect()->back()->with('error', 'Status tidak valid');
        }

        try {
            // Set validation rules for status update
            $this->meetingModel->setValidationRules([
                'status' => 'required|in_list[pending,approved,rejected,cancelled]'
            ]);

            $result = $this->meetingModel->update($id, ['status' => $status]);
            if ($result === false) {
                log_message('error', 'Failed to update meeting status: ' . print_r($this->meetingModel->errors(), true));
                return redirect()->back()
                    ->with('error', 'Gagal mengupdate status meeting: ' . implode(', ', $this->meetingModel->errors()));
            }

            log_message('info', 'Successfully updated meeting ' . $id . ' status to ' . $status);
            return redirect()->back()
                ->with('success', 'Status meeting berhasil diupdate menjadi ' . strtoupper($status));
        } catch (\Exception $e) {
            log_message('error', 'Exception during status update: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengupdate status meeting: ' . $e->getMessage());
        }
    }
}


