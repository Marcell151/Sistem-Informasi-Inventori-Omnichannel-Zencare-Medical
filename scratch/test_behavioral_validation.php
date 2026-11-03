<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== TEST 1: DASHBOARD HITUNG RETUR SELESAI POTONG FAKTUR ===\n";
$returMenungguCount = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur = 'menunggu_pengganti'")->fetchColumn();
$returSelesaiCount  = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur IN ('selesai_tukar_barang', 'selesai_potong_faktur')")->fetchColumn();
echo "Retur Menunggu: $returMenungguCount | Retur Selesai (Termasuk Potong Faktur): $returSelesaiCount\n";

echo "\n=== TEST 2: SIMULASI BATCH DENGAN SELURUH STOK RESERVED (stok_sisa = 0) ===\n";
// Ambil 1 batch contoh
$testBatch = $pdo->query("SELECT sb.id, sb.id_variasi, sb.no_batch, sb.stok_sisa FROM stok_batch sb WHERE sb.stok_sisa > 0 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Sampel Batch: {$testBatch['no_batch']} (ID: {$testBatch['id']}, Stok Sisa Awal: {$testBatch['stok_sisa']})\n";

// Cek query opname snapshot baru:
$stmtTestSnapshot = $pdo->prepare("
    SELECT sb.id AS id_batch, sb.no_batch, sb.stok_sisa,
           COALESCE((
               SELECT SUM(pba.qty_satuan_kecil) 
               FROM pesanan_batch_alokasi pba 
               JOIN penjualan p ON pba.id_penjualan = p.id 
               WHERE pba.id_batch = sb.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0) AS stok_reserved_kecil
    FROM stok_batch sb
    WHERE sb.id = ?
      AND (sb.stok_sisa + COALESCE((
               SELECT SUM(pba.qty_satuan_kecil) 
               FROM pesanan_batch_alokasi pba 
               JOIN penjualan p ON pba.id_penjualan = p.id 
               WHERE pba.id_batch = sb.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0)) > 0
");

// Kondisi normal
$stmtTestSnapshot->execute([$testBatch['id']]);
$row1 = $stmtTestSnapshot->fetch(PDO::FETCH_ASSOC);
echo "Snapshot Normal: Muncul? " . ($row1 ? "YA (Fisik = {$row1['stok_sisa']})" : "TIDAK") . "\n";

echo "\n=== TEST 3: API GET AVAILABLE SNS DENGAN RESERVED UNIT ===\n";
$_GET = ['action' => 'get_available_sns', 'id_variasi' => 53];
ob_start();
require __DIR__ . '/../api/get_batch_sn.php';
$rawJson = ob_get_clean();
$dataSn = json_decode($rawJson, true);
echo "Jumlah SN terambil: " . count($dataSn['items'] ?? []) . "\n";
if (!empty($dataSn['items'])) {
    foreach ($dataSn['items'] as $it) {
        echo "- SN: {$it['serial_number']} | Status: {$it['status']}\n";
    }
}

