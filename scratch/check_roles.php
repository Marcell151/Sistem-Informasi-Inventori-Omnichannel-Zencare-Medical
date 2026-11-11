<?php
require_once __DIR__ . '/../config/db.php';
// Let's inspect session & role definitions in stok_opname.php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
preg_match_all('/(\$(?:role|isSuperAdmin|canVerify|isAdmin)[a-zA-Z0-9_]*\s*=\s*[^;]+;)/', $content, $matches);
foreach ($matches[0] as $m) {
    echo $m . PHP_EOL;
}

