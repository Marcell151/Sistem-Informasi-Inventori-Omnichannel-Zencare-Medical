<?php
require 'config/koneksi.php';

try {
    $pdo->beginTransaction();

    // 1. Buat sesi uji coba
    $stmtSesi = $pdo->prepare("INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, catatan) VALUES (?, 'Aktif', 1, NOW(), 'Test Auto Rollback')");
    $kodeTest = 'OPN-TEST-' . time();
    $stmtSesi->execute([$kodeTest]);
    $idSesi = $pdo->lastInsertId();

    // Ambil 1 produk variasi obat untuk ditest
    $testItem = $pdo->query("SELECT sb.*, pv.id AS var_id, st.stok AS stok_toko FROM stok_batch sb JOIN produk_variasi pv ON sb.id_variasi = pv.id JOIN stok_toko st ON pv.id = st.id_variasi WHERE sb.stok_sisa > 5 LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $idBatch = $testItem['id'];
    $idVariasi = $testItem['var_id'];
    $stokAwalBatch = $testItem['stok_sisa'];
    $stokAwalToko = $testItem['stok_toko'];

    // Insert detail opname
    $stmtDetail = $pdo->prepare("
        INSERT INTO detail_opname_batch (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi, alasan_mutasi)
        VALUES (?, ?, ?, 'Obat', 'kecil', 'Pcs', ?, 0, ?, ?, ?, 'Belum Dimutasi', NULL)
    ");
    $hitungFisik = $stokAwalBatch - 2; // Kurang 2
    $selisih = -2;
    $stmtDetail->execute([$idSesi, $idBatch, $idVariasi, $stokAwalBatch, $stokAwalBatch, $hitungFisik, $selisih]);
    $idDetail = $pdo->lastInsertId();

    // Eksekusi mutasi (kurangi 2)
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - 2 WHERE id = ?")->execute([$idBatch]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok - 2 WHERE id_variasi = ?")->execute([$idVariasi]);
    $pdo->prepare("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Rusak' WHERE id = ?")->execute([$idDetail]);

    // Verifikasi stok terpotong
    $stokSetelahMutasi = $pdo->query("SELECT stok_sisa FROM stok_batch WHERE id = $idBatch")->fetchColumn();
    assert($stokSetelahMutasi == ($stokAwalBatch - 2), "Stok harus terpotong 2");
    echo "[PASS] Mutasi awal berhasil memotong stok.\n";

    // Simulasi batalkan_sesi dengan auto rollback
    // 1. Cek baris yang sudah dimutasi
    $stmtMutated = $pdo->prepare("
        SELECT dob.*, pv.nama_variasi, pi.nama_produk, sb.no_batch
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
        WHERE dob.id_sesi = ? AND dob.status_mutasi = 'Sudah Dimutasi'
    ");
    $stmtMutated->execute([$idSesi]);
    $itemsToRollback = $stmtMutated->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itemsToRollback as $mIt) {
        $sel = intval($mIt['selisih']);
        $absSel = abs($sel);
        $reverseOp = ($sel < 0) ? '+' : '-';
        $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $reverseOp ? WHERE id = ?")->execute([$absSel, $mIt['id_batch']]);
        $pdo->prepare("UPDATE stok_toko SET stok = stok $reverseOp ? WHERE id_variasi = ?")->execute([$absSel, $mIt['id_variasi']]);
        $pdo->prepare("UPDATE detail_opname_batch SET status_mutasi = 'Belum Dimutasi' WHERE id = ?")->execute([$mIt['id']]);
    }

    $stokSetelahRollback = $pdo->query("SELECT stok_sisa FROM stok_batch WHERE id = $idBatch")->fetchColumn();
    assert($stokSetelahRollback == $stokAwalBatch, "Stok setelah rollback harus sama dengan stok awal!");
    echo "[PASS] Auto-Rollback berhasil mengembalikan stok ke nilai awal ({$stokAwalBatch})!\n";

    // Rollback test transaction so DB remains pristine
    $pdo->rollBack();
    echo "[PASS] Test transaction rolled back. Database is 100% pristine.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "[FAIL] " . $e->getMessage() . "\n";
}

