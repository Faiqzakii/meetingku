<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Edit Pegawai</h2>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
    <form action="<?= base_url('pegawai/update/' . $pegawai['id']) ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        <div class="space-y-4">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       id="nama" 
                       name="nama"
                       value="<?= old('nama', $pegawai['nama']) ?>"
                       required>
            </div>
            <div>
                <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       id="nip" 
                       name="nip"
                       value="<?= old('nip', $pegawai['nip']) ?>"
                       required>
            </div>
            <div>
                <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       id="no_hp" 
                       name="no_hp"
                       value="<?= old('no_hp', $pegawai['no_hp'] ?? '') ?>"
                       placeholder="Contoh: 08123456789">
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       id="username" 
                       name="username"
                       value="<?= old('username', $pegawai['username']) ?>"
                       required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       id="password" 
                       name="password">
            </div>
            <div>
                <label for="is_admin" class="block text-sm font-medium text-gray-700">Role</label>
                <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                        id="is_admin" 
                        name="is_admin" 
                        required>
                    <option value="false" <?= old('is_admin', $pegawai['is_admin']) ? '' : 'selected' ?>>Pegawai</option>
                    <option value="true" <?= old('is_admin', $pegawai['is_admin']) ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="flex items-center space-x-6">
                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="checkbox" name="terima_notif_offline" value="true" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" <?= old('terima_notif_offline', $pegawai['terima_notif_offline']) ? 'checked' : '' ?>>
                    Terima Notifikasi Meeting Offline
                </label>
                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="checkbox" name="terima_notif_zoom" value="true" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" <?= old('terima_notif_zoom', $pegawai['terima_notif_zoom']) ? 'checked' : '' ?>>
                    Terima Notifikasi Meeting Zoom
                </label>
            </div>
            <div class="flex justify-end space-x-3">
                <a href="<?= base_url('pegawai') ?>" 
                   class="btn-outline-custom" style="padding:0.5rem 1rem;">
                    Batal
                </a>
                <button type="submit" class="btn-primary-gradient">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </form>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="mt-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
