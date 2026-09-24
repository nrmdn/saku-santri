<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';
require_once __DIR__ . '/../includes/db.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

// 1. Verifikasi CSRF Token
verify_csrf_token($_POST['csrf_token'] ?? '', true);

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$ip       = get_client_ip();

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Silakan masukkan username dan password.";
    header("Location: login.php");
    exit;
}

// 2. Pemeriksaan Proteksi Brute Force (Maksimal 5x gagal dalam 15 menit)
$rate_limit = check_login_rate_limit($koneksi, $ip, $username, 5, 15);
if ($rate_limit['is_locked']) {
    $menit_sisa = ceil($rate_limit['remaining_seconds'] / 60);
    $_SESSION['login_error'] = "Akses login sementara dikunci demi keamanan karena terlalu banyak percobaan gagal. Silakan coba kembali dalam {$menit_sisa} menit.";
    audit_log($koneksi, 'login_blocked', 'auth', 'users', null, "Percobaan login ditolak karena lockout aktif. User: {$username}");
    header("Location: login.php");
    exit;
}

// 3. Cari data user dengan Prepared Statement
$stmt = mysqli_prepare($koneksi, "SELECT id, username, password, nama_lengkap, role, foto FROM users WHERE username = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['login_error'] = "Terjadi gangguan pada sistem database.";
    header("Location: login.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$login_sukses = false;
$user_data = null;

if ($hasil && mysqli_num_rows($hasil) > 0) {
    $user_data = mysqli_fetch_assoc($hasil);
    // Verifikasi password & migrasi otomatis dari MD5 ke password_hash jika perlu
    if (verify_and_migrate_password($koneksi, $user_data['id'], $password, $user_data['password'])) {
        $login_sukses = true;
    }
}
mysqli_stmt_close($stmt);

if ($login_sukses && $user_data) {
    // 4. Bersihkan counter gagal login
    clear_login_attempts($koneksi, $ip, $username);

    // 5. Mencegah Serangan Session Fixation
    session_regenerate_id(true);

    // 6. Buat Session Aman
    $_SESSION['login']         = true;
    $_SESSION['id_admin']      = (int)$user_data['id'];
    $_SESSION['username']      = $user_data['username'];
    $_SESSION['nama_lengkap']  = $user_data['nama_lengkap'];
    $_SESSION['role']          = $user_data['role'] ?? 'admin';
    $_SESSION['last_activity'] = time();

    // 7. Catat Audit Log Keberhasilan Login
    audit_log($koneksi, 'login_success', 'auth', 'users', $user_data['id'], "Pengurus {$username} berhasil login ke sistem.");

    header("Location: dashboard.php");
    exit;
} else {
    // 8. Catat kegagalan login untuk Brute Force Protection
    record_failed_login($koneksi, $ip, $username);
    audit_log($koneksi, 'login_failed', 'auth', 'users', null, "Percobaan login gagal untuk username: {$username}");

    // Pesan error umum (Generic Error) untuk mencegah Account Enumeration
    $_SESSION['login_error'] = "Username atau password salah.";
    header("Location: login.php");
    exit;
}