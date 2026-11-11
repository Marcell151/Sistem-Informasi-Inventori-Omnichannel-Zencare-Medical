<?php
// Script to backup current pristine inventory tables to scratch/baseline_inventory.json
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$baseline = [
    'timestamp' => date('Y-m-d H:i:s'),
    'stok_batch' => $pdo->query("SELECT * FROM stok_batch")->fetchAll(PDO::FETCH_ASSOC),
    'stok_toko' => $pdo->query("SELECT * FROM stok_toko")->fetchAll(PDO::FETCH_ASSOC),
    'unit_serial' => $pdo->query("SELECT * FROM unit_serial")->fetchAll(PDO::FETCH_ASSOC),
    'max_kartu_stok_id' => $pdo->query("SELECT COALESCE(MAX(id), 0) FROM kartu_stok")->fetchColumn(),
    'max_sesi_opname_id' => $pdo->query("SELECT COALESCE(MAX(id), 0) FROM sesi_opname")->fetchColumn()
];

file_put_contents(__DIR__ . '/baseline_inventory.json', json_encode($baseline, JSON_PRETTY_PRINT));
echo "✓ Baseline inventory successfully saved to scratch/baseline_inventory.json!\n";
echo "Total stok_batch: " . count($baseline['stok_batch']) . "\n";
echo "Total stok_toko: " . count($baseline['stok_toko']) . "\n";
echo "Total unit_serial: " . count($baseline['unit_serial']) . "\n";
echo "Max kartu_stok ID: " . $baseline['max_kartu_stok_id'] . "\n";
echo "Max sesi_opname ID: " . $baseline['max_sesi_opname_id'] . "\n";

