<?php
require '../config/koneksi.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/auth.php';
require_permission('laporan_cash');
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 1. Tangkap Parameter Filter Tanggal (Jika ada)
$tgl_mulai   = isset($_GET['tgl_mulai']) ? trim((string)$_GET['tgl_mulai']) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim((string)$_GET['tgl_selesai']) : '';

if (!validate_date($tgl_mulai, 'Y-m-d')) { $tgl_mulai = ''; }
if (!validate_date($tgl_selesai, 'Y-m-d')) { $tgl_selesai = ''; }

$sql_filter_standar = "";
$sql_filter_join = "";
$info_filter = "";

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql_filter_standar = " AND DATE(tanggal) BETWEEN '{$tgl_mulai}' AND '{$tgl_selesai}'";
    $sql_filter_join    = " AND DATE(transaksi.tanggal) BETWEEN '{$tgl_mulai}' AND '{$tgl_selesai}'";
    $info_filter        = " <span class='badge bg-warning text-dark ms-2 fs-6'>(Periode: " . date('d/m/Y', strtotime($tgl_mulai)) . " - " . date('d/m/Y', strtotime($tgl_selesai)) . ")</span>";
}

// 2. Hitung Total Kredit (Uang Masuk Keseluruhan + Filter)
$q_kredit = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_kredit FROM transaksi WHERE jenis_transaksi = 'masuk' AND status = 'sukses' $sql_filter_standar");
$d_kredit = mysqli_fetch_assoc($q_kredit);
$total_kredit = $d_kredit['total_kredit'] ? $d_kredit['total_kredit'] : 0;

// 3. Hitung Total Debit (Uang Keluar Keseluruhan + Filter)
$q_debit = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_debit FROM transaksi WHERE jenis_transaksi = 'keluar' AND status = 'sukses' $sql_filter_standar");
$d_debit = mysqli_fetch_assoc($q_debit);
$total_debit = $d_debit['total_debit'] ? $d_debit['total_debit'] : 0;

// 4. Hitung Total Saldo Aktif Keseluruhan (ATM Bendahara - Tetap Kumulatif Total)
$q_saldo_kumulatif = mysqli_query($koneksi, "SELECT SUM(saldo) AS total_saldo FROM santri");
$d_saldo_kumulatif = mysqli_fetch_assoc($q_saldo_kumulatif);
$total_saldo_bendahara = $d_saldo_kumulatif['total_saldo'] ? $d_saldo_kumulatif['total_saldo'] : 0;

