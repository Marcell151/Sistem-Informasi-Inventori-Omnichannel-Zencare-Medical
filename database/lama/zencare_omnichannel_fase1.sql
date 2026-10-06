-- ==============================================================================
-- SISTEM INFORMASI MANAJEMEN INVENTARIS & E-COMMERCE OMNICHANNEL ZENCARE MEDICAL
-- FASE 1: DDL Structure & Initial Dummy Data (phpMyAdmin Ready)
-- Engine: InnoDB | Character Set: utf8mb4 | Soft Delete: is_active
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. DROPPING EXISTING TABLES (CLEAN REBUILD)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS gudang_karantina;
DROP TABLE IF EXISTS kartu_stok;
DROP TABLE IF EXISTS stock_opname;
DROP TABLE IF EXISTS mutasi_stok;
DROP TABLE IF EXISTS detail_penjualan;
DROP TABLE IF EXISTS penjualan;
DROP TABLE IF EXISTS pengaturan_api;
DROP TABLE IF EXISTS stok_cabang;
DROP TABLE IF EXISTS produk_variasi;
DROP TABLE IF EXISTS produk_induk;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS cabang;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- 2. MASTER DATA TABLES
-- ------------------------------------------------------------------------------

-- Master Cabang
CREATE TABLE cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    kota_id INT NOT NULL COMMENT 'ID Kota RajaOngkir (256 = Kota Malang)',
    is_active BOOLEAN DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Master Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'kasir', 'pelanggan') NOT NULL,
    id_cabang INT NULL COMMENT 'Terikat ke cabang jika role kasir, NULL untuk admin/pelanggan',
    telepon VARCHAR(20) NULL,
    alamat TEXT NULL,
    kota_id INT NULL,
    lat DECIMAL(10,8) NULL,
    lng DECIMAL(11,8) NULL,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Master Supplier
CREATE TABLE supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kontak VARCHAR(50) NULL,
    alamat TEXT NULL,
    is_active BOOLEAN DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Master Produk Induk (Parent)
CREATE TABLE produk_induk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku_induk VARCHAR(50) NOT NULL UNIQUE,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    kategori VARCHAR(50) NOT NULL,
    id_supplier INT NULL,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Master Produk Variasi (Child - Entitas yang dikelola stoknya)
