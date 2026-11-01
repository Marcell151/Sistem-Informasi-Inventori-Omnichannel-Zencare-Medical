<?php
require_once __DIR__ . '/../config/koneksi.php';
$counted = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = 3 AND hitung_fisik IS NOT NULL")->fetchColumn();
echo "Counted rows in session 3: $counted\n";

