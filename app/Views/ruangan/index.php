<?= $this->extend('layout') ?>

<?= $this->section('styles') ?>
<style>
    .ruangan-list { container-type: inline-size; }
    .ruangan-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
    .ruangan-filters .field { flex: 1 1 15rem; }
    .ruangan-filters :is(.input, .chip) { min-height: 44px; }
    .ruangan-filters .chip.is-active { background: var(--primary-dark); border-color: var(--primary-dark); }
    .ruangan-table thead th:first-child { width: 55%; }
    .ruangan-table thead th:last-child { width: 1%; }
    .ruangan-table thead th:last-child,
    .ruangan-table tbody td:last-child { text-align: right; }
    .ruangan-table .ruangan-name {
        font-size: 1rem;
        font-weight: 600;
        text-align: left;
        overflow-wrap: anywhere;
    }
    .ruangan-table :is(.badge-status, .row-actions) { white-space: nowrap; }
    .ruangan-table .badge-status { color: var(--ink-soft); }
    .ruangan-list :is(a, button, input):focus-visible {
        outline: 2px solid var(--primary-dark);
        outline-offset: 2px;
    }
    .ruangan-table .kebab-menu .danger:hover { color: var(--ink); }
    #ruanganNoMatches:empty { padding: 0; }

    @container (max-width: 36rem) {
        .ruangan-filters { padding: 14px; border-bottom: 1px solid var(--border); }
        .ruangan-filters .chip { padding-inline: 10px; }
        .ruangan-table,
        .ruangan-table tbody { display: block; }
        .ruangan-table thead {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip-path: inset(50%);
        }
        .ruangan-table tbody tr:not([hidden]) {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
        }
        .ruangan-table tbody tr:last-child { border-bottom: 0; }
        .ruangan-table tbody :is(th, td) { padding: 0; border: 0; }
        .ruangan-table .ruangan-name { flex: 0 0 100%; min-width: 0; }
        .ruangan-table tbody td:last-child { margin-inline-start: auto; }
        .ruangan-table :is(.btn-sm, .kebab-btn, .kebab-menu a, .kebab-menu button) {
            min-height: 44px;
            min-width: 44px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $isAdmin = (bool) session()->get('is_admin'); ?>

<div class="page-header">
    <div>
        <h2 class="page-title">
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

<div class="card ruangan-list">
    <?php if (empty($ruangan)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-building" aria-hidden="true"></i></div>
            <p class="empty-state-title">Belum ada data ruangan</p>
            <?php if ($isAdmin): ?>
                <p class="empty-state-desc">Gunakan Tambah Ruangan untuk membuat ruangan pertama.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card-section ruangan-filters">
            <div class="field">
                <label class="visually-hidden" for="searchRuangan">Cari ruangan</label>
                <input type="search" id="searchRuangan" class="input" placeholder="Cari nama ruangan...">
            </div>
            <div class="chip-row" role="group" aria-label="Filter tipe ruangan">
                <button type="button" class="chip is-active" data-filter-type="" aria-pressed="true">Semua</button>
                <button type="button" class="chip" data-filter-type="Offline" aria-pressed="false">Offline</button>
                <button type="button" class="chip" data-filter-type="Online" aria-pressed="false">Online</button>
                <button type="button" class="chip" data-filter-type="Hybrid" aria-pressed="false">Hybrid</button>
            </div>
        </div>
        <table class="table-modern ruangan-table" id="ruanganTable" role="table" aria-label="Daftar ruangan">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Nama ruangan</th>
                    <th scope="col" role="columnheader">Tipe</th>
                    <th scope="col" role="columnheader">Status</th>
                    <th scope="col" role="columnheader">Aksi</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                <?php foreach ($ruangan as $r): ?>
                    <?php $isActive = bool_val($r['is_active'] ?? true); ?>
                    <tr role="row" data-type="<?= esc($r['tipe'], 'attr') ?>"
                        data-search="<?= esc(mb_strtolower($r['nama_ruangan']), 'attr') ?>">
                        <th scope="row" role="rowheader" class="ruangan-name"><?= esc($r['nama_ruangan']) ?></th>
                        <td role="cell"><?= esc($r['tipe']) ?></td>
                        <td role="cell">
                            <span class="badge-status <?= $isActive ? 'badge-approved' : 'badge-rejected' ?>">
                                <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td role="cell">
                            <div class="row-actions">
                                <a href="<?= base_url('ruangan/' . $r['id'] . '/meetings') ?>" class="btn btn-secondary btn-sm"
                                   aria-label="Agenda <?= esc($r['nama_ruangan'], 'attr') ?>">
                                    <i class="fas fa-calendar-alt" aria-hidden="true"></i> Agenda
                                </a>
                                <?php if ($isAdmin): ?>
                                    <div class="kebab" data-kebab>
                                        <button type="button" class="kebab-btn" aria-haspopup="menu" aria-expanded="false"
                                                aria-label="Aksi untuk <?= esc($r['nama_ruangan'], 'attr') ?>">
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
                                    <form action="<?= base_url('ruangan/delete/' . $r['id']) ?>" method="POST" id="deleteForm-<?= esc($r['id']) ?>" hidden>
                                        <?= csrf_field() ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="ruanganNoMatches" class="empty-state" role="status"></div>
    <?php endif; ?>
</div>

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
    var table = document.getElementById('ruanganTable');
    if (table) {
        var search = document.getElementById('searchRuangan');
        var rows = table.querySelectorAll('[data-search]');
        var typeButtons = document.querySelectorAll('[data-filter-type]');
        var noMatches = document.getElementById('ruanganNoMatches');
        var typeFilter = '';

        function applyFilters() {
            var query = search.value.trim().toLowerCase();
            var hasMatches = false;

            rows.forEach(function(row) {
                var matches = row.dataset.search.includes(query)
                    && (!typeFilter || row.dataset.type === typeFilter);
                row.hidden = !matches;
                hasMatches = hasMatches || matches;
            });

            noMatches.textContent = hasMatches ? '' : 'Tidak ada ruangan yang cocok. Coba kata kunci atau tipe lain.';
        }

        search.addEventListener('input', applyFilters);
        typeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                typeButtons.forEach(function(button) {
                    var active = button === btn;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', String(active));
                });
                typeFilter = btn.dataset.filterType;
                applyFilters();
            });
        });
    }

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
