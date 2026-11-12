<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

$activeSession = cekSesiOpnameAktif($pdo);
echo "Active session: " . ($activeSession ? "ID {$activeSession['id']}, Tahap: {$activeSession['tahap']}" : "None") . "\n";

$stats = hitungStatistikOpname($pdo, $activeSession['id']);
echo "Stats belum_hitung: {$stats['belum_hitung']}\n";
echo "Stats total_item: {$stats['total_item']}\n";

$role = 'superadmin';
$isSuperadmin = ($role === 'superadmin');
$isAdmin = ($role === 'admin');
$isKasir = ($role === 'kasir');
$canCountPhysical = ($isKasir || $isSuperadmin);

echo "canCountPhysical: " . ($canCountPhysical ? 'YES' : 'NO') . "\n";
echo "Show button condition: " . (($canCountPhysical && $stats['belum_hitung'] > 0) ? 'YES' : 'NO') . "\n";

