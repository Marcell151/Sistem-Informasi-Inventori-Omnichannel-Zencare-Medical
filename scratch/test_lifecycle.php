<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== SIMULATION TEST: ANOMALY LIFECYCLE ===\n";

// 1. Check current status of Row 3 (the user's test row)
$r3 = $pdo->query("SELECT la.*, p.no_invoice AS inv_anomali, p_res.no_invoice AS inv_res FROM log_anomali_fefo la LEFT JOIN penjualan p ON la.id_penjualan = p.id LEFT JOIN penjualan p_res ON la.resolved_in_penjualan = p_res.id WHERE la.id = 3")->fetch(PDO::FETCH_ASSOC);

echo "Row 3 data:\n";
echo "- Produk ID: " . $r3['id_variasi'] . "\n";
echo "- Batch diambil (ditukar): " . $r3['batch_diambil'] . "\n";
echo "- Batch seharusnya: " . $r3['batch_seharusnya'] . "\n";
echo "- Invoice kejadian: " . $r3['inv_anomali'] . "\n";
echo "- Status: " . $r3['status'] . "\n";
echo "- Invoice penyelesaian: " . $r3['inv_res'] . "\n";
echo "- Waktu penyelesaian: " . $r3['resolved_at'] . "\n";

assert($r3['status'] === 'disesuaikan', "Status should be disesuaikan");
assert($r3['inv_res'] === 'POS-20261029-6112', "Resolved invoice should match");
echo "✅ SIMULATION TEST PASSED!\n";

