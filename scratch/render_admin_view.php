<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';

$_GET = []; // default view

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Rendered length: " . strlen($html) . "\n";

// Check if black bar exists
if (strpos($html, 'bg-slate-900 text-white rounded-xl flex items-center justify-between') !== false) {
    echo "WARNING: Black bar STILL EXISTS!\n";
} else {
    echo "SUCCESS: Black bar is GONE!\n";
}

// Check if red bullet exists
if (strpos($html, 'bg-rose-500 rounded-full') !== false || strpos($html, 'bg-rose-600 rounded-full') !== false || strpos($html, 'rounded-full bg-rose') !== false) {
    echo "WARNING: Red bullet STILL EXISTS!\n";
} else {
    echo "SUCCESS: Red bullet is GONE!\n";
}

// Check if Fokus Anomali duplicate text exists
if (strpos($html, 'Fokus Anomali: Menampilkan') !== false) {
    echo "WARNING: Fokus Anomali text STILL EXISTS!\n";
} else {
    echo "SUCCESS: Fokus Anomali duplicate text is GONE!\n";
}

// Check if Tabs are present
if (strpos($html, 'Perlu Mutasi (Berselisih)') !== false && strpos($html, 'Sesuai (Klop)') !== false) {
    echo "SUCCESS: Clean tabs are PRESENT for Admin Toko!\n";
} else {
    echo "WARNING: Clean tabs NOT FOUND!\n";
}

