<?php
$files = ['inventori/stok_opname.php', 'inventori/dokumentasi_testing.php', 'scratch/run_full_opname_simulation.php'];
$clean = true;
foreach ($files as $f) {
    $content = file_get_contents($f);
    if (stripos($content, 'farmasi') !== false) {
        echo "Found 'farmasi' in $f\n";
        $clean = false;
    }
    if (stripos($content, 'apotek') !== false) {
        echo "Found 'apotek' in $f\n";
        $clean = false;
    }
}
if ($clean) {
    echo "CLEAN: 0 forbidden words found.\n";
}
