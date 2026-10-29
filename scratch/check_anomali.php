<?php
require 'config/koneksi.php';

echo "=== log_anomali_fefo ===\n";
print_r($pdo->query("SELECT a.*, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) as nama_produk, u.nama_lengkap 
FROM log_anomali_fefo a 
LEFT JOIN produk_variasi pv ON a.id_variasi = pv.id 
LEFT JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
LEFT JOIN users u ON a.id_user = u.id 
ORDER BY a.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));

