<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mt-4 mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Jadwal Pertemuan</h2>
    </div>
    <div class="sm:flex sm:items-center gap-4">
        <select id="ruanganFilter" class="block w-48 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Semua Ruangan</option>
            <?php foreach ($ruangan as $r): ?>
                <option value="<?= $r['id'] ?>">
                    <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (session()->get('logged_in')): ?>
            <button type="button" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    data-bs-toggle="modal" 
                    data-bs-target="#createMeetingModal">
                <div class="flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span>Buat Meeting</span>
                </div>
            </button>
        <?php else: ?>
            <a href="<?= base_url('auth/login') ?>" 
               class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <div class="flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span>Buat Meeting</span>
                </div>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
    <div id="calendar"></div>
</div>

<!-- Create Meeting Modal -->
<div class="modal fade" id="createMeetingModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-medium text-gray-900" id="modalTitle">Buat Meeting</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?= base_url('meeting/create') ?>" method="POST">
                <div class="modal-body p-6">
                    <?= csrf_field() ?>
                    <div class="space-y-4">
                        <div>
                            <label for="nama_keg" class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                            <input type="text" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="nama_keg" 
                                   name="nama_keg" 
                                   required>
                        </div>
                        <div>
                            <label for="ruangan_id" class="block text-sm font-medium text-gray-700">Ruangan</label>
                            <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                    id="ruangan_id" 
                                    name="ruangan_id" 
                                    required>
                                <?php foreach ($ruangan as $r): ?>
                                    <option value="<?= $r['id'] ?>">
                                        <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai</label>
                            <input type="datetime-local" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="waktu_mulai" 
                                   name="waktu_mulai" 
                                   step="900"
                                   required>
                        </div>
                        <div>
                            <label for="durasi" class="block text-sm font-medium text-gray-700">Durasi</label>
                            <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                    id="durasi" 
                                    name="durasi" 
                                    required>
                                <option value="60" >1 jam</option>
                                <option value="120">2 jam</option>
                                <option value="180">3 jam</option>
                                <option value="240">4 jam</option>
                                <option value="Penuh">Satu Hari Penuh</option>
                            </select>
                        </div>
                        <input type="hidden" id="waktu_selesai" name="waktu_selesai">
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-medium text-gray-900" id="eventDetailsModalTitle">Detail Meeting</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                        <p id="eventTitle" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ruangan</label>
                        <p id="eventRoom" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Waktu</label>
                        <p id="eventTime" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pemohon</label>
                        <p id="eventPegawai" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p id="eventStatus" class="mt-1"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 px-6 py-3">
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                        data-bs-dismiss="modal">Tutup</button>
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

    // Add event listeners
    document.getElementById('waktu_mulai').addEventListener('change', updateEndTime);
    document.getElementById('durasi').addEventListener('change', updateEndTime);

    // Store all events
    var allEvents = <?= json_encode(array_map(function($meeting) {
        return [
            'id' => $meeting['id'],
            'title' => $meeting['nama_keg'],
            'start' => $meeting['waktu_mulai'],
            'end' => $meeting['waktu_selesai'],
            'backgroundColor' => $meeting['status'] === 'approved' ? '#10B981' : 
                               ($meeting['status'] === 'pending' ? '#F59E0B' : '#EF4444'),
            'borderColor' => $meeting['status'] === 'approved' ? '#059669' : 
                            ($meeting['status'] === 'pending' ? '#D97706' : '#DC2626'),
            'extendedProps' => [
                'ruangan_id' => $meeting['ruangan_id'],
                'ruangan' => $meeting['nama_ruangan'],
                'tipe' => $meeting['tipe'],
                'status' => $meeting['status'],
                'pegawai' => $meeting['nama_pegawai'] ?? null
            ]
        ];
    }, $meetings)) ?>;

    // Filter events based on selected ruangan
    function filterEvents(ruanganId) {
        if (!ruanganId) {
            return allEvents;
        }
        return allEvents.filter(function(event) {
            return event.extendedProps.ruangan_id == ruanganId;
        });
    }

    // Initialize calendar
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotDuration: '00:15:00',
        slotMinTime: '07:00:00',
        slotMaxTime: '18:00:00',
        events: allEvents,
        eventClick: function(info) {
            var event = info.event;
            var props = event.extendedProps;
            
            // Set modal content
            document.getElementById('eventTitle').textContent = event.title;
            document.getElementById('eventRoom').textContent = props.ruangan + ' (' + props.tipe + ')';
            document.getElementById('eventTime').textContent = moment(event.start).format('DD MMM YYYY HH:mm') + ' - ' + 
                                                             moment(event.end).format('HH:mm');
            document.getElementById('eventPegawai').textContent = props.pegawai || 'Tidak tersedia';
            
            // Set status with appropriate styling
            var statusElement = document.getElementById('eventStatus');
            var statusClass = props.status === 'approved' ? 'bg-green-100 text-green-800' : 
                            (props.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
            statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + 
                                    statusClass + '">' + props.status.toUpperCase() + '</span>';
            
            // Show modal using Bootstrap
            var eventModal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            eventModal.show();
        }
    });
    calendar.render();

    // Handle ruangan filter change
    document.getElementById('ruanganFilter').addEventListener('change', function(e) {
        var ruanganId = e.target.value;
        calendar.removeAllEvents();
        calendar.addEventSource(filterEvents(ruanganId));
    });

    // Auto-hide flash messages
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(function() {
            flashMessage.style.display = 'none';
        }, 3000);
    }
});
</script>
<?= $this->endSection() ?>
