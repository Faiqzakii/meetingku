<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="sm:flex sm:items-center sm:justify-between mt-2 mb-6">
    <div>
        <h2 class="section-title">
            <i class="fas fa-users mr-2 text-orange-500"></i>Data Pegawai
        </h2>
        <p class="text-sm text-gray-500 mt-1">Kelola data pegawai dalam sistem</p>
    </div>
    <?php if (session()->get('is_admin')): ?>
    <div class="flex flex-wrap items-center gap-3 mt-4 sm:mt-0">
        <button type="button" class="btn-outline-custom" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel"></i> Import Excel
        </button>
        <button type="button" class="btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#addPegawaiModal">
            <i class="fas fa-plus"></i> Tambah Pegawai
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="card-modern">
    <div class="overflow-x-auto">
        <table class="min-w-full table-modern">
            <thead>
                <tr>
                    <th style="width:50px;" class="text-center">#</th>
                    <th class="text-left">Nama</th>
                    <th class="text-left">NIP</th>
                    <th class="text-left">Role</th>
                    <?php if (session()->get('is_admin')): ?>
                        <th class="text-right" style="width:120px;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pegawai)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <p class="empty-state-title">Belum ada data pegawai</p>
                                <p class="empty-state-desc">Tambahkan pegawai pertama atau import dari Excel</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pegawai as $i => $p): ?>
                        <tr>
                            <td class="text-center text-gray-400 text-xs font-mono"><?= $i + 1 ?></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="nav-user-avatar" style="width:2rem;height:2rem;font-size:0.625rem; flex-shrink:0;">
                                        <?= strtoupper(substr($p['nama'], 0, 2)) ?>
                                    </div>
                                    <span class="font-semibold text-gray-900"><?= esc($p['nama']) ?></span>
                                </div>
                            </td>
                            <td class="text-gray-600 font-mono text-sm"><?= esc($p['nip']) ?></td>
                            <td>
                                <?php if (($p['role'] ?? '') === 'admin'): ?>
                                    <span class="badge-status badge-approved" style="font-size:0.6875rem;">Admin</span>
                                <?php else: ?>
                                    <span class="badge-status" style="background:#f1f5f9; color:#64748b; font-size:0.6875rem;">User</span>
                                <?php endif; ?>
                            </td>
                            <?php if (session()->get('is_admin')): ?>
                                <td class="text-right">
                                    <button type="button" class="expand-trigger btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php if (session()->get('is_admin')): ?>
                        <tr class="details-row hidden">
                            <td colspan="5" style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                                <div class="flex items-center gap-2 justify-end py-1">
                                    <a href="<?= base_url('pegawai/edit/' . $p['id']) ?>" class="btn-outline-custom" style="padding:0.375rem 0.75rem; font-size:0.75rem;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="<?= base_url('pegawai/delete/' . $p['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pegawai ini?');" class="inline-block">
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

<!-- Add Pegawai Modal -->
<div class="modal fade" id="addPegawaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus mr-2 text-orange-500"></i>Tambah Pegawai
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="<?= base_url('pegawai/create') ?>" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="space-y-4">
                        <div>
                            <label for="add_nama" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-user mr-1 text-orange-400 text-xs"></i> Nama
                            </label>
                            <input type="text" class="input-modern" id="add_nama" name="nama" placeholder="Nama lengkap" required>
                        </div>
                        <div>
                            <label for="add_nip" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-id-card mr-1 text-orange-400 text-xs"></i> NIP
                            </label>
                            <input type="text" class="input-modern" id="add_nip" name="nip" placeholder="Nomor Induk Pegawai" required>
                        </div>
                        <div>
                            <label for="add_no_hp" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-phone mr-1 text-orange-400 text-xs"></i> Nomor Telepon
                            </label>
                            <input type="text" class="input-modern" id="add_no_hp" name="no_hp" placeholder="Contoh: 08123456789">
                        </div>
                        <div>
                            <label for="add_username" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-at mr-1 text-orange-400 text-xs"></i> Username
                            </label>
                            <input type="text" class="input-modern" id="add_username" name="username" placeholder="Username untuk login" required>
                        </div>
                        <div>
                            <label for="add_password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-lock mr-1 text-orange-400 text-xs"></i> Password
                            </label>
                            <input type="password" class="input-modern" id="add_password" name="password" placeholder="Password" required>
                        </div>
                        <div>
                            <label for="add_role" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-shield-halved mr-1 text-orange-400 text-xs"></i> Role
                            </label>
                            <select class="input-modern dropdown-modern" id="add_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-excel mr-2 text-green-500"></i>Import Data Pegawai
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="<?= base_url('pegawai/import') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="p-4 rounded-lg border-2 border-dashed border-gray-300 text-center hover:border-orange-300 transition-colors cursor-pointer" id="dropzone">
                        <i class="fas fa-cloud-arrow-up text-3xl text-gray-300 mb-3"></i>
                        <p class="text-sm font-medium text-gray-600">Pilih file Excel (.xlsx, .xls)</p>
                        <p class="text-xs text-gray-400 mt-1">Atau drag & drop file ke sini</p>
                        <input type="file" name="file" accept=".xlsx,.xls" required class="mt-3 text-sm" style="max-width:250px;">
                    </div>
                </div>
                <div class="modal-footer flex justify-end gap-3">
                    <button type="button" class="btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-gradient">
                        <i class="fas fa-upload"></i> Upload
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
