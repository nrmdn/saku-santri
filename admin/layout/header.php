<?php
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/db.php';

// Memastikan admin telah login & sesi masih valid
require_login();

$nama_admin     = e($_SESSION['nama_lengkap'] ?? 'Administrator');
$username_admin = e($_SESSION['username'] ?? 'admin');
$role_admin     = e($_SESSION['role'] ?? 'admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Saku Santri</title>

    <link rel="stylesheet" href="../assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/libs/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/spark-compat.css">
    
    <!-- DataTables Bootstrap 5 CSS via jsDelivr (DIIZINKAN OLEH CSP ANDA) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Ikon FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- CUSTOM CSS UNTUK MERAPIKAN DATATABLES -->
    <style>
        /* 1. Jarak Form Pencarian & Solusi Ujung Terpotong */
        div.dataTables_wrapper div.dataTables_length {
            margin-bottom: 12px;
            padding-top: 5px;
        }
        div.dataTables_wrapper div.dataTables_filter {
            margin-bottom: 12px;
            padding-top: 5px;
            padding-right: 10px; /* INI KUNCINYA: Memberi ruang agar shadow hijau tidak terpotong */
        }

        div.dataTables_wrapper div.dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 5px 10px;
            margin-left: 8px;
        }
        div.dataTables_wrapper div.dataTables_filter input:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); /* Efek hijau khas Saku Santri */
            outline: 0;
        }

        /* 2. MENGHILANGKAN GARIS VERTIKAL (Batas Kolom) SEPENUHNYA */
        table.dataTable thead th, 
        table.dataTable tbody td {
            border-left: none !important;  
            border-right: none !important; 
        }

        /* Menata ulang header tabel */
        table.dataTable thead th {
            background-color: #212529 !important;
            color: #ffffff !important;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            border-bottom: none !important;
            padding: 12px 10px !important; 
        }

        /* Ikon sortir warna putih */
        table.dataTable thead th.sorting:after, table.dataTable thead th.sorting_asc:after, table.dataTable thead th.sorting_desc:after,
        table.dataTable thead th.sorting:before, table.dataTable thead th.sorting_asc:before, table.dataTable thead th.sorting_desc:before {
            color: #ffffff !important;
            opacity: 0.6;
        }
        
        /* 3. Merapikan isi tabel */
        table.dataTable tbody td {
            vertical-align: middle;
            padding: 10px 8px !important;
            font-size: 13px;
            border-top: 1px solid #e9ecef !important;
            border-bottom: 1px solid #e9ecef !important;
        }

        /* 4. SOLUSI TOMBOL SUMBING / NABRAK GARIS KANAN */
        table.dataTable thead th:last-child, 
        table.dataTable tbody td:last-child {
            min-width: 195px !important; 
            padding-right: 30px !important; 
            white-space: nowrap !important;
        }
        
        table.dataTable thead th:first-child, 
        table.dataTable tbody td:first-child {
            padding-left: 20px !important; 
        }

        /* Merapikan 5 tombol Aksi */
        .table td .d-flex.gap-1 {
            gap: 6px !important;
            flex-wrap: nowrap !important;
            justify-content: center;
        }
        
        .table td .d-flex.gap-1 .btn {
            padding: 5px 8px !important;
            font-size: 12px !important;
        }

        /* 5. Scroll Horizontal jika layar kurang lebar */
        .table-responsive {
            overflow-x: auto !important;
            width: 100%;
            border: none !important; 
        }

        /* 6. Pagination */
        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate {
            margin-top: 15px;
            font-size: 13px;
        }
        .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
            color: #ffffff !important;
        }

        /* 7. Mencegah Kedip */
        #tabelSantri, #tabelPengguna {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease-in-out;
            width: 100% !important;
        }
    </style>
</head>
<body>
