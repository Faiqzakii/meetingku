<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<?php $isAdmin = (bool) session()->get('is_admin'); ?>

<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-door-open" aria-hidden="true"></i>
            Data Ruangan
        </h2>
        <p class="page-subtitle">Kelola ruangan dan jadwalnya.</p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuanganModal">
                <i class="fas fa-plus" aria-hidden="true"></i> Tambah Ruangan
            </button>
        </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-section" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div class="field" style="flex:1;min-width:240px;">
            <label class="field-label sr-only" for="searchRuangan" style="position:absolute;left:-9999px;">Cari ruangan</label>
            <input type="search" id="searchRuangan" class="input" placeholder="Cari nama ruangan...">
        </div>
        <div class="chip-row" role="group" aria-label="Filter tipe ruangan">
            <button type="button" class="chip is-active" data-filter-type="">Semua</button>
            <button type="button" class="chip" data-filter-type="Offline">Offline</button>
            <button type="button" class="chip" data-filter-type="Online">Online</button>
            <button type="button" class="chip" data-filter-type="Hybrid">Hybrid</button>
        </div>
    </div>
</div>

<?php if (empty($ruangan)): ?>
    <div class="card"><div class="card-section">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-building" aria-hidden="true"></i></div>
            <p class="empty-state-title">Belum ada data ruangan</p>
            <p class="empty-state-desc">Tambahkan ruangan pertama untuk memulai.</p>
        </div>
    </div></div>
<?php else: ?>
    <div id="ruanganGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
        <?php foreach ($ruangan as $r): ?>
            <?php
                $typeIcon = $r['tipe'] === 'Online' ? 'fa-globe' : ($r['tipe'] === 'Hybrid' ? 'fa-arrows-left-right' : 'fa-building');
                $isActive = bool_val($r['is_active'] ?? true);
            ?>
            <article class="card" data-type="<?= esc($r['tipe'], 'attr') ?>"
                     data-search="<?= esc(strtolower($r['nama_ruangan']), 'attr') ?>">
                <div class="card-section">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                            <span style="width:40px;height:40px;border-radius:10px;background:var(--primary-soft);color:var(--primary-dark);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas <?= esc($typeIcon, 'attr') ?>" aria-hidden="true"></i>
                            </span>
                            <div style="min-width:0;flex:1;">
                                <h3 class="clamp-1" style="font-size:.95rem;font-weight:700;color:var(--ink);" title="<?= esc($r['nama_ruangan'], 'attr') ?>"><?= esc($r['nama_ruangan']) ?></h3>
                                <p style="margin:2px 0 0;color:var(--mute);font-size:.75rem;"><?= esc($r['tipe']) ?></p>
                            </div>
                        </div>
                        <span class="badge-status <?= $isActive ? 'badge-approved' : 'badge-rejected' ?>">
                            <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </div>

                    <div class="row-actions" style="margin-top:14px;flex-wrap:wrap;">
                        <a href="<?= base_url('ruangan/' . $r['id'] . '/meetings') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-calendar-alt" aria-hidden="true"></i> Agenda
                        </a>
                        <?php if ($isAdmin): ?>
                            <div class="kebab" data-kebab style="margin-left:auto;">
                                <button type="button" class="kebab-btn" aria-haspopup="menu" aria-expanded="false" aria-label="Aksi ruangan">
                                    <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                                </button>
                                <div class="kebab-menu" role="menu">
                                    <a href="<?= base_url('ruangan/edit/' . $r['id']) ?>" role="menuitem">
                                        <i class="fas fa-pen" aria-hidden="true"></i> Edit
                                    </a>
                                    <button type="button" data-delete-form="<?= esc($r['id'], 'attr') ?>" role="menuitem" class="danger">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            <form action="<?= base_url('ruangan/delete/' . $r['id']) ?>" method="POST" id="deleteForm-<?= esc($r['id']) ?>" style="display:none;">
                                <?= csrf_field() ?>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Ruangan Modal -->
<div class="modal fade" id="addRuanganModal" tabindex="-1" aria-hidden="true" aria-labelledby="addRuanganTitle">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRuanganTitle">Tambah Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('ruangan/create') ?>" method="POST" class="js-validated" novalidate>
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="stack">
                        <div class="field">
                            <label class="field-label" for="add_nama_ruangan">Nama ruangan</label>
                            <input class="input" type="text" id="add_nama_ruangan" name="nama_ruangan" required
                                   minlength="3" maxlength="100"
                                   data-rule-label="Nama ruangan"
                                   data-rule-min="3" data-rule-max="100">
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_tipe">Tipe</label>
                            <select class="input" id="add_tipe" name="tipe" required>
                                <option value="Offline">Offline</option>
                                <option value="Online">Online</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label" for="add_status">Status</label>
                            <select class="input" id="add_status" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
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
    var search = document.getElementById('searchRuangan');
    var grid = document.getElementById('ruanganGrid');
    var cards = grid ? grid.querySelectorAll('[data-search]') : [];
    var typeFilter = '';
    var queryFilter = '';

    function applyFilters() {
        cards.forEach(function(c) {
            var key = c.getAttribute('data-search') || '';
            var type = c.getAttribute('data-type') || '';
            var matchQuery = !queryFilter || key.indexOf(queryFilter) !== -1;
            var matchType = !typeFilter || type === typeFilter;
            c.style.display = (matchQuery && matchType) ? '' : 'none';
        });
    }

    if (search) search.addEventListener('input', function() {
        queryFilter = (search.value || '').trim().toLowerCase();
        applyFilters();
    });

    document.querySelectorAll('[data-filter-type]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-filter-type]').forEach(function(b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            typeFilter = btn.getAttribute('data-filter-type') || '';
            applyFilters();
        });
    });

    document.addEventListener('click', function(e) {
        var del = e.target.closest('[data-delete-form]');
        if (!del) return;
        var id = del.getAttribute('data-delete-form');
        var form = document.getElementById('deleteForm-' + id);
        if (!form) return;
        if (confirm('Hapus ruangan ini? Tindakan tidak dapat dibatalkan.')) form.submit();
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
