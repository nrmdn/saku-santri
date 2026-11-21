<?php
require '../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_permission('dashboard');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// =================================================================
// 1. PROSES AKSI HAPUS LOG
// =================================================================
$notif_type = '';
$notif_pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_hapus_log'])) {
    $jenis_hapus = mysqli_real_escape_string($koneksi, $_POST['jenis_hapus'] ?? '');

    if ($jenis_hapus === '30_hari') {
        $q_del = mysqli_query($koneksi, "DELETE FROM log_laporan_wa WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        if ($q_del) {
            $jumlah = mysqli_affected_rows($koneksi);
            $notif_type  = 'success';
            $notif_pesan = "Berhasil menghapus <b>$jumlah</b> data log yang berusia lebih dari 30 hari.";
        } else {
            $notif_type  = 'danger';
            $notif_pesan = 'Gagal menghapus log: ' . mysqli_error($koneksi);
        }
    } elseif ($jenis_hapus === 'semua') {
        $q_del = mysqli_query($koneksi, "TRUNCATE TABLE log_laporan_wa");
        if ($q_del) {
            $notif_type  = 'success';
            $notif_pesan = 'Seluruh data riwayat pengiriman WA berhasil dibersihkan.';
        } else {
            $notif_type  = 'danger';
            $notif_pesan = 'Gagal mengosongkan log: ' . mysqli_error($koneksi);
        }
    }
}

// =================================================================
// 2. TANGKAP FILTER PARAMETER
// =================================================================
$filter_status  = isset($_GET['status']) ? trim(mysqli_real_escape_string($koneksi, $_GET['status'])) : '';
$filter_periode = isset($_GET['periode']) ? trim(mysqli_real_escape_string($koneksi, $_GET['periode'])) : '';
$search_q       = isset($_GET['q']) ? trim(mysqli_real_escape_string($koneksi, $_GET['q'])) : '';

$where_conditions = [];

if (!empty($filter_status)) {
    $where_conditions[] = "status = '$filter_status'";
}
if (!empty($filter_periode)) {
    $where_conditions[] = "periode = '$filter_periode'";
}
if (!empty($search_q)) {
    $where_conditions[] = "(nama_santri LIKE '%$search_q%' OR nis LIKE '%$search_q%' OR no_wa LIKE '%$search_q%')";
}

$sql_where = "";
if (count($where_conditions) > 0) {
    $sql_where = "WHERE " . implode(' AND ', $where_conditions);
}

// =================================================================
// 3. QUERY RINGKASAN STATISTIK
// =================================================================
$q_sukses   = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM log_laporan_wa WHERE status='sukses'");
$q_gagal    = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM log_laporan_wa WHERE status='gagal'");
$q_dilewati = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM log_laporan_wa WHERE status='dilewati'");

$total_sukses   = (int)(mysqli_fetch_assoc($q_sukses)['total'] ?? 0);
$total_gagal    = (int)(mysqli_fetch_assoc($q_gagal)['total'] ?? 0);
$total_dilewati = (int)(mysqli_fetch_assoc($q_dilewati)['total'] ?? 0);

// Ambil daftar unik periode laporan untuk filter dropdown
$q_periode_list = mysqli_query($koneksi, "SELECT DISTINCT periode FROM log_laporan_wa ORDER BY id DESC");

// =================================================================
// 4. QUERY UTAMA DATA LOG
// =================================================================
$query_log = "SELECT * FROM log_laporan_wa $sql_where ORDER BY created_at DESC LIMIT 300";
$res_log   = mysqli_query($koneksi, $query_log);

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
    }
    .label-stat {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        letter-spacing: 0.8px;
    }
    .value-stat {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        margin-top: 15px;
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
    .table-custom th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        background-color: #f8f9fa;
        border-bottom: 2px solid #edf2f7;
    }
    .table-custom td {
        vertical-align: middle;
        font-size: 13px;
    }
    /* Style kustomisasi SweetAlert */
    .swal2-popup {
        border-radius: 24px !important;
        padding: 1.8rem !important;
    }
    .swal2-title {
        font-size: 20px !important;
        font-weight: 800 !important;
        color: #111827 !important;
    }
</style>

