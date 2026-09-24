<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: laporan_cash.php');
    exit;
}

require_permission('laporan_cash');
verify_csrf_token($_POST['csrf_token'] ?? '', true);

$santri_id = validate_id($_POST['santri_id'] ?? null);
$nominal = validate_nominal($_POST['nominal'] ?? 0, 1, 50000000);
$keterangan = trim((string)($_POST['keterangan'] ?? ''));

if (!$santri_id || !$nominal || $keterangan === '') {
    $_SESSION['laporan_status'] = 'danger';
    $_SESSION['laporan_message'] = 'Data cash masuk tidak valid.';
    header('Location: laporan_cash.php');
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $stmt_santri = mysqli_prepare($koneksi, "SELECT id, nama, saldo FROM santri WHERE id = ? AND deleted_at IS NULL LIMIT 1 FOR UPDATE");
    if (!$stmt_santri) {
        throw new Exception('Gagal membaca data santri.');
    }
    mysqli_stmt_bind_param($stmt_santri, 'i', $santri_id);
    mysqli_stmt_execute($stmt_santri);
    $res_s = mysqli_stmt_get_result($stmt_santri);
    $santri = mysqli_fetch_assoc($res_s);
    mysqli_stmt_close($stmt_santri);

    if (!$santri) {
        throw new Exception('Santri tidak ditemukan.');
    }

    $tanggal = date('Y-m-d H:i:s');
    $stmt_trx = mysqli_prepare($koneksi, "INSERT INTO transaksi (santri_id, tanggal, jenis_transaksi, nominal, keterangan, status) VALUES (?, ?, 'masuk', ?, ?, 'sukses')");
    if (!$stmt_trx) {
        throw new Exception('Gagal mencatat transaksi.');
    }
    mysqli_stmt_bind_param($stmt_trx, 'isds', $santri_id, $tanggal, $nominal, $keterangan);
    $ok_trx = mysqli_stmt_execute($stmt_trx);
    mysqli_stmt_close($stmt_trx);
    if (!$ok_trx) {
        throw new Exception('Gagal menyimpan riwayat transaksi.');
    }

    $stmt_update = mysqli_prepare($koneksi, "UPDATE santri SET saldo = saldo + ? WHERE id = ?");
    if (!$stmt_update) {
        throw new Exception('Gagal memperbarui saldo santri.');
    }
    mysqli_stmt_bind_param($stmt_update, 'di', $nominal, $santri_id);
    $ok_update = mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
    if (!$ok_update) {
        throw new Exception('Gagal menambahkan saldo santri.');
    }

    audit_log(
        $koneksi,
        'cash_global_masuk',
        'laporan',
        'santri',
        $santri_id,
        "Penambahan cash global untuk santri {$santri['nama']} sebesar " . format_rupiah($nominal) . '. Keterangan: ' . $keterangan,
        ['saldo_sebelum' => (float)$santri['saldo']],
        ['saldo_sesudah' => (float)$santri['saldo'] + $nominal]
    );

    mysqli_commit($koneksi);
    $_SESSION['laporan_status'] = 'success';
    $_SESSION['laporan_message'] = 'Cash masuk berhasil ditambahkan dan saldo santri telah diperbarui.';
    header('Location: laporan_cash.php');
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['laporan_status'] = 'danger';
    $_SESSION['laporan_message'] = $e->getMessage();
    header('Location: laporan_cash.php');
    exit;
}
