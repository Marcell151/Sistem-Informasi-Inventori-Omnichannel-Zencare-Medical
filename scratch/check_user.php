<?php
require_once __DIR__ . '/../config/koneksi.php';
$u = $pdo->query('SELECT id, nama_lengkap, role FROM users WHERE id = 4')->fetch(PDO::FETCH_ASSOC);
print_r($u);

