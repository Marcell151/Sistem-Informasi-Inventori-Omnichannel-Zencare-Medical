<?php
$files = ['index.php'];
$pattern = '/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

foreach ($files as $file) {
    echo "=== $file ===\n";
    $lines = file($file);
    foreach ($lines as $num => $line) {
        if (preg_match_all($pattern, $line, $matches)) {
            echo "Line " . ($num + 1) . ": " . implode(' ', $matches[0]) . " --> " . trim($line) . "\n";
        }
    }
}
