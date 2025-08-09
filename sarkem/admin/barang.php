
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

// Buat folder uploads jika belum ada
if (!file_exists('../uploads/barang/')) {
    mkdir('../uploads/barang/', 0777, true);
}

// Proses form
if ($_POST) {
    if (isset($_POST['tambah_barang'])) {
        $nama_barang = $_POST['nama_barang'];
        $stok = $_POST['stok'];
        $satuan = $_POST['satuan'];
        $harga = $_POST['harga'];
        $foto_barang = '';
        
        // Handle upload foto
        if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto_barang']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $foto_barang = 'barang_' . time() . '.' . $filetype;
                move_uploaded_file($_FILES['foto_barang']['tmp_name'], '../uploads/barang/' . $foto_barang);
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO barang (nama_barang, stok, satuan, harga, foto_barang) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisds", $nama_barang, $stok, $satuan, $harga, $foto_barang);
        
        if ($stmt->execute()) {
            $message = 'Barang berhasil ditambahkan';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan barang';
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['edit_barang'])) {
        $id = $_POST['id'];
        $nama_barang = $_POST['nama_barang'];
        $stok = $_POST['stok'];
        $satuan = $_POST['satuan'];
        $harga = $_POST['harga'];
        
        // Get current data
        $stmt = $conn->prepare("SELECT foto_barang FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_data = $result->fetch_assoc();
        $foto_barang = $current_data['foto_barang'];
        
        // Handle upload foto baru
        if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto_barang']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Hapus foto lama
                if ($foto_barang && file_exists('../uploads/barang/' . $foto_barang)) {
                    unlink('../uploads/barang/' . $foto_barang);
                }
                
                $foto_barang = 'barang_' . time() . '.' . $filetype;
                move_uploaded_file($_FILES['foto_barang']['tmp_name'], '../uploads/barang/' . $foto_barang);
            }
        }
        
        $stmt = $conn->prepare("UPDATE barang SET nama_barang = ?, stok = ?, satuan = ?, harga = ?, foto_barang = ? WHERE id = ?");
        $stmt->bind_param("sisdsi", $nama_barang, $stok, $satuan, $harga, $foto_barang, $id);
        
        if ($stmt->execute()) {
            $message = 'Barang berhasil diupdate';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate barang';
            $message_type = 'danger';
        }
    }
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Get foto untuk dihapus
    $stmt = $conn->prepare("SELECT foto_barang FROM barang WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Hapus file foto
        if ($data['foto_barang'] && file_exists('../uploads/barang/' . $data['foto_barang'])) {
            unlink('../uploads/barang/' . $data['foto_barang']);
        }
        
        $message = 'Barang berhasil dihapus';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus barang';
        $message_type = 'danger';
    }
}

// Query data barang
$barang_list = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC");
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - SARKEM</title>
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
                <a class="nav-link active bg-primary text-white rounded mb-1" href="barang.php">
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
                    <h1 class="h3">Data Barang</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Barang
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
                                        <th>Foto</th>
                                        <th>Nama Barang</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($barang_list->num_rows > 0): ?>
                                        <?php while ($barang = $barang_list->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $barang['id']; ?></td>
                                            <td>
                                                <?php if ($barang['foto_barang']): ?>
                                                <img src="../uploads/barang/<?php echo $barang['foto_barang']; ?>" alt="Foto" width="40" height="40" class="rounded">
                                                <?php else: ?>
                                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-box text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($barang['nama_barang']); ?></td>
                                            <td><?php echo number_format($barang['stok']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo strtoupper($barang['satuan']); ?></span>
                                            </td>
                                            <td>Rp <?php echo number_format($barang['harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm me-1" 
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        onclick="editBarang(<?php echo $barang['id']; ?>, '<?php echo htmlspecialchars($barang['nama_barang']); ?>', <?php echo $barang['stok']; ?>, '<?php echo $barang['satuan']; ?>', <?php echo $barang['harga']; ?>, '<?php echo $barang['foto_barang']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#hapusModal"
                                                        onclick="hapusBarang(<?php echo $barang['id']; ?>, '<?php echo htmlspecialchars($barang['nama_barang']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-box fs-1 d-block mb-2"></i>
                                                Belum ada data barang
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
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" name="nama_barang" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stok" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satuan</label>
                            <select class="form-select" name="satuan" required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="pcs">PCS</option>
                                <option value="set">SET</option>
                                <option value="pack">PACK</option>
                                <option value="hari">HARI</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" name="foto_barang" accept="image/*" onchange="previewFoto(this, 'previewTambah')">
                            <div class="mt-2">
                                <img id="previewTambah" src="" alt="Preview" width="100" height="100" class="border rounded" style="display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_barang" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" name="nama_barang" id="editNamaBarang" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stok" id="editStok" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satuan</label>
                            <select class="form-select" name="satuan" id="editSatuan" required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="pcs">PCS</option>
                                <option value="set">SET</option>
                                <option value="pack">PACK</option>
                                <option value="hari">HARI</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" id="editHarga" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" name="foto_barang" accept="image/*" onchange="previewFoto(this, 'previewEdit')">
                            <div class="mt-2">
                                <img id="previewEdit" src="" alt="Preview" width="100" height="100" class="border rounded">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_barang" class="btn btn-warning">Update</button>
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
                    <p>Apakah Anda yakin ingin menghapus barang <strong id="hapusNama"></strong>?</p>
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
        function previewFoto(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function editBarang(id, nama_barang, stok, satuan, harga, foto_barang) {
            document.getElementById('editId').value = id;
            document.getElementById('editNamaBarang').value = nama_barang;
            document.getElementById('editStok').value = stok;
            document.getElementById('editSatuan').value = satuan;
            document.getElementById('editHarga').value = harga;
            
            const preview = document.getElementById('previewEdit');
            if (foto_barang) {
                preview.src = '../uploads/barang/' + foto_barang;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
        
        function hapusBarang(id, nama_barang) {
            document.getElementById('hapusNama').textContent = nama_barang;
            document.getElementById('hapusLink').href = '?hapus=' + id;
        }
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c06547f0b7e9c9',t:'MTc1NDY3MDU5OC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
