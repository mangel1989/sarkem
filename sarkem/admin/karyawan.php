
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
if (!file_exists('../uploads/profil/')) {
    mkdir('../uploads/profil/', 0777, true);
}

// Proses form
if ($_POST) {
    if (isset($_POST['tambah_karyawan'])) {
        $nama = $_POST['nama'];
        $no_wa = $_POST['no_wa'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $id_cabang = $_POST['id_cabang'];
        $foto_profil = '';
        
        // Handle upload foto
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto_profil']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $foto_profil = 'profil_' . time() . '.' . $filetype;
                move_uploaded_file($_FILES['foto_profil']['tmp_name'], '../uploads/profil/' . $foto_profil);
            }
        }
        
        $username = $_POST['username'];
        
        $stmt = $conn->prepare("INSERT INTO users (nama, username, no_wa, password, role, id_cabang, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nama, $username, $no_wa, $password, $role, $id_cabang, $foto_profil);
        
        if ($stmt->execute()) {
            $message = 'Karyawan berhasil ditambahkan';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan karyawan';
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['edit_karyawan'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        $no_wa = $_POST['no_wa'];
        $role = $_POST['role'];
        $id_cabang = $_POST['id_cabang'];
        
        // Get current data
        $stmt = $conn->prepare("SELECT foto_profil FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_data = $result->fetch_assoc();
        $foto_profil = $current_data['foto_profil'];
        
        // Handle upload foto baru
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto_profil']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Hapus foto lama
                if ($foto_profil && file_exists('../uploads/profil/' . $foto_profil)) {
                    unlink('../uploads/profil/' . $foto_profil);
                }
                
                $foto_profil = 'profil_' . time() . '.' . $filetype;
                move_uploaded_file($_FILES['foto_profil']['tmp_name'], '../uploads/profil/' . $foto_profil);
            }
        }
        
        // Update dengan atau tanpa password
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, no_wa = ?, password = ?, role = ?, id_cabang = ?, foto_profil = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $nama, $username, $no_wa, $password, $role, $id_cabang, $foto_profil, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, no_wa = ?, role = ?, id_cabang = ?, foto_profil = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $nama, $username, $no_wa, $role, $id_cabang, $foto_profil, $id);
        }
        
        if ($stmt->execute()) {
            $message = 'Karyawan berhasil diupdate';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate karyawan';
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['reset_password'])) {
        $id = $_POST['id'];
        $new_password = password_hash('123456', PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $id);
        
        if ($stmt->execute()) {
            $message = 'Password berhasil direset ke "123456"';
            $message_type = 'success';
        } else {
            $message = 'Gagal mereset password';
            $message_type = 'danger';
        }
    }
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Get foto untuk dihapus
    $stmt = $conn->prepare("SELECT foto_profil FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Hapus file foto
        if ($data['foto_profil'] && file_exists('../uploads/profil/' . $data['foto_profil'])) {
            unlink('../uploads/profil/' . $data['foto_profil']);
        }
        
        $message = 'Karyawan berhasil dihapus';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus karyawan';
        $message_type = 'danger';
    }
}

