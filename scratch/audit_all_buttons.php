<?php
// Audit all buttons and forms across the project
$dirs = ['admin', 'inventori', 'laporan', 'pos', 'ecommerce'];
$results = [];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/../' . $dir;
    if (!is_dir($path)) continue;
    $files = glob($path . '/*.php');
    foreach ($files as $f) {
        $content = file_get_contents($f);
        $lines = explode("\n", $content);
        $relName = $dir . '/' . basename($f);

        foreach ($lines as $lineNum => $line) {
            $n = $lineNum + 1;
            // 1. Check stopPropagation on buttons
            if (preg_match('/<button[^>]*stopPropagation/i', $line)) {
                $results[] = "[STOP_PROPAGATION ON BUTTON] $relName:$n -> " . trim($line);
            }
            // 2. Check broken onclick or unescaped quotes
            if (preg_match('/onclick=[\'"][^\'"]*addslashes[^\'"]*[\'"]/i', $line)) {
                $results[] = "[POTENTIAL QUOTE BREAK] $relName:$n -> " . trim($line);
            }
            // 3. Check native confirm in onsubmit or onclick
            if (preg_match('/confirm\([^\)]*\)/i', $line)) {
                $results[] = "[CONFIRM USAGE] $relName:$n -> " . trim($line);
            }
        }
    }
}

foreach ($results as $r) {
    echo $r . PHP_EOL;
}
