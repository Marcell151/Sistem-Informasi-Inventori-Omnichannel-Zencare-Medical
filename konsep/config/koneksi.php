<?php
// File: config/koneksi.php
// Koneksi Database PDO Aman untuk ZenCare Medical Omnichannel System

require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Menggunakan prepared statements murni bawaan MySQL server
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Sembunyikan detail sensitif database saat produksi, catat ke log server
    error_log("ZenCare DB Connection Failure: " . $e->getMessage());
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'>
            <h3>Koneksi Database Gagal</h3>
            <p>Sistem tidak dapat terhubung ke server database ZenCare Medical. Pastikan MySQL aktif.</p>
         </div>");
}
?>
