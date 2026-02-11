<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<!-- Breadcrumb -->
<nav class="flex items-center gap-2 text-sm text-gray-500 mb-5 mt-2">
    <a href="<?= base_url('/upcoming') ?>" class="hover:text-orange-600 transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
    </a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700 font-medium">Edit Meeting</span>
</nav>

<div class="max-w-3xl mx-auto">
    <div class="card-modern-elevated">
        <!-- Top gradient accent -->
        <div style="height:4px; background: var(--gradient-primary);"></div>
        
        <div class="p-4 sm:p-6 border-b" style="border-color:#f1f5f9; background:linear-gradient(to bottom, #fafaff, white);">
            <h2 class="section-title text-lg">
                <i class="fas fa-edit mr-2 text-orange-500"></i>Edit Meeting
            </h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi meeting yang sudah ada</p>
        </div>

        <form action="<?= base_url('meeting/update/' . $meeting['id']) ?>" method="POST" class="p-4 sm:p-6" id="editMeetingForm">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 gap-y-5 gap-x-4 sm:grid-cols-6">
                <!-- Nama Kegiatan -->
                <div class="sm:col-span-6">
                    <label for="nama_keg" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-bookmark mr-1 text-orange-400 text-xs"></i> Nama Kegiatan
                    </label>
                    <input type="text" 
                           class="input-modern" 
                           id="nama_keg" 
                           name="nama_keg"
                           value="<?= old('nama_keg', $meeting['nama_keg']) ?>" 
                           required>
                </div>

                <!-- Ruangan -->
                <div class="sm:col-span-3">
                    <label for="ruangan_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-door-open mr-1 text-orange-400 text-xs"></i> Ruangan
                    </label>
                    <select class="input-modern dropdown-modern" 
                            id="ruangan_id" 
                            name="ruangan_id" 
                            required>
                        <?php foreach ($ruangan as $r): ?>
                            <option value="<?= $r['id'] ?>" 
                                    data-tipe="<?= $r['tipe'] ?>" 
                                    <?= $r['id'] == $meeting['ruangan_id'] ? 'selected' : '' ?>>
                                <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jumlah Peserta -->
                <div class="sm:col-span-3" id="participants_container">
                    <label for="jumlah_peserta" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-users mr-1 text-orange-400 text-xs"></i> Jumlah Peserta
                    </label>
                    <input type="number" 
                           class="input-modern" 
                           id="jumlah_peserta" 
                           name="jumlah_peserta"
                           value="<?= old('jumlah_peserta', $meeting['jumlah_peserta'] ?? '') ?>" 
                           min="1" 
                           required>
                </div>

                <!-- Divider -->
                <div class="sm:col-span-6">
                    <div style="border-top:1px solid #f1f5f9;" class="my-1"></div>
                </div>

                <!-- Waktu Mulai -->
                <div class="sm:col-span-3">
                    <label for="waktu_mulai" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-clock mr-1 text-orange-400 text-xs"></i> Waktu Mulai
                    </label>
                    <input type="datetime-local" 
                           class="input-modern" 
                           id="waktu_mulai" 
                           name="waktu_mulai"
                           value="<?= date('Y-m-d\TH:i', strtotime($meeting['waktu_mulai'])) ?>" 
                           step="900"
                           required>
                </div>

                <!-- Durasi -->
                <div class="sm:col-span-3">
                    <label for="durasi" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-hourglass-half mr-1 text-orange-400 text-xs"></i> Durasi
                    </label>
                    <?php
                        $defaultDurasi = 60;
                        if (isset($meeting['waktu_mulai'], $meeting['waktu_selesai'])) {
                            $start = new DateTime($meeting['waktu_mulai']);
                            $end = new DateTime($meeting['waktu_selesai']);
                            $diff = $start->diff($end);
                            $diffMinutes = ($diff->h * 60) + $diff->i + ($diff->days * 24 * 60);
                            if ($diffMinutes >= 480) {
                                $defaultDurasi = 'Penuh';
                            } else {
                                $defaultDurasi = $diffMinutes;
                            }
                        }
                    ?>
                    <select class="input-modern dropdown-modern" 
                            id="durasi" 
                            name="durasi" 
                            required>
                        <option value="60" <?= $defaultDurasi == 60  ? 'selected' : '' ?>>1 jam</option>
                        <option value="120" <?= $defaultDurasi == 120 ? 'selected' : '' ?>>2 jam</option>
                        <option value="180" <?= $defaultDurasi == 180 ? 'selected' : '' ?>>3 jam</option>
                        <option value="240" <?= $defaultDurasi == 240 ? 'selected' : '' ?>>4 jam</option>
                        <option value="Penuh" <?= $defaultDurasi === 'Penuh' ? 'selected' : '' ?>>Satu Hari Penuh</option>
                    </select>
                </div>

                <!-- Divider -->
                <div class="sm:col-span-6">
                    <div style="border-top:1px solid #f1f5f9;" class="my-1"></div>
                </div>

                <!-- Fasilitas -->
                <div class="sm:col-span-6" id="fasilitas_container">
                    <label class="block text-sm font-semibold text-gray-700 mb-2.5">
                        <i class="fas fa-concierge-bell mr-1 text-orange-400 text-xs"></i> Fasilitas Rapat
                    </label>
                    <?php 
                        $fasilitasArr = [];
                        if (!empty($meeting['fasilitas'])) {
                            $fasilitasArr = json_decode($meeting['fasilitas'], true) ?? [];
                        }
                        $fasilitas_options = [
                            ['icon' => 'fa-microphone', 'name' => 'Microphone'],
                            ['icon' => 'fa-camera', 'name' => 'Dokumentasi'],
                            ['icon' => 'fa-video', 'name' => 'Zoom (Hybrid)'],
                            ['icon' => 'fa-cookie-bite', 'name' => 'Snack'],
                            ['icon' => 'fa-utensils', 'name' => 'Makanan Berat'],
                            ['icon' => 'fa-glass-water', 'name' => 'Air Minum'],
                        ];
                        $knownFasilitas = array_column($fasilitas_options, 'name');
                        $lainnyaValue = '';
                        $hasLainnya = false;
                        foreach ($fasilitasArr as $f) {
                            if (!in_array($f, $knownFasilitas) && strtolower($f) !== 'lainnya') {
                                $lainnyaValue = $f;
                                $hasLainnya = true;
                            } else if (strtolower($f) === 'lainnya') {
                                $hasLainnya = true;
                            }
                        }
                    ?>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <?php foreach ($fasilitas_options as $f): ?>
                        <label for="fasilitas_edit_<?= url_title($f['name'], '-', true) ?>"
                               class="flex items-center gap-2.5 p-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all"
                               style="font-size:0.8125rem;">
                            <input id="fasilitas_edit_<?= url_title($f['name'], '-', true) ?>"
                                   name="fasilitas[]" 
                                   value="<?= $f['name'] ?>" 
                                   type="checkbox" 
                                   class="h-4 w-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                   <?= in_array($f['name'], $fasilitasArr) ? 'checked' : '' ?>>
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
                                   class="h-4 w-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                   <?= $hasLainnya ? 'checked' : '' ?>>
                            <i class="fas fa-ellipsis-h text-orange-400 text-xs"></i>
                            <span class="font-medium text-gray-700">Lainnya</span>
                        </label>
                        <div class="col-span-2 sm:col-span-3">
                            <input type="text" 
                                   id="fasilitas_lainnya_text" 
                                   name="fasilitas_lainnya" 
                                   class="input-modern <?= !$hasLainnya ? 'hidden' : '' ?>" 
                                   placeholder="Sebutkan fasilitas lainnya..."
                                   value="<?= esc($lainnyaValue) ?>">
                        </div>
                    </div>
                </div>

                <input type="hidden" id="waktu_selesai" name="waktu_selesai" value="<?= date('Y-m-d\TH:i', strtotime($meeting['waktu_selesai'])) ?>">
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-3 mt-8 pt-5" style="border-top:1px solid #f1f5f9;">
                <a href="<?= base_url('/upcoming') ?>" class="btn-outline-custom">
                    <i class="fas fa-times"></i> Batal
                </a>
                <button type="submit" id="editSubmitBtn" class="btn-primary-gradient">
                    <i class="fas fa-save"></i>
                    <span id="editSubmitText">Simpan Perubahan</span>
                </button>
            </div>
        </form>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Duration / end time calculation
    function updateEndTime() {
        var startEl = document.getElementById('waktu_mulai');
        var durasiEl = document.getElementById('durasi');
        if (!startEl || !durasiEl) return;
        var duration = parseInt(durasiEl.value);
        if (startEl.value && !isNaN(duration)) {
            var endTime = new Date(startEl.value);
            endTime.setMinutes(endTime.getMinutes() + duration);
            document.getElementById('waktu_selesai').value = endTime.toISOString().slice(0, 16);
        }
    }
    document.getElementById('waktu_mulai').addEventListener('change', updateEndTime);
    document.getElementById('durasi').addEventListener('change', updateEndTime);

    // Lainnya toggle
    const lainnyaCheckbox = document.getElementById('fasilitas_lainnya_checkbox');
    const lainnyaText = document.getElementById('fasilitas_lainnya_text');
    if (lainnyaCheckbox && lainnyaText) {
        lainnyaCheckbox.addEventListener('change', function() {
            if (this.checked) {
                lainnyaText.classList.remove('hidden');
                lainnyaText.focus();
            } else {
                lainnyaText.classList.add('hidden');
                lainnyaText.value = '';
            }
        });
    }

    // Room type toggle
    const ruanganSelect = document.getElementById('ruangan_id');
    const fasilitasContainer = document.getElementById('fasilitas_container');
    const participantsContainer = document.getElementById('participants_container');
    const participantsInput = document.getElementById('jumlah_peserta');
    
    function toggleFasilitas() {
        const selectedOption = ruanganSelect.options[ruanganSelect.selectedIndex];
        const tipe = selectedOption ? selectedOption.getAttribute('data-tipe') : '';
        if (tipe === 'Online') {
            fasilitasContainer.classList.add('hidden');
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

    // Prevent double submit
    const form = document.getElementById('editMeetingForm');
    const submitBtn = document.getElementById('editSubmitBtn');
    const submitText = document.getElementById('editSubmitText');
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitText.textContent = 'Menyimpan...';
            setTimeout(function() { submitBtn.disabled = false; submitText.textContent = 'Simpan Perubahan'; }, 5000);
        });
    }

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 300); }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
