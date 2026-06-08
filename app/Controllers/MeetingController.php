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

    // ── Form token helpers ──────────────────────────────────────────

    protected function consumeFormToken(): ?string
    {
        $formToken = $this->request->getPost('form_token');
        if (!$formToken) {
            return null;
        }

        $usedTokens = session()->get('used_form_tokens');
        if (!is_array($usedTokens)) {
            $usedTokens = [];
        }
        if (in_array($formToken, $usedTokens)) {
            return '__duplicate__';
        }

        $usedTokens[] = $formToken;
        session()->set('used_form_tokens', $usedTokens);
        return $formToken;
    }

    protected function releaseFormToken(string $formToken): void
    {
        $currentTokens = session()->get('used_form_tokens');
        if (!is_array($currentTokens)) {
            $currentTokens = [];
        }
        $currentTokens = array_diff($currentTokens, [$formToken]);
        session()->set('used_form_tokens', $currentTokens);
    }

    protected function pruneFormTokens(): void
    {
        $currentTokens = session()->get('used_form_tokens');
        if (is_array($currentTokens) && count($currentTokens) > 10) {
            session()->set('used_form_tokens', array_slice($currentTokens, -10));
        }
    }

    // ── Shared meeting data helpers ─────────────────────────────────

    protected function resolveTimeRange(): array
    {
        $waktuMulai = $this->request->getPost('waktu_mulai');
        $durasi = $this->request->getPost('durasi');

        $waktuMulai = date('Y-m-d H:i:s', strtotime($waktuMulai));

        if ($durasi == 'Penuh') {
            $waktuSelesai = date('Y-m-d 23:59:59', strtotime($waktuMulai));
        } else {
            $durasi = (int) $durasi;
            $waktuSelesai = date('Y-m-d H:i:s', strtotime($waktuMulai . ' + ' . $durasi . ' minutes'));
        }

        return [$waktuMulai, $waktuSelesai];
    }

    protected function buildValidationRules(bool $isOnline): array
    {
        $rules = [
            'nama_keg' => 'required|min_length[3]|max_length[100]',
            'ruangan_id' => 'required|integer|is_not_unique[ruangan.id]',
            'waktu_mulai' => 'required|valid_date[Y-m-d H:i:s]',
            'waktu_selesai' => 'required|valid_date[Y-m-d H:i:s]',
        ];
        $rules['jumlah_peserta'] = $isOnline
            ? 'permit_empty'
            : 'required|integer|greater_than[0]';
        return $rules;
    }

    protected function resolveFasilitas(): ?string
    {
        $fasilitas = $this->request->getPost('fasilitas');
        $fasilitasLainnya = $this->request->getPost('fasilitas_lainnya');

        if ($fasilitas && is_array($fasilitas)) {
            if (($key = array_search('Lainnya', $fasilitas)) !== false) {
                if (!empty($fasilitasLainnya)) {
                    $fasilitas[$key] = 'Lainnya: ' . $fasilitasLainnya;
                }
            }
        }

        return $fasilitas ? json_encode($fasilitas) : null;
    }

    protected function notifyGroupWhatsApp(array $data, string $header): void
    {
        try {
            if (!env('whatsapp.enabled', true)) {
                return;
            }

            $ruangan = $this->ruanganModel->find($data['ruangan_id']);
            $pegawai = $this->pegawaiModel->find($data['pegawai_id']);

            $namaKegiatan = $data['nama_keg'];
            $tempat       = ($ruangan['nama_ruangan'] ?? '') . ' - ' . ($ruangan['tipe'] ?? '');
            $waktu        = date('d M Y H:i', strtotime($data['waktu_mulai'])) . ' - ' . date('H:i', strtotime($data['waktu_selesai']));
            $oleh         = $pegawai['nama'] ?? '';
            $jmlPeserta   = $data['jumlah_peserta'] ?? '';
            $fasilitasStr = $data['fasilitas'] ? implode(', ', json_decode($data['fasilitas'], true) ?? []) : '-';

            $message = "*[Meetingku]*\n"
                . "{$header}\n\n"
                . "*Nama Kegiatan*: {$namaKegiatan}\n"
                . "*Tempat*: {$tempat}\n"
                . "*Jumlah Peserta*: {$jmlPeserta}\n"
                . "*Waktu*: {$waktu}\n"
                . "*Fasilitas*: {$fasilitasStr}\n"
                . "*Oleh*: {$oleh}";

            $this->sendWhatsAppToGroup($message);
        } catch (\Throwable $tex) {
            log_message('error', 'WhatsApp notify exception: ' . $tex->getMessage());
        }
    }

    // ── Zoom / WhatsApp helpers ─────────────────────────────────────

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

    protected function generateStartToken(): string
    {
        return bin2hex(random_bytes(10));
    }

    // ── Controller actions ──────────────────────────────────────────

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

        $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $startDate = $startParam ? date('Y-m-d 00:00:00', strtotime($startParam)) : $monday;
        $endDate   = $endParam ? date('Y-m-d 23:59:59', strtotime($endParam)) : $sunday;

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

        $formToken = $this->consumeFormToken();
        if ($formToken === null) {
            return redirect()->back()->with('error', 'Token form tidak valid')->withInput();
        }
        if ($formToken === '__duplicate__') {
            return redirect()->back()->with('error', 'Form telah dikirim. Mohon tunggu proses selesai.')->withInput();
        }

        [$waktuMulai, $waktuSelesai] = $this->resolveTimeRange();

        $ruanganId = $this->request->getPost('ruangan_id');
        $ruangan = $this->ruanganModel->find($ruanganId);
        $isOnline = ($ruangan && $ruangan['tipe'] === 'Online');

        $this->meetingModel->setValidationRules($this->buildValidationRules($isOnline));

        $fasilitasJson = $this->resolveFasilitas();

        $data = [
            'nama_keg' => $this->request->getPost('nama_keg'),
            'jumlah_peserta' => $this->request->getPost('jumlah_peserta') ?: null,
            'fasilitas' => $fasilitasJson,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'ruangan_id' => $this->request->getPost('ruangan_id'),
            'pegawai_id' => (int) session()->get('pegawai_id'),
            'status' => 'pending'
        ];

        if (!$this->meetingModel->validate($data)) {
            return redirect()->back()
                ->with('errors', $this->meetingModel->errors())
                ->withInput();
        }

        try {
            $result = $this->meetingModel->insert($data);
            if ($result === false) {
                return redirect()->back()
                    ->with('error', 'Gagal membuat meeting: ' . implode(', ', $this->meetingModel->errors()))
                    ->withInput();
            }

            $this->notifyGroupWhatsApp($data, 'Terdapat pengajuan meeting baru');
            $this->pruneFormTokens();

            return redirect()->back()->with('success', 'Meeting berhasil dibuat');
        } catch (\Exception $e) {
            $this->releaseFormToken($formToken);
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

        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengupdate meeting ini');
        }

        $formToken = $this->consumeFormToken();
        if ($formToken === null) {
            return redirect()->back()->with('error', 'Token form tidak valid')->withInput();
        }
        if ($formToken === '__duplicate__') {
            return redirect()->back()->with('error', 'Form telah dikirim. Mohon tunggu proses selesai.')->withInput();
        }

        [$waktuMulai, $waktuSelesai] = $this->resolveTimeRange();

        $ruanganId = $this->request->getPost('ruangan_id');
        $ruangan = $this->ruanganModel->find($ruanganId);
        $isOnline = ($ruangan && $ruangan['tipe'] === 'Online');

        $this->meetingModel->setValidationRules($this->buildValidationRules($isOnline));

        $fasilitasJson = $this->resolveFasilitas();

        $data = [
            'nama_keg' => $this->request->getPost('nama_keg'),
            'jumlah_peserta' => $this->request->getPost('jumlah_peserta') ?: null,
            'fasilitas' => $fasilitasJson,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'ruangan_id' => $this->request->getPost('ruangan_id'),
            'last_edited_by' => (int) session()->get('pegawai_id'),
            'last_edited_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->meetingModel->validate($data)) {
            return redirect()->back()
                ->with('errors', $this->meetingModel->errors())
                ->withInput();
        }

        try {
            if ($this->meetingModel->update($id, $data) === false) {
                return redirect()->back()
                    ->with('error', 'Gagal mengupdate meeting: ' . implode(', ', $this->meetingModel->errors()))
                    ->withInput();
            }

            if (!empty($meeting['zoom_meeting_id'])) {
                $scheduleChanged = ($meeting['waktu_mulai'] !== $data['waktu_mulai'])
                                || ($meeting['waktu_selesai'] !== $data['waktu_selesai'])
                                || ($meeting['nama_keg'] !== $data['nama_keg']);
                if ($scheduleChanged) {
                    $this->syncZoomOnScheduleChange($id, $meeting, $data);
                }
            }

            $this->notifyGroupWhatsApp(
                array_merge($data, ['pegawai_id' => $meeting['pegawai_id']]),
                'Terdapat edit detail meeting'
            );
            $this->pruneFormTokens();

            return redirect()->to('/upcoming')->with('success', 'Meeting berhasil diupdate');
        } catch (\Exception $e) {
            $this->releaseFormToken($formToken);
            return redirect()->back()
                ->with('error', 'Gagal mengupdate meeting: ' . $e->getMessage())
                ->withInput();
        }
    }

    protected function syncZoomOnScheduleChange(int $id, array $meeting, array $data): void
    {
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
                $meeting['waktu_mulai']   = $data['waktu_mulai'];
                $meeting['waktu_selesai'] = $data['waktu_selesai'];
                $meeting['nama_keg']      = $data['nama_keg'];
                $this->sendWhatsAppToPegawai($meeting, 'zoom_updated');
            }
        } catch (\Throwable $ze) {
            log_message('error', 'Failed to update Zoom meeting: ' . $ze->getMessage());
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

        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus meeting ini');
        }

        if (!empty($meeting['zoom_meeting_id'])) {
            try {
                $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
            } catch (\Exception $e) {
                log_message('error', 'Failed to delete Zoom meeting: ' . $e->getMessage());
                // Continue with DB deletion even if Zoom fails
            }
        }

        try {
            if ($this->meetingModel->delete($id) === false) {
                $errors = $this->meetingModel->errors();
                return redirect()->back()->with('error', 'Gagal menghapus meeting: ' . implode(', ', $errors));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus meeting: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Meeting berhasil dihapus');
    }

    public function updateStatus($id = null)
    {
        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return redirect()->to('/upcoming')->with('error', 'Meeting tidak ditemukan');
        }

        $status = $this->request->getPost('status');
        $currentPegawaiId = (int) session()->get('pegawai_id');
        $isOwner = (int) $meeting['pegawai_id'] === $currentPegawaiId;

        if (!$this->isAdmin()) {
            if (!$isOwner || $status !== 'cancelled') {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah status meeting ini');
            }
        }

        if (!in_array($status, ['approved', 'rejected', 'pending', 'cancelled'])) {
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

            if (in_array($status, ['rejected', 'cancelled']) && !empty($meeting['zoom_meeting_id'])) {
                try {
                    $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
                } catch (\Throwable $ze) {
                    log_message('error', 'Failed to delete Zoom meeting: ' . $ze->getMessage());
                }
                $updateData['zoom_meeting_id'] = null;
                $updateData['zoom_join_url']   = null;
                $updateData['zoom_start_url']  = null;
                $updateData['start_token']     = null;

                $this->sendWhatsAppToPegawai($meeting, 'cancelled');
            }

            $result = $this->meetingModel->update($id, $updateData);
            if ($result === false) {
                return redirect()->back()
                    ->with('error', 'Gagal mengupdate status meeting: ' . implode(', ', $this->meetingModel->errors()));
            }

            return redirect()->back()
                ->with('success', 'Status meeting berhasil diupdate menjadi ' . strtoupper($status));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate status meeting: ' . $e->getMessage());
        }
    }

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

        if (strtotime($meeting['waktu_selesai']) < time()) {
            return redirect()->back()->with('error', 'Tidak bisa membuat Zoom — meeting sudah selesai/lewat');
        }

        if (!empty($meeting['zoom_meeting_id'])) {
            return redirect()->back()->with('error', 'Zoom meeting sudah dibuat untuk meeting ini');
        }

        $ruangan = $this->ruanganModel->find($meeting['ruangan_id']);
        if (!$ruangan || !in_array($ruangan['tipe'], ['Online', 'Hybrid'])) {
            return redirect()->back()->with('error', 'Ruangan bukan tipe Online/Hybrid');
        }

        if (!$this->zoomLibrary->isEnabled()) {
            return redirect()->back()->with('error', 'Integrasi Zoom belum dikonfigurasi');
        }

        try {
            $startTimestamp = strtotime($meeting['waktu_mulai']);
            $endTimestamp   = strtotime($meeting['waktu_selesai']);
            $duration       = max(30, (int) round(($endTimestamp - $startTimestamp) / 60));
            $startTimeISO   = date('Y-m-d\TH:i:s', $startTimestamp);

            $zoomResult = $this->zoomLibrary->createMeeting(
                $meeting['nama_keg'],
                $startTimeISO,
                $duration,
                'Meeting: ' . $meeting['nama_keg']
            );

            if (!$zoomResult) {
                return redirect()->back()->with('error', 'Gagal membuat Zoom meeting. Periksa konfigurasi Zoom API.');
            }

            $startToken = $this->generateStartToken();
            $this->meetingModel->setValidationRules([]);
            $this->meetingModel->update($id, [
                'zoom_meeting_id' => $zoomResult['id'],
                'zoom_join_url'   => $zoomResult['join_url'],
                'zoom_start_url'  => $zoomResult['start_url'],
                'start_token'     => $startToken,
            ]);

            $meeting['zoom_join_url']  = $zoomResult['join_url'];
            $meeting['zoom_start_url'] = $zoomResult['start_url'];
            $meeting['start_token']    = $startToken;

            $this->sendWhatsAppToPegawai($meeting, 'zoom_created');

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
        $isFirstZoomLink = empty($meeting['zoom_join_url']);
        $updated = $this->meetingModel->update($id, [
            'zoom_join_url' => $zoomJoinUrl,
            'zoom_meeting_id' => $zoomMeetingId,
        ]);

        if ($updated === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan link Zoom manual');
        }

        if ($isFirstZoomLink) {
            $meeting['zoom_join_url'] = $zoomJoinUrl;
            $meeting['zoom_meeting_id'] = $zoomMeetingId;
            $this->sendWhatsAppToPegawai($meeting, 'zoom_manual_created');
        }

        $message = $isFirstZoomLink
            ? 'Link Zoom manual berhasil disimpan dan dikirim ke pegawai'
            : 'Link Zoom manual berhasil disimpan';

        return redirect()->back()->with('success', $message);
    }

    public function refreshZoom($id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting || empty($meeting['zoom_meeting_id'])) {
            return redirect()->back()->with('error', 'Meeting tidak memiliki Zoom');
        }

        $currentPegawaiId = (int) session()->get('pegawai_id');
        if (!$this->isAdmin() && (int) $meeting['pegawai_id'] !== $currentPegawaiId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }

        $meetingEnd = strtotime($meeting['waktu_selesai']);
        if (time() >= $meetingEnd) {
            return redirect()->back()->with('error', 'Meeting sudah selesai, link Host tidak tersedia');
        }

        $meetingStart = strtotime($meeting['waktu_mulai']);
        if (time() < ($meetingStart - 3600)) {
            return redirect()->back()->with('error', 'Link Host hanya tersedia 1 jam sebelum meeting dimulai');
        }

        try {
            $zoomData = $this->zoomLibrary->getMeeting($meeting['zoom_meeting_id']);
            if (!$zoomData || empty($zoomData['start_url'])) {
                return redirect()->back()->with('error', 'Gagal mengambil data Zoom meeting');
            }

            $this->meetingModel->setValidationRules([]);
            $this->meetingModel->update($id, [
                'zoom_start_url' => $zoomData['start_url'],
                'zoom_join_url'  => $zoomData['join_url'],
            ]);

            return redirect()->to($zoomData['start_url']);
        } catch (\Exception $e) {
            log_message('error', 'Exception during refreshZoom: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal refresh Zoom: ' . $e->getMessage());
        }
    }

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

    protected function renderHostError(string $message)
    {
        // NOTE: All user-facing dynamic content in $message MUST be escaped via esc()
        // before passing to renderHostError(). The view renders $message as raw HTML.
        return view('meeting/host_error', ['message' => $message]);
    }

    protected function sendWhatsAppToPegawai(array $meeting, string $type = 'zoom_created'): void
    {
        try {
            if (!env('whatsapp.enabled', true)) {
                return;
            }

            $pegawai = $this->pegawaiModel->find($meeting['pegawai_id']);
            if (!$pegawai) {
                return;
            }

            $ruangan = $this->ruanganModel->find($meeting['ruangan_id']);
            $namaKegiatan = $meeting['nama_keg'];
            $tempat       = ($ruangan['nama_ruangan'] ?? '') . ' - ' . ($ruangan['tipe'] ?? '');
            $waktu        = date('d M Y H:i', strtotime($meeting['waktu_mulai'])) . ' - ' . date('H:i', strtotime($meeting['waktu_selesai']));

            if (in_array($type, ['zoom_created', 'zoom_manual_created'], true)) {
                $shortlink = !empty($meeting['start_token'])
                    ? base_url('zoom/start/' . $meeting['start_token'])
                    : '';
                $shortlinkLine = $shortlink
                    ? "\n\n🖥️ *Link Host (H-1 jam)*:\n{$shortlink}\nℹ️ _Link ini hanya aktif 1 jam sebelum meeting dimulai._"
                    : "\n\nℹ️ _Link Host tersedia 1 jam sebelum meeting pada website meetingku._";

                $title = $type === 'zoom_manual_created'
                    ? "✅ Link Zoom meeting telah tersedia\n\n"
                    : "✅ Pengajuan meeting Anda telah disetujui\n\n";
                $message = "*[Meetingku]*\n"
                    . $title
                    . "*Nama Kegiatan*: {$namaKegiatan}\n"
                    . "*Tempat*: {$tempat}\n"
                    . "*Waktu*: {$waktu}\n\n"
                    . "🔗 *Link Join (Peserta)*:\n" . ($meeting['zoom_join_url'] ?? '-')
                    . $shortlinkLine;
            } elseif ($type === 'zoom_updated') {
                $message = "*[Meetingku]*\n"
                    . "📝 Jadwal Zoom meeting telah diubah\n\n"
                    . "*Nama Kegiatan*: {$namaKegiatan}\n"
                    . "*Waktu Baru*: {$waktu}\n\n"
                    . "Link Zoom tetap sama:\n"
                    . "🔗 *Join*: " . ($meeting['zoom_join_url'] ?? '-');
            } elseif ($type === 'cancelled') {
                $message = "*[Meetingku]*\n"
                    . "❌ Meeting telah dibatalkan\n\n"
                    . "*Nama Kegiatan*: {$namaKegiatan}\n"
                    . "*Waktu*: {$waktu}\n\n"
                    . "Link Zoom sudah tidak berlaku.";
            } else {
                return;
            }

            $recipients = [];
            if (!empty($pegawai['no_hp'])) {
                $recipients[] = $pegawai['no_hp'];
            } else {
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

        return true;
    }
}
