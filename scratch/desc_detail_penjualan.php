<?php
require 'config/koneksi.php';
$cols = $pdo->query('DESCRIBE detail_penjualan')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

