<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';

// Pastikan metode POST dan user sudah login
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_admin'])) {
    header('Location: profile.php');
    exit;
}

verify_csrf_token($_POST['csrf_token'] ?? '', true);

$id_admin     = $_SESSION['id_admin'];
$nama_lengkap = trim(mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']));
$username     = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
$password_baru= $_POST['password_baru'] ?? '';
$foto_lama    = $_POST['foto_lama'] ?? 'default.png';

$foto_nama    = $foto_lama;
$query_pass   = "";

// 1. CEK USERNAME GANDA (Jangan sampai sama dengan admin lain)
$cek_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' AND id != '$id_admin'");
if(mysqli_num_rows($cek_user) > 0) {
    $_SESSION['pengguna_status'] = 'danger';
    $_SESSION['pengguna_message'] = 'Gagal menyimpan! Username tersebut sudah dipakai oleh admin lain.';
    header('Location: profile.php');
    exit;
}

// 2. PROSES UPLOAD FOTO (Jika ada foto baru)
if (isset($_FILES['foto_baru']) && $_FILES['foto_baru']['error'] === UPLOAD_ERR_OK) {
    $file_tmp  = $_FILES['foto_baru']['tmp_name'];
    $file_name = $_FILES['foto_baru']['name'];
    $file_size = $_FILES['foto_baru']['size'];
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    
    // Validasi Ekstensi & Ukuran (Maksimal 2MB)
    if (in_array($ext, $allowed_ext) && $file_size <= 2000000) {
        // Buat nama acak biar tidak menimpa foto lain
        $foto_baru = "admin_" . time() . "_" . uniqid() . "." . $ext;
        $dir_upload = __DIR__ . '/../assets/images/';
        
        if (move_uploaded_file($file_tmp, $dir_upload . $foto_baru)) {
            $foto_nama = $foto_baru; // Pakai foto baru
            // Hapus foto lama jika bukan default
            if ($foto_lama !== 'default.png' && file_exists($dir_upload . $foto_lama)) {
                unlink($dir_upload . $foto_lama);
            }
        }
    } else {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Format foto harus JPG/PNG dan maksimal 2MB.';
        header('Location: profile.php');
        exit;
    }
}

// 3. PROSES PASSWORD BARU (Jika diisi)
if (!empty($password_baru)) {
    // Diasumsikan password Anda menggunakan hash MD5 atau password_hash
    // Ganti MD5 jika aplikasi Anda menggunakan password_hash!
    $password_hashed = md5($password_baru); 
    // Jika Anda memakai bcrypt: $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
    
    $query_pass = ", password = '$password_hashed'";
}

// 4. UPDATE DATABASE
$query_update = "UPDATE users SET 
                 nama_lengkap = '$nama_lengkap', 
                 username = '$username', 
                 foto = '$foto_nama' 
                 $query_pass 
                 WHERE id = '$id_admin'";

if (mysqli_query($koneksi, $query_update)) {
    // Perbarui sesi dengan data terbaru agar tampilan atas (Header) ikut berubah
    $_SESSION['nama_lengkap'] = $nama_lengkap;
    $_SESSION['username']     = $username;
    
    $_SESSION['pengguna_status'] = 'success';
    $_SESSION['pengguna_message'] = 'Profil Anda berhasil diperbarui!';
} else {
    $_SESSION['pengguna_status'] = 'danger';
    $_SESSION['pengguna_message'] = 'Terjadi kesalahan sistem saat menyimpan profil.';
}

header('Location: profile.php');
exit;