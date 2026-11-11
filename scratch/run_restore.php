<?php
require_once __DIR__ . '/restore_baseline.php';
require_once __DIR__ . '/../config/koneksi.php';
restoreBaselineInventory($pdo);
echo "Baseline restored successfully!\n";

