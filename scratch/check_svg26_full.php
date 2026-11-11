<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
echo substr($content, 137800, 800);

