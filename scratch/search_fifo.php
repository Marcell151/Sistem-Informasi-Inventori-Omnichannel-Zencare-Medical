<?php
$files = ['index.php', 'pos/pos.php', 'admin/pesanan.php'];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $c = substr_count(strtoupper($content), 'FIFO');
    echo $f . ': ' . $c . ' occurrences of FIFO' . PHP_EOL;
}

