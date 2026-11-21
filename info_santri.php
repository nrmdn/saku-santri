<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php';

date_default_timezone_set('Asia/Jakarta');

$data_santri = null;

// 1. Ambil data santri via Token QR Aman
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
        <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="max-width: 400px;">
            <div class="text-danger fs-1 mb-3"><i class="fas fa-qrcode"></i></div>
            <h5 class="fw-bold text-dark mb-2">QR Code Tidak Valid</h5>
            <p class="text-muted small mb-4">
                Informasi santri tidak ditemukan. Pastikan Anda memindai QR Code resmi yang tertera pada Kartu Tanda Santri.
            </p>
            <a href="index.php" class="btn btn-dark rounded-pill px-4 fw-bold">Kembali ke Beranda</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$id_santri = (int)$data_santri['id'];
$foto_santri = (!empty($data_santri['foto'])) ? $data_santri['foto'] : 'default.png';

// =================================================================
// LOGIKA FILTER RENTANG TANGGAL
// =================================================================
$tgl_mulai   = isset($_GET['tgl_mulai']) ? trim(mysqli_real_escape_string($koneksi, $_GET['tgl_mulai'])) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim(mysqli_real_escape_string($koneksi, $_GET['tgl_selesai'])) : '';

$sql_date_filter = "";
$label_waktu = "Seluruh Waktu";

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql_date_filter = " AND DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
    $label_waktu = date('d/m/Y', strtotime($tgl_mulai)) . " - " . date('d/m/Y', strtotime($tgl_selesai));
} else {
    // Default tampilkan bulan berjalan jika belum difilter
    $sql_date_filter = " AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
    $label_waktu = "Bulan Ini (" . date('F Y') . ")";
}

// =================================================================
// AMBIL DATA STATISTIK BERDASARKAN FILTER
// =================================================================
$total_masuk = 0;
$stmt_m = mysqli_prepare($koneksi, "SELECT SUM(nominal) AS total_masuk FROM transaksi WHERE santri_id = ? AND jenis_transaksi = 'masuk' AND status = 'sukses' $sql_date_filter");
if ($stmt_m) {
    mysqli_stmt_bind_param($stmt_m, "i", $id_santri);
    mysqli_stmt_execute($stmt_m);
    $res_m = mysqli_stmt_get_result($stmt_m);
    $total_masuk = (float)(mysqli_fetch_assoc($res_m)['total_masuk'] ?? 0);
    mysqli_stmt_close($stmt_m);
}

$total_keluar = 0;
$stmt_k = mysqli_prepare($koneksi, "SELECT SUM(nominal) AS total_keluar FROM transaksi WHERE santri_id = ? AND jenis_transaksi = 'keluar' AND status = 'sukses' $sql_date_filter");
if ($stmt_k) {
    mysqli_stmt_bind_param($stmt_k, "i", $id_santri);
    mysqli_stmt_execute($stmt_k);
    $res_k = mysqli_stmt_get_result($stmt_k);
    $total_keluar = (float)(mysqli_fetch_assoc($res_k)['total_keluar'] ?? 0);
    mysqli_stmt_close($stmt_k);
}

// Riwayat transaksi
$transaksi = [];
$stmt_t = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE santri_id = ? AND status = 'sukses' $sql_date_filter ORDER BY tanggal DESC LIMIT 100");
if ($stmt_t) {
    mysqli_stmt_bind_param($stmt_t, "i", $id_santri);
    mysqli_stmt_execute($stmt_t);
    $res_t = mysqli_stmt_get_result($stmt_t);
    while ($row = mysqli_fetch_assoc($res_t)) {
        $transaksi[] = $row;
    }
    mysqli_stmt_close($stmt_t);
}

