<?php
require_once __DIR__ . '/../config/koneksi.php';

$jsonFile = __DIR__ . '/product_images_map.json';
if (!file_exists($jsonFile)) {
    die("File map not found!\n");
}

$map = json_decode(file_get_contents($jsonFile), true);
if (empty($map)) {
    die("Map is empty!\n");
}

$stmtVar = $pdo->prepare("UPDATE produk_variasi SET gambar = ? WHERE id = ?");
$stmtInduk = $pdo->prepare("UPDATE produk_induk SET gambar = ? WHERE id = ?");

$countVar = 0;
$countInduk = 0;
$indukSeen = [];

foreach ($map as $varId => $info) {
    $imgRel = 'assets/img/produk/' . $info['filename'];
    $stmtVar->execute([$imgRel, $varId]);
    $countVar++;

    $indukId = $info['induk_id'];
    if (!isset($indukSeen[$indukId])) {
        $stmtInduk->execute([$imgRel, $indukId]);
        $indukSeen[$indukId] = true;
        $countInduk++;
    }
}

echo "BERHASIL UPDATE DATABASE:\n";
echo "- $countVar variasi diperbarui fotonya.\n";
echo "- $countInduk produk induk diperbarui fotonya.\n";
