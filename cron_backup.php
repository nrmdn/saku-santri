<?php
// 1. Konfigurasi Database (Sesuaikan dengan milik Anda)
$host = 'localhost';
$user = 'root';
$pass = ''; // Kosongkan jika XAMPP bawaan tidak pakai password
$db   = 'db_tapcash'; // GANTI dengan nama database Anda di phpMyAdmin

// 2. Tentukan lokasi folder penyimpanan backup
$folder_backup = __DIR__ . '/backup/';

// Buat foldernya otomatis jika Anda lupa membuat Langkah 1
if (!is_dir($folder_backup)) {
    mkdir($folder_backup, 0777, true);
}

// 3. Format Nama File (contoh: backup_2026-10-28_14-30.sql)
$nama_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$lokasi_simpan = $folder_backup . $nama_file;

// 4. Lokasi aplikasi mysqldump bawaan XAMPP Windows
// (Nanti saat pindah ke VPS Linux, baris ini cukup diganti menjadi: $mysqldump = 'mysqldump';)
$mysqldump = 'C:\xampp\mysql\bin\mysqldump.exe'; 

// 5. Susun perintah sistem (CMD)
if ($pass === '') {
    $command = "\"$mysqldump\" --user=$user --host=$host $db > \"$lokasi_simpan\" 2>&1";
} else {
    $command = "\"$mysqldump\" --user=$user --password=$pass --host=$host $db > \"$lokasi_simpan\" 2>&1";
}

// 6. Eksekusi perintah secara otomatis oleh PHP
exec($command, $output, $status_eksekusi);

// 7. Cek Ciri-ciri Keberhasilan
if ($status_eksekusi === 0 && file_exists($lokasi_simpan) && filesize($lokasi_simpan) > 0) {
    echo "<h3>✅ MANTAP! Backup Otomatis Berhasil!</h3>";
    echo "<b>Nama File:</b> " . $nama_file . "<br>";
    echo "<b>Ukuran File:</b> " . round(filesize($lokasi_simpan) / 1024, 2) . " KB<br>";
    echo "<b>Lokasi:</b> " . $lokasi_simpan . "<br><br>";
    echo "<i>File ini sudah siap dipanggil oleh server setiap hari jam 2 pagi.</i>";
} else {
    echo "<h3>❌ YAH GAGAL!</h3>";
    echo "Pesan Error dari Sistem:<br>";
    echo "<pre>" . print_r($output, true) . "</pre>";
    echo "Coba pastikan: <br>1. Nama database benar. <br>2. XAMPP terinstall di Drive C (C:\xampp).";
}
?>