// Ambil parameter token/id untuk form pencarian
$param_key = '';
$param_val = '';
if (!empty($_GET['token'])) {
    $param_key = 'token';
    $param_val = $_GET['token'];
} elseif (!empty($_GET['id'])) {
    $param_key = 'id';
    $param_val = $_GET['id'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>E-Wallet - <?= e($data_santri['nama']); ?></title>
    <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        
        .app-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #ffffff;
            min-height: 100vh;
            box-shadow: 0 0 40px rgba(0,0,0,0.05);
            position: relative;
            padding-bottom: 40px;
        }

        .header-bg {
            background: linear-gradient(135deg, #0f5132 0%, #198754 100%);
            padding: 30px 20px 80px 20px;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            color: white;
            position: relative;
        }

        .balance-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px 20px;
            margin: -60px 20px 20px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            position: relative;
            z-index: 10;
        }

        .foto-profil {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .filter-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 15px;
            margin: 0 20px 20px 20px;
        }

        .trx-icon {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .icon-in { background-color: #e8f5e9; color: #2e7d32; }
        .icon-out { background-color: #ffebee; color: #c62828; }
        
        .trx-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
        }
        .trx-item:last-child {
            border-bottom: none;
        }
        
        .trx-info {
            flex-grow: 1;
            min-width: 0;
            padding-right: 12px;
            padding-left: 12px;
        }
        .trx-title {
            font-size: 14px;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
            white-space: normal !important;
            line-height: 1.4;
        }
        /* Dikunci di kanan, tidak tergeser, dan tampil sejajar dalam satu baris */
        .trx-nominal {
            flex-shrink: 0;
            white-space: nowrap;
            text-align: right;
            font-size: 14px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="app-container">
    
    <!-- HEADER -->
    <div class="header-bg d-flex justify-content-between align-items-center">
        <div>
            <small class="text-white fw-bold text-uppercase " style="letter-spacing: 1px;">Saku Santri</small>
            <h5 class="fw-bold mb-0 mt-1 text-truncate" style="max-width: 240px;"><?= e($data_santri['nama']); ?></h5>
        </div>
        <img src="assets/images/<?= e($foto_santri); ?>" alt="Profil" class="foto-profil" onerror="this.src='assets/images/default.png'">
    </div>

    <!-- KARTU SALDO MELAYANG -->
    <div class="balance-card text-center">
        <span class="text-muted fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Sisa Saldo Aktif</span>
        <h1 class="fw-bold text-dark mt-2 mb-3" style="font-size: 2.2rem;">
            <?= format_rupiah($data_santri['saldo']); ?>
        </h1>
        <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
            <i class="fas fa-id-badge me-1"></i> NIS: <?= e($data_santri['nis']); ?>
        </div>
    </div>

    <!-- FORM FILTER RENTANG TANGGAL -->
    <div class="filter-box">
        <form method="GET" action="">
            <?php if (!empty($param_key)): ?>
                <input type="hidden" name="<?= $param_key; ?>" value="<?= e($param_val); ?>">
            <?php endif; ?>
            
            <div class="fw-bold text-dark mb-2" style="font-size: 13px;">
                <i class="fas fa-filter text-success me-1"></i> Filter Periode Transaksi
            </div>
            
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="text-muted" style="font-size: 11px;">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= e($tgl_mulai); ?>" required>
                </div>
                <div class="col-6">
                    <label class="text-muted" style="font-size: 11px;">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= e($tgl_selesai); ?>" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold rounded-3">Tampilkan</button>
                <?php if (!empty($tgl_mulai)): ?>
                    <a href="?<?= $param_key; ?>=<?= e($param_val); ?>" class="btn btn-outline-secondary btn-sm rounded-3" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- RINGKASAN PEMASUKAN & PENGELUARAN -->
    <div class="row g-3 px-4 mb-4">
        <div class="col-6">
            <div class="p-3 rounded-4" style="background-color: #f1f8f5; border: 1px solid #e0eee7;">
                <div class="text-success mb-1 small fw-bold"><i class="fas fa-arrow-circle-down"></i> Masuk</div>
                <h6 class="fw-bold text-dark mb-0"><?= format_rupiah($total_masuk); ?></h6>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 rounded-4" style="background-color: #fff5f5; border: 1px solid #fce8e8;">
                <div class="text-danger mb-1 small fw-bold"><i class="fas fa-arrow-circle-up"></i> Keluar</div>
                <h6 class="fw-bold text-dark mb-0"><?= format_rupiah($total_keluar); ?></h6>
            </div>
        </div>
    </div>

    <!-- DAFTAR TRANSAKSI -->
    <div class="px-4 mb-2 d-flex justify-content-between align-items-end">
        <h6 class="fw-bold text-dark mb-0">Riwayat Mutasi</h6>
        <small class="text-muted"><?= count($transaksi); ?> Data (<?= e($label_waktu); ?>)</small>
    </div>

    <div class="bg-white">
        <?php if (!empty($transaksi)): ?>
            <?php foreach ($transaksi as $trx): ?>
                <?php $is_masuk = ($trx['jenis_transaksi'] === 'masuk'); ?>
                <div class="trx-item">
                    <!-- Ikon -->
                    <div class="trx-icon <?= $is_masuk ? 'icon-in' : 'icon-out'; ?>">
                        <i class="fas <?= $is_masuk ? 'fa-download' : 'fa-shopping-bag'; ?>"></i>
                    </div>
                    
                    <!-- Keterangan Transaksi -->
                    <div class="trx-info">
                        <div class="trx-title">
                            <?= e($trx['keterangan']); ?>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 11.5px;">
                            <i class="far fa-clock me-1"></i><?= date('d M Y • H:i', strtotime($trx['tanggal'])); ?>
                        </div>
                    </div>

                    <!-- Nominal (Warna hijau untuk masuk, merah untuk keluar; tampil dalam satu baris) -->
                    <div class="trx-nominal <?= $is_masuk ? 'text-success' : 'text-danger'; ?>">
                        <?= $is_masuk ? '+ ' : '- '; ?><?= format_rupiah($trx['nominal']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted fs-1 mb-3 opacity-50"><i class="fas fa-receipt"></i></div>
                <h6 class="fw-bold text-dark">Tidak ada transaksi</h6>
                <p class="text-muted small">Tidak ditemukan catatan kas pada rentang tanggal tersebut.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
