<?php
/**
 * SAKUSANTRI AUTHENTICATION & AUTHORIZATION LAYER
 * Session Fixation Protection, RBAC Middleware, Password Migration & Brute Force Lockout
 */

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/audit.php';

/**
 * Memeriksa apakah admin sedang login
 *
 * @return bool
 */
function is_logged_in() {
    return !empty($_SESSION['login']) && !empty($_SESSION['id_admin']);
}

/**
 * Mendapatkan role pengguna saat ini
 *
 * @return string
 */
function current_role() {
    return $_SESSION['role'] ?? 'admin';
}

/**
 * Middleware: Wajibkan login sebelum mengakses halaman
 *
 * @param string $redirect_url
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        header("Location: " . $redirect_url);
        exit;
    }

    // Session Inactivity Timeout (Maksimal 60 menit)
    $timeout_seconds = 3600;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_seconds) {
        secure_logout();
        header("Location: " . $redirect_url . "?pesan=timeout");
        exit;
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Pemetaan izin (Permission Matrix) berdasarkan Role SakuSantri
 *
 * @param string $permission
 * @return bool
 */
function has_permission($permission) {
    $role = current_role();

    // Superadmin memiliki akses ke seluruh modul sistem
    if ($role === 'superadmin') {
        return true;
    }

    $permissions_map = [
        'admin' => [
            'dashboard', 'data_santri', 'data_pengguna', 
            'verifikasi_topup', 'topup_manual', 'pengeluaran', 
            'laporan_cash', 'cetak_kartu', 'view_santri', 'ganti_password'
        ],
        'bendahara' => [
            'dashboard', 'verifikasi_topup', 'topup_manual', 
            'pengeluaran', 'laporan_cash', 'view_santri', 
            'ganti_password'
        ],
        'operator' => [
            'dashboard', 'data_santri', 'view_santri', 'cetak_kartu', 
            'topup_manual', 'view_laporan', 'ganti_password'
        ]
    ];

    $allowed = $permissions_map[$role] ?? [];
    return in_array($permission, $allowed, true);
}

/**
 * Middleware: Wajibkan izin tertentu, cegah Privilege Escalation di server-side
 *
 * @param string $permission
 */
function require_permission($permission) {
    require_login();

    if (!has_permission($permission)) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>403 - Akses Terlarang</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="max-width: 480px;">
                <div class="mb-3 text-warning fs-1"><i class="fas fa-user-shield"></i></div>
                <h4 class="fw-bold text-dark">Akses Terbatas</h4>
                <p class="text-muted small mb-4">
                    Akun Anda dengan hak akses <strong><?= e(strtoupper(current_role())); ?></strong> tidak memiliki izin untuk mengakses fitur atau data ini.
                </p>
                <div>
                    <a href="dashboard.php" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

/**
 * Memeriksa apakah IP atau Username terkena pemblokiran Brute Force
 *
 * @param mysqli $koneksi
 * @param string $ip
 * @param string $username
 * @param int $max_attempts Batas percobaan gagal (default: 5 kali)
 * @param int $lock_minutes Durasi blokir dalam menit (default: 15 menit)
 * @return array ['is_locked' => bool, 'remaining_seconds' => int]
 */
function check_login_rate_limit($koneksi, $ip, $username, $max_attempts = 5, $lock_minutes = 15) {
    // 1. Cek kunci di level akun (users.locked_until)
    $stmt = mysqli_prepare($koneksi, "SELECT locked_until FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($user = mysqli_fetch_assoc($res)) {
            if (!empty($user['locked_until'])) {
                $locked_until_ts = strtotime($user['locked_until']);
                $now_ts = time();
                if ($locked_until_ts > $now_ts) {
                    mysqli_stmt_close($stmt);
                    return [
                        'is_locked' => true,
                        'remaining_seconds' => ($locked_until_ts - $now_ts)
                    ];
                }
            }
        }
        mysqli_stmt_close($stmt);
    }

    // 2. Cek frekuensi gagal login berdasarkan IP dalam rentang waktu $lock_minutes
    $time_threshold = date('Y-m-d H:i:s', time() - ($lock_minutes * 60));
    $stmt_ip = mysqli_prepare($koneksi, "SELECT COUNT(id) AS total_fail FROM login_attempts WHERE ip_address = ? AND attempt_time >= ?");
    if ($stmt_ip) {
        mysqli_stmt_bind_param($stmt_ip, "ss", $ip, $time_threshold);
        mysqli_stmt_execute($stmt_ip);
        $res_ip = mysqli_stmt_get_result($stmt_ip);
        $data_ip = mysqli_fetch_assoc($res_ip);
        $total_fail = (int)($data_ip['total_fail'] ?? 0);
        mysqli_stmt_close($stmt_ip);

        if ($total_fail >= $max_attempts) {
            return [
                'is_locked' => true,
                'remaining_seconds' => $lock_minutes * 60
            ];
        }
    }

    return ['is_locked' => false, 'remaining_seconds' => 0];
}

