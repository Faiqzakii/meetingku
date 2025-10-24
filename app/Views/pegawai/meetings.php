<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Meeting - <?= esc($pegawai['nama']) ?></h2>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="<?= base_url('pegawai') ?>" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
    <?php if (empty($meetings)): ?>
        <p class="text-gray-500 text-center py-4">Tidak ada meeting terjadwal</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($meetings as $meeting): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-900"><?= esc($meeting['nama_keg']) ?></h3>
                    <div class="mt-2 text-sm text-gray-500">
                        <p class="mb-1">
                            <i class="fas fa-clock mr-2"></i>
                            <?= date('l, d F Y H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                            <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-building mr-2"></i>
                            Ruangan: <?= esc($meeting['nama_ruangan']) ?>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-info-circle mr-2"></i>
                            Status: 
                            <span class="font-medium <?= $meeting['status'] === 'pending' ? 'text-yellow-600' : ($meeting['status'] === 'approved' ? 'text-green-600' : 'text-red-600') ?>">
                                <?= strtoupper($meeting['status']) ?>
                            </span>
                        </p>
                        <?php if ($isAdmin): ?>
                            <form action="<?= base_url('meeting/' . $meeting['id'] . '/status') ?>" 
                                  method="POST" 
                                  class="mt-2 space-x-2">
                                <?= csrf_field() ?>
                                <button type="submit" 
                                        name="status" 
                                        value="approved"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-check mr-1"></i> Setuju
                                </button>
                                <button type="submit" 
                                        name="status" 
                                        value="rejected"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i class="fas fa-times mr-1"></i> Tolak
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
