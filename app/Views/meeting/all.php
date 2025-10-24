<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Semua Kegiatan</h2>
        <form method="GET" action="<?= base_url('meeting/all') ?>" class="flex items-center space-x-2">
            <div>
                <label for="start" class="block text-sm font-medium text-gray-700">Start</label>
                <input type="date" id="start" name="start" value="<?= esc($start) ?>" class="mt-1 block w-48 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
            <div>
                <label for="end" class="block text-sm font-medium text-gray-700">End</label>
                <input type="date" id="end" name="end" value="<?= esc($end) ?>" class="mt-1 block w-48 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
            <div class="pt-6">
                <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($meetings)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada kegiatan pada rentang waktu ini</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($meetings as $meeting): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-normal break-words text-sm font-medium text-gray-900">
                                    <?= esc($meeting['nama_keg']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc($meeting['nama_ruangan']) ?> (<?= esc($meeting['tipe']) ?>)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                                    <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?= $meeting['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($meeting['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= strtoupper($meeting['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                    <button type="button" class="expand-trigger inline-flex gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 justify-end">
                                        Aksi
                                        <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr class="details-row hidden">
                                <td colspan="5" class="px-6 py-4 whitespace-normal break-words text-sm">
                                    <div class="flex items-center space-x-4 justify-end">
                                        <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                            <i class="fas fa-edit mr-2"></i> Edit
                                        </a>
                                        <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');" class="inline-block">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                <i class="fas fa-trash-alt mr-2"></i> Hapus
                                            </button>
                                        </form>
                                        <?php if ($meeting['status'] === 'pending'): ?>
                                        <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                <i class="fas fa-check mr-2"></i> Setuju
                                            </button>
                                        </form>
                                        <form action="<?= base_url('meeting/status/' . $meeting['id']) ?>" method="POST" class="inline-block">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="text-gray-700 inline-flex items-center px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                                <i class="fas fa-times mr-2"></i> Tolak
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.expand-trigger');
        if (!btn) return;
        var row = btn.closest('tr');
        if (!row) return;
        var details = row.nextElementSibling;
        if (!details || !details.classList.contains('details-row')) return;
        document.querySelectorAll('.details-row').forEach(function(dr){ if (!dr.classList.contains('hidden')) dr.classList.add('hidden'); });
        details.classList.toggle('hidden');
    });
});
</script>
<?= $this->endSection() ?>
