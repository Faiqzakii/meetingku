<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<?php $isAdmin = (bool) session()->get('is_admin'); ?>

<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-users" aria-hidden="true"></i>
            Data Pegawai
        </h2>
        <p class="page-subtitle">Kelola data pegawai dalam sistem.</p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="page-actions">
            <a href="<?= base_url('pegawai/downloadTemplate') ?>" class="btn btn-secondary">
                <i class="fas fa-file-arrow-down" aria-hidden="true"></i> Template
            </a>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel" aria-hidden="true"></i> Import
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPegawaiModal">
                <i class="fas fa-user-plus" aria-hidden="true"></i> Tambah Pegawai
            </button>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-section" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div class="field" style="flex:1;min-width:240px;">
            <label class="field-label sr-only" for="searchPegawai" style="position:absolute;left:-9999px;">Cari pegawai</label>
            <input type="search" id="searchPegawai" class="input" placeholder="Cari nama, NIP, atau username...">
        </div>
        <span style="color:var(--mute);font-size:.8125rem;">
            <span id="rowCount"><?= count($pegawai) ?></span> dari <?= count($pegawai) ?> pegawai
        </span>
    </div>
    <div class="card-divider"></div>
    <div style="overflow-x:auto;">
        <table class="table-modern" id="pegawaiTable">
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Role</th>
                    <?php if ($isAdmin): ?><th style="width:64px;text-align:right;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pegawai)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-user-plus" aria-hidden="true"></i></div>
                                <p class="empty-state-title">Belum ada data pegawai</p>
                                <p class="empty-state-desc">Tambahkan pegawai pertama atau import dari Excel.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pegawai as $i => $p): ?>
                        <tr data-search="<?= esc(strtolower(($p['nama'] ?? '') . ' ' . ($p['nip'] ?? '') . ' ' . ($p['username'] ?? '')), 'attr') ?>">
                            <td style="color:var(--mute);font-family:ui-monospace,monospace;font-size:.75rem;text-align:center;"><?= $i + 1 ?></td>
                            <td class="cell-truncate" style="max-width:280px;" title="<?= esc($p['nama'], 'attr') ?>">
                                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                                    <span class="user-avatar" style="width:28px;height:28px;font-size:.65rem;flex-shrink:0;">
                                        <?= esc(strtoupper(mb_substr($p['nama'], 0, 2))) ?>
                                    </span>
                                    <span style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc($p['nama']) ?></span>
                                </div>
                            </td>
                            <td style="font-family:ui-monospace,monospace;font-size:.8rem;color:var(--body);white-space:nowrap;"><?= esc($p['nip']) ?></td>
                            <td>
                                <?php if (bool_val($p['is_admin'] ?? false)): ?>
                                    <span class="badge-status badge-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge-status">User</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td style="text-align:right;">
                                    <div class="kebab" data-kebab>
                                        <button type="button" class="kebab-btn" aria-haspopup="menu" aria-expanded="false" aria-label="Aksi pegawai">
                                            <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                                        </button>
                                        <div class="kebab-menu" role="menu">
                                            <a href="<?= base_url('pegawai/edit/' . $p['id']) ?>" role="menuitem">
                                                <i class="fas fa-pen" aria-hidden="true"></i> Edit
                                            </a>
                                            <button type="button" data-delete-form="<?= esc($p['id'], 'attr') ?>" role="menuitem" class="danger">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <form action="<?= base_url('pegawai/delete/' . $p['id']) ?>" method="POST" id="deleteForm-<?= esc($p['id']) ?>" style="display:none;">
                                        <?= csrf_field() ?>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Pegawai Modal -->
<div class="modal fade" id="addPegawaiModal" tabindex="-1" aria-hidden="true" aria-labelledby="addPegawaiTitle">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPegawaiTitle">Tambah Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('pegawai/create') ?>" method="POST" class="js-validated" novalidate>
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="stack">
                        <div class="field">
                            <label class="field-label" for="add_nama">Nama lengkap</label>
                            <input class="input" type="text" id="add_nama" name="nama" placeholder="Nama lengkap" required
                                   minlength="3" maxlength="100"
                                   data-rule-label="Nama"
                                   data-rule-min="3" data-rule-max="100">
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_nip">NIP</label>
                            <input class="input" type="text" id="add_nip" name="nip" placeholder="Nomor Induk Pegawai (18 digit)" required
                                   minlength="18" maxlength="18" inputmode="numeric"
                                   pattern="\d{18}"
                                   data-rule-label="NIP"
                                   data-rule-min="18" data-rule-max="18"
                                   data-rule-pattern="^\d{18}$"
                                   data-rule-pattern-message="NIP harus terdiri dari 18 digit angka.">
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_no_hp">Nomor telepon</label>
                            <input class="input" type="text" id="add_no_hp" name="no_hp" placeholder="08xxxxxxxxxx"
                                   maxlength="20" inputmode="tel"
                                   pattern="[0-9+\-\s]{8,20}"
                                   data-rule-label="Nomor telepon"
                                   data-rule-max="20"
                                   data-rule-pattern="^[0-9+\-\s]{8,20}$"
                                   data-rule-pattern-message="Nomor telepon hanya boleh angka, +, atau spasi (8–20 karakter).">
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_username">Username</label>
                            <input class="input" type="text" id="add_username" name="username" placeholder="Untuk login" required
                                   minlength="3" maxlength="50"
                                   data-rule-label="Username"
                                   data-rule-min="3" data-rule-max="50">
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_password">Password</label>
                            <input class="input" type="password" id="add_password" name="password" placeholder="Password" required
                                   minlength="3" maxlength="100"
                                   data-rule-label="Password"
                                   data-rule-min="3" data-rule-max="100">
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_role">Role</label>
                            <select class="input" id="add_role" name="is_admin" required>
                                <option value="0">User</option>
                                <option value="1">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true" aria-labelledby="importTitle">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importTitle">Import data pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('pegawai/import') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <p style="margin:0 0 12px;color:var(--body);font-size:.875rem;">
                        Pilih file Excel (.xlsx). <a href="<?= base_url('pegawai/downloadTemplate') ?>" style="color:var(--primary-dark);font-weight:600;">Unduh template</a>.
                    </p>
                    <div style="border:1.5px dashed var(--border-strong);border-radius:var(--radius-md);padding:18px;text-align:center;">
                        <i class="fas fa-cloud-arrow-up" aria-hidden="true" style="font-size:1.5rem;color:var(--mute);"></i>
                        <p style="margin:8px 0 4px;font-weight:600;font-size:.875rem;">Pilih file Excel</p>
                        <p style="color:var(--mute);font-size:.75rem;margin:0 0 10px;">Format .xlsx</p>
                        <input type="file" name="excel_file" accept=".xlsx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload" aria-hidden="true"></i> Upload</button>
                </div>
            </form>
        </div>
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
    var search = document.getElementById('searchPegawai');
    var rows = document.querySelectorAll('#pegawaiTable tbody tr');
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

    document.addEventListener('click', function(e) {
        var del = e.target.closest('[data-delete-form]');
        if (!del) return;
        var id = del.getAttribute('data-delete-form');
        var form = document.getElementById('deleteForm-' + id);
        if (!form) return;
        if (confirm('Hapus pegawai ini? Tindakan tidak dapat dibatalkan.')) form.submit();
    });

    var toast = document.getElementById('flashToast');
    if (toast) {
        var c = toast.querySelector('.toast-close');
        if (c) c.addEventListener('click', function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250); });
        setTimeout(function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250); }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
