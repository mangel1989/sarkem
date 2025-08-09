
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teknisi') {
    header("Location: ../index.php");
    exit;
}
include '../config.php';
$teknisi = $_SESSION['user'];

$message = '';
$message_type = '';

// Proses ambil jadwal
if ($_POST && isset($_POST['ambil_jadwal'])) {
    $id_jadwal = $_POST['id_jadwal'];
    
    $stmt = $conn->prepare("UPDATE jadwal_perbaikan SET status = 'proses' WHERE id = ? AND id_teknisi = ?");
    $stmt->bind_param("ii", $id_jadwal, $teknisi['id']);
    
    if ($stmt->execute()) {
        $message = 'Jadwal berhasil diambil';
        $message_type = 'success';
    } else {
        $message = 'Gagal mengambil jadwal';
        $message_type = 'danger';
    }
}

// Query jadwal perbaikan
$stmt = $conn->prepare("
    SELECT jp.*, p.nama as nama_pelanggan 
    FROM jadwal_perbaikan jp 
    JOIN pelanggan p ON jp.id_pelanggan = p.id 
    WHERE jp.id_teknisi = ? 
    ORDER BY jp.tgl_perbaikan ASC
");
$stmt->bind_param("i", $teknisi['id']);
$stmt->execute();
$jadwal_list = $stmt->get_result();
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Perbaikan - SARKEM</title>
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
                <a class="nav-link text-dark mb-1" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link text-dark mb-1" href="absen.php">
                    <i class="bi bi-calendar-check me-2"></i>Absen Harian
                </a>
                <a class="nav-link active bg-primary text-white rounded mb-1" href="jadwal.php">
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
                <h1 class="h3 mb-4">Jadwal Perbaikan</h1>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-calendar-event me-2"></i>Daftar Jadwal Perbaikan
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Lokasi</th>
                                        <th>Masalah</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($jadwal_list->num_rows > 0): ?>
                                        <?php while ($jadwal = $jadwal_list->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($jadwal['tgl_perbaikan'])); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['nama_pelanggan']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['lokasi']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['masalah']); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = '';
                                                switch($jadwal['status']) {
                                                    case 'belum':
                                                        $badge_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'proses':
                                                        $badge_class = 'bg-info text-dark';
                                                        break;
                                                    case 'selesai':
                                                        $badge_class = 'bg-success';
                                                        break;
                                                    default:
                                                        $badge_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($jadwal['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($jadwal['status'] == 'belum'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="id_jadwal" value="<?php echo $jadwal['id']; ?>">
                                                    <button type="submit" name="ambil_jadwal" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-hand-thumbs-up"></i> Ambil Jadwal
                                                    </button>
                                                </form>
                                                <?php elseif ($jadwal['status'] == 'proses'): ?>
                                                <span class="text-muted">Sedang Dikerjakan</span>
                                                <?php elseif ($jadwal['status'] == 'selesai'): ?>
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle"></i> Selesai
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                                Tidak ada jadwal perbaikan
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c03ff8a465e9c8',t:'MTc1NDY2OTA3MC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
