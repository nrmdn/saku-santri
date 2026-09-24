<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php';

// Atur zona waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: topup.php");
    exit;
}

// 1. Verifikasi CSRF Token
verify_csrf_token($_POST['csrf_token'] ?? '', true);

// 2. Tangkap dan Validasi Input
$nis         = validate_nis($_POST['nis'] ?? '');
$nama_wali   = trim($_POST['nama_wali'] ?? '');
$no_hp       = validate_phone($_POST['no_hp'] ?? '') ?: trim($_POST['no_hp'] ?? '');
$bank_tujuan = trim($_POST['bank_tujuan'] ?? '');
$nominal     = validate_nominal($_POST['nominal'] ?? 0, 10000, 50000000);
$catatan     = trim($_POST['catatan'] ?? '');

if (!$nis || empty($nama_wali) || empty($bank_tujuan) || !$nominal) {
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = 'Harap isi semua kolom wajib dengan data yang benar (Nominal minimal Rp 10.000).';
    header("Location: topup.php?nis=" . urlencode((string)($_POST['nis'] ?? '')));
    exit;
}

// 3. Validasi Santri berdasarkan NIS menggunakan Prepared Statement
$stmt_santri = mysqli_prepare($koneksi, "SELECT id, nama FROM santri WHERE nis = ? AND deleted_at IS NULL LIMIT 1");
if (!$stmt_santri) {
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = 'Terjadi kesalahan pada sistem. Silakan coba kembali.';
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}

mysqli_stmt_bind_param($stmt_santri, "s", $nis);
mysqli_stmt_execute($stmt_santri);
$res_santri = mysqli_stmt_get_result($stmt_santri);

if (!$res_santri || mysqli_num_rows($res_santri) === 0) {
    mysqli_stmt_close($stmt_santri);
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = "Santri dengan NIS " . e($nis) . " tidak ditemukan atau sudah tidak aktif.";
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}

$santri_data = mysqli_fetch_assoc($res_santri);
$santri_id   = (int)$santri_data['id'];
$santri_nama = $santri_data['nama'];
mysqli_stmt_close($stmt_santri);

// 4. Validasi Keamanan Berkas Bukti Transfer (MIME Type Asli & Ukuran)
if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = 'Silakan unggah foto atau tangkapan layar bukti transfer.';
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}

$allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
$upload_check  = validate_file_upload($_FILES['bukti_transfer'], $allowed_mimes, 5 * 1024 * 1024);

if (!$upload_check['valid']) {
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = $upload_check['error'];
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}

// 5. Simpan berkas dengan nama acak aman di folder tujuan
$upload_dir = __DIR__ . '/assets/img/struk/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$nama_file_baru = generate_secure_filename('struk_' . date('Ymd'), $upload_check['ext']);
$tujuan_simpan  = $upload_dir . $nama_file_baru;

if (!move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $tujuan_simpan)) {
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = 'Gagal menyimpan file bukti transfer ke server. Silakan hubungi pengurus pondok.';
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}

// 6. Simpan Transaksi dengan status 'pending' menggunakan Prepared Statement
$ket_parts = [];
$ket_parts[] = "Top Up Orang Tua: " . $nama_wali;
if (!empty($no_hp)) {
    $ket_parts[] = "($no_hp)";
}
$ket_parts[] = "via " . $bank_tujuan;
if (!empty($catatan)) {
    $ket_parts[] = "- Catatan: " . $catatan;
}
$keterangan = implode(' ', $ket_parts);
$tanggal    = date('Y-m-d H:i:s');

$sql_insert = "INSERT INTO transaksi (santri_id, jenis_transaksi, nominal, keterangan, bukti_transfer, status, tanggal) 
               VALUES (?, 'masuk', ?, ?, ?, 'pending', ?)";

$stmt_insert = mysqli_prepare($koneksi, $sql_insert);
if (!$stmt_insert) {
    if (file_exists($tujuan_simpan)) {
        unlink($tujuan_simpan);
    }
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = 'Terjadi kesalahan sistem database saat mencatat transaksi.';
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}

mysqli_stmt_bind_param($stmt_insert, "idsss", $santri_id, $nominal, $keterangan, $nama_file_baru, $tanggal);
$execute_success = mysqli_stmt_execute($stmt_insert);

if ($execute_success) {
    $trx_id = mysqli_stmt_insert_id($stmt_insert);
    mysqli_stmt_close($stmt_insert);

    $_SESSION['topup_status']  = 'success';
    $_SESSION['topup_data']    = [
        'trx_id'      => $trx_id,
        'nis'         => $nis,
        'nama_santri' => $santri_nama,
        'nama_wali'   => $nama_wali,
        'bank'        => $bank_tujuan,
        'nominal'     => $nominal,
        'tanggal'     => $tanggal
    ];
    header("Location: topup.php?topup_sukses=1");
    exit;
} else {
    mysqli_stmt_close($stmt_insert);
    if (file_exists($tujuan_simpan)) {
        unlink($tujuan_simpan);
    }
    $_SESSION['topup_status']  = 'error';
    $_SESSION['topup_message'] = 'Gagal mencatat transaksi ke sistem. Silakan coba kembali.';
    header("Location: topup.php?nis=" . urlencode($nis));
    exit;
}
