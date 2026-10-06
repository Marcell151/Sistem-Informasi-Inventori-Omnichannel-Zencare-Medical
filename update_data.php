<?php
require_once __DIR__ . '/config/koneksi.php';
try {
    // Karantina usually eceran or besar, let's just say it's eceran for now
    $pdo->exec("UPDATE kartu_stok SET satuan_tipe = 'besar' WHERE keterangan LIKE '%STOK AWAL%' OR keterangan LIKE '%Penerimaan%' OR keterangan LIKE '%Grosir%' OR keterangan LIKE '%Bongkar Dus%' OR alasan_mutasi = 'Bongkar Dus'");
    $pdo->exec("UPDATE kartu_stok SET satuan_tipe = 'kecil' WHERE keterangan LIKE '%Hasil Konversi%' OR keterangan LIKE '%Eceran%' OR keterangan LIKE '%E-Commerce%' OR keterangan LIKE '%Luring%' OR alasan_mutasi = 'Hasil Bongkar'");
    echo "Success updating data";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
