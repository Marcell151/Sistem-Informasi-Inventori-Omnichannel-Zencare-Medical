<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();
$stmt = $pdo->query("SELECT id, kode_sesi, status, tahap FROM sesi_opname ORDER BY id DESC LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . PHP_EOL;
}
