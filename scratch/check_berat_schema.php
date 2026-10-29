<?php
require 'config/koneksi.php';
$cols = $pdo->query('DESCRIBE produk_variasi')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if (stripos($c['Field'], 'berat') !== false || stripos($c['Field'], 'gram') !== false) {
        echo "Found column: {$c['Field']} ({$c['Type']})\n";
    }
}

// Sample products and their berat
$samples = $pdo->query('SELECT id, nama_variasi, berat FROM produk_variasi LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
echo "\nSample product variasi berat:\n";
foreach ($samples as $s) {
    echo "ID {$s['id']}: {$s['nama_variasi']} => Berat: {$s['berat']} gram\n";
}

