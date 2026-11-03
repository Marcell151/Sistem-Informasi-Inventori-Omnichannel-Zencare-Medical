<?php
// File: api/get_batch_sn.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

$idVariasi = intval($_GET['id_variasi'] ?? 0);

if (!$idVariasi) {
    echo json_encode(['error' => 'ID Variasi wajib diisi']);
    exit;
}

try {
    // Cek kategori produk
    $kategori = $pdo->query("SELECT pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = $idVariasi")->fetchColumn();

    $response = ['kategori' => $kategori, 'items' => []];

    if ($kategori === 'Obat') {
        // Ambil Batch aktif yang memiliki stok fisik di toko (stok_sisa + reserved > 0)
        $stmt = $pdo->prepare("
            SELECT sb.id, sb.no_batch, sb.tgl_exp, sb.stok_sisa,
                   COALESCE((
                       SELECT SUM(pba.qty_satuan_kecil) 
                       FROM pesanan_batch_alokasi pba 
                       JOIN penjualan p ON pba.id_penjualan = p.id 
                       WHERE pba.id_batch = sb.id 
                         AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                   ), 0) AS stok_reserved
            FROM stok_batch sb
            WHERE sb.id_variasi = ? 
              AND (sb.stok_sisa + COALESCE((
                       SELECT SUM(pba.qty_satuan_kecil) 
                       FROM pesanan_batch_alokasi pba 
                       JOIN penjualan p ON pba.id_penjualan = p.id 
                       WHERE pba.id_batch = sb.id 
                         AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                   ), 0)) > 0
            ORDER BY sb.tgl_exp ASC
        ");
        $stmt->execute([$idVariasi]);
        $response['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else if ($kategori === 'Alat Kesehatan') {
        // Ambil SN aktif: unit Tersedia DAN unit yang dikunci pesanan aktif (karena barang fisiknya masih ada di toko)
        $stmt = $pdo->prepare("
            SELECT us.id, us.serial_number, 
                   CASE 
                       WHEN us.status = 'Tersedia' THEN 'Tersedia'
                       WHEN us.status = 'Terjual' AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil') THEN CONCAT('Reserved (', p.status_pesanan, ')')
                       ELSE us.status
                   END AS status,
                   CASE WHEN us.status = 'Tersedia' THEN 1 ELSE 0 END AS is_available
            FROM unit_serial us
            LEFT JOIN penjualan p ON us.id_penjualan = p.id
            WHERE us.id_variasi = ? 
              AND (us.status = 'Tersedia' OR (us.status = 'Terjual' AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')))
            ORDER BY is_available DESC, us.id ASC
        ");
        $stmt->execute([$idVariasi]);
        $snRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['items'] = $snRows;
        $response['sns'] = $snRows;
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
