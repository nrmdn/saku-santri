<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';

require_login();

$id_santri = validate_id($_GET['id'] ?? null);
if (!$id_santri) {
    echo 'ID Santri tidak valid!';
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM santri WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id_santri);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) === 0) {
    mysqli_stmt_close($stmt);
    echo 'Data santri tidak ditemukan!';
    exit;
}
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$link_info = $protocol . '://' . $host . '/tapcash-santri/info_santri.php?token=' . urlencode($data['qr_token'] ?? '');
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($link_info);

// --- TAMBAHKAN BLOK KODE INI ---
if (isset($_GET['download']) && $_GET['download'] == '1') {
    $filename = "QR_" . $data['nis'] . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', $data['nama']) . ".png";
    header("Content-Type: application/octet-stream");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    readfile($qr_url);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download QR - <?= htmlspecialchars($data['nama']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card-qr { background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; width: 350px; border: 2px solid #ddd; }
        .card-qr h3 { margin: 0 0 5px 0; color: #333; }
        .card-qr p { margin: 0 0 20px 0; color: #666; font-size: 14px; }
        .qr-image { width: 250px; height: 250px; border: 5px solid #198754; border-radius: 8px; padding: 10px; }
        .info-scan { margin-top: 15px; font-size: 13px; color: #888; }
        .btn-download { margin-top: 20px; padding: 12px 25px; background-color: #0d6efd; color: white; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; font-weight: bold; width: 100%; }
    </style>
</head>
<body>

    <div class="card-qr">
        
        <h3><?= htmlspecialchars($data['nama']); ?></h3>
        <p>NIS: <?= htmlspecialchars($data['nis']); ?></p>

        <img src="<?= $qr_url; ?>" alt="QR Code" class="qr-image">

        <div class="info-scan">
            Scan QR Code ini untuk melihat halaman info saldo & riwayat transaksi lengkap.
        </div>

        <!-- Ganti menjadi tag anchor (a) yang mengarah kembali ke halaman ini dengan parameter download=1 -->
        <a href="?id=<?= $id_santri; ?>&download=1" class="btn-download" style="display: block; box-sizing: border-box; text-decoration: none;">
            Download QR Code (.png)
        </a>
    </div>

</body>
</html>
