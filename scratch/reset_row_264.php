<?php
require_once __DIR__ . '/../config/koneksi.php';

// Revert 20 pcs to stok_batch and stok_toko
$pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa + 20 WHERE id = 7")->execute();
$pdo->prepare("UPDATE stok_toko SET stok = stok + 20 WHERE id_variasi = 1")->execute();

// Delete test kartu_stok rows
$pdo->prepare("DELETE FROM kartu_stok WHERE id IN (197, 198)")->execute();

// Reset row 264
$pdo->prepare("
    UPDATE detail_opname_batch
    SET status_mutasi = 'Belum Dimutasi',
        alasan_mutasi = NULL,
        keterangan_mutasi = NULL,
        dimutasi_oleh = NULL,
        waktu_mutasi = NULL
    WHERE id = 264
")->execute();

echo "Row 264 reset back to Belum Dimutasi, ready for user demo!\n";

