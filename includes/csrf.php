<?php
/**
 * SAKUSANTRI CSRF PROTECTION LAYER
 * Cross-Site Request Forgery Prevention
 */

require_once __DIR__ . '/security.php';

/**
 * Mendapatkan atau membuat CSRF Token baru
 *
 * @return string
 */
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Menghasilkan input hidden HTML berisi CSRF token
 *
 * @return string HTML tag
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Memvalidasi CSRF token dari request (POST/Header)
 *
 * @param string|null $token Jika null, otomatis ambil dari $_POST['csrf_token'] atau header X-CSRF-TOKEN
 * @param bool $die_on_failure Hentikan eksekusi dengan HTTP 403 jika tidak valid
 * @return bool
 */
function verify_csrf_token($token = null, $die_on_failure = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($token === null) {
        if (!empty($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
    }

    $session_token = $_SESSION['csrf_token'] ?? '';

    $is_valid = (!empty($token) && !empty($session_token) && hash_equals($session_token, $token));

    if (!$is_valid && $die_on_failure) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>403 - Akses Ditolak (CSRF Token Tidak Valid)</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="max-width: 480px;">
                <div class="mb-3 text-danger fs-1"><i class="fas fa-shield-virus"></i></div>
                <h4 class="fw-bold text-dark">Sesi Kedaluwarsa atau Akses Tidak Sah</h4>
                <p class="text-muted small mb-4">
                    Permintaan Anda tidak dapat diproses karena token keamanan (CSRF Token) tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman formulir dan coba kembali.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button onclick="window.history.back();" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </button>
                    <a href="javascript:location.reload();" class="btn btn-success rounded-pill px-4">
                        <i class="fas fa-redo me-1"></i> Muat Ulang
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    return $is_valid;
}

