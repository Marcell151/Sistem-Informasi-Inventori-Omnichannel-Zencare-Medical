<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'id="modal_mutasi"');
$pos_end = strpos($content, '</form>', $pos);
echo substr($content, $pos, $pos_end - $pos + 10);

