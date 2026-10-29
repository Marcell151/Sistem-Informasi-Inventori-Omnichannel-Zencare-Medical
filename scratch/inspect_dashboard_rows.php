<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, '<!-- ROW') !== false || strpos($l, '<!-- Row') !== false || strpos($l, 'role ===') !== false || strpos($l, 'salesChart') !== false || strpos($l, 'chart.js') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($l) . "\n";
    }
}

