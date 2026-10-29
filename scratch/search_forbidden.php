<?php
$dir = new RecursiveDirectoryIterator('.');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$forbidden = ['apotek', 'farmasi', 'apoteker', 'pbf'];
$results = [];

foreach ($files as $file) {
    $filePath = str_replace('\\', '/', $file[0]);
    if (strpos($filePath, '/.git/') !== false || strpos($filePath, '/scratch/') !== false) continue;
    
    $c = file_get_contents($filePath);
    $lines = explode("\n", $c);
    foreach ($lines as $i => $line) {
        $trimmed = trim($line);
        if (strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0 || strpos($trimmed, '/*') === 0) continue;
        
        foreach ($forbidden as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $line)) {
                $results[] = "$filePath:" . ($i + 1) . ": " . $trimmed;
                break;
            }
        }
    }
}

echo "Total potential forbidden occurrences: " . count($results) . "\n";
foreach (array_slice($results, 0, 50) as $res) {
    echo $res . "\n";
}
