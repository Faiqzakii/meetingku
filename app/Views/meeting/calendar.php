<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mt-2 mb-6">
    <div>
        <h2 class="section-title">
            <i class="fas fa-calendar-alt mr-2 text-orange-500"></i>Jadwal Pertemuan
        </h2>
        <p class="text-sm text-gray-500 mt-1">Lihat dan kelola jadwal meeting di satu tempat</p>
    </div>
    <div class="flex flex-wrap items-center gap-3 mt-4 sm:mt-0">
        <select id="ruanganFilter" class="input-modern dropdown-modern" style="width:auto; min-width:180px;">
            <option value="">📍 Semua Ruangan</option>
            <?php foreach ($ruangan as $r): ?>
                <option value="<?= $r['id'] ?>">
                    <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (session()->get('logged_in')): ?>
            <button type="button" 
                    class="btn-primary-gradient"
                    data-bs-toggle="modal" 
                    data-bs-target="#createMeetingModal">
                <i class="fas fa-plus"></i>
                <span>Buat Meeting</span>
            </button>
        <?php else: ?>
            <a href="<?= base_url('auth/login') ?>" class="btn-primary-gradient">
                <i class="fas fa-plus"></i>
                <span>Buat Meeting</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Status Legend -->
<div class="flex flex-wrap items-center gap-4 mb-4 px-1">
    <span class="flex items-center gap-2 text-xs font-medium text-gray-500">
        <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Disetujui
    </span>
    <span class="flex items-center gap-2 text-xs font-medium text-gray-500">
        <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Menunggu
    </span>
    <span class="flex items-center gap-2 text-xs font-medium text-gray-500">
        <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Ditolak
    </span>
</div>

<div class="card-modern-elevated p-4 sm:p-6">
    <div id="calendar"></div>
</div>

