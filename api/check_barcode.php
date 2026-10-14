<?php
// File: api/check_barcode.php
// Endpoint pemeriksa status barcode untuk POS & Scanner
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

$barcode = trim($_GET['barcode'] ?? '');
if (!$barcode) {
    echo json_encode(['found' => false]);
    exit;
}

try {
    // 1. Cek di stok_batch (Obat)
    $stmt = $pdo->prepare("
        SELECT sb.no_batch, sb.tgl_exp, sb.stok_sisa, pi.nama_produk, pv.nama_variasi, pv.sku_variasi
        FROM stok_batch sb
        JOIN produk_variasi pv ON sb.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE LOWER(sb.no_batch) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$barcode]);
    $batch = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($batch) {
        $today = date('Y-m-d');
        $isExpired = ($batch['tgl_exp'] < $today);
        echo json_encode([
            'found' => true,
            'tipe' => 'batch',
            'no_batch' => $batch['no_batch'],
            'nama' => $batch['nama_produk'] . ' - ' . $batch['nama_variasi'],
            'tgl_exp' => $batch['tgl_exp'],
            'stok_sisa' => intval($batch['stok_sisa']),
            'is_expired' => $isExpired
        ]);
        exit;
    }

    // 2. Cek di unit_serial (Alkes)
    $stmtSn = $pdo->prepare("
        SELECT us.serial_number, us.status, us.catatan, pi.nama_produk, pv.nama_variasi,
               pj.no_invoice, pj.created_at AS tgl_terjual
        FROM unit_serial us
        JOIN produk_variasi pv ON us.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN penjualan pj ON us.id_penjualan = pj.id
        WHERE LOWER(us.serial_number) = LOWER(?)
        LIMIT 1
    ");
    $stmtSn->execute([$barcode]);
    $sn = $stmtSn->fetch(PDO::FETCH_ASSOC);

    if ($sn) {
        $sisaGaransi = null;
        if (!empty($sn['tgl_terjual'])) {
            $tglBeli = strtotime($sn['tgl_terjual']);
            $sisaGaransi = 30 - floor((time() - $tglBeli) / 86400);
        }
        echo json_encode([
            'found' => true,
            'tipe' => 'sn',
            'serial_number' => $sn['serial_number'],
            'nama' => $sn['nama_produk'] . ' - ' . $sn['nama_variasi'],
            'status' => $sn['status'],
            'catatan' => $sn['catatan'] ?? '',
            'no_invoice' => $sn['no_invoice'] ?? '',
            'tgl_terjual' => !empty($sn['tgl_terjual']) ? date('d/m/Y H:i', strtotime($sn['tgl_terjual'])) : '',
            'sisa_garansi' => $sisaGaransi,
            'is_expired' => false
        ]);
        exit;
    }

    // 3. Cek SKU Variasi
    $stmtSku = $pdo->prepare("
        SELECT pv.sku_variasi, pi.nama_produk, pv.nama_variasi
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE LOWER(pv.sku_variasi) = LOWER(?)
        LIMIT 1
    ");
    $stmtSku->execute([$barcode]);
    $skuVar = $stmtSku->fetch(PDO::FETCH_ASSOC);

    if ($skuVar) {
        echo json_encode([
            'found' => true,
            'tipe' => 'sku',
            'sku' => $skuVar['sku_variasi'],
            'nama' => $skuVar['nama_produk'] . ' - ' . $skuVar['nama_variasi'],
            'is_expired' => false
        ]);
        exit;
    }

    echo json_encode(['found' => false]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
