<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-clipboard-list" aria-hidden="true"></i>
            Meeting · <?= esc($pegawai['nama']) ?>
        </h2>
        <p class="page-subtitle">Riwayat meeting yang diajukan pegawai ini.</p>
    </div>
    <div class="page-actions">
        <a href="<?= base_url('pegawai') ?>" class="btn btn-ghost">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-section">
        <?php if (empty($meetings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></div>
                <p class="empty-state-title">Tidak ada meeting terjadwal</p>
                <p class="empty-state-desc">Pegawai ini belum memiliki ajuan meeting.</p>
            </div>
        <?php else: ?>
            <ul class="stack" style="margin:0;padding:0;list-style:none;">
                <?php foreach ($meetings as $meeting): ?>
                    <li style="border:1px solid var(--border);border-radius:var(--radius-md);padding:14px 16px;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                            <div style="min-width:0;flex:1;">
                                <h3 class="clamp-1" style="font-size:.95rem;font-weight:700;margin:0 0 4px;" title="<?= esc($meeting['nama_keg'], 'attr') ?>"><?= esc($meeting['nama_keg']) ?></h3>
                                <p class="clamp-1" style="margin:0 0 4px;color:var(--body);font-size:.8125rem;">
                                    <i class="fas fa-clock" aria-hidden="true" style="color:var(--mute);"></i>
                                    <?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?> – <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                </p>
                                <p class="clamp-1" style="margin:0;color:var(--body);font-size:.8125rem;">
                                    <i class="fas fa-door-open" aria-hidden="true" style="color:var(--mute);"></i>
                                    <?= esc($meeting['nama_ruangan']) ?>
                                </p>
                            </div>
                            <span class="badge-status badge-<?= esc($meeting['status']) ?>" style="flex-shrink:0;">
                                <?= esc(ucfirst($meeting['status'])) ?>
                            </span>
                        </div>

                        <?php if (!empty($isAdmin) && $meeting['status'] === 'pending'): ?>
                            <form action="<?= base_url('meeting/' . $meeting['id'] . '/status') ?>" method="POST" class="row-actions" style="margin-top:12px;">
                                <?= csrf_field() ?>
                                <button type="submit" name="status" value="approved" class="btn btn-secondary btn-sm" style="color:var(--success);border-color:#bbf7d0;">
                                    <i class="fas fa-check" aria-hidden="true"></i> Setuju
                                </button>
                                <button type="submit" name="status" value="rejected" class="btn btn-secondary btn-sm" style="color:var(--danger);border-color:#fecaca;">
                                    <i class="fas fa-xmark" aria-hidden="true"></i> Tolak
                                </button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<div class="toast-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="toast-notification toast-success" id="flashToast" role="status">
            <i class="fas fa-circle-check" aria-hidden="true"></i>
            <span><?= esc(session()->getFlashdata('success')) ?></span>
            <button class="toast-close" type="button" aria-label="Tutup notifikasi"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="toast-notification toast-error" id="flashToast" role="alert">
            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
            <button class="toast-close" type="button" aria-label="Tutup notifikasi"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toast = document.getElementById('flashToast');
    if (toast) {
        var c = toast.querySelector('.toast-close');
        if (c) c.addEventListener('click', function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250); });
        setTimeout(function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250); }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
