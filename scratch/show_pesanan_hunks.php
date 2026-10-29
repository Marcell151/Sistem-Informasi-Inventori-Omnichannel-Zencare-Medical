<?php
$c = file_get_contents('scratch/pesanan_diff.txt');
$hunks = explode('@@', $c);
array_shift($hunks);
for ($i = 0; $i < count($hunks); $i += 2) {
    $header = $hunks[$i];
    $body = $hunks[$i + 1] ?? '';
    echo "=== HUNK $header ===\n";
    $lines = explode("\n", $body);
    foreach ($lines as $line) {
        if (strpos($line, '+') === 0 || strpos($line, '-') === 0) {
            echo substr($line, 0, 100) . "\n";
        }
    }
}

