<?php
require 'config/koneksi.php';

try {
    $pdo->beginTransaction();

    // Cek item EasyTouch di sesi 3
    $detail = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = 5 AND id_sesi = 3")->fetch(PDO::FETCH_ASSOC);
    if (!$detail) {
        throw new Exception("Detail 5 tidak ditemukan");
    }

    echo "Status Mutasi saat ini: {$detail['status_mutasi']}\n";
    if ($detail['status_mutasi'] === 'Sudah Dimutasi') {
        // Rollback stok_batch
        $pdo->query("UPDATE stok_batch SET stok_sisa = stok_sisa + 10 WHERE id = {$detail['id_batch']}");
        // Rollback stok_toko
        $pdo->query("UPDATE stok_toko SET stok = stok + 10 WHERE id_variasi = {$detail['id_variasi']}");
        
        $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$detail['id_variasi']}")->fetchColumn();

        // Insert reversal kartu_stok
        $stmtKS = $pdo->prepare("
            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
            VALUES (?, ?, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', 'OPN-20261029-999', 10, ?, '[Rollback] Pembatalan Sesi Opname OPN-20261029-999: Pengembalian stok selisih yang dibatalkan', 1)
        ");
        $stmtKS->execute([$detail['id_variasi'], $detail['id_batch'], $sisaStok]);

        // Reset detail opname
        $pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Belum Dimutasi', alasan_mutasi = NULL WHERE id = 5");

        echo "[SUCCESS] Rollback berhasil! Stok EasyTouch kembali menjadi {$sisaStok}.\n";
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "[ERROR] " . $e->getMessage() . "\n";
}

