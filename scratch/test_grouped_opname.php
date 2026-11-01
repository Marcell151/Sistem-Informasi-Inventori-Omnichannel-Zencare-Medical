<?php
require 'config/koneksi.php';

$idSesi = 7; // sesi historis
$stmt = $pdo->prepare("
    SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
           pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = ?
    ORDER BY dob.jenis_komoditas ASC, pi.nama_produk ASC, pv.nama_variasi ASC, sb.tgl_exp ASC
");
$stmt->execute([$idSesi]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($rows as $r) {
    $vid = $r['id_variasi'];
    if (!isset($grouped[$vid])) {
        $grouped[$vid] = [
            'id_variasi' => $vid,
            'nama_produk' => $r['nama_produk'],
            'nama_variasi' => $r['nama_variasi'],
            'sku_variasi' => $r['sku_variasi'],
            'jenis_komoditas' => $r['jenis_komoditas'],
            'satuan_besar' => $r['satuan_besar'],
            'satuan_kecil' => $r['satuan_kecil'],
            'rasio_konversi' => $r['rasio_konversi'],
            'items' => []
        ];
    }
    $grouped[$vid]['items'][] = $r;
}

echo "Total Produk Variasi: " . count($grouped) . "\n";
echo "Total Baris Batch/Item: " . count($rows) . "\n\n";

$i = 1;
foreach (array_slice($grouped, 0, 5, true) as $vid => $g) {
    echo "#$i [{$g['jenis_komoditas']}] {$g['nama_produk']} - {$g['nama_variasi']} ({$g['sku_variasi']})\n";
    if ($g['rasio_konversi'] > 1) {
        echo "   Konversi: 1 {$g['satuan_besar']} = {$g['rasio_konversi']} {$g['satuan_kecil']}\n";
    }
    foreach ($g['items'] as $it) {
        $batch = $it['no_batch'] ?: 'Serial Number';
        echo "   -> [{$it['satuan_tipe']}] Batch: $batch | Satuan: {$it['nama_satuan']} | Fisik: {$it['stok_sistem_fisik']}\n";
    }
    echo "--------------------------------------------------------\n";
    $i++;
}

