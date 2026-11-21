<?php
require 'config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

echo "<h3>🔄 Memulai Sinkronisasi Data...</h3>";

// 1. Tentukan URL API Pusat (Ini contoh menggunakan API publik gratis untuk simulasi)
$url_api = "https://jsonplaceholder.typicode.com/users"; 

// 2. Tarik data menggunakan cURL (Metode paling tangguh di PHP)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Beri batas 30 detik agar server tidak hang
$response = curl_exec($ch);
curl_close($ch);

// 3. Ubah respon JSON menjadi Array PHP
$data_api = json_decode($response, true);
$jumlah_update = 0;

if ($data_api && is_array($data_api)) {
    // 4. Looping data dari pusat
    foreach ($data_api as $row) {
        /* 
          DI SINI LOGIKA DATABASE ANDA:
          $nis = $row['username'];
          $nama = $row['name'];
          
          Cek apakah NIS sudah ada? 
          - Jika ADA -> Jalankan query UPDATE
          - Jika TIDAK ADA -> Jalankan query INSERT INTO santri
        */
        
        $jumlah_update++; // Simulasi hitungan data yang terproses
    }
    
    echo "✅ <b>Sukses!</b> Berhasil menarik dan menyinkronkan <b>$jumlah_update</b> data dari pusat.<br>";
    echo "Waktu Sinkronisasi: " . date('d-m-Y H:i:s');
} else {
    echo "❌ <b>Gagal!</b> Tidak dapat terhubung ke server pusat atau format data salah.";
}
?>