<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mt-2 mb-6">
    <div>
        <h2 class="section-title">
            <i class="fas fa-door-open mr-2 text-orange-500"></i>Data Ruangan
        </h2>
        <p class="text-sm text-gray-500 mt-1">Kelola ruangan dan jadwalnya</p>
    </div>
    <?php if (session()->get('is_admin')): ?>
    <div class="mt-4 sm:mt-0">
        <button type="button" class="btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#addRuanganModal">
            <i class="fas fa-plus"></i> Tambah Ruangan
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($ruangan)): ?>
    <div class="card-modern p-8">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-building"></i>
            </div>
            <p class="empty-state-title">Belum ada data ruangan</p>
            <p class="empty-state-desc">Tambahkan ruangan pertama untuk memulai</p>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($ruangan as $r): ?>
            <div class="card-modern group transition-all hover:shadow-lg hover:-translate-y-0.5">
                <!-- Top gradient accent by type -->
                <?php 
                    $typeGradient = 'linear-gradient(135deg, #6366f1, #8b5cf6)';
                    $typeIcon = 'fa-building';
                    $typeLabel = 'Offline';
                    if ($r['tipe'] === 'Online') {
                        $typeGradient = 'linear-gradient(135deg, #06b6d4, #3b82f6)';
                        $typeIcon = 'fa-globe';
                        $typeLabel = 'Online';
                    } else if ($r['tipe'] === 'Hybrid') {
                        $typeGradient = 'linear-gradient(135deg, #8b5cf6, #ec4899)';
                        $typeIcon = 'fa-arrows-alt';
                        $typeLabel = 'Hybrid';
                    }
                ?>
                <div style="height:4px; background: <?= $typeGradient ?>;"></div>
                
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div style="width:2.5rem;height:2.5rem;border-radius:0.625rem;background:<?= $typeGradient ?>;display:flex;align-items:center;justify-content:center;color:white;font-size:0.875rem;">
                                <i class="fas <?= $typeIcon ?>"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900"><?= esc($r['nama_ruangan']) ?></h3>
                                <span class="text-xs font-medium text-gray-500"><?= $typeLabel ?></span>
                            </div>
                        </div>
                        <div>
                            <?php if (($r['status'] ?? 'active') === 'active'): ?>
                                <span class="badge-status badge-approved" style="font-size:0.6875rem;">Aktif</span>
                            <?php else: ?>
                                <span class="badge-status badge-rejected" style="font-size:0.6875rem;">Nonaktif</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <a href="<?= base_url('meeting/room/' . $r['id']) ?>" 
                           class="btn-outline-custom flex-1 justify-center" style="padding:0.5rem 0.75rem; font-size:0.8125rem;">
                            <i class="fas fa-calendar-alt"></i> Jadwal
                        </a>
                        <?php if (session()->get('is_admin')): ?>
                            <a href="<?= base_url('ruangan/edit/' . $r['id']) ?>" 
                               class="btn-outline-custom" style="padding:0.5rem 0.75rem; font-size:0.8125rem;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="<?= base_url('ruangan/delete/' . $r['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');" class="inline-block">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-outline-custom" style="padding:0.5rem 0.75rem; font-size:0.8125rem; color:#ef4444; border-color:#fecaca;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Ruangan Modal -->
<div class="modal fade" id="addRuanganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2 text-orange-500"></i>Tambah Ruangan
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="<?= base_url('ruangan/create') ?>" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="space-y-4">
                        <div>
                            <label for="add_nama_ruangan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-door-open mr-1 text-orange-400 text-xs"></i> Nama Ruangan
                            </label>
                            <input type="text" class="input-modern" id="add_nama_ruangan" name="nama_ruangan" placeholder="Nama ruangan" required>
                        </div>
                        <div>
                            <label for="add_tipe" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-tags mr-1 text-orange-400 text-xs"></i> Tipe
                            </label>
                            <select class="input-modern dropdown-modern" id="add_tipe" name="tipe" required>
                                <option value="Offline">Offline</option>
                                <option value="Online">Online</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div>
                            <label for="add_status" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-toggle-on mr-1 text-orange-400 text-xs"></i> Status
                            </label>
                            <select class="input-modern dropdown-modern" id="add_status" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex justify-end gap-3">
                    <button type="button" class="btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-gradient">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Flash Messages -->
<div class="toast-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="toast-notification toast-success" id="flashToast">
            <div class="toast-icon"><i class="fas fa-check"></i></div>
            <span><?= session()->getFlashdata('success') ?></span>
            <button class="toast-close" onclick="this.parentElement.classList.add('toast-hiding');setTimeout(()=>this.parentElement.remove(),300)"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="toast-notification toast-error" id="flashToast">
            <div class="toast-icon"><i class="fas fa-exclamation"></i></div>
            <span><?= session()->getFlashdata('error') ?></span>
            <button class="toast-close" onclick="this.parentElement.classList.add('toast-hiding');setTimeout(()=>this.parentElement.remove(),300)"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 300); }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
