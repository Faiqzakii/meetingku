<?= $this->extend('layout') ?>

<?= $this->section('styles') ?>
<style>
    .wa-page { max-width: 1180px; margin: 0 auto; padding: 24px 20px 40px; }
    .wa-hero { margin-bottom:22px; }
    .wa-eyebrow { margin:0 0 6px; color:var(--primary-dark); font-weight:800; font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; }
    .wa-title { font-size:clamp(1.6rem, 3vw, 2.25rem); font-weight:800; margin:0; }
    .wa-subtitle { color:var(--body); margin:.45rem 0 0; max-width:680px; }
    .wa-card-header.wa-session-header { align-items:center; flex-wrap:wrap; }
    .wa-session-heading { display:flex; align-items:center; gap:12px; min-width:0; }
    .wa-session-mark { display:flex; align-items:center; justify-content:center; flex:0 0 44px; height:44px; border-radius:var(--radius-md); background:var(--primary-soft); color:var(--primary-dark); font-size:1.4rem; }
    .wa-session-status { display:inline-flex; align-items:center; gap:7px; max-width:100%; padding:6px 10px; border-radius:var(--radius-sm); font-size:.75rem; font-weight:700; line-height:1.5; overflow-wrap:anywhere; }
    .wa-session-status i { flex-shrink:0; }
    .wa-session-status.is-success { background:var(--success-soft); color:#166534; }
    .wa-session-status.is-warning { background:var(--warning-soft); color:#92400e; }
    .wa-session-status.is-danger { background:var(--danger-soft); color:#991b1b; }
    .wa-session-status.is-neutral { background:var(--canvas-soft); color:var(--body); }
    .wa-session-content { display:grid; grid-template-columns:minmax(0, 1.4fr) minmax(0, 1fr); gap:24px; align-items:center; padding:24px 20px; }
    .wa-session-summary { min-width:0; }
    .wa-session-summary h3 { font-size:1.125rem; font-weight:800; margin:0 0 8px; }
    .wa-session-summary p { margin:0; color:var(--body); font-size:.875rem; max-width:55ch; }
    .wa-session-detail { min-width:0; margin:0; padding:14px 16px; background:var(--canvas-soft); border:1px solid var(--border); border-radius:var(--radius-md); }
    .wa-session-detail dt { color:var(--body); font-size:.75rem; font-weight:600; margin-bottom:6px; }
    .wa-session-detail dd { margin:0; }
    .wa-session-detail code { color:var(--ink); font-size:.875rem; overflow-wrap:anywhere; white-space:normal; }
    .wa-session-error { display:flex; align-items:flex-start; gap:9px; margin:0 20px 20px; padding:12px 14px; background:var(--danger-soft); color:#991b1b; border-radius:var(--radius-md); font-size:.8125rem; overflow-wrap:anywhere; }
    .wa-session-error i { flex-shrink:0; margin-top:3px; }
    .wa-session-footer { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 20px; background:var(--canvas-soft); border-top:1px solid var(--border); }
    .wa-session-footer p { margin:0; color:var(--body); font-size:.8125rem; max-width:55ch; }
    .wa-dashboard-link { flex-shrink:0; }
    .wa-dashboard-link:focus-visible { outline:2px solid var(--primary-dark); outline-offset:3px; box-shadow:var(--shadow-focus); }
    .wa-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-2); overflow:hidden; }
    .wa-card-header { padding:18px 20px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    .wa-card-title { font-size:1rem; font-weight:800; margin:0; display:flex; align-items:center; gap:8px; }
    .wa-card-desc { margin:4px 0 0; color:var(--body); font-size:.875rem; }
    .wa-card-body { padding:20px; }
    @media (max-width: 640px) {
        .wa-session-content { grid-template-columns:minmax(0, 1fr); gap:18px; padding:20px; }
        .wa-session-footer { align-items:stretch; flex-direction:column; }
        .wa-dashboard-link { width:100%; }
    }
    .wa-alert { padding:12px 14px; border-radius:var(--radius-md); border:1px solid var(--border); margin-bottom:14px; font-weight:600; }
    .wa-alert.success { background:#dcfce7; color:#14532d; border-color:#86efac; }
    .wa-alert.error { background:#fee2e2; color:#7f1d1d; border-color:#fecaca; }
    .wa-table { width:100%; border-collapse:separate; border-spacing:0; }
    .wa-table th { text-align:left; color:var(--mute); text-transform:uppercase; letter-spacing:.05em; font-size:.72rem; padding:10px 12px; border-bottom:1px solid var(--border); }
    .wa-table td { padding:12px; border-bottom:1px solid var(--border); vertical-align:middle; font-size:.875rem; }
    .wa-table tr:last-child td { border-bottom:0; }
    .wa-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 9px; border-radius:999px; font-weight:700; font-size:.75rem; }
    .wa-badge.ok { background:#dcfce7; color:#166534; }
    .wa-badge.muted { background:#f1f5f9; color:#475569; }
    .wa-badge.warn { background:#fef3c7; color:#92400e; }
    .wa-queue { margin-top:18px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $state = is_string($status['status'] ?? null) && trim($status['status']) !== '' ? $status['status'] : 'UNKNOWN';
    $hasStatusError = !empty($status['error']);
    $sessionStates = [
        'CONNECTED' => ['success', 'fa-circle-check', 'WhatsApp terhubung', 'Perangkat siap digunakan untuk mengirim pesan dari antrean.'],
        'DISCONNECTED' => ['warning', 'fa-plug-circle-xmark', 'WhatsApp terputus', 'Perangkat sudah login tetapi belum terhubung. Periksa koneksi melalui dashboard GOWA.'],
        'NOT_LOGGED_IN' => ['warning', 'fa-qrcode', 'Perangkat belum login', 'Pilih perangkat ini di dashboard GOWA dan pindai kode QR untuk menghubungkan akun WhatsApp.'],
    ];
    [$sessionTone, $sessionIcon, $sessionTitle, $sessionDescription] = $hasStatusError
        ? ['danger', 'fa-triangle-exclamation', 'Status koneksi belum dapat diperiksa', 'MeetingKu tidak dapat memastikan koneksi WhatsApp. Periksa layanan dan perangkat melalui dashboard GOWA.']
        : ($sessionStates[$state] ?? ['neutral', 'fa-circle-question', 'Status perangkat belum dikenali', 'Kesiapan pengiriman belum dapat dipastikan. Periksa status perangkat melalui dashboard GOWA.']);
?>
<div class="wa-page">
    <section class="wa-hero">
        <div>
            <p class="wa-eyebrow">Admin · WhatsApp</p>
            <h1 class="wa-title">WhatsApp Gateway</h1>
            <p class="wa-subtitle">Pantau status WhatsApp dan antrean pengiriman pesan. Pengelolaan perangkat dilakukan di dashboard GOWA.</p>
        </div>
    </section>

    <?php if (session()->getFlashdata('success')): ?><div class="wa-alert success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="wa-alert error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <section class="wa-card wa-session" aria-labelledby="wa-session-title">
        <div class="wa-card-header wa-session-header">
            <div class="wa-session-heading">
                <span class="wa-session-mark"><i class="fab fa-whatsapp" aria-hidden="true"></i></span>
                <div>
                    <h2 class="wa-card-title" id="wa-session-title">Koneksi WhatsApp</h2>
                    <p class="wa-card-desc">Perangkat GOWA</p>
                </div>
            </div>
            <span class="wa-session-status is-<?= esc($sessionTone, 'attr') ?>">
                <i class="fas <?= esc($sessionIcon, 'attr') ?>" aria-hidden="true"></i>
                <?= $hasStatusError ? 'Tidak terverifikasi' : esc($state) ?>
            </span>
        </div>
        <div class="wa-session-content">
            <div class="wa-session-summary">
                <h3><?= esc($sessionTitle) ?></h3>
                <p><?= esc($sessionDescription) ?></p>
            </div>
            <dl class="wa-session-detail">
                <dt>ID perangkat</dt>
                <dd><code><?= esc($deviceId) ?></code></dd>
            </dl>
        </div>
        <?php if ($hasStatusError): ?>
            <div class="wa-session-error">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <span><?= esc($status['error']) ?></span>
            </div>
        <?php endif; ?>
        <div class="wa-session-footer">
            <p>Login, pindai QR, dan kelola perangkat di dashboard GOWA. Status diperiksa saat halaman dimuat.</p>
            <?php if ($dashboardUrl !== null): ?>
            <a class="btn btn-secondary wa-dashboard-link" href="<?= esc($dashboardUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer" aria-label="Buka dashboard GOWA (tab baru)">
                Buka dashboard GOWA <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="wa-card wa-queue">
        <div class="wa-card-header">
            <div>
                <h2 class="wa-card-title"><i class="fas fa-list-check" aria-hidden="true"></i> Queue terbaru</h2>
                <p class="wa-card-desc">20 pesan terakhir dari database queue.</p>
            </div>
        </div>
        <div class="wa-card-body" style="overflow-x:auto;">
            <table class="wa-table">
                <thead><tr><th>ID</th><th>Tujuan</th><th>Status</th><th>Attempts</th><th>Error</th></tr></thead>
                <tbody>
                <?php foreach ($queue as $row): ?>
                    <tr>
                        <td><?= esc($row['id']) ?></td>
                        <td><code><?= esc($row['to_number']) ?></code></td>
                        <td><span class="wa-badge <?= $row['status'] === 'sent' ? 'ok' : ($row['status'] === 'failed' ? 'muted' : 'warn') ?>"><?= esc($row['status']) ?></span></td>
                        <td><?= esc($row['attempts']) ?></td>
                        <td style="max-width:380px;color:var(--body);"><?= esc($row['last_error'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($queue)): ?><tr><td colspan="5" style="color:var(--mute);">Queue masih kosong.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
