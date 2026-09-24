<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

secure_logout($koneksi);

header("Location: login.php?pesan=logout");
exit;