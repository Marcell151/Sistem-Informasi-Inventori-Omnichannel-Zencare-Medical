<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');

$pos = strpos($content, 'reset_database_demo');
if ($pos !== false) {
    echo "reset_database_demo at $pos:\n";
    echo substr($content, $pos - 100, 300) . PHP_EOL;
} else {
    echo "reset_database_demo not found.\n";
}

$pos2 = strpos($content, 'estLossRusak');
if ($pos2 !== false) {
    echo "\nestLossRusak at $pos2:\n";
    echo substr($content, $pos2 - 150, 400) . PHP_EOL;
} else {
    echo "estLossRusak not found.\n";
}

