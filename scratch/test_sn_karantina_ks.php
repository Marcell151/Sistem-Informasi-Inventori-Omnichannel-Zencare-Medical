<?php
require_once __DIR__ . '/../config/koneksi.php';

$pdo->beginTransaction();
try {
    $idVar = 5; // Termometer
    $initStok = (int)$pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
    $initKsCount = (int)$pdo->query("SELECT COUNT(*) FROM kartu_stok WHERE id_variasi = $idVar")->fetchColumn();

    // Create a dummy SN Tersedia
    $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number, status, created_at) VALUES (?, 'TEST-SN-DAMAGED-01', 'Tersedia', NOW())")->execute([$idVar]);
    $snId = $pdo->lastInsertId();

    // Simulate post action
    $oldStatus = 'Tersedia';
    $snVal = 'TEST-SN-DAMAGED-01';
    $catatanKarantina = 'Layar pecah saat inspeksi rak';

    $pdo->prepare("UPDATE unit_serial SET status = 'Retur/Rusak', catatan = ? WHERE id = ?")->execute([$catatanKarantina, $snId]);
    if ($oldStatus === 'Tersedia') {
        $pdo->prepare("UPDATE stok_toko SET stok = GREATEST(0, stok - 1) WHERE id_variasi = ?")->execute([$idVar]);
        $sisaStok = (int)$pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
        $refDoc = "KARANTINA-TEST";
        $desc = "Isolasi Unit Cacat Gudang [SN: $snVal] - $catatanKarantina";

        $pdo->prepare("
            INSERT INTO kartu_stok 
            (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh, tanggal)
            VALUES (?, NULL, 'besar', 'Keluar', 'Manual', 'Barang Rusak', ?, 1, ?, ?, 1, NOW())
        ")->execute([$idVar, $refDoc, $sisaStok, $desc]);
    }

    $afterStok = (int)$pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
    $afterKs = $pdo->query("SELECT id, satuan_tipe, jenis_mutasi, alasan_mutasi, sisa_stok, keterangan FROM kartu_stok WHERE id_variasi = $idVar ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    echo "=== UJI MUTASI KARANTINA SN TERSEDIA ===\n";
    echo "Stok Toko Awal: $initStok -> Setelah Karantina: $afterStok (Berkurang 1)\n";
    echo "Kartu Stok Baru Tercatat:\n";
    print_r($afterKs);

    $pdo->rollBack();
    echo "Rollback sukses.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

