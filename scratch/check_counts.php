<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$allInduk = $pdo->query("SELECT id, nama_produk FROM produk_induk WHERE is_active=1")->fetchAll(PDO::FETCH_ASSOC);
$totalInduk = count($allInduk);
$totalVar = $pdo->query("SELECT COUNT(*) FROM produk_variasi WHERE is_active=1")->fetchColumn();

echo "Total Induk Aktif: $totalInduk\n";
echo "Total Variasi Aktif: $totalVar\n";

$counts = $pdo->query("
    SELECT pi.nama_produk, COUNT(pv.id) as cnt
    FROM produk_induk pi
    LEFT JOIN produk_variasi pv ON pv.id_produk_induk = pi.id AND pv.is_active=1
    WHERE pi.is_active=1
    GROUP BY pi.id, pi.nama_produk
    HAVING cnt > 1
")->fetchAll(PDO::FETCH_ASSOC);

print_r($counts);

