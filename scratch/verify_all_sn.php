<?php
require __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT us.id, us.id_variasi, pv.nama_variasi, pi.nama_produk, us.serial_number, us.status, us.id_penjualan, p.status_pesanan 
                     FROM unit_serial us 
                     JOIN produk_variasi pv ON us.id_variasi = pv.id
                     JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                     LEFT JOIN penjualan p ON us.id_penjualan = p.id
                     ORDER BY us.id_variasi, us.id");
echo "=== ALL UNIT SERIALS IN DB ===\n";
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID Var: {$r['id_variasi']} | {$r['nama_produk']} | SN: {$r['serial_number']} | Status: {$r['status']} | Order: {$r['id_penjualan']} ({$r['status_pesanan']})\n";
}

