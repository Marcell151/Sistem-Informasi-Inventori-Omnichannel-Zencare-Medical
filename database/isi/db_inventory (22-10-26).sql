-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Okt 2026 pada 02.36
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

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id`, `id_penjualan`, `id_variasi`, `qty`, `harga_satuan`, `catatan_logistik`) VALUES
(1, 1, 1, 2, 5000.00, 'AMX-2026-01.A (2x)'),
(2, 1, 5, 1, 85000.00, 'OMR-TH-01'),
(3, 2, 10, 5, 3500.00, 'PCT-500-26.A (3x), PCT-500-26.B (2x)'),
(4, 3, 6, 5, 18000.00, 'HUF-TMP-26.A (2x), HUF-TMP-27.A (3x)'),
(5, 3, 6, 1, 850000.00, 'HUF-TMP-27 (1 Karton = 50 Botol)'),
(6, 4, 30, 2, 85000.00, 'IMB-FRC-26 (2x)'),
(7, 5, 2, 1, 350000.00, 'SNM-2026-A (1 Karton = 24 Botol)'),
(8, 5, 4, 1, 550000.00, 'OMR-TENS-01'),
(9, 6, 56, 1, 850000.00, 'OMR-NEB-99'),
(15, 13, 6, 1, 850000.00, 'HUF-TMP-26 (1 Karton = 50 Botol)'),
(16, 14, 7, 1, 18000.00, 'HUFPIL-2027-01.A (1 Botol)'),
(17, 14, 7, 1, 850000.00, 'HUFPIL-2027-01 (1 Karton)'),
(18, 15, 8, 1, 850000.00, 'HUFBP-2027-01 (1 Karton = 50 Botol)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `histori_konversi`
--

CREATE TABLE `histori_konversi` (
  `id` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `batch_asal` varchar(100) NOT NULL,
  `batch_hasil` varchar(100) NOT NULL,
  `qty_box_buka` int(11) NOT NULL,
  `qty_pcs_hasil` int(11) NOT NULL,
  `sisa_dus_akhir` int(11) DEFAULT 0,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `histori_konversi`
--

INSERT INTO `histori_konversi` (`id`, `id_variasi`, `batch_asal`, `batch_hasil`, `qty_box_buka`, `qty_pcs_hasil`, `sisa_dus_akhir`, `dibuat_oleh`, `created_at`) VALUES
(1, 1, 'AMX-2026-01', 'AMX-2026-01.A', 1, 10, 1, 4, '2026-10-05 09:15:00'),
(2, 2, 'SNM-2026-A', 'SNM-2026-A.A', 1, 24, 9, 2, '2026-10-07 10:30:00'),
(3, 10, 'PCT-500-26', 'PCT-500-26.A', 1, 10, 19, 4, '2026-10-08 11:00:00'),
(4, 10, 'PCT-500-26', 'PCT-500-26.B', 1, 10, 18, 4, '2026-10-10 14:20:00'),
(5, 6, 'HUF-TMP-26', 'HUF-TMP-26.A', 1, 50, 7, 4, '2026-10-09 13:45:00'),
(6, 6, 'HUF-TMP-27', 'HUF-TMP-27.A', 1, 50, 9, 2, '2026-10-11 15:30:00'),
(12, 7, 'hufpil-2027-01', 'hufpil-2027-01.A', 1, 50, 14, 4, '2026-10-21 23:42:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kartu_stok`
--

