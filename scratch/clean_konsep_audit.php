<?php
$f = 'konsep/index.php';
if (file_exists($f)) {
    $c = file_get_contents($f);
    $c = str_replace('Audit Kartu Stok', 'Log Kartu Stok', $c);
    $c = str_replace('Audit Log Feed', 'Log Feed', $c);
    $c = str_replace('AUDIT FEED', 'LOG FEED', $c);
    file_put_contents($f, $c);
    echo "Updated konsep/index.php\n";
}

