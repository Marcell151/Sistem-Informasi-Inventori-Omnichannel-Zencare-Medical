<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'name="hitung_baik[');
if ($pos !== false) {
    echo substr($content, $pos - 200, 700);
}

