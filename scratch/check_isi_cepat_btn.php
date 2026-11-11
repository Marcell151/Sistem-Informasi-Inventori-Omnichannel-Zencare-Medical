<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$pos = strpos($content, 'Isi Cepat Angka Uji Coba');
if ($pos !== false) {
    echo substr($content, $pos - 350, 400);
}

