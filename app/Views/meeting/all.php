<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="mt-2 mb-6">
    <h2 class="section-title">
        <i class="fas fa-list mr-2 text-orange-500"></i>Semua Kegiatan
    </h2>
    <p class="text-sm text-gray-500 mt-1">Lihat semua riwayat meeting berdasarkan periode</p>
</div>

<!-- Filter Card -->
<div class="card-modern p-4 mb-6">
    <form action="<?= base_url('meeting/all') ?>" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="start_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                <i class="fas fa-calendar mr-1 text-orange-400"></i> Dari Tanggal
            </label>
            <input type="date" 
                   class="input-modern" 
                   id="start_date" 
                   name="start_date" 
                   value="<?= esc($startDate ?? date('Y-m-01')) ?>"
                   style="min-width:160px;">
        </div>
        <div>
            <label for="end_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                <i class="fas fa-calendar mr-1 text-orange-400"></i> Sampai Tanggal
            </label>
            <input type="date" 
                   class="input-modern" 
                   id="end_date" 
                   name="end_date" 
                   value="<?= esc($endDate ?? date('Y-m-t')) ?>"
                   style="min-width:160px;">
        </div>
        <button type="submit" class="btn-primary-gradient">
            <i class="fas fa-search"></i> Tampilkan
        </button>
    </form>
</div>

<!-- Meetings Table -->
<div class="card-modern">
    <div class="overflow-x-auto">
        <table class="min-w-full table-modern">
            <thead>
                <tr>
                    <th class="text-left">Nama Kegiatan</th>
                    <th class="text-left">Ruangan</th>
                    <th class="text-left">Waktu</th>
                    <th class="text-left">Status</th>
                    <?php if (session()->get('logged_in')): ?>
                        <th class="text-right" style="width:120px;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($meetings)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <p class="empty-state-title">Tidak ada meeting ditemukan</p>
                                <p class="empty-state-desc">Coba ubah filter tanggal untuk melihat data lainnya</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($meetings as $meeting): ?>
                        <tr>
                            <td class="font-semibold text-gray-900">
                                <?= esc($meeting['nama_keg']) ?>
                            </td>
                            <td class="text-gray-600">
                                <i class="fas fa-door-open text-orange-300 mr-1 text-xs"></i>
                                <?= esc($meeting['nama_ruangan']) ?> 
                                <span class="text-gray-400">(<?= $meeting['tipe'] ?>)</span>
                            </td>
                            <td class="text-gray-600">
                                <i class="fas fa-clock text-orange-300 mr-1 text-xs"></i>
                                <?= date('d M Y H:i', strtotime($meeting['waktu_mulai'])) ?> - 
                                <?= date('H:i', strtotime($meeting['waktu_selesai'])) ?>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= $meeting['status'] ?>">
                                    <?= strtoupper($meeting['status']) ?>
                                </span>
                            </td>
                            <?php if (session()->get('logged_in')): ?>
                                <td class="text-right">
                                    <?php 
                                        $isAdmin = session()->get('is_admin');
                                        $canOwnOrAdmin = ((int)($meeting['pegawai_id'] ?? 0) === (int)session()->get('pegawai_id')) || $isAdmin;
                                    ?>
                                    <?php if ($canOwnOrAdmin): ?>
                                        <button type="button" class="expand-trigger btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php if (session()->get('logged_in') && $canOwnOrAdmin): ?>
                        <tr class="details-row hidden">
                            <td colspan="5" style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                                <?php if ($isAdmin): ?>
                                <div class="px-2 pt-2 pb-1">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5"><i class="fas fa-history mr-1"></i>Log Aktivitas</p>
                                    <div class="space-y-1 mb-2">
                                        <div class="flex items-start gap-2 text-xs text-gray-500">
                                            <i class="fas fa-plus-circle text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                            <span><strong>Di-input</strong> oleh <strong><?= esc($meeting['nama_pegawai'] ?? '-') ?></strong> pada <?= $meeting['created_at'] ? date('d M Y H:i', strtotime($meeting['created_at'])) : '-' ?></span>
                                        </div>
                                        <?php if (!empty($meeting['last_edited_by_name']) && !empty($meeting['last_edited_at'])): ?>
                                        <div class="flex items-start gap-2 text-xs text-gray-500">
                                            <i class="fas fa-pen text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                            <span><strong>Diedit</strong> oleh <strong><?= esc($meeting['last_edited_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['last_edited_at'])) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($meeting['status_changed_by_name']) && !empty($meeting['status_changed_at'])): ?>
                                        <div class="flex items-start gap-2 text-xs text-gray-500">
                                            <i class="fas fa-gavel text-orange-300 mt-0.5" style="min-width:14px;"></i>
                                            <?php 
                                                $statusLabel = $meeting['status'] === 'approved' ? 'Disetujui' : ($meeting['status'] === 'rejected' ? 'Ditolak' : 'Status diubah');
                                            ?>
                                            <span><strong><?= $statusLabel ?></strong> oleh <strong><?= esc($meeting['status_changed_by_name']) ?></strong> pada <?= date('d M Y H:i', strtotime($meeting['status_changed_at'])) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="flex items-center gap-2 justify-end py-1">
                                    <a href="<?= base_url('meeting/edit/' . $meeting['id']) ?>" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="<?= base_url('meeting/delete/' . $meeting['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meeting ini?');" class="inline-block">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem; color:#ef4444; border-color:#fecaca;">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
    // Expandable row toggle
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.expand-trigger');
        if (!btn) return;
        var row = btn.closest('tr');
        if (!row) return;
        var details = row.nextElementSibling;
        if (!details || !details.classList.contains('details-row')) return;
        document.querySelectorAll('.details-row').forEach(function(dr){ if (dr !== details && !dr.classList.contains('hidden')) dr.classList.add('hidden'); });
        details.classList.toggle('hidden');
    });

    // Auto-hide toast
    var toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(function() { toast.classList.add('toast-hiding'); setTimeout(function(){ toast.remove(); }, 300); }, 4000);
    }
});
</script>
<?= $this->endSection() ?>
