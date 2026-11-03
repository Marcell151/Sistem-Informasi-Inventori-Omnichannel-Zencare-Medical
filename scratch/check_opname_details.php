<?php
require_once __DIR__ . '/../config/koneksi.php';
$rows = $pdo->query("SELECT status_mutasi, count(*) as c FROM detail_opname_batch WHERE id_sesi = 11 GROUP BY status_mutasi")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
