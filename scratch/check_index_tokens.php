<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $line) {
    if (strpos($line, 'openAnomaliModal') !== false || strpos($line, 'openReturModal') !== false || strpos($line, 'PANEL ANOMALI FEFO') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
