<?php
$lines = file('index.php');
foreach ($lines as $n => $l) {
    if (stripos($l, 'ADMIN DASHBOARD') !== false || stripos($l, 'Row 1: Metrics Admin') !== false) {
        echo ($n + 1) . ': ' . trim($l) . "\n";
    }
}

