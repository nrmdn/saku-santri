<?php 
require_once __DIR__ . '/../includes/auth.php';
require_permission('data_santri');
require '../config/koneksi.php';
include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<!-- KONTEN UTAMA DIMULAI -->
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Data Santri</h2>
        
        <!-- BLOKADE FRONTEND: Sembunyikan Tombol Tambah & Import untuk Admin/Bendahara -->
        <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="fas fa-file-excel me-2"></i>Import Excel
            </button>
            <button type="button" class="btn-custom btn-custom-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-2"></i>Tambah Santri
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (isset($_SESSION['santri_message'])): ?>
        <div class="alert alert-<?= $_SESSION['santri_status']; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= $_SESSION['santri_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
            unset($_SESSION['santri_message']);
            unset($_SESSION['santri_status']);
        ?>
    <?php endif; ?>
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                    <table id="tabelSantri" class="table table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="8%" class="text-center">Foto</th>
                            <th width="12%" class="text-center">NIS</th>
                            <th width="20%" class="text-center">Nama Lengkap</th>
                            <th width="10%" class="text-center">Jenis Kelamin</th>
                            <th width="auto" class="text-center">Saldo Aktif</th>
                            <th width="auto" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php
                        $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
                        $search_safe = mysqli_real_escape_string($koneksi, $search);

                        if ($search !== '') {
                            $query = "SELECT * FROM santri WHERE deleted_at IS NULL AND (nama LIKE '%$search_safe%' OR nis LIKE '%$search_safe%') ORDER BY nama ASC";
                        } else {
                            $query = "SELECT * FROM santri WHERE deleted_at IS NULL ORDER BY nama ASC";
                        }

                        $hasil = mysqli_query($koneksi, $query);
                        $no = 1;

                        if(mysqli_num_rows($hasil) > 0) {
                            while($row = mysqli_fetch_assoc($hasil)) {
                            $foto_santri = (!empty($row['foto'])) ? $row['foto'] : 'default.png';
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center">
                                <img src="../assets/images/<?= htmlspecialchars($foto_santri); ?>" alt="Foto" width="45" height="45" class="rounded-circle shadow-sm" style="object-fit: cover;">
                            </td>
                            <td class="text-center"><?= htmlspecialchars($row['nis']); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td class="text-center"><?= ($row['jk'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td class="fw-bold text-success">Rp <?= number_format($row['saldo'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    
                                    <a href="detail_santri.php?id=<?= $row['id']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-info text-white shadow-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="cetak_qr.php?id=<?= $row['id']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-dark text-white shadow-sm" title="Cetak QR Code">
                                        <i class="fas fa-qrcode"></i>
                                     </a>

                                    <a href="cetak_kartu.php?id=<?= $row['id']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-secondary text-white shadow-sm" title="Cetak Kartu">
                                        <i class="fas fa-id-card"></i>
                                    </a>

                                    <!-- BLOKADE FRONTEND: Sembunyikan Tombol Edit & Hapus untuk Admin/Bendahara -->
                                    <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
                                    <button class="btn btn-sm btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id']; ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $row['id']; ?>" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>

                        <!-- BLOK MODAL: HANYA DIRENDER JIKA SUPERADMIN -->
                        <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
                        
                        <!-- MODAL EDIT SANTRI -->
                        <div class="modal fade" id="modalEdit<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Data Santri</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="proses_santri.php" method="POST" enctype="multipart/form-data">
                                        <?= csrf_field(); ?>
                                        <div class="modal-body p-4 text-start">
                                            <input type="hidden" name="aksi" value="edit">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($foto_santri); ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-6 border-end">
                                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informasi Utama</h6>
                                                    <div class="mb-3 text-center">
                                                        <label class="form-label fw-bold text-secondary d-block small">Foto Saat Ini</label>
                                                        <img src="../assets/images/<?= htmlspecialchars($foto_santri); ?>" width="90" height="90" class="rounded-circle shadow-sm mb-2" style="object-fit: cover;">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Ganti Foto Profil</label>
                                                        <input type="file" class="form-control form-control-sm" name="foto_baru" accept="image/png, image/jpeg, image/jpg">
                                                        <small class="text-muted" style="font-size: 11px;">Biarkan kosong jika tidak diganti.</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">NIS (Nomor Induk Santri)</label>
                                                        <input type="number" oninput="javascript: if (this.value.length > 7) this.value = this.value.slice(0, 7);" inputmode="numeric" pattern="[0-9]{7}" class="form-control" name="nis" value="<?= htmlspecialchars($row['nis']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Nama Lengkap</label>
                                                        <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($row['nama']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Jenis Kelamin</label>
                                                        <select class="form-select" name="jk" required>
                                                            <option value="L" <?= ($row['jk'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                                            <option value="P" <?= ($row['jk'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-success mb-3 border-bottom pb-2">Data Pribadi & Orang Tua</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Tempat, Tanggal Lahir</label>
                                                        <input type="text" class="form-control" name="ttl" value="<?= htmlspecialchars($row['ttl'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Alamat Lengkap</label>
                                                        <textarea class="form-control" name="alamat" rows="2" required><?= htmlspecialchars($row['alamat'] ?? ''); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Nama Orang Tua / Wali</label>
                                                        <input type="text" class="form-control" name="nama_ortu" value="<?= htmlspecialchars($row['nama_ortu'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">No. WhatsApp / HP</label>
                                                        <input type="number" oninput="javascript: if (this.value.length > 13) this.value = this.value.slice(0, 13);" inputmode="numeric" pattern="[0-9]{13}" class="form-control" name="no_hp_ortu" value="<?= htmlspecialchars($row['no_hp_ortu'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-secondary small">Email Orang Tua (Akses Web)</label>
                                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($row['email'] ?? ''); ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn-custom btn-custom-outline-primary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn-custom btn-custom-outline-secondary">
                                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL HAPUS SANTRI -->
                        <div class="modal fade" id="modalHapus<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title text-white fw-bold"><i class="fas fa-trash-alt me-2"></i>Hapus Santri</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="proses_santri.php" method="POST">
                                        <?= csrf_field(); ?>
                                        <div class="modal-body p-4 text-start">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <p>Apakah Anda yakin ingin menghapus data santri bernama <strong><?= htmlspecialchars($row['nama']); ?></strong>?</p>
                                            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i> Peringatan: Semua riwayat transaksi milik santri ini juga berpotensi hilang.</div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn-custom btn-custom-outline-primary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-2"></i>Ya, Hapus Data</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endif; // Akhir blok khusus Superadmin ?>

                        <?php 
                            } 
                        } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data santri. Silakan tambahkan data.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- BLOK MODAL LUAR: HANYA DIRENDER JIKA SUPERADMIN -->
<?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>

<!-- MODAL TAMBAH SANTRI -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-user-plus me-2"></i>Tambah Data Santri</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses_santri.php" method="POST" enctype="multipart/form-data">
                  <?= csrf_field(); ?>
                <div class="modal-body p-4">
                    <input type="hidden" name="aksi" value="tambah">
                    
                    <div class="row">
                        <!-- Bagian Kiri -->
                        <div class="col-md-6 border-end">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Foto Profil (Opsional)</label>
                                <input type="file" class="form-control" name="foto" accept="image/png, image/jpeg, image/jpg">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">NIS (Nomor Induk Santri)<span class="text-danger">*</span></label>
                                <input type="number" oninput="javascript: if (this.value.length > 7) this.value = this.value.slice(0, 7);" inputmode="numeric" pattern="[0-9]{7}" class="form-control" name="nis" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Nama Lengkap<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Jenis Kelamin<span class="text-danger">*</span></label>
                                <select class="form-select" name="jk" required>
                                    <option value="" selected disabled>-- Pilih Jenis Kelamin --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bagian Kanan -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Tempat, Tanggal Lahir<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ttl" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Alamat Lengkap<span class="text-danger">*</span></label>
                                <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap santri..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Nama Orang Tua / Wali<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_ortu" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">No. WhatsApp / HP Ortu<span class="text-danger">*</span></label>
                                <input type="number" oninput="javascript: if (this.value.length > 13) this.value = this.value.slice(0, 13);" inputmode="numeric" pattern="[0-9]{13}" class="form-control" name="no_hp_ortu" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Email Orang Tua <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn-custom btn-custom-outline-primary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-custom btn-custom-outline-secondary">
                        <i class="fas fa-save me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-file-excel me-2"></i>Import Data Santri</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses_import_santri.php" method="POST" enctype="multipart/form-data">
                  <?= csrf_field(); ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Langkah Import:</h6>
                        <ol class="mb-0 ps-3">
                            <li>Download template Excel.</li>
                            <li>Isi data santri tanpa mengubah format baris judul (header).</li>
                            <li>Simpan, lalu upload file pada kolom di bawah.</li>
                        </ol>
                    </div>
                    
                    <div class="text-center mb-4">
                    <a href="Template_Data_Santri.xlsx" download="Template_Data_Santri.xlsx" class="btn btn-outline-success">
                        <i class="fas fa-download me-2"></i>Download Template Excel (.xlsx)
                    </a>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Pilih File Excel (.xls / .xlsx)</label>
                        <input type="file" class="form-control" name="file_excel" accept=".xls,.xlsx" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn-custom btn-custom-outline-primary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-custom btn-custom-outline-secondary">
                        <i class="fas fa-upload me-2"></i>Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; // Akhir blok khusus Superadmin ?>

<!-- SCRIPT AKTIVASI DATATABLES -->
<script>
    $(document).ready(function() {
        $('#tabelSantri').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            "pageLength": 10
        });
    });
</script>
<!-- KONTEN UTAMA SELESAI -->
<?php include 'layout/footer.php'; ?>
