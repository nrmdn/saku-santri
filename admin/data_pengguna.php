<?php 
require_once __DIR__ . '/../includes/auth.php';
require_permission('data_pengguna');
require '../config/koneksi.php';

// =================================================================
// BLOKADE KEAMANAN DENGAN SWEETALERT2
// =================================================================
if (($_SESSION['role'] ?? '') !== 'superadmin') {
    // Render halaman kosong sementara dengan SweetAlert
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Ditolak</title>
        <!-- Memanggil library SweetAlert2 dari CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>body { background-color: #f1f5f9; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }</style>
    </head>
    <body>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "error",
                    title: "Akses Ditolak!",
                    text: "Hanya Superadmin yang diizinkan mengakses manajemen pengguna.",
                    confirmButtonColor: "#dc3545",
                    confirmButtonText: "<i class=\'fas fa-arrow-left\'></i> Kembali ke Dashboard",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showClass: { popup: "animate__animated animate__fadeInDown" },
                    hideClass: { popup: "animate__animated animate__fadeOutUp" }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "dashboard.php";
                    }
                });
            });
        </script>
    </body>
    </html>';
    exit;
}

include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>



<div class="container-fluid">
    <!-- ALERT NOTIFIKASI PENGGUNA -->
    <?php if (isset($_SESSION['pengguna_message'])): ?>
        <div class="alert alert-<?= $_SESSION['pengguna_status']; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <?= htmlspecialchars($_SESSION['pengguna_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
            unset($_SESSION['pengguna_message']);
            unset($_SESSION['pengguna_status']);
        ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Data Pengguna (Admin)</h2>
        <button type="button" class="btn-custom btn-custom-outline-primary " data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fas fa-user-plus me-2"></i>Tambah Admin
        </button>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="10%" class="text-center">Foto</th>
                            <th>Nama Lengkap</th>
                            <th width="20%">Username</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users ORDER BY nama_lengkap ASC";
                        $hasil = mysqli_query($koneksi, $query);
                        $no = 1;

                        while($row = mysqli_fetch_assoc($hasil)) {
                            // Cek apakah baris ini adalah admin yang sedang login
                            $is_current_user = ($row['id'] == $_SESSION['id_admin']);
                            // Cek foto, jika kosong gunakan default
                            $foto = (!empty($row['foto'])) ? $row['foto'] : 'default.png';
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center">
                                <img src="../assets/images/<?= htmlspecialchars($foto); ?>" alt="Foto" class="rounded-circle shadow-sm" width="50" height="50" style="object-fit: cover;">
                            </td>
                            <td>
                                <?= htmlspecialchars($row['nama_lengkap']); ?>
                                <?php if($is_current_user) { ?>
                                    <span class="badge bg-success ms-2">Anda</span>
                                <?php } ?>
                                <span class="badge bg-primary ms-1"><?= strtoupper($row['role']); ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-warning text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $row['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <?php if(!$is_current_user) { ?>
                                    <button class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalHapusUser<?= $row['id']; ?>">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT PENGGUNA -->
                        <div class="modal fade" id="modalEditUser<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Admin</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    
                                    <form action="proses_pengguna.php" method="POST" enctype="multipart/form-data">
                                        <div class="modal-body p-4 text-start">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="aksi" value="edit">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($foto); ?>">
                                            
                                            <div class="mb-3 text-center">
                                                <label class="form-label fw-bold text-secondary d-block">Foto Saat Ini</label>
                                                <img src="../assets/images/<?= htmlspecialchars($foto); ?>" width="100" height="100" class="rounded-circle shadow-sm mb-2" style="object-fit: cover;">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary">Ganti Foto Profil</label>
                                                <input type="file" class="form-control" name="foto_baru" accept="image/png, image/jpeg, image/jpg">
                                                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti foto.</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary">Nama Lengkap</label>
                                                <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary">Username</label>
                                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($row['username']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary">Pilih Role</label>
                                                <select class="form-select" name="role" required>
                                                    <option value="superadmin" <?= ($row['role'] == 'superadmin') ? 'selected' : ''; ?>>Superadmin</option>
                                                    <option value="admin" <?= ($row['role'] == 'admin') ? 'selected' : ''; ?>>Admin (Bendahara)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary">Password Baru</label>
                                                <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diubah">
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fas fa-save me-2"></i>Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL HAPUS PENGGUNA -->
                        <div class="modal fade" id="modalHapusUser<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title text-white fw-bold"><i class="fas fa-trash-alt me-2"></i>Hapus Admin</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="proses_pengguna.php" method="POST">
                                        <div class="modal-body p-4 text-start">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <p>Yakin ingin menghapus admin <strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong>?</p>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-2"></i>Hapus</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PENGGUNA -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-user-plus me-2"></i>Tambah Admin Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses_pengguna.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="aksi" value="tambah">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Foto Profil (Opsional)</label>
                        <input type="file" class="form-control" name="foto" accept="image/png, image/jpeg, image/jpg">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Pilih Role</label>
                        <select class="form-select" name="role" required>
                            <option value="admin">Admin (Bendahara)</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
