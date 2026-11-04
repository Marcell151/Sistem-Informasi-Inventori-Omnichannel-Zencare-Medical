<?php
require_once __DIR__ . '/../config/koneksi.php';

$cols = $pdo->query("SHOW COLUMNS FROM penjualan")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if (preg_match('/bayar|status|batal|midtrans|tipe/i', $c['Field'])) {
        echo "{$c['Field']} ({$c['Type']})\n";
    }
}