/**
 * Mencatat percobaan gagal login dan mengunci akun jika melebihi batas
 *
 * @param mysqli $koneksi
 * @param string $ip
 * @param string $username
 */
function record_failed_login($koneksi, $ip, $username) {
    $now = date('Y-m-d H:i:s');

    // Catat ke tabel login_attempts
    $stmt = mysqli_prepare($koneksi, "INSERT INTO login_attempts (ip_address, username, attempt_time) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $ip, $username, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Update counter gagal di tabel users
    $stmt_u = mysqli_prepare($koneksi, "SELECT id, failed_attempts FROM users WHERE username = ? LIMIT 1");
    if ($stmt_u) {
        mysqli_stmt_bind_param($stmt_u, "s", $username);
        mysqli_stmt_execute($stmt_u);
        $res = mysqli_stmt_get_result($stmt_u);
        if ($user = mysqli_fetch_assoc($res)) {
            $new_fail = $user['failed_attempts'] + 1;
            $locked_until = null;
            if ($new_fail >= 5) {
                // Kunci akun selama 15 menit
                $locked_until = date('Y-m-d H:i:s', time() + (15 * 60));
            }
            $stmt_up = mysqli_prepare($koneksi, "UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
            if ($stmt_up) {
                mysqli_stmt_bind_param($stmt_up, "isi", $new_fail, $locked_until, $user['id']);
                mysqli_stmt_execute($stmt_up);
                mysqli_stmt_close($stmt_up);
            }
        }
        mysqli_stmt_close($stmt_u);
    }
}

/**
 * Membersihkan riwayat percobaan login setelah login berhasil
 *
 * @param mysqli $koneksi
 * @param string $ip
 * @param string $username
 */
function clear_login_attempts($koneksi, $ip, $username) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM login_attempts WHERE ip_address = ? OR username = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $ip, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $stmt_u = mysqli_prepare($koneksi, "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE username = ?");
    if ($stmt_u) {
        mysqli_stmt_bind_param($stmt_u, "s", $username);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);
    }
}

/**
 * Memverifikasi password dan melakukan migrasi transparan jika masih berformat MD5
 *
 * @param mysqli $koneksi
 * @param int $user_id
 * @param string $password_input
 * @param string $password_db
 * @return bool
 */
function verify_and_migrate_password($koneksi, $user_id, $password_input, $password_db) {
    // 1. Coba verifikasi standar dengan bcrypt / argon2 (password_verify)
    if (password_verify($password_input, $password_db)) {
        // Jika cost atau algoritma perlu diperbarui
        if (password_needs_rehash($password_db, PASSWORD_DEFAULT)) {
            $new_hash = password_hash($password_input, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $new_hash, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        return true;
    }

    // 2. Cek apakah password di DB masih menggunakan MD5 lama
    if (md5($password_input) === $password_db) {
        // Otomatis migrasi MD5 ke password_hash modern
        $new_hash = password_hash($password_input, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $new_hash, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        return true;
    }

    return false;
}

/**
 * Logout aman dan pemusnahan session
 *
 * @param mysqli|null $koneksi
 */
function secure_logout($koneksi = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($koneksi && !empty($_SESSION['id_admin'])) {
        audit_log($koneksi, 'logout', 'auth', 'users', $_SESSION['id_admin'], "Pengguna {$_SESSION['username']} berhasil keluar.");
    }

    // Hapus seluruh data sesi
    $_SESSION = [];

    // Hapus cookie sesi dari browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}
