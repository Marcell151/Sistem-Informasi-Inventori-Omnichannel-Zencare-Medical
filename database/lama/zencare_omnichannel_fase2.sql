-- ==============================================================================
-- SISTEM INFORMASI MANAJEMEN INVENTARIS & E-COMMERCE OMNICHANNEL ZENCARE MEDICAL
-- FASE 2: Revisi Total Arsitektur + Data Dummy Detail (Sesuai Bab 1-3 Tugas Akhir)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. DROP EXISTING TABLES
DROP TABLE IF EXISTS stok_batch;
DROP TABLE IF EXISTS unit_serial;
DROP TABLE IF EXISTS gudang_karantina;
DROP TABLE IF EXISTS kartu_stok;
DROP TABLE IF EXISTS mutasi_stok;
DROP TABLE IF EXISTS detail_penjualan;
DROP TABLE IF EXISTS penjualan;
DROP TABLE IF EXISTS pengaturan_api;
DROP TABLE IF EXISTS pengaturan_web;
DROP TABLE IF EXISTS stok_cabang;
DROP TABLE IF EXISTS produk_variasi;
DROP TABLE IF EXISTS produk_induk;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS cabang;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 2. DDL: STRUCTURE CREATION
-- ==============================================================================

CREATE TABLE cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    kota_id INT NOT NULL COMMENT 'ID Kota RajaOngkir',
    is_active BOOLEAN DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'karyawan', 'pelanggan') NOT NULL,
    id_cabang INT NULL,
    telepon VARCHAR(20) NULL,
    alamat TEXT NULL,
    kota_id INT NULL,
    lat DECIMAL(10,8) NULL,
    lng DECIMAL(11,8) NULL,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kontak VARCHAR(50) NULL,
    alamat TEXT NULL,
    is_active BOOLEAN DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk_induk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku_induk VARCHAR(50) NOT NULL UNIQUE,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    kategori ENUM('Obat', 'Alat Kesehatan') NOT NULL,
    id_supplier INT NULL,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk_variasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk_induk INT NULL,
    sku_variasi VARCHAR(50) NOT NULL UNIQUE,
    nama_variasi VARCHAR(100) NOT NULL,
    satuan_kecil VARCHAR(50) NOT NULL DEFAULT 'Pcs',
    satuan_besar VARCHAR(50) NOT NULL DEFAULT 'Box',
    rasio_konversi INT NOT NULL DEFAULT 1 COMMENT 'Obat > 1. Alkes = 1',
    harga_jual_kecil DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_jual_besar DECIMAL(12,2) NOT NULL DEFAULT 0,
    berat INT NOT NULL DEFAULT 100,
    gambar VARCHAR(255) NULL,
    tampil_di_online BOOLEAN DEFAULT 1,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_produk_induk) REFERENCES produk_induk(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stok_cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    id_cabang INT NOT NULL,
    stok INT NOT NULL DEFAULT 0 COMMENT 'Total kumulatif terkecil (Pcs/Strip)',
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE CASCADE,
    UNIQUE KEY uq_variasi_cabang (id_variasi, id_cabang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stok_batch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    id_cabang INT NOT NULL,
    no_batch VARCHAR(50) NOT NULL,
    tgl_exp DATE NOT NULL,
    stok_sisa INT NOT NULL DEFAULT 0 COMMENT 'Dalam satuan terkecil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE unit_serial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    id_cabang INT NOT NULL,
    serial_number VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('Tersedia', 'Terjual', 'Retur/Rusak') NOT NULL DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pengaturan_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('midtrans', 'rajaongkir') NOT NULL,
    api_key VARCHAR(255) NULL,
    api_secret VARCHAR(255) NULL,
    webhook_url VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pengaturan_web (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    telepon VARCHAR(20) NULL,
    alamat TEXT NULL,
    logo VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    id_cabang INT NOT NULL,
    id_user INT NULL,
    tipe_transaksi ENUM('pos', 'ecommerce') NOT NULL,
    status_pesanan ENUM('Menunggu', 'Diproses', 'Dikirim', 'Siap Diambil', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu',
    total_harga DECIMAL(12,2) NOT NULL,
    ongkir DECIMAL(10,2) DEFAULT 0,
    nama_penerima VARCHAR(100) NULL,
    telepon VARCHAR(20) NULL,
    alamat_lengkap TEXT NULL,
    kurir VARCHAR(50) NULL,
    layanan VARCHAR(50) NULL,
    snap_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id),
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_variasi INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    catatan_logistik TEXT NULL COMMENT 'Menyimpan riwayat batch/SN yang terpakai',
    FOREIGN KEY (id_penjualan) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mutasi_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    cabang_asal INT NOT NULL,
    cabang_tujuan INT NOT NULL,
    qty INT NOT NULL,
    keterangan TEXT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id),
    FOREIGN KEY (cabang_asal) REFERENCES cabang(id),
    FOREIGN KEY (cabang_tujuan) REFERENCES cabang(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kartu_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cabang INT NOT NULL,
    id_variasi INT NOT NULL,
    jenis_mutasi ENUM('Masuk', 'Keluar', 'Penyesuaian', 'Transfer') NOT NULL,
    qty INT NOT NULL,
    sisa_stok INT NOT NULL,
    keterangan TEXT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id),
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==============================================================================
-- 3. DML: DUMMY DATA INSERTS (DATA LOGISTIK RIIL)
-- ==============================================================================

-- 3.1 Insert Cabang
INSERT INTO cabang (id, nama, alamat, kota_id, is_active) VALUES 
(1, 'ZenCare Pusat Muharto', 'Jl. Muharto No.1, Malang', 256, 1),
(2, 'ZenCare Cabang Sawojajar', 'Jl. Danau Toba No.10, Malang', 256, 1);

-- 3.2 Insert Users (Password default '123456' — hash bcrypt valid)
INSERT INTO users (id, username, password, nama_lengkap, role, id_cabang, telepon, is_active) VALUES 
(1, 'superadmin', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Marcell (Pemilik)', 'super_admin', NULL, '081234567890', 1),
(2, 'admin_muharto', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Karyawan Muharto', 'karyawan', 1, '082111111111', 1),
(3, 'admin_sawojajar', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Karyawan Sawojajar', 'karyawan', 2, '082222222222', 1),
(4, 'pelanggan1', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Budi Santoso', 'pelanggan', NULL, '083333333333', 1);

-- 3.3 Insert Supplier
INSERT INTO supplier (id, nama, kontak, alamat) VALUES 
(1, 'PT Kimia Farma Trading & Distribution', '0341-111111', 'Jl. Farmasi Raya, Malang'),
(2, 'PT Omron Healthcare Indonesia', '021-222222', 'Jakarta Pusat'),
(3, 'PT Jayamas Medica Industri (OneMed)', '031-333333', 'Sidoarjo');

-- 3.4 Insert Produk Induk
INSERT INTO produk_induk (id, sku_induk, nama_produk, deskripsi, kategori, id_supplier) VALUES 
(1, 'PRD-OBT-001', 'Paracetamol Syrup Anak 60ml', 'Obat pereda demam anak', 'Obat', 1),
(2, 'PRD-OBT-002', 'Amoxicillin 500mg', 'Antibiotik resep dokter', 'Obat', 1),
(3, 'PRD-ALK-001', 'Tensimeter Digital Omron HEM-7120', 'Alat pengukur tekanan darah digital akurat', 'Alat Kesehatan', 2),
(4, 'PRD-ALK-002', 'Kursi Roda Medis Sella', 'Kursi roda standar medis rumah sakit', 'Alat Kesehatan', 3);

-- 3.5 Insert Produk Variasi
-- Obat menggunakan rasio konversi (Dual-UOM)
-- Alkes menggunakan rasio konversi 1 (Single-UOM)
INSERT INTO produk_variasi (id, id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, berat) VALUES 
(1, 1, 'OBT-001-SYR', 'Syrup 60ml (Botol)', 'Botol', 'Karton', 50, 15000, 700000, 150),
(2, 2, 'OBT-002-TAB', 'Tablet 500mg (Strip)', 'Strip', 'Box', 10, 5000, 48000, 50),
(3, 3, 'ALK-001-SET', 'Set Omron HEM-7120', 'Unit', 'Unit', 1, 450000, 450000, 800),
(4, 4, 'ALK-002-SET', 'Standard Set', 'Unit', 'Unit', 1, 1500000, 1500000, 15000);

-- 3.6 Insert Stok Cabang (Agregat Pivot)
-- Cabang 1 (Pusat Muharto)
INSERT INTO stok_cabang (id_variasi, id_cabang, stok) VALUES 
(1, 1, 100), -- 100 Botol Paracetamol (2 Karton)
(2, 1, 50),  -- 50 Strip Amoxicillin (5 Box)
(3, 1, 3),   -- 3 Unit Tensimeter Omron
(4, 1, 2);   -- 2 Unit Kursi Roda

-- Cabang 2 (Sawojajar)
INSERT INTO stok_cabang (id_variasi, id_cabang, stok) VALUES 
(1, 2, 50),  -- 50 Botol Paracetamol
(3, 2, 1);   -- 1 Unit Tensimeter

-- 3.7 Insert Data Logistik Obat Berbasis FEFO (Tabel stok_batch)
-- Paracetamol di Pusat (Total 100)
INSERT INTO stok_batch (id_variasi, id_cabang, no_batch, tgl_exp, stok_sisa) VALUES 
(1, 1, 'BATCH-PARA-001', '2026-12-31', 50),
(1, 1, 'BATCH-PARA-002', '2027-06-30', 50);

-- Paracetamol di Sawojajar (Total 50)
INSERT INTO stok_batch (id_variasi, id_cabang, no_batch, tgl_exp, stok_sisa) VALUES 
(1, 2, 'BATCH-PARA-001', '2026-12-31', 50);

-- Amoxicillin di Pusat (Total 50)
INSERT INTO stok_batch (id_variasi, id_cabang, no_batch, tgl_exp, stok_sisa) VALUES 
(2, 1, 'BATCH-AMOX-001', '2026-10-15', 20), -- Kadaluwarsa terdekat
(2, 1, 'BATCH-AMOX-002', '2027-01-20', 30);

-- 3.8 Insert Data Logistik Alkes Berbasis Serial Number (Tabel unit_serial)
-- Tensimeter Omron di Pusat (Total 3)
INSERT INTO unit_serial (id_variasi, id_cabang, serial_number, status) VALUES 
(3, 1, 'OMR-7120-A001', 'Tersedia'),
(3, 1, 'OMR-7120-A002', 'Tersedia'),
(3, 1, 'OMR-7120-A003', 'Tersedia');

-- Tensimeter Omron di Sawojajar (Total 1)
INSERT INTO unit_serial (id_variasi, id_cabang, serial_number, status) VALUES 
(3, 2, 'OMR-7120-B001', 'Tersedia');

-- Kursi Roda di Pusat (Total 2)
INSERT INTO unit_serial (id_variasi, id_cabang, serial_number, status) VALUES 
(4, 1, 'SEL-WC-001', 'Tersedia'),
(4, 1, 'SEL-WC-002', 'Tersedia');

-- 3.9 Insert Riwayat Kartu Stok (Audit Trail Penerimaan Awal)
INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan, tanggal) VALUES 
(1, 1, 'Masuk', 100, 100, 'Pengadaan Awal Batch BATCH-PARA-001 & BATCH-PARA-002', CURRENT_TIMESTAMP),
(1, 2, 'Masuk', 50, 50, 'Pengadaan Awal Batch BATCH-AMOX-001 & BATCH-AMOX-002', CURRENT_TIMESTAMP),
(1, 3, 'Masuk', 3, 3, 'Pengadaan Awal SN: OMR-7120-A001, A002, A003', CURRENT_TIMESTAMP),
(1, 4, 'Masuk', 2, 2, 'Pengadaan Awal SN: SEL-WC-001, SEL-WC-002', CURRENT_TIMESTAMP),
(2, 1, 'Masuk', 50, 50, 'Pengadaan Awal Batch BATCH-PARA-001', CURRENT_TIMESTAMP),
(2, 3, 'Masuk', 1, 1, 'Pengadaan Awal SN: OMR-7120-B001', CURRENT_TIMESTAMP);

-- 3.10 Pengaturan Ekosistem API & Web
INSERT INTO pengaturan_web (nama_toko, deskripsi, telepon, alamat) VALUES 
('ZenCare Medical', 'Solusi Obat & Alat Kesehatan Terpercaya', '081234567890', 'Jl. Merdeka No.1, Malang');

INSERT INTO pengaturan_api (platform, api_key, is_active) VALUES 
('midtrans', 'SB-Mid-server-xxxx', 1),
('rajaongkir', 'xxx-rajaongkir-key', 1);