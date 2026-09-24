<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_santri.php');
    exit;
}

require_permission('data_santri');
verify_csrf_token($_POST['csrf_token'] ?? '', true);

$aksi = trim((string)($_POST['aksi'] ?? ''));

// ==========================================
// BLOKADE KEAMANAN BACKEND
// ==========================================
if (in_array($aksi, ['tambah', 'edit', 'hapus'])) {
    if (($_SESSION['role'] ?? '') !== 'superadmin') {
        $_SESSION['santri_status'] = 'danger';
        $_SESSION['santri_message'] = 'Akses Ditolak! Hanya Superadmin yang berhak menambah, mengedit, atau menghapus data santri.';
        header('Location: data_santri.php');
        exit;
    }
}

$dir_upload = __DIR__ . '/../assets/images/';

if (!is_dir($dir_upload)) {
    mkdir($dir_upload, 0777, true);
}

// ==========================================
// AKSI TAMBAH DATA
// ==========================================
if ($aksi === 'tambah') {
    $nis = validate_nis($_POST['nis'] ?? '');
    $nama = trim((string)($_POST['nama'] ?? ''));
    $jk = strtoupper(trim((string)($_POST['jk'] ?? '')));
    $ttl = trim((string)($_POST['ttl'] ?? ''));
    $nama_ortu = trim((string)($_POST['nama_ortu'] ?? ''));
    $no_hp_ortu = trim((string)($_POST['no_hp_ortu'] ?? ''));
    $alamat = trim((string)($_POST['alamat'] ?? '')); // TAMBAHAN ALAMAT
    $email = validate_email($_POST['email'] ?? '') ?: trim((string)($_POST['email'] ?? ''));

    if (!$nis || $nama === '' || !in_array($jk, ['L', 'P'], true)) {
        $_SESSION['santri_status'] = 'danger';
        $_SESSION['santri_message'] = 'Data santri yang dikirim tidak valid.';
        header('Location: data_santri.php');
        exit;
    }

    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM santri WHERE nis = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt_check, 's', $nis);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    if (mysqli_num_rows($res_check) > 0) {
        mysqli_stmt_close($stmt_check);
        $_SESSION['santri_status'] = 'danger';
        $_SESSION['santri_message'] = 'NIS sudah terdaftar.';
        header('Location: data_santri.php');
        exit;
    }
    mysqli_stmt_close($stmt_check);

    $nama_foto = 'default.png';
    if (!empty($_FILES['foto']['name'])) {
        $file_check = validate_file_upload($_FILES['foto'], ['image/jpeg', 'image/png', 'image/webp'], 2 * 1024 * 1024);
        if (!$file_check['valid']) {
            $_SESSION['santri_status'] = 'danger';
            $_SESSION['santri_message'] = $file_check['error'];
            header('Location: data_santri.php');
            exit;
        }
        $nama_foto = generate_secure_filename('santri', $file_check['ext']);
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dir_upload . $nama_foto)) {
            $_SESSION['santri_status'] = 'danger';
            $_SESSION['santri_message'] = 'Gagal mengunggah foto santri.';
            header('Location: data_santri.php');
            exit;
        }
    }

    $qr_token = bin2hex(random_bytes(32));
    
    // Query Insert dengan Alamat (10 parameter string: ssssssssss)
    $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO santri (nis, nama, jk, ttl, nama_ortu, no_hp_ortu, alamat, email, foto, saldo, qr_token, deleted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NULL)");
    mysqli_stmt_bind_param($stmt_insert, 'ssssssssss', $nis, $nama, $jk, $ttl, $nama_ortu, $no_hp_ortu, $alamat, $email, $nama_foto, $qr_token);
    
    $ok = mysqli_stmt_execute($stmt_insert);
    $insert_id = mysqli_stmt_insert_id($stmt_insert);
    mysqli_stmt_close($stmt_insert);

    if ($ok) {
        audit_log($koneksi, 'tambah_santri', 'santri', 'santri', $insert_id, 'Menambah data santri baru dengan NIS ' . $nis);
        $_SESSION['santri_status'] = 'success';
        $_SESSION['santri_message'] = 'Santri berhasil ditambahkan.';
        header('Location: data_santri.php');
        exit;
    }

    $_SESSION['santri_status'] = 'danger';
    $_SESSION['santri_message'] = 'Gagal menambah data santri.';
    header('Location: data_santri.php');
    exit;
}

