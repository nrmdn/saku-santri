<?php
// Mencegah PHP timeout saat memproses banyak data
set_time_limit(0);
ignore_user_abort(true);

require 'vendor/autoload.php';
require 'config/koneksi.php';

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Asia/Jakarta');

// Menentukan rentang waktu bulan lalu
$bulan_lalu = date('m', strtotime('first day of last month'));
$tahun_lalu = date('Y', strtotime('first day of last month'));
$nama_bulan = date('F Y', strtotime('first day of last month'));

echo "<h3>🚀 Memulai Generate & Kirim Laporan Bulan $nama_bulan...</h3>";

// 1. Ambil data semua santri aktif
$query_teks = "SELECT id, nis, nama, no_hp_ortu, saldo FROM santri WHERE deleted_at IS NULL";
$q_santri = mysqli_query($koneksi, $query_teks);

if (!$q_santri) {
    die("❌ GAGAL QUERY DATABASE: " . mysqli_error($koneksi));
}

$sukses_kirim = 0;
$gagal_kirim  = 0;
$dilewati     = 0;

while ($santri = mysqli_fetch_assoc($q_santri)) {
    $id_santri   = $santri['id'];
    $nis_santri  = mysqli_real_escape_string($koneksi, $santri['nis']);
    $nama_santri = mysqli_real_escape_string($koneksi, $santri['nama']);
    $no_wa       = mysqli_real_escape_string($koneksi, $santri['no_hp_ortu']);
    
    // Safety Check: Catat ke log jika nomor kosong / kurang dari 10 digit (dummy)
    if (empty($no_wa) || strlen($no_wa) < 10) {
        $dilewati++;
        $ket = empty($no_wa) ? "Nomor WA belum diisi" : "Nomor WA tidak valid (<10 digit)";
        
        mysqli_query($koneksi, "INSERT INTO log_laporan_wa (santri_id, nis, nama_santri, no_wa, periode, status, keterangan) 
            VALUES ('$id_santri', '$nis_santri', '$nama_santri', '$no_wa', '$nama_bulan', 'dilewati', '$ket')");
        continue;
    }
    
    // 2. Ambil Riwayat Transaksi Bulan Lalu
    $q_trx = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE santri_id = '$id_santri' AND MONTH(tanggal) = '$bulan_lalu' AND YEAR(tanggal) = '$tahun_lalu' AND status = 'sukses' ORDER BY tanggal ASC");
    
    // Susun kerangka HTML untuk Laporan PDF
    $html = '
    <div style="font-family: Arial, sans-serif;">
        <h2 style="text-align: center; color: #198754;">Saku Santri - Laporan Keuangan Bulanan</h2>
        <hr>
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td><b>Nama Santri:</b> '.$santri['nama'].'</td>
                <td style="text-align: right;"><b>Periode:</b> '.$nama_bulan.'</td>
            </tr>
            <tr>
                <td><b>NIS:</b> '.$santri['nis'].'</td>
                <td style="text-align: right;"><b>Sisa Saldo Saat Ini:</b> Rp '.number_format($santri['saldo'],0,',','.').'</td>
            </tr>
        </table>
        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>';
            
    $ada_transaksi = false;
    while ($trx = mysqli_fetch_assoc($q_trx)) {
        $ada_transaksi = true;
        $simbol = ($trx['jenis_transaksi'] == 'masuk') ? '+' : '-';
        $warna  = ($trx['jenis_transaksi'] == 'masuk') ? 'green' : 'red';
        
        $html .= '<tr>
                    <td>'.date('d/m/Y', strtotime($trx['tanggal'])).'</td>
                    <td>'.$trx['keterangan'].'</td>
                    <td style="color: '.$warna.'; text-align: right;">'.$simbol.' Rp '.number_format($trx['nominal'],0,',','.').'</td>
                  </tr>';
    }
    
    if (!$ada_transaksi) {
        $html .= '<tr><td colspan="3" style="text-align: center;">Tidak ada transaksi di bulan ini.</td></tr>';
    }
    
    $html .= '</tbody></table>
              <p style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
                Dicetak secara otomatis oleh Sistem Saku Santri Pondok Pesantren Al-Habibatain Bumiayu.
              </p>
              </div>';

    // 3. Proses Konversi HTML ke PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 4. Simpan File PDF ke server lokal
    $nama_file = "Laporan_".$santri['nis']."_".$nama_bulan.".pdf";
    $nama_file = str_replace(' ', '_', $nama_file);
    $lokasi_simpan = __DIR__ . DIRECTORY_SEPARATOR . "arsip_laporan" . DIRECTORY_SEPARATOR . $nama_file;
    
    if (!is_dir(__DIR__ . "/arsip_laporan")) {
        mkdir(__DIR__ . "/arsip_laporan", 0777, true);
    }
    
    file_put_contents($lokasi_simpan, $dompdf->output());

    // 5. Tembak API WhatsApp Node.js
    $pesan_wa = "Assalamualaikum Wr. Wb.\n\nBerikut terlampir Laporan Keuangan Saku Santri an. *".$santri['nama']."* untuk periode *".$nama_bulan."*.\n\nSisa Saldo Aktif: *Rp ".number_format($santri['saldo'],0,',','.')."*\n\nTerima kasih.\n_Sistem Otomatis Pondok Pesantren Al-Habibatain Bumiayu_";

    $payload = json_encode([
        'number' => $no_wa,
        'message' => $pesan_wa,
        'pdf_path' => $lokasi_simpan 
    ]);

    $ch = curl_init('http://localhost:3000/send-document');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $res_data = json_decode($response, true);

    // 6. Catat Status Pengiriman ke Database Log
    if ($http_code == 200 && isset($res_data['status']) && $res_data['status'] == 'success') {
        $sukses_kirim++;
        mysqli_query($koneksi, "INSERT INTO log_laporan_wa (santri_id, nis, nama_santri, no_wa, periode, status, keterangan) 
            VALUES ('$id_santri', '$nis_santri', '$nama_santri', '$no_wa', '$nama_bulan', 'sukses', 'Berhasil terkirim via WhatsApp Gateway')");
    } else {
        $gagal_kirim++;
        $pesan_err = mysqli_real_escape_string($koneksi, $res_data['message'] ?? 'Gagal terhubung ke WA Gateway / Timeout');
        mysqli_query($koneksi, "INSERT INTO log_laporan_wa (santri_id, nis, nama_santri, no_wa, periode, status, keterangan) 
            VALUES ('$id_santri', '$nis_santri', '$nama_santri', '$no_wa', '$nama_bulan', 'gagal', '$pesan_err')");
    }

    // 7. Jeda natural 8-15 detik (Anti-Blokir WA)
    $waktu_jeda = rand(8, 15);
    sleep($waktu_jeda);
}

echo "<hr><h3>🎉 Selesai!</h3>";
echo "<ul>
        <li>Terkirim: <b>$sukses_kirim</b></li>
        <li>Gagal: <b>$gagal_kirim</b></li>
        <li>Dilewati: <b>$dilewati</b></li>
      </ul>";
?>