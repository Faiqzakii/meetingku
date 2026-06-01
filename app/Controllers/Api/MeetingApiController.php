<?php

namespace App\Controllers\Api;

use App\Libraries\WaApiKeyService;
use App\Libraries\ZoomLibrary;
use App\Models\MeetingModel;
use App\Models\PegawaiModel;
use App\Models\RuanganModel;
use App\Models\WaApiKeyModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class MeetingApiController extends ResourceController
{
    protected MeetingModel $meetingModel;
    protected RuanganModel $ruanganModel;
    protected PegawaiModel $pegawaiModel;
    protected ZoomLibrary $zoomLibrary;
    protected $db;

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
        $this->ruanganModel = new RuanganModel();
        $this->pegawaiModel = new PegawaiModel();
        $this->zoomLibrary  = new ZoomLibrary();
        $this->db           = db_connect();
    }

    /**
     * Authenticate request via X-API-KEY header.
     * Returns pegawai info or null.
     */
    private function authenticate(): ?array
    {
        $apiKey = $this->request->getHeaderLine('X-API-KEY');
        if ($apiKey === '') {
            return null;
        }

        $apiKeyRow = (new WaApiKeyModel())
            ->where('key_hash', WaApiKeyService::hash($apiKey))
            ->where('is_active', true)
            ->first();

        if (!$apiKeyRow || empty($apiKeyRow['pegawai_id'])) {
            log_message('warning', 'API auth failed: invalid/inactive key from IP=' . $this->request->getIPAddress());
            return null;
        }

        $pegawai = $this->pegawaiModel->find($apiKeyRow['pegawai_id']);
        if (!$pegawai) {
            log_message('warning', 'API auth failed: pegawai_id=' . $apiKeyRow['pegawai_id'] . ' not found, IP=' . $this->request->getIPAddress());
            return null;
        }

        // Update last_used_at
        (new WaApiKeyModel())->update($apiKeyRow['id'], ['last_used_at' => date('Y-m-d H:i:s')]);

        return [
            'api_key_id' => $apiKeyRow['id'],
            'pegawai_id' => (int) $pegawai['id'],
            'is_admin'   => (bool) ($pegawai['is_admin'] ?? false),
        ];
    }

    /**
     * GET /api/meetings — List meetings
     */
    public function index(): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $startParam = $this->request->getGet('start_date');
        $endParam   = $this->request->getGet('end_date');

        $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $startDate = $startParam ? date('Y-m-d 00:00:00', strtotime($startParam)) : $monday;
        $endDate   = $endParam ? date('Y-m-d 23:59:59', strtotime($endParam)) : $sunday;

        $meetings = $this->meetingModel->getMeetingsByDateRange($startDate, $endDate);

        return $this->respond([
            'success' => true,
            'data'    => $meetings,
            'meta'    => [
                'start_date' => date('Y-m-d', strtotime($startDate)),
                'end_date'   => date('Y-m-d', strtotime($endDate)),
                'total'      => count($meetings),
            ],
        ]);
    }

    /**
     * GET /api/meetings/{id} — Get meeting detail
     */
    public function show($id = null): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $meeting = $this->meetingModel->getMeetingDetails($id);
        if (!$meeting) {
            return $this->failNotFound('Meeting not found');
        }

        return $this->respond([
            'success' => true,
            'data'    => $meeting,
        ]);
    }

    /**
     * POST /api/meetings — Create meeting
     */
    public function create(): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();

        // Validate required fields
        $rules = [
            'nama_keg'    => 'required|min_length[3]|max_length[100]',
            'ruangan_id'  => 'required|integer|is_not_unique[ruangan.id]',
            'waktu_mulai' => 'required|valid_date[Y-m-d H:i:s]',
            'waktu_selesai' => 'required|valid_date[Y-m-d H:i:s]',
        ];

        // Check room type for conditional validation
        $ruanganId = $payload['ruangan_id'] ?? null;
        $ruangan = $ruanganId ? $this->ruanganModel->find($ruanganId) : null;
        $isOnline = ($ruangan && ($ruangan['tipe'] ?? '') === 'Online');

        if ($isOnline) {
            $rules['jumlah_peserta'] = 'permit_empty';
        } else {
            $rules['jumlah_peserta'] = 'permit_empty|integer|greater_than[0]';
        }

        $this->meetingModel->setValidationRules($rules);

        // Handle fasilitas array
        $fasilitas = $payload['fasilitas'] ?? null;
        $fasilitasJson = null;
        if ($fasilitas && is_array($fasilitas)) {
            if (count($fasilitas) > 20) {
                return $this->failValidationErrors([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => ['fasilitas' => 'Maximum 20 fasilitas items allowed'],
                ]);
            }
            foreach ($fasilitas as $item) {
                if (!is_string($item) || strlen($item) > 100) {
                    return $this->failValidationErrors([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors'  => ['fasilitas' => 'Each fasilitas item must be a string (max 100 chars)'],
                    ]);
                }
            }
            $fasilitasJson = json_encode($fasilitas);
        }

        $data = [
            'nama_keg'       => $payload['nama_keg'] ?? '',
            'jumlah_peserta' => $payload['jumlah_peserta'] ?? null,
            'fasilitas'      => $fasilitasJson,
            'waktu_mulai'    => $payload['waktu_mulai'] ?? '',
            'waktu_selesai'  => $payload['waktu_selesai'] ?? '',
            'ruangan_id'     => $payload['ruangan_id'] ?? null,
            'pegawai_id'     => $auth['pegawai_id'],
            'status'         => 'pending',
        ];

        // Custom validation: end time > start time
        if (!empty($data['waktu_mulai']) && !empty($data['waktu_selesai'])) {
            if (strtotime($data['waktu_selesai']) <= strtotime($data['waktu_mulai'])) {
                return $this->failValidationErrors([
                    'success' => false,
                    'message' => 'waktu_selesai must be after waktu_mulai',
                    'errors'  => ['waktu_selesai' => 'Waktu selesai harus lebih besar dari waktu mulai'],
                ]);
            }
        }

        if (!$this->meetingModel->validate($data)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $this->meetingModel->errors(),
            ]);
        }

        try {
            $meetingId = $this->meetingModel->insert($data);
            if ($meetingId === false) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Failed to create meeting',
                    'errors'  => $this->meetingModel->errors(),
                ], 500);
            }

            $meeting = $this->meetingModel->getMeetingDetails($meetingId);

            log_message('info', 'API: Meeting created ID=' . $meetingId . ' by pegawai=' . $auth['pegawai_id']);

            return $this->respondCreated([
                'success' => true,
                'data'    => $meeting,
                'message' => 'Meeting created successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API create meeting error: ' . $e->getMessage());
            return $this->failServerError([
                'success' => false,
                'message' => 'Gagal menyimpan data',
            ]);
        }
    }

    /**
     * PUT /api/meetings/{id} — Update meeting
     */
    public function update($id = null): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return $this->failNotFound('Meeting not found');
        }

        // Authorization: owner or admin
        if (!$auth['is_admin'] && (int) $meeting['pegawai_id'] !== $auth['pegawai_id']) {
            return $this->failForbidden('You can only update your own meetings');
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($payload)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'No data provided for update',
            ]);
        }

        // Build update data (only include provided fields)
        $updateData = [];

        if (isset($payload['nama_keg'])) {
            $updateData['nama_keg'] = $payload['nama_keg'];
        }
        if (isset($payload['jumlah_peserta'])) {
            $updateData['jumlah_peserta'] = $payload['jumlah_peserta'];
        }
        if (isset($payload['fasilitas'])) {
            if (is_array($payload['fasilitas'])) {
                if (count($payload['fasilitas']) > 20) {
                    return $this->failValidationErrors([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors'  => ['fasilitas' => 'Maximum 20 fasilitas items allowed'],
                    ]);
                }
                foreach ($payload['fasilitas'] as $item) {
                    if (!is_string($item) || strlen($item) > 100) {
                        return $this->failValidationErrors([
                            'success' => false,
                            'message' => 'Validation failed',
                            'errors'  => ['fasilitas' => 'Each fasilitas item must be a string (max 100 chars)'],
                        ]);
                    }
                }
                $updateData['fasilitas'] = json_encode($payload['fasilitas']);
            } else {
                $updateData['fasilitas'] = $payload['fasilitas'];
            }
        }
        if (isset($payload['waktu_mulai'])) {
            $updateData['waktu_mulai'] = $payload['waktu_mulai'];
        }
        if (isset($payload['waktu_selesai'])) {
            $updateData['waktu_selesai'] = $payload['waktu_selesai'];
        }
        if (isset($payload['ruangan_id'])) {
            $updateData['ruangan_id'] = $payload['ruangan_id'];
        }

        if (empty($updateData)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'No valid fields to update',
            ]);
        }

        // Validate end time if both times provided
        $startTime = $updateData['waktu_mulai'] ?? $meeting['waktu_mulai'];
        $endTime   = $updateData['waktu_selesai'] ?? $meeting['waktu_selesai'];
        if (strtotime($endTime) <= strtotime($startTime)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'waktu_selesai must be after waktu_mulai',
                'errors'  => ['waktu_selesai' => 'Waktu selesai harus lebih besar dari waktu mulai'],
            ]);
        }

        // Audit fields
        $updateData['last_edited_by'] = $auth['pegawai_id'];
        $updateData['last_edited_at'] = date('Y-m-d H:i:s');

        // Skip model-level status validation for partial updates
        $this->meetingModel->setValidationRules([
            'nama_keg'       => 'permit_empty|min_length[3]|max_length[100]',
            'jumlah_peserta' => 'permit_empty|integer|greater_than[0]',
            'ruangan_id'     => 'permit_empty|integer|is_not_unique[ruangan.id]',
            'waktu_mulai'    => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'waktu_selesai'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        ]);

        if (!$this->meetingModel->validate($updateData)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $this->meetingModel->errors(),
            ]);
        }

        try {
            if ($this->meetingModel->update($id, $updateData) === false) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Failed to update meeting',
                    'errors'  => $this->meetingModel->errors(),
                ], 500);
            }

            $updated = $this->meetingModel->getMeetingDetails($id);

            log_message('info', 'API: Meeting updated ID=' . $id . ' by pegawai=' . $auth['pegawai_id']);

            return $this->respond([
                'success' => true,
                'data'    => $updated,
                'message' => 'Meeting updated successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API update meeting error: ' . $e->getMessage());
            return $this->failServerError([
                'success' => false,
                'message' => 'Gagal menyimpan data',
            ]);
        }
    }

    /**
     * PATCH /api/meetings/{id}/approve — Approve/Reject/Cancel meeting (admin only)
     */
    public function approve($id = null): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        if (!$auth['is_admin']) {
            return $this->failForbidden('Only admin can approve/reject meetings');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return $this->failNotFound('Meeting not found');
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $status = $payload['status'] ?? null;

        if (!in_array($status, ['approved', 'rejected', 'cancelled'], true)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'Status must be one of: approved, rejected, cancelled',
            ]);
        }

        $this->meetingModel->setValidationRules([
            'status' => 'required|in_list[pending,approved,rejected,cancelled]',
        ]);

        $updateData = [
            'status'            => $status,
            'status_changed_by' => $auth['pegawai_id'],
            'status_changed_at' => date('Y-m-d H:i:s'),
        ];

        // If rejecting/cancelling and has Zoom, clean up
        if (in_array($status, ['rejected', 'cancelled'], true) && !empty($meeting['zoom_meeting_id'])) {
            try {
                $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
                log_message('info', 'API: Zoom meeting deleted for meeting ' . $id);
            } catch (\Throwable $ze) {
                log_message('error', 'API: Failed to delete Zoom meeting: ' . $ze->getMessage());
            }
            $updateData['zoom_meeting_id'] = null;
            $updateData['zoom_join_url']   = null;
            $updateData['zoom_start_url']  = null;
            $updateData['start_token']     = null;
        }

        try {
            if ($this->meetingModel->update($id, $updateData) === false) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Failed to update meeting status',
                    'errors'  => $this->meetingModel->errors(),
                ], 500);
            }

            $updated = $this->meetingModel->getMeetingDetails($id);

            log_message('info', 'API: Meeting ' . $id . ' status changed to ' . $status . ' by pegawai=' . $auth['pegawai_id']);

            return $this->respond([
                'success' => true,
                'data'    => $updated,
                'message' => 'Meeting status updated to ' . strtoupper($status),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API approve meeting error: ' . $e->getMessage());
            return $this->failServerError([
                'success' => false,
                'message' => 'Gagal menyimpan data',
            ]);
        }
    }

    /**
     * GET /api/rooms — List active rooms
     */
    public function rooms(): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $rooms = $this->ruanganModel->getActiveRooms();

        return $this->respond([
            'success' => true,
            'data'    => $rooms,
            'meta'    => ['total' => count($rooms)],
        ]);
    }

    /**
     * GET /api/meetings/conflict — Check room schedule conflicts
     * Query params: ruangan_id, waktu_mulai, waktu_selesai, exclude_id (optional)
     */
    public function conflict(): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $ruanganId   = $this->request->getGet('ruangan_id');
        $waktuMulai  = $this->request->getGet('waktu_mulai');
        $waktuSelesai = $this->request->getGet('waktu_selesai');
        $excludeId   = $this->request->getGet('exclude_id');

        // Validate required params
        $errors = [];
        if (empty($ruanganId) || !ctype_digit((string) $ruanganId)) {
            $errors['ruangan_id'] = 'ruangan_id required and must be integer';
        }
        if (empty($waktuMulai) || strtotime($waktuMulai) === false) {
            $errors['waktu_mulai'] = 'waktu_mulai required, format Y-m-d H:i:s';
        }
        if (empty($waktuSelesai) || strtotime($waktuSelesai) === false) {
            $errors['waktu_selesai'] = 'waktu_selesai required, format Y-m-d H:i:s';
        }

        if (!empty($errors)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $errors,
            ]);
        }

        // Validate end > start
        if (strtotime($waktuSelesai) <= strtotime($waktuMulai)) {
            return $this->failValidationErrors([
                'success' => false,
                'message' => 'waktu_selesai must be after waktu_mulai',
                'errors'  => ['waktu_selesai' => 'Waktu selesai harus lebih besar dari waktu mulai'],
            ]);
        }

        // Validate room exists
        $ruangan = $this->ruanganModel->find($ruanganId);
        if (!$ruangan) {
            return $this->failNotFound('Ruangan not found');
        }

        // Query overlapping meetings (pending or approved)
        $builder = $this->meetingModel->db->table('meeting')
            ->select('meeting.id, meeting.nama_keg, meeting.waktu_mulai, meeting.waktu_selesai, meeting.status')
            ->where('ruangan_id', $ruanganId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('waktu_mulai <', $waktuSelesai)
            ->where('waktu_selesai >', $waktuMulai);

        if (!empty($excludeId) && ctype_digit((string) $excludeId)) {
            $builder->where('id !=', $excludeId);
        }

        $conflicts = $builder->orderBy('waktu_mulai', 'ASC')->get()->getResultArray();

        return $this->respond([
            'success' => true,
            'data'    => $conflicts,
            'meta'    => [
                'has_conflict' => count($conflicts) > 0,
                'count'        => count($conflicts),
            ],
        ]);
    }

    /**
     * DELETE /api/meetings/{id} — Delete meeting
     */
    public function delete($id = null): ResponseInterface
    {
        $auth = $this->authenticate();
        if (!$auth) {
            return $this->failUnauthorized('API key required or invalid');
        }

        $meeting = $this->meetingModel->find($id);
        if (!$meeting) {
            return $this->failNotFound('Meeting not found');
        }

        // Authorization: owner or admin
        if (!$auth['is_admin'] && (int) $meeting['pegawai_id'] !== $auth['pegawai_id']) {
            return $this->failForbidden('You can only delete your own meetings');
        }

        // Delete Zoom meeting if exists
        if (!empty($meeting['zoom_meeting_id'])) {
            try {
                $this->zoomLibrary->deleteMeeting($meeting['zoom_meeting_id']);
                log_message('info', 'API: Zoom meeting deleted for meeting ' . $id);
            } catch (\Throwable $ze) {
                log_message('error', 'API: Failed to delete Zoom meeting: ' . $ze->getMessage());
            }
        }

        try {
            if ($this->meetingModel->delete($id) === false) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Failed to delete meeting',
                    'errors'  => $this->meetingModel->errors(),
                ], 500);
            }

            log_message('info', 'API: Meeting deleted ID=' . $id . ' by pegawai=' . $auth['pegawai_id']);

            return $this->respond([
                'success' => true,
                'message' => 'Meeting deleted successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API delete meeting error: ' . $e->getMessage());
            return $this->failServerError([
                'success' => false,
                'message' => 'Gagal menyimpan data',
            ]);
        }
    }
}
