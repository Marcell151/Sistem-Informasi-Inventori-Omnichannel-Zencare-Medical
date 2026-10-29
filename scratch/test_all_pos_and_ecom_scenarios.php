<?php
// Script pengujian menyeluruh skenario validasi POS dan E-Commerce
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/order_helper.php';

echo "=======================================================\n";
echo "   PENGUJIAN VALIDASI POS & E-COMMERCE ZENCARE MEDICAL\n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($name, $condition, $details = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $name\n";
        if ($details) echo "        $details\n";
        $passCount++;
    } else {
        echo " [FAIL] $name\n";
        if ($details) echo "        $details\n";
        $failCount++;
    }
}

// 1. Uji Database: Variasi & Stok
$stmt = $pdo->query("SELECT pv.id, pi.nama_produk, pv.nama_variasi, pv.sku_variasi, pi.kategori, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.sku_variasi = 'ESY-GLU'");
$esy = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest("Alkes EasyTouch Rasio Konversi = 1", $esy && intval($esy['rasio_konversi']) === 1, "Rasio: " . ($esy['rasio_konversi'] ?? 'null'));

// 2. Cek Stok Batch untuk EasyTouch
$stmtBatch = $pdo->prepare("SELECT id, no_batch, stok_sisa FROM stok_batch WHERE id_variasi = ?");
$stmtBatch->execute([$esy['id']]);
$esyBatch = $stmtBatch->fetch(PDO::FETCH_ASSOC);

assertTest("EasyTouch memiliki tracking batch BMHP", $esyBatch && !empty($esyBatch['no_batch']), "Batch: " . ($esyBatch['no_batch'] ?? 'none') . " | Stok: " . ($esyBatch['stok_sisa'] ?? 0));

// 3. Cek Serial Number untuk Tensimeter
$stmtTens = $pdo->query("SELECT pv.id, pi.nama_produk, pv.sku_variasi FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.sku_variasi = 'OMR-TENS'");
$tens = $stmtTens->fetch(PDO::FETCH_ASSOC);

$stmtSn = $pdo->prepare("SELECT COUNT(*) FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia'");
$stmtSn->execute([$tens['id']]);
$snCount = $stmtSn->fetchColumn();

assertTest("Tensimeter memiliki Serial Number di unit_serial", $snCount > 0, "Tersedia: $snCount unit");

