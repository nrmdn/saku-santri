<?php
/**
 * SAKUSANTRI AUDIT LOG LAYER
 * Tamper-evident Audit Trail for Financial & System Actions
 */

require_once __DIR__ . '/security.php';

/**
 * Mencatat aktivitas penting, perubahan saldo, dan tindakan administrator ke audit log
 *
 * @param mysqli $koneksi
 * @param string $action Nama aksi (misal: 'login', 'logout', 'verifikasi_topup', 'pengeluaran', 'tambah_santri')
 * @param string $module Nama modul (misal: 'auth', 'transaksi', 'santri', 'user')
 * @param string|null $target_type Tipe entitas (misal: 'santri', 'transaksi', 'users')
 * @param string|int|null $target_id ID entitas terkait
 * @param string $description Penjelasan rinci aktivitas
 * @param mixed $old_value Nilai sebelum perubahan (opsional)
 * @param mixed $new_value Nilai sesudah perubahan (opsional)
 * @return bool
 */
function audit_log($koneksi, $action, $module, $target_type = null, $target_id = null, $description = '', $old_value = null, $new_value = null) {
    if (!$koneksi) {
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user_id    = $_SESSION['id_admin'] ?? null;
    $ip_address = get_client_ip();
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);
    $created_at = date('Y-m-d H:i:s');

    $old_val_str = is_array($old_value) || is_object($old_value) ? json_encode($old_value, JSON_UNESCAPED_UNICODE) : ($old_value !== null ? (string)$old_value : null);
    $new_val_str = is_array($new_value) || is_object($new_value) ? json_encode($new_value, JSON_UNESCAPED_UNICODE) : ($new_value !== null ? (string)$new_value : null);
    $target_id_str = $target_id !== null ? (string)$target_id : null;

    $sql = "INSERT INTO audit_logs (user_id, action, module, target_type, target_id, description, old_value, new_value, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        error_log("Audit log prepare failed: " . mysqli_error($koneksi));
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "issssssssss",
        $user_id,
        $action,
        $module,
        $target_type,
        $target_id_str,
        $description,
        $old_val_str,
        $new_val_str,
        $ip_address,
        $user_agent,
        $created_at
    );

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}

