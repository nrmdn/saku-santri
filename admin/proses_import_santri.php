<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';

// Panggil kedua library
require_once __DIR__ . '/SimpleXLSX.php';
require_once __DIR__ . '/SimpleXLS.php';

use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLS;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_santri.php');
    exit;
}

require_login();
require_permission('data_santri');
if(!isset($_POST['csrf_token'])) {
    // Redirect kembali ke halaman sebelumnya jika token tidak ada
    header('Location: data_santri.php');
    exit;
}
verify_csrf_token($_POST['csrf_token'], true);

if (empty($_FILES['file_excel']['name'])) {
    header('Location: data_santri.php');
    exit;
}

// 1. MIME Type sudah DIPERLUAS agar browser tidak memblokir file
$allowed_mimes = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
    'application/msexcel',
    'application/x-msexcel',
    'application/x-ms-excel',
    'application/x-excel',
    'application/x-dos_ms_excel',
    'application/xls',
    'application/x-xls',
    'application/octet-stream',
    'text/csv'
];

$file_check = validate_file_upload($_FILES['file_excel'], $allowed_mimes, 10 * 1024 * 1024);

// 2. Cek Ekstensi
if (!$file_check['valid'] || !in_array($file_check['ext'], ['xls', 'xlsx'])) {
    $_SESSION['santri_status'] = 'danger';
    $_SESSION['santri_message'] = 'Sistem mendeteksi file tidak valid. Pastikan file berakhiran .xlsx!';
    header('Location: data_santri.php');
    exit;
}

// 3. Logika parsing
$rows = [];
if ($file_check['ext'] === 'xlsx') {
    if ($xlsx = SimpleXLSX::parse($_FILES['file_excel']['tmp_name'])) {
        $rows = $xlsx->rows();
    }
} else {
    if ($xls = SimpleXLS::parse($_FILES['file_excel']['tmp_name'])) {
        $rows = $xls->rows();
    }
}

if (empty($rows)) {
    $_SESSION['santri_status'] = 'danger';
    $_SESSION['santri_message'] = 'File Excel kosong atau format tabel rusak.';
    header('Location: data_santri.php');
    exit;
}

$berhasil = 0;
$gagal = 0;
$duplikat = 0;
$baris_gagal = []; // Menyimpan informasi baris mana saja yang formatnya salah

foreach ($rows as $index => $row) {
    // Lewati baris pertama (header)
    if ($index === 0) continue;

    // Pastikan tidak memproses baris kosong
    if (empty(trim((string)($row[0] ?? ''))) && empty(trim((string)($row[1] ?? '')))) {
        continue;
    }

    $nis         = validate_nis(trim((string)($row[0] ?? '')));
    $nama        = trim((string)($row[1] ?? ''));
    $jk          = strtoupper(trim((string)($row[2] ?? '')));
    $ttl         = trim((string)($row[3] ?? ''));
    $nama_ortu   = trim((string)($row[4] ?? ''));
    $no_hp_ortu  = trim((string)($row[5] ?? ''));
    $email       = validate_email(trim((string)($row[6] ?? ''))) ?: trim((string)($row[6] ?? ''));
    $alamat      = trim((string)($row[7] ?? '')); // TAMBAHAN: Kolom ke-8 (Index 7) untuk Alamat

    // VALIDASI KETAT
    if (!$nis || $nama === '' || !in_array($jk, ['L', 'P'], true)) {
        $gagal++;
        $baris_gagal[] = "Baris " . ($index + 1);
        continue;
    }

    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM santri WHERE nis = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt_check, 's', $nis);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    $exists = mysqli_num_rows($res_check) > 0;
    mysqli_stmt_close($stmt_check);

    if ($exists) {
        $duplikat++;
        continue;
    }

    $qr_token = bin2hex(random_bytes(32));
    
    // Perbarui query INSERT agar mencakup kolom alamat (total 10 parameter 'ssssssssss')
    $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO santri (nis, nama, jk, ttl, nama_ortu, no_hp_ortu, alamat, email, foto, saldo, qr_token, deleted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'default.png', 0, ?, NULL)");
    mysqli_stmt_bind_param($stmt_insert, 'sssssssss', $nis, $nama, $jk, $ttl, $nama_ortu, $no_hp_ortu, $alamat, $email, $qr_token);
    $ok = mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);

    if ($ok) {
        $berhasil++;
    } else {
        $gagal++;
    }
}

audit_log($koneksi, 'import_santri', 'santri', 'santri', null, "Import data santri selesai: berhasil {$berhasil}, gagal {$gagal}, duplikat {$duplikat}");

if ($gagal > 0 && $berhasil === 0) {
    $_SESSION['santri_status'] = 'warning';
    $list_gagal = implode(", ", array_slice($baris_gagal, 0, 5));
    $_SESSION['santri_message'] = "Semua data gagal di-import. Cek format data pada {$list_gagal}. Pastikan format NIS benar dan Jenis Kelamin memakai huruf 'L' atau 'P'.";
} else {
    $_SESSION['santri_status'] = 'success';
    $_SESSION['santri_message'] = "Import selesai! Berhasil: {$berhasil}, Duplikat: {$duplikat}, Gagal Format: {$gagal}.";
}

header('Location: data_santri.php');
exit;