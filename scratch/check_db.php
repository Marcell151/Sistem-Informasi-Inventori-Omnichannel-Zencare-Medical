<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
print_r($stmt->fetchAll());

$stmt2 = $pdo->query("SELECT id, username, role, is_active FROM users");
print_r($stmt2->fetchAll());

