
<?php
session_start();

// Check if user is admin, redirect if not
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include database configuration
include '../config.php';

// Get filter parameters
$selected_pelanggan = $_GET['pelanggan'] ?? '';

// Fetch all customers for dropdown
$pelanggan_result = $conn->query("SELECT id, nama FROM pelanggan ORDER BY nama ASC");
$pelanggan_list = $pelanggan_result->fetch_all(MYSQLI_ASSOC);

// Fetch repair reports based on filters
$laporan_list = [];
$selected_pelanggan_name = '';
if (!empty($selected_pelanggan)) {
    // Get selected customer name
    $stmt = $conn->prepare("SELECT nama FROM pelanggan WHERE id = ?");
    $stmt->bind_param("i", $selected_pelanggan);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selected_pelanggan_name = $row['nama'];
    }
    $stmt->close();
    
    // Get repair reports
    $stmt = $conn->prepare("
        SELECT 
            jp.id as jadwal_id,
            jp.tgl_perbaikan,
            jp.masalah,
            jp.barang_digunakan,
            jp.status,
            jp.id_teknisi,
            jp.id_pelanggan,
            u.nama as nama_teknisi,
            p.nama as nama_pelanggan,
            tp.id as tagihan_id,
            tp.jumlah_tagihan,
            tp.tanggal_tagihan
        FROM jadwal_perbaikan jp
        JOIN users u ON jp.id_teknisi = u.id
        JOIN pelanggan p ON jp.id_pelanggan = p.id
        LEFT JOIN tagihan_pelanggan tp ON jp.id_pelanggan = tp.id_pelanggan
        WHERE tp.id_pelanggan = ?
        ORDER BY jp.tgl_perbaikan DESC
    ");
    $stmt->bind_param("i", $selected_pelanggan);
    $stmt->execute();
    $result = $stmt->get_result();
    $laporan_list = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Function to format currency
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Function to get status badge class
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'selesai':
            return 'bg-success';
        case 'dalam proses':
            return 'bg-warning text-dark';
        case 'pending':
            return 'bg-secondary';
        case 'dibatalkan':
            return 'bg-danger';
        default:
            return 'bg-primary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Laporan Perbaikan - SARKEM Admin</title>
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
        .filter-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Fixed Topbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top topbar">
        <div class="container-fluid">
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <span class="navbar-brand mb-0 h1">SARKEM</span>
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
                            <a class="nav-link" href="dashboard.php">
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
                            <a class="nav-link active" href="rekap_laporan_perbaikan.php">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Rekap Laporan Perbaikan
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
                        <h1 class="h2">Rekap Laporan Perbaikan</h1>
                    </div>

                    <!-- Filter Form -->
                    <div class="filter-form">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="pelanggan" class="form-label">Nama Pelanggan</label>
                                <select class="form-select" id="pelanggan" name="pelanggan" required>
                                    <option value="">-- Pilih Pelanggan --</option>
                                    <?php foreach ($pelanggan_list as $pelanggan): ?>
                                        <option value="<?php echo $pelanggan['id']; ?>" 
                                                <?php echo ($selected_pelanggan == $pelanggan['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($pelanggan['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-2"></i>Filter
                                </button>
                                <a href="rekap_laporan_perbaikan.php" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Results Table -->
                    <?php if (!empty($selected_pelanggan)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-list-ul me-2"></i>
                                    Laporan Perbaikan - <?php echo htmlspecialchars($selected_pelanggan_name); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($laporan_list)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">Tidak ada data laporan perbaikan untuk pelanggan yang dipilih.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Tgl Perbaikan</th>
                                                    <th>Nama Teknisi</th>
                                                    <th>Masalah</th>
                                                    <th>Barang Digunakan</th>
                                                    <th>Status</th>
                                                    <th>Jumlah Tagihan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($laporan_list as $laporan): ?>
                                                    <tr>
                                                        <td><?php echo date('d/m/Y', strtotime($laporan['tgl_perbaikan'])); ?></td>
                                                        <td><?php echo htmlspecialchars($laporan['nama_teknisi']); ?></td>
                                                        <td><?php echo htmlspecialchars($laporan['masalah']); ?></td>
                                                        <td><?php echo htmlspecialchars($laporan['barang_digunakan'] ?: '-'); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo getStatusBadge($laporan['status']); ?>">
                                                                <?php echo htmlspecialchars($laporan['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo $laporan['jumlah_tagihan'] ? formatRupiah($laporan['jumlah_tagihan']) : '-'; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($laporan['tagihan_id']): ?>
                                                                <a href="invoice_pdf.php?pelanggan=<?php echo $laporan['id_pelanggan']; ?>&bulan=<?php echo date('Y-m', strtotime($laporan['tgl_perbaikan'])); ?>" 
                                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                                    <i class="bi bi-printer me-1"></i>Cetak Invoice
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Summary -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Ringkasan</h6>
                                                    <p class="card-text mb-1">
                                                        <strong>Total Perbaikan:</strong> <?php echo count($laporan_list); ?> kasus
                                                    </p>
                                                    <p class="card-text mb-1">
                                                        <strong>Total Tagihan:</strong> 
                                                        <?php 
                                                        $total_tagihan = array_sum(array_filter(array_column($laporan_list, 'jumlah_tagihan')));
                                                        echo formatRupiah($total_tagihan);
                                                        ?>
                                                    </p>
                                                    <p class="card-text mb-0">
                                                        <strong>Status Selesai:</strong> 
                                                        <?php 
                                                        $selesai = count(array_filter($laporan_list, function($item) {
                                                            return strtolower($item['status']) === 'selesai';
                                                        }));
                                                        echo $selesai . ' dari ' . count($laporan_list) . ' kasus';
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-info text-white">
                                                <div class="card-body">
                                                    <h6 class="card-title">Informasi Pelanggan</h6>
                                                    <p class="card-text mb-1">
                                                        <strong>Nama:</strong> <?php echo htmlspecialchars($selected_pelanggan_name); ?>
                                                    </p>
                                                    <p class="card-text mb-0">
                                                        <strong>ID Pelanggan:</strong> #<?php echo str_pad($selected_pelanggan, 4, '0', STR_PAD_LEFT); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-funnel fs-1"></i>
                            <p class="mt-2">Silakan pilih pelanggan untuk melihat rekap laporan perbaikan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c48c9ff624e9d4',t:'MTc1NDcxNDE1My4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
