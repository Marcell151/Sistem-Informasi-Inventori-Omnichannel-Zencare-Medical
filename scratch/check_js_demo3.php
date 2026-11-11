<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'function isiOtomatisAngkaDemo');
if ($pos !== false) {
    echo substr($content, $pos + 3000, 1500);
}

