<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== unit_serial ===\n";
print_r($pdo->query("DESCRIBE unit_serial")->fetchAll(PDO::FETCH_ASSOC));




