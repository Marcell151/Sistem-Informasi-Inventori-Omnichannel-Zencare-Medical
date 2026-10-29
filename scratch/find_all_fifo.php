<?php
$dir = new RecursiveDirectoryIterator('.');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$found = 0;
foreach ($files as $file) {
    $filePath = str_replace('\\', '/', $file[0]);
    if (strpos($filePath, '/.git/') !== false || strpos($filePath, '/scratch/') !== false) continue;
    
    $c = file_get_contents($filePath);
    $lines = explode("\n", $c);
    foreach ($lines as $i => $line) {
        if (preg_match('/\bfifo\b/i', $line)) {
            echo "$filePath:" . ($i + 1) . ": " . trim($line) . "\n";
            $found++;
        }
    }
}
echo "Total FIFO occurrences: $found\n";

