-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: inventory_zencare
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cabang`
--

DROP TABLE IF EXISTS `cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cabang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kota_id` int(11) NOT NULL COMMENT 'ID Kota RajaOngkir',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang`
--

LOCK TABLES `cabang` WRITE;
/*!40000 ALTER TABLE `cabang` DISABLE KEYS */;
INSERT INTO `cabang` VALUES (1,'ZenCare Pusat Muharto','Jl. Muharto No.1, Malang',256,1),(2,'ZenCare Cabang Sawojajar','Jl. Danau Toba No.10, Malang',256,1);
/*!40000 ALTER TABLE `cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penjualan`
--

DROP TABLE IF EXISTS `detail_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `catatan_logistik` text DEFAULT NULL COMMENT 'Menyimpan riwayat batch/SN yang terpakai',
  PRIMARY KEY (`id`),
  KEY `id_penjualan` (`id_penjualan`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
/*!40000 ALTER TABLE `detail_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_stok`
--

DROP TABLE IF EXISTS `kartu_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cabang` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `jenis_mutasi` enum('Masuk','Keluar','Penyesuaian','Transfer') NOT NULL,
  `qty` int(11) NOT NULL,
  `sisa_stok` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_cabang` (`id_cabang`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `kartu_stok_ibfk_1` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`),
  CONSTRAINT `kartu_stok_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` VALUES (1,1,1,'Masuk',100,100,'Pengadaan Awal Batch BATCH-PARA-001 & BATCH-PARA-002','2026-09-09 09:15:19'),(2,1,2,'Masuk',50,50,'Pengadaan Awal Batch BATCH-AMOX-001 & BATCH-AMOX-002','2026-09-09 09:15:19'),(3,1,3,'Masuk',3,3,'Pengadaan Awal SN: OMR-7120-A001, A002, A003','2026-09-09 09:15:19'),(4,1,4,'Masuk',2,2,'Pengadaan Awal SN: SEL-WC-001, SEL-WC-002','2026-09-09 09:15:19'),(5,2,1,'Masuk',50,50,'Pengadaan Awal Batch BATCH-PARA-001','2026-09-09 09:15:19'),(6,2,3,'Masuk',1,1,'Pengadaan Awal SN: OMR-7120-B001','2026-09-09 09:15:19');
/*!40000 ALTER TABLE `kartu_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mutasi_stok`
--

DROP TABLE IF EXISTS `mutasi_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mutasi_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `cabang_asal` int(11) NOT NULL,
  `cabang_tujuan` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_variasi` (`id_variasi`),
  KEY `cabang_asal` (`cabang_asal`),
  KEY `cabang_tujuan` (`cabang_tujuan`),
  CONSTRAINT `mutasi_stok_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  CONSTRAINT `mutasi_stok_ibfk_2` FOREIGN KEY (`cabang_asal`) REFERENCES `cabang` (`id`),
  CONSTRAINT `mutasi_stok_ibfk_3` FOREIGN KEY (`cabang_tujuan`) REFERENCES `cabang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mutasi_stok`
--

LOCK TABLES `mutasi_stok` WRITE;
/*!40000 ALTER TABLE `mutasi_stok` DISABLE KEYS */;
/*!40000 ALTER TABLE `mutasi_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan_api`
--

DROP TABLE IF EXISTS `pengaturan_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaturan_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` enum('midtrans','rajaongkir') NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_api`
--

LOCK TABLES `pengaturan_api` WRITE;
/*!40000 ALTER TABLE `pengaturan_api` DISABLE KEYS */;
INSERT INTO `pengaturan_api` VALUES (1,'midtrans','SB-Mid-server-xxxx',NULL,NULL,1),(2,'rajaongkir','xxx-rajaongkir-key',NULL,NULL,1);
/*!40000 ALTER TABLE `pengaturan_api` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan_web`
--

DROP TABLE IF EXISTS `pengaturan_web`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaturan_web` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_web`
--

LOCK TABLES `pengaturan_web` WRITE;
/*!40000 ALTER TABLE `pengaturan_web` DISABLE KEYS */;
INSERT INTO `pengaturan_web` VALUES (1,'ZenCare Medical','Solusi Obat & Alat Kesehatan Terpercaya','081234567890','Jl. Merdeka No.1, Malang',NULL);
/*!40000 ALTER TABLE `pengaturan_web` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_invoice` varchar(50) NOT NULL,
  `id_cabang` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tipe_transaksi` enum('pos','ecommerce') NOT NULL,
  `status_pesanan` enum('Menunggu','Diproses','Dikirim','Siap Diambil','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  `total_harga` decimal(12,2) NOT NULL,
  `ongkir` decimal(10,2) DEFAULT 0.00,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL,
  `layanan` varchar(50) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_invoice` (`no_invoice`),
  KEY `id_cabang` (`id_cabang`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`),
  CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_induk`
--

DROP TABLE IF EXISTS `produk_induk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produk_induk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_induk` varchar(50) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('Obat','Alat Kesehatan') NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_induk` (`sku_induk`),
  KEY `id_supplier` (`id_supplier`),
  CONSTRAINT `produk_induk_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_induk`
--

LOCK TABLES `produk_induk` WRITE;
/*!40000 ALTER TABLE `produk_induk` DISABLE KEYS */;
INSERT INTO `produk_induk` VALUES (1,'PRD-OBT-001','Paracetamol Syrup Anak 60ml','Obat pereda demam anak','Obat',1,1),(2,'PRD-OBT-002','Amoxicillin 500mg','Antibiotik resep dokter','Obat',1,1),(3,'PRD-ALK-001','Tensimeter Digital Omron HEM-7120','Alat pengukur tekanan darah digital akurat','Alat Kesehatan',2,1),(4,'PRD-ALK-002','Kursi Roda Medis Sella','Kursi roda standar medis rumah sakit','Alat Kesehatan',3,1);
/*!40000 ALTER TABLE `produk_induk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_variasi`
--

DROP TABLE IF EXISTS `produk_variasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produk_variasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk_induk` int(11) DEFAULT NULL,
  `sku_variasi` varchar(50) NOT NULL,
  `nama_variasi` varchar(100) NOT NULL,
  `satuan_kecil` varchar(50) NOT NULL DEFAULT 'Pcs',
  `satuan_besar` varchar(50) NOT NULL DEFAULT 'Box',
  `rasio_konversi` int(11) NOT NULL DEFAULT 1 COMMENT 'Obat > 1. Alkes = 1',
  `harga_jual_kecil` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual_besar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `berat` int(11) NOT NULL DEFAULT 100,
  `gambar` varchar(255) DEFAULT NULL,
  `tampil_di_online` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_variasi` (`sku_variasi`),
  KEY `id_produk_induk` (`id_produk_induk`),
  CONSTRAINT `produk_variasi_ibfk_1` FOREIGN KEY (`id_produk_induk`) REFERENCES `produk_induk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_variasi`
--

LOCK TABLES `produk_variasi` WRITE;
/*!40000 ALTER TABLE `produk_variasi` DISABLE KEYS */;
INSERT INTO `produk_variasi` VALUES (1,1,'OBT-001-SYR','Syrup 60ml (Botol)','Botol','Karton',50,15000.00,700000.00,150,NULL,1,1),(2,2,'OBT-002-TAB','Tablet 500mg (Strip)','Strip','Box',10,5000.00,48000.00,50,NULL,1,1),(3,3,'ALK-001-SET','Set Omron HEM-7120','Unit','Unit',1,450000.00,450000.00,800,NULL,1,1),(4,4,'ALK-002-SET','Standard Set','Unit','Unit',1,1500000.00,1500000.00,15000,NULL,1,1);
/*!40000 ALTER TABLE `produk_variasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_batch`
--

DROP TABLE IF EXISTS `stok_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `id_cabang` int(11) NOT NULL,
  `no_batch` varchar(50) NOT NULL,
  `tgl_exp` date NOT NULL,
  `stok_sisa` int(11) NOT NULL DEFAULT 0 COMMENT 'Dalam satuan terkecil',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_variasi` (`id_variasi`),
  KEY `id_cabang` (`id_cabang`),
  CONSTRAINT `stok_batch_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stok_batch_ibfk_2` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_batch`
--

LOCK TABLES `stok_batch` WRITE;
/*!40000 ALTER TABLE `stok_batch` DISABLE KEYS */;
INSERT INTO `stok_batch` VALUES (1,1,1,'BATCH-PARA-001','2026-12-31',50,'2026-09-09 09:15:19'),(2,1,1,'BATCH-PARA-002','2027-06-30',50,'2026-09-09 09:15:19'),(3,1,2,'BATCH-PARA-001','2026-12-31',50,'2026-09-09 09:15:19'),(4,2,1,'BATCH-AMOX-001','2026-10-15',20,'2026-09-09 09:15:19'),(5,2,1,'BATCH-AMOX-002','2027-01-20',30,'2026-09-09 09:15:19');
/*!40000 ALTER TABLE `stok_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_cabang`
--

DROP TABLE IF EXISTS `stok_cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_cabang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `id_cabang` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0 COMMENT 'Total kumulatif terkecil (Pcs/Strip)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_variasi_cabang` (`id_variasi`,`id_cabang`),
  KEY `id_cabang` (`id_cabang`),
  CONSTRAINT `stok_cabang_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stok_cabang_ibfk_2` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_cabang`
--

LOCK TABLES `stok_cabang` WRITE;
/*!40000 ALTER TABLE `stok_cabang` DISABLE KEYS */;
INSERT INTO `stok_cabang` VALUES (1,1,1,100),(2,2,1,50),(3,3,1,3),(4,4,1,2),(5,1,2,50),(6,3,2,1);
/*!40000 ALTER TABLE `stok_cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'PT Kimia Farma Trading & Distribution','0341-111111','Jl. Kesehatan Raya, Malang',1),(2,'PT Omron Healthcare Indonesia','021-222222','Jakarta Pusat',1),(3,'PT Jayamas Medica Industri (OneMed)','031-333333','Sidoarjo',1);
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_serial`
--

DROP TABLE IF EXISTS `unit_serial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_serial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `id_cabang` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `status` enum('Tersedia','Terjual','Retur/Rusak') NOT NULL DEFAULT 'Tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `id_variasi` (`id_variasi`),
  KEY `id_cabang` (`id_cabang`),
  CONSTRAINT `unit_serial_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_serial_ibfk_2` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_serial`
--

LOCK TABLES `unit_serial` WRITE;
/*!40000 ALTER TABLE `unit_serial` DISABLE KEYS */;
INSERT INTO `unit_serial` VALUES (1,3,1,'OMR-7120-A001','Tersedia','2026-09-09 09:15:19'),(2,3,1,'OMR-7120-A002','Tersedia','2026-09-09 09:15:19'),(3,3,1,'OMR-7120-A003','Tersedia','2026-09-09 09:15:19'),(4,3,2,'OMR-7120-B001','Tersedia','2026-09-09 09:15:19'),(5,4,1,'SEL-WC-001','Tersedia','2026-09-09 09:15:19'),(6,4,1,'SEL-WC-002','Tersedia','2026-09-09 09:15:19');
/*!40000 ALTER TABLE `unit_serial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('super_admin','karyawan','pelanggan') NOT NULL,
  `id_cabang` int(11) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota_id` int(11) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `id_cabang` (`id_cabang`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','123456','Marcell (Pemilik)','super_admin',NULL,'081234567890',NULL,NULL,NULL,NULL,1),(2,'admin_muharto','$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p','Karyawan Muharto','karyawan',1,'082111111111',NULL,NULL,NULL,NULL,1),(3,'admin_sawojajar','$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p','Karyawan Sawojajar','karyawan',2,'082222222222',NULL,NULL,NULL,NULL,1),(4,'pelanggan1','$2y$10$e8wVfC2o9.c1ZtM6wY4k6.Z3J1w0m4YqX8B3p5x1Z2k3l4m5n6o7p','Budi Santoso','pelanggan',NULL,'083333333333',NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-10 14:20:34
