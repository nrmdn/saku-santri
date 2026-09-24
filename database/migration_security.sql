-- ==========================================================
-- SAKUSANTRI DATABASE SECURITY & SCHEMA MIGRATION
-- Project: SakuSantri (db_tapcash)
-- ==========================================================

-- 1. Modifikasi tipe data saldo dan penambahan kolom pada tabel santri
ALTER TABLE santri
MODIFY saldo DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS qr_token VARCHAR(64) NULL DEFAULT NULL;

-- Tambah indeks unik untuk qr_token jika belum ada
ALTER TABLE santri
ADD UNIQUE INDEX IF NOT EXISTS uq_santri_qr_token (qr_token);

-- 2. Modifikasi tipe data nominal dan penambahan indeks pada tabel transaksi
ALTER TABLE transaksi
MODIFY nominal DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
ADD INDEX IF NOT EXISTS idx_transaksi_santri_status (santri_id, status),
ADD INDEX IF NOT EXISTS idx_transaksi_status_jenis (status, jenis_transaksi),
ADD INDEX IF NOT EXISTS idx_transaksi_tanggal (tanggal);

-- 3. Penambahan kolom role, failed_attempts, dan locked_until pada tabel users
ALTER TABLE users
ADD COLUMN IF NOT EXISTS role ENUM(
    'superadmin',
    'admin',
    'bendahara',
    'operator'
) NOT NULL DEFAULT 'admin',
ADD COLUMN IF NOT EXISTS failed_attempts INT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL DEFAULT NULL;

-- 4. Tabel login_attempts untuk proteksi Brute Force berbasis IP & Akun
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NOT NULL,
    attempt_time DATETIME NOT NULL,
    INDEX idx_login_ip_time (ip_address, attempt_time),
    INDEX idx_login_user_time (username, attempt_time)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 5. Tabel audit_logs untuk pencatatan rekam jejak finansial dan aktivitas sistem
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id VARCHAR(50) NULL,
    description TEXT NOT NULL,
    old_value LONGTEXT NULL,
    new_value LONGTEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_module (module),
    INDEX idx_audit_created (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;