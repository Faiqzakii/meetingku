<?php

namespace App\Controllers;

use App\Models\MeetingModel;
use App\Models\RuanganModel;
use App\Models\PegawaiModel;
use App\Models\WaMessageQueueModel;
use App\Libraries\ZoomLibrary;
use CodeIgniter\Controller;

class MeetingController extends Controller
{
    private const WHATSAPP_GROUP_ID = '120363425375670792@g.us';

    protected $meetingModel;
    protected $ruanganModel;
    protected $pegawaiModel;
    protected $zoomLibrary;

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
        $this->ruanganModel = new RuanganModel();
        $this->pegawaiModel = new PegawaiModel();
        $this->zoomLibrary  = new ZoomLibrary();
    }

    protected function isAdmin()
    {
        return session()->get('is_admin') === true;
    }

    protected function sendWhatsAppToGroup(string $message): bool
    {
        return $this->sendWhatsAppMessage(self::WHATSAPP_GROUP_ID, $message);
    }

    protected function extractZoomMeetingIdFromUrl(string $zoomUrl): ?string
    {
        $parts = parse_url($zoomUrl);
        if ($parts === false) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if (!preg_match('/(^|\.)zoom\.us$/', $host)) {
            return null;
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');
        if ($path === '') {
            return null;
        }

        if (preg_match('/(?:^|\/)j\/(\d{9,15})(?:\/|$)/', $path, $match) === 1) {
            return $match[1];
        }

        if (preg_match('/(?:^|\/)wc\/(\d{9,15})(?:\/|$)/', $path, $match) === 1) {
            return $match[1];
        }

        return null;
    }

    /**
     * Generate a unique start_token for Zoom host shortlink.
     */
    protected function generateStartToken(): string
    {
        return bin2hex(random_bytes(10));
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $data['meetings'] = $this->meetingModel->getUpcomingMeetings();
        $data['ruangan'] = $this->ruanganModel->getActiveRooms();
        $data['isAdmin'] = $this->isAdmin();
        return view('meeting/index', $data);
    }

    public function calendar()
    {
        $data['meetings'] = $this->meetingModel->getNotRejectedMeetingWithDetails();
        $data['ruangan'] = $this->ruanganModel->getActiveRooms();
        $data['isAdmin'] = session()->get('logged_in') ? $this->isAdmin() : false;
        return view('meeting/calendar', $data);
    }

    public function upcoming()
    {
        $data['today_meetings'] = $this->meetingModel->getTodayMeetings();
        $data['upcoming_meetings'] = $this->meetingModel->getUpcomingMeetings();
        $data['ruangan'] = $this->ruanganModel->getActiveRooms();
        $data['isAdmin'] = session()->get('logged_in') ? $this->isAdmin() : false;
        return view('meeting/upcoming', $data);
    }

    public function all()
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        $startParam = $this->request->getGet('start_date');
        $endParam   = $this->request->getGet('end_date');

        // Default to this week
        $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $startDate = $startParam ? date('Y-m-d 00:00:00', strtotime($startParam)) : $monday;
        $endDate   = $endParam ? date('Y-m-d 23:59:59', strtotime($endParam)) : $sunday;

        $data = [];
        $data['startDate'] = date('Y-m-d', strtotime($startDate));
        $data['endDate']   = date('Y-m-d', strtotime($endDate));
        $data['meetings'] = $this->meetingModel->getMeetingsByDateRange($startDate, $endDate);
        $data['ruangan']  = $this->ruanganModel->getActiveRooms();
        $data['isAdmin']  = true;

        return view('meeting/all', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        // Check for form token to prevent double submission
        $formToken = $this->request->getPost('form_token');
        if (!$formToken) {
            log_message('error', 'No form token provided');
            return redirect()->back()
                ->with('error', 'Token form tidak valid')
                ->withInput();
        }

        // Check if this token has been used before (stored in session)
        $usedTokens = session()->get('used_form_tokens');
        if (!is_array($usedTokens)) {
            $usedTokens = [];
        }
        if (in_array($formToken, $usedTokens)) {
            log_message('warning', 'Duplicate form submission detected with token: ' . $formToken);
            return redirect()->back()
                ->with('error', 'Form telah dikirim. Mohon tunggu proses selesai.')
                ->withInput();
        }

        // Mark this token as used
        $usedTokens[] = $formToken;
        session()->set('used_form_tokens', $usedTokens);

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
        
        // Check room type for conditional validation
        $ruanganId = $this->request->getPost('ruangan_id');
        $ruangan = $this->ruanganModel->find($ruanganId);
        $isOnline = ($ruangan && $ruangan['tipe'] === 'Online');

        // Set validation rules for create
        $validationRules = [
            'nama_keg' => 'required|min_length[3]|max_length[100]',
            'ruangan_id' => 'required|integer|is_not_unique[ruangan.id]',
            'pegawai_id' => 'required|integer|is_not_unique[pegawai.id]',
            'waktu_mulai' => 'required|valid_date[Y-m-d H:i:s]',
            'waktu_selesai' => 'required|valid_date[Y-m-d H:i:s]',
            'status' => 'required|in_list[pending,approved,rejected,cancelled]'
        ];

        if ($isOnline) {
            $validationRules['jumlah_peserta'] = 'permit_empty';
        } else {
            $validationRules['jumlah_peserta'] = 'required|integer|greater_than[0]';
        }

        $this->meetingModel->setValidationRules($validationRules);

        $fasilitas = $this->request->getPost('fasilitas');
        $fasilitasLainnya = $this->request->getPost('fasilitas_lainnya');

        if ($fasilitas && is_array($fasilitas)) {
            if (($key = array_search('Lainnya', $fasilitas)) !== false) {
                if (!empty($fasilitasLainnya)) {
                    $fasilitas[$key] = 'Lainnya: ' . $fasilitasLainnya;
                }
            }
        }
        
        $data = [
            'nama_keg' => $this->request->getPost('nama_keg'),
            'jumlah_peserta' => $this->request->getPost('jumlah_peserta') ?: null,
            'fasilitas' => $fasilitas ? json_encode($fasilitas) : null,
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
            
            // Send notification via WhatsApp if configured
            try {
                $whatsappEnabled = env('whatsapp.enabled', true);

                if ($whatsappEnabled) {
                    $ruangan = $this->ruanganModel->find($data['ruangan_id']);
                    $pegawai = $this->pegawaiModel->find($data['pegawai_id']);

                    $namaKegiatan = $data['nama_keg'];
                    $tempat       = ($ruangan['nama_ruangan'] ?? '') . ' - ' . ($ruangan['tipe'] ?? '');
                    $waktu        = date('d M Y H:i', strtotime($data['waktu_mulai'])) . ' - ' . date('H:i', strtotime($data['waktu_selesai']));
                    $oleh         = $pegawai['nama'] ?? '';
                    $jmlPeserta   = $data['jumlah_peserta'] ?? '';
                    $fasilitasStr = $data['fasilitas'] ? implode(', ', json_decode($data['fasilitas'], true) ?? []) : '-';

                    $message = "*[Meetingku]*\n" .
                               "Terdapat pengajuan meeting baru\n\n" .
                               "*Nama Kegiatan*: $namaKegiatan\n" .
                               "*Tempat*: $tempat\n" .
                               "*Jumlah Peserta*: $jmlPeserta\n" .
                               "*Waktu*: $waktu\n" .
                               "*Fasilitas*: $fasilitasStr\n" .
                               "*Oleh*: $oleh";

                    $this->sendWhatsAppToGroup($message);
                } else {
                    log_message('debug', 'WhatsApp not configured or disabled; skipping notify.');
                }
            } catch (\Throwable $tex) {
                log_message('error', 'WhatsApp notify exception: ' . $tex->getMessage());
            }

            // Clean up old tokens (keep only last 10 tokens)
            $currentTokens = session()->get('used_form_tokens');
            if (!is_array($currentTokens)) {
                $currentTokens = [];
            }
            if (count($currentTokens) > 10) {
                $currentTokens = array_slice($currentTokens, -10);
                session()->set('used_form_tokens', $currentTokens);
            }

            return redirect()->back()->with('success', 'Meeting berhasil dibuat');
        } catch (\Exception $e) {
            log_message('error', 'Exception during insert: ' . $e->getMessage());
            
            // Remove token from used tokens on error so user can retry
            $currentTokens = session()->get('used_form_tokens');
            if (!is_array($currentTokens)) {
                $currentTokens = [];
            }
            $currentTokens = array_diff($currentTokens, [$formToken]);
            session()->set('used_form_tokens', $currentTokens);
            
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

        $meeting = $this->meetingModel->getMeetingDetails($id);
        if (!$meeting) {
            return redirect()->to('/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        // Only allow editing if user is admin or the meeting creator
        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit meeting ini');
        }

        $data['meeting'] = $meeting;
        $data['ruangan'] = $this->ruanganModel->getActiveRooms();
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

        // Check for form token to prevent double submission
        $formToken = $this->request->getPost('form_token');
        if (!$formToken) {
            log_message('error', 'No form token provided for update');
            return redirect()->back()
                ->with('error', 'Token form tidak valid')
                ->withInput();
        }

        // Check if this token has been used before (stored in session)
        $usedTokens = session()->get('used_form_tokens');
        if (!is_array($usedTokens)) {
            $usedTokens = [];
        }
        if (in_array($formToken, $usedTokens)) {
            log_message('warning', 'Duplicate form submission detected with token: ' . $formToken);
            return redirect()->back()
                ->with('error', 'Form telah dikirim. Mohon tunggu proses selesai.')
                ->withInput();
        }

        // Mark this token as used
        $usedTokens[] = $formToken;
        session()->set('used_form_tokens', $usedTokens);

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
        
        // Check room type for conditional validation
        $ruanganId = $this->request->getPost('ruangan_id');
        $ruangan = $this->ruanganModel->find($ruanganId);
        $isOnline = ($ruangan && $ruangan['tipe'] === 'Online');
        
        // Set validation rules for update
        $validationRules = [
            'nama_keg' => 'required|min_length[3]|max_length[100]',
            'ruangan_id' => 'required|integer|is_not_unique[ruangan.id]',
            'waktu_mulai' => 'required|valid_date[Y-m-d H:i:s]',
            'waktu_selesai' => 'required|valid_date[Y-m-d H:i:s]'
        ];

        if ($isOnline) {
             $validationRules['jumlah_peserta'] = 'permit_empty';
        } else {
             $validationRules['jumlah_peserta'] = 'required|integer|greater_than[0]';
        }

        $this->meetingModel->setValidationRules($validationRules);

        $fasilitas = $this->request->getPost('fasilitas');
        $fasilitasLainnya = $this->request->getPost('fasilitas_lainnya');

        if ($fasilitas && is_array($fasilitas)) {
            if (($key = array_search('Lainnya', $fasilitas)) !== false) {
                if (!empty($fasilitasLainnya)) {
                    $fasilitas[$key] = 'Lainnya: ' . $fasilitasLainnya;
                }
            }
        }

        $data = [
            'nama_keg' => $this->request->getPost('nama_keg'),
            'jumlah_peserta' => $this->request->getPost('jumlah_peserta') ?: null,
            'fasilitas' => $fasilitas ? json_encode($fasilitas) : null,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'ruangan_id' => $this->request->getPost('ruangan_id'),
            'last_edited_by' => (int) session()->get('pegawai_id'),
            'last_edited_at' => date('Y-m-d H:i:s'),
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

            // If meeting has Zoom and schedule changed, update Zoom meeting
            if (!empty($meeting['zoom_meeting_id'])) {
                $scheduleChanged = ($meeting['waktu_mulai'] !== $data['waktu_mulai'])
                                || ($meeting['waktu_selesai'] !== $data['waktu_selesai'])
                                || ($meeting['nama_keg'] !== $data['nama_keg']);
                if ($scheduleChanged) {
                    try {
                        $startTimestamp = strtotime($data['waktu_mulai']);
                        $endTimestamp   = strtotime($data['waktu_selesai']);
                        $duration       = max(30, (int) round(($endTimestamp - $startTimestamp) / 60));
                        $startTimeISO   = date('Y-m-d\TH:i:s', $startTimestamp);

                        $updated = $this->zoomLibrary->updateMeeting($meeting['zoom_meeting_id'], [
                            'topic'      => $data['nama_keg'],
                            'start_time' => $startTimeISO,
                            'duration'   => $duration,
                        ]);

                        if ($updated) {
                            // Refresh URLs from Zoom
                            $zoomData = $this->zoomLibrary->getMeeting($meeting['zoom_meeting_id']);
                            if ($zoomData) {
                                $this->meetingModel->setValidationRules([]);
                                $this->meetingModel->update($id, [
                                    'zoom_start_url' => $zoomData['start_url'],
                                    'zoom_join_url'  => $zoomData['join_url'],
                                ]);
                                $meeting['zoom_join_url']  = $zoomData['join_url'];
                                $meeting['zoom_start_url'] = $zoomData['start_url'];
                            }
                            // Notify pegawai about schedule change
                            $meeting['waktu_mulai']   = $data['waktu_mulai'];
                            $meeting['waktu_selesai'] = $data['waktu_selesai'];
                            $meeting['nama_keg']      = $data['nama_keg'];
                            $this->sendWhatsAppToPegawai($meeting, 'zoom_updated');
                            log_message('info', 'Zoom meeting updated for meeting ' . $id);
                        }
                    } catch (\Throwable $ze) {
                        log_message('error', 'Failed to update Zoom meeting: ' . $ze->getMessage());
                    }
                }
            }

            // Send notification for update via WhatsApp if configured
            try {
                $whatsappEnabled = env('whatsapp.enabled', true);

                if ($whatsappEnabled) {
                    $ruangan = $this->ruanganModel->find($data['ruangan_id']);
                    $pegawai = $this->pegawaiModel->find($meeting['pegawai_id']);

                    $namaKegiatan = $data['nama_keg'];
                    $tempat       = ($ruangan['nama_ruangan'] ?? '') . ' - ' . ($ruangan['tipe'] ?? '');
                    $waktu        = date('d M Y H:i', strtotime($data['waktu_mulai'])) . ' - ' . date('H:i', strtotime($data['waktu_selesai']));
                    $oleh         = $pegawai['nama'] ?? '';
                    $jmlPeserta   = $data['jumlah_peserta'] ?? '';
                    $fasilitasStr = $data['fasilitas'] ? implode(', ', json_decode($data['fasilitas'], true) ?? []) : '-';

                    $message = "*[Meetingku]*\n" .
                               "Terdapat edit detail meeting\n\n" .
                               "*Nama Kegiatan*: $namaKegiatan\n" .
                               "*Tempat*: $tempat\n" .
                               "*Jumlah Peserta*: $jmlPeserta\n" .
                               "*Waktu*: $waktu\n" .
                               "*Fasilitas*: $fasilitasStr\n" .
                               "*Oleh*: $oleh";

                    $this->sendWhatsAppToGroup($message);
                } else {
                    log_message('debug', 'WhatsApp update not configured or disabled; skipping notify.');
                }
            } catch (\Throwable $tex) {
                log_message('error', 'WhatsApp update notify exception: ' . $tex->getMessage());
            }
            // Clean up old tokens (keep only last 10 tokens)
            $currentTokens = session()->get('used_form_tokens');
            if (!is_array($currentTokens)) {
                $currentTokens = [];
            }
            if (count($currentTokens) > 10) {
                $currentTokens = array_slice($currentTokens, -10);
                session()->set('used_form_tokens', $currentTokens);
            }

            return redirect()->to('/upcoming')->with('success', 'Meeting berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Exception during update: ' . $e->getMessage());
            
            // Remove token from used tokens on error so user can retry
            $currentTokens = session()->get('used_form_tokens');
            if (!is_array($currentTokens)) {
                $currentTokens = [];
            }
            $currentTokens = array_diff($currentTokens, [$formToken]);
            session()->set('used_form_tokens', $currentTokens);
            
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

        // Delete associated Zoom meeting if exists
        if (!empty($meeting['zoom_meeting_id'])) {
            try {
                $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
                log_message('info', 'Zoom meeting deleted for meeting ID: ' . $id);
            } catch (\Exception $e) {
                log_message('error', 'Failed to delete Zoom meeting: ' . $e->getMessage());
                // Continue with DB deletion even if Zoom fails
            }
        }

        try {
            if ($this->meetingModel->delete($id) === false) {
                $errors = $this->meetingModel->errors();
                log_message('error', 'Failed to delete meeting ID ' . $id . ': ' . print_r($errors, true));
                return redirect()->back()->with('error', 'Gagal menghapus meeting: ' . implode(', ', $errors));
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during meeting deletion ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus meeting: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Meeting berhasil dihapus');
    }



    public function updateStatus($id = null)
    {
        log_message('debug', 'Status update request for meeting ' . $id . ': ' . print_r($this->request->getPost(), true));

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->to('/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        $status = $this->request->getPost('status');
        $currentPegawaiId = (int) session()->get('pegawai_id');
        $isOwner = (int) $meeting['pegawai_id'] === $currentPegawaiId;

        // Non-admin: only the meeting owner can cancel their own meeting
        if (!$this->isAdmin()) {
            if (!$isOwner || $status !== 'cancelled') {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah status meeting ini');
            }
        }

        if (!in_array($status, ['approved', 'rejected', 'pending', 'cancelled'])) {
            log_message('error', 'Invalid status: ' . $status);
            return redirect()->back()->with('error', 'Status tidak valid');
        }

        try {
            $this->meetingModel->setValidationRules([
                'status' => 'required|in_list[pending,approved,rejected,cancelled]'
            ]);

            $updateData = [
                'status' => $status,
                'status_changed_by' => (int) session()->get('pegawai_id'),
                'status_changed_at' => date('Y-m-d H:i:s'),
            ];

            // If rejecting/cancelling and meeting has Zoom, delete the Zoom meeting
            if (in_array($status, ['rejected', 'cancelled']) && !empty($meeting['zoom_meeting_id'])) {
                try {
                    $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
                    log_message('info', 'Zoom meeting deleted for meeting ' . $id);
                } catch (\Throwable $ze) {
                    log_message('error', 'Failed to delete Zoom meeting: ' . $ze->getMessage());
                }
                $updateData['zoom_meeting_id'] = null;
                $updateData['zoom_join_url']   = null;
                $updateData['zoom_start_url']  = null;
                $updateData['start_token']     = null;

                // Notify pegawai that Zoom link is cancelled
                $this->sendWhatsAppToPegawai($meeting, 'cancelled');
            }

            $result = $this->meetingModel->update($id, $updateData);
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

    /**
     * Send Zoom meeting link - called when admin clicks "Kirim Zoom" button
     */
    public function sendZoom($id = null)
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengirim link Zoom');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->back()->with('error', 'Meeting tidak ditemukan');
        }

        if ($meeting['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Meeting harus disetujui terlebih dahulu');
        }

        // Check if meeting time has passed
        if (strtotime($meeting['waktu_selesai']) < time()) {
            return redirect()->back()->with('error', 'Tidak bisa membuat Zoom — meeting sudah selesai/lewat');
        }

        // Check if Zoom already created
        if (!empty($meeting['zoom_meeting_id'])) {
            return redirect()->back()->with('error', 'Zoom meeting sudah dibuat untuk meeting ini');
        }

        // Check room type
        $ruangan = $this->ruanganModel->find($meeting['ruangan_id']);
        if (!$ruangan || !in_array($ruangan['tipe'], ['Online', 'Hybrid'])) {
            return redirect()->back()->with('error', 'Ruangan bukan tipe Online/Hybrid');
        }

        // Check Zoom enabled
        if (!$this->zoomLibrary->isEnabled()) {
            return redirect()->back()->with('error', 'Integrasi Zoom belum dikonfigurasi');
        }

        try {
            // Calculate duration in minutes
            $startTimestamp = strtotime($meeting['waktu_mulai']);
            $endTimestamp   = strtotime($meeting['waktu_selesai']);
            $duration       = max(30, (int) round(($endTimestamp - $startTimestamp) / 60));

            // Format start time for Zoom API (ISO 8601)
            $startTimeISO = date('Y-m-d\TH:i:s', $startTimestamp);

            $zoomResult = $this->zoomLibrary->createMeeting(
                $meeting['nama_keg'],
                $startTimeISO,
                $duration,
                'Meeting: ' . $meeting['nama_keg']
            );

            if (!$zoomResult) {
                return redirect()->back()->with('error', 'Gagal membuat Zoom meeting. Periksa konfigurasi Zoom API.');
            }

            // Save Zoom data to DB with start_token for public host shortlink
            $startToken = $this->generateStartToken();
            $this->meetingModel->setValidationRules([]);
            $this->meetingModel->update($id, [
                'zoom_meeting_id' => $zoomResult['id'],
                'zoom_join_url'   => $zoomResult['join_url'],
                'zoom_start_url'  => $zoomResult['start_url'],
                'start_token'     => $startToken,
            ]);

            // Refresh meeting data for notification
            $meeting['zoom_join_url']  = $zoomResult['join_url'];
            $meeting['zoom_start_url'] = $zoomResult['start_url'];
            $meeting['start_token']    = $startToken;

            // Send WhatsApp to pegawai with Zoom links
            $this->sendWhatsAppToPegawai($meeting, 'zoom_created');

            // Check for conflicts and warn
            $conflicts = $this->meetingModel->getConflictingZoomMeetings(
                $meeting['waktu_mulai'],
                $meeting['waktu_selesai'],
                (int) $id
            );

            $successMsg = 'Zoom meeting berhasil dibuat dan link telah dikirim';
            if (!empty($conflicts)) {
                $conflictNames = array_map(fn($c) => $c['nama_keg'], $conflicts);
                $successMsg .= '. ⚠️ PERHATIAN: Ada Zoom lain di waktu yang sama: ' . implode(', ', $conflictNames);
            }

            return redirect()->back()->with('success', $successMsg);
        } catch (\Exception $e) {
            log_message('error', 'Exception during sendZoom: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membuat Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Save manual Zoom join link for approved online/hybrid meetings.
     */
    public function updateManualZoomJoin($id = null)
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat menginput link Zoom manual');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->back()->with('error', 'Meeting tidak ditemukan');
        }

        if ($meeting['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Link Zoom manual hanya bisa diinput untuk meeting approved');
        }

        $ruangan = $this->ruanganModel->find($meeting['ruangan_id']);
        if (!$ruangan || !in_array($ruangan['tipe'], ['Online', 'Hybrid'])) {
            return redirect()->back()->with('error', 'Link Zoom manual hanya untuk ruangan Online/Hybrid');
        }

        if (strtotime($meeting['waktu_selesai']) <= time()) {
            return redirect()->back()->with('error', 'Meeting sudah selesai, link Zoom tidak dapat diubah');
        }

        $zoomJoinUrl = trim((string) $this->request->getPost('zoom_join_url'));
        if ($zoomJoinUrl === '') {
            return redirect()->back()->with('error', 'Link Zoom wajib diisi');
        }

        if (!filter_var($zoomJoinUrl, FILTER_VALIDATE_URL)) {
            return redirect()->back()->with('error', 'Format URL tidak valid');
        }

        $parts = parse_url($zoomJoinUrl);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true) || !preg_match('/(^|\.)zoom\.us$/', $host)) {
            return redirect()->back()->with('error', 'URL harus menggunakan domain Zoom (zoom.us)');
        }

        $this->meetingModel->setValidationRules([]);
        $zoomMeetingId = $this->extractZoomMeetingIdFromUrl($zoomJoinUrl);
        $updated = $this->meetingModel->update($id, [
            'zoom_join_url' => $zoomJoinUrl,
            'zoom_meeting_id' => $zoomMeetingId,
        ]);

        if ($updated === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan link Zoom manual');
        }

        return redirect()->back()->with('success', 'Link Zoom manual berhasil disimpan');
    }

    /**
     * Refresh Zoom start_url and redirect to it.
     * Used as the "Host" button action — generates a fresh start_url (valid 2h) on the fly.
     */
    public function refreshZoom($id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting || empty($meeting['zoom_meeting_id'])) {
            return redirect()->back()->with('error', 'Meeting tidak memiliki Zoom');
        }

        // Only allow owner or admin
        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }

        // Do not allow host link when meeting is finished
        $meetingEnd = strtotime($meeting['waktu_selesai']);
        if (time() >= $meetingEnd) {
            return redirect()->back()->with('error', 'Meeting sudah selesai, link Host tidak tersedia');
        }

        // Only allow within 1 hour before meeting start
        $meetingStart = strtotime($meeting['waktu_mulai']);
        $now = time();
        if ($now < ($meetingStart - 3600)) {
            return redirect()->back()->with('error', 'Link Host hanya tersedia 1 jam sebelum meeting dimulai');
        }

        try {
            $zoomData = $this->zoomLibrary->getMeeting($meeting['zoom_meeting_id']);
            if (!$zoomData || empty($zoomData['start_url'])) {
                return redirect()->back()->with('error', 'Gagal mengambil data Zoom meeting');
            }

            // Update the stored URLs
            $this->meetingModel->setValidationRules([]);
            $this->meetingModel->update($id, [
                'zoom_start_url' => $zoomData['start_url'],
                'zoom_join_url'  => $zoomData['join_url'],
            ]);

            // Redirect directly to the fresh start_url
            return redirect()->to($zoomData['start_url']);
        } catch (\Exception $e) {
            log_message('error', 'Exception during refreshZoom: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal refresh Zoom: ' . $e->getMessage());
        }
    }

    /**
     * Public shortlink to host a Zoom meeting — no login required.
     * Valid only within 1 hour before meeting start and until meeting ends.
     */
    public function startHost($token = null)
    {
        if (empty($token)) {
            return redirect()->to('/')->with('error', 'Token tidak valid');
        }

        $meeting = $this->meetingModel->where('start_token', $token)->first();
        if (!$meeting || empty($meeting['zoom_meeting_id'])) {
            return $this->renderHostError('Link host tidak valid atau sudah kadaluarsa.');
        }

        if ($meeting['status'] !== 'approved') {
            return $this->renderHostError('Meeting tidak dalam status disetujui.');
        }

        $now = time();
        $meetingEnd = strtotime($meeting['waktu_selesai']);
        if ($now >= $meetingEnd) {
            return $this->renderHostError('Meeting sudah selesai, link host tidak tersedia.');
        }

        $meetingStart = strtotime($meeting['waktu_mulai']);
        if ($now < ($meetingStart - 3600)) {
            $availableTime = date('d M Y, H:i', $meetingStart - 3600);
            $meetingTime   = date('d M Y, H:i', $meetingStart);
            return $this->renderHostError(
                "Link host hanya tersedia 1 jam sebelum meeting dimulai.<br>" .
                "Meeting: <strong>" . esc($meeting['nama_keg']) . "</strong><br>" .
                "Waktu meeting: {$meetingTime}<br>" .
                "Link aktif pada: {$availableTime}"
            );
        }

        try {
            $zoomData = $this->zoomLibrary->getMeeting($meeting['zoom_meeting_id']);
            if (!$zoomData || empty($zoomData['start_url'])) {
                return $this->renderHostError('Gagal mengambil data Zoom meeting. Silakan coba lagi.');
            }

            $this->meetingModel->setValidationRules([]);
            $this->meetingModel->update($meeting['id'], [
                'zoom_start_url' => $zoomData['start_url'],
                'zoom_join_url'  => $zoomData['join_url'],
            ]);

            return redirect()->to($zoomData['start_url']);
        } catch (\Exception $e) {
            log_message('error', 'Exception during startHost: ' . $e->getMessage());
            return $this->renderHostError('Terjadi kesalahan saat menghubungkan ke Zoom. Silakan coba lagi.');
        }
    }

    /**
     * Render a user-friendly error page for the host shortlink.
     */
    protected function renderHostError(string $message)
    {
        // NOTE: All user-facing dynamic content in $message MUST be escaped via esc()
        // before passing to renderHostError(). The view renders $message as raw HTML.
        return view('meeting/host_error', ['message' => $message]);
    }

    /**
     * Send WhatsApp notification to pegawai about Zoom
     */
    protected function sendWhatsAppToPegawai(array $meeting, string $type = 'zoom_created'): void
    {
        try {
            $whatsappEnabled = env('whatsapp.enabled', true);

            if (!$whatsappEnabled) {
                log_message('debug', 'WhatsApp not configured; skipping Zoom notification.');
                return;
            }

            // Get pegawai info
            $pegawai = $this->pegawaiModel->find($meeting['pegawai_id']);
            if (!$pegawai) {
                log_message('warning', 'Pegawai not found for meeting ' . $meeting['id']);
                return;
            }

            $ruangan = $this->ruanganModel->find($meeting['ruangan_id']);
            $namaKegiatan = $meeting['nama_keg'];
            $tempat       = ($ruangan['nama_ruangan'] ?? '') . ' - ' . ($ruangan['tipe'] ?? '');
            $waktu        = date('d M Y H:i', strtotime($meeting['waktu_mulai'])) . ' - ' . date('H:i', strtotime($meeting['waktu_selesai']));

            if ($type === 'zoom_created') {
                $shortlink = !empty($meeting['start_token'])
                    ? base_url('zoom/start/' . $meeting['start_token'])
                    : '';
                $shortlinkLine = $shortlink
                    ? "\n\n🖥️ *Link Host (H-1 jam)*:\n{$shortlink}\nℹ️ _Link ini hanya aktif 1 jam sebelum meeting dimulai._"
                    : "\n\nℹ️ _Link Host tersedia 1 jam sebelum meeting pada website meetingku._";

                $message = "*[Meetingku]*\n" .
                           "✅ Pengajuan meeting Anda telah disetujui\n\n" .
                           "*Nama Kegiatan*: $namaKegiatan\n" .
                           "*Tempat*: $tempat\n" .
                           "*Waktu*: $waktu\n\n" .
                           "🔗 *Link Join (Peserta)*:\n" . ($meeting['zoom_join_url'] ?? '-') .
                           $shortlinkLine;
            } elseif ($type === 'zoom_updated') {
                $message = "*[Meetingku]*\n" .
                           "📝 Jadwal Zoom meeting telah diubah\n\n" .
                           "*Nama Kegiatan*: $namaKegiatan\n" .
                           "*Waktu Baru*: $waktu\n\n" .
                           "Link Zoom tetap sama:\n" .
                           "🔗 *Join*: " . ($meeting['zoom_join_url'] ?? '-');
            } elseif ($type === 'cancelled') {
                $message = "*[Meetingku]*\n" .
                           "❌ Meeting telah dibatalkan\n\n" .
                           "*Nama Kegiatan*: $namaKegiatan\n" .
                           "*Waktu*: $waktu\n\n" .
                           "Link Zoom sudah tidak berlaku.";
            } else {
                return;
            }

            // Determine recipients: pegawai no_hp + admin fallback
            $recipients = [];
            if (!empty($pegawai['no_hp'])) {
                $recipients[] = $pegawai['no_hp'];
            } else {
                // Fallback to admin number only if pegawai has no phone
                $adminTo = env('whatsapp.to');
                if ($adminTo) {
                    $recipients[] = $adminTo;
                }
            }

            foreach ($recipients as $to) {
                $this->sendWhatsAppMessage($to, $message);
            }
        } catch (\Throwable $tex) {
            log_message('error', 'WhatsApp Zoom notify exception: ' . $tex->getMessage());
        }
    }

    /**
     * Queue a WhatsApp message for asynchronous delivery by wa:worker.
     */
    protected function sendWhatsAppMessage(string $to, string $message): bool
    {
        $queueId = (new WaMessageQueueModel())->insert([
            'api_key_id' => null,
            'to_number' => $to,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'scheduled_at' => date('Y-m-d H:i:s'),
        ], true);

        if ($queueId === false) {
            log_message('error', 'WhatsApp queue insert failed for ' . $to);
            return false;
        }

        log_message('info', 'WhatsApp queued via worker for ' . $to . ' with queue ID: ' . $queueId);
        return true;
    }}


