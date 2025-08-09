
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

// Proses form laporan
if ($_POST && isset($_POST['submit_laporan'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $barang_digunakan = $_POST['barang_digunakan'];
    $foto_sebelum_base64 = $_POST['foto_sebelum_base64'] ?? '';
    $foto_sesudah_base64 = $_POST['foto_sesudah_base64'] ?? '';
    
    $foto_sebelum = '';
    $foto_sesudah = '';
    
    // Buat folder uploads jika belum ada
    if (!file_exists('../uploads/')) {
        mkdir('../uploads/', 0777, true);
    }
    
    // Simpan foto sebelum
    if (!empty($foto_sebelum_base64)) {
        $foto_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_sebelum_base64));
        $foto_sebelum = 'sebelum_' . $id_jadwal . '_' . date('Ymd_His') . '.jpg';
        file_put_contents('../uploads/' . $foto_sebelum, $foto_data);
    }
    
    // Simpan foto sesudah
    if (!empty($foto_sesudah_base64)) {
        $foto_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_sesudah_base64));
        $foto_sesudah = 'sesudah_' . $id_jadwal . '_' . date('Ymd_His') . '.jpg';
        file_put_contents('../uploads/' . $foto_sesudah, $foto_data);
    }
    
    // Update jadwal perbaikan
    $stmt = $conn->prepare("UPDATE jadwal_perbaikan SET barang_digunakan = ?, foto_sebelum = ?, foto_sesudah = ?, status = 'selesai' WHERE id = ? AND id_teknisi = ?");
    $stmt->bind_param("sssii", $barang_digunakan, $foto_sebelum, $foto_sesudah, $id_jadwal, $teknisi['id']);
    
    if ($stmt->execute()) {
        $message = 'Laporan perbaikan berhasil disimpan';
        $message_type = 'success';
    } else {
        $message = 'Gagal menyimpan laporan perbaikan';
        $message_type = 'danger';
    }
}

