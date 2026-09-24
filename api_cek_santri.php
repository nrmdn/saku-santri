<?php
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php';

// Rate Limiting anti-scraping sederhana
$now = time();
if (!isset($_SESSION['api_last_check'])) {
    $_SESSION['api_last_check'] = $now;
    $_SESSION['api_check_count'] = 1;
} else {
    if ($now - $_SESSION['api_last_check'] < 60) {
        $_SESSION['api_check_count']++;
        if ($_SESSION['api_check_count'] > 30) {
            http_response_code(429);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        $_SESSION['api_last_check'] = $now;
        $_SESSION['api_check_count'] = 1;
    }
}

$nis_raw = $_GET['nis'] ?? '';
$nis = validate_nis($nis_raw);

if (!$nis) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Format NIS tidak valid.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT id, nis, nama, nama_ortu, foto FROM santri WHERE nis = ? AND deleted_at IS NULL LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Terjadi kesalahan sistem internal.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $nis);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $santri = mysqli_fetch_assoc($result);
    $foto = !empty($santri['foto']) ? $santri['foto'] : 'default.png';

    // Samarkan nama ortu untuk privasi (contoh: "Ahmad S****")
    $nama_ortu_raw = trim($santri['nama_ortu'] ?? '');
    $nama_ortu_masked = '-';
    if (!empty($nama_ortu_raw)) {
        $parts = explode(' ', $nama_ortu_raw);
        if (count($parts) > 1) {
            $nama_ortu_masked = $parts[0] . ' ' . substr($parts[1], 0, 1) . '***';
        } else {
            $nama_ortu_masked = substr($nama_ortu_raw, 0, 3) . '***';
        }
    }

    echo json_encode([
        'status' => 'success',
        'data'   => [
            'nis'       => e($santri['nis']),
            'nama'      => e($santri['nama']),
            'nama_ortu' => e($nama_ortu_masked),
            'foto'      => e($foto)
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Santri dengan NIS tersebut tidak ditemukan atau sudah nonaktif.'
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_stmt_close($stmt);
