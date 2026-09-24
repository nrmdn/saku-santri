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

require_permission('pengeluaran');
verify_csrf_token($_POST['csrf_token'] ?? '', true);

$santri_id = validate_id($_POST['santri_id'] ?? null);
$nominal = validate_nominal($_POST['nominal'] ?? 0, 1, 50000000);
$keterangan = trim((string)($_POST['keterangan'] ?? ''));

if (!$santri_id || !$nominal || $nominal <= 0 || trim($keterangan) === '') {
    $_SESSION['detail_status'] = 'danger';
    $_SESSION['detail_message'] = 'Data pengeluaran tidak valid. Pastikan nominal dan keterangan sudah benar.';
    header('Location: detail_santri.php?id=' . urlencode((string)$santri_id));
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "SELECT id, nama, saldo FROM santri WHERE id = ? AND deleted_at IS NULL FOR UPDATE");
    if (!$stmt) {
        throw new Exception('Gagal membaca data santri.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $santri_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $santri = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$santri) {
        throw new Exception('Santri tidak ditemukan atau sudah non-aktif.');
    }

    if ((float)$santri['saldo'] < $nominal) {
        throw new Exception('Saldo santri tidak mencukupi untuk pengeluaran ini.');
    }

    $tanggal = date('Y-m-d H:i:s');
    $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO transaksi (santri_id, tanggal, jenis_transaksi, nominal, keterangan, status) VALUES (?, ?, 'keluar', ?, ?, 'sukses')");
    if (!$stmt_insert) {
        throw new Exception('Gagal menyimpan riwayat pengeluaran.');
    }

    mysqli_stmt_bind_param($stmt_insert, 'isds', $santri_id, $tanggal, $nominal, $keterangan);
    $ok_insert = mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);

    if (!$ok_insert) {
        throw new Exception('Gagal mencatat transaksi keluar.');
    }

    $stmt_update = mysqli_prepare($koneksi, "UPDATE santri SET saldo = saldo - ? WHERE id = ? AND saldo >= ?");
    if (!$stmt_update) {
        throw new Exception('Gagal mengurangi saldo santri.');
    }

    mysqli_stmt_bind_param($stmt_update, 'did', $nominal, $santri_id, $nominal);
    mysqli_stmt_execute($stmt_update);
    $affected = mysqli_stmt_affected_rows($stmt_update);
    mysqli_stmt_close($stmt_update);

    if ($affected !== 1) {
        throw new Exception('Saldo tidak dapat dipotong karena kondisi saldo berubah.');
    }

    audit_log(
        $koneksi,
        'pengeluaran_manual',
        'transaksi',
        'santri',
        $santri_id,
        "Pengeluaran manual untuk santri {$santri['nama']} sebesar " . format_rupiah($nominal) . '. Keterangan: ' . $keterangan,
        ['saldo_sebelum' => (float)$santri['saldo']],
        ['saldo_sesudah' => (float)$santri['saldo'] - $nominal, 'nominal' => $nominal]
    );

    mysqli_commit($koneksi);
    $_SESSION['detail_status'] = 'success';
    $_SESSION['detail_message'] = 'Pengeluaran berhasil dicatat dan saldo telah dipotong.';
    header('Location: detail_santri.php?id=' . urlencode((string)$santri_id));
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['detail_status'] = 'danger';
    $_SESSION['detail_message'] = $e->getMessage();
    header('Location: detail_santri.php?id=' . urlencode((string)$santri_id));
    exit;
}

