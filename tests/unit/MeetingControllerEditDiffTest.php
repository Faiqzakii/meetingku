<?php

use App\Controllers\MeetingController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MeetingControllerEditDiffTest extends CIUnitTestCase
{
    private function invoke(array $before, array $after): array
    {
        $controller = (new ReflectionClass(MeetingController::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($controller, 'buildMeetingEditDiff');
        $method->setAccessible(true);

        return $method->invoke($controller, $before, $after);
    }

    public function testReturnsEmptyWhenNothingChanged(): void
    {
        $row = [
            'nama_keg'        => 'Rapat',
            'jumlah_peserta'  => '10',
            'waktu_mulai'     => '2026-07-15 10:00:00',
            'waktu_selesai'   => '2026-07-15 11:00:00',
            'ruangan_id'      => 1,
            'fasilitas'       => json_encode(['Snack', 'Mic']),
        ];

        $this->assertSame([], $this->invoke($row, $row));
    }

    public function testDetectsNameChange(): void
    {
        $diff = $this->invoke(
            ['nama_keg' => 'Rapat Lama', 'ruangan_id' => 1],
            ['nama_keg' => 'Rapat Baru', 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('Nama Kegiatan', $diff[0]['label']);
        $this->assertSame('Rapat Lama', $diff[0]['before']);
        $this->assertSame('Rapat Baru', $diff[0]['after']);
    }

    public function testDetectsParticipantCountChange(): void
    {
        $diff = $this->invoke(
            ['jumlah_peserta' => '10', 'ruangan_id' => 1],
            ['jumlah_peserta' => '15', 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('Jumlah Peserta', $diff[0]['label']);
        $this->assertSame('10', $diff[0]['before']);
        $this->assertSame('15', $diff[0]['after']);
    }

    public function testDetectsStartTimeShiftOnly(): void
    {
        $diff = $this->invoke(
            ['waktu_mulai' => '2026-07-15 10:00:00', 'waktu_selesai' => '2026-07-15 11:00:00', 'ruangan_id' => 1],
            ['waktu_mulai' => '2026-07-15 10:30:00', 'waktu_selesai' => '2026-07-15 11:00:00', 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('Waktu', $diff[0]['label']);
        $this->assertSame('15 Jul 2026 10:00 - 11:00', $diff[0]['before']);
        $this->assertSame('15 Jul 2026 10:30 - 11:00', $diff[0]['after']);
    }

    public function testDetectsEndTimeShiftOnly(): void
    {
        $diff = $this->invoke(
            ['waktu_mulai' => '2026-07-15 10:00:00', 'waktu_selesai' => '2026-07-15 11:00:00', 'ruangan_id' => 1],
            ['waktu_mulai' => '2026-07-15 10:00:00', 'waktu_selesai' => '2026-07-15 12:00:00', 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('Waktu', $diff[0]['label']);
        $this->assertSame('15 Jul 2026 10:00 - 11:00', $diff[0]['before']);
        $this->assertSame('15 Jul 2026 10:00 - 12:00', $diff[0]['after']);
    }

    public function testFasilitasIsOrderInsensitive(): void
    {
        $diff = $this->invoke(
            ['fasilitas' => json_encode(['Snack', 'Mic']), 'ruangan_id' => 1],
            ['fasilitas' => json_encode(['Mic', 'Snack']), 'ruangan_id' => 1]
        );

        $this->assertSame([], $diff, 'reorder alone is not a meaningful change');
    }

    public function testFasilitasContentChangeIsDetected(): void
    {
        $diff = $this->invoke(
            ['fasilitas' => json_encode(['Snack']), 'ruangan_id' => 1],
            ['fasilitas' => json_encode(['Snack', 'Mic']), 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('Fasilitas', $diff[0]['label']);
        $this->assertSame('Snack', $diff[0]['before']);
        $this->assertSame('Mic, Snack', $diff[0]['after']);
    }

    public function testFasilitasFromNullToSomeIsDetected(): void
    {
        $diff = $this->invoke(
            ['fasilitas' => null, 'ruangan_id' => 1],
            ['fasilitas' => json_encode(['Snack']), 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('-', $diff[0]['before']);
        $this->assertSame('Snack', $diff[0]['after']);
    }

    public function testFasilitasFromSomeToNullIsDetected(): void
    {
        $diff = $this->invoke(
            ['fasilitas' => json_encode(['Snack']), 'ruangan_id' => 1],
            ['fasilitas' => null, 'ruangan_id' => 1]
        );

        $this->assertCount(1, $diff);
        $this->assertSame('Snack', $diff[0]['before']);
        $this->assertSame('-', $diff[0]['after']);
    }

    public function testReportsAllChangedFieldsTogether(): void
    {
        $diff = $this->invoke(
            [
                'nama_keg'        => 'Rapat',
                'jumlah_peserta'  => '10',
                'waktu_mulai'     => '2026-07-15 10:00:00',
                'waktu_selesai'   => '2026-07-15 11:00:00',
                'ruangan_id'      => 1,
                'fasilitas'       => json_encode(['Snack']),
            ],
            [
                'nama_keg'        => 'Rapat Edit',
                'jumlah_peserta'  => '20',
                'waktu_mulai'     => '2026-07-15 14:00:00',
                'waktu_selesai'   => '2026-07-15 15:00:00',
                'ruangan_id'      => 1,
                'fasilitas'       => json_encode(['Snack', 'Mic']),
            ]
        );

        $labels = array_column($diff, 'label');
        $this->assertSame(
            ['Nama Kegiatan', 'Jumlah Peserta', 'Waktu', 'Fasilitas'],
            $labels
        );
    }
}
