<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'id="modal_mutasi"');
echo substr($content, $pos + 5000, 3500);

