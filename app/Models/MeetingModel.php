<?php

namespace App\Models;

use Closure;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use RuntimeException;

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
        'zoom_source_meeting_id',
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

    protected function validateEndTime(array $data): bool
    {
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

    public function getMeetingsByDateRange($startDate, $endDate)
    {
        return $this->withRoom()
            ->select('meeting.nama_keg, meeting.waktu_mulai, meeting.waktu_selesai, meeting.status, ruangan.nama_ruangan')
            ->where('meeting.waktu_mulai >=', $startDate)
            ->where('meeting.waktu_selesai <=', $endDate)
            ->get()
            ->getResultArray();
    }

    public function calendarMeetings(string $start, string $end, ?int $roomId, bool $loggedIn, bool $admin): array
    {
        $builder = $this->withRoom()
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->select('meeting.id, meeting.nama_keg, meeting.waktu_mulai, meeting.waktu_selesai, meeting.status, '
                . 'meeting.pegawai_id, meeting.jumlah_peserta, meeting.fasilitas, ruangan.nama_ruangan, ruangan.tipe, '
                . 'pegawai.nama as nama_pegawai')
            ->where('meeting.status !=', 'rejected')
            ->where('meeting.waktu_mulai <', $end)
            ->where('meeting.waktu_selesai >', $start);

        if ($roomId !== null) {
            $builder->where('meeting.ruangan_id', $roomId);
        }
        if ($loggedIn) {
            $builder->select('meeting.zoom_meeting_id, meeting.zoom_join_url, meeting.start_token');
        }
        if ($admin) {
            $builder->join('pegawai as status_pegawai', 'status_pegawai.id = meeting.status_changed_by', 'left')
                ->join('pegawai as edit_pegawai', 'edit_pegawai.id = meeting.last_edited_by', 'left')
                ->select('status_pegawai.nama as status_changed_by_name, edit_pegawai.nama as last_edited_by_name, '
                    . 'meeting.status_changed_at, meeting.last_edited_at, meeting.created_at');
        }

        return $builder->get()->getResultArray();
    }

    public function scheduleList(string $start, ?string $end, ?int $roomId, bool $loggedIn): BaseBuilder
    {
        $builder = $this->withRoom()
            ->select('meeting.id, meeting.nama_keg, meeting.pegawai_id, meeting.waktu_mulai, meeting.waktu_selesai, '
                . 'meeting.status, meeting.zoom_join_url, ruangan.nama_ruangan, ruangan.tipe')
            ->where('meeting.waktu_mulai >=', $start);

        if ($end !== null) {
            $builder->where('meeting.waktu_mulai <', $end);
        }
        if ($roomId !== null) {
            $builder->where('meeting.ruangan_id', $roomId);
        }
        if ($loggedIn) {
            $builder->select('meeting.zoom_meeting_id, meeting.start_token');
        }

        return $builder;
    }

    public function reportList(string $start, string $end, string $status, string $query): BaseBuilder
    {
        $builder = $this->withRoom()
            ->select('meeting.id, meeting.nama_keg, meeting.waktu_mulai, meeting.waktu_selesai, meeting.status, '
                . 'meeting.zoom_join_url, ruangan.nama_ruangan, ruangan.tipe')
            ->where('meeting.waktu_mulai >=', $start)
            ->where('meeting.waktu_mulai <', $end)
            ->where('meeting.waktu_selesai <', $end);

        if ($status !== 'all') {
            $builder->where('meeting.status', $status);
        }
        if ($query !== '') {
            $builder->groupStart()
                ->like('meeting.nama_keg', $query, 'both', null, true)
                ->orLike('ruangan.nama_ruangan', $query, 'both', null, true)
                ->groupEnd();
        }

        return $builder;
    }

    private function withRoom(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->join('ruangan', 'ruangan.id = meeting.ruangan_id')
            ->orderBy('meeting.waktu_mulai')
            ->orderBy('meeting.id');
    }

    public function withZoomWrite(array $booking, ?string $nextZoomId, Closure $write): mixed
    {
        $id = (int) $booking['id'];
        $keys = ['meeting:' . $id];
        foreach (array_unique([$booking['zoom_meeting_id'] ?? '', $nextZoomId ?? '']) as $zoomId) {
            if ($zoomId !== '') {
                $keys[] = 'zoom:' . $zoomId;
            }
        }
        sort($keys);

        // Serialize the sharing check with local link changes and remote mutations.
        $locked = [];
        try {
            foreach ($keys as $key) {
                $name = hash('sha256', $this->db->getDatabase() . ':' . $this->db->getPrefix() . ':meetingku:' . $key);
                $result = match ($this->db->DBDriver) {
                    'Postgre' => $this->db->query(
                        'SELECT CAST(pg_try_advisory_lock(hashtext(?), hashtext(?)) AS INTEGER) AS acquired',
                        ['meetingku-zoom', $name],
                    )->getRowArray()['acquired'],
                    'MySQLi' => $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$name])->getRowArray()['acquired'],
                    'SQLite3' => 1, // Local tests have no concurrent provider writes.
                    default => throw new RuntimeException('Unsupported database for Zoom booking writes.'),
                };
                if ((int) $result !== 1) {
                    throw new RuntimeException('Zoom booking is already being updated.');
                }
                $locked[] = $name;
            }

            $current = $this->find($id);
            if ($current === null || $current !== $booking) {
                throw new RuntimeException('Meeting changed before the Zoom booking could be updated.');
            }
            return $write();
        } finally {
            foreach (array_reverse($locked) as $name) {
                match ($this->db->DBDriver) {
                    'Postgre' => $this->db->query('SELECT pg_advisory_unlock(hashtext(?), hashtext(?))', ['meetingku-zoom', $name]),
                    'MySQLi' => $this->db->query('SELECT RELEASE_LOCK(?)', [$name]),
                    default => null,
                };
            }
        }
    }

    public function hasOtherZoomBookings(array $meeting): bool
    {
        return $this->otherZoomBookings($meeting)->select('id')->get(1)->getRowArray() !== null;
    }

    /**
     * Detach the current row without orphaning the host link of a remaining booking.
     * When the canonical row goes away, transfer its provider credentials to a new root.
     */
    public function detachZoomBooking(array $meeting, ?array $replacement = null): bool
    {
        $id = (int) $meeting['id'];
        if (empty($meeting['zoom_meeting_id'])) {
            return $replacement === null ? $this->delete($id) : $this->update($id, $replacement);
        }

        if (!$this->db->transBegin()) {
            return false;
        }

        $committed = false;
        try {
            if (empty($meeting['zoom_source_meeting_id'])) {
                $next = $this->otherZoomBookings($meeting)
                    ->select('id')
                    ->orderBy('id')
                    ->get(1)
                    ->getRowArray();
                if ($next !== null) {
                    $nextId = (int) $next['id'];
                    if (!$this->otherZoomBookings($meeting)->where('id !=', $nextId)->update([
                        'zoom_source_meeting_id' => $nextId,
                    ]) || !$this->db->table($this->table)->where('id', $nextId)->update([
                        'zoom_source_meeting_id' => null,
                        'zoom_start_url' => $meeting['zoom_start_url'],
                    ])) {
                        return false;
                    }
                }
            }

            $saved = $replacement === null ? $this->delete($id) : $this->update($id, $replacement);
            $committed = $saved && $this->db->transStatus() && $this->db->transCommit();
            return $committed;
        } finally {
            if (!$committed) {
                $this->db->transRollback();
            }
        }
    }

    private function otherZoomBookings(array $meeting): BaseBuilder
    {
        return $this->db->table($this->table)
            ->where('zoom_meeting_id', $meeting['zoom_meeting_id'])
            ->where('id !=', $meeting['id']);
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
