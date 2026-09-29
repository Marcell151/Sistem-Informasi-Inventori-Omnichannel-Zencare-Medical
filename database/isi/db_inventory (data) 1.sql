-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Sep 2026 pada 10.53
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_inventory`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `catatan_logistik` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kartu_stok`
--

CREATE TABLE `kartu_stok` (
  `id` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `jenis_mutasi` enum('Masuk','Keluar','Penyesuaian') NOT NULL,
  `kanal` enum('POS','E-Commerce','Manual','Penerimaan','PO') NOT NULL DEFAULT 'Manual',
  `alasan_mutasi` enum('Penerimaan Barang','Penjualan POS','Penjualan E-Commerce','Retur Barang Rusak','Klaim Garansi SN','Selisih Stok Opname','Barang Kedaluwarsa','Koreksi Manual') NOT NULL DEFAULT 'Koreksi Manual',
  `no_ref_dokumen` varchar(100) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `sisa_stok` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_anomali_fefo`
--

CREATE TABLE `log_anomali_fefo` (
  `id` int(11) NOT NULL,
  `id_penjualan` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `batch_diambil` varchar(100) DEFAULT NULL,
  `batch_seharusnya` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penerimaan_detail`
--

CREATE TABLE `penerimaan_detail` (
  `id` int(11) NOT NULL,
  `id_penerimaan` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `qty_terima` int(11) NOT NULL,
  `harga_beli` decimal(15,2) DEFAULT NULL,
  `no_batch` varchar(50) DEFAULT NULL,
  `tgl_exp` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penerimaan_detail`
--

INSERT INTO `penerimaan_detail` (`id`, `id_penerimaan`, `id_variasi`, `qty_terima`, `harga_beli`, `no_batch`, `tgl_exp`) VALUES
(1, 1, 2, 20, NULL, 'PCT260915A', '2029-09-15'),
(2, 1, 1, 15, NULL, 'PCT260915B', '2028-09-15'),
(3, 2, 4, 10, 50000.00, 'PCT2609G65', '2028-10-22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penerimaan_stok`
--

CREATE TABLE `penerimaan_stok` (
  `id` int(11) NOT NULL,
  `no_referensi` varchar(50) NOT NULL,
  `sumber` enum('PO','Pembelian Langsung') NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `tanggal_terima` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `file_nota` varchar(255) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penerimaan_stok`
--

INSERT INTO `penerimaan_stok` (`id`, `no_referensi`, `sumber`, `id_supplier`, `tanggal_terima`, `catatan`, `file_nota`, `dibuat_oleh`, `created_at`) VALUES
(1, 'NS1PW-NFAS-QOMW12', 'PO', 1, '2026-09-15', 'Beli Obat Paracetamol', NULL, 2, '2026-09-15 07:36:36'),
(2, 'NS1PW-NFAS-QOMTQ2', 'Pembelian Langsung', NULL, '2026-09-22', 'Beli Obat Paracetamol', NULL, 2, '2026-09-22 00:30:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan_web`
--

CREATE TABLE `pengaturan_web` (
  `id` int(11) NOT NULL,
  `nama_toko` varchar(100) NOT NULL DEFAULT 'ZenCare Medical',
  `deskripsi` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner_promosi` varchar(255) DEFAULT NULL,
  `email_cs` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan_web`
--

INSERT INTO `pengaturan_web` (`id`, `nama_toko`, `deskripsi`, `telepon`, `whatsapp`, `alamat`, `logo`, `banner_promosi`, `email_cs`) VALUES
(1, 'ZenCare Medical', 'Distributor Resmi Alat Kesehatan & Obat-Obatan Terpercaya di Malang', '0341-111222', '6281234567890', 'Jl. Muharto No.1, Kota Malang, Jawa Timur 65118', NULL, NULL, 'cs@zencare.id');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL,
  `no_invoice` varchar(50) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tipe_transaksi` enum('pos','ecommerce') NOT NULL,
  `metode_pengambilan` enum('Kurir','Pick-up') DEFAULT NULL,
  `status_pesanan` enum('Menunggu Pembayaran','Diproses','Dikirim','Siap Diambil','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `total_harga` decimal(12,2) NOT NULL,
  `metode_bayar_pos` varchar(50) DEFAULT 'Tunai',
  `ongkir` decimal(10,2) DEFAULT 0.00,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `telepon_penerima` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `kota_tujuan` varchar(100) DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL,
  `layanan` varchar(50) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `payment_method_ecommerce` varchar(50) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `kode_pickup` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_induk`
--

CREATE TABLE `produk_induk` (
  `id` int(11) NOT NULL,
  `sku_induk` varchar(50) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL COMMENT 'Spesifikasi teknis medis',
  `info_pengiriman` text DEFAULT NULL,
  `kategori` enum('Obat','Alat Kesehatan') NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `batas_hari_expired` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_induk`
--

INSERT INTO `produk_induk` (`id`, `sku_induk`, `nama_produk`, `deskripsi`, `spesifikasi`, `info_pengiriman`, `kategori`, `gambar`, `is_active`, `batas_hari_expired`, `created_at`) VALUES
(1, 'OB-HFG-001', 'Hufagrip 60ml', '', NULL, NULL, 'Obat', NULL, 1, 90, '2026-09-29 06:54:39'),
(2, 'OB-SAN-001', 'Sanmol Sirup', '', NULL, NULL, 'Obat', NULL, 1, 60, '2026-09-29 06:54:39'),
(3, 'OB-AMX-001', 'Amoxicillin Trihydrate', '', NULL, NULL, 'Obat', NULL, 1, 120, '2026-09-29 06:54:39'),
(4, 'OB-ASM-001', 'Asam Mefenamat', '', NULL, NULL, 'Obat', NULL, 1, 120, '2026-09-29 06:54:39'),
(5, 'OB-AML-001', 'Amlodipine Besylate', '', NULL, NULL, 'Obat', NULL, 1, 120, '2026-09-29 06:54:39'),
(6, 'OB-TOL-001', 'Tolak Angin', '', NULL, NULL, 'Obat', NULL, 1, 180, '2026-09-29 06:54:39'),
(7, 'OB-ANT-001', 'Antangin JRG', '', NULL, NULL, 'Obat', NULL, 1, 180, '2026-09-29 06:54:39'),
(8, 'AK-EST-001', 'Strip Test EasyTouch', '', NULL, NULL, 'Alat Kesehatan', NULL, 1, 90, '2026-09-29 06:54:39'),
(9, 'AK-SEN-001', 'Masker Medis Sensi', '', NULL, NULL, 'Alat Kesehatan', NULL, 1, 90, '2026-09-29 06:54:39'),
(10, 'AK-TRM-001', 'Termometer Omron', '', NULL, NULL, 'Alat Kesehatan', NULL, 1, 0, '2026-09-29 06:54:39'),
(11, 'AK-TNS-001', 'Tensimeter Digital Omron', '', NULL, NULL, 'Alat Kesehatan', NULL, 1, 0, '2026-09-29 06:54:39'),
(12, 'AK-KRS-001', 'Kursi Roda Gea', '', NULL, NULL, 'Alat Kesehatan', NULL, 1, 0, '2026-09-29 06:54:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_variasi`
--

CREATE TABLE `produk_variasi` (
  `id` int(11) NOT NULL,
  `id_produk_induk` int(11) DEFAULT NULL,
  `sku_variasi` varchar(50) NOT NULL,
  `nama_variasi` varchar(100) NOT NULL,
  `satuan_kecil` varchar(50) NOT NULL DEFAULT 'Pcs',
  `satuan_besar` varchar(50) NOT NULL DEFAULT 'Box',
  `rasio_konversi` smallint(6) NOT NULL DEFAULT 1,
  `harga_jual_kecil` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual_besar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok_minimum` int(11) NOT NULL DEFAULT 5,
  `berat` int(11) NOT NULL DEFAULT 100,
  `tampil_di_online` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `gambar` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_variasi`
--

INSERT INTO `produk_variasi` (`id`, `id_produk_induk`, `sku_variasi`, `nama_variasi`, `satuan_kecil`, `satuan_besar`, `rasio_konversi`, `harga_jual_kecil`, `harga_jual_besar`, `stok_minimum`, `berat`, `tampil_di_online`, `is_active`, `gambar`) VALUES
(1, 1, 'HFG-KUN', 'Hufagrip Flu & Batuk (Kuning)', 'Botol', 'Karton', 24, 15000.00, 350000.00, 10, 150, 1, 1, NULL),
(2, 1, 'HFG-HIJ', 'Hufagrip BP (Hijau)', 'Botol', 'Karton', 24, 15000.00, 350000.00, 10, 150, 1, 1, NULL),
(3, 1, 'HFG-MER', 'Hufagrip TMP (Merah)', 'Botol', 'Karton', 24, 15000.00, 350000.00, 10, 150, 1, 1, NULL),
(4, 1, 'HFG-BIR', 'Hufagrip Pilek (Biru)', 'Botol', 'Karton', 24, 15000.00, 350000.00, 10, 150, 1, 1, NULL),
(5, 2, 'SAN-60', 'Sanmol Sirup 60ml', 'Botol', 'Karton', 24, 18000.00, 420000.00, 15, 150, 1, 1, NULL),
(6, 2, 'SAN-15', 'Sanmol Drops 15ml', 'Botol', 'Karton', 24, 22000.00, 500000.00, 10, 50, 1, 1, NULL),
(7, 3, 'AMX-500', 'Amoxicillin 500mg Kapsul', 'Strip', 'Box (10 Strip)', 10, 7000.00, 65000.00, 20, 50, 1, 1, NULL),
(8, 3, 'AMX-250', 'Amoxicillin 250mg Kapsul', 'Strip', 'Box (10 Strip)', 10, 5000.00, 45000.00, 20, 50, 1, 1, NULL),
(9, 4, 'ASM-500', 'Asam Mefenamat 500mg Kaplet', 'Strip', 'Box (10 Strip)', 10, 4000.00, 38000.00, 20, 50, 1, 1, NULL),
(10, 5, 'AML-5', 'Amlodipine 5mg', 'Strip', 'Box (3 Strip)', 3, 15000.00, 43000.00, 10, 20, 1, 1, NULL),
(11, 5, 'AML-10', 'Amlodipine 10mg', 'Strip', 'Box (3 Strip)', 3, 25000.00, 72000.00, 10, 20, 1, 1, NULL),
(12, 6, 'TOL-KUN', 'Tolak Angin Cair Dus Kuning', 'Sachet', 'Box (12 Sachet)', 12, 4000.00, 46000.00, 24, 25, 1, 1, NULL),
(13, 6, 'TOL-ANK', 'Tolak Angin Anak', 'Sachet', 'Box (12 Sachet)', 12, 3500.00, 40000.00, 24, 25, 1, 1, NULL),
(14, 6, 'TOL-FLU', 'Tolak Angin Flu', 'Sachet', 'Box (12 Sachet)', 12, 4500.00, 52000.00, 24, 25, 1, 1, NULL),
(15, 7, 'ANT-CAIR', 'Antangin JRG Cair', 'Sachet', 'Box (12 Sachet)', 12, 3800.00, 44000.00, 24, 25, 1, 1, NULL),
(16, 7, 'ANT-TAB', 'Antangin JRG Tablet', 'Strip', 'Box (20 Strip)', 20, 2500.00, 48000.00, 40, 30, 1, 1, NULL),
(17, 8, 'EST-GLU', 'Strip Gula Darah (Blood Glucose)', 'Botol (Isi 25)', 'Box (2 Botol)', 2, 85000.00, 160000.00, 4, 100, 1, 1, NULL),
(18, 8, 'EST-URI', 'Strip Asam Urat (Uric Acid)', 'Botol (Isi 25)', 'Box (1 Botol)', 1, 95000.00, 95000.00, 2, 80, 1, 1, NULL),
(19, 8, 'EST-CHO', 'Strip Kolesterol', 'Botol (Isi 10)', 'Box (1 Botol)', 1, 150000.00, 150000.00, 2, 80, 1, 1, NULL),
(20, 9, 'SEN-EAR', 'Sensi Earloop 3-Ply (Hijau)', 'Box (Isi 50)', 'Karton (40 Box)', 40, 35000.00, 1300000.00, 10, 300, 1, 1, NULL),
(21, 9, 'SEN-DUC', 'Sensi Duckbill (Putih)', 'Box', 'Karton (40 Box)', 40, 45000.00, 1700000.00, 10, 350, 1, 1, NULL),
(22, 10, 'TRM-246', 'Termometer Digital Omron MC-246', 'Pcs', 'Pcs', 1, 75000.00, 75000.00, 5, 100, 1, 1, NULL),
(23, 10, 'TRM-720', 'Termometer Tembak Omron MC-720', 'Pcs', 'Pcs', 1, 450000.00, 450000.00, 2, 250, 1, 1, NULL),
(24, 11, 'TNS-7120', 'Tensimeter HEM-7120', 'Unit', 'Unit', 1, 650000.00, 650000.00, 2, 800, 1, 1, NULL),
(25, 11, 'TNS-7156', 'Tensimeter HEM-7156 (Bluetooth)', 'Unit', 'Unit', 1, 950000.00, 950000.00, 2, 900, 1, 1, NULL),
(26, 12, 'KRS-809', 'Kursi Roda Standar FS809', 'Unit', 'Unit', 1, 1200000.00, 1200000.00, 1, 15000, 1, 1, NULL),
(27, 12, 'KRS-TRV', 'Kursi Roda Travel Lipat', 'Unit', 'Unit', 1, 1850000.00, 1850000.00, 1, 12000, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_batch`
--

CREATE TABLE `stok_batch` (
  `id` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `no_batch` varchar(100) NOT NULL,
  `tgl_exp` date NOT NULL,
  `stok_sisa` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok_batch`
--

INSERT INTO `stok_batch` (`id`, `id_variasi`, `no_batch`, `tgl_exp`, `stok_sisa`, `created_at`) VALUES
(1, 1, 'HFG-KUN-A1', '2028-09-01', 50, '2026-09-01 03:00:00'),
(2, 1, 'HFG-KUN-A2', '2026-10-09', 15, '2026-09-01 03:00:00'),
(3, 2, 'HFG-HIJ-A1', '2028-09-01', 30, '2026-09-01 03:00:00'),
(4, 3, 'HFG-MER-A1', '2028-09-01', 5, '2026-09-01 03:00:00'),
(5, 5, 'SAN-60-B1', '2027-12-01', 40, '2026-09-01 03:00:00'),
(6, 6, 'SAN-15-B1', '2027-12-01', 8, '2026-09-01 03:00:00'),
(7, 7, 'AMX-2024-A', '2026-12-01', 5, '2024-01-01 03:00:00'),
(8, 7, 'AMX-2024-B', '2027-08-01', 5, '2024-03-01 03:00:00'),
(9, 7, 'AMX-2024-C', '2028-01-01', 5, '2024-06-01 03:00:00'),
(10, 8, 'AMX250-C1', '2029-01-01', 50, '2026-09-01 03:00:00'),
(11, 9, 'ASM500-D1', '2028-12-01', 80, '2026-09-01 03:00:00'),
(12, 10, 'AML5-E1', '2026-10-06', 12, '2026-09-01 03:00:00'),
(13, 11, 'AML10-E1', '2028-06-01', 40, '2026-09-01 03:00:00'),
(14, 12, 'TOLKUN-F1', '2027-08-01', 144, '2026-08-01 03:00:00'),
(15, 12, 'TOLKUN-F2', '2028-01-01', 72, '2026-09-01 03:00:00'),
(16, 13, 'TOLANK-F1', '2027-08-01', 48, '2026-08-01 03:00:00'),
(17, 15, 'ANTC-G1', '2027-08-01', 120, '2026-08-01 03:00:00'),
(18, 16, 'ANTT-G1', '2027-08-01', 200, '2026-08-01 03:00:00'),
(19, 17, 'EST-GLU-H1', '2027-05-01', 10, '2026-09-01 03:00:00'),
(20, 18, 'EST-URI-H1', '2026-10-07', 1, '2026-09-01 03:00:00'),
(21, 19, 'EST-CHO-H1', '2027-05-01', 5, '2026-09-01 03:00:00'),
(22, 20, 'SENEAR-I1', '2030-01-01', 80, '2026-09-01 03:00:00'),
(23, 21, 'SENDUC-I1', '2030-01-01', 120, '2026-09-01 03:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_toko`
--

CREATE TABLE `stok_toko` (
  `id` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok_toko`
--

INSERT INTO `stok_toko` (`id`, `id_variasi`, `stok`) VALUES
(1, 1, 65),
(2, 2, 30),
(3, 3, 5),
(4, 4, 0),
(5, 5, 40),
(6, 6, 8),
(7, 7, 15),
(8, 8, 50),
(9, 9, 80),
(10, 10, 12),
(11, 11, 40),
(12, 12, 216),
(13, 13, 48),
(14, 14, 0),
(15, 15, 120),
(16, 16, 200),
(17, 17, 10),
(18, 18, 1),
(19, 19, 5),
(20, 20, 80),
(21, 21, 120),
(22, 22, 8),
(23, 23, 3),
(24, 24, 2),
(25, 25, 1),
(26, 26, 1),
(27, 27, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id`, `nama`, `kontak`, `email`, `alamat`, `is_active`) VALUES
(1, 'PT Kimia Farma Trading & Distribution', '0341-999001', 'kf.malang@kimiafarma.co.id', 'Jl. Farmasi Raya No.1, Malang', 1),
(2, 'PT Omron Healthcare Indonesia', '021-555222', 'info@omron-healthcare.co.id', 'Gedung Omron, Jakarta Pusat', 1),
(3, 'PT Jayamas Medica Industri (OneMed)', '031-444333', 'sales@onemed.co.id', 'Kawasan Industri Rungkut, Surabaya', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit_serial`
--

CREATE TABLE `unit_serial` (
  `id` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `serial_number` varchar(150) NOT NULL,
  `status` enum('Tersedia','Terjual','Retur/Rusak') NOT NULL DEFAULT 'Tersedia',
  `id_penjualan` int(11) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `tgl_habis_garansi` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unit_serial`
--

INSERT INTO `unit_serial` (`id`, `id_variasi`, `serial_number`, `status`, `id_penjualan`, `catatan`, `tgl_habis_garansi`, `created_at`, `updated_at`) VALUES
(1, 22, 'SN-TRM246-001', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(2, 22, 'SN-TRM246-002', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(3, 22, 'SN-TRM246-003', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(4, 22, 'SN-TRM246-004', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(5, 22, 'SN-TRM246-005', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(6, 22, 'SN-TRM246-006', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(7, 22, 'SN-TRM246-007', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(8, 22, 'SN-TRM246-008', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(9, 23, 'SN-TRM720-001', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(10, 23, 'SN-TRM720-002', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(11, 23, 'SN-TRM720-003', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(12, 24, 'OMR-HEM7120-001', 'Tersedia', NULL, NULL, '2026-10-29', '2026-09-01 03:00:00', NULL),
(13, 24, 'OMR-HEM7120-002', 'Tersedia', NULL, NULL, '2028-08-15', '2026-09-01 03:00:00', NULL),
(14, 25, 'SN-HEM7156-Y1', 'Tersedia', NULL, NULL, '2029-01-01', '2026-09-01 03:00:00', NULL),
(15, 26, 'SN-KRS809-Z1', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL),
(16, 27, 'SN-KRSTRV-Z1', 'Tersedia', NULL, NULL, NULL, '2026-09-01 03:00:00', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('superadmin','admin','kasir','pelanggan') NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL COMMENT 'Alamat utama pelanggan untuk pengiriman',
  `kota_id` int(11) DEFAULT NULL COMMENT 'ID kota RajaOngkir untuk kalkulasi ongkir',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `email`, `telepon`, `alamat`, `kota_id`, `is_active`, `created_at`) VALUES
(1, 'superadmin', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'SuperAdmin (Pemilik)', 'superadmin', 'superadmin@zencare.id', '081234567890', NULL, NULL, 1, '2026-09-10 07:42:09'),
(2, 'admin_toko', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Admin Toko', 'admin', 'admin@zencare.id', '082111222333', NULL, NULL, 1, '2026-09-10 07:42:09'),
(3, 'pelanggan1', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Budi Santoso', 'pelanggan', 'budi.santoso@gmail.com', '083333444555', 'Jl. Soekarno Hatta No.12, Malang', 391, 1, '2026-09-10 07:42:09'),
(4, 'kasir_toko', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Kasir (Front Office)', 'kasir', 'kasir@zencare.id', '08222333444', NULL, NULL, 1, '2026-09-28 14:16:12');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_penjualan` (`id_penjualan`),
  ADD KEY `id_variasi` (`id_variasi`);

--
-- Indeks untuk tabel `kartu_stok`
--
ALTER TABLE `kartu_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_variasi` (`id_variasi`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indeks untuk tabel `log_anomali_fefo`
--
ALTER TABLE `log_anomali_fefo`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `penerimaan_detail`
--
ALTER TABLE `penerimaan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_penerimaan` (`id_penerimaan`),
  ADD KEY `id_variasi` (`id_variasi`);

--
-- Indeks untuk tabel `penerimaan_stok`
--
ALTER TABLE `penerimaan_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indeks untuk tabel `pengaturan_web`
--
ALTER TABLE `pengaturan_web`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_invoice` (`no_invoice`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `produk_induk`
--
ALTER TABLE `produk_induk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku_induk` (`sku_induk`);

--
-- Indeks untuk tabel `produk_variasi`
--
ALTER TABLE `produk_variasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku_variasi` (`sku_variasi`),
  ADD KEY `id_produk_induk` (`id_produk_induk`);

--
-- Indeks untuk tabel `stok_batch`
--
ALTER TABLE `stok_batch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fefo` (`id_variasi`,`tgl_exp`);

--
-- Indeks untuk tabel `stok_toko`
--
ALTER TABLE `stok_toko`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_variasi` (`id_variasi`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `unit_serial`
--
ALTER TABLE `unit_serial`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `id_variasi` (`id_variasi`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kartu_stok`
--
ALTER TABLE `kartu_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_anomali_fefo`
--
ALTER TABLE `log_anomali_fefo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penerimaan_detail`
--
ALTER TABLE `penerimaan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `penerimaan_stok`
--
ALTER TABLE `penerimaan_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_web`
--
ALTER TABLE `pengaturan_web`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk_induk`
--
ALTER TABLE `produk_induk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `produk_variasi`
--
ALTER TABLE `produk_variasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `stok_batch`
--
ALTER TABLE `stok_batch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `stok_toko`
--
ALTER TABLE `stok_toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `unit_serial`
--
ALTER TABLE `unit_serial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`);

--
-- Ketidakleluasaan untuk tabel `kartu_stok`
--
ALTER TABLE `kartu_stok`
  ADD CONSTRAINT `kartu_stok_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  ADD CONSTRAINT `kartu_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penerimaan_detail`
--
ALTER TABLE `penerimaan_detail`
  ADD CONSTRAINT `penerimaan_detail_ibfk_1` FOREIGN KEY (`id_penerimaan`) REFERENCES `penerimaan_stok` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penerimaan_detail_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`);

--
-- Ketidakleluasaan untuk tabel `penerimaan_stok`
--
ALTER TABLE `penerimaan_stok`
  ADD CONSTRAINT `penerimaan_stok_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penerimaan_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `produk_variasi`
--
ALTER TABLE `produk_variasi`
  ADD CONSTRAINT `produk_variasi_ibfk_1` FOREIGN KEY (`id_produk_induk`) REFERENCES `produk_induk` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `stok_batch`
--
ALTER TABLE `stok_batch`
  ADD CONSTRAINT `stok_batch_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stok_toko`
--
ALTER TABLE `stok_toko`
  ADD CONSTRAINT `stok_toko_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `unit_serial`
--
ALTER TABLE `unit_serial`
  ADD CONSTRAINT `unit_serial_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
