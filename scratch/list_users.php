<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('SELECT id, username, role, is_active FROM users');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "{$u['id']} | {$u['username']} | {$u['role']} | {$u['is_active']}\n";
}
