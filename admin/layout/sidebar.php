<?php
// Cek apakah admin sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_admin = (int)($_SESSION['id_admin'] ?? 0);

// Ambil data admin terbaru dari database menggunakan Prepared Statement
$stmt_adm = mysqli_prepare($koneksi, "SELECT nama_lengkap, username, role, foto FROM users WHERE id = ? LIMIT 1");
$data_admin = null;
if ($stmt_adm) {
    mysqli_stmt_bind_param($stmt_adm, "i", $id_admin);
    mysqli_stmt_execute($stmt_adm);
    $res_adm = mysqli_stmt_get_result($stmt_adm);
    $data_admin = mysqli_fetch_assoc($res_adm);
    mysqli_stmt_close($stmt_adm);
}

$nama_admin     = $data_admin['nama_lengkap'] ?? ($_SESSION['nama_lengkap'] ?? 'Administrator');
$username_admin = $data_admin['username'] ?? ($_SESSION['username'] ?? 'admin');
$role_admin     = $data_admin['role'] ?? ($_SESSION['role'] ?? 'admin');
$foto           = (!empty($data_admin['foto'])) ? $data_admin['foto'] : 'default.png';

// Hitung total pending top up dengan Prepared Statement
$q_badge_topup = mysqli_query($koneksi, "SELECT COUNT(id) AS total_pending FROM transaksi WHERE status = 'pending' AND jenis_transaksi = 'masuk'");
$d_badge_topup = mysqli_fetch_assoc($q_badge_topup);
$total_pending_topup = (int)($d_badge_topup['total_pending'] ?? 0);
?>
<!-- Sidebar Spark Admin -->
<div class="sidebar-wrapper" id="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <i class="bi bi-credit-card-2-front-fill"></i>
        <span>Saku Santri</span>
    </a>

    <div class="flex-grow-1 overflow-y-auto">
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Menu</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="dashboard.php" class="sidebar-menu-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Data Utama</div>
            <ul class="sidebar-menu-list">
                
                <!-- HANYA SUPERADMIN YANG BISA MELIHAT MENU DATA PENGGUNA -->
                <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
                <li class="sidebar-menu-item">
                    <a href="data_pengguna.php" class="sidebar-menu-link <?= basename($_SERVER['PHP_SELF']) === 'data_pengguna.php' ? 'active' : ''; ?>">
                        <i class="bi bi-people-fill"></i>
                        <span>Data Pengguna</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (has_permission('data_santri') || has_permission('view_santri')): ?>
                <li class="sidebar-menu-item">
                    <a href="data_santri.php" class="sidebar-menu-link <?= basename($_SERVER['PHP_SELF']) === 'data_santri.php' ? 'active' : ''; ?>">
                        <i class="bi bi-mortarboard-fill"></i>
                        <span>Data Santri</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Transaksi</div>
            <ul class="sidebar-menu-list">
                <?php if (has_permission('laporan_cash') || has_permission('view_laporan')): ?>
                <li class="sidebar-menu-item">
                    <a href="laporan_cash.php" class="sidebar-menu-link <?= basename($_SERVER['PHP_SELF']) === 'laporan_cash.php' ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up"></i>
                        <span>Laporan Cash</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (has_permission('verifikasi_topup')): ?>
                <li class="sidebar-menu-item">
                    <a href="verifikasi_topup.php" class="sidebar-menu-link d-flex align-items-center justify-content-between <?= basename($_SERVER['PHP_SELF']) === 'verifikasi_topup.php' ? 'active' : ''; ?>">
                        <div>
                            <i class="bi bi-patch-check-fill"></i>
                            <span>Verifikasi Top Up</span>
                        </div>
                        <?php if ($total_pending_topup > 0): ?>
                            <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 11px;"><?= $total_pending_topup; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Pengaturan</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="ganti_password.php" class="sidebar-menu-link <?= basename($_SERVER['PHP_SELF']) === 'ganti_password.php' ? 'active' : ''; ?>">
                        <i class="bi bi-key-fill"></i>
                        <span>Ganti Password</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="logout.php" class="sidebar-menu-link text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-profile">
         <img src="../assets/images/<?= e($foto); ?>" class="sidebar-profile-img" alt="Foto">
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name d-flex align-items-center gap-1">
                <?= e($nama_admin); ?>
            </div>
            <div class="sidebar-profile-email">
                <?= e($username_admin); ?> 
                <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2" style="font-size: 9.5px;"><?= e(strtoupper($role_admin)); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <header class="navbar-custom">
        <div class="navbar-left">
            <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-2"
                    id="desktop-sidebar-toggle" aria-label="Minimize Sidebar" type="button">
                <i class="bi bi-chevron-bar-left"></i>
            </button>

            <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation" type="button">
                <i class="bi bi-list"></i>
            </button>

            <div class="dropdown ms-2">
                <button class="btn-quick-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-lg"></i>
                    <span>Aksi Cepat</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-quick-action">
                    <li class="dropdown-header">Akses Cepat</li>
                    
                    <!-- HANYA SUPERADMIN YANG BISA AKSES MENU TAMBAH CEPAT INI -->
                    <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
                        <li><a class="dropdown-item" href="data_santri.php"><i class="bi bi-person-plus"></i> Tambah Santri</a></li>
                        <li><a class="dropdown-item" href="data_pengguna.php"><i class="bi bi-person-gear"></i> Data Pengguna</a></li>
                    <?php endif; ?>
                    
                    <?php if (has_permission('verifikasi_topup')): ?>
                    <li><a class="dropdown-item" href="verifikasi_topup.php"><i class="bi bi-patch-check"></i> Verifikasi Top Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <form action="data_santri.php" method="GET" class="navbar-search-wrapper mb-0">
            <input type="text" name="search" class="navbar-search-input" placeholder="Cari Nama Santri atau NIS..." id="main-search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button class="navbar-search-btn" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>

        <div class="navbar-actions">
            <button class="navbar-action-btn me-1" aria-label="Toggle Fullscreen" id="btn-fullscreen" type="button">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>

            <div class="dropdown ms-1">
                <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../assets/images/<?= e($foto); ?>" alt="Profile" class="navbar-profile-img">
                    <span class="navbar-profile-name d-none d-md-inline"><?= e($nama_admin); ?></span>
                    <i class="bi bi-chevron-down navbar-profile-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile">
                    <li class="dropdown-header" style="font-size: 8px;">
                        Selamat datang, <?= e($nama_admin); ?>
                    </li>
                    <li><a class="dropdown-item" href="ganti_password.php"><i class="bi bi-key"></i> Ganti Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                </ul>
            </div>
        </div>
    </header>
