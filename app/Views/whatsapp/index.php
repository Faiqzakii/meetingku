<?= $this->extend('layout') ?>

<?= $this->section('styles') ?>
<style>
    .wa-page { max-width: 1180px; margin: 0 auto; padding: 24px 20px 40px; }
    .wa-hero { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:18px; }
    .wa-eyebrow { margin:0 0 6px; color:var(--primary-dark); font-weight:800; font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; }
    .wa-title { font-size:clamp(1.6rem, 3vw, 2.25rem); font-weight:800; margin:0; }
    .wa-subtitle { color:var(--body); margin:.45rem 0 0; max-width:680px; }
    .wa-status-pill { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; font-weight:700; font-size:.85rem; border:1px solid var(--border); background:var(--surface); }
    .wa-status-pill.connected { color:#166534; background:#dcfce7; border-color:#86efac; }
    .wa-status-pill.disconnected { color:#991b1b; background:#fee2e2; border-color:#fecaca; }
    .wa-grid { display:grid; grid-template-columns:minmax(0, 1.05fr) minmax(320px, .95fr); gap:18px; align-items:start; }
    .wa-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-2); overflow:hidden; }
    .wa-card-header { padding:18px 20px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    .wa-card-title { font-size:1rem; font-weight:800; margin:0; display:flex; align-items:center; gap:8px; }
    .wa-card-desc { margin:4px 0 0; color:var(--body); font-size:.875rem; }
    .wa-card-body { padding:20px; }
    .wa-qr-box { min-height:300px; display:grid; place-items:center; border:1.5px dashed var(--border-strong); border-radius:var(--radius-lg); background:var(--canvas-soft); text-align:center; padding:18px; }
    .wa-qr-box img { width:260px; height:260px; border-radius:16px; background:#fff; padding:10px; box-shadow:var(--shadow-2); }
    .wa-empty-icon { width:56px; height:56px; border-radius:18px; display:inline-grid; place-items:center; background:var(--primary-soft); color:var(--primary-dark); font-size:1.35rem; margin-bottom:10px; }
    .wa-actions { display:flex; flex-wrap:wrap; gap:10px; align-items:end; }
    .wa-form-inline { display:flex; gap:10px; flex:1; min-width:260px; align-items:end; }
    .wa-form-inline .field { flex:1; margin:0; }
    .wa-alert { padding:12px 14px; border-radius:var(--radius-md); border:1px solid var(--border); margin-bottom:14px; font-weight:600; }
    .wa-alert.success { background:#dcfce7; color:#14532d; border-color:#86efac; }
    .wa-alert.error { background:#fee2e2; color:#7f1d1d; border-color:#fecaca; }
    .wa-alert.warning { background:#fef3c7; color:#78350f; border-color:#fde68a; }
    .wa-key { display:block; max-width:430px; overflow:auto; white-space:nowrap; padding:7px 9px; border-radius:8px; background:#0f172a; color:#e2e8f0; font-size:.78rem; }
    .wa-table { width:100%; border-collapse:separate; border-spacing:0; }
    .wa-table th { text-align:left; color:var(--mute); text-transform:uppercase; letter-spacing:.05em; font-size:.72rem; padding:10px 12px; border-bottom:1px solid var(--border); }
    .wa-table td { padding:12px; border-bottom:1px solid var(--border); vertical-align:middle; font-size:.875rem; }
    .wa-table tr:last-child td { border-bottom:0; }
    .wa-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 9px; border-radius:999px; font-weight:700; font-size:.75rem; }
    .wa-badge.ok { background:#dcfce7; color:#166534; }
    .wa-badge.muted { background:#f1f5f9; color:#475569; }
    .wa-badge.warn { background:#fef3c7; color:#92400e; }
    .wa-queue { margin-top:18px; }
    @media (max-width: 900px) { .wa-hero, .wa-grid { grid-template-columns:1fr; display:grid; } .wa-form-inline { flex-direction:column; align-items:stretch; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $connected = !empty($status['connected']);
    $state = (string) ($status['state'] ?? 'unknown');
    $qr = $status['qr'] ?? null;
?>
<div class="wa-page">
    <section class="wa-hero">
        <div>
            <p class="wa-eyebrow">Admin · WhatsApp</p>
            <h1 class="wa-title">WhatsApp Gateway</h1>
            <p class="wa-subtitle">Kelola koneksi WhatsApp single-session, API key project lain, dan antrean pengiriman pesan.</p>
        </div>
        <span class="wa-status-pill <?= $connected ? 'connected' : 'disconnected' ?>">
            <i class="<?= $connected ? 'fas fa-circle-check' : 'fas fa-circle-xmark' ?>" aria-hidden="true"></i>
            <?= $connected ? 'Connected' : esc(str_replace('_', ' ', ucfirst($state))) ?>
        </span>
    </section>

    <?php if (session()->getFlashdata('success')): ?><div class="wa-alert success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="wa-alert error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php if ($newApiKey): ?><div class="wa-alert warning">API key baru: <code><?= esc($newApiKey) ?></code></div><?php endif; ?>

    <div class="wa-grid">
        <section class="wa-card">
            <div class="wa-card-header">
                <div>
                    <h2 class="wa-card-title"><i class="fab fa-whatsapp" aria-hidden="true"></i> Koneksi WhatsApp</h2>
                    <p class="wa-card-desc">QR Code direkomendasikan untuk login awal. Pairing code tetap tersedia sebagai fallback.</p>
                </div>
            </div>
            <div class="wa-card-body">
                <div class="wa-qr-box">
                    <?php if ($connected): ?>
                        <div>
                            <span class="wa-empty-icon"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <h3 class="wa-card-title" style="justify-content:center;">WhatsApp tersambung</h3>
                            <p class="wa-card-desc">Sender siap memproses queue.</p>
                        </div>
                    <?php elseif ($qr): ?>
                        <div>
                            <img src="<?= esc($qr, 'attr') ?>" alt="QR Code WhatsApp">
                            <p class="wa-card-desc" style="margin-top:12px;">Scan dari WhatsApp → Perangkat tertaut → Tautkan perangkat.</p>
                        </div>
                    <?php else: ?>
                        <div>
                            <span class="wa-empty-icon"><i class="fas fa-qrcode" aria-hidden="true"></i></span>
                            <h3 class="wa-card-title" style="justify-content:center;">QR belum tersedia</h3>
                            <p class="wa-card-desc">Klik Reset untuk memulai ulang session dan menghasilkan QR baru.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="wa-actions" style="margin-top:16px;">
                    <form method="post" action="<?= base_url('whatsapp/pairing-code') ?>" class="wa-form-inline">
                        <?= csrf_field() ?>
                        <div class="field">
                            <label class="field-label" for="phone_number">Nomor pairing</label>
                            <input class="input" id="phone_number" name="phone_number" placeholder="62812xxxx" inputmode="numeric">
                        </div>
                        <button class="btn btn-secondary" type="submit"><i class="fas fa-key" aria-hidden="true"></i> Pairing Code</button>
                    </form>
                    <form method="post" action="<?= base_url('whatsapp/reset-session') ?>"><?= csrf_field() ?><button class="btn btn-secondary" type="submit"><i class="fas fa-rotate" aria-hidden="true"></i> Reset</button></form>
                    <form method="post" action="<?= base_url('whatsapp/logout') ?>"><?= csrf_field() ?><button class="btn btn-danger" type="submit"><i class="fas fa-right-from-bracket" aria-hidden="true"></i> Logout</button></form>
                </div>
            </div>
        </section>

        <section class="wa-card">
            <div class="wa-card-header">
                <div>
                    <h2 class="wa-card-title"><i class="fas fa-key" aria-hidden="true"></i> API Keys</h2>
                    <p class="wa-card-desc">API key bisa dilihat admin. Revoke jika bocor.</p>
                </div>
            </div>
            <div class="wa-card-body">
                <form method="post" action="<?= base_url('whatsapp/api-keys') ?>" class="wa-form-inline" style="margin-bottom:14px;">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="field-label" for="api_key_name">Nama project</label>
                        <input class="input" id="api_key_name" name="name" placeholder="Project integrasi" required>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> Generate</button>
                </form>
                <div style="overflow-x:auto;">
                    <table class="wa-table">
                        <thead><tr><th>Nama</th><th>API Key</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($apiKeys as $key): ?>
                            <tr>
                                <td><strong><?= esc($key['name']) ?></strong></td>
                                <td><code class="wa-key"><?= esc($key['plain_key'] ?? $key['prefix']) ?></code></td>
                                <td><span class="wa-badge <?= !empty($key['is_active']) ? 'ok' : 'muted' ?>"><?= !empty($key['is_active']) ? 'Aktif' : 'Revoked' ?></span></td>
                                <td>
                                    <?php if (!empty($key['is_active'])): ?>
                                        <form method="post" action="<?= base_url('whatsapp/api-keys/' . $key['id'] . '/revoke') ?>" onsubmit="return confirm('Revoke API key ini?');"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Revoke</button></form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($apiKeys)): ?><tr><td colspan="4" style="color:var(--mute);">Belum ada API key.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

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
