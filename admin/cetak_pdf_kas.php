<?php
require '../config/koneksi.php';
require_once __DIR__ . '/../includes/validation.php';

if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }

// Tangkap parameter filter dari GET
$filter      = isset($_GET['filter_jenis']) ? trim((string)$_GET['filter_jenis']) : 'semua';
$santri_id   = isset($_GET['santri_id']) ? trim((string)$_GET['santri_id']) : '';
$tgl_mulai   = isset($_GET['tgl_mulai']) ? trim((string)$_GET['tgl_mulai']) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim((string)$_GET['tgl_selesai']) : '';

if (!validate_date($tgl_mulai, 'Y-m-d')) { $tgl_mulai = ''; }
if (!validate_date($tgl_selesai, 'Y-m-d')) { $tgl_selesai = ''; }
if ($santri_id !== '' && !validate_id($santri_id)) { $santri_id = ''; }

if (!in_array($filter, ['semua','masuk','keluar','persantri'], true)) { $filter = 'semua'; }

// Susun Query Berdasarkan Filter Jenis
$where_clause = "WHERE transaksi.status = 'sukses'";
$judul_laporan = "Laporan Arus Kas Keseluruhan (Masuk & Keluar)";

if ($filter == 'masuk') {
    $where_clause .= " AND transaksi.jenis_transaksi = 'masuk'";
    $judul_laporan = "Laporan Arus Kas - Khusus Kredit (Uang Masuk)";
} elseif ($filter == 'keluar') {
    $where_clause .= " AND transaksi.jenis_transaksi = 'keluar'";
    $judul_laporan = "Laporan Arus Kas - Khusus Debit (Uang Keluar)";
} elseif ($filter == 'persantri' && !empty($santri_id)) {
    $where_clause .= " AND transaksi.santri_id = '$santri_id'";
    $q_nama = mysqli_query($koneksi, "SELECT nama, nis FROM santri WHERE id = '$santri_id'");
    $d_nama = mysqli_fetch_assoc($q_nama);
    $judul_laporan = "Laporan Mutasi Kas Santri : " . htmlspecialchars($d_nama['nama']) . " (NIS: " . htmlspecialchars($d_nama['nis']) . ")";
}

// Tambahkan Filter Tanggal ke Query (Jika diisi)
$periode_cetak = "";
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where_clause .= " AND DATE(transaksi.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
    $periode_cetak = "<br><span style='font-size: 14px; font-weight: normal;'>Periode: " . date('d/m/Y', strtotime($tgl_mulai)) . " s/d " . date('d/m/Y', strtotime($tgl_selesai)) . "</span>";
}

// Ambil semua data sesuai filter gabungan
$query = "SELECT transaksi.*, santri.nama, santri.nis FROM transaksi JOIN santri ON transaksi.santri_id = santri.id $where_clause ORDER BY transaksi.tanggal DESC";
$hasil = mysqli_query($koneksi, $query);

// Siapkan wadah (array) untuk memisahkan masuk dan keluar
$data_masuk = [];
$data_keluar = [];
$total_masuk = 0;
$total_keluar = 0;

if ($hasil && mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        if ($row['jenis_transaksi'] == 'masuk') {
            $data_masuk[] = $row;
            $total_masuk += $row['nominal'];
        } else {
            $data_keluar[] = $row;
            $total_keluar += $row['nominal'];
        }
    }
}

// Hitung sisa saldo dari data yang difilter
$sisa_saldo = $total_masuk - $total_keluar;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= strip_tags($judul_laporan); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 20px; }
        .header-text { text-align: center; margin-bottom: 20px; }
        .header-text h2, .header-text p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; font-size: 13px; }
        table, th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-success { color: #198754; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        .section-title { font-size: 15px; margin-bottom: 5px; margin-top: 20px; padding-left: 5px; border-left: 4px solid #333; }
        .saldo-box { margin-top: 10px; padding: 15px; border: 2px solid #333; text-align: center; font-size: 18px; font-weight: bold; background-color: #f8f9fa; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0d6efd; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="header-text">
        <h2>PONDOK PESANTREN AL HABIBATAIN BUMIAYU</h2>
        <p><strong><?= $judul_laporan; ?></strong><?= $periode_cetak; ?></p>
        <hr style="border: 1px solid #333; margin-top: 10px;">
    </div>

    <!-- TAMPILKAN TABEL PEMASUKAN -->
    <?php if ($filter == 'semua' || $filter == 'persantri' || $filter == 'masuk') : ?>
    <h3 class="section-title">A. Rincian Pemasukan (Kredit)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Nama Santri (NIS)</th>
                <th width="15%">Arus Kas</th>
                <th width="15%">Nominal</th>
                <th width="25%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($data_masuk) > 0) {
                $no = 1;
                foreach ($data_masuk as $row) {
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                <td><?= htmlspecialchars($row['nama']); ?> <br><small>NIS: <?= $row['nis']; ?></small></td>
                <td>Kredit (Masuk)</td>
                <td class="text-success">+ Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></td>
                <td><?= htmlspecialchars($row['keterangan']); ?></td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data pemasukan pada periode ini.</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right; font-size: 14px;">Total Pemasukan:</th>
                <th colspan="2" class="text-success" style="font-size: 15px;">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></th>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>

    <!-- TAMPILKAN TABEL PENGELUARAN -->
    <?php if ($filter == 'semua' || $filter == 'persantri' || $filter == 'keluar') : ?>
    <h3 class="section-title">B. Rincian Pengeluaran (Debit)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Nama Santri (NIS)</th>
                <th width="15%">Arus Kas</th>
                <th width="15%">Nominal</th>
                <th width="25%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($data_keluar) > 0) {
                $no = 1;
                foreach ($data_keluar as $row) {
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                <td><?= htmlspecialchars($row['nama']); ?> <br><small>NIS: <?= $row['nis']; ?></small></td>
                <td>Debit (Keluar)</td>
                <td class="text-danger">- Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></td>
                <td><?= htmlspecialchars($row['keterangan']); ?></td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data pengeluaran pada periode ini.</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right; font-size: 14px;">Total Pengeluaran:</th>
                <th colspan="2" class="text-danger" style="font-size: 15px;">Rp <?= number_format($total_keluar, 0, ',', '.'); ?></th>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>

    <!-- TAMPILKAN TOTAL SISA SALDO DI BAWAH KEDUA TABEL -->
    <?php if ($filter == 'semua' || $filter == 'persantri') : ?>
    <div class="saldo-box">
        SISA SALDO (BERDASARKAN FILTER) : 
        <span class="<?= $sisa_saldo >= 0 ? 'text-success' : 'text-danger'; ?>">
            <?= $sisa_saldo >= 0 ? '' : '- ' ?>Rp <?= number_format(abs($sisa_saldo), 0, ',', '.'); ?>
        </span>
    </div>
    <?php endif; ?>

</body>
</html>
