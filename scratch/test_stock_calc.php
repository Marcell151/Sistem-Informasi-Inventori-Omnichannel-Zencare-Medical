<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->prepare('
    SELECT pv.id, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
           pi.nama_produk, pi.kategori,
           COALESCE(st.stok, 0) AS stok_eceran,
           (SELECT COALESCE(SUM(sb.stok_sisa), 0) 
            FROM stok_batch sb 
            WHERE sb.id_variasi = pv.id 
              AND sb.no_batch NOT LIKE "%.%" 
              AND sb.tgl_exp >= CURDATE()
           ) AS stok_batch_utuh
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON st.id_variasi = pv.id
    WHERE pv.id = 7
');
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);

$rasio = max(1, intval($row['rasio_konversi']));
$maxDus = (int) floor(intval($row['stok_batch_utuh']) / $rasio);
echo "Calculated maxDus: " . $maxDus . "\n";

// Also check all batches of variation 7
$stmt2 = $pdo->query("SELECT id, id_variasi, no_batch, stok_sisa, tgl_exp FROM stok_batch WHERE id_variasi = 7");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
