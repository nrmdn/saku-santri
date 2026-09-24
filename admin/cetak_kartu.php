<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';

require_login();

$id = validate_id($_GET['id'] ?? null);
if (!$id) {
    echo 'ID Santri tidak valid!';
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM santri WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) === 0) {
    mysqli_stmt_close($stmt);
    echo 'Data santri tidak ditemukan!';
    exit;
}
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
$foto = (!empty($data['foto'])) ? $data['foto'] : 'default.png';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$link_info = $protocol . '://' . $host . '/tapcash-santri/info_santri.php?token=' . urlencode($data['qr_token'] ?? '');
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($link_info);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak KTS - <?= htmlspecialchars($data['nama']); ?></title>
    <style>
             @import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;700;800&display=swap');

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card-container {
            /* UKURAN FISIK PASTI KARTU (CR80) - 3.37 inch x 2.125 inch */
            width: 3.37in;
            height: 2.125in;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            background-color: #fff;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .card-front { background-image: url('../assets/img/bg_depan.png'); }
        .card-back { background-image: url('../assets/img/bg_belakang.png'); }

        /* --- STYLING BAGIAN DEPAN (FRONT) --- */
        .card-title {
            text-align: center;
            color: #0c3e5f;
            font-size: 15px; /* Diperkecil proporsional dengan kartu fisik */
            font-weight: 800;
            margin-top: 58px; /* Disesuaikan untuk menghindari tabrakan dengan kop */
            margin-bottom: 10px;
            letter-spacing: 0.3px;
        }

        .card-body-content {
            display: flex;
            padding: 0 18px;
            gap: 12px;
            align-items: flex-start;
        }

        .santri-photo {
            width: 62px;
            height: 82px;
            object-fit: cover;
            border-radius: 4px;
            border: 1.5px solid #0c3e5f; 
        }

        .santri-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px; /* Jarak antar baris teks */
        }

        .detail-row {
            display: flex;
            font-size: 9.5px; /* Ukuran ini akan terlihat rapi dan terbaca jelas saat dicetak */
            color: #0c3e5f;
            font-weight: 700; 
        }

        .label {
            width: 75px; 
            flex-shrink: 0;
        }

        .separator {
            width: 8px;
            text-align: center;
            flex-shrink: 0;
        }

        .value {
            flex: 1;
        }

        .card-footer {
            position: absolute;
            bottom: 12px; /* Dinaikkan agar bebas dari grafis footer kuning/hijau */
            right: 0;
            left: 0;
            text-align: right;
            padding-right: 10px; 
            font-size: 5px;
            color: #0c3e5f;
            font-style: italic;
            font-weight: 500;
            z-index: 10;
        }

        /* --- STYLING BAGIAN BELAKANG (BACK) --- */
        .santri-qr {
            position: absolute;
            top: 90px; /* Menurunkan QR agar pas di tengah */
            left: 50%;
            transform: translateX(-50%); 
            width: 75px;
            height: 75px;
            background: white;
            padding: 3px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* --- STYLING TOMBOL & CETAK --- */
        .btn-print {
            padding: 10px 25px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 10px;
        }
        .btn-print:hover { background-color: #0b5ed7; }

        /* Pengaturan Khusus Mesin Printer */
        @media print {
            @page {
                margin: 0.5cm; /* Margin luar kertas */
            }
            body { 
                background: none; 
                display: block; 
                padding: 0; 
            }
            .btn-print { display: none; }
            .card-container { 
                box-shadow: none; 
                margin: 10px; /* Memberi jarak jika dicetak potong */
                page-break-inside: avoid; 
                border: 0.5px solid #ccc; /* Garis potong tipis untuk panduan memotong */
            }
        }

    </style>
</head>
<body>

    <!-- Sisi Depan Kartu -->
    <div class="card-container card-front">
        <div class="card-title">KARTU TANDA SANTRI</div>
        <div class="card-body-content">
            <!-- Foto -->
            <img src="../assets/images/<?= htmlspecialchars($foto); ?>" class="santri-photo" alt="Foto Santri">
            
            <!-- Detail Data -->
            <div class="santri-details">
                <div class="detail-row">
                    <span class="label">Nama Lengkap</span>
                    <span class="separator">:</span>
                    <span class="value"><?= htmlspecialchars($data['nama'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">NIS</span>
                    <span class="separator">:</span>
                    <span class="value"><?= htmlspecialchars($data['nis'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">TTL</span>
                    <span class="separator">:</span>
                    <span class="value"><?= htmlspecialchars($data['ttl'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">No. HP Ortu</span>
                    <span class="separator">:</span>
                    <!-- Mengambil data nomor HP Orang Tua -->
                    <span class="value"><?= htmlspecialchars($data['no_hp_ortu'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Alamat</span>
                    <span class="separator">:</span>
                    <!-- Jika di database belum ada kolom alamat, maka akan menampilkan strip (-) -->
                    <span class="value"><?= htmlspecialchars($data['alamat'] ?? '-'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Footer Kecil Pojok Kanan Bawah -->
        <div class="card-footer">
            Jl. PonPes Al Habibatain, Karangdempul | www.alhabibatain.sch.id
        </div>
    </div>

    <!-- Sisi Belakang Kartu -->
    <div class="card-container card-back">
        <img src="<?= $qr_url; ?>" class="santri-qr" alt="QR Code Info Santri">
         <div class="card-footer">
            Jl. PonPes Al Habibatain, Karangdempul | www.alhabibatain.sch.id
        </div>
    </div>

    <button class="btn-print" onclick="window.print()">🖨️ Cetak Kartu KTS</button>

</body>
</html>