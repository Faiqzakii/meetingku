<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<?php
    $loggedIn = (bool) session()->get('logged_in');
    $isAdmin  = isset($isAdmin) ? (bool) $isAdmin : (bool) session()->get('is_admin');
    $pegawaiId = (int) (session()->get('pegawai_id') ?? 0);

    $renderRow = function (array $meeting) use ($loggedIn, $isAdmin, $pegawaiId) {
        $canOwnOrAdmin = $loggedIn && (
            ($meeting['pegawai_id'] !== null && (int) $meeting['pegawai_id'] === $pegawaiId)
            || $isAdmin
        );
        $canApprove = $loggedIn && $isAdmin && $meeting['status'] === 'pending';
        $canManage  = $canOwnOrAdmin || $canApprove;

        $isOnlineOrHybrid = in_array($meeting['tipe'], ['Online', 'Hybrid'], true);
        $hasJoinUrl       = !empty($meeting['zoom_join_url']);
        $hasHostMeeting   = !empty($meeting['zoom_meeting_id']);
        $hasStartToken    = !empty($meeting['start_token']);
        $meetingNotEnded  = time() < strtotime($meeting['waktu_selesai']);
        $withinOneHour    = time() >= (strtotime($meeting['waktu_mulai']) - 3600);
        $isToday          = date('Y-m-d', strtotime($meeting['waktu_mulai'])) === date('Y-m-d');

        ob_start(); ?>
        <tr data-ruangan-id="<?= esc($meeting['ruangan_id'], 'attr') ?>">
            <td class="cell-truncate" style="max-width:280px;" title="<?= esc($meeting['nama_keg'], 'attr') ?>">
                <span style="font-weight:600;"><?= esc($meeting['nama_keg']) ?></span>
            </td>
            <td class="cell-truncate" style="max-width:180px;" title="<?= esc($meeting['nama_ruangan'] . ' (' . $meeting['tipe'] . ')', 'attr') ?>">
                <i class="fas fa-door-open" aria-hidden="true" style="color:var(--mute);font-size:.8em;"></i>
                <?= esc($meeting['nama_ruangan']) ?>
                <span style="color:var(--mute);">(<?= esc($meeting['tipe']) ?>)</span>
            </td>
            <td style="white-space:nowrap;">
                <?= $isToday
                    ? date('H:i', strtotime($meeting['waktu_mulai'])) . ' – ' . date('H:i', strtotime($meeting['waktu_selesai']))
                    : date('d M Y H:i', strtotime($meeting['waktu_mulai'])) . ' – ' . date('H:i', strtotime($meeting['waktu_selesai']))
                ?>
            </td>
            <td>
                <span class="badge-status badge-<?= esc($meeting['status']) ?>">
                    <?= esc(ucfirst($meeting['status'])) ?>
                </span>
            </td>
            <td>
                <?php if (!empty($meeting['zoom_join_url'])): ?>
                    <button type="button" class="btn btn-ghost btn-sm" data-copy="<?= esc($meeting['zoom_join_url'], 'attr') ?>">
                        <i class="fas fa-copy" aria-hidden="true"></i> Copy
                    </button>
                <?php else: ?>
                    <span style="color:var(--mute);font-size:.8rem;">—</span>
                <?php endif; ?>
            </td>
            <?php if ($loggedIn): ?>
                <td style="text-align:right;white-space:nowrap;">
                    <?php if ($isOnlineOrHybrid && $meeting['status'] === 'approved' && $canOwnOrAdmin && $hasJoinUrl): ?>
                        <a href="<?= esc($meeting['zoom_join_url'], 'attr') ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" style="color:var(--info);border-color:#bae6fd;">
                            <i class="fas fa-video" aria-hidden="true"></i> Join
                        </a>
                    <?php endif; ?>
                    <?php if ($canManage): ?>
                        <div class="kebab" data-kebab>
                            <button type="button" class="kebab-btn" aria-haspopup="menu" aria-expanded="false" aria-label="Aksi lain">
                                <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                            </button>
                            <div class="kebab-menu" role="menu">
                                <?php if ($canOwnOrAdmin): ?>
                                    <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" role="menuitem">
                                        <i class="fas fa-pen" aria-hidden="true"></i> Edit
                                    </a>
                                <?php endif; ?>

                                <?php if ($canApprove): ?>
                                    <button type="button" data-status-form="<?= esc($meeting['id'], 'attr') ?>" data-status="approved" role="menuitem">
                                        <i class="fas fa-check" aria-hidden="true"></i> Setujui
                                    </button>
                                    <button type="button" data-status-form="<?= esc($meeting['id'], 'attr') ?>" data-status="rejected" role="menuitem">
                                        <i class="fas fa-xmark" aria-hidden="true"></i> Tolak
                                    </button>
                                <?php endif; ?>

                                <?php if ($isOnlineOrHybrid && $meeting['status'] === 'approved' && $canOwnOrAdmin): ?>
                                    <?php if ($hasJoinUrl && $hasHostMeeting && $meetingNotEnded && $withinOneHour): ?>
                                        <button type="button" data-zoom-action="refresh" data-id="<?= esc($meeting['id'], 'attr') ?>" role="menuitem">
                                            <i class="fas fa-play-circle" aria-hidden="true"></i> Refresh Host
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($hasStartToken): ?>
                                        <button type="button" data-copy-host-link="<?= esc(base_url('zoom/start/' . $meeting['start_token']), 'attr') ?>" role="menuitem">
                                            <i class="fas fa-share-alt" aria-hidden="true"></i> Copy Host Link
                                        </button>
                                    <?php endif; ?>
                                    <?php if (!$hasJoinUrl && $isAdmin && !$hasHostMeeting): ?>
                                        <button type="button" data-zoom-action="send" data-id="<?= esc($meeting['id'], 'attr') ?>" role="menuitem">
                                            <i class="fas fa-paper-plane" aria-hidden="true"></i> Buat &amp; kirim Zoom
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($isAdmin && $meetingNotEnded): ?>
                                        <button type="button" data-zoom-action="manual" data-id="<?= esc($meeting['id'], 'attr') ?>"
                                                data-current="<?= esc($meeting['zoom_join_url'] ?? '', 'attr') ?>" role="menuitem">
                                            <i class="fas fa-link" aria-hidden="true"></i> Link manual…
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($canOwnOrAdmin): ?>
                                    <button type="button" data-delete-form="<?= esc($meeting['id'], 'attr') ?>" role="menuitem" class="danger">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" id="deleteForm-<?= esc($meeting['id']) ?>" style="display:none;">
                            <?= csrf_field() ?>
                        </form>
                        <?php if ($canApprove): ?>
                            <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" id="statusForm-<?= esc($meeting['id']) ?>" style="display:none;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="">
                            </form>
                        <?php endif; ?>
                        <?php if ($isOnlineOrHybrid && $meeting['status'] === 'approved' && $canOwnOrAdmin): ?>
                            <form action="<?= base_url('meeting/refresh-zoom/' . $meeting['id']) ?>" method="POST" id="zoomRefresh-<?= esc($meeting['id']) ?>" target="_blank" style="display:none;">
                                <?= csrf_field() ?>
                            </form>
                            <form action="<?= base_url('meeting/send-zoom/' . $meeting['id']) ?>" method="POST" id="zoomSend-<?= esc($meeting['id']) ?>" style="display:none;">
                                <?= csrf_field() ?>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
        </tr>
        <?php
        return ob_get_clean();
    };
