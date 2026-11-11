<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
echo substr($content, 95300, 1100);

