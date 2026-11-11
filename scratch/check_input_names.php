<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'id="input_baik_');
if ($pos !== false) {
    echo substr($content, $pos - 150, 600);
}

