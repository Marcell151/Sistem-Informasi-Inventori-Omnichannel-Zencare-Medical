<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->prepare("
    SELECT p.*, u.nama_lengkap AS nama_petugas, u.role AS role_petugas
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id
    WHERE p.no_invoice = 'POS-20261101-2875'
");
$stmt->execute();
$trx = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtDetail = $pdo->prepare("
    SELECT d.*, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_item, v.sku_variasi
    FROM detail_penjualan d
    JOIN produk_variasi v ON d.id_variasi = v.id
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE d.id_penjualan = ?
");
$stmtDetail->execute([$trx['id']]);
$items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

$aggregatedItems = [];
foreach ($items as $row) {
    $key = $row['id_variasi'] . '_' . (float)$row['harga_satuan'];
    if (isset($aggregatedItems[$key])) {
        $aggregatedItems[$key]['qty'] += (int)$row['qty'];
        if (!empty($row['catatan_logistik'])) {
            $parts = array_map('trim', explode(',', $aggregatedItems[$key]['catatan_logistik'] . ',' . $row['catatan_logistik']));
            $aggregatedItems[$key]['catatan_logistik'] = implode(', ', array_unique(array_filter($parts)));
        }
    } else {
        $row['qty'] = (int)$row['qty'];
        $aggregatedItems[$key] = $row;
    }
}

echo "TRX:\n";
print_r($trx);
echo "AGGREGATED ITEMS:\n";
print_r($aggregatedItems);

