<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<?php
    $startDate = $filters['start_date'];
    $endDate = $filters['end_date'];
    $statusFilter = $filters['status'];
    $rangeKey = $filters['range'];
    $reportUrl = base_url('meeting/all') . '?' . http_build_query($filters);
    $today = date('Y-m-d');
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <i class="fas fa-list" aria-hidden="true"></i>
            Semua Kegiatan
        </h2>
        <p class="page-subtitle">Riwayat meeting per periode + filter cepat.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= esc($reportUrl . '&format=csv', 'attr') ?>">
            <i class="fas fa-file-export" aria-hidden="true"></i> Unduh CSV
        </a>
        <a class="btn btn-secondary" href="<?= esc($reportUrl . '&format=print', 'attr') ?>">
            <i class="fas fa-print" aria-hidden="true"></i> Lihat untuk dicetak
        </a>
    </div>
</div>

<search aria-label="Filter riwayat meeting">
<form id="filterForm" action="<?= base_url('meeting/all') ?>" method="GET" class="card" style="padding:16px;margin-bottom:20px;">
    <div style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
        <div class="field" style="min-width:160px;">
            <label class="field-label" for="start_date"><i class="fas fa-calendar" aria-hidden="true"></i> Dari</label>
            <input class="input" type="date" id="start_date" name="start_date" value="<?= esc($startDate, 'attr') ?>">
        </div>
        <div class="field" style="min-width:160px;">
            <label class="field-label" for="end_date"><i class="fas fa-calendar" aria-hidden="true"></i> Sampai</label>
            <input class="input" type="date" id="end_date" name="end_date" value="<?= esc($endDate, 'attr') ?>">
        </div>
        <div class="field" style="min-width:160px;">
            <label class="field-label" for="status">Status</label>
            <select class="input" id="status" name="status">
                <option value="all"      <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua</option>
                <option value="pending"  <?= $statusFilter === 'pending'  ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>
        <div class="field" style="flex:1;min-width:200px;">
            <label class="field-label" for="q">Cari</label>
            <input class="input" type="search" id="q" name="q" maxlength="100" placeholder="Cari nama kegiatan / ruangan..." value="<?= esc($filters['q'], 'attr') ?>">
        </div>
        <div class="row-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter" aria-hidden="true"></i> Terapkan</button>
            <a class="btn btn-ghost" href="<?= base_url('meeting/all') ?>"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset</a>
        </div>
    </div>

    <div style="margin-top:12px;display:flex;gap:6px;flex-wrap:wrap;" role="group" aria-label="Filter cepat tanggal">
        <?php
            $chips = [
                'today'   => ['label' => 'Hari ini',     'start' => $today, 'end' => $today],
                'week'    => ['label' => 'Minggu ini',   'start' => date('Y-m-d', strtotime('monday this week')), 'end' => date('Y-m-d', strtotime('sunday this week'))],
                'month'   => ['label' => 'Bulan ini',    'start' => date('Y-m-01'), 'end' => date('Y-m-t')],
                'pending' => ['label' => 'Pending',      'status' => 'pending'],
            ];
        ?>
        <?php foreach ($chips as $key => $chip): ?>
            <?php
                $href = base_url('meeting/all') . '?range=' . $key;
                if (isset($chip['start']))  $href .= '&start_date=' . $chip['start'] . '&end_date=' . $chip['end'];
                if (isset($chip['status'])) $href .= '&status=' . $chip['status'];
                $active = ($rangeKey === $key)
                    || ($key === 'pending' && $statusFilter === 'pending')
                    || ($key === 'today' && $startDate === $today && $endDate === $today);
            ?>
            <a class="chip <?= $active ? 'is-active' : '' ?>" href="<?= esc($href, 'attr') ?>"><?= esc($chip['label']) ?></a>
        <?php endforeach; ?>
    </div>
</form>
</search>

