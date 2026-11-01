<?php
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../config/order_helper.php';

echo "=== CEK KODE PEMBATALAN PESANAN (KADALUARSA / BATAL) ===\n";
$reflector = new ReflectionFunction('batalkanPesananKadaluarsa');
$file = $reflector->getFileName();
$start = $reflector->getStartLine();
$end = $reflector->getEndLine();
echo "Defined in: $file from line $start to $end\n";

$lines = file($file);
for ($i = $start - 1; $i < $end; $i++) {
    echo ($i + 1) . ": " . $lines[$i];
}

