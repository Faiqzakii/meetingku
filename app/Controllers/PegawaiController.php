<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PegawaiController extends Controller
{
    protected $pegawaiModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
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

        $data['pegawai'] = $this->pegawaiModel->findAll();
        $data['isAdmin'] = $this->isAdmin();
        return view('pegawai/index', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nip' => $this->request->getPost('nip'),
            'no_hp' => $this->request->getPost('no_hp'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'is_admin' => (int) bool_val($this->request->getPost('is_admin')),
            'terima_notif_offline' => 0,
            'terima_notif_zoom' => 0
        ];

        if ($this->pegawaiModel->insert($data) === false) {
            return redirect()->back()
                ->with('errors', $this->pegawaiModel->errors())
                ->withInput();
        }

        return redirect()->to('/pegawai')->with('success', 'Pegawai berhasil ditambahkan');
    }

    public function edit($id = null)
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        $data['pegawai'] = $this->pegawaiModel->find($id);
        if (!$data['pegawai']) {
            return redirect()->to('/pegawai')->with('error', 'Pegawai tidak ditemukan');
        }

        return view('pegawai/edit', $data);
    }

    public function update($id = null)
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        $existingPegawai = $this->pegawaiModel->find($id);
        if (!$existingPegawai) {
            return redirect()->to('/pegawai')->with('error', 'Pegawai tidak ditemukan');
        }

        $input = $this->request->getMethod() === 'put'
            ? $this->request->getRawInput()
            : $this->request->getPost();

        $data = [];
        $nama = $input['nama'] ?? '';
        if ($nama && $nama !== $existingPegawai['nama']) {
            $data['nama'] = $nama;
        }

        $nip = $input['nip'] ?? '';
        if ($nip && $nip !== $existingPegawai['nip']) {
            $data['nip'] = $nip;
        }

        $username = $input['username'] ?? '';
        if ($username && $username !== $existingPegawai['username']) {
            $data['username'] = $username;
        }

        $isAdmin = (int) bool_val($input['is_admin'] ?? false);
        if ($isAdmin !== (int) bool_val($existingPegawai['is_admin'])) {
            $data['is_admin'] = $isAdmin;
        }

        $no_hp = $input['no_hp'] ?? '';
        if ($no_hp !== ($existingPegawai['no_hp'] ?? '')) {
            $data['no_hp'] = $no_hp;
        }

        if ($password = ($input['password'] ?? false)) {
            $data['password'] = $password;
        }

        if (empty($data)) {
            return redirect()->to('/pegawai')->with('success', 'Tidak ada perubahan pada data pegawai');
        }

        if ($this->pegawaiModel->update($id, $data) === false) {
            return redirect()->back()
                ->with('errors', $this->pegawaiModel->errors())
                ->withInput();
        }

        return redirect()->to('/pegawai')->with('success', 'Pegawai berhasil diupdate');
    }

    public function delete($id = null)
    {
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->to('auth/login');
        }

        if ($this->pegawaiModel->delete($id) === false) {
            return redirect()->back()->with('error', 'Gagal menghapus pegawai');
        }

        return redirect()->to('/pegawai')->with('success', 'Pegawai berhasil dihapus');
    }

    public function import()
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengimport data pegawai');
        }

        $file = $this->request->getFile('excel_file');
        
        if (!$file->isValid() || $file->getExtension() !== 'xlsx') {
            return redirect()->back()->with('error', 'File harus dalam format Excel (.xlsx)');
        }

        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $row) {
                if (empty($row[0])) continue;

                $data = [
                    'nama' => trim($row[0]),
                    'nip' => trim($row[1]),
                    'no_hp' => isset($row[2]) ? trim($row[2]) : '',
                    'username' => trim($row[3]),
                    'password' => trim($row[4]),
                    'is_admin' => (int) (isset($row[5]) && strtolower(trim($row[5])) === 'ya'),
                    'terima_notif_offline' => 0,
                    'terima_notif_zoom' => 0
                ];

                if ($this->pegawaiModel->insert($data) === false) {
                    $errorCount++;
                    $errors[] = "Baris " . ($successCount + $errorCount + 1) . ": " . implode(', ', $this->pegawaiModel->errors());
                } else {
                    $successCount++;
                }
            }

            $message = "Berhasil import $successCount data pegawai.";
            if ($errorCount > 0) {
                $message .= " Gagal import $errorCount data.";
                if (!empty($errors)) {
                    $message .= "\nError: " . implode("\n", $errors);
                }
                return redirect()->back()->with('warning', $message);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'NIP');
        $sheet->setCellValue('C1', 'No HP');
        $sheet->setCellValue('D1', 'Username');
        $sheet->setCellValue('E1', 'Password');
        $sheet->setCellValue('F1', 'Admin (Ya/Tidak)');

        $sheet->setCellValue('A2', 'John Doe');
        $sheet->setCellValue('B2', '198501012010011001');
        $sheet->setCellValue('C2', '08123456789');
        $sheet->setCellValue('D2', 'johndoe');
        $sheet->setCellValue('E2', 'password123');
        $sheet->setCellValue('F2', 'Tidak');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="template_pegawai.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
