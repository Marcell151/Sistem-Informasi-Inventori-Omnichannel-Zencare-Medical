<?php
require 'config/koneksi.php';
$stmt = $pdo->prepare('SELECT * FROM penjualan WHERE no_invoice LIKE "%6112%" LIMIT 1');
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
