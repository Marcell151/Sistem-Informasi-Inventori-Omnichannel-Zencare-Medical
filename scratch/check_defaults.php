<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query('SELECT id, fisik_baik, fisik_rusak, fisik_expired, hitung_fisik, status_hitung FROM detail_opname_batch ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

