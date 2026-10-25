<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, username, nama_lengkap, role, is_active FROM users");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['id'] . ' | ' . $r['username'] . ' | ' . $r['nama_lengkap'] . ' | ' . $r['role'] . ' | is_active: ' . $r['is_active'] . PHP_EOL;
}