include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<div class="container-fluid">
    <!-- ALERT NOTIFIKASI LAPORAN CASH -->
    <?php if (isset($_SESSION['laporan_message'])): ?>
        <div class="alert alert-<?= $_SESSION['laporan_status']; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <?= htmlspecialchars($_SESSION['laporan_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
            unset($_SESSION['laporan_message']);
            unset($_SESSION['laporan_status']);
        ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0 fs-3">Laporan Arus Kas <?= $info_filter; ?></h2>
        
        <div class="d-flex gap-2">
            <!-- Tombol Tambah Cash Masuk -->
            <button type="button" class="btn btn-success btn-sm shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahCash">
                <i class="fas fa-plus-circle me-1"></i>Tambah Cash
            </button>
            
            <!-- Tombol Trigger Modal Filter Tanggal -->
            <button type="button" class="btn btn-primary btn-sm shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalFilter">
                <i class="fas fa-calendar-alt me-1"></i>Filter
            </button>

            <!-- Tombol Reset Filter (Muncul jika filter aktif) -->
            <?php if(!empty($tgl_mulai) || !empty($tgl_selesai)) { ?>
                <a href="laporan_cash.php" class="btn btn-outline-danger btn-sm shadow-sm px-3 fw-bold">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            <?php } ?>
            
            <!-- Tombol Cetak PDF -->
            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCetakPDF">
                <i class="fas fa-file-pdf me-1 text-danger"></i>Cetak PDF
            </button>
        </div>
    </div>

    <!-- TATA LETAK CARD ATAS: KIRI KELUAR, KANAN MASUK (Ukuran Compact) -->
    <div class="row mb-3">
        <!-- Kanan: Card Kredit (Masuk) -->
        <div class="col-md-6 mb-2">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100 rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted d-block fw-bold" style="font-size: 13px; text-transform: uppercase;">Total Kredit (Masuk)</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($total_kredit, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 px-3 py-2 rounded-circle">
                            <i class="fas fa-arrow-down text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Kiri: Card Debit (Keluar) -->
        <div class="col-md-6 mb-2">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100 rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted d-block fw-bold" style="font-size: 13px; text-transform: uppercase;">Total Debit (Keluar)</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1">Rp <?= number_format($total_debit, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 px-3 py-2 rounded-circle">
                            <i class="fas fa-arrow-up text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Sisa Saldo ATM Bendahara -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-start border-primary border-4 bg-primary bg-opacity-10 rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-primary d-block fw-bold" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Uang di ATM Bendahara (Kumulatif Seluruh Santri)</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1">Rp <?= number_format($total_saldo_bendahara, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-wallet fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DUA KARTU RIWAYAT TRANSAKSI BERSEBELAHAN (MASUK & KELUAR) -->
    <div class="row">
        <!-- Tabel Uang Masuk (Kredit) -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0 border-top border-success border-3 h-100 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold text-success mb-0"><i class="fas fa-arrow-down me-2"></i>Riwayat Kredit (Uang Masuk)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="15%" class="py-3 text-dark">Tanggal</th>
                                    <th width="25%" class="py-3 text-dark">Nama Santri</th>
                                    <th width="20%" class="py-3 text-dark">Nominal Masuk</th>
                                    <th width="40%" class="py-3 text-dark">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q_masuk = mysqli_query($koneksi, "SELECT transaksi.*, santri.nama, santri.nis 
                                                                   FROM transaksi 
                                                                   JOIN santri ON transaksi.santri_id = santri.id 
                                                                   WHERE transaksi.jenis_transaksi = 'masuk' AND transaksi.status = 'sukses' $sql_filter_join 
                                                                   ORDER BY transaksi.tanggal DESC");
                                if(mysqli_num_rows($q_masuk) > 0) {
                                    while($row_m = mysqli_fetch_assoc($q_masuk)) {
                                ?>
                                <tr>
                                    <td class="py-3">
                                        <i class="far fa-calendar text-muted me-1"></i>
                                        <strong><?= date('d/m/y', strtotime($row_m['tanggal'])); ?></strong><br>
                                        <small class="text-muted ms-4" style="font-size: 12px;"><?= date('H:i', strtotime($row_m['tanggal'])); ?> WIB</small>
                                    </td>
                                    <td class="py-3">
                                        <strong><?= htmlspecialchars($row_m['nama']); ?></strong><br>
                                        <small class="text-muted">NIS: <?= $row_m['nis']; ?></small>
                                    </td>
                                    <td class="py-3 text-success fw-bold fs-6">+ Rp <?= number_format($row_m['nominal'], 0, ',', '.'); ?></td>
                                    <td class="py-3 text-secondary"><?= htmlspecialchars($row_m['keterangan']); ?></td>
                                </tr>
                                <?php 
                                    } 
                                } else { 
                                    echo "<tr><td colspan='4' class='text-center text-muted py-5'>Belum ada riwayat uang masuk pada periode ini.</td></tr>"; 
                                } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Uang Keluar (Debit) -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0 border-top border-danger border-3 h-100 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold text-danger mb-0"><i class="fas fa-arrow-up me-2"></i>Riwayat Debit (Uang Keluar)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="15%" class="py-3 text-dark">Tanggal</th>
                                    <th width="25%" class="py-3 text-dark">Nama Santri</th>
                                    <th width="20%" class="py-3 text-dark">Nominal Keluar</th>
                                    <th width="40%" class="py-3 text-dark">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q_keluar = mysqli_query($koneksi, "SELECT transaksi.*, santri.nama, santri.nis 
                                                                    FROM transaksi 
                                                                    JOIN santri ON transaksi.santri_id = santri.id 
                                                                    WHERE transaksi.jenis_transaksi = 'keluar' AND transaksi.status = 'sukses' $sql_filter_join 
                                                                    ORDER BY transaksi.tanggal DESC");
                                if(mysqli_num_rows($q_keluar) > 0) {
                                    while($row_k = mysqli_fetch_assoc($q_keluar)) {
                                ?>
                                <tr>
                                    <td class="py-3">
                                        <i class="far fa-calendar text-muted me-1"></i>
                                        <strong><?= date('d/m/y', strtotime($row_k['tanggal'])); ?></strong><br>
                                        <small class="text-muted ms-4" style="font-size: 12px;"><?= date('H:i', strtotime($row_k['tanggal'])); ?> WIB</small>
                                    </td>
                                    <td class="py-3">
                                        <strong><?= htmlspecialchars($row_k['nama']); ?></strong><br>
                                        <small class="text-muted">NIS: <?= $row_k['nis']; ?></small>
                                    </td>
                                    <td class="py-3 text-danger fw-bold fs-6">- Rp <?= number_format($row_k['nominal'], 0, ',', '.'); ?></td>
                                    <td class="py-3 text-secondary"><?= htmlspecialchars($row_k['keterangan']); ?></td>
                                </tr>
                                <?php 
                                    } 
                                } else { 
                                    echo "<tr><td colspan='4' class='text-center text-muted py-5'>Belum ada riwayat pengeluaran pada periode ini.</td></tr>"; 
                                } 
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
                <h6 class="modal-title fw-bold"><i class="fas fa-filter me-2"></i>Filter Tanggal Laporan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="laporan_cash.php">
                <div class="modal-body p-4">
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
                    <button type="submit" class="btn btn-primary px-4 rounded-pill small fw-bold">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL TAMBAH CASH MASUK                        -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahCash" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Cash Masuk (Top Up)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="proses_tambah_cash_global.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                        <label class="form-label fw-bold text-secondary">Pilih Santri</label>
                        <select class="form-select rounded-3" name="santri_id" required>
                            <option value="" selected disabled>-- Cari dan Pilih Santri --</option>
                            <?php
                            $q_santri = mysqli_query($koneksi, "SELECT id, nis, nama FROM santri ORDER BY nama ASC");
                            while($s = mysqli_fetch_assoc($q_santri)) {
                                echo "<option value='{$s['id']}'>[{$s['nis']}] {$s['nama']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nominal Cash Masuk (Rp)</label>
                        <input type="number" class="form-control form-control-lg rounded-3 border-success" name="nominal" required min="10000" placeholder="Minimal Rp10.000">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Keterangan / Sumber Dana</label>
                        <textarea class="form-control rounded-3" name="keterangan" rows="2" placeholder="Contoh: Setoran tunai melalui bendahara" required></textarea>
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
<!-- MODAL FILTER CETAK PDF                         -->
<!-- ============================================== -->
<div class="modal fade" id="modalCetakPDF" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-file-pdf me-2"></i>Cetak Laporan PDF</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="cetak_pdf_kas.php" method="GET" target="_blank">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Pilih Jenis Laporan</label>
                        <select class="form-select rounded-3" name="filter_jenis" id="filterJenis" onchange="toggleSantri()" required>
                            <option value="semua">Semua (Masuk & Keluar)</option>
                            <option value="masuk">Hanya Kredit (Uang Masuk)</option>
                            <option value="keluar">Hanya Debit (Uang Keluar)</option>
                            <option value="persantri">Laporan Khusus Per Santri</option>
                        </select>
                    </div>

                    <div class="mb-4" id="wrapPilihSantri" style="display: none;">
                        <label class="form-label fw-bold text-secondary">Pilih Santri</label>
                        <select class="form-select rounded-3" name="santri_id">
                            <option value="" selected disabled>-- Pilih Santri --</option>
                            <?php
                            $q_santri_opt = mysqli_query($koneksi, "SELECT id, nis, nama FROM santri ORDER BY nama ASC");
                            while($so = mysqli_fetch_assoc($q_santri_opt)) {
                                echo "<option value='{$so['id']}'>[{$so['nis']}] {$so['nama']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <hr class="text-muted">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>Filter Rentang Waktu (Opsional)</h6>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Dari Tanggal</label>
                            <input type="date" class="form-control rounded-3" name="tgl_mulai" value="<?= $tgl_mulai; ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Sampai Tanggal</label>
                            <input type="date" class="form-control rounded-3" name="tgl_selesai" value="<?= $tgl_selesai; ?>">
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block" style="font-size: 11px;">*Kosongkan kolom tanggal di atas jika ingin mencetak semua riwayat dari awal.</small>

                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold"><i class="fas fa-print me-2"></i>Generate PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Logika untuk menampilkan pilihan santri hanya jika filter "Per Santri" dipilih
function toggleSantri() {
    var jenis = document.getElementById('filterJenis').value;
    var wrap = document.getElementById('wrapPilihSantri');
    if (jenis === 'persantri') {
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
}
</script>

<?php include 'layout/footer.php'; ?>
