<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, 'chartLabels') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($l) . "\n";
    }
}

