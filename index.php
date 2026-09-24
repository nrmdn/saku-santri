<?php
require 'config/koneksi.php';
require_once 'includes/security.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Saku Santri - Sistem Kartu Digital & Portal Pesantren</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0f6848;
            --primary-dark: #094731;
            --primary-light: #e8f5ee;
            --accent-color: #f59e0b;
            --accent-hover: #d97706;
            --dark-color: #1e293b;
            --light-bg: #f8fafc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        /* Cegah 100% white gap / horizontal overflow di semua browser HP */
        html, body {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden !important;
            position: relative;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #334155;
            scroll-behavior: smooth;
        }

        section, header, footer, nav {
            width: 100%;
            max-width: 100%;
            overflow-x: clip;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding-left: 15px;
            padding-right: 15px;
            margin-left: auto;
            margin-right: auto;
            overflow-x: clip;
        }

        /* Navbar Styling */
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 4px 20px rgba(15, 104, 72, 0.15);
            transition: all 0.3s ease;
            padding: 0.75rem 0;
            width: 100%;
        }
        .navbar-custom .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: clamp(1.1rem, 3vw, 1.3rem);
        }
        .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.88) !important;
            transition: color 0.2s ease;
            padding: 0.5rem 0.85rem !important;
            font-size: 0.95rem;
        }
        .nav-link:hover {
            color: #ffffff !important;
        }
        .btn-topup-nav {
            background-color: var(--accent-color);
            color: #1e293b !important;
            font-weight: 700;
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
            transition: all 0.25s ease;
            border: none;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-topup-nav:hover {
            background-color: var(--accent-hover);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.45);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #094731 0%, #0f6848 50%, #15803d 100%);
            color: white;
            padding: clamp(45px, 7vw, 90px) 0 clamp(40px, 6vw, 80px);
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 85% 20%, rgba(245, 158, 11, 0.18) 0%, transparent 45%),
                        radial-gradient(circle at 10% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 40%);
            pointer-events: none;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 5px 14px;
            border-radius: 50px;
            font-size: clamp(0.78rem, 2vw, 0.875rem);
            font-weight: 600;
            margin-bottom: 18px;
        }
        .hero-title {
            font-size: clamp(1.85rem, 5vw, 2.85rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.5px;
            margin-bottom: 18px;
        }
        .hero-title span {
            color: #fbbf24;
        }
        .hero-desc {
            font-size: clamp(0.95rem, 2.2vw, 1.12rem);
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            max-width: 600px;
        }
        .btn-hero-primary {
            background-color: var(--accent-color);
            color: #1e293b;
            font-weight: 700;
            border-radius: 50px;
            padding: 13px 28px;
            font-size: 1rem;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.35);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-hero-primary:hover {
            background-color: var(--accent-hover);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(245, 158, 11, 0.45);
        }
        .btn-hero-outline {
            background-color: transparent;
            color: #ffffff;
            font-weight: 600;
            border-radius: 50px;
            padding: 13px 26px;
            font-size: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-hero-outline:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-color: #ffffff;
            transform: translateY(-2px);
        }

        /* Mockup Card Hero */
        .card-preview-mockup {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 22px;
            padding: clamp(18px, 4vw, 28px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            color: white;
            transition: transform 0.4s ease;
            max-width: 440px;
            margin: 0 auto;
            width: 100%;
        }
        .card-preview-mockup:hover {
            transform: translateY(-4px);
        }
        .smartcard-chip {
            width: 40px;
            height: 30px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 6px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        /* Section Generic */
        .section-padding {
            padding: clamp(45px, 6vw, 80px) 0;
            width: 100%;
        }
        .section-header {
            text-align: center;
            max-width: 720px;
            margin: 0 auto clamp(30px, 5vw, 50px);
        }
        .section-tag {
            color: var(--primary-color);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.8rem;
            margin-bottom: 8px;
            display: inline-block;
        }
        .section-title {
            font-size: clamp(1.6rem, 3.8vw, 2.25rem);
            font-weight: 800;
            color: var(--dark-color);
            letter-spacing: -0.5px;
            margin-bottom: 12px;
        }
        .section-subtitle {
            font-size: clamp(0.9rem, 2vw, 1.05rem);
            color: #64748b;
            line-height: 1.6;
        }

        /* Benefit Cards */
        .benefit-card {
            background: #ffffff;
            border-radius: 18px;
            padding: clamp(20px, 3.5vw, 28px) clamp(16px, 3vw, 24px);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        .benefit-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px rgba(15, 104, 72, 0.08);
            border-color: #cbd5e1;
        }
        .benefit-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background-color: var(--primary-light);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .benefit-card:hover .benefit-icon {
            background-color: var(--primary-color);
            color: #ffffff;
            transform: scale(1.06);
        }
        .benefit-card h5 {
            font-weight: 700;
            font-size: clamp(1.05rem, 2.5vw, 1.2rem);
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        .benefit-card p {
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* Feature Cards */
        .feature-card {
            background: #ffffff;
            border-radius: 18px;
            padding: clamp(20px, 3vw, 26px);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
            height: 100%;
            transition: all 0.3s ease;
            width: 100%;
        }
        .feature-card:hover {
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
            transform: translateY(-3px);
        }
        .feature-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 16px;
        }
        .bg-teal-soft { background: #ccfbf1; color: #0d9488; }
        .bg-blue-soft { background: #e0e7ff; color: #4338ca; }
        .bg-amber-soft { background: #fef3c7; color: #d97706; }
        .bg-purple-soft { background: #f3e8ff; color: #7e22ce; }
        .bg-emerald-soft { background: #d1fae5; color: #059669; }
        .bg-rose-soft { background: #ffe4e6; color: #e11d48; }

        /* CTA Banner Section */
        .cta-banner-topup {
            background: linear-gradient(135deg, #094731 0%, #0f6848 100%);
            border-radius: clamp(18px, 3vw, 28px);
            padding: clamp(24px, 5vw, 45px) clamp(18px, 4vw, 36px);
            color: white;
            box-shadow: 0 15px 35px rgba(15, 104, 72, 0.2);
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        .cta-banner-topup::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            transform: translate(30%, -30%);
            width: 200px;
            height: 200px;
            background: rgba(245, 158, 11, 0.15);
            border-radius: 50%;
            pointer-events: none;
        }
        .cta-banner-topup h3 {
            font-size: clamp(1.3rem, 3vw, 1.85rem);
            font-weight: 800;
        }

        /* Check Saldo Section */
        .check-saldo-box {
            background: #ffffff;
            border-radius: 22px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 30px rgba(0,0,0,0.04);
            padding: clamp(18px, 4vw, 35px);
            width: 100%;
            max-width: 100%;
        }

        .form-control {
            font-size: 15px;
            padding: 10px 14px;
            border-radius: 10px;
            max-width: 100%;
        }

        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Footer */
        .footer-main {
            background: #092c1e;
            color: #94a3b8;
            padding: clamp(40px, 6vw, 60px) 0 25px;
            font-size: 0.92rem;
            width: 100%;
        }
        .footer-main h5 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 16px;
            font-size: 1.1rem;
        }
        .footer-main a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .footer-main a:hover {
            color: #fbbf24;
        }

        /* Floating action button untuk Orang Tua */
        .float-topup-btn {
            position: fixed;
            bottom: clamp(14px, 3vw, 25px);
            right: clamp(14px, 3vw, 25px);
            z-index: 999;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            font-weight: 700;
            border-radius: 50px;
            padding: clamp(9px, 2vw, 12px) clamp(16px, 3vw, 24px);
            box-shadow: 0 8px 22px rgba(245, 158, 11, 0.45);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            text-decoration: none;
            transition: all 0.3s ease;
            max-width: calc(100vw - 28px);
            white-space: nowrap;
        }
        .float-topup-btn:hover {
            transform: translateY(-3px) scale(1.02);
            color: #ffffff;
            box-shadow: 0 12px 26px rgba(245, 158, 11, 0.55);
        }

        @media (max-width: 576px) {
            .btn-hero-primary, .btn-hero-outline {
                width: 100%;
            }
            .hero-badge {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Mengambang (Floating CTA) untuk Orang Tua -->
    <a href="topup.php" class="float-topup-btn shadow">
        <i class="fas fa-wallet fs-5"></i>
        <span>Isi Saldo Santri</span>
    </a>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/img/logo_ponpes.png" alt="Logo" class="me-2" style="height: 40px;">
                <div>
                    <span class="d-block lh-1">Saku Santri</span>
                    <small class="text-white" style="font-size: 12px; font-weight: 400; letter-spacing: 0.5px; line-height: 1.1;">Ponpes Al Habibatain Bumiayu</small>
                </div>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1 my-3 my-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#hero"><i class="fas fa-home me-1 opacity-75"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang"><i class="fas fa-info-circle me-1 opacity-75"></i> Mengenal Saku Santri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur"><i class="fas fa-th-large me-1 opacity-75"></i> Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cek_saldo.php"><i class="fas fa-search me-1 opacity-75"></i> Cek Saldo</a>
                    </li>
                    <li class="nav-item ms-lg-2 my-2 my-lg-0">
                        <!-- Tombol Khusus Orang Tua: Buka Halaman Top Up -->
                        <a class="btn btn-topup-nav w-100 w-lg-auto" href="topup.php">
                            <i class="fas fa-wallet me-2"></i>Isi Saldo Santri
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="admin/login.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3 py-2 w-100 w-lg-auto text-center">
                            <i class="fas fa-user-shield me-1"></i> Login Pengurus
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <header class="hero-section" id="hero">
        <div class="container">
            <div class="row align-items-center gy-4 gy-lg-5">
                <div class="col-lg-7">
                    <div class="hero-badge">
                        <i class="fas fa-sparkles text-warning"></i>
                        <span>Inovasi Pesantren Modern & Terpercaya</span>
                    </div>
                    <h1 class="hero-title">
                        Saku Santri <br>
                        <span>PonPes Al Habibatain Bumiayu</span>
                    </h1>
                    <p class="hero-desc">
                        Saku Santri Pondok Pesantren Al Habibatain Bumiayu merupakan pengembangan Kartu Tanda Santri yang terintegrasi dengan teknologi Smart Card dan QR Code. Memudahkan proses identifikasi santri serta memberikan akses informasi saldo secara cepat, praktis, dan transparan melalui satu kartu.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3">
                        <!-- Tombol Utama Khusus Orang Tua Menuju Halaman Top Up -->
                        <a href="topup.php" class="btn btn-hero-primary">
                            <i class="fas fa-wallet me-2"></i>Isi Saldo Santri Sekarang
                        </a>
                        <a href="cek_saldo.php" class="btn btn-hero-outline">
                            <i class="fas fa-search me-2"></i>Cek Saldo & Mutasi
                        </a>
                    </div>

                    <!-- Poin Keunggulan (Bebas Negative Margin Row) -->
                    <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3 mt-4 pt-3 border-top border-white border-opacity-10 text-white-50">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-shield-halved text-warning fs-6"></i>
                            <span class="text-white small fw-semibold">100% Data Santri Lebih Terlindungi
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-clock text-warning fs-6"></i>
                            <span class="text-white small fw-semibold">Pantau Saldo Kapan Saja</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-user-check text-warning fs-6"></i>
                            <span class="text-white small fw-semibold">Sistem Terverifikasi</span>
                        </div>
                    </div>
                </div>

                <!-- Ilustrasi Kartu Saku Santri Digital -->
                <div class="col-lg-5">
                    <div class="card-preview-mockup">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-wifi fa-rotate-90 text-white fs-4 opacity-75"></i>
                                <span class="fw-bold tracking-wider">SAKU SANTRI</span>
                            </div>
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">SMART CARD</span>
                        </div>

                        <div class="my-4">
                            <div class="smartcard-chip mb-3"></div>
                            <h3 class="fw-bold mb-1 letter-spacing-1" style="font-size: clamp(1.15rem, 3.8vw, 1.45rem); word-break: break-all;">•••• •••• •••• 2026</h3>
                            <small class="text-white-50">Nomor Induk Santri (NIS)</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-end pt-3 border-top border-white border-opacity-20">
                            <div>
                                <small class="text-white-50 d-block text-uppercase" style="font-size: 11px;">Nama Santri</small>
                                <span class="fw-bold fs-6">Nur Ramadan</span>
                            </div>
                            <div class="text-end">
                                <small class="text-white-50 d-block text-uppercase" style="font-size: 11px;">Lembaga</small>
                                <span class="fw-semibold small">PP. Al Habibatain</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 text-white-50 small">
                        <i class="fas fa-lock me-1"></i> Dilengkapi otentikasi chip/QR aman & terenkripsi
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- SECTION PENGENALAN: UNTUK APA SAJA SAKU SANTRI? -->
    <section class="section-padding" id="tentang">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Pengenalan Sistem</span>
                <h2 class="section-title">Untuk Apa Saja Saku Santri?</h2>
                <p class="section-subtitle">
                    Saku Santri merupakan inovasi Kartu Tanda Santri di Pondok Pesantren Al Habibatain Bumiayu yang mengintegrasikan teknologi Smart Card, dan QR Code dalam satu kartu. Sistem ini dirancang untuk mendukung proses identifikasi santri serta memberikan akses informasi saldo secara cepat, praktis, dan transparan.
                </p>
            </div>

            <div class="row g-3 g-md-4">
                <!-- Manfaat 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5>Kartu Identitas Santri Terintegrasi</h5>
                        <p>
                            Saku Santri terintegrasi langsung dengan Kartu Tanda Santri, sehingga satu kartu dapat berfungsi sebagai identitas sekaligus menjadi bagian dari sistem layanan digital pesantren.
                        </p>
                    </div>
                </div>

                <!-- Manfaat 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h5>Identifikasi dengan Teknologi Tap</h5>
                        <p>
                            Teknologi Tap memungkinkan kartu dikenali oleh sistem secara cepat dan praktis. Fitur ini dapat dimanfaatkan untuk mendukung berbagai kebutuhan identifikasi dan layanan santri yang terintegrasi dengan sistem.
                        </p>
                    </div>
                </div>

                <!-- Manfaat 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h5>Cek Saldo dengan QR Code</h5>
                        <p>
                            Setiap kartu dilengkapi QR Code yang dapat dipindai untuk mengakses informasi saldo santri. Informasi dapat dilihat dengan mudah tanpa menjadikan kartu sebagai alat transaksi.
                        </p>
                    </div>
                </div>

                <!-- Manfaat 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h5>Informasi Saldo yang Mudah Diakses</h5>
                        <p>
                            Santri dan wali santri dapat memperoleh informasi saldo secara lebih praktis melalui sistem digital. Dengan begitu, kondisi saldo dapat diketahui dengan cepat tanpa proses yang rumit.
                        </p>
                    </div>
                </div>

                <!-- Manfaat 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h5>Satu Kartu, Beragam Fungsi</h5>
                        <p>
                            Integrasi identitas santri, Smart Card dan QR Code menjadikan Saku Santri sebagai satu kartu yang mendukung berbagai kebutuhan layanan digital di lingkungan pesantren.
                        </p>
                    </div>
                </div>

                <!-- Manfaat 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-digital-tachograph"></i>
                        </div>
                        <h5>Mendukung Digitalisasi Pesantren</h5>
                        <p>
                            Saku Santri menjadi bagian dari upaya Pondok Pesantren Al Habibatain Bumiayu dalam mengembangkan layanan yang lebih modern, efisien, terintegrasi, dan mudah diakses oleh santri maupun wali santri.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION FITUR-FITUR UTAMA -->
    <section class="section-padding bg-light" id="fitur">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Fitur Unggulan</span>
                <h2 class="section-title">Fitur Modern Saku Santri</h2>
                <p class="section-subtitle">
                    Berbagai kemudahan teknologi yang terintegrasi untuk santri, wali santri, dan pengurus pondok pesantren.
                </p>
            </div>

            <div class="row g-3 g-md-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-badge bg-emerald-soft">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5 class="fw-bold mb-2 fs-6">Kartu Santri Pintar (QR)</h5>
                        <p class="text-muted small mb-0">
                            Kartu fisik multifungsi sebagai kartu tanda santri sekaligus kartu cek saldo digital.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-badge bg-amber-soft">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h5 class="fw-bold mb-2 fs-6">Portal Top Up Online Wali Santri</h5>
                        <p class="text-muted small mb-2">
                            Layanan khusus orang tua di halaman tersendiri untuk mengisi saldo anak via transfer bank, lengkap dengan upload bukti transfer.
                        </p>
                        <a href="topup.php" class="text-warning fw-bold small text-decoration-none">
                            Buka Form Top Up <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-badge bg-teal-soft">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h5 class="fw-bold mb-2 fs-6">Panel Verifikasi Administrator</h5>
                        <p class="text-muted small mb-0">
                            Antrean bukti transaksi yang masuk langsung diverifikasi pengurus pondok dengan satu klik persetujuan untuk menambah saldo santri.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-badge bg-blue-soft">
                            <i class="fas fa-search-dollar"></i>
                        </div>
                        <h5 class="fw-bold mb-2 fs-6">Portal Pengecekan Saldo Mandiri</h5>
                        <p class="text-muted small mb-0">
                            Orang tua dapat mengecek sisa saldo santri kapan saja cukup dengan memasukkan NIS, nama, dan verifikasi nomor HP/email wali.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-badge bg-purple-soft">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="fw-bold mb-2 fs-6">Laporan & Riwayat Mutasi Rinci</h5>
                        <p class="text-muted small mb-0">
                            Catatan lengkap arus kas masuk (top up) dan keluar dengan stempel waktu, nominal, dan keterangan jelas.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-badge bg-rose-soft">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5 class="fw-bold mb-2 fs-6">Keamanan & Privasi Terjamin</h5>
                        <p class="text-muted small mb-0">
                            Keamanan data santri dilindungi dengan verifikasi ganda saat akses, mencegah kebocoran informasi pribadi santri.
                        </p>
                    </div>
                </div>
            </div>

            <!-- BANNER PROMOSI ISI SALDO (MENGARAHKAN KE HALAMAN TOP UP) -->
            <div class="mt-4 mt-md-5">
                <div class="cta-banner-topup">
                    <div class="row align-items-center gy-3">
                        <div class="col-lg-8">
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2" style="font-size: 11px;">
                                <i class="fas fa-heart me-1"></i> Sayangi Putra-Putri Anda
                            </span>
                            <h3 class="mb-2">Ingin Mengisi Uang Saku / Saldo Santri Sekarang?</h3>
                            <p class="text-white-50 mb-0 small" style="line-height: 1.6;">
                                Wali santri kini dapat melakukan transfer dari rumah kapan saja. Buka halaman khusus isi saldo dan unggah bukti transfer Anda untuk diverifikasi oleh admin.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a href="topup.php" class="btn btn-warning btn-md fw-bold px-4 py-3 rounded-pill shadow w-100 w-lg-auto text-center">
                                <i class="fas fa-wallet me-2"></i>Buka Halaman Isi Saldo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION PENGECEKAN SALDO & MUTASI -->
    <section class="section-padding" id="cek-saldo">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-tag">Portal Transparansi</span>
                <h2 class="section-title">Cek Saldo & Mutasi Santri</h2>
                <p class="section-subtitle mx-auto" style="max-width: 680px;">
                    Wali santri kini dapat memantau sisa uang saku dan mutasi transaksi anak secara mandiri melalui portal khusus yang aman dan terenkripsi.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-body p-4 p-md-5">
                            <div class="row align-items-center gy-4">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center gap-2 text-success fw-bold small mb-2">
                                        <i class="fas fa-shield-alt fs-5"></i>
                                        <span>Keamanan Verifikasi Ganda</span>
                                    </div>
                                    <h3 class="fw-bold text-dark mb-3">Pengecekan Saldo Lebih Praktis di Halaman Khusus</h3>
                                    <p class="text-muted small mb-4" style="line-height: 1.7;">
                                        Untuk menjamin kerahasiaan data dan kenyamanan akses dari ponsel maupun laptop, fitur pengecekan saldo dan mutasi telah dipisahkan ke portal mandiri. Cukup masukkan NIS, nama santri, dan verifikasi kontak wali.
                                    </p>
                                    <div class="d-flex flex-column flex-sm-row gap-3">
                                        <a href="cek_saldo.php" class="btn btn-success fw-bold rounded-pill px-4 py-3 shadow-sm text-center">
                                            <i class="fas fa-search-dollar me-2"></i>Buka Portal Cek Saldo & Mutasi
                                        </a>
                                        <a href="topup.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 py-3 text-center">
                                            <i class="fas fa-wallet me-2"></i>Isi Saldo Santri
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-5 text-center">
                                    <div class="p-4 rounded-4 bg-white shadow-sm border text-center">
                                        <div class="benefit-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">Cek Cepat via QR</h5>
                                        <p class="text-muted small mb-0">
                                            Pindai QR Code pada Kartu Tanda Santri (KTS) untuk membuka saldo secara instan di smartphone Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer-main">
        <div class="container">
            <div class="row g-4 mb-4 mb-md-5">
                <div class="col-lg-5">
                    <h5 class="d-flex align-items-center gap-2">
                        <i class="fas fa-credit-card text-warning"></i> Saku Santri
                    </h5>
                    <p class="text-white-50 small mb-3" style="max-width: 400px; line-height: 1.7;">
                        Platform portal transparansi keuangan santri Pondok Pesantren Al Habibatain Bumiayu.
                    </p>
                    <span class="badge bg-white bg-opacity-10 text-white p-2">
                        <img src="assets/img/logo_ponpes.png" alt="Logo" class="img-fluid" style="max-height: 20px;">
                        Pondok Pesantren Al Habibatain Bumiayu
                    </span>
                </div>

                <div class="col-lg-3 col-5">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="#hero"><i class="fas fa-chevron-right me-1 text-warning"></i> Beranda</a></li>
                        <li><a href="admin/login.php"><i class="fas fa-chevron-right me-1 text-warning"></i> Login Admin</a></li>
                        <li><a href="#tentang"><i class="fas fa-chevron-right me-1 text-warning"></i> Mengenal Saku Santri</a></li>
                        <li><a href="#fitur"><i class="fas fa-chevron-right me-1 text-warning"></i> Fitur Unggulan</a></li>
                        <li><a href="topup.php"><i class="fas fa-chevron-right me-1 text-warning"></i> Isi Saldo Santri</a></li>
                        <li><a href="cek_saldo.php"><i class="fas fa-chevron-right me-1 text-warning"></i> Cek Saldo & Mutasi</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-7">
                    <h5>Pusat Bantuan</h5>
                    <p class="small text-white-50 mb-2">
                        Kendala transaksi atau pengecekan kartu?
                    </p>
                    <div class="d-flex align-items-center gap-2 mb-2 text-white">
                        <i class="fab fa-whatsapp text-success fs-5"></i>
                        <a href="https://wa.me/6282225113957" target="_blank" class="small text-white text-decoration-none">
                            +62 822-2511-3957
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2 text-white">
                        <i class="fab fa-instagram text-danger fs-5"></i>
                        <a href="https://www.instagram.com/alhabibatainbumiayu?stkn=YXA0YWZmajNjaG5zhttps:" target="_blank" class="small text-white text-decoration-none">
                            @alhabibatainbumiayu
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-white">
                        <i class="fas fa-envelope text-warning fs-5"></i>
                        <a href="mailto:alhabibatain210@gmail.com" class="small text-white text-decoration-none">
                            alhabibatain210@gmail.com
                        </a>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-top border-white border-opacity-10 d-flex flex-wrap justify-content-between align-items-center text-white-50 small gap-2">
                <div>
                    &copy; <?= date('Y'); ?> Saku Santri - PP. Al Habibatain.
                </div>
                <div>
                    Dibuat dengan <i class="fas fa-heart text-danger"></i> oleh <a href="https://www.instagram.com/alhabibatainbumiayu?stkn=YXA0YWZmajNjaG5zhttps:" target="_blank" class="text-white-50">Ponpes Al Habibatain</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>