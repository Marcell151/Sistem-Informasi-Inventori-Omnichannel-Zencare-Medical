<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT dob.id, pi.nama_produk, pv.nama_variasi, dob.satuan_tipe, dob.stok_sistem_fisik,
           dob.jenis_komoditas, sb.tgl_exp, sb.no_batch
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = (SELECT id FROM sesi_opname WHERE status = 'Aktif' ORDER BY id DESC LIMIT 1)
    ORDER BY dob.id ASC
");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total items: " . count($items) . PHP_EOL;

$anomaliFound = [];
foreach ($items as $it) {
    $nama = strtolower($it['nama_produk']);
    $satuanTipe = $it['satuan_tipe'];
    $sistem = intval($it['stok_sistem_fisik']);
    $isExp = ($it['jenis_komoditas'] === 'Obat' && !empty($it['tgl_exp']) && strtotime($it['tgl_exp']) < strtotime(date('Y-m-d')));

    if (!isset($anomaliFound['cefadroxil']) && strpos($nama, 'cefadroxil') !== false && $satuanTipe === 'kecil') {
        $rusakQty = min(2, $sistem);
        $baik = max(0, $sistem - $rusakQty);
        $anomaliFound['cefadroxil'] = "Cefadroxil: Baik $baik, Rusak $rusakQty";
    } elseif (!isset($anomaliFound['sanmol']) && strpos($nama, 'sanmol') !== false && $isExp) {
        $expFisik = max(0, $sistem - 3);
        $anomaliFound['sanmol'] = "Sanmol Expired: Baik 0, Exp $expFisik (Defisit 3)";
    } elseif (!isset($anomaliFound['amox']) && strpos($nama, 'amox') !== false && $satuanTipe === 'kecil') {
        $hilang = min(2, $sistem);
        $baik = max(0, $sistem - $hilang);
        $anomaliFound['amox'] = "Amoxicillin: Baik $baik (Kurang $hilang)";
    } elseif (!isset($anomaliFound['tensimeter']) && strpos($nama, 'tensimeter') !== false && $sistem >= 1) {
        $baik = max(0, $sistem - 1);
        $anomaliFound['tensimeter'] = "Tensimeter: Baik $baik (Kurang 1)";
    } elseif (!isset($anomaliFound['betadine']) && strpos($nama, 'betadine') !== false && $satuanTipe === 'kecil') {
        $baik = $sistem + 2;
        $anomaliFound['betadine'] = "Betadine: Baik $baik (Surplus +2)";
    }
}

foreach ($anomaliFound as $k => $v) {
    echo "  $v" . PHP_EOL;
}

