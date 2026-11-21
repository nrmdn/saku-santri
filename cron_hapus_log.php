<?php
date_default_timezone_set('Asia/Jakarta');

// 1. Tentukan target folder yang mau dibersihkan (misal: folder logs)
$folder_log = __DIR__ . '/logs/';

// Buat foldernya jika belum ada (untuk uji coba)
if (!is_dir($folder_log)) {
    mkdir($folder_log, 0777, true);
    // Bikin file dummy untuk testing
    file_put_contents($folder_log . 'dummy_lama.log', 'Ini log lama');
    // Mundurkan waktu file dummy menjadi 35 hari yang lalu
    touch($folder_log . 'dummy_lama.log', time() - (35 * 86400));
}

// 2. Batas waktu kedaluwarsa (30 hari)
$hari_kedaluwarsa = 30; 
$batas_waktu = time() - ($hari_kedaluwarsa * 86400); // 86400 detik = 1 hari
$file_terhapus = 0;

// 3. Cari semua file berekstensi .log di dalam folder
$file_list = glob($folder_log . "*.log");

if ($file_list) {
    foreach ($file_list as $file) {
        // Cek apakah tanggal modifikasi file lebih tua dari batas waktu
        if (filemtime($file) < $batas_waktu) {
            unlink($file); // Hapus file secara permanen
            $file_terhapus++;
        }
    }
}

echo "<h3>🧹 Pembersihan Selesai!</h3>";
echo "Total file sampah yang berhasil dihapus: <b>$file_terhapus file</b>.<br>";
echo "<i>Skrip ini siap dipanggil oleh Task Scheduler/Cron seminggu sekali.</i>";
?>