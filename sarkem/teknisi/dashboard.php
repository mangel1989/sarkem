
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teknisi') {
    header("Location: ../index.php");
    exit;
}
include '../config.php';
$teknisi = $_SESSION['user'];

// Query statistik teknisi
$stats = [];

// Total absen hari ini
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi WHERE id_teknisi = ? AND tanggal = CURDATE()");
$stmt->bind_param("i", $teknisi['id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['absen_hari_ini'] = $result->fetch_assoc()['total'];

// Jadwal perbaikan hari ini
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM jadwal_perbaikan WHERE id_teknisi = ? AND tgl_perbaikan = CURDATE()");
$stmt->bind_param("i", $teknisi['id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['jadwal_hari_ini'] = $result->fetch_assoc()['total'];

// Perbaikan selesai
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM jadwal_perbaikan WHERE id_teknisi = ? AND status = 'selesai'");
$stmt->bind_param("i", $teknisi['id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['perbaikan_selesai'] = $result->fetch_assoc()['total'];
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Teknisi - SARKEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="navbar-toggler d-lg-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <span class="text-white me-3"><?php echo htmlspecialchars($teknisi['nama']); ?></span>
                
                <a class="navbar-brand fw-bold" href="#">SARKEM</a>
            </div>
            
            <a href="../logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" style="width: 280px; margin-top: 56px;">
        <div class="offcanvas-header d-lg-none">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body bg-light">
            <nav class="nav flex-column">
                <a class="nav-link active bg-primary text-white rounded mb-1" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link text-dark mb-1" href="absen-harian.php">
                    <i class="bi bi-calendar-check me-2"></i>Absen Harian
                </a>
                <a class="nav-link text-dark mb-1" href="jadwal-perbaikan.php">
                    <i class="bi bi-calendar-event me-2"></i>Jadwal Perbaikan
                </a>
                <a class="nav-link text-dark mb-1" href="laporan-perbaikan.php">
                    <i class="bi bi-file-earmark-text me-2"></i>Laporan Perbaikan
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="container-fluid pt-5" style="margin-left: 0;">
        <div class="row">
            <div class="col-lg-10 offset-lg-2 col-xl-9 offset-xl-3">
                <h1 class="h3 mb-4">Dashboard Teknisi</h1>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <!-- Total Absen Hari Ini -->
                    <div class="col-12 col-sm-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-calendar-check text-primary me-3" style="font-size: 48px;"></i>
                                <div>
                                    <h2 class="fw-bold mb-0" style="font-size: 2rem;"><?php echo $stats['absen_hari_ini']; ?></h2>
                                    <p class="text-muted mb-0">Absen Hari Ini</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Jadwal Perbaikan Hari Ini -->
                    <div class="col-12 col-sm-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-calendar-event text-primary me-3" style="font-size: 48px;"></i>
                                <div>
                                    <h2 class="fw-bold mb-0" style="font-size: 2rem;"><?php echo $stats['jadwal_hari_ini']; ?></h2>
                                    <p class="text-muted mb-0">Jadwal Hari Ini</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Perbaikan Selesai -->
                    <div class="col-12 col-sm-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-check-circle text-primary me-3" style="font-size: 48px;"></i>
                                <div>
                                    <h2 class="fw-bold mb-0" style="font-size: 2rem;"><?php echo $stats['perbaikan_selesai']; ?></h2>
                                    <p class="text-muted mb-0">Perbaikan Selesai</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c038c7e4bde9c8',t:'MTc1NDY2ODc3NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