CREATE TABLE `kartu_stok` (
  `id` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `satuan_tipe` enum('besar','kecil') NOT NULL DEFAULT 'kecil',
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

--
-- Dumping data untuk tabel `kartu_stok`
--

INSERT INTO `kartu_stok` (`id`, `id_variasi`, `satuan_tipe`, `jenis_mutasi`, `kanal`, `alasan_mutasi`, `no_ref_dokumen`, `qty`, `sisa_stok`, `keterangan`, `dibuat_oleh`, `tanggal`) VALUES
(1, 1, 'besar', 'Keluar', 'Manual', 'Barang Kedaluwarsa', 'MUT-EXP-20260901-01', 15, 2, 'Pemusnahan Batch Kadaluarsa [Batch: AMX-OLD-2025]', 2, '2026-09-01 10:00:00'),
(2, 10, 'besar', 'Keluar', 'Manual', 'Barang Kedaluwarsa', 'MUT-EXP-20260901-02', 20, 20, 'Pemusnahan Batch Kadaluarsa [Batch: PCT-OLD-2025]', 2, '2026-09-01 10:15:00'),
(3, 1, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', NULL, 1, 1, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: AMX-2026-01]', 4, '2026-10-05 09:15:00'),
(4, 1, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', NULL, 10, 10, 'Lahir Sub-Batch AMX-2026-01.A (Masuk 10 Strip)', 4, '2026-10-05 09:15:00'),
(5, 2, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', NULL, 1, 9, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: SNM-2026-A]', 2, '2026-10-07 10:30:00'),
(6, 2, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', NULL, 24, 24, 'Lahir Sub-Batch SNM-2026-A.A (Masuk 24 Botol)', 2, '2026-10-07 10:30:00'),
(7, 10, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', NULL, 1, 19, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]', 4, '2026-10-08 11:00:00'),
(8, 10, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', NULL, 10, 10, 'Lahir Sub-Batch PCT-500-26.A (Masuk 10 Strip)', 4, '2026-10-08 11:00:00'),
(9, 10, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', NULL, 1, 18, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]', 4, '2026-10-10 14:20:00'),
(10, 10, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', NULL, 10, 10, 'Lahir Sub-Batch PCT-500-26.B (Masuk 10 Strip)', 4, '2026-10-10 14:20:00'),
(11, 6, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', NULL, 1, 7, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-26]', 4, '2026-10-09 13:45:00'),
(12, 6, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', NULL, 50, 50, 'Lahir Sub-Batch HUF-TMP-26.A (Masuk 50 Botol)', 4, '2026-10-09 13:45:00'),
(13, 6, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', NULL, 1, 9, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-27]', 2, '2026-10-11 15:30:00'),
(14, 6, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', NULL, 50, 50, 'Lahir Sub-Batch HUF-TMP-27.A (Masuk 50 Botol)', 2, '2026-10-11 15:30:00'),
(15, 56, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261012-0901', 1, 4, 'Penjualan Luring POS-20261012-0901 [SN: OMR-NEB-99]', 4, '2026-10-12 16:10:00'),
(16, 56, 'kecil', 'Masuk', 'Manual', 'Retur Barang Rusak', 'POS-20261012-0901', 1, 5, 'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi) [SN: OMR-NEB-99]', 2, '2026-10-15 11:20:00'),
(17, 1, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261014-1001', 2, 8, 'Penjualan Luring POS-20261014-1001 [Batch: AMX-2026-01.A (2x)]', 4, '2026-10-14 10:15:20'),
(18, 5, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261014-1001', 1, 5, 'Penjualan Luring POS-20261014-1001 [SN: OMR-TH-01]', 4, '2026-10-14 10:15:20'),
(19, 10, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261016-1002', 3, 15, 'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.A (3 Tablet)]', 4, '2026-10-16 14:22:10'),
(20, 6, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261017-1003', 2, 95, 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-26.A (2 Botol)]', 4, '2026-10-17 11:40:05'),
(21, 6, 'besar', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261017-1003', 1, 8, 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27 (1 Karton = 50 Botol)]', 4, '2026-10-17 11:40:05'),
(22, 30, 'kecil', 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', 'INV-20261018-041', 2, 58, 'Pengiriman via Ekspedisi (Kurir) [Batch: IMB-FRC-26 (2x)]', 2, '2026-10-18 09:30:00'),
(23, 2, 'besar', 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', 'INV-20261019-052', 1, 8, 'Pengiriman via Ekspedisi (Kurir) [Batch: SNM-2026-A (1 Karton = 24 Botol)]', 2, '2026-10-19 13:20:00'),
(24, 4, 'kecil', 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', 'INV-20261019-052', 1, 4, 'Pengiriman via Ekspedisi (Kurir) [SN: OMR-TENS-01]', 2, '2026-10-19 13:20:00'),
(25, 61, 'besar', 'Masuk', '', '', 'INIT-5BOX', 5, 5, 'Reset Skenario Dosen: Saldo Awal 5 Karton Utuh (Gudang)', 1, '2026-10-21 23:40:20'),
(26, 7, 'besar', 'Keluar', 'POS', '', NULL, 1, 15, 'Bongkar 1 Karton (Rp0 - Konversi POS) [Batch: hufpil-2027-01]', 4, '2026-10-21 23:42:03'),
(27, 7, 'kecil', 'Masuk', 'POS', '', NULL, 50, 750, 'Lahir Sub-Batch hufpil-2027-01.A (Masuk 50 Botol)', 4, '2026-10-21 23:42:03'),
(28, 7, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261022-9476', 1, 749, 'Penjualan Luring POS-20261022-9476 [Batch: HUFPIL-2027-01.A (1 Botol)]', 4, '2026-10-21 23:42:26'),
(29, 7, 'besar', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261022-9476', 1, 13, 'Penjualan Luring POS-20261022-9476 [Batch: HUFPIL-2027-01 (1 Karton)]', 4, '2026-10-21 23:42:26'),
(30, 10, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261016-1002', 2, 15, 'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.B (2 Tablet)]', 4, '2026-10-16 14:22:10'),
(31, 6, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261017-1003', 3, 95, 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27.A (3 Botol)]', 4, '2026-10-17 11:40:05');

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
(1, 1, 1, 15, 40000.00, 'AMX-2026-01', '2027-12-15'),
(2, 1, 10, 25, 25000.00, 'PCT-500-26', '2027-11-20'),
(3, 2, 2, 12, 300000.00, 'SNM-2026-A', '2028-05-10'),
(4, 2, 6, 10, 750000.00, 'HUF-TMP-27', '2028-06-20'),
(5, 3, 30, 20, 210000.00, 'IMB-FRC-26', '2027-09-15');

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
(1, 'PO-20260915-01', 'PO', 1, '2026-09-15', 'Penerimaan stok awal obat dan multivitamin dari PT Kimia Farma Trading', NULL, 2, '2026-09-15 08:30:00'),
(2, 'PO-20260925-02', 'PO', 2, '2026-09-25', 'Penerimaan restok sirup obat anak dan alat kesehatan dari PT Enseval', NULL, 2, '2026-09-25 09:15:00'),
(3, 'PO-20261005-03', 'PO', 3, '2026-10-05', 'Penerimaan suplemen daya tahan tubuh dan peralatan diagnostik dari PT Kalbe Farma', NULL, 2, '2026-10-05 10:00:00');

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

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id`, `no_invoice`, `id_user`, `tipe_transaksi`, `metode_pengambilan`, `status_pesanan`, `total_harga`, `metode_bayar_pos`, `ongkir`, `nama_penerima`, `telepon_penerima`, `alamat_lengkap`, `kota_tujuan`, `kurir`, `layanan`, `snap_token`, `payment_method_ecommerce`, `paid_at`, `kode_pickup`, `created_at`, `updated_at`) VALUES
(1, 'POS-20261014-1001', 4, 'pos', 'Pick-up', 'Selesai', 95000.00, 'Tunai', 0.00, 'Pelanggan Langsung (Apotek)', '081299887766', 'ZenCare Toko Fisik (Muharto)', 'KOTA MALANG', NULL, NULL, NULL, NULL, '2026-10-14 10:15:20', NULL, '2026-10-14 10:15:20', '2026-10-14 10:15:20'),
(2, 'POS-20261016-1002', 4, 'pos', 'Pick-up', 'Selesai', 17500.00, 'QRIS', 0.00, 'Pelanggan Langsung (Apotek)', '081344556677', 'ZenCare Toko Fisik (Muharto)', 'KOTA MALANG', NULL, NULL, NULL, NULL, '2026-10-16 14:22:10', NULL, '2026-10-16 14:22:10', '2026-10-16 14:22:10'),
(3, 'POS-20261017-1003', 4, 'pos', 'Pick-up', 'Selesai', 940000.00, 'Tunai', 0.00, 'Klinik Sehat Bersama (Grosir & Eceran)', '081122334455', 'ZenCare Toko Fisik (Muharto)', 'KOTA MALANG', NULL, NULL, NULL, NULL, '2026-10-17 11:40:05', NULL, '2026-10-17 11:40:05', '2026-10-17 11:40:05'),
(4, 'INV-20261018-041', 3, 'ecommerce', 'Kurir', 'Selesai', 188000.00, 'Midtrans Payment', 18000.00, 'Budi Santoso', '083333444555', 'Jl. Soekarno Hatta No. 12, Lowokwaru', 'KOTA MALANG', 'jne', 'REG', 'snap-token-inv-041-midtrans', 'bank_transfer', '2026-10-18 09:35:00', NULL, '2026-10-18 09:30:00', '2026-10-18 11:00:00'),
(5, 'INV-20261019-052', 3, 'ecommerce', 'Kurir', 'Selesai', 945000.00, 'Midtrans Payment', 45000.00, 'Budi Santoso', '083333444555', 'Jl. Soekarno Hatta No. 12, Lowokwaru', 'KOTA MALANG', 'sicepat', 'REG', 'snap-token-inv-052-midtrans', 'qris', '2026-10-19 13:25:00', NULL, '2026-10-19 13:20:00', '2026-10-19 15:40:00'),
(6, 'POS-20261012-0901', 4, 'pos', 'Pick-up', 'Selesai', 850000.00, 'Tunai', 0.00, 'dr. Hendra Setiawan', '081234567890', 'Klinik Pratama Medika', 'KOTA MALANG', NULL, NULL, NULL, NULL, '2026-10-12 16:10:00', NULL, '2026-10-12 16:10:00', '2026-10-12 16:10:00'),
(13, 'WEB-20261021-047', 3, 'ecommerce', 'Kurir', 'Dibatalkan', 860000.00, 'Tunai', 10000.00, 'Budi Santoso', '083333444555', 'Jl. Soekarno Hatta No.12, Malang', 'MALANG', 'internal', 'ZenCare Direct Delivery (0.0 km, 100g)', '83770147-085c-4e6e-9cca-66f25ad8fd3a', NULL, NULL, NULL, '2026-10-21 15:04:53', '2026-10-21 15:05:12'),
(14, 'POS-20261022-9476', 4, 'pos', NULL, 'Selesai', 868000.00, 'Tunai', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-21 23:42:26', '2026-10-21 23:42:26'),
(15, 'WEB-20261022-072', 3, 'ecommerce', 'Kurir', 'Diproses', 864000.00, 'Tunai', 14000.00, 'Budi Santoso', '083333444555', 'Jl. Soekarno Hatta No.12, Malang', 'BATU', 'jne', 'REG', '80f0b780-21c2-4ff8-81f0-9b44c7f19b6f', NULL, NULL, NULL, '2026-10-22 00:27:25', '2026-10-22 00:34:08');

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
(1, 'IND-D4853B', 'Amoxicillin 500mg', 'Antibiotik Kapsul (Wajib Resep)', NULL, NULL, 'Obat', 'assets/img/produk/var_1.jpg', 1, 90, '2026-10-06 21:39:14'),
(2, 'IND-B42BD2', 'Sanmol Sirup Anak 60ml', 'Obat Demam Anak Rasa Stroberi', NULL, NULL, 'Obat', 'assets/img/produk/var_2.jpg', 1, 180, '2026-10-06 21:39:14'),
(3, 'IND-B35E3A', 'Strip Gula Darah EasyTouch', 'BMHP Gula Darah - Isi 25', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_3.jpg', 1, 90, '2026-10-06 21:39:14'),
(4, 'IND-4422CC', 'Tensimeter Digital Omron', 'Alat Ukur Tekanan Darah Lengan', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_4.jpg', 1, 0, '2026-10-06 21:39:14'),
(5, 'IND-BE2154', 'Termometer Digital Omron', 'Termometer Badan Digital MC-246', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_5.jpg', 1, 0, '2026-10-06 21:39:14'),
(6, 'IND-9929D6', 'Hufagrip', 'Obat Flu Anak', NULL, NULL, 'Obat', 'assets/img/produk/var_6.jpg', 1, 180, '2026-10-06 21:39:14'),
(7, 'IND-5EE388', 'Paracetamol', 'Pereda Nyeri / Penurun Panas', NULL, NULL, 'Obat', 'assets/img/produk/var_10.jpg', 1, 90, '2026-10-06 21:39:14'),
(8, 'IND-E859E3', 'Promag Tablet', 'Obat Sakit Maag & Kembung', NULL, NULL, 'Obat', 'assets/img/produk/var_13.jpg', 1, 90, '2026-10-06 21:39:14'),
(9, 'IND-9D679C', 'Panadol Extra', 'Pereda Sakit Kepala Membandel', NULL, NULL, 'Obat', 'assets/img/produk/var_14.jpg', 1, 90, '2026-10-06 21:39:14'),
(10, 'IND-CBA5E0', 'Tolak Angin Cair', 'Obat Masuk Angin', NULL, NULL, 'Obat', 'assets/img/produk/var_15.jpg', 1, 90, '2026-10-06 21:39:14'),
(11, 'IND-80EAC6', 'Mylanta Cair 150ml', 'Obat Maag Cair', NULL, NULL, 'Obat', 'assets/img/produk/var_16.jpg', 1, 90, '2026-10-06 21:39:14'),
(12, 'IND-38F7DB', 'Insto Reguler 7.5ml', 'Tetes Mata Merah', NULL, NULL, 'Obat', 'assets/img/produk/var_17.jpg', 1, 90, '2026-10-06 21:39:14'),
(13, 'IND-70AB72', 'Betadine Solution 15ml', 'Antiseptik Luka', NULL, NULL, 'Obat', 'assets/img/produk/var_18.jpg', 1, 90, '2026-10-06 21:39:14'),
(14, 'IND-ABF94E', 'Counterpain Krim 30g', 'Krim Pereda Nyeri Otot', NULL, NULL, 'Obat', 'assets/img/produk/var_19.jpg', 1, 90, '2026-10-06 21:39:14'),
(15, 'IND-E8639A', 'Neurobion Forte', 'Vitamin B Kompleks', NULL, NULL, 'Obat', 'assets/img/produk/var_20.jpg', 1, 90, '2026-10-06 21:39:14'),
(16, 'IND-E26D93', 'Sangobion Kapsul', 'Suplemen Zat Besi', NULL, NULL, 'Obat', 'assets/img/produk/var_21.jpg', 1, 90, '2026-10-06 21:39:14'),
(17, 'IND-7F92A0', 'Diapet Kapsul', 'Obat Diare', NULL, NULL, 'Obat', 'assets/img/produk/var_22.jpg', 1, 90, '2026-10-06 21:39:14'),
(18, 'IND-D23520', 'Entrostop', 'Obat Diare Dewasa', NULL, NULL, 'Obat', 'assets/img/produk/var_23.jpg', 1, 90, '2026-10-06 21:39:14'),
(19, 'IND-FCA962', 'Woods Peppermint Syr', 'Obat Batuk Antitusif', NULL, NULL, 'Obat', 'assets/img/produk/var_24.jpg', 1, 90, '2026-10-06 21:39:14'),
(20, 'IND-681C6D', 'Komix Herbal', 'Sirup Obat Batuk Tube', NULL, NULL, 'Obat', 'assets/img/produk/var_25.jpg', 1, 90, '2026-10-06 21:39:14'),
(21, 'IND-E6B0E1', 'Antangin JRG', 'Obat Herbal Masuk Angin', NULL, NULL, 'Obat', 'assets/img/produk/var_26.jpg', 1, 90, '2026-10-06 21:39:14'),
(22, 'IND-429941', 'CDR Effervescent', 'Vitamin C & Kalsium', NULL, NULL, 'Obat', 'assets/img/produk/var_27.jpg', 1, 90, '2026-10-06 21:39:14'),
(23, 'IND-619525', 'Redoxon Double Action', 'Multivitamin C & Zinc', NULL, NULL, 'Obat', 'assets/img/produk/var_28.jpg', 1, 90, '2026-10-06 21:39:14'),
(24, 'IND-F85AB5', 'Enervon-C Multivitamin', 'Vitamin C & B Kompleks', NULL, NULL, 'Obat', 'assets/img/produk/var_29.jpg', 1, 90, '2026-10-06 21:39:14'),
(25, 'IND-72ECA5', 'Imboost Force', 'Suplemen Daya Tahan Tubuh', NULL, NULL, 'Obat', 'assets/img/produk/var_30.jpg', 1, 90, '2026-10-06 21:39:14'),
(26, 'IND-95AB80', 'Bodrex Migra', 'Obat Sakit Kepala Migrain', NULL, NULL, 'Obat', 'assets/img/produk/var_31.jpg', 1, 90, '2026-10-06 21:39:14'),
(27, 'IND-EDAB79', 'Neozep Forte', 'Obat Flu & Pilek', NULL, NULL, 'Obat', 'assets/img/produk/var_32.jpg', 1, 90, '2026-10-06 21:39:14'),
(28, 'IND-CDE41C', 'Decolgen', 'Obat Flu Ringan', NULL, NULL, 'Obat', 'assets/img/produk/var_33.jpg', 1, 90, '2026-10-06 21:39:14'),
(29, 'IND-6C721E', 'OBH Combi Plus', 'Sirup Batuk & Flu', NULL, NULL, 'Obat', 'assets/img/produk/var_34.jpg', 1, 90, '2026-10-06 21:39:14'),
(30, 'IND-2EDE6B', 'Salonpas Koyo', 'Koyo Pereda Nyeri', NULL, NULL, 'Obat', 'assets/img/produk/var_35.jpg', 1, 90, '2026-10-06 21:39:14'),
(31, 'IND-516FBF', 'Vicks VapoRub 10g', 'Balsem Pelega Tenggorokan', NULL, NULL, 'Obat', 'assets/img/produk/var_36.jpg', 1, 90, '2026-10-06 21:39:14'),
(32, 'IND-75F1A9', 'Tolak Linu', 'Obat Pegal Linu Cair', NULL, NULL, 'Obat', 'assets/img/produk/var_37.jpg', 1, 90, '2026-10-06 21:39:14'),
(33, 'IND-B79B70', 'Decadryl Expectorant', 'Obat Batuk Berdahak', NULL, NULL, 'Obat', 'assets/img/produk/var_38.jpg', 1, 90, '2026-10-06 21:39:14'),
(34, 'IND-1E6A32', 'Polysilane Kapsul', 'Obat Asam Lambung', NULL, NULL, 'Obat', 'assets/img/produk/var_39.jpg', 1, 90, '2026-10-06 21:39:14'),
(35, 'IND-A35F8F', 'Combantrin Jeruk 10ml', 'Obat Cacing Sirup Anak', NULL, NULL, 'Obat', 'assets/img/produk/var_40.jpg', 1, 90, '2026-10-06 21:39:14'),
(36, 'IND-2A3D66', 'Cefadroxil 500mg', 'Antibiotik (Resep Dokter)', NULL, NULL, 'Obat', 'assets/img/produk/var_41.jpg', 1, 90, '2026-10-06 21:39:14'),
(37, 'IND-9D0FFA', 'CTM', 'Obat Alergi Ringan', NULL, NULL, 'Obat', 'assets/img/produk/var_42.jpg', 1, 90, '2026-10-06 21:39:14'),
(38, 'IND-0DA012', 'Cetirizine 10mg', 'Obat Alergi / Antihistamin', NULL, NULL, 'Obat', 'assets/img/produk/var_43.jpg', 1, 90, '2026-10-06 21:39:14'),
(39, 'IND-3EFAA1', 'Masker Sensi Earloop', 'Masker Medis 3 Ply (Isi 50)', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_44.jpg', 1, 90, '2026-10-06 21:39:14'),
(40, 'IND-04864C', 'Hansaplast Kain Elastis', 'Plester Luka', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_45.jpg', 1, 90, '2026-10-06 21:39:14'),
(41, 'IND-25FD84', 'Plester Dermafix', 'Plester Anti Air Transparan', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_46.jpg', 1, 90, '2026-10-06 21:39:14'),
(42, 'IND-195AD9', 'Kasa Steril Onemed', 'Kasa Luka Steril 16x16', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_47.jpg', 1, 90, '2026-10-06 21:39:14'),
(43, 'IND-DDF14F', 'Alkohol Onemed 70% 100ml', 'Cairan Antiseptik', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_48.jpg', 1, 90, '2026-10-06 21:39:14'),
(44, 'IND-6F0C4A', 'Rivanol 100ml', 'Cairan Pembersih Luka', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_49.jpg', 1, 90, '2026-10-06 21:39:14'),
(45, 'IND-6C597D', 'Povidone Iodine 1 Liter', 'Betadine Literan RS', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_50.jpg', 1, 90, '2026-10-06 21:39:14'),
(46, 'IND-9EBAEB', 'Alat Cek Asam Urat EasyTouch', 'BMHP Asam Urat - Isi 25', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_51.jpg', 1, 90, '2026-10-06 21:39:14'),
(47, 'IND-64BBB0', 'Alat Cek Kolesterol Nesco', 'BMHP Kolesterol - Isi 10', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_52.jpg', 1, 90, '2026-10-06 21:39:14'),
(48, 'IND-FC6027', 'Kursi Roda Standard Sella', 'Kursi Roda Lipat Ringan', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_53.jpg', 1, 0, '2026-10-06 21:39:14'),
(49, 'IND-CE2685', 'Tabung Oksigen 1m3', 'Tabung O2 Medis Kosong', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_54.jpg', 1, 0, '2026-10-06 21:39:14'),
(50, 'IND-26584F', 'Regulator Oksigen', 'Regulator Medis O2', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_55.jpg', 1, 0, '2026-10-06 21:39:14'),
(51, 'IND-A7E800', 'Nebulizer Omron', 'Alat Terapi Uap Asma NE-C104', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_56.jpg', 1, 0, '2026-10-06 21:39:14'),
(52, 'IND-3DB28F', 'Termometer Infra Merah', 'Thermometer Tembak Dahi', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_57.jpg', 1, 0, '2026-10-06 21:39:14'),
(53, 'IND-D92462', 'Stetoskop Littmann', 'Stethoscope Classic III Medis', NULL, NULL, 'Alat Kesehatan', 'assets/img/produk/var_58.jpg', 1, 0, '2026-10-06 21:39:14'),
(58, 'SIM-IND-5BOX', 'Obat Uji Skenario Dosen (5 Kardus)', 'Produk khusus demonstrasi skenario Tragedi 5 Kardus & Omnichannel FEFO.', NULL, NULL, 'Obat', NULL, 1, 0, '2026-10-21 23:40:17');

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
  `stok_minimum_kecil` int(11) DEFAULT 0,
  `stok_minimum_besar` int(11) DEFAULT 0,
  `berat` int(11) NOT NULL DEFAULT 100,
  `tampil_di_online` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `gambar` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_variasi`
--

INSERT INTO `produk_variasi` (`id`, `id_produk_induk`, `sku_variasi`, `nama_variasi`, `satuan_kecil`, `satuan_besar`, `rasio_konversi`, `harga_jual_kecil`, `harga_jual_besar`, `stok_minimum_kecil`, `stok_minimum_besar`, `berat`, `tampil_di_online`, `is_active`, `gambar`) VALUES
(1, 1, 'AMX-500', 'Tablet 500mg', 'Strip', 'Box', 10, 5000.00, 48000.00, 10, 2, 100, 1, 1, 'assets/img/produk/var_1.jpg'),
(2, 2, 'SAN-SYR', 'Botol 60ml', 'Botol', 'Karton', 24, 15000.00, 350000.00, 5, 2, 100, 1, 1, 'assets/img/produk/var_2.jpg'),
(3, 3, 'ESY-GLU', 'Tube (25 Pcs)', 'Tube', 'Tube', 1, 85000.00, 800000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_3.jpg'),
(4, 4, 'OMR-TENS', 'Unit HEM-7120', 'Unit', 'Unit', 1, 550000.00, 2600000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_4.jpg'),
(5, 5, 'OMR-THER', 'Unit MC-246', 'Unit', 'Unit', 1, 85000.00, 800000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_5.jpg'),
(6, 6, 'HUF-TMP', 'Hufagrip Merah (TMP)', 'Botol', 'Karton', 50, 18000.00, 850000.00, 5, 2, 100, 1, 1, 'assets/img/produk/var_6.jpg'),
(7, 6, 'HUF-PIL', 'Hufagrip Biru (Pilek)', 'Botol', 'Karton', 50, 18000.00, 850000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_7.jpg'),
(8, 6, 'HUF-BP', 'Hufagrip Hijau (BP)', 'Botol', 'Karton', 50, 18000.00, 850000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_8.jpg'),
(9, 6, 'HUF-FLU', 'Hufagrip Kuning (Flu Batuk)', 'Botol', 'Karton', 50, 18000.00, 850000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_9.jpg'),
(10, 7, 'PCT-500', 'Tablet 500mg', 'Strip', 'Box', 10, 3500.00, 30000.00, 5, 2, 100, 1, 1, 'assets/img/produk/var_10.jpg'),
(11, 7, 'PCT-250', 'Tablet 250mg', 'Strip', 'Box', 10, 2500.00, 22000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_11.jpg'),
(12, 7, 'PCT-125', 'Tablet 125mg', 'Strip', 'Box', 10, 2000.00, 18000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_12.jpg'),
(13, 8, 'PRM-01', 'Tablet Kunyah', 'Strip', 'Box', 12, 9500.00, 110000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_13.jpg'),
(14, 9, 'PND-EXT', 'Kaplet Merah', 'Strip', 'Box', 10, 12500.00, 120000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_14.jpg'),
(15, 10, 'TLK-ANG', 'Sachet 15ml', 'Sachet', 'Box', 12, 4500.00, 52000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_15.jpg'),
(16, 11, 'MYL-150', 'Botol 150ml', 'Botol', 'Karton', 24, 45000.00, 1050000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_16.jpg'),
(17, 12, 'INS-REG', 'Botol 7.5ml', 'Botol', 'Box', 12, 16000.00, 185000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_17.jpg'),
(18, 13, 'BET-15', 'Botol 15ml', 'Botol', 'Box', 12, 12500.00, 145000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_18.jpg'),
(19, 14, 'CTP-30', 'Tube 30g', 'Tube', 'Box', 10, 55000.00, 530000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_19.jpg'),
(20, 15, 'NEU-FRT', 'Tablet Merah', 'Strip', 'Box', 10, 48000.00, 460000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_20.jpg'),
(21, 16, 'SNG-CAP', 'Kapsul Merah', 'Strip', 'Box', 25, 18500.00, 450000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_21.jpg'),
(22, 17, 'DPT-CAP', 'Kapsul Isi 10', 'Strip', 'Box', 25, 4500.00, 105000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_22.jpg'),
(23, 18, 'ENT-DSW', 'Tablet', 'Strip', 'Box', 20, 9500.00, 180000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_23.jpg'),
(24, 19, 'WOD-PEP', 'Botol 100ml', 'Botol', 'Karton', 24, 38000.00, 890000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_24.jpg'),
(25, 20, 'KMX-HRB', 'Sachet 15ml', 'Sachet', 'Box', 30, 2500.00, 70000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_25.jpg'),
(26, 21, 'ANT-JRG', 'Sachet 15ml', 'Sachet', 'Box', 12, 4000.00, 46000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_26.jpg'),
(27, 22, 'CDR-EFT', 'Tube Isi 15', 'Tube', 'Karton', 10, 65000.00, 630000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_27.jpg'),
(28, 23, 'RDX-DBL', 'Tube Isi 10', 'Tube', 'Karton', 10, 55000.00, 530000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_28.jpg'),
(29, 24, 'ENV-C', 'Strip Isi 4', 'Strip', 'Box', 25, 6000.00, 145000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_29.jpg'),
(30, 25, 'IMB-FRC', 'Strip Isi 10', 'Strip', 'Box', 3, 85000.00, 245000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_30.jpg'),
(31, 26, 'BDR-MGR', 'Strip Isi 4', 'Strip', 'Box', 25, 3500.00, 85000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_31.jpg'),
(32, 27, 'NZP-FRT', 'Strip Isi 4', 'Strip', 'Box', 25, 3500.00, 85000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_32.jpg'),
(33, 28, 'DCL-GEN', 'Strip Isi 4', 'Strip', 'Box', 25, 3000.00, 72000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_33.jpg'),
(34, 29, 'OBH-CPL', 'Botol 100ml', 'Botol', 'Karton', 24, 25000.00, 580000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_34.jpg'),
(35, 30, 'SLN-PAS', 'Sachet Isi 10', 'Sachet', 'Box', 40, 8500.00, 320000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_35.jpg'),
(36, 31, 'VK-VP10', 'Pot 10g', 'Pot', 'Box', 24, 12000.00, 275000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_36.jpg'),
(37, 32, 'TLK-LNU', 'Sachet 15ml', 'Sachet', 'Box', 12, 4500.00, 52000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_37.jpg'),
(38, 33, 'DCD-EXP', 'Botol 120ml', 'Botol', 'Karton', 24, 22000.00, 510000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_38.jpg'),
(39, 34, 'PLS-CAP', 'Strip Isi 10', 'Strip', 'Box', 10, 12000.00, 115000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_39.jpg'),
(40, 35, 'CMB-JRK', 'Botol 10ml', 'Botol', 'Box', 12, 21000.00, 245000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_40.jpg'),
(41, 36, 'CFD-500', 'Strip Isi 10', 'Strip', 'Box', 10, 18000.00, 170000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_41.jpg'),
(42, 37, 'CTM-ALR', 'Strip Isi 12', 'Strip', 'Box', 10, 2500.00, 23000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_42.jpg'),
(43, 38, 'CTR-10', 'Strip Isi 10', 'Strip', 'Box', 10, 8500.00, 80000.00, NULL, 2, 100, 1, 1, 'assets/img/produk/var_43.jpg'),
(44, 39, 'SNS-MSK', 'Box Isi 50', 'Box', 'Box', 1, 25000.00, 950000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_44.jpg'),
(45, 40, 'HNS-PLS', 'Box Isi 100', 'Lembar', 'Lembar', 1, 800.00, 75000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_45.jpg'),
(46, 41, 'DRM-FIX', 'Pcs 5x7cm', 'Pcs', 'Pcs', 1, 4500.00, 105000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_46.jpg'),
(47, 42, 'OM-KSA', 'Kotak Isi 10', 'Kotak', 'Kotak', 1, 12000.00, 580000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_47.jpg'),
(48, 43, 'OM-ALK', 'Botol 100ml', 'Botol', 'Botol', 1, 8500.00, 195000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_48.jpg'),
(49, 44, 'RVN-100', 'Botol 100ml', 'Botol', 'Botol', 1, 7500.00, 175000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_49.jpg'),
(50, 45, 'PVD-1L', 'Botol 1L', 'Botol', 'Botol', 1, 125000.00, 1200000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_50.jpg'),
(51, 46, 'ESY-UA', 'Tube Isi 25', 'Tube', 'Tube', 1, 95000.00, 900000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_51.jpg'),
(52, 47, 'NSC-CHL', 'Tube Isi 10', 'Tube', 'Tube', 1, 135000.00, 1300000.00, NULL, 5, 100, 1, 1, 'assets/img/produk/var_52.jpg'),
(53, 48, 'SLL-KRD', 'Unit Standar', 'Unit', 'Unit', 1, 1450000.00, 1400000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_53.jpg'),
(54, 49, 'O2-1M3', 'Tabung 1m3', 'Tabung', 'Tabung', 1, 750000.00, 720000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_54.jpg'),
(55, 50, 'O2-REG', 'Unit Flowmeter', 'Unit', 'Unit', 1, 250000.00, 2400000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_55.jpg'),
(56, 51, 'OMR-NEB', 'Unit NE-C104', 'Unit', 'Unit', 1, 850000.00, 4100000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_56.jpg'),
(57, 52, 'IR-THER', 'Unit Pistol', 'Unit', 'Unit', 1, 125000.00, 2400000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_57.jpg'),
(58, 53, 'LTM-STC', 'Unit Classic III', 'Unit', 'Unit', 1, 1850000.00, 18000000.00, 5, 5, 100, 1, 1, 'assets/img/produk/var_58.jpg'),
(60, 6, 'HUF-BPA', 'Hufagrip BP Anak', 'Botol', 'Karton', 50, 18000.00, 850000.00, NULL, 2, 100, 1, 1, ''),
(61, 58, 'SIM-5BOX', 'Karton Isi 10 Box', 'Box', 'Karton', 10, 28000.00, 250000.00, 0, 0, 500, 1, 1, NULL);

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
(1, 1, 'AMX-OLD-2025', '2025-08-15', 0, '2025-08-15 08:00:00'),
(2, 1, 'AMX-2026-01', '2027-12-15', 10, '2026-09-15 08:30:00'),
(3, 1, 'AMX-2026-01.A', '2027-12-15', 8, '2026-10-05 09:15:00'),
(4, 2, 'SNM-2026-A', '2028-05-10', 192, '2026-09-25 09:15:00'),
(5, 2, 'SNM-2026-A.A', '2028-05-10', 24, '2026-10-07 10:30:00'),
(6, 2, 'SNM-EXP-22', '2026-10-22', 5, '2026-08-10 10:00:00'),
(7, 3, 'BMHP-ESYGLU-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(8, 6, 'HUF-TMP-26', '2027-08-15', 350, '2026-09-10 09:00:00'),
(9, 6, 'HUF-TMP-26.A', '2027-08-15', 48, '2026-10-09 13:45:00'),
(10, 6, 'HUF-TMP-27', '2028-06-20', 400, '2026-09-25 09:15:00'),
(11, 6, 'HUF-TMP-27.A', '2028-06-20', 47, '2026-10-11 15:30:00'),
(12, 7, 'HUFPIL-2027-01', '2028-08-15', 650, '2026-09-10 10:00:00'),
(13, 8, 'HUFBP-2027-01', '2028-08-15', 700, '2026-09-10 10:00:00'),
(14, 9, 'HUFFLU-2027-01', '2028-08-15', 750, '2026-09-10 10:00:00'),
(15, 10, 'PCT-OLD-2025', '2025-10-01', 0, '2025-10-01 08:00:00'),
(16, 10, 'PCT-500-26', '2027-11-20', 180, '2026-09-15 08:30:00'),
(17, 10, 'PCT-500-26.A', '2027-11-20', 7, '2026-10-08 11:00:00'),
(18, 10, 'PCT-500-26.B', '2027-11-20', 8, '2026-10-10 14:20:00'),
(19, 11, 'PCT250-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(20, 12, 'PCT125-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(21, 13, 'PRM01-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(22, 14, 'PNDEXT-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(23, 15, 'TLKANG-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(24, 16, 'MYL150-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'),
(25, 17, 'INSREG-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(26, 18, 'BET15-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(27, 19, 'CTP30-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(28, 20, 'NEUFRT-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(29, 21, 'SNGCAP-2027-01', '2028-08-15', 375, '2026-09-10 10:00:00'),
(30, 22, 'DPT-2027', '2027-09-10', 25, '2026-09-10 10:00:00'),
(31, 23, 'ENTDSW-2027-01', '2028-08-15', 300, '2026-09-10 10:00:00'),
(32, 24, 'WODPEP-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'),
(33, 25, 'KMXHRB-2027-01', '2028-08-15', 450, '2026-09-10 10:00:00'),
(34, 26, 'ANTJRG-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(35, 27, 'CDREFT-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(36, 28, 'RDXDBL-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(37, 29, 'ENVC-2027-01', '2028-08-15', 375, '2026-09-10 10:00:00'),
(38, 30, 'IMB-FRC-26', '2027-09-15', 58, '2026-10-05 10:00:00'),
(39, 31, 'BDR-2028', '2028-03-12', 250, '2026-09-10 10:00:00'),
(40, 31, 'BDR-EXP-23', '2026-10-23', 4, '2026-08-15 10:00:00'),
(41, 32, 'NZPFRT-2027-01', '2028-08-15', 375, '2026-09-10 10:00:00'),
(42, 33, 'DCL-2027', '2027-10-15', 25, '2026-09-10 10:00:00'),
(43, 34, 'OBHCPL-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'),
(44, 35, 'SLNPAS-2027-01', '2028-08-15', 600, '2026-09-10 10:00:00'),
(45, 36, 'VKVP10-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'),
(46, 37, 'TLKLNU-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(47, 38, 'DCDEXP-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'),
(48, 39, 'PLSCAP-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(49, 40, 'CMBJRK-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'),
(50, 41, 'CFD500-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(51, 42, 'CTMALR-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(52, 43, 'CTR10-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'),
(53, 44, 'BMHP-SNSMSK-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(54, 45, 'BMHP-HNSPLS-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(55, 46, 'BMHP-DRMFIX-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(56, 47, 'BMHP-OMKSA-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(57, 48, 'BMHP-OMALK-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(58, 49, 'BMHP-RVN100-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(59, 50, 'BMHP-PVD1L-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(60, 51, 'BMHP-ESYUA-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(61, 52, 'BMHP-NSCCHL-28', '2028-11-20', 15, '2026-09-10 10:00:00'),
(62, 60, 'HUFBPA-2027-01', '2028-08-15', 750, '2026-09-10 10:00:00'),
(150, 61, 'BATCH-DOSEN-A', '2027-01-20', 20, '2026-10-21 23:40:20'),
(151, 61, 'BATCH-DOSEN-B', '2027-04-20', 30, '2026-10-21 23:40:20'),
(152, 7, 'hufpil-2027-01.A', '2028-08-15', 49, '2026-10-21 23:42:03');

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
(1, 1, 18),
(2, 2, 221),
(3, 3, 15),
(4, 4, 9),
(5, 5, 9),
(6, 6, 845),
(7, 7, 699),
(8, 8, 700),
(9, 9, 750),
(10, 10, 195),
(11, 11, 150),
(12, 12, 150),
(13, 13, 180),
(14, 14, 150),
(15, 15, 180),
(16, 16, 360),
(17, 17, 180),
(18, 18, 180),
(19, 19, 150),
(20, 20, 150),
(21, 21, 375),
(22, 22, 25),
(23, 23, 300),
(24, 24, 360),
(25, 25, 450),
(26, 26, 180),
(27, 27, 150),
(28, 28, 150),
(29, 29, 375),
(30, 30, 58),
(31, 31, 254),
(32, 32, 375),
(33, 33, 25),
(34, 34, 360),
(35, 35, 600),
(36, 36, 360),
(37, 37, 180),
(38, 38, 360),
(39, 39, 150),
(40, 40, 180),
(41, 41, 150),
(42, 42, 150),
(43, 43, 150),
(44, 44, 15),
(45, 45, 15),
(46, 46, 15),
(47, 47, 15),
(48, 48, 15),
(49, 49, 15),
(50, 50, 15),
(51, 51, 15),
(52, 52, 15),
(53, 53, 2),
(54, 54, 12),
(55, 55, 3),
(56, 56, 9),
(57, 57, 12),
(58, 58, 12),
(59, 60, 750),
(62, 61, 50);

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
(1, 4, 'OMR-TENS-01', 'Terjual', 5, NULL, '2028-10-19', '2026-09-25 09:15:00', '2026-10-19 13:20:00'),
(2, 4, 'OMR-TENS-02', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(3, 4, 'OMR-TENS-03', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(4, 4, 'OMR-TENS-04', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(5, 4, 'OMR-TENS-05', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(6, 4, 'OMR-TENS-06', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(7, 4, 'OMR-TENS-07', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(8, 4, 'OMR-TENS-08', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(9, 4, 'OMR-TENS-09', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(10, 4, 'OMR-TENS-10', 'Tersedia', NULL, NULL, '2028-10-19', '2026-09-25 09:15:00', NULL),
(11, 5, 'OMR-TH-01', 'Terjual', 1, NULL, '2027-10-14', '2026-09-15 08:30:00', '2026-10-14 10:15:20'),
(12, 5, 'OMR-TH-02', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(13, 5, 'OMR-TH-03', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(14, 5, 'OMR-TH-04', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(15, 5, 'OMR-TH-05', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(16, 5, 'OMR-TH-06', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(17, 5, 'OMR-TH-07', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(18, 5, 'OMR-TH-08', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(19, 5, 'OMR-TH-09', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(20, 5, 'OMR-TH-10', 'Tersedia', NULL, NULL, '2027-10-14', '2026-09-15 08:30:00', NULL),
(21, 53, 'SLL-KRD-01', 'Tersedia', NULL, NULL, '2028-09-01', '2026-09-01 09:00:00', NULL),
(22, 53, 'SLL-KRD-02', 'Tersedia', NULL, NULL, '2028-09-01', '2026-09-01 09:00:00', NULL),
(23, 54, 'O21M3-001', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(24, 54, 'O21M3-002', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(25, 54, 'O21M3-003', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(26, 54, 'O21M3-004', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(27, 54, 'O21M3-005', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(28, 54, 'O21M3-006', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(29, 54, 'O21M3-007', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(30, 54, 'O21M3-008', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(31, 54, 'O21M3-009', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(32, 54, 'O21M3-010', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(33, 54, 'O21M3-011', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(34, 54, 'O21M3-012', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(35, 55, 'O2-REG-01', 'Tersedia', NULL, NULL, '2027-09-01', '2026-09-01 09:00:00', NULL),
(36, 55, 'O2-REG-02', 'Tersedia', NULL, NULL, '2027-09-01', '2026-09-01 09:00:00', NULL),
(37, 55, 'O2-REG-03', 'Tersedia', NULL, NULL, '2027-09-01', '2026-09-01 09:00:00', NULL),
(38, 56, 'OMR-NEB-99', 'Retur/Rusak', 6, 'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi)', '2028-10-12', '2026-09-20 10:00:00', '2026-10-15 11:20:00'),
(39, 56, 'OMR-NEB-01', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(40, 56, 'OMR-NEB-02', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(41, 56, 'OMR-NEB-03', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(42, 56, 'OMR-NEB-04', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(43, 56, 'OMR-NEB-05', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(44, 56, 'OMR-NEB-06', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(45, 56, 'OMR-NEB-07', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(46, 56, 'OMR-NEB-08', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(47, 56, 'OMR-NEB-09', 'Tersedia', NULL, NULL, '2028-10-12', '2026-09-20 10:00:00', NULL),
(48, 57, 'IRTHER-001', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(49, 57, 'IRTHER-002', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(50, 57, 'IRTHER-003', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(51, 57, 'IRTHER-004', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(52, 57, 'IRTHER-005', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(53, 57, 'IRTHER-006', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(54, 57, 'IRTHER-007', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(55, 57, 'IRTHER-008', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(56, 57, 'IRTHER-009', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(57, 57, 'IRTHER-010', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(58, 57, 'IRTHER-011', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(59, 57, 'IRTHER-012', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(60, 58, 'LTMSTC-001', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(61, 58, 'LTMSTC-002', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(62, 58, 'LTMSTC-003', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(63, 58, 'LTMSTC-004', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(64, 58, 'LTMSTC-005', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(65, 58, 'LTMSTC-006', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(66, 58, 'LTMSTC-007', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(67, 58, 'LTMSTC-008', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(68, 58, 'LTMSTC-009', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(69, 58, 'LTMSTC-010', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(70, 58, 'LTMSTC-011', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL),
(71, 58, 'LTMSTC-012', 'Tersedia', NULL, NULL, '2028-06-30', '2026-09-10 10:00:00', NULL);

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
(1, 'superadmin', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'SuperAdmin (Pemilik)', 'superadmin', 'superadmin@zencare.id', '081234567890', NULL, NULL, 1, '2026-09-10 21:42:09'),
(2, 'admin_toko', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Admin Toko', 'admin', 'admin@zencare.id', '082111222333', NULL, NULL, 1, '2026-09-10 21:42:09'),
(3, 'pelanggan1', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Budi Santoso', 'pelanggan', 'budi.santoso@gmail.com', '083333444555', 'Jl. Soekarno Hatta No.12, Malang', 393, 1, '2026-09-10 21:42:09'),
(4, 'kasir_toko', '$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe', 'Kasir (Front Office)', 'kasir', 'kasir@zencare.id', '08222333444', NULL, NULL, 1, '2026-09-29 04:16:12');

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
-- Indeks untuk tabel `histori_konversi`
--
ALTER TABLE `histori_konversi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_variasi` (`id_variasi`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `histori_konversi`
--
ALTER TABLE `histori_konversi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `kartu_stok`
--
ALTER TABLE `kartu_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `log_anomali_fefo`
--
ALTER TABLE `log_anomali_fefo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penerimaan_detail`
--
ALTER TABLE `penerimaan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `penerimaan_stok`
--
ALTER TABLE `penerimaan_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_web`
--
ALTER TABLE `pengaturan_web`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `produk_induk`
--
ALTER TABLE `produk_induk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT untuk tabel `produk_variasi`
--
ALTER TABLE `produk_variasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `stok_batch`
--
ALTER TABLE `stok_batch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT untuk tabel `stok_toko`
--
ALTER TABLE `stok_toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `unit_serial`
--
ALTER TABLE `unit_serial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

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
-- Ketidakleluasaan untuk tabel `histori_konversi`
--
ALTER TABLE `histori_konversi`
  ADD CONSTRAINT `histori_konversi_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  ADD CONSTRAINT `histori_konversi_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`);

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
