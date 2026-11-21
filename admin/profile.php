<?php 
require_once __DIR__ . '/../includes/auth.php';
require '../config/koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user yang sedang login dari database
$id_admin = $_SESSION['id_admin'];
$query = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($query, 'i', $id_admin);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$user = mysqli_fetch_assoc($result);

// Set foto bawaan jika kosong
$foto = (!empty($user['foto'])) ? $user['foto'] : 'default.png';

include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Profil Saya</h2>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 text-center pb-4">
                <div class="card-body mt-4">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="../assets/images/<?= htmlspecialchars($foto); ?>" alt="Foto Profil" class="rounded-circle shadow border border-3 border-success" width="150" height="150" style="object-fit: cover; background-color: #f1f5f9;">
                    </div>
                    
                    <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['nama_lengkap']); ?></h4>
                    <p class="text-muted mb-3">@<?= htmlspecialchars($user['username']); ?></p>
                    
                    <?php if($user['role'] == 'superadmin'): ?>
                        <span class="badge bg-primary px-4 py-2 rounded-pill shadow-sm fs-6"><i class="fas fa-user-shield me-2"></i>SUPERADMIN</span>
                    <?php else: ?>
                        <span class="badge bg-success px-4 py-2 rounded-pill shadow-sm fs-6"><i class="fas fa-user-tie me-2"></i>ADMIN (BENDAHARA)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-success"><i class="fas fa-user-edit me-2"></i>Edit Data Profil</h5>
                    <hr class="text-muted mb-0">
                </div>
                
                <div class="card-body p-4">
                    <form action="proses_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($foto); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Nama Lengkap</label>
                            <input type="text" class="form-control rounded-3" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Username</label>
                            <input type="text" class="form-control rounded-3" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Ganti Foto Profil (Opsional)</label>
                            <input type="file" class="form-control rounded-3" name="foto_baru" accept="image/png, image/jpeg, image/jpg">
                            <small class="text-muted" style="font-size: 12px;">*Biarkan kosong jika tidak ingin mengubah foto profil Anda.</small>
                        </div>

                        <div class="alert alert-warning border-0 shadow-sm mt-4 rounded-3">
                            <h6 class="fw-bold mb-1"><i class="fas fa-key me-2"></i>Keamanan (Ganti Password)</h6>
                            <small class="mb-2 d-block">Isi kolom di bawah ini <b>HANYA</b> jika Anda ingin mengganti password. Jika tidak ingin mengganti, biarkan kosong.</small>
                            <input type="password" class="form-control rounded-3 mb-2" name="password_baru" placeholder="Masukkan Password Baru" minlength="6">
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>