<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/..');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$errors = [];
$total = 0;

foreach ($files as $file) {
    $f = $file[0];
    if (strpos($f, 'vendor') !== false) continue;
    $total++;
    $out = [];
    $ret = 0;
    exec("C:\\xampp\\php\\php.exe -l " . escapeshellarg($f) . " 2>&1", $out, $ret);
    if ($ret !== 0) {
        $errors[$f] = implode("\n", $out);
    }
}

echo "Total PHP files scanned: $total\n";
if (empty($errors)) {
    echo "SUCCESS: 100% of PHP files passed with ZERO syntax errors!\n";
} else {
    echo "FOUND " . count($errors) . " ERRORS:\n";
    foreach ($errors as $path => $err) {
        echo "File: $path\n$err\n---\n";
    }
}

