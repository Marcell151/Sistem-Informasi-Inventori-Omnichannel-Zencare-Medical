<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

// Check current active session 28
$stmt = $pdo->query("SELECT * FROM sesi_opname WHERE status = 'Aktif' LIMIT 1");
$sess = $stmt->fetch();
echo "Active Session: " . ($sess ? $sess['kode_sesi'] . " (Tahap: " . $sess['tahap'] . ")" : "None") . PHP_EOL;

if ($sess) {
    $stmtStats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN hitung_fisik IS NULL THEN 1 ELSE 0 END) as belum_hitung,
            SUM(CASE WHEN hitung_fisik IS NOT NULL THEN 1 ELSE 0 END) as sudah_hitung
        FROM detail_opname_batch
        WHERE id_sesi = " . $sess['id']
    );
    $st = $stmtStats->fetch();
    echo "Stats: Total={$st['total']}, Belum Hitung={$st['belum_hitung']}, Sudah Hitung={$st['sudah_hitung']}" . PHP_EOL;
}

