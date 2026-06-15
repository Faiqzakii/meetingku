<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-user-pen" aria-hidden="true"></i>
            Edit Pegawai
        </h2>
        <p class="page-subtitle"><?= esc($pegawai['nama']) ?> · <?= esc($pegawai['nip']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= base_url('pegawai') ?>" class="btn btn-ghost">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="card" style="border-left:4px solid var(--danger);margin-bottom:16px;">
        <div class="card-section">
            <strong style="color:var(--danger);">Periksa kembali isian:</strong>
            <ul style="margin:8px 0 0 18px;">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-section">
        <form action="<?= base_url('pegawai/update/' . $pegawai['id']) ?>" method="POST" class="stack js-validated" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">

            <div class="field">
                <label class="field-label" for="nama">Nama lengkap</label>
                <input class="input" type="text" id="nama" name="nama" value="<?= esc(old('nama', $pegawai['nama']), 'attr') ?>" required
                       minlength="3" maxlength="100"
                       data-rule-label="Nama"
                       data-rule-min="3" data-rule-max="100">
            </div>

            <div class="field">
                <label class="field-label" for="nip">NIP</label>
                <input class="input" type="text" id="nip" name="nip" value="<?= esc(old('nip', $pegawai['nip']), 'attr') ?>" required
                       minlength="18" maxlength="18" inputmode="numeric"
                       pattern="\d{18}"
                       data-rule-label="NIP"
                       data-rule-min="18" data-rule-max="18"
                       data-rule-pattern="^\d{18}$"
                       data-rule-pattern-message="NIP harus terdiri dari 18 digit angka.">
            </div>

            <div class="field">
                <label class="field-label" for="no_hp">Nomor telepon</label>
                <input class="input" type="text" id="no_hp" name="no_hp" value="<?= esc(old('no_hp', $pegawai['no_hp'] ?? ''), 'attr') ?>" placeholder="08xxxxxxxxxx"
                       maxlength="20" inputmode="tel"
                       pattern="[0-9+\-\s]{8,20}"
                       data-rule-label="Nomor telepon"
                       data-rule-max="20"
                       data-rule-pattern="^[0-9+\-\s]{8,20}$"
                       data-rule-pattern-message="Nomor telepon hanya boleh angka, +, atau spasi (8–20 karakter).">
            </div>

            <div class="field">
                <label class="field-label" for="username">Username</label>
                <input class="input" type="text" id="username" name="username" value="<?= esc(old('username', $pegawai['username']), 'attr') ?>" required
                       minlength="3" maxlength="50"
                       data-rule-label="Username"
                       data-rule-min="3" data-rule-max="50">
            </div>

            <div class="field">
                <label class="field-label" for="password">Password</label>
                <input class="input" type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah"
                       minlength="3" maxlength="100"
                       data-rule-label="Password"
                       data-rule-min="3" data-rule-max="100">
                <span class="field-help">Biarkan kosong untuk mempertahankan password lama.</span>
            </div>

            <div class="field">
                <label class="field-label" for="is_admin">Role</label>
                <?php $currentIsAdmin = bool_val(old('is_admin', $pegawai['is_admin'] ?? false)); ?>
                <select class="input" id="is_admin" name="is_admin" required>
                    <option value="0" <?= $currentIsAdmin ? '' : 'selected' ?>>Pegawai</option>
                    <option value="1" <?= $currentIsAdmin ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="row-actions" style="justify-content:flex-end;margin-top:8px;">
                <a href="<?= base_url('pegawai') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
