<?php
// File: setup.php
// Script ini otomatis membuat database, tabel, dan data dummy Zencare Medical (Sistem Omnichannel TA).

require_once __DIR__ . '/config/config.php';

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Buat Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    echo "<h3>1. Database '" . DB_NAME . "' berhasil dibuat/dipastikan ada.</h3>";

    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Drop all old & new tables to guarantee fresh start
    $tables = [
        'riwayat_stok', 'detail_pesanan', 'pesanan', 'produk', // Old ones
        'gudang_karantina', 'kartu_stok', 'stock_opname', 'mutasi_stok', // New history
        'detail_penjualan', 'penjualan', // New transactions
        'pengaturan_api', 'stok_cabang', 'produk_variasi', 'produk_induk', 'supplier', 'cabang', 'users' // New master
    ];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table;");
    }

    echo "<h3>2. Tabel lama berhasil dihapus (Refresh Migration).</h3>";

    // 2. CREATE TABLES
    $sql = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('super_admin', 'kasir', 'pelanggan') NOT NULL,
        id_cabang INT NULL,
        is_active BOOLEAN DEFAULT 1
    ) ENGINE=InnoDB;

    CREATE TABLE cabang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        alamat TEXT NOT NULL,
        kota_id INT NOT NULL,
        is_active BOOLEAN DEFAULT 1
    ) ENGINE=InnoDB;

    CREATE TABLE supplier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        kontak VARCHAR(50),
        is_active BOOLEAN DEFAULT 1
    ) ENGINE=InnoDB;

    CREATE TABLE produk_induk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sku_induk VARCHAR(50) UNIQUE NOT NULL,
        nama_produk VARCHAR(150) NOT NULL,
        deskripsi TEXT,
        kategori VARCHAR(50),
        id_supplier INT NULL,
        is_active BOOLEAN DEFAULT 1,
        FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    CREATE TABLE produk_variasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_produk_induk INT NULL,
        sku_variasi VARCHAR(50) UNIQUE NOT NULL,
        nama_variasi VARCHAR(100),
        harga DECIMAL(12,2) NOT NULL,
        berat INT NOT NULL DEFAULT 100,
        gambar VARCHAR(255) NULL,
        is_active BOOLEAN DEFAULT 1,
        FOREIGN KEY (id_produk_induk) REFERENCES produk_induk(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    CREATE TABLE stok_cabang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_variasi INT NOT NULL,
        id_cabang INT NOT NULL,
        stok INT NOT NULL DEFAULT 0,
        FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE,
        FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE CASCADE,
        UNIQUE KEY (id_variasi, id_cabang)
    ) ENGINE=InnoDB;

    CREATE TABLE pengaturan_api (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cabang INT NOT NULL,
        platform ENUM('shopee', 'midtrans', 'rajaongkir') NOT NULL,
        api_key VARCHAR(255),
        api_secret VARCHAR(255),
        webhook_url VARCHAR(255),
        is_active BOOLEAN DEFAULT 0,
        FOREIGN KEY (id_cabang) REFERENCES cabang(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE penjualan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        no_invoice VARCHAR(50) UNIQUE NOT NULL,
        id_cabang INT NOT NULL,
        id_user INT NULL,
        tipe_transaksi ENUM('pos', 'ecommerce', 'shopee') NOT NULL,
        status_pesanan ENUM('Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') NOT NULL,
        total_harga DECIMAL(12,2) NOT NULL,
        ongkir DECIMAL(10,2) DEFAULT 0,
        nama_penerima VARCHAR(100) NULL,
        telepon VARCHAR(20) NULL,
        alamat_lengkap TEXT NULL,
        kurir VARCHAR(50) NULL,
        layanan VARCHAR(50) NULL,
        snap_token VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cabang) REFERENCES cabang(id),
        FOREIGN KEY (id_user) REFERENCES users(id)
    ) ENGINE=InnoDB;

    CREATE TABLE detail_penjualan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_penjualan INT NOT NULL,
        id_variasi INT NOT NULL,
        qty INT NOT NULL,
        harga_satuan DECIMAL(12,2) NOT NULL,
        FOREIGN KEY (id_penjualan) REFERENCES penjualan(id),
        FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
    ) ENGINE=InnoDB;

    CREATE TABLE mutasi_stok (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_variasi INT NOT NULL,
        cabang_asal INT NOT NULL,
        cabang_tujuan INT NOT NULL,
        qty INT NOT NULL,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id),
        FOREIGN KEY (cabang_asal) REFERENCES cabang(id),
        FOREIGN KEY (cabang_tujuan) REFERENCES cabang(id)
    ) ENGINE=InnoDB;

    CREATE TABLE stock_opname (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cabang INT NOT NULL,
        id_variasi INT NOT NULL,
        stok_sistem INT NOT NULL,
        stok_fisik INT NOT NULL,
        selisih INT NOT NULL,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cabang) REFERENCES cabang(id),
        FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
    ) ENGINE=InnoDB;

    CREATE TABLE kartu_stok (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cabang INT NOT NULL,
        id_variasi INT NOT NULL,
        jenis_mutasi ENUM('Masuk', 'Keluar', 'Pengembalian/Batal', 'Karantina', 'Opname') NOT NULL,
        qty INT NOT NULL,
        sisa_stok INT NOT NULL,
        keterangan TEXT,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cabang) REFERENCES cabang(id),
        FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
    ) ENGINE=InnoDB;

    CREATE TABLE gudang_karantina (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cabang INT NOT NULL,
        id_variasi INT NOT NULL,
        qty INT NOT NULL,
        alasan TEXT,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cabang) REFERENCES cabang(id),
        FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id)
    ) ENGINE=InnoDB;
    ";

    $pdo->exec($sql);
    echo "<h3>3. Arsitektur Tabel (Omnichannel TA) berhasil dibuat.</h3>";

    // Enable foreign key checks back
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 3. Masukkan Data Dummy Master (Cabang, Supplier, Produk, Stok)
    
    // Cabang
    $pdo->exec("INSERT INTO cabang (nama, alamat, kota_id) VALUES 
        ('Zencare Muharto', 'Jl. Muharto No. 55D, Jodipan, Kec. Blimbing, Kota Malang', 256),
        ('Zencare Borobudur', 'Jl. Simpang Borobudur No. 54, Mojolangu, Kec. Lowokwaru, Kota Malang', 256)");
    
    // Supplier
    $pdo->exec("INSERT INTO supplier (nama, kontak) VALUES ('PT. Alkes Medika Nusantara', '08123456789')");
    $id_supplier = $pdo->lastInsertId();
    
    // Dummy Products
    $dummyProducts = [
        [
            'sku' => 'MON-2026-001',
            'nama' => 'Tensimeter Digital Omron',
            'var' => 'HEM-7120',
            'kat' => 'Alat Monitor',
            'harga' => 550000.00,
            'berat' => 800,
            'gambar' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=600&q=80',
            'deskripsi' => 'Alat pengukur tekanan darah digital otomatis yang akurat.',
            'stok_muharto' => 10,
            'stok_borobudur' => 5
        ],
        [
            'sku' => 'CEK-2026-002',
            'nama' => 'Easy Touch GCU 3 in 1 Kit',
            'var' => 'Standard',
            'kat' => 'Alat Cek Darah',
            'harga' => 175000.00,
            'berat' => 300,
            'gambar' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=600&q=80',
            'deskripsi' => 'Alat ukur kadar Gula Darah, Kolesterol, dan Asam Urat.',
            'stok_muharto' => 15,
            'stok_borobudur' => 10
        ],
        [
            'sku' => 'BTL-2026-003',
            'nama' => 'Kursi Roda Medis Sella',
            'var' => 'Chrome Steel',
            'kat' => 'Alat Bantu Jalan',
            'harga' => 950000.00,
            'berat' => 15000,
            'gambar' => 'https://images.unsplash.com/photo-1581093450021-4a7360e9a6b5?auto=format&fit=crop&w=600&q=80',
            'deskripsi' => 'Kursi roda standar kuat dan nyaman.',
            'stok_muharto' => 5,
            'stok_borobudur' => 3
        ]
    ];

    $insInduk = $pdo->prepare("INSERT INTO produk_induk (sku_induk, nama_produk, deskripsi, kategori, id_supplier) VALUES (?, ?, ?, ?, ?)");
    $insVar = $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk, sku_variasi, nama_variasi, harga, berat, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    $insStok = $pdo->prepare("INSERT INTO stok_cabang (id_variasi, id_cabang, stok) VALUES (?, ?, ?)");
    $insKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Masuk', ?, ?, 'STOK AWAL (SETUP)')");

    foreach ($dummyProducts as $prod) {
        // Induk
        $sku_induk = $prod['sku'] . '-IND';
        $insInduk->execute([$sku_induk, $prod['nama'], $prod['deskripsi'], $prod['kat'], $id_supplier]);
        $id_induk = $pdo->lastInsertId();

        // Variasi
        $sku_var = $prod['sku'] . '-' . strtoupper(substr($prod['var'], 0, 3));
        $insVar->execute([$id_induk, $sku_var, $prod['var'], $prod['harga'], $prod['berat'], $prod['gambar']]);
        $id_var = $pdo->lastInsertId();

        // Stok & Kartu Stok (Muharto = 1)
        if ($prod['stok_muharto'] > 0) {
            $insStok->execute([$id_var, 1, $prod['stok_muharto']]);
            $insKartu->execute([1, $id_var, $prod['stok_muharto'], $prod['stok_muharto']]);
        }
        
        // Stok & Kartu Stok (Borobudur = 2)
        if ($prod['stok_borobudur'] > 0) {
            $insStok->execute([$id_var, 2, $prod['stok_borobudur']]);
            $insKartu->execute([2, $id_var, $prod['stok_borobudur'], $prod['stok_borobudur']]);
        }
    }

    echo "<h3>4. Data Dummy Master dan Stok per Cabang berhasil diisi.</h3>";
    echo "<h3>Setup Keseluruhan Berhasil! Sistem Omnichannel Zencare Medical sudah siap digunakan.</h3>";
    echo "<a href='zencare_store.php' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Buka Zencare Store</a>";

} catch (PDOException $e) {
    die("<h3>Setup Gagal: " . $e->getMessage() . "</h3>");
}
?>