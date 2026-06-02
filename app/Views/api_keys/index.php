<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-key" aria-hidden="true"></i>
            API Key Management
        </h2>
        <p class="page-subtitle">Kelola API key untuk akses MeetingKU API.</p>
    </div>
    <div class="page-actions">
        <a href="<?= site_url('/') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>

<?php if (!empty($newKey)): ?>
    <div class="card" style="border-color: var(--warning); background: var(--warning-soft); margin-bottom: 16px;">
        <div class="card-section">
            <div style="display:flex;align-items:start;gap:12px;">
                <i class="fas fa-exclamation-triangle" style="color:var(--warning);font-size:1.25rem;margin-top:2px;"></i>
                <div style="flex:1;">
                    <p style="font-weight:700;margin:0 0 4px;">API Key Baru — Simpan Sekarang!</p>
                    <p style="color:var(--body);font-size:.8125rem;margin:0 0 8px;">Pegawai: <strong><?= esc($newKey['pegawai']) ?></strong>. Key ini hanya ditampilkan <strong>sekali</strong>.</p>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="text" class="input" id="newKeyInput" value="<?= esc($newKey['key']) ?>" readonly style="font-family:ui-monospace,monospace;font-size:.8rem;max-width:420px;flex:1;min-width:200px;">
                        <button class="btn btn-secondary btn-sm" type="button" onclick="copyKey()">
                            <i class="fas fa-copy" aria-hidden="true"></i> Copy
                        </button>
                    </div>
                    <p style="color:var(--mute);font-size:.75rem;margin:8px 0 0;">Gunakan header <code style="background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;">X-API-KEY</code> saat memanggil API.</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Generate New Key -->
<div class="card" style="margin-bottom: 16px;">
    <div class="card-section">
        <h3 style="font-size:.9375rem;font-weight:700;margin:0 0 16px;">
            <i class="fas fa-plus-circle" aria-hidden="true" style="color:var(--primary);"></i> Generate API Key Baru
        </h3>
        <form action="<?= site_url('api-keys') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="pegawai_id">Pegawai</label>
                    <select class="input" id="pegawai_id" name="pegawai_id" required>
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($pegawaiList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="name">Nama Key (opsional)</label>
                    <input class="input" type="text" id="name" name="name" placeholder="Meeting API Key" value="Meeting API Key">
                </div>
                <div class="field" style="align-self:end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key" aria-hidden="true"></i> Generate Key
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- API Keys Table -->
<div class="card">
    <div class="card-section" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div class="field" style="flex:1;min-width:240px;">
            <label class="field-label sr-only" for="searchKeys" style="position:absolute;left:-9999px;">Cari API key</label>
            <input type="search" id="searchKeys" class="input" placeholder="Cari nama, pegawai, prefix...">
        </div>
        <span style="color:var(--mute);font-size:.8125rem;">
            <span id="rowCount"><?= count($keys) ?></span> dari <?= count($keys) ?> keys
        </span>
    </div>
    <div class="card-divider"></div>
    <div style="overflow-x:auto;">
        <table class="table-modern" id="keysTable">
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Nama</th>
                    <th>Prefix</th>
                    <th>Pegawai</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Terakhir Dipakai</th>
                    <th style="width:100px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keys)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-key" aria-hidden="true"></i></div>
                                <p class="empty-state-title">Belum ada API key</p>
                                <p class="empty-state-desc">Generate key pertama untuk pegawai.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($keys as $i => $key): ?>
                        <tr data-search="<?= esc(strtolower(($key['name'] ?? '') . ' ' . ($key['pegawai_nama'] ?? '') . ' ' . ($key['prefix'] ?? '')), 'attr') ?>">
                            <td style="color:var(--mute);font-family:ui-monospace,monospace;font-size:.75rem;text-align:center;"><?= $i + 1 ?></td>
                            <td style="font-weight:600;"><?= esc($key['name'] ?? '-') ?></td>
                            <td><code style="font-family:ui-monospace,monospace;font-size:.8rem;background:var(--canvas-soft);padding:2px 8px;border-radius:4px;"><?= esc($key['prefix'] ?? '-') ?></code></td>
                            <td><?= esc($key['pegawai_nama'] ?? '-') ?></td>
                            <td>
                                <?php if ($key['is_active']): ?>
                                    <span class="badge-status badge-approved">Active</span>
                                <?php else: ?>
                                    <span class="badge-status badge-rejected">Revoked</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.75rem;color:var(--mute);"><?= esc($key['created_at'] ?? '-') ?></td>
                            <td style="font-size:.75rem;color:var(--mute);"><?= esc($key['last_used_at'] ?? 'Belum pernah') ?></td>
                            <td style="text-align:right;">
                                <?php if ($key['is_active']): ?>
                                    <form action="<?= site_url('api-keys/' . $key['id'] . '/revoke') ?>" method="post" class="d-inline"
                                          onsubmit="return confirm('Yakin revoke key ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-ban" aria-hidden="true"></i> Revoke
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:.75rem;color:var(--mute);">Revoked <?= esc($key['revoked_at'] ?? '') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Toast Notifications -->
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
    // Search filter
    var search = document.getElementById('searchKeys');
    var rows = document.querySelectorAll('#keysTable tbody tr[data-search]');
    var rowCount = document.getElementById('rowCount');
    if (search) {
        search.addEventListener('input', function() {
            var q = (search.value || '').trim().toLowerCase();
            var visible = 0;
            rows.forEach(function(tr) {
                var key = tr.getAttribute('data-search') || '';
                var match = !q || key.indexOf(q) !== -1;
                tr.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (rowCount) rowCount.textContent = visible;
        });
    }

    // Toast auto-dismiss
    var toast = document.getElementById('flashToast');
    if (toast) {
        var c = toast.querySelector('.toast-close');
        if (c) c.addEventListener('click', function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250); });
        setTimeout(function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250); }, 4000);
    }
});

function copyKey() {
    var input = document.getElementById('newKeyInput');
    input.select();
    navigator.clipboard.writeText(input.value);
    var btn = input.nextElementSibling;
    var orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(function() { btn.innerHTML = orig; }, 2000);
}
</script>
<?= $this->endSection() ?>
