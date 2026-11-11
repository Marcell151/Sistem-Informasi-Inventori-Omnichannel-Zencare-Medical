<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('DESCRIBE sesi_opname');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
