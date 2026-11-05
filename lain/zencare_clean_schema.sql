-- =========================================================================
-- CLEAN MYSQL SCHEMA - ZENCARE MEDICAL (20 TABLES)
-- Sistem Informasi Inventori Omnichannel Obat & Alat Kesehatan
-- Siap diimpor langsung ke dbdiagram.io (Import -> From MySQL)
-- =========================================================================

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
);

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
);

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
);

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
);

CREATE TABLE `stok_toko` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_variasi` (`id_variasi`),
  CONSTRAINT `stok_toko_ibfk_1` FOREIGN KEY (`id_variasi`) REFERENCES `produk_variasi` (`id`) ON DELETE CASCADE
);

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
);

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
);

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
);

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
);

CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_invoice` varchar(50) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tipe_transaksi` enum('pos','ecommerce') NOT NULL,
  `metode_pengambilan` enum('Kurir','Pick-up') DEFAULT NULL,
  `status_pesanan` enum('Menunggu Pembayaran','Diproses','Dikirim','Siap Diambil','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `alasan_batal` varchar(255) DEFAULT NULL,
  `dibatalkan_oleh` varchar(50) DEFAULT NULL,
  `waktu_batal` timestamp NULL DEFAULT NULL,
  `request_batal` tinyint(1) NOT NULL DEFAULT 0,
  `request_batal_alasan` varchar(255) DEFAULT NULL,
  `request_batal_waktu` timestamp NULL DEFAULT NULL,
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
);

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
);

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
);

CREATE TABLE `kartu_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_variasi` int(11) NOT NULL,
  `id_batch` int(11) DEFAULT NULL,
  `satuan_tipe` enum('besar','kecil') NOT NULL DEFAULT 'kecil',
  `jenis_mutasi` enum('Masuk','Keluar','Penyesuaian') NOT NULL,
  `kanal` enum('POS','E-Commerce','Manual','Penerimaan','PO') NOT NULL DEFAULT 'Manual',
  `alasan_mutasi` enum('Penerimaan Barang','Penjualan POS','Penjualan E-Commerce','Retur Barang Rusak','Klaim Garansi SN','Selisih Stok Opname','Barang Rusak','Barang Kedaluwarsa','Barang Hilang','Koreksi Salah Catat / Ditemukan','Koreksi Manual') NOT NULL DEFAULT 'Selisih Stok Opname',
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
);

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
);

CREATE TABLE `log_anomali_fefo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_variasi` int(11) NOT NULL,
  `batch_diambil` varchar(100) DEFAULT NULL,
  `batch_seharusnya` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `satuan_tipe` varchar(20) NOT NULL DEFAULT 'kecil',
  `status` enum('terlewat','sebagian','disesuaikan') NOT NULL DEFAULT 'terlewat',
  `qty_disesuaikan` int(11) NOT NULL DEFAULT 0,
  `resolved_in_penjualan` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

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
);

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
  `alasan_mutasi` varchar(255) DEFAULT NULL,
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
);

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
);

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
);

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
);
