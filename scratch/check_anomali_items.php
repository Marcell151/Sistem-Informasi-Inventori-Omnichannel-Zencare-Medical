<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("
    SELECT dob.id, pi.nama_produk, pv.nama_variasi, dob.satuan_tipe, dob.stok_sistem_fisik, dob.jenis_komoditas, sb.tgl_exp, sb.no_batch
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = (SELECT id FROM sesi_opname WHERE status = 'Aktif' ORDER BY id DESC LIMIT 1)
    AND (
        pi.nama_produk LIKE '%Cefadroxil%'
        OR pi.nama_produk LIKE '%Sanmol%'
        OR pi.nama_produk LIKE '%Amoxicillin%'
        OR pi.nama_produk LIKE '%Betadine%'
        OR pi.nama_produk LIKE '%Omron%'
    )
");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['id'] . ' | ' . $r['nama_produk'] . ' | ' . $r['satuan_tipe'] . ' | Sys:' . $r['stok_sistem_fisik'] . ' | Exp:' . ($r['tgl_exp'] ?: '-') . PHP_EOL;
}
