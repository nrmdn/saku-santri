<?php
/**
 * SAKUSANTRI INPUT & FILE VALIDATION LAYER
 * Server-side Strong Input Validation & File Upload Security
 */

require_once __DIR__ . '/security.php';

/**
 * Validasi Integer ID (Cegah manipulasi IDOR dan SQL injection pada parameter ID)
 *
 * @param mixed $id
 * @return int|false
 */
function validate_id($id) {
    if ($id === null || $id === '') {
        return false;
    }
    $filtered = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return ($filtered !== false) ? (int)$filtered : false;
}

/**
 * Validasi Nominal Uang
 *
 * @param mixed $amount Nilai uang dalam bentuk string/angka (misal: "50.000", "50000", 50000)
 * @param float $min Batas minimal (default: 1.00)
 * @param float $max Batas maksimal transaksi (default: 50.000.000.00)
 * @return float|false
 */
function validate_nominal($amount, $min = 1.0, $max = 50000000.0) {
    if ($amount === null || $amount === '') {
        return false;
    }

    // Bersihkan format titik/koma/spasi/karakter Rp
    if (is_string($amount)) {
        $amount = preg_replace('/[^0-9]/', '', $amount);
    }

    if (!is_numeric($amount)) {
        return false;
    }

    $val = (float)$amount;
    if ($val < $min || $val > $max) {
        return false;
    }

    return $val;
}

/**
 * Validasi Nomor Induk Santri (NIS)
 *
 * @param string $nis
 * @return string|false
 */
function validate_nis($nis) {
    $clean = trim((string)$nis);
    if (preg_match('/^[A-Za-z0-9\-\.]{3,20}$/', $clean)) {
        return $clean;
    }
    return false;
}

/**
 * Validasi Alamat Email
 *
 * @param string $email
 * @return string|false
 */
function validate_email($email) {
    $clean = trim((string)$email);
    if (filter_var($clean, FILTER_VALIDATE_EMAIL)) {
        return $clean;
    }
    return false;
}

/**
 * Validasi Nomor HP / WhatsApp Indonesia
 *
 * @param string $phone
 * @return string|false
 */
function validate_phone($phone) {
    $clean = preg_replace('/[^0-9\+]/', '', trim((string)$phone));
    if (preg_match('/^(\+62|62|08)[0-9]{8,13}$/', $clean)) {
        return $clean;
    }
    return false;
}

/**
 * Validasi Tanggal (Format Y-m-d)
 *
 * @param string $date
 * @param string $format
 * @return string|false
 */
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        return $date;
    }
    return false;
}

/**
 * Validasi Keamanan Upload Berkas (MIME Type Asli + Ukuran Berkas)
 *
 * @param array $file Contoh: $_FILES['foto']
 * @param array $allowed_mimes Daftar MIME tipe yang diizinkan (misal: ['image/jpeg', 'image/png', 'image/webp'])
 * @param int $max_bytes Batas ukuran maksimal dalam bytes (default: 5MB)
 * @return array ['valid' => bool, 'error' => string, 'mime' => string, 'ext' => string]
 */
function validate_file_upload($file, array $allowed_mimes, $max_bytes = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Parameter berkas tidak valid.', 'mime' => '', 'ext' => ''];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => false, 'error' => 'Tidak ada berkas yang diunggah.', 'mime' => '', 'ext' => ''];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'Ukuran berkas melebihi batas yang diizinkan server.', 'mime' => '', 'ext' => ''];
        default:
            return ['valid' => false, 'error' => 'Terjadi kesalahan saat mengunggah berkas.', 'mime' => '', 'ext' => ''];
    }

    if ($file['size'] > $max_bytes) {
        $mb = round($max_bytes / 1048576, 1);
        return ['valid' => false, 'error' => "Ukuran berkas terlalu besar! Maksimal {$mb} MB.", 'mime' => '', 'ext' => ''];
    }

    // Periksa MIME Type asli dari isi file menggunakan finfo
    $tmp_name = $file['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_name);

    if (!in_array($mime, $allowed_mimes, true)) {
        return ['valid' => false, 'error' => 'Tipe berkas tidak diizinkan atau format berkas berbahaya.', 'mime' => $mime, 'ext' => ''];
    }

    // Map MIME ke ekstensi aman
    $mime_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/pdf' => 'pdf'
    ];

    $ext = $mime_map[$mime] ?? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    return [
        'valid' => true,
        'error' => '',
        'mime'  => $mime,
        'ext'   => $ext
    ];
}

/**
 * Menghasilkan nama file acak dan aman (Cryptographically Secure Random Filename)
 *
 * @param string $prefix Contoh: "santri", "struk", "admin"
 * @param string $ext Contoh: "jpg", "png", "xlsx"
 * @return string Contoh: "santri_3f8a92b10c9d8e7a6b5c4d3e2f1a0b9c.jpg"
 */
function generate_secure_filename($prefix, $ext) {
    $random_hash = bin2hex(random_bytes(16));
    $clean_prefix = preg_replace('/[^a-zA-Z0-9_\-]/', '', $prefix);
    $clean_ext = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($ext));
    return $clean_prefix . '_' . $random_hash . '.' . $clean_ext;
}