// Query jadwal yang sedang proses
$stmt = $conn->prepare("
    SELECT jp.*, p.nama as nama_pelanggan, p.alamat 
    FROM jadwal_perbaikan jp 
    JOIN pelanggan p ON jp.id_pelanggan = p.id 
    WHERE jp.id_teknisi = ? AND jp.status = 'proses'
    ORDER BY jp.tgl_perbaikan ASC
");
$stmt->bind_param("i", $teknisi['id']);
$stmt->execute();
$jadwal_proses = $stmt->get_result();
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perbaikan - SARKEM</title>
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
                <a class="nav-link text-dark mb-1" href="jadwal.php">
                    <i class="bi bi-calendar-event me-2"></i>Jadwal Perbaikan
                </a>
                <a class="nav-link active bg-primary text-white rounded mb-1" href="laporan.php">
                    <i class="bi bi-file-earmark-text me-2"></i>Laporan Perbaikan
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="container-fluid pt-5" style="margin-left: 0;">
        <div class="row">
            <div class="col-lg-10 offset-lg-2 col-xl-9 offset-xl-3">
                <h1 class="h3 mb-4">Laporan Perbaikan</h1>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-file-earmark-text me-2"></i>Form Laporan Perbaikan
                        </h5>
                        
                        <?php if ($jadwal_proses->num_rows > 0): ?>
                        <form method="POST" id="laporanForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Jadwal Perbaikan</label>
                                        <select class="form-select" name="id_jadwal" id="jadwalSelect" required>
                                            <option value="">-- Pilih Jadwal --</option>
                                            <?php while ($jadwal = $jadwal_proses->fetch_assoc()): ?>
                                            <option value="<?php echo $jadwal['id']; ?>" 
                                                    data-masalah="<?php echo htmlspecialchars($jadwal['masalah']); ?>"
                                                    data-pelanggan="<?php echo htmlspecialchars($jadwal['nama_pelanggan']); ?>"
                                                    data-alamat="<?php echo htmlspecialchars($jadwal['alamat']); ?>">
                                                <?php echo htmlspecialchars($jadwal['nama_pelanggan']); ?> - <?php echo htmlspecialchars($jadwal['alamat']); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Masalah</label>
                                        <input type="text" class="form-control" id="masalahInput" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Barang yang Digunakan</label>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <select class="form-select" id="barangSelect">
                                                    <option value="">-- Pilih Barang --</option>
                                                    <option value="Kabel Listrik">Kabel Listrik</option>
                                                    <option value="Stop Kontak">Stop Kontak</option>
                                                    <option value="Saklar">Saklar</option>
                                                    <option value="Lampu LED">Lampu LED</option>
                                                    <option value="MCB">MCB</option>
                                                    <option value="Fitting Lampu">Fitting Lampu</option>
                                                    <option value="Isolasi">Isolasi</option>
                                                    <option value="Klem Kabel">Klem Kabel</option>
                                                    <option value="Pipa PVC">Pipa PVC</option>
                                                    <option value="Terminal">Terminal</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <input type="number" class="form-control" id="jumlahBarang" placeholder="Jumlah" min="1">
                                            </div>
                                            <div class="col-3">
                                                <select class="form-select" id="satuanBarang">
                                                    <option value="pcs">pcs</option>
                                                    <option value="meter">meter</option>
                                                    <option value="roll">roll</option>
                                                    <option value="buah">buah</option>
                                                    <option value="set">set</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="tambahBarangBtn">
                                            <i class="bi bi-plus-circle"></i> Tambah Barang
                                        </button>
                                        
                                        <div id="daftarBarang" class="mb-3">
                                            <!-- Daftar barang yang ditambahkan akan muncul di sini -->
                                        </div>
                                        
                                        <textarea class="form-control" name="barang_digunakan" id="barangTextarea" rows="4" readonly style="background-color: #f8f9fa;" required></textarea>
                                        <div class="form-text">Gunakan dropdown di atas untuk menambah barang</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Foto Sebelum -->
                                    <div class="mb-4">
                                        <label class="form-label">Foto Sebelum Perbaikan</label>
                                        <div class="mb-2">
                                            <video id="videoSebelum" width="320" height="240" autoplay class="border rounded"></video>
                                            <canvas id="canvasSebelum" width="320" height="240" style="display: none;"></canvas>
                                        </div>
                                        <button type="button" id="captureSebelumBtn" class="btn btn-primary btn-sm me-2">
                                            <i class="bi bi-camera"></i> Ambil Foto Sebelum
                                        </button>
                                        <button type="button" id="retakeSebelumBtn" class="btn btn-secondary btn-sm" style="display: none;">
                                            <i class="bi bi-arrow-clockwise"></i> Foto Ulang
                                        </button>
                                    </div>
                                    
                                    <!-- Foto Sesudah -->
                                    <div class="mb-4">
                                        <label class="form-label">Foto Sesudah Perbaikan</label>
                                        <div class="mb-2">
                                            <video id="videoSesudah" width="320" height="240" autoplay class="border rounded"></video>
                                            <canvas id="canvasSesudah" width="320" height="240" style="display: none;"></canvas>
                                        </div>
                                        <button type="button" id="captureSesudahBtn" class="btn btn-primary btn-sm me-2">
                                            <i class="bi bi-camera"></i> Ambil Foto Sesudah
                                        </button>
                                        <button type="button" id="retakeSesudahBtn" class="btn btn-secondary btn-sm" style="display: none;">
                                            <i class="bi bi-arrow-clockwise"></i> Foto Ulang
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="foto_sebelum_base64" name="foto_sebelum_base64">
                            <input type="hidden" id="foto_sesudah_base64" name="foto_sesudah_base64">
                            
                            <div class="d-grid">
                                <button type="submit" name="submit_laporan" id="submitBtn" class="btn btn-success btn-lg" disabled>
                                    <i class="bi bi-check-circle"></i> Simpan Laporan & Selesaikan Perbaikan
                                </button>
                            </div>
                        </form>
                        
                        <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                            <h5>Tidak ada jadwal yang sedang dikerjakan</h5>
                            <p>Ambil jadwal perbaikan terlebih dahulu di menu Jadwal Perbaikan</p>
                            <a href="jadwal.php" class="btn btn-primary">
                                <i class="bi bi-calendar-event"></i> Lihat Jadwal
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const jadwalSelect = document.getElementById('jadwalSelect');
        const masalahInput = document.getElementById('masalahInput');
        const submitBtn = document.getElementById('submitBtn');
        
        // Video elements
        const videoSebelum = document.getElementById('videoSebelum');
        const canvasSebelum = document.getElementById('canvasSebelum');
        const captureSebelumBtn = document.getElementById('captureSebelumBtn');
        const retakeSebelumBtn = document.getElementById('retakeSebelumBtn');
        
        const videoSesudah = document.getElementById('videoSesudah');
        const canvasSesudah = document.getElementById('canvasSesudah');
        const captureSesudahBtn = document.getElementById('captureSesudahBtn');
        const retakeSesudahBtn = document.getElementById('retakeSesudahBtn');
        
        // Hidden inputs
        const fotoSebelumInput = document.getElementById('foto_sebelum_base64');
        const fotoSesudahInput = document.getElementById('foto_sesudah_base64');
        
        let streamSebelum = null;
        let streamSesudah = null;
        let fotoSebelumTaken = false;
        let fotoSesudahTaken = false;
        
        // Update masalah saat jadwal dipilih
        jadwalSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                masalahInput.value = selectedOption.dataset.masalah;
            } else {
                masalahInput.value = '';
            }
            checkFormReady();
        });
        
        // Akses kamera
        async function startCameras() {
            try {
                streamSebelum = await navigator.mediaDevices.getUserMedia({ video: true });
                videoSebelum.srcObject = streamSebelum;
                
                streamSesudah = await navigator.mediaDevices.getUserMedia({ video: true });
                videoSesudah.srcObject = streamSesudah;
            } catch (err) {
                alert('Tidak dapat mengakses kamera: ' + err.message);
            }
        }
        
        // Foto sebelum
        captureSebelumBtn.addEventListener('click', function() {
            const context = canvasSebelum.getContext('2d');
            context.drawImage(videoSebelum, 0, 0, 320, 240);
            
            const dataURL = canvasSebelum.toDataURL('image/jpeg');
            fotoSebelumInput.value = dataURL;
            
            videoSebelum.style.display = 'none';
            canvasSebelum.style.display = 'block';
            captureSebelumBtn.style.display = 'none';
            retakeSebelumBtn.style.display = 'inline-block';
            
            fotoSebelumTaken = true;
            checkFormReady();
        });
        
        retakeSebelumBtn.addEventListener('click', function() {
            videoSebelum.style.display = 'block';
            canvasSebelum.style.display = 'none';
            captureSebelumBtn.style.display = 'inline-block';
            retakeSebelumBtn.style.display = 'none';
            
            fotoSebelumInput.value = '';
            fotoSebelumTaken = false;
            checkFormReady();
        });
        
        // Foto sesudah
        captureSesudahBtn.addEventListener('click', function() {
            const context = canvasSesudah.getContext('2d');
            context.drawImage(videoSesudah, 0, 0, 320, 240);
            
            const dataURL = canvasSesudah.toDataURL('image/jpeg');
            fotoSesudahInput.value = dataURL;
            
            videoSesudah.style.display = 'none';
            canvasSesudah.style.display = 'block';
            captureSesudahBtn.style.display = 'none';
            retakeSesudahBtn.style.display = 'inline-block';
            
            fotoSesudahTaken = true;
            checkFormReady();
        });
        
        retakeSesudahBtn.addEventListener('click', function() {
            videoSesudah.style.display = 'block';
            canvasSesudah.style.display = 'none';
            captureSesudahBtn.style.display = 'inline-block';
            retakeSesudahBtn.style.display = 'none';
            
            fotoSesudahInput.value = '';
            fotoSesudahTaken = false;
            checkFormReady();
        });
        
        // Cek apakah form siap submit
        function checkFormReady() {
            const jadwalSelected = jadwalSelect.value !== '';
            const barangFilled = document.querySelector('textarea[name="barang_digunakan"]').value.trim() !== '';
            
            if (jadwalSelected && barangFilled && fotoSebelumTaken && fotoSesudahTaken) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        // Barang management
        const barangSelect = document.getElementById('barangSelect');
        const jumlahBarang = document.getElementById('jumlahBarang');
        const satuanBarang = document.getElementById('satuanBarang');
        const tambahBarangBtn = document.getElementById('tambahBarangBtn');
        const daftarBarang = document.getElementById('daftarBarang');
        const barangTextarea = document.getElementById('barangTextarea');
        
        let daftarBarangArray = [];
        
        // Tambah barang
        tambahBarangBtn.addEventListener('click', function() {
            const namaBarang = barangSelect.value;
            const jumlah = jumlahBarang.value;
            const satuan = satuanBarang.value;
            
            if (namaBarang && jumlah && jumlah > 0) {
                const barangItem = {
                    nama: namaBarang,
                    jumlah: jumlah,
                    satuan: satuan
                };
                
                daftarBarangArray.push(barangItem);
                updateDaftarBarang();
                
                // Reset form
                barangSelect.value = '';
                jumlahBarang.value = '';
                satuanBarang.value = 'pcs';
            } else {
                alert('Pilih barang dan masukkan jumlah yang valid');
            }
        });
        
        // Update tampilan daftar barang
        function updateDaftarBarang() {
            let html = '';
            daftarBarangArray.forEach((item, index) => {
                html += `
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-2">
                        <span>${item.nama} - ${item.jumlah} ${item.satuan}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="hapusBarang(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            });
            
            daftarBarang.innerHTML = html;
            
            // Update textarea
            const textBarang = daftarBarangArray.map(item => `${item.nama} - ${item.jumlah} ${item.satuan}`).join('\n');
            barangTextarea.value = textBarang;
            
            checkFormReady();
        }
        
        // Hapus barang
        window.hapusBarang = function(index) {
            daftarBarangArray.splice(index, 1);
            updateDaftarBarang();
        };
        
        // Monitor barang textarea
        function checkFormReady() {
            const jadwalSelected = jadwalSelect.value !== '';
            const barangFilled = barangTextarea.value.trim() !== '';
            
            if (jadwalSelected && barangFilled && fotoSebelumTaken && fotoSesudahTaken) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        // Inisialisasi
        if (document.getElementById('laporanForm')) {
            startCameras();
        }
        
        // Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function() {
            if (streamSebelum) {
                streamSebelum.getTracks().forEach(track => track.stop());
            }
            if (streamSesudah) {
                streamSesudah.getTracks().forEach(track => track.stop());
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c048044197e9cd',t:'MTc1NDY2OTQwMC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