CREATE TABLE produk_variasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk_induk INT NULL,
    sku_variasi VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: OBT-2026-0001-SYR / ALK-2026-0001-SET',
    nama_variasi VARCHAR(100) NOT NULL,
    satuan_kecil VARCHAR(50) NOT NULL DEFAULT 'Pcs',
    satuan_besar VARCHAR(50) NOT NULL DEFAULT 'Box',
    rasio_konversi INT NOT NULL DEFAULT 1,
    harga_jual_kecil DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_jual_besar DECIMAL(12,2) NOT NULL DEFAULT 0,
    berat INT NOT NULL DEFAULT 100 COMMENT 'Dalam satuan Gram',
    gambar VARCHAR(255) NULL,
    tampil_di_online BOOLEAN DEFAULT 1,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_produk_induk) REFERENCES produk_induk(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pivot Stok Cabang (Single Source of Truth Real-time Inventory)
CREATE TABLE stok_cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    id_cabang INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE CASCADE,
    UNIQUE KEY uq_variasi_cabang (id_variasi, id_cabang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 3. API CONFIGURATION TABLE (TOGGLE API)
-- ------------------------------------------------------------------------------
CREATE TABLE pengaturan_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('shopee', 'midtrans', 'rajaongkir') NOT NULL,
    api_key VARCHAR(255) NULL,
    api_secret VARCHAR(255) NULL,
    webhook_url VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT 0 COMMENT '0 = OFF, 1 = ON'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pengaturan Web (CMS E-Commerce)
CREATE TABLE pengaturan_web (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    telepon VARCHAR(20) NULL,
    alamat TEXT NULL,
    logo VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 4. TRANSAKSI & INVENTORY LOG TABLES
-- ------------------------------------------------------------------------------

-- Penjualan (POS, E-Commerce Mandiri, & Shopee)
CREATE TABLE penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    id_cabang INT NOT NULL,
    id_user INT NULL COMMENT 'NULL jika transaksi POS Kasir Offline',
    tipe_transaksi ENUM('pos', 'ecommerce', 'shopee') NOT NULL,
    status_pesanan ENUM('Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu',
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

-- Detail Penjualan
CREATE TABLE detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_variasi INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_penjualan) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mutasi Stok Antar Cabang
CREATE TABLE mutasi_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    cabang_asal INT NOT NULL,
    cabang_tujuan INT NOT NULL,
    qty INT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id),
    FOREIGN KEY (cabang_asal) REFERENCES cabang(id),
    FOREIGN KEY (cabang_tujuan) REFERENCES cabang(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kartu Stok Audit Trail
CREATE TABLE kartu_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cabang INT NOT NULL,
    id_variasi INT NOT NULL,
    jenis_mutasi ENUM('Masuk', 'Keluar', 'Pengembalian/Batal', 'Karantina', 'Opname') NOT NULL,
    qty INT NOT NULL,
    sisa_stok INT NOT NULL,
    keterangan TEXT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id),
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gudang Karantina (Virtual Defect Storage / Garansi)
CREATE TABLE gudang_karantina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cabang INT NOT NULL,
    id_variasi INT NOT NULL,
    qty INT NOT NULL,
    alasan TEXT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cabang) REFERENCES cabang(id),
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================================
-- 5. INITIAL SEED DATA (DATA DUMMY KOMPREHENSIF)
-- ==============================================================================

-- Seed Data Cabang (2 Cabang Fisik ZenCare)
INSERT INTO cabang (id, nama, alamat, kota_id, is_active) VALUES
(1, 'ZenCare Muharto', 'Jl. Muharto No. 55D, Jodipan, Lowokwaru, Kota Malang', 256, 1),
(2, 'ZenCare Borobudur', 'Jl. Simpang Borobudur No. 54, Lowokwaru, Kota Malang', 256, 1);

-- Seed Data Users (Password: 123456 - Hash bcrypt)
INSERT INTO users (id, username, password, nama_lengkap, role, id_cabang, is_active) VALUES
(1, 'admin', '$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p', 'Super Admin ZenCare', 'super_admin', NULL, 1),
(2, 'kasir_muharto', '$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p', 'Kasir Muharto', 'kasir', 1, 1),
(3, 'kasir_borobudur', '$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p', 'Kasir Borobudur', 'kasir', 2, 1),
(4, 'pelanggan1', '$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p', 'Budi Santoso', 'pelanggan', NULL, 1);

-- Seed Data Supplier
INSERT INTO supplier (id, nama, kontak, alamat, is_active) VALUES
(1, 'PT. Alkes Medika Utama', '081234567890', 'Jl. Industri Medis No. 12, Surabaya', 1),
(2, 'PT. Kimia Farmasindo', '082198765432', 'Jl. Farmasi Utama No. 8, Jakarta', 1);

-- Seed Data Produk Induk
INSERT INTO produk_induk (id, sku_induk, nama_produk, deskripsi, kategori, id_supplier, is_active) VALUES
(1, 'OBT-2026-0001-IND', 'Paracetamol Syrup Anak 60ml', 'Obat penurun demam dan pereda nyeri untuk anak-anak.', 'Obat-obatan', 2, 1),
(2, 'ALK-2026-0001-IND', 'Tensimeter Digital Omron', 'Alat ukur tekanan darah digital otomatis akurat bergaransi resmi.', 'Alat Monitor', 1, 1),
(3, 'ALK-2026-0002-IND', 'Kursi Roda Medis Sella', 'Kursi roda lipat standar rumah sakit bahan chrome kuat.', 'Alat Bantu Jalan', 1, 1);

-- Seed Data Produk Variasi (Dengan Multi-UOM Konversi)
INSERT INTO produk_variasi (id, id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, berat) VALUES
(1, 1, 'OBT-2026-0001-SYR', 'Syrup 60ml (Botol)', 'Botol', 'Karton', 50, 15000, 700000, 100),
(2, 2, 'OBT-2026-0002-TAB', 'Tablet 500mg (Strip)', 'Strip', 'Box', 100, 5000, 450000, 10),
(3, 3, 'ALK-2026-0001-SET', 'Standard Set', 'Pcs', 'Box', 10, 1500000, 14000000, 15000);

-- Seed Data Stok Cabang (Stok masing-masing cabang)
INSERT INTO stok_cabang (id_variasi, id_cabang, stok) VALUES
(1, 1, 50), -- Paracetamol Syrup di Muharto: 50
(1, 2, 30), -- Paracetamol Syrup di Borobudur: 30
(2, 1, 12), -- Tensimeter Omron di Muharto: 12
(2, 2, 8),  -- Tensimeter Omron di Borobudur: 8
(3, 1, 5),  -- Kursi Roda Sella di Muharto: 5
(3, 2, 3);  -- Kursi Roda Sella di Borobudur: 3

-- Seed Data Pengaturan API (Toggle Sandbox)
INSERT INTO pengaturan_api (platform, api_key, api_secret, webhook_url, is_active) VALUES
('shopee', 'SHP_SANDBOX_KEY_ZENCARE', 'SHP_SANDBOX_SECRET_ZENCARE', 'http://localhost/inventory_zencare/api/webhook_shopee.php', 1),
('midtrans', 'SB-Mid-server-TESTKEY123', 'SB-Mid-client-TESTKEY123', 'http://localhost/inventory_zencare/api/webhook.php', 1),
('rajaongkir', 'komerce_api_key_sandbox_123', NULL, 'http://localhost/inventory_zencare/api/rajaongkir.php', 1);

-- Seed Data Pengaturan Web
INSERT INTO pengaturan_web (id, nama_toko, deskripsi, telepon, alamat) VALUES 
(1, 'ZenCare Medical Store', 'Penyedia Alat Kesehatan Terpercaya', '081234567890', 'Jl. Muharto, Kota Malang');

-- Seed Initial Kartu Stok (Audit Log Awal)
INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES
(1, 1, 'Masuk', 50, 50, 'Stok Awal Pengadaan Cabang Muharto'),
(2, 1, 'Masuk', 30, 30, 'Stok Awal Pengadaan Cabang Borobudur'),
(1, 2, 'Masuk', 12, 12, 'Stok Awal Pengadaan Cabang Muharto'),
(2, 2, 'Masuk', 8, 8, 'Stok Awal Pengadaan Cabang Borobudur'),
(1, 3, 'Masuk', 5, 5, 'Stok Awal Pengadaan Cabang Muharto'),
(2, 3, 'Masuk', 3, 3, 'Stok Awal Pengadaan Cabang Borobudur');

-- Re-enable Foreign Key Checks
SET FOREIGN_KEY_CHECKS = 1;
