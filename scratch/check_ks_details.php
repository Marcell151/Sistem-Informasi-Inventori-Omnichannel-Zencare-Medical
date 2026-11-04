<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== KARTU STOK ID 1 (Amoxicillin) ===\n";
$ks1 = $pdo->query("SELECT id, tanggal, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, qty, sisa_stok, keterangan FROM kartu_stok WHERE id_variasi = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
print_r($ks1);

echo "\n=== KARTU STOK ID 5 (Termometer) ===\n";
$ks5 = $pdo->query("SELECT id, tanggal, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, qty, sisa_stok, keterangan FROM kartu_stok WHERE id_variasi = 5 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
print_r($ks5);

