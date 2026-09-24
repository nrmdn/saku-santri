<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php';

// Atur zona waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

$pencarian_ditemukan = false;
$pesan_error = "";
$data_santri = null;
$tot_m = 0;
$tot_k = 0;
$transaksi_list = [];

// Tangkap prefill NIS jika ada
$prefill_nis = isset($_GET['nis']) ? e(trim($_GET['nis'])) : '';

// 1. Logika Pemrosesan Pencarian Cek Saldo Santri
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['aksi_cek_saldo'])) {
    // Validasi CSRF Token
    verify_csrf_token($_POST['csrf_token'] ?? '', true);

    // Rate limiting anti-scraping sederhana berbasis session & IP
    $now = time();
    if (!isset($_SESSION['last_search_time'])) {
        $_SESSION['last_search_time'] = $now;
        $_SESSION['search_count'] = 1;
    } else {
        if ($now - $_SESSION['last_search_time'] < 60) {
            $_SESSION['search_count']++;
            if ($_SESSION['search_count'] > 15) {
                $pesan_error = "Terlalu banyak permintaan pencarian. Mohon tunggu 1 menit sebelum mencoba kembali demi keamanan.";
            }
        } else {
            $_SESSION['last_search_time'] = $now;
            $_SESSION['search_count'] = 1;
        }
    }

    if (empty($pesan_error)) {
        $nis_input        = trim($_POST['nis'] ?? '');
        $verifikasi_input = trim($_POST['verifikasi'] ?? ''); // Email atau No HP

        if (empty($nis_input) || empty($verifikasi_input)) {
            $pesan_error = "Harap isi seluruh kolom: NIS dan Data Verifikasi (No. HP/Email).";
        } else {
            // Prepared Statement Pencarian Santri (Hanya NIS & Kontak, Nama Dihapus agar aman)
            $sql = "SELECT * FROM santri 
                    WHERE nis = ? 
                      AND (email = ? OR no_hp_ortu = ?) 
                      AND deleted_at IS NULL 
                    LIMIT 1";

            $stmt = mysqli_prepare($koneksi, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $nis_input, $verifikasi_input, $verifikasi_input);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);

                if ($res && mysqli_num_rows($res) > 0) {
                    $data_santri = mysqli_fetch_assoc($res);
                    $pencarian_ditemukan = true;
                    $id_s = $data_santri['id'];

                    // Hitung total masuk
                    $stmt_m = mysqli_prepare($koneksi, "SELECT SUM(nominal) as tot FROM transaksi WHERE santri_id = ? AND jenis_transaksi = 'masuk' AND status = 'sukses'");
                    if ($stmt_m) {
                        mysqli_stmt_bind_param($stmt_m, "i", $id_s);
                        mysqli_stmt_execute($stmt_m);
                        $res_m = mysqli_stmt_get_result($stmt_m);
                        $tot_m = (float)(mysqli_fetch_assoc($res_m)['tot'] ?? 0);
                        mysqli_stmt_close($stmt_m);
                    }

                    // Hitung total keluar
                    $stmt_k = mysqli_prepare($koneksi, "SELECT SUM(nominal) as tot FROM transaksi WHERE santri_id = ? AND jenis_transaksi = 'keluar' AND status = 'sukses'");
                    if ($stmt_k) {
                        mysqli_stmt_bind_param($stmt_k, "i", $id_s);
                        mysqli_stmt_execute($stmt_k);
                        $res_k = mysqli_stmt_get_result($stmt_k);
                        $tot_k = (float)(mysqli_fetch_assoc($res_k)['tot'] ?? 0);
                        mysqli_stmt_close($stmt_k);
                    }

                    // Riwayat 15 transaksi terakhir
                    $stmt_trx = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE santri_id = ? AND status = 'sukses' ORDER BY tanggal DESC LIMIT 15");
                    if ($stmt_trx) {
                        mysqli_stmt_bind_param($stmt_trx, "i", $id_s);
                        mysqli_stmt_execute($stmt_trx);
                        $res_trx = mysqli_stmt_get_result($stmt_trx);
                        while ($row = mysqli_fetch_assoc($res_trx)) {
                            $transaksi_list[] = $row;
                        }
                        mysqli_stmt_close($stmt_trx);
                    }

                } else {
                    $pesan_error = "Data tidak ditemukan! Pastikan NIS dan No. HP/Email cocok dengan data yang terdaftar di pondok.";
                }
                mysqli_stmt_close($stmt);
            } else {
                $pesan_error = "Terjadi kesalahan sistem saat mencari data. Silakan coba kembali.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Saldo & Mutasi Santri - Saku Santri</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0f6848;
            --primary-dark: #094731;
            --primary-light: #e8f5ee;
            --accent-color: #f59e0b;
            --accent-hover: #d97706;
            --dark-color: #1e293b;
            --light-bg: #f8fafc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 4px 20px rgba(15, 104, 72, 0.15);
            padding: 0.75rem 0;
        }
        .navbar-custom .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: clamp(1.1rem, 3vw, 1.25rem);
        }

        .page-header {
            background: linear-gradient(135deg, #094731 0%, #0f6848 100%);
            color: white;
            padding: clamp(35px, 6vw, 55px) 0 clamp(25px, 5vw, 45px);
            position: relative;
        }
        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(245, 158, 11, 0.18) 0%, transparent 50%);
            pointer-events: none;
        }

        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .card-custom {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(15, 104, 72, 0.15);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #0f6848, #094731);
            color: #ffffff;
            font-weight: 700;
            border: none;
            transition: all 0.25s ease;
        }
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #15803d, #0f6848);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(15, 104, 72, 0.25);
        }

        .footer-main {
            background-color: #094731;
            color: #ffffff;
            padding: 40px 0 20px;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                  <img src="assets/img/logo_ponpes.png" alt="Logo" class="me-2" style="height: 40px;">
                <div>
                    <span class="d-block lh-1">Saku Santri</span>
                    <small class="text-white" style="font-size: 10px; font-weight: 400; letter-spacing: 0.5px; line-height: 1.1;">Ponpes Al Habibatain Bumiayu</small>
                </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 pt-3 pt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="index.php"><i class="fas fa-home me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="topup.php"><i class="fas fa-wallet me-1"></i> Isi Saldo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-white" href="cek_saldo.php"><i class="fas fa-search-dollar me-1"></i> Cek Saldo</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="admin/login.php">
                            <i class="fas fa-lock me-1"></i> Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <header class="page-header">
        <div class="container position-relative">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white-50 text-decoration-none">Beranda</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Cek Saldo & Mutasi</li>
                </ol>
            </nav>
            <h1 class="mb-2">Portal Pengecekan Saldo Santri</h1>
            <p class="text-white-50 mb-0" style="max-width: 600px; font-size: 0.95rem;">
                Ketahui sisa saldo uang saku anak dan riwayat transaksinya dengan aman, transparan, dan terpercaya.
            </p>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="container my-4 my-md-5">
        <div class="row g-4 justify-content-center">

            <!-- FORM PENCARIAN -->
            <div class="col-lg-5">
                <div class="card-custom p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Cari Data Santri</h5>
                            <small class="text-muted">Masukkan identitas santri & wali</small>
                        </div>
                    </div>

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger border-0 shadow-sm py-2 px-3 small fw-semibold mb-3 rounded-3">
                            <i class="fas fa-exclamation-circle me-1"></i> <?= e($pesan_error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="cek_saldo.php" method="POST">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="aksi_cek_saldo" value="1">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Nomor Induk Santri (NIS)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-id-card"></i></span>
                                <input type="number"  oninput="javascript: if (this.value.length > 7) this.value = this.value.slice(0, 7);" inputmode="numeric" pattern="[0-9]{7}" class="form-control" name="nis" required value="<?= e($_POST['nis'] ?? $prefill_nis); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Email / No HP Wali Terdaftar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-shield-alt"></i></span>
                                <input type="text" class="form-control" name="verifikasi" required value="<?= e($_POST['verifikasi'] ?? ''); ?>">
                            </div>
                            <div class="form-text small" style="font-size: 11.5px;">
                                <i class="fas fa-lock me-1 text-success"></i> Harus sesuai dengan nomor WhatsApp atau email wali yang terdaftar di pondok.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-3 rounded-3 shadow-sm">
                            <i class="fas fa-search me-2"></i>Tampilkan Saldo & Riwayat
                        </button>
                    </form>

                    <!-- Info Bantuan / Lupa NIS -->
                    <?php
                    // Setup template pesan WA untuk bantuan lupa NIS
                    $pesan_wa_bantuan = "Assalamualaikum, Admin.\n\nSaya wali santri mengalami kendala saat cek saldo. Saya lupa NIS anak saya / Kontak saya belum terdaftar di sistem.\n\nMohon bantuannya untuk pengecekan data anak saya. Terima kasih.";
                    $link_wa_bantuan = "https://wa.me/6285803932175?text=" . rawurlencode($pesan_wa_bantuan);
                    ?>
                    <div class="mt-4 pt-4 border-top text-center">
                        <span class="fw-bold d-block mb-2 text-dark">Lupa NIS atau Kontak Tidak Cocok?</span>
                        <small class="text-muted d-block mb-3">Silakan klik tombol di bawah untuk meminta bantuan Pengurus mengecek data santri Anda agar cepat diproses.</small>
                        <a href="<?= $link_wa_bantuan; ?>" target="_blank" class="btn btn-success rounded-pill px-4 py-2 fw-bold w-100 shadow-sm" style="text-decoration: none;">
                            <i class="fab fa-whatsapp fs-5 me-2 align-middle"></i> Hubungi Pengurus via WA
                        </a>
                    </div>
                </div>
            </div>

            <!-- HASIL PENCARIAN / PANDUAN -->
            <div class="col-lg-7">
                <?php if ($pencarian_ditemukan && $data_santri): ?>
                    <?php
                    $foto = (!empty($data_santri['foto'])) ? $data_santri['foto'] : 'default.png';
                    ?>
                    <div class="card-custom p-4 h-100">
                        <!-- Header Profil Santri -->
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3 gap-2">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/<?= e($foto); ?>" class="rounded-circle border border-3 border-success shadow-sm me-3" width="65" height="65" style="object-fit: cover;" onerror="this.src='assets/images/default.png'">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= e($data_santri['nama']); ?></h5>
                                    <span class="badge bg-secondary" style="font-size: 11px;">NIS: <?= e($data_santri['nis']); ?></span>
                                    <small class="text-muted d-block mt-1" style="font-size: 12px;">Wali: <?= e($data_santri['nama_ortu'] ?? '-'); ?></small>
                                </div>
                            </div>
                            <!-- Tombol Langsung Isi Saldo -->
                            <a href="topup.php?nis=<?= urlencode($data_santri['nis']); ?>" class="btn btn-warning btn-sm fw-bold rounded-pill shadow-sm px-3 py-2 text-center mt-2 mt-sm-0">
                                <i class="fas fa-wallet me-1"></i> Isi Saldo Anak Ini
                            </a>
                        </div>

                        <!-- Ringkasan Saldo -->
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <div class="bg-success bg-opacity-10 p-3 rounded-4 text-center text-success border border-success border-opacity-25">
                                    <small class="fw-bold text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 1px;">Sisa Saldo Aktif Santri</small>
                                    <h2 class="fw-bold mb-0" style="font-size: clamp(1.6rem, 4.5vw, 2.2rem);"><?= format_rupiah($data_santri['saldo']); ?></h2>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded-3 text-center border">
                                    <small class="text-muted d-block" style="font-size: 11px;">Total Masuk (Top Up)</small>
                                    <span class="fw-bold text-success fs-6"><?= format_rupiah($tot_m); ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded-3 text-center border">
                                    <small class="text-muted d-block" style="font-size: 11px;">Total Pengeluaran</small>
                                    <span class="fw-bold text-danger fs-6"><?= format_rupiah($tot_k); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Mutasi -->
                        <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                            <h6 class="fw-bold mb-0 text-dark small"><i class="fas fa-history me-1 text-primary"></i> Mutasi Transaksi Terakhir</h6>
                            <span class="badge bg-light text-dark border" style="font-size: 10px;">15 Terakhir</span>
                        </div>

                        <div class="table-responsive rounded-3 border" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Tipe</th>
                                        <th>Nominal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transaksi_list)): ?>
                                        <?php foreach ($transaksi_list as $t): ?>
                                            <?php $in = ($t['jenis_transaksi'] === 'masuk'); ?>
                                            <tr>
                                                <td class="text-nowrap text-muted"><?= date('d/m/y H:i', strtotime($t['tanggal'])); ?></td>
                                                <td><span class="badge <?= $in ? 'bg-success' : 'bg-danger'; ?>"><?= $in ? 'Masuk' : 'Keluar'; ?></span></td>
                                                <td class="fw-bold text-nowrap <?= $in ? 'text-success' : 'text-danger'; ?>">
                                                    <?= $in ? '+' : '-'; ?> <?= format_rupiah($t['nominal']); ?>
                                                </td>
                                                <td class="small"><?= e($t['keterangan']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat transaksi.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Kartu Panduan Pengecekan -->
                    <div class="card-custom p-4 h-100 d-flex flex-column justify-content-center text-center">
                        <div class="mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 80px; height: 80px; font-size: 2.2rem;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Portal Transparansi Terproteksi</h4>
                        <p class="text-muted small mx-auto mb-4" style="max-width: 420px; line-height: 1.6;">
                            Untuk melindungi privasi data santri dari pihak luar, sistem menerapkan autentikasi: <strong>NIS Santri</strong> dan <strong>Nomor HP/Email Wali yang Terdaftar</strong>.
                        </p>
                        <div class="row g-3 text-start justify-content-center">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fw-bold text-success small mb-1"><i class="fas fa-check-circle me-1"></i> Data Selalu Terkini</div>
                                    <small class="text-muted">Saldo dan mutasi diperbarui secara real-time setiap ada transaksi.</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fw-bold text-warning small mb-1"><i class="fas fa-bolt me-1"></i> Isi Saldo Langsung</div>
                                    <small class="text-muted">Dapat langsung melakukan top up setelah memeriksa saldo anak.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer-main">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <h5 class="d-flex align-items-center gap-2">
                        <i class="fas fa-credit-card text-warning"></i> Saku Santri
                    </h5>
                    <p class="text-white-50 small mb-3" style="max-width: 400px; line-height: 1.7;">
                        Platform portal transparansi keuangan santri Pondok Pesantren Al Habibatain Bumiayu.
                    </p>
                    <span class="badge bg-white bg-opacity-10 text-white p-2">
                        <img src="assets/img/logo_ponpes.png" alt="Logo" class="img-fluid" style="max-height: 20px;">
                        <span class="ms-1">Pondok Pesantren Al Habibatain Bumiayu</span>
                    </span>
                </div>
                <div class="col-lg-3 col-6">
                    <h6 class="text-white fw-bold mb-3">Tautan Cepat</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="index.php" class="text-white-50 text-decoration-none"><i class="fas fa-chevron-right me-1 text-warning"></i> Beranda</a></li>
                        <li><a href="topup.php" class="text-white-50 text-decoration-none"><i class="fas fa-chevron-right me-1 text-warning"></i> Isi Saldo Santri</a></li>
                        <li><a href="cek_saldo.php" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-1 text-warning"></i> Cek Saldo & Mutasi</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-7">
                    <h5>Pusat Bantuan</h5>
                    <p class="small text-white-50 mb-2">
                        Kendala transaksi atau pengecekan kartu?
                    </p>
                    <div class="d-flex align-items-center gap-2 mb-2 text-white">
                        <i class="fab fa-whatsapp text-success fs-5"></i>
                        <a href="https://wa.me/6282225113957" target="_blank" class="small text-white text-decoration-none">
                            +62 822-2511-3957
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2 text-white">
                        <i class="fab fa-instagram text-danger fs-5"></i>
                        <a href="https://www.instagram.com/alhabibatainbumiayu?stkn=YXA0YWZmajNjaG5zhttps:" target="_blank" class="small text-white text-decoration-none">
                            @alhabibatainbumiayu
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-white">
                        <i class="fas fa-envelope text-warning fs-5"></i>
                        <a href="mailto:alhabibatain210@gmail.com" class="small text-white text-decoration-none">
                            alhabibatain210@gmail.com
                        </a>
                    </div>
                </div>
            </div>
            <div class="pt-3 border-top border-white border-opacity-10 d-flex flex-wrap justify-content-between align-items-center text-white-50 small gap-2">
                <div>&copy; <?= date('Y'); ?> Saku Santri - PP. Al Habibatain Bumiayu.</div>
                <div><a href="admin/login.php" class="text-white-50 text-decoration-none"><i class="fas fa-lock me-1"></i> Login Pengurus Sistem</a></div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>