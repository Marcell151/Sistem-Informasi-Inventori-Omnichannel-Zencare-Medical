<?php
require_once __DIR__ . '/../config/koneksi.php';

$pdo->prepare("UPDATE log_anomali_fefo SET status = 'disesuaikan', resolved_in_penjualan = 6, resolved_at = '2026-10-28 15:00:00' WHERE id IN (1, 2)")->execute();
echo "Rows 1 and 2 updated to disesuaikan.\n";

