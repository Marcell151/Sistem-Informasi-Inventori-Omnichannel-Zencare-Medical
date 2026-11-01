<?php
$content = file_get_contents(__DIR__ . '/../scratch/full_bimbingan_prompt.txt');
$pos = 0;
while (($pos = stripos($content, 'stok opname', $pos)) !== false) {
    echo "=== FOUND AT POS $pos ===\n";
    echo substr($content, max(0, $pos - 200), 1000) . "\n\n";
    $pos += 500;
}

