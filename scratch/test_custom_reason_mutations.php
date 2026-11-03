<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== TEST MUTASI DENGAN ALASAN KUSTOM ===\n";

// 1. Uji Row 264 (Obat, Surplus +2):
// Simulasi kirim alasan surplus kustom
$idDetail = 264;
$r = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = $idDetail")->fetch(PDO::FETCH_ASSOC);
echo "Row 264: Komoditas={$r['jenis_komoditas']} | Selisih={$r['selisih']}\n";

// 2. Uji Alkes Row 331 (Alkes, Defisit -1):
$idAlkes = 331;
$rAlkes = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = $idAlkes")->fetch(PDO::FETCH_ASSOC);
echo "Row 331: Komoditas={$rAlkes['jenis_komoditas']} | Selisih={$rAlkes['selisih']}\n";

echo "\n=== TEST API GET AVAILABLE SNS ===\n";
$_GET = ['action' => 'get_available_sns', 'id_variasi' => 53];
ob_start();
require __DIR__ . '/../api/get_batch_sn.php';
$res = ob_get_clean();
echo "API Response: $res\n";

