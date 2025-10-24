<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Jadwal Meeting - <?= esc($ruangan['nama_ruangan']) ?></h2>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="<?= base_url('ruangan') ?>" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
    <?php if (empty($meetings)): ?>
        <p class="text-gray-500 text-center py-4">Tidak ada meeting terjadwal</p>
    <?php else: ?>
        <?php foreach ($meetings as $meeting): ?>
            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900"><?= esc($meeting['nama_keg']) ?></h3>
                <div class="mt-2 text-sm text-gray-500">
                    <p class="mb-1">
                        <i class="fas fa-clock mr-2"></i>
                        <?= date('l, d F Y H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                        <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-info-circle mr-2"></i>
                        Status: 
                        <span class="font-medium <?= $meeting['status'] === 'pending' ? 'text-yellow-600' : 'text-green-600' ?>">
                            <?= strtoupper($meeting['status']) ?>
                        </span>
                    </p>
                    <p>
                        <i class="fas fa-user mr-2"></i>
                        Pegawai: <?= esc($meeting['nama_pegawai'] ?? 'Tidak tersedia') ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
