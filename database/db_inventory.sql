-- ==============================================================================
-- SISTEM INFORMASI MANAJEMEN INVENTARIS & E-COMMERCE OMNICHANNEL ZENCARE MEDICAL
-- DATABASE: db_inventory
-- Versi: 3.1 — Final (Cabang Removed, CMS Web Upgraded)
-- Tanggal: 2026-09-10
-- ==============================================================================
-- ATURAN: File ini adalah database BARU.
-- Role baru: superadmin | admin | pelanggan
-- Fitur multi-cabang: DIHAPUS SEPENUHNYA (Single-store view).
-- Password semua akun demo: 123456
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS db_inventory DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_inventory;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- DROP TABLES (urutan aman: child first)
-- ============================================================================
DROP TABLE IF EXISTS kartu_stok;
DROP TABLE IF EXISTS penerimaan_detail;
DROP TABLE IF EXISTS penerimaan_stok;
DROP TABLE IF EXISTS po_detail;
DROP TABLE IF EXISTS purchase_order;
DROP TABLE IF EXISTS detail_penjualan;
DROP TABLE IF EXISTS penjualan;
DROP TABLE IF EXISTS stok_batch;
DROP TABLE IF EXISTS unit_serial;
DROP TABLE IF EXISTS stok_toko;
DROP TABLE IF EXISTS produk_variasi;
DROP TABLE IF EXISTS produk_induk;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS pengaturan_web;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. USERS (Role baru: superadmin | admin | pelanggan)
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'admin', 'pelanggan') NOT NULL,
    -- Pelanggan & Umum fields
    email VARCHAR(150) NULL,
    telepon VARCHAR(20) NULL,
    alamat TEXT NULL COMMENT 'Alamat utama pelanggan untuk pengiriman',
    kota_id INT NULL COMMENT 'ID kota RajaOngkir untuk kalkulasi ongkir',
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 2. SUPPLIER
-- ============================================================================
CREATE TABLE supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kontak VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    alamat TEXT NULL,
    is_active BOOLEAN DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 3. PRODUK INDUK
-- ============================================================================
CREATE TABLE produk_induk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku_induk VARCHAR(50) NOT NULL UNIQUE,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    spesifikasi TEXT NULL COMMENT 'Spesifikasi teknis medis',
    info_pengiriman TEXT NULL,
    kategori ENUM('Obat', 'Alat Kesehatan') NOT NULL,
    gambar VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 4. PRODUK VARIASI (UOM: Unit of Measure)
