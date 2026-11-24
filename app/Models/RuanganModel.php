<?php

namespace App\Models;

use CodeIgniter\Model;

class RuanganModel extends Model
{
    protected $table = 'ruangan';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['nama_ruangan', 'tipe', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nama_ruangan' => 'required|min_length[3]|max_length[100]|is_unique[ruangan.nama_ruangan,id,{id}]',
        'tipe' => 'required|in_list[Online,Offline,Hybrid]'
    ];

    protected $validationMessages = [
        'nama_ruangan' => [
            'required' => 'Nama ruangan harus diisi',
            'min_length' => 'Nama ruangan minimal 3 karakter',
            'max_length' => 'Nama ruangan maksimal 100 karakter',
            'is_unique' => 'Nama ruangan sudah digunakan'
        ],
        'tipe' => [
            'required' => 'Tipe ruangan harus diisi',
            'in_list' => 'Tipe ruangan harus Online, Offline, atau Hybrid'
        ]
    ];

    public function canDelete($ruanganId)
    {
        $approvedMeetings = $this->db->table('meeting')
            ->where('ruangan_id', $ruanganId)
            ->where('status', 'approved')
            ->countAllResults();

        return $approvedMeetings === 0;
    }

    public function getActiveRooms()
    {
        return $this->where('is_active', true)->findAll();
    }

    public function getMeetings(int $ruanganId, string $date, bool $loggedIn): array
    {
        $builder = $this->db->table('meeting')
            ->select('meeting.nama_keg, meeting.waktu_mulai, meeting.waktu_selesai, meeting.status')
            ->where('meeting.ruangan_id', $ruanganId)
            ->where('meeting.waktu_mulai >=', $date . ' 00:00:00')
            ->where('meeting.waktu_selesai <=', $date . ' 23:59:59')
            ->orderBy('meeting.waktu_mulai', 'ASC');

        if ($loggedIn) {
            $builder->select('pegawai.nama as nama_pegawai')
                ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left');
        } else {
            $builder->where('meeting.status', 'approved');
        }

        return $builder->get()->getResultArray();
    }
}
