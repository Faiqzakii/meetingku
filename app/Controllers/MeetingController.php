<?php

namespace App\Controllers;

use App\Models\MeetingModel;
use App\Models\RuanganModel;
use App\Models\PegawaiModel;
use App\Models\WaMessageQueueModel;
use App\Libraries\ZoomLibrary;
use CodeIgniter\Controller;
use CodeIgniter\Database\BaseBuilder;
use DateTimeImmutable;

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

    private function issueFormToken(string $action): string
    {
        $token = bin2hex(random_bytes(16));
        $tokens = session()->get('meeting_form_tokens') ?? [];
        $tokens[$token] = $action;
        session()->set('meeting_form_tokens', array_slice($tokens, -20, null, true));
        return $token;
    }

    private function consumeFormToken(string $action): bool
    {
        $token = $this->request->getPost('form_token');
        $tokens = session()->get('meeting_form_tokens') ?? [];
        if (!is_string($token) || ($tokens[$token] ?? null) !== $action) {
            return false;
        }

        unset($tokens[$token]);
        session()->set('meeting_form_tokens', $tokens);
        return true;
    }

    // ── Shared meeting data helpers ─────────────────────────────────

    protected function resolveTimeRange(bool $calendarCreate = false): array
    {
        if ($calendarCreate) {
            $day = $this->request->getPost('tanggal_mulai');
            $time = $this->request->getPost('jam_mulai');
            $start = is_string($day) && $day !== '' && is_string($time) && $time !== '' ? "$day $time" : null;
        } else {
            $start = $this->request->getPost('waktu_mulai');
        }
        $duration = $this->request->getPost('durasi');

        if (!is_string($start) || !preg_match('/^[1-9]\d{3}-\d{2}-\d{2}[T ]\d{2}:\d{2}(?::\d{2})?$/D', $start)) {
            return ['', ''];
        }
        $start = str_replace('T', ' ', $start);
        if (strlen($start) === 16) {
            $start .= ':00';
        }
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $start);
        if (!$parsed || $parsed->format('Y-m-d H:i:s') !== $start) {
            return ['', ''];
        }
        if ($duration === 'Penuh') {
            $end = $parsed->setTime(17, (int) $parsed->format('N') <= 4 ? 0 : 30);
        } else {
            $minutes = filter_var($duration, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1440]]);
            if ($minutes === false) {
                return [$start, ''];
            }
            $end = $parsed->modify("+$minutes minutes");
        }
        return [$start, $end->format('Y-m-d H:i:s')];
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
        return bin2hex(random_bytes(32));
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
            'zoom_start_url' => $sourceMeeting === null && $zoomMeetingId === ($meeting['zoom_meeting_id'] ?? null)
                ? ($meeting['zoom_start_url'] ?? null)
                : null,
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
            ->where('zoom_source_meeting_id', null)
            ->where('start_token IS NOT NULL')
            ->orderBy('zoom_start_url IS NULL', 'ASC', false)
            ->orderBy('waktu_mulai', 'ASC')
            ->first();
    }

    private function isZoomHostSource(array $meeting): bool
    {
        if (empty($meeting['zoom_meeting_id']) || !empty($meeting['zoom_source_meeting_id']) || empty($meeting['zoom_start_url'])) {
            return false;
        }

        // Older manual replacements could leave a start URL for a different provider meeting.
        $path = parse_url($meeting['zoom_start_url'], PHP_URL_PATH);
        return is_string($path) && trim($path, '/') === 's/' . $meeting['zoom_meeting_id'];
    }

    private function zoomHostSource(array $meeting): ?array
    {
        if ($this->isZoomHostSource($meeting)) {
            return $meeting;
        }
        $root = $this->findSharedZoomSource($meeting['zoom_meeting_id'], (int) $meeting['id']);
        return $root !== null && $this->isZoomHostSource($root) ? $root : null;
    }

    private function deleteDetachedZoom(array $meeting): void
    {
        if (!$this->isZoomHostSource($meeting) || $this->meetingModel->hasOtherZoomBookings($meeting)) {
            return;
        }
        try {
            $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete Zoom meeting: ' . $e->getMessage());
        }
    }

    // ── Controller actions ──────────────────────────────────────────

    public function calendar()
    {
        $loggedIn = (bool) session()->get('logged_in');
        $admin = $loggedIn && $this->isAdmin();
        $viewerId = $loggedIn ? (int) session()->get('pegawai_id') : null;
        $view = $this->request->getGet('view');
        $view = in_array($view, ['dayGridMonth', 'timeGridWeek', 'timeGridDay'], true) ? $view : 'dayGridMonth';
        $today = new DateTimeImmutable('today');
        $date = $this->queryDate('date', $today) ?? $today;
        $roomId = $this->requestedRoomId();

        $anchor = $view === 'dayGridMonth' ? $date->modify('first day of this month') : $date;
        $start = $view === 'timeGridDay' ? $anchor : $anchor->modify('-' . $anchor->format('w') . ' days');
        $days = match ($view) {
            'dayGridMonth' => 42, // The calendar shows six rows, including days from neighbouring months.
            'timeGridWeek' => 7,
            'timeGridDay' => 1,
        };
        $rows = $this->meetingModel->calendarMeetings(
            $start->format('Y-m-d H:i:s'),
            $start->modify("+$days days")->format('Y-m-d H:i:s'),
            $roomId,
            $loggedIn,
            $admin,
        );
        if ($loggedIn) {
            $this->response->setHeader('Cache-Control', 'private, no-store');
        }

        return view('meeting/calendar', [
            'calendarDate' => $date->format('Y-m-d'),
            'calendarView' => $view,
            'calendarEvents' => array_map(fn (array $row): array => $this->calendarEvent($row, $viewerId, $admin), $rows),
            'calendarRoomId' => $roomId,
            'ruangan' => $this->ruanganModel->getActiveRooms(),
            'isAdmin' => $admin,
            'formToken' => $loggedIn ? $this->issueFormToken('create') : null,
        ]);
    }

    private function calendarEvent(array $meeting, ?int $viewerId, bool $admin): array
    {
        $canManage = $admin || ($viewerId !== null && $viewerId > 0 && $viewerId === (int) $meeting['pegawai_id']);
        $facilities = json_decode($meeting['fasilitas'] ?? '[]', true);
        $props = [
            'ruangan' => $meeting['nama_ruangan'],
            'tipe' => $meeting['tipe'],
            'status' => $meeting['status'],
            'pegawai' => $meeting['nama_pegawai'],
            'jumlah_peserta' => $meeting['jumlah_peserta'],
            'fasilitas' => is_array($facilities) ? implode(', ', $facilities) : '',
            'canManage' => $canManage,
        ];
        if ($canManage) {
            $props['waktu_mulai_ts'] = strtotime($meeting['waktu_mulai']);
            $props['waktu_selesai_ts'] = strtotime($meeting['waktu_selesai']);
            $props['zoom_join_url'] = $meeting['zoom_join_url'];
            $props['hasZoomMeeting'] = !empty($meeting['zoom_meeting_id']);
            if ($props['hasZoomMeeting'] && $meeting['status'] === 'approved' && !empty($meeting['start_token'])) {
                $props['zoomHostUrl'] = base_url('zoom/start/' . $meeting['start_token']);
            }
        }
        if ($admin) {
            foreach (['status_changed_by_name', 'status_changed_at', 'last_edited_by_name', 'last_edited_at', 'created_at'] as $key) {
                $props[$key] = $meeting[$key];
            }
        }
        return [
            'id' => $meeting['id'],
            'title' => $meeting['nama_keg'],
            'start' => str_replace(' ', 'T', $meeting['waktu_mulai']),
            'end' => str_replace(' ', 'T', $meeting['waktu_selesai']),
            'classNames' => ['fc-event-status-' . $meeting['status']],
            'extendedProps' => $props,
        ];
    }

    public function upcoming()
    {
        $loggedIn = (bool) session()->get('logged_in');
        $roomId = $this->requestedRoomId();
        $today = date('Y-m-d 00:00:00');
        $tomorrow = date('Y-m-d 00:00:00', strtotime('tomorrow'));
        $data['today_meetings'] = $this->paginate(
            $this->meetingModel->scheduleList($today, $tomorrow, $roomId, $loggedIn),
            'today',
        );
        $data['upcoming_meetings'] = $this->paginate(
            $this->meetingModel->scheduleList($tomorrow, null, $roomId, $loggedIn),
            'upcoming',
        );
        $data['pager'] = service('pager')->only(['room', 'page_today', 'page_upcoming']);
        $data['ruangan'] = $this->ruanganModel->getActiveRooms();
        $data['roomId'] = $roomId;
        $data['isAdmin'] = $loggedIn && $this->isAdmin();
        if ($loggedIn) {
            $this->response->setHeader('Cache-Control', 'private, no-store');
        }
        return view('meeting/upcoming', $data);
    }

    private function requestedRoomId(): ?int
    {
        return filter_var($this->request->getGet('room'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    private function queryDate(string $name, DateTimeImmutable $default): ?DateTimeImmutable
    {
        $raw = $this->request->getGet($name);
        if ($raw === null || $raw === '') {
            return $default;
        }
        if (!is_string($raw) || !preg_match('/^[1-9]\d{3}-\d{2}-\d{2}$/D', $raw) || $raw >= '9999-01-01') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
        return $date !== false && $date->format('Y-m-d') === $raw ? $date : null;
    }

    private function paginate(BaseBuilder $builder, string $group): array
    {
        $pager = service('pager');
        $total = $builder->countAllResults(false);
        $perPage = $pager->getPerPage($group);
        $pager->store($group, $pager->getCurrentPage($group), $perPage, $total);
        $offset = ($pager->getCurrentPage($group) - 1) * $perPage;
        return $total === 0 ? [] : $builder->get($perPage, $offset)->getResultArray();
    }

    public function all()
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        $this->response->setHeader('Cache-Control', 'private, no-store');

        $monday = new DateTimeImmutable('monday this week');
        $start = $this->queryDate('start_date', $monday);
        $end = $this->queryDate('end_date', $monday->modify('+6 days'));
        if ($start === null || $end === null || $end < $start || $end > $start->modify('+365 days')) {
            return redirect()->to('meeting/all')->with('error', 'Pilih periode tanggal yang valid dengan maksimal 366 hari.');
        }

        $status = $this->request->getGet('status');
        $status = in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true) ? $status : 'all';
        $query = $this->request->getGet('q');
        $query = is_string($query) ? mb_substr(trim($query), 0, 100) : '';
        $range = $this->request->getGet('range');
        $data['filters'] = [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => $status,
            'q' => $query,
            'range' => in_array($range, ['today', 'week', 'month', 'pending'], true) ? $range : 'custom',
        ];
        $builder = $this->meetingModel->reportList(
            $start->format('Y-m-d H:i:s'),
            $end->modify('+1 day')->format('Y-m-d H:i:s'),
            $status,
            $query,
        );
        $format = $this->request->getGet('format');
        if ($format === 'csv') {
            return $this->downloadMeetingReport($builder);
        }
        if ($format === 'print') {
            $data['meetings'] = $builder->get();
            return view('meeting/print', $data);
        }
        $data['meetings'] = $this->paginate($builder, 'default');
        $data['pager'] = service('pager')->only(['page', 'start_date', 'end_date', 'status', 'q', 'range']);
        return view('meeting/all', $data);
    }

    private function downloadMeetingReport(BaseBuilder $builder)
    {
        $file = fopen('php://temp', 'w+b');
        fputcsv($file, ['Nama Kegiatan', 'Ruangan', 'Waktu', 'Status'], ',', '"', '');
        $results = $builder->get();
        while ($meeting = $results->getUnbufferedRow('array')) {
            $row = [
                $meeting['nama_keg'],
                $meeting['nama_ruangan'] . ' (' . $meeting['tipe'] . ')',
                date('d M Y H:i', strtotime($meeting['waktu_mulai'])) . ' – ' . date('H:i', strtotime($meeting['waktu_selesai'])),
                ucfirst($meeting['status']),
            ];
            // Keep text as text when opening the report in a spreadsheet.
            $row = array_map(static fn (string $value): string => preg_match('/^[\x00-\x20]*[=+@-]/', $value) ? "'" . $value : $value, $row);
            fputcsv($file, $row, ',', '"', '');
        }
        rewind($file);
        $content = stream_get_contents($file);
        fclose($file);
        return $this->response->download('meetingku-' . date('Y-m-d') . '.csv', $content, true)
            ->setHeader('Cache-Control', 'private, no-store');
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }
        if (!$this->consumeFormToken('create')) {
            return redirect()->back()->with('error', 'Form tidak valid atau sudah dikirim. Buka kembali kalender.');
        }

        [$waktuMulai, $waktuSelesai] = $this->resolveTimeRange(true);

        $ruanganId = filter_var($this->request->getPost('ruangan_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $ruangan = $ruanganId !== false ? $this->ruanganModel->find($ruanganId) : null;
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

            return redirect()->back()->with('success', 'Meeting berhasil dibuat');
        } catch (\Throwable $e) {
            log_message('error', 'Meeting creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat meeting.')->withInput();
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
        $data['formToken'] = $this->issueFormToken('edit:' . $meeting['id']);
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

        if (!$this->consumeFormToken('edit:' . $meeting['id'])) {
            return redirect()->back()->with('error', 'Form tidak valid atau sudah dikirim. Buka kembali halaman edit.')->withInput();
        }

        [$waktuMulai, $waktuSelesai] = $this->resolveTimeRange();

        $ruanganId = filter_var($this->request->getPost('ruangan_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $ruangan = $ruanganId !== false ? $this->ruanganModel->find($ruanganId) : null;
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
            $updated = $this->meetingModel->withZoomWrite($meeting, null, function () use ($id, $meeting, $data): bool {
                if (!$this->meetingModel->update($id, $data)) {
                    return false;
                }
                if ($meeting['waktu_mulai'] !== $data['waktu_mulai'] || $meeting['waktu_selesai'] !== $data['waktu_selesai']
                    || $meeting['nama_keg'] !== $data['nama_keg']) {
                    $this->syncZoomOnScheduleChange((int) $id, $meeting, $data);
                }
                return true;
            });
            if (!$updated) {
                return redirect()->back()
                    ->with('error', 'Gagal mengupdate meeting: ' . implode(', ', $this->meetingModel->errors()))
                    ->withInput();
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

            return redirect()->to('/upcoming')->with('success', 'Meeting berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Meeting update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate meeting. Muat ulang halaman dan coba lagi.')->withInput();
        }
    }

    protected function syncZoomOnScheduleChange(int $id, array $meeting, array $data): void
    {
        if (!$this->isZoomHostSource($meeting) || $this->meetingModel->hasOtherZoomBookings($meeting)) {
            return;
        }

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

        try {
            $deleted = $this->meetingModel->withZoomWrite($meeting, null, function () use ($meeting): bool {
                if (!$this->meetingModel->detachZoomBooking($meeting)) {
                    return false;
                }
                $this->deleteDetachedZoom($meeting);
                return true;
            });
            if (!$deleted) {
                return redirect()->back()->with('error', 'Gagal menghapus meeting.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Meeting deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus meeting.');
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

            $detach = in_array($status, ['rejected', 'cancelled'], true) && !empty($meeting['zoom_join_url']);
            if ($detach) {
                $updateData['zoom_meeting_id'] = null;
                $updateData['zoom_source_meeting_id'] = null;
                $updateData['zoom_join_url'] = null;
                $updateData['zoom_start_url'] = null;
                $updateData['start_token'] = null;
            }
            $result = $this->meetingModel->withZoomWrite($meeting, null, function () use ($id, $meeting, $detach, $updateData): bool {
                if (!$detach) {
                    return $this->meetingModel->update($id, $updateData);
                }
                if (!$this->meetingModel->detachZoomBooking($meeting, $updateData)) {
                    return false;
                }
                $this->deleteDetachedZoom($meeting);
                return true;
            });
            if ($result === false) {
                return redirect()->back()
                    ->with('error', 'Gagal mengupdate status meeting: ' . implode(', ', $this->meetingModel->errors()));
            }
            if ($detach) {
                $this->sendWhatsAppToPegawai($meeting, 'cancelled');
            }

            return redirect()->back()
                ->with('success', 'Status meeting berhasil diupdate menjadi ' . strtoupper($status));
        } catch (\Exception $e) {
            log_message('error', 'Meeting status update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate status meeting. Muat ulang halaman dan coba lagi.');
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

            $zoom = $this->meetingModel->withZoomWrite($meeting, null, function () use ($id, $meeting, $startTimeISO, $duration): ?array {
                $result = $this->zoomLibrary->createMeeting(
                    $meeting['nama_keg'],
                    $startTimeISO,
                    $duration,
                    'Meeting: ' . $meeting['nama_keg'],
                );
                if (!$result) {
                    return null;
                }
                $fields = [
                    'zoom_meeting_id' => $result['id'],
                    'zoom_join_url' => $result['join_url'],
                    'zoom_start_url' => $result['start_url'],
                    'start_token' => $this->generateStartToken(),
                ];
                $this->meetingModel->setValidationRules([]);
                return $this->meetingModel->update($id, $fields) ? $fields : null;
            });
            if ($zoom === null) {
                return redirect()->back()->with('error', 'Gagal membuat atau menyimpan Zoom meeting. Periksa konfigurasi Zoom API.');
            }
            $meeting = array_replace($meeting, $zoom);

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
                ->with('error', 'Gagal membuat Zoom meeting. Muat ulang halaman dan coba lagi.');
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
        $sameSession = $zoomMeetingId === ($meeting['zoom_meeting_id'] ?? null);
        try {
            $updateData = $this->meetingModel->withZoomWrite($meeting, $zoomMeetingId, function () use ($id, $meeting, $zoomJoinUrl, $zoomMeetingId, $sameSession): ?array {
                $sourceMeeting = $sameSession && empty($meeting['zoom_source_meeting_id'])
                    ? null
                    : $this->findSharedZoomSource($zoomMeetingId, (int) $id);
                $data = $this->buildManualZoomUpdateData($zoomJoinUrl, $zoomMeetingId, $meeting, $sourceMeeting);
                $updated = $sameSession
                    ? $this->meetingModel->update($id, $data)
                    : $this->meetingModel->detachZoomBooking($meeting, $data);
                return $updated ? $data : null;
            });
        } catch (\Exception $e) {
            log_message('error', 'Manual Zoom update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan link Zoom manual. Muat ulang halaman dan coba lagi.');
        }
        $previousJoinUrl = (string) ($meeting['zoom_join_url'] ?? '');
        $isFirstZoomLink = $previousJoinUrl === '';
        if ($updateData === null) {
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

        $source = $this->zoomHostSource($meeting);
        if ($source === null) {
            return redirect()->back()->with('error', 'Meeting tidak memiliki link khusus host.');
        }

        try {
            $zoomData = $this->zoomLibrary->getMeeting($source['zoom_meeting_id']);
            if (!$zoomData || empty($zoomData['start_url'])) {
                return redirect()->back()->with('error', 'Gagal mengambil data Zoom meeting');
            }

            return $this->redirectToZoom($zoomData['start_url']);
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

        $zoomMeeting = $this->zoomHostSource($meeting);
        if ($zoomMeeting === null) {
            return $this->redirectToZoom($meeting['zoom_join_url']);
        }

        try {
            $zoomData = $this->zoomLibrary->getMeeting($zoomMeeting['zoom_meeting_id']);
            if (!$zoomData || empty($zoomData['start_url'])) {
                return $this->redirectToZoom($meeting['zoom_join_url']);
            }

            return $this->redirectToZoom($zoomData['start_url']);
        } catch (\Exception $e) {
            log_message('error', 'Exception during startHost: ' . $e->getMessage());
            return $this->renderHostError('Terjadi kesalahan saat menghubungkan ke Zoom. Silakan coba lagi.');
        }
    }

    private function redirectToZoom(string $url)
    {
        return redirect()->to($url)
            ->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('Referrer-Policy', 'no-referrer');
    }

    protected function renderHostError(string $message)
    {
        $this->response->setHeader('Cache-Control', 'private, no-store');
        $this->response->setHeader('Referrer-Policy', 'no-referrer');
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
