
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
        $where_conditions[] = "k.user_id = ?";
        $params[] = $filter_teknisi;
        $param_types .= 'i';
    }
    
    if (!empty($_POST['bulan'])) {
        $filter_bulan = $_POST['bulan'];
        $where_conditions[] = "DATE_FORMAT(k.tanggal, '%Y-%m') = ?";
        $params[] = $filter_bulan;
        $param_types .= 's';
    }
}

// Proses form
if ($_POST) {
    if (isset($_POST['tambah_kasbon'])) {
        $user_id = $_POST['user_id'];
        $jumlah = $_POST['jumlah'];
        $keterangan = $_POST['keterangan'];
        $tanggal = $_POST['tanggal'];
        
        $stmt = $conn->prepare("INSERT INTO kasbon (user_id, jumlah, keterangan, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $jumlah, $keterangan, $tanggal);
        
        if ($stmt->execute()) {
            $message = 'Kasbon berhasil ditambahkan';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan kasbon';
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['edit_kasbon'])) {
        $id = $_POST['id'];
        $user_id = $_POST['user_id'];
        $jumlah = $_POST['jumlah'];
        $keterangan = $_POST['keterangan'];
        $tanggal = $_POST['tanggal'];
        
        $stmt = $conn->prepare("UPDATE kasbon SET user_id = ?, jumlah = ?, keterangan = ?, tanggal = ? WHERE id = ?");
        $stmt->bind_param("idssi", $user_id, $jumlah, $keterangan, $tanggal, $id);
        
        if ($stmt->execute()) {
            $message = 'Kasbon berhasil diupdate';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate kasbon';
            $message_type = 'danger';
        }
    }
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    $stmt = $conn->prepare("DELETE FROM kasbon WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = 'Kasbon berhasil dihapus';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus kasbon';
        $message_type = 'danger';
    }
}

// Build WHERE clause for main query
$where_clause = implode(' AND ', $where_conditions);

// Query data kasbon dengan join users
$main_query = "SELECT k.*, u.nama as nama_teknisi 
               FROM kasbon k 
               JOIN users u ON k.user_id = u.id 
               WHERE $where_clause 
               ORDER BY k.tanggal DESC, k.id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($main_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $kasbon_list = $stmt->get_result();
} else {
    $kasbon_list = $conn->query($main_query);
}

// Get teknisi list for dropdown
$teknisi_list = $conn->query("SELECT id, nama FROM users WHERE role = 'teknisi' ORDER BY nama ASC");
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kasbon Teknisi - SARKEM</title>
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
                <a class="nav-link active bg-primary text-white rounded mb-1" href="kasbon.php">
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
                    <h1 class="h3">Rekap Kasbon Teknisi</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Kasbon
                    </button>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Filter Form -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="POST" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Teknisi</label>
                                <select class="form-select" name="teknisi">
                                    <option value="">-- Semua Teknisi --</option>
                                    <?php 
                                    $teknisi_list->data_seek(0);
                                    while ($teknisi = $teknisi_list->fetch_assoc()): 
                                    ?>
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
                                <a href="kasbon.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Teknisi</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($kasbon_list->num_rows > 0): ?>
                                        <?php 
                                        $total_kasbon = 0;
                                        while ($kasbon = $kasbon_list->fetch_assoc()): 
                                            $total_kasbon += $kasbon['jumlah'];
                                        ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $tanggal = new DateTime($kasbon['tanggal']);
                                                echo $tanggal->format('d/m/Y'); 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($kasbon['nama_teknisi']); ?></td>
                                            <td>
                                                <strong class="text-success">Rp <?php echo number_format($kasbon['jumlah'], 0, ',', '.'); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($kasbon['keterangan']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm me-1" 
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        onclick="editKasbon(<?php echo $kasbon['id']; ?>, <?php echo $kasbon['user_id']; ?>, <?php echo $kasbon['jumlah']; ?>, '<?php echo htmlspecialchars($kasbon['keterangan']); ?>', '<?php echo $kasbon['tanggal']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#hapusModal"
                                                        onclick="hapusKasbon(<?php echo $kasbon['id']; ?>, '<?php echo htmlspecialchars($kasbon['nama_teknisi']); ?>', <?php echo $kasbon['jumlah']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-info">
                                            <td colspan="2"><strong>Total Kasbon</strong></td>
                                            <td><strong class="text-primary">Rp <?php echo number_format($total_kasbon, 0, ',', '.'); ?></strong></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                                                <?php if ($filter_teknisi || $filter_bulan): ?>
                                                    Tidak ada data kasbon sesuai filter
                                                <?php else: ?>
                                                    Belum ada data kasbon
                                                <?php endif; ?>
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
                        <h5 class="modal-title">Tambah Kasbon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teknisi</label>
                            <select class="form-select" name="user_id" required>
                                <option value="">-- Pilih Teknisi --</option>
                                <?php 
                                $teknisi_list->data_seek(0);
                                while ($teknisi = $teknisi_list->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $teknisi['id']; ?>"><?php echo htmlspecialchars($teknisi['nama']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" min="0" step="1000" required>
                            <div class="form-text">Masukkan jumlah kasbon dalam Rupiah</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3" required placeholder="Masukkan keterangan kasbon..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_kasbon" class="btn btn-primary">Simpan</button>
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
                        <h5 class="modal-title">Edit Kasbon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="editTanggal" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teknisi</label>
                            <select class="form-select" name="user_id" id="editUserId" required>
                                <option value="">-- Pilih Teknisi --</option>
                                <?php 
                                $teknisi_list->data_seek(0);
                                while ($teknisi = $teknisi_list->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $teknisi['id']; ?>"><?php echo htmlspecialchars($teknisi['nama']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" id="editJumlah" min="0" step="1000" required>
                            <div class="form-text">Masukkan jumlah kasbon dalam Rupiah</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="editKeterangan" rows="3" required placeholder="Masukkan keterangan kasbon..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_kasbon" class="btn btn-warning">Update</button>
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
                    <p>Apakah Anda yakin ingin menghapus kasbon untuk <strong id="hapusNama"></strong>?</p>
                    <p>Jumlah: <strong class="text-success" id="hapusJumlah"></strong></p>
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
        function editKasbon(id, user_id, jumlah, keterangan, tanggal) {
            document.getElementById('editId').value = id;
            document.getElementById('editTanggal').value = tanggal;
            document.getElementById('editUserId').value = user_id;
            document.getElementById('editJumlah').value = jumlah;
            document.getElementById('editKeterangan').value = keterangan;
        }
        
        function hapusKasbon(id, nama, jumlah) {
            document.getElementById('hapusNama').textContent = nama;
            document.getElementById('hapusJumlah').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(jumlah);
            document.getElementById('hapusLink').href = '?hapus=' + id;
        }
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c071b4e667e9d1',t:'MTc1NDY3MTEwNy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
