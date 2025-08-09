
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include '../config.php';
$admin = $_SESSION['user'];

$selected_teknisi = '';
$selected_bulan = '';
$teknisi_data = null;
$absensi_data = [];
$kasbon_data = [];
$total_hadir = 0;
$total_kasbon = 0;
$gaji_bersih = 0;

// Process filter
if ($_POST && isset($_POST['tampilkan'])) {
    $selected_teknisi = $_POST['teknisi'];
    $selected_bulan = $_POST['bulan'];
    
    if (!empty($selected_teknisi) && !empty($selected_bulan)) {
        // Get teknisi data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'teknisi'");
        $stmt->bind_param("i", $selected_teknisi);
        $stmt->execute();
        $teknisi_data = $stmt->get_result()->fetch_assoc();
        
        if ($teknisi_data) {
            // Get absensi data for the month
            $stmt = $conn->prepare("SELECT * FROM absensi WHERE user_id = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ? ORDER BY tanggal ASC");
            $stmt->bind_param("is", $selected_teknisi, $selected_bulan);
            $stmt->execute();
            $absensi_result = $stmt->get_result();
            
            while ($row = $absensi_result->fetch_assoc()) {
                $absensi_data[] = $row;
                if ($row['status'] == 'hadir' || $row['status'] == 'terlambat') {
                    $total_hadir++;
                }
            }
            
            // Get kasbon data for the month
            $stmt = $conn->prepare("SELECT * FROM kasbon WHERE user_id = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ? ORDER BY tanggal ASC");
            $stmt->bind_param("is", $selected_teknisi, $selected_bulan);
            $stmt->execute();
            $kasbon_result = $stmt->get_result();
            
            while ($row = $kasbon_result->fetch_assoc()) {
                $kasbon_data[] = $row;
                $total_kasbon += $row['jumlah'];
            }
            
            // Calculate gaji bersih
            $gaji_pokok = $teknisi_data['gaji_pokok'] ?? 0;
            $gaji_bersih = $gaji_pokok - $total_kasbon;
        }
    }
}

