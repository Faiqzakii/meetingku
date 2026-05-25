<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingModel extends Model
{
    protected $table = 'meeting';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nama_keg',
        'jumlah_peserta',
        'fasilitas',
        'ruangan_id',
        'pegawai_id',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'zoom_meeting_id',
        'zoom_join_url',
        'zoom_start_url',
        'start_token',
        'status_changed_by',
        'status_changed_at',
        'last_edited_by',
        'last_edited_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'nama_keg' => 'permit_empty|min_length[3]|max_length[100]',
        'jumlah_peserta' => 'required|integer|greater_than[0]',
        'ruangan_id' => 'permit_empty|integer|is_not_unique[ruangan.id]',
        'pegawai_id' => 'permit_empty|integer|is_not_unique[pegawai.id]',
        'waktu_mulai' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'waktu_selesai' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'status' => 'required|in_list[pending,approved,rejected,cancelled]'
    ];

    protected $validationMessages = [
        'nama_keg' => [
            'required' => 'Nama kegiatan harus diisi',
            'min_length' => 'Nama kegiatan minimal 3 karakter',
            'max_length' => 'Nama kegiatan maksimal 100 karakter'
        ],
        'jumlah_peserta' => [
            'required' => 'Jumlah peserta harus diisi',
            'integer' => 'Jumlah peserta harus berupa angka',
            'greater_than' => 'Jumlah peserta harus lebih dari 0'
        ],
        'ruangan_id' => [
            'required' => 'Ruangan harus dipilih',
            'integer' => 'ID Ruangan tidak valid',
            'is_not_unique' => 'Ruangan tidak ditemukan'
        ],
        'pegawai_id' => [
            'required' => 'Pegawai harus dipilih',
            'integer' => 'ID Pegawai tidak valid',
            'is_not_unique' => 'Pegawai tidak ditemukan'
        ],
        'waktu_mulai' => [
            'required' => 'Waktu mulai harus diisi',
            'valid_date' => 'Format waktu mulai tidak valid'
        ],
        'waktu_selesai' => [
            'required' => 'Waktu selesai harus diisi',
            'valid_date' => 'Format waktu selesai tidak valid'
        ],
        'status' => [
            'required' => 'Status harus diisi',
            'in_list' => 'Status tidak valid'
        ]
    ];

    // Custom validation for end time
    protected function validateEndTime(array $data): bool
    {
        // Only validate if both dates are present
        if (!isset($data['waktu_mulai']) || !isset($data['waktu_selesai']) || 
            empty($data['waktu_mulai']) || empty($data['waktu_selesai'])) {
            return true;
        }

        $startTime = strtotime($data['waktu_mulai']);
        $endTime = strtotime($data['waktu_selesai']);

        if ($endTime <= $startTime) {
            $this->validation->setError('waktu_selesai', 'Waktu selesai harus lebih besar dari waktu mulai');
            return false;
        }

        return true;
    }

    public function validate($data): bool
    {
        if (!parent::validate($data)) {
            return false;
        }

        return $this->validateEndTime($data);
    }

    // Get meeting details with related data
    public function getMeetingDetails($meetingId)
    {
        return $this->db->table('meeting')
            ->where('meeting.id', $meetingId)
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->join('pegawai as status_pegawai', 'status_pegawai.id = meeting.status_changed_by', 'left')
            ->join('pegawai as edit_pegawai', 'edit_pegawai.id = meeting.last_edited_by', 'left')
            ->select('meeting.*, ruangan.nama_ruangan, ruangan.tipe, pegawai.nama as nama_pegawai, pegawai.id as pegawai_id, status_pegawai.nama as status_changed_by_name, edit_pegawai.nama as last_edited_by_name')
            ->get()
            ->getRowArray();
    }

    // Get all meetings for a specific date range
    public function getMeetingsByDateRange($startDate, $endDate)
    {
        return $this->db->table('meeting')
            ->where('waktu_mulai >=', $startDate)
            ->where('waktu_selesai <=', $endDate)
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->join('pegawai as status_pegawai', 'status_pegawai.id = meeting.status_changed_by', 'left')
            ->join('pegawai as edit_pegawai', 'edit_pegawai.id = meeting.last_edited_by', 'left')
            ->select('meeting.*, ruangan.nama_ruangan, ruangan.tipe, pegawai.nama as nama_pegawai, pegawai.id as pegawai_id, pegawai.is_admin, pegawai.no_hp as pegawai_no_hp, status_pegawai.nama as status_changed_by_name, edit_pegawai.nama as last_edited_by_name')
            ->orderBy('waktu_mulai', 'ASC')
            ->get()
            ->getResultArray();
    }

    // Get all meetings with details
    public function getAllMeetingsWithDetails()
    {
        return $this->db->table('meeting')
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->select('meeting.*, ruangan.nama_ruangan, ruangan.tipe, pegawai.nama as nama_pegawai, pegawai.id as pegawai_id, pegawai.is_admin')
            ->orderBy('waktu_mulai', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getNotRejectedMeetingWithDetails()
    {
        return $this->db->table('meeting')
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->join('pegawai as status_pegawai', 'status_pegawai.id = meeting.status_changed_by', 'left')
            ->join('pegawai as edit_pegawai', 'edit_pegawai.id = meeting.last_edited_by', 'left')
            ->select('meeting.*, ruangan.nama_ruangan, ruangan.tipe, pegawai.nama as nama_pegawai, pegawai.id as pegawai_id, pegawai.is_admin, pegawai.no_hp as pegawai_no_hp, status_pegawai.nama as status_changed_by_name, edit_pegawai.nama as last_edited_by_name')
            ->where('status !=', 'rejected')
            ->orderBy('waktu_mulai', 'ASC')
            ->get()
            ->getResultArray();
    }

    // Get today's meetings
    public function getTodayMeetings()
    {
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        
        return $this->db->table('meeting')
            ->where('waktu_mulai >=', $today_start)
            ->where('waktu_mulai <=', $today_end)
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->join('pegawai as status_pegawai', 'status_pegawai.id = meeting.status_changed_by', 'left')
            ->join('pegawai as edit_pegawai', 'edit_pegawai.id = meeting.last_edited_by', 'left')
            ->select('meeting.*, ruangan.nama_ruangan, ruangan.tipe, pegawai.nama as nama_pegawai, pegawai.id as pegawai_id, pegawai.is_admin, pegawai.no_hp as pegawai_no_hp, status_pegawai.nama as status_changed_by_name, edit_pegawai.nama as last_edited_by_name')
            ->orderBy('waktu_mulai', 'ASC')
            ->get()
            ->getResultArray();
    }

    // Get upcoming meetings (after today)
    public function getUpcomingMeetings()
    {
        $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
        return $this->db->table('meeting')
            ->where('waktu_mulai >=', $tomorrow)
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->join('pegawai as status_pegawai', 'status_pegawai.id = meeting.status_changed_by', 'left')
            ->join('pegawai as edit_pegawai', 'edit_pegawai.id = meeting.last_edited_by', 'left')
            ->select('meeting.*, ruangan.nama_ruangan, ruangan.tipe, pegawai.nama as nama_pegawai, pegawai.id as pegawai_id, pegawai.is_admin, pegawai.no_hp as pegawai_no_hp, status_pegawai.nama as status_changed_by_name, edit_pegawai.nama as last_edited_by_name')
            ->orderBy('waktu_mulai', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get approved meetings that already have Zoom and overlap with given time range.
     * Used to warn admin about concurrent Zoom conflicts (Pro = 1 concurrent meeting).
     */
    public function getConflictingZoomMeetings(string $startTime, string $endTime, ?int $excludeId = null): array
    {
        $builder = $this->db->table('meeting')
            ->select('meeting.id, meeting.nama_keg, meeting.waktu_mulai, meeting.waktu_selesai, meeting.zoom_meeting_id')
            ->where('meeting.status', 'approved')
            ->where('meeting.zoom_meeting_id IS NOT NULL')
            ->where('meeting.zoom_meeting_id !=', '')
            ->where('meeting.waktu_mulai <', $endTime)
            ->where('meeting.waktu_selesai >', $startTime);

        if ($excludeId) {
            $builder->where('meeting.id !=', $excludeId);
        }

        return $builder->get()->getResultArray();
    }
}
