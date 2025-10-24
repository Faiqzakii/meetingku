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
        <div class="space-y-4">
            <div>
                <label for="nama_keg" class="block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                       id="nama_keg" 
                       name="nama_keg" 
                       value="<?= old('nama_keg', $meeting['nama_keg']) ?>"
                       required>
            </div>
            <div>
                <label for="ruangan_id" class="block text-sm font-medium text-gray-700">Ruangan</label>
                <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        id="ruangan_id" 
                        name="ruangan_id" 
                        required>
                    <?php foreach ($ruangan as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $meeting['ruangan_id'] ? 'selected' : '' ?>>
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
                       value="<?= date('Y-m-d\TH:i', strtotime($meeting['waktu_mulai'])) ?>"
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
});
</script>
<?= $this->endSection() ?>
