<?php
require_once __DIR__ . '/../config/koneksi.php';
$sessions = $pdo->query("SELECT id, kode_sesi, status, catatan FROM sesi_opname")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sessions as $s) {
    echo "ID: {$s['id']} | Kode: {$s['kode_sesi']} | Status: {$s['status']} | Catatan: " . substr($s['catatan'] ?? '', 0, 40) . "\n";
}

