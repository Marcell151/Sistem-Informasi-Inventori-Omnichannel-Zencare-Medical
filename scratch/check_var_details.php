<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$q = $pdo->query("
    SELECT pi.id, pi.nama_produk, COUNT(pv.id) as cnt
    FROM produk_induk pi
    LEFT JOIN produk_variasi pv ON pv.id_produk_induk = pi.id AND pv.is_active=1
    WHERE pi.is_active=1
    GROUP BY pi.id
")->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($q as $r) {
    $total += $r['cnt'];
    if ($r['cnt'] != 1) {
        echo "{$r['nama_produk']} (ID: {$r['id']}): {$r['cnt']} variasi\n";
    }
}
echo "Sum of variasi from active induk: $total\n";

$orphanVar = $pdo->query("
    SELECT pv.id, pv.nama_variasi, pv.id_produk_induk 
    FROM produk_variasi pv 
    LEFT JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE pv.is_active=1 AND (pi.id IS NULL OR pi.is_active=0)
")->fetchAll(PDO::FETCH_ASSOC);
echo "Orphan variasi: " . count($orphanVar) . "\n";
foreach ($orphanVar as $ov) {
    echo "- Var ID: {$ov['id']}, Name: {$ov['nama_variasi']}, Induk ID: {$ov['id_produk_induk']}\n";
}

