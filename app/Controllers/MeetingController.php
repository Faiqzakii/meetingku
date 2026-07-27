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
            $dayOfWeek = (int) date('N', strtotime($waktuMulai));
            $closeTime = $dayOfWeek <= 4 ? '17:00:00' : '17:30:00';
            $waktuSelesai = date('Y-m-d ' . $closeTime, strtotime($waktuMulai));
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

    protected function normalizeFasilitasLainnya(?string $value): string
    {
        return trim(preg_replace('/^(?:\s*Lainnya\s*:\s*)+/i', '', $value ?? '') ?? '');
    }

    protected function resolveFasilitas(): ?string
    {
        $fasilitas = $this->request->getPost('fasilitas');
        $fasilitasLainnya = $this->normalizeFasilitasLainnya($this->request->getPost('fasilitas_lainnya'));

        if ($fasilitas && is_array($fasilitas)) {
            if (($key = array_search('Lainnya', $fasilitas)) !== false) {
                if ($fasilitasLainnya !== '') {
                    $fasilitas[$key] = 'Lainnya: ' . $fasilitasLainnya;
                }

            }
        }

        return $fasilitas ? json_encode($fasilitas) : null;
    }

    protected function notifyGroupWhatsApp(
        array $data,
        string $header,
        string $kind,
        int $meetingId,
        ?string $scheduledAt = null
    ): void {
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

            $this->sendWhatsAppToGroup($message, [
                'meta_kind'       => $kind,
                'meta_meeting_id' => $meetingId,
            ], $scheduledAt);
        } catch (\Throwable $tex) {
            log_message('error', 'WhatsApp notify exception: ' . $tex->getMessage());
        }
    }

    /**
     * H-2 schedule for the new-meeting group notification:
   * fires two days before $waktuMulai at 09:00 local time.
   * Past H-2 (meeting <2 days away) collapses to "now" so we never miss it.
   */
    protected function scheduleNewMeetingGroupNotification(
        string $waktuMulai,
        ?int $now = null
    ): string {
        $now = $now ?? time();
        $meetingTs  = strtotime($waktuMulai);
        $hMinusTwo  = strtotime('-2 days', strtotime(date('Y-m-d 09:00:00', $meetingTs)));
        $scheduleTs = max($now, $hMinusTwo);

        return date('Y-m-d H:i:s', $scheduleTs);
    }

    /**
     * Edit notif only goes out once the matching new-meeting notif has
     * actually been sent (status='sent' on the queue row) AND we are inside
     * the H-2..meeting-end window. Pre-H-2 edits are silent — the new
     * notif itself will carry the updated payload at H-2.
     */
    protected function isEditGroupNotificationEligible(
        int $meetingId,
        string $waktuMulai,
        string $waktuSelesai,
        ?int $now = null
    ): bool {
        $now   = $now ?? time();
        $start = strtotime($waktuMulai);
        $end   = strtotime($waktuSelesai);
        if ($start === false || $end === false || $now >= $end) {
            return false;
        }

        $hMinusTwo = strtotime('-2 days', strtotime(date('Y-m-d 09:00:00', $start)));
        if ($now < $hMinusTwo) {
            return false;
        }

        $row = db_connect()
            ->table('wa_message_queue')
            ->select('id')
            ->where('meta_kind', 'new_meeting')
            ->where('meta_meeting_id', $meetingId)
            ->where('status', 'sent')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row !== null;
    }

    /**
     * Build a field-by-field diff of meeting editable columns.
     * Returns array of [label, before, after] for fields that actually
     * changed; empty array if nothing in the whitelisted set differs.
   * Fasilitas compared as a sorted set; waktu rendered for humans.
     */
    protected function buildMeetingEditDiff(array $before, array $after): array
    {
        $diff = [];

        if (($before['nama_keg'] ?? null) !== ($after['nama_keg'] ?? null)) {
            $diff[] = ['label' => 'Nama Kegiatan', 'before' => (string) ($before['nama_keg'] ?? '-'), 'after' => (string) ($after['nama_keg'] ?? '-')];
        }

        if (($before['jumlah_peserta'] ?? null) !== ($after['jumlah_peserta'] ?? null)) {
            $diff[] = [
                'label' => 'Jumlah Peserta',
                'before' => (string) ($before['jumlah_peserta'] ?? '-'),
                'after'  => (string) ($after['jumlah_peserta'] ?? '-'),
            ];
        }

        if (($before['waktu_mulai'] ?? null) !== ($after['waktu_mulai'] ?? null)
            || ($before['waktu_selesai'] ?? null) !== ($after['waktu_selesai'] ?? null)) {
            $beforeWaktu = $this->formatWaktuRange(
                $before['waktu_mulai'] ?? null,
                $before['waktu_selesai'] ?? null
            );
            $afterWaktu = $this->formatWaktuRange(
                $after['waktu_mulai'] ?? null,
                $after['waktu_selesai'] ?? null
            );
            $diff[] = ['label' => 'Waktu', 'before' => $beforeWaktu, 'after' => $afterWaktu];
        }

        if (($before['ruangan_id'] ?? null) !== ($after['ruangan_id'] ?? null)) {
            $diff[] = [
                'label' => 'Ruangan',
                'before' => $this->resolveRuanganLabel($before['ruangan_id'] ?? null),
                'after'  => $this->resolveRuanganLabel($after['ruangan_id'] ?? null),
            ];
        }

        $beforeFasilitas = $this->normalizeFasilitasForDisplay($before['fasilitas'] ?? null);
        $afterFasilitas  = $this->normalizeFasilitasForDisplay($after['fasilitas'] ?? null);
        if ($beforeFasilitas !== $afterFasilitas) {
            $diff[] = ['label' => 'Fasilitas', 'before' => $beforeFasilitas, 'after' => $afterFasilitas];
        }

        return $diff;
    }

    /**
     * Render the edit WhatsApp message: header line, meeting context,
     * then a "Sebelum → Menjadi" line per changed field.
     */
    protected function renderMeetingEditMessage(array $after, array $diff, ?string $ruanganName, ?string $ruanganTipe, ?string $pegawaiName): string
    {
        $tempat = trim(($ruanganName ?? '-') . ' - ' . ($ruanganTipe ?? ''), " -");
        $waktu  = $this->formatWaktuRange($after['waktu_mulai'] ?? null, $after['waktu_selesai'] ?? null);
        $oleh   = $pegawaiName ?? '-';

        $lines = [
            '*[Meetingku]*',
            'Terdapat edit detail meeting',
            '',
            '*Nama Kegiatan*: ' . ($after['nama_keg'] ?? '-'),
            '*Tempat*: ' . $tempat,
            '*Waktu*: ' . $waktu,
            '*Oleh*: ' . $oleh,
            '',
            '*Perubahan*:',
        ];

        foreach ($diff as $change) {
            $lines[] = '• *' . $change['label'] . '*: ' . $change['before'] . ' → ' . $change['after'];
        }

        return implode("\n", $lines);
    }

    /**
     * Enqueue the edit-notification when the gate is open. Compares
     * the row before the update with the new payload, and short-circuits
     * if the whitelisted editable fields are all unchanged.
     */
    protected function notifyGroupEditIfChanged(array $before, array $after, int $meetingId): void
    {
        $diff = $this->buildMeetingEditDiff($before, $after);
        if ($diff === []) {
            log_message('info', 'Group edit notif skipped for meeting ' . $meetingId . ' (no whitelisted field changed)');
            return;
        }

        $ruangan = !empty($after['ruangan_id']) ? $this->ruanganModel->find($after['ruangan_id']) : null;
        $pegawai = !empty($after['pegawai_id']) ? $this->pegawaiModel->find($after['pegawai_id']) : null;

        $message = $this->renderMeetingEditMessage(
            $after,
            $diff,
            $ruangan['nama_ruangan'] ?? null,
            $ruangan['tipe'] ?? null,
            $pegawai['nama'] ?? null
        );

        $this->sendWhatsAppToGroup($message, [
            'meta_kind'       => 'edit_meeting',
            'meta_meeting_id' => $meetingId,
        ]);
    }

    protected function formatWaktuRange(?string $mulai, ?string $selesai): string
    {
        if (empty($mulai) || empty($selesai)) {
            return '-';
        }
        $start = strtotime($mulai);
        $end   = strtotime($selesai);
        if ($start === false || $end === false) {
            return '-';
        }

        return date('d M Y H:i', $start) . ' - ' . date('H:i', $end);
    }

    protected function resolveRuanganLabel($ruanganId): string
    {
        if (empty($ruanganId)) {
            return '-';
        }
        $row = $this->ruanganModel->find($ruanganId);
        if (!$row) {
            return '#' . $ruanganId;
        }

        return ($row['nama_ruangan'] ?? '-') . ' - ' . ($row['tipe'] ?? '-');
    }

    protected function normalizeFasilitasForDisplay($value): string
    {
        if (empty($value)) {
            return '-';
        }
        $decoded = json_decode((string) $value, true);
        if (!is_array($decoded) || $decoded === []) {
            return '-';
        }
        sort($decoded, SORT_STRING);

        return implode(', ', $decoded);
    }


    // ── Zoom / WhatsApp helpers ─────────────────────────────────────
    protected function sendWhatsAppToGroup(string $message, array $meta = [], ?string $scheduledAt = null): bool
    {
        return $this->sendWhatsAppMessage(self::WHATSAPP_GROUP_ID, $message, $meta, $scheduledAt);
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

    protected function buildManualZoomUpdateData(
        string $zoomJoinUrl,
        ?string $zoomMeetingId,
        array $meeting,
        ?array $sourceMeeting
    ): array {
        $canonicalJoinUrl = $sourceMeeting['zoom_join_url'] ?? $zoomJoinUrl;

        return [
            'zoom_join_url' => $canonicalJoinUrl,
            'zoom_meeting_id' => $zoomMeetingId,
            'zoom_source_meeting_id' => isset($sourceMeeting['id']) ? (int) $sourceMeeting['id'] : null,
            'start_token' => !empty($meeting['start_token'])
                ? $meeting['start_token']
                : $this->generateStartToken(),
        ];
    }

    protected function findSharedZoomSource(?string $zoomMeetingId, int $excludeId): ?array
    {
        if (empty($zoomMeetingId)) {
            return null;
        }

        return $this->meetingModel
            ->where('zoom_meeting_id', $zoomMeetingId)
            ->where('id !=', $excludeId)
            ->where('start_token IS NOT NULL')
            ->orderBy('waktu_mulai', 'ASC')
            ->first();
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

            $meetingId = (int) $result;
            $this->notifyGroupWhatsApp(
                $data,
                'Terdapat pengajuan meeting baru',
                'new_meeting',
                $meetingId,
                $this->scheduleNewMeetingGroupNotification($waktuMulai)
            );
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

            $meetingId = (int) $id;
            if ($this->isEditGroupNotificationEligible($meetingId, $data['waktu_mulai'], $data['waktu_selesai'])) {
                $this->notifyGroupEditIfChanged(
                    array_merge($meeting, ['pegawai_id' => $meeting['pegawai_id']]),
                    array_merge($data, ['pegawai_id' => $meeting['pegawai_id']]),
                    $meetingId
                );
            } else {
                log_message('info', 'Group edit notif skipped for meeting ' . $meetingId . ' (new-meeting notif not yet sent or outside H-2 window)');
            }
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
        $sourceMeeting = $this->findSharedZoomSource($zoomMeetingId, (int) $id);
        $previousJoinUrl = (string) ($meeting['zoom_join_url'] ?? '');
        $isFirstZoomLink = $previousJoinUrl === '';
        $updateData = $this->buildManualZoomUpdateData($zoomJoinUrl, $zoomMeetingId, $meeting, $sourceMeeting);
        $updated = $this->meetingModel->update($id, $updateData);

        if ($updated === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan link Zoom manual');
        }

        $meeting['zoom_join_url'] = $updateData['zoom_join_url'];
        $meeting['zoom_meeting_id'] = $updateData['zoom_meeting_id'];
        $meeting['start_token'] = $updateData['start_token'];

        if ($isFirstZoomLink) {
            $this->sendWhatsAppToPegawai($meeting, 'zoom_manual_created');
            $message = 'Link Zoom manual berhasil disimpan dan dikirim ke pegawai';
        } elseif ($previousJoinUrl !== $meeting['zoom_join_url']) {
            $this->sendWhatsAppToPegawai($meeting, 'zoom_manual_updated', $previousJoinUrl);
            $message = 'Link Zoom manual berhasil diubah dan notifikasi dikirim ke pegawai';
        } else {
            $message = 'Link Zoom manual berhasil disimpan';
        }

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

        $zoomMeeting = $meeting;
        if (!empty($meeting['zoom_source_meeting_id'])) {
            $zoomMeeting = $this->meetingModel->find($meeting['zoom_source_meeting_id']) ?: $meeting;
        }

        if (empty($zoomMeeting['zoom_start_url'])) {
            return redirect()->to($zoomMeeting['zoom_join_url']);
        }

        try {
            $zoomData = $this->zoomLibrary->getMeeting($zoomMeeting['zoom_meeting_id']);
            if (!$zoomData || empty($zoomData['start_url'])) {
                return redirect()->to($zoomMeeting['zoom_join_url']);
            }

            $this->meetingModel->setValidationRules([]);
            $this->meetingModel->update($zoomMeeting['id'], [
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
        // before passing to renderHostError(). The view renders $message as raw HTML.
        return view('meeting/host_error', ['message' => $message]);
    }

    protected function sendWhatsAppToPegawai(array $meeting, string $type = 'zoom_created', ?string $previousJoinUrl = null): void
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
            $tempat = ($ruangan['nama_ruangan'] ?? '') . ' - ' . ($ruangan['tipe'] ?? '');
            $waktu = date('d M Y H:i', strtotime($meeting['waktu_mulai'])) . ' - ' . date('H:i', strtotime($meeting['waktu_selesai']));
            if (in_array($type, ['zoom_created', 'zoom_manual_created'], true)) {
                $title = $type === 'zoom_manual_created' ? "✅ Link Zoom meeting telah tersedia\n\n" : "✅ Pengajuan meeting Anda telah disetujui\n\n";
                $message = "*[Meetingku]*\n{$title}*Nama Kegiatan*: {$namaKegiatan}\n*Tempat*: {$tempat}\n*Waktu*: {$waktu}\n\n🔗 *Link Join (Peserta)*:\n" . ($meeting['zoom_join_url'] ?? '-');
                if (!empty($meeting['start_token'])) {
                    $message .= "\n\n🖥️ *Link Host (H-1 jam)*:\n" . base_url('zoom/start/' . $meeting['start_token']);
                }
            } elseif ($type === 'zoom_manual_updated') {
                $message = "*[Meetingku]*\n📝 Detail meeting diperbarui\n\n*Nama Kegiatan*: {$namaKegiatan}\n*Tempat*: {$tempat}\n*Waktu*: {$waktu}\n\n*Perubahan*:\n• *Link Zoom*: " . ($previousJoinUrl ?: '-') . ' → ' . ($meeting['zoom_join_url'] ?? '-') . "\n\n🔗 *Link Join (Peserta)*:\n" . ($meeting['zoom_join_url'] ?? '-');
                if (!empty($meeting['start_token'])) {
                    $message .= "\n\n🖥️ *Link Host (H-1 jam)*:\n" . base_url('zoom/start/' . $meeting['start_token']);
                }
            } elseif ($type === 'zoom_updated') {
                $message = "*[Meetingku]*\n📝 Jadwal Zoom meeting telah diubah\n\n*Nama Kegiatan*: {$namaKegiatan}\n*Waktu Baru*: {$waktu}\n\nLink Zoom tetap sama:\n🔗 *Join*: " . ($meeting['zoom_join_url'] ?? '-');
            } elseif ($type === 'cancelled') {
                $message = "*[Meetingku]*\n❌ Meeting telah dibatalkan\n\n*Nama Kegiatan*: {$namaKegiatan}\n*Waktu*: {$waktu}\n\nLink Zoom sudah tidak berlaku.";
            } else {
                return;
            }
            $recipients = !empty($pegawai['no_hp']) ? [$pegawai['no_hp']] : array_filter([(string) env('whatsapp.to')]);
            foreach ($recipients as $to) {
                $this->sendWhatsAppMessage($to, $message);
            }
        } catch (\Throwable $tex) {
            log_message('error', 'WhatsApp Zoom notify exception: ' . $tex->getMessage());
        }
    }

    protected function sendWhatsAppMessage(
        string $to,
        string $message,
        array $meta = [],
        ?string $scheduledAt = null
    ): bool {
        $queueId = (new WaMessageQueueModel())->insert([
            'api_key_id'       => null,
            'to_number'        => $to,
            'message'          => $message,
            'meta_kind'        => $meta['meta_kind'] ?? null,
            'meta_meeting_id'  => isset($meta['meta_meeting_id']) ? (int) $meta['meta_meeting_id'] : null,
            'status'           => 'pending',
            'attempts'         => 0,
            'max_attempts'     => 3,
            'scheduled_at'     => $scheduledAt ?? date('Y-m-d H:i:s'),
        ], true);

        if ($queueId === false) {
            log_message('error', 'WhatsApp queue insert failed for ' . $to);
            return false;
        }

        return true;
    }
}
