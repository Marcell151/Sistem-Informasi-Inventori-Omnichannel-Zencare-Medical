<?php
require __DIR__ . '/../config/koneksi.php';
$rows = $pdo->query("SELECT id, id_batch, stok_sistem_fisik, hitung_fisik FROM detail_opname_batch WHERE id_sesi = 11 AND stok_sistem_fisik <= 0")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
