<?php 
// 1. Panggil koneksi
require '../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_permission('view_santri');
date_default_timezone_set('Asia/Jakarta');
// 2. Keamanan: Cek parameter 'id'
$id_santri = validate_id($_GET['id'] ?? null);
if (!$id_santri) {
    echo "<script>
            alert('Akses ditolak! ID Santri tidak ditemukan.');
            window.location.href = 'data_santri.php';
          </script>";
    exit;
}

// 3. Query Profil Santri
$stmt = mysqli_prepare($koneksi, "SELECT * FROM santri WHERE id = ? AND deleted_at IS NULL LIMIT 1");
if (!$stmt) {
    echo "<script>alert('Terjadi gangguan saat mengambil data santri.');window.location.href='data_santri.php';</script>";
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id_santri);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) == 0) {
    mysqli_stmt_close($stmt);
    echo "<script>
            alert('Data santri tidak ditemukan!');
            window.location.href = 'data_santri.php';
          </script>";
    exit;
}

$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
$foto_santri = (!empty($data['foto'])) ? $data['foto'] : 'default.png';

// 4. Tangkap Parameter Filter Tanggal (Jika ada)
$tgl_mulai   = isset($_GET['tgl_mulai']) ? trim((string)$_GET['tgl_mulai']) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim((string)$_GET['tgl_selesai']) : '';

if (!validate_date($tgl_mulai, 'Y-m-d')) { $tgl_mulai = ''; }
if (!validate_date($tgl_selesai, 'Y-m-d')) { $tgl_selesai = ''; }

$sql_filter = "";
$info_filter = "";
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql_filter = " AND DATE(tanggal) BETWEEN '{$tgl_mulai}' AND '{$tgl_selesai}'";
    $info_filter = " (Periode: " . date('d/m/y', strtotime($tgl_mulai)) . " - " . date('d/m/y', strtotime($tgl_selesai)) . ")";
}

// 5. Hitung Total Uang Masuk (Sesuai Filter)
$q_masuk = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_masuk FROM transaksi WHERE santri_id = {$id_santri} AND jenis_transaksi = 'masuk' AND status = 'sukses' {$sql_filter}");
$d_masuk = mysqli_fetch_assoc($q_masuk);
$total_masuk = $d_masuk['total_masuk'] ? $d_masuk['total_masuk'] : 0;

// 6. Hitung Total Uang Keluar (Sesuai Filter)
$q_keluar = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_keluar FROM transaksi WHERE santri_id = {$id_santri} AND jenis_transaksi = 'keluar' AND status = 'sukses' {$sql_filter}");
$d_keluar = mysqli_fetch_assoc($q_keluar);
$total_keluar = $d_keluar['total_keluar'] ? $d_keluar['total_keluar'] : 0;

