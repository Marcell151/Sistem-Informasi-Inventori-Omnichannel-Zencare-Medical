<?php
require_once __DIR__ . '/../config/koneksi.php';

// Check columns in sesi_opname
$stmt = $pdo->query("SHOW COLUMNS FROM sesi_opname");
$cols_sesi = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Live DB sesi_opname columns:\n" . implode(", ", $cols_sesi) . "\n\n";

// Check columns in detail_opname_batch
$stmt = $pdo->query("SHOW COLUMNS FROM detail_opname_batch");
$cols_dob = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Live DB detail_opname_batch columns:\n" . implode(", ", $cols_dob) . "\n\n";

// Check if any active sessions exist
$stmt = $pdo->query("SELECT id, kode_sesi, status, tahap FROM sesi_opname WHERE status = 'Aktif'");
$aktif = $stmt->fetchAll();
echo "Active sessions in live DB: " . count($aktif) . "\n";
foreach ($aktif as $row) {
    echo "ID: {$row['id']}, Kode: {$row['kode_sesi']}, Status: {$row['status']}, Tahap: {$row['tahap']}\n";
}

