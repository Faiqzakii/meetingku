<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div data-logged-in="<?= session()->get('logged_in') ? 'true' : 'false' ?>"
     data-pegawai-id="<?= session()->get('pegawai_id') ?? '' ?>"
     data-is-admin="<?= isset($isAdmin) && $isAdmin ? 'true' : 'false' ?>">
<script>
    var baseUrl = '<?= rtrim(base_url(), '/') ?>';
</script>
<div class="space-y-8">
    <!-- Today's Meetings -->
    <div>
        <div class="sm:flex sm:items-center sm:justify-between mt-2 mb-5">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-sun mr-2 text-yellow-500"></i>Pertemuan Hari Ini
                </h2>
                <p class="text-sm text-gray-500 mt-1">Jadwal meeting untuk hari ini</p>
            </div>
            <div class="mt-3 sm:mt-0">
                <select id="ruanganFilter" class="input-modern dropdown-modern" style="width:auto; min-width:180px;">
                    <option value="">📍 Semua Ruangan</option>
                    <?php foreach ($ruangan as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card-modern">
            <div class="overflow-x-auto">
                <table id="todayTable" class="min-w-full table-modern">
                    <thead>
                        <tr>
                            <th class="text-left">Nama Kegiatan</th>
                            <th class="text-left">Ruangan</th>
                            <th class="text-left">Waktu</th>
                            <th class="text-left">Status</th>
                            <?php if (session()->get('logged_in')): ?>
                                <th class="text-right" style="width:120px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($today_meetings)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-sun"></i>
                                        </div>
                                        <p class="empty-state-title">Tidak ada pertemuan hari ini</p>
                                        <p class="empty-state-desc">Nikmati hari yang tenang! 🎉</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($today_meetings as $meeting): ?>
                                <?php 
                                    $canOwnOrAdmin = false; 
                                    $canApprove = false; 
                                    if (session()->get('logged_in')) {
                                        $canOwnOrAdmin = ($meeting['pegawai_id'] !== null && (int)$meeting['pegawai_id'] === (int)session()->get('pegawai_id')) || $isAdmin;
                                        $canApprove = $isAdmin && $meeting['status'] === 'pending';
                                    }
                                ?>
                                <tr>
                                    <td class="font-semibold text-gray-900" style="max-width:250px; word-wrap:break-word; white-space:normal;">
                                        <?= esc($meeting['nama_keg']) ?>
                                    </td>
                                    <td class="text-gray-600" data-ruangan-id="<?= $meeting['ruangan_id'] ?>">
                                        <i class="fas fa-door-open text-orange-300 mr-1 text-xs"></i>
                                        <?= esc($meeting['nama_ruangan']) ?> <span class="text-gray-400">(<?= $meeting['tipe'] ?>)</span>
                                    </td>
                                    <td class="text-gray-600">
                                        <i class="fas fa-clock text-orange-300 mr-1 text-xs"></i>
                                        <?= date('H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                                        <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= $meeting['status'] ?>">
                                            <?= strtoupper($meeting['status']) ?>
                                        </span>
                                    </td>
                                    <?php if (session()->get('logged_in')): ?>
                                        <td class="text-right">
                                            <?php if ($canOwnOrAdmin || $canApprove): ?>
                                                <button type="button" class="expand-trigger btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($canOwnOrAdmin || $canApprove): ?>
                                <tr class="details-row hidden">
                                    <td colspan="5" style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                                        <?php if ($isAdmin): ?>
                                        <div class="px-2 pt-2 pb-1">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5"><i class="fas fa-history mr-1"></i>Log Aktivitas</p>
                                            <div class="space-y-1 mb-2">
                                                <div class="flex items-start gap-2 text-xs text-gray-500">
                                                    <i class="fas fa-plus-circle text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                                    <span><strong>Di-input</strong> oleh <strong><?= esc($meeting['nama_pegawai'] ?? '-') ?></strong> pada <?= $meeting['created_at'] ? date('d M Y H:i', strtotime($meeting['created_at'])) : '-' ?></span>
                                                </div>
                                                <?php if (!empty($meeting['last_edited_by_name']) && !empty($meeting['last_edited_at'])): ?>
                                                <div class="flex items-start gap-2 text-xs text-gray-500">
                                                    <i class="fas fa-pen text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                                    <span><strong>Diedit</strong> oleh <strong><?= esc($meeting['last_edited_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['last_edited_at'])) ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($meeting['status_changed_by_name']) && !empty($meeting['status_changed_at'])): ?>
                                                <div class="flex items-start gap-2 text-xs text-gray-500">
                                                    <i class="fas fa-gavel text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                                    <?php 
                                                        $statusLabel = $meeting['status'] === 'approved' ? 'Disetujui' : ($meeting['status'] === 'rejected' ? 'Ditolak' : 'Status diubah');
                                                    ?>
                                                    <span><strong><?= $statusLabel ?></strong> oleh <strong><?= esc($meeting['status_changed_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['status_changed_at'])) ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2 justify-end py-1 flex-wrap">
                                            <?php
                                                $isOnlineOrHybrid = in_array($meeting['tipe'] ?? '', ['Online', 'Hybrid']);
                                                $hasJoinUrl = !empty($meeting['zoom_join_url']);
                                                $hasHostMeeting = !empty($meeting['zoom_meeting_id']);
                                                $meetingNotEnded = time() < strtotime($meeting['waktu_selesai']);
                                            ?>
                                            <?php if ($isOnlineOrHybrid && $meeting['status'] === 'approved' && $canOwnOrAdmin): ?>
                                                <?php if ($hasJoinUrl): ?>
                                                    <a href="<?= esc($meeting['zoom_join_url']) ?>" target="_blank" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe;">
                                                        <i class="fas fa-video"></i> Join Zoom
                                                    </a>
                                                    <?php $withinOneHour = time() >= (strtotime($meeting['waktu_mulai']) - 3600); ?>
                                                    <?php if ($hasHostMeeting && $meetingNotEnded && $withinOneHour): ?>
                                                        <form action="<?= base_url('meeting/refresh-zoom/' . $meeting['id']) ?>" method="POST" class="inline-block" target="_blank">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#7c3aed; border-color:#ddd6fe;" title="Buka sebagai Host (generate link baru)">
                                                                <i class="fas fa-play-circle"></i> Host
                                                            </button>
                                                        </form>
                                                    <?php elseif ($hasHostMeeting && $meetingNotEnded): ?>
                                                        <span class="text-xs text-gray-400 italic" title="Link Host tersedia 1 jam sebelum meeting">
                                                            <i class="fas fa-lock text-gray-300"></i> Host (H-1 jam)
                                                        </span>
                                                    <?php endif; ?>
                                                <?php elseif ($isAdmin && !$hasHostMeeting): ?>
                                                    <form action="<?= base_url('meeting/send-zoom/' . $meeting['id']) ?>" method="POST" class="inline-block" onsubmit="return confirm('Buat Zoom meeting dan kirim link ke pegawai?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe; font-weight:600;">
                                                            🚀 Kirim Zoom
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($isAdmin && $isOnlineOrHybrid && $meeting['status'] === 'approved' && $meetingNotEnded): ?>
                                                <form action="<?= base_url('meeting/manual-zoom/' . $meeting['id']) ?>" method="POST" class="inline-flex items-center gap-1" style="padding:0; margin:0;">
                                                    <?= csrf_field() ?>
                                                    <input type="url" name="zoom_join_url" value="<?= esc($meeting['zoom_join_url'] ?? '') ?>" placeholder="https://us02web.zoom.us/j/..." class="input-modern" style="height:30px; font-size:0.75rem; min-width:220px; padding:0.25rem 0.5rem;" required>
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#0f766e; border-color:#99f6e4;">
                                                        <i class="fas fa-link"></i> Simpan Link
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canOwnOrAdmin): ?>
                                                <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                                        <i class="fas fa-trash-alt"></i> Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canApprove): ?>
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#059669; border-color:#a7f3d0;">
                                                        <i class="fas fa-check"></i> Setuju
                                                    </button>
                                                </form>

                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Meetings -->
    <div class="pt-4">
        <div class="sm:flex sm:items-center sm:justify-between mb-5">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-calendar-day mr-2 text-orange-500"></i>Pertemuan Mendatang
                </h2>
                <p class="text-sm text-gray-500 mt-1">Jadwal yang akan datang</p>
            </div>
        </div>

        <div class="card-modern">
            <div class="overflow-x-auto">
                <table id="upcomingTable" class="min-w-full table-modern">
                    <thead>
                        <tr>
                            <th class="text-left">Nama Kegiatan</th>
                            <th class="text-left">Ruangan</th>
                            <th class="text-left">Waktu</th>
                            <th class="text-left">Status</th>
                            <?php if (session()->get('logged_in')): ?>
                                <th class="text-right" style="width:120px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($upcoming_meetings)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <p class="empty-state-title">Tidak ada pertemuan mendatang</p>
                                        <p class="empty-state-desc">Belum ada jadwal yang dijadwalkan</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($upcoming_meetings as $meeting): ?>
                                <?php 
                                    $canOwnOrAdmin = false; 
                                    $canApprove = false; 
                                    if (session()->get('logged_in')) {
                                        $canOwnOrAdmin = ((int)$meeting['pegawai_id'] === (int)session()->get('pegawai_id')) || $isAdmin;
                                        $canApprove = $isAdmin && $meeting['status'] === 'pending';
                                    }
                                ?>
                                <tr>
                                    <td class="font-semibold text-gray-900">
                                        <?= esc($meeting['nama_keg']) ?>
                                    </td>
                                    <td class="text-gray-600" data-ruangan-id="<?= $meeting['ruangan_id'] ?>">
                                        <i class="fas fa-door-open text-orange-300 mr-1 text-xs"></i>
                                        <?= esc($meeting['nama_ruangan']) ?> <span class="text-gray-400">(<?= $meeting['tipe'] ?>)</span>
                                    </td>
                                    <td class="text-gray-600">
                                        <i class="fas fa-clock text-orange-300 mr-1 text-xs"></i>
                                        <?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                                        <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= $meeting['status'] ?>">
                                            <?= strtoupper($meeting['status']) ?>
                                        </span>
                                    </td>
                                    <?php if (session()->get('logged_in')): ?>
                                        <td class="text-right">
                                            <?php if ($canOwnOrAdmin || $canApprove): ?>
                                                <button type="button" class="expand-trigger btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($canOwnOrAdmin || $canApprove): ?>
                                <tr class="details-row hidden">
                                    <td colspan="5" style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                                        <?php if ($isAdmin): ?>
                                        <div class="px-2 pt-2 pb-1">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5"><i class="fas fa-history mr-1"></i>Log Aktivitas</p>
                                            <div class="space-y-1 mb-2">
                                                <div class="flex items-start gap-2 text-xs text-gray-500">
                                                    <i class="fas fa-plus-circle text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                                    <span><strong>Di-input</strong> oleh <strong><?= esc($meeting['nama_pegawai'] ?? '-') ?></strong> pada <?= $meeting['created_at'] ? date('d M Y H:i', strtotime($meeting['created_at'])) : '-' ?></span>
                                                </div>
                                                <?php if (!empty($meeting['last_edited_by_name']) && !empty($meeting['last_edited_at'])): ?>
                                                <div class="flex items-start gap-2 text-xs text-gray-500">
                                                    <i class="fas fa-pen text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                                    <span><strong>Diedit</strong> oleh <strong><?= esc($meeting['last_edited_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['last_edited_at'])) ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($meeting['status_changed_by_name']) && !empty($meeting['status_changed_at'])): ?>
                                                <div class="flex items-start gap-2 text-xs text-gray-500">
                                                    <i class="fas fa-gavel text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                                    <?php 
                                                        $statusLabel = $meeting['status'] === 'approved' ? 'Disetujui' : ($meeting['status'] === 'rejected' ? 'Ditolak' : 'Status diubah');
                                                    ?>
                                                    <span><strong><?= $statusLabel ?></strong> oleh <strong><?= esc($meeting['status_changed_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['status_changed_at'])) ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2 justify-end py-1 flex-wrap">
                                            <?php
                                                $isOnlineOrHybrid = in_array($meeting['tipe'] ?? '', ['Online', 'Hybrid']);
                                                $hasJoinUrl = !empty($meeting['zoom_join_url']);
                                                $hasHostMeeting = !empty($meeting['zoom_meeting_id']);
                                                $meetingNotEnded = time() < strtotime($meeting['waktu_selesai']);
                                            ?>
                                            <?php if ($isOnlineOrHybrid && $meeting['status'] === 'approved' && $canOwnOrAdmin): ?>
                                                <?php if ($hasJoinUrl): ?>
                                                    <a href="<?= esc($meeting['zoom_join_url']) ?>" target="_blank" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe;">
                                                        <i class="fas fa-video"></i> Join Zoom
                                                    </a>
                                                    <?php $withinOneHour = time() >= (strtotime($meeting['waktu_mulai']) - 3600); ?>
                                                    <?php if ($hasHostMeeting && $meetingNotEnded && $withinOneHour): ?>
                                                        <form action="<?= base_url('meeting/refresh-zoom/' . $meeting['id']) ?>" method="POST" class="inline-block" target="_blank">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#7c3aed; border-color:#ddd6fe;" title="Buka sebagai Host (generate link baru)">
                                                                <i class="fas fa-play-circle"></i> Host
                                                            </button>
                                                        </form>
                                                    <?php elseif ($hasHostMeeting && $meetingNotEnded): ?>
                                                        <span class="text-xs text-gray-400 italic" title="Link Host tersedia 1 jam sebelum meeting">
                                                            <i class="fas fa-lock text-gray-300"></i> Host (H-1 jam)
                                                        </span>
                                                    <?php endif; ?>
                                                <?php elseif ($isAdmin && !$hasHostMeeting): ?>
                                                    <form action="<?= base_url('meeting/send-zoom/' . $meeting['id']) ?>" method="POST" class="inline-block" onsubmit="return confirm('Buat Zoom meeting dan kirim link ke pegawai?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe; font-weight:600;">
                                                            🚀 Kirim Zoom
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($isAdmin && $isOnlineOrHybrid && $meeting['status'] === 'approved' && $meetingNotEnded): ?>
                                                <form action="<?= base_url('meeting/manual-zoom/' . $meeting['id']) ?>" method="POST" class="inline-flex items-center gap-1" style="padding:0; margin:0;">
                                                    <?= csrf_field() ?>
                                                    <input type="url" name="zoom_join_url" value="<?= esc($meeting['zoom_join_url'] ?? '') ?>" placeholder="https://us02web.zoom.us/j/..." class="input-modern" style="height:30px; font-size:0.75rem; min-width:220px; padding:0.25rem 0.5rem;" required>
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#0f766e; border-color:#99f6e4;">
                                                        <i class="fas fa-link"></i> Simpan Link
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canOwnOrAdmin): ?>
                                                <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                                        <i class="fas fa-trash-alt"></i> Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canApprove): ?>
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#059669; border-color:#a7f3d0;">
                                                        <i class="fas fa-check"></i> Setuju
                                                    </button>
                                                </form>

                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Toast Flash Messages -->
<div class="toast-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="toast-notification toast-success" id="flashToast">
            <div class="toast-icon"><i class="fas fa-check"></i></div>
            <span><?= session()->getFlashdata('success') ?></span>
            <button class="toast-close" onclick="this.parentElement.classList.add('toast-hiding');setTimeout(()=>this.parentElement.remove(),300)"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="toast-notification toast-error" id="flashToast">
            <div class="toast-icon"><i class="fas fa-exclamation"></i></div>
            <span><?= session()->getFlashdata('error') ?></span>
            <button class="toast-close" onclick="this.parentElement.classList.add('toast-hiding');setTimeout(()=>this.parentElement.remove(),300)"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function escHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escAttr(value) {
        return escHtml(value).replace(/`/g, '&#96;');
    }

    function safeHttpUrl(url) {
        if (!url) {
            return '';
        }

        try {
            var parsed = new URL(String(url), window.location.origin);
            if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
                return parsed.href;
            }
        } catch (e) {
            return '';
        }

        return '';
    }

    function updateEndTime() {
        var startTime = document.getElementById('waktu_mulai');
        var durasi = document.getElementById('durasi');
        if (!startTime || !durasi) return;
        var duration = parseInt(durasi.value);
        if (startTime.value) {
            var endTime = new Date(startTime.value);
            endTime.setMinutes(endTime.getMinutes() + duration);
            document.getElementById('waktu_selesai').value = endTime.toISOString().slice(0, 16);
        }
    }

    var todayMeetings = <?= json_encode(array_map(static function ($meeting) {
        $meeting['waktu_mulai_ts'] = isset($meeting['waktu_mulai']) ? strtotime($meeting['waktu_mulai']) : null;
        $meeting['waktu_selesai_ts'] = isset($meeting['waktu_selesai']) ? strtotime($meeting['waktu_selesai']) : null;
        return $meeting;
    }, $today_meetings)) ?>;
    var upcomingMeetings = <?= json_encode(array_map(static function ($meeting) {
        $meeting['waktu_mulai_ts'] = isset($meeting['waktu_mulai']) ? strtotime($meeting['waktu_mulai']) : null;
        $meeting['waktu_selesai_ts'] = isset($meeting['waktu_selesai']) ? strtotime($meeting['waktu_selesai']) : null;
        return $meeting;
    }, $upcoming_meetings)) ?>;
    
    function generateMeetingRowExpandable(meeting) {
        var badgeClass = meeting.status === 'pending' ? 'badge-pending' :
                          (meeting.status === 'approved' ? 'badge-approved' : 'badge-rejected');

        var isLoggedIn = !!document.querySelector('[data-logged-in="true"]');
        var isAdmin    = !!document.querySelector('[data-is-admin="true"]');
        var pegEl      = document.querySelector('[data-pegawai-id]');
        var currentId  = pegEl ? Number(pegEl.dataset.pegawaiId) : 0;
        var canOwnOrAdmin = isLoggedIn && ((meeting.pegawai_id != null && Number(meeting.pegawai_id) === currentId) || isAdmin);
        var canApprove    = isLoggedIn && isAdmin && meeting.status === 'pending';
        var safeNamaKeg = escHtml(meeting.nama_keg);
        var safeNamaRuangan = escHtml(meeting.nama_ruangan);
        var safeTipe = escHtml(meeting.tipe);
        var safeNamaPegawai = escHtml(meeting.nama_pegawai || '-');
        var safeLastEditedBy = escHtml(meeting.last_edited_by_name || '');
        var safeStatusChangedBy = escHtml(meeting.status_changed_by_name || '');
        var safeStatusLabel = escHtml(meeting.status === 'approved' ? 'Disetujui' : (meeting.status === 'rejected' ? 'Ditolak' : 'Status diubah'));
        var safeStatusText = escHtml(String(meeting.status || '').toUpperCase());
        var joinUrl = safeHttpUrl(meeting.zoom_join_url);
        var joinValue = escAttr(meeting.zoom_join_url || '');

        var html = `
            <tr>
                <td class="font-semibold text-gray-900" style="max-width:250px; word-wrap:break-word; white-space:normal;">
                    ${safeNamaKeg}
                </td>
                <td class="text-gray-600" data-ruangan-id="${meeting.ruangan_id}">
                    <i class="fas fa-door-open text-orange-300 mr-1 text-xs"></i>
                    ${safeNamaRuangan} <span class="text-gray-400">(${safeTipe})</span>
                </td>
                <td class="text-gray-600">
                    <i class="fas fa-clock text-orange-300 mr-1 text-xs"></i>
                    ${meeting.is_today ?
                        moment(meeting.waktu_mulai).format('HH:mm') + ' - ' + moment(meeting.waktu_selesai).format('HH:mm') :
                        moment(meeting.waktu_mulai).format('DD MMM YYYY HH:mm') + ' - ' + moment(meeting.waktu_selesai).format('HH:mm')
                    }
                </td>
                <td>
                    <span class="badge-status ${badgeClass}">
                        ${safeStatusText}
                    </span>
                </td>`;

        if (isLoggedIn) {
            if (canOwnOrAdmin || canApprove) {
                html += `
                    <td class="text-right">
                        <button type="button" class="expand-trigger btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </td>`;
            } else {
                html += `<td></td>`;
            }
        }

        html += `</tr>`;

        if (isLoggedIn && (canOwnOrAdmin || canApprove)) {
            var csrfEl = document.querySelector('[name="csrf_test_name"]');
            var csrf   = csrfEl ? csrfEl.value : '';
            var auditHtml = '';
            if (isAdmin) {
                auditHtml = `
                    <div class="px-2 pt-2 pb-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5"><i class="fas fa-history mr-1"></i>Log Aktivitas</p>
                        <div class="space-y-1 mb-2">
                            <div class="flex items-start gap-2 text-xs text-gray-500">
                                <i class="fas fa-plus-circle text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                <span><strong>Di-input</strong> oleh <strong>${safeNamaPegawai}</strong> pada ${meeting.created_at ? moment(meeting.created_at).format('DD MMM YYYY HH:mm') : '-'}</span>
                            </div>
                            ${meeting.last_edited_by_name && meeting.last_edited_at ? `
                            <div class="flex items-start gap-2 text-xs text-gray-500">
                                <i class="fas fa-pen text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                <span><strong>Diedit</strong> oleh <strong>${safeLastEditedBy}</strong> pada ${moment(meeting.last_edited_at).format('DD MMM YYYY HH:mm')}</span>
                            </div>` : ''}
                            ${meeting.status_changed_by_name && meeting.status_changed_at ? `
                            <div class="flex items-start gap-2 text-xs text-gray-500">
                                <i class="fas fa-gavel text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                <span><strong>${safeStatusLabel}</strong> oleh <strong>${safeStatusChangedBy}</strong> pada ${moment(meeting.status_changed_at).format('DD MMM YYYY HH:mm')}</span>
                            </div>` : ''}
                        </div>
                    </div>`;
            }
            html += `
            <tr class="details-row hidden">
                <td colspan="5" style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                    ${auditHtml}
                    <div class="flex items-center gap-2 justify-end py-1 flex-wrap">
                        ${(function(){
                            var isOnlineOrHybrid = meeting.tipe === 'Online' || meeting.tipe === 'Hybrid';
                            var hasJoinUrl = meeting.zoom_join_url && meeting.zoom_join_url !== '';
                            var hasHostMeeting = meeting.zoom_meeting_id && meeting.zoom_meeting_id !== '';
                            var meetingEnd = Number(meeting.waktu_selesai_ts) * 1000;
                            var meetingNotEnded = Date.now() < meetingEnd;
                            var zoomHtml = '';
                            if (isOnlineOrHybrid && meeting.status === 'approved' && canOwnOrAdmin) {
                                if (hasJoinUrl) {
                                    zoomHtml += '<a href="' + joinUrl + '" target="_blank" rel="noopener noreferrer" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe;"><i class="fas fa-video"></i> Join Zoom</a>';
                                    var meetStart = Number(meeting.waktu_mulai_ts) * 1000;
                                    var nowMs = Date.now();
                                    if (hasHostMeeting && meetingNotEnded && nowMs >= (meetStart - 3600000)) {
                                        zoomHtml += '<form action="' + baseUrl + '/meeting/refresh-zoom/' + meeting.id + '" method="POST" class="inline-block" target="_blank"><input type="hidden" name="csrf_test_name" value="' + csrf + '"><button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#7c3aed; border-color:#ddd6fe;" title="Buka sebagai Host (generate link baru)"><i class="fas fa-play-circle"></i> Host</button></form>';
                                    } else if (hasHostMeeting && meetingNotEnded) {
                                        zoomHtml += '<span class="text-xs text-gray-400 italic" title="Link Host tersedia 1 jam sebelum meeting"><i class="fas fa-lock text-gray-300"></i> Host (H-1 jam)</span>';
                                    }
                                } else if (isAdmin && !hasHostMeeting) {
                                    zoomHtml += '<form action="' + baseUrl + '/meeting/send-zoom/' + meeting.id + '" method="POST" class="inline-block" onsubmit="return confirm(\'Buat Zoom meeting dan kirim link ke pegawai?\');"><input type="hidden" name="csrf_test_name" value="' + csrf + '"><button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe; font-weight:600;">\ud83d\ude80 Kirim Zoom</button></form>';
                                }
                            }
                            if (isAdmin && isOnlineOrHybrid && meeting.status === 'approved' && meetingNotEnded) {
                                zoomHtml += '<form action="' + baseUrl + '/meeting/manual-zoom/' + meeting.id + '" method="POST" class="inline-flex items-center gap-1" style="padding:0; margin:0;"><input type="hidden" name="csrf_test_name" value="' + csrf + '"><input type="url" name="zoom_join_url" value="' + joinValue + '" placeholder="https://us02web.zoom.us/j/..." class="input-modern" style="height:30px; font-size:0.75rem; min-width:220px; padding:0.25rem 0.5rem;" required><button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#0f766e; border-color:#99f6e4;"><i class="fas fa-link"></i> Simpan Link</button></form>';
                            }
                            return zoomHtml;
                        })()}
                        ${canOwnOrAdmin ? `
                        <a href="${baseUrl}/meeting/edit/${meeting.id}" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="${baseUrl}/meeting/delete/${meeting.id}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');">
                            <input type="hidden" name="csrf_test_name" value="${csrf}">
                            <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </form>` : ''}
                        ${canApprove ? `
                        <form action="${baseUrl}/meeting/status/${meeting.id}" method="POST" class="inline-block">
                            <input type="hidden" name="csrf_test_name" value="${csrf}">
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#059669; border-color:#a7f3d0;">
                                <i class="fas fa-check"></i> Setuju
                            </button>
                        </form>` : ''}
                    </div>
                </td>
            </tr>`;
        }

        return html;
    }

    function updateTable(meetings, tableBody, isToday) {
        if (meetings.length === 0) {
            var emptyIcon = isToday ? 'fa-sun' : 'fa-calendar-check';
            var emptyText = isToday ? 'Tidak ada pertemuan hari ini' : 'Tidak ada pertemuan mendatang';
            var emptyDesc = isToday ? 'Nikmati hari yang tenang! 🎉' : 'Belum ada jadwal yang dijadwalkan';
            tableBody.innerHTML = `
                <tr><td colspan="5">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas ${emptyIcon}"></i></div>
                        <p class="empty-state-title">${emptyText}</p>
                        <p class="empty-state-desc">${emptyDesc}</p>
                    </div>
                </td></tr>`;
            return;
        }
        tableBody.innerHTML = meetings.map(meeting => {
            meeting.is_today = isToday;
            return generateMeetingRowExpandable(meeting);
        }).join('');
    }

    const ruanganFilter = document.getElementById('ruanganFilter');
    if (ruanganFilter) {
        ruanganFilter.addEventListener('change', function(e) {
            var ruanganId = e.target.value;
            var filteredToday = ruanganId ? todayMeetings.filter(m => m.ruangan_id.toString() === ruanganId) : todayMeetings;
            var filteredUpcoming = ruanganId ? upcomingMeetings.filter(m => m.ruangan_id.toString() === ruanganId) : upcomingMeetings;
            const todayTableBody = document.querySelector('#todayTable tbody');
            const upcomingTableBody = document.querySelector('#upcomingTable tbody');
            if (todayTableBody && upcomingTableBody) {
                updateTable(filteredToday, todayTableBody, true);
                updateTable(filteredUpcoming, upcomingTableBody, false);
            }
        });
    }

    const waktuMulai = document.getElementById('waktu_mulai');
    const durasi = document.getElementById('durasi');
    if (waktuMulai && durasi) {
        waktuMulai.addEventListener('change', updateEndTime);
        durasi.addEventListener('change', updateEndTime);
    }

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(function() {
            toast.classList.add('toast-hiding');
            setTimeout(function(){ toast.remove(); }, 300);
        }, 4000);
    }

    if (ruanganFilter) {
        ruanganFilter.dispatchEvent(new Event('change'));
    }

    // Expandable row toggle
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.expand-trigger');
        if (!btn) return;
        var row = btn.closest('tr');
        if (!row) return;
        var details = row.nextElementSibling;
        if (!details || !details.classList.contains('details-row')) return;
        document.querySelectorAll('.details-row').forEach(function(dr){ if (dr !== details && !dr.classList.contains('hidden')) dr.classList.add('hidden'); });
        details.classList.toggle('hidden');
    });
});
</script>
<?= $this->endSection() ?>
