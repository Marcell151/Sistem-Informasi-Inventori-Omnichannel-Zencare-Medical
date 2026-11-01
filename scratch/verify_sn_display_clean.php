<?php
ob_start();
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SERVER['REQUEST_METHOD'] = 'GET';
include 'inventori/stok_opname.php';
$html = ob_get_clean();

// Check if Nebulizer Omron shows 'Unit di Rak: 6 Unit' and does NOT show 'OMR-NEB-99'
if (strpos($html, 'OMR-NEB-99') === false) {
    echo "[PASS] OMR-NEB-99 (unit retur/rusak) is NOT rendered.\n";
} else {
    echo "[FAIL] OMR-NEB-99 is still rendered.\n";
}

if (strpos($html, 'Unit di Rak: <strong class="text-emerald-700 font-bold">6 Unit</strong>') !== false) {
    echo "[PASS] Display correctly shows: Unit di Rak: 6 Unit.\n";
} else {
    echo "[WARN] Unit count string mismatch.\n";
}

if (strpos($html, 'Obat &amp; BMHP') === false && strpos($html, 'Obat / BMHP') === false) {
    echo "[PASS] No BMHP labels remaining.\n";
} else {
    echo "[WARN] BMHP label still found.\n";
}
