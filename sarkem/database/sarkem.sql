-- =========================================================
--  SARKEM - SATU FILE SQL
-- =========================================================

-- 1. TABEL USERS ------------------------------------------------
CREATE TABLE users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  no_wa       VARCHAR(20)  NOT NULL,
  password    VARCHAR(255) NOT NULL,
  role        ENUM('owner','admin','teknisi','user') NOT NULL,
  foto_profil VARCHAR(255) DEFAULT NULL,
  id_cabang   INT DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (nama,no_wa,password,role,id_cabang) VALUES
('Admin Utama','081234567890','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',NULL),
('Owner','081111111111','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','owner',NULL),
('Teknisi 1','082222222222','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teknisi',1);

-- 2. TABEL CABANG ------------------------------------------------
CREATE TABLE cabang (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nama_cabang VARCHAR(100) NOT NULL,
  alamat     TEXT,
  logo       VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO cabang (nama_cabang,alamat) VALUES
('Cabang Jakarta','Jl. Sudirman No 1'),
('Cabang Bandung','Jl. Asia Afrika No 2');

-- 3. TABEL PELANGGAN --------------------------------------------
CREATE TABLE pelanggan (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nama      VARCHAR(100) NOT NULL,
  alamat    TEXT,
  no_telp   VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO pelanggan (nama,alamat,no_telp) VALUES
('Budi Santoso','Jl. Merdeka 10','081'),('Ani Lestari','Jl. Melati 5','082');

-- 4. TABEL BARANG ------------------------------------------------
CREATE TABLE barang (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama_barang VARCHAR(100) NOT NULL,
  stok        INT DEFAULT 0,
  satuan      ENUM('pcs','set','pack','hari') DEFAULT 'pcs',
  harga       DECIMAL(10,2) DEFAULT 0,
  foto_barang VARCHAR(255) DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO barang (nama_barang,stok,satuan,harga) VALUES
('Kabel 5 m',100,'pcs',15000),('Paket PCB',50,'set',250000);

-- 5. TABEL ABSENSI ----------------------------------------------
CREATE TABLE absensi (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  id_teknisi   INT NOT NULL,
  tanggal      DATE NOT NULL,
  jam_masuk    TIME NOT NULL,
  lokasi_masuk VARCHAR(255),
  foto_masuk   VARCHAR(255),
  status       ENUM('hadir','terlambat','izin') DEFAULT 'hadir',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_teknisi) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. TABEL KASBON ----------------------------------------------
CREATE TABLE kasbon (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  id_teknisi INT NOT NULL,
  jumlah     DECIMAL(10,2) NOT NULL,
  keterangan TEXT,
  tanggal    DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_teknisi) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. TABEL JADWAL_PERBAIKAN ------------------------------------
CREATE TABLE jadwal_perbaikan (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  id_teknisi       INT NOT NULL,
  id_pelanggan     INT NOT NULL,
  tgl_perbaikan    DATE NOT NULL,
  lokasi           TEXT,
  masalah          TEXT,
  barang_digunakan TEXT,
  status           ENUM('belum','proses','selesai') DEFAULT 'belum',
  foto_sebelum     VARCHAR(255),
  foto_sesudah     VARCHAR(255),
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_teknisi)   REFERENCES users(id)     ON DELETE CASCADE,
  FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE CASCADE
);

-- 8. TABEL TAGIHAN_PELANGGAN -----------------------------------
CREATE TABLE tagihan_pelanggan (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  id_pelanggan INT NOT NULL,
  id_teknisi   INT NOT NULL,
  id_jadwal    INT NOT NULL,
  jumlah       DECIMAL(10,2) NOT NULL,
  status       ENUM('belum','lunas') DEFAULT 'belum',
  tgl_tagihan  DATE NOT NULL,
  created_by   ENUM('teknisi','admin') DEFAULT 'teknisi',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id)            ON DELETE CASCADE,
  FOREIGN KEY (id_teknisi)   REFERENCES users(id)                ON DELETE CASCADE,
  FOREIGN KEY (id_jadwal)    REFERENCES jadwal_perbaikan(id)     ON DELETE CASCADE
);

-- 9. TABEL SETTINGS (key-value) --------------------------------
CREATE TABLE settings (
  kunci VARCHAR(50) PRIMARY KEY,
  nilai TEXT
);

INSERT INTO settings (kunci,nilai) VALUES
('nama_perusahaan','PT SARKEM Jaya'),
('logo',''),
('gaji_pokok','2500000'),
('jam_masuk','08:00'),
('toleransi_menit','15');