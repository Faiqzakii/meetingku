<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiModel extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nama',
        'nip',
        'no_hp',
        'username',
        'password',
        'is_admin',
        'terima_notif_offline',
        'terima_notif_zoom',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [];
    protected $validationRulesCreate = [
        'nama' => 'required|min_length[3]|max_length[100]',
        'nip' => 'required|min_length[18]|max_length[18]|is_unique[pegawai.nip]',
        'username' => 'required|min_length[3]|max_length[50]|is_unique[pegawai.username]',
        'password' => 'required|min_length[3]',
        'is_admin' => 'permit_empty|in_list[0,1,true,false]',
        'terima_notif_offline' => 'permit_empty|in_list[0,1]',
        'terima_notif_zoom' => 'permit_empty|in_list[0,1]'
    ];
    protected $validationRulesUpdate = [
        'nama' => 'if_exist|min_length[3]|max_length[100]',
        'nip' => 'if_exist|min_length[18]|max_length[18]|is_unique[pegawai.nip,id,{id}]',
        'username' => 'if_exist|min_length[3]|max_length[50]|is_unique[pegawai.username,id,{id}]',
        'password' => 'if_exist|min_length[3]',
        'is_admin' => 'if_exist|in_list[0,1,true,false]',
        'terima_notif_offline' => 'if_exist|in_list[0,1]',
        'terima_notif_zoom' => 'if_exist|in_list[0,1]'
    ];

    public function insert($data = null, bool $returnID = true)
    {
        $this->validationRules = $this->validationRulesCreate;
        return parent::insert($data, $returnID);
    }

    public function update($id = null, $data = null): bool
    {
        $this->validationRules = $this->validationRulesUpdate;
        return parent::update($id, $data);
    }

    protected $validationMessages = [
        'nama' => [
            'required' => 'Nama harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter'
        ],
        'nip' => [
            'required' => 'NIP harus diisi',
            'min_length' => 'NIP harus 18 karakter',
            'max_length' => 'NIP harus 18 karakter',
            'is_unique' => 'NIP sudah digunakan'
        ],
        'username' => [
            'required' => 'Username harus diisi',
            'min_length' => 'Username minimal 3 karakter',
            'max_length' => 'Username maksimal 50 karakter',
            'is_unique' => 'Username sudah digunakan'
        ],
        'password' => [
            'min_length' => 'Password minimal 3 karakter'
        ],
        'is_admin' => [
            'in_list' => 'Status admin tidak valid'
        ]
    ];

    protected $beforeInsert = ['hashPassword', 'boolToPgString'];
    protected $beforeUpdate = ['hashPassword', 'boolToPgString'];

    protected function hashPassword(array $data)
    {
        if (!isset($data['data']['password']) || empty($data['data']['password'])) {
            unset($data['data']['password']);
            return $data;
        }

        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        return $data;
    }

    /**
     * Convert PHP bool to PostgreSQL string 't'/'f' for boolean columns.
     * PostgreSQL PDO rejects integer 1/0 for boolean columns.
     */
    protected function boolToPgString(array $data): array
    {
        $boolFields = ['is_admin', 'terima_notif_offline', 'terima_notif_zoom'];
        foreach ($boolFields as $field) {
            if (isset($data['data'][$field])) {
                $data['data'][$field] = $data['data'][$field] ? 't' : 'f';
            }
        }
        return $data;
    }
}
