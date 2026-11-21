<?php
require '../config/koneksi.php';
require '../vendor/autoload.php';
require_once __DIR__ . '/../includes/auth.php';

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$santri_id   = isset($_POST['santri_id']) ? (int)$_POST['santri_id'] : 0;
$tgl_mulai   = isset($_POST['tgl_mulai']) ? trim($_POST['tgl_mulai']) : '';
$tgl_selesai = isset($_POST['tgl_selesai']) ? trim($_POST['tgl_selesai']) : '';

if ($santri_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Santri belum dipilih.']);
    exit;
}

// 1. Ambil Data Santri
$q_santri = mysqli_query($koneksi, "SELECT * FROM santri WHERE id = '$santri_id' AND deleted_at IS NULL LIMIT 1");
$santri   = mysqli_fetch_assoc($q_santri);

if (!$santri) {
    echo json_encode(['status' => 'error', 'message' => 'Data santri tidak ditemukan.']);
    exit;
}

$no_wa = $santri['no_hp_ortu'];
if (empty($no_wa) || strlen($no_wa) < 10) {
    echo json_encode(['status' => 'error', 'message' => "Nomor WA wali santri '{$santri['nama']}' belum terisi atau tidak valid (minimal 10 digit)."]);
    exit;
}

// 2. Filter Tanggal
$sql_filter = "";
$info_periode = "Semua Riwayat";
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql_filter   = " AND DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
    $info_periode = date('d/m/Y', strtotime($tgl_mulai)) . " - " . date('d/m/Y', strtotime($tgl_selesai));
}

// 3. Ambil Transaksi Santri
$q_trx = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE santri_id = '$santri_id' AND status = 'sukses' $sql_filter ORDER BY tanggal ASC");

// 4. Susun HTML PDF
$html = '
<div style="font-family: Arial, sans-serif;">
    <h2 style="text-align: center; color: #198754; margin-bottom: 5px;">Saku Santri - Laporan Keuangan Individual</h2>
    <p style="text-align: center; margin-top: 0; color: #666; font-size: 13px;">Periode: '.$info_periode.'</p>
    <hr>
    <table style="width: 100%; margin-bottom: 20px; font-size: 14px;">
        <tr>
            <td><b>Nama Santri:</b> '.$santri['nama'].'</td>
            <td style="text-align: right;"><b>NIS:</b> '.$santri['nis'].'</td>
        </tr>
        <tr>
            <td><b>Wali Santri:</b> '.($santri['nama_ortu'] ?? '-').'</td>
            <td style="text-align: right;"><b>Sisa Saldo Saat Ini:</b> Rp '.number_format($santri['saldo'], 0, ',', '.').'</td>
        </tr>
    </table>
    <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead style="background-color: #f8f9fa;">
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>';

$ada_trx = false;
while ($trx = mysqli_fetch_assoc($q_trx)) {
    $ada_trx = true;
    $simbol = ($trx['jenis_transaksi'] == 'masuk') ? '+' : '-';
    $warna  = ($trx['jenis_transaksi'] == 'masuk') ? 'green' : 'red';
    
    $html .= '<tr>
                <td>'.date('d/m/Y H:i', strtotime($trx['tanggal'])).' WIB</td>
                <td>'.strtoupper($trx['jenis_transaksi']).'</td>
                <td>'.htmlspecialchars($trx['keterangan']).'</td>
                <td style="color: '.$warna.'; text-align: right; font-weight: bold;">'.$simbol.' Rp '.number_format($trx['nominal'], 0, ',', '.').'</td>
              </tr>';
}

if (!$ada_trx) {
    $html .= '<tr><td colspan="4" style="text-align: center; color: #888;">Tidak ada riwayat transaksi pada periode ini.</td></tr>';
}

$html .= '</tbody></table>
          <p style="margin-top: 30px; text-align: center; font-size: 11px; color: #888;">
            Dicetak secara otomatis oleh Sistem Saku Santri Pondok Pesantren Al-Habibatain Bumiayu.
          </p>
          </div>';

// 5. Render PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 6. Simpan File PDF
$nama_file = "Laporan_Khusus_".$santri['nis']."_".time().".pdf";
$lokasi_simpan = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "arsip_laporan" . DIRECTORY_SEPARATOR . $nama_file;

if (!is_dir(__DIR__ . "/../arsip_laporan")) {
    mkdir(__DIR__ . "/../arsip_laporan", 0777, true);
}
file_put_contents($lokasi_simpan, $dompdf->output());

// 7. Tembak WA Gateway Node.js
$pesan_wa = "Assalamualaikum Wr. Wb.\n\nBerikut terlampir Laporan Rekapitulasi Keuangan Saku Santri an. *".$santri['nama']."* untuk periode *".$info_periode."*.\n\nSisa Saldo Aktif: *Rp ".number_format($santri['saldo'], 0, ',', '.')."*\n\nTerima kasih.\n_Sistem Otomatis Pondok Pesantren Al-Habibatain Bumiayu_";

$payload = json_encode([
    'number'   => $no_wa,
    'message'  => $pesan_wa,
    'pdf_path' => $lokasi_simpan 
]);

$ch = curl_init('http://localhost:3000/send-document');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$res_data = json_decode($response, true);

// 8. Catat Log ke Database
$nama_santri_esc = mysqli_real_escape_string($koneksi, $santri['nama']);
$nis_esc         = mysqli_real_escape_string($koneksi, $santri['nis']);
$no_wa_esc       = mysqli_real_escape_string($koneksi, $no_wa);

if ($http_code == 200 && isset($res_data['status']) && $res_data['status'] == 'success') {
    mysqli_query($koneksi, "INSERT INTO log_laporan_wa (santri_id, nis, nama_santri, no_wa, periode, status, keterangan) 
        VALUES ('$santri_id', '$nis_esc', '$nama_santri_esc', '$no_wa_esc', '$info_periode', 'sukses', 'Permintaan Laporan Khusus terkirim via WA')");
    
    echo json_encode(['status' => 'success', 'message' => "Laporan berhasil dikirimkan ke WhatsApp wali santri ({$santri['nama']})."]);
} else {
    $err_msg = mysqli_real_escape_string($koneksi, $res_data['message'] ?? 'Gateway WA Off / Timeout');
    mysqli_query($koneksi, "INSERT INTO log_laporan_wa (santri_id, nis, nama_santri, no_wa, periode, status, keterangan) 
        VALUES ('$santri_id', '$nis_esc', '$nama_santri_esc', '$no_wa_esc', '$info_periode', 'gagal', '$err_msg')");
    
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim WA: ' . ($res_data['message'] ?? 'Pastikan Server Node.js Port 3000 Aktif.')]);
}