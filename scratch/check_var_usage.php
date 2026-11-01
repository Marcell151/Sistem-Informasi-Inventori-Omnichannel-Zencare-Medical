<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

foreach ([61, 62, 63] as $vid) {
    $stokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $vid")->fetchColumn();
    $stokBatch = $pdo->query("SELECT COUNT(*) FROM stok_batch WHERE id_variasi = $vid")->fetchColumn();
    $penjualan = $pdo->query("SELECT COUNT(*) FROM detail_penjualan WHERE id_variasi = $vid")->fetchColumn();
    echo "Variasi ID $vid -> stok_toko: " . var_export($stokToko, true) . ", stok_batch: $stokBatch, penjualan: $penjualan\n";
}

