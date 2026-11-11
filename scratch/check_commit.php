<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, '$pdo->commit();');
echo substr($content, $pos - 400, 600);

