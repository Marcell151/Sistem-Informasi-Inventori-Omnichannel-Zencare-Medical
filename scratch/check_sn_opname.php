<?php
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("DESCRIBE detail_opname_sn")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

$snHist = $pdo->query("SELECT * FROM detail_opname_sn LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "Samples in detail_opname_sn:\n";
print_r($snHist);

