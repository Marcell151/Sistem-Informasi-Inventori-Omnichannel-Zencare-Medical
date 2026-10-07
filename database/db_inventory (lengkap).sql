-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_inventory
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
  `catatan_logistik` text DEFAULT NULL,
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
-- Table structure for table `histori_konversi`
--

DROP TABLE IF EXISTS `histori_konversi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `histori_konversi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `batch_asal` varchar(100) NOT NULL,
  `batch_hasil` varchar(100) NOT NULL,
  `qty_box_buka` int(11) NOT NULL,
  `qty_pcs_hasil` int(11) NOT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_variasi` (`id_variasi`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  CONSTRAINT `histori_konversi_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  CONSTRAINT `histori_konversi_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `histori_konversi`
--

LOCK TABLES `histori_konversi` WRITE;
/*!40000 ALTER TABLE `histori_konversi` DISABLE KEYS */;
INSERT INTO `histori_konversi` VALUES (1,26,'BCH-26-14','BCH-26-14.A',1,12,2,'2026-10-06 02:01:17'),(2,15,'bch-26-3','bch-26-3.A',1,12,4,'2026-10-06 02:32:27'),(3,6,'HUF-2026-00','HUF-2026-00.A',1,50,4,'2026-10-06 05:40:42'),(4,6,'HUF-2026-00','HUF-2026-00.A',1,50,4,'2026-10-06 05:55:54'),(5,7,'HUF-2026-01','HUF-2026-01.B',1,50,4,'2026-10-06 05:58:09'),(6,7,'HUF-2026-01','HUF-2026-01.C',1,50,1,'2026-10-06 07:15:16'),(7,7,'HUF-2026-01','HUF-2026-01.B',1,50,2,'2026-10-07 06:27:35');
/*!40000 ALTER TABLE `histori_konversi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_stok`
--

DROP TABLE IF EXISTS `kartu_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_variasi` (`id_variasi`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  CONSTRAINT `kartu_stok_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  CONSTRAINT `kartu_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` VALUES (1,7,'besar','Keluar','Manual','',NULL,1,10,'Bongkar 1 Karton (Rp0 - Konversi)',2,'2026-10-07 06:27:35'),(2,7,'kecil','Masuk','Manual','',NULL,50,512,'Lahir Sub-Batch HUF-2026-01.B (Masuk 50 Botol)',2,'2026-10-07 06:27:35');
/*!40000 ALTER TABLE `kartu_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_anomali_fefo`
--

DROP TABLE IF EXISTS `log_anomali_fefo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_anomali_fefo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `batch_diambil` varchar(100) DEFAULT NULL,
  `batch_seharusnya` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_anomali_fefo`
--

LOCK TABLES `log_anomali_fefo` WRITE;
/*!40000 ALTER TABLE `log_anomali_fefo` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_anomali_fefo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penerimaan_detail`
--

DROP TABLE IF EXISTS `penerimaan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penerimaan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penerimaan` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `qty_terima` int(11) NOT NULL,
  `harga_beli` decimal(15,2) DEFAULT NULL,
  `no_batch` varchar(50) DEFAULT NULL,
  `tgl_exp` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_penerimaan` (`id_penerimaan`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `penerimaan_detail_ibfk_1` FOREIGN KEY (`id_penerimaan`) REFERENCES `penerimaan_stok` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penerimaan_detail_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan_detail`
--

LOCK TABLES `penerimaan_detail` WRITE;
/*!40000 ALTER TABLE `penerimaan_detail` DISABLE KEYS */;
INSERT INTO `penerimaan_detail` VALUES (1,1,2,20,NULL,'PCT260915A','2029-09-15'),(2,1,1,15,NULL,'PCT260915B','2028-09-15'),(3,2,4,10,50000.00,'PCT2609G65','2028-10-22');
/*!40000 ALTER TABLE `penerimaan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penerimaan_stok`
--

DROP TABLE IF EXISTS `penerimaan_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penerimaan_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_referensi` varchar(50) NOT NULL,
  `sumber` enum('PO','Pembelian Langsung') NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `tanggal_terima` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `file_nota` varchar(255) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_supplier` (`id_supplier`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  CONSTRAINT `penerimaan_stok_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penerimaan_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan_stok`
--

LOCK TABLES `penerimaan_stok` WRITE;
/*!40000 ALTER TABLE `penerimaan_stok` DISABLE KEYS */;
INSERT INTO `penerimaan_stok` VALUES (1,'NS1PW-NFAS-QOMW12','PO',1,'2026-09-15','Beli Obat Paracetamol',NULL,2,'2026-09-15 07:36:36'),(2,'NS1PW-NFAS-QOMTQ2','Pembelian Langsung',NULL,'2026-09-22','Beli Obat Paracetamol',NULL,2,'2026-09-22 00:30:35');
/*!40000 ALTER TABLE `penerimaan_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan_web`
--

DROP TABLE IF EXISTS `pengaturan_web`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaturan_web` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(100) NOT NULL DEFAULT 'ZenCare Medical',
  `deskripsi` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner_promosi` varchar(255) DEFAULT NULL,
  `email_cs` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_web`
--

LOCK TABLES `pengaturan_web` WRITE;
/*!40000 ALTER TABLE `pengaturan_web` DISABLE KEYS */;
INSERT INTO `pengaturan_web` VALUES (1,'ZenCare Medical','Distributor Resmi Alat Kesehatan & Obat-Obatan Terpercaya di Malang','0341-111222','6281234567890','Jl. Muharto No.1, Kota Malang, Jawa Timur 65118',NULL,NULL,'cs@zencare.id');
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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_invoice` (`no_invoice`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL
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
  `spesifikasi` text DEFAULT NULL COMMENT 'Spesifikasi teknis medis',
  `info_pengiriman` text DEFAULT NULL,
  `kategori` enum('Obat','Alat Kesehatan') NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `batas_hari_expired` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_induk` (`sku_induk`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_induk`
--

LOCK TABLES `produk_induk` WRITE;
/*!40000 ALTER TABLE `produk_induk` DISABLE KEYS */;
INSERT INTO `produk_induk` VALUES (1,'IND-D4853B','Amoxicillin 500mg','Antibiotik Kapsul (Wajib Resep)',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(2,'IND-B42BD2','Sanmol Sirup Anak 60ml','Obat Demam Anak Rasa Stroberi',NULL,NULL,'Obat',NULL,1,180,'2026-10-06 07:39:14'),(3,'IND-B35E3A','Strip Gula Darah EasyTouch','BMHP Gula Darah - Isi 25',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(4,'IND-4422CC','Tensimeter Digital Omron','Alat Ukur Tekanan Darah Lengan',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(5,'IND-BE2154','Termometer Digital Omron','Termometer Badan Digital MC-246',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(6,'IND-9929D6','Hufagrip','Obat Flu Anak',NULL,NULL,'Obat',NULL,1,180,'2026-10-06 07:39:14'),(7,'IND-5EE388','Paracetamol','Pereda Nyeri / Penurun Panas',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(8,'IND-E859E3','Promag Tablet','Obat Sakit Maag & Kembung',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(9,'IND-9D679C','Panadol Extra','Pereda Sakit Kepala Membandel',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(10,'IND-CBA5E0','Tolak Angin Cair','Obat Masuk Angin',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(11,'IND-80EAC6','Mylanta Cair 150ml','Obat Maag Cair',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(12,'IND-38F7DB','Insto Reguler 7.5ml','Tetes Mata Merah',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(13,'IND-70AB72','Betadine Solution 15ml','Antiseptik Luka',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(14,'IND-ABF94E','Counterpain Krim 30g','Krim Pereda Nyeri Otot',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(15,'IND-E8639A','Neurobion Forte','Vitamin B Kompleks',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(16,'IND-E26D93','Sangobion Kapsul','Suplemen Zat Besi',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(17,'IND-7F92A0','Diapet Kapsul','Obat Diare',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(18,'IND-D23520','Entrostop','Obat Diare Dewasa',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(19,'IND-FCA962','Woods Peppermint Syr','Obat Batuk Antitusif',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(20,'IND-681C6D','Komix Herbal','Sirup Obat Batuk Tube',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(21,'IND-E6B0E1','Antangin JRG','Obat Herbal Masuk Angin',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(22,'IND-429941','CDR Effervescent','Vitamin C & Kalsium',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(23,'IND-619525','Redoxon Double Action','Multivitamin C & Zinc',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(24,'IND-F85AB5','Enervon-C Multivitamin','Vitamin C & B Kompleks',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(25,'IND-72ECA5','Imboost Force','Suplemen Daya Tahan Tubuh',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(26,'IND-95AB80','Bodrex Migra','Obat Sakit Kepala Migrain',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(27,'IND-EDAB79','Neozep Forte','Obat Flu & Pilek',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(28,'IND-CDE41C','Decolgen','Obat Flu Ringan',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(29,'IND-6C721E','OBH Combi Plus','Sirup Batuk & Flu',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(30,'IND-2EDE6B','Salonpas Koyo','Koyo Pereda Nyeri',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(31,'IND-516FBF','Vicks VapoRub 10g','Balsem Pelega Tenggorokan',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(32,'IND-75F1A9','Tolak Linu','Obat Pegal Linu Cair',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(33,'IND-B79B70','Decadryl Expectorant','Obat Batuk Berdahak',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(34,'IND-1E6A32','Polysilane Kapsul','Obat Asam Lambung',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(35,'IND-A35F8F','Combantrin Jeruk 10ml','Obat Cacing Sirup Anak',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(36,'IND-2A3D66','Cefadroxil 500mg','Antibiotik (Resep Dokter)',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(37,'IND-9D0FFA','CTM','Obat Alergi Ringan',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(38,'IND-0DA012','Cetirizine 10mg','Obat Alergi / Antihistamin',NULL,NULL,'Obat',NULL,1,90,'2026-10-06 07:39:14'),(39,'IND-3EFAA1','Masker Sensi Earloop','Masker Medis 3 Ply (Isi 50)',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(40,'IND-04864C','Hansaplast Kain Elastis','Plester Luka',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(41,'IND-25FD84','Plester Dermafix','Plester Anti Air Transparan',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(42,'IND-195AD9','Kasa Steril Onemed','Kasa Luka Steril 16x16',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(43,'IND-DDF14F','Alkohol Onemed 70% 100ml','Cairan Antiseptik',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(44,'IND-6F0C4A','Rivanol 100ml','Cairan Pembersih Luka',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(45,'IND-6C597D','Povidone Iodine 1 Liter','Betadine Literan RS',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(46,'IND-9EBAEB','Alat Cek Asam Urat EasyTouch','BMHP Asam Urat - Isi 25',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(47,'IND-64BBB0','Alat Cek Kolesterol Nesco','BMHP Kolesterol - Isi 10',NULL,NULL,'Alat Kesehatan',NULL,1,90,'2026-10-06 07:39:14'),(48,'IND-FC6027','Kursi Roda Standard Sella','Kursi Roda Lipat Ringan',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(49,'IND-CE2685','Tabung Oksigen 1m3','Tabung O2 Medis Kosong',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(50,'IND-26584F','Regulator Oksigen','Regulator Medis O2',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(51,'IND-A7E800','Nebulizer Omron','Alat Terapi Uap Asma NE-C104',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(52,'IND-3DB28F','Termometer Infra Merah','Thermometer Tembak Dahi',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14'),(53,'IND-D92462','Stetoskop Littmann','Stethoscope Classic III Medis',NULL,NULL,'Alat Kesehatan',NULL,1,0,'2026-10-06 07:39:14');
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
  `rasio_konversi` smallint(6) NOT NULL DEFAULT 1,
  `harga_jual_kecil` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual_besar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok_minimum_kecil` int(11) DEFAULT 0,
  `stok_minimum_besar` int(11) DEFAULT 0,
  `berat` int(11) NOT NULL DEFAULT 100,
  `tampil_di_online` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `gambar` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_variasi` (`sku_variasi`),
  KEY `id_produk_induk` (`id_produk_induk`),
  CONSTRAINT `produk_variasi_ibfk_1` FOREIGN KEY (`id_produk_induk`) REFERENCES `produk_induk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_variasi`
--

LOCK TABLES `produk_variasi` WRITE;
/*!40000 ALTER TABLE `produk_variasi` DISABLE KEYS */;
INSERT INTO `produk_variasi` VALUES (1,1,'AMX-500','Tablet 500mg','Strip','Box',10,5000.00,48000.00,10,2,100,1,1,NULL),(2,2,'SAN-SYR','Botol 60ml','Botol','Karton',24,15000.00,350000.00,10,2,100,1,1,NULL),(3,3,'ESY-GLU','Tube (25 Pcs)','Tube','Tube',1,85000.00,800000.00,5,5,100,1,1,NULL),(4,4,'OMR-TENS','Unit HEM-7120','Unit','Unit',1,550000.00,2600000.00,2,2,100,1,1,NULL),(5,5,'OMR-THER','Unit MC-246','Unit','Unit',1,85000.00,800000.00,3,3,100,1,1,NULL),(6,6,'HUF-TMP','Hufagrip Merah (TMP)','Botol','Karton',50,18000.00,850000.00,5,2,100,1,1,NULL),(7,6,'HUF-PIL','Hufagrip Biru (Pilek)','Botol','Karton',50,18000.00,850000.00,5,2,100,1,1,NULL),(8,6,'HUF-BP','Hufagrip Hijau (BP)','Botol','Karton',50,18000.00,850000.00,5,2,100,1,1,NULL),(9,6,'HUF-FLU','Hufagrip Kuning (Flu Batuk)','Botol','Karton',50,18000.00,850000.00,5,2,100,1,1,NULL),(10,7,'PCT-500','Tablet 500mg','Strip','Box',10,3500.00,30000.00,5,2,100,1,1,NULL),(11,7,'PCT-250','Tablet 250mg','Strip','Box',10,2500.00,22000.00,5,2,100,1,1,NULL),(12,7,'PCT-125','Tablet 125mg','Strip','Box',10,2000.00,18000.00,5,2,100,1,1,NULL),(13,8,'PRM-01','Tablet Kunyah','Strip','Box',12,9500.00,110000.00,5,2,100,1,1,NULL),(14,9,'PND-EXT','Kaplet Merah','Strip','Box',10,12500.00,120000.00,5,2,100,1,1,NULL),(15,10,'TLK-ANG','Sachet 15ml','Sachet','Box',12,4500.00,52000.00,5,2,100,1,1,NULL),(16,11,'MYL-150','Botol 150ml','Botol','Karton',24,45000.00,1050000.00,5,2,100,1,1,NULL),(17,12,'INS-REG','Botol 7.5ml','Botol','Box',12,16000.00,185000.00,5,2,100,1,1,NULL),(18,13,'BET-15','Botol 15ml','Botol','Box',12,12500.00,145000.00,5,2,100,1,1,NULL),(19,14,'CTP-30','Tube 30g','Tube','Box',10,55000.00,530000.00,5,2,100,1,1,NULL),(20,15,'NEU-FRT','Tablet Merah','Strip','Box',10,48000.00,460000.00,5,2,100,1,1,NULL),(21,16,'SNG-CAP','Kapsul Merah','Strip','Box',25,18500.00,450000.00,5,2,100,1,1,NULL),(22,17,'DPT-CAP','Kapsul Isi 10','Strip','Box',25,4500.00,105000.00,5,2,100,1,1,NULL),(23,18,'ENT-DSW','Tablet','Strip','Box',20,9500.00,180000.00,5,2,100,1,1,NULL),(24,19,'WOD-PEP','Botol 100ml','Botol','Karton',24,38000.00,890000.00,5,2,100,1,1,NULL),(25,20,'KMX-HRB','Sachet 15ml','Sachet','Box',30,2500.00,70000.00,5,2,100,1,1,NULL),(26,21,'ANT-JRG','Sachet 15ml','Sachet','Box',12,4000.00,46000.00,5,2,100,1,1,NULL),(27,22,'CDR-EFT','Tube Isi 15','Tube','Karton',10,65000.00,630000.00,5,2,100,1,1,NULL),(28,23,'RDX-DBL','Tube Isi 10','Tube','Karton',10,55000.00,530000.00,5,2,100,1,1,NULL),(29,24,'ENV-C','Strip Isi 4','Strip','Box',25,6000.00,145000.00,5,2,100,1,1,NULL),(30,25,'IMB-FRC','Strip Isi 10','Strip','Box',3,85000.00,245000.00,5,2,100,1,1,NULL),(31,26,'BDR-MGR','Strip Isi 4','Strip','Box',25,3500.00,85000.00,5,2,100,1,1,NULL),(32,27,'NZP-FRT','Strip Isi 4','Strip','Box',25,3500.00,85000.00,5,2,100,1,1,NULL),(33,28,'DCL-GEN','Strip Isi 4','Strip','Box',25,3000.00,72000.00,5,2,100,1,1,NULL),(34,29,'OBH-CPL','Botol 100ml','Botol','Karton',24,25000.00,580000.00,5,2,100,1,1,NULL),(35,30,'SLN-PAS','Sachet Isi 10','Sachet','Box',40,8500.00,320000.00,5,2,100,1,1,NULL),(36,31,'VK-VP10','Pot 10g','Pot','Box',24,12000.00,275000.00,5,2,100,1,1,NULL),(37,32,'TLK-LNU','Sachet 15ml','Sachet','Box',12,4500.00,52000.00,5,2,100,1,1,NULL),(38,33,'DCD-EXP','Botol 120ml','Botol','Karton',24,22000.00,510000.00,5,2,100,1,1,NULL),(39,34,'PLS-CAP','Strip Isi 10','Strip','Box',10,12000.00,115000.00,5,2,100,1,1,NULL),(40,35,'CMB-JRK','Botol 10ml','Botol','Box',12,21000.00,245000.00,5,2,100,1,1,NULL),(41,36,'CFD-500','Strip Isi 10','Strip','Box',10,18000.00,170000.00,5,2,100,1,1,NULL),(42,37,'CTM-ALR','Strip Isi 12','Strip','Box',10,2500.00,23000.00,5,2,100,1,1,NULL),(43,38,'CTR-10','Strip Isi 10','Strip','Box',10,8500.00,80000.00,5,2,100,1,1,NULL),(44,39,'SNS-MSK','Box Isi 50','Box','Box',1,25000.00,950000.00,5,5,100,1,1,NULL),(45,40,'HNS-PLS','Box Isi 100','Lembar','Lembar',1,800.00,75000.00,5,5,100,1,1,NULL),(46,41,'DRM-FIX','Pcs 5x7cm','Pcs','Pcs',1,4500.00,105000.00,5,5,100,1,1,NULL),(47,42,'OM-KSA','Kotak Isi 10','Kotak','Kotak',1,12000.00,580000.00,5,5,100,1,1,NULL),(48,43,'OM-ALK','Botol 100ml','Botol','Botol',1,8500.00,195000.00,5,5,100,1,1,NULL),(49,44,'RVN-100','Botol 100ml','Botol','Botol',1,7500.00,175000.00,5,5,100,1,1,NULL),(50,45,'PVD-1L','Botol 1L','Botol','Botol',1,125000.00,1200000.00,5,5,100,1,1,NULL),(51,46,'ESY-UA','Tube Isi 25','Tube','Tube',1,95000.00,900000.00,5,5,100,1,1,NULL),(52,47,'NSC-CHL','Tube Isi 10','Tube','Tube',1,135000.00,1300000.00,5,5,100,1,1,NULL),(53,48,'SLL-KRD','Unit Standar','Unit','Unit',1,1450000.00,1400000.00,5,5,100,1,1,NULL),(54,49,'O2-1M3','Tabung 1m3','Tabung','Tabung',1,750000.00,720000.00,5,5,100,1,1,NULL),(55,50,'O2-REG','Unit Flowmeter','Unit','Unit',1,250000.00,2400000.00,5,5,100,1,1,NULL),(56,51,'OMR-NEB','Unit NE-C104','Unit','Unit',1,850000.00,4100000.00,5,5,100,1,1,NULL),(57,52,'IR-THER','Unit Pistol','Unit','Unit',1,125000.00,2400000.00,5,5,100,1,1,NULL),(58,53,'LTM-STC','Unit Classic III','Unit','Unit',1,1850000.00,18000000.00,5,5,100,1,1,NULL);
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
  `no_batch` varchar(100) NOT NULL,
  `tgl_exp` date NOT NULL,
  `stok_sisa` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fefo` (`id_variasi`,`tgl_exp`),
  CONSTRAINT `stok_batch_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_batch`
--

LOCK TABLES `stok_batch` WRITE;
/*!40000 ALTER TABLE `stok_batch` DISABLE KEYS */;
INSERT INTO `stok_batch` VALUES (1,1,'AMX-2025.A','2025-01-10',8,'2026-10-06 07:39:14'),(2,1,'AMX-2025.B','2025-01-10',10,'2026-10-06 07:39:14'),(3,1,'AMX-2026','2026-12-15',50,'2026-10-06 07:39:14'),(4,2,'SAN-001','2026-08-01',48,'2026-10-06 07:39:14'),(5,2,'SAN-001.A','2026-08-01',15,'2026-10-06 07:39:14'),(6,2,'SAN-002','2028-05-10',120,'2026-10-06 07:39:14'),(7,3,'EASY-GLUCO-01','2027-10-10',50,'2026-10-06 07:39:14'),(8,3,'EASY-GLUCO-01.A','2027-10-10',1,'2026-10-06 07:39:14'),(9,6,'HUF-2026-00','2026-10-10',200,'2026-10-06 07:39:14'),(10,6,'HUF-2026-00.A','2026-10-10',12,'2026-10-06 07:39:14'),(11,6,'HUF-2027-00','2027-10-10',300,'2026-10-06 07:39:14'),(12,7,'HUF-2026-01','2026-10-10',150,'2026-10-06 07:39:14'),(13,7,'HUF-2026-01.A','2026-10-10',12,'2026-10-06 07:39:14'),(14,7,'HUF-2027-01','2027-10-10',300,'2026-10-06 07:39:14'),(15,8,'HUF-2026-02','2026-10-10',200,'2026-10-06 07:39:14'),(16,8,'HUF-2026-02.A','2026-10-10',12,'2026-10-06 07:39:14'),(17,8,'HUF-2027-02','2027-10-10',300,'2026-10-06 07:39:14'),(18,9,'HUF-2026-03','2026-10-10',200,'2026-10-06 07:39:14'),(19,9,'HUF-2026-03.A','2026-10-10',12,'2026-10-06 07:39:14'),(20,9,'HUF-2027-03','2027-10-10',300,'2026-10-06 07:39:14'),(21,10,'PCT-25-0','2025-11-01',50,'2026-10-06 07:39:14'),(22,10,'PCT-25-0.A','2025-11-01',5,'2026-10-06 07:39:14'),(23,10,'PCT-26-0','2026-11-01',100,'2026-10-06 07:39:14'),(24,11,'PCT-25-1','2025-11-01',50,'2026-10-06 07:39:14'),(25,11,'PCT-25-1.A','2025-11-01',5,'2026-10-06 07:39:14'),(26,11,'PCT-26-1','2026-11-01',100,'2026-10-06 07:39:14'),(27,12,'PCT-25-2','2025-11-01',50,'2026-10-06 07:39:14'),(28,12,'PCT-25-2.A','2025-11-01',5,'2026-10-06 07:39:14'),(29,12,'PCT-26-2','2026-11-01',100,'2026-10-06 07:39:14'),(30,13,'BCH-25-1','2025-08-01',600,'2026-10-06 07:39:14'),(31,13,'BCH-25-1.A','2025-08-01',3,'2026-10-06 07:39:14'),(32,13,'BCH-26-1','2026-10-15',1200,'2026-10-06 07:39:14'),(33,14,'BCH-25-2','2025-08-01',500,'2026-10-06 07:39:14'),(34,14,'BCH-25-2.A','2025-08-01',3,'2026-10-06 07:39:14'),(35,14,'BCH-26-2','2026-10-15',1000,'2026-10-06 07:39:14'),(36,15,'BCH-25-3','2025-08-01',600,'2026-10-06 07:39:14'),(37,15,'BCH-25-3.A','2025-08-01',3,'2026-10-06 07:39:14'),(38,15,'BCH-26-3','2026-10-15',1200,'2026-10-06 07:39:14'),(39,16,'BCH-25-4','2025-08-01',1200,'2026-10-06 07:39:14'),(40,16,'BCH-25-4.A','2025-08-01',3,'2026-10-06 07:39:14'),(41,16,'BCH-26-4','2026-10-15',2400,'2026-10-06 07:39:14'),(42,17,'BCH-25-5','2025-08-01',600,'2026-10-06 07:39:14'),(43,17,'BCH-25-5.A','2025-08-01',3,'2026-10-06 07:39:14'),(44,17,'BCH-26-5','2026-10-15',1200,'2026-10-06 07:39:14'),(45,18,'BCH-25-6','2025-08-01',600,'2026-10-06 07:39:14'),(46,18,'BCH-25-6.A','2025-08-01',3,'2026-10-06 07:39:14'),(47,18,'BCH-26-6','2026-10-15',1200,'2026-10-06 07:39:14'),(48,19,'BCH-25-7','2025-08-01',500,'2026-10-06 07:39:14'),(49,19,'BCH-25-7.A','2025-08-01',3,'2026-10-06 07:39:14'),(50,19,'BCH-26-7','2026-10-15',1000,'2026-10-06 07:39:14'),(51,20,'BCH-25-8','2025-08-01',500,'2026-10-06 07:39:14'),(52,20,'BCH-25-8.A','2025-08-01',3,'2026-10-06 07:39:14'),(53,20,'BCH-26-8','2026-10-15',1000,'2026-10-06 07:39:14'),(54,21,'BCH-25-9','2025-08-01',1250,'2026-10-06 07:39:14'),(55,21,'BCH-25-9.A','2025-08-01',3,'2026-10-06 07:39:14'),(56,21,'BCH-26-9','2026-10-15',2500,'2026-10-06 07:39:14'),(57,22,'BCH-25-10','2025-08-01',1250,'2026-10-06 07:39:14'),(58,22,'BCH-25-10.A','2025-08-01',3,'2026-10-06 07:39:14'),(59,22,'BCH-26-10','2026-10-15',2500,'2026-10-06 07:39:14'),(60,23,'BCH-25-11','2025-08-01',1000,'2026-10-06 07:39:14'),(61,23,'BCH-25-11.A','2025-08-01',3,'2026-10-06 07:39:14'),(62,23,'BCH-26-11','2026-10-15',2000,'2026-10-06 07:39:14'),(63,24,'BCH-25-12','2025-08-01',1200,'2026-10-06 07:39:14'),(64,24,'BCH-25-12.A','2025-08-01',3,'2026-10-06 07:39:14'),(65,24,'BCH-26-12','2026-10-15',2400,'2026-10-06 07:39:14'),(66,25,'BCH-25-13','2025-08-01',1500,'2026-10-06 07:39:14'),(67,25,'BCH-25-13.A','2025-08-01',3,'2026-10-06 07:39:14'),(68,25,'BCH-26-13','2026-10-15',3000,'2026-10-06 07:39:14'),(69,26,'BCH-25-14','2025-08-01',600,'2026-10-06 07:39:14'),(70,26,'BCH-25-14.A','2025-08-01',3,'2026-10-06 07:39:14'),(71,26,'BCH-26-14','2026-10-15',1200,'2026-10-06 07:39:14'),(72,27,'BCH-25-15','2025-08-01',500,'2026-10-06 07:39:14'),(73,27,'BCH-25-15.A','2025-08-01',3,'2026-10-06 07:39:14'),(74,27,'BCH-26-15','2026-10-15',1000,'2026-10-06 07:39:14'),(75,28,'BCH-25-16','2025-08-01',500,'2026-10-06 07:39:14'),(76,28,'BCH-25-16.A','2025-08-01',3,'2026-10-06 07:39:14'),(77,28,'BCH-26-16','2026-10-15',1000,'2026-10-06 07:39:14'),(78,29,'BCH-25-17','2025-08-01',1250,'2026-10-06 07:39:14'),(79,29,'BCH-25-17.A','2025-08-01',3,'2026-10-06 07:39:14'),(80,29,'BCH-26-17','2026-10-15',2500,'2026-10-06 07:39:14'),(81,30,'BCH-25-18','2025-08-01',150,'2026-10-06 07:39:14'),(82,30,'BCH-25-18.A','2025-08-01',3,'2026-10-06 07:39:14'),(83,30,'BCH-26-18','2026-10-15',300,'2026-10-06 07:39:14'),(84,31,'BCH-25-19','2025-08-01',1250,'2026-10-06 07:39:14'),(85,31,'BCH-25-19.A','2025-08-01',3,'2026-10-06 07:39:14'),(86,31,'BCH-26-19','2026-10-15',2500,'2026-10-06 07:39:14'),(87,32,'BCH-25-20','2025-08-01',1250,'2026-10-06 07:39:14'),(88,32,'BCH-25-20.A','2025-08-01',3,'2026-10-06 07:39:14'),(89,32,'BCH-26-20','2026-10-15',2500,'2026-10-06 07:39:14'),(90,33,'BCH-25-21','2025-08-01',1250,'2026-10-06 07:39:14'),(91,33,'BCH-25-21.A','2025-08-01',3,'2026-10-06 07:39:14'),(92,33,'BCH-26-21','2026-10-15',2500,'2026-10-06 07:39:14'),(93,34,'BCH-25-22','2025-08-01',1200,'2026-10-06 07:39:14'),(94,34,'BCH-25-22.A','2025-08-01',3,'2026-10-06 07:39:14'),(95,34,'BCH-26-22','2026-10-15',2400,'2026-10-06 07:39:14'),(96,35,'BCH-25-23','2025-08-01',2000,'2026-10-06 07:39:14'),(97,35,'BCH-25-23.A','2025-08-01',3,'2026-10-06 07:39:14'),(98,35,'BCH-26-23','2026-10-15',4000,'2026-10-06 07:39:14'),(99,36,'BCH-25-24','2025-08-01',1200,'2026-10-06 07:39:14'),(100,36,'BCH-25-24.A','2025-08-01',3,'2026-10-06 07:39:14'),(101,36,'BCH-26-24','2026-10-15',2400,'2026-10-06 07:39:14'),(102,37,'BCH-25-25','2025-08-01',600,'2026-10-06 07:39:14'),(103,37,'BCH-25-25.A','2025-08-01',3,'2026-10-06 07:39:14'),(104,37,'BCH-26-25','2026-10-15',1200,'2026-10-06 07:39:14'),(105,38,'BCH-25-26','2025-08-01',1200,'2026-10-06 07:39:14'),(106,38,'BCH-25-26.A','2025-08-01',3,'2026-10-06 07:39:14'),(107,38,'BCH-26-26','2026-10-15',2400,'2026-10-06 07:39:14'),(108,39,'BCH-25-27','2025-08-01',500,'2026-10-06 07:39:14'),(109,39,'BCH-25-27.A','2025-08-01',3,'2026-10-06 07:39:14'),(110,39,'BCH-26-27','2026-10-15',1000,'2026-10-06 07:39:14'),(111,40,'BCH-25-28','2025-08-01',600,'2026-10-06 07:39:14'),(112,40,'BCH-25-28.A','2025-08-01',3,'2026-10-06 07:39:14'),(113,40,'BCH-26-28','2026-10-15',1200,'2026-10-06 07:39:14'),(114,41,'BCH-25-29','2025-08-01',500,'2026-10-06 07:39:14'),(115,41,'BCH-25-29.A','2025-08-01',3,'2026-10-06 07:39:14'),(116,41,'BCH-26-29','2026-10-15',1000,'2026-10-06 07:39:14'),(117,42,'BCH-25-30','2025-08-01',500,'2026-10-06 07:39:14'),(118,42,'BCH-25-30.A','2025-08-01',3,'2026-10-06 07:39:14'),(119,42,'BCH-26-30','2026-10-15',1000,'2026-10-06 07:39:14'),(120,43,'BCH-25-31','2025-08-01',500,'2026-10-06 07:39:14'),(121,43,'BCH-25-31.A','2025-08-01',3,'2026-10-06 07:39:14'),(122,43,'BCH-26-31','2026-10-15',1000,'2026-10-06 07:39:14'),(123,44,'ALK-25-32','2025-11-20',800,'2026-10-06 07:39:14'),(124,44,'ALK-25-32.A','2025-11-20',2,'2026-10-06 07:39:14'),(125,45,'ALK-25-33','2025-11-20',2000,'2026-10-06 07:39:14'),(126,45,'ALK-25-33.A','2025-11-20',2,'2026-10-06 07:39:14'),(127,46,'ALK-25-34','2025-11-20',500,'2026-10-06 07:39:14'),(128,46,'ALK-25-34.A','2025-11-20',2,'2026-10-06 07:39:14'),(129,47,'ALK-25-35','2025-11-20',1000,'2026-10-06 07:39:14'),(130,47,'ALK-25-35.A','2025-11-20',2,'2026-10-06 07:39:14'),(131,48,'ALK-25-36','2025-11-20',480,'2026-10-06 07:39:14'),(132,48,'ALK-25-36.A','2025-11-20',2,'2026-10-06 07:39:14'),(133,49,'ALK-25-37','2025-11-20',480,'2026-10-06 07:39:14'),(134,49,'ALK-25-37.A','2025-11-20',2,'2026-10-06 07:39:14'),(135,50,'ALK-25-38','2025-11-20',200,'2026-10-06 07:39:14'),(136,50,'ALK-25-38.A','2025-11-20',2,'2026-10-06 07:39:14'),(137,51,'ALK-25-39','2025-11-20',200,'2026-10-06 07:39:14'),(138,51,'ALK-25-39.A','2025-11-20',2,'2026-10-06 07:39:14'),(139,52,'ALK-25-40','2025-11-20',200,'2026-10-06 07:39:14'),(140,52,'ALK-25-40.A','2025-11-20',2,'2026-10-06 07:39:14'),(141,7,'HUF-2026-01.B','2026-10-10',50,'2026-10-07 06:27:35');
/*!40000 ALTER TABLE `stok_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_toko`
--

DROP TABLE IF EXISTS `stok_toko`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_toko` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `stok_toko_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_toko`
--

LOCK TABLES `stok_toko` WRITE;
/*!40000 ALTER TABLE `stok_toko` DISABLE KEYS */;
INSERT INTO `stok_toko` VALUES (1,1,68),(2,2,183),(3,3,51),(4,4,5),(5,5,5),(6,6,512),(7,7,512),(8,8,512),(9,9,512),(10,10,155),(11,11,155),(12,12,155),(13,13,1803),(14,14,1503),(15,15,1803),(16,16,3603),(17,17,1803),(18,18,1803),(19,19,1503),(20,20,1503),(21,21,3753),(22,22,3753),(23,23,3003),(24,24,3603),(25,25,4503),(26,26,1803),(27,27,1503),(28,28,1503),(29,29,3753),(30,30,453),(31,31,3753),(32,32,3753),(33,33,3753),(34,34,3603),(35,35,6003),(36,36,3603),(37,37,1803),(38,38,3603),(39,39,1503),(40,40,1803),(41,41,1503),(42,42,1503),(43,43,1503),(44,44,802),(45,45,2002),(46,46,502),(47,47,1002),(48,48,482),(49,49,482),(50,50,202),(51,51,202),(52,52,202),(53,53,3),(54,54,3),(55,55,3),(56,56,3),(57,57,3),(58,58,3);
/*!40000 ALTER TABLE `stok_toko` ENABLE KEYS */;
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
  `email` varchar(150) DEFAULT NULL,
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
INSERT INTO `supplier` VALUES (1,'PT Kimia Farma Trading & Distribution','0341-999001','kf.malang@kimiafarma.co.id','Jl. Farmasi Raya No.1, Malang',1),(2,'PT Omron Healthcare Indonesia','021-555222','info@omron-healthcare.co.id','Gedung Omron, Jakarta Pusat',1),(3,'PT Jayamas Medica Industri (OneMed)','031-444333','sales@onemed.co.id','Kawasan Industri Rungkut, Surabaya',1);
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
  `serial_number` varchar(150) NOT NULL,
  `status` enum('Tersedia','Terjual','Retur/Rusak') NOT NULL DEFAULT 'Tersedia',
  `id_penjualan` int(11) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `tgl_habis_garansi` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `unit_serial_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_serial`
--

LOCK TABLES `unit_serial` WRITE;
/*!40000 ALTER TABLE `unit_serial` DISABLE KEYS */;
INSERT INTO `unit_serial` VALUES (1,4,'OMR-111001','Tersedia',NULL,NULL,'2028-01-01','2026-10-06 07:39:14',NULL),(2,4,'OMR-111002','Tersedia',NULL,NULL,'2028-01-01','2026-10-06 07:39:14',NULL),(3,4,'OMR-111003','Tersedia',NULL,NULL,'2028-01-01','2026-10-06 07:39:14',NULL),(4,4,'OMR-111004','Tersedia',NULL,NULL,'2028-01-01','2026-10-06 07:39:14',NULL),(5,4,'OMR-111005','Tersedia',NULL,NULL,'2026-11-06','2026-10-06 07:39:14',NULL),(6,5,'OMR-TH-1','Tersedia',NULL,NULL,'2027-06-15','2026-10-06 07:39:14',NULL),(7,5,'OMR-TH-2','Tersedia',NULL,NULL,'2027-06-15','2026-10-06 07:39:14',NULL),(8,5,'OMR-TH-3','Tersedia',NULL,NULL,'2027-06-15','2026-10-06 07:39:14',NULL),(9,5,'OMR-TH-4','Tersedia',NULL,NULL,'2027-06-15','2026-10-06 07:39:14',NULL),(10,5,'OMR-TH-5','Tersedia',NULL,NULL,'2027-06-15','2026-10-06 07:39:14',NULL),(11,53,'SN-SLL-KRD-1001','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(12,53,'SN-SLL-KRD-1002','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(13,53,'SN-SLL-KRD-1003','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(14,54,'SN-O2-1M3-1001','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(15,54,'SN-O2-1M3-1002','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(16,54,'SN-O2-1M3-1003','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(17,55,'SN-O2-REG-1001','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(18,55,'SN-O2-REG-1002','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(19,55,'SN-O2-REG-1003','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(20,56,'SN-OMR-NEB-1001','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(21,56,'SN-OMR-NEB-1002','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(22,56,'SN-OMR-NEB-1003','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(23,57,'SN-IR-THER-1001','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(24,57,'SN-IR-THER-1002','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(25,57,'SN-IR-THER-1003','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(26,58,'SN-LTM-STC-1001','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(27,58,'SN-LTM-STC-1002','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL),(28,58,'SN-LTM-STC-1003','Tersedia',NULL,NULL,'2028-12-31','2026-10-06 07:39:14',NULL);
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
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('superadmin','admin','kasir','pelanggan') NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL COMMENT 'Alamat utama pelanggan untuk pengiriman',
  `kota_id` int(11) DEFAULT NULL COMMENT 'ID kota RajaOngkir untuk kalkulasi ongkir',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','SuperAdmin (Pemilik)','superadmin','superadmin@zencare.id','081234567890',NULL,NULL,1,'2026-09-10 07:42:09'),(2,'admin_toko','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','Admin Toko','admin','admin@zencare.id','082111222333',NULL,NULL,1,'2026-09-10 07:42:09'),(3,'pelanggan1','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','Budi Santoso','pelanggan','budi.santoso@gmail.com','083333444555','Jl. Soekarno Hatta No.12, Malang',391,1,'2026-09-10 07:42:09'),(4,'kasir_toko','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','Kasir (Front Office)','kasir','kasir@zencare.id','08222333444',NULL,NULL,1,'2026-09-28 14:16:12');
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

-- Dump completed on 2026-10-07 18:57:39
