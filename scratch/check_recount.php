<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');

if (strpos($content, 'function openModalRecount') !== false) {
    echo "openModalRecount exists in JS\n";
}
if (strpos($content, 'id="modal_recount"') !== false) {
    echo "modal_recount HTML exists\n";
}
if (strpos($content, 'hitung_ulang_baris') !== false) {
    echo "hitung_ulang_baris PHP exists\n";
}

