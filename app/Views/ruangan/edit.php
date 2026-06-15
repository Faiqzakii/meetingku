<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-pen-to-square" aria-hidden="true"></i>
            Edit Ruangan
        </h2>
        <p class="page-subtitle"><?= esc($ruangan['nama_ruangan']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= base_url('ruangan') ?>" class="btn btn-ghost">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-section">
        <form action="<?= base_url('ruangan/update/' . $ruangan['id']) ?>" method="POST" class="stack js-validated" novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label class="field-label" for="nama_ruangan">Nama ruangan</label>
                <input class="input" type="text" id="nama_ruangan" name="nama_ruangan"
                       value="<?= esc(old('nama_ruangan', $ruangan['nama_ruangan']), 'attr') ?>" required
                       minlength="3" maxlength="100"
                       data-rule-label="Nama ruangan"
                       data-rule-min="3" data-rule-max="100">
            </div>

            <div class="field">
                <label class="field-label" for="tipe">Tipe</label>
                <select class="input" id="tipe" name="tipe" required>
                    <?php $tipe = old('tipe', $ruangan['tipe']); ?>
                    <option value="Offline" <?= $tipe === 'Offline' ? 'selected' : '' ?>>Offline</option>
                    <option value="Online"  <?= $tipe === 'Online'  ? 'selected' : '' ?>>Online</option>
                    <option value="Hybrid"  <?= $tipe === 'Hybrid'  ? 'selected' : '' ?>>Hybrid</option>
                </select>
            </div>

            <label style="display:inline-flex;align-items:center;gap:10px;font-size:.9rem;">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       <?= bool_val(old('is_active', $ruangan['is_active'] ?? false)) ? 'checked' : '' ?>>
                Aktifkan ruangan
            </label>

            <div class="row-actions" style="justify-content:flex-end;">
                <a href="<?= base_url('ruangan') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
