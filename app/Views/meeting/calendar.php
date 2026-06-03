<?= $this->extend('layout') ?>

<?= $this->section('styles') ?>
<style>
    .meeting-form-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px 16px;
    }
    .meeting-form-grid .col-full { grid-column: 1 / -1; }
    .meeting-form-grid .col-half { grid-column: span 3; }
    .meeting-form-grid .col-third { grid-column: span 2; }
    @media (max-width: 640px) {
        .meeting-form-grid { grid-template-columns: 1fr; }
        .meeting-form-grid .col-full,
        .meeting-form-grid .col-half,
        .meeting-form-grid .col-third { grid-column: 1 / -1; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            Jadwal Pertemuan
        </h2>
        <p class="page-subtitle">Lihat dan kelola jadwal meeting di satu tempat.</p>
    </div>
    <div class="page-actions" style="align-items:center;">
        <div class="field" style="min-width:200px;">
            <label class="field-label sr-only" for="ruanganFilter" style="position:absolute;left:-9999px;">Filter ruangan</label>
            <select id="ruanganFilter" class="input">
                <option value="">Semua ruangan</option>
                <?php foreach ($ruangan as $r): ?>
                    <option value="<?= esc($r['id'], 'attr') ?>"><?= esc($r['nama_ruangan']) ?> (<?= esc($r['tipe']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (session()->get('logged_in')): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                <i class="fas fa-plus" aria-hidden="true"></i>
                <span>Buat Meeting</span>
            </button>
        <?php else: ?>
            <a href="<?= base_url('auth/login') ?>" class="btn btn-primary">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Login
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Status legend -->
<div class="chip-row" style="margin-bottom:16px;align-items:center;" aria-label="Legenda status">
    <span style="font-size:.75rem;color:var(--mute);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-right:4px;">Status</span>
    <span class="badge-status badge-approved">Disetujui</span>
    <span class="badge-status badge-pending">Menunggu</span>
    <span class="badge-status badge-rejected">Ditolak</span>
</div>

<div class="card">
    <div class="card-section" style="padding:16px;">
        <div id="calendar"></div>
    </div>
</div>

<!-- Create Meeting Modal -->
<div class="modal fade" id="createMeetingModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Buat Meeting Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('meeting/create') ?>" method="POST" id="createMeetingForm">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="meeting-form-grid">
                        <div class="field col-full">
                            <label class="field-label" for="nama_keg">Nama kegiatan</label>
                            <input class="input" type="text" id="nama_keg" name="nama_keg"
                                   placeholder="Contoh: Rapat Koordinasi Bulanan" required>
                        </div>

                        <div class="field col-half" id="room_container">
                            <label class="field-label" for="ruangan_id">Ruangan</label>
                            <select class="input" id="ruangan_id" name="ruangan_id" required>
                                <?php foreach ($ruangan as $r): ?>
                                    <option value="<?= esc($r['id'], 'attr') ?>" data-tipe="<?= esc($r['tipe'], 'attr') ?>">
                                        <?= esc($r['nama_ruangan']) ?> (<?= esc($r['tipe']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field col-half" id="participants_container">
                            <label class="field-label" for="jumlah_peserta">Jumlah peserta</label>
                            <input class="input" type="number" id="jumlah_peserta" name="jumlah_peserta"
                                   min="1" placeholder="0" required>
                        </div>

                        <div class="field col-third">
                            <label class="field-label" for="tanggal_mulai">Tanggal</label>
                            <input class="input" type="date" id="tanggal_mulai" required>
                        </div>

                        <div class="field col-third">
                            <label class="field-label" for="jam_mulai">Jam mulai</label>
                            <select class="input" id="jam_mulai" required>
                                <?php
                                for ($h = 7; $h <= 17; $h++) {
                                    for ($m = 0; $m < 60; $m += 15) {
                                        if ($h == 17 && $m > 0) break;
                                        $val = sprintf('%02d:%02d', $h, $m);
                                        echo "<option value=\"$val\">$val</option>\n";
                                    }
                                }
                                ?>
                            </select>
                            <input type="hidden" id="waktu_mulai" name="waktu_mulai">
                        </div>

                        <div class="field col-third">
                            <label class="field-label" for="durasi">Durasi</label>
                            <select class="input" id="durasi" name="durasi" required>
                                <option value="60">1 jam</option>
                                <option value="120">2 jam</option>
                                <option value="180">3 jam</option>
                                <option value="240">4 jam</option>
                                <option value="Penuh">Satu Hari Penuh</option>
                            </select>
                        </div>

                        <div class="field col-full" id="fasilitas_container">
                            <label class="field-label">Fasilitas rapat</label>
                            <div class="check-grid">
                                <?php
                                $fasilitas_options = [
                                    ['icon' => 'fa-microphone',   'name' => 'Microphone'],
                                    ['icon' => 'fa-camera',       'name' => 'Dokumentasi'],
                                    ['icon' => 'fa-video',        'name' => 'Zoom (Hybrid)'],
                                    ['icon' => 'fa-cookie-bite',  'name' => 'Snack'],
                                    ['icon' => 'fa-utensils',     'name' => 'Makanan Berat'],
                                    ['icon' => 'fa-glass-water',  'name' => 'Air Minum'],
                                ];
                                foreach ($fasilitas_options as $f): ?>
                                    <label class="check-pill" for="fasilitas_<?= url_title($f['name'], '-', true) ?>">
                                        <input id="fasilitas_<?= url_title($f['name'], '-', true) ?>"
                                               name="fasilitas[]" value="<?= esc($f['name'], 'attr') ?>" type="checkbox">
                                        <i class="fas <?= esc($f['icon']) ?>" aria-hidden="true"></i>
                                        <span><?= esc($f['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                                <label class="check-pill check-pill-wide" for="fasilitas_lainnya_checkbox">
                                    <input id="fasilitas_lainnya_checkbox" name="fasilitas[]" value="Lainnya" type="checkbox">
                                    <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                                    <span>Lainnya</span>
                                </label>
                                <input type="text" id="fasilitas_lainnya_text" name="fasilitas_lainnya"
                                       class="input check-pill-wide hidden"
                                       placeholder="Sebutkan fasilitas lainnya...">
                            </div>
                        </div>

                        <input type="hidden" id="waktu_selesai" name="waktu_selesai">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <span id="submitText">Simpan</span>
                        <span id="submitSpinner" class="hidden" aria-hidden="true">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalTitle">
                    <i class="fas fa-info-circle mr-2 text-orange-500"></i>Detail Meeting
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-bookmark text-orange-400 mt-0.5"></i>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Kegiatan</label>
                            <p id="eventTitle" class="text-sm font-semibold text-gray-900 mt-0.5"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-door-open text-orange-400 mt-0.5"></i>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Ruangan</label>
                                <p id="eventRoom" class="text-sm text-gray-900 mt-0.5"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-users text-orange-400 mt-0.5"></i>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Peserta</label>
                                <p id="eventPeserta" class="text-sm text-gray-900 mt-0.5"></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-concierge-bell text-orange-400 mt-0.5"></i>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Fasilitas</label>
                            <p id="eventFasilitas" class="text-sm text-gray-900 mt-0.5"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-clock text-orange-400 mt-0.5"></i>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</label>
                                <p id="eventTime" class="text-sm text-gray-900 mt-0.5"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-user text-orange-400 mt-0.5"></i>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemohon</label>
                                <p id="eventPegawai" class="text-sm text-gray-900 mt-0.5"></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-flag text-orange-400"></i>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                            <p id="eventStatus" class="mt-0.5"></p>
                        </div>
                    </div>
                    <!-- Zoom Links Section -->
                    <div id="eventZoomSection" class="hidden border-t pt-3 mt-1" style="border-color:#e2e8f0;">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><i class="fas fa-video mr-1"></i>Zoom Meeting</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <a id="zoomJoinLink" href="#" target="_blank" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe;">
                                <i class="fas fa-video"></i> Join Zoom
                            </a>
                            <form id="zoomHostForm" method="POST" class="inline-block hidden" target="_blank">
                                <?= csrf_field() ?>
                                <button id="zoomHostButton" type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#7c3aed; border-color:#ddd6fe;" title="Buka sebagai Host">
                                    <i id="zoomHostIcon" class="fas fa-play-circle"></i> <span id="zoomHostLabel">Host</span>
                                </button>
                            </form>
                            <button type="button" id="zoomHostLinkCopy" class="btn-outline-custom hidden" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#334155; border-color:#cbd5e1;">
                                <i class="fas fa-copy"></i> Copy Host Link
                            </button>
                            <input id="zoomHostLinkInput" type="text" readonly class="sr-only" value="">
                        </div>
                    </div>
                    <!-- Audit trail (admin only) -->
                    <?php if (session()->get('is_admin')): ?>
                    <div id="eventAuditSection" class="hidden border-t pt-3 mt-1" style="border-color:#e2e8f0;">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><i class="fas fa-history mr-1"></i>Riwayat</p>
                        <div class="space-y-2">
                            <div id="auditStatusRow" class="hidden flex items-start gap-2 text-xs text-gray-500 bg-gray-50 rounded-md p-2">
                                <i class="fas fa-gavel text-orange-300 mt-0.5"></i>
                                <span id="auditStatusText"></span>
                            </div>
                            <div id="auditEditRow" class="hidden flex items-start gap-2 text-xs text-gray-500 bg-gray-50 rounded-md p-2">
                                <i class="fas fa-pen text-orange-300 mt-0.5"></i>
                                <span id="auditEditText"></span>
                            </div>
                            <div id="auditCreatedRow" class="hidden flex items-start gap-2 text-xs text-gray-500 bg-gray-50 rounded-md p-2">
                                <i class="fas fa-plus-circle text-orange-300 mt-0.5"></i>
                                <span id="auditCreatedText"></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer flex justify-between">
                <div id="eventAdminActions" class="hidden flex items-center gap-2 flex-wrap">
                    <form id="approveForm" method="POST" class="inline-block">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#059669; border-color:#a7f3d0;">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                    </form>

                    <form id="sendZoomForm" method="POST" class="inline-block hidden" onsubmit="return confirm('Buat Zoom meeting dan kirim link ke pegawai?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#2563eb; border-color:#bfdbfe; font-weight:600;">
                            🚀 Kirim Zoom
                        </button>
                    </form>
                    <form id="manualZoomForm" method="POST" class="hidden items-center gap-1 flex-nowrap">
                        <?= csrf_field() ?>
                        <input id="manualZoomInput" type="url" name="zoom_join_url" placeholder="https://us02web.zoom.us/j/..." class="input-modern" style="height:30px; font-size:0.75rem; min-width:220px; padding:0.25rem 0.5rem;" required>
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#0f766e; border-color:#99f6e4; white-space:nowrap;">
                            <i class="fas fa-link"></i> Simpan Link
                        </button>
                    </form>
                </div>
                
                <div id="eventOwnerActions" class="hidden flex items-center gap-2 flex-wrap ml-2">
                    <a id="editMeetingLink" href="#" class="btn-outline-custom hidden" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#334155; border-color:#cbd5e1;">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <form id="deleteForm" method="POST" class="inline-block hidden" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </form>
                </div>

                <div class="ml-auto">
                    <button type="button" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;" data-bs-dismiss="modal">Tutup</button>
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
            <button class="toast-close" onclick="this.parentElement.classList.add('toast-hiding');setTimeout(()=>this.parentElement.remove(),300)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="toast-notification toast-error" id="flashToast">
            <div class="toast-icon"><i class="fas fa-exclamation"></i></div>
            <span><?= session()->getFlashdata('error') ?></span>
            <button class="toast-close" onclick="this.parentElement.classList.add('toast-hiding');setTimeout(()=>this.parentElement.remove(),300)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Generate unique form token
    function generateFormToken() {
        return 'token_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Add hidden token field to form
    function addTokenToForm() {
        const form = document.getElementById('createMeetingForm');
        if (form) {
            const existingToken = form.querySelector('input[name="form_token"]');
            if (existingToken) existingToken.remove();
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
                combineDateTime();
                addTokenToForm();
                submitBtn.disabled = true;
                submitText.textContent = 'Menyimpan...';
                submitSpinner.classList.remove('hidden');
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
                    submitBtn.disabled = false;
                    submitText.textContent = 'Simpan';
                    submitSpinner.classList.add('hidden');
                }
            });
        }
    }
    
    handleFormSubmit();
    resetFormOnModalClose();

    // Combine date + time into hidden waktu_mulai
    function combineDateTime() {
        var tanggal = document.getElementById('tanggal_mulai').value;
        var jam = document.getElementById('jam_mulai').value;
        if (tanggal && jam) {
            document.getElementById('waktu_mulai').value = tanggal + 'T' + jam;
        }
    }

    // Handle duration change to auto-compute end time
    function updateEndTime() {
        combineDateTime();
        var startTime = document.getElementById('waktu_mulai').value;
        var durasi = document.getElementById('durasi').value;
        if (startTime && durasi !== 'Penuh') {
            var duration = parseInt(durasi);
            var endTime = new Date(startTime);
            endTime.setMinutes(endTime.getMinutes() + duration);
            document.getElementById('waktu_selesai').value = endTime.toISOString().slice(0, 16);
        } else if (startTime && durasi === 'Penuh') {
            var d = new Date(startTime);
            d.setHours(17, 0, 0, 0);
            document.getElementById('waktu_selesai').value = d.toISOString().slice(0, 16);
        }
    }

    document.getElementById('tanggal_mulai').addEventListener('change', updateEndTime);
    document.getElementById('jam_mulai').addEventListener('change', updateEndTime);
    document.getElementById('durasi').addEventListener('change', updateEndTime);

    // Store all events
    var allEvents = <?= json_encode(array_map(function($meeting) {
        return [
            'id' => $meeting['id'],
            'title' => $meeting['nama_keg'],
            'start' => $meeting['waktu_mulai'],
            'end' => $meeting['waktu_selesai'],
            'classNames' => ['fc-event-status-' . ($meeting['status'] ?? 'pending')],
            'extendedProps' => [
                'ruangan_id' => $meeting['ruangan_id'],
                'ruangan' => $meeting['nama_ruangan'],
                'tipe' => $meeting['tipe'],
                'status' => $meeting['status'],
                'waktu_mulai_ts' => isset($meeting['waktu_mulai']) ? strtotime($meeting['waktu_mulai']) : null,
                'waktu_selesai_ts' => isset($meeting['waktu_selesai']) ? strtotime($meeting['waktu_selesai']) : null,
                'pegawai' => $meeting['nama_pegawai'] ?? null,
                'pegawai_id' => $meeting['pegawai_id'] ?? null,
                'jumlah_peserta' => $meeting['jumlah_peserta'] ?? null,
                'fasilitas' => $meeting['fasilitas'] ? implode(', ', json_decode($meeting['fasilitas'], true) ?? []) : '-',
                'status_changed_by_name' => $meeting['status_changed_by_name'] ?? null,
                'status_changed_at' => $meeting['status_changed_at'] ?? null,
                'last_edited_by_name' => $meeting['last_edited_by_name'] ?? null,
                'last_edited_at' => $meeting['last_edited_at'] ?? null,
                'created_at' => $meeting['created_at'] ?? null,
                'zoom_meeting_id' => $meeting['zoom_meeting_id'] ?? null,
                'zoom_join_url' => $meeting['zoom_join_url'] ?? null,
                'zoom_start_url' => $meeting['zoom_start_url'] ?? null,
                'start_token' => $meeting['start_token'] ?? null,
            ]
        ];
    }, $meetings)) ?>;

    // Filter events based on selected ruangan
    function filterEvents(ruanganId) {
        if (!ruanganId) return allEvents;
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
        displayEventEnd: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        events: allEvents,
        eventClick: function(info) {
            var event = info.event;
            var props = event.extendedProps;
            
            document.getElementById('eventTitle').textContent = event.title;
            document.getElementById('eventRoom').textContent = props.ruangan + ' (' + props.tipe + ')';
            document.getElementById('eventTime').textContent = moment(event.start).format('DD MMM YYYY HH:mm') + ' - ' + 
                                                             moment(event.end).format('HH:mm');
            document.getElementById('eventPegawai').textContent = props.pegawai || 'Tidak tersedia';
            document.getElementById('eventPeserta').textContent = props.jumlah_peserta || 'Tidak tersedia';
            document.getElementById('eventFasilitas').textContent = props.fasilitas || 'Tidak tersedia';
            
            var statusElement = document.getElementById('eventStatus');
            var badgeClass = props.status === 'approved' ? 'badge-approved' : 
                            (props.status === 'pending' ? 'badge-pending' : 'badge-rejected');
            statusElement.textContent = '';
            var statusBadge = document.createElement('span');
            statusBadge.className = 'badge-status ' + badgeClass;
            statusBadge.textContent = String(props.status || '').toUpperCase();
            statusElement.appendChild(statusBadge);
            
            // Show admin actions based on status
            var adminActions = document.getElementById('eventAdminActions');
            var isAdmin = <?= session()->get('is_admin') ? 'true' : 'false' ?>;
            if (adminActions) {
                var approveForm = document.getElementById('approveForm');
                var sendZoomForm = document.getElementById('sendZoomForm');
                var manualZoomForm = document.getElementById('manualZoomForm');
                var manualZoomInput = document.getElementById('manualZoomInput');
                var showContainer = false;

                // Show approve/reject only for pending
                if (isAdmin && props.status === 'pending') {
                    var statusUrl = '<?= base_url('meeting/status/') ?>' + event.id;
                    approveForm.action = statusUrl;
                    approveForm.classList.remove('hidden');
                    showContainer = true;
                } else {
                    approveForm.classList.add('hidden');
                }

                // Show "Kirim Zoom" only for approved + Online/Hybrid + no zoom yet
                if (sendZoomForm) {
                    var isOnlineOrHybrid = props.tipe === 'Online' || props.tipe === 'Hybrid';
                    var hasHostMeeting = props.zoom_meeting_id && props.zoom_meeting_id !== '';
                    if (isAdmin && props.status === 'approved' && isOnlineOrHybrid && !hasHostMeeting) {
                        sendZoomForm.classList.remove('hidden');
                        sendZoomForm.action = '<?= base_url('meeting/send-zoom/') ?>' + event.id;
                        showContainer = true;
                    } else {
                        sendZoomForm.classList.add('hidden');
                    }
                }

                if (manualZoomForm && manualZoomInput) {
                    var isOnlineOrHybrid = props.tipe === 'Online' || props.tipe === 'Hybrid';
                    var meetingNotEnded = Date.now() < (Number(props.waktu_selesai_ts) * 1000);
                    if (isAdmin && props.status === 'approved' && isOnlineOrHybrid && meetingNotEnded) {
                        manualZoomForm.classList.remove('hidden');
                        manualZoomForm.classList.add('inline-flex');
                        manualZoomForm.action = '<?= base_url('meeting/manual-zoom/') ?>' + event.id;
                        manualZoomInput.value = props.zoom_join_url || '';
                        showContainer = true;
                    } else {
                        manualZoomForm.classList.remove('inline-flex');
                        manualZoomForm.classList.add('hidden');
                        manualZoomInput.value = '';
                    }
                }

                adminActions.classList.toggle('hidden', !showContainer);
            }

            // Owner Actions (Delete)
            var ownerActions = document.getElementById('eventOwnerActions');
            var currentPegawaiId = <?= (int)session()->get('pegawai_id') ?>;
            var isOwner = parseInt(props.pegawai_id) === currentPegawaiId;
            var isAdmin = <?= session()->get('is_admin') ? 'true' : 'false' ?>;

            if (ownerActions) {
                var editMeetingLink = document.getElementById('editMeetingLink');
                var deleteForm = document.getElementById('deleteForm');
                
                editMeetingLink.classList.add('hidden');
                deleteForm.classList.add('hidden');
                ownerActions.classList.add('hidden');

                if (isOwner || isAdmin) {
                    editMeetingLink.href = '<?= base_url('meeting/edit/') ?>' + event.id;
                    editMeetingLink.classList.remove('hidden');
                    // Direct Delete
                    deleteForm.action = '<?= base_url('meeting/delete/') ?>' + event.id;
                    deleteForm.classList.remove('hidden');
                    ownerActions.classList.remove('hidden');
                }
            }
            
            var eventModal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));

            // Populate audit trail (admin only)
            var auditSection = document.getElementById('eventAuditSection');
            if (auditSection) {
                var hasAudit = false;
                var statusRow = document.getElementById('auditStatusRow');
                var editRow = document.getElementById('auditEditRow');
                if (props.status_changed_by_name && props.status_changed_at) {
                    var statusLabel = props.status === 'approved' ? 'Disetujui' : (props.status === 'rejected' ? 'Ditolak' : 'Status diubah');
                    document.getElementById('auditStatusText').textContent = statusLabel + ' oleh ' + props.status_changed_by_name + ' pada ' + moment(props.status_changed_at).format('DD MMM YYYY HH:mm');
                    statusRow.classList.remove('hidden');
                    hasAudit = true;
                } else {
                    statusRow.classList.add('hidden');
                }
                if (props.last_edited_by_name && props.last_edited_at) {
                    document.getElementById('auditEditText').textContent = 'Diedit oleh ' + props.last_edited_by_name + ' pada ' + moment(props.last_edited_at).format('DD MMM YYYY HH:mm');
                    editRow.classList.remove('hidden');
                    hasAudit = true;
                } else {
                    editRow.classList.add('hidden');
                }
                var createdRow = document.getElementById('auditCreatedRow');
                if (props.pegawai && props.created_at) {
                    document.getElementById('auditCreatedText').textContent = 'Di-input oleh ' + props.pegawai + ' pada ' + moment(props.created_at).format('DD MMM YYYY HH:mm');
                    createdRow.classList.remove('hidden');
                    hasAudit = true;
                } else {
                    createdRow.classList.add('hidden');
                }
                auditSection.classList.toggle('hidden', !hasAudit);
            }

            // Show Zoom links (only for admin or meeting owner)
            var zoomSection = document.getElementById('eventZoomSection');
            if (zoomSection) {
                var isOnlineOrHybrid = props.tipe === 'Online' || props.tipe === 'Hybrid';
                var hasJoinUrl = props.zoom_join_url && props.zoom_join_url !== '';
                var hasHostMeeting = props.zoom_meeting_id && props.zoom_meeting_id !== '';
                var currentPegawaiId = <?= (int) session()->get('pegawai_id') ?>;
                var isOwner = parseInt(props.pegawai_id) === currentPegawaiId;
                var canSeeZoom = isAdmin || isOwner;
                var meetingNotEnded = Date.now() < (Number(props.waktu_selesai_ts) * 1000);
                var joinUrl = safeHttpUrl(props.zoom_join_url);
                if (isOnlineOrHybrid && props.status === 'approved' && hasJoinUrl && canSeeZoom) {
                    zoomSection.classList.remove('hidden');
                    document.getElementById('zoomJoinLink').href = joinUrl || '#';
                    var hostForm = document.getElementById('zoomHostForm');
                    var hostButton = document.getElementById('zoomHostButton');
                    var hostIcon = document.getElementById('zoomHostIcon');
                    var hostLabel = document.getElementById('zoomHostLabel');
                    var hostLinkCopy = document.getElementById('zoomHostLinkCopy');
                    var hostLinkInput = document.getElementById('zoomHostLinkInput');
                    var meetStart = Number(props.waktu_mulai_ts) * 1000;
                    var nowMs = Date.now();
                    var canHostNow = hasHostMeeting && meetingNotEnded && nowMs >= (meetStart - 3600000);

                    if (hasHostMeeting && meetingNotEnded) {
                        hostForm.action = '<?= base_url('meeting/refresh-zoom/') ?>' + event.id;
                        hostForm.classList.remove('hidden');
                        hostButton.disabled = !canHostNow;
                        hostButton.setAttribute('aria-disabled', canHostNow ? 'false' : 'true');
                        hostButton.title = canHostNow ? 'Buka sebagai Host' : 'Host aktif 1 jam sebelum meeting';
                        hostIcon.className = 'fas ' + (canHostNow ? 'fa-play-circle' : 'fa-lock');
                        hostLabel.textContent = canHostNow ? 'Host' : 'Host H-1 jam';
                    } else {
                        hostForm.classList.add('hidden');
                    }

                    if (props.start_token && props.start_token !== '') {
                        hostLinkInput.value = '<?= base_url('zoom/start/') ?>' + props.start_token;
                        hostLinkCopy.classList.remove('hidden');
                    } else {
                        hostLinkInput.value = '';
                        hostLinkCopy.classList.add('hidden');
                    }
                } else {
                    zoomSection.classList.add('hidden');
                }
            }

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

    // Auto-open create modal via ?action=create (used by topbar "+ Meeting")
    try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('action') === 'create') {
            var modalEl = document.getElementById('createMeetingModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    } catch (e) {}

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(function() {
            toast.classList.add('toast-hiding');
            setTimeout(function(){ toast.remove(); }, 300);
        }, 4000);
    }

    // Copy host shortlink in event modal
    var hostLinkCopyBtn = document.getElementById('zoomHostLinkCopy');
    if (hostLinkCopyBtn) {
        hostLinkCopyBtn.addEventListener('click', function() {
            var input = document.getElementById('zoomHostLinkInput');
            if (!input || !input.value) return;
            navigator.clipboard.writeText(input.value).then(function() {
                hostLinkCopyBtn.innerHTML = '<i class="fas fa-check"></i> Tersalin';
                hostLinkCopyBtn.style.color = '#16a34a';
                hostLinkCopyBtn.style.borderColor = '#bbf7d0';
                setTimeout(function() {
                    hostLinkCopyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                    hostLinkCopyBtn.style.color = '';
                    hostLinkCopyBtn.style.borderColor = '';
                }, 2000);
            }, function() {
                alert('Gagal menyalin link.');
            });
        });
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
        const roomContainer = document.getElementById('room_container');
        const participantsContainer = document.getElementById('participants_container');
        const participantsInput = document.getElementById('jumlah_peserta');
        
        if (tipe === 'Online') {
            if (roomContainer) {
                roomContainer.classList.remove('col-half');
                roomContainer.classList.add('col-full');
            }
            fasilitasContainer.classList.add('hidden');
            const checkboxes = fasilitasContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
            if (fasilitasLainnyaText) {
                fasilitasLainnyaText.classList.add('hidden');
                fasilitasLainnyaText.value = '';
            }
            if (participantsContainer) participantsContainer.classList.add('hidden');
            if (participantsInput) { participantsInput.required = false; participantsInput.value = ''; }
        } else {
            if (roomContainer) {
                roomContainer.classList.remove('col-full');
                roomContainer.classList.add('col-half');
            }
            fasilitasContainer.classList.remove('hidden');
            if (participantsContainer) participantsContainer.classList.remove('hidden');
            if (participantsInput) participantsInput.required = true;
        }
    }

    if (ruanganSelect && fasilitasContainer) {
        ruanganSelect.addEventListener('change', toggleFasilitas);
        toggleFasilitas();
    }
});
</script>
<?= $this->endSection() ?>

