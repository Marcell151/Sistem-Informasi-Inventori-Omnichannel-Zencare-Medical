<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';
require_once __DIR__ . '/restore_baseline.php';

restoreBaselineInventory($pdo);
cekSesiOpnameAktif($pdo, true);
echo "Database restored to clean baseline successfully!\n";
