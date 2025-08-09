
<?php
session_start();

// Check if user is logged in and has owner role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../config.php';

// Get dashboard statistics
$stats = [];

// Count total teknisi
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'teknisi'");
$stats['teknisi'] = $result ? $result->fetch_assoc()['total'] : 0;

// Count total cabang
$result = $conn->query("SELECT COUNT(*) as total FROM cabang");
$stats['cabang'] = $result ? $result->fetch_assoc()['total'] : 0;

// Count total barang
$result = $conn->query("SELECT COUNT(*) as total FROM barang");
$stats['barang'] = $result ? $result->fetch_assoc()['total'] : 0;

// Get total kasbon this month
$current_month = date('Y-m');
$result = $conn->query("SELECT SUM(jumlah) as total FROM kasbon WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$current_month'");
$stats['kasbon'] = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;

// Get owner name
$owner_name = $_SESSION['nama'] ?? 'Owner';
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - SARKEM</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #10b981;
            --primary-dark: #059669;
            --secondary-color: #34d399;
            --accent-color: #6ee7b7;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #ecfdf5;
            --border-color: #a7f3d0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        /* Topbar Styles */
        .topbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            height: 70px;
        }
        
        .topbar .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: white !important;
            letter-spacing: -0.02em;
        }
        
        .topbar .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .topbar .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            width: 280px;
            height: calc(100vh - 70px);
            background: white;
            border-right: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            z-index: 1020;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
        }
        
        .sidebar-menu .nav-link {
            color: var(--text-dark);
            padding: 0.875rem 1.5rem;
            font-weight: 500;
            border: none;
            border-radius: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-menu .nav-link:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }
        
        .sidebar-menu .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            position: relative;
        }
        
        .sidebar-menu .nav-link.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--accent-color);
        }
        
        .sidebar-menu .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        
        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            font-weight: 400;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1.5rem;
        }
        
        .stat-icon.teknisi { background: linear-gradient(135deg, #34d399, #10b981); }
        .stat-icon.cabang { background: linear-gradient(135deg, #6ee7b7, #34d399); }
        .stat-icon.barang { background: linear-gradient(135deg, #7dd3fc, #0ea5e9); }
        .stat-icon.kasbon { background: linear-gradient(135deg, #f9a8d4, #ec4899); }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-change {
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .stat-change.positive { color: #10b981; }
        .stat-change.negative { color: #ef4444; }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.5rem;
            }
            
            .stat-value {
                font-size: 2rem;
            }
        }
        
        /* Sidebar Toggle Button */
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Logout Button */
        .btn-logout {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--text-light);
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg topbar">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle d-lg-none me-3" type="button" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand" href="#">
                    <i class="bi bi-layers me-2"></i>SARKEM
                </a>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="user-info d-none d-sm-flex">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($owner_name, 0, 2)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;"><?php echo htmlspecialchars($owner_name); ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.8;">Owner</div>
                    </div>
                </div>
                <a href="../logout.php" class="btn btn-logout">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    <span class="d-none d-sm-inline">Logout</span>
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="data-teknisi.php">
                        <i class="bi bi-people"></i>
                        Data Teknisi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="grafik-absensi.php">
                        <i class="bi bi-bar-chart"></i>
                        Grafik Absensi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="input-kasbon.php">
                        <i class="bi bi-cash-coin"></i>
                        Input Kasbon Teknisi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="data-barang.php">
                        <i class="bi bi-box-seam"></i>
                        Data Barang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="input-jadwal.php">
                        <i class="bi bi-calendar-check"></i>
                        Input Jadwal Perbaikan
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Dashboard Owner</h1>
            <p class="page-subtitle">Selamat datang di sistem manajemen SARKEM. Berikut adalah ringkasan data terkini.</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total Teknisi -->
            <div class="stat-card">
                <div class="stat-icon teknisi">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['teknisi']); ?></div>
                <div class="stat-label">Total Teknisi</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> Aktif
                </div>
            </div>
            
            <!-- Total Cabang -->
            <div class="stat-card">
                <div class="stat-icon cabang">
                    <i class="bi bi-building"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['cabang']); ?></div>
                <div class="stat-label">Total Cabang</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> Beroperasi
                </div>
            </div>
            
            <!-- Total Barang -->
            <div class="stat-card">
                <div class="stat-icon barang">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['barang']); ?></div>
                <div class="stat-label">Total Barang</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> Tersedia
                </div>
            </div>
            
            <!-- Total Kasbon Bulan Ini -->
            <div class="stat-card">
                <div class="stat-icon kasbon">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($stats['kasbon'], 0, ',', '.'); ?></div>
                <div class="stat-label">Kasbon Bulan Ini</div>
                <div class="stat-change <?php echo $stats['kasbon'] > 0 ? 'negative' : 'positive'; ?>">
                    <i class="bi bi-<?php echo $stats['kasbon'] > 0 ? 'arrow-up' : 'dash'; ?>"></i> 
                    <?php echo date('F Y'); ?>
                </div>
            </div>
        </div>
        
        <!-- Additional Content Area -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Informasi Sistem
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Versi Sistem:</strong> SARKEM v1.0</p>
                                <p class="mb-2"><strong>Database:</strong> MySQL</p>
                                <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">Online</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Last Update:</strong> <?php echo date('d F Y, H:i'); ?></p>
                                <p class="mb-2"><strong>Server Time:</strong> <?php echo date('H:i:s'); ?></p>
                                <p class="mb-0"><strong>Timezone:</strong> Asia/Jakarta</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('show');
            
            // Close sidebar when clicking outside on mobile
            if (sidebar.classList.contains('show')) {
                document.addEventListener('click', closeSidebarOnClickOutside);
            } else {
                document.removeEventListener('click', closeSidebarOnClickOutside);
            }
        }
        
        function closeSidebarOnClickOutside(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            
            if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
                sidebar.classList.remove('show');
                document.removeEventListener('click', closeSidebarOnClickOutside);
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                document.removeEventListener('click', closeSidebarOnClickOutside);
            }
        });
        
        // Auto-refresh stats every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c01cc57345e9c8',t:'MTc1NDY2NzYyOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
