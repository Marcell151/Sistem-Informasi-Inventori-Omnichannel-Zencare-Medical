<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, nama_produk, deskripsi FROM produk_induk WHERE deskripsi LIKE '%\"%' OR nama_produk LIKE '%\"%' OR deskripsi LIKE '%\n%' OR deskripsi LIKE \"%'%\"");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['id'] . ': ' . $r['nama_produk'] . ' | ' . substr($r['deskripsi'], 0, 50) . PHP_EOL;
}
