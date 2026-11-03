<?php
$sql = file_get_contents('c:/xampp/htdocs/inventory_zencare/database/isi/db_inventory (demo) 3.sql');

$checks = [
    'Admin Toko',
    'Permohonan Pelanggan Disetujui Admin: Salah memilih variasi kemasan obat',
    'Dibatalkan oleh Admin Toko karena gangguan pemadaman listrik PLN',
    'Stok Opname Rutin Awal Bulan oleh Admin Toko'
];

echo "=== VERIFIKASI AKTOR DI FILE DUMP SQL (DEMO 3) ===\n";
foreach ($checks as $c) {
    if (strpos($sql, $c) !== false) {
        echo "  ✓ Ditemukan: '$c'\n";
    } else {
        echo "  ✗ TIDAK DITEMUKAN: '$c'\n";
    }
}
