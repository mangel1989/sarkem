
<?php
session_start();

// Check if user is admin, redirect if not
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include database configuration
include '../config.php';

$message = '';
$message_type = '';

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_company':
                $nama_perusahaan = $_POST['nama_perusahaan'];
                
                // Handle logo upload
                $logo_path = '';
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $logo_filename = 'logo_' . time() . '.' . $file_extension;
                        $logo_path = $upload_dir . $logo_filename;
                        
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                            // Update logo path in database
                            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('company_logo', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                            $stmt->bind_param("s", $logo_path);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } else {
                        $message = 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
                        $message_type = 'danger';
                    }
                }
                
                // Update company name
                if (empty($message)) {
                    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('company_name', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $stmt->bind_param("s", $nama_perusahaan);
                    $stmt->execute();
                    $stmt->close();
                    
                    $message = 'Profil perusahaan berhasil diperbarui!';
                    $message_type = 'success';
                }
                break;
                
            case 'update_attendance':
                $jam_masuk = $_POST['jam_masuk'];
                $toleransi_menit = $_POST['toleransi_menit'];
                
                // Update work time
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('work_time', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->bind_param("s", $jam_masuk);
                $stmt->execute();
                $stmt->close();
                
                // Update tolerance minutes
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('tolerance_minutes', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->bind_param("i", $toleransi_menit);
                $stmt->execute();
                $stmt->close();
                
                $message = 'Aturan absensi berhasil diperbarui!';
                $message_type = 'success';
                break;
        }
    }
}

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Set default values
$company_name = $settings['company_name'] ?? '';
$company_logo = $settings['company_logo'] ?? '';
$work_time = $settings['work_time'] ?? '08:00';
$tolerance_minutes = $settings['tolerance_minutes'] ?? 15;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SARKEM Admin</title>
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
        .logo-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        .logo-placeholder {
            width: 100px;
            height: 100px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
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
                            <a class="nav-link" href="pelanggan.php">
                                <i class="bi bi-people me-2"></i>
                                Data Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tagihan_pelanggan.php">
                                <i class="bi bi-receipt me-2"></i>
                                Tagihan Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="teknisi.php">
                                <i class="bi bi-tools me-2"></i>
                                Teknisi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="laporan.php">
                                <i class="bi bi-graph-up me-2"></i>
                                Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="settings.php">
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
                        <h1 class="h2">Settings</h1>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Company Profile Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-building me-2"></i>Profil Perusahaan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update_company">
                                        
                                        <div class="mb-3">
                                            <label for="nama_perusahaan" class="form-label">Nama Perusahaan</label>
                                            <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" 
                                                   value="<?php echo htmlspecialchars($company_name); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Logo Perusahaan</label>
                                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                            <div class="form-text">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Preview Logo</label>
                                            <div>
                                                <?php if (!empty($company_logo) && file_exists($company_logo)): ?>
                                                    <img src="<?php echo htmlspecialchars($company_logo); ?>" alt="Company Logo" class="logo-preview">
                                                <?php else: ?>
                                                    <div class="logo-placeholder">
                                                        <i class="bi bi-image fs-3"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Simpan Profil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Rules Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-clock me-2"></i>Aturan Absensi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_attendance">
                                        
                                        <div class="mb-3">
                                            <label for="jam_masuk" class="form-label">Jam Masuk</label>
                                            <input type="time" class="form-control" id="jam_masuk" name="jam_masuk" 
                                                   value="<?php echo htmlspecialchars($work_time); ?>" required>
                                            <div class="form-text">Waktu mulai kerja karyawan.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="toleransi_menit" class="form-label">Toleransi Keterlambatan (Menit)</label>
                                            <input type="number" class="form-control" id="toleransi_menit" name="toleransi_menit" 
                                                   value="<?php echo htmlspecialchars($tolerance_minutes); ?>" min="0" max="60" required>
                                            <div class="form-text">Batas toleransi keterlambatan dalam menit.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Informasi Saat Ini:</h6>
                                                    <p class="card-text mb-1">
                                                        <strong>Jam Masuk:</strong> <?php echo date('H:i', strtotime($work_time)); ?>
                                                    </p>
                                                    <p class="card-text mb-0">
                                                        <strong>Toleransi:</strong> <?php echo $tolerance_minutes; ?> menit
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Simpan Aturan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c478801529e9d4',t:'MTc1NDcxMzMyOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
