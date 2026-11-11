<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, '$stmtVal = $pdo->prepare');
echo substr($content, $pos - 50, 900);

