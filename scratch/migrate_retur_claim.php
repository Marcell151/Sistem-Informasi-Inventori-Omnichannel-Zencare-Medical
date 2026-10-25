<?php
require_once __DIR__ . '/../config/koneksi.php';

try {
    // 1. Cek apakah kolom status_retur sudah ada
    $cols = $pdo->query("SHOW COLUMNS FROM penerimaan_detail LIKE 'status_retur'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE penerimaan_detail 
            ADD COLUMN status_retur VARCHAR(30) NOT NULL DEFAULT 'tidak_ada' AFTER tgl_exp,
            ADD COLUMN qty_pengganti_diterima INT NOT NULL DEFAULT 0 AFTER status_retur,
            ADD COLUMN no_batch_pengganti VARCHAR(50) NULL DEFAULT NULL AFTER qty_pengganti_diterima,
            ADD COLUMN tgl_exp_pengganti DATE NULL DEFAULT NULL AFTER no_batch_pengganti,
            ADD COLUMN tgl_selesai_retur DATETIME NULL DEFAULT NULL AFTER tgl_exp_pengganti,
            ADD COLUMN catatan_retur TEXT NULL DEFAULT NULL AFTER tgl_selesai_retur");
        echo "Columns for retur claim successfully added to penerimaan_detail.\n";
    } else {
        echo "Columns for retur claim already exist.\n";
    }

    // 2. Set status_retur untuk row yang sudah ada dengan qty_rusak > 0
    $pdo->exec("UPDATE penerimaan_detail 
        SET status_retur = 'menunggu_pengganti' 
        WHERE qty_rusak > 0 AND status_retur = 'tidak_ada'");
    
    // 3. Verifikasi
    $stmt = $pdo->query("SELECT id, id_penerimaan, id_variasi, qty_pesan, qty_terima, qty_rusak, status_retur FROM penerimaan_detail WHERE qty_rusak > 0");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Existing retur items verified:\n";
    print_r($res);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
