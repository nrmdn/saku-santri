</div>

<!-- JS Bawaan Bootstrap & Dashboard -->
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dashboard.js"></script>

<!-- jQuery & DataTables JS via jsDelivr -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- PANGGIL LIBRARY SWEETALERT2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SCRIPT INISIALISASI DATATABLES GLOBAL -->
<script>
$(document).ready(function() {
    var konfigurasiDataTables = {
        "lengthMenu": [ [10, 20, 40, 50, 100, -1], [10, 20, 40, 50, 100, "Semua"] ],
        "pageLength": 20,
        "language": {
            "sEmptyTable":   "Tidak ada data yang tersedia pada tabel ini",
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ baris data",
            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
            "sInfoFiltered": "(disaring dari _MAX_ data keseluruhan)",
            "sSearch":       "Cari Cepat:",
            "oPaginate": {
                "sFirst":    "Awal",
                "sPrevious": "Mundur",
                "sNext":     "Maju",
                "sLast":     "Akhir"
            }
        },
        // Memicu kemunculan tabel setelah DataTables selesai diproses
        "initComplete": function(settings, json) {
            $(this).css({
                'visibility': 'visible',
                'opacity': '1'
            }); 
        }
    };

    if ($('#tabelSantri').length > 0) {
        $('#tabelSantri').DataTable(konfigurasiDataTables);
    }
    
    if ($('#tabelPengguna').length > 0) {
        $('#tabelPengguna').DataTable(konfigurasiDataTables);
    }
});
</script>

<!-- SCRIPT PENGUBAH SESSION MENJADI SWEETALERT -->
<?php 
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

if ($alert_message !== ''): 
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
                timer: <?= ($icon === 'success') ? '3000' : 'null' ?>
            });
        });
    </script>
<?php endif; ?>

</body>
</html>