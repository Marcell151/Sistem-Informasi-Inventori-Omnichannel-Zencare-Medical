<?php
$lines = file('inventori/tambah_stok.php');
foreach ($lines as $idx => $line) {
    if (strpos($line, 'type="submit"') !== false) {
        echo ($idx + 1) . ": " . trim($line) . "\n";
    }
}