?>

<div data-logged-in="<?= $loggedIn ? 'true' : 'false' ?>"
     data-is-admin="<?= $isAdmin ? 'true' : 'false' ?>"
     data-pegawai-id="<?= esc($pegawaiId, 'attr') ?>">
    <script>var baseUrl = '<?= rtrim(base_url(), '/') ?>';</script>

    <div class="page-header">
        <div>
            <h2 class="section-title">
                <i class="fas fa-clock" aria-hidden="true"></i>
                Meeting saya
            </h2>
            <p class="page-subtitle">Hari ini &amp; mendatang. Filter cepat per ruangan.</p>
        </div>
        <div class="page-actions">
            <div class="field" style="min-width:200px;">
                <label class="field-label sr-only" for="ruanganFilter" style="position:absolute;left:-9999px;">Filter ruangan</label>
                <select class="input" id="ruanganFilter">
                    <option value="">Semua ruangan</option>
                    <?php foreach ($ruangan as $r): ?>
                        <option value="<?= esc($r['id'], 'attr') ?>"><?= esc($r['nama_ruangan']) ?> (<?= esc($r['tipe']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <section class="card" style="margin-bottom:20px;">
        <div class="card-section" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <h3 style="font-size:1rem;font-weight:700;display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-sun" style="color:var(--warning);"></i> Hari ini
                <span class="badge-status badge-primary"><?= count($today_meetings) ?></span>
            </h3>
        </div>
        <div class="card-divider"></div>
        <div style="overflow-x:auto;">
            <table class="table-modern" id="todayTable">
                <thead>
                    <tr>
                        <th>Nama Kegiatan</th>
                        <th>Ruangan</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Zoom</th>
                        <?php if ($loggedIn): ?><th style="width:120px;text-align:right;">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($today_meetings)): ?>
                        <tr><td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-sun" aria-hidden="true"></i></div>
                                <p class="empty-state-title">Tidak ada pertemuan hari ini</p>
                                <p class="empty-state-desc">Nikmati hari tenang.</p>
                            </div>
                        </td></tr>
                    <?php else: foreach ($today_meetings as $m) echo $renderRow($m); endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <div class="card-section" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <h3 style="font-size:1rem;font-weight:700;display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-calendar-day" style="color:var(--primary);"></i> Mendatang
                <span class="badge-status badge-primary"><?= count($upcoming_meetings) ?></span>
            </h3>
        </div>
        <div class="card-divider"></div>
        <div style="overflow-x:auto;">
            <table class="table-modern" id="upcomingTable">
                <thead>
                    <tr>
                        <th>Nama Kegiatan</th>
                        <th>Ruangan</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Zoom</th>
                        <?php if ($loggedIn): ?><th style="width:120px;text-align:right;">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upcoming_meetings)): ?>
                        <tr><td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></div>
                                <p class="empty-state-title">Tidak ada pertemuan mendatang</p>
                                <p class="empty-state-desc">Belum ada jadwal yang dijadwalkan.</p>
                            </div>
                        </td></tr>
                    <?php else: foreach ($upcoming_meetings as $m) echo $renderRow($m); endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Manual Zoom modal -->
