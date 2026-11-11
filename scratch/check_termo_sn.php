<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT pv.id, pi.nama_produk, pv.nama_variasi, pv.harga_jual_kecil, pv.harga_jual_besar, st.stok
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON pv.id = st.id_variasi
    WHERE pi.nama_produk LIKE '%Termometer Digital Omron%'
");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "ID: {$r['id']} | {$r['nama_produk']} | Harga: {$r['harga_jual_kecil']} | Stok: {$r['stok']}" . PHP_EOL;

$stmtSn = $pdo->query("SELECT id, serial_number, status FROM unit_serial WHERE id_variasi = {$r['id']} LIMIT 5");
while ($s = $stmtSn->fetch(PDO::FETCH_ASSOC)) {
    echo "  SN: {$s['id']} - {$s['serial_number']} ({$s['status']})" . PHP_EOL;
}