// ==========================================
// AKSI EDIT DATA
// ==========================================
if ($aksi === 'edit') {
    $id = validate_id($_POST['id'] ?? null);
    $nis = validate_nis($_POST['nis'] ?? '');
    $nama = trim((string)($_POST['nama'] ?? ''));
    $jk = strtoupper(trim((string)($_POST['jk'] ?? '')));
    $ttl = trim((string)($_POST['ttl'] ?? ''));
    $nama_ortu = trim((string)($_POST['nama_ortu'] ?? ''));
    $no_hp_ortu = trim((string)($_POST['no_hp_ortu'] ?? ''));
    $alamat = trim((string)($_POST['alamat'] ?? '')); // TAMBAHAN ALAMAT
    $email = validate_email($_POST['email'] ?? '') ?: trim((string)($_POST['email'] ?? ''));
    $foto_lama = trim((string)($_POST['foto_lama'] ?? 'default.png'));

    if (!$id || !$nis || $nama === '' || !in_array($jk, ['L', 'P'], true)) {
        $_SESSION['santri_status'] = 'danger';
        $_SESSION['santri_message'] = 'Data edit santri tidak valid.';
        header('Location: data_santri.php');
        exit;
    }

    $nama_foto = $foto_lama;
    if (!empty($_FILES['foto_baru']['name'])) {
        $file_check = validate_file_upload($_FILES['foto_baru'], ['image/jpeg', 'image/png', 'image/webp'], 2 * 1024 * 1024);
        if (!$file_check['valid']) {
            $_SESSION['santri_status'] = 'danger';
            $_SESSION['santri_message'] = $file_check['error'];
            header('Location: data_santri.php');
            exit;
        }
        $nama_foto = generate_secure_filename('santri', $file_check['ext']);
        if (!move_uploaded_file($_FILES['foto_baru']['tmp_name'], $dir_upload . $nama_foto)) {
            $_SESSION['santri_status'] = 'danger';
            $_SESSION['santri_message'] = 'Gagal mengunggah foto santri baru.';
            header('Location: data_santri.php');
            exit;
        }
        if ($foto_lama !== 'default.png' && $foto_lama !== '' && file_exists($dir_upload . $foto_lama)) {
            unlink($dir_upload . $foto_lama);
        }
    }

    // Query Update dengan Alamat
    $stmt = mysqli_prepare($koneksi, "UPDATE santri SET nis = ?, nama = ?, jk = ?, ttl = ?, nama_ortu = ?, no_hp_ortu = ?, alamat = ?, email = ?, foto = ? WHERE id = ? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt, 'sssssssssi', $nis, $nama, $jk, $ttl, $nama_ortu, $no_hp_ortu, $alamat, $email, $nama_foto, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        audit_log($koneksi, 'edit_santri', 'santri', 'santri', $id, 'Mengubah data santri NIS ' . $nis);
        $_SESSION['santri_status'] = 'success';
        $_SESSION['santri_message'] = 'Data santri berhasil diperbarui.';
        header('Location: data_santri.php');
        exit;
    }

    $_SESSION['santri_status'] = 'danger';
    $_SESSION['santri_message'] = 'Gagal memperbarui data santri.';
    header('Location: data_santri.php');
    exit;
}

// ==========================================
// AKSI HAPUS DATA
// ==========================================
if ($aksi === 'hapus') {
    $id = validate_id($_POST['id'] ?? null);
    
    if (!$id) {
        $_SESSION['santri_status'] = 'danger';
        $_SESSION['santri_message'] = 'ID santri tidak valid.';
        header('Location: data_santri.php');
        exit;
    }

    $stmt_foto = mysqli_prepare($koneksi, "SELECT foto FROM santri WHERE id = ?");
    mysqli_stmt_bind_param($stmt_foto, 'i', $id);
    mysqli_stmt_execute($stmt_foto);
    $res_foto = mysqli_stmt_get_result($stmt_foto);
    $data_foto = mysqli_fetch_assoc($res_foto);
    mysqli_stmt_close($stmt_foto);

    mysqli_begin_transaction($koneksi);
    
    try {
        $stmt_trx = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE santri_id = ?");
        mysqli_stmt_bind_param($stmt_trx, 'i', $id);
        mysqli_stmt_execute($stmt_trx);
        mysqli_stmt_close($stmt_trx);

        $stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM santri WHERE id = ?");
        mysqli_stmt_bind_param($stmt_hapus, 'i', $id);
        $ok = mysqli_stmt_execute($stmt_hapus);
        mysqli_stmt_close($stmt_hapus);

        if (!$ok) {
            throw new Exception('Gagal menghapus data santri dari database.');
        }

        if (!empty($data_foto['foto']) && $data_foto['foto'] !== 'default.png') {
            $path_foto = __DIR__ . '/../assets/images/' . $data_foto['foto'];
            if (file_exists($path_foto)) {
                unlink($path_foto);
            }
        }

        audit_log($koneksi, 'hapus_santri', 'santri', 'santri', $id, "Menghapus permanen data santri beserta riwayat transaksinya.");
        mysqli_commit($koneksi);

        $_SESSION['santri_status'] = 'success';
        $_SESSION['santri_message'] = 'Data santri beserta seluruh riwayat transaksinya berhasil dihapus permanen.';
        header('Location: data_santri.php');
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['santri_status'] = 'danger';
        $_SESSION['santri_message'] = $e->getMessage();
        header('Location: data_santri.php');
        exit;
    }
}

$_SESSION['santri_status'] = 'danger';
$_SESSION['santri_message'] = 'Aksi tidak dikenali.';
header('Location: data_santri.php');
exit;
