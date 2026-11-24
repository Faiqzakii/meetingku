<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Meeting | MeetingKU</title>
    <link href="<?= esc(base_url('css/buttons.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { margin: 1.5rem; font-family: system-ui, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid; padding: .5rem; text-align: left; }
        @media print { #printReport { display: none; } tr { break-inside: avoid; } }
    </style>
</head>
<body>
<main>
    <h1>Riwayat meeting</h1>
    <p><?= esc($filters['start_date']) ?> sampai <?= esc($filters['end_date']) ?></p>
    <p><button type="button" class="btn btn-primary" id="printReport">Cetak</button></p>
    <table>
        <thead>
            <tr>
                <th scope="col">Nama Kegiatan</th>
                <th scope="col">Ruangan</th>
                <th scope="col">Waktu</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $meeting = $meetings->getUnbufferedRow('array'); ?>
            <?php if (!$meeting): ?>
                <tr><td colspan="4">Tidak ada meeting untuk filter ini.</td></tr>
            <?php endif; ?>
            <?php while ($meeting): ?>
                <tr>
                    <td><?= esc($meeting['nama_keg']) ?></td>
                    <td><?= esc($meeting['nama_ruangan'] . ' (' . $meeting['tipe'] . ')') ?></td>
                    <td><?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?> – <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?></td>
                    <td><?= esc(ucfirst($meeting['status'])) ?></td>
                </tr>
                <?php $meeting = $meetings->getUnbufferedRow('array'); ?>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<script>document.getElementById('printReport').addEventListener('click', () => window.print());</script>
</body>
</html>
