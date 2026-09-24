<?php
/**
 * SAKUSANTRI SECURITY LAYER
 * Global Security Headers, Session Cookie Protection & XSS Sanitization
 */

if (!defined('SAKUSANTRI_SECURITY')) {
    define('SAKUSANTRI_SECURITY', true);
}

// 1. Matikan tampilan detail error internal PHP ke browser demi keamanan
// Seluruh error tetap dicatat ke error_log server
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// 2. Konfigurasi Keamanan Session Cookie (Sebelum session_start)
if (session_status() === PHP_SESSION_NONE) {
    $is_https = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1))
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $session_options = [
        'lifetime' => 0, // Bertahan selama browser terbuka
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,      // Cegah pencurian session lewat XSS (JavaScript document.cookie)
        'samesite' => 'Lax'      // Mitigasi serangan CSRF antar-domain
    ];

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($session_options);
    } else {
        session_set_cookie_params(
            $session_options['lifetime'],
            $session_options['path'] . '; samesite=' . $session_options['samesite'],
            $session_options['domain'],
            $session_options['secure'],
            $session_options['httponly']
        );
    }

    session_start();
}

// 3. Set Global HTTP Security Headers jika belum dikirim
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'self'; form-action 'self' https://wa.me; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';");
}

/**
 * Helper global untuk sanitasi output HTML (Mencegah serangan XSS)
 *
 * @param mixed $value Nilai yang akan ditampilkan
 * @return string Nilai yang aman di-render di HTML
 */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka nominal uang ke Rupiah yang rapi
 *
 * @param float|int|string $nominal
 * @return string Contoh: "Rp 150.000"
 */
function format_rupiah($nominal) {
    return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
}

/**
 * Mendapatkan IP Address klien secara aman
 *
 * @return string
 */
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($list[0]);
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}
