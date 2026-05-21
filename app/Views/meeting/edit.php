<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-pen-to-square" aria-hidden="true"></i>
            Edit Meeting
        </h2>
        <p class="page-subtitle"><?= esc($meeting['nama_keg']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= base_url('/upcoming') ?>" class="btn btn-ghost">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>

<div style="max-width:840px;">
    <div class="card">
        <form action="<?= base_url('meeting/update/' . $meeting['id']) ?>" method="POST" class="card-section" id="editMeetingForm">
            <?= csrf_field() ?>
            <input type="hidden" name="form_token" value="<?= bin2hex(random_bytes(16)) ?>">
            <div class="form-grid">
                <div class="field col-span-2">
                    <label class="field-label" for="nama_keg">Nama kegiatan</label>
                    <input class="input" type="text" id="nama_keg" name="nama_keg"
                           value="<?= esc(old('nama_keg', $meeting['nama_keg']), 'attr') ?>" required>
                </div>

                <div class="field">
                    <label class="field-label" for="ruangan_id">Ruangan</label>
                    <select class="input" id="ruangan_id" name="ruangan_id" required>
                        <?php foreach ($ruangan as $r): ?>
                            <option value="<?= esc($r['id'], 'attr') ?>"
                                    data-tipe="<?= esc($r['tipe'], 'attr') ?>"
                                    <?= $r['id'] == $meeting['ruangan_id'] ? 'selected' : '' ?>>
                                <?= esc($r['nama_ruangan']) ?> (<?= esc($r['tipe']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field" id="participants_container">
                    <label class="field-label" for="jumlah_peserta">Jumlah peserta</label>
                    <input class="input" type="number" id="jumlah_peserta" name="jumlah_peserta"
                           value="<?= esc(old('jumlah_peserta', $meeting['jumlah_peserta'] ?? ''), 'attr') ?>"
                           min="1" required>
                </div>

                <div class="field">
                    <label class="field-label" for="waktu_mulai">Waktu mulai</label>
                    <input class="input" type="datetime-local" id="waktu_mulai" name="waktu_mulai"
                           value="<?= date('Y-m-d\TH:i', strtotime($meeting['waktu_mulai'])) ?>"
                           step="900" required>
                </div>

                <div class="field">
                    <label class="field-label" for="durasi">Durasi</label>
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
                    <select class="input" id="durasi" name="durasi" required>
                        <option value="60"  <?= $defaultDurasi == 60  ? 'selected' : '' ?>>1 jam</option>
                        <option value="120" <?= $defaultDurasi == 120 ? 'selected' : '' ?>>2 jam</option>
                        <option value="180" <?= $defaultDurasi == 180 ? 'selected' : '' ?>>3 jam</option>
                        <option value="240" <?= $defaultDurasi == 240 ? 'selected' : '' ?>>4 jam</option>
                        <option value="Penuh" <?= $defaultDurasi === 'Penuh' ? 'selected' : '' ?>>Satu Hari Penuh</option>
                    </select>
                </div>

                <div class="field col-span-2" id="fasilitas_container">
                    <label class="field-label">Fasilitas rapat</label>
                    <?php
                        $fasilitasArr = [];
                        if (!empty($meeting['fasilitas'])) {
                            $fasilitasArr = json_decode($meeting['fasilitas'], true) ?? [];
                        }
                        $fasilitas_options = [
                            ['icon' => 'fa-microphone',   'name' => 'Microphone'],
                            ['icon' => 'fa-camera',       'name' => 'Dokumentasi'],
                            ['icon' => 'fa-video',        'name' => 'Zoom (Hybrid)'],
                            ['icon' => 'fa-cookie-bite',  'name' => 'Snack'],
                            ['icon' => 'fa-utensils',     'name' => 'Makanan Berat'],
                            ['icon' => 'fa-glass-water',  'name' => 'Air Minum'],
                        ];
                        $knownFasilitas = array_column($fasilitas_options, 'name');
                        $lainnyaValue = '';
                        $hasLainnya = false;
                        foreach ($fasilitasArr as $f) {
                            if (!in_array($f, $knownFasilitas) && strtolower($f) !== 'lainnya') {
                                $lainnyaValue = $f;
                                $hasLainnya = true;
                            } elseif (strtolower($f) === 'lainnya') {
                                $hasLainnya = true;
                            }
                        }
                    ?>
                    <div class="check-grid">
                        <?php foreach ($fasilitas_options as $f): ?>
                            <label class="check-pill" for="fasilitas_edit_<?= url_title($f['name'], '-', true) ?>">
                                <input id="fasilitas_edit_<?= url_title($f['name'], '-', true) ?>"
                                       name="fasilitas[]" value="<?= esc($f['name'], 'attr') ?>" type="checkbox"
                                       <?= in_array($f['name'], $fasilitasArr) ? 'checked' : '' ?>>
                                <i class="fas <?= esc($f['icon']) ?>" aria-hidden="true"></i>
                                <span><?= esc($f['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <label class="check-pill check-pill-wide" for="fasilitas_lainnya_checkbox">
                            <input id="fasilitas_lainnya_checkbox" name="fasilitas[]" value="Lainnya" type="checkbox"
                                   <?= $hasLainnya ? 'checked' : '' ?>>
                            <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                            <span>Lainnya</span>
                        </label>
                        <input type="text" id="fasilitas_lainnya_text" name="fasilitas_lainnya"
                               class="input check-pill-wide <?= !$hasLainnya ? 'hidden' : '' ?>"
                               placeholder="Sebutkan fasilitas lainnya..."
                               value="<?= esc($lainnyaValue, 'attr') ?>">
                    </div>
                </div>

                <input type="hidden" id="waktu_selesai" name="waktu_selesai" value="<?= date('Y-m-d\TH:i', strtotime($meeting['waktu_selesai'])) ?>">
            </div>

            <?php if (session()->get('is_admin')): ?>
            <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--border);">
                <p style="font-size:.7rem;font-weight:700;color:var(--mute);text-transform:uppercase;letter-spacing:.04em;margin:0 0 10px;">
                    <i class="fas fa-history" aria-hidden="true"></i> Log aktivitas
                </p>
                <div class="stack-sm">
                    <div style="display:flex;align-items:flex-start;gap:8px;font-size:.75rem;color:var(--body);background:var(--canvas-soft);border-radius:var(--radius-sm);padding:8px 10px;">
                        <i class="fas fa-plus-circle" aria-hidden="true" style="color:var(--primary);margin-top:2px;"></i>
                        <span><strong>Di-input</strong> oleh <strong><?= esc($meeting['nama_pegawai'] ?? '-') ?></strong> pada <?= $meeting['created_at'] ? date('d M Y H:i', strtotime($meeting['created_at'])) : '-' ?></span>
                    </div>
                    <?php if (!empty($meeting['last_edited_by_name']) && !empty($meeting['last_edited_at'])): ?>
                        <div style="display:flex;align-items:flex-start;gap:8px;font-size:.75rem;color:var(--body);background:var(--canvas-soft);border-radius:var(--radius-sm);padding:8px 10px;">
                            <i class="fas fa-pen" aria-hidden="true" style="color:var(--primary);margin-top:2px;"></i>
                            <span><strong>Diedit</strong> oleh <strong><?= esc($meeting['last_edited_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['last_edited_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($meeting['status_changed_by_name']) && !empty($meeting['status_changed_at'])): ?>
                        <?php $statusLabel = $meeting['status'] === 'approved' ? 'Disetujui' : ($meeting['status'] === 'rejected' ? 'Ditolak' : 'Status diubah'); ?>
                        <div style="display:flex;align-items:flex-start;gap:8px;font-size:.75rem;color:var(--body);background:var(--canvas-soft);border-radius:var(--radius-sm);padding:8px 10px;">
                            <i class="fas fa-gavel" aria-hidden="true" style="color:var(--primary);margin-top:2px;"></i>
                            <span><strong><?= esc($statusLabel) ?></strong> oleh <strong><?= esc($meeting['status_changed_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['status_changed_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action buttons -->
            <div class="row-actions" style="justify-content:flex-end;margin-top:24px;padding-top:18px;border-top:1px solid var(--border);">
                <a href="<?= base_url('/upcoming') ?>" class="btn btn-secondary">
                    <i class="fas fa-times" aria-hidden="true"></i> Batal
                </a>
                <button type="submit" id="editSubmitBtn" class="btn btn-primary">
                    <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                    <span id="editSubmitText">Simpan perubahan</span>
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