// Query data karyawan dengan join cabang
$karyawan_list = $conn->query("
    SELECT u.*, c.nama_cabang 
    FROM users u 
    LEFT JOIN cabang c ON u.id_cabang = c.id 
    ORDER BY u.nama ASC
");

// Query cabang untuk dropdown
$cabang_list = $conn->query("SELECT * FROM cabang ORDER BY nama_cabang ASC");
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - SARKEM</title>
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
                <a class="nav-link text-dark mb-1" href="cabang.php">
                    <i class="bi bi-building me-2"></i>Data Cabang
                </a>
                <a class="nav-link active bg-primary text-white rounded mb-1" href="karyawan.php">
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
                    <h1 class="h3">Data Karyawan</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Karyawan
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
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>No. WhatsApp</th>
                                        <th>Role</th>
                                        <th>Cabang</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($karyawan_list->num_rows > 0): ?>
                                        <?php while ($karyawan = $karyawan_list->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $karyawan['id']; ?></td>
                                            <td>
                                                <?php if ($karyawan['foto_profil']): ?>
                                                <img src="../uploads/profil/<?php echo $karyawan['foto_profil']; ?>" alt="Foto" width="40" height="40" class="rounded-circle">
                                                <?php else: ?>
                                                <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($karyawan['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($karyawan['username']); ?></td>
                                            <td><?php echo htmlspecialchars($karyawan['no_wa']); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = '';
                                                switch($karyawan['role']) {
                                                    case 'owner': $badge_class = 'bg-danger'; break;
                                                    case 'admin': $badge_class = 'bg-primary'; break;
                                                    case 'teknisi': $badge_class = 'bg-success'; break;
                                                    case 'user': $badge_class = 'bg-secondary'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($karyawan['role']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($karyawan['nama_cabang'] ?? '-'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm me-1" 
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        onclick="editKaryawan(<?php echo $karyawan['id']; ?>, '<?php echo htmlspecialchars($karyawan['nama']); ?>', '<?php echo htmlspecialchars($karyawan['username']); ?>', '<?php echo htmlspecialchars($karyawan['no_wa']); ?>', '<?php echo $karyawan['role']; ?>', '<?php echo $karyawan['id_cabang']; ?>', '<?php echo $karyawan['foto_profil']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm me-1" 
                                                        data-bs-toggle="modal" data-bs-target="#resetModal"
                                                        onclick="resetPassword(<?php echo $karyawan['id']; ?>, '<?php echo htmlspecialchars($karyawan['nama']); ?>')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#hapusModal"
                                                        onclick="hapusKaryawan(<?php echo $karyawan['id']; ?>, '<?php echo htmlspecialchars($karyawan['nama']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                                Belum ada data karyawan
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
                        <h5 class="modal-title">Tambah Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" class="form-control" name="no_wa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="teknisi">Teknisi</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cabang</label>
                            <select class="form-select" name="id_cabang" required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php 
                                $cabang_list->data_seek(0);
                                while ($cabang = $cabang_list->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama_cabang']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Profil</label>
                            <input type="file" class="form-control" name="foto_profil" accept="image/*" onchange="previewFoto(this, 'previewTambah')">
                            <div class="mt-2">
                                <img id="previewTambah" src="" alt="Preview" width="100" height="100" class="border rounded" style="display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_karyawan" class="btn btn-primary">Simpan</button>
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
                        <h5 class="modal-title">Edit Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" id="editNama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" class="form-control" name="no_wa" id="editNoWa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="editPassword">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="editRole" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="teknisi">Teknisi</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cabang</label>
                            <select class="form-select" name="id_cabang" id="editCabang" required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php 
                                $cabang_list->data_seek(0);
                                while ($cabang = $cabang_list->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $cabang['id']; ?>"><?php echo htmlspecialchars($cabang['nama_cabang']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Profil</label>
                            <input type="file" class="form-control" name="foto_profil" accept="image/*" onchange="previewFoto(this, 'previewEdit')">
                            <div class="mt-2">
                                <img id="previewEdit" src="" alt="Preview" width="100" height="100" class="border rounded">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_karyawan" class="btn btn-warning">Update</button>
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
                    <p>Apakah Anda yakin ingin menghapus karyawan <strong id="hapusNama"></strong>?</p>
                    <p class="text-danger"><small>Data yang sudah dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="hapusLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Reset Password -->
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="resetId">
                        <p>Apakah Anda yakin ingin mereset password untuk <strong id="resetNama"></strong>?</p>
                        <p class="text-info"><small>Password akan direset menjadi: <strong>123456</strong></small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="reset_password" class="btn btn-info">Reset Password</button>
                    </div>
                </form>
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
        
        function editKaryawan(id, nama, username, no_wa, role, id_cabang, foto_profil) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editUsername').value = username;
            document.getElementById('editNoWa').value = no_wa;
            document.getElementById('editRole').value = role;
            document.getElementById('editCabang').value = id_cabang;
            
            const preview = document.getElementById('previewEdit');
            if (foto_profil) {
                preview.src = '../uploads/profil/' + foto_profil;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
        
        function resetPassword(id, nama) {
            document.getElementById('resetId').value = id;
            document.getElementById('resetNama').textContent = nama;
        }
        
        function hapusKaryawan(id, nama) {
            document.getElementById('hapusNama').textContent = nama;
            document.getElementById('hapusLink').href = '?hapus=' + id;
        }
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c05e44b2f6e9d1',t:'MTc1NDY3MDMxMS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
