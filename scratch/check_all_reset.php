<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$matches = [];
$offset = 0;
while (($pos = strpos($content, 'reset_database_demo', $offset)) !== false) {
    echo "Found at $pos:\n" . substr($content, max(0, $pos - 150), 300) . "\n---\n";
    $offset = $pos + 10;
}

