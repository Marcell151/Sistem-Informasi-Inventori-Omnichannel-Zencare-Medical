<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("DESCRIBE users");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

$stmt = $pdo->query("SELECT * FROM users");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers:\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Username: {$r['username']} | Nama: {$r['nama_lengkap']} | Role: {$r['role']}\n";
}
