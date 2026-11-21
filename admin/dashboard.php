<?php
require '../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_permission('dashboard');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

$id_admin = $_SESSION['id_admin']; 
$query_admin = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id_admin'");
$data_admin = mysqli_fetch_assoc($query_admin);

$nama_admin     = $data_admin['nama_lengkap'];
$username_admin = $data_admin['username'];
$foto           = (!empty($data_admin['foto'])) ? $data_admin['foto'] : 'default.png';

// =================================================================
// 1. TANGKAP PARAMETER FILTER TANGGAL
// =================================================================
$tgl_mulai   = isset($_GET['tgl_mulai']) ? trim(mysqli_real_escape_string($koneksi, $_GET['tgl_mulai'])) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim(mysqli_real_escape_string($koneksi, $_GET['tgl_selesai'])) : '';

$sql_filter_trx = "";
$info_chart   = "7 Hari Terakhir";

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql_filter_trx = " AND DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
    $info_chart     = date('d/m/Y', strtotime($tgl_mulai)) . " - " . date('d/m/Y', strtotime($tgl_selesai));
}

// =================================================================
// 2. MENGAMBIL DATA STATISTIK UTAMA
// =================================================================
$q_santri = mysqli_query($koneksi, "SELECT COUNT(id) AS total_santri FROM santri WHERE deleted_at IS NULL");
$total_santri = (int) (mysqli_fetch_assoc($q_santri)['total_santri'] ?? 0);

$q_saldo = mysqli_query($koneksi, "SELECT SUM(saldo) AS total_saldo FROM santri");
$total_saldo = (float) (mysqli_fetch_assoc($q_saldo)['total_saldo'] ?? 0);

$q_pending = mysqli_query($koneksi, "SELECT COUNT(id) AS total_pending FROM transaksi WHERE status='pending' AND jenis_transaksi='masuk'");
$total_pending = (int) (mysqli_fetch_assoc($q_pending)['total_pending'] ?? 0);

$q_masuk = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_masuk FROM transaksi WHERE jenis_transaksi='masuk' AND status='sukses' $sql_filter_trx");
$total_masuk = (float) (mysqli_fetch_assoc($q_masuk)['total_masuk'] ?? 0);

$q_keluar = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_keluar FROM transaksi WHERE jenis_transaksi='keluar' AND status='sukses' $sql_filter_trx");
$total_keluar = (float) (mysqli_fetch_assoc($q_keluar)['total_keluar'] ?? 0);

// =================================================================
// 3. MENGAMBIL DATA UNTUK GRAFIK ARUS KAS
// =================================================================
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $start_date = $tgl_mulai;
    $end_date   = $tgl_selesai;
} else {
    $start_date = date('Y-m-d', strtotime("-6 days")); 
    $end_date   = date('Y-m-d');
}

$period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), (new DateTime($end_date))->modify('+1 day'));
$chart_data_masuk = [];
$chart_data_keluar = [];
$label_hari = [];

$max_days = 0;
foreach ($period as $dt) {
    if ($max_days > 60) break; 
    $date_str = $dt->format('Y-m-d');
    $label_hari[] = $dt->format('d M');
    $chart_data_masuk[$date_str] = 0;
    $chart_data_keluar[$date_str] = 0;
    $max_days++;
}

$q_chart_in = mysqli_query($koneksi, "SELECT DATE(tanggal) as tgl, SUM(nominal) as tot FROM transaksi WHERE jenis_transaksi='masuk' AND status='sukses' AND DATE(tanggal) BETWEEN '$start_date' AND '$end_date' GROUP BY DATE(tanggal)");
while($r = mysqli_fetch_assoc($q_chart_in)) {
    if (isset($chart_data_masuk[$r['tgl']])) $chart_data_masuk[$r['tgl']] = (int)$r['tot'];
}

$q_chart_out = mysqli_query($koneksi, "SELECT DATE(tanggal) as tgl, SUM(nominal) as tot FROM transaksi WHERE jenis_transaksi='keluar' AND status='sukses' AND DATE(tanggal) BETWEEN '$start_date' AND '$end_date' GROUP BY DATE(tanggal)");
while($r = mysqli_fetch_assoc($q_chart_out)) {
    if (isset($chart_data_keluar[$r['tgl']])) $chart_data_keluar[$r['tgl']] = (int)$r['tot'];
}

$data_masuk_chart = array_values($chart_data_masuk);
$data_keluar_chart = array_values($chart_data_keluar);

