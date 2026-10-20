<?php
require_once __DIR__ . '/../config/koneksi.php';

$induk = $pdo->query("SELECT * FROM produk_induk WHERE id NOT IN (54, 56) ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "Clean Produk Induk Count: " . count($induk) . "\n";

$variasi = $pdo->query("SELECT * FROM produk_variasi WHERE id NOT IN (59) ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "Clean Produk Variasi Count: " . count($variasi) . "\n";

// Check if any variasi references missing induk
foreach ($variasi as $v) {
    $found = false;
    foreach ($induk as $pi) {
        if ($pi['id'] == $v['id_produk_induk']) { $found = true; break; }
    }
    if (!$found) echo "WARNING: Variasi {$v['id']} ({$v['nama_variasi']}) references missing induk {$v['id_produk_induk']}\n";
}
