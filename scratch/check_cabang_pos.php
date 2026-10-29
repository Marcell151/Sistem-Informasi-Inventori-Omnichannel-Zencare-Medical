<?php
$c = file_get_contents('pos/pos.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, 'cabang') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($l) . "\n";
    }
}

