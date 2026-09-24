<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_santri.php');
    exit;
}

require_permission('topup_manual');
verify_csrf_token($_POST['csrf_token'] ?? '', true);

$santri_id = validate_id($_POST['santri_id'] ?? null);
$nominal = validate_nominal($_POST['nominal'] ?? 0, 1, 50000000);
$keterangan = trim((string)($_POST['keterangan'] ?? ''));

if (!$santri_id || !$nominal || trim($keterangan) === '') {
    $_SESSION['detail_status'] = 'danger';
    $_SESSION['detail_message'] = 'Data top up manual tidak valid.';
    header('Location: detail_santri.php?id=' . urlencode((string)$santri_id));
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $stmt_santri = mysqli_prepare($koneksi, "SELECT id, nama, saldo FROM santri WHERE id = ? AND deleted_at IS NULL FOR UPDATE");
    if (!$stmt_santri) {
        throw new Exception('Gagal membaca data santri.');
    }

    mysqli_stmt_bind_param($stmt_santri, 'i', $santri_id);
    mysqli_stmt_execute($stmt_santri);
    $result = mysqli_stmt_get_result($stmt_santri);
    $santri = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt_santri);

    if (!$santri) {
        throw new Exception('Santri tidak ditemukan atau sudah non-aktif.');
    }

    $tanggal = date('Y-m-d H:i:s');
    $nama_admin = $_SESSION['nama_lengkap'] ?? 'Admin';
    $ket_lengkap = $keterangan . ' (Oleh Admin: ' . $nama_admin . ')';

    $stmt_trx = mysqli_prepare($koneksi, "INSERT INTO transaksi (santri_id, jenis_transaksi, nominal, keterangan, bukti_transfer, status, tanggal) VALUES (?, 'masuk', ?, ?, '-', 'sukses', ?)");
    if (!$stmt_trx) {
        throw new Exception('Gagal mencatat riwayat transaksi.');
    }

    mysqli_stmt_bind_param($stmt_trx, 'idss', $santri_id, $nominal, $ket_lengkap, $tanggal);
    $ok_trx = mysqli_stmt_execute($stmt_trx);
    mysqli_stmt_close($stmt_trx);

    if (!$ok_trx) {
        throw new Exception('Gagal menambah transaksi masuk.');
    }

    $stmt_saldo = mysqli_prepare($koneksi, "UPDATE santri SET saldo = saldo + ? WHERE id = ?");
    if (!$stmt_saldo) {
        throw new Exception('Gagal menambahkan saldo santri.');
    }

    mysqli_stmt_bind_param($stmt_saldo, 'di', $nominal, $santri_id);
    $ok_saldo = mysqli_stmt_execute($stmt_saldo);
    mysqli_stmt_close($stmt_saldo);

    if (!$ok_saldo) {
        throw new Exception('Gagal memperbarui saldo santri.');
    }

    audit_log(
        $koneksi,
        'topup_manual',
        'transaksi',
        'santri',
        $santri_id,
        "Top up manual untuk santri {$santri['nama']} sebesar " . format_rupiah($nominal) . '. Keterangan: ' . $keterangan,
        ['saldo_sebelum' => (float)$santri['saldo']],
        ['saldo_sesudah' => (float)$santri['saldo'] + $nominal, 'nominal' => $nominal]
    );

    mysqli_commit($koneksi);
    $_SESSION['detail_status'] = 'success';
    $_SESSION['detail_message'] = 'BERHASIL! Saldo sebesar ' . format_rupiah($nominal) . ' telah ditambahkan ke akun santri.';
    header('Location: detail_santri.php?id=' . urlencode((string)$santri_id));
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['detail_status'] = 'danger';
    $_SESSION['detail_message'] = $e->getMessage();
    header('Location: detail_santri.php?id=' . urlencode((string)$santri_id));
    exit;
}