<div class="container-fluid pb-4">

    <!-- HEADER HALAMAN -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Riwayat Pengiriman WA</h3>
            <p class="text-secondary mb-0">Lacak rekam jejak dan status pengiriman laporan PDF bulanan ke wali santri.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="konfirmasiHapusLog()">
                <i class="bi bi-trash3-fill me-1"></i> Bersihkan Log
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- STATISTIK RINGKAS LOG -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-modern-b">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">BERHASIL TERKIRIM</div>
                        <div class="icon-box-b" style="background-color: #dcfce7; color: #16a34a;"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                    <div class="value-stat text-success"><?= number_format($total_sukses, 0, ',', '.'); ?> <span class="fs-6 text-muted fw-normal">Pesan</span></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-modern-b">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">GAGAL TERKIRIM</div>
                        <div class="icon-box-b" style="background-color: #fee2e2; color: #dc2626;"><i class="bi bi-x-circle-fill"></i></div>
                    </div>
                    <div class="value-stat text-danger"><?= number_format($total_gagal, 0, ',', '.'); ?> <span class="fs-6 text-muted fw-normal">Pesan</span></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-modern-b">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="label-stat">DILEWATI (TIDAK VALID)</div>
                        <div class="icon-box-b" style="background-color: #fef3c7; color: #d97706;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    </div>
                    <div class="value-stat text-warning"><?= number_format($total_dilewati, 0, ',', '.'); ?> <span class="fs-6 text-muted fw-normal">Santri</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD TABEL & FILTER -->
    <div class="card card-modern-b p-3">
        <div class="card-header bg-white border-0 pt-3 px-3 pb-0">
            <form method="GET" action="riwayat_laporan_wa.php" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari Nama Santri, NIS, atau Nomor WA..." value="<?= htmlspecialchars($search_q); ?>">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Status --</option>
                        <option value="sukses" <?= ($filter_status == 'sukses') ? 'selected' : ''; ?>>Sukses</option>
                        <option value="gagal" <?= ($filter_status == 'gagal') ? 'selected' : ''; ?>>Gagal</option>
                        <option value="dilewati" <?= ($filter_status == 'dilewati') ? 'selected' : ''; ?>>Dilewati</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="periode" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Periode --</option>
                        <?php while ($p = mysqli_fetch_assoc($q_periode_list)): ?>
                            <option value="<?= $p['periode']; ?>" <?= ($filter_periode == $p['periode']) ? 'selected' : ''; ?>><?= $p['periode']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                    <?php if (!empty($filter_status) || !empty($filter_periode) || !empty($search_q)): ?>
                        <a href="riwayat_laporan_wa.php" class="btn btn-outline-danger btn-sm rounded-3" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card-body px-3 pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Tanggal & Waktu</th>
                            <th>Santri</th>
                            <th>No. WhatsApp</th>
                            <th>Periode</th>
                            <th class="text-center">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($res_log) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($res_log)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($row['created_at'])); ?></div>
                                        <small class="text-muted" style="font-size: 11px;"><?= date('H:i:s', strtotime($row['created_at'])); ?> WIB</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_santri']); ?></div>
                                        <small class="text-muted" style="font-size: 11px;">NIS: <?= htmlspecialchars($row['nis']); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['no_wa'])): ?>
                                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $row['no_wa']); ?>" target="_blank" class="text-decoration-none fw-semibold text-success">
                                                <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($row['no_wa']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted italic">- (Kosong)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['periode']); ?></span></td>
                                    <td class="text-center">
                                        <?php if ($row['status'] == 'sukses'): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Sukses</span>
                                        <?php elseif ($row['status'] == 'gagal'): ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i> Gagal</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-warning-subtle text-warning px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Dilewati</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-secondary small"><?= htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data riwayat pengiriman laporan WhatsApp.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- FORM TERSEMBUNYI UNTUK SUBMIT SWEETALERT -->
<form id="formHapusLog" method="POST" action="riwayat_laporan_wa.php" style="display: none;">
    <input type="hidden" name="proses_hapus_log" value="1">
    <input type="hidden" name="jenis_hapus" id="input_jenis_hapus" value="">
</form>

<!-- SWEETALERT2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function konfirmasiHapusLog() {
    Swal.fire({
        title: 'Bersihkan Log WA',
        html: `
            <div class="text-start mt-2">
                <p class="text-secondary small mb-3">Pilih kategori pembersihan log riwayat pengiriman laporan WhatsApp:</p>
                
                <div class="form-check mb-3 p-3 border rounded-3 bg-light text-start">
                    <input class="form-check-input" type="radio" name="swal_jenis_hapus" id="swal_hapus30" value="30_hari" checked>
                    <label class="form-check-label fw-semibold text-dark ms-1" for="swal_hapus30">
                        Hapus Log Lebih Dari 30 Hari
                        <small class="d-block text-muted fw-normal">Menghapus catatan yang berusia lebih dari 1 bulan (Rekomendasi).</small>
                    </label>
                </div>

                <div class="form-check p-3 border border-danger-subtle rounded-3 bg-danger-subtle bg-opacity-10 text-start">
                    <input class="form-check-input" type="radio" name="swal_jenis_hapus" id="swal_hapusSemua" value="semua">
                    <label class="form-check-label fw-bold text-danger ms-1" for="swal_hapusSemua">
                        Hapus SELURUH Log Riwayat
                        <small class="d-block text-danger fw-normal opacity-75">Mengosongkan seluruh tabel riwayat dari awal sampai sekarang.</small>
                    </label>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Eksekusi Hapus',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-danger rounded-pill px-4 ms-2',
            cancelButton: 'btn btn-secondary rounded-pill px-4'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const selected = document.querySelector('input[name="swal_jenis_hapus"]:checked');
            if (!selected) {
                Swal.showValidationMessage('Silakan pilih opsi pembersihan!');
                return false;
            }
            return selected.value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('input_jenis_hapus').value = result.value;
            document.getElementById('formHapusLog').submit();
        }
    });
}

<?php if (!empty($notif_pesan)): ?>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $notif_type === 'success' ? 'success' : 'error'; ?>',
        title: '<?= $notif_type === 'success' ? 'Berhasil!' : 'Gagal!'; ?>',
        html: '<?= addslashes($notif_pesan); ?>',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
    });
});
<?php endif; ?>
</script>

<?php include 'layout/footer.php'; ?>