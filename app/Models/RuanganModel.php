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

    public function getMeetings($ruanganId, ?string $date = null)
    {
        $builder = $this->db->table('meeting')
            ->select('meeting.*, pegawai.nama as nama_pegawai')
            ->join('pegawai', 'pegawai.id = meeting.pegawai_id', 'left')
            ->where('ruangan_id', $ruanganId)
            ->orderBy('waktu_mulai', 'ASC');

        if ($date) {
            $start = date('Y-m-d 00:00:00', strtotime($date));
            $end   = date('Y-m-d 23:59:59', strtotime($date));
            $builder->where('waktu_mulai >=', $start)
                    ->where('waktu_selesai <=', $end);
        }

        return $builder->get()->getResultArray();
    }
}
