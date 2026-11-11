<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');

$pos = 0;
while (($pos = strpos($content, 'foreach ($groupedDetailItems', $pos)) !== false) {
    echo "Line: " . substr_count(substr($content, 0, $pos), "\n") . PHP_EOL;
    echo substr($content, $pos, 600) . PHP_EOL . "---" . PHP_EOL;
    $pos += 30;
}

