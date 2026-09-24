-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Sep 2026 pada 09.47
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_tapcash`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `target_type`, `target_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 21:50:15'),
(2, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 16:53:54'),
(3, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 22:04:02'),
(4, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 22:04:09'),
(5, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '114.10.7.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 22:17:09'),
(6, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '114.10.7.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 17:40:44'),
(7, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-17 11:12:57'),
(8, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-17 11:13:23'),
(9, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-17 11:13:42'),
(10, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-17 11:13:51'),
(11, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-17 06:16:06'),
(12, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 14:33:49'),
(13, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 09:34:59'),
(14, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '114.10.6.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 14:56:39'),
(15, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '114.10.6.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 09:57:33'),
(16, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '114.10.6.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 15:06:40'),
(17, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '114.10.6.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 10:23:50'),
(18, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 15:38:13'),
(19, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '114.10.6.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 16:14:22'),
(20, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '114.10.6.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 11:43:25'),
(21, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 11:12:12'),
(22, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 06:13:41'),
(23, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 11:14:13'),
(24, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 06:14:43'),
(25, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 22:22:35'),
(26, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #20 untuk santri Ahmad Fulan sebesar Rp 1.000.000', '{\"saldo_sebelum\":1900000}', '{\"saldo_sesudah\":2900000,\"nominal\":1000000,\"transaksi_id\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 22:22:43'),
(27, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #21 untuk santri Ahmad Fulan sebesar Rp 1.000.000', '{\"saldo_sebelum\":2900000}', '{\"saldo_sesudah\":3900000,\"nominal\":1000000,\"transaksi_id\":21}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 22:22:55'),
(28, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '3', 'Persetujuan top up #22 untuk santri Nur Ramadan sebesar Rp 500.000', '{\"saldo_sebelum\":366000}', '{\"saldo_sesudah\":866000,\"nominal\":500000,\"transaksi_id\":22}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 22:23:06'),
(29, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #23 untuk santri Ahmad Fulan sebesar Rp 500.000', '{\"saldo_sebelum\":3900000}', '{\"saldo_sesudah\":4400000,\"nominal\":500000,\"transaksi_id\":23}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 22:29:22'),
(30, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.138.0 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36', '2026-09-20 23:21:28'),
(31, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:05:16'),
(32, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #24 untuk santri Ahmad Fulan sebesar Rp 200.000', '{\"saldo_sebelum\":4400000}', '{\"saldo_sesudah\":4600000,\"nominal\":200000,\"transaksi_id\":24}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:11:39'),
(33, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #25 untuk santri Ahmad Fulan sebesar Rp 500.000', '{\"saldo_sebelum\":4600000}', '{\"saldo_sesudah\":5100000,\"nominal\":500000,\"transaksi_id\":25}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:13:49'),
(34, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #28 untuk santri Ahmad Fulan sebesar Rp 500.000', '{\"saldo_sebelum\":5100000}', '{\"saldo_sesudah\":5600000,\"nominal\":500000,\"transaksi_id\":28}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:14:22'),
(35, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #26 untuk santri Ahmad Fulan sebesar Rp 500.000', '{\"saldo_sebelum\":5600000}', '{\"saldo_sesudah\":6100000,\"nominal\":500000,\"transaksi_id\":26}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:19:22'),
(36, 1, 'verifikasi_topup_tolak', 'transaksi', 'transaksi', '27', 'Penolakan top up pengajuan #27 nominal Rp 500.000', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:20:47'),
(37, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #29 untuk santri Ahmad Fulan sebesar Rp 1.000.000', '{\"saldo_sebelum\":6100000}', '{\"saldo_sesudah\":7100000,\"nominal\":1000000,\"transaksi_id\":29}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 01:22:32'),
(38, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 20:27:02'),
(39, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:47:36'),
(40, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:47:46'),
(41, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:48:00'),
(42, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 10:48:15'),
(43, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:51:41'),
(44, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #30 untuk santri Ahmad Fulan sebesar Rp 50.000', '{\"saldo_sebelum\":7100000}', '{\"saldo_sesudah\":7150000,\"nominal\":50000,\"transaksi_id\":30}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:51:53'),
(45, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 10:52:44'),
(46, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:53:22'),
(47, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 10:54:19'),
(48, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 15:54:32'),
(49, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:03:43'),
(50, 1, 'change_password', 'users', 'users', '1', 'Pengurus admin berhasil mengubah password akun.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 11:09:15'),
(51, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 11:09:25'),
(52, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:09:50'),
(53, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:09:58'),
(54, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 11:44:45'),
(55, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:46:42'),
(56, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 11:47:08'),
(57, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:48:29'),
(58, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #31 untuk santri Ahmad Fulan sebesar Rp 500.000', '{\"saldo_sebelum\":7150000}', '{\"saldo_sesudah\":7650000,\"nominal\":500000,\"transaksi_id\":31}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:48:57'),
(59, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 11:50:24'),
(60, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:54:10'),
(61, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 16:54:20'),
(62, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:07:52'),
(63, 1, 'change_password', 'users', 'users', '1', 'Pengurus admin berhasil mengubah password akun.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:08:17'),
(64, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:11:50'),
(65, 1, 'edit_santri', 'santri', 'santri', '11', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:27:39'),
(66, 1, 'edit_santri', 'santri', 'santri', '11', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:27:49'),
(67, 1, 'hapus_santri', 'santri', 'santri', '12', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:28:16'),
(68, 1, 'hapus_santri', 'santri', 'santri', '12', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:28:20'),
(69, 1, 'hapus_santri', 'santri', 'santri', '12', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:28:29'),
(70, 1, 'edit_santri', 'santri', 'santri', '11', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:30:02'),
(71, 1, 'hapus_santri', 'santri', 'santri', '12', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:31:20'),
(72, 1, 'tambah_santri', 'santri', 'santri', '14', 'Menambah data santri baru dengan NIS 2026002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:53:24'),
(73, 1, 'edit_santri', 'santri', 'santri', '14', 'Mengubah data santri NIS 2026002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:53:49'),
(74, 1, 'hapus_santri', 'santri', 'santri', '14', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 05:54:10'),
(75, 1, 'topup_manual', 'transaksi', 'santri', '4', 'Top up manual untuk santri Budi Santoso sebesar Rp 150.000. Keterangan: dari orang tua', '{\"saldo_sebelum\":150000}', '{\"saldo_sesudah\":300000,\"nominal\":150000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 11:11:08'),
(76, 1, 'pengeluaran_manual', 'transaksi', 'santri', '4', 'Pengeluaran manual untuk santri Budi Santoso sebesar Rp 100.000. Keterangan: Beli sabun', '{\"saldo_sebelum\":300000}', '{\"saldo_sesudah\":200000,\"nominal\":100000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 11:11:35'),
(77, 1, 'pengeluaran_manual', 'transaksi', 'santri', '11', 'Pengeluaran manual untuk santri Ahmad Fulan sebesar Rp 500.000. Keterangan: Buat Jajan sebulan', '{\"saldo_sebelum\":7650000}', '{\"saldo_sesudah\":7150000,\"nominal\":500000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 11:12:28'),
(78, 1, 'topup_manual', 'transaksi', 'santri', '11', 'Top up manual untuk santri Ahmad Fulan sebesar Rp 50.000. Keterangan: Dari Bu nyai', '{\"saldo_sebelum\":7150000}', '{\"saldo_sesudah\":7200000,\"nominal\":50000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 11:13:08'),
(79, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 11:41:24'),
(80, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:43:39'),
(81, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:43:52'),
(82, 1, 'edit_santri', 'santri', 'santri', '11', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 08:46:11'),
(83, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 13:46:20'),
(84, 1, 'tambah_santri', 'santri', 'santri', '15', 'Menambah data santri baru dengan NIS 4232302', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 08:47:28'),
(85, 1, 'topup_manual', 'transaksi', 'santri', '15', 'Top up manual untuk santri risqi sebesar Rp 50.000. Keterangan: buat saku', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":50000,\"nominal\":50000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:48:10'),
(86, 1, 'pengeluaran_manual', 'transaksi', 'santri', '15', 'Pengeluaran manual untuk santri risqi sebesar Rp 10.000. Keterangan: beli cilor', '{\"saldo_sebelum\":50000}', '{\"saldo_sesudah\":40000,\"nominal\":10000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:48:31'),
(87, 1, 'hapus_santri', 'santri', 'santri', '15', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 08:50:24'),
(88, 1, 'pengeluaran_manual', 'transaksi', 'santri', '3', 'Pengeluaran manual untuk santri Nur Ramadan sebesar Rp 6.000. Keterangan: beli telor', '{\"saldo_sebelum\":866000}', '{\"saldo_sesudah\":860000,\"nominal\":6000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:51:16'),
(89, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 08:53:17'),
(90, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 08:54:10'),
(91, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:54:33'),
(92, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 13:55:04'),
(93, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #36 untuk santri Ahmad Fulan sebesar Rp 50.000', '{\"saldo_sebelum\":7200000}', '{\"saldo_sesudah\":7250000,\"nominal\":50000,\"transaksi_id\":36}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 13:55:43'),
(94, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 08:55:47'),
(95, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:58:33'),
(96, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #40 untuk santri Ahmad Fulan sebesar Rp 50.000', '{\"saldo_sebelum\":7250000}', '{\"saldo_sesudah\":7300000,\"nominal\":50000,\"transaksi_id\":40}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:59:47'),
(97, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '11', 'Persetujuan top up #41 untuk santri Ahmad Fulan sebesar Rp 100.000', '{\"saldo_sebelum\":7300000}', '{\"saldo_sesudah\":7400000,\"nominal\":100000,\"transaksi_id\":41}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 14:00:44'),
(98, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 14:20:16'),
(99, 1, 'tambah_pengguna', 'users', 'users', '2', 'Menambahkan pengguna Admin1 dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 09:32:36'),
(100, 1, 'hapus_pengguna', 'users', 'users', '2', 'Menghapus pengguna akun Admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 09:32:58'),
(101, 1, 'tambah_santri', 'santri', 'santri', '16', 'Menambah data santri baru dengan NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:00:22'),
(102, 1, 'tambah_santri', 'santri', 'santri', '17', 'Menambah data santri baru dengan NIS 2026002', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:01:20'),
(103, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:01:45'),
(104, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:03:17'),
(105, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '17', 'Persetujuan top up #42 untuk santri Adil Kusuma sebesar Rp 1.000.000', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":1000000,\"nominal\":1000000,\"transaksi_id\":42}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:03:29'),
(106, 1, 'pengeluaran_manual', 'transaksi', 'santri', '17', 'Pengeluaran manual untuk santri Adil Kusuma sebesar Rp 200.000. Keterangan: Tuku LC', '{\"saldo_sebelum\":1000000}', '{\"saldo_sesudah\":800000,\"nominal\":200000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:05:43'),
(107, 1, 'cash_global_masuk', 'laporan', 'santri', '16', 'Penambahan cash global untuk santri Nur Ramadan sebesar Rp 1.000.000. Keterangan: Dari DPR', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":1000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:07:53'),
(108, 1, 'topup_manual', 'transaksi', 'santri', '16', 'Top up manual untuk santri Nur Ramadan sebesar Rp 500.000. Keterangan: Dari Jokowi', '{\"saldo_sebelum\":1000000}', '{\"saldo_sesudah\":1500000,\"nominal\":500000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:09:30'),
(109, 1, 'pengeluaran_manual', 'transaksi', 'santri', '16', 'Pengeluaran manual untuk santri Nur Ramadan sebesar Rp 350.000. Keterangan: Beli Knalpot', '{\"saldo_sebelum\":1500000}', '{\"saldo_sesudah\":1150000,\"nominal\":350000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:10:39'),
(110, 1, 'tambah_santri', 'santri', 'santri', '18', 'Menambah data santri baru dengan NIS 2026003', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:12:04'),
(111, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 50.000.000. Keterangan: dari CEO', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":50000000,\"nominal\":50000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:13:11'),
(112, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 5.000.000. Keterangan: Dari CEO', '{\"saldo_sebelum\":50000000}', '{\"saldo_sesudah\":55000000,\"nominal\":5000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:13:55'),
(113, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 1.000.000. Keterangan: Dari ortu', '{\"saldo_sebelum\":55000000}', '{\"saldo_sesudah\":56000000,\"nominal\":1000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:14:51'),
(114, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 1.000.000. Keterangan: Dari ortu', '{\"saldo_sebelum\":56000000}', '{\"saldo_sesudah\":57000000,\"nominal\":1000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:15:05'),
(115, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 10.000.000. Keterangan: dari ortu', '{\"saldo_sebelum\":57000000}', '{\"saldo_sesudah\":67000000,\"nominal\":10000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:15:20'),
(116, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 3.500.000. Keterangan: dari ortu', '{\"saldo_sebelum\":67000000}', '{\"saldo_sesudah\":70500000,\"nominal\":3500000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:16:00'),
(117, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 1.000. Keterangan: Dari adil', '{\"saldo_sebelum\":70500000}', '{\"saldo_sesudah\":70501000,\"nominal\":1000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:16:33'),
(118, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 500.000. Keterangan: Dari hamba allah', '{\"saldo_sebelum\":70501000}', '{\"saldo_sesudah\":71001000,\"nominal\":500000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:17:03'),
(119, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 49.000. Keterangan: dari adil', '{\"saldo_sebelum\":71001000}', '{\"saldo_sesudah\":71050000,\"nominal\":49000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:17:30'),
(120, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 150.000. Keterangan: dari aril', '{\"saldo_sebelum\":71050000}', '{\"saldo_sesudah\":71200000,\"nominal\":150000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:17:48'),
(121, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 20.000.000. Keterangan: Dari hamba allah', '{\"saldo_sebelum\":71200000}', '{\"saldo_sesudah\":91200000,\"nominal\":20000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:18:17'),
(122, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 8.000.000. Keterangan: dari hamba allah', '{\"saldo_sebelum\":91200000}', '{\"saldo_sesudah\":99200000,\"nominal\":8000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:18:52'),
(123, 1, 'topup_manual', 'transaksi', 'santri', '18', 'Top up manual untuk santri Rafli sebesar Rp 1.000.000. Keterangan: setoran', '{\"saldo_sebelum\":99200000}', '{\"saldo_sesudah\":100200000,\"nominal\":1000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:19:06'),
(124, 1, 'pengeluaran_manual', 'transaksi', 'santri', '18', 'Pengeluaran manual untuk santri Rafli sebesar Rp 5.000.000. Keterangan: Buat Dugem', '{\"saldo_sebelum\":100200000}', '{\"saldo_sesudah\":95200000,\"nominal\":5000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:21:09'),
(125, 1, 'pengeluaran_manual', 'transaksi', 'santri', '18', 'Pengeluaran manual untuk santri Rafli sebesar Rp 50.000.000. Keterangan: Buat ngeLC', '{\"saldo_sebelum\":95200000}', '{\"saldo_sesudah\":45200000,\"nominal\":50000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:21:36'),
(126, 1, 'pengeluaran_manual', 'transaksi', 'santri', '18', 'Pengeluaran manual untuk santri Rafli sebesar Rp 45.200.000. Keterangan: Nyumbang Masjid Agung Bumiayu', '{\"saldo_sebelum\":45200000}', '{\"saldo_sesudah\":0,\"nominal\":45200000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:22:24'),
(127, 1, 'hapus_santri', 'santri', 'santri', '18', 'Soft delete santri untuk menjaga histori transaksi.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:23:28'),
(128, 1, 'tambah_santri', 'santri', 'santri', '19', 'Menambah data santri baru dengan NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:28:08'),
(129, 1, 'topup_manual', 'transaksi', 'santri', '19', 'Top up manual untuk santri Nur Ramadan sebesar Rp 1.000.000. Keterangan: Dari DPR', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":1000000,\"nominal\":1000000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:28:27'),
(130, 1, 'pengeluaran_manual', 'transaksi', 'santri', '19', 'Pengeluaran manual untuk santri Nur Ramadan sebesar Rp 500.000. Keterangan: ngelc', '{\"saldo_sebelum\":1000000}', '{\"saldo_sesudah\":500000,\"nominal\":500000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 15:28:42'),
(131, 1, 'hapus_santri', 'santri', 'santri', '19', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:28:52'),
(132, 1, 'tambah_santri', 'santri', 'santri', '20', 'Menambah data santri baru dengan NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:29:39'),
(133, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 15:42:13'),
(134, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 15:42:23'),
(135, 1, 'edit_pengguna', 'users', 'users', '1', 'Mengubah data pengguna admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 10:42:42'),
(136, 1, 'tambah_pengguna', 'users', 'users', '3', 'Menambahkan pengguna maulida dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 10:43:03'),
(137, 1, 'tambah_santri', 'santri', 'santri', '21', 'Menambah data santri baru dengan NIS 2026005', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 10:49:33'),
(138, 1, 'cash_global_masuk', 'laporan', 'santri', '21', 'Penambahan cash global untuk santri Maulida sebesar Rp 10.000. Keterangan: buat beli cilok', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":10000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-22 15:51:28'),
(139, 1, 'edit_santri', 'santri', 'santri', '21', 'Mengubah data santri NIS 2026005', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 10:56:28'),
(140, 1, 'hapus_santri', 'santri', 'santri', '20', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 11:00:08'),
(141, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 10:08:52'),
(142, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 10:15:23'),
(143, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 0, gagal 1, duplikat 0', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 06:02:40'),
(144, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 0, gagal 1, duplikat 0', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 06:03:36'),
(145, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 1, gagal 0, duplikat 0', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 06:07:55'),
(146, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 11:16:10'),
(147, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 2, gagal 0, duplikat 0', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 06:18:55'),
(148, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 0, gagal 0, duplikat 2', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 06:18:55'),
(149, 1, 'edit_santri', 'santri', 'santri', '22', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 06:33:22'),
(150, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 13:32:57'),
(151, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 13:33:11'),
(152, 1, 'edit_santri', 'santri', 'santri', '22', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:29:25'),
(153, 1, 'tambah_santri', 'santri', 'santri', '25', 'Menambah data santri baru dengan NIS 4232310', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:31:22'),
(154, 1, 'hapus_santri', 'santri', 'santri', '21', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:31:46'),
(155, 1, 'hapus_santri', 'santri', 'santri', '23', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:31:49'),
(156, 1, 'hapus_santri', 'santri', 'santri', '25', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:31:53'),
(157, 1, 'hapus_santri', 'santri', 'santri', '24', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:32:38'),
(158, 1, 'hapus_santri', 'santri', 'santri', '22', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:37:23'),
(159, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 1, gagal 0, duplikat 0', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:41:41'),
(160, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:46:03'),
(161, 1, 'edit_santri', 'santri', 'santri', '26', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:46:28'),
(162, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:46:43'),
(163, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:47:23'),
(164, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '26', 'Persetujuan top up #66 untuk santri Nur Ramadan sebesar Rp 1.000.000', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":1000000,\"nominal\":1000000,\"transaksi_id\":66}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:47:51'),
(165, 1, 'pengeluaran_manual', 'transaksi', 'santri', '26', 'Pengeluaran manual untuk santri Nur Ramadan sebesar Rp 15.000. Keterangan: beli kitab', '{\"saldo_sebelum\":1000000}', '{\"saldo_sesudah\":985000,\"nominal\":15000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:48:47'),
(166, 1, 'tambah_santri', 'santri', 'santri', '27', 'Menambah data santri baru dengan NIS 4232301', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:51:00'),
(167, 1, 'edit_santri', 'santri', 'santri', '27', 'Mengubah data santri NIS 4232301', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:51:50'),
(168, 1, 'topup_manual', 'transaksi', 'santri', '27', 'Top up manual untuk santri santri1 sebesar Rp 50.000. Keterangan: ya go ko', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":50000,\"nominal\":50000}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:52:12'),
(169, 1, 'hapus_santri', 'santri', 'santri', '27', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:52:21'),
(170, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 0, gagal 0, duplikat 1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:52:59');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `target_type`, `target_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(171, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 2, gagal 0, duplikat 0', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:53:19'),
(172, 1, 'hapus_santri', 'santri', 'santri', '28', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:53:34'),
(173, 1, 'import_santri', 'santri', 'santri', NULL, 'Import data santri selesai: berhasil 1, gagal 0, duplikat 1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:53:46'),
(174, 1, 'hapus_santri', 'santri', 'santri', '30', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:53:51'),
(175, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:53:58'),
(176, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:54:43'),
(177, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:54:54'),
(178, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:54:58'),
(179, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 14:56:36'),
(180, 1, 'edit_pengguna', 'users', 'users', '3', 'Mengubah data pengguna maulida', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:56:49'),
(181, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 09:57:45'),
(182, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '114.10.120.139', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Mobile Safari/537.36', '2026-09-23 15:02:10'),
(183, 1, 'cash_global_masuk', 'laporan', 'santri', '29', 'Penambahan cash global untuk santri Risqi sebesar Rp 100.000. Keterangan: Dari DPR', '{\"saldo_sebelum\":0}', '{\"saldo_sesudah\":100000}', '114.10.120.139', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Mobile Safari/537.36', '2026-09-23 15:03:18'),
(184, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '114.10.120.139', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Mobile Safari/537.36', '2026-09-23 10:05:21'),
(185, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 15:09:26'),
(186, 1, 'verifikasi_topup_setuju', 'transaksi', 'santri', '26', 'Persetujuan top up #70 untuk santri Nur Ramadan sebesar Rp 1.000.000', '{\"saldo_sebelum\":985000}', '{\"saldo_sesudah\":1985000,\"nominal\":1000000,\"transaksi_id\":70}', '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 15:10:03'),
(187, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-23 10:12:43'),
(188, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-24 10:06:37'),
(189, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-24 05:10:06'),
(190, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-24 10:17:23'),
(191, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:22:32'),
(192, 1, 'hapus_pengguna', 'users', 'users', '3', 'Menghapus pengguna akun maulida', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:26:43'),
(193, 1, 'tambah_pengguna', 'users', 'users', '4', 'Menambahkan pengguna Nur Ramadan dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:27:34'),
(194, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:27:40'),
(195, 4, 'login_success', 'auth', 'users', '4', 'Pengurus Nur Ramadan berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:27:53'),
(196, 4, 'logout', 'auth', 'users', '4', 'Pengguna Nur Ramadan berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:28:56'),
(197, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-24 05:29:25'),
(198, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:36:55'),
(199, 1, 'hapus_pengguna', 'users', 'users', '4', 'Menghapus pengguna akun Nur Ramadan', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:37:07'),
(200, 1, 'tambah_pengguna', 'users', 'users', '5', 'Menambahkan pengguna admin1 dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:37:22'),
(201, 1, 'hapus_pengguna', 'users', 'users', '5', 'Menghapus pengguna akun admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:38:20'),
(202, 1, 'tambah_pengguna', 'users', 'users', '6', 'Menambahkan pengguna nrmdn dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:38:33'),
(203, 1, 'hapus_pengguna', 'users', 'users', '6', 'Menghapus pengguna akun nrmdn', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:39:22'),
(204, 1, 'tambah_pengguna', 'users', 'users', '7', 'Menambahkan pengguna nrmdn dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:40:15'),
(205, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:40:36'),
(206, 7, 'login_success', 'auth', 'users', '7', 'Pengurus nrmdn berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:40:44'),
(207, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:43:32'),
(208, 1, 'tambah_pengguna', 'users', 'users', '8', 'Menambahkan pengguna admin1 dengan role admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:43:51'),
(209, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:44:08'),
(210, 8, 'login_success', 'auth', 'users', '8', 'Pengurus admin1 berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:44:17'),
(211, 8, 'logout', 'auth', 'users', '8', 'Pengguna admin1 berhasil keluar.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 05:44:26'),
(212, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:44:36'),
(213, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 10:46:52'),
(214, 1, 'hapus_pengguna', 'users', 'users', '8', 'Menghapus pengguna akun admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:01:24'),
(215, 1, 'tambah_pengguna', 'users', 'users', '9', 'Menambahkan pengguna admin1 dengan role admin', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:01:49'),
(216, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:01:55'),
(217, 9, 'login_success', 'auth', 'users', '9', 'Pengurus admin1 berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:02:03'),
(218, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:03:08'),
(219, 1, 'edit_pengguna', 'users', 'users', '9', 'Mengubah data pengguna admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:03:32'),
(220, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:04:09'),
(221, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:04:14'),
(222, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:04:22'),
(223, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:04:29'),
(224, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:04:39'),
(225, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:04:45'),
(226, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:05:04'),
(227, 9, 'logout', 'auth', 'users', '9', 'Pengguna admin1 berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:05:07'),
(228, NULL, 'login_failed', 'auth', 'users', NULL, 'Percobaan login gagal untuk username: admin1', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:05:15'),
(229, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:05:20'),
(230, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:05:21'),
(231, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:05:39'),
(232, 9, 'login_success', 'auth', 'users', '9', 'Pengurus admin1 berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:05:46'),
(233, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-24 11:06:08'),
(234, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:09:39'),
(235, 9, 'login_success', 'auth', 'users', '9', 'Pengurus admin1 berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:09:49'),
(236, 9, 'logout', 'auth', 'users', '9', 'Pengguna admin1 berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:11:08'),
(237, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:11:16'),
(238, 1, 'logout', 'auth', 'users', '1', 'Pengguna admin berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:13:37'),
(239, 9, 'login_success', 'auth', 'users', '9', 'Pengurus admin1 berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:13:46'),
(240, 9, 'logout', 'auth', 'users', '9', 'Pengguna admin1 berhasil keluar.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:14:22'),
(241, 1, 'login_success', 'auth', 'users', '1', 'Pengurus admin berhasil login ke sistem.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 11:14:30'),
(242, 1, 'tambah_santri', 'santri', 'santri', '31', 'Menambah data santri baru dengan NIS 2026002', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:15:34'),
(243, 1, 'hapus_santri', 'santri', 'santri', '31', 'Menghapus permanen data santri beserta riwayat transaksinya.', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:15:45'),
(244, 1, 'edit_santri', 'santri', 'santri', '26', 'Mengubah data santri NIS 2026001', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:35:40'),
(245, 1, 'tambah_santri', 'santri', 'santri', '32', 'Menambah data santri baru dengan NIS 2026002', NULL, NULL, '103.175.230.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-24 06:36:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(50) NOT NULL,
  `attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `santri`
--

CREATE TABLE `santri` (
  `id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `ttl` varchar(100) DEFAULT NULL,
  `nama_ortu` varchar(100) DEFAULT NULL,
  `no_hp_ortu` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jk` enum('L','P') NOT NULL,
  `foto` varchar(255) DEFAULT 'default.png',
  `saldo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deleted_at` datetime DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `santri`
--

INSERT INTO `santri` (`id`, `nis`, `nama`, `ttl`, `nama_ortu`, `no_hp_ortu`, `alamat`, `email`, `jk`, `foto`, `saldo`, `deleted_at`, `qr_token`) VALUES
(26, '2026001', 'Nur Ramadan', 'Brebes, 13 Oktober 2005', 'Suherlan', '087710383581', 'Ds. Karangjongkeng, Kec. Tonjong, Kab. Brebes', 'nurramadan56@gmail.com', 'L', 'santri_8af79603b7f84d237e979eb99450e28b.jpg', 1985000.00, NULL, 'f853888d17edde4b345f326e44f4b1634c525b5d03dfa3148c92e9f898c6055c'),
(29, '4232303', 'Risqi', 'Brebes, 02 Desember 2006', 'kepo 2', '098765', '', 'risqi@gmail.com', 'P', 'default.png', 100000.00, NULL, '45db6a4b9e3b094368fdb709974bdcd5a39c56de9fbb3e37b8413e9c0b9da21d'),
(32, '2026002', 'Adil Kusuma', 'Brebes, 09 Desember 2004', 'Abdul Wahid', '081234567890', 'Karangjongkeng', 'adil@gmail.com', 'L', 'default.png', 0.00, NULL, '4f9f5fbb1202b030b77af81e20d77a7937405bb87b09a6ef1f9a64be83a9a3ce');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `santri_id` int(11) NOT NULL,
  `jenis_transaksi` enum('masuk','keluar') NOT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` varchar(255) DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status` enum('pending','sukses','gagal') DEFAULT 'pending',
  `tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `santri_id`, `jenis_transaksi`, `nominal`, `keterangan`, `bukti_transfer`, `status`, `tanggal`) VALUES
(66, 26, 'masuk', 1000000.00, 'Top Up Orang Tua: sumanto (87710383564) via BRI - 012401002345539 - Catatan: hello', 'struk_20260923_996aaa516ba042790c7d61150102777b.jpg', 'sukses', '2026-09-23 14:45:11'),
(67, 26, 'keluar', 15000.00, 'beli kitab', NULL, 'sukses', '2026-09-23 14:48:47'),
(69, 29, 'masuk', 100000.00, 'Dari DPR', NULL, 'sukses', '2026-09-23 15:03:18'),
(70, 26, 'masuk', 1000000.00, 'Top Up Orang Tua: Ahmad (085803932175) via BRI - 012401002345539 - Catatan: Cuy', 'struk_20260923_358c534f65e9441b0bfb987e29c801fb.jpg', 'sukses', '2026-09-23 15:09:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `foto` varchar(255) DEFAULT 'default.png',
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin') DEFAULT 'admin',
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `foto`, `password`, `nama_lengkap`, `role`, `failed_attempts`, `locked_until`) VALUES
(1, 'admin', 'admin_1788109103.jpg', '$2y$10$u013tt9gkpIIWscgGxOyx.TMsosh0OnZvz1UldrIoJXyTkfRLqf5y', 'Admin', 'superadmin', 0, NULL),
(9, 'admin1', 'default.png', '$2y$10$VXI9Mg6/b774sn5vg3mrZedR/JJw/FPhInlC5k6lPcb58lN02Onz.', 'siapa hayoh', 'admin', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_module` (`module`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indeks untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_ip_time` (`ip_address`,`attempt_time`),
  ADD KEY `idx_login_user_time` (`username`,`attempt_time`);

--
-- Indeks untuk tabel `santri`
--
ALTER TABLE `santri`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `uq_santri_qr_token` (`qr_token`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaksi_santri_status` (`santri_id`,`status`),
  ADD KEY `idx_transaksi_status_jenis` (`status`,`jenis_transaksi`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `santri`
--
ALTER TABLE `santri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_santri` FOREIGN KEY (`santri_id`) REFERENCES `santri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trx_to_santri` FOREIGN KEY (`santri_id`) REFERENCES `santri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