<div class="card" id="meetingsCard">
    <div style="overflow-x:auto;">
        <table class="table-modern" id="meetingsTable">
            <thead>
                <tr>
                    <th scope="col">Nama Kegiatan</th>
                    <th scope="col">Ruangan</th>
                    <th scope="col">Waktu</th>
                    <th scope="col">Status</th>
                    <th scope="col">Zoom</th>
                    <th scope="col" style="width:64px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($meetings)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-search" aria-hidden="true"></i></div>
                                <p class="empty-state-title">Tidak ada meeting ditemukan</p>
                                <p class="empty-state-desc">Coba ubah filter atau reset untuk melihat semua data.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($meetings as $meeting): ?>
                        <?php
                            $zoomLink = $meeting['zoom_join_url'];
                            $isOnlineOrHybrid = in_array($meeting['tipe'], ['Online', 'Hybrid'], true);
                            $meetingNotEnded = time() < strtotime($meeting['waktu_selesai']);
                            $canSetManualZoom = $isOnlineOrHybrid && $meeting['status'] === 'approved' && $meetingNotEnded;
                        ?>
                        <tr>
                            <td class="cell-truncate" style="max-width:300px;" title="<?= esc($meeting['nama_keg'], 'attr') ?>">
                                <span style="font-weight:600;"><?= esc($meeting['nama_keg']) ?></span>
                            </td>
                            <td class="cell-truncate" style="max-width:200px;" title="<?= esc($meeting['nama_ruangan'] . ' (' . $meeting['tipe'] . ')', 'attr') ?>">
                                <i class="fas fa-door-open" aria-hidden="true" style="color:var(--mute);font-size:.8em;"></i>
                                <?= esc($meeting['nama_ruangan']) ?>
                                <span style="color:var(--mute);">(<?= esc($meeting['tipe']) ?>)</span>
                            </td>
                            <td style="white-space:nowrap;">
                                <?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?>
                                –
                                <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= esc($meeting['status']) ?>">
                                    <?= esc(ucfirst($meeting['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($zoomLink)): ?>
                                    <button type="button" class="btn btn-ghost btn-sm" data-copy="<?= esc($zoomLink, 'attr') ?>">
                                        <i class="fas fa-copy" aria-hidden="true"></i>
                                        <span>Copy</span>
                                    </button>
                                <?php else: ?>
                                    <span style="color:var(--mute);font-size:.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                        <div class="kebab" data-kebab>
                                            <button type="button" class="kebab-btn" aria-haspopup="menu" aria-expanded="false" aria-label="Aksi lain">
                                                <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                                            </button>
                                            <div class="kebab-menu" role="menu">
                                                <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" role="menuitem">
                                                    <i class="fas fa-pen" aria-hidden="true"></i> Edit
                                                </a>
                                                <?php if ($meeting['status'] === 'pending'): ?>
                                                    <button type="button" data-status-form="<?= esc($meeting['id'], 'attr') ?>" data-status="approved" role="menuitem">
                                                        <i class="fas fa-check" aria-hidden="true"></i> Setujui
                                                    </button>
                                                    <button type="button" data-status-form="<?= esc($meeting['id'], 'attr') ?>" data-status="rejected" role="menuitem">
                                                        <i class="fas fa-xmark" aria-hidden="true"></i> Tolak
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($canSetManualZoom): ?>
                                                    <button type="button" data-zoom-action="manual" data-id="<?= esc($meeting['id'], 'attr') ?>"
                                                            data-current="<?= esc($zoomLink, 'attr') ?>" role="menuitem">
                                                        <i class="fas fa-link" aria-hidden="true"></i> Link manual...
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" data-delete-form="<?= esc($meeting['id'], 'attr') ?>" role="menuitem" class="danger">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                        <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" id="deleteForm-<?= esc($meeting['id']) ?>" style="display:none;">
                                            <?= csrf_field() ?>
                                        </form>
                                        <?php if ($meeting['status'] === 'pending'): ?>
                                            <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" id="statusForm-<?= esc($meeting['id']) ?>" style="display:none;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="">
                                            </form>
                                        <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= view('meeting/pagination', ['pager' => $pager, 'group' => 'default', 'label' => 'Halaman riwayat meeting']) ?>
</div>

<div class="modal fade" id="manualZoomModal" tabindex="-1" aria-hidden="true" aria-labelledby="manualZoomTitle">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="manualZoomModalForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="manualZoomTitle">Set link Zoom manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="field">
                        <label class="field-label" for="manualZoomUrl">URL Zoom</label>
                        <div class="zoom-link-row">
                            <input class="input" type="url" id="manualZoomUrl" name="zoom_join_url" placeholder="https://us02web.zoom.us/j/..." aria-describedby="manualZoomHelp" required>
                            <button type="submit" class="btn btn-primary">Simpan Link</button>
                        </div>
                        <span class="field-help" id="manualZoomHelp">Saat pertama kali disimpan, link dikirim otomatis ke WhatsApp pegawai.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
    // Copy Zoom link
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;
        var url = btn.getAttribute('data-copy') || '';
        if (!url) return;
        navigator.clipboard.writeText(url).then(function() {
            window.showToast && window.showToast('Link Zoom disalin.', 'success');
        }, function() {
            window.showToast && window.showToast('Gagal menyalin link.', 'error');
        });
    });

    // Status / delete kebab actions
    document.addEventListener('click', function(e) {
        var statusBtn = e.target.closest('[data-status-form]');
        if (statusBtn) {
            var id = statusBtn.getAttribute('data-status-form');
            var status = statusBtn.getAttribute('data-status');
            var form = document.getElementById('statusForm-' + id);
            if (!form) return;
            form.querySelector('input[name="status"]').value = status;
            if (confirm(status === 'approved' ? 'Setujui meeting ini?' : 'Tolak meeting ini?')) {
                form.submit();
            }
            return;
        }
        var manualBtn = e.target.closest('[data-zoom-action="manual"]');
        if (manualBtn) {
            var modalEl = document.getElementById('manualZoomModal');
            var form = document.getElementById('manualZoomModalForm');
            var input = document.getElementById('manualZoomUrl');
            if (!modalEl || !form || !input) return;
            form.action = '<?= base_url('meeting/manual-zoom/') ?>' + manualBtn.getAttribute('data-id');
            input.value = manualBtn.getAttribute('data-current') || '';
            if (window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            return;
        }
        var delBtn = e.target.closest('[data-delete-form]');
        if (delBtn) {
            var did = delBtn.getAttribute('data-delete-form');
            var dform = document.getElementById('deleteForm-' + did);
            if (!dform) return;
            if (confirm('Hapus meeting ini? Tindakan tidak dapat dibatalkan.')) {
                dform.submit();
            }
        }
    });

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        var closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) closeBtn.addEventListener('click', function() {
            toast.classList.add('toast-hiding'); setTimeout(function() { toast.remove(); }, 250);
        });
        setTimeout(function() {
            toast.classList.add('toast-hiding'); setTimeout(function() { toast.remove(); }, 250);
        }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