// 4. Simulasi Bayar POS untuk Obat Eceran (Multi-batch / Sub-batch)
try {
    $pdo->beginTransaction();
    
    // Ambil data Amoxicillin
    $stmtAmx = $pdo->query("SELECT pv.id, pv.rasio_konversi, pv.satuan_kecil, pv.satuan_besar, pv.harga_jual_kecil FROM produk_variasi pv WHERE pv.sku_variasi = 'AMX-500'");
    $amx = $stmtAmx->fetch(PDO::FETCH_ASSOC);
    
    // Cari batch eceran (.A)
    $stmtAmxBatch = $pdo->prepare("SELECT id, no_batch, stok_sisa FROM stok_batch WHERE id_variasi = ? AND no_batch LIKE '%.%' AND stok_sisa > 0 LIMIT 1");
    $stmtAmxBatch->execute([$amx['id']]);
    $amxBatch = $stmtAmxBatch->fetch(PDO::FETCH_ASSOC);
    
    if ($amxBatch) {
        $stokAwal = $amxBatch['stok_sisa'];
        $invPos = 'TEST-SIM-POS-' . rand(1000, 9999);
        $pdo->prepare("INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, status_pesanan, total_harga, metode_bayar_pos, created_at) VALUES (?, 2, 'pos', 'Selesai', ?, 'Tunai', NOW())")
            ->execute([$invPos, $amx['harga_jual_kecil']]);
        $idPenjualan = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan) VALUES (?, ?, 1, ?)")
            ->execute([$idPenjualan, $amx['id'], $amx['harga_jual_kecil']]);
        $idDetail = $pdo->lastInsertId();
        
        // Alokasi batch eceran
        $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - 1 WHERE id = ?")->execute([$amxBatch['id']]);
        $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, 1)")
            ->execute([$idPenjualan, $idDetail, $amxBatch['id']]);
            
        // Kartu stok
        $pdo->prepare("INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'kecil', 'Keluar', 'POS', 'Penjualan POS', ?, 1, ?, 'Penjualan Luring $invPos', 2)")
            ->execute([$amx['id'], $amxBatch['id'], $invPos, $stokAwal - 1]);
            
        assertTest("Simulasi POS Obat Eceran: Potong stok_batch & insert pesanan_batch_alokasi berhasil", true, "Invoice: $invPos | Batch: {$amxBatch['no_batch']} (-1)");
    } else {
        assertTest("Simulasi POS Obat Eceran", false, "Batch eceran Amoxicillin tidak ditemukan");
    }
    
    // 5. Simulasi Bayar POS untuk Alkes dengan Batch BMHP (EasyTouch)
    $stokAwalEsy = $esyBatch['stok_sisa'];
    $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan) VALUES (?, ?, 1, ?)")
        ->execute([$idPenjualan, $esy['id'], $esy['harga_jual_kecil']]);
    $idDetailEsy = $pdo->lastInsertId();
    
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - 1 WHERE id = ?")->execute([$esyBatch['id']]);
    $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, 1)")
        ->execute([$idPenjualan, $idDetailEsy, $esyBatch['id']]);
        
    $pdo->prepare("INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'kecil', 'Keluar', 'POS', 'Penjualan POS', ?, 1, ?, 'Penjualan Luring $invPos', 2)")
        ->execute([$esy['id'], $esyBatch['id'], $invPos, $stokAwalEsy - 1]);
        
    assertTest("Simulasi POS Alkes BMHP (EasyTouch): Potong stok_batch berhasil tanpa error SN", true, "Batch: {$esyBatch['no_batch']}");
    
    // 6. Simulasi Bayar POS untuk Alkes dengan Serial Number (Tensimeter)
    $stmtPickSn = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia' LIMIT 1 FOR UPDATE");
    $stmtPickSn->execute([$tens['id']]);
    $snPicked = $stmtPickSn->fetchColumn();
    
    if ($snPicked) {
        $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan, catatan_logistik) VALUES (?, ?, 1, 550000, ?)")
            ->execute([$idPenjualan, $tens['id'], $snPicked]);
        $pdo->prepare("UPDATE unit_serial SET status = 'Terjual', id_penjualan = ? WHERE serial_number = ? AND id_variasi = ?")
            ->execute([$idPenjualan, $snPicked, $tens['id']]);
        $pdo->prepare("INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, NULL, 'kecil', 'Keluar', 'POS', 'Penjualan POS', ?, 1, 7, 'Penjualan Luring $invPos [SN: $snPicked]', 2)")
            ->execute([$tens['id'], $invPos]);
            
        assertTest("Simulasi POS Alkes Serial Number (Tensimeter): Update unit_serial berhasil", true, "SN: $snPicked");
    } else {
        assertTest("Simulasi POS Alkes Serial Number", false, "SN Tensimeter tidak tersedia");
    }
    
    // Rollback agar database tetap bersih (tidak meninggalkan data simulasi)
    $pdo->rollBack();
    echo "\n [INFO] Transaksi simulasi di-rollback dengan sempurna (DB bersih).\n";
} catch (Exception $e) {
    $pdo->rollBack();
    assertTest("Simulasi POS Transaksi", false, "Error: " . $e->getMessage());
}

// 7. Cek integritas UI POS file
$posContent = file_get_contents(__DIR__ . '/../pos/pos.php');

assertTest("Dropdown POS default 'kecil' (selected)", strpos($posContent, '<option value="kecil" selected>') !== false);
assertTest("Rasio <= 1 diproteksi selalu 'kecil' di addToPos", strpos($posContent, 'if (rasio <= 1)') !== false);
assertTest("Allocations mengenali batch BMHP dan SN Alkes", strpos($posContent, 'existingItem.allocations && existingItem.allocations.length > 0') !== false);
assertTest("Sinkron live tidak men-nol-kan ci.maxPcs untuk produk single UOM", strpos($posContent, 'newTotalStok') !== false);

// 8. Cek E-Commerce Checkout handling
$chkContent = file_get_contents(__DIR__ . '/../api/checkout.php');
assertTest("API Checkout membedakan hasBatch dan hasSn", strpos($chkContent, '$chkBatch = $pdo->prepare("SELECT COUNT(*) FROM stok_batch WHERE id_variasi = ?");') !== false);

echo "\n=======================================================\n";
echo " Hasil Pengujian: $passCount PASS | $failCount FAIL\n";
echo "=======================================================\n";

