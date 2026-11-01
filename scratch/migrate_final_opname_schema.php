<?php
// File: scratch/migrate_final_opname_schema.php
require_once __DIR__ . '/../config/koneksi.php';

try {
    echo "=== 1. Update ENUM unit_serial.status (menambahkan 'Hilang') ===\n";
    $pdo->exec("ALTER TABLE unit_serial MODIFY COLUMN status ENUM('Tersedia', 'Terjual', 'Retur/Rusak', 'Hilang') NOT NULL DEFAULT 'Tersedia'");
    echo "Success: unit_serial.status updated.\n";

    echo "\n=== 2. Update detail_opname_batch (tambah jenis_komoditas & jadikan id_batch nullable) ===\n";
    // Cek kolom jenis_komoditas
    $chk = $pdo->query("SHOW COLUMNS FROM detail_opname_batch LIKE 'jenis_komoditas'")->fetch();
    if (!$chk) {
        $pdo->exec("ALTER TABLE detail_opname_batch ADD COLUMN jenis_komoditas ENUM('Obat', 'Alkes') NOT NULL DEFAULT 'Obat' AFTER id_variasi");
        echo "Success: kolom jenis_komoditas ditambahkan.\n";
    } else {
        echo "Info: kolom jenis_komoditas sudah ada.\n";
    }
    $pdo->exec("ALTER TABLE detail_opname_batch MODIFY COLUMN id_batch INT NULL");
    echo "Success: id_batch nullable.\n";

    echo "\n=== 3. Buat tabel detail_opname_sn ===\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS detail_opname_sn (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_detail_opname INT NOT NULL,
            id_unit_serial INT NULL,
            serial_number VARCHAR(100) NOT NULL,
            jenis_tindakan ENUM('Kurang_Rusak', 'Kurang_Hilang', 'Tambah_Ditemukan') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_detail_opname) REFERENCES detail_opname_batch(id) ON DELETE CASCADE,
            FOREIGN KEY (id_unit_serial) REFERENCES unit_serial(id) ON DELETE SET NULL,
            INDEX idx_dos_detail (id_detail_opname),
            INDEX idx_dos_sn (serial_number)
        ) ENGINE=InnoDB;
    ");
    echo "Success: tabel detail_opname_sn siap.\n";

    echo "\n=== 4. Verifikasi tabel riwayat_hitung_opname ===\n";
    $chkR = $pdo->query("SHOW TABLES LIKE 'riwayat_hitung_opname'")->fetch();
    if ($chkR) {
        echo "Success: tabel riwayat_hitung_opname sudah ada.\n";
    }

    echo "\n=== SEMUA MIGRASI SKEMA OPNAME SELESAI DENGAN SUKSES! ===\n";
} catch (Exception $e) {
    echo "ERROR MIGRASI: " . $e->getMessage() . "\n";
}

