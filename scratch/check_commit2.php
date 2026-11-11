<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'simpan_semua_hitung_fisik');
$pos_commit = strpos($content, '$pdo->commit();', $pos);
echo substr($content, $pos_commit - 300, 500);

