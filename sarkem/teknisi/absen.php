
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

// Proses form absen
if ($_POST) {
    $id_teknisi = $teknisi['id'];
    $tanggal = date('Y-m-d');
    $jam_masuk = date('H:i:s');
    $lokasi_masuk = $_POST['lokasi'] ?? '';
    $foto_base64 = $_POST['foto_base64'] ?? '';
    
    // Cek apakah sudah absen hari ini
    $stmt = $conn->prepare("SELECT id FROM absensi WHERE id_teknisi = ? AND tanggal = ?");
    $stmt->bind_param("is", $id_teknisi, $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = 'Sudah absen hari ini';
        $message_type = 'success';
    } else {
        // Simpan foto ke folder uploads
        $foto_name = '';
        if (!empty($foto_base64)) {
            $foto_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_base64));
            $foto_name = 'absen_' . $id_teknisi . '_' . date('Ymd_His') . '.jpg';
            $foto_path = '../uploads/' . $foto_name;
            
            // Buat folder uploads jika belum ada
            if (!file_exists('../uploads/')) {
                mkdir('../uploads/', 0777, true);
            }
            
            file_put_contents($foto_path, $foto_data);
        }
        
        // Insert ke database
        $stmt = $conn->prepare("INSERT INTO absensi (id_teknisi, tanggal, jam_masuk, lokasi_masuk, foto_masuk) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_teknisi, $tanggal, $jam_masuk, $lokasi_masuk, $foto_name);
        
        if ($stmt->execute()) {
            $message = 'Absen berhasil disimpan';
            $message_type = 'success';
        } else {
            $message = 'Gagal menyimpan absen';
            $message_type = 'danger';
        }
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absen Harian - SARKEM</title>
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
                <a class="nav-link active bg-primary text-white rounded mb-1" href="absen.php">
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
                <h1 class="h3 mb-4">Absen Harian</h1>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-camera me-2"></i>Selfie Absen
                        </h5>
                        
                        <form method="POST" id="absenForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Camera Preview -->
                                    <div class="mb-3">
                                        <video id="video" width="320" height="240" autoplay class="border rounded"></video>
                                        <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <button type="button" id="captureBtn" class="btn btn-primary me-2">
                                            <i class="bi bi-camera"></i> Ambil Foto
                                        </button>
                                        <button type="button" id="retakeBtn" class="btn btn-secondary" style="display: none;">
                                            <i class="bi bi-arrow-clockwise"></i> Foto Ulang
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Info Absen -->
                                    <div class="mb-3">
                                        <label class="form-label">Teknisi</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($teknisi['nama']); ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal</label>
                                        <input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jam</label>
                                        <input type="text" class="form-control" id="currentTime" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Lokasi</label>
                                        <input type="text" class="form-control" id="lokasi" name="lokasi" readonly>
                                        <div class="form-text">Mengambil lokasi otomatis...</div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="foto_base64" name="foto_base64">
                            
                            <div class="d-grid">
                                <button type="submit" id="submitBtn" class="btn btn-success btn-lg" disabled>
                                    <i class="bi bi-check-circle"></i> Kirim Absen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('captureBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const submitBtn = document.getElementById('submitBtn');
        const fotoInput = document.getElementById('foto_base64');
        const lokasiInput = document.getElementById('lokasi');
        const currentTimeInput = document.getElementById('currentTime');
        
        let stream = null;
        let photoTaken = false;
        
        // Update waktu setiap detik
        function updateTime() {
            const now = new Date();
            currentTimeInput.value = now.toLocaleTimeString('id-ID');
        }
        setInterval(updateTime, 1000);
        updateTime();
        
        // Akses kamera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
            } catch (err) {
                alert('Tidak dapat mengakses kamera: ' + err.message);
            }
        }
        
        // Ambil lokasi
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        lokasiInput.value = `${lat}, ${lng}`;
                        checkFormReady();
                    },
                    function(error) {
                        lokasiInput.value = 'Lokasi tidak tersedia';
                        lokasiInput.nextElementSibling.textContent = 'Gagal mengambil lokasi';
                        checkFormReady();
                    }
                );
            } else {
                lokasiInput.value = 'Geolocation tidak didukung';
                lokasiInput.nextElementSibling.textContent = 'Browser tidak mendukung geolocation';
                checkFormReady();
            }
        }
        
        // Ambil foto
        captureBtn.addEventListener('click', function() {
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 320, 240);
            
            const dataURL = canvas.toDataURL('image/jpeg');
            fotoInput.value = dataURL;
            
            // Tampilkan hasil foto
            video.style.display = 'none';
            canvas.style.display = 'block';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            
            photoTaken = true;
            checkFormReady();
        });
        
        // Foto ulang
        retakeBtn.addEventListener('click', function() {
            video.style.display = 'block';
            canvas.style.display = 'none';
            captureBtn.style.display = 'inline-block';
            retakeBtn.style.display = 'none';
            
            fotoInput.value = '';
            photoTaken = false;
            checkFormReady();
        });
        
        // Cek apakah form siap submit
        function checkFormReady() {
            if (photoTaken && lokasiInput.value && lokasiInput.value !== 'Mengambil lokasi otomatis...') {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        // Inisialisasi
        startCamera();
        getLocation();
        
        // Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c03ce6c241e9c8',t:'MTc1NDY2ODk0NC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
