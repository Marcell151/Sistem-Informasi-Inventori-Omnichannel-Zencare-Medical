<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

try {
    $pdo->beginTransaction();

    // Dapatkan semua variasi Alat Kesehatan
    $stmt = $pdo->query("
        SELECT pv.id 
        FROM produk_variasi pv 
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
        WHERE pi.kategori = 'Alat Kesehatan'
    ");
    $alkesVariasi = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($alkesVariasi) > 0) {
        $inQuery = implode(',', array_fill(0, count($alkesVariasi), '?'));
        
        // Update rasio menjadi 1, dan samakan nama satuan besar dengan satuan kecil
        $updateStmt = $pdo->prepare("
            UPDATE produk_variasi 
            SET rasio_konversi = 1,
                satuan_besar = satuan_kecil,
                stok_minimum_besar = stok_minimum_kecil
            WHERE id IN ($inQuery)
        ");
        $updateStmt->execute($alkesVariasi);
        
        echo "Berhasil memperbarui " . count($alkesVariasi) . " produk Alat Kesehatan menjadi rasio 1:1.\n";
    } else {
        echo "Tidak ada produk Alat Kesehatan yang ditemukan.\n";
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Gagal: " . $e->getMessage() . "\n";
}
