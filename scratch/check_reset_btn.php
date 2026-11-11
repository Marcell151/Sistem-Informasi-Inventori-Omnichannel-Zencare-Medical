<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'reset_database_demo');
if ($pos !== false) {
    $pos2 = strpos($content, 'reset_database_demo', $pos + 1);
    if ($pos2 !== false) {
        echo substr($content, $pos2 - 200, 500);
    }
}

