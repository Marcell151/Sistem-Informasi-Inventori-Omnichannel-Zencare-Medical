<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
preg_match_all('/\$aksi\s*===\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $content, $matches);
echo "Actions found:\n";
foreach (array_unique($matches[1]) as $act) {
    echo "- " . $act . PHP_EOL;
}

