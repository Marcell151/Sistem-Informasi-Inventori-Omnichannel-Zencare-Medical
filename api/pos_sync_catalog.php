<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

try {
    $stmt = $pdo->prepare("
        SELECT v.id, v.sku_variasi, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama,
               v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, 
               COALESCE(sc.stok,0) AS stok, i.kategori,
               (SELECT GROUP_CONCAT(serial_number ORDER BY created_at ASC SEPARATOR ',') 
                FROM unit_serial WHERE id_variasi = v.id AND status='Tersedia') AS sn_list,
               (SELECT GROUP_CONCAT(CONCAT(no_batch, '|', stok_sisa) ORDER BY tgl_exp ASC, no_batch ASC SEPARATOR ',') 
                FROM stok_batch WHERE id_variasi = v.id AND stok_sisa > 0 AND tgl_exp >= CURDATE()) AS batch_list,
               (SELECT COALESCE(SUM(stok_sisa), 0) 
                FROM stok_batch WHERE id_variasi = v.id AND no_batch LIKE '%.%' AND tgl_exp >= CURDATE()) AS stok_eceran,
               (SELECT COALESCE(SUM(stok_sisa), 0) 
                FROM stok_batch WHERE id_variasi = v.id AND no_batch NOT LIKE '%.%' AND tgl_exp >= CURDATE()) AS stok_dus
        FROM produk_variasi v 
        JOIN produk_induk i ON v.id_produk_induk=i.id
        LEFT JOIN stok_toko sc ON sc.id_variasi=v.id 
        WHERE v.is_active=1 AND i.is_active=1 
        ORDER BY i.nama_produk ASC
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'timestamp' => time(),
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
