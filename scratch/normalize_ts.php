<?php
$f = 'inventori/tambah_stok.php';
$c = file_get_contents($f);
$c = str_replace("\r\n", "\n", $c);
file_put_contents($f, $c);
echo "Normalized $f to LF.\n";

$f2 = 'laporan/penerimaan_barang.php';
$c2 = file_get_contents($f2);
$c2 = str_replace("\r\n", "\n", $c2);
file_put_contents($f2, $c2);
echo "Normalized $f2 to LF.\n";
