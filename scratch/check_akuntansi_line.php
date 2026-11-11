<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'non-akuntansi');
if ($pos !== false) {
    echo substr($content, $pos - 300, 700);
}

