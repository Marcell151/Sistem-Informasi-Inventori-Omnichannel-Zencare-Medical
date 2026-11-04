<?php
require_once __DIR__ . '/../config/koneksi.php';

$idVar = 10;
$stokToko = (int)$pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
$stokSisaKecil = (int)$pdo->query("SELECT COALESCE(SUM(stok_sisa), 0) FROM stok_batch WHERE id_variasi = $idVar")->fetchColumn();
$reservedKecil = (int)$pdo->query("
    SELECT COALESCE(SUM(pba.qty_satuan_kecil), 0)
    FROM pesanan_batch_alokasi pba
    JOIN penjualan p ON pba.id_penjualan = p.id
    WHERE pba.id_variasi = $idVar AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
")->fetchColumn();

// If pba doesn't have id_variasi:
if ($reservedKecil == 0) {
    $reservedKecil = (int)$pdo->query("
        SELECT COALESCE(SUM(pba.qty_satuan_kecil), 0)
        FROM pesanan_batch_alokasi pba
        JOIN stok_batch sb ON pba.id_batch = sb.id
        JOIN penjualan p ON pba.id_penjualan = p.id
        WHERE sb.id_variasi = $idVar AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
    ")->fetchColumn();
}

$lastKs = $pdo->query("
    SELECT id, satuan_tipe, jenis_mutasi, alasan_mutasi, qty, sisa_stok, keterangan
    FROM kartu_stok
    WHERE id_variasi = $idVar
    ORDER BY id DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

echo "PARACETAMOL 500MG (ID 10):\n";
echo "- stok_toko        : $stokToko Strip (Stok Tersedia Bebas Dijual)\n";
echo "- stok_sisa batch  : $stokSisaKecil Strip\n";
echo "- Reserved Pesanan : $reservedKecil Strip\n";
echo "- Total Fisik Toko : " . ($stokSisaKecil + $reservedKecil) . " Strip (Fisik Nyata di Rak & Meja Packing)\n";
echo "- Kartu Stok Sisa  : " . ($lastKs['sisa_stok'] ?? 0) . " ({$lastKs['satuan_tipe']})\n";

