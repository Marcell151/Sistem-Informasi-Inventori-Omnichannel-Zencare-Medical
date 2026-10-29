<?php
$files = ['inventori/logistik_medis.php', 'laporan/logistik_medis.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $c = file_get_contents($file);
        $lines = explode("\n", $c);
        foreach ($lines as $idx => $line) {
            if (stripos($line, 'fifo') !== false) {
                echo "$file Line " . ($idx + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

