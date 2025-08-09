
<?php
session_start();

// Check if user is admin, redirect if not
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include database configuration
include '../config.php';

// Process POST requests for add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nama = $_POST['nama'];
                $alamat = $_POST['alamat'];
                $no_telp = $_POST['no_telp'];
                
                $stmt = $conn->prepare("INSERT INTO pelanggan (nama, alamat, no_telp) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nama, $alamat, $no_telp);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $nama = $_POST['nama'];
                $alamat = $_POST['alamat'];
                $no_telp = $_POST['no_telp'];
                
                $stmt = $conn->prepare("UPDATE pelanggan SET nama=?, alamat=?, no_telp=? WHERE id=?");
                $stmt->bind_param("sssi", $nama, $alamat, $no_telp, $id);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM pelanggan WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                break;
        }
        header('Location: pelanggan.php');
        exit();
    }
}

// Fetch all customers
$result = $conn->query("SELECT * FROM pelanggan ORDER BY nama ASC");
$pelanggan_list = $result->fetch_all(MYSQLI_ASSOC);

// Get edit data if editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM pelanggan WHERE id = $edit_id");
    $edit_data = $edit_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - SARKEM Admin</title>
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
            <!-- Sidebar Offcanvas -->
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
                            <a class="nav-link active" href="pelanggan.php">
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
                            <a class="nav-link" href="pengaturan.php">
                                <i class="bi bi-gear me-2"></i>
                                Pengaturan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid pt-5">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">Data Pelanggan</h1>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pelangganModal">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Pelanggan
                        </button>
                    </div>

                    <!-- Customer Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pelanggan_list)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada data pelanggan</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pelanggan_list as $pelanggan): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pelanggan['id']); ?></td>
                                            <td><?php echo htmlspecialchars($pelanggan['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($pelanggan['alamat']); ?></td>
                                            <td><?php echo htmlspecialchars($pelanggan['no_telp']); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $pelanggan['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#pelangganModal">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $pelanggan['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="pelangganModal" tabindex="-1" aria-labelledby="pelangganModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pelangganModalLabel">
                        <?php echo $edit_data ? 'Edit Pelanggan' : 'Tambah Pelanggan'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Pelanggan</label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo $edit_data ? htmlspecialchars($edit_data['alamat']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="no_telp" class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" id="no_telp" name="no_telp" 
                                   value="<?php echo $edit_data ? htmlspecialchars($edit_data['no_telp']) : ''; ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (isset($_GET['edit'])): ?>
    <script>
        // Auto-open modal only when editing
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('pelangganModal'));
            modal.show();
        });
    </script>
    <?php endif; ?>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c47527b059e9d4',t:'MTc1NDcxMzE5MS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
