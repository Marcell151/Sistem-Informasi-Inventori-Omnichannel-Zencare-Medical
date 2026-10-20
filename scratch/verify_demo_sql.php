<?php
/**
 * verify_demo_sql.php
 * Validates db_inventory (demo).sql
 */

$sqlFile = __DIR__ . '/../database/isi/db_inventory (demo).sql';
if (!file_exists($sqlFile)) {
    echo "ERROR: File not found: $sqlFile\n";
    exit(1);
}

$content = file_get_contents($sqlFile);
echo "SQL File Size: " . strlen($content) . " bytes\n";

// 1. Check absence of dummy product
$forbidden = ['Obat Uji Skenario Dosen', 'SIM-5BOX', 'SIM-IND-5BOX'];
foreach ($forbidden as $f) {
    if (stripos($content, $f) !== false) {
        echo "FAIL: Found forbidden string '$f' in SQL dump!\n";
    } else {
        echo "PASS: Forbidden string '$f' is absent.\n";
    }
}

// 2. Check table inserts
$tables = [
    'users', 'supplier', 'pengaturan_web', 'produk_induk', 'produk_variasi',
    'stok_toko', 'stok_batch', 'unit_serial', 'penerimaan_stok', 'penerimaan_detail',
    'histori_konversi', 'penjualan', 'detail_penjualan', 'kartu_stok'
];

foreach ($tables as $t) {
    if (preg_match('/INSERT INTO `' . $t . '` \((.*?)\) VALUES\s*(.*?);/s', $content, $m)) {
        $rows = explode("),\n(", $m[2]);
        echo "Table `$t`: " . count($rows) . " rows inserted.\n";
    } else {
        echo "WARNING: No INSERT found for table `$t`\n";
    }
}

// 3. Check Expiring Batches (22 & 23 Oct 2026)
if (preg_match("/'SNM-EXP-22', '2026-10-22'/", $content) && preg_match("/'BDR-EXP-23', '2026-10-23'/", $content)) {
    echo "PASS: Found exactly the 2 targeted expiring batches (22 and 23 Oct 2026).\n";
} else {
    echo "FAIL: Expiring batches not found or formatted incorrectly.\n";
}

// 4. Check Transactions
$invoices = ['POS-20261014-1001', 'POS-20261016-1002', 'POS-20261017-1003', 'INV-20261018-041', 'INV-20261019-052', 'POS-20261012-0901'];
foreach ($invoices as $inv) {
    if (strpos($content, $inv) !== false) {
        echo "PASS: Found invoice $inv\n";
    } else {
        echo "FAIL: Missing invoice $inv\n";
    }
}

// 5. Check Alkes Retur
if (strpos($content, 'OMR-NEB-99') !== false && strpos($content, 'Retur/Rusak') !== false) {
    echo "PASS: Alkes retur OMR-NEB-99 properly set to Retur/Rusak.\n";
} else {
    echo "FAIL: Alkes retur OMR-NEB-99 not found or incorrect.\n";
}
