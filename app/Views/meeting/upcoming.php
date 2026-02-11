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
                                        <div class="flex items-center gap-2 justify-end py-1">
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
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                                        <i class="fas fa-times"></i> Tolak
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
                                        <div class="flex items-center gap-2 justify-end py-1">
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
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                                        <i class="fas fa-times"></i> Tolak
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

    var todayMeetings = <?= json_encode($today_meetings) ?>;
    var upcomingMeetings = <?= json_encode($upcoming_meetings) ?>;
    
    function generateMeetingRowExpandable(meeting) {
        var badgeClass = meeting.status === 'pending' ? 'badge-pending' :
                          (meeting.status === 'approved' ? 'badge-approved' : 'badge-rejected');

        var isLoggedIn = !!document.querySelector('[data-logged-in="true"]');
        var isAdmin    = !!document.querySelector('[data-is-admin="true"]');
        var pegEl      = document.querySelector('[data-pegawai-id]');
        var currentId  = pegEl ? Number(pegEl.dataset.pegawaiId) : 0;
        var canOwnOrAdmin = isLoggedIn && ((meeting.pegawai_id != null && Number(meeting.pegawai_id) === currentId) || isAdmin);
        var canApprove    = isLoggedIn && isAdmin && meeting.status === 'pending';

        var html = `
            <tr>
                <td class="font-semibold text-gray-900" style="max-width:250px; word-wrap:break-word; white-space:normal;">
                    ${meeting.nama_keg}
                </td>
                <td class="text-gray-600" data-ruangan-id="${meeting.ruangan_id}">
                    <i class="fas fa-door-open text-orange-300 mr-1 text-xs"></i>
                    ${meeting.nama_ruangan} <span class="text-gray-400">(${meeting.tipe})</span>
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
                        ${meeting.status.toUpperCase()}
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
            html += `
            <tr class="details-row hidden">
                <td colspan="5" style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                    <div class="flex items-center gap-2 justify-end py-1">
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
                        </form>
                        <form action="${baseUrl}/meeting/status/${meeting.id}" method="POST" class="inline-block">
                            <input type="hidden" name="csrf_test_name" value="${csrf}">
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                <i class="fas fa-times"></i> Tolak
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
