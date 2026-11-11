<?php
// Function to restore inventory tables to the pristine baseline
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

function restoreBaselineInventory($pdo) {
    $baselineFile = __DIR__ . '/baseline_inventory.json';
    if (!file_exists($baselineFile)) {
        throw new Exception("File baseline tidak ditemukan: $baselineFile");
    }

    $data = json_decode(file_get_contents($baselineFile), true);
    if (!$data) {
        throw new Exception("Format baseline inventory rusak / tidak valid.");
    }

    $pdo->beginTransaction();

    // 1. Hapus sesi opname baru yang dibuat setelah baseline
    $maxSesi = intval($data['max_sesi_opname_id']);
    $pdo->query("DELETE FROM detail_opname_sn WHERE id_detail_opname IN (SELECT id FROM detail_opname_batch WHERE id_sesi > $maxSesi)");
    $pdo->query("DELETE FROM riwayat_hitung_opname WHERE id_detail_opname IN (SELECT id FROM detail_opname_batch WHERE id_sesi > $maxSesi)");
    $pdo->query("DELETE FROM detail_opname_batch WHERE id_sesi > $maxSesi");
    $pdo->query("DELETE FROM sesi_opname WHERE id > $maxSesi");

    // 2. Hapus log kartu_stok baru yang dibuat setelah baseline
    $maxKartu = intval($data['max_kartu_stok_id']);
    $pdo->query("DELETE FROM kartu_stok WHERE id > $maxKartu");

    // 3. Restore stok_batch
    $updBatch = $pdo->prepare("UPDATE stok_batch SET stok_sisa = ?, tgl_exp = ? WHERE id = ?");
    foreach ($data['stok_batch'] as $b) {
        $updBatch->execute([$b['stok_sisa'], $b['tgl_exp'], $b['id']]);
    }

    // 4. Restore stok_toko
    $updStokToko = $pdo->prepare("UPDATE stok_toko SET stok = ? WHERE id = ?");
    foreach ($data['stok_toko'] as $st) {
        $updStokToko->execute([$st['stok'], $st['id']]);
    }

    // 5. Restore unit_serial
    $updSn = $pdo->prepare("UPDATE unit_serial SET status = ?, catatan = ?, id_penjualan = ? WHERE id = ?");
    foreach ($data['unit_serial'] as $sn) {
        $updSn->execute([$sn['status'], $sn['catatan'], $sn['id_penjualan'], $sn['id']]);
    }

    $pdo->commit();
    return true;
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        restoreBaselineInventory($pdo);
        echo "✓ Baseline inventory successfully restored to clean state!\n";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "✗ Restore failed: " . $e->getMessage() . "\n";
    }
}