<!-- Create Meeting Modal -->
<div class="modal fade" id="createMeetingModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus-circle mr-2 text-orange-500"></i>Buat Meeting Baru
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="<?= base_url('meeting/create') ?>" method="POST" id="createMeetingForm">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 gap-y-5 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="nama_keg" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-bookmark mr-1 text-orange-400 text-xs"></i> Nama Kegiatan
                            </label>
                            <input type="text" 
                                   class="input-modern" 
                                   id="nama_keg" 
                                   name="nama_keg" 
                                   placeholder="Contoh: Rapat Koordinasi Bulanan"
                                   required>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="ruangan_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-door-open mr-1 text-orange-400 text-xs"></i> Ruangan
                            </label>
                            <select class="input-modern dropdown-modern" 
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

                        <div class="sm:col-span-3" id="participants_container">
                            <label for="jumlah_peserta" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-users mr-1 text-orange-400 text-xs"></i> Jumlah Peserta
                            </label>
                            <input type="number" 
                                   class="input-modern" 
                                   id="jumlah_peserta" 
                                   name="jumlah_peserta" 
                                   min="1"
                                   placeholder="0"
                                   required>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="tanggal_mulai" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-calendar mr-1 text-orange-400 text-xs"></i> Tanggal
                            </label>
                            <input type="date" 
                                   class="input-modern" 
                                   id="tanggal_mulai" 
                                   required>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="jam_mulai" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-clock mr-1 text-orange-400 text-xs"></i> Jam Mulai
                            </label>
                            <select class="input-modern dropdown-modern" 
                                    id="jam_mulai" 
                                    required>
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

                        <div class="sm:col-span-3">
                            <label for="durasi" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-hourglass-half mr-1 text-orange-400 text-xs"></i> Durasi
                            </label>
                            <select class="input-modern dropdown-modern" 
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

                        <div class="sm:col-span-6" id="fasilitas_container">
                            <label class="block text-sm font-semibold text-gray-700 mb-2.5">
                                <i class="fas fa-concierge-bell mr-1 text-orange-400 text-xs"></i> Fasilitas Rapat
                            </label>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <?php 
                                $fasilitas_options = [
                                    ['icon' => 'fa-microphone', 'name' => 'Microphone'],
                                    ['icon' => 'fa-camera', 'name' => 'Dokumentasi'],
                                    ['icon' => 'fa-video', 'name' => 'Zoom (Hybrid)'],
                                    ['icon' => 'fa-cookie-bite', 'name' => 'Snack'],
                                    ['icon' => 'fa-utensils', 'name' => 'Makanan Berat'],
                                    ['icon' => 'fa-glass-water', 'name' => 'Air Minum'],
                                ];
                                foreach ($fasilitas_options as $f): 
                                ?>
                                <label for="fasilitas_<?= url_title($f['name'], '-', true) ?>" 
                                       class="flex items-center gap-2.5 p-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all"
                                       style="font-size:0.8125rem;">
                                    <input id="fasilitas_<?= url_title($f['name'], '-', true) ?>" 
                                           name="fasilitas[]" 
                                           value="<?= $f['name'] ?>" 
                                           type="checkbox" 
                                           class="h-4 w-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                    <i class="fas <?= $f['icon'] ?> text-orange-400 text-xs"></i>
                                    <span class="font-medium text-gray-700"><?= $f['name'] ?></span>
                                </label>
                                <?php endforeach; ?>
                                <label for="fasilitas_lainnya_checkbox"
                                       class="flex items-center gap-2.5 p-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all col-span-2 sm:col-span-3"
                                       style="font-size:0.8125rem;">
                                    <input id="fasilitas_lainnya_checkbox" 
                                           name="fasilitas[]" 
                                           value="Lainnya" 
                                           type="checkbox" 
                                           class="h-4 w-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                    <i class="fas fa-ellipsis-h text-orange-400 text-xs"></i>
                                    <span class="font-medium text-gray-700">Lainnya</span>
                                </label>
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" 
                                           id="fasilitas_lainnya_text" 
                                           name="fasilitas_lainnya" 
                                           class="input-modern hidden" 
                                           placeholder="Sebutkan fasilitas lainnya...">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="waktu_selesai" name="waktu_selesai">
                    </div>
                </div>
                <div class="modal-footer flex justify-end gap-3">
                    <button type="button" class="btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="submitBtn" class="btn-primary-gradient">
                        <span id="submitText">Simpan</span>
                        <span id="submitSpinner" class="hidden">
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
                                <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#7c3aed; border-color:#ddd6fe;" title="Buka sebagai Host (generate link baru)">
                                    <i class="fas fa-play-circle"></i> Host
                                </button>
                            </form>
                            <span id="zoomHostLocked" class="text-xs text-gray-400 italic hidden" title="Link Host tersedia 1 jam sebelum meeting">
                                <i class="fas fa-lock text-gray-300"></i> Host (H-1 jam)
                            </span>
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
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.875rem; font-size:0.8125rem; color:#059669; border-color:#a7f3d0;">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                    </form>

                    <form id="sendZoomForm" method="POST" class="inline-block hidden" onsubmit="return confirm('Buat Zoom meeting dan kirim link ke pegawai?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.875rem; font-size:0.8125rem; color:#2563eb; border-color:#bfdbfe; font-weight:600;">
                            🚀 Kirim Zoom
                        </button>
                    </form>
                    </form>
                </div>
                
                <div id="eventOwnerActions" class="hidden flex items-center gap-2 flex-wrap ml-2">
                    <form id="deleteForm" method="POST" class="inline-block hidden" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.875rem; font-size:0.8125rem; color:#ef4444; border-color:#fecaca;">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </form>
                </div>

                <div class="ml-auto">
                    <button type="button" class="btn-outline-custom" data-bs-dismiss="modal">Tutup</button>
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
            'backgroundColor' => $meeting['status'] === 'approved' ? '#10B981' : 
                               ($meeting['status'] === 'pending' ? '#F59E0B' : '#EF4444'),
            'borderColor' => $meeting['status'] === 'approved' ? '#059669' : 
                            ($meeting['status'] === 'pending' ? '#D97706' : '#DC2626'),
            'extendedProps' => [
                'ruangan_id' => $meeting['ruangan_id'],
                'ruangan' => $meeting['nama_ruangan'],
                'tipe' => $meeting['tipe'],
                'status' => $meeting['status'],
                'waktu_mulai_ts' => isset($meeting['waktu_mulai']) ? strtotime($meeting['waktu_mulai']) : null,
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
            statusElement.innerHTML = '<span class="badge-status ' + badgeClass + '">' + props.status.toUpperCase() + '</span>';
            
            // Show admin actions based on status
            var adminActions = document.getElementById('eventAdminActions');
            var isAdmin = <?= session()->get('is_admin') ? 'true' : 'false' ?>;
            if (adminActions) {
                var approveForm = document.getElementById('approveForm');
                var sendZoomForm = document.getElementById('sendZoomForm');
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
                    var hasZoom = props.zoom_meeting_id && props.zoom_meeting_id !== '';
                    if (isAdmin && props.status === 'approved' && isOnlineOrHybrid && !hasZoom) {
                        sendZoomForm.classList.remove('hidden');
                        sendZoomForm.action = '<?= base_url('meeting/send-zoom/') ?>' + event.id;
                        showContainer = true;
                    } else {
                        sendZoomForm.classList.add('hidden');
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
                var deleteForm = document.getElementById('deleteForm');
                
                deleteForm.classList.add('hidden');
                ownerActions.classList.add('hidden');

                if (isOwner || isAdmin) {
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
                    document.getElementById('auditStatusText').innerHTML = '<strong>' + statusLabel + '</strong> oleh <strong>' + props.status_changed_by_name + '</strong> pada ' + moment(props.status_changed_at).format('DD MMM YYYY HH:mm');
                    statusRow.classList.remove('hidden');
                    hasAudit = true;
                } else {
                    statusRow.classList.add('hidden');
                }
                if (props.last_edited_by_name && props.last_edited_at) {
                    document.getElementById('auditEditText').innerHTML = '<strong>Diedit</strong> oleh <strong>' + props.last_edited_by_name + '</strong> pada ' + moment(props.last_edited_at).format('DD MMM YYYY HH:mm');
                    editRow.classList.remove('hidden');
                    hasAudit = true;
                } else {
                    editRow.classList.add('hidden');
                }
                var createdRow = document.getElementById('auditCreatedRow');
                if (props.pegawai && props.created_at) {
                    document.getElementById('auditCreatedText').innerHTML = '<strong>Di-input</strong> oleh <strong>' + props.pegawai + '</strong> pada ' + moment(props.created_at).format('DD MMM YYYY HH:mm');
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
                var hasZoom = props.zoom_meeting_id && props.zoom_meeting_id !== '';
                var currentPegawaiId = <?= (int) session()->get('pegawai_id') ?>;
                var isOwner = parseInt(props.pegawai_id) === currentPegawaiId;
                var canSeeZoom = isAdmin || isOwner;
                if (isOnlineOrHybrid && props.status === 'approved' && hasZoom && canSeeZoom) {
                    zoomSection.classList.remove('hidden');
                    document.getElementById('zoomJoinLink').href = props.zoom_join_url || '#';
                    var hostForm = document.getElementById('zoomHostForm');
                    var hostLocked = document.getElementById('zoomHostLocked');
                    var meetStart = Number(props.waktu_mulai_ts) * 1000;
                    var nowMs = Date.now();
                    if (nowMs >= (meetStart - 3600000)) {
                        hostForm.action = '<?= base_url('meeting/refresh-zoom/') ?>' + event.id;
                        hostForm.classList.remove('hidden');
                        hostLocked.classList.add('hidden');
                    } else {
                        hostForm.classList.add('hidden');
                        hostLocked.classList.remove('hidden');
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

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(function() {
            toast.classList.add('toast-hiding');
            setTimeout(function(){ toast.remove(); }, 300);
        }, 4000);
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
            const checkboxes = fasilitasContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
            if (fasilitasLainnyaText) {
                fasilitasLainnyaText.classList.add('hidden');
                fasilitasLainnyaText.value = '';
            }
            if (participantsContainer) participantsContainer.classList.add('hidden');
            if (participantsInput) { participantsInput.required = false; participantsInput.value = ''; }
        } else {
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
