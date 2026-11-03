<?php
require __DIR__ . '/../config/koneksi.php';
$deleted = $pdo->exec("DELETE FROM detail_opname_batch WHERE stok_sistem_fisik <= 0 AND hitung_fisik IS NULL");
echo "Deleted $deleted uncounted 0-stock rows from detail_opname_batch.\n";

