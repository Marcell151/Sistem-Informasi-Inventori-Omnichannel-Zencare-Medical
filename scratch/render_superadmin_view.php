<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';

$_GET = [];

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Rendered length (Superadmin): " . strlen($html) . "\n";

// Check if black bar exists
if (strpos($html, 'bg-slate-900 text-white rounded-xl flex items-center justify-between') !== false) {
    echo "WARNING: Black bar STILL EXISTS in Superadmin!\n";
} else {
    echo "SUCCESS: Black bar is GONE in Superadmin!\n";
}

// Check if red bullet exists
if (strpos($html, 'bg-rose-500 rounded-full') !== false || strpos($html, 'bg-rose-600 rounded-full') !== false || strpos($html, 'rounded-full bg-rose') !== false) {
    echo "WARNING: Red bullet STILL EXISTS in Superadmin!\n";
} else {
    echo "SUCCESS: Red bullet is GONE in Superadmin!\n";
}

// Check if Tabs are present
if (strpos($html, 'Perlu Mutasi (Berselisih)') !== false && strpos($html, 'Sesuai (Klop)') !== false) {
    echo "SUCCESS: Clean tabs are PRESENT for Superadmin!\n";
} else {
    echo "WARNING: Clean tabs NOT FOUND in Superadmin!\n";
}

