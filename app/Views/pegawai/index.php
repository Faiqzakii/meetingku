<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mt-4 mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Data Pegawai</h2>
    </div>
    <?php if ($isAdmin): ?>
    <div class="sm:mt-0 space-x-3">
        <!-- Import Excel Button -->
        <button type="button" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                data-bs-toggle="modal" 
                data-bs-target="#importExcelModal">
            <i class="fas fa-file-import mr-2"></i> Import Excel
        </button>
        <!-- Add Pegawai -->
        <button type="button" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                data-bs-toggle="modal" 
                data-bs-target="#createPegawaiModal">
            <i class="fas fa-plus mr-2"></i> Tambah Pegawai
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <?php if ($isAdmin): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($pegawai as $p): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?= esc($p['nama']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?= esc($p['nip']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= esc($p['username']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php if ($p['is_admin']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                Admin
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Pegawai
                            </span>
                        <?php endif; ?>
                    </td>
                    <?php if ($isAdmin): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button onclick="window.location.href='<?= base_url('pegawai/edit/' . $p['id']) ?>'" 
                                    class="text-blue-600 hover:text-blue-900 no-underline">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <form action="<?= base_url('pegawai/delete/' . $p['id']) ?>" 
                                  method="POST" 
                                  class="inline-block"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:text-red-900 no-underline">
                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Pegawai Modal -->
<div class="modal fade" id="createPegawaiModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-medium text-gray-900" id="modalTitle">Tambah Pegawai</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?= base_url('pegawai/create') ?>" method="POST">
                <div class="modal-body p-6">
                    <?= csrf_field() ?>
                    <div class="space-y-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                            <input type="text" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="nama" 
                                   name="nama" 
                                   required>
                        </div>
                        <div>
                            <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                            <input type="text" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="nip" 
                                   name="nip" 
                                   required>
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="username" 
                                   name="username" 
                                   required>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   id="password" 
                                   name="password" 
                                   required>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                   id="is_admin" 
                                   name="is_admin" 
                                   value="1">
                            <label for="is_admin" class="ml-2 block text-sm text-gray-900">
                                Admin
                            </label>
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

<?php 
$warning = session()->getFlashdata('warning');
if ($warning): 
?>
    <div class="fixed bottom-4 right-4 px-4 py-2 bg-yellow-500 text-white rounded shadow-lg" id="flashMessage">
        <?= is_array($warning) ? implode("\n", $warning) : nl2br($warning) ?>
    </div>
<?php endif; ?>

<!-- Import Excel Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-medium text-gray-900" id="importModalTitle">Import Data Pegawai</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-6">
                <div class="space-y-4">
                    <!-- Download Template -->
                    <div class="text-sm text-gray-600 mb-4">
                        <p>Silakan download template Excel terlebih dahulu:</p>
                        <a href="<?= base_url('pegawai/downloadTemplate') ?>" 
                           class="inline-flex items-center mt-2 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-download mr-2"></i> Download Template
                        </a>
                    </div>
                    
                    <!-- Import Form -->
                    <form action="<?= base_url('pegawai/import') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="space-y-4">
                            <div>
                                <label for="excel_file" class="block text-sm font-medium text-gray-700">Pilih File Excel</label>
                                <input type="file" 
                                       id="excel_file"
                                       name="excel_file" 
                                       accept=".xlsx"
                                       class="mt-1 block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-md file:border-0
                                              file:text-sm file:font-medium
                                              file:bg-indigo-50 file:text-indigo-700
                                              hover:file:bg-indigo-100"
                                       required>
                            </div>
                            <div class="flex justify-end space-x-3 mt-4">
                                <button type="button" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                                        data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-file-import mr-2"></i> Import
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(function() {
            flashMessage.style.display = 'none';
        }, 5000); // Show for 5 seconds for import messages
    }
});
</script>
<?= $this->endSection() ?>
