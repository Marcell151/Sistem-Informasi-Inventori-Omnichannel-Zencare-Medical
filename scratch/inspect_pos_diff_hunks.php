<?php
$c = file_get_contents('scratch/pos_diff.txt');
$lines = explode("\n", $c);
foreach ($lines as $i => $line) {
    if (strpos($line, '@@') === 0) {
        echo "HUNK: " . trim($line) . "\n";
    } elseif (strpos($line, '+') === 0 || strpos($line, '-') === 0) {
        if (stripos($line, 'satuan') !== false || stripos($line, 'scan') !== false || stripos($line, 'needsVerification') !== false || stripos($line, 'verification') !== false || stripos($line, 'konversi') !== false) {
            echo ($i + 1) . ": " . substr($line, 0, 100) . "\n";
        }
    }
}

