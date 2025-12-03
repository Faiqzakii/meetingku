<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Edit Meeting</h2>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <form action="<?= base_url('meeting/update/' . $meeting['id']) ?>" method="POST" class="p-6">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-6">
                <label for="nama_keg" class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                <div class="mt-1">
                    <input type="text" 
                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
                           id="nama_keg" 
                           name="nama_keg" 
                           value="<?= old('nama_keg', $meeting['nama_keg']) ?>"
                           required>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="jumlah_peserta" class="block text-sm font-medium text-gray-700">Jumlah Peserta</label>
                <div class="mt-1">
                    <input type="number" 
                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-2 px-3" 
                           id="jumlah_peserta" 
                           name="jumlah_peserta" 
                           value="<?= old('jumlah_peserta', $meeting['jumlah_peserta'] ?? '') ?>"
                           min="1"
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
                            <option value="<?= $r['id'] ?>" data-tipe="<?= $r['tipe'] ?>" <?= $r['id'] == $meeting['ruangan_id'] ? 'selected' : '' ?>>
                                <?= esc($r['nama_ruangan']) ?> (<?= $r['tipe'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                           value="<?= date('Y-m-d\TH:i', strtotime($meeting['waktu_mulai'])) ?>"
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
                        'Microphone', 'Kamera', 'Zoom Meeting', 
                        'Snack', 'Makanan Berat', 'Air Minum'
                    ];
                    $selected_fasilitas = json_decode($meeting['fasilitas'] ?? '[]', true) ?? [];
                    
                    // Check for "Lainnya"
                    $lainnya_value = '';
                    $lainnya_checked = false;
                    foreach ($selected_fasilitas as $sf) {
                        if (strpos($sf, 'Lainnya: ') === 0) {
                            $lainnya_value = substr($sf, 9); // Remove "Lainnya: "
                            $lainnya_checked = true;
                        } elseif ($sf === 'Lainnya') {
                             $lainnya_checked = true;
                        }
                    }

                    foreach ($fasilitas_options as $f): 
                    ?>
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input id="fasilitas_<?= url_title($f, '-', true) ?>" 
                                   name="fasilitas[]" 
                                   value="<?= $f ?>" 
                                   type="checkbox" 
                                   <?= in_array($f, $selected_fasilitas) ? 'checked' : '' ?>
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
                                   <?= $lainnya_checked ? 'checked' : '' ?>
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm flex-grow">
                            <label for="fasilitas_lainnya_checkbox" class="font-medium text-gray-700">Lainnya</label>
                            <input type="text" 
                                   id="fasilitas_lainnya_text" 
                                   name="fasilitas_lainnya" 
                                   value="<?= esc($lainnya_value) ?>"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-400 rounded-md shadow-sm py-1 px-2 <?= $lainnya_checked ? '' : 'hidden' ?>" 
                                   placeholder="Sebutkan fasilitas lainnya...">
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="waktu_selesai" name="waktu_selesai">
        </div>
        <div class="mt-6 flex justify-end space-x-3">
            <a href="<?= base_url('meeting/upcoming') ?>" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Batal
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Simpan
            </button>
        </div>
    </form>
</div>

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
        
        if (tipe === 'Online') {
            fasilitasContainer.classList.add('hidden');
            // Optional: Uncheck all facilities if hidden
            const checkboxes = fasilitasContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
            if (fasilitasLainnyaText) {
                fasilitasLainnyaText.classList.add('hidden');
                fasilitasLainnyaText.value = '';
            }
        } else {
            fasilitasContainer.classList.remove('hidden');
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
