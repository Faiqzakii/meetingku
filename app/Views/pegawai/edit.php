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
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                       id="nama" 
                       name="nama"
                       value="<?= old('nama', $pegawai['nama']) ?>"
                       required>
            </div>
            <div>
                <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                       id="nip" 
                       name="nip"
                       value="<?= old('nip', $pegawai['nip']) ?>"
                       required>
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                       id="username" 
                       name="username"
                       value="<?= old('username', $pegawai['username']) ?>"
                       required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                       id="password" 
                       name="password">
            </div>
            <div>
                <label for="is_admin" class="block text-sm font-medium text-gray-700">Role</label>
                <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        id="is_admin" 
                        name="is_admin" 
                        required>
                    <option value="false" <?= old('is_admin', $pegawai['is_admin']) ? '' : 'selected' ?>>Pegawai</option>
                    <option value="true" <?= old('is_admin', $pegawai['is_admin']) ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <a href="<?= base_url('pegawai') ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Simpan
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
