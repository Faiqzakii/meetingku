<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/** @internal */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class RuanganAgendaAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite3 is required for room agenda tests.');
        }

        $config = new class extends Database {
            public array $tests = ['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBDebug' => true];
        };
        Factories::injectMock('config', Database::class, $config);
        $this->db = Database::connect('tests');
        $this->db->query('CREATE TABLE ruangan (id INTEGER PRIMARY KEY, nama_ruangan TEXT, tipe TEXT)');
        $this->db->query('CREATE TABLE pegawai (id INTEGER PRIMARY KEY, nama TEXT)');
        $this->db->query('CREATE TABLE meeting (id INTEGER PRIMARY KEY, nama_keg TEXT, ruangan_id INTEGER, '
            . 'pegawai_id INTEGER, waktu_mulai TEXT, waktu_selesai TEXT, status TEXT)');
        $this->db->query("INSERT INTO ruangan VALUES (1, 'Ruang Uji Agenda', 'Hybrid')");
        $this->db->query("INSERT INTO pegawai VALUES (1, 'Nama Pemohon Internal')");
        $this->db->query(<<<'SQL'
            INSERT INTO meeting VALUES
                (1, 'Rapat Uji Menunggu', 1, 1, '2025-09-15 08:00:00', '2025-09-15 09:00:00', 'pending'),
                (2, 'Rapat Uji Disetujui', 1, 1, '2025-09-15 09:00:00', '2025-09-15 10:00:00', 'approved'),
                (3, 'Rapat Uji Ditolak', 1, 1, '2025-09-15 10:00:00', '2025-09-15 11:00:00', 'rejected')
            SQL);
    }

    public static function viewers(): array
    {
        return [
            'guest' => [[], false],
            'employee' => [['logged_in' => true, 'is_admin' => false, 'pegawai_id' => 2], true],
            'admin' => [['logged_in' => true, 'is_admin' => true, 'pegawai_id' => 1], true],
        ];
    }

    #[DataProvider('viewers')]
    public function testAgendaShowsOnlyTheDataAllowedForTheViewer(array $session, bool $canSeeInternal): void
    {
        $agenda = $this->withSession($session)->get('/ruangan/1/meetings?date=2025-09-15');

        $agenda->assertStatus(200);
        $agenda->assertSee('Rapat Uji Disetujui');
        foreach (['Rapat Uji Menunggu', 'Rapat Uji Ditolak', 'Nama Pemohon Internal'] as $detail) {
            if ($canSeeInternal) {
                $agenda->assertSee($detail);
            } else {
                $agenda->assertDontSee($detail);
            }
        }
    }
}
