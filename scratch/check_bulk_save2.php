<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'simpan_semua_hitung_fisik');
echo substr($content, $pos + 2000, 1500);

