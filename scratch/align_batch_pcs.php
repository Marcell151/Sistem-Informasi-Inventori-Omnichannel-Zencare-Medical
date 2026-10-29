<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$pdo->beginTransaction();

try {
    // 1. Ambil semua batch non-sub-batch pada produk multi-UOM
    $stmt = $pdo->query("
        SELECT sb.id, sb.id_variasi, sb.no_batch, sb.stok_sisa, pv.rasio_konversi, st.stok as stok_toko
        FROM stok_batch sb
        JOIN produk_variasi pv ON sb.id_variasi = pv.id
        LEFT JOIN stok_toko st ON st.id_variasi = pv.id
        WHERE pv.rasio_konversi > 1 AND sb.no_batch NOT LIKE '%.%'
    ");
    $batches = $stmt->fetchAll();

    foreach ($batches as $b) {
        $rasio = (int)$b['rasio_konversi'];
        $current = (int)$b['stok_sisa'];
        $toko = (int)$b['stok_toko'];

        // Cek jika nilai stok_sisa saat ini adalah representasi DUS (yaitu current * rasio <= toko + 50)
        // dan jika dikalikan rasio akan mencerminkan stok fisik satuan kecil yang sesungguhnya
        if ($current > 0 && ($current * $rasio <= $toko + 100)) {
            $newStokSisa = $current * $rasio;
            $upd = $pdo->prepare("UPDATE stok_batch SET stok_sisa = ? WHERE id = ?");
            $upd->execute([$newStokSisa, $b['id']]);
            echo "Updated batch {$b['no_batch']} (Var {$b['id_variasi']}): {$current} Dus -> {$newStokSisa} Pcs\n";
        }
    }

    $pdo->commit();
    echo "\nAll batch scales successfully aligned to PCS (Satuan Kecil)!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

