
<?php
session_start();

// Check if user is admin, redirect if not
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include database configuration
include '../config.php';

// Get dashboard statistics
$stats = [];

// Count branches
$result = $conn->query("SELECT COUNT(*) as count FROM cabang");
$stats['cabang'] = $result->fetch_assoc()['count'];

// Count users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $result->fetch_assoc()['count'];

// Count items
$result = $conn->query("SELECT COUNT(*) as count FROM barang");
$stats['barang'] = $result->fetch_assoc()['count'];

// Count customers
$result = $conn->query("SELECT COUNT(*) as count FROM pelanggan");
$stats['pelanggan'] = $result->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SARKEM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .main-content {
            padding-top: 80px;
        }
        .topbar {
            z-index: 1030;
        }
        .stat-icon {
            font-size: 48px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Fixed Topbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top topbar">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <span class="navbar-brand mb-0 h1">SARKEM</span>
            </div>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Admin: <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></span>
                <a href="../logout.php" class="text-white text-decoration-none">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="data_cabang.php">
                                <i class="bi bi-building me-2"></i>
                                Data Cabang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="data_karyawan.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Data Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="data_absensi.php">
                                <i class="bi bi-calendar-check me-2"></i>
                                Data Absensi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="data_barang.php">
                                <i class="bi bi-box-seam me-2"></i>
                                Data Barang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rekap_kasbon_teknisi.php">
                                <i class="bi bi-cash-coin me-2"></i>
                                Rekap Kasbon Teknisi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="slip_gaji_teknisi.php">
                                <i class="bi bi-receipt me-2"></i>
                                Slip Gaji Teknisi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tagihan_pelanggan.php">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Tagihan Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pelanggan.php">
                                <i class="bi bi-people me-2"></i>
                                Data Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rekap_laporan_perbaikan.php">
                                <i class="bi bi-clipboard-data me-2"></i>
                                Rekap Laporan Perbaikan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear me-2"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid pt-5">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">Dashboard</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <!-- Total Cabang -->
                        <div class="col-12 col-sm-6 col-lg-3 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-building stat-icon text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="stat-number"><?php echo $stats['cabang']; ?></div>
                                        <div class="text-muted">Total Cabang</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Karyawan -->
                        <div class="col-12 col-sm-6 col-lg-3 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-person-badge stat-icon text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="stat-number"><?php echo $stats['users']; ?></div>
                                        <div class="text-muted">Total Karyawan</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Barang -->
                        <div class="col-12 col-sm-6 col-lg-3 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-box-seam stat-icon text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="stat-number"><?php echo $stats['barang']; ?></div>
                                        <div class="text-muted">Total Barang</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Pelanggan -->
                        <div class="col-12 col-sm-6 col-lg-3 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-people stat-icon text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="stat-number"><?php echo $stats['pelanggan']; ?></div>
                                        <div class="text-muted">Total Pelanggan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Message -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Selamat Datang di SARKEM Admin Panel
                                    </h5>
                                    <p class="card-text">
                                        Sistem Administrasi dan Rekap Keuangan Elektronik Manajemen (SARKEM) membantu Anda mengelola data cabang, karyawan, barang, dan pelanggan dengan mudah. 
                                        Gunakan menu di sebelah kiri untuk mengakses berbagai fitur yang tersedia.
                                    </p>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Fitur Utama:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Manajemen Data Cabang</li>
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Manajemen Karyawan & Absensi</li>
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Inventori Barang</li>
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Data Pelanggan</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Laporan & Rekap:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Rekap Kasbon Teknisi</li>
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Slip Gaji Teknisi</li>
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Tagihan Pelanggan</li>
                                                <li><i class="bi bi-check-circle text-success me-2"></i>Rekap Laporan Perbaikan</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c49aae23e4e9cc',t:'MTc1NDcxNDcyOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
