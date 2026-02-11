<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Edit Ruangan</h2>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
    <form action="<?= base_url('ruangan/update/' . $ruangan['id']) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="space-y-4">
            <div>
                <label for="nama_ruangan" class="block text-sm font-medium text-gray-700">Nama Ruangan</label>
                <input type="text" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       id="nama_ruangan" 
                       name="nama_ruangan"
                       value="<?= old('nama_ruangan', $ruangan['nama_ruangan']) ?>"
                       required>
            </div>
            <div>
                <label for="tipe" class="block text-sm font-medium text-gray-700">Tipe</label>
                <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                        id="tipe" 
                        name="tipe" 
                        required>
                    <option value="Online" <?= old('tipe', $ruangan['tipe']) === 'Online' ? 'selected' : '' ?>>Online</option>
                    <option value="Offline" <?= old('tipe', $ruangan['tipe']) === 'Offline' ? 'selected' : '' ?>>Offline</option>
                    <option value="Hybrid" <?= old('tipe', $ruangan['tipe']) === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                </select>
            </div>
            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1" <?= old('is_active', $ruangan['is_active']) ? 'checked' : '' ?> class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Aktif
                </label>
            </div>
            <div class="flex justify-end space-x-3">
                <a href="<?= base_url('ruangan') ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
