<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_pengguna.php');
    exit;
}

require_login();
require_permission('data_pengguna');
verify_csrf_token($_POST['csrf_token'] ?? '', true);

// BLOKADE KEAMANAN BACKEND
if (($_SESSION['role'] ?? '') !== 'superadmin') {
    $_SESSION['pengguna_status'] = 'danger';
    $_SESSION['pengguna_message'] = 'Akses Ditolak! Hanya Superadmin yang berhak mengeksekusi data.';
    header('Location: dashboard.php');
    exit;
}

$dir_upload = __DIR__ . '/../assets/images/';
$aksi = trim((string)($_POST['aksi'] ?? ''));

if ($aksi === 'tambah') {
    $nama_lengkap = trim((string)($_POST['nama_lengkap'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = trim((string)($_POST['role'] ?? 'admin'));

    if ($nama_lengkap === '' || $username === '' || strlen($password) < 6 || !in_array($role, ['superadmin', 'admin', 'bendahara', 'operator'], true)) {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Data pengguna baru tidak valid.';
        header('Location: data_pengguna.php');
        exit;
    }

    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_check, 's', $username);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    if (mysqli_num_rows($res_check) > 0) {
        mysqli_stmt_close($stmt_check);
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Username sudah dipakai.';
        header('Location: data_pengguna.php');
        exit;
    }
    mysqli_stmt_close($stmt_check);

    $nama_foto = 'default.png';
    if (!empty($_FILES['foto']['name'])) {
        $file_check = validate_file_upload($_FILES['foto'], ['image/jpeg', 'image/png', 'image/webp'], 2 * 1024 * 1024);
        if (!$file_check['valid']) {
            $_SESSION['pengguna_status'] = 'danger';
            $_SESSION['pengguna_message'] = $file_check['error'];
            header('Location: data_pengguna.php');
            exit;
        }
        $nama_foto = generate_secure_filename('admin', $file_check['ext']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $dir_upload . $nama_foto);
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama_lengkap, username, password, foto, role) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama_lengkap, $username, $password_hash, $nama_foto, $role);
    $ok = mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    if ($ok) {
        audit_log($koneksi, 'tambah_pengguna', 'users', 'users', $id, "Menambahkan pengguna {$username} dengan role {$role}");
        $_SESSION['pengguna_status'] = 'success';
        $_SESSION['pengguna_message'] = 'Admin baru berhasil ditambahkan.';
        header('Location: data_pengguna.php');
        exit;
    }

    $_SESSION['pengguna_status'] = 'danger';
    $_SESSION['pengguna_message'] = 'Gagal menambah admin.';
    header('Location: data_pengguna.php');
    exit;
}

if ($aksi === 'edit') {
    $id = validate_id($_POST['id'] ?? null);
    $nama_lengkap = trim((string)($_POST['nama_lengkap'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = trim((string)($_POST['role'] ?? 'admin'));
    $foto_lama = trim((string)($_POST['foto_lama'] ?? 'default.png'));

    if (!$id || $nama_lengkap === '' || $username === '' || !in_array($role, ['superadmin', 'admin', 'bendahara', 'operator'], true)) {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Data edit pengguna tidak valid.';
        header('Location: data_pengguna.php');
        exit;
    }

    $nama_foto = $foto_lama;
    if (!empty($_FILES['foto_baru']['name'])) {
        $file_check = validate_file_upload($_FILES['foto_baru'], ['image/jpeg', 'image/png', 'image/webp'], 2 * 1024 * 1024);
        if (!$file_check['valid']) {
            $_SESSION['pengguna_status'] = 'danger';
            $_SESSION['pengguna_message'] = $file_check['error'];
            header('Location: data_pengguna.php');
            exit;
        }
        $nama_foto = generate_secure_filename('admin', $file_check['ext']);
        move_uploaded_file($_FILES['foto_baru']['tmp_name'], $dir_upload . $nama_foto);
        if ($foto_lama !== 'default.png' && file_exists($dir_upload . $foto_lama)) {
            unlink($dir_upload . $foto_lama);
        }
    }

    $sql = "UPDATE users SET nama_lengkap = ?, username = ?, foto = ?, role = ?";
    $types = 'ssss';
    $params = [$nama_lengkap, $username, $nama_foto, $role];

    if ($password !== '') {
        if (strlen($password) < 6) {
            $_SESSION['pengguna_status'] = 'danger';
            $_SESSION['pengguna_message'] = 'Password minimal 6 karakter.';
            header('Location: data_pengguna.php');
            exit;
        }
        $sql .= ', password = ?';
        $types .= 's';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= ' WHERE id = ?';
    $types .= 'i';
    $params[] = $id;

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        audit_log($koneksi, 'edit_pengguna', 'users', 'users', $id, "Mengubah data pengguna {$username}");
        $_SESSION['pengguna_status'] = 'success';
        $_SESSION['pengguna_message'] = 'Data admin berhasil diperbarui.';
        header('Location: data_pengguna.php');
        exit;
    }

    $_SESSION['pengguna_status'] = 'danger';
    $_SESSION['pengguna_message'] = 'Gagal memperbarui data.';
    header('Location: data_pengguna.php');
    exit;
}

if ($aksi === 'hapus') {
    $id = validate_id($_POST['id'] ?? null);
    if (!$id) {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'ID pengguna tidak valid.';
        header('Location: data_pengguna.php');
        exit;
    }

    if ((int)$id === (int)($_SESSION['id_admin'] ?? 0)) {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Anda tidak bisa menghapus akun yang sedang aktif.';
        header('Location: data_pengguna.php');
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT foto, role, username FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$user) {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Pengguna tidak ditemukan.';
        header('Location: data_pengguna.php');
        exit;
    }

    $stmt_count = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'superadmin'");
    mysqli_stmt_execute($stmt_count);
    $count_res = mysqli_stmt_get_result($stmt_count);
    $count = mysqli_fetch_assoc($count_res);
    mysqli_stmt_close($stmt_count);

    if (($user['role'] ?? '') === 'superadmin' && ((int)($count['total'] ?? 0)) <= 1) {
        $_SESSION['pengguna_status'] = 'danger';
        $_SESSION['pengguna_message'] = 'Tidak bisa menghapus superadmin terakhir.';
        header('Location: data_pengguna.php');
        exit;
    }

    $delete = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($delete, 'i', $id);
    $ok = mysqli_stmt_execute($delete);
    mysqli_stmt_close($delete);

    if ($ok) {
        if (!empty($user['foto']) && $user['foto'] !== 'default.png' && file_exists($dir_upload . $user['foto'])) {
            unlink($dir_upload . $user['foto']);
        }
        audit_log($koneksi, 'hapus_pengguna', 'users', 'users', $id, 'Menghapus pengguna akun ' . ($user['username'] ?? 'unknown'));
        $_SESSION['pengguna_status'] = 'success';
        $_SESSION['pengguna_message'] = 'Admin berhasil dihapus.';
        header('Location: data_pengguna.php');
        exit;
    }

    $_SESSION['pengguna_status'] = 'danger';
    $_SESSION['pengguna_message'] = 'Gagal menghapus admin.';
    header('Location: data_pengguna.php');
    exit;
}

$_SESSION['pengguna_status'] = 'danger';
$_SESSION['pengguna_message'] = 'Aksi tidak dikenali.';
header('Location: data_pengguna.php');
exit;
