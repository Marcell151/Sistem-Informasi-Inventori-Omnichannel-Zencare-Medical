<?php
require 'config/koneksi.php';

$idSesi = 7;
$stmtItems = $pdo->prepare("
    SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
           pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp,
           u_hit.nama_lengkap AS nama_penghitung, u_mut.nama_lengkap AS nama_pemutasi
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    LEFT JOIN users u_hit ON dob.dihitung_oleh = u_hit.id
    LEFT JOIN users u_mut ON dob.dimutasi_oleh = u_mut.id
    WHERE dob.id_sesi = ?
    ORDER BY dob.jenis_komoditas ASC, pi.nama_produk ASC, pv.nama_variasi ASC, (dob.satuan_tipe = 'besar') DESC, sb.tgl_exp ASC
");
$stmtItems->execute([$idSesi]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$groupedItems = [];
foreach ($items as $it) {
    $vid = $it['id_variasi'];
    if (!isset($groupedItems[$vid])) {
        $groupedItems[$vid] = [
            'id_variasi'     => $vid,
            'nama_produk'    => $it['nama_produk'],
            'nama_variasi'   => $it['nama_variasi'],
            'sku_variasi'    => $it['sku_variasi'] ?? '',
            'kategori'       => $it['kategori'] ?? '',
            'jenis_komoditas'=> $it['jenis_komoditas'],
            'satuan_besar'   => $it['satuan_besar'] ?? '',
            'satuan_kecil'   => $it['satuan_kecil'] ?? '',
            'rasio_konversi' => intval($it['rasio_konversi'] ?? 1),
            'items'          => []
        ];
    }
    $groupedItems[$vid]['items'][] = $it;
}

echo "Total items: " . count($items) . "\n";
echo "Total grouped: " . count($groupedItems) . "\n";
foreach (array_slice($groupedItems, 0, 3, true) as $g) {
    echo "PRODUCT: {$g['nama_produk']} - {$g['nama_variasi']} ({$g['sku_variasi']})\n";
    foreach ($g['items'] as $it) {
        echo "   -> [{$it['satuan_tipe']}] {$it['nama_satuan']} | Sistem: {$it['stok_sistem_fisik']} | Fisik: {$it['hitung_fisik']} | Sel: {$it['selisih']}\n";
    }
}

