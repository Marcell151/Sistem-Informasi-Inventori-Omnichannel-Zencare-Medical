<?php
// scratch/generate_complete_sql.php
require_once __DIR__ . '/../config/koneksi.php';

echo "Generating complete SQL for db_inventory (demo) 1.sql...\n";

function sqlVal($val) {
    if ($val === null) return "NULL";
    if (is_int($val)) return (string)$val;
    if (is_float($val)) return (string)$val;
    return "'" . addslashes((string)$val) . "'";
}

$lines = [];
$lines[] = "-- phpMyAdmin SQL Dump";
$lines[] = "-- version 5.2.1";
$lines[] = "-- https://www.phpmyadmin.net/";
$lines[] = "--";
$lines[] = "-- Host: 127.0.0.1";
$lines[] = "-- Waktu pembuatan: " . date('d M Y \p\a\d\a H.i');
$lines[] = "-- Versi server: 10.4.32-MariaDB";
$lines[] = "-- Versi PHP: 8.2.12";
$lines[] = "";
$lines[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
$lines[] = "SET FOREIGN_KEY_CHECKS = 0;";
$lines[] = "START TRANSACTION;";
$lines[] = "SET time_zone = \"+00:00\";";
$lines[] = "";
$lines[] = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;";
$lines[] = "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;";
$lines[] = "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;";
$lines[] = "/*!40101 SET NAMES utf8mb4 */;";
$lines[] = "";
$lines[] = "--";
$lines[] = "-- Database: `db_inventory`";
$lines[] = "--";
$lines[] = "";
$lines[] = "-- Drop existing tables in reverse dependency order";
$lines[] = "DROP TABLE IF EXISTS `detail_penjualan`;";
$lines[] = "DROP TABLE IF EXISTS `penjualan`;";
$lines[] = "DROP TABLE IF EXISTS `kartu_stok`;";
$lines[] = "DROP TABLE IF EXISTS `histori_konversi`;";
$lines[] = "DROP TABLE IF EXISTS `log_anomali_fefo`;";
$lines[] = "DROP TABLE IF EXISTS `penerimaan_detail`;";
$lines[] = "DROP TABLE IF EXISTS `penerimaan_stok`;";
$lines[] = "DROP TABLE IF EXISTS `unit_serial`;";
$lines[] = "DROP TABLE IF EXISTS `stok_batch`;";
$lines[] = "DROP TABLE IF EXISTS `stok_toko`;";
$lines[] = "DROP TABLE IF EXISTS `produk_variasi`;";
$lines[] = "DROP TABLE IF EXISTS `produk_induk`;";
$lines[] = "DROP TABLE IF EXISTS `supplier`;";
$lines[] = "DROP TABLE IF EXISTS `pengaturan_web`;";
$lines[] = "DROP TABLE IF EXISTS `users`;";
$lines[] = "";

// =========================================================================
// 1. PENGATURAN WEB
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `pengaturan_web`";
$lines[] = "CREATE TABLE `pengaturan_web` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `nama_toko` varchar(100) NOT NULL DEFAULT 'ZenCare Medical',";
$lines[] = "  `deskripsi` text DEFAULT NULL,";
$lines[] = "  `telepon` varchar(20) DEFAULT NULL,";
$lines[] = "  `whatsapp` varchar(20) DEFAULT NULL,";
$lines[] = "  `alamat` text DEFAULT NULL,";
$lines[] = "  `logo` varchar(255) DEFAULT NULL,";
$lines[] = "  `banner_promosi` varchar(255) DEFAULT NULL,";
$lines[] = "  `email_cs` varchar(150) DEFAULT NULL";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";
$lines[] = "INSERT INTO `pengaturan_web` (`id`, `nama_toko`, `deskripsi`, `telepon`, `whatsapp`, `alamat`, `logo`, `banner_promosi`, `email_cs`) VALUES";
$lines[] = "(1, 'ZenCare Medical', 'Distributor Resmi Alat Kesehatan & Obat-Obatan Terpercaya di Malang', '0341-111222', '6281234567890', 'Jl. Muharto No.1, Kota Malang, Jawa Timur 65118', NULL, NULL, 'cs@zencare.id');";
$lines[] = "";

// =========================================================================
// 2. USERS
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `users`";
$lines[] = "CREATE TABLE `users` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `username` varchar(50) NOT NULL,";
$lines[] = "  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',";
$lines[] = "  `nama_lengkap` varchar(100) NOT NULL,";
$lines[] = "  `role` enum('superadmin','admin','kasir','pelanggan') NOT NULL,";
$lines[] = "  `email` varchar(150) DEFAULT NULL,";
$lines[] = "  `telepon` varchar(20) DEFAULT NULL,";
$lines[] = "  `alamat` text DEFAULT NULL COMMENT 'Alamat utama pelanggan untuk pengiriman',";
$lines[] = "  `kota_id` int(11) DEFAULT NULL COMMENT 'ID kota RajaOngkir untuk kalkulasi ongkir',";
$lines[] = "  `is_active` tinyint(1) DEFAULT 1,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$userVals = [];
foreach ($users as $u) {
    $userVals[] = "(" . implode(", ", [
        $u['id'],
        sqlVal($u['username']),
        sqlVal($u['password']),
        sqlVal($u['nama_lengkap']),
        sqlVal($u['role']),
        sqlVal($u['email']),
        sqlVal($u['telepon']),
        sqlVal($u['alamat']),
        $u['kota_id'] !== null ? $u['kota_id'] : "NULL",
        $u['is_active'],
        sqlVal($u['created_at'])
    ]) . ")";
}
$lines[] = "INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `email`, `telepon`, `alamat`, `kota_id`, `is_active`, `created_at`) VALUES";
$lines[] = implode(",\n", $userVals) . ";";
$lines[] = "";

// =========================================================================
// 3. SUPPLIER
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `supplier`";
$lines[] = "CREATE TABLE `supplier` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `nama` varchar(100) NOT NULL,";
$lines[] = "  `kontak` varchar(50) DEFAULT NULL,";
$lines[] = "  `email` varchar(150) DEFAULT NULL,";
$lines[] = "  `alamat` text DEFAULT NULL,";
$lines[] = "  `is_active` tinyint(1) DEFAULT 1";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$suppVals = [];
foreach ($suppliers as $s) {
    $suppVals[] = "(" . implode(", ", [
        $s['id'],
        sqlVal($s['nama']),
        sqlVal($s['kontak']),
        sqlVal($s['email']),
        sqlVal($s['alamat']),
        $s['is_active']
    ]) . ")";
}
$lines[] = "INSERT INTO `supplier` (`id`, `nama`, `kontak`, `email`, `alamat`, `is_active`) VALUES";
$lines[] = implode(",\n", $suppVals) . ";";
$lines[] = "";

// =========================================================================
// 4. PRODUK INDUK
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `produk_induk`";
$lines[] = "CREATE TABLE `produk_induk` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `sku_induk` varchar(50) NOT NULL,";
$lines[] = "  `nama_produk` varchar(150) NOT NULL,";
$lines[] = "  `deskripsi` text DEFAULT NULL,";
$lines[] = "  `spesifikasi` text DEFAULT NULL COMMENT 'Spesifikasi teknis medis',";
$lines[] = "  `info_pengiriman` text DEFAULT NULL,";
$lines[] = "  `kategori` enum('Obat','Alat Kesehatan') NOT NULL,";
$lines[] = "  `gambar` varchar(255) DEFAULT NULL,";
$lines[] = "  `is_active` tinyint(1) DEFAULT 1,";
$lines[] = "  `batas_hari_expired` int(11) DEFAULT 0,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$pInduks = $pdo->query("SELECT * FROM produk_induk ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$pIndukVals = [];
foreach ($pInduks as $pi) {
    $pIndukVals[] = "(" . implode(", ", [
        $pi['id'],
        sqlVal($pi['sku_induk']),
        sqlVal($pi['nama_produk']),
        sqlVal($pi['deskripsi']),
        sqlVal($pi['spesifikasi']),
        sqlVal($pi['info_pengiriman']),
        sqlVal($pi['kategori']),
        sqlVal($pi['gambar']),
        $pi['is_active'],
        (int)$pi['batas_hari_expired'],
        sqlVal($pi['created_at'])
    ]) . ")";
}
$lines[] = "INSERT INTO `produk_induk` (`id`, `sku_induk`, `nama_produk`, `deskripsi`, `spesifikasi`, `info_pengiriman`, `kategori`, `gambar`, `is_active`, `batas_hari_expired`, `created_at`) VALUES";
$lines[] = implode(",\n", $pIndukVals) . ";";
$lines[] = "";

// =========================================================================
// 5. PRODUK VARIASI (Multi-Gambar Lengkap Terjaga)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `produk_variasi`";
$lines[] = "CREATE TABLE `produk_variasi` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_produk_induk` int(11) DEFAULT NULL,";
$lines[] = "  `sku_variasi` varchar(50) NOT NULL,";
$lines[] = "  `nama_variasi` varchar(100) NOT NULL,";
$lines[] = "  `satuan_kecil` varchar(50) NOT NULL DEFAULT 'Pcs',";
$lines[] = "  `satuan_besar` varchar(50) NOT NULL DEFAULT 'Box',";
$lines[] = "  `rasio_konversi` smallint(6) NOT NULL DEFAULT 1,";
$lines[] = "  `harga_jual_kecil` decimal(12,2) NOT NULL DEFAULT 0.00,";
$lines[] = "  `harga_jual_besar` decimal(12,2) NOT NULL DEFAULT 0.00,";
$lines[] = "  `stok_minimum_kecil` int(11) DEFAULT 0,";
$lines[] = "  `stok_minimum_besar` int(11) DEFAULT 0,";
$lines[] = "  `berat` int(11) NOT NULL DEFAULT 100,";
$lines[] = "  `tampil_di_online` tinyint(1) DEFAULT 1,";
$lines[] = "  `is_active` tinyint(1) DEFAULT 1,";
$lines[] = "  `gambar` varchar(500) DEFAULT NULL";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$pVars = $pdo->query("SELECT * FROM produk_variasi ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$pVarVals = [];
foreach ($pVars as $pv) {
    $pVarVals[] = "(" . implode(", ", [
        $pv['id'],
        $pv['id_produk_induk'] !== null ? $pv['id_produk_induk'] : "NULL",
        sqlVal($pv['sku_variasi']),
        sqlVal($pv['nama_variasi']),
        sqlVal($pv['satuan_kecil']),
        sqlVal($pv['satuan_besar']),
        (int)$pv['rasio_konversi'],
        $pv['harga_jual_kecil'],
        $pv['harga_jual_besar'],
        $pv['stok_minimum_kecil'] !== null ? (int)$pv['stok_minimum_kecil'] : "NULL",
        $pv['stok_minimum_besar'] !== null ? (int)$pv['stok_minimum_besar'] : "NULL",
        (int)$pv['berat'],
        $pv['tampil_di_online'],
        $pv['is_active'],
        sqlVal($pv['gambar'])
    ]) . ")";
}
$lines[] = "INSERT INTO `produk_variasi` (`id`, `id_produk_induk`, `sku_variasi`, `nama_variasi`, `satuan_kecil`, `satuan_besar`, `rasio_konversi`, `harga_jual_kecil`, `harga_jual_besar`, `stok_minimum_kecil`, `stok_minimum_besar`, `berat`, `tampil_di_online`, `is_active`, `gambar`) VALUES";
$lines[] = implode(",\n", $pVarVals) . ";";
$lines[] = "";

// =========================================================================
// 6. PENERIMAAN STOK (6 Transaksi Bervariasi Sesuai Permintaan Dosen)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `penerimaan_stok`";
$lines[] = "CREATE TABLE `penerimaan_stok` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `no_referensi` varchar(50) NOT NULL,";
$lines[] = "  `sumber` enum('PO','Pembelian Langsung') NOT NULL,";
$lines[] = "  `id_supplier` int(11) DEFAULT NULL,";
$lines[] = "  `tanggal_terima` date NOT NULL,";
$lines[] = "  `catatan` text DEFAULT NULL,";
$lines[] = "  `file_nota` varchar(255) DEFAULT NULL,";
$lines[] = "  `dibuat_oleh` int(11) DEFAULT NULL,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$penerimaanList = [
    [
        'id' => 1,
        'no_referensi' => 'PO-20261015-OBAT5',
        'sumber' => 'PO',
        'id_supplier' => 1,
        'tanggal_terima' => '2026-10-15',
        'catatan' => 'Penerimaan stok awal 5 jenis obat farmasi resmi dari PT Kimia Farma Trading & Distribution',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-15 08:30:00'
    ],
    [
        'id' => 2,
        'no_referensi' => 'PO-20261018-ALKES4',
        'sumber' => 'PO',
        'id_supplier' => 2,
        'tanggal_terima' => '2026-10-18',
        'catatan' => 'Penerimaan 4 jenis alat kesehatan diagnostik bergaransi resmi PT Omron Healthcare (Nomor Seri unit terdaftar)',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-18 09:15:00'
    ],
    [
        'id' => 3,
        'no_referensi' => 'KUL-20261020-CAMPUR',
        'sumber' => 'Pembelian Langsung',
        'id_supplier' => 3,
        'tanggal_terima' => '2026-10-20',
        'catatan' => 'Kulakan langsung sediaan obat maag & flu serta BMHP alat kesehatan habis pakai volume besar dari PT OneMed',
        'file_nota' => null,
        'dibuat_oleh' => 4,
        'created_at' => '2026-10-20 10:00:00'
    ],
    [
        'id' => 4,
        'no_referensi' => 'PO-20261022-OBAT-RETUR',
        'sumber' => 'PO',
        'id_supplier' => 1,
        'tanggal_terima' => '2026-10-22',
        'catatan' => 'PO 4 jenis obat - Ditemukan 2 box Cefadroxil dan 3 karton Woods pecah/rusak. Pihak Kimia Farma telah mengganti penuh barang pengganti (Klaim Tukar Selesai 100%).',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-22 11:20:00'
    ],
    [
        'id' => 5,
        'no_referensi' => 'PO-20261023-ALKES-RETUR',
        'sumber' => 'PO',
        'id_supplier' => 2,
        'tanggal_terima' => '2026-10-23',
        'catatan' => 'PO 5 jenis alat kesehatan - Ditemukan 2 unit Tensimeter (LCD garis) dan 1 unit Termometer (sensor mati). Berkas klaim telah diproses ke PT Omron (Menunggu Pengganti).',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-23 13:45:00'
    ],
    [
        'id' => 6,
        'no_referensi' => 'KUL-20261024-CAMPUR-RETUR',
        'sumber' => 'Pembelian Langsung',
        'id_supplier' => 3,
        'tanggal_terima' => '2026-10-24',
        'catatan' => 'Kulakan 3 obat & 3 alkes - 2 box Decolgen basah rembes dan 1 Tabung O2 katup bocor. Sedang proses klaim garansi retur ke OneMed (Menunggu Pengganti).',
        'file_nota' => null,
        'dibuat_oleh' => 4,
        'created_at' => '2026-10-24 15:30:00'
    ]
];

$penVals = [];
foreach ($penerimaanList as $p) {
    $penVals[] = "(" . implode(", ", [
        $p['id'],
        sqlVal($p['no_referensi']),
        sqlVal($p['sumber']),
        $p['id_supplier'],
        sqlVal($p['tanggal_terima']),
        sqlVal($p['catatan']),
        sqlVal($p['file_nota']),
        $p['dibuat_oleh'],
        sqlVal($p['created_at'])
    ]) . ")";
}
$lines[] = "INSERT INTO `penerimaan_stok` (`id`, `no_referensi`, `sumber`, `id_supplier`, `tanggal_terima`, `catatan`, `file_nota`, `dibuat_oleh`, `created_at`) VALUES";
$lines[] = implode(",\n", $penVals) . ";";
$lines[] = "";

// =========================================================================
// 7. PENERIMAAN DETAIL (Dengan Kolom Fitur Retur Lengkap)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `penerimaan_detail`";
$lines[] = "CREATE TABLE `penerimaan_detail` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_penerimaan` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `qty_pesan` int(11) NOT NULL DEFAULT 0,";
$lines[] = "  `qty_terima` int(11) NOT NULL,";
$lines[] = "  `qty_rusak` int(11) NOT NULL DEFAULT 0,";
$lines[] = "  `harga_beli` decimal(15,2) DEFAULT NULL,";
$lines[] = "  `no_batch` varchar(50) DEFAULT NULL,";
$lines[] = "  `tgl_exp` date DEFAULT NULL,";
$lines[] = "  `status_retur` varchar(30) NOT NULL DEFAULT 'tidak_ada',";
$lines[] = "  `qty_pengganti_diterima` int(11) NOT NULL DEFAULT 0,";
$lines[] = "  `no_batch_pengganti` varchar(50) DEFAULT NULL,";
$lines[] = "  `tgl_exp_pengganti` date DEFAULT NULL,";
$lines[] = "  `tgl_selesai_retur` datetime DEFAULT NULL,";
$lines[] = "  `catatan_retur` text DEFAULT NULL";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$detailList = [
    // Transaksi 1: PO 5 Jenis Obat (Kimia Farma)
    [1, 1, 1, 15, 15, 0, 40000.00, 'AMX-2026-01', '2027-12-15', 'tidak_ada', 0, null, null, null, null],
    [2, 1, 10, 25, 25, 0, 25000.00, 'PCT-500-26', '2027-11-20', 'tidak_ada', 0, null, null, null, null],
    [3, 1, 2, 12, 12, 0, 300000.00, 'SNM-2026-A', '2028-05-10', 'tidak_ada', 0, null, null, null, null],
    [4, 1, 6, 10, 10, 0, 750000.00, 'HUF-TMP-27', '2028-06-20', 'tidak_ada', 0, null, null, null, null],
    [5, 1, 30, 20, 20, 0, 210000.00, 'IMB-FRC-26', '2027-09-15', 'tidak_ada', 0, null, null, null, null],

    // Transaksi 2: PO 4 Jenis Alkes (Omron)
    [6, 2, 4, 5, 5, 0, 450000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [7, 2, 5, 5, 5, 0, 65000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [8, 2, 56, 4, 4, 0, 680000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [9, 2, 57, 6, 6, 0, 95000.00, null, null, 'tidak_ada', 0, null, null, null, null],

    // Transaksi 3: Kulakan Campuran 2 Obat + 2 Alkes Kuantitas Banyak (OneMed)
    [10, 3, 13, 30, 30, 0, 95000.00, 'PRM-KLK-26', '2027-11-30', 'tidak_ada', 0, null, null, null, null],
    [11, 3, 14, 25, 25, 0, 105000.00, 'PND-KLK-26', '2027-11-30', 'tidak_ada', 0, null, null, null, null],
    [12, 3, 44, 50, 50, 0, 20000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [13, 3, 48, 40, 40, 0, 6500.00, null, null, 'tidak_ada', 0, null, null, null, null],

    // Transaksi 4: PO 4 Obat dengan Retur 2 Obat SELESAI Tukar Guling (Kimia Farma)
    [14, 4, 41, 10, 8, 2, 150000.00, 'CFD-500-26', '2027-11-30', 'selesai_tukar_barang', 2, 'CFD-GNT-26', '2028-02-15', '2026-10-25 10:30:00', '2 box blister rusak penyok telah ditukar 2 box pengganti utuh batch CFD-GNT-26'],
    [15, 4, 24, 12, 9, 3, 780000.00, 'WOD-PEP-26', '2028-04-10', 'selesai_tukar_barang', 3, 'WOD-GNT-26', '2028-05-20', '2026-10-25 11:15:00', '3 karton botol pecah di kardus telah diganti 3 karton baru batch WOD-GNT-26'],
    [16, 4, 27, 10, 10, 0, 550000.00, 'CDR-EFT-26', '2027-11-30', 'tidak_ada', 0, null, null, null, null],
    [17, 4, 29, 15, 15, 0, 125000.00, 'ENV-C-26', '2028-03-15', 'tidak_ada', 0, null, null, null, null],

    // Transaksi 5: PO 5 Alkes dengan Retur 2 Alkes MENUNGGU PENGGANTI (Omron)
    [18, 5, 4, 6, 4, 2, 450000.00, null, null, 'menunggu_pengganti', 0, null, null, null, '2 unit layar LCD mati/garis [SN Cacat: OMR-TENS-RET1, OMR-TENS-RET2]. Menunggu replacement unit dari Omron.'],
    [19, 5, 5, 8, 7, 1, 65000.00, null, null, 'menunggu_pengganti', 0, null, null, null, '1 unit tombol power macet [SN Cacat: OMR-TH-RET1]. Menunggu tukar guling.'],
    [20, 5, 56, 3, 3, 0, 680000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [21, 5, 58, 3, 3, 0, 1500000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [22, 5, 53, 2, 2, 0, 1200000.00, null, null, 'tidak_ada', 0, null, null, null, null],

    // Transaksi 6: Kulakan 3 Obat & 3 Alkes dengan Retur 1 Obat & 1 Alkes MENUNGGU (OneMed)
    [23, 6, 33, 10, 8, 2, 62000.00, 'DCL-GEN-26', '2027-10-15', 'menunggu_pengganti', 0, null, null, null, '2 box kemasan basah rembes saat ekspedisi toko, diajukan penukaran ke PBF OneMed'],
    [24, 6, 18, 15, 15, 0, 120000.00, 'BET-15-26', '2027-11-30', 'tidak_ada', 0, null, null, null, null],
    [25, 6, 22, 20, 20, 0, 90000.00, 'DPT-CAP-26', '2027-09-10', 'tidak_ada', 0, null, null, null, null],
    [26, 6, 54, 5, 4, 1, 650000.00, null, null, 'menunggu_pengganti', 0, null, null, null, '1 tabung drat kran valve bocor gas [SN Cacat: O21M3-RET1]. Menunggu penggantian tabung baru dari depot OneMed.'],
    [27, 6, 55, 3, 3, 0, 210000.00, null, null, 'tidak_ada', 0, null, null, null, null],
    [28, 6, 47, 50, 50, 0, 9500.00, null, null, 'tidak_ada', 0, null, null, null, null]
];

$detVals = [];
foreach ($detailList as $d) {
    $detVals[] = "(" . implode(", ", [
        $d[0],
        $d[1],
        $d[2],
        $d[3],
        $d[4],
        $d[5],
        $d[6],
        sqlVal($d[7]),
        sqlVal($d[8]),
        sqlVal($d[9]),
        $d[10],
        sqlVal($d[11]),
        sqlVal($d[12]),
        sqlVal($d[13]),
        sqlVal($d[14])
    ]) . ")";
}
$lines[] = "INSERT INTO `penerimaan_detail` (`id`, `id_penerimaan`, `id_variasi`, `qty_pesan`, `qty_terima`, `qty_rusak`, `harga_beli`, `no_batch`, `tgl_exp`, `status_retur`, `qty_pengganti_diterima`, `no_batch_pengganti`, `tgl_exp_pengganti`, `tgl_selesai_retur`, `catatan_retur`) VALUES";
$lines[] = implode(",\n", $detVals) . ";";
$lines[] = "";

// =========================================================================
// 8. UNIT SERIAL (Garansi Aktif, Retur Pelanggan Garansi, & Retur Supplier)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `unit_serial`";
$lines[] = "CREATE TABLE `unit_serial` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `serial_number` varchar(150) NOT NULL,";
$lines[] = "  `status` enum('Tersedia','Terjual','Retur/Rusak') NOT NULL DEFAULT 'Tersedia',";
$lines[] = "  `id_penjualan` int(11) DEFAULT NULL,";
$lines[] = "  `catatan` varchar(255) DEFAULT NULL,";
$lines[] = "  `tgl_habis_garansi` date DEFAULT NULL,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
$lines[] = "  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$serials = [
    // Tensimeter Omron (Var 4): 1 Terjual garansi aktif, 8 Tersedia, 2 Rusak retur supplier
    [1, 4, 'OMR-TENS-01', 'Terjual', 5, null, '2028-10-19', '2026-09-25 09:15:00', '2026-10-19 13:20:00'],
    [2, 4, 'OMR-TENS-02', 'Tersedia', null, null, '2028-10-19', '2026-09-25 09:15:00', null],
    [3, 4, 'OMR-TENS-03', 'Tersedia', null, null, '2028-10-19', '2026-09-25 09:15:00', null],
    [4, 4, 'OMR-TENS-04', 'Tersedia', null, null, '2028-10-19', '2026-09-25 09:15:00', null],
    [5, 4, 'OMR-TENS-05', 'Tersedia', null, null, '2028-10-19', '2026-09-25 09:15:00', null],
    [6, 4, 'OMR-TENS-06', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],
    [7, 4, 'OMR-TENS-07', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],
    [8, 4, 'OMR-TENS-08', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],
    [9, 4, 'OMR-TENS-09', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],
    [10, 4, 'OMR-TENS-RET1', 'Retur/Rusak', null, 'Cacat Pabrik: Layar LCD garis vertikal saat unboxing (Klaim PO: PO-20261023-ALKES-RETUR)', null, '2026-10-23 13:45:00', null],
    [11, 4, 'OMR-TENS-RET2', 'Retur/Rusak', null, 'Cacat Pabrik: Motor pompa cuff bocor angin (Klaim PO: PO-20261023-ALKES-RETUR)', null, '2026-10-23 13:45:00', null],

    // Termometer Digital Omron (Var 5): 1 Terjual garansi aktif, 11 Tersedia, 1 Rusak retur supplier
    [12, 5, 'OMR-TH-01', 'Terjual', 1, null, '2027-10-14', '2026-09-15 08:30:00', '2026-10-14 10:15:20'],
    [13, 5, 'OMR-TH-02', 'Tersedia', null, null, '2027-10-14', '2026-09-15 08:30:00', null],
    [14, 5, 'OMR-TH-03', 'Tersedia', null, null, '2027-10-14', '2026-09-15 08:30:00', null],
    [15, 5, 'OMR-TH-04', 'Tersedia', null, null, '2027-10-14', '2026-09-15 08:30:00', null],
    [16, 5, 'OMR-TH-05', 'Tersedia', null, null, '2027-10-14', '2026-09-15 08:30:00', null],
    [17, 5, 'OMR-TH-06', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [18, 5, 'OMR-TH-07', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [19, 5, 'OMR-TH-08', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [20, 5, 'OMR-TH-09', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [21, 5, 'OMR-TH-10', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [22, 5, 'OMR-TH-11', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [23, 5, 'OMR-TH-12', 'Tersedia', null, null, '2027-10-23', '2026-10-23 13:45:00', null],
    [24, 5, 'OMR-TH-RET1', 'Retur/Rusak', null, 'Cacat Pabrik: Error E-01 sensor mati (Klaim PO: PO-20261023-ALKES-RETUR)', null, '2026-10-23 13:45:00', null],

    // Kursi Roda Sella (Var 53): 2 Tersedia
    [25, 53, 'SLL-KRD-01', 'Tersedia', null, null, '2028-09-01', '2026-09-01 09:00:00', null],
    [26, 53, 'SLL-KRD-02', 'Tersedia', null, null, '2028-09-01', '2026-09-01 09:00:00', null],

    // Tabung Oksigen 1m3 (Var 54): 4 Tersedia, 1 Rusak retur supplier
    [27, 54, 'O21M3-001', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [28, 54, 'O21M3-002', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [29, 54, 'O21M3-003', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [30, 54, 'O21M3-004', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [31, 54, 'O21M3-RET1', 'Retur/Rusak', null, 'Drat kran valve bocor gas (Klaim Kulakan: KUL-20261024-CAMPUR-RETUR)', null, '2026-10-24 15:30:00', null],

    // Regulator Oksigen (Var 55): 3 Tersedia
    [32, 55, 'O2-REG-01', 'Tersedia', null, null, '2027-09-01', '2026-09-01 09:00:00', null],
    [33, 55, 'O2-REG-02', 'Tersedia', null, null, '2027-09-01', '2026-09-01 09:00:00', null],
    [34, 55, 'O2-REG-03', 'Tersedia', null, null, '2027-09-01', '2026-09-01 09:00:00', null],

    // Nebulizer Omron (Var 56): 1 Terjual Retur Pelanggan Garansi Aktif, 6 Tersedia
    [35, 56, 'OMR-NEB-99', 'Retur/Rusak', 6, 'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi)', '2028-10-12', '2026-09-20 10:00:00', '2026-10-15 11:20:00'],
    [36, 56, 'OMR-NEB-01', 'Tersedia', null, null, '2028-10-12', '2026-09-20 10:00:00', null],
    [37, 56, 'OMR-NEB-02', 'Tersedia', null, null, '2028-10-12', '2026-09-20 10:00:00', null],
    [38, 56, 'OMR-NEB-03', 'Tersedia', null, null, '2028-10-12', '2026-09-20 10:00:00', null],
    [39, 56, 'OMR-NEB-04', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],
    [40, 56, 'OMR-NEB-05', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],
    [41, 56, 'OMR-NEB-06', 'Tersedia', null, null, '2028-10-23', '2026-10-23 13:45:00', null],

    // Termometer Infra Merah (Var 57): 6 Tersedia
    [42, 57, 'IRTHER-001', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [43, 57, 'IRTHER-002', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [44, 57, 'IRTHER-003', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [45, 57, 'IRTHER-004', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [46, 57, 'IRTHER-005', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [47, 57, 'IRTHER-006', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],

    // Stetoskop Littmann (Var 58): 3 Tersedia
    [48, 58, 'LTMSTC-001', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [49, 58, 'LTMSTC-002', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null],
    [50, 58, 'LTMSTC-003', 'Tersedia', null, null, '2028-06-30', '2026-09-10 10:00:00', null]
];

$serVals = [];
foreach ($serials as $s) {
    $serVals[] = "(" . implode(", ", [
        $s[0],
        $s[1],
        sqlVal($s[2]),
        sqlVal($s[3]),
        $s[4] !== null ? $s[4] : "NULL",
        sqlVal($s[5]),
        sqlVal($s[6]),
        sqlVal($s[7]),
        sqlVal($s[8])
    ]) . ")";
}
$lines[] = "INSERT INTO `unit_serial` (`id`, `id_variasi`, `serial_number`, `status`, `id_penjualan`, `catatan`, `tgl_habis_garansi`, `created_at`, `updated_at`) VALUES";
$lines[] = implode(",\n", $serVals) . ";";
$lines[] = "";

// =========================================================================
// 9. PENJUALAN (POS & E-Commerce Lengkap)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `penjualan`";
$lines[] = "CREATE TABLE `penjualan` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `no_invoice` varchar(50) NOT NULL,";
$lines[] = "  `id_user` int(11) DEFAULT NULL,";
$lines[] = "  `tipe_transaksi` enum('pos','ecommerce') NOT NULL,";
$lines[] = "  `metode_pengambilan` enum('Kurir','Pick-up') DEFAULT NULL,";
$lines[] = "  `status_pesanan` enum('Menunggu Pembayaran','Diproses','Dikirim','Siap Diambil','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',";
$lines[] = "  `total_harga` decimal(12,2) NOT NULL,";
$lines[] = "  `metode_bayar_pos` varchar(50) DEFAULT 'Tunai',";
$lines[] = "  `ongkir` decimal(10,2) DEFAULT 0.00,";
$lines[] = "  `nama_penerima` varchar(100) DEFAULT NULL,";
$lines[] = "  `telepon_penerima` varchar(20) DEFAULT NULL,";
$lines[] = "  `alamat_lengkap` text DEFAULT NULL,";
$lines[] = "  `kota_tujuan` varchar(100) DEFAULT NULL,";
$lines[] = "  `kurir` varchar(50) DEFAULT NULL,";
$lines[] = "  `layanan` varchar(50) DEFAULT NULL,";
$lines[] = "  `snap_token` varchar(255) DEFAULT NULL,";
$lines[] = "  `payment_method_ecommerce` varchar(50) DEFAULT NULL,";
$lines[] = "  `paid_at` timestamp NULL DEFAULT NULL,";
$lines[] = "  `kode_pickup` varchar(20) DEFAULT NULL,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
$lines[] = "  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$penjualanList = [
    [1, 'POS-20261014-1001', 4, 'pos', 'Pick-up', 'Selesai', 95000.00, 'Tunai', 0.00, 'Pelanggan Langsung (Apotek)', '081299887766', 'ZenCare Toko Fisik (Muharto)', 'KOTA MALANG', null, null, null, null, '2026-10-14 10:15:20', null, '2026-10-14 10:15:20', '2026-10-14 10:15:20'],
    [2, 'POS-20261016-1002', 4, 'pos', 'Pick-up', 'Selesai', 17500.00, 'QRIS', 0.00, 'Pelanggan Langsung (Apotek)', '081344556677', 'ZenCare Toko Fisik (Muharto)', 'KOTA MALANG', null, null, null, null, '2026-10-16 14:22:10', null, '2026-10-16 14:22:10', '2026-10-16 14:22:10'],
    [3, 'POS-20261017-1003', 4, 'pos', 'Pick-up', 'Selesai', 940000.00, 'Tunai', 0.00, 'Klinik Sehat Bersama (Grosir & Eceran)', '081122334455', 'ZenCare Toko Fisik (Muharto)', 'KOTA MALANG', null, null, null, null, '2026-10-17 11:40:05', null, '2026-10-17 11:40:05', '2026-10-17 11:40:05'],
    [4, 'INV-20261018-041', 3, 'ecommerce', 'Kurir', 'Selesai', 188000.00, 'Midtrans Payment', 18000.00, 'Budi Santoso', '083333444555', 'Jl. Soekarno Hatta No. 12, Lowokwaru', 'KOTA MALANG', 'jne', 'REG', 'snap-token-inv-041-midtrans', 'bank_transfer', '2026-10-18 09:35:00', null, '2026-10-18 09:30:00', '2026-10-18 11:00:00'],
    [5, 'INV-20261019-052', 3, 'ecommerce', 'Kurir', 'Selesai', 945000.00, 'Midtrans Payment', 45000.00, 'Budi Santoso', '083333444555', 'Jl. Soekarno Hatta No. 12, Lowokwaru', 'KOTA MALANG', 'sicepat', 'REG', 'snap-token-inv-052-midtrans', 'qris', '2026-10-19 13:25:00', null, '2026-10-19 13:20:00', '2026-10-19 15:40:00'],
    [6, 'POS-20261012-0901', 4, 'pos', 'Pick-up', 'Selesai', 850000.00, 'Tunai', 0.00, 'dr. Hendra Setiawan', '081234567890', 'Klinik Pratama Medika', 'KOTA MALANG', null, null, null, null, '2026-10-12 16:10:00', null, '2026-10-12 16:10:00', '2026-10-12 16:10:00']
];

$jualVals = [];
foreach ($penjualanList as $j) {
    $jualVals[] = "(" . implode(", ", [
        $j[0],
        sqlVal($j[1]),
        $j[2],
        sqlVal($j[3]),
        sqlVal($j[4]),
        sqlVal($j[5]),
        $j[6],
        sqlVal($j[7]),
        $j[8],
        sqlVal($j[9]),
        sqlVal($j[10]),
        sqlVal($j[11]),
        sqlVal($j[12]),
        sqlVal($j[13]),
        sqlVal($j[14]),
        sqlVal($j[15]),
        sqlVal($j[16]),
        sqlVal($j[17]),
        sqlVal($j[18]),
        sqlVal($j[19]),
        sqlVal($j[20])
    ]) . ")";
}
$lines[] = "INSERT INTO `penjualan` (`id`, `no_invoice`, `id_user`, `tipe_transaksi`, `metode_pengambilan`, `status_pesanan`, `total_harga`, `metode_bayar_pos`, `ongkir`, `nama_penerima`, `telepon_penerima`, `alamat_lengkap`, `kota_tujuan`, `kurir`, `layanan`, `snap_token`, `payment_method_ecommerce`, `paid_at`, `kode_pickup`, `created_at`, `updated_at`) VALUES";
$lines[] = implode(",\n", $jualVals) . ";";
$lines[] = "";

// =========================================================================
// 10. DETAIL PENJUALAN
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `detail_penjualan`";
$lines[] = "CREATE TABLE `detail_penjualan` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_penjualan` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `qty` int(11) NOT NULL,";
$lines[] = "  `harga_satuan` decimal(12,2) NOT NULL,";
$lines[] = "  `catatan_logistik` text DEFAULT NULL";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$detJualList = [
    [1, 1, 1, 2, 5000.00, 'AMX-2026-01.A (2x)'],
    [2, 1, 5, 1, 85000.00, 'OMR-TH-01'],
    [3, 2, 10, 5, 3500.00, 'PCT-500-26.A (3x), PCT-500-26.B (2x)'],
    [4, 3, 6, 5, 18000.00, 'HUF-TMP-26.A (2x), HUF-TMP-27.A (3x)'],
    [5, 3, 6, 1, 850000.00, 'HUF-TMP-27 (1 Karton = 50 Botol)'],
    [6, 4, 30, 2, 85000.00, 'IMB-FRC-26 (2x)'],
    [7, 5, 2, 1, 350000.00, 'SNM-2026-A (1 Karton = 24 Botol)'],
    [8, 5, 4, 1, 550000.00, 'OMR-TENS-01'],
    [9, 6, 56, 1, 850000.00, 'OMR-NEB-99']
];

$djVals = [];
foreach ($detJualList as $dj) {
    $djVals[] = "(" . implode(", ", [
        $dj[0],
        $dj[1],
        $dj[2],
        $dj[3],
        $dj[4],
        sqlVal($dj[5])
    ]) . ")";
}
$lines[] = "INSERT INTO `detail_penjualan` (`id`, `id_penjualan`, `id_variasi`, `qty`, `harga_satuan`, `catatan_logistik`) VALUES";
$lines[] = implode(",\n", $djVals) . ";";
$lines[] = "";

// =========================================================================
// 11. HISTORI KONVERSI (Satuan Besar ke Kecil)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `histori_konversi`";
$lines[] = "CREATE TABLE `histori_konversi` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `batch_asal` varchar(100) NOT NULL,";
$lines[] = "  `batch_hasil` varchar(100) NOT NULL,";
$lines[] = "  `qty_box_buka` int(11) NOT NULL,";
$lines[] = "  `qty_pcs_hasil` int(11) NOT NULL,";
$lines[] = "  `sisa_dus_akhir` int(11) DEFAULT 0,";
$lines[] = "  `dibuat_oleh` int(11) DEFAULT NULL,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$lines[] = "";

$konversiList = [
    [1, 1, 'AMX-2026-01', 'AMX-2026-01.A', 1, 10, 14, 4, '2026-10-05 09:15:00'],
    [2, 2, 'SNM-2026-A', 'SNM-2026-A.A', 1, 24, 11, 2, '2026-10-07 10:30:00'],
    [3, 10, 'PCT-500-26', 'PCT-500-26.A', 1, 10, 24, 4, '2026-10-08 11:00:00'],
    [4, 10, 'PCT-500-26', 'PCT-500-26.B', 1, 10, 23, 4, '2026-10-10 14:20:00'],
    [5, 6, 'HUF-TMP-26', 'HUF-TMP-26.A', 1, 50, 6, 4, '2026-10-09 13:45:00'],
    [6, 6, 'HUF-TMP-27', 'HUF-TMP-27.A', 1, 50, 9, 2, '2026-10-11 15:30:00'],
    [7, 13, 'PRM-KLK-26', 'PRM-KLK-26.A', 1, 12, 29, 4, '2026-10-21 11:00:00'],
    [8, 24, 'WOD-PEP-26', 'WOD-PEP-26.A', 1, 24, 11, 2, '2026-10-23 16:30:00']
];

$konvVals = [];
foreach ($konversiList as $k) {
    $konvVals[] = "(" . implode(", ", [
        $k[0],
        $k[1],
        sqlVal($k[2]),
        sqlVal($k[3]),
        $k[4],
        $k[5],
        $k[6],
        $k[7],
        sqlVal($k[8])
    ]) . ")";
}
$lines[] = "INSERT INTO `histori_konversi` (`id`, `id_variasi`, `batch_asal`, `batch_hasil`, `qty_box_buka`, `qty_pcs_hasil`, `sisa_dus_akhir`, `dibuat_oleh`, `created_at`) VALUES";
$lines[] = implode(",\n", $konvVals) . ";";
$lines[] = "";

// =========================================================================
// 12. STOK BATCH (Dengan Kadaluarsa Hari Ini 2026-10-26 & Besok 2026-10-27)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `stok_batch`";
$lines[] = "CREATE TABLE `stok_batch` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `no_batch` varchar(100) NOT NULL,";
$lines[] = "  `tgl_exp` date NOT NULL,";
$lines[] = "  `stok_sisa` int(11) NOT NULL DEFAULT 0,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

// Periksa permintaan:
// Hari Ini: 2026-10-26
// Besok: 2026-10-27
// Warning Segera Kadaluarsa: Nov 2026 & Des 2026
// Batch Normal: 2027 - 2028
$stokBatchList = [
    // 1. Batch Lama Yang Sudah Dimusnahkan (Sisa 0)
    [1, 1, 'AMX-OLD-2025', '2025-08-15', 0, '2025-08-15 08:00:00'],
    [2, 10, 'PCT-OLD-2025', '2025-10-01', 0, '2025-10-01 08:00:00'],

    // 2. Batch KADALUARSA HARI INI (2026-10-26) - Permintaan Khusus User
    [3, 2, 'SNM-EXP-TODAY', '2026-10-26', 5, '2026-08-10 10:00:00'],

    // 3. Batch KADALUARSA BESOK (2026-10-27) - Permintaan Khusus User
    [4, 31, 'BDR-EXP-TOMORROW', '2026-10-27', 4, '2026-08-15 10:00:00'],

    // 4. Batch MENDEKATI KADALUARSA (Warning FEFO)
    [5, 42, 'CTM-EXP-SOON', '2026-11-15', 8, '2026-08-20 10:00:00'],
    [6, 6, 'HUF-EXP-SOON', '2026-12-05', 10, '2026-08-25 10:00:00'],

    // 5. Batch Penerimaan 1 s/d 6 & Sub-Batch Konversi
    // Var 1 (Amoxicillin 500mg, rasio 10)
    [7, 1, 'AMX-2026-01', '2027-12-15', 14, '2026-10-15 08:30:00'], // 15 box masuk - 1 box konversi = 14 box
    [8, 1, 'AMX-2026-01.A', '2027-12-15', 8, '2026-10-05 09:15:00'],  // 10 strip hasil konversi - 2 terjual = 8 strip

    // Var 2 (Sanmol Sirup 60ml, rasio 24)
    [9, 2, 'SNM-2026-A', '2028-05-10', 10, '2026-10-15 08:30:00'], // 12 masuk - 1 konversi - 1 ecom = 10 karton
    [10, 2, 'SNM-2026-A.A', '2028-05-10', 24, '2026-10-07 10:30:00'], // 24 botol hasil konversi

    // Var 3 (BMHP Strip Gula Darah EasyTouch)
    [11, 3, 'BMHP-ESYGLU-28', '2028-11-20', 15, '2026-09-10 10:00:00'],

    // Var 6 (Hufagrip Merah TMP, rasio 50)
    [12, 6, 'HUF-TMP-26', '2027-08-15', 6, '2026-09-10 09:00:00'], // 7 - 1 konversi = 6 karton
    [13, 6, 'HUF-TMP-26.A', '2027-08-15', 48, '2026-10-09 13:45:00'], // 50 - 2 terjual = 48 botol
    [14, 6, 'HUF-TMP-27', '2028-06-20', 8, '2026-10-15 08:30:00'], // 10 masuk - 1 konversi - 1 terjual grosir = 8 karton
    [15, 6, 'HUF-TMP-27.A', '2028-06-20', 47, '2026-10-11 15:30:00'], // 50 - 3 terjual = 47 botol

    // Var 7, 8, 9 (Hufagrip Varian Lain)
    [16, 7, 'HUFPIL-2027-01', '2028-08-15', 750, '2026-09-10 10:00:00'],
    [17, 8, 'HUFBP-2027-01', '2028-08-15', 750, '2026-09-10 10:00:00'],
    [18, 9, 'HUFFLU-2027-01', '2028-08-15', 750, '2026-09-10 10:00:00'],

    // Var 10 (Paracetamol 500mg, rasio 10)
    [19, 10, 'PCT-500-26', '2027-11-20', 23, '2026-10-15 08:30:00'], // 25 masuk - 2 konversi = 23 box
    [20, 10, 'PCT-500-26.A', '2027-11-20', 7, '2026-10-08 11:00:00'],  // 10 - 3 terjual = 7 strip
    [21, 10, 'PCT-500-26.B', '2027-11-20', 8, '2026-10-10 14:20:00'],  // 10 - 2 terjual = 8 strip

    // Var 11, 12 (Paracetamol dosis lain)
    [22, 11, 'PCT250-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],
    [23, 12, 'PCT125-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],

    // Var 13 (Promag Tablet, rasio 12)
    [24, 13, 'PRM-KLK-26', '2027-11-30', 29, '2026-10-20 10:00:00'], // 30 masuk - 1 konversi = 29 box
    [25, 13, 'PRM-KLK-26.A', '2027-11-30', 12, '2026-10-21 11:00:00'], // 12 strip hasil konversi

    // Var 14 (Panadol Extra, rasio 10)
    [26, 14, 'PND-KLK-26', '2027-11-30', 25, '2026-10-20 10:00:00'], // 25 box

    // Var 15 s/d 23 (Obat Bebas & Suplemen)
    [27, 15, 'TLKANG-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'],
    [28, 16, 'MYL150-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'],
    [29, 17, 'INSREG-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'],
    [30, 18, 'BET-15-26', '2027-11-30', 15, '2026-10-24 15:30:00'],
    [31, 19, 'CTP30-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],
    [32, 20, 'NEUFRT-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],
    [33, 21, 'SNGCAP-2027-01', '2028-08-15', 375, '2026-09-10 10:00:00'],
    [34, 22, 'DPT-CAP-26', '2027-09-10', 20, '2026-10-24 15:30:00'],
    [35, 23, 'ENTDSW-2027-01', '2028-08-15', 300, '2026-09-10 10:00:00'],

    // Var 24 (Woods Peppermint Syr, rasio 24)
    [36, 24, 'WOD-PEP-26', '2028-04-10', 8, '2026-10-22 11:20:00'], // 9 masuk awal - 1 konversi = 8 karton
    [37, 24, 'WOD-GNT-26', '2028-05-20', 3, '2026-10-25 11:15:00'], // 3 karton pengganti retur
    [38, 24, 'WOD-PEP-26.A', '2028-04-10', 24, '2026-10-23 16:30:00'], // 24 botol hasil konversi

    // Var 25 s/d 29
    [39, 25, 'KMXHRB-2027-01', '2028-08-15', 450, '2026-09-10 10:00:00'],
    [40, 26, 'ANTJRG-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'],
    [41, 27, 'CDR-EFT-26', '2027-11-30', 10, '2026-10-22 11:20:00'],
    [42, 28, 'RDXDBL-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],
    [43, 29, 'ENV-C-26', '2028-03-15', 15, '2026-10-22 11:20:00'],

    // Var 30 (Imboost Force)
    [44, 30, 'IMB-FRC-26', '2027-09-15', 58, '2026-10-15 08:30:00'], // 60 strip (20 box x 3) - 2 terjual ecom = 58 strip

    // Var 31 (Bodrex Migra)
    [45, 31, 'BDR-2028', '2028-03-12', 250, '2026-09-10 10:00:00'],

    // Var 32 s/d 38
    [46, 32, 'NZPFRT-2027-01', '2028-08-15', 375, '2026-09-10 10:00:00'],
    [47, 33, 'DCL-GEN-26', '2027-10-15', 8, '2026-10-24 15:30:00'], // 8 box diterima utuh
    [48, 34, 'OBHCPL-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'],
    [49, 35, 'SLNPAS-2027-01', '2028-08-15', 600, '2026-09-10 10:00:00'],
    [50, 36, 'VKVP10-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'],
    [51, 37, 'TLKLNU-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'],
    [52, 38, 'DCDEXP-2027-01', '2028-08-15', 360, '2026-09-10 10:00:00'],
    [53, 39, 'PLSCAP-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],
    [54, 40, 'CMBJRK-2027-01', '2027-11-30', 180, '2026-09-10 10:00:00'],

    // Var 41 (Cefadroxil 500mg, PO Retur Selesai Tukar)
    [55, 41, 'CFD-500-26', '2027-11-30', 8, '2026-10-22 11:20:00'], // 8 box awal
    [56, 41, 'CFD-GNT-26', '2028-02-15', 2, '2026-10-25 10:30:00'], // 2 box pengganti retur

    // Var 42, 43
    [57, 42, 'CTMALR-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],
    [58, 43, 'CTR10-2027-01', '2027-11-30', 150, '2026-09-10 10:00:00'],

    // BMHP Alkes
    [59, 44, 'BMHP-SNSMSK-28', '2028-11-20', 65, '2026-09-10 10:00:00'],
    [60, 45, 'BMHP-HNSPLS-28', '2028-11-20', 15, '2026-09-10 10:00:00'],
    [61, 46, 'BMHP-DRMFIX-28', '2028-11-20', 115, '2026-09-10 10:00:00'],
    [62, 47, 'BMHP-OMKSA-28', '2028-11-20', 65, '2026-09-10 10:00:00'],
    [63, 48, 'BMHP-OMALK-28', '2028-11-20', 55, '2026-09-10 10:00:00'],
    [64, 49, 'BMHP-RVN100-28', '2028-11-20', 15, '2026-09-10 10:00:00'],
    [65, 50, 'BMHP-PVD1L-28', '2028-11-20', 15, '2026-09-10 10:00:00'],
    [66, 51, 'BMHP-ESYUA-28', '2028-11-20', 15, '2026-09-10 10:00:00'],
    [67, 52, 'BMHP-NSCCHL-28', '2028-11-20', 15, '2026-09-10 10:00:00'],
    [68, 60, 'HUFBPA-2027-01', '2028-08-15', 750, '2026-09-10 10:00:00']
];

$sbVals = [];
foreach ($stokBatchList as $sb) {
    $sbVals[] = "(" . implode(", ", [
        $sb[0],
        $sb[1],
        sqlVal($sb[2]),
        sqlVal($sb[3]),
        $sb[4],
        sqlVal($sb[5])
    ]) . ")";
}
$lines[] = "INSERT INTO `stok_batch` (`id`, `id_variasi`, `no_batch`, `tgl_exp`, `stok_sisa`, `created_at`) VALUES";
$lines[] = implode(",\n", $sbVals) . ";";
$lines[] = "";

// =========================================================================
// 13. STOK TOKO (Dihitung Konsisten Sesuai Batch & Serials)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `stok_toko`";
$lines[] = "CREATE TABLE `stok_toko` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `stok` int(11) NOT NULL DEFAULT 0";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

// Hitung stok per variasi
$stokPerVar = [];
foreach ($pVars as $pv) {
    $vId = $pv['id'];
    $stokPerVar[$vId] = 0;
}

// 1. Tambah dari stok_batch
foreach ($stokBatchList as $sb) {
    $vId = $sb[1];
    $sisa = $sb[4];
    if (isset($stokPerVar[$vId])) {
        // Cek apakah batch ini adalah kemasan besar (Dus/Box/Karton)
        // Jika batch adalah kemasan besar, konversi ke satuan kecil untuk stok_toko
        $pInfo = null;
        foreach ($pVars as $pv) {
            if ($pv['id'] == $vId) { $pInfo = $pv; break; }
        }
        $rasio = $pInfo ? (int)$pInfo['rasio_konversi'] : 1;
        // Jika batch berakhiran .A atau .B atau bernilai kecil, itu satuan kecil
        if (strpos($sb[2], '.A') !== false || strpos($sb[2], '.B') !== false || $rasio == 1) {
            $stokPerVar[$vId] += $sisa;
        } else {
            $stokPerVar[$vId] += ($sisa * $rasio);
        }
    }
}

// 2. Tambah dari unit_serial yang Tersedia
foreach ($serials as $sr) {
    $vId = $sr[1];
    $status = $sr[3];
    if ($status === 'Tersedia' && isset($stokPerVar[$vId])) {
        $stokPerVar[$vId] += 1;
    }
}

$stokTokoVals = [];
$tokoId = 1;
foreach ($pVars as $pv) {
    $vId = $pv['id'];
    $stokAkhir = max(0, $stokPerVar[$vId] ?? 0);
    $stokTokoVals[] = "($tokoId, $vId, $stokAkhir)";
    $tokoId++;
}

$lines[] = "INSERT INTO `stok_toko` (`id`, `id_variasi`, `stok`) VALUES";
$lines[] = implode(",\n", $stokTokoVals) . ";";
$lines[] = "";

// =========================================================================
// 14. KARTU STOK (Audit Ledger Lengkap: Penerimaan, Mutasi, POS, E-Commerce)
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `kartu_stok`";
$lines[] = "CREATE TABLE `kartu_stok` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `satuan_tipe` enum('besar','kecil') NOT NULL DEFAULT 'kecil',";
$lines[] = "  `jenis_mutasi` enum('Masuk','Keluar','Penyesuaian') NOT NULL,";
$lines[] = "  `kanal` enum('POS','E-Commerce','Manual','Penerimaan','PO') NOT NULL DEFAULT 'Manual',";
$lines[] = "  `alasan_mutasi` enum('Penerimaan Barang','Penjualan POS','Penjualan E-Commerce','Retur Barang Rusak','Klaim Garansi SN','Selisih Stok Opname','Barang Kedaluwarsa','Koreksi Manual') NOT NULL DEFAULT 'Koreksi Manual',";
$lines[] = "  `no_ref_dokumen` varchar(100) DEFAULT NULL,";
$lines[] = "  `qty` int(11) NOT NULL,";
$lines[] = "  `sisa_stok` int(11) NOT NULL,";
$lines[] = "  `keterangan` text DEFAULT NULL,";
$lines[] = "  `dibuat_oleh` int(11) DEFAULT NULL,";
$lines[] = "  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$lines[] = "";

$kartuStokList = [
    // 1. Pemusnahan Batch Kedaluwarsa
    [1, 1, 'besar', 'Keluar', 'Manual', 'Barang Kedaluwarsa', 'MUT-EXP-20260901-01', 15, 2, 'Pemusnahan Batch Kadaluarsa [Batch: AMX-OLD-2025]', 2, '2026-09-01 10:00:00'],
    [2, 10, 'besar', 'Keluar', 'Manual', 'Barang Kedaluwarsa', 'MUT-EXP-20260901-02', 20, 20, 'Pemusnahan Batch Kadaluarsa [Batch: PCT-OLD-2025]', 2, '2026-09-01 10:15:00'],

    // 2. Histori Konversi (Bongkar Satuan Besar -> Satuan Kecil)
    [3, 1, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 14, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: AMX-2026-01]', 4, '2026-10-05 09:15:00'],
    [4, 1, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 10, 10, 'Lahir Sub-Batch AMX-2026-01.A (Masuk 10 Strip)', 4, '2026-10-05 09:15:00'],
    [5, 2, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 11, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: SNM-2026-A]', 2, '2026-10-07 10:30:00'],
    [6, 2, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 24, 24, 'Lahir Sub-Batch SNM-2026-A.A (Masuk 24 Botol)', 2, '2026-10-07 10:30:00'],
    [7, 10, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 24, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]', 4, '2026-10-08 11:00:00'],
    [8, 10, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 10, 10, 'Lahir Sub-Batch PCT-500-26.A (Masuk 10 Strip)', 4, '2026-10-08 11:00:00'],
    [9, 10, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 23, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]', 4, '2026-10-10 14:20:00'],
    [10, 10, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 10, 10, 'Lahir Sub-Batch PCT-500-26.B (Masuk 10 Strip)', 4, '2026-10-10 14:20:00'],
    [11, 6, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 6, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-26]', 4, '2026-10-09 13:45:00'],
    [12, 6, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 50, 50, 'Lahir Sub-Batch HUF-TMP-26.A (Masuk 50 Botol)', 4, '2026-10-09 13:45:00'],
    [13, 6, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 9, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-27]', 2, '2026-10-11 15:30:00'],
    [14, 6, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 50, 50, 'Lahir Sub-Batch HUF-TMP-27.A (Masuk 50 Botol)', 2, '2026-10-11 15:30:00'],
    [15, 13, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 29, 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PRM-KLK-26]', 4, '2026-10-21 11:00:00'],
    [16, 13, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 12, 12, 'Lahir Sub-Batch PRM-KLK-26.A (Masuk 12 Strip)', 4, '2026-10-21 11:00:00'],
    [17, 24, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', null, 1, 11, 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: WOD-PEP-26]', 2, '2026-10-23 16:30:00'],
    [18, 24, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', null, 24, 24, 'Lahir Sub-Batch WOD-PEP-26.A (Masuk 24 Botol)', 2, '2026-10-23 16:30:00'],

    // 3. Penjualan POS & E-Commerce
    [19, 56, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261012-0901', 1, 4, 'Penjualan Luring POS-20261012-0901 [SN: OMR-NEB-99]', 4, '2026-10-12 16:10:00'],
    [20, 56, 'kecil', 'Masuk', 'Manual', 'Retur Barang Rusak', 'POS-20261012-0901', 1, 5, 'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi) [SN: OMR-NEB-99]', 2, '2026-10-15 11:20:00'],
    [21, 1, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261014-1001', 2, 8, 'Penjualan Luring POS-20261014-1001 [Batch: AMX-2026-01.A (2x)]', 4, '2026-10-14 10:15:20'],
    [22, 5, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261014-1001', 1, 4, 'Penjualan Luring POS-20261014-1001 [SN: OMR-TH-01]', 4, '2026-10-14 10:15:20'],
    [23, 10, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261016-1002', 3, 7, 'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.A (3 Tablet)]', 4, '2026-10-16 14:22:10'],
    [24, 10, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261016-1002', 2, 8, 'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.B (2 Tablet)]', 4, '2026-10-16 14:22:10'],
    [25, 6, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261017-1003', 2, 48, 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-26.A (2 Botol)]', 4, '2026-10-17 11:40:05'],
    [26, 6, 'kecil', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261017-1003', 3, 47, 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27.A (3 Botol)]', 4, '2026-10-17 11:40:05'],
    [27, 6, 'besar', 'Keluar', 'POS', 'Penjualan POS', 'POS-20261017-1003', 1, 8, 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27 (1 Karton = 50 Botol)]', 4, '2026-10-17 11:40:05'],
    [28, 30, 'kecil', 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', 'INV-20261018-041', 2, 58, 'Pengiriman via Ekspedisi (Kurir) [Batch: IMB-FRC-26 (2x)]', 2, '2026-10-18 09:30:00'],
    [29, 2, 'besar', 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', 'INV-20261019-052', 1, 10, 'Pengiriman via Ekspedisi (Kurir) [Batch: SNM-2026-A (1 Karton = 24 Botol)]', 2, '2026-10-19 13:20:00'],
    [30, 4, 'kecil', 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', 'INV-20261019-052', 1, 8, 'Pengiriman via Ekspedisi (Kurir) [SN: OMR-TENS-01]', 2, '2026-10-19 13:20:00'],

    // 4. Selisih Stok Opname (Audit Gudang)
    [31, 1, 'kecil', 'Masuk', 'Manual', 'Selisih Stok Opname', 'SO-20261020-01', 5, 13, 'Penyesuaian Opname Fisik: Ditemukan selisih fisik lebih 5 strip pada rak display [Batch: AMX-2026-01.A]', 2, '2026-10-20 09:30:00'],
    [32, 10, 'besar', 'Keluar', 'Manual', 'Selisih Stok Opname', 'SO-20261020-02', 1, 22, 'Penyesuaian Opname Fisik: Selisih fisik kurang 1 box kemasan rusak saat audit gudang [Batch: PCT-500-26]', 2, '2026-10-20 10:15:00'],

    // 5. Penerimaan Barang PO 1 s/d 6
    [33, 1, 'besar', 'Masuk', 'PO', 'Penerimaan Barang', 'PO-20261015-OBAT5', 15, 15, 'Penerimaan PO Farmasi Kimia Farma [Batch: AMX-2026-01]', 2, '2026-10-15 08:30:00'],
    [34, 10, 'besar', 'Masuk', 'PO', 'Penerimaan Barang', 'PO-20261015-OBAT5', 25, 25, 'Penerimaan PO Farmasi Kimia Farma [Batch: PCT-500-26]', 2, '2026-10-15 08:30:00'],
    [35, 4, 'kecil', 'Masuk', 'PO', 'Penerimaan Barang', 'PO-20261018-ALKES4', 5, 5, 'Penerimaan PO Alat Kesehatan Omron [SN: OMR-TENS-01 s/d 05]', 2, '2026-10-18 09:15:00'],
    [36, 13, 'besar', 'Masuk', 'Penerimaan', 'Penerimaan Barang', 'KUL-20261020-CAMPUR', 30, 30, 'Kulakan OneMed [Batch: PRM-KLK-26]', 4, '2026-10-20 10:00:00'],
    [37, 41, 'besar', 'Masuk', 'PO', 'Penerimaan Barang', 'PO-20261022-OBAT-RETUR', 8, 8, 'Penerimaan PO Obat Awal [Batch: CFD-500-26] (2 box rusak)', 2, '2026-10-22 11:20:00'],
    [38, 41, 'besar', 'Masuk', 'PO', 'Retur Barang Rusak', 'PO-20261022-OBAT-RETUR', 2, 10, 'Penerimaan Barang Pengganti Retur Selesai (2 Box) [Batch: CFD-GNT-26]', 2, '2026-10-25 10:30:00'],
    [39, 4, 'kecil', 'Masuk', 'PO', 'Penerimaan Barang', 'PO-20261023-ALKES-RETUR', 4, 9, 'Penerimaan PO Alkes Omron [4 Unit Diterima, 2 Unit Cacat Menunggu Pengganti]', 2, '2026-10-23 13:45:00']
];

$ksVals = [];
foreach ($kartuStokList as $ks) {
    $ksVals[] = "(" . implode(", ", [
        $ks[0],
        $ks[1],
        sqlVal($ks[2]),
        sqlVal($ks[3]),
        sqlVal($ks[4]),
        sqlVal($ks[5]),
        sqlVal($ks[6]),
        $ks[7],
        $ks[8],
        sqlVal($ks[9]),
        $ks[10] !== null ? $ks[10] : "NULL",
        sqlVal($ks[11])
    ]) . ")";
}
$lines[] = "INSERT INTO `kartu_stok` (`id`, `id_variasi`, `satuan_tipe`, `jenis_mutasi`, `kanal`, `alasan_mutasi`, `no_ref_dokumen`, `qty`, `sisa_stok`, `keterangan`, `dibuat_oleh`, `tanggal`) VALUES";
$lines[] = implode(",\n", $ksVals) . ";";
$lines[] = "";

// =========================================================================
// 15. LOG ANOMALI FEFO
// =========================================================================
$lines[] = "-- --------------------------------------------------------";
$lines[] = "-- Struktur dari tabel `log_anomali_fefo`";
$lines[] = "CREATE TABLE `log_anomali_fefo` (";
$lines[] = "  `id` int(11) NOT NULL,";
$lines[] = "  `id_penjualan` int(11) DEFAULT NULL,";
$lines[] = "  `id_user` int(11) NOT NULL,";
$lines[] = "  `id_variasi` int(11) NOT NULL,";
$lines[] = "  `batch_diambil` varchar(100) DEFAULT NULL,";
$lines[] = "  `batch_seharusnya` varchar(100) DEFAULT NULL,";
$lines[] = "  `qty` int(11) DEFAULT NULL,";
$lines[] = "  `created_at` timestamp NOT NULL DEFAULT current_timestamp()";
$lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$lines[] = "";

// =========================================================================
// 16. INDEXES & AUTO_INCREMENTS
// =========================================================================
$lines[] = "-- Indexes for dumped tables";
$lines[] = "--";
$lines[] = "ALTER TABLE `detail_penjualan`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD KEY `id_penjualan` (`id_penjualan`),";
$lines[] = "  ADD KEY `id_variasi` (`id_variasi`);";
$lines[] = "";
$lines[] = "ALTER TABLE `histori_konversi`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD KEY `id_variasi` (`id_variasi`),";
$lines[] = "  ADD KEY `dibuat_oleh` (`dibuat_oleh`);";
$lines[] = "";
$lines[] = "ALTER TABLE `kartu_stok`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD KEY `id_variasi` (`id_variasi`),";
$lines[] = "  ADD KEY `dibuat_oleh` (`dibuat_oleh`);";
$lines[] = "";
$lines[] = "ALTER TABLE `log_anomali_fefo`";
$lines[] = "  ADD PRIMARY KEY (`id`);";
$lines[] = "";
$lines[] = "ALTER TABLE `penerimaan_detail`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD KEY `id_penerimaan` (`id_penerimaan`),";
$lines[] = "  ADD KEY `id_variasi` (`id_variasi`);";
$lines[] = "";
$lines[] = "ALTER TABLE `penerimaan_stok`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD KEY `id_supplier` (`id_supplier`),";
$lines[] = "  ADD KEY `dibuat_oleh` (`dibuat_oleh`);";
$lines[] = "";
$lines[] = "ALTER TABLE `pengaturan_web`";
$lines[] = "  ADD PRIMARY KEY (`id`);";
$lines[] = "";
$lines[] = "ALTER TABLE `penjualan`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD UNIQUE KEY `no_invoice` (`no_invoice`),";
$lines[] = "  ADD KEY `id_user` (`id_user`);";
$lines[] = "";
$lines[] = "ALTER TABLE `produk_induk`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD UNIQUE KEY `sku_induk` (`sku_induk`);";
$lines[] = "";
$lines[] = "ALTER TABLE `produk_variasi`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD UNIQUE KEY `sku_variasi` (`sku_variasi`),";
$lines[] = "  ADD KEY `id_produk_induk` (`id_produk_induk`);";
$lines[] = "";
$lines[] = "ALTER TABLE `stok_batch`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD KEY `idx_fefo` (`id_variasi`,`tgl_exp`);";
$lines[] = "";
$lines[] = "ALTER TABLE `stok_toko`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD UNIQUE KEY `id_variasi` (`id_variasi`);";
$lines[] = "";
$lines[] = "ALTER TABLE `supplier`";
$lines[] = "  ADD PRIMARY KEY (`id`);";
$lines[] = "";
$lines[] = "ALTER TABLE `unit_serial`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD UNIQUE KEY `serial_number` (`serial_number`),";
$lines[] = "  ADD KEY `id_variasi` (`id_variasi`);";
$lines[] = "";
$lines[] = "ALTER TABLE `users`";
$lines[] = "  ADD PRIMARY KEY (`id`),";
$lines[] = "  ADD UNIQUE KEY `username` (`username`);";
$lines[] = "";

$lines[] = "-- AUTO_INCREMENT untuk tabel yang dibuang";
$lines[] = "ALTER TABLE `detail_penjualan` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;";
$lines[] = "ALTER TABLE `histori_konversi` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;";
$lines[] = "ALTER TABLE `kartu_stok` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;";
$lines[] = "ALTER TABLE `log_anomali_fefo` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
$lines[] = "ALTER TABLE `penerimaan_detail` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;";
$lines[] = "ALTER TABLE `penerimaan_stok` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;";
$lines[] = "ALTER TABLE `pengaturan_web` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;";
$lines[] = "ALTER TABLE `penjualan` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;";
$lines[] = "ALTER TABLE `produk_induk` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;";
$lines[] = "ALTER TABLE `produk_variasi` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;";
$lines[] = "ALTER TABLE `stok_batch` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;";
$lines[] = "ALTER TABLE `stok_toko` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;";
$lines[] = "ALTER TABLE `supplier` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;";
$lines[] = "ALTER TABLE `unit_serial` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;";
$lines[] = "ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;";
$lines[] = "";

// =========================================================================
// 17. FOREIGN KEY CONSTRAINTS
// =========================================================================
$lines[] = "-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)";
$lines[] = "ALTER TABLE `detail_penjualan`";
$lines[] = "  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,";
$lines[] = "  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`);";
$lines[] = "";
$lines[] = "ALTER TABLE `histori_konversi`";
$lines[] = "  ADD CONSTRAINT `histori_konversi_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),";
$lines[] = "  ADD CONSTRAINT `histori_konversi_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`);";
$lines[] = "";
$lines[] = "ALTER TABLE `kartu_stok`";
$lines[] = "  ADD CONSTRAINT `kartu_stok_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),";
$lines[] = "  ADD CONSTRAINT `kartu_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;";
$lines[] = "";
$lines[] = "ALTER TABLE `penerimaan_detail`";
$lines[] = "  ADD CONSTRAINT `penerimaan_detail_ibfk_1` FOREIGN KEY (`id_penerimaan`) REFERENCES `penerimaan_stok` (`id`) ON DELETE CASCADE,";
$lines[] = "  ADD CONSTRAINT `penerimaan_detail_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`);";
$lines[] = "";
$lines[] = "ALTER TABLE `penerimaan_stok`";
$lines[] = "  ADD CONSTRAINT `penerimaan_stok_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,";
$lines[] = "  ADD CONSTRAINT `penerimaan_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;";
$lines[] = "";
$lines[] = "ALTER TABLE `penjualan`";
$lines[] = "  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;";
$lines[] = "";
$lines[] = "ALTER TABLE `produk_variasi`";
$lines[] = "  ADD CONSTRAINT `produk_variasi_ibfk_1` FOREIGN KEY (`id_produk_induk`) REFERENCES `produk_induk` (`id`) ON DELETE SET NULL;";
$lines[] = "";
$lines[] = "ALTER TABLE `stok_batch`";
$lines[] = "  ADD CONSTRAINT `stok_batch_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE;";
$lines[] = "";
$lines[] = "ALTER TABLE `stok_toko`";
$lines[] = "  ADD CONSTRAINT `stok_toko_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE;";
$lines[] = "";
$lines[] = "ALTER TABLE `unit_serial`";
$lines[] = "  ADD CONSTRAINT `unit_serial_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE;";
$lines[] = "COMMIT;";
$lines[] = "";
$lines[] = "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;";
$lines[] = "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;";
$lines[] = "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

$finalSql = implode("\n", $lines);
$destFile = __DIR__ . '/../database/isi/db_inventory (demo) 1.sql';

file_put_contents($destFile, $finalSql);
echo "SUCCESS: File created at: $destFile (" . strlen($finalSql) . " bytes)\n";
