<?php
// File: scratch/migrate_step1_schema.php
require_once __DIR__ . '/../config/koneksi.php';

try {
    echo "Starting Step 1 Migration...\n";

    // 1. Tambah kolom id_batch pada kartu_stok jika belum ada
    $checkCol = $pdo->query("SHOW COLUMNS FROM kartu_stok LIKE 'id_batch'")->fetch();
    if (!$checkCol) {
        $pdo->exec("ALTER TABLE kartu_stok ADD COLUMN id_batch INT NULL AFTER id_variasi, ADD INDEX idx_kartu_batch (id_batch)");
        echo "1. Kolom 'id_batch' berhasil ditambahkan ke tabel kartu_stok.\n";
    } else {
        echo "1. Kolom 'id_batch' sudah ada di tabel kartu_stok.\n";
    }

    // 2. Buat tabel pesanan_batch_alokasi
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pesanan_batch_alokasi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_penjualan INT NOT NULL,
            id_detail_penjualan INT NOT NULL,
            id_batch INT NOT NULL,
            qty_satuan_kecil INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_penjualan) REFERENCES penjualan(id) ON DELETE CASCADE,
            FOREIGN KEY (id_detail_penjualan) REFERENCES detail_penjualan(id) ON DELETE CASCADE,
            FOREIGN KEY (id_batch) REFERENCES stok_batch(id) ON DELETE RESTRICT,
            INDEX idx_pba_batch (id_batch),
            INDEX idx_pba_penjualan (id_penjualan)
        ) ENGINE=InnoDB;
    ");
    echo "2. Tabel 'pesanan_batch_alokasi' berhasil dibuat/diverifikasi.\n";

    // 3. Buat tabel sesi_opname
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sesi_opname (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_sesi VARCHAR(50) NOT NULL UNIQUE,
            status ENUM('Aktif', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Aktif',
            dibekukan_oleh INT NOT NULL,
            dibekukan_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            dibuka_oleh INT NULL,
            dibuka_pada TIMESTAMP NULL,
            catatan TEXT NULL,
            FOREIGN KEY (dibekukan_oleh) REFERENCES users(id),
            FOREIGN KEY (dibuka_oleh) REFERENCES users(id)
        ) ENGINE=InnoDB;
    ");
    echo "3. Tabel 'sesi_opname' berhasil dibuat/diverifikasi.\n";

    // 4. Buat tabel detail_opname_batch
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS detail_opname_batch (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_sesi INT NOT NULL,
            id_batch INT NOT NULL,
            id_variasi INT NOT NULL,
            satuan_tipe ENUM('besar', 'kecil') NOT NULL DEFAULT 'kecil',
            nama_satuan VARCHAR(50) NOT NULL DEFAULT 'Unit',
            stok_tersedia_sistem INT NOT NULL,
            stok_reserved INT NOT NULL,
            stok_sistem_fisik INT NOT NULL,
            hitung_fisik INT NULL,
            selisih INT NULL,
            status_mutasi ENUM('Belum Dimutasi', 'Sudah Dimutasi', 'Sesuai') NOT NULL DEFAULT 'Belum Dimutasi',
            alasan_mutasi ENUM('Barang Rusak', 'Barang Kedaluwarsa', 'Barang Hilang', 'Koreksi Salah Catat / Ditemukan', 'Lainnya') NULL,
            keterangan_mutasi TEXT NULL,
            id_kartu_stok INT NULL,
            dihitung_oleh INT NULL,
            waktu_hitung TIMESTAMP NULL,
            dimutasi_oleh INT NULL,
            waktu_mutasi TIMESTAMP NULL,
            FOREIGN KEY (id_sesi) REFERENCES sesi_opname(id) ON DELETE CASCADE,
            FOREIGN KEY (id_batch) REFERENCES stok_batch(id),
            FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id),
            FOREIGN KEY (dihitung_oleh) REFERENCES users(id),
            FOREIGN KEY (dimutasi_oleh) REFERENCES users(id),
            INDEX idx_dob_sesi_batch (id_sesi, id_batch)
        ) ENGINE=InnoDB;
    ");
    echo "4. Tabel 'detail_opname_batch' berhasil dibuat/diverifikasi.\n";

    // 5. Buat tabel riwayat_hitung_opname (untuk pencatatan koreksi/hitung ulang yang transparan)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS riwayat_hitung_opname (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_detail_opname INT NOT NULL,
            hitung_sebelumnya INT NULL,
            hitung_baru INT NOT NULL,
            dihitung_oleh INT NOT NULL,
            alasan_koreksi TEXT NULL,
            waktu_koreksi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_detail_opname) REFERENCES detail_opname_batch(id) ON DELETE CASCADE,
            FOREIGN KEY (dihitung_oleh) REFERENCES users(id)
        ) ENGINE=InnoDB;
    ");
    echo "5. Tabel 'riwayat_hitung_opname' berhasil dibuat/diverifikasi.\n";

    echo "ALL STEP 1 MIGRATIONS COMPLETED SUCCESSFULLY!\n";

} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
