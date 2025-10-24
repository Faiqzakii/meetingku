<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<!-- Add data attributes for JavaScript -->
<div data-logged-in="<?= session()->get('logged_in') ? 'true' : 'false' ?>"
     data-pegawai-id="<?= session()->get('pegawai_id') ?? '' ?>"
     data-is-admin="<?= isset($isAdmin) && $isAdmin ? 'true' : 'false' ?>">
<script>
    var baseUrl = '<?= rtrim(base_url(), '/') ?>';
</script>
<div class="space-y-8">
    <!-- Today's Meetings -->
    <div>
        <div class="sm:flex sm:items-center sm:justify-between mt-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Pertemuan Hari Ini</h2>
            <div class="sm:mt-0">
                <select id="ruanganFilter" class="block w-48 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Semua Ruangan</option>
                    <?php foreach ($ruangan as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="todayTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <?php if (session()->get('logged_in')): ?>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($today_meetings)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada pertemuan hari ini
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($today_meetings as $meeting): ?>
                                <tr class="hover:bg-gray-50">
                                    <?php 
                                        $canOwnOrAdmin = false; 
                                        $canApprove = false; 
                                        if (session()->get('logged_in')) {
                                            $canOwnOrAdmin = ((int)$meeting['pegawai_id'] === (int)session()->get('pegawai_id')) || $isAdmin;
                                            $canApprove = $isAdmin && $meeting['status'] === 'pending';
                                        }
                                    ?>
                                    <td class="px-6 py-4 whitespace-normal break-words text-sm font-medium text-gray-900">
                                        <?= esc($meeting['nama_keg']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-ruangan-id="<?= $meeting['ruangan_id'] ?>">
                                        <?= esc($meeting['nama_ruangan']) ?> (<?= $meeting['tipe'] ?>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                                        <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?= $meeting['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($meeting['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= strtoupper($meeting['status']) ?>
                                        </span>
                                    </td>
                                    <?php if (session()->get('logged_in')): ?> 
                                        <?php 
                                            $canOwnOrAdmin = ((int)$meeting['pegawai_id'] === (int)session()->get('pegawai_id')) || $isAdmin; 
                                            $canApprove = $isAdmin && $meeting['status'] === 'pending';
                                        ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                            <?php if ($canOwnOrAdmin || $canApprove): ?>
                                                <button type="button" class="expand-trigger inline-flex gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 justify-end">
                                                    Aksi
                                                    <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($canOwnOrAdmin || $canApprove): ?>
                                <tr class="details-row hidden">
                                    <td colspan="5" class="px-6 py-4 whitespace-normal break-words text-sm">
                                        <div class="flex items-center space-x-4 justify-end">
                                            <?php if ($canOwnOrAdmin): ?>
                                                <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                    <i class="fas fa-edit mr-2"></i> Edit
                                                </a>
                                                <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                        <i class="fas fa-trash-alt mr-2"></i> Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canApprove): ?>
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                        <i class="fas fa-check mr-2"></i> Setuju
                                                    </button>
                                                </form>
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                        <i class="fas fa-times mr-2"></i> Tolak
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
    <div class="pt-8">
        <div class="sm:flex sm:items-center sm:justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Pertemuan Mendatang</h2>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="upcomingTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <?php if (session()->get('logged_in')): ?>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($upcoming_meetings)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada pertemuan mendatang
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($upcoming_meetings as $meeting): ?>
                                <tr class="hover:bg-gray-50">
                                    <?php 
                                        $canOwnOrAdmin = false; 
                                        $canApprove = false; 
                                        if (session()->get('logged_in')) {
                                            $canOwnOrAdmin = ((int)$meeting['pegawai_id'] === (int)session()->get('pegawai_id')) || $isAdmin;
                                            $canApprove = $isAdmin && $meeting['status'] === 'pending';
                                        }
                                    ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= esc($meeting['nama_keg']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-ruangan-id="<?= $meeting['ruangan_id'] ?>">
                                        <?= esc($meeting['nama_ruangan']) ?> (<?= $meeting['tipe'] ?>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                                        <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?= $meeting['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($meeting['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= strtoupper($meeting['status']) ?>
                                        </span>
                                    </td>
                                    <?php if (session()->get('logged_in')): ?> 
                                        <?php 
                                            $canOwnOrAdmin = ((int)$meeting['pegawai_id'] === (int)session()->get('pegawai_id')) || $isAdmin; 
                                            $canApprove = $isAdmin && $meeting['status'] === 'pending';
                                        ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                            <?php if ($canOwnOrAdmin || $canApprove): ?>
                                                <button type="button" class="expand-trigger inline-flex gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 justify-end">
                                                    Aksi
                                                    <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($canOwnOrAdmin || $canApprove): ?>
                                <tr class="details-row hidden">
                                    <td colspan="5" class="px-6 py-4 whitespace-normal break-words text-sm">
                                        <div class="flex items-center space-x-4 justify-end">
                                            <?php if ($canOwnOrAdmin): ?>
                                                <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                    <i class="fas fa-edit mr-2"></i> Edit
                                                </a>
                                                <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                        <i class="fas fa-trash-alt mr-2"></i> Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canApprove): ?>
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                        <i class="fas fa-check mr-2"></i> Setuju
                                                    </button>
                                                </form>
                                                <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                        <i class="fas fa-times mr-2"></i> Tolak
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="fixed bottom-4 right-4 px-4 py-2 bg-green-500 text-white rounded shadow-lg" id="flashMessage">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="fixed bottom-4 right-4 px-4 py-2 bg-red-500 text-white rounded shadow-lg" id="flashMessage">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle duration change to auto-compute end time
    function updateEndTime() {
        var startTime = document.getElementById('waktu_mulai').value;
        var duration = parseInt(document.getElementById('durasi').value);
        
        if (startTime) {
            var endTime = new Date(startTime);
            endTime.setMinutes(endTime.getMinutes() + duration);
            document.getElementById('waktu_selesai').value = endTime.toISOString().slice(0, 16);
        }
    }

    // Store all meetings data
    var todayMeetings = <?= json_encode($today_meetings) ?>;
    var upcomingMeetings = <?= json_encode($upcoming_meetings) ?>;
    
    // Function to generate meeting row HTML
    function generateMeetingRow(meeting) {
        var statusClass = meeting.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                         (meeting.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
        
        var html = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${meeting.nama_keg}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-ruangan-id="${meeting.ruangan_id}">
                    ${meeting.nama_ruangan} (${meeting.tipe})
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${meeting.is_today ? 
                        moment(meeting.waktu_mulai).format('HH:mm') + ' - ' + moment(meeting.waktu_selesai).format('HH:mm') :
                        moment(meeting.waktu_mulai).format('DD MMM YYYY HH:mm') + ' - ' + moment(meeting.waktu_selesai).format('HH:mm')
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${meeting.status.toUpperCase()}
                    </span>
                </td>`;

        if (document.querySelector('[data-logged-in="true"]')) {
            html += `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="relative inline-block text-left">
                        <button type="button" class="action-trigger inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Aksi
                            <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="action-menu hidden fixed z-50 w-40 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="py-1">
                                ${(
                                    Number(meeting.pegawai_id) === Number(document.querySelector('[data-pegawai-id]').dataset.pegawaiId)
                                    || document.querySelector('[data-is-admin="true"]')
                                ) ? `
                                    <a href="${baseUrl}/meeting/edit/${meeting.id}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </a>
                                    <form action="${baseUrl}/meeting/delete/${meeting.id}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');">
                                        <input type="hidden" name="csrf_test_name" value="${document.querySelector('[name="csrf_test_name"]').value}">
                                        <button type="submit" class="w-full text-left text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50">
                                            <i class="fas fa-trash-alt mr-2"></i> Hapus
                                        </button>
                                    </form>
                                ` : ''}
                                ${(document.querySelector('[data-is-admin="true"]') && meeting.status === 'pending') ? `
                                    <form action="${baseUrl}/meeting/status/${meeting.id}" method="POST">
                                        <input type="hidden" name="csrf_test_name" value="${document.querySelector('[name="csrf_test_name"]').value}">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="w-full text-left text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50">
                                            <i class="fas fa-check mr-2"></i> Setuju
                                        </button>
                                    </form>
                                    <form action="${baseUrl}/meeting/status/${meeting.id}" method="POST">
                                        <input type="hidden" name="csrf_test_name" value="${document.querySelector('[name="csrf_test_name"]').value}">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="w-full text-left text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50">
                                            <i class="fas fa-times mr-2"></i> Tolak
                                        </button>
                                    </form>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </td>`;
        }

        html += '</tr>';
        return html;
    }

    // Function to update table content
    function updateTable(meetings, tableBody, isToday) {
        if (meetings.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        ${isToday ? 'Tidak ada pertemuan hari ini' : 'Tidak ada pertemuan mendatang'}
                    </td>
                </tr>`;
            return;
        }

        tableBody.innerHTML = meetings.map(meeting => {
            meeting.is_today = isToday;
            return generateMeetingRowExpandable(meeting);
        }).join('');
    }

    // Initialize filter functionality
    const ruanganFilter = document.getElementById('ruanganFilter');
    if (ruanganFilter) {
        ruanganFilter.addEventListener('change', function(e) {
            console.log('Filter changed to:', e.target.value);
        var ruanganId = e.target.value;
        
        // Filter meetings
        var filteredTodayMeetings = ruanganId ? 
            todayMeetings.filter(m => m.ruangan_id.toString() === ruanganId) : 
            todayMeetings;
            
        var filteredUpcomingMeetings = ruanganId ? 
            upcomingMeetings.filter(m => m.ruangan_id.toString() === ruanganId) : 
            upcomingMeetings;

        // Update tables
        const todayTableBody = document.querySelector('#todayTable tbody');
        const upcomingTableBody = document.querySelector('#upcomingTable tbody');
        
        if (todayTableBody && upcomingTableBody) {
            updateTable(filteredTodayMeetings, todayTableBody, true);
            updateTable(filteredUpcomingMeetings, upcomingTableBody, false);
        } else {
            console.error('Table bodies not found:', {
                todayTableBody: !!todayTableBody,
                upcomingTableBody: !!upcomingTableBody
            });
        }
        });
    }

    // Handle duration inputs if they exist (for create/edit forms)
    const waktuMulai = document.getElementById('waktu_mulai');
    const durasi = document.getElementById('durasi');
    if (waktuMulai && durasi) {
        waktuMulai.addEventListener('change', updateEndTime);
        durasi.addEventListener('change', updateEndTime);
    }

    // Auto-hide flash messages
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(function() {
            flashMessage.style.display = 'none';
        }, 3000);
    }

    // Trigger initial filter to populate tables
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
        // Close any other open details
        document.querySelectorAll('.details-row').forEach(function(dr){ if (!dr.classList.contains('hidden')) dr.classList.add('hidden'); });
        // Toggle this one
        details.classList.toggle('hidden');
    });
});

    // Function to generate meeting row HTML (expandable row actions)
    function generateMeetingRowExpandable(meeting) {
        var statusClass = meeting.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                          (meeting.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');

        var isLoggedIn = !!document.querySelector('[data-logged-in="true"]');
        var isAdmin    = !!document.querySelector('[data-is-admin="true"]');
        var pegEl      = document.querySelector('[data-pegawai-id]');
        var currentId  = pegEl ? Number(pegEl.dataset.pegawaiId) : 0;
        var canOwnOrAdmin = isLoggedIn && (Number(meeting.pegawai_id) === currentId || isAdmin);
        var canApprove    = isLoggedIn && isAdmin && meeting.status === 'pending';

        var html = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-normal break-words text-sm font-medium text-gray-900">
                    ${meeting.nama_keg}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-ruangan-id="${meeting.ruangan_id}">
                    ${meeting.nama_ruangan} (${meeting.tipe})
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${meeting.is_today ?
                        moment(meeting.waktu_mulai).format('HH:mm') + ' - ' + moment(meeting.waktu_selesai).format('HH:mm') :
                        moment(meeting.waktu_mulai).format('DD MMM YYYY HH:mm') + ' - ' + moment(meeting.waktu_selesai).format('HH:mm')
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${meeting.status.toUpperCase()}
                    </span>
                </td>`;

        if (isLoggedIn) {
            if (canOwnOrAdmin || canApprove) {
                html += `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                        <button type="button" class="expand-trigger inline-flex gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 justify-end">
                            Aksi
                            <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </td>`;
            } else {
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium"></td>`;
            }
        }

        html += `</tr>`;

        if (isLoggedIn && (canOwnOrAdmin || canApprove)) {
            var csrfEl = document.querySelector('[name="csrf_test_name"]');
            var csrf   = csrfEl ? csrfEl.value : '';
            html += `
            <tr class="details-row hidden">
                <td colspan="5" class="px-6 py-4 whitespace-normal break-words text-sm">
                    <div class="flex items-center space-x-4 justify-end">
                        ${canOwnOrAdmin ? `
                        <a href="${baseUrl}/meeting/edit/${meeting.id}" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>
                        <form action="${baseUrl}/meeting/delete/${meeting.id}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');">
                            <input type="hidden" name="csrf_test_name" value="${csrf}">
                            <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                <i class="fas fa-trash-alt mr-2"></i> Hapus
                            </button>
                        </form>` : ''}
                        ${canApprove ? `
                        <form action="${baseUrl}/meeting/status/${meeting.id}" method="POST" class="inline-block">
                            <input type="hidden" name="csrf_test_name" value="${csrf}">
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                <i class="fas fa-check mr-2"></i> Setuju
                            </button>
                        </form>
                        <form action="${baseUrl}/meeting/status/${meeting.id}" method="POST" class="inline-block">
                            <input type="hidden" name="csrf_test_name" value="${csrf}">
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                <i class="fas fa-times mr-2"></i> Tolak
                            </button>
                        </form>` : ''}
                    </div>
                </td>
            </tr>`;
        }

        return html;
    }</script>
<?= $this->endSection() ?>




