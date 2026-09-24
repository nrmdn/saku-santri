<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php';

date_default_timezone_set('Asia/Jakarta');

$data_santri = null;

// 1. Ambil data santri via Token QR Aman (Rekomendasi OWASP)
if (!empty($_GET['token'])) {
    $token = trim((string)$_GET['token']);
    if (preg_match('/^[a-f0-9]{64}$/', $token)) {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM santri WHERE qr_token = ? AND deleted_at IS NULL LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && mysqli_num_rows($res) > 0) {
                $data_santri = mysqli_fetch_assoc($res);
            }
            mysqli_stmt_close($stmt);
        }
    }
} 
// 2. Fallback via ID hanya jika diakses oleh Pengurus/Admin yang sedang login
elseif (!empty($_GET['id'])) {
    if (is_logged_in()) {
        $id = validate_id($_GET['id']);
        if ($id) {
            $stmt = mysqli_prepare($koneksi, "SELECT * FROM santri WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && mysqli_num_rows($res) > 0) {
                    $data_santri = mysqli_fetch_assoc($res);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

if (!$data_santri) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Tidak Ditemukan - Saku Santri</title>
        <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
        <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="max-width: 440px;">
            <div class="text-warning fs-1 mb-3"><i class="fas fa-qrcode"></i></div>
            <h4 class="fw-bold text-dark mb-2">QR Code Tidak Valid</h4>
            <p class="text-muted small mb-4">
                Informasi santri tidak ditemukan. Pastikan Anda memindai QR Code resmi yang tertera pada Kartu Tanda Santri fisik Saku Santri.
            </p>
            <div>
                <a href="index.php" class="btn btn-success rounded-pill px-4">
                    <i class="fas fa-home me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$id_santri = (int)$data_santri['id'];
$foto_santri = (!empty($data_santri['foto'])) ? $data_santri['foto'] : 'default.png';

// Hitung total masuk & keluar
$total_masuk = 0;
$stmt_m = mysqli_prepare($koneksi, "SELECT SUM(nominal) AS total_masuk FROM transaksi WHERE santri_id = ? AND jenis_transaksi = 'masuk' AND status = 'sukses'");
if ($stmt_m) {
    mysqli_stmt_bind_param($stmt_m, "i", $id_santri);
    mysqli_stmt_execute($stmt_m);
    $res_m = mysqli_stmt_get_result($stmt_m);
    $total_masuk = (float)(mysqli_fetch_assoc($res_m)['total_masuk'] ?? 0);
    mysqli_stmt_close($stmt_m);
}

$total_keluar = 0;
$stmt_k = mysqli_prepare($koneksi, "SELECT SUM(nominal) AS total_keluar FROM transaksi WHERE santri_id = ? AND jenis_transaksi = 'keluar' AND status = 'sukses'");
if ($stmt_k) {
    mysqli_stmt_bind_param($stmt_k, "i", $id_santri);
    mysqli_stmt_execute($stmt_k);
    $res_k = mysqli_stmt_get_result($stmt_k);
    $total_keluar = (float)(mysqli_fetch_assoc($res_k)['total_keluar'] ?? 0);
    mysqli_stmt_close($stmt_k);
}

// Riwayat 10 transaksi terakhir
$transaksi = [];
$stmt_t = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE santri_id = ? AND status = 'sukses' ORDER BY tanggal DESC LIMIT 10");
if ($stmt_t) {
    mysqli_stmt_bind_param($stmt_t, "i", $id_santri);
    mysqli_stmt_execute($stmt_t);
    $res_t = mysqli_stmt_get_result($stmt_t);
    while ($row = mysqli_fetch_assoc($res_t)) {
        $transaksi[] = $row;
    }
    mysqli_stmt_close($stmt_t);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Digital - <?= e($data_santri['nama']); ?></title>
    <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-4" style="max-width: 500px;">
    
    <!-- HEADER BRAND -->
    <div class="text-center mb-3">
        <h5 class="fw-bold text-success"><i class="fas fa-credit-card me-2"></i>Saku Santri</h5>
        <small class="text-muted">Pondok Pesantren Al Habibatain Bumiayu</small>
    </div>

    <!-- PROFIL CARD -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4 text-center">
            <img src="assets/images/<?= e($foto_santri); ?>" alt="Foto" class="rounded-circle border border-3 border-success mb-3 shadow-sm" width="90" height="90" style="object-fit: cover;" onerror="this.src='assets/images/default.png'">
            <h4 class="fw-bold mb-1"><?= e($data_santri['nama']); ?></h4>
            <span class="badge bg-secondary mb-3">NIS: <?= e($data_santri['nis']); ?></span>
            
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                <small class="d-block text-uppercase fw-bold">Sisa Saldo Aktif</small>
                <h2 class="fw-bold mb-0"><?= format_rupiah($data_santri['saldo']); ?></h2>
            </div>
        </div>
    </div>

    <!-- RINGKASAN MASUK / KELUAR -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="card border-0 shadow-sm rounded-3 bg-success bg-opacity-10 text-success p-3 text-center">
                <small class="fw-bold"><i class="fas fa-arrow-down me-1"></i> Total Masuk</small>
                <h6 class="fw-bold mb-0 mt-1"><?= format_rupiah($total_masuk); ?></h6>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow-sm rounded-3 bg-danger bg-opacity-10 text-danger p-3 text-center">
                <small class="fw-bold"><i class="fas fa-arrow-up me-1"></i> Total Keluar</small>
                <h6 class="fw-bold mb-0 mt-1"><?= format_rupiah($total_keluar); ?></h6>
            </div>
        </div>
    </div>

    <!-- RIWAYAT TRANSAKSI -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>Riwayat Transaksi Terakhir</h6>
        </div>
        <div class="card-body p-3">
            <div class="list-group list-group-flush">
                <?php if (!empty($transaksi)): ?>
                    <?php foreach ($transaksi as $trx): ?>
                        <?php $is_masuk = ($trx['jenis_transaksi'] === 'masuk'); ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-3">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 14px;"><?= e($trx['keterangan']); ?></div>
                                <small class="text-muted" style="font-size: 11px;"><?= date('d M Y - H:i', strtotime($trx['tanggal'])); ?></small>
                            </div>
                            <span class="fw-bold <?= $is_masuk ? 'text-success' : 'text-danger'; ?>" style="font-size: 14px;">
                                <?= $is_masuk ? '+' : '-'; ?> <?= format_rupiah($trx['nominal']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">Belum ada riwayat transaksi.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Beranda Saku Santri
        </a>
    </div>
    
</div>

</body>
</html>
