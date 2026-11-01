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
-- Table structure for table `detail_opname_batch`
--

DROP TABLE IF EXISTS `detail_opname_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_opname_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sesi` int(11) NOT NULL,
  `id_batch` int(11) DEFAULT NULL,
  `id_variasi` int(11) NOT NULL,
  `jenis_komoditas` enum('Obat','Alkes') NOT NULL DEFAULT 'Obat',
  `satuan_tipe` enum('besar','kecil') NOT NULL DEFAULT 'kecil',
  `nama_satuan` varchar(50) NOT NULL DEFAULT 'Unit',
  `stok_tersedia_sistem` int(11) NOT NULL,
  `stok_reserved` int(11) NOT NULL,
  `stok_sistem_fisik` int(11) NOT NULL,
  `hitung_fisik` int(11) DEFAULT NULL,
  `selisih` int(11) DEFAULT NULL,
  `status_mutasi` enum('Belum Dimutasi','Sudah Dimutasi','Sesuai') NOT NULL DEFAULT 'Belum Dimutasi',
  `alasan_mutasi` enum('Barang Rusak','Barang Kedaluwarsa','Barang Hilang','Koreksi Salah Catat / Ditemukan','Lainnya') DEFAULT NULL,
  `keterangan_mutasi` text DEFAULT NULL,
  `id_kartu_stok` int(11) DEFAULT NULL,
  `dihitung_oleh` int(11) DEFAULT NULL,
  `waktu_hitung` timestamp NULL DEFAULT NULL,
  `dimutasi_oleh` int(11) DEFAULT NULL,
  `waktu_mutasi` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_batch` (`id_batch`),
  KEY `id_variasi` (`id_variasi`),
  KEY `dihitung_oleh` (`dihitung_oleh`),
  KEY `dimutasi_oleh` (`dimutasi_oleh`),
  KEY `idx_dob_sesi_batch` (`id_sesi`,`id_batch`),
  CONSTRAINT `detail_opname_batch_ibfk_1` FOREIGN KEY (`id_sesi`) REFERENCES `sesi_opname` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_opname_batch_ibfk_2` FOREIGN KEY (`id_batch`) REFERENCES `stok_batch` (`id`),
  CONSTRAINT `detail_opname_batch_ibfk_3` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  CONSTRAINT `detail_opname_batch_ibfk_4` FOREIGN KEY (`dihitung_oleh`) REFERENCES `users` (`id`),
  CONSTRAINT `detail_opname_batch_ibfk_5` FOREIGN KEY (`dimutasi_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=255 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_opname_batch`
--

LOCK TABLES `detail_opname_batch` WRITE;
/*!40000 ALTER TABLE `detail_opname_batch` DISABLE KEYS */;
INSERT INTO `detail_opname_batch` (`id`, `id_sesi`, `id_batch`, `id_variasi`, `jenis_komoditas`, `satuan_tipe`, `nama_satuan`, `stok_tersedia_sistem`, `stok_reserved`, `stok_sistem_fisik`, `hitung_fisik`, `selisih`, `status_mutasi`, `alasan_mutasi`, `keterangan_mutasi`, `id_kartu_stok`, `dihitung_oleh`, `waktu_hitung`, `dimutasi_oleh`, `waktu_mutasi`) VALUES (5,3,66,51,'Obat','kecil','Tube',15,0,15,5,-10,'Belum Dimutasi',NULL,'',NULL,1,'2026-11-01 02:35:30',1,'2026-11-01 02:43:01'),(6,3,67,52,'Obat','kecil','Tube',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,3,63,48,'Obat','kecil','Botol',55,0,55,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,3,1,1,'Obat','kecil','Strip',0,0,0,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,3,7,1,'Obat','kecil','Strip',30,0,30,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,3,8,1,'Obat','kecil','Strip',8,0,8,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,3,81,26,'Obat','kecil','Sachet',12,0,12,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,3,40,26,'Obat','kecil','Sachet',2148,0,2148,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(13,3,30,18,'Obat','kecil','Botol',180,0,180,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(14,3,4,31,'Obat','kecil','Strip',100,0,100,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(15,3,45,31,'Obat','kecil','Strip',6250,0,6250,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(16,3,41,27,'Obat','kecil','Tube',100,0,100,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(17,3,55,41,'Obat','kecil','Strip',80,0,80,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(18,3,56,41,'Obat','kecil','Strip',20,0,20,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(19,3,58,43,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(20,3,54,40,'Obat','kecil','Botol',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(21,3,31,19,'Obat','kecil','Tube',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,3,5,42,'Obat','kecil','Strip',80,0,80,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(23,3,57,42,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(24,3,52,38,'Obat','kecil','Botol',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(25,3,47,33,'Obat','kecil','Strip',200,0,200,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(26,3,34,22,'Obat','kecil','Strip',500,0,500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(27,3,43,29,'Obat','kecil','Strip',375,0,375,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(28,3,35,23,'Obat','kecil','Strip',6000,0,6000,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(29,3,60,45,'Obat','kecil','Lembar',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(30,3,80,7,'Obat','kecil','Botol',48,0,48,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(31,3,82,7,'Obat','kecil','Botol',48,0,48,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(32,3,16,7,'Obat','kecil','Botol',34950,0,34950,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(33,3,68,60,'Obat','kecil','Botol',37500,0,37500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(34,3,17,8,'Obat','kecil','Botol',32450,0,32450,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(35,3,18,9,'Obat','kecil','Botol',37500,0,37500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(36,3,6,6,'Obat','kecil','Botol',500,0,500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(37,3,12,6,'Obat','kecil','Botol',300,0,300,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(38,3,13,6,'Obat','kecil','Botol',47,0,47,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(39,3,14,6,'Obat','kecil','Botol',400,0,400,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(40,3,15,6,'Obat','kecil','Botol',47,0,47,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(41,3,44,30,'Obat','kecil','Strip',174,0,174,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(42,3,29,17,'Obat','kecil','Botol',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(43,3,62,47,'Obat','kecil','Kotak',65,0,65,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(44,3,39,25,'Obat','kecil','Sachet',13500,0,13500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(45,3,59,44,'Obat','kecil','Box',65,0,65,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(46,3,28,16,'Obat','kecil','Botol',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(47,3,46,32,'Obat','kecil','Strip',9375,0,9375,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(48,3,32,20,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(49,3,48,34,'Obat','kecil','Botol',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(50,3,26,14,'Obat','kecil','Strip',250,0,250,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(51,3,23,12,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(52,3,22,11,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(53,3,2,10,'Obat','kecil','Strip',0,0,0,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(54,3,19,10,'Obat','kecil','Strip',230,0,230,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(55,3,20,10,'Obat','kecil','Strip',7,0,7,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(56,3,21,10,'Obat','kecil','Strip',8,0,8,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(57,3,61,46,'Obat','kecil','Pcs',115,0,115,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(58,3,53,39,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(59,3,65,50,'Obat','kecil','Botol',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(60,3,24,13,'Obat','kecil','Strip',348,0,348,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(61,3,25,13,'Obat','kecil','Strip',12,0,12,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(62,3,42,28,'Obat','kecil','Tube',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(63,3,64,49,'Obat','kecil','Botol',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(64,3,49,35,'Obat','kecil','Sachet',24000,0,24000,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(65,3,33,21,'Obat','kecil','Strip',9375,0,9375,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(66,3,3,2,'Obat','kecil','Botol',120,0,120,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(67,3,9,2,'Obat','kecil','Botol',240,0,240,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(68,3,10,2,'Obat','kecil','Botol',24,0,24,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(69,3,11,3,'Obat','kecil','Tube',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(70,3,27,15,'Obat','kecil','Sachet',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(71,3,51,37,'Obat','kecil','Sachet',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(72,3,50,36,'Obat','kecil','Pot',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(73,3,38,24,'Obat','kecil','Botol',24,0,24,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(74,3,36,24,'Obat','kecil','Botol',192,0,192,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(75,3,37,24,'Obat','kecil','Botol',72,0,72,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(81,3,NULL,53,'Alkes','kecil','Unit',2,0,2,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(83,3,NULL,56,'Alkes','kecil','Unit',6,0,6,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(86,3,NULL,55,'Alkes','kecil','Unit',3,0,3,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(88,3,NULL,58,'Alkes','kecil','Unit',3,0,3,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(90,3,NULL,54,'Alkes','kecil','Tabung',4,0,4,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(91,3,NULL,4,'Alkes','kecil','Unit',8,0,8,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(92,3,NULL,5,'Alkes','kecil','Unit',11,0,11,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(93,3,NULL,57,'Alkes','kecil','Unit',6,0,6,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(97,6,66,51,'Obat','kecil','Tube',15,0,15,20,5,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-11-01 03:49:09',NULL,NULL),(98,6,67,52,'Obat','kecil','Tube',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(99,6,63,48,'Obat','kecil','Botol',55,0,55,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(100,6,1,1,'Obat','kecil','Strip',0,0,0,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(101,6,7,1,'Obat','kecil','Strip',40,0,40,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(102,6,8,1,'Obat','kecil','Strip',8,0,8,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(103,6,81,26,'Obat','kecil','Sachet',12,0,12,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(104,6,40,26,'Obat','kecil','Sachet',2148,0,2148,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(105,6,30,18,'Obat','kecil','Botol',180,0,180,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(106,6,4,31,'Obat','kecil','Strip',100,0,100,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(107,6,45,31,'Obat','kecil','Strip',6250,0,6250,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(108,6,41,27,'Obat','kecil','Tube',100,0,100,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(109,6,55,41,'Obat','kecil','Strip',80,0,80,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(110,6,56,41,'Obat','kecil','Strip',20,0,20,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(111,6,58,43,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(112,6,54,40,'Obat','kecil','Botol',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(113,6,31,19,'Obat','kecil','Tube',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(114,6,5,42,'Obat','kecil','Strip',80,0,80,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(115,6,57,42,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(116,6,52,38,'Obat','kecil','Botol',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(117,6,47,33,'Obat','kecil','Strip',200,0,200,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(118,6,34,22,'Obat','kecil','Strip',500,0,500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(119,6,43,29,'Obat','kecil','Strip',375,0,375,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(120,6,35,23,'Obat','kecil','Strip',6000,0,6000,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(121,6,60,45,'Obat','kecil','Lembar',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(122,6,16,7,'Obat','kecil','Botol',34950,0,34950,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(123,6,80,7,'Obat','kecil','Botol',48,0,48,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(124,6,82,7,'Obat','kecil','Botol',48,0,48,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(125,6,68,60,'Obat','kecil','Botol',37500,0,37500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(126,6,17,8,'Obat','kecil','Botol',32450,0,32450,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(127,6,18,9,'Obat','kecil','Botol',37500,0,37500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(128,6,6,6,'Obat','kecil','Botol',500,0,500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(129,6,12,6,'Obat','kecil','Botol',300,0,300,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(130,6,13,6,'Obat','kecil','Botol',47,0,47,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(131,6,14,6,'Obat','kecil','Botol',400,0,400,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(132,6,15,6,'Obat','kecil','Botol',47,0,47,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(133,6,44,30,'Obat','kecil','Strip',174,0,174,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(134,6,29,17,'Obat','kecil','Botol',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(135,6,62,47,'Obat','kecil','Kotak',65,0,65,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(136,6,39,25,'Obat','kecil','Sachet',13500,0,13500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(137,6,59,44,'Obat','kecil','Box',65,0,65,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(138,6,28,16,'Obat','kecil','Botol',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(139,6,46,32,'Obat','kecil','Strip',9375,0,9375,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(140,6,32,20,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(141,6,48,34,'Obat','kecil','Botol',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(142,6,26,14,'Obat','kecil','Strip',250,0,250,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(143,6,23,12,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(144,6,22,11,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(145,6,2,10,'Obat','kecil','Strip',0,0,0,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(146,6,19,10,'Obat','kecil','Strip',230,0,230,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(147,6,20,10,'Obat','kecil','Strip',7,0,7,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(148,6,21,10,'Obat','kecil','Strip',8,0,8,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(149,6,61,46,'Obat','kecil','Pcs',115,0,115,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(150,6,53,39,'Obat','kecil','Strip',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(151,6,65,50,'Obat','kecil','Botol',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(152,6,24,13,'Obat','kecil','Strip',348,0,348,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(153,6,25,13,'Obat','kecil','Strip',12,0,12,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(154,6,42,28,'Obat','kecil','Tube',1500,0,1500,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(155,6,64,49,'Obat','kecil','Botol',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(156,6,49,35,'Obat','kecil','Sachet',24000,0,24000,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(157,6,33,21,'Obat','kecil','Strip',9375,0,9375,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(158,6,3,2,'Obat','kecil','Botol',120,0,120,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(159,6,9,2,'Obat','kecil','Botol',240,0,240,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(160,6,10,2,'Obat','kecil','Botol',24,0,24,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(161,6,11,3,'Obat','kecil','Tube',15,0,15,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(162,6,27,15,'Obat','kecil','Sachet',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(163,6,51,37,'Obat','kecil','Sachet',2160,0,2160,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(164,6,50,36,'Obat','kecil','Pot',8640,0,8640,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(165,6,36,24,'Obat','kecil','Botol',192,0,192,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(166,6,38,24,'Obat','kecil','Botol',24,0,24,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(167,6,37,24,'Obat','kecil','Botol',72,0,72,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(168,6,NULL,53,'Alkes','kecil','Unit',2,0,2,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(169,6,NULL,56,'Alkes','kecil','Unit',6,0,6,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(170,6,NULL,55,'Alkes','kecil','Unit',3,0,3,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(171,6,NULL,58,'Alkes','kecil','Unit',3,0,3,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(172,6,NULL,54,'Alkes','kecil','Tabung',4,0,4,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(173,6,NULL,4,'Alkes','kecil','Unit',8,0,8,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(174,6,NULL,5,'Alkes','kecil','Unit',11,0,11,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(175,6,NULL,57,'Alkes','kecil','Unit',6,0,6,NULL,NULL,'Belum Dimutasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(176,7,8,1,'Obat','kecil','Strip',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(177,7,1,1,'Obat','besar','Box',0,0,0,0,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(178,7,7,1,'Obat','besar','Box',4,0,4,4,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(179,7,81,26,'Obat','kecil','Sachet',12,0,12,11,-1,'Sudah Dimutasi','Barang Rusak',NULL,NULL,1,'2026-10-15 03:30:00',1,'2026-10-15 04:10:00'),(180,7,40,26,'Obat','besar','Box',179,0,179,179,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(181,7,30,18,'Obat','besar','Box',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(182,7,45,31,'Obat','besar','Box',250,0,250,250,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(183,7,4,31,'Obat','besar','Box',4,0,4,2,-2,'Sudah Dimutasi','Barang Kedaluwarsa',NULL,NULL,1,'2026-10-15 03:30:00',1,'2026-10-15 04:15:00'),(184,7,41,27,'Obat','besar','Karton',10,0,10,10,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(185,7,55,41,'Obat','besar','Box',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(186,7,56,41,'Obat','besar','Box',2,0,2,2,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(187,7,58,43,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(188,7,54,40,'Obat','besar','Box',180,0,180,180,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(189,7,31,19,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(190,7,5,42,'Obat','besar','Box',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(191,7,57,42,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(192,7,52,38,'Obat','besar','Karton',360,0,360,360,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(193,7,47,33,'Obat','besar','Box',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(194,7,34,22,'Obat','besar','Box',20,0,20,20,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(195,7,43,29,'Obat','besar','Box',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(196,7,35,23,'Obat','besar','Box',300,0,300,300,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(197,7,82,7,'Obat','kecil','Botol',48,0,48,48,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(198,7,16,7,'Obat','besar','Karton',699,0,699,699,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(199,7,80,7,'Obat','kecil','Botol',48,0,48,48,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(200,7,68,60,'Obat','besar','Karton',750,0,750,750,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(201,7,17,8,'Obat','besar','Karton',649,0,649,649,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(202,7,18,9,'Obat','besar','Karton',750,0,750,750,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(203,7,12,6,'Obat','besar','Karton',6,0,6,6,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(204,7,15,6,'Obat','kecil','Botol',47,0,47,47,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(205,7,6,6,'Obat','besar','Karton',10,0,10,10,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(206,7,13,6,'Obat','kecil','Botol',47,0,47,47,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(207,7,14,6,'Obat','besar','Karton',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(208,7,44,30,'Obat','besar','Box',58,0,58,58,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(209,7,29,17,'Obat','besar','Box',180,0,180,180,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(210,7,39,25,'Obat','besar','Box',450,0,450,450,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(211,7,28,16,'Obat','besar','Karton',360,0,360,360,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(212,7,46,32,'Obat','besar','Box',375,0,375,375,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(213,7,32,20,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(214,7,48,34,'Obat','besar','Karton',360,0,360,360,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(215,7,26,14,'Obat','besar','Box',25,0,25,25,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(216,7,23,12,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(217,7,22,11,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(218,7,2,10,'Obat','besar','Box',0,0,0,0,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(219,7,21,10,'Obat','kecil','Strip',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(220,7,19,10,'Obat','besar','Box',23,0,23,23,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(221,7,20,10,'Obat','kecil','Strip',7,0,7,7,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(222,7,53,39,'Obat','besar','Box',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(223,7,24,13,'Obat','besar','Box',29,0,29,29,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(224,7,25,13,'Obat','kecil','Strip',12,0,12,12,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(225,7,42,28,'Obat','besar','Karton',150,0,150,150,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(226,7,49,35,'Obat','besar','Box',600,0,600,600,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(227,7,33,21,'Obat','besar','Box',375,0,375,375,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(228,7,3,2,'Obat','besar','Karton',5,0,5,5,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(229,7,9,2,'Obat','besar','Karton',10,0,10,10,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(230,7,10,2,'Obat','kecil','Botol',24,0,24,24,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(231,7,27,15,'Obat','besar','Box',180,0,180,180,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(232,7,51,37,'Obat','besar','Box',180,0,180,180,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(233,7,50,36,'Obat','besar','Box',360,0,360,360,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(234,7,38,24,'Obat','kecil','Botol',24,0,24,24,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(235,7,36,24,'Obat','besar','Karton',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(236,7,37,24,'Obat','besar','Karton',3,0,3,3,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(237,7,NULL,51,'Alkes','kecil','Tube',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(238,7,NULL,52,'Alkes','kecil','Tube',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(239,7,NULL,48,'Alkes','kecil','Botol',55,0,55,54,-1,'Sudah Dimutasi','Barang Rusak',NULL,NULL,1,'2026-10-15 03:30:00',1,'2026-10-15 04:20:00'),(240,7,NULL,45,'Alkes','kecil','Lembar',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(241,7,NULL,47,'Alkes','kecil','Kotak',65,0,65,65,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(242,7,NULL,53,'Alkes','kecil','Unit',2,0,2,2,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(243,7,NULL,44,'Alkes','kecil','Box',65,0,65,65,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(244,7,NULL,56,'Alkes','kecil','Unit',6,0,6,6,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(245,7,NULL,46,'Alkes','kecil','Pcs',115,0,115,115,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(246,7,NULL,50,'Alkes','kecil','Botol',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(247,7,NULL,55,'Alkes','kecil','Unit',3,0,3,3,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(248,7,NULL,49,'Alkes','kecil','Botol',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(249,7,NULL,58,'Alkes','kecil','Unit',3,0,3,3,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(250,7,NULL,3,'Alkes','kecil','Tube',15,0,15,15,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(251,7,NULL,54,'Alkes','kecil','Tabung',4,0,4,4,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(252,7,NULL,4,'Alkes','kecil','Unit',8,0,8,8,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(253,7,NULL,5,'Alkes','kecil','Unit',11,0,11,11,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL),(254,7,NULL,57,'Alkes','kecil','Unit',6,0,6,6,0,'Belum Dimutasi',NULL,NULL,NULL,1,'2026-10-15 03:30:00',NULL,NULL);
/*!40000 ALTER TABLE `detail_opname_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_opname_sn`
--

DROP TABLE IF EXISTS `detail_opname_sn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_opname_sn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_detail_opname` int(11) NOT NULL,
  `id_unit_serial` int(11) DEFAULT NULL,
  `serial_number` varchar(100) NOT NULL,
  `jenis_tindakan` enum('Kurang_Rusak','Kurang_Hilang','Tambah_Ditemukan') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_unit_serial` (`id_unit_serial`),
  KEY `idx_dos_detail` (`id_detail_opname`),
  KEY `idx_dos_sn` (`serial_number`),
  CONSTRAINT `detail_opname_sn_ibfk_1` FOREIGN KEY (`id_detail_opname`) REFERENCES `detail_opname_batch` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_opname_sn_ibfk_2` FOREIGN KEY (`id_unit_serial`) REFERENCES `unit_serial` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_opname_sn`
--

LOCK TABLES `detail_opname_sn` WRITE;
/*!40000 ALTER TABLE `detail_opname_sn` DISABLE KEYS */;
/*!40000 ALTER TABLE `detail_opname_sn` ENABLE KEYS */;
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
  `catatan_logistik` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_penjualan` (`id_penjualan`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` (`id`, `id_penjualan`, `id_variasi`, `qty`, `harga_satuan`, `catatan_logistik`) VALUES (1,1,1,2,5000.00,'AMX-2026-01.A (2x)'),(2,1,5,1,85000.00,'OMR-TH-01'),(3,2,10,5,3500.00,'PCT-500-26.A (3x), PCT-500-26.B (2x)'),(4,3,6,5,18000.00,'HUF-TMP-26.A (2x), HUF-TMP-27.A (3x)'),(5,3,6,1,850000.00,'HUF-TMP-27 (1 Karton = 50 Botol)'),(6,4,30,2,85000.00,'IMB-FRC-26 (2x)'),(7,5,2,1,350000.00,'SNM-2026-A (1 Karton = 24 Botol)'),(8,5,4,1,550000.00,'OMR-TENS-01'),(9,6,56,1,850000.00,'OMR-NEB-99'),(28,13,1,1,48000.00,'AMX-2026-01 (1 Box = 10 Strip)'),(29,14,1,1,48000.00,'AMX-2026-01 (1 Box = 10 Strip)'),(30,15,8,2,850000.00,'HUFBP-2027-01 (2 Karton = 100 Botol)'),(42,21,7,2,18000.00,'HUFPIL-2027-01.B (2 Botol)'),(43,21,6,1,18000.00,'HUF-TMP-26.A (1 Botol)'),(44,21,8,1,850000.00,'HUFBP-2027-01 (1 Karton)'),(45,22,7,2,18000.00,'HUFPIL-2027-01.A (2 Botol)'),(73,32,1,1,48000.00,'AMX-2026-01 (1 Box = 10 Strip)');
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
  `sisa_dus_akhir` int(11) DEFAULT 0,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_variasi` (`id_variasi`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  CONSTRAINT `histori_konversi_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  CONSTRAINT `histori_konversi_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `histori_konversi`
--

LOCK TABLES `histori_konversi` WRITE;
/*!40000 ALTER TABLE `histori_konversi` DISABLE KEYS */;
INSERT INTO `histori_konversi` (`id`, `id_variasi`, `batch_asal`, `batch_hasil`, `qty_box_buka`, `qty_pcs_hasil`, `sisa_dus_akhir`, `dibuat_oleh`, `created_at`) VALUES (1,1,'AMX-2026-01','AMX-2026-01.A',1,10,14,4,'2026-10-05 09:15:00'),(2,2,'SNM-2026-A','SNM-2026-A.A',1,24,11,2,'2026-10-07 10:30:00'),(3,10,'PCT-500-26','PCT-500-26.A',1,10,24,4,'2026-10-08 11:00:00'),(4,10,'PCT-500-26','PCT-500-26.B',1,10,23,4,'2026-10-10 14:20:00'),(5,6,'HUF-TMP-26','HUF-TMP-26.A',1,50,6,4,'2026-10-09 13:45:00'),(6,6,'HUF-TMP-27','HUF-TMP-27.A',1,50,9,2,'2026-10-11 15:30:00'),(7,13,'PRM-KLK-26','PRM-KLK-26.A',1,12,29,4,'2026-10-21 11:00:00'),(8,24,'WOD-PEP-26','WOD-PEP-26.A',1,24,11,2,'2026-10-23 16:30:00'),(15,7,'HUFPIL-2027-01','HUFPIL-2027-01.A',1,50,14,4,'2026-10-28 11:40:56'),(16,26,'ANTJRG-2027-01','ANTJRG-2027-01.A',1,12,179,1,'2026-10-29 06:30:34'),(17,7,'HUFPIL-2027-01','HUFPIL-2027-01.B',1,50,699,4,'2026-10-29 06:48:22');
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
  `id_batch` int(11) DEFAULT NULL,
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
  KEY `idx_kartu_batch` (`id_batch`),
  CONSTRAINT `kartu_stok_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`),
  CONSTRAINT `kartu_stok_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` (`id`, `id_variasi`, `id_batch`, `satuan_tipe`, `jenis_mutasi`, `kanal`, `alasan_mutasi`, `no_ref_dokumen`, `qty`, `sisa_stok`, `keterangan`, `dibuat_oleh`, `tanggal`) VALUES (1,1,NULL,'besar','Keluar','Manual','Barang Kedaluwarsa','MUT-EXP-20260901-01',15,2,'Pemusnahan Batch Kadaluarsa [Batch: AMX-OLD-2025]',2,'2026-09-01 10:00:00'),(2,10,NULL,'besar','Keluar','Manual','Barang Kedaluwarsa','MUT-EXP-20260901-02',20,20,'Pemusnahan Batch Kadaluarsa [Batch: PCT-OLD-2025]',2,'2026-09-01 10:15:00'),(3,1,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,14,'Bongkar 1 Box (Rp0 - Konversi) [Batch: AMX-2026-01]',4,'2026-10-05 09:15:00'),(4,1,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,10,10,'Lahir Sub-Batch AMX-2026-01.A (Masuk 10 Strip)',4,'2026-10-05 09:15:00'),(5,2,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,11,'Bongkar 1 Karton (Rp0 - Konversi) [Batch: SNM-2026-A]',2,'2026-10-07 10:30:00'),(6,2,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,24,24,'Lahir Sub-Batch SNM-2026-A.A (Masuk 24 Botol)',2,'2026-10-07 10:30:00'),(7,10,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,24,'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]',4,'2026-10-08 11:00:00'),(8,10,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,10,10,'Lahir Sub-Batch PCT-500-26.A (Masuk 10 Strip)',4,'2026-10-08 11:00:00'),(9,10,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,23,'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]',4,'2026-10-10 14:20:00'),(10,10,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,10,10,'Lahir Sub-Batch PCT-500-26.B (Masuk 10 Strip)',4,'2026-10-10 14:20:00'),(11,6,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,6,'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-26]',4,'2026-10-09 13:45:00'),(12,6,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,50,50,'Lahir Sub-Batch HUF-TMP-26.A (Masuk 50 Botol)',4,'2026-10-09 13:45:00'),(13,6,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,9,'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-27]',2,'2026-10-11 15:30:00'),(14,6,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,50,50,'Lahir Sub-Batch HUF-TMP-27.A (Masuk 50 Botol)',2,'2026-10-11 15:30:00'),(15,13,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,29,'Bongkar 1 Box (Rp0 - Konversi) [Batch: PRM-KLK-26]',4,'2026-10-21 11:00:00'),(16,13,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,12,12,'Lahir Sub-Batch PRM-KLK-26.A (Masuk 12 Strip)',4,'2026-10-21 11:00:00'),(17,24,NULL,'besar','Keluar','Manual','Koreksi Manual',NULL,1,11,'Bongkar 1 Karton (Rp0 - Konversi) [Batch: WOD-PEP-26]',2,'2026-10-23 16:30:00'),(18,24,NULL,'kecil','Masuk','Manual','Koreksi Manual',NULL,24,24,'Lahir Sub-Batch WOD-PEP-26.A (Masuk 24 Botol)',2,'2026-10-23 16:30:00'),(19,56,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261012-0901',1,4,'Penjualan Luring POS-20261012-0901 [SN: OMR-NEB-99]',4,'2026-10-12 16:10:00'),(20,56,NULL,'kecil','Masuk','Manual','Retur Barang Rusak','POS-20261012-0901',1,5,'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi) [SN: OMR-NEB-99]',2,'2026-10-15 11:20:00'),(21,1,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261014-1001',2,8,'Penjualan Luring POS-20261014-1001 [Batch: AMX-2026-01.A (2x)]',4,'2026-10-14 10:15:20'),(22,5,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261014-1001',1,4,'Penjualan Luring POS-20261014-1001 [SN: OMR-TH-01]',4,'2026-10-14 10:15:20'),(23,10,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261016-1002',3,7,'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.A (3 Tablet)]',4,'2026-10-16 14:22:10'),(24,10,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261016-1002',2,8,'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.B (2 Tablet)]',4,'2026-10-16 14:22:10'),(25,6,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261017-1003',2,48,'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-26.A (2 Botol)]',4,'2026-10-17 11:40:05'),(26,6,NULL,'kecil','Keluar','POS','Penjualan POS','POS-20261017-1003',3,47,'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27.A (3 Botol)]',4,'2026-10-17 11:40:05'),(27,6,NULL,'besar','Keluar','POS','Penjualan POS','POS-20261017-1003',1,8,'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27 (1 Karton = 50 Botol)]',4,'2026-10-17 11:40:05'),(28,30,NULL,'kecil','Keluar','E-Commerce','Penjualan E-Commerce','INV-20261018-041',2,58,'Pengiriman via Ekspedisi (Kurir) [Batch: IMB-FRC-26 (2x)]',2,'2026-10-18 09:30:00'),(29,2,NULL,'besar','Keluar','E-Commerce','Penjualan E-Commerce','INV-20261019-052',1,10,'Pengiriman via Ekspedisi (Kurir) [Batch: SNM-2026-A (1 Karton = 24 Botol)]',2,'2026-10-19 13:20:00'),(30,4,NULL,'kecil','Keluar','E-Commerce','Penjualan E-Commerce','INV-20261019-052',1,8,'Pengiriman via Ekspedisi (Kurir) [SN: OMR-TENS-01]',2,'2026-10-19 13:20:00'),(31,1,NULL,'kecil','Masuk','Manual','Selisih Stok Opname','SO-20261020-01',5,13,'Penyesuaian Opname Fisik: Ditemukan selisih fisik lebih 5 strip pada rak display [Batch: AMX-2026-01.A]',2,'2026-10-20 09:30:00'),(32,10,NULL,'besar','Keluar','Manual','Selisih Stok Opname','SO-20261020-02',1,22,'Penyesuaian Opname Fisik: Selisih fisik kurang 1 box kemasan rusak saat audit gudang [Batch: PCT-500-26]',2,'2026-10-20 10:15:00'),(33,1,NULL,'besar','Masuk','PO','Penerimaan Barang','PO-20261015-OBAT5',15,15,'Penerimaan PO Farmasi Kimia Farma [Batch: AMX-2026-01]',2,'2026-10-15 08:30:00'),(34,10,NULL,'besar','Masuk','PO','Penerimaan Barang','PO-20261015-OBAT5',25,25,'Penerimaan PO Farmasi Kimia Farma [Batch: PCT-500-26]',2,'2026-10-15 08:30:00'),(35,4,NULL,'kecil','Masuk','PO','Penerimaan Barang','PO-20261018-ALKES4',5,5,'Penerimaan PO Alat Kesehatan Omron [SN: OMR-TENS-01 s/d 05]',2,'2026-10-18 09:15:00'),(36,13,NULL,'besar','Masuk','Penerimaan','Penerimaan Barang','KUL-20261020-CAMPUR',30,30,'Kulakan OneMed [Batch: PRM-KLK-26]',4,'2026-10-20 10:00:00'),(37,41,NULL,'besar','Masuk','PO','Penerimaan Barang','PO-20261022-OBAT-RETUR',8,8,'Penerimaan PO Obat Awal [Batch: CFD-500-26] (2 box rusak)',2,'2026-10-22 11:20:00'),(38,41,NULL,'besar','Masuk','PO','Retur Barang Rusak','PO-20261022-OBAT-RETUR',2,10,'Penerimaan Barang Pengganti Retur Selesai (2 Box) [Batch: CFD-GNT-26]',2,'2026-10-25 10:30:00'),(39,4,NULL,'kecil','Masuk','PO','Penerimaan Barang','PO-20261023-ALKES-RETUR',4,9,'Penerimaan PO Alkes Omron [4 Unit Diterima, 2 Unit Cacat Menunggu Pengganti]',2,'2026-10-23 13:45:00'),(50,7,16,'besar','Keluar','Manual','',NULL,1,14,'Bongkar 1 Karton (Rp0 - Konversi POS) [Batch: HUFPIL-2027-01]',4,'2026-10-28 11:40:56'),(51,7,80,'kecil','Masuk','Manual','',NULL,50,50,'Lahir Sub-Batch HUFPIL-2027-01.A (Masuk 50 Botol)',4,'2026-10-28 11:40:56'),(52,1,7,'besar','Keluar','E-Commerce','Penjualan E-Commerce','WEB-20261029-191',1,13,'Pengiriman via Ekspedisi (Kurir) [Batch: AMX-2026-01 (1 Box)]',1,'2026-10-29 04:31:47'),(56,26,40,'besar','Keluar','Manual','',NULL,1,179,'Bongkar 1 Box (Rp0 - Konversi POS) [Batch: ANTJRG-2027-01]',1,'2026-10-29 06:30:34'),(57,26,81,'kecil','Masuk','Manual','',NULL,12,12,'Lahir Sub-Batch ANTJRG-2027-01.A (Masuk 12 Sachet)',1,'2026-10-29 06:30:34'),(64,7,16,'besar','Keluar','Manual','',NULL,1,699,'Bongkar 1 Karton (Rp0 - Konversi POS) [Batch: HUFPIL-2027-01]',4,'2026-10-29 06:48:22'),(65,7,82,'kecil','Masuk','Manual','',NULL,50,100,'Lahir Sub-Batch HUFPIL-2027-01.B (Masuk 50 Botol)',4,'2026-10-29 06:48:22'),(66,7,82,'kecil','Keluar','POS','Penjualan POS','POS-20261029-6744',2,37498,'Penjualan Luring POS-20261029-6744 [Batch: HUFPIL-2027-01.B (2 Botol)]',4,'2026-10-29 06:52:03'),(67,6,13,'kecil','Keluar','POS','Penjualan POS','POS-20261029-6744',1,1294,'Penjualan Luring POS-20261029-6744 [Batch: HUF-TMP-26.A (1 Botol)]',4,'2026-10-29 06:52:03'),(68,8,17,'besar','Keluar','POS','Penjualan POS','POS-20261029-6744',1,747,'Penjualan Luring POS-20261029-6744 [Batch: HUFBP-2027-01 (1 Karton)]',4,'2026-10-29 06:52:03'),(69,7,80,'kecil','Keluar','POS','Penjualan POS','POS-20261029-6112',2,37496,'Penjualan Luring POS-20261029-6112 [Batch: HUFPIL-2027-01.A (2 Botol)]',4,'2026-10-29 06:52:33'),(115,51,66,'kecil','Keluar','Manual','Selisih Stok Opname','OPN-20261029-999',10,5,'[Stok Opname: OPN-20261029-999] Batch: BMHP-ESYUA-28 | Barang Hilang',1,'2026-11-01 02:43:01'),(116,51,66,'kecil','Masuk','Manual','Koreksi Manual','OPN-20261029-999',10,15,'[Rollback] Pembatalan Sesi Opname OPN-20261029-999: Pengembalian stok selisih yang dibatalkan',1,'2026-11-01 02:48:38'),(123,26,81,'kecil','Keluar','Manual','Selisih Stok Opname','OPN-20261015-001',1,12,'[Stok Opname: OPN-20261015-001] Batch: ANTJRG-2027-01.A | Barang Rusak - Kemasan pecah saat penataan',1,'2026-10-15 04:10:00'),(124,31,4,'kecil','Keluar','Manual','Selisih Stok Opname','OPN-20261015-001',2,100,'[Stok Opname: OPN-20261015-001] Batch: BDR-EXP-TOMORROW | Barang Kedaluwarsa - Pemusnahan obat expired',1,'2026-10-15 04:15:00'),(125,48,NULL,'kecil','Keluar','Manual','Selisih Stok Opname','OPN-20261015-001',1,55,'[Stok Opname: OPN-20261015-001] SN: OMR-TENS-RET1 | Barang Rusak - Cacat pabrik display bergaris',1,'2026-10-15 04:20:00');
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
  `satuan_tipe` varchar(20) NOT NULL DEFAULT 'kecil',
  `status` enum('terlewat','disesuaikan') NOT NULL DEFAULT 'terlewat',
  `resolved_in_penjualan` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_anomali_fefo`
--

LOCK TABLES `log_anomali_fefo` WRITE;
/*!40000 ALTER TABLE `log_anomali_fefo` DISABLE KEYS */;
INSERT INTO `log_anomali_fefo` (`id`, `id_penjualan`, `id_user`, `id_variasi`, `batch_diambil`, `batch_seharusnya`, `qty`, `satuan_tipe`, `status`, `resolved_in_penjualan`, `resolved_at`, `created_at`) VALUES (1,6,4,1,'SNM-2026-A','SNM-EXP-22',2,'kecil','disesuaikan',6,'2026-10-28 15:00:00','2026-10-27 06:50:04'),(2,6,4,2,'BDR-2028','BDR-EXP-23',1,'kecil','disesuaikan',6,'2026-10-28 15:00:00','2026-10-28 06:50:04'),(3,21,4,7,'HUFPIL-2027-01.B','HUFPIL-2027-01.A',2,'kecil','disesuaikan',22,'2026-10-29 13:58:00','2026-10-29 06:52:03');
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
  `qty_pesan` int(11) NOT NULL DEFAULT 0,
  `qty_terima` int(11) NOT NULL,
  `qty_rusak` int(11) NOT NULL DEFAULT 0,
  `harga_beli` decimal(15,2) DEFAULT NULL,
  `no_batch` varchar(50) DEFAULT NULL,
  `tgl_exp` date DEFAULT NULL,
  `status_retur` varchar(30) NOT NULL DEFAULT 'tidak_ada',
  `qty_pengganti_diterima` int(11) NOT NULL DEFAULT 0,
  `no_batch_pengganti` varchar(50) DEFAULT NULL,
  `tgl_exp_pengganti` date DEFAULT NULL,
  `tgl_selesai_retur` datetime DEFAULT NULL,
  `catatan_retur` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_penerimaan` (`id_penerimaan`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `penerimaan_detail_ibfk_1` FOREIGN KEY (`id_penerimaan`) REFERENCES `penerimaan_stok` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penerimaan_detail_ibfk_2` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan_detail`
--

LOCK TABLES `penerimaan_detail` WRITE;
/*!40000 ALTER TABLE `penerimaan_detail` DISABLE KEYS */;
INSERT INTO `penerimaan_detail` (`id`, `id_penerimaan`, `id_variasi`, `qty_pesan`, `qty_terima`, `qty_rusak`, `harga_beli`, `no_batch`, `tgl_exp`, `status_retur`, `qty_pengganti_diterima`, `no_batch_pengganti`, `tgl_exp_pengganti`, `tgl_selesai_retur`, `catatan_retur`) VALUES (1,1,1,15,15,0,40000.00,'AMX-2026-01','2027-12-15','tidak_ada',0,NULL,NULL,NULL,NULL),(2,1,10,25,25,0,25000.00,'PCT-500-26','2027-11-20','tidak_ada',0,NULL,NULL,NULL,NULL),(3,1,2,12,12,0,300000.00,'SNM-2026-A','2028-05-10','tidak_ada',0,NULL,NULL,NULL,NULL),(4,1,6,10,10,0,750000.00,'HUF-TMP-27','2028-06-20','tidak_ada',0,NULL,NULL,NULL,NULL),(5,1,30,20,20,0,210000.00,'IMB-FRC-26','2027-09-15','tidak_ada',0,NULL,NULL,NULL,NULL),(6,2,4,5,5,0,450000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(7,2,5,5,5,0,65000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(8,2,56,4,4,0,680000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(9,2,57,6,6,0,95000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(10,3,13,30,30,0,95000.00,'PRM-KLK-26','2027-11-30','tidak_ada',0,NULL,NULL,NULL,NULL),(11,3,14,25,25,0,105000.00,'PND-KLK-26','2027-11-30','tidak_ada',0,NULL,NULL,NULL,NULL),(12,3,44,50,50,0,20000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(13,3,48,40,40,0,6500.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(14,4,41,10,8,2,150000.00,'CFD-500-26','2027-11-30','selesai_tukar_barang',2,'CFD-GNT-26','2028-02-15','2026-10-25 10:30:00','2 box blister rusak penyok telah ditukar 2 box pengganti utuh batch CFD-GNT-26'),(15,4,24,12,9,3,780000.00,'WOD-PEP-26','2028-04-10','selesai_tukar_barang',3,'WOD-GNT-26','2028-05-20','2026-10-25 11:15:00','3 karton botol pecah di kardus telah diganti 3 karton baru batch WOD-GNT-26'),(16,4,27,10,10,0,550000.00,'CDR-EFT-26','2027-11-30','tidak_ada',0,NULL,NULL,NULL,NULL),(17,4,29,15,15,0,125000.00,'ENV-C-26','2028-03-15','tidak_ada',0,NULL,NULL,NULL,NULL),(18,5,4,6,4,2,450000.00,NULL,NULL,'menunggu_pengganti',0,NULL,NULL,NULL,'2 unit layar LCD mati/garis [SN Cacat: OMR-TENS-RET1, OMR-TENS-RET2]. Menunggu replacement unit dari Omron.'),(19,5,5,8,7,1,65000.00,NULL,NULL,'menunggu_pengganti',0,NULL,NULL,NULL,'1 unit tombol power macet [SN Cacat: OMR-TH-RET1]. Menunggu tukar guling.'),(20,5,56,3,3,0,680000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(21,5,58,3,3,0,1500000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(22,5,53,2,2,0,1200000.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL),(23,6,33,10,8,2,62000.00,'DCL-GEN-26','2027-10-15','menunggu_pengganti',0,NULL,NULL,NULL,'2 box kemasan basah rembes saat ekspedisi toko, diajukan penukaran ke PBF OneMed'),(24,6,18,15,15,0,120000.00,'BET-15-26','2027-11-30','tidak_ada',0,NULL,NULL,NULL,NULL),(25,6,22,20,20,0,90000.00,'DPT-CAP-26','2027-09-10','tidak_ada',0,NULL,NULL,NULL,NULL),(26,6,54,5,4,1,650000.00,NULL,NULL,'menunggu_pengganti',0,NULL,NULL,NULL,'1 tabung drat kran valve bocor gas [SN Cacat: O21M3-RET1]. Menunggu penggantian tabung baru dari depot OneMed.'),(27,6,55,3,3,0,210000.00,NULL,NULL,'selesai_potong_faktur',0,NULL,NULL,'2026-10-25 15:30:00','Kompensasi disepakati potong tagihan faktur tunai karena stok distributor pengganti habis'),(28,6,47,50,50,0,9500.00,NULL,NULL,'tidak_ada',0,NULL,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan_stok`
--

LOCK TABLES `penerimaan_stok` WRITE;
/*!40000 ALTER TABLE `penerimaan_stok` DISABLE KEYS */;
INSERT INTO `penerimaan_stok` (`id`, `no_referensi`, `sumber`, `id_supplier`, `tanggal_terima`, `catatan`, `file_nota`, `dibuat_oleh`, `created_at`) VALUES (1,'PO-20261015-OBAT5','PO',1,'2026-10-15','Penerimaan stok awal 5 jenis obat farmasi resmi dari PT Kimia Farma Trading & Distribution',NULL,2,'2026-10-15 08:30:00'),(2,'PO-20261018-ALKES4','PO',2,'2026-10-18','Penerimaan 4 jenis alat kesehatan diagnostik bergaransi resmi PT Omron Healthcare (Nomor Seri unit terdaftar)',NULL,2,'2026-10-18 09:15:00'),(3,'KUL-20261020-CAMPUR','Pembelian Langsung',3,'2026-10-20','Kulakan langsung sediaan obat maag & flu serta BMHP alat kesehatan habis pakai volume besar dari PT OneMed',NULL,4,'2026-10-20 10:00:00'),(4,'PO-20261022-OBAT-RETUR','PO',1,'2026-10-22','PO 4 jenis obat - Ditemukan 2 box Cefadroxil dan 3 karton Woods pecah/rusak. Pihak Kimia Farma telah mengganti penuh barang pengganti (Klaim Tukar Selesai 100%).',NULL,2,'2026-10-22 11:20:00'),(5,'PO-20261023-ALKES-RETUR','PO',2,'2026-10-23','PO 5 jenis alat kesehatan - Ditemukan 2 unit Tensimeter (LCD garis) dan 1 unit Termometer (sensor mati). Berkas klaim telah diproses ke PT Omron (Menunggu Pengganti).',NULL,2,'2026-10-23 13:45:00'),(6,'KUL-20261024-CAMPUR-RETUR','Pembelian Langsung',3,'2026-10-24','Kulakan 3 obat & 3 alkes - 2 box Decolgen basah rembes dan 1 Tabung O2 katup bocor. Sedang proses klaim garansi retur ke OneMed (Menunggu Pengganti).',NULL,4,'2026-10-24 15:30:00');
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
INSERT INTO `pengaturan_web` (`id`, `nama_toko`, `deskripsi`, `telepon`, `whatsapp`, `alamat`, `logo`, `banner_promosi`, `email_cs`) VALUES (1,'ZenCare Medical','Distributor Resmi Alat Kesehatan & Obat-Obatan Terpercaya di Malang','0341-111222','6281234567890','Jl. Muharto No.1, Kota Malang, Jawa Timur 65118',NULL,NULL,'cs@zencare.id');
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` (`id`, `no_invoice`, `id_user`, `tipe_transaksi`, `metode_pengambilan`, `status_pesanan`, `total_harga`, `metode_bayar_pos`, `ongkir`, `nama_penerima`, `telepon_penerima`, `alamat_lengkap`, `kota_tujuan`, `kurir`, `layanan`, `snap_token`, `payment_method_ecommerce`, `paid_at`, `kode_pickup`, `created_at`, `updated_at`) VALUES (1,'POS-20261014-1001',4,'pos','Pick-up','Selesai',95000.00,'Tunai',0.00,'Pelanggan Langsung (Apotek)','081299887766','ZenCare Toko Fisik (Muharto)','KOTA MALANG',NULL,NULL,NULL,NULL,'2026-10-14 10:15:20',NULL,'2026-10-14 10:15:20','2026-10-14 10:15:20'),(2,'POS-20261016-1002',4,'pos','Pick-up','Selesai',17500.00,'QRIS',0.00,'Pelanggan Langsung (Apotek)','081344556677','ZenCare Toko Fisik (Muharto)','KOTA MALANG',NULL,NULL,NULL,NULL,'2026-10-16 14:22:10',NULL,'2026-10-16 14:22:10','2026-10-16 14:22:10'),(3,'POS-20261017-1003',4,'pos','Pick-up','Selesai',940000.00,'Tunai',0.00,'Klinik Sehat Bersama (Grosir & Eceran)','081122334455','ZenCare Toko Fisik (Muharto)','KOTA MALANG',NULL,NULL,NULL,NULL,'2026-10-17 11:40:05',NULL,'2026-10-17 11:40:05','2026-10-17 11:40:05'),(4,'INV-20261018-041',3,'ecommerce','Kurir','Selesai',188000.00,'Midtrans Payment',18000.00,'Budi Santoso','083333444555','Jl. Soekarno Hatta No. 12, Lowokwaru','KOTA MALANG','jne','REG','snap-token-inv-041-midtrans','bank_transfer','2026-10-18 09:35:00',NULL,'2026-10-18 09:30:00','2026-10-18 11:00:00'),(5,'INV-20261019-052',3,'ecommerce','Kurir','Selesai',945000.00,'Midtrans Payment',45000.00,'Budi Santoso','083333444555','Jl. Soekarno Hatta No. 12, Lowokwaru','KOTA MALANG','sicepat','REG','snap-token-inv-052-midtrans','qris','2026-10-19 13:25:00',NULL,'2026-10-19 13:20:00','2026-10-19 15:40:00'),(6,'POS-20261012-0901',4,'pos','Pick-up','Selesai',850000.00,'Tunai',0.00,'dr. Hendra Setiawan','081234567890','Klinik Pratama Medika','KOTA MALANG',NULL,NULL,NULL,NULL,'2026-10-12 16:10:00',NULL,'2026-10-12 16:10:00','2026-10-12 16:10:00'),(13,'WEB-20261028-544',3,'ecommerce','Kurir','Dibatalkan',143000.00,'Tunai',95000.00,'Budi Santoso','083333444555','Jl. Soekarno Hatta No.12, Malang','BATU','jne','JTR','348d8f32-3f25-4980-afa7-83d98fde4e78','qris',NULL,NULL,'2026-10-28 11:38:54','2026-10-28 14:26:19'),(14,'WEB-20261029-191',3,'ecommerce','Kurir','Selesai',143000.00,'Tunai',95000.00,'Budi Santoso','083333444555','Jl. Soekarno Hatta No.12, Malang','BATU','jne','JTR','6dd65636-3b96-4135-8bfc-97b29b5e338c','qris','2026-10-29 04:30:30',NULL,'2026-10-29 04:29:39','2026-10-29 04:32:19'),(15,'WEB-20261029-294',3,'ecommerce','Kurir','Diproses',1714000.00,'Tunai',14000.00,'Budi Santoso','083333444555','Jl. Soekarno Hatta No.12, Malang','BATU','jne','REG','61158892-fe96-4b07-b5df-69b59b4d3007','bank_transfer','2026-10-29 04:33:10',NULL,'2026-10-29 04:32:54','2026-10-29 04:33:10'),(21,'POS-20261029-6744',4,'pos',NULL,'Selesai',904000.00,'QRIS',0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-29 06:52:03','2026-10-29 06:52:03'),(22,'POS-20261029-6112',4,'pos',NULL,'Selesai',36000.00,'Tunai',0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-29 06:52:33','2026-10-29 06:52:33'),(32,'WEB-20261029-308',3,'ecommerce','Kurir','Dibatalkan',60000.00,'Tunai',12000.00,'Budi Santoso','083333444555','Jl. Soekarno Hatta No.12, Malang','KOTA BATU','jne','REG','62af6b3c-d70a-47a1-aeca-ec9173574183','qris',NULL,NULL,'2026-10-29 10:17:02','2026-10-29 13:57:52');
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan_batch_alokasi`
--

DROP TABLE IF EXISTS `pesanan_batch_alokasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesanan_batch_alokasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `id_detail_penjualan` int(11) NOT NULL,
  `id_batch` int(11) NOT NULL,
  `qty_satuan_kecil` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_detail_penjualan` (`id_detail_penjualan`),
  KEY `idx_pba_batch` (`id_batch`),
  KEY `idx_pba_penjualan` (`id_penjualan`),
  CONSTRAINT `pesanan_batch_alokasi_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pesanan_batch_alokasi_ibfk_2` FOREIGN KEY (`id_detail_penjualan`) REFERENCES `detail_penjualan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pesanan_batch_alokasi_ibfk_3` FOREIGN KEY (`id_batch`) REFERENCES `stok_batch` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan_batch_alokasi`
--

LOCK TABLES `pesanan_batch_alokasi` WRITE;
/*!40000 ALTER TABLE `pesanan_batch_alokasi` DISABLE KEYS */;
INSERT INTO `pesanan_batch_alokasi` (`id`, `id_penjualan`, `id_detail_penjualan`, `id_batch`, `qty_satuan_kecil`, `created_at`) VALUES (5,14,29,7,10,'2026-10-29 04:29:39'),(6,15,30,17,100,'2026-10-29 04:32:54'),(15,21,42,82,2,'2026-10-29 06:52:03'),(16,21,43,13,1,'2026-10-29 06:52:03'),(17,21,44,17,50,'2026-10-29 06:52:03'),(18,22,45,80,2,'2026-10-29 06:52:33');
/*!40000 ALTER TABLE `pesanan_batch_alokasi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_induk`
--

LOCK TABLES `produk_induk` WRITE;
/*!40000 ALTER TABLE `produk_induk` DISABLE KEYS */;
INSERT INTO `produk_induk` (`id`, `sku_induk`, `nama_produk`, `deskripsi`, `spesifikasi`, `info_pengiriman`, `kategori`, `gambar`, `is_active`, `batas_hari_expired`, `created_at`) VALUES (1,'IND-D4853B','Amoxicillin 500mg','Antibiotik Kapsul (Wajib Resep)',NULL,NULL,'Obat','assets/img/produk/var_1.jpg',1,90,'2026-10-07 04:39:14'),(2,'IND-B42BD2','Sanmol Sirup Anak 60ml','Obat Demam Anak Rasa Stroberi',NULL,NULL,'Obat','assets/img/produk/var_2.jpg',1,180,'2026-10-07 04:39:14'),(3,'IND-B35E3A','Strip Gula Darah EasyTouch','BMHP Gula Darah - Isi 25',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_3.jpg',1,90,'2026-10-07 04:39:14'),(4,'IND-4422CC','Tensimeter Digital Omron','Alat Ukur Tekanan Darah Lengan',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_4.jpg',1,0,'2026-10-07 04:39:14'),(5,'IND-BE2154','Termometer Digital Omron','Termometer Badan Digital MC-246',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_5.jpg',1,0,'2026-10-07 04:39:14'),(6,'IND-9929D6','Hufagrip','Obat Flu Anak',NULL,NULL,'Obat','assets/img/produk/var_6.jpg',1,180,'2026-10-07 04:39:14'),(7,'IND-5EE388','Paracetamol','Pereda Nyeri / Penurun Panas',NULL,NULL,'Obat','assets/img/produk/var_10.jpg',1,90,'2026-10-07 04:39:14'),(8,'IND-E859E3','Promag Tablet','Obat Sakit Maag & Kembung',NULL,NULL,'Obat','assets/img/produk/var_13.jpg',1,90,'2026-10-07 04:39:14'),(9,'IND-9D679C','Panadol Extra','Pereda Sakit Kepala Membandel',NULL,NULL,'Obat','assets/img/produk/var_14.jpg',1,90,'2026-10-07 04:39:14'),(10,'IND-CBA5E0','Tolak Angin Cair','Obat Masuk Angin',NULL,NULL,'Obat','assets/img/produk/var_15.jpg',1,90,'2026-10-07 04:39:14'),(11,'IND-80EAC6','Mylanta Cair 150ml','Obat Maag Cair',NULL,NULL,'Obat','assets/img/produk/var_16.jpg',1,90,'2026-10-07 04:39:14'),(12,'IND-38F7DB','Insto Reguler 7.5ml','Tetes Mata Merah',NULL,NULL,'Obat','assets/img/produk/var_17.jpg',1,90,'2026-10-07 04:39:14'),(13,'IND-70AB72','Betadine Solution 15ml','Antiseptik Luka',NULL,NULL,'Obat','assets/img/produk/var_18.jpg',1,90,'2026-10-07 04:39:14'),(14,'IND-ABF94E','Counterpain Krim 30g','Krim Pereda Nyeri Otot',NULL,NULL,'Obat','assets/img/produk/var_19.jpg',1,90,'2026-10-07 04:39:14'),(15,'IND-E8639A','Neurobion Forte','Vitamin B Kompleks',NULL,NULL,'Obat','assets/img/produk/var_20.jpg',1,90,'2026-10-07 04:39:14'),(16,'IND-E26D93','Sangobion Kapsul','Suplemen Zat Besi',NULL,NULL,'Obat','assets/img/produk/var_21.jpg',1,90,'2026-10-07 04:39:14'),(17,'IND-7F92A0','Diapet Kapsul','Obat Diare',NULL,NULL,'Obat','assets/img/produk/var_22.jpg',1,90,'2026-10-07 04:39:14'),(18,'IND-D23520','Entrostop','Obat Diare Dewasa',NULL,NULL,'Obat','assets/img/produk/var_23.jpg',1,90,'2026-10-07 04:39:14'),(19,'IND-FCA962','Woods Peppermint Syr','Obat Batuk Antitusif',NULL,NULL,'Obat','assets/img/produk/var_24.jpg',1,90,'2026-10-07 04:39:14'),(20,'IND-681C6D','Komix Herbal','Sirup Obat Batuk Tube',NULL,NULL,'Obat','assets/img/produk/var_25.jpg',1,90,'2026-10-07 04:39:14'),(21,'IND-E6B0E1','Antangin JRG','Obat Herbal Masuk Angin',NULL,NULL,'Obat','assets/img/produk/var_26.jpg',1,90,'2026-10-07 04:39:14'),(22,'IND-429941','CDR Effervescent','Vitamin C & Kalsium',NULL,NULL,'Obat','assets/img/produk/var_27.jpg',1,90,'2026-10-07 04:39:14'),(23,'IND-619525','Redoxon Double Action','Multivitamin C & Zinc',NULL,NULL,'Obat','assets/img/produk/var_28.jpg',1,90,'2026-10-07 04:39:14'),(24,'IND-F85AB5','Enervon-C Multivitamin','Vitamin C & B Kompleks',NULL,NULL,'Obat','assets/img/produk/var_29.jpg',1,90,'2026-10-07 04:39:14'),(25,'IND-72ECA5','Imboost Force','Suplemen Daya Tahan Tubuh',NULL,NULL,'Obat','assets/img/produk/var_30.jpg',1,90,'2026-10-07 04:39:14'),(26,'IND-95AB80','Bodrex Migra','Obat Sakit Kepala Migrain',NULL,NULL,'Obat','assets/img/produk/var_31.jpg',1,90,'2026-10-07 04:39:14'),(27,'IND-EDAB79','Neozep Forte','Obat Flu & Pilek',NULL,NULL,'Obat','assets/img/produk/var_32.jpg',1,90,'2026-10-07 04:39:14'),(28,'IND-CDE41C','Decolgen','Obat Flu Ringan',NULL,NULL,'Obat','assets/img/produk/var_33.jpg',1,90,'2026-10-07 04:39:14'),(29,'IND-6C721E','OBH Combi Plus','Sirup Batuk & Flu',NULL,NULL,'Obat','assets/img/produk/var_34.jpg',1,90,'2026-10-07 04:39:14'),(30,'IND-2EDE6B','Salonpas Koyo','Koyo Pereda Nyeri',NULL,NULL,'Obat','assets/img/produk/var_35.jpg',1,90,'2026-10-07 04:39:14'),(31,'IND-516FBF','Vicks VapoRub 10g','Balsem Pelega Tenggorokan',NULL,NULL,'Obat','assets/img/produk/var_36.jpg',1,90,'2026-10-07 04:39:14'),(32,'IND-75F1A9','Tolak Linu','Obat Pegal Linu Cair',NULL,NULL,'Obat','assets/img/produk/var_37.jpg',1,90,'2026-10-07 04:39:14'),(33,'IND-B79B70','Decadryl Expectorant','Obat Batuk Berdahak',NULL,NULL,'Obat','assets/img/produk/var_38.jpg',1,90,'2026-10-07 04:39:14'),(34,'IND-1E6A32','Polysilane Kapsul','Obat Asam Lambung',NULL,NULL,'Obat','assets/img/produk/var_39.jpg',1,90,'2026-10-07 04:39:14'),(35,'IND-A35F8F','Combantrin Jeruk 10ml','Obat Cacing Sirup Anak',NULL,NULL,'Obat','assets/img/produk/var_40.jpg',1,90,'2026-10-07 04:39:14'),(36,'IND-2A3D66','Cefadroxil 500mg','Antibiotik (Resep Dokter)',NULL,NULL,'Obat','assets/img/produk/var_41.jpg',1,90,'2026-10-07 04:39:14'),(37,'IND-9D0FFA','CTM','Obat Alergi Ringan',NULL,NULL,'Obat','assets/img/produk/var_42.jpg',1,90,'2026-10-07 04:39:14'),(38,'IND-0DA012','Cetirizine 10mg','Obat Alergi / Antihistamin',NULL,NULL,'Obat','assets/img/produk/var_43.jpg',1,90,'2026-10-07 04:39:14'),(39,'IND-3EFAA1','Masker Sensi Earloop','Masker Medis 3 Ply (Isi 50)',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_44.jpg',1,90,'2026-10-07 04:39:14'),(40,'IND-04864C','Hansaplast Kain Elastis','Plester Luka',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_45.jpg',1,90,'2026-10-07 04:39:14'),(41,'IND-25FD84','Plester Dermafix','Plester Anti Air Transparan',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_46.jpg',1,90,'2026-10-07 04:39:14'),(42,'IND-195AD9','Kasa Steril Onemed','Kasa Luka Steril 16x16',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_47.jpg',1,90,'2026-10-07 04:39:14'),(43,'IND-DDF14F','Alkohol Onemed 70% 100ml','Cairan Antiseptik',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_48.jpg',1,90,'2026-10-07 04:39:14'),(44,'IND-6F0C4A','Rivanol 100ml','Cairan Pembersih Luka',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_49.jpg',1,90,'2026-10-07 04:39:14'),(45,'IND-6C597D','Povidone Iodine 1 Liter','Betadine Literan RS',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_50.jpg',1,90,'2026-10-07 04:39:14'),(46,'IND-9EBAEB','Alat Cek Asam Urat EasyTouch','BMHP Asam Urat - Isi 25',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_51.jpg',1,90,'2026-10-07 04:39:14'),(47,'IND-64BBB0','Alat Cek Kolesterol Nesco','BMHP Kolesterol - Isi 10',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_52.jpg',1,90,'2026-10-07 04:39:14'),(48,'IND-FC6027','Kursi Roda Standard Sella','Kursi Roda Lipat Ringan',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_53.jpg',1,0,'2026-10-07 04:39:14'),(49,'IND-CE2685','Tabung Oksigen 1m3','Tabung O2 Medis Kosong',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_54.jpg',1,0,'2026-10-07 04:39:14'),(50,'IND-26584F','Regulator Oksigen','Regulator Medis O2',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_55.jpg',1,0,'2026-10-07 04:39:14'),(51,'IND-A7E800','Nebulizer Omron','Alat Terapi Uap Asma NE-C104',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_56.jpg',1,0,'2026-10-07 04:39:14'),(52,'IND-3DB28F','Termometer Infra Merah','Thermometer Tembak Dahi',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_57.jpg',1,0,'2026-10-07 04:39:14'),(53,'IND-D92462','Stetoskop Littmann','Stethoscope Classic III Medis',NULL,NULL,'Alat Kesehatan','assets/img/produk/var_58.jpg',1,0,'2026-10-07 04:39:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_variasi`
--

LOCK TABLES `produk_variasi` WRITE;
/*!40000 ALTER TABLE `produk_variasi` DISABLE KEYS */;
INSERT INTO `produk_variasi` (`id`, `id_produk_induk`, `sku_variasi`, `nama_variasi`, `satuan_kecil`, `satuan_besar`, `rasio_konversi`, `harga_jual_kecil`, `harga_jual_besar`, `stok_minimum_kecil`, `stok_minimum_besar`, `berat`, `tampil_di_online`, `is_active`, `gambar`) VALUES (1,1,'AMX-500','Tablet 500mg','Strip','Box',10,5000.00,48000.00,10,2,100,1,1,'assets/img/produk/var_1_1.jpg,assets/img/produk/var_1_2.jpg,assets/img/produk/var_1_3.jpg,assets/img/produk/var_1_4.jpg'),(2,2,'SAN-SYR','Botol 60ml','Botol','Karton',24,15000.00,350000.00,5,2,100,1,1,'assets/img/produk/var_2_1.jpg,assets/img/produk/var_2_2.jpg,assets/img/produk/var_2_3.jpg,assets/img/produk/var_2_4.jpg'),(3,3,'ESY-GLU','Tube (25 Pcs)','Tube','Tube',1,85000.00,800000.00,NULL,5,100,1,1,'assets/img/produk/var_3_1.jpg,assets/img/produk/var_3_2.jpg,assets/img/produk/var_3_3.jpg,assets/img/produk/var_3_4.jpg'),(4,4,'OMR-TENS','Unit HEM-7120','Unit','Unit',1,550000.00,2600000.00,5,5,100,1,1,'assets/img/produk/var_4_1.jpg,assets/img/produk/var_4_2.jpg,assets/img/produk/var_4_3.jpg,assets/img/produk/var_4_4.jpg'),(5,5,'OMR-THER','Unit MC-246','Unit','Unit',1,85000.00,800000.00,5,5,100,1,1,'assets/img/produk/var_5_1.jpg,assets/img/produk/var_5_2.jpg,assets/img/produk/var_5_3.jpg,assets/img/produk/var_5_4.jpg'),(6,6,'HUF-TMP','Hufagrip Merah (TMP)','Botol','Karton',50,18000.00,850000.00,5,2,100,1,1,'assets/img/produk/var_6_1.jpg,assets/img/produk/var_6_2.jpg,assets/img/produk/var_6_3.jpg,assets/img/produk/var_6_4.jpg'),(7,6,'HUF-PIL','Hufagrip Biru (Pilek)','Botol','Karton',50,18000.00,850000.00,NULL,2,100,1,1,'assets/img/produk/var_7_1.jpg,assets/img/produk/var_7_2.jpg,assets/img/produk/var_7_3.jpg,assets/img/produk/var_7_4.jpg'),(8,6,'HUF-BP','Hufagrip Hijau (BP)','Botol','Karton',50,18000.00,850000.00,NULL,2,100,1,1,'assets/img/produk/var_8_1.jpg,assets/img/produk/var_8_2.jpg,assets/img/produk/var_8_3.jpg,assets/img/produk/var_8_4.jpg'),(9,6,'HUF-FLU','Hufagrip Kuning (Flu Batuk)','Botol','Karton',50,18000.00,850000.00,NULL,2,100,1,1,'assets/img/produk/var_9_1.jpg,assets/img/produk/var_9_2.jpg,assets/img/produk/var_9_3.jpg,assets/img/produk/var_9_4.jpg'),(10,7,'PCT-500','Tablet 500mg','Strip','Box',10,3500.00,30000.00,5,2,100,1,1,'assets/img/produk/var_10_1.jpg,assets/img/produk/var_10_2.jpg,assets/img/produk/var_10_3.jpg,assets/img/produk/var_10_4.jpg'),(11,7,'PCT-250','Tablet 250mg','Strip','Box',10,2500.00,22000.00,NULL,2,100,1,1,'assets/img/produk/var_11_1.jpg,assets/img/produk/var_11_2.jpg,assets/img/produk/var_11_3.jpg,assets/img/produk/var_11_4.jpg'),(12,7,'PCT-125','Tablet 125mg','Strip','Box',10,2000.00,18000.00,NULL,2,100,1,1,'assets/img/produk/var_12_1.jpg,assets/img/produk/var_12_2.jpg,assets/img/produk/var_12_3.jpg,assets/img/produk/var_12_4.jpg'),(13,8,'PRM-01','Tablet Kunyah','Strip','Box',12,9500.00,110000.00,NULL,2,100,1,1,'assets/img/produk/var_13_1.jpg,assets/img/produk/var_13_2.jpg,assets/img/produk/var_13_3.jpg,assets/img/produk/var_13_4.jpg'),(14,9,'PND-EXT','Kaplet Merah','Strip','Box',10,12500.00,120000.00,NULL,2,100,1,1,'assets/img/produk/var_14_1.jpg,assets/img/produk/var_14_2.jpg,assets/img/produk/var_14_3.jpg,assets/img/produk/var_14_4.jpg'),(15,10,'TLK-ANG','Sachet 15ml','Sachet','Box',12,4500.00,52000.00,NULL,2,100,1,1,'assets/img/produk/var_15_1.jpg,assets/img/produk/var_15_2.jpg,assets/img/produk/var_15_3.jpg,assets/img/produk/var_15_4.jpg'),(16,11,'MYL-150','Botol 150ml','Botol','Karton',24,45000.00,1050000.00,NULL,2,100,1,1,'assets/img/produk/var_16_1.jpg,assets/img/produk/var_16_2.jpg,assets/img/produk/var_16_3.jpg,assets/img/produk/var_16_4.jpg'),(17,12,'INS-REG','Botol 7.5ml','Botol','Box',12,16000.00,185000.00,NULL,2,100,1,1,'assets/img/produk/var_17_1.jpg,assets/img/produk/var_17_2.jpg,assets/img/produk/var_17_3.jpg,assets/img/produk/var_17_4.jpg'),(18,13,'BET-15','Botol 15ml','Botol','Box',12,12500.00,145000.00,NULL,2,100,1,1,'assets/img/produk/var_18_1.jpg,assets/img/produk/var_18_2.jpg,assets/img/produk/var_18_3.jpg,assets/img/produk/var_18_4.jpg'),(19,14,'CTP-30','Tube 30g','Tube','Box',10,55000.00,530000.00,NULL,2,100,1,1,'assets/img/produk/var_19_1.jpg,assets/img/produk/var_19_2.jpg,assets/img/produk/var_19_3.jpg,assets/img/produk/var_19_4.jpg'),(20,15,'NEU-FRT','Tablet Merah','Strip','Box',10,48000.00,460000.00,NULL,2,100,1,1,'assets/img/produk/var_20_1.jpg,assets/img/produk/var_20_2.jpg,assets/img/produk/var_20_3.jpg,assets/img/produk/var_20_4.jpg'),(21,16,'SNG-CAP','Kapsul Merah','Strip','Box',25,18500.00,450000.00,NULL,2,100,1,1,'assets/img/produk/var_21_1.jpg,assets/img/produk/var_21_2.jpg,assets/img/produk/var_21_3.jpg,assets/img/produk/var_21_4.jpg'),(22,17,'DPT-CAP','Kapsul Isi 10','Strip','Box',25,4500.00,105000.00,NULL,2,100,1,1,'assets/img/produk/var_22_1.jpg,assets/img/produk/var_22_2.jpg,assets/img/produk/var_22_3.jpg,assets/img/produk/var_22_4.jpg'),(23,18,'ENT-DSW','Tablet','Strip','Box',20,9500.00,180000.00,NULL,2,100,1,1,'assets/img/produk/var_23_1.jpg,assets/img/produk/var_23_2.jpg,assets/img/produk/var_23_3.jpg,assets/img/produk/var_23_4.jpg'),(24,19,'WOD-PEP','Botol 100ml','Botol','Karton',24,38000.00,890000.00,NULL,2,100,1,1,'assets/img/produk/var_24_1.jpg,assets/img/produk/var_24_2.jpg,assets/img/produk/var_24_3.jpg,assets/img/produk/var_24_4.jpg'),(25,20,'KMX-HRB','Sachet 15ml','Sachet','Box',30,2500.00,70000.00,NULL,2,100,1,1,'assets/img/produk/var_25_1.jpg,assets/img/produk/var_25_2.jpg,assets/img/produk/var_25_3.jpg,assets/img/produk/var_25_4.jpg'),(26,21,'ANT-JRG','Sachet 15ml','Sachet','Box',12,4000.00,46000.00,NULL,2,100,1,1,'assets/img/produk/var_26_1.jpg,assets/img/produk/var_26_2.jpg,assets/img/produk/var_26_3.jpg,assets/img/produk/var_26_4.jpg'),(27,22,'CDR-EFT','Tube Isi 15','Tube','Karton',10,65000.00,630000.00,NULL,2,100,1,1,'assets/img/produk/var_27_1.jpg,assets/img/produk/var_27_2.jpg,assets/img/produk/var_27_3.jpg,assets/img/produk/var_27_4.jpg'),(28,23,'RDX-DBL','Tube Isi 10','Tube','Karton',10,55000.00,530000.00,NULL,2,100,1,1,'assets/img/produk/var_28_1.jpg,assets/img/produk/var_28_2.jpg,assets/img/produk/var_28_3.jpg,assets/img/produk/var_28_4.jpg'),(29,24,'ENV-C','Strip Isi 4','Strip','Box',25,6000.00,145000.00,NULL,2,100,1,1,'assets/img/produk/var_29_1.jpg,assets/img/produk/var_29_2.jpg,assets/img/produk/var_29_3.jpg,assets/img/produk/var_29_4.jpg'),(30,25,'IMB-FRC','Strip Isi 10','Strip','Box',3,85000.00,245000.00,NULL,2,100,1,1,'assets/img/produk/var_30_1.jpg,assets/img/produk/var_30_2.jpg,assets/img/produk/var_30_3.jpg,assets/img/produk/var_30_4.jpg'),(31,26,'BDR-MGR','Strip Isi 4','Strip','Box',25,3500.00,85000.00,NULL,2,100,1,1,'assets/img/produk/var_31_1.jpg,assets/img/produk/var_31_2.jpg,assets/img/produk/var_31_3.jpg,assets/img/produk/var_31_4.jpg'),(32,27,'NZP-FRT','Strip Isi 4','Strip','Box',25,3500.00,85000.00,NULL,2,100,1,1,'assets/img/produk/var_32_1.jpg,assets/img/produk/var_32_2.jpg,assets/img/produk/var_32_3.jpg,assets/img/produk/var_32_4.jpg'),(33,28,'DCL-GEN','Strip Isi 4','Strip','Box',25,3000.00,72000.00,NULL,2,100,1,1,'assets/img/produk/var_33_1.jpg,assets/img/produk/var_33_2.jpg,assets/img/produk/var_33_3.jpg,assets/img/produk/var_33_4.jpg'),(34,29,'OBH-CPL','Botol 100ml','Botol','Karton',24,25000.00,580000.00,NULL,2,100,1,1,'assets/img/produk/var_34_1.jpg,assets/img/produk/var_34_2.jpg,assets/img/produk/var_34_3.jpg,assets/img/produk/var_34_4.jpg'),(35,30,'SLN-PAS','Sachet Isi 10','Sachet','Box',40,8500.00,320000.00,NULL,2,100,1,1,'assets/img/produk/var_35_1.jpg,assets/img/produk/var_35_2.jpg,assets/img/produk/var_35_3.jpg,assets/img/produk/var_35_4.jpg'),(36,31,'VK-VP10','Pot 10g','Pot','Box',24,12000.00,275000.00,NULL,2,100,1,1,'assets/img/produk/var_36_1.jpg,assets/img/produk/var_36_2.jpg,assets/img/produk/var_36_3.jpg,assets/img/produk/var_36_4.jpg'),(37,32,'TLK-LNU','Sachet 15ml','Sachet','Box',12,4500.00,52000.00,NULL,2,100,1,1,'assets/img/produk/var_37_1.jpg,assets/img/produk/var_37_2.jpg,assets/img/produk/var_37_3.jpg,assets/img/produk/var_37_4.jpg'),(38,33,'DCD-EXP','Botol 120ml','Botol','Karton',24,22000.00,510000.00,NULL,2,100,1,1,'assets/img/produk/var_38_1.jpg,assets/img/produk/var_38_2.jpg,assets/img/produk/var_38_3.jpg,assets/img/produk/var_38_4.jpg'),(39,34,'PLS-CAP','Strip Isi 10','Strip','Box',10,12000.00,115000.00,NULL,2,100,1,1,'assets/img/produk/var_39_1.jpg,assets/img/produk/var_39_2.jpg,assets/img/produk/var_39_3.jpg,assets/img/produk/var_39_4.jpg'),(40,35,'CMB-JRK','Botol 10ml','Botol','Box',12,21000.00,245000.00,NULL,2,100,1,1,'assets/img/produk/var_40_1.jpg,assets/img/produk/var_40_2.jpg,assets/img/produk/var_40_3.jpg,assets/img/produk/var_40_4.jpg'),(41,36,'CFD-500','Strip Isi 10','Strip','Box',10,18000.00,170000.00,NULL,2,100,1,1,'assets/img/produk/var_41_1.jpg,assets/img/produk/var_41_2.jpg,assets/img/produk/var_41_3.jpg,assets/img/produk/var_41_4.jpg'),(42,37,'CTM-ALR','Strip Isi 12','Strip','Box',10,2500.00,23000.00,NULL,2,100,1,1,'assets/img/produk/var_42_1.jpg,assets/img/produk/var_42_2.jpg,assets/img/produk/var_42_3.jpg,assets/img/produk/var_42_4.jpg'),(43,38,'CTR-10','Strip Isi 10','Strip','Box',10,8500.00,80000.00,NULL,2,100,1,1,'assets/img/produk/var_43_1.jpg,assets/img/produk/var_43_2.jpg,assets/img/produk/var_43_3.jpg,assets/img/produk/var_43_4.jpg'),(44,39,'SNS-MSK','Box Isi 50','Box','Box',1,25000.00,950000.00,NULL,5,100,1,1,'assets/img/produk/var_44_1.jpg,assets/img/produk/var_44_2.jpg,assets/img/produk/var_44_3.jpg,assets/img/produk/var_44_4.jpg'),(45,40,'HNS-PLS','Box Isi 100','Lembar','Lembar',1,800.00,75000.00,NULL,5,100,1,1,'assets/img/produk/var_45_1.jpg,assets/img/produk/var_45_2.jpg,assets/img/produk/var_45_3.jpg,assets/img/produk/var_45_4.jpg'),(46,41,'DRM-FIX','Pcs 5x7cm','Pcs','Pcs',1,4500.00,105000.00,NULL,5,100,1,1,'assets/img/produk/var_46_1.jpg,assets/img/produk/var_46_2.jpg,assets/img/produk/var_46_3.jpg,assets/img/produk/var_46_4.jpg'),(47,42,'OM-KSA','Kotak Isi 10','Kotak','Kotak',1,12000.00,580000.00,NULL,5,100,1,1,'assets/img/produk/var_47_1.jpg,assets/img/produk/var_47_2.jpg,assets/img/produk/var_47_3.jpg,assets/img/produk/var_47_4.jpg'),(48,43,'OM-ALK','Botol 100ml','Botol','Botol',1,8500.00,195000.00,NULL,5,100,1,1,'assets/img/produk/var_48_1.jpg,assets/img/produk/var_48_2.jpg,assets/img/produk/var_48_3.jpg,assets/img/produk/var_48_4.jpg'),(49,44,'RVN-100','Botol 100ml','Botol','Botol',1,7500.00,175000.00,NULL,5,100,1,1,'assets/img/produk/var_49_1.jpg,assets/img/produk/var_49_2.jpg,assets/img/produk/var_49_3.jpg,assets/img/produk/var_49_4.jpg'),(50,45,'PVD-1L','Botol 1L','Botol','Botol',1,125000.00,1200000.00,NULL,5,100,1,1,'assets/img/produk/var_50_1.jpg,assets/img/produk/var_50_2.jpg,assets/img/produk/var_50_3.jpg,assets/img/produk/var_50_4.jpg'),(51,46,'ESY-UA','Tube Isi 25','Tube','Tube',1,95000.00,900000.00,NULL,5,100,1,1,'assets/img/produk/var_51_1.jpg,assets/img/produk/var_51_2.jpg,assets/img/produk/var_51_3.jpg,assets/img/produk/var_51_4.jpg'),(52,47,'NSC-CHL','Tube Isi 10','Tube','Tube',1,135000.00,1300000.00,NULL,5,100,1,1,'assets/img/produk/var_52_1.jpg,assets/img/produk/var_52_2.jpg,assets/img/produk/var_52_3.jpg,assets/img/produk/var_52_4.jpg'),(53,48,'SLL-KRD','Unit Standar','Unit','Unit',1,1450000.00,1400000.00,5,5,100,1,1,'assets/img/produk/var_53_1.jpg,assets/img/produk/var_53_2.jpg,assets/img/produk/var_53_3.jpg,assets/img/produk/var_53_4.jpg'),(54,49,'O2-1M3','Tabung 1m3','Tabung','Tabung',1,750000.00,720000.00,5,5,100,1,1,'assets/img/produk/var_54_1.jpg,assets/img/produk/var_54_2.jpg,assets/img/produk/var_54_3.jpg,assets/img/produk/var_54_4.jpg'),(55,50,'O2-REG','Unit Flowmeter','Unit','Unit',1,250000.00,2400000.00,5,5,100,1,1,'assets/img/produk/var_55_1.jpg,assets/img/produk/var_55_2.jpg,assets/img/produk/var_55_3.jpg,assets/img/produk/var_55_4.jpg'),(56,51,'OMR-NEB','Unit NE-C104','Unit','Unit',1,850000.00,4100000.00,5,5,100,1,1,'assets/img/produk/var_56_1.jpg,assets/img/produk/var_56_2.jpg,assets/img/produk/var_56_3.jpg,assets/img/produk/var_56_4.jpg'),(57,52,'IR-THER','Unit Pistol','Unit','Unit',1,125000.00,2400000.00,5,5,100,1,1,'assets/img/produk/var_57_1.jpg,assets/img/produk/var_57_2.jpg,assets/img/produk/var_57_3.jpg,assets/img/produk/var_57_4.jpg'),(58,53,'LTM-STC','Unit Classic III','Unit','Unit',1,1850000.00,18000000.00,5,5,100,1,1,'assets/img/produk/var_58_1.jpg,assets/img/produk/var_58_2.jpg,assets/img/produk/var_58_3.jpg,assets/img/produk/var_58_4.jpg'),(60,6,'HUF-BPA','Hufagrip BP Anak','Botol','Karton',50,18000.00,850000.00,NULL,2,100,1,1,'assets/img/produk/var_60_1.jpg,assets/img/produk/var_60_2.jpg,assets/img/produk/var_60_3.jpg,assets/img/produk/var_60_4.jpg'),(61,58,'SIM-5BOX','Karton Isi 10 Box','Box','Karton',10,28000.00,250000.00,0,0,500,1,1,'assets/img/produk/var_61_1.jpg,assets/img/produk/var_61_2.jpg,assets/img/produk/var_61_3.jpg,assets/img/produk/var_61_4.jpg'),(62,59,'8998838110412','Sachet Box isi 12','Sachet','Box',12,0.00,0.00,0,0,100,1,1,NULL),(63,60,'8997021870564','Kemasan Box','Pcs','Box',10,0.00,0.00,0,0,100,1,1,NULL);
/*!40000 ALTER TABLE `produk_variasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_hitung_opname`
--

DROP TABLE IF EXISTS `riwayat_hitung_opname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_hitung_opname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_detail_opname` int(11) NOT NULL,
  `hitung_sebelumnya` int(11) DEFAULT NULL,
  `hitung_baru` int(11) NOT NULL,
  `dihitung_oleh` int(11) NOT NULL,
  `alasan_koreksi` text DEFAULT NULL,
  `waktu_koreksi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_detail_opname` (`id_detail_opname`),
  KEY `dihitung_oleh` (`dihitung_oleh`),
  CONSTRAINT `riwayat_hitung_opname_ibfk_1` FOREIGN KEY (`id_detail_opname`) REFERENCES `detail_opname_batch` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_hitung_opname_ibfk_2` FOREIGN KEY (`dihitung_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_hitung_opname`
--

LOCK TABLES `riwayat_hitung_opname` WRITE;
/*!40000 ALTER TABLE `riwayat_hitung_opname` DISABLE KEYS */;
/*!40000 ALTER TABLE `riwayat_hitung_opname` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesi_opname`
--

DROP TABLE IF EXISTS `sesi_opname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesi_opname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_sesi` varchar(50) NOT NULL,
  `status` enum('Aktif','Selesai','Dibatalkan') NOT NULL DEFAULT 'Aktif',
  `dibekukan_oleh` int(11) NOT NULL,
  `dibekukan_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `dibuka_oleh` int(11) DEFAULT NULL,
  `dibuka_pada` timestamp NULL DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_sesi` (`kode_sesi`),
  KEY `dibekukan_oleh` (`dibekukan_oleh`),
  KEY `dibuka_oleh` (`dibuka_oleh`),
  CONSTRAINT `sesi_opname_ibfk_1` FOREIGN KEY (`dibekukan_oleh`) REFERENCES `users` (`id`),
  CONSTRAINT `sesi_opname_ibfk_2` FOREIGN KEY (`dibuka_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesi_opname`
--

LOCK TABLES `sesi_opname` WRITE;
/*!40000 ALTER TABLE `sesi_opname` DISABLE KEYS */;
INSERT INTO `sesi_opname` (`id`, `kode_sesi`, `status`, `dibekukan_oleh`, `dibekukan_pada`, `dibuka_oleh`, `dibuka_pada`, `catatan`) VALUES (3,'OPN-20261029-999','Dibatalkan',1,'2026-10-29 13:56:01',1,'2026-11-01 02:43:18','Sesi Stok Opname Berkala | Dibatalkan: ditunda'),(6,'OPN-20261101-938','Dibatalkan',1,'2026-11-01 03:49:05',1,'2026-11-01 03:49:28','Sesi Stok Opname Berkala | Dibatalkan: ada kendala'),(7,'OPN-20261015-001','Selesai',1,'2026-10-15 01:00:00',1,'2026-10-15 04:30:00','Stok Opname Triwulan III - Audit Fisik Berkala Seluruh Obat dan Alkes (Selesai 100% Rekonsiliasi)');
/*!40000 ALTER TABLE `sesi_opname` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_batch`
--

LOCK TABLES `stok_batch` WRITE;
/*!40000 ALTER TABLE `stok_batch` DISABLE KEYS */;
INSERT INTO `stok_batch` (`id`, `id_variasi`, `no_batch`, `tgl_exp`, `stok_sisa`, `created_at`) VALUES (1,1,'AMX-OLD-2025','2025-08-15',0,'2025-08-15 08:00:00'),(2,10,'PCT-OLD-2025','2025-10-01',0,'2025-10-01 08:00:00'),(3,2,'SNM-EXP-TODAY','2026-11-02',120,'2026-08-10 10:00:00'),(4,31,'BDR-EXP-TOMORROW','2026-11-03',100,'2026-08-15 10:00:00'),(5,42,'CTM-EXP-SOON','2026-11-05',80,'2026-08-20 10:00:00'),(6,6,'HUF-EXP-SOON','2026-12-05',500,'2026-08-25 10:00:00'),(7,1,'AMX-2026-01','2027-12-15',40,'2026-10-15 08:30:00'),(8,1,'AMX-2026-01.A','2027-12-15',8,'2026-10-05 09:15:00'),(9,2,'SNM-2026-A','2028-05-10',240,'2026-10-15 08:30:00'),(10,2,'SNM-2026-A.A','2028-05-10',24,'2026-10-07 10:30:00'),(11,3,'BMHP-ESYGLU-28','2028-11-20',15,'2026-09-10 10:00:00'),(12,6,'HUF-TMP-26','2027-08-15',300,'2026-09-10 09:00:00'),(13,6,'HUF-TMP-26.A','2027-08-15',47,'2026-10-09 13:45:00'),(14,6,'HUF-TMP-27','2028-06-20',400,'2026-10-15 08:30:00'),(15,6,'HUF-TMP-27.A','2028-06-20',47,'2026-10-11 15:30:00'),(16,7,'HUFPIL-2027-01','2028-08-15',34950,'2026-09-10 10:00:00'),(17,8,'HUFBP-2027-01','2028-08-15',32450,'2026-09-10 10:00:00'),(18,9,'HUFFLU-2027-01','2028-08-15',37500,'2026-09-10 10:00:00'),(19,10,'PCT-500-26','2027-11-20',230,'2026-10-15 08:30:00'),(20,10,'PCT-500-26.A','2027-11-20',7,'2026-10-08 11:00:00'),(21,10,'PCT-500-26.B','2027-11-20',8,'2026-10-10 14:20:00'),(22,11,'PCT250-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(23,12,'PCT125-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(24,13,'PRM-KLK-26','2027-11-30',348,'2026-10-20 10:00:00'),(25,13,'PRM-KLK-26.A','2027-11-30',12,'2026-10-21 11:00:00'),(26,14,'PND-KLK-26','2027-11-30',250,'2026-10-20 10:00:00'),(27,15,'TLKANG-2027-01','2027-11-30',2160,'2026-09-10 10:00:00'),(28,16,'MYL150-2027-01','2028-08-15',8640,'2026-09-10 10:00:00'),(29,17,'INSREG-2027-01','2027-11-30',2160,'2026-09-10 10:00:00'),(30,18,'BET-15-26','2027-11-30',180,'2026-10-24 15:30:00'),(31,19,'CTP30-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(32,20,'NEUFRT-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(33,21,'SNGCAP-2027-01','2028-08-15',9375,'2026-09-10 10:00:00'),(34,22,'DPT-CAP-26','2027-09-10',500,'2026-10-24 15:30:00'),(35,23,'ENTDSW-2027-01','2028-08-15',6000,'2026-09-10 10:00:00'),(36,24,'WOD-PEP-26','2028-04-10',192,'2026-10-22 11:20:00'),(37,24,'WOD-GNT-26','2028-05-20',72,'2026-10-25 11:15:00'),(38,24,'WOD-PEP-26.A','2028-04-10',24,'2026-10-23 16:30:00'),(39,25,'KMXHRB-2027-01','2028-08-15',13500,'2026-09-10 10:00:00'),(40,26,'ANTJRG-2027-01','2027-11-30',2148,'2026-09-10 10:00:00'),(41,27,'CDR-EFT-26','2027-11-30',100,'2026-10-22 11:20:00'),(42,28,'RDXDBL-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(43,29,'ENV-C-26','2028-03-15',375,'2026-10-22 11:20:00'),(44,30,'IMB-FRC-26','2027-09-15',174,'2026-10-15 08:30:00'),(45,31,'BDR-2028','2028-03-12',6250,'2026-09-10 10:00:00'),(46,32,'NZPFRT-2027-01','2028-08-15',9375,'2026-09-10 10:00:00'),(47,33,'DCL-GEN-26','2027-10-15',200,'2026-10-24 15:30:00'),(48,34,'OBHCPL-2027-01','2028-08-15',8640,'2026-09-10 10:00:00'),(49,35,'SLNPAS-2027-01','2028-08-15',24000,'2026-09-10 10:00:00'),(50,36,'VKVP10-2027-01','2028-08-15',8640,'2026-09-10 10:00:00'),(51,37,'TLKLNU-2027-01','2027-11-30',2160,'2026-09-10 10:00:00'),(52,38,'DCDEXP-2027-01','2028-08-15',8640,'2026-09-10 10:00:00'),(53,39,'PLSCAP-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(54,40,'CMBJRK-2027-01','2027-11-30',2160,'2026-09-10 10:00:00'),(55,41,'CFD-500-26','2027-11-30',80,'2026-10-22 11:20:00'),(56,41,'CFD-GNT-26','2028-02-15',20,'2026-10-25 10:30:00'),(57,42,'CTMALR-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(58,43,'CTR10-2027-01','2027-11-30',1500,'2026-09-10 10:00:00'),(59,44,'BMHP-SNSMSK-28','2028-11-20',65,'2026-09-10 10:00:00'),(60,45,'BMHP-HNSPLS-28','2028-11-20',15,'2026-09-10 10:00:00'),(61,46,'BMHP-DRMFIX-28','2028-11-20',115,'2026-09-10 10:00:00'),(62,47,'BMHP-OMKSA-28','2028-11-20',65,'2026-09-10 10:00:00'),(63,48,'BMHP-OMALK-28','2028-11-20',55,'2026-09-10 10:00:00'),(64,49,'BMHP-RVN100-28','2028-11-20',15,'2026-09-10 10:00:00'),(65,50,'BMHP-PVD1L-28','2028-11-20',15,'2026-09-10 10:00:00'),(66,51,'BMHP-ESYUA-28','2028-11-20',15,'2026-09-10 10:00:00'),(67,52,'BMHP-NSCCHL-28','2028-11-20',15,'2026-09-10 10:00:00'),(68,60,'HUFBPA-2027-01','2028-08-15',37500,'2026-09-10 10:00:00'),(80,7,'HUFPIL-2027-01.A','2028-08-15',48,'2026-10-28 11:40:56'),(81,26,'ANTJRG-2027-01.A','2027-11-30',12,'2026-10-29 06:30:34'),(82,7,'HUFPIL-2027-01.B','2028-08-15',48,'2026-10-29 06:48:22');
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
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_toko`
--

LOCK TABLES `stok_toko` WRITE;
/*!40000 ALTER TABLE `stok_toko` DISABLE KEYS */;
INSERT INTO `stok_toko` (`id`, `id_variasi`, `stok`) VALUES (1,1,138),(2,2,384),(3,3,15),(4,4,8),(5,5,11),(6,6,1294),(7,7,37496),(8,8,37350),(9,9,37500),(10,10,245),(11,11,1500),(12,12,1500),(13,13,360),(14,14,250),(15,15,2160),(16,16,8640),(17,17,2160),(18,18,180),(19,19,1500),(20,20,1500),(21,21,9375),(22,22,500),(23,23,6000),(24,24,288),(25,25,13500),(26,26,2160),(27,27,100),(28,28,1500),(29,29,375),(30,30,174),(31,31,6350),(32,32,9375),(33,33,200),(34,34,8640),(35,35,24000),(36,36,8640),(37,37,2160),(38,38,8640),(39,39,1500),(40,40,2160),(41,41,100),(42,42,1580),(43,43,1500),(44,44,65),(45,45,15),(46,46,115),(47,47,65),(48,48,55),(49,49,15),(50,50,15),(51,51,15),(52,52,15),(53,53,2),(54,54,4),(55,55,3),(56,56,6),(57,57,6),(58,58,3),(59,60,37500),(60,61,0),(61,62,0),(62,63,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` (`id`, `nama`, `kontak`, `email`, `alamat`, `is_active`) VALUES (1,'PT Kimia Farma Trading & Distribution','0341-999001','kf.malang@kimiafarma.co.id','Jl. Farmasi Raya No.1, Malang',1),(2,'PT Omron Healthcare Indonesia','021-555222','info@omron-healthcare.co.id','Gedung Omron, Jakarta Pusat',1),(3,'PT Jayamas Medica Industri (OneMed)','031-444333','sales@onemed.co.id','Kawasan Industri Rungkut, Surabaya',1);
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
  `status` enum('Tersedia','Terjual','Retur/Rusak','Hilang') NOT NULL DEFAULT 'Tersedia',
  `id_penjualan` int(11) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `tgl_habis_garansi` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `unit_serial_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_serial`
--

LOCK TABLES `unit_serial` WRITE;
/*!40000 ALTER TABLE `unit_serial` DISABLE KEYS */;
INSERT INTO `unit_serial` (`id`, `id_variasi`, `serial_number`, `status`, `id_penjualan`, `catatan`, `tgl_habis_garansi`, `created_at`, `updated_at`) VALUES (1,4,'OMR-TENS-01','Terjual',5,NULL,'2028-10-19','2026-09-25 09:15:00','2026-10-19 13:20:00'),(2,4,'OMR-TENS-02','Tersedia',NULL,NULL,'2028-10-19','2026-09-25 09:15:00',NULL),(3,4,'OMR-TENS-03','Tersedia',NULL,NULL,'2028-10-19','2026-09-25 09:15:00',NULL),(4,4,'OMR-TENS-04','Tersedia',NULL,NULL,'2028-10-19','2026-09-25 09:15:00',NULL),(5,4,'OMR-TENS-05','Tersedia',NULL,NULL,'2028-10-19','2026-09-25 09:15:00',NULL),(6,4,'OMR-TENS-06','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(7,4,'OMR-TENS-07','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(8,4,'OMR-TENS-08','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(9,4,'OMR-TENS-09','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(10,4,'OMR-TENS-RET1','Retur/Rusak',NULL,'Cacat Pabrik: Layar LCD garis vertikal saat unboxing (Klaim PO: PO-20261023-ALKES-RETUR)',NULL,'2026-10-23 13:45:00',NULL),(11,4,'OMR-TENS-RET2','Retur/Rusak',NULL,'Cacat Pabrik: Motor pompa cuff bocor angin (Klaim PO: PO-20261023-ALKES-RETUR)',NULL,'2026-10-23 13:45:00',NULL),(12,5,'OMR-TH-01','Terjual',1,NULL,'2027-10-14','2026-09-15 08:30:00','2026-10-14 10:15:20'),(13,5,'OMR-TH-02','Tersedia',NULL,NULL,'2027-10-14','2026-09-15 08:30:00',NULL),(14,5,'OMR-TH-03','Tersedia',NULL,NULL,'2027-10-14','2026-09-15 08:30:00',NULL),(15,5,'OMR-TH-04','Tersedia',NULL,NULL,'2027-10-14','2026-09-15 08:30:00',NULL),(16,5,'OMR-TH-05','Tersedia',NULL,NULL,'2027-10-14','2026-09-15 08:30:00',NULL),(17,5,'OMR-TH-06','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(18,5,'OMR-TH-07','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(19,5,'OMR-TH-08','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(20,5,'OMR-TH-09','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(21,5,'OMR-TH-10','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(22,5,'OMR-TH-11','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(23,5,'OMR-TH-12','Tersedia',NULL,NULL,'2027-10-23','2026-10-23 13:45:00',NULL),(24,5,'OMR-TH-RET1','Retur/Rusak',NULL,'Cacat Pabrik: Error E-01 sensor mati (Klaim PO: PO-20261023-ALKES-RETUR)',NULL,'2026-10-23 13:45:00',NULL),(25,53,'SLL-KRD-01','Tersedia',NULL,NULL,'2028-09-01','2026-09-01 09:00:00',NULL),(26,53,'SLL-KRD-02','Tersedia',NULL,NULL,'2028-09-01','2026-09-01 09:00:00',NULL),(27,54,'O21M3-001','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(28,54,'O21M3-002','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(29,54,'O21M3-003','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(30,54,'O21M3-004','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(31,54,'O21M3-RET1','Retur/Rusak',NULL,'Drat kran valve bocor gas (Klaim Kulakan: KUL-20261024-CAMPUR-RETUR)',NULL,'2026-10-24 15:30:00',NULL),(32,55,'O2-REG-01','Tersedia',NULL,NULL,'2027-09-01','2026-09-01 09:00:00',NULL),(33,55,'O2-REG-02','Tersedia',NULL,NULL,'2027-09-01','2026-09-01 09:00:00',NULL),(34,55,'O2-REG-03','Tersedia',NULL,NULL,'2027-09-01','2026-09-01 09:00:00',NULL),(35,56,'OMR-NEB-99','Retur/Rusak',6,'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi)','2028-10-12','2026-09-20 10:00:00','2026-10-15 11:20:00'),(36,56,'OMR-NEB-01','Tersedia',NULL,NULL,'2028-10-12','2026-09-20 10:00:00',NULL),(37,56,'OMR-NEB-02','Tersedia',NULL,NULL,'2028-10-12','2026-09-20 10:00:00',NULL),(38,56,'OMR-NEB-03','Tersedia',NULL,NULL,'2028-10-12','2026-09-20 10:00:00',NULL),(39,56,'OMR-NEB-04','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(40,56,'OMR-NEB-05','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(41,56,'OMR-NEB-06','Tersedia',NULL,NULL,'2028-10-23','2026-10-23 13:45:00',NULL),(42,57,'IRTHER-001','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(43,57,'IRTHER-002','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(44,57,'IRTHER-003','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(45,57,'IRTHER-004','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(46,57,'IRTHER-005','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(47,57,'IRTHER-006','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(48,58,'LTMSTC-001','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(49,58,'LTMSTC-002','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL),(50,58,'LTMSTC-003','Tersedia',NULL,NULL,'2028-06-30','2026-09-10 10:00:00',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `email`, `telepon`, `alamat`, `kota_id`, `is_active`, `created_at`) VALUES (1,'superadmin','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','SuperAdmin (Pemilik)','superadmin','superadmin@zencare.id','081234567890',NULL,NULL,1,'2026-09-11 04:42:09'),(2,'admin_toko','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','Admin Toko','admin','admin@zencare.id','082111222333',NULL,NULL,1,'2026-09-11 04:42:09'),(3,'pelanggan1','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','Budi Santoso','pelanggan','budi.santoso@gmail.com','083333444555','Jl. Soekarno Hatta No.12, Malang',393,1,'2026-09-11 04:42:09'),(4,'kasir_toko','$2y$10$khJXR9zVmz9x0YqbJKsMf.SVh2fsD75/tWhBfRIRuZ4dfWUrwwWTe','Kasir (Front Office)','kasir','kasir@zencare.id','08222333444',NULL,NULL,1,'2026-09-29 11:16:12');
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

-- Dump completed on 2026-11-01 11:08:46
