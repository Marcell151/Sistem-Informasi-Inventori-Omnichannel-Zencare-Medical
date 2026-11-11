<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');

$pos = strpos($content, 'Isi Cepat Angka Uji Coba');
if ($pos !== false) {
    echo "Button context:\n";
    echo substr($content, $pos - 200, 500) . PHP_EOL;
}

$pos2 = strpos($content, 'function isiCepatAngkaDemo');
if ($pos2 !== false) {
    echo "\nFunction context:\n";
    echo substr($content, $pos2 - 100, 600) . PHP_EOL;
} else {
    echo "\nFunction isiCepatAngkaDemo not found by name. Searching for click handler...\n";
}

