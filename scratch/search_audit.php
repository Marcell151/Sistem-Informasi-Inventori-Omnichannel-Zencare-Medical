<?php
$dir = new RecursiveDirectoryIterator('.');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$results = [];
foreach ($files as $file) {
    $filePath = str_replace('\\', '/', $file[0]);
    if (strpos($filePath, '/.git/') !== false || strpos($filePath, '/scratch/') !== false) continue;
    
    $c = file_get_contents($filePath);
    $lines = explode("\n", $c);
    foreach ($lines as $i => $line) {
        $trimmed = trim($line);
        if (strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0 || strpos($trimmed, '/*') === 0) continue;
        
        if (preg_match('/\baudit\b/i', $line)) {
            $results[] = "$filePath:" . ($i + 1) . ": " . $trimmed;
        }
    }
}

echo "Total audit occurrences: " . count($results) . "\n";
foreach ($results as $res) {
    echo $res . "\n";
}

