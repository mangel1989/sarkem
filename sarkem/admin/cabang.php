
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include '../config.php';
$admin = $_SESSION['user'];

$message = '';
$message_type = '';

// Proses form
if ($_POST) {
    if (isset($_POST['tambah_cabang'])) {
        $nama_cabang = $_POST['nama_cabang'];
        $alamat = $_POST['alamat'];
        
        $stmt = $conn->prepare("INSERT INTO cabang (nama_cabang, alamat) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama_cabang, $alamat);
        
        if ($stmt->execute()) {
            $message = 'Cabang berhasil ditambahkan';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan cabang';
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['edit_cabang'])) {
        $id = $_POST['id'];
        $nama_cabang = $_POST['nama_cabang'];
        $alamat = $_POST['alamat'];
        
        $stmt = $conn->prepare("UPDATE cabang SET nama_cabang = ?, alamat = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama_cabang, $alamat, $id);
        
        if ($stmt->execute()) {
            $message = 'Cabang berhasil diupdate';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate cabang';
            $message_type = 'danger';
        }
    }
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    $stmt = $conn->prepare("DELETE FROM cabang WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = 'Cabang berhasil dihapus';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus cabang';
        $message_type = 'danger';
    }
}

// Query data cabang
$cabang_list = $conn->query("SELECT * FROM cabang ORDER BY nama_cabang ASC");
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Cabang - SARKEM</title>
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
                
                <span class="text-white me-3"><?php echo htmlspecialchars($admin['nama']); ?></span>
                
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
                <a class="nav-link active bg-primary text-white rounded mb-1" href="cabang.php">
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
                    <h1 class="h3">Data Cabang</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Cabang
                    </button>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Cabang</th>
                                        <th>Alamat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($cabang_list->num_rows > 0): ?>
                                        <?php while ($cabang = $cabang_list->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $cabang['id']; ?></td>
                                            <td><?php echo htmlspecialchars($cabang['nama_cabang']); ?></td>
                                            <td><?php echo htmlspecialchars($cabang['alamat']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm me-1" 
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        onclick="editCabang(<?php echo $cabang['id']; ?>, '<?php echo htmlspecialchars($cabang['nama_cabang']); ?>', '<?php echo htmlspecialchars($cabang['alamat']); ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#hapusModal"
                                                        onclick="hapusCabang(<?php echo $cabang['id']; ?>, '<?php echo htmlspecialchars($cabang['nama_cabang']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-building fs-1 d-block mb-2"></i>
                                                Belum ada data cabang
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
    
    <!-- Modal Tambah -->
    <div class="modal fade" id="tambahModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Cabang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Cabang</label>
                            <input type="text" class="form-control" name="nama_cabang" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_cabang" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Cabang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Nama Cabang</label>
                            <input type="text" class="form-control" name="nama_cabang" id="editNama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" id="editAlamat" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_cabang" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Hapus -->
    <div class="modal fade" id="hapusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus cabang <strong id="hapusNama"></strong>?</p>
                    <p class="text-danger"><small>Data yang sudah dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="hapusLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editCabang(id, nama, alamat) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editAlamat').value = alamat;
        }
        
        function hapusCabang(id, nama) {
            document.getElementById('hapusNama').textContent = nama;
            document.getElementById('hapusLink').href = '?hapus=' + id;
        }
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c05369c7c2e9cd',t:'MTc1NDY2OTg2Ni4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
