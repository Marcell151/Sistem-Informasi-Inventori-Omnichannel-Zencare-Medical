<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT pi.kategori, pv.id, pv.sku_variasi, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama,
           (SELECT COUNT(*) FROM unit_serial WHERE id_variasi = pv.id AND status='Tersedia') AS sn_count,
           (SELECT COUNT(*) FROM stok_batch WHERE id_variasi = pv.id AND stok_sisa > 0) AS batch_count
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan'
    ORDER BY pv.id
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== ALAT KESEHATAN BREAKDOWN ===\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | SKU: {$r['sku_variasi']} | SNs: {$r['sn_count']} | Batches: {$r['batch_count']} | {$r['nama']}\n";
}