include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<style>
    /* CSS Kustom untuk Tampilan Kartu yang Premium */
    .bg-gradient-dark { background: linear-gradient(135deg, #141E30 0%, #243B55 100%); }
    
    /* Warna Emerald Mewah ala Kartu Bank */
    .bg-gradient-emerald { 
        background: linear-gradient(135deg, #059669 0%, #047857 50%, #064e3b 100%); 
        position: relative;
    }
    
    .btn-action { padding: 10px 15px; border-radius: 8px; font-weight: 600; }
    
    /* Styling khusus Kartu ATM/Bank (Sudah Diperbaiki) */
    .bank-card {
        border-radius: 16px;
        box-shadow: 0 10px 20px rgba(4, 120, 87, 0.3);
        min-height: 200px; /* Diubah menggunakan min-height agar tidak kepotong */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        padding: 1.5rem; 
    }
    
    .bank-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        z-index: 0;
    }
    
    .bank-card::after {
        content: '';
        position: absolute;
        bottom: -30px;
        right: 40px;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        z-index: 0;
    }
</style>

<div class="container-fluid">
  
    <!-- BARIS HEADER DAN NAVIGASI -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Detail Profil Santri</h3>
            <p class="text-muted small mb-0">Manajemen biodata dan dompet digital santri</p>
        </div>
        <a href="data_santri.php" class="btn btn-outline-secondary px-4 py-2 shadow-sm rounded-pill fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- KARTU PROFIL UTAMA (MEWAH & ELEGAN) -->
    <div class="card border-0 shadow rounded-4 overflow-hidden mb-4">
        <div class="card-header border-0 py-3 px-4 bg-gradient-dark">
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge bg-white text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-check-circle me-1 text-success"></i> Santri Aktif
                </span>
               
            </div>
        </div>
        
        <div class="card-body p-4 p-lg-5 bg-white">
            <div class="row g-4 align-items-center">
                
                <!-- KOLOM FOTO & IDENTITAS -->
                <div class="col-lg-3 text-center border-end-lg pe-lg-4">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="../assets/images/<?= htmlspecialchars($foto_santri); ?>" alt="Foto Santri" class="rounded-circle border border-4 border-light shadow" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($data['nama']); ?></h5>
                    <span class="badge bg-light text-secondary border px-3 py-2 mt-1 rounded-pill">
                        <?= ($data['jk'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?>
                    </span>
                </div>

                <!-- KOLOM BIODATA & NIS -->
                <div class="col-lg-5 ps-lg-4">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3" style="letter-spacing: 1px;">
                        <i class="fas fa-address-card me-2 text-primary"></i>Informasi Santri & Wali
                    </h6>
                    
                    <div class="d-flex align-items-center mb-3 p-3 rounded-3 bg-light border border-light shadow-sm">
                        <div class="bg-white p-2 rounded shadow-sm me-3">
                            <i class="fas fa-id-badge text-primary fs-5" style="width: 25px; text-align: center;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold" style="font-size: 11px; text-transform: uppercase;">Nomor Induk Santri (NIS)</small>
                            <span class="fw-bold text-dark fs-6" style="letter-spacing: 1px;"><?= htmlspecialchars($data['nis']); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3 p-3 rounded-3 bg-light border border-light shadow-sm">
                        <div class="bg-white p-2 rounded shadow-sm me-3">
                            <i class="fas fa-calendar-alt text-primary fs-5" style="width: 25px; text-align: center;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold" style="font-size: 11px; text-transform: uppercase;">Tempat, Tanggal Lahir</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($data['ttl'] ?? '-'); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3 p-3 rounded-3 bg-light border border-light shadow-sm">
                        <div class="bg-white p-2 rounded shadow-sm me-3">
                            <i class="fas fa-map-marker-alt text-primary fs-5" style="width: 25px; text-align: center;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold" style="font-size: 11px; text-transform: uppercase;">Alamat Lengkap</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($data['alamat'] ?? '-'); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3 p-3 rounded-3 bg-light border border-light shadow-sm">
                        <div class="bg-white p-2 rounded shadow-sm me-3">
                            <i class="fas fa-user-friends text-primary fs-5" style="width: 25px; text-align: center;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold" style="font-size: 11px; text-transform: uppercase;">Orang Tua / Wali</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($data['nama_ortu'] ?? '-'); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center p-3 rounded-3 bg-light border border-light shadow-sm">
                        <div class="bg-white p-2 rounded shadow-sm me-3">
                            <i class="fab fa-whatsapp text-success fs-5" style="width: 25px; text-align: center;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold" style="font-size: 11px; text-transform: uppercase;">Kontak Wali (HP/Email)</small>
                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($data['no_hp_ortu'] ?? '-'); ?></span>
                            <span class="text-muted small"><?= htmlspecialchars($data['email'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- KOLOM WIDGET KARTU BANK SALDO & TOMBOL -->
                <div class="col-lg-4">
                    <!-- Kartu Saldo Emerald Mewah ala Kartu Bank (Sudah Diperbaiki Ukuran Font & Layoutnya) -->
                    <div class="bg-gradient-emerald text-white bank-card mb-3 position-relative">
                        <!-- Bagian Atas Kartu (Chip & Contactless) -->
                        <div class="d-flex justify-content-between align-items-center position-relative z-index-1 mb-2">
                            <div>
                                <i class="fas fa-microchip fs-3" style="color: #fbbf24;"></i> <!-- Warna Emas -->
                                <i class="fas fa-wifi ms-2 text-white opacity-75" style="transform: rotate(90deg); font-size: 1.1rem;"></i>
                            </div>
                            <span class="fw-bold fst-italic opacity-75" style="letter-spacing: 1px; font-size: 14px;">SMART CARD</span>
                        </div>
                        
                        <!-- Bagian Tengah Kartu (Saldo) -->
                        <div class="position-relative z-index-1 my-2">
                            <span class="d-block text-white opacity-75 text-uppercase fw-bold mb-1" style="letter-spacing: 1px; font-size: 11px;">Sisa Saldo Aktif</span>
                            <h3 class="fw-bold text-white mb-0" style="letter-spacing: 1px; text-shadow: 1px 1px 3px rgba(0,0,0,0.2); font-size: 1.5rem;">
                                Rp <?= number_format($data['saldo'], 0, ',', '.'); ?>
                            </h3>
                        </div>

                        <!-- Bagian Bawah Kartu (Nama) -->
                        <div class="d-flex justify-content-between align-items-end position-relative z-index-1 mt-auto pt-2">
                            <div>
                                <span class="d-block text-white opacity-75 small text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Cardholder Name</span>
                                <span class="fw-bold text-uppercase text-white" style="letter-spacing: 1px; font-size: 14px; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                                    <?= htmlspecialchars($data['nama']); ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <i class="fas fa-check-circle text-white opacity-75 fs-5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi (Spacious & Clean) -->
                    <div class="d-flex gap-3 mt-3">
                        <button type="button" class="btn btn-success flex-fill btn-action shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#modalTopUp">
                            <i class="fas fa-plus-circle me-2"></i>Top Up
                        </button>
                        <button type="button" class="btn btn-danger flex-fill btn-action shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#modalPengeluaran">
                            <i class="fas fa-minus-circle me-2"></i>Tarik
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- HEADER DATA TRANSAKSI & TOMBOL FILTER -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Data Transaksi & Mutasi</h4>
            <span class="text-muted small">Ringkasan kas <?= $info_filter ?: 'Keseluruhan'; ?></span>
        </div>
        <div>
            <?php if(!empty($tgl_mulai) || !empty($tgl_selesai)) { ?>
                <a href="detail_santri.php?id=<?= $id_santri; ?>" class="btn btn-outline-danger px-4 py-2 rounded-pill shadow-sm fw-bold me-2">
                    <i class="fas fa-times me-2"></i>Reset Filter
                </a>
            <?php } ?>
            <button class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalFilter">
                <i class="fas fa-calendar-day me-2"></i>Filter Tanggal
            </button>
        </div>
    </div>

    <!-- KARTU RINGKASAN MASUK & KELUAR -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted d-block fw-bold mb-1" style="font-size: 13px; text-transform: uppercase;">Total Pemasukan</span>
                            <h3 class="fw-bold text-success mb-0">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-arrow-down text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted d-block fw-bold mb-1" style="font-size: 13px; text-transform: uppercase;">Total Pengeluaran</span>
                            <h3 class="fw-bold text-danger mb-0">Rp <?= number_format($total_keluar, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-arrow-up text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. RIWAYAT UANG MASUK (ATAS) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-top border-success border-3 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold text-success mb-0">
                        <i class="fas fa-arrow-down me-2"></i>Riwayat Kredit (Masuk)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="25%" class="py-3 ps-3">Tanggal & Waktu</th>
                                    <th width="25%" class="py-3">Nominal Masuk</th>
                                    <th class="py-3">Keterangan Tambahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q_hist_masuk = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE santri_id = '$id_santri' AND jenis_transaksi = 'masuk' AND status = 'sukses' $sql_filter ORDER BY tanggal DESC");
                                if(mysqli_num_rows($q_hist_masuk) > 0) {
                                    while($row_m = mysqli_fetch_assoc($q_hist_masuk)) {
                                ?>
                                <tr>
                                    <td class="py-3 ps-3">
                                        <i class="far fa-calendar text-muted me-2"></i>
                                        <strong class="text-dark"><?= date('d/m/Y', strtotime($row_m['tanggal'])); ?></strong> 
                                        <span class="text-muted ms-2 px-2 py-1 bg-light rounded small"><?= date('H:i', strtotime($row_m['tanggal'])); ?> WIB</span>
                                    </td>
                                    <td class="text-success fw-bold py-3 fs-6">+ Rp <?= number_format($row_m['nominal'], 0, ',', '.'); ?></td>
                                    <td class="py-3 text-secondary"><?= htmlspecialchars($row_m['keterangan']); ?></td>
                                </tr>
                                <?php 
                                    } 
                                } else { echo "<tr><td colspan='3' class='text-center text-muted py-5'>Tidak ada riwayat uang masuk pada periode ini.</td></tr>"; } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. RIWAYAT UANG KELUAR (BAWAH) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-top border-danger border-3 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold text-danger mb-0">
                        <i class="fas fa-arrow-up me-2"></i>Riwayat Debit (Keluar)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="25%" class="py-3 ps-3">Tanggal & Waktu</th>
                                    <th width="25%" class="py-3">Nominal Keluar</th>
                                    <th class="py-3">Keterangan Tambahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q_hist_keluar = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE santri_id = '$id_santri' AND jenis_transaksi = 'keluar' AND status = 'sukses' $sql_filter ORDER BY tanggal DESC");
                                if(mysqli_num_rows($q_hist_keluar) > 0) {
                                    while($row_k = mysqli_fetch_assoc($q_hist_keluar)) {
                                ?>
                                <tr>
                                    <td class="py-3 ps-3">
                                        <i class="far fa-calendar text-muted me-2"></i>
                                        <strong class="text-dark"><?= date('d/m/Y', strtotime($row_k['tanggal'])); ?></strong> 
                                        <span class="text-muted ms-2 px-2 py-1 bg-light rounded small"><?= date('H:i', strtotime($row_k['tanggal'])); ?> WIB</span>
                                    </td>
                                    <td class="text-danger fw-bold py-3 fs-6">- Rp <?= number_format($row_k['nominal'], 0, ',', '.'); ?></td>
                                    <td class="py-3 text-secondary"><?= htmlspecialchars($row_k['keterangan']); ?></td>
                                </tr>
                                <?php 
                                    } 
                                } else { echo "<tr><td colspan='3' class='text-center text-muted py-5'>Tidak ada riwayat pengeluaran pada periode ini.</td></tr>"; } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================== -->
<!-- MODAL KECIL: FILTER TANGGAL                    -->
<!-- ============================================== -->
<div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h6 class="modal-title text-white fw-bold"><i class="fas fa-filter me-2"></i>Filter Tanggal</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="detail_santri.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" value="<?= $id_santri; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Mulai Tanggal</label>
                        <input type="date" class="form-control rounded-3" name="tgl_mulai" value="<?= $tgl_mulai; ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-secondary small">Sampai Tanggal</label>
                        <input type="date" class="form-control rounded-3" name="tgl_selesai" value="<?= $tgl_selesai; ?>" required>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill small fw-bold">Tampilkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: TOP UP MANUAL                           -->
<!-- ============================================== -->
<div class="modal fade" id="modalTopUp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-wallet me-2"></i>Top Up Saldo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_topup_manual.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="santri_id" value="<?= $id_santri; ?>">
                    <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3 text-center">
                        Penerima Saldo: <br><strong><?= htmlspecialchars($data['nama']); ?></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nominal Top Up (Rp)</label>
                        <input type="number" class="form-control form-control-lg rounded-3 border-success" name="nominal" required min="1000" placeholder="Contoh: 50000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-secondary">Keterangan / Catatan</label>
                        <textarea class="form-control rounded-3" name="keterangan" rows="2" placeholder="Cth: Setoran Tunai Ortu" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-save me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: PENGELUARAN MANUAL                      -->
<!-- ============================================== -->
<div class="modal fade" id="modalPengeluaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-shopping-cart me-2"></i>Penarikan / Pengeluaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_pengeluaran_manual.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="santri_id" value="<?= $id_santri; ?>">
                    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 text-center">
                        Memotong saldo: <strong><?= htmlspecialchars($data['nama']); ?></strong><br>
                        Sisa saldo: <strong>Rp <?= number_format($data['saldo'], 0, ',', '.'); ?></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nominal Tarik (Rp)</label>
                        <input type="number" class="form-control form-control-lg rounded-3 border-danger" name="nominal" required min="1000" max="<?= $data['saldo']; ?>" placeholder="Contoh: 20000">
                        <small class="text-danger mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Maksimal: Rp <?= number_format($data['saldo'], 0, ',', '.'); ?></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-secondary">Keterangan</label>
                        <textarea class="form-control rounded-3" name="keterangan" rows="2" placeholder="Contoh: Beli kitab" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold"><i class="fas fa-save me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>