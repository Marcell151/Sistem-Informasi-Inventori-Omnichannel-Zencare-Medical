<?php
require __DIR__ . '/../config/koneksi.php';

$sns = $pdo->query("SELECT us.id, us.id_variasi, pv.nama_variasi, pi.nama_produk, us.serial_number, us.status, us.catatan FROM unit_serial us JOIN produk_variasi pv ON us.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id ORDER BY us.id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "=== UNIT SERIAL NUMBER CURRENT ===\n";
foreach ($sns as $s) {
    echo "ID: {$s['id']} | [{$s['nama_produk']} - {$s['nama_variasi']}] | SN: {$s['serial_number']} | Status: {$s['status']} | Catatan: {$s['catatan']}\n";
}