// Get teknisi list for dropdown
$teknisi_list = $conn->query("SELECT id, nama FROM users WHERE role = 'teknisi' ORDER BY nama ASC");
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji Teknisi - SARKEM</title>
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
                <a class="nav-link text-dark mb-1" href="absensi.php">
                    <i class="bi bi-calendar-check me-2"></i>Data Absensi
                </a>
                <a class="nav-link text-dark mb-1" href="barang.php">
                    <i class="bi bi-box me-2"></i>Data Barang
                </a>
                <a class="nav-link text-dark mb-1" href="kasbon.php">
                    <i class="bi bi-cash-coin me-2"></i>Rekap Kasbon Teknisi
                </a>
                <a class="nav-link active bg-primary text-white rounded mb-1" href="slip_gaji.php">
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
                    <h1 class="h3">Slip Gaji Teknisi</h1>
                </div>
                
                <!-- Filter Form -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="POST" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Teknisi</label>
                                <select class="form-select" name="teknisi" required>
                                    <option value="">-- Pilih Teknisi --</option>
                                    <?php while ($teknisi = $teknisi_list->fetch_assoc()): ?>
                                    <option value="<?php echo $teknisi['id']; ?>" <?php echo ($selected_teknisi == $teknisi['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($teknisi['nama']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pilih Bulan</label>
                                <input type="month" class="form-control" name="bulan" value="<?php echo $selected_bulan; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="tampilkan" class="btn btn-primary">
                                    <i class="bi bi-eye"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($teknisi_data): ?>
                <!-- Slip Gaji -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-0">
                                    <i class="bi bi-wallet2 me-2"></i>
                                    Slip Gaji - <?php echo htmlspecialchars($teknisi_data['nama']); ?>
                                </h5>
                                <small>
                                    Periode: <?php 
                                    $bulan_indo = [
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ];
                                    $tahun_bulan = explode('-', $selected_bulan);
                                    echo $bulan_indo[$tahun_bulan[1]] . ' ' . $tahun_bulan[0];
                                    ?>
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <h6 class="mb-1">SARKEM</h6>
                                <small>Service & Repair Center</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Informasi Kantor Cabang -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-primary mb-2">
                                                    <i class="bi bi-building me-2"></i>Kantor Cabang
                                                </h6>
                                                <p class="mb-1"><strong><?php echo htmlspecialchars($teknisi_data['cabang'] ?? 'Cabang Utama'); ?></strong></p>
                                                <p class="mb-0 text-muted small">
                                                    <?php echo htmlspecialchars($teknisi_data['alamat_cabang'] ?? 'Jl. Raya Utama No. 123, Jakarta Pusat 10110'); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-6 text-md-end">
                                                <p class="mb-1 text-muted small">Tanggal Cetak:</p>
                                                <p class="mb-0"><strong><?php echo date('d/m/Y H:i'); ?> WIB</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Data Karyawan -->
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary mb-3">Data Karyawan</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="40%">Nama</td>
                                        <td width="5%">:</td>
                                        <td><?php echo htmlspecialchars($teknisi_data['nama']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>:</td>
                                        <td><?php echo htmlspecialchars($teknisi_data['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>No. HP</td>
                                        <td>:</td>
                                        <td><?php echo htmlspecialchars($teknisi_data['no_hp'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Gaji Pokok</td>
                                        <td>:</td>
                                        <td><strong class="text-success">Rp <?php echo number_format($teknisi_data['gaji_pokok'] ?? 0, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Ringkasan Gaji -->
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary mb-3">Ringkasan Gaji</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <h4 class="text-info mb-1"><?php echo $total_hadir; ?></h4>
                                                    <small class="text-muted">Total Hadir</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <h6 class="text-warning mb-1">Rp <?php echo number_format($total_kasbon, 0, ',', '.'); ?></h6>
                                                    <small class="text-muted">Total Kasbon</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <h5 class="text-success mb-1">Rp <?php echo number_format($gaji_bersih, 0, ',', '.'); ?></h5>
                                                <small class="text-muted">Gaji Bersih</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Detail Absensi -->
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary mb-3">Detail Absensi</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Jam Masuk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($absensi_data)): ?>
                                                <?php foreach ($absensi_data as $absensi): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $tgl = new DateTime($absensi['tanggal']);
                                                        echo $tgl->format('d/m'); 
                                                        ?>
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
                                                        <span class="badge <?php echo $badge_class; ?> small"><?php echo ucfirst($status); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo $absensi['jam_masuk'] ? date('H:i', strtotime($absensi['jam_masuk'])) : '-'; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada data absensi</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Detail Kasbon -->
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary mb-3">Detail Kasbon</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jumlah</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($kasbon_data)): ?>
                                                <?php foreach ($kasbon_data as $kasbon): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $tgl = new DateTime($kasbon['tanggal']);
                                                        echo $tgl->format('d/m'); 
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="text-danger">Rp <?php echo number_format($kasbon['jumlah'], 0, ',', '.'); ?></span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo htmlspecialchars($kasbon['keterangan']); ?></small>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada kasbon</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tanda Tangan -->
                        <div class="row mt-5">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="mb-1">Jakarta, <?php echo date('d') . ' ' . $bulan_indo[date('m')] . ' ' . date('Y'); ?></p>
                                    <p class="mb-4">Mengetahui,</p>
                                    <div style="height: 80px; border-bottom: 1px solid #000; margin-bottom: 10px; position: relative;">
                                        <div style="position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); background: white; padding: 0 10px;">
                                            <strong><?php echo htmlspecialchars($admin['nama']); ?></strong>
                                        </div>
                                    </div>
                                    <p class="mb-0 small text-muted">Admin SARKEM</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Download PDF Button -->
                        <div class="text-center mt-4">
                            <form method="POST" action="slip_pdf.php" style="display: inline;">
                                <input type="hidden" name="teknisi" value="<?php echo $selected_teknisi; ?>">
                                <input type="hidden" name="bulan" value="<?php echo $selected_bulan; ?>">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($_POST && isset($_POST['tampilkan'])): ?>
                <!-- No Data Found -->
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3"></i>
                        <h5>Data Tidak Ditemukan</h5>
                        <p class="text-muted">Teknisi atau bulan yang dipilih tidak memiliki data.</p>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Initial State -->
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-wallet2 fs-1 text-primary mb-3"></i>
                        <h5>Pilih Teknisi dan Bulan</h5>
                        <p class="text-muted">Silakan pilih teknisi dan bulan untuk melihat slip gaji.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c07a3f94fbe9cb',t:'MTc1NDY3MTQ1Ny4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
