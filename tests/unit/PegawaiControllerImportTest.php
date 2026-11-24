<?php

use App\Controllers\PegawaiController;
use App\Models\PegawaiModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @internal
 */
final class PegawaiControllerImportTest extends CIUnitTestCase
{
    private array $tempPaths = [];

    public function testImportsOnlyFirstSheetWithActualRowNumbersAndPartialSuccess(): void
    {
        $book = new Spreadsheet();
        $first = $book->getActiveSheet();
        $first->fromArray(['Nama', 'NIP', 'No HP', 'Username', 'Password', 'Admin (Ya/Tidak)']);
        $first->getStyle('B:C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $this->addEmployee($first, 2, 'pegawai_awal');
        $first->setCellValue('F2', ' yA ');
        $this->addEmployee($first, 5, 'duplikat');
        $this->addEmployee($first, 6, 'rumus');
        $first->setCellValue('D6', '=1+1');
        $this->addEmployee($first, 7, 'nip_angka');
        $first->setCellValueExplicit('B7', 198501012010011000, DataType::TYPE_NUMERIC);
        $this->addEmployee($first, 251, 'pegawai_akhir');
        $first->setCellValue('XFD300', 'Di luar kolom import');
        $second = $book->createSheet();
        $this->addEmployee($second, 252, 'abaikan_lembar_ini');
        $book->setActiveSheetIndex(1);

        $attempts = [];
        $model = $this->createMock(PegawaiModel::class);
        $model->expects($this->exactly(3))->method('insert')->willReturnCallback(
            static function (array $data) use (&$attempts): int|false {
                $attempts[] = $data;
                return $data['username'] === 'duplikat' ? false : count($attempts);
            }
        );
        $model->expects($this->once())->method('errors')->willReturn(['username' => 'Username sudah digunakan']);
        $this->import($model, $this->workbookUpload($book));

        $this->assertSame(['pegawai_awal', 'duplikat', 'pegawai_akhir'], array_column($attempts, 'username'));
        $this->assertSame('098501012010011001', $attempts[0]['nip']);
        $this->assertSame('08123456789', $attempts[0]['no_hp']);
        $this->assertSame('=kata_sandi', $attempts[0]['password']);
        $this->assertTrue($attempts[0]['is_admin']);
        $this->assertFalse($attempts[2]['is_admin']);
        $this->assertSame(
            "Berhasil import 2 data pegawai. Gagal import 3 data.\nError: Baris 5: Username sudah digunakan"
            . "\nBaris 6: Gunakan nilai biasa, bukan rumus atau nilai error Excel."
            . "\nBaris 7: NIP harus diisi sebagai teks agar 18 digitnya tetap utuh.",
            session()->getFlashdata('warning')
        );
    }

    public function testRejectsRowsAfterLimitBeforeSavingAnyEmployee(): void
    {
        $book = new Spreadsheet();
        $first = $book->getActiveSheet();
        $this->addEmployee($first, 2, 'pegawai_awal');
        $first->setCellValue('F252', 'Tidak');

        $this->import($this->modelExpectingNoInserts(), $this->workbookUpload($book));

        $this->assertStringContainsString('sampai baris 251', session()->getFlashdata('error'));
    }

    public function testEmptyFirstSheetDoesNotFallBackToActiveSheet(): void
    {
        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray(['Nama', 'NIP', 'No HP', 'Username', 'Password', 'Admin (Ya/Tidak)']);
        $this->addEmployee($book->createSheet(), 2, 'abaikan_lembar_ini');
        $book->setActiveSheetIndex(1);

        $this->import($this->modelExpectingNoInserts(), $this->workbookUpload($book));

        $this->assertStringContainsString('Tidak ada data pegawai pada lembar pertama', session()->getFlashdata('error'));
    }

    public function testRejectsOversizedUploadBeforeReadingIt(): void
    {
        $upload = $this->createMock(UploadedFile::class);
        $upload->method('getError')->willReturn(UPLOAD_ERR_OK);
        $upload->method('isValid')->willReturn(true);
        $upload->method('getSize')->willReturn(2 * 1024 * 1024 + 1);
        $upload->expects($this->never())->method('getExtension');
        $upload->expects($this->never())->method('getTempName');

        $this->import($this->modelExpectingNoInserts(), $upload);

        $this->assertSame('Ukuran file Excel maksimal 2 MB.', session()->getFlashdata('error'));
    }

    public function testRejectsWorkbookWithAnOversizedExpandedEntry(): void
    {
        $book = new Spreadsheet();
        $this->addEmployee($book->getActiveSheet(), 2, 'pegawai_awal');
        $upload = $this->workbookUpload($book);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($upload->getTempName()));
        $this->assertTrue($zip->addFromString('fixture.txt', str_repeat('x', 2 * 1024 * 1024 + 1)));
        $this->assertTrue($zip->close());

        $this->import($this->modelExpectingNoInserts(), $upload);

        $this->assertStringContainsString('Isi file Excel terlalu besar', session()->getFlashdata('error'));
    }

    public function testNonAdminCannotReadUpload(): void
    {
        $this->import($this->modelExpectingNoInserts(), null, false);

        $this->assertSame('Hanya admin yang dapat mengimport data pegawai', session()->getFlashdata('error'));
    }

    public function testMissingUploadReturnsAFormatError(): void
    {
        $this->import($this->modelExpectingNoInserts(), null);

        $this->assertSame('File harus dalam format Excel (.xlsx)', session()->getFlashdata('error'));
    }

    protected function tearDown(): void
    {
        foreach ($this->tempPaths as $path) {
            unlink($path);
        }
        parent::tearDown();
    }

    private function workbookUpload(Spreadsheet $book): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pegawai_import_');
        $this->assertIsString($path);
        $this->tempPaths[] = $path;
        try {
            (new Xlsx($book))->setPreCalculateFormulas(false)->save($path);
        } finally {
            $book->disconnectWorksheets();
        }

        $upload = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$path, 'pegawai.xlsx', null, null, UPLOAD_ERR_OK])
            ->onlyMethods(['isValid'])
            ->getMock();
        $upload->method('isValid')->willReturn(true);
        return $upload;
    }

    private function addEmployee(Worksheet $sheet, int $row, string $username): void
    {
        $sheet->setCellValue("A$row", 'Pegawai Contoh');
        $sheet->setCellValueExplicit("B$row", '098501012010011001', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("C$row", '08123456789', DataType::TYPE_STRING);
        $sheet->setCellValue("D$row", $username);
        $sheet->setCellValueExplicit("E$row", '=kata_sandi', DataType::TYPE_STRING);
    }

    private function modelExpectingNoInserts(): PegawaiModel
    {
        $model = $this->createMock(PegawaiModel::class);
        $model->expects($this->never())->method('insert');
        return $model;
    }

    private function import(PegawaiModel $model, ?UploadedFile $upload, bool $isAdmin = true): void
    {
        session()->set(['logged_in' => true, 'is_admin' => $isAdmin, '_ci_previous_url' => site_url('pegawai')]);
        $request = $this->createMock(IncomingRequest::class);
        $request->expects($isAdmin ? $this->once() : $this->never())->method('getFile')
            ->with('excel_file')->willReturn($upload);
        $controller = new class ($model) extends PegawaiController {
            public function __construct(PegawaiModel $model)
            {
                $this->pegawaiModel = $model;
            }
        };
        $controller->initController($request, service('response'), service('logger'));
        $controller->import();
    }
}
