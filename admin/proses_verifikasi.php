<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/audit.php';
require_once __DIR__ . '/../includes/db.php';

date_default_timezone_set('Asia/Jakarta');

require_permission('verifikasi_topup');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: verifikasi_topup.php');
    exit;
}

verify_csrf_token($_POST['csrf_token'] ?? '', true);

$id_transaksi = validate_id($_POST['id'] ?? null);
$aksi = trim((string)($_POST['aksi'] ?? ''));

if (!$id_transaksi || !in_array($aksi, ['setuju', 'tolak'], true)) {
    $_SESSION['verif_status'] = 'danger';
    $_SESSION['verif_message'] = 'Parameter verifikasi tidak valid atau token keamanan tidak sah.';
    header('Location: verifikasi_topup.php');
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $stmt_lock = mysqli_prepare($koneksi, "SELECT id, santri_id, nominal, status FROM transaksi WHERE id = ? FOR UPDATE");
    if (!$stmt_lock) {
        throw new Exception('Gagal mengunci data transaksi.');
    }

    mysqli_stmt_bind_param($stmt_lock, 'i', $id_transaksi);
    mysqli_stmt_execute($stmt_lock);
    $res_lock = mysqli_stmt_get_result($stmt_lock);
    $data_trx = mysqli_fetch_assoc($res_lock);
    mysqli_stmt_close($stmt_lock);

    if (!$data_trx || $data_trx['status'] !== 'pending') {
        throw new Exception('Transaksi tidak ditemukan atau sudah diproses oleh admin lain.');
    }

    $santri_id = (int)$data_trx['santri_id'];
    $nominal = (float)$data_trx['nominal'];

    if ($nominal <= 0) {
        throw new Exception('Nominal transaksi tidak valid.');
    }

    if ($aksi === 'setuju') {
        $stmt_status = mysqli_prepare($koneksi, "UPDATE transaksi SET status = 'sukses' WHERE id = ? AND status = 'pending'");
        if (!$stmt_status) {
            throw new Exception('Gagal menyimpan status transaksi.');
        }

        mysqli_stmt_bind_param($stmt_status, 'i', $id_transaksi);
        mysqli_stmt_execute($stmt_status);
        $affected_trx = mysqli_stmt_affected_rows($stmt_status);
        mysqli_stmt_close($stmt_status);

        if ($affected_trx !== 1) {
            throw new Exception('Transaksi sudah diproses sebelumnya.');
        }

        $stmt_s = mysqli_prepare($koneksi, "SELECT nama, saldo FROM santri WHERE id = ? FOR UPDATE");
        if (!$stmt_s) {
            throw new Exception('Gagal mengunci data santri.');
        }

        mysqli_stmt_bind_param($stmt_s, 'i', $santri_id);
        mysqli_stmt_execute($stmt_s);
        $res_s = mysqli_stmt_get_result($stmt_s);
        $data_santri = mysqli_fetch_assoc($res_s);
        mysqli_stmt_close($stmt_s);

        if (!$data_santri) {
            throw new Exception('Data santri terkait tidak ditemukan.');
        }

        $saldo_lama = (float)$data_santri['saldo'];
        $saldo_baru = $saldo_lama + $nominal;

        $stmt_saldo = mysqli_prepare($koneksi, "UPDATE santri SET saldo = saldo + ? WHERE id = ?");
        if (!$stmt_saldo) {
            throw new Exception('Gagal memperbarui saldo santri.');
        }

        mysqli_stmt_bind_param($stmt_saldo, 'di', $nominal, $santri_id);
        mysqli_stmt_execute($stmt_saldo);
        $affected_saldo = mysqli_stmt_affected_rows($stmt_saldo);
        mysqli_stmt_close($stmt_saldo);

        if ($affected_saldo !== 1) {
            throw new Exception('Gagal menambahkan saldo santri.');
        }

        audit_log(
            $koneksi,
            'verifikasi_topup_setuju',
            'transaksi',
            'santri',
            $santri_id,
            "Persetujuan top up #{$id_transaksi} untuk santri {$data_santri['nama']} sebesar " . format_rupiah($nominal),
            ['saldo_sebelum' => $saldo_lama],
            ['saldo_sesudah' => $saldo_baru, 'nominal' => $nominal, 'transaksi_id' => $id_transaksi]
        );

        mysqli_commit($koneksi);

        $_SESSION['verif_status'] = 'success';
        $_SESSION['verif_message'] = 'BERHASIL! Top up sebesar ' . format_rupiah($nominal) . ' telah disetujui.';
    } else {
        $stmt_tolak = mysqli_prepare($koneksi, "UPDATE transaksi SET status = 'gagal' WHERE id = ? AND status = 'pending'");
        if (!$stmt_tolak) {
            throw new Exception('Gagal menolak transaksi.');
        }

        mysqli_stmt_bind_param($stmt_tolak, 'i', $id_transaksi);
        mysqli_stmt_execute($stmt_tolak);
        $affected_tolak = mysqli_stmt_affected_rows($stmt_tolak);
        mysqli_stmt_close($stmt_tolak);

        if ($affected_tolak !== 1) {
            throw new Exception('Transaksi tidak dapat ditolak karena sudah diproses.');
        }

        audit_log(
            $koneksi,
            'verifikasi_topup_tolak',
            'transaksi',
            'transaksi',
            $id_transaksi,
            'Penolakan top up pengajuan #' . $id_transaksi . ' nominal ' . format_rupiah($nominal)
        );

        mysqli_commit($koneksi);

        $_SESSION['verif_status'] = 'warning';
        $_SESSION['verif_message'] = 'Transaksi top up #' . $id_transaksi . ' berhasil ditolak.';
    }
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['verif_status'] = 'danger';
    $_SESSION['verif_message'] = 'ERROR: ' . $e->getMessage();
}

header('Location: verifikasi_topup.php');
exit;