-- ============================================================================
CREATE TABLE produk_variasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk_induk INT NULL,
    sku_variasi VARCHAR(50) NOT NULL UNIQUE,
    nama_variasi VARCHAR(100) NOT NULL,
    satuan_kecil VARCHAR(50) NOT NULL DEFAULT 'Pcs',
    satuan_besar VARCHAR(50) NOT NULL DEFAULT 'Box',
    rasio_konversi SMALLINT NOT NULL DEFAULT 1,
    harga_jual_kecil DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_jual_besar DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 5,
    berat INT NOT NULL DEFAULT 100,
    gambar VARCHAR(500) NULL,
    tampil_di_online BOOLEAN DEFAULT 1,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_produk_induk) REFERENCES produk_induk(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 5. STOK TOKO (Agregat pivot)
-- ============================================================================
CREATE TABLE stok_toko (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL UNIQUE,
    stok INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 6. STOK BATCH (Logistik Obat — FEFO)
-- ============================================================================
CREATE TABLE stok_batch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    no_batch VARCHAR(100) NOT NULL,
    tgl_exp DATE NOT NULL,
    stok_sisa INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE,
    INDEX idx_fefo (id_variasi, tgl_exp ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 7. UNIT SERIAL (Logistik Alkes)
-- ============================================================================
CREATE TABLE unit_serial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    serial_number VARCHAR(150) NOT NULL UNIQUE,
    status ENUM('Tersedia', 'Terjual', 'Retur/Rusak') NOT NULL DEFAULT 'Tersedia',
    id_penjualan INT NULL,
    catatan VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 8. PENERIMAAN STOK
-- ============================================================================
CREATE TABLE penerimaan_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_referensi VARCHAR(50) NOT NULL,
    sumber ENUM('PO', 'Pembelian Langsung') NOT NULL,
    id_supplier INT NULL,
    tanggal_terima DATE NOT NULL,
    catatan TEXT NULL,
    file_nota VARCHAR(255) NULL,
    dibuat_oleh INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE penerimaan_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penerimaan INT NOT NULL,
    id_variasi INT NOT NULL,
    qty_terima INT NOT NULL,
    harga_beli DECIMAL(15,2) NULL,
    no_batch VARCHAR(50) NULL,
    tgl_exp DATE NULL,
    FOREIGN KEY (id_penerimaan) REFERENCES penerimaan_stok(id) ON DELETE CASCADE,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 9. PENJUALAN
-- ============================================================================
CREATE TABLE penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    id_user INT NULL,
    tipe_transaksi ENUM('pos', 'ecommerce') NOT NULL,
    metode_pengambilan ENUM('Kurir', 'Pick-up') NULL,
    status_pesanan ENUM('Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Siap Diambil', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',
    total_harga DECIMAL(12,2) NOT NULL,
    ongkir DECIMAL(10,2) DEFAULT 0,
    nama_penerima VARCHAR(100) NULL,
    telepon_penerima VARCHAR(20) NULL,
    alamat_lengkap TEXT NULL,
    kota_tujuan VARCHAR(100) NULL,
    kurir VARCHAR(50) NULL,
    layanan VARCHAR(50) NULL,
    snap_token VARCHAR(255) NULL,
    payment_method_ecommerce VARCHAR(50) NULL,
    metode_bayar_pos VARCHAR(50) DEFAULT 'Tunai',
    paid_at TIMESTAMP NULL,
    kode_pickup VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_variasi INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    catatan_logistik TEXT NULL,
    FOREIGN KEY (id_penjualan) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 10. KARTU STOK (Audit trail fisik)
-- ============================================================================
CREATE TABLE kartu_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    jenis_mutasi ENUM('Masuk', 'Keluar', 'Penyesuaian') NOT NULL,
    kanal ENUM('POS', 'E-Commerce', 'Manual', 'Penerimaan', 'PO') NOT NULL DEFAULT 'Manual',
    alasan_mutasi ENUM(
        'Penerimaan Barang',
        'Penjualan POS',
        'Penjualan E-Commerce',
        'Retur Barang Rusak',
        'Klaim Garansi SN',
        'Selisih Stok Opname',
        'Barang Kedaluwarsa',
        'Koreksi Manual'
    ) NOT NULL DEFAULT 'Koreksi Manual',
    no_ref_dokumen VARCHAR(100) NULL,
    qty INT NOT NULL,
    sisa_stok INT NOT NULL,
    keterangan TEXT NULL,
    dibuat_oleh INT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id),
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 11. PENGATURAN WEB (CMS)
-- ============================================================================
CREATE TABLE pengaturan_web (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(100) NOT NULL DEFAULT 'ZenCare Medical',
    deskripsi TEXT NULL,
    telepon VARCHAR(20) NULL,
    whatsapp VARCHAR(20) NULL,
    alamat TEXT NULL,
    logo VARCHAR(255) NULL,
    banner_promosi VARCHAR(255) NULL,
    email_cs VARCHAR(150) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 12. DATA DUMMY
-- ============================================================================
INSERT INTO users (id, username, password, nama_lengkap, role, email, telepon, is_active) VALUES
(1, 'superadmin', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'SuperAdmin  (Pemilik)', 'superadmin', 'superadmin@zencare.id', '081234567890', 1),
(2, 'admin_toko', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Admin (Staff Toko)', 'admin', 'admin@zencare.id', '082111222333', 1),
(3, 'pelanggan1', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Budi Santoso', 'pelanggan', 'budi.santoso@gmail.com', '083333444555', 1);

UPDATE users SET alamat = 'Jl. Soekarno Hatta No.12, Malang', kota_id = 256 WHERE id = 3;

INSERT INTO supplier (id, nama, kontak, email, alamat) VALUES 
(1, 'PT Kimia Farma Trading & Distribution', '0341-999001', 'kf.malang@kimiafarma.co.id', 'Jl. Farmasi Raya No.1, Malang'),
(2, 'PT Omron Healthcare Indonesia', '021-555222', 'info@omron-healthcare.co.id', 'Gedung Omron, Jakarta Pusat'),
(3, 'PT Jayamas Medica Industri (OneMed)', '031-444333', 'sales@onemed.co.id', 'Kawasan Industri Rungkut, Surabaya');

INSERT INTO produk_induk (id, sku_induk, nama_produk, deskripsi, spesifikasi, info_pengiriman, kategori) VALUES 
(1, 'PRD-OBT-001', 'Paracetamol Sirup Anak 60ml', 'Obat penurun demam dan pereda nyeri', 'Komposisi: Paracetamol 160mg/5ml', 'Aman dikirim reguler', 'Obat'),
(2, 'PRD-OBT-002', 'Amoxicillin Kapsul 500mg', 'Antibiotik golongan penisilin', 'Komposisi: Amoxicillin trihydrate 500mg', 'Simpan di tempat sejuk', 'Obat'),
(3, 'PRD-ALK-001', 'Tensimeter Digital Omron HEM-7120', 'Alat pengukur tekanan darah digital otomatis', 'Akurasi tinggi, memori 60 data', 'Fragile, wajib bubble wrap', 'Alat Kesehatan');

INSERT INTO produk_variasi (id, id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, stok_minimum, berat) VALUES
(1, 1, 'OBT-001-60ML', 'Sirup 60ml per Botol', 'Botol', 'Karton (50 Botol)', 50, 18500, 875000, 10, 150),
(2, 2, 'OBT-002-500MG', 'Kapsul 500mg per Strip', 'Strip', 'Box (10 Strip)', 10, 7500, 70000, 20, 50),
(3, 3, 'ALK-001-HEM7120', 'Set Lengkap Omron HEM-7120', 'Unit', 'Unit', 1, 495000, 495000, 3, 900);

INSERT INTO stok_toko (id_variasi, stok) VALUES 
(1, 150), (2, 80), (3, 5);

INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES 
(1, 'BPAR-2024-001', '2026-03-31', 50),
(1, 'BPAR-2024-002', '2026-12-31', 100),
(2, 'BAMX-2024-003', '2026-10-15', 80);

INSERT INTO unit_serial (id_variasi, serial_number, status) VALUES 
(3, 'OMR-HEM7120-A001', 'Tersedia'),
(3, 'OMR-HEM7120-A002', 'Tersedia'),
(3, 'OMR-HEM7120-A003', 'Tersedia'),
(3, 'OMR-HEM7120-A004', 'Terjual'),
(3, 'OMR-HEM7120-A005', 'Retur/Rusak');

INSERT INTO pengaturan_web (nama_toko, deskripsi, telepon, whatsapp, alamat, email_cs) VALUES 
('ZenCare Medical', 'Distributor Resmi Alat Kesehatan & Obat-Obatan Terpercaya di Malang', 
 '0341-111222', '6281234567890', 
 'Jl. Muharto No.1, Kota Malang, Jawa Timur 65118', 'cs@zencare.id');
