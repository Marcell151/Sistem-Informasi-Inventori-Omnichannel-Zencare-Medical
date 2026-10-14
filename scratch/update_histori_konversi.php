<?php
require_once __DIR__ . '/../config/koneksi.php';

try {
    $cols = $pdo->query("SHOW COLUMNS FROM histori_konversi LIKE 'sisa_dus_akhir'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE histori_konversi ADD COLUMN sisa_dus_akhir INT DEFAULT 0 AFTER qty_pcs_hasil");
        echo "Column sisa_dus_akhir added successfully.\n";
    } else {
        echo "Column sisa_dus_akhir already exists.\n";
    }

    // Populate existing records with sisa stok dus if any are 0
    $rows = $pdo->query("SELECT id, id_variasi, batch_asal FROM histori_konversi WHERE sisa_dus_akhir = 0")->fetchAll();
    foreach ($rows as $r) {
        $sisaDus = $pdo->prepare("SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch WHERE id_variasi = ? AND no_batch = ?");
        $sisaDus->execute([$r['id_variasi'], $r['batch_asal']]);
        $sisaPcs = $sisaDus->fetchColumn();

        $rasio = $pdo->query("SELECT rasio_konversi FROM produk_variasi WHERE id = " . intval($r['id_variasi']))->fetchColumn() ?: 1;
        $boxSisa = floor($sisaPcs / $rasio);

        $pdo->prepare("UPDATE histori_konversi SET sisa_dus_akhir = ? WHERE id = ?")->execute([$boxSisa, $r['id']]);
    }
    echo "Existing histori_konversi records backfilled.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
