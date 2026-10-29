<?php
$files = ['inventori/logistik_medis.php', 'laporan/logistik_medis.php'];
foreach ($files as $f) {
    $c = file_get_contents($f);
    $c = str_replace('Terdaftar (FIFO)', 'Waktu Masuk / Registrasi', $c);
    $c = str_replace('diurutkan FIFO', 'diurutkan urutan masuk', $c);
    $c = str_replace('(FIFO: barang terdaftar paling lama ke paling baru)', '(Urutan barang terdaftar paling lama ke paling baru)', $c);
    file_put_contents($f, $c);
    echo "Replaced FIFO in $f\n";
}

