<?php


$host     = "localhost";
$user     = "root";
$password = ""; 
$db       = "db_tapcash";

// Membuka koneksi
$koneksi = mysqli_connect($host, $user, $password, $db);

// Cek jika koneksi gagal
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>