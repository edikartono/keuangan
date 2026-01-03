<?php
// File: includes/header.php
require_once __DIR__ . '/../config/database.php';

$base_path = BASE_PATH;

// Mendapatkan nama halaman saat ini untuk menandai link aktif di sidebar.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    

    
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            /* Memberi padding atas pada body untuk mencegah konten tertimpa header fixed */
            padding-top: 72px; 
        }
        /* Custom Header Style */
        #main-header {
            background: linear-gradient(90deg, #16A085 0%, #F4D03F 100%);
            min-height: 72px; 
        }
        #app-title {
            font-weight: 700;
            font-size: 1.3rem;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        /* Sidebar positioning and styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            z-index: 100;
            padding: 72px 0 0; 
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            overflow-y: auto; /* Memastikan sidebar bisa di-scroll jika isinya panjang */
        }
        .sidebar-nav .nav-link {
            color: #333;
            font-weight: 500;
            padding: .75rem 1rem;
        }
        .sidebar-nav .nav-link.active {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }
        .main-content {
            margin-left: 250px;
        }

        /* Responsive styles for mobile */
        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                top: 72px;
                height: calc(100% - 72px);
                padding-top: .5rem;
            }
            .navbar-brand {
                width: auto !important;
                flex-grow: 1; 
            }
        }
    </style>
</head>
<body>
    <!-- Header dengan posisi fixed-top -->
    <header class="navbar navbar-dark fixed-top flex-md-nowrap p-0 shadow" id="main-header">
    
        <button class="navbar-toggler d-md-none collapsed border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Brand/Judul aplikasi -->
        <a class="navbar-brand col-md-3 col-lg-2 me-auto ps-3" href="<?php echo $base_path; ?>index.php" id="app-title">KeuanganKu</a>
        
        <!-- Dropdown Pengaturan Pengguna -->
        <div class="dropdown">
            <a href="#" class="d-block link-light text-decoration-none dropdown-toggle px-3" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark text-small shadow">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pengaturanAkunModal">
                        <i class="bi bi-gear-fill me-2"></i>Pengaturan Akun
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?php echo $base_path; ?>auth/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column sidebar-nav">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : '' ?>" href="<?php echo $base_path; ?>index.php">
                                <i class="bi bi-house-door-fill me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'pendapatan.php') ? 'active' : '' ?>" href="<?php echo $base_path; ?>pendapatan.php">
                                <i class="bi bi-arrow-down-circle-fill me-2"></i> Pendapatan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'pengeluaran.php') ? 'active' : '' ?>" href="<?php echo $base_path; ?>pengeluaran.php">
                                <i class="bi bi-arrow-up-circle-fill me-2"></i> Pengeluaran
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'sumber_dana.php') ? 'active' : '' ?>" href="<?php echo $base_path; ?>sumber_dana.php">
                                <i class="bi bi-arrow-up-circle-fill me-2"></i> Mutasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'kategori.php') ? 'active' : '' ?>" href="<?php echo $base_path; ?>kategori.php">
                                <i class="bi bi-tags-fill me-2"></i> Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'laporan.php') ? 'active' : '' ?>" href="<?php echo $base_path; ?>laporan.php">
                                <i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Laporan
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
