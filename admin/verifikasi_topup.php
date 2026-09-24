<?php 
require_once __DIR__ . '/../includes/auth.php';
require_permission('verifikasi_topup');
// Panggil koneksi database
require '../config/koneksi.php';

// Panggil Header & Sidebar layout
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

// ==========================================
// TANGKAP PARAMETER FILTER & TAB AKTIF
// ==========================================
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$tab        = isset($_GET['tab']) ? $_GET['tab'] : '';

// Jika sedang melakukan pencarian (filter) atau berada di parameter riwayat, pastikan tab riwayat yang aktif
$active_tab = (!empty($start_date) || !empty($end_date) || $tab === 'riwayat') ? 'riwayat' : 'pending';
?>

<!-- KONTEN UTAMA DIMULAI -->
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-patch-check-fill text-success me-2"></i>Verifikasi Top Up Santri</h2>
            <p class="text-muted small mb-0">Tinjau dan verifikasi bukti transfer pengisian saldo yang diajukan oleh wali santri.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
    
    <!-- Tab Navigasi Antrean vs Riwayat -->
    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($active_tab === 'pending') ? 'active fw-bold' : ''; ?> rounded-pill px-4" 
                    id="pills-pending-tab" data-bs-toggle="pill" data-bs-target="#pills-pending" type="button" role="tab">
                <i class="fas fa-clock me-2"></i>Menunggu Verifikasi
                <?php if ($total_pending_topup > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-2"><?= $total_pending_topup; ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link <?= ($active_tab === 'riwayat') ? 'active fw-bold' : ''; ?> rounded-pill px-4" 
                    id="pills-riwayat-tab" data-bs-toggle="pill" data-bs-target="#pills-riwayat" type="button" role="tab">
                <i class="fas fa-history me-2"></i>Riwayat Terverifikasi
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        
        <!-- ========================================== -->
        <!-- TAB 1: PENDING VERIFIKASI                  -->
        <!-- ========================================== -->
        <div class="tab-pane fade <?= ($active_tab === 'pending') ? 'show active' : ''; ?>" id="pills-pending" role="tabpanel">
            
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                <i class="fas fa-exclamation-circle fs-4 me-3 text-warning"></i>
                <div class="small">
                    Pastikan Anda memeriksa keaslian foto struk bukti transfer dan mencocokkannya dengan mutasi rekening bank pondok sebelum menekan tombol <strong>Setujui</strong>. Sistem akan otomatis melacak nomor WA dari teks keterangan untuk mengirimkan notifikasi.
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-inbox me-2 text-primary"></i>Antrean Top Up Masuk</h5>
                    <span class="badge bg-light text-muted border"><?= $total_pending_topup; ?> Permintaan</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="14%">Waktu Pengajuan</th>
                                    <th width="20%">Santri</th>
                                    <th width="15%">Nominal Transfer</th>
                                    <th width="24%">Keterangan & Pengirim</th>
                                    <th width="10%" class="text-center">Bukti Struk</th>
                                    <th width="13%" class="text-center">Aksi Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT transaksi.*, santri.nama, santri.nis, santri.foto, santri.no_hp_ortu 
                                          FROM transaksi 
                                          JOIN santri ON transaksi.santri_id = santri.id 
                                          WHERE transaksi.status = 'pending' AND transaksi.jenis_transaksi = 'masuk'
                                          ORDER BY transaksi.tanggal ASC";
                                          
                                $hasil = mysqli_query($koneksi, $query);
                                $no = 1;

                                if($hasil && mysqli_num_rows($hasil) > 0) {
                                    while($row = mysqli_fetch_assoc($hasil)) {
                                        $foto_santri = !empty($row['foto']) ? $row['foto'] : 'default.png';
                                        $file_struk  = $row['bukti_transfer'];
                                        $struk_path  = "../assets/img/struk/" . $file_struk;
                                        $ada_struk   = (!empty($file_struk) && $file_struk != '-' && file_exists(__DIR__ . '/' . $struk_path));
                                        
                                        // LOGIKA PENCARIAN NOMOR WA DARI KOLOM KETERANGAN
                                        $keterangan_text = $row['keterangan'];
                                        $no_wa_target = '';
                                        
                                        if (preg_match('/(08[0-9]{8,12}|628[0-9]{8,12})/', $keterangan_text, $matches)) {
                                            $no_wa_target = $matches[1]; 
                                        } else {
                                            $no_wa_target = $row['no_hp_ortu'];
                                        }

                                        $js_nohp = htmlspecialchars($no_wa_target ?? '');
                                        $js_nama = htmlspecialchars(addslashes($row['nama']));
                                        $js_nom  = number_format($row['nominal'], 0, ',', '.');
                                        $js_id   = $row['id'];
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    
                                    <td>
                                        <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($row['tanggal'])); ?></div>
                                        <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('H:i', strtotime($row['tanggal'])); ?> WIB</small>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="../assets/images/<?= htmlspecialchars($foto_santri); ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;" onerror="this.src='../assets/images/default.png'">
                                            <div>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($row['nama']); ?></strong>
                                                <span class="badge bg-light text-dark border">NIS: <?= htmlspecialchars($row['nis']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-bold text-success fs-6">
                                            Rp <?= number_format($row['nominal'], 0, ',', '.'); ?>
                                        </div>
                                        <span class="badge bg-warning bg-opacity-25 text-warning-emphasis fw-semibold" style="font-size: 11px;">
                                            <i class="fas fa-hourglass-half me-1"></i>Pending
                                        </span>
                                    </td>
                                    
                                    <td class="small">
                                        <div class="text-dark fw-medium"><?= htmlspecialchars($row['keterangan']); ?></div>
                                        <?php if(!empty($no_wa_target)): ?>
                                            <span class="badge bg-success-subtle text-success border mt-1"><i class="fab fa-whatsapp"></i> Terdeteksi: <?= $no_wa_target; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if ($ada_struk): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" 
                                                    onclick="bukaModalStruk('<?= $struk_path; ?>', '<?= $js_nama; ?>', '<?= $row['nis']; ?>', '<?= $js_nom; ?>', '<?= $js_id; ?>', '<?= $js_nohp; ?>')">
                                                <i class="fas fa-receipt me-1"></i> Lihat
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Tidak ada file</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <form method="POST" action="proses_verifikasi.php" class="d-inline" onsubmit="return kirimWaDanSubmit(this, '<?= $js_nohp; ?>', '<?= $js_nama; ?>', '<?= $js_nom; ?>', 'setuju');">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                                                <input type="hidden" name="aksi" value="setuju">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                                    <i class="fas fa-check me-1"></i> Setujui
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="proses_verifikasi.php" class="d-inline" onsubmit="return kirimWaDanSubmit(this, '<?= $js_nohp; ?>', '<?= $js_nama; ?>', '<?= $js_nom; ?>', 'tolak');">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                                                <input type="hidden" name="aksi" value="tolak">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 shadow-sm" title="Tolak Transaksi">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    } 
                                } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-check-circle fs-1 text-success opacity-50 mb-3 d-block"></i>
                                        <h5 class="fw-bold text-dark">Tidak Ada Antrean Verifikasi</h5>
                                        <p class="small text-muted mb-0">Semua pengajuan top up dari orang tua santri telah selesai diverifikasi.</p>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: RIWAYAT SUDAH DIVERIFIKASI          -->
        <!-- ========================================== -->
        <div class="tab-pane fade <?= ($active_tab === 'riwayat') ? 'show active' : ''; ?>" id="pills-riwayat" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                
                <!-- FILTER WAKTU DI HEADER TABEL -->
                <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history me-2 text-secondary"></i>Riwayat Verifikasi</h5>
                        <span class="badge bg-light text-muted border mt-1">
                            <?= (!empty($start_date) || !empty($end_date)) ? 'Menampilkan hasil filter' : 'Menampilkan 30 Transaksi Terakhir'; ?>
                        </span>
                    </div>
                    
                    <!-- Form Filter Tanggal -->
                    <form method="GET" action="verifikasi_topup.php" class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="hidden" name="tab" value="riwayat">
                        
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar-day"></i></span>
                            <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($start_date); ?>" required>
                        </div>
                        <span class="text-muted small">s/d</span>
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar-day"></i></span>
                            <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($end_date); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-sm rounded-2 shadow-sm px-3"><i class="fas fa-search me-1"></i>Filter</button>
                        
                        <?php if(!empty($start_date) || !empty($end_date)): ?>
                            <a href="verifikasi_topup.php?tab=riwayat" class="btn btn-outline-danger btn-sm rounded-2 shadow-sm px-2" title="Reset Pencarian">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="15%">Waktu</th>
                                    <th width="20%">Santri</th>
                                    <th width="15%">Nominal</th>
                                    <th width="26%">Keterangan</th>
                                    <th width="10%" class="text-center">Bukti</th>
                                    <th width="10%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // SUSUN QUERY UNTUK TAB RIWAYAT BERDASARKAN FILTER TANGGAL
                                $where_clauses = ["transaksi.jenis_transaksi = 'masuk'", "transaksi.status IN ('sukses', 'gagal')"];
                                
                                if (!empty($start_date)) {$where_clauses[] = "DATE(transaksi.tanggal) >= '" . mysqli_real_escape_string($koneksi,$start_date) . "'";
                                }
                                if (!empty($end_date)) {$where_clauses[] = "DATE(transaksi.tanggal) <= '" . mysqli_real_escape_string($koneksi,$end_date) . "'";
                                }

                                $where_sql = implode(" AND ", $where_clauses);
                                
                                // Jika ada filter, hilangkan batas LIMIT agar semua data pada tanggal tersebut tampil.
                                // Jika tidak ada filter, batasi hanya 30 transaksi terakhir.
                                $limit_sql = (!empty($start_date) || !empty($end_date)) ? "" : "LIMIT 30";

                                $query_history = "SELECT transaksi.*, santri.nama, santri.nis 
                                                  FROM transaksi 
                                                  JOIN santri ON transaksi.santri_id = santri.id 
                                                  WHERE $where_sql
                                                  ORDER BY transaksi.tanggal DESC 
                                                  $limit_sql";
                                                  
                                $res_history = mysqli_query($koneksi, $query_history);$no_h = 1;

                                if($res_history && mysqli_num_rows($res_history) > 0) {
                                    while($h = mysqli_fetch_assoc($res_history)) {
                                        $is_sukses = ($h['status'] === 'sukses');
                                        $file_h =$h['bukti_transfer'];
                                        $struk_h_path = "../assets/img/struk/" . $file_h;
                                        $ada_struk_h = (!empty($file_h) && $file_h != '-' && file_exists(__DIR__ . '/' .$struk_h_path));
                                ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no_h++; ?></td>
                                    <td>
                                        <div><?= date('d/m/Y', strtotime($h['tanggal'])); ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($h['tanggal'])); ?> WIB</small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($h['nama']); ?></strong><br>
                                        <small class="text-muted">NIS: <?= htmlspecialchars($h['nis']); ?></small>
                                    </td>
                                    <td class="fw-bold <?= $is_sukses ? 'text-success' : 'text-danger'; ?>">
                                        Rp <?= number_format($h['nominal'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($h['keterangan']); ?></td>
                                    <td class="text-center">
                                        <?php if ($ada_struk_h): ?>
                                            <a href="<?= $struk_h_path; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 11px;">
                                                <i class="fas fa-image me-1"></i> Struk
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($is_sukses): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-check-circle me-1"></i>Sukses
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-times-circle me-1"></i>Ditolak
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                    echo "<tr>
                                            <td colspan='7' class='text-center text-muted py-5'>
                                                <i class=".'"'."fas fa-folder-open fs-2 text-muted opacity-50 mb-3 d-block".'"'."></i>
                                                Data riwayat transaksi tidak ditemukan pada rentang tanggal tersebut.
                                            </td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- KONTEN UTAMA SELESAI -->

<!-- Modal Pratinjau Struk Transfer -->
<div class="modal fade" id="modalStrukPreview" tabindex="-1" aria-labelledby="modalStrukLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modalStrukLabel">
                    <i class="fas fa-receipt me-2 text-warning"></i>Bukti Transfer Wali Santri
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="row g-3 mb-3 text-start small bg-light p-3 rounded-3 border">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nama Santri:</span>
                        <strong class="fs-6 text-dark" id="modalSantriNama">-</strong>
                        <span class="text-muted d-block mt-1" id="modalSantriNis">NIS: -</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nominal Transfer:</span>
                        <strong class="fs-5 text-success" id="modalNominal">Rp 0</strong>
                        <span class="text-muted d-block mt-1" id="modalInfoWa"></span>
                    </div>
                </div>

                <div class="p-2 border rounded-3 bg-white text-center shadow-sm">
                    <img id="imgPreviewStruk" src="" alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 480px; object-fit: contain;">
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <a id="linkBukaTab" href="#" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Buka Ukuran Penuh
                </a>
                <div class="d-flex gap-2">
                    <form id="formModalTolak" method="POST" action="proses_verifikasi.php" class="d-inline">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="aksi" value="tolak">
                        <input type="hidden" name="id" value="">
                        <button type="submit" class="btn btn-danger btn-sm px-3">
                            <i class="fas fa-times me-1"></i> Tolak
                        </button>
                    </form>
                    <form id="formModalSetuju" method="POST" action="proses_verifikasi.php" class="d-inline">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="aksi" value="setuju">
                        <input type="hidden" name="id" value="">
                        <button type="submit" class="btn btn-success btn-sm px-3">
                            <i class="fas fa-check me-1"></i> Setujui & Tambah Saldo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk memproses Setuju/Tolak sekaligus membuka tab WhatsApp
    function kirimWaDanSubmit(form, noHp, namaSantri, nominal, aksi) {
        let pesanConfirm = "";
        let pesanWa = "";
        let hasWa = (noHp && noHp.trim() !== '' && noHp.trim() !== '-');

        if (aksi === 'setuju') {
            pesanConfirm = `Apakah Anda yakin ingin menyetujui Top Up ini?\n\nSantri: ${namaSantri}\nNominal: Rp ${nominal}`;
            if (hasWa) pesanConfirm += `\n\nSistem akan otomatis membuka WA pengirim (${noHp}) untuk mengirim notifikasi SUKSES.`;
            
            pesanWa = `Assalamualaikum, Bapak/Ibu.\n\nAlhamdulillah, kami menginformasikan bahwa pengajuan Top Up saldo untuk ananda *${namaSantri}* sebesar *Rp ${nominal}* telah *BERHASIL* diverifikasi.\n\nSaldo sudah ditambahkan ke akun santri. Terima kasih.`;
        } else {
            pesanConfirm = `Yakin ingin menolak transaksi ini?\n\nSantri: ${namaSantri}\nNominal: Rp ${nominal}`;
            if (hasWa) pesanConfirm += `\n\nSistem akan otomatis membuka WA pengirim (${noHp}) untuk mengirim notifikasi PENOLAKAN.`;
            
            pesanWa = `Assalamualaikum, Bapak/Ibu.\n\nMohon maaf, pengajuan Top Up saldo untuk ananda *${namaSantri}* sebesar *Rp ${nominal}* *KAMI TOLAK*.\n\nAlasan: Bukti transfer tidak valid atau dana belum masuk ke rekening pesantren.\n\nSilakan periksa kembali atau hubungi pengurus untuk informasi lebih lanjut.`;
        }

        if (confirm(pesanConfirm)) {
            if (hasWa) {
                let noHpFormat = noHp.replace(/\D/g, ''); 
                if (noHpFormat.startsWith('0')) {
                    noHpFormat = '62' + noHpFormat.substring(1);
                }
                
                let waLink = `https://wa.me/${noHpFormat}?text=${encodeURIComponent(pesanWa)}`;
                window.open(waLink, '_blank');
            }
            return true; 
        }
        
        return false; 
    }

    // Fungsi untuk membuka detail struk gambar di Modal
    function bukaModalStruk(urlGambar, namaSantri, nis, nominal, idTrx, noHp) {
        document.getElementById('imgPreviewStruk').src = urlGambar;
        document.getElementById('modalSantriNama').textContent = namaSantri;
        document.getElementById('modalSantriNis').textContent = 'NIS: ' + nis;
        document.getElementById('modalNominal').textContent = 'Rp ' + nominal;
        
        if (noHp) {
            document.getElementById('modalInfoWa').innerHTML = `<i class="fab fa-whatsapp text-success me-1"></i> WA Pengirim: ${noHp}`;
        } else {
            document.getElementById('modalInfoWa').innerHTML = "";
        }

        document.getElementById('linkBukaTab').href = urlGambar;

        document.querySelector('#formModalSetuju input[name="id"]').value = idTrx;
        document.querySelector('#formModalTolak input[name="id"]').value = idTrx;

        document.getElementById('formModalSetuju').onsubmit = function() {
            return kirimWaDanSubmit(this, noHp, namaSantri, nominal, 'setuju');
        };
        document.getElementById('formModalTolak').onsubmit = function() {
            return kirimWaDanSubmit(this, noHp, namaSantri, nominal, 'tolak');
        };

        var myModal = new bootstrap.Modal(document.getElementById('modalStrukPreview'));
        myModal.show();
    }
</script>

<?php 
// Panggil Footer
include 'layout/footer.php'; 
?>