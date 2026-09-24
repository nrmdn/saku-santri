
</div>

<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dashboard.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- PANGGIL LIBRARY SWEETALERT2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SCRIPT PENGUBAH SESSION MENJADI SWEETALERT -->
<?php 
// Kumpulkan semua jenis session notifikasi yang Anda gunakan
$alert_status = '';
$alert_message = '';

if (isset($_SESSION['santri_message'])) {
    $alert_status = $_SESSION['santri_status'];
    $alert_message = $_SESSION['santri_message'];
    unset($_SESSION['santri_message'], $_SESSION['santri_status']);
} elseif (isset($_SESSION['pengguna_message'])) {
    $alert_status = $_SESSION['pengguna_status'];
    $alert_message = $_SESSION['pengguna_message'];
    unset($_SESSION['pengguna_message'], $_SESSION['pengguna_status']);
} elseif (isset($_SESSION['detail_message'])) {
    $alert_status = $_SESSION['detail_status'];
    $alert_message = $_SESSION['detail_message'];
    unset($_SESSION['detail_message'], $_SESSION['detail_status']);
}

// Jika ada pesan, jalankan SweetAlert
if ($alert_message !== ''): 
    // Ubah status bawaan bootstrap (danger/warning) menjadi format SweetAlert (error/warning)
    $icon = $alert_status;
    if ($icon === 'danger') $icon = 'error';
    
    $title = ($icon === 'success') ? 'Berhasil!' : (($icon === 'error') ? 'Gagal!' : 'Perhatian!');
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: '<?= $icon ?>',
                title: '<?= $title ?>',
                text: '<?= $alert_message ?>',
                showConfirmButton: true,
                confirmButtonColor: '#0d6efd',
                timer: <?= ($icon === 'success') ? '3000' : 'null' ?> // Sukses hilang dalam 3 detik, Error harus di-klik OK
            });
        });
    </script>
<?php endif; ?>

</body>
</html>
