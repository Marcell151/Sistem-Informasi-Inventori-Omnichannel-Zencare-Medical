<?php
$files = [
    'inventori/stok_opname.php',
    'inventori/dokumentasi_testing.php',
    'scratch/run_full_opname_simulation.php'
];

$hasError = false;
foreach ($files as $f) {
    $fullPath = __DIR__ . '/../' . $f;
    if (!file_exists($fullPath)) continue;
    $content = file_get_contents($fullPath);

    // Check farmasi / apotek
    if (preg_match('/farmasi|apotek/i', $content, $m)) {
        echo "[ERROR] Found forbidden word '{$m[0]}' in $f\n";
        $hasError = true;
    }

    // Check emojis
    if (preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content, $m)) {
        echo "[ERROR] Found emoji in $f\n";
        $hasError = true;
    }
}

if (!$hasError) {
    echo "✓ Audit PASSED: 0 forbidden words, 0 emojis across all core opname files.\n";
}