// =================================================================
// 4. MENGAMBIL DATA UNTUK GRAFIK GENDER SANTRI
// =================================================================
$q_jk = mysqli_query($koneksi, "SELECT jk, COUNT(id) AS tot FROM santri WHERE deleted_at IS NULL GROUP BY jk");
$santri_l = 0; $santri_p = 0;
while($r = mysqli_fetch_assoc($q_jk)){
    if($r['jk'] == 'L') $santri_l = $r['tot'];
    if($r['jk'] == 'P') $santri_p = $r['tot'];
}

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<style>
    body {
        background-color: #f7f9fa;
    }
    .card-modern-b {
        background: #ffffff;
        border: none !important;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        padding: 0.5rem;
    }
    .card-modern-b:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    }
    .label-stat {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        letter-spacing: 0.8px;
        line-height: 1.4;
    }
    .value-stat {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin-top: 25px;
    }
    .icon-box-b {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .filter-wrapper {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 8px 16px;
        border: 1px solid #e9ecef;
    }
    .filter-wrapper input[type="date"] {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: #495057;
        font-weight: 500;
        font-size: 13px;
        padding: 0;
        cursor: pointer;
    }
    .btn-kirim {
        background-color: #073a26;
        color: white;
        border-radius: 8px;
        font-weight: 600;
        padding: 6px 20px;
        font-size: 13px;
    }
    .btn-kirim:hover {
        background-color: #0a5236;
        color: white;
    }
    .btn-wa-cron {
        background-color: #25D366;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 22px;
        font-size: 14px;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-wa-cron:hover {
        background-color: #1eb956;
        color: white;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    }
    .swal2-popup {
        border-radius: 24px !important;
        padding: 1.8rem !important;
    }
</style>

<div class="container-fluid pb-4">
    
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Dashboard</h3>
        <p class="text-secondary mb-0">Pantau performa dan arus kas Saku Santri.</p>
    </div>

    <?php if ($total_pending > 0): ?>
    <div class="alert border-0 card-modern-b mb-4 p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3" style="background: linear-gradient(to right, #fff8e1, #ffffff);">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box-b bg-warning text-dark shadow-sm rounded-circle flex-shrink-0" style="font-size:18px;">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-1">Ada <?= $total_pending; ?> transaksi butuh verifikasi.</h6>
                <span class="text-secondary small">Harap periksa dan konfirmasi top-up saldo santri yang tertunda.</span>
            </div>
        </div>
        <div class="flex-shrink-0">
            <a href="verifikasi_topup.php" class="btn btn-warning btn-sm fw-bold text-dark rounded-pill px-4 shadow-sm w-100">
                Tinjau Sekarang <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- STATISTIK KARTU UTAMA -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-modern-b h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">SANTRI<br>AKTIF</div>
                        <div class="icon-box-b" style="background-color: #e6f0ff; color: #3b82f6;"><i class="bi bi-people-fill"></i></div>
                    </div>
                    <div class="value-stat text-truncate"><?= number_format($total_santri, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-modern-b h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">SALDO<br>TOTAL</div>
                        <div class="icon-box-b" style="background-color: #e0f7fa; color: #06b6d4;"><i class="bi bi-wallet2"></i></div>
                    </div>
                    <div class="value-stat text-truncate" title="Rp <?= number_format($total_saldo, 0, ',', '.'); ?>">Rp <?= number_format($total_saldo, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-modern-b h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">PEMASUKAN</div>
                        <div class="icon-box-b" style="background-color: #e8f5e9; color: #22c55e;"><i class="bi bi-graph-up-arrow"></i></div>
                    </div>
                    <div class="value-stat text-truncate" title="Rp <?= number_format($total_masuk, 0, ',', '.'); ?>">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-modern-b h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">PENGELUARAN</div>
                        <div class="icon-box-b" style="background-color: #ffebee; color: #ef4444;"><i class="bi bi-graph-down-arrow"></i></div>
                    </div>
                    <div class="value-stat text-truncate" title="Rp <?= number_format($total_keluar, 0, ',', '.'); ?>">Rp <?= number_format($total_keluar, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU FITUR PENGIRIMAN LAPORAN WA -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-modern-b p-3">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-box-b text-white shadow-sm rounded-3 flex-shrink-0" style="background-color: #25D366; font-size:22px;">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Pengiriman Laporan Keuangan WA</h6>
                            <p class="text-muted small mb-0">Generate laporan PDF bulanan dan kirimkan langsung ke WhatsApp seluruh wali santri secara otomatis.</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <button id="btnKirimLaporan" class="btn btn-wa-cron d-inline-flex align-items-center gap-2 shadow-sm" onclick="kirimLaporanWA()">
                            <i class="bi bi-send-fill"></i> Kirim Laporan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAFIK ARUS KAS & RASIO SANTRI -->
    <div class="row g-4 mb-2">
        <div class="col-12 col-lg-8">
            <div class="card card-modern-b h-100 p-2">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-bar-chart-fill text-success fs-5"></i> Tren Arus Kas
                        </h6>
                        <small class="text-muted" style="font-size:11px; margin-left: 28px;"><?= $info_chart; ?></small>
                    </div>
                    
                    <form method="GET" action="dashboard.php" class="filter-wrapper d-flex flex-wrap align-items-center justify-content-center gap-3 m-0">
                        <i class="bi bi-funnel-fill text-muted"></i>
                        <input type="date" name="tgl_mulai" class="form-control" style="width: 120px;" value="<?= $tgl_mulai; ?>" required>
                        <span class="text-muted fw-bold">-</span>
                        <input type="date" name="tgl_selesai" class="form-control" style="width: 120px;" value="<?= $tgl_selesai; ?>" required>
                        <button type="submit" class="btn btn-kirim">Kirim</button>
                        
                        <?php if (!empty($tgl_mulai)): ?>
                            <a href="dashboard.php" class="text-danger ms-1" title="Reset Filter"><i class="bi bi-x-circle-fill fs-6"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="card-body px-4 pb-3 mt-2">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="chartArusKas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card card-modern-b h-100 p-2">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-primary fs-5"></i> Rasio Santri
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 mt-2 d-flex flex-column justify-content-center align-items-center">
                    <div style="height: 210px; width: 100%; position: relative;">
                        <canvas id="chartGender"></canvas>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-5 mt-4 w-100">
                        <div class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                <span style="width: 12px; height: 12px; background-color: #0d6efd; border-radius: 4px; display: inline-block;"></span>
                                <span class="text-muted small fw-bold">Laki-laki</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-0"><?= $santri_l; ?></h5>
                        </div>
                        <div class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                <span style="width: 12px; height: 12px; background-color: #d63384; border-radius: 4px; display: inline-block;"></span>
                                <span class="text-muted small fw-bold">Perempuan</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-0"><?= $santri_p; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- SWEETALERT2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// FUNSI SWEETALERT PENGIRIMAN LAPORAN WA
function kirimLaporanWA() {
    Swal.fire({
        title: 'Kirim Laporan WA?',
        text: "Sistem akan membuat PDF rekapitulasi dan mengirimkannya ke SELURUH nomor WhatsApp wali santri terdata.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#25D366',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Kirim Sekarang!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-success rounded-pill px-4 ms-2',
            cancelButton: 'btn btn-secondary rounded-pill px-4'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan SweetAlert Loading
            Swal.fire({
                title: 'Proses Pengiriman Sedang Berjalan...',
                html: 'Sistem sedang mencetak PDF dan mengantrekan pengiriman WA.<br><small class="text-muted">Mohon tunggu dan jangan menutup halaman ini.</small>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Tembak file cron_laporan_wa.php via AJAX
            fetch('../cron_laporan_wa.php')
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: '🎉 Pengiriman Selesai!',
                        text: 'Laporan keuangan bulanan berhasil diproses dan dikirim ke WhatsApp wali santri.',
                        confirmButtonColor: '#25D366',
                        customClass: { confirmButton: 'btn btn-success rounded-pill px-4' },
                        buttonsStyling: false
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Gagal Memproses',
                        html: `${error.message}.<br><small class="text-muted">Pastikan Server Node.js WhatsApp Gateway aktif di Port 3000.</small>`,
                        confirmButtonColor: '#dc3545',
                        customClass: { confirmButton: 'btn btn-danger rounded-pill px-4' },
                        buttonsStyling: false
                    });
                });
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    
    // 1. GRAFIK ARUS KAS
    const ctxArusKas = document.getElementById('chartArusKas').getContext('2d');
    new Chart(ctxArusKas, {
        type: 'bar',
        data: {
            labels: <?= json_encode($label_hari); ?>, 
            datasets: [
                {
                    label: 'Uang Masuk',
                    data: <?= json_encode($data_masuk_chart); ?>,
                    backgroundColor: '#198754',
                    borderRadius: 6,
                    barPercentage: 0.5,
                    categoryPercentage: 0.7
                },
                {
                    label: 'Uang Keluar',
                    data: <?= json_encode($data_keluar_chart); ?>,
                    backgroundColor: '#dc3545',
                    borderRadius: 6,
                    barPercentage: 0.5,
                    categoryPercentage: 0.7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: { usePointStyle: true, boxWidth: 8, padding: 25, font: { family: "'Segoe UI', sans-serif", size: 12 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)', padding: 12, titleFont: { size: 12 }, bodyFont: { size: 13, weight: 'bold' },
                    callbacks: { label: function(context) { return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID'); } }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: "'Segoe UI', sans-serif", size:11 } } },
                y: {
                    border: { display: false }, grid: { color: '#f5f5f5', drawTicks: false },
                    ticks: {
                        padding: 10, font: { family: "'Segoe UI', sans-serif", size:11 },
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp' + (value / 1000000) + ' Jt';
                            if (value >= 1000) return 'Rp' + (value / 1000) + ' Rb';
                            return 'Rp' + value;
                        }
                    }
                }
            }
        }
    });

    // 2. GRAFIK DEMOGRAFI 
    const ctxGender = document.getElementById('chartGender').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [<?= $santri_l; ?>, <?= $santri_p; ?>],
                backgroundColor: ['#0d6efd', '#d63384'],
                borderWidth: 0, hoverOffset: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '78%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.parsed + ' Santri'; } } }
            }
        }
    });

});
</script>

<?php include 'layout/footer.php'; ?>