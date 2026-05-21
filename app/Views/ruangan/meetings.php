<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<?php $selectedDate = $date ?? date('Y-m-d'); ?>

<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            Agenda · <?= esc($ruangan['nama_ruangan']) ?>
        </h2>
        <p class="page-subtitle"><?= esc($ruangan['tipe']) ?> · <?= date('l, d F Y', strtotime($selectedDate)) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= base_url('ruangan') ?>" class="btn btn-ghost">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>

<form method="GET" action="<?= base_url('ruangan/' . $ruangan['id'] . '/meetings') ?>" class="card" style="margin-bottom:20px;">
    <div class="card-section" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
        <div class="field" style="min-width:200px;">
            <label class="field-label" for="date">Tanggal</label>
            <input class="input" type="date" id="date" name="date" value="<?= esc($selectedDate, 'attr') ?>">
        </div>
        <div class="row-actions">
            <button class="btn btn-primary" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Tampilkan</button>
            <a href="<?= base_url('ruangan/' . $ruangan['id'] . '/meetings') ?>?date=<?= date('Y-m-d') ?>" class="btn btn-ghost">Hari ini</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-section">
        <?php if (empty($meetings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></div>
                <p class="empty-state-title">Tidak ada meeting terjadwal</p>
                <p class="empty-state-desc">Coba pilih tanggal lain atau periksa ruangan lain.</p>
            </div>
        <?php else: ?>
            <ul class="stack" style="margin:0;padding:0;list-style:none;">
                <?php foreach ($meetings as $meeting): ?>
                    <li style="border:1px solid var(--border);border-radius:var(--radius-md);padding:14px 16px;display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:flex-start;">
                        <div style="min-width:0;flex:1;">
                            <h3 class="clamp-1" style="font-size:.95rem;font-weight:700;color:var(--ink);margin:0 0 4px;" title="<?= esc($meeting['nama_keg'], 'attr') ?>">
                                <?= esc($meeting['nama_keg']) ?>
                            </h3>
                            <p class="clamp-1" style="margin:0;color:var(--body);font-size:.8125rem;">
                                <i class="fas fa-clock" aria-hidden="true" style="color:var(--mute);"></i>
                                <?= date('H:i', strtotime($meeting['waktu_mulai'])) ?> – <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                · <i class="fas fa-user" aria-hidden="true" style="color:var(--mute);margin-left:6px;"></i>
                                <?= esc($meeting['nama_pegawai'] ?? '—') ?>
                            </p>
                        </div>
                        <span class="badge-status badge-<?= esc($meeting['status']) ?>" style="flex-shrink:0;">
                            <?= esc(ucfirst($meeting['status'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
