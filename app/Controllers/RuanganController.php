<?php

namespace App\Controllers;

use App\Models\RuanganModel;
use CodeIgniter\Controller;

class RuanganController extends Controller
{
    protected $ruanganModel;

    public function __construct()
    {
        $this->ruanganModel = new RuanganModel();
    }

    protected function isAdmin()
    {
        return session()->get('is_admin') === true;
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $data['ruangan'] = $this->ruanganModel->findAll();
        $data['isAdmin'] = session()->get('is_admin') ?? false;
        return view('ruangan/index', $data);
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat membuat ruangan baru');
        }

        $data = [
            'nama_ruangan' => $this->request->getPost('nama_ruangan'),
            'tipe' => $this->request->getPost('tipe')
        ];

        if (!$this->ruanganModel->validate($data)) {
            return redirect()->back()
                ->with('errors', $this->ruanganModel->errors())
                ->withInput();
        }

        if ($this->ruanganModel->insert($data) === false) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data ruangan')
                ->withInput();
        }

        return redirect()->to('/ruangan')->with('success', 'Ruangan berhasil ditambahkan');
    }

    public function edit($id = null)
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengubah data ruangan');
        }

        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            return redirect()->to('/ruangan')->with('error', 'Ruangan tidak ditemukan');
        }

        $data['ruangan'] = $ruangan;
        $data['isAdmin'] = true;
        return view('ruangan/edit', $data);
    }

    public function update($id = null)
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengubah data ruangan');
        }

        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            return redirect()->to('/ruangan')->with('error', 'Ruangan tidak ditemukan');
        }

        $data = [
            'nama_ruangan' => $this->request->getPost('nama_ruangan'),
            'tipe' => $this->request->getPost('tipe')
        ];

        if (!$this->ruanganModel->validate($data)) {
            return redirect()->back()
                ->with('errors', $this->ruanganModel->errors())
                ->withInput();
        }

        if ($this->ruanganModel->update($id, $data) === false) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate data ruangan')
                ->withInput();
        }

        return redirect()->to('/ruangan')->with('success', 'Data ruangan berhasil diupdate');
    }

    public function delete($id = null)
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat menghapus ruangan');
        }

        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            return redirect()->to('/ruangan')->with('error', 'Ruangan tidak ditemukan');
        }

        if (!$this->ruanganModel->canDelete($id)) {
            return redirect()->back()->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki meeting yang disetujui');
        }

        if ($this->ruanganModel->delete($id) === false) {
            return redirect()->back()->with('error', 'Gagal menghapus data ruangan');
        }

        return redirect()->to('/ruangan')->with('success', 'Data ruangan berhasil dihapus');
    }

    public function meetings($id = null)
    {
        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            return redirect()->to('/ruangan')->with('error', 'Ruangan tidak ditemukan');
        }

        $data['meetings'] = $this->ruanganModel->getMeetings($id);
        $data['ruangan'] = $ruangan;
        return view('ruangan/meetings', $data);
    }
}
