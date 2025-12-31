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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-medium text-gray-900" id="modalTitle">Buat Meeting</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?= base_url('meeting/create') ?>" method="POST" id="createMeetingForm">
                <div class="modal-body p-6">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="nama_keg" class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                            <div class="mt-1">
                                <input type="text" 
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
                                       id="nama_keg" 
                                       name="nama_keg" 
                                       placeholder="Contoh: Rapat Koordinasi Bulanan"
                                       required>
                            </div>
                        </div>

                        
                        <div class="sm:col-span-3">
                            <label for="ruangan_id" class="block text-sm font-medium text-gray-700">Ruangan</label>
                            <div class="mt-1">
                                <select class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
                                        id="ruangan_id" 
                                        name="ruangan_id" 
                                        required>
                                    <?php foreach ($ruangan as $r): ?>
                                        <option value="<?= $r['id'] ?>" data-tipe="<?= $r['tipe'] ?>">
                                            <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-3" id="participants_container">
                            <label for="jumlah_peserta" class="block text-sm font-medium text-gray-700">Jumlah Peserta</label>
                            <div class="mt-1">
                                <input type="number" 
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
                                       id="jumlah_peserta" 
                                       name="jumlah_peserta" 
                                       min="1"
                                       required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai</label>
                            <div class="mt-1">
                                <input type="datetime-local" 
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
                                       id="waktu_mulai" 
                                       name="waktu_mulai" 
                                       step="900"
                                       required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="durasi" class="block text-sm font-medium text-gray-700">Durasi</label>
                            <div class="mt-1">
                                <select class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
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
                        </div>

                        <div class="sm:col-span-6" id="fasilitas_container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fasilitas Rapat</label>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                <?php 
                                $fasilitas_options = [
                                    'Microphone', 'Dokumentasi', 'Zoom (Hybrid)', 
                                    'Snack', 'Makanan Berat', 'Air Minum'
                                ];
                                foreach ($fasilitas_options as $f): 
                                ?>
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="fasilitas_<?= url_title($f, '-', true) ?>" 
                                               name="fasilitas[]" 
                                               value="<?= $f ?>" 
                                               type="checkbox" 
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="fasilitas_<?= url_title($f, '-', true) ?>" class="font-medium text-gray-700"><?= $f ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="relative flex items-start col-span-2 sm:col-span-3">
                                    <div class="flex items-center h-5">
                                        <input id="fasilitas_lainnya_checkbox" 
                                               name="fasilitas[]" 
                                               value="Lainnya" 
                                               type="checkbox" 
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm flex-grow">
                                        <label for="fasilitas_lainnya_checkbox" class="font-medium text-gray-700">Lainnya</label>
                                        <input type="text" 
                                               id="fasilitas_lainnya_text" 
                                               name="fasilitas_lainnya" 
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-1 px-2 hidden" 
                                               placeholder="Sebutkan fasilitas lainnya...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="waktu_selesai" name="waktu_selesai">
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                            id="submitBtn"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span id="submitText">Simpan</span>
                        <span id="submitSpinner" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
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
                        <label class="block text-sm font-medium text-gray-700">Jumlah Peserta</label>
                        <p id="eventPeserta" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fasilitas</label>
                        <p id="eventFasilitas" class="mt-1 text-sm text-gray-900"></p>
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
    // Generate unique form token
    function generateFormToken() {
        return 'token_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Add hidden token field to form
    function addTokenToForm() {
        const form = document.getElementById('createMeetingForm');
        if (form) {
            // Remove existing token if any
            const existingToken = form.querySelector('input[name="form_token"]');
            if (existingToken) {
                existingToken.remove();
            }
            
            // Add new token
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'form_token';
            tokenInput.value = generateFormToken();
            form.appendChild(tokenInput);
        }
    }
    
    // Handle form submission to prevent double submit
    function handleFormSubmit() {
        const form = document.getElementById('createMeetingForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                // Add token to form before submission
                addTokenToForm();
                
                // Disable submit button and show loading
                submitBtn.disabled = true;
                submitText.textContent = 'Menyimpan...';
                submitSpinner.classList.remove('hidden');
                
                // Re-enable after 5 seconds as a fallback
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitText.textContent = 'Simpan';
                    submitSpinner.classList.add('hidden');
                }, 5000);
            });
        }
    }
    
    // Reset form when modal is hidden
    function resetFormOnModalClose() {
        const modal = document.getElementById('createMeetingModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('createMeetingForm');
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitSpinner = document.getElementById('submitSpinner');
                
                if (form) {
                    form.reset();
                    // Reset submit button
                    submitBtn.disabled = false;
                    submitText.textContent = 'Simpan';
                    submitSpinner.classList.add('hidden');
                }
            });
        }
    }
    
    // Initialize form protection
    handleFormSubmit();
    resetFormOnModalClose();
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
                'pegawai' => $meeting['nama_pegawai'] ?? null,
                'jumlah_peserta' => $meeting['jumlah_peserta'] ?? null,
                'fasilitas' => $meeting['fasilitas'] ? implode(', ', json_decode($meeting['fasilitas'], true) ?? []) : '-',
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
            document.getElementById('eventPeserta').textContent = props.jumlah_peserta || 'Tidak tersedia';
            document.getElementById('eventFasilitas').textContent = props.fasilitas || 'Tidak tersedia';
            
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

    // Handle "Lainnya" checkbox
    const fasilitasLainnyaCheckbox = document.getElementById('fasilitas_lainnya_checkbox');
    const fasilitasLainnyaText = document.getElementById('fasilitas_lainnya_text');

    if (fasilitasLainnyaCheckbox && fasilitasLainnyaText) {
        fasilitasLainnyaCheckbox.addEventListener('change', function() {
            if (this.checked) {
                fasilitasLainnyaText.classList.remove('hidden');
                fasilitasLainnyaText.required = true;
                fasilitasLainnyaText.focus();
            } else {
                fasilitasLainnyaText.classList.add('hidden');
                fasilitasLainnyaText.required = false;
                fasilitasLainnyaText.value = '';
            }
        });
    }

    // Handle Room Type Change for Facilities Visibility
    const ruanganSelect = document.getElementById('ruangan_id');
    const fasilitasContainer = document.getElementById('fasilitas_container');

    function toggleFasilitas() {
        const selectedOption = ruanganSelect.options[ruanganSelect.selectedIndex];
        const tipe = selectedOption ? selectedOption.getAttribute('data-tipe') : '';
        const participantsContainer = document.getElementById('participants_container');
        const participantsInput = document.getElementById('jumlah_peserta');
        
        if (tipe === 'Online') {
            fasilitasContainer.classList.add('hidden');
            // Optional: Uncheck all facilities if hidden
            const checkboxes = fasilitasContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
            if (fasilitasLainnyaText) {
                fasilitasLainnyaText.classList.add('hidden');
                fasilitasLainnyaText.value = '';
            }

            // Hide participants container and remove required
            if (participantsContainer) {
                participantsContainer.classList.add('hidden');
            }
            if (participantsInput) {
                participantsInput.required = false;
                participantsInput.value = '';
            }
        } else {
            fasilitasContainer.classList.remove('hidden');
            
            // Show participants container and add required
            if (participantsContainer) {
                participantsContainer.classList.remove('hidden');
            }
            if (participantsInput) {
                participantsInput.required = true;
            }
        }
    }

    if (ruanganSelect && fasilitasContainer) {
        ruanganSelect.addEventListener('change', toggleFasilitas);
        // Initial check
        toggleFasilitas();
    }
});
</script>
<?= $this->endSection() ?>
