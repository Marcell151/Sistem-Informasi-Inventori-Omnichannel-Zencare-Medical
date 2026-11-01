<?php
require __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('SELECT id, username, nama_lengkap, role FROM users');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | User: {$r['username']} | Role: {$r['role']} | Nama: {$r['nama_lengkap']}\n";
}

