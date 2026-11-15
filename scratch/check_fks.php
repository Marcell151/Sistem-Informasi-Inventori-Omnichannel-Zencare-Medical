<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
echo "Checking FK candidates in live DB:\n";

// 1. log_anomali_fefo
$res = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE id_penjualan IS NOT NULL AND id_penjualan NOT IN (SELECT id FROM penjualan)")->fetchColumn();
echo "log_anomali_fefo invalid id_penjualan: $res\n";

$res = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE id_user IS NOT NULL AND id_user NOT IN (SELECT id FROM users)")->fetchColumn();
echo "log_anomali_fefo invalid id_user: $res\n";

$res = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE id_variasi IS NOT NULL AND id_variasi NOT IN (SELECT id FROM produk_variasi)")->fetchColumn();
echo "log_anomali_fefo invalid id_variasi: $res\n";

// 2. kartu_stok id_batch
$res = $pdo->query("SELECT COUNT(*) FROM kartu_stok WHERE id_batch IS NOT NULL AND id_batch NOT IN (SELECT id FROM stok_batch)")->fetchColumn();
echo "kartu_stok invalid id_batch: $res\n";

// 3. unit_serial id_penjualan
$res = $pdo->query("SELECT COUNT(*) FROM unit_serial WHERE id_penjualan IS NOT NULL AND id_penjualan NOT IN (SELECT id FROM penjualan)")->fetchColumn();
echo "unit_serial invalid id_penjualan: $res\n";

// 4. detail_opname_batch id_kartu_stok
$res = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_kartu_stok IS NOT NULL AND id_kartu_stok NOT IN (SELECT id FROM kartu_stok)")->fetchColumn();
echo "detail_opname_batch invalid id_kartu_stok: $res\n";
