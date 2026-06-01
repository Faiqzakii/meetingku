<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-key"></i> API Key Management</h2>
        <a href="<?= site_url('/') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($newKey)): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <h5><i class="bi bi-exclamation-triangle"></i> API Key Baru — Simpan Sekarang!</h5>
            <p class="mb-1">Pegawai: <strong><?= esc($newKey['pegawai']) ?></strong></p>
            <p class="mb-2">Key ini hanya ditampilkan <strong>sekali</strong>:</p>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" id="newKeyInput"
                       value="<?= esc($newKey['key']) ?>" readonly>
                <button class="btn btn-outline-primary" type="button" onclick="copyKey()">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
            <small class="text-muted">Gunakan header <code>X-API-KEY</code> saat memanggil API.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Generate New Key -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-plus-circle"></i> Generate API Key Baru
        </div>
        <div class="card-body">
            <form action="<?= site_url('api-keys') ?>" method="post">
                <?= csrf_field() ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pegawai</label>
                        <select name="pegawai_id" class="form-select" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Key (opsional)</label>
                        <input type="text" name="name" class="form-control" placeholder="Meeting API Key" value="Meeting API Key">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-key-fill"></i> Generate Key
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- API Keys Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-list"></i> Daftar API Keys (<?= count($keys) ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Prefix</th>
                            <th>Pegawai</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Terakhir Dipakai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($keys)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">Belum ada API key</td></tr>
                        <?php else: ?>
                            <?php foreach ($keys as $i => $key): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($key['name'] ?? '-') ?></td>
                                    <td><code><?= esc($key['prefix'] ?? '-') ?></code></td>
                                    <td><?= esc($key['pegawai_nama'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($key['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Revoked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= $key['created_at'] ?? '-' ?></small></td>
                                    <td><small><?= $key['last_used_at'] ?? 'Belum pernah' ?></small></td>
                                    <td>
                                        <?php if ($key['is_active']): ?>
                                            <form action="<?= site_url('api-keys/' . $key['id'] . '/revoke') ?>" method="post" class="d-inline"
                                                  onsubmit="return confirm('Yakin revoke key ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-x-circle"></i> Revoke
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Revoked <?= $key['revoked_at'] ?? '' ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function copyKey() {
    const input = document.getElementById('newKeyInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        alert('API Key copied!');
    });
}
</script>

<?php $this->endSection(); ?>
