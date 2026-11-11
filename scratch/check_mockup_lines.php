<?php
$content = file_get_contents(__DIR__ . '/../inventori/dokumentasi_testing.php');
$lines = explode("\n", $content);
foreach ($lines as $no => $l) {
    if (strpos($l, 'localhost') !== false) {
        echo "Line " . ($no + 1) . ":\n";
        for ($i = max(0, $no - 8); $i <= min(count($lines) - 1, $no + 8); $i++) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
        echo "---\n";
    }
}

