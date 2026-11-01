<?php
require_once __DIR__ . '/../config/koneksi.php';
$rows = $pdo->query('SELECT id, username, nama_lengkap, role FROM users')->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

