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
        // Ambil Batch aktif
        $stmt = $pdo->prepare("SELECT no_batch, tgl_exp, stok_sisa FROM stok_batch WHERE id_variasi = ? AND stok_sisa > 0 ORDER BY tgl_exp ASC");
        $stmt->execute([$idVariasi]);
        $response['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else if ($kategori === 'Alat Kesehatan') {
        // Ambil SN aktif
        $stmt = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia'");
        $stmt->execute([$idVariasi]);
        $response['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
