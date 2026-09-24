<?php
require '../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_permission('dashboard');
include 'layout/header.php';
include 'layout/sidebar.php';


$id_admin = $_SESSION['id_admin']; 

$query_admin = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id_admin'");
$data_admin = mysqli_fetch_assoc($query_admin);


$nama_admin     = $data_admin['nama_lengkap'];
$username_admin = $data_admin['username'];

$foto           = (!empty($data_admin['foto'])) ? $data_admin['foto'] : 'default.png';


$q_santri = mysqli_query($koneksi, "SELECT COUNT(id) AS total_santri FROM santri");
$d_santri = mysqli_fetch_assoc($q_santri);
$total_santri = (int) ($d_santri['total_santri'] ?? 0);
$foto_santri           = (!empty($data_admin['foto'])) ? $data_admin['foto'] : 'default.png';

$q_saldo = mysqli_query($koneksi, "SELECT SUM(saldo) AS total_saldo FROM santri");
$d_saldo = mysqli_fetch_assoc($q_saldo);
$total_saldo = (float) ($d_saldo['total_saldo'] ?? 0);

$q_masuk = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_masuk FROM transaksi WHERE jenis_transaksi='masuk' AND status='sukses'");
$d_masuk = mysqli_fetch_assoc($q_masuk);
$total_masuk = (float) ($d_masuk['total_masuk'] ?? 0);

$q_keluar = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_keluar FROM transaksi WHERE jenis_transaksi='keluar' AND status='sukses'");
$d_keluar = mysqli_fetch_assoc($q_keluar);
$total_keluar = (float) ($d_keluar['total_keluar'] ?? 0);

$q_pending = mysqli_query($koneksi, "SELECT COUNT(id) AS total_pending FROM transaksi WHERE status='pending' AND jenis_transaksi='masuk'");
$d_pending = mysqli_fetch_assoc($q_pending);
$total_pending = (int) ($d_pending['total_pending'] ?? 0);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Panel Kontrol</h1>
        <p class="page-subtitle">
            Ringkasan aktivitas Saku Santri dan kondisi saldo saat ini.
        </p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
            <div class="card-header">
                <span class="stat-label">Konfirmasi Pending</span>
                <i class="bi bi-bell text-danger fs-5"></i>
            </div>
            <div class="stat-value"><?= number_format($total_pending, 0, ',', '.'); ?></div>
            <div class="trend-badge <?= $total_pending > 0 ? 'trend-down' : 'trend-up'; ?>">
                <i class="bi <?= $total_pending > 0 ? 'bi-exclamation-circle' : 'bi-check-circle'; ?>"></i>
                <span><?= $total_pending > 0 ? 'Perlu ditinjau' : 'Tidak ada antrean'; ?></span>
            </div>
            <?php if ($total_pending > 0): ?>
                <div class="mt-4">
                    <a href="verifikasi_topup.php" class="btn-custom btn-custom-primary btn-custom-sm">
                        <i class="bi bi-arrow-right"></i> Cek sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
            <div class="card-header">
                <span class="stat-label">Total Santri</span>
                <i class="bi bi-people-fill text-success fs-5"></i>
            </div>
            <div class="stat-value"><?= number_format($total_santri, 0, ',', '.'); ?></div>
            <div class="trend-badge trend-up">
                <i class="bi bi-person-check-fill"></i>
                <span>Santri terdaftar</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
            <div class="card-header">
                <span class="stat-label">Total Saldo Sistem</span>
                <i class="bi bi-wallet2 text-success fs-5"></i>
            </div>
            <div class="stat-value" style="font-size: 1.65rem;">
                Rp <?= number_format($total_saldo, 0, ',', '.'); ?>
            </div>
            <div class="trend-badge trend-up">
                <i class="bi bi-arrow-up-right"></i>
                <span>Saldo aktif seluruh santri</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
            <div class="card-header">
                <span class="stat-label">Total Pemasukan</span>
                <i class="bi bi-graph-up-arrow text-success fs-5"></i>
            </div>
            <div class="stat-value" style="font-size: 1.65rem;">
                Rp <?= number_format($total_masuk, 0, ',', '.'); ?>
            </div>
            <div class="trend-badge trend-up">
                <i class="bi bi-check2-circle"></i>
                <span>Top up berstatus sukses</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
            <div class="card-header">
                <span class="stat-label">Total Pengeluaran</span>
                <i class="bi bi-graph-up-arrow text-success fs-5"></i>
            </div>
            <div class="stat-value" style="font-size: 1.65rem;">
                Rp <?= number_format($total_keluar, 0, ',', '.'); ?>
            </div>
            <div class="trend-badge trend-up">
                <i class="bi bi-check2-circle"></i>
                <span>Pengeluaran berstatus sukses</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <div>
                    <h5 class="card-title">Ringkasan Sistem</h5>
                    <p class="page-subtitle mt-1">Informasi singkat mengenai operasional Saku Santri.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="p-3 rounded-4" style="background:#F8FAF9;">
                        <div class="text-muted mb-2"><i class="bi bi-people me-1"></i> Santri</div>
                        <div class="fw-bold fs-4"><?= number_format($total_santri, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-4" style="background:#F8FAF9;">
                        <div class="text-muted mb-2"><i class="bi bi-wallet2 me-1"></i> Saldo</div>
                        <div class="fw-bold fs-4">Rp <?= number_format($total_saldo, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-4" style="background:#F8FAF9;">
                        <div class="text-muted mb-2"><i class="bi bi-arrow-down-circle me-1"></i> Pemasukan</div>
                        <div class="fw-bold fs-4">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-4" style="background:#F8FAF9;">
                        <div class="text-muted mb-2"><i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran</div>
                        <div class="fw-bold fs-4">Rp <?= number_format($total_keluar, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title">Akses Cepat</span>
                <i class="bi bi-lightning-charge-fill text-success"></i>
            </div>
            <div class="d-grid gap-2">
                <a href="data_santri.php" class="btn-custom btn-custom-light justify-content-start">
                    <i class="bi bi-person-plus"></i> Kelola Data Santri
                </a>
                <a href="verifikasi_topup.php" class="btn-custom btn-custom-light justify-content-start">
                    <i class="bi bi-patch-check"></i> Verifikasi Top Up
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
