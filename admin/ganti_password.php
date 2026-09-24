<?php 
require_once __DIR__ . '/../includes/auth.php';
require_permission('ganti_password');
require '../config/koneksi.php';
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

$pesan_error  = "";
$pesan_sukses = "";

// ==========================================
// LOGIKA PEMROSESAN GANTI PASSWORD
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn_ganti'])) {
    // 1. Verifikasi CSRF Token
    verify_csrf_token($_POST['csrf_token'] ?? '', true);

    $id_admin   = (int)$_SESSION['id_admin'];
    $pass_lama  = (string)($_POST['password_lama'] ?? '');
    $pass_baru  = (string)($_POST['password_baru'] ?? '');
    $konfirmasi = (string)($_POST['konfirmasi_password'] ?? '');

    // 2. Validasi Kelengkapan & Panjang Password
    if (empty($pass_lama) || empty($pass_baru) || empty($konfirmasi)) {
        $pesan_error = "Harap isi semua kolom password.";
    } elseif (strlen($pass_baru) < 6) {
        $pesan_error = "Password baru minimal terdiri dari 6 karakter.";
    } elseif ($pass_baru !== $konfirmasi) {
        $pesan_error = "Konfirmasi password tidak cocok! Pastikan Anda mengetik password baru dengan benar.";
    } else {
        // 3. Ambil password lama dari database dengan Prepared Statement
        $stmt = mysqli_prepare($koneksi, "SELECT password, username FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_admin);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $data_user = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($data_user) {
                $pass_db = $data_user['password'];

                // 4. Verifikasi kecocokan password lama (Mendukung MD5 legacy & bcrypt)
                $old_pass_valid = false;
                if (password_verify($pass_lama, $pass_db) || md5($pass_lama) === $pass_db) {
                    $old_pass_valid = true;
                }

                if (!$old_pass_valid) {
                    $pesan_error = "Password lama yang Anda masukkan salah!";
                } else {
                    // 5. Hash password baru dengan bcrypt standar industri
                    $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
                    $stmt_up = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt_up) {
                        mysqli_stmt_bind_param($stmt_up, "si", $hash_baru, $id_admin);
                        $sukses = mysqli_stmt_execute($stmt_up);
                        mysqli_stmt_close($stmt_up);

                        if ($sukses) {
                            $pesan_sukses = "Password berhasil diperbarui! Silakan gunakan password baru ini pada login berikutnya.";
                            audit_log($koneksi, 'change_password', 'users', 'users', $id_admin, "Pengurus {$data_user['username']} berhasil mengubah password akun.");
                        } else {
                            $pesan_error = "Terjadi kesalahan sistem database saat memperbarui password.";
                        }
                    }
                }
            } else {
                $pesan_error = "Data pengguna tidak ditemukan.";
            }
        } else {
            $pesan_error = "Gagal memproses permintaan.";
        }
    }
}
?>

<!-- KONTEN UTAMA DIMULAI -->
<div class="container-fluid">
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Ganti Password</h1>
            <p class="page-subtitle">Perbarui kata sandi akun pengurus Anda secara berkala demi keamanan sistem.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    
                    <!-- Area Notifikasi Alert -->
                    <?php if (!empty($pesan_error)) { ?>
                        <div class="alert alert-danger shadow-sm border-0 alert-dismissible fade show rounded-3">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?= e($pesan_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php } ?>

                    <?php if (!empty($pesan_sukses)) { ?>
                        <div class="alert alert-success shadow-sm border-0 alert-dismissible fade show rounded-3">
                            <i class="fas fa-check-circle me-2"></i> <?= e($pesan_sukses); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php } ?>
                    
                    <!-- Form Ganti Password -->
                    <form action="ganti_password.php" method="POST">
                        <?= csrf_field(); ?>

                        <div class="mb-4 pb-3 border-bottom">
                            <label for="password_lama" class="form-label fw-bold text-secondary">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" required placeholder="Masukkan kata sandi saat ini">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_baru" class="form-label fw-bold text-secondary">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" required minlength="6" placeholder="Buat kata sandi baru">
                            <div class="form-text"><i class="fas fa-info-circle me-1"></i> Minimal 6 karakter. Gunakan kombinasi huruf dan angka.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="konfirmasi_password" class="form-label fw-bold text-secondary">Ulangi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required minlength="6" placeholder="Ketik ulang kata sandi baru">
                        </div>
                        
                        <button type="submit" name="btn_ganti" class="btn btn-primary shadow-sm px-4 rounded-pill">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan Password
                        </button>
                    </form>
                    
                </div>
            </div>
        </div>
        
        <!-- Pesan Bantuan UI -->
        <div class="col-md-6 mt-4 mt-md-0">
            <div class="alert alert-light border shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-shield-alt text-success me-2"></i>Panduan Keamanan Kata Sandi</h5>
                <ul class="text-muted small mb-0 d-flex flex-column gap-2" style="line-height: 1.6;">
                    <li>Jangan gunakan kata sandi yang mudah ditebak seperti <code>123456</code> atau nama panggilan.</li>
                    <li>Gunakan kombinasi huruf besar, huruf kecil, dan angka.</li>
                    <li>Hindari membagikan informasi akun kepada pihak luar atau santri.</li>
                    <li>Sistem mengenkripsi password dengan algoritma <strong>Bcrypt (PHP password_hash)</strong> yang tidak dapat didekripsi balik.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- KONTEN UTAMA SELESAI -->

<?php 
include 'layout/footer.php'; 
?>