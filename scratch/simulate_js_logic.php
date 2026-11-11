<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT dob.id, pi.nama_produk, pv.nama_variasi, dob.satuan_tipe, dob.stok_sistem_fisik,
           dob.jenis_komoditas, sb.tgl_exp, sb.no_batch,
           CONCAT(pi.nama_produk, ' ', pv.nama_variasi, ' ', COALESCE(pv.sku_variasi,''), ' ', COALESCE(sb.no_batch,'')) AS barcode_search
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = (SELECT id FROM sesi_opname WHERE status = 'Aktif' ORDER BY id DESC LIMIT 1)
    ORDER BY dob.id ASC
");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total items in active session: " . count($items) . PHP_EOL;

$anomaliFound = [];

foreach ($items as $it) {
    $nama = strtolower($it['nama_produk']);
    $barcodeSearch = strtolower($it['barcode_search']);
    $satuanTipe = $it['satuan_tipe'];
    $sistem = intval($it['stok_sistem_fisik']);
    $isExp = ($it['jenis_komoditas'] === 'Obat' && !empty($it['tgl_exp']) && strtotime($it['tgl_exp']) < strtotime(date('Y-m-d')));

    // Check against the JS logic in isiOtomatisAngkaDemo:
    if (!isset($anomaliFound['cefadroxil']) && strpos($nama, 'cefadroxil') !== false && (strpos($barcodeSearch, 'cfd-500-26') !== false || $satuanTipe === 'kecil')) {
        $rusakQty = min(2, $sistem);
        $baik = max(0, $sistem - $rusakQty);
        $anomaliFound['cefadroxil'] = "ID: {$it['id']} | {$it['nama_produk']} ($satuanTipe) | Sys: $sistem -> Baik: $baik, Rusak: $rusakQty, Exp: 0";
    }
    elseif (!isset($anomaliFound['sanmol']) && strpos($nama, 'sanmol') !== false && $isExp) {
        $anomaliFound['sanmol'] = "ID: {$it['id']} | {$it['nama_produk']} ($satuanTipe) | Sys: $sistem -> Baik: 0, Rusak: 0, Exp: $sistem";
    }
    elseif (!isset($anomaliFound['amox']) && strpos($nama, 'amox') !== false && $satuanTipe === 'kecil') {
        $hilang = min(2, $sistem);
        $baik = max(0, $sistem - $hilang);
        $anomaliFound['amox'] = "ID: {$it['id']} | {$it['nama_produk']} ($satuanTipe) | Sys: $sistem -> Baik: $baik, Rusak: 0, Exp: 0 (Hilang $hilang)";
    }
    elseif (!isset($anomaliFound['omron']) && (strpos($nama, 'omron') !== false || strpos($nama, 'tensimeter') !== false) && $sistem >= 1) {
        $baik = max(0, $sistem - 1);
        $anomaliFound['omron'] = "ID: {$it['id']} | {$it['nama_produk']} ($satuanTipe) | Sys: $sistem -> Baik: $baik, Rusak: 0, Exp: 0 (Hilang 1)";
    }
    elseif (!isset($anomaliFound['betadine']) && strpos($nama, 'betadine') !== false && $satuanTipe === 'kecil') {
        $baik = $sistem + 2;
        $anomaliFound['betadine'] = "ID: {$it['id']} | {$it['nama_produk']} ($satuanTipe) | Sys: $sistem -> Baik: $baik, Rusak: 0, Exp: 0 (Surplus +2)";
    }
}

echo "Anomali detected by JS logic:" . PHP_EOL;
foreach ($anomaliFound as $k => $v) {
    echo "  [$k] $v" . PHP_EOL;
}

