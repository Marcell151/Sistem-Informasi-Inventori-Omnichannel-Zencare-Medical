<?php
require 'config/koneksi.php';
$stmt = $pdo->query("SELECT id, username, nama_lengkap, role FROM users");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['id'] . " | " . $r['username'] . " | " . $r['role'] . " | " . $r['nama_lengkap'] . "\n";
}
