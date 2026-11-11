<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');

preg_match_all('/(jenis_komoditas[^\n]+)/', $content, $matches);
foreach ($matches[1] as $m) {
    echo $m . PHP_EOL;
}