<div class="modal fade" id="manualZoomModal" tabindex="-1" aria-hidden="true" aria-labelledby="manualZoomTitle">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="manualZoomForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="manualZoomTitle">Set link Zoom manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="field">
                        <label class="field-label" for="manualZoomUrl">URL Zoom</label>
                        <input class="input" type="url" id="manualZoomUrl" name="zoom_join_url" placeholder="https://us02web.zoom.us/j/..." required>
                        <span class="field-help">Tempel link join Zoom yang sudah dibuat manual.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan link</button>
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
    // Ruangan filter (toggle visibility)
    var filter = document.getElementById('ruanganFilter');
    if (filter) {
        filter.addEventListener('change', function() {
            var v = filter.value;
            ['todayTable', 'upcomingTable'].forEach(function(id) {
                document.querySelectorAll('#' + id + ' tbody tr').forEach(function(tr) {
                    if (!v) { tr.style.display = ''; return; }
                    tr.style.display = (tr.getAttribute('data-ruangan-id') === v) ? '' : 'none';
                });
            });
        });
    }

    // Copy zoom link
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

    // Copy host shortlink
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-copy-host-link]');
        if (!btn) return;
        var url = btn.getAttribute('data-copy-host-link') || '';
        if (!url) return;
        navigator.clipboard.writeText(url).then(function() {
            window.showToast && window.showToast('Link Host disalin. Link hanya aktif H-1 jam.', 'success');
        }, function() {
            window.showToast && window.showToast('Gagal menyalin link.', 'error');
        });
    });

    // Status / delete actions
    document.addEventListener('click', function(e) {
        var statusBtn = e.target.closest('[data-status-form]');
        if (statusBtn) {
            var id = statusBtn.getAttribute('data-status-form');
            var status = statusBtn.getAttribute('data-status');
            var form = document.getElementById('statusForm-' + id);
            if (!form) return;
            form.querySelector('input[name="status"]').value = status;
            if (confirm(status === 'approved' ? 'Setujui meeting ini?' : 'Tolak meeting ini?')) form.submit();
            return;
        }
        var del = e.target.closest('[data-delete-form]');
        if (del) {
            var did = del.getAttribute('data-delete-form');
            var dform = document.getElementById('deleteForm-' + did);
            if (!dform) return;
            if (confirm('Hapus meeting ini? Tidak dapat dibatalkan.')) dform.submit();
            return;
        }
        var zoomBtn = e.target.closest('[data-zoom-action]');
        if (zoomBtn) {
            var action = zoomBtn.getAttribute('data-zoom-action');
            var zid = zoomBtn.getAttribute('data-id');
            if (action === 'refresh') {
                document.getElementById('zoomRefresh-' + zid).submit();
            } else if (action === 'send') {
                if (confirm('Buat Zoom meeting dan kirim link ke pegawai?')) {
                    document.getElementById('zoomSend-' + zid).submit();
                }
            } else if (action === 'manual') {
                var modalEl = document.getElementById('manualZoomModal');
                if (!modalEl) return;
                var form = document.getElementById('manualZoomForm');
                form.action = baseUrl + '/meeting/manual-zoom/' + zid;
                document.getElementById('manualZoomUrl').value = zoomBtn.getAttribute('data-current') || '';
                if (window.bootstrap) {
                    var m = bootstrap.Modal.getOrCreateInstance(modalEl); m.show();
                }
            }
        }
    });

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        var c = toast.querySelector('.toast-close');
        if (c) c.addEventListener('click', function() {
            toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250);
        });
        setTimeout(function() {
            toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 250);
        }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
