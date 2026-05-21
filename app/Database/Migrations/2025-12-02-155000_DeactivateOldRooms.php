<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DeactivateOldRooms extends Migration
{
    public function up()
    {
        $oldRooms = [
            'Ruang Rapat BPS Provinsi Kalimantan Utara',
            'Ruang Rapat Atas BPS Provinsi Kalimantan Utara',
            'Zoom BPS Provinsi Kalimantan Utara'
        ];

        $this->db->table('ruangan')
            ->whereIn('nama_ruangan', $oldRooms)
            ->update(['is_active' => false]);
    }

    public function down()
    {
        $oldRooms = [
            'Ruang Rapat BPS Provinsi Kalimantan Utara',
            'Ruang Rapat Atas BPS Provinsi Kalimantan Utara',
            'Zoom BPS Provinsi Kalimantan Utara'
        ];

        $this->db->table('ruangan')
            ->whereIn('nama_ruangan', $oldRooms)
            ->update(['is_active' => true]);
    }
}
