<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, '.git') !== false || strpos($path, 'scratch') !== false || strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false) continue;
    if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') continue;
    $content = file_get_contents($path);
    $c = substr_count(strtoupper($content), 'FIFO');
    if ($c > 0) {
        echo "$path: $c occurrences of FIFO\n";
    }
}

