<?php
// Test script to calculate Estimasi Kerugian Persediaan
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT dob.*, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil, pv.harga_jual_kecil, pv.harga_jual_besar
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    LIMIT 10
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Fetched " . count($rows) . " sample rows for testing calculation.\n";

