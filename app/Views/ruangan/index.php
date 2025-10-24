<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mt-4 mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Ruangan</h2>
    </div>
    <?php if ($isAdmin): ?>
    <div class="sm:mt-0">
        <button type="button" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                data-bs-toggle="modal" 
                data-bs-target="#createRuanganModal">
            <i class="fas fa-plus mr-2"></i> Tambah Ruangan
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($ruangan as $room): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?= esc($room['nama_ruangan']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $room['tipe'] === 'Online' ? 'bg-green-100 text-green-800' : ($room['tipe'] === 'Offline' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') ?>">
                            <?= esc($room['tipe']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="<?= base_url('ruangan/' . $room['id'] . '/meetings') ?>" 
                           class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-calendar-alt mr-1"></i> Lihat Jadwal
                        </a>
                        <?php if ($isAdmin): ?>
                            <a href="<?= base_url('ruangan/edit/' . $room['id']) ?>" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <form action="<?= base_url('ruangan/delete/' . $room['id']) ?>" 
                                  method="POST" 
                                  class="inline-block"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Ruangan Modal -->
<div class="modal fade" id="createRuanganModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-medium text-gray-900" id="modalTitle">Tambah Ruangan</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?= base_url('ruangan/create') ?>" method="POST">
                <div class="modal-body p-6">
                    <?= csrf_field() ?>
                    <div class="space-y-4">
                        <div>
                            <label for="nama_ruangan" class="block text-sm font-medium text-gray-700">Nama Ruangan</label>
                            <input type="text" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="nama_ruangan" 
                                   name="nama_ruangan" 
                                   required>
                        </div>
                        <div>
                            <label for="tipe" class="block text-sm font-medium text-gray-700">Tipe</label>
                            <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                    id="tipe" 
                                    name="tipe" 
                                    required>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
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
// Auto-hide flash messages after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(function() {
            flashMessage.style.display = 'none';
        }, 3000);
    }
});
</script>
<?= $this->endSection() ?>
