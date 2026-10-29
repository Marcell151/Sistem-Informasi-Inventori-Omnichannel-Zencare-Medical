<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
for ($i = 450; $i < 580; $i++) {
    if (isset($lines[$i])) {
        if (strpos($lines[$i], 'Log Kartu Stok') !== false || strpos($lines[$i], 'kartuTerbaru') !== false || strpos($lines[$i], 'foreach') !== false || strpos($lines[$i], 'table') !== false) {
            echo ($i + 1) . ': ' . trim($lines[$i]) . "\n";
        }
    }
}

