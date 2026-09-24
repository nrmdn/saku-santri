<?php
require 'config/koneksi.php';
require_once 'includes/security.php';
require_once 'includes/csrf.php';

// Ambil data notifikasi dari session flash
$topup_status  = $_SESSION['topup_status'] ?? null;
$topup_message = $_SESSION['topup_message'] ?? '';
$topup_data    = $_SESSION['topup_data'] ?? null;

// Hapus flash session agar tidak muncul terus
unset($_SESSION['topup_status'], $_SESSION['topup_message'], $_SESSION['topup_data']);

// Ambil NIS dari parameter URL jika ada (misal dari hasil cek saldo di beranda)
$prefill_nis = isset($_GET['nis']) ? htmlspecialchars(trim($_GET['nis'])) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Saldo Santri (Top Up) - Portal Wali Santri</title>
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

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 4px 20px rgba(15, 104, 72, 0.15);
            padding: 0.75rem 0;
        }
        .navbar-custom .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: clamp(1.1rem, 3vw, 1.25rem);
        }

        .page-header {
            background: linear-gradient(135deg, #094731 0%, #0f6848 100%);
            color: white;
            padding: clamp(35px, 6vw, 55px) 0 clamp(25px, 5vw, 45px);
            position: relative;
        }
        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(245, 158, 11, 0.18) 0%, transparent 50%);
            pointer-events: none;
        }

        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .bank-card {
            background: #ffffff;
            border: 1.5px dashed #cbd5e1;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
            transition: all 0.2s ease;
        }
        .bank-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(15, 104, 72, 0.08);
        }
        .bank-badge {
            font-weight: 800;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
        }
        .bank-bri { background: #00529c; color: white; }

        .rek-number {
            font-size: clamp(1.1rem, 3.5vw, 1.35rem);
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f172a;
        }

        .btn-copy {
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Responsive Presets: Grid on mobile, inline on desktop */
        .preset-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 8px;
        }
        .preset-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 10px;
            padding: 8px 10px;
            transition: all 0.2s ease;
            text-align: center;
            width: 100%;
        }
        .preset-btn:hover, .preset-btn.active {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        .card-topup {
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            padding: clamp(18px, 4vw, 36px);
        }

        .form-control, .form-select {
            font-size: 15px;
            padding: 10px 14px;
            border-radius: 10px;
        }

        .footer-simple {
            margin-top: auto;
            background: #092c1e;
            color: #94a3b8;
            padding: 25px 0;
            font-size: 0.88rem;
        }

        @media (max-width: 576px) {
            .preset-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .btn-copy {
                width: 100%;
                margin-top: 6px;
            }
            .bank-card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-dark navbar-custom sticky-top">
        <div class="container px-3 px-sm-4">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo_ponpes.png" alt="Logo" class="me-2" style="height: 40px;">
                <div>
                    <span class="d-block lh-1">Saku Santri</span>
                    <small class="text-white" style="font-size: 10px; font-weight: 400; letter-spacing: 0.5px; line-height: 1.1;">Ponpes Al Habibatain Bumiayu</small>
                </div>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3 py-1">
                    <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">Kembali ke </span>Beranda
                </a>
                <a href="cek_saldo.php" class="btn btn-warning btn-sm fw-bold rounded-pill px-3 py-1 text-dark">
                    <i class="fas fa-search me-1"></i> Cek Saldo
                </a>
            </div>
        </div>
    </nav>

    <!-- Header Halaman -->
    <div class="page-header">
        <div class="container px-3 px-sm-4">
            <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-pill small mb-2 border border-white border-opacity-20">
                <i class="fas fa-wallet text-warning"></i>
                <span class="small fw-semibold">Portal Khusus Orang Tua / Wali Santri</span>
            </div>
            <h1 class="mb-2">Isi Uang / Saldo Santri (Top Up)</h1>
            <p class="text-white-50 mb-0 small" style="max-width: 650px; line-height: 1.6;">
                Kirimkan uang saku untuk putra/putri Anda secara aman via transfer bank resmi pesantren. Konfirmasikan bukti transfer di bawah ini agar diverifikasi oleh admin.
            </p>
        </div>
    </div>

    <!-- Modal Konfirmasi Berhasil -->
    <?php 
    if ($topup_status === 'success' && $topup_data): 
        // Susun Data Pesan WA dari Session
        $wa_nis  = htmlspecialchars($topup_data['nis']);
        $wa_nom  = 'Rp ' . number_format($topup_data['nominal'], 0, ',', '.');
        
        // Template Pesan Sesuai Permintaan
        $pesan_wa = "Assalamualaikum, Saya Wali santri NIS: $wa_nis, telah melakukan top up sebesar $wa_nom, mohon ditindaklanjuti";
        $link_wa  = "https://wa.me/6285803932175?text=" . rawurlencode($pesan_wa);
    ?>
    <div class="modal fade show" id="modalTopupSukses" tabindex="-1" style="display: block; background: rgba(0,0,0,0.6);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto" style="max-width: 480px;">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-success text-white py-3 border-0">
                    <h5 class="modal-title fw-bold fs-6"><i class="fas fa-check-circle me-2"></i>Konfirmasi Berhasil Terkirim!</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('modalTopupSukses').style.display='none';"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-success">
                        <i class="fas fa-receipt fa-3x"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1 fs-5">Alhamdulillah!</h4>
                    <p class="text-muted small mb-3">
                        Bukti transaksi pengisian saldo sebesar <strong class="text-success fs-5 d-block mt-1"><?= $wa_nom; ?></strong>
                        telah kami terima dan masuk ke dalam antrean verifikasi pengurus pondok.
                    </p>

                    <div class="bg-light p-3 rounded-3 text-start small border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">NIS Santri:</span>
                            <span class="fw-bold"><?= htmlspecialchars($topup_data['nis']); ?></span>
                        </div>
                       
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Pengirim:</span>
                            <span class="fw-bold"><?= htmlspecialchars($topup_data['nama_wali']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Bank Tujuan:</span>
                            <span class="badge bg-primary"><?= htmlspecialchars($topup_data['bank']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Menunggu Verifikasi</span>
                        </div>
                    </div>

                    <div class="alert alert-info text-start small py-2 mb-3">
                        <i class="fas fa-info-circle me-1"></i> Admin akan memverifikasi struk Anda. Saldo akan otomatis bertambah setelah disetujui.
                    </div>


                    <div class="mt-4 pt-4 border-top text-center">
                        <span class="fw-bold d-block mb-2 text-dark">Langkah Terakhir!</span>
                        <small class="text-muted d-block mb-3">Silakan klik tombol di bawah untuk mengirim pesan otomatis ke Pengurus agar cepat diproses.</small>
                        <a href="<?= $link_wa; ?>" target="_blank" class="btn btn-success rounded-pill px-4 py-2 fw-bold w-100 shadow-sm" style="text-decoration: none;">
                            <i class="fab fa-whatsapp fs-5 me-2 align-middle"></i> Verifikasi via WhatsApp
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Konten Utama Halaman -->
    <div class="container py-4 py-md-5 px-3 px-sm-4">
        
        <!-- Notifikasi Error Jika Ada -->
        <?php if ($topup_status === 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Mohon Maaf!</strong> <?= htmlspecialchars($topup_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 align-items-stretch">
            
            <!-- Kolom Kiri: Rekening Resmi & Panduan -->
            <div class="col-lg-5">
                <div class="card card-topup h-100 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                        <div class="bg-success bg-opacity-10 text-success p-2 p-sm-3 rounded-4 fs-4">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark fs-6 fs-sm-5">Rekening Resmi Pondok</h5>
                            <small class="text-muted">Tujuan transfer resmi PP. Al Habibatain</small>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        Silakan transfer uang saku santri ke salah satu rekening resmi di bawah ini sebelum mengisi formulir konfirmasi:
                    </p>

                    <!-- Rekening 1: BRI -->
                    <div class="bank-card">
                        <div class="d-flex justify-content-between align-items-center mb-2 bank-card-header">
                            <span class="bank-badge bank-bri">BRI (Bank Rakyat Indonesia)</span>
                            <button type="button" class="btn btn-outline-secondary btn-copy" onclick="salinTeks('012401002345539', 'BRI')">
                                <i class="fas fa-copy me-1"></i> Salin
                            </button>
                        </div>
                        <div class="rek-number" id="rek-bri">012401002345539</div>
                        <small class="text-muted d-block mt-1" style="font-size: 12px;">a.n. <strong>Yayasan Ponpes Al Habibatain</strong></small>
                    </div>

                    <!-- Panduan -->
                    <div class="mt-auto pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-info-circle me-1 text-success"></i>Alur Konfirmasi Singkat:</h6>
                        <ol class="small text-muted ps-3 mb-0" style="line-height: 1.7; font-size: 13px;">
                            <li>Lakukan transfer via ATM / Mobile Banking.</li>
                            <li>Ambil foto atau screenshot bukti transfer Anda.</li>
                            <li>Isi form di samping dengan NIS santri yang benar.</li>
                            <li>Admin pondok akan memverifikasi dan saldo bertambah.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Formulir Konfirmasi Top Up -->
            <div class="col-lg-7">
                <div class="card card-topup h-100 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h4 class="fw-bold text-success mb-1 fs-5"><i class="fas fa-file-invoice-dollar me-2"></i>Formulir Bukti Top Up</h4>
                            <p class="text-muted small mb-0">Isi data di bawah ini untuk diverifikasi admin</p>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-3 small">
                            <i class="fas fa-shield-alt"></i> Aman
                        </span>
                    </div>

                    <form action="proses_topup_ortu.php" method="POST" enctype="multipart/form-data" id="formTopUp">
                        <?= csrf_field(); ?>
                        
                        <!-- 1. NIS Santri -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">
                                Nomor Induk Santri (NIS) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-id-card"></i></span>
                                <input type="number" oninput="javascript: if (this.value.length > 7) this.value = this.value.slice(0, 7);" inputmode="numeric" pattern="[0-9]{7}" class="form-control" name="nis" id="topup_nis" required value="<?= htmlspecialchars($prefill_nis); ?>" placeholder="Masukkan NIS Santri">
                            </div>
                            <div class="form-text small text-muted" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1"></i> Tidak tahu NIS? 
                                <a href="https://wa.me/6285803932175?text=Assalamualaikum,%20Admin.%20Mohon%20informasi%20NIS%20untuk%20anak%20saya%20yang%20bernama...%20kelas..." target="_blank" class="text-success fw-bold text-decoration-none">
                                    Hubungi Pengurus via WA
                                </a>
                            </div>
                        </div>

                        <!-- 2. Data Pengirim (Wali Santri) -->
                        <div class="row g-2 g-sm-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small text-secondary">Nama Pengirim<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="nama_wali" id="topup_nama_wali" required>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small text-secondary">No. WhatsApp / HP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fab fa-whatsapp"></i></span>
                                    <input type="number" oninput="javascript: if (this.value.length > 13) this.value = this.value.slice(0, 13);" inputmode="numeric" pattern="[0-9]{13}" class="form-control" name="no_hp" id="topup_no_hp" required>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Rekening Tujuan Bank -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Ditransfer ke Rekening Pondok <span class="text-danger">*</span></label>
                            <select class="form-select bg-light" name="bank_tujuan" id="topup_bank_tujuan" required>
                                <option value="" selected disabled>-- Pilih Rekening Tujuan Transfer --</option>
                                <option value="BRI - 012401002345539">BRI - 012401002345539 (a.n. Yayasan Ponpes Al Habibatain)</option>
                            </select>
                        </div>

                        <!-- 4. Nominal Transfer dengan Tombol Cepat -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Nominal Transfer (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light fw-bold text-success">Rp</span>
                                <input type="number" class="form-control fs-5 fw-bold" name="nominal" id="topup_nominal" min="10000" required>
                            </div>
                            <div class="preset-container">
                                <button type="button" class="preset-btn" onclick="pilihNominal(50000)">Rp 50.000</button>
                                <button type="button" class="preset-btn" onclick="pilihNominal(100000)">Rp 100.000</button>
                                <button type="button" class="preset-btn" onclick="pilihNominal(200000)">Rp 200.000</button>
                                <button type="button" class="preset-btn" onclick="pilihNominal(500000)">Rp 500.000</button>
                                <button type="button" class="preset-btn" onclick="pilihNominal(1000000)">Rp 1.000.000</button>
                            </div>
                        </div>

                        <!-- 5. Unggah Bukti Transfer -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">
                                Upload Foto Bukti Transfer (Struk / Screenshot) <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" type="file" name="bukti_transfer" id="topup_file" accept="image/jpeg,image/png,image/jpg,image/webp" required onchange="previewGambar(this)">
                            <div class="form-text small text-muted" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1"></i> Format: JPG, JPEG, PNG, WEBP. Maksimal 5 MB.
                            </div>

                            <!-- Image Preview Container -->
                            <div id="previewContainer" class="mt-2 text-center p-2 border rounded-3 bg-light" style="display: none;">
                                <small class="text-muted d-block mb-1" style="font-size: 12px;">Pratinjau Foto Bukti:</small>
                                <img id="previewImage" src="" alt="Pratinjau Struk" class="img-fluid rounded shadow-sm" style="max-height: 200px; object-fit: contain;">
                            </div>
                        </div>

                        <!-- 6. Catatan Tambahan (Opsional) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Pesan / Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Contoh: Titipan uang saku anak bulan ini"></textarea>
                        </div>

                        <!-- Tombol Submit Form -->
                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Konfirmasi Bukti Top Up
                        </button>
                        <small class="text-center d-block text-muted mt-2" style="font-size: 12px;">
                            <i class="fas fa-lock me-1"></i> Bukti transaksi Anda akan langsung masuk ke panel Administrator untuk diverifikasi.
                        </small>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-simple text-center">
        <div class="container px-3 px-sm-4">
            <p class="mb-1 text-white">&copy; <?= date('Y'); ?>  Saku Santri - Pondok Pesantren Al Habibatain Bumiayu.</p>
            <small class="text-white-50"><a href="index.php" class="text-warning text-decoration-none">Kembali ke Halaman Beranda</a> | <a href="index.php#cek-saldo" class="text-white-50 text-decoration-none">Cek Saldo Santri</a></small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fungsi Salin Rekening
        function salinTeks(nomor, bank) {
            navigator.clipboard.writeText(nomor).then(function() {
                alert('Nomor Rekening ' + bank + ' (' + nomor + ') berhasil disalin ke clipboard!');
            }, function() {
                var temp = document.createElement("input");
                document.body.appendChild(temp);
                temp.value = nomor;
                temp.select();
                document.execCommand("copy");
                document.body.removeChild(temp);
                alert('Nomor Rekening ' + bank + ' (' + nomor + ') berhasil disalin!');
            });
        }

        // Fungsi Pilih Nominal Cepat
        function pilihNominal(nilai) {
            document.getElementById('topup_nominal').value = nilai;
            const buttons = document.querySelectorAll('.preset-btn');
            buttons.forEach(btn => {
                if (parseInt(btn.textContent.replace(/[^0-9]/g, '')) === nilai) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        // Pratinjau Gambar Bukti Struk
        function previewGambar(input) {
            const container = document.getElementById('previewContainer');
            const preview = document.getElementById('previewImage');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                container.style.display = 'none';
            }
        }
    </script>
</body>
</html>