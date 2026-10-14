<?php
// File: api/cek_stok_keranjang.php
// Endpoint validasi real-time stok keranjang e-commerce terhadap stok fisik toko (dus / eceran)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode POST dibutuhkan']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (!is_array($cart) || empty($cart)) {
    echo json_encode([
        'status' => 'success',
        'has_adjustments' => false,
        'adjusted_items' => [],
        'items' => []
    ]);
    exit;
}

try {
    $pdo = DB::connect();
    $hasAdjustments = false;
    $adjustedItems = [];
    $liveStockList = [];

    foreach ($cart as $item) {
        $idVariasi = intval($item['id'] ?? 0);
        $qtyDiminta = intval($item['qty'] ?? 1);
        if ($idVariasi <= 0) continue;

        $stmt = $pdo->prepare("
            SELECT pv.id, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
                   pv.harga_jual_besar, pv.harga_jual_kecil, pi.nama_produk, pi.kategori,
                   COALESCE(st.stok, 0) AS stok_eceran,
                   (SELECT COALESCE(SUM(sb.stok_sisa), 0) 
                    FROM stok_batch sb 
                    WHERE sb.id_variasi = pv.id 
                      AND sb.no_batch NOT LIKE '%.%' 
                      AND sb.tgl_exp >= CURDATE()
                   ) AS stok_batch_utuh
            FROM produk_variasi pv
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            LEFT JOIN stok_toko st ON st.id_variasi = pv.id
            WHERE pv.id = ?
        ");
        $stmt->execute([$idVariasi]);
        $prod = $stmt->fetch();

        if (!$prod) {
            $hasAdjustments = true;
            $adjustedItems[] = [
                'id' => $idVariasi,
                'nama' => $item['nama'] ?? 'Produk tidak ditemukan',
                'old_qty' => $qtyDiminta,
                'new_qty' => 0,
                'max_dus' => 0,
                'satuan' => 'Dus',
                'reason' => 'Produk sudah tidak tersedia'
            ];
            continue;
        }

        $rasio = max(1, intval($prod['rasio_konversi']));
        $stokEceran = max(0, intval($prod['stok_eceran']));
        if ($prod['kategori'] === 'Obat') {
            $maxDus = (int) floor(intval($prod['stok_batch_utuh']) / $rasio);
        } else {
            $maxDus = (int) floor($stokEceran / $rasio);
        }
        $namaLengkap = $prod['nama_produk'] . ' (' . $prod['nama_variasi'] . ')';
        $satuanLabel = $prod['satuan_besar'] ?: 'Dus';

        $liveStockList[$idVariasi] = [
            'id' => $idVariasi,
            'nama' => $namaLengkap,
            'max_dus' => $maxDus,
            'stok_eceran' => $stokEceran,
            'rasio' => $rasio,
            'satuan' => $satuanLabel,
            'harga_besar' => (float)($prod['harga_jual_besar'] ?: $prod['harga_jual_kecil'] * $rasio)
        ];

        if ($qtyDiminta > $maxDus) {
            $hasAdjustments = true;
            $newQty = max(0, $maxDus);
            $adjustedItems[] = [
                'id' => $idVariasi,
                'nama' => $namaLengkap,
                'old_qty' => $qtyDiminta,
                'new_qty' => $newQty,
                'max_dus' => $maxDus,
                'satuan' => $satuanLabel,
                'reason' => $maxDus > 0 
                    ? "Tersisa $maxDus $satuanLabel karena ada transaksi toko fisik / buka kardus."
                    : "Stok habis karena transaksi kasir / mutasi unit fisik."
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'has_adjustments' => $hasAdjustments,
        'adjusted_items' => $adjustedItems,
        'stock_map' => $liveStockList
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
