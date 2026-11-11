<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$lines = explode("\n", $content);
foreach ($lines as $no => $l) {
    if (strpos($l, 'Alkes (SN)') !== false) {
        echo "Line " . ($no + 1) . ":\n";
        for ($i = max(0, $no - 5); $i <= min(count($lines) - 1, $no + 5); $i++) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
        echo "---\n";
    }
}

