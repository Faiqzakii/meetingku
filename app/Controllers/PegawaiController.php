<?php

namespace App\Controllers;

use App\Libraries\EmployeeXlsxReader;
use App\Models\PegawaiModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class PegawaiController extends Controller
{
    private const MAX_IMPORT_FILE_BYTES = 2 * 1024 * 1024;
    private const MAX_IMPORT_EXPANDED_BYTES = 8 * 1024 * 1024;
    private const MAX_IMPORT_ZIP_ENTRIES = 256;

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
            'is_admin' => bool_val($this->request->getPost('is_admin')) ? '1' : '0',
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

        $input = $this->request->getPost();

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

        $isAdmin = bool_val($input['is_admin'] ?? false);
        if ($isAdmin !== bool_val($existingPegawai['is_admin'])) {
            $data['is_admin'] = $isAdmin ? '1' : '0';
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
        if (!session()->get('logged_in') || !$this->isAdmin()) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengimport data pegawai');
        }

        $file = $this->request->getFile('excel_file');
        if (in_array($file?->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return redirect()->back()->with('error', 'Ukuran file Excel maksimal 2 MB.');
        }
        if (!$file?->isValid()) {
            return redirect()->back()->with('error', 'File harus dalam format Excel (.xlsx)');
        }
        $size = $file->getSize();
        if ($size > self::MAX_IMPORT_FILE_BYTES) {
            return redirect()->back()->with('error', 'Ukuran file Excel maksimal 2 MB.');
        }
        if (!$size || strtolower($file->getExtension()) !== 'xlsx') {
            return redirect()->back()->with('error', 'File harus dalam format Excel (.xlsx)');
        }

        $path = $file->getTempName();
        $spreadsheet = null;
        $successCount = 0;
        $errors = [];
        $readError = 'File Excel tidak dapat dibaca. Gunakan template import pegawai.';
        $emptyError = 'Tidak ada data pegawai pada lembar pertama. Isi kolom A–F mulai baris 2.';

        try {
            $archiveError = $this->checkImportArchive($path, $readError);
            if ($archiveError !== null) {
                return redirect()->back()->with('error', $archiveError);
            }

            $reader = new EmployeeXlsxReader();
            // The import always uses the first tab, regardless of which tab was active when saved.
            $sheetNames = $reader->listWorksheetNames($path);
            $firstSheet = $sheetNames[0] ?? '';
            // Xlsx compares sheet names loosely; reject ambiguous names before loading.
            if ($firstSheet === '' || count(array_keys($sheetNames, $firstSheet)) !== 1) {
                return redirect()->back()->with('error', $readError);
            }

            $spreadsheet = $reader->setLoadSheetsOnly($firstSheet)
                ->load($path);
            if ($reader->rowLimitExceeded()) {
                return redirect()->back()->with(
                    'error',
                    'Lembar pertama hanya boleh berisi data pada kolom A–F sampai baris ' . EmployeeXlsxReader::LAST_IMPORT_ROW
                    . ' (maksimal ' . EmployeeXlsxReader::MAX_DATA_ROWS . ' baris data).',
                );
            }
            if ($spreadsheet->getSheetCount() !== 1) {
                return redirect()->back()->with('error', $readError);
            }

            $worksheet = $spreadsheet->getSheet(0);
            $lastDataRow = min(EmployeeXlsxReader::LAST_IMPORT_ROW, $worksheet->getHighestDataRow());
            if ($lastDataRow < 2) {
                return redirect()->back()->with('error', $emptyError);
            }

            foreach ($worksheet->getRowIterator(2, $lastDataRow) as $rowNumber => $row) {
                $cells = $row->getCellIterator('A', 'F');
                $cells->setIfNotExists(CellIterator::IF_NOT_EXISTS_RETURN_NULL);
                $values = [];
                $rowError = null;

                foreach ($cells as $column => $cell) {
                    $type = $cell?->getDataType();
                    if ($type === DataType::TYPE_FORMULA || $type === DataType::TYPE_ERROR) {
                        $rowError = 'Gunakan nilai biasa, bukan rumus atau nilai error Excel.';
                        break;
                    }
                    if ($column === 'B' && $type === DataType::TYPE_NUMERIC) {
                        $rowError = 'NIP harus diisi sebagai teks agar 18 digitnya tetap utuh.';
                        break;
                    }
                    if ($column === 'C' && $type === DataType::TYPE_NUMERIC) {
                        $rowError = 'Nomor HP harus diisi sebagai teks agar angka awalnya tetap utuh.';
                        break;
                    }
                    $value = trim((string) $cell?->getValue());
                    if ($value !== '') {
                        $values[$column] = $value;
                    }
                }
                if ($rowError !== null) {
                    $errors[] = "Baris $rowNumber: $rowError";
                    continue;
                }
                if ($values === []) {
                    continue;
                }

                $data = [
                    'nama' => $values['A'] ?? '',
                    'nip' => $values['B'] ?? '',
                    'no_hp' => $values['C'] ?? '',
                    'username' => $values['D'] ?? '',
                    'password' => $values['E'] ?? '',
                    'is_admin' => strtolower($values['F'] ?? '') === 'ya',
                    'terima_notif_offline' => 0,
                    'terima_notif_zoom' => 0,
                ];

                if ($this->pegawaiModel->insert($data) === false) {
                    $rowError = implode(', ', $this->pegawaiModel->errors()) ?: 'Data pegawai tidak dapat disimpan.';
                    $errors[] = "Baris $rowNumber: $rowError";
                } else {
                    $successCount++;
                }
            }

            if ($successCount === 0 && $errors === []) {
                return redirect()->back()->with('error', $emptyError);
            }

            $message = "Berhasil import $successCount data pegawai.";
            if ($errors !== []) {
                $errorCount = count($errors);
                $message .= " Gagal import $errorCount data.\nError: " . implode("\n", $errors);
                return redirect()->back()->with('warning', $message);
            }
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            log_message('error', 'Import pegawai gagal ({type}).', ['type' => $e::class]);
            $message = $spreadsheet === null ? $readError : "Import terhenti. Berhasil import $successCount data pegawai.";
            return redirect()->back()->with('error', $message);
        } finally {
            $spreadsheet?->disconnectWorksheets();
        }
    }

    private function checkImportArchive(string $path, string $readError): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
            return $readError;
        }

        // The reader still expands package XML and shared strings before applying its cell filter.
        $archiveTooLarge = 'Isi file Excel terlalu besar. Pindahkan data ke template import pegawai.';
        try {
            if ($zip->numFiles > self::MAX_IMPORT_ZIP_ENTRIES) {
                return $archiveTooLarge;
            }
            $expandedBytes = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);
                if ($entry === false || $entry['size'] < 0) {
                    return $readError;
                }
                $expandedBytes += $entry['size'];
                if ($entry['size'] > self::MAX_IMPORT_FILE_BYTES || $expandedBytes > self::MAX_IMPORT_EXPANDED_BYTES) {
                    return $archiveTooLarge;
                }
            }
            return null;
        } finally {
            $zip->close();
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
        $sheet->setCellValueExplicit('B2', '198501012010011001', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '08123456789', DataType::TYPE_STRING);
        $sheet->setCellValue('D2', 'johndoe');
        $sheet->setCellValue('E2', 'password123');
        $sheet->setCellValue('F2', 'Tidak');
        $sheet->getStyle('B:C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->setSelectedCells('A1');

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
