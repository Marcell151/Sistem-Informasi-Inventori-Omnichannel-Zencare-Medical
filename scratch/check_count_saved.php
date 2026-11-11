<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, '$countSaved');
echo substr($content, $pos + 500, 1000);

