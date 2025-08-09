
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include '../config.php';
$admin = $_SESSION['user'];

// Filter variables
$filter_teknisi = '';
$filter_bulan = '';
$where_conditions = ["u.role = 'teknisi'"];
$params = [];
$param_types = '';

// Process filter
if ($_POST && isset($_POST['filter'])) {
    if (!empty($_POST['teknisi'])) {
        $filter_teknisi = $_POST['teknisi'];
        $where_conditions[] = "a.user_id = ?";
        $params[] = $filter_teknisi;
        $param_types .= 'i';
    }
    
    if (!empty($_POST['bulan'])) {
        $filter_bulan = $_POST['bulan'];
        $where_conditions[] = "DATE_FORMAT(a.tanggal, '%Y-%m') = ?";
        $params[] = $filter_bulan;
        $param_types .= 's';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where_clause = implode(' AND ', $where_conditions);

// Count total records
$count_query = "SELECT COUNT(*) as total FROM absensi a 
                JOIN users u ON a.user_id = u.id 
                WHERE $where_clause";

if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
} else {
    $count_result = $conn->query($count_query);
}

$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Main query with pagination
$main_query = "SELECT a.*, u.nama as nama_teknisi, u.foto_profil 
               FROM absensi a 
               JOIN users u ON a.user_id = u.id 
               WHERE $where_clause 
               ORDER BY a.tanggal DESC, a.jam_masuk DESC 
               LIMIT $limit OFFSET $offset";

if (!empty($params)) {
    $stmt = $conn->prepare($main_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $absensi_list = $stmt->get_result();
} else {
    $absensi_list = $conn->query($main_query);
}

// Get teknisi list for dropdown
$teknisi_list = $conn->query("SELECT id, nama FROM users WHERE role = 'teknisi' ORDER BY nama ASC");
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absensi - SARKEM</title>
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
                
                <a class="navbar-brand fw-bold" href="#">SARKEM</a>
            </div>
            
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><?php echo htmlspecialchars($admin['nama']); ?></span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
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
                <a class="nav-link text-dark mb-1" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link text-dark mb-1" href="cabang.php">
                    <i class="bi bi-building me-2"></i>Data Cabang
                </a>
                <a class="nav-link text-dark mb-1" href="karyawan.php">
                    <i class="bi bi-people me-2"></i>Data Karyawan
                </a>
                <a class="nav-link active bg-primary text-white rounded mb-1" href="absensi.php">
                    <i class="bi bi-calendar-check me-2"></i>Data Absensi
                </a>
                <a class="nav-link text-dark mb-1" href="barang.php">
                    <i class="bi bi-box me-2"></i>Data Barang
                </a>
                <a class="nav-link text-dark mb-1" href="kasbon.php">
                    <i class="bi bi-cash-coin me-2"></i>Rekap Kasbon Teknisi
                </a>
                <a class="nav-link text-dark mb-1" href="gaji.php">
                    <i class="bi bi-wallet2 me-2"></i>Slip Gaji Teknisi
                </a>
                <a class="nav-link text-dark mb-1" href="tagihan.php">
                    <i class="bi bi-receipt me-2"></i>Tagihan Pelanggan
                </a>
                <a class="nav-link text-dark mb-1" href="pelanggan.php">
                    <i class="bi bi-person-lines-fill me-2"></i>Data Pelanggan
                </a>
                <a class="nav-link text-dark mb-1" href="settings.php">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="container-fluid pt-5" style="margin-left: 0;">
        <div class="row">
            <div class="col-lg-10 offset-lg-2 col-xl-9 offset-xl-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Data Absensi</h1>
                    <div class="text-muted">
                        Total: <?php echo number_format($total_records); ?> record
                    </div>
                </div>
                
                <!-- Filter Form -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="POST" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Teknisi</label>
                                <select class="form-select" name="teknisi">
                                    <option value="">-- Semua Teknisi --</option>
                                    <?php while ($teknisi = $teknisi_list->fetch_assoc()): ?>
                                    <option value="<?php echo $teknisi['id']; ?>" <?php echo ($filter_teknisi == $teknisi['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($teknisi['nama']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pilih Bulan</label>
                                <input type="month" class="form-control" name="bulan" value="<?php echo $filter_bulan; ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="filter" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="absensi.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Foto</th>
                                        <th>Nama Teknisi</th>
                                        <th>Jam Masuk</th>
                                        <th>Lokasi Masuk</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($absensi_list->num_rows > 0): ?>
                                        <?php while ($absensi = $absensi_list->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $tanggal = new DateTime($absensi['tanggal']);
                                                echo $tanggal->format('d/m/Y'); 
                                                ?>
                                                <br>
                                                <small class="text-muted"><?php echo $tanggal->format('l'); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($absensi['foto_profil']): ?>
                                                <img src="../uploads/profil/<?php echo $absensi['foto_profil']; ?>" alt="Foto" width="40" height="40" class="rounded-circle">
                                                <?php else: ?>
                                                <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($absensi['nama_teknisi']); ?></td>
                                            <td>
                                                <?php if ($absensi['jam_masuk']): ?>
                                                    <strong><?php echo date('H:i', strtotime($absensi['jam_masuk'])); ?></strong>
                                                    <br>
                                                    <?php if ($absensi['jam_keluar']): ?>
                                                        <small class="text-muted">Keluar: <?php echo date('H:i', strtotime($absensi['jam_keluar'])); ?></small>
                                                    <?php else: ?>
                                                        <small class="text-warning">Belum absen keluar</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($absensi['lokasi_masuk']): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars($absensi['lokasi_masuk']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $absensi['status'];
                                                $badge_class = '';
                                                switch($status) {
                                                    case 'hadir': $badge_class = 'bg-success'; break;
                                                    case 'terlambat': $badge_class = 'bg-warning'; break;
                                                    case 'izin': $badge_class = 'bg-info'; break;
                                                    case 'sakit': $badge_class = 'bg-secondary'; break;
                                                    case 'alpha': $badge_class = 'bg-danger'; break;
                                                    default: $badge_class = 'bg-light text-dark';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                                <?php if ($filter_teknisi || $filter_bulan): ?>
                                                    Tidak ada data absensi sesuai filter
                                                <?php else: ?>
                                                    Belum ada data absensi
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page-1); ?><?php echo $filter_teknisi ? '&teknisi='.$filter_teknisi : ''; ?><?php echo $filter_bulan ? '&bulan='.$filter_bulan : ''; ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_teknisi ? '&teknisi='.$filter_teknisi : ''; ?><?php echo $filter_bulan ? '&bulan='.$filter_bulan : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page+1); ?><?php echo $filter_teknisi ? '&teknisi='.$filter_teknisi : ''; ?><?php echo $filter_bulan ? '&bulan='.$filter_bulan : ''; ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                            
                            <div class="text-center text-muted mt-2">
                                Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> 
                                (<?php echo number_format($total_records); ?> total record)
                            </div>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c06a9ce193e9c7',t:'MTc1NDY3MDgxNy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
