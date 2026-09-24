<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';

// Jika sudah login, langsung alihkan ke dashboard.
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$login_error = $_SESSION['login_error'] ?? '';
$login_info  = $_SESSION['login_info'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_info']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Saku Santri</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/libs/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Custom Responsive Login Style -->
    <link rel="stylesheet" href="../assets/css/login_style.css">
</head>
<body class="login-page">

<div class="login-wrapper">

    <!-- SLIDER: Atas di HP, Kanan di Desktop -->
    <div class="login-slider-pane">
        <div id="sliderLogin" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3500">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="../assets/img/bg_4.jpg" alt="Pesantren 1">
                </div>
                <div class="carousel-item">
                    <img src="../assets/img/bg_2.jpeg" alt="Pesantren 2">
                </div>
                <div class="carousel-item">
                    <img src="../assets/img/bg_3.jpg" alt="Pesantren 3">
                </div>
            </div>

            <!-- Overlay Teks Edukasi -->
            <div class="slider-overlay-text">
                <h4><img src="../assets/img/logo_ponpes.png" alt="Logo" class="me-2" style="height: 40px;">PonPes Al Habibatain Bumiayu</h4>
                <p class="mb-0">Mewujudkan ekosistem pesantren digital yang aman, transparan, dan barokah.</p>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#sliderLogin" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#sliderLogin" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Selanjutnya</span>
            </button>
        </div>
    </div>

    <!-- FORM LOGIN: Bawah di HP, Kiri di Desktop -->
    <div class="login-form-pane">
        <div class="login-card-inner">

            <div class="brand-badge">
                <img src="../assets/img/logo_ponpes.png" alt="Logo" style="height: 25px;">
                <span>Saku Santri System</span>
            </div>

            <h2 class="login-title">Selamat Datang</h2>
            <p class="login-subtitle">Silakan masukkan username dan password akun pengurus untuk mengelola sistem.</p>

            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger border-0 shadow-sm py-2 px-3 small fw-semibold mb-3 rounded-3">
                    <i class="fas fa-exclamation-triangle me-1"></i> <?= e($login_error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($login_info)): ?>
                <div class="alert alert-info border-0 shadow-sm py-2 px-3 small fw-semibold mb-3 rounded-3">
                    <i class="fas fa-info-circle me-1"></i> <?= e($login_info); ?>
                </div>
            <?php endif; ?>

            <!-- Form Login -->
            <form action="proses_login.php" method="POST">
                <?= csrf_field(); ?>

                <div class="form-group-custom">
                    <label for="username">Username</label>
                    <div class="input-group-custom">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Masukkan username"
                            id="username"
                            name="username"
                            autocomplete="username"
                            required>
                    </div>
                </div>

                <div class="form-group-custom">
                    <label for="password">Password</label>
                    <div class="input-group-custom">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input
                            type="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn-login mt-2">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Panel Kontrol
                </button>

                <div class="login-back-link">
                    <a href="../index.php" class="btn-back-home">
                        <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
