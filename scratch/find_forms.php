<?php
$file = file_get_contents(__DIR__ . '/../admin/master_produk.php');
$lines = explode("\n", $file);
foreach ($lines as $num => $line) {
    if (stripos($line, '<form') !== false || stripos($line, '</form') !== false || stripos($line, 'toggle_induk') !== false || stripos($line, 'toggle_variasi') !== false) {
        echo ($num + 1) . ': ' . trim($line) . PHP_EOL;
    }
}
