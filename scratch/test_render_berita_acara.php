<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, kode_sesi, status FROM sesi_opname ORDER BY id DESC LIMIT 1");
$sesi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sesi) {
    echo "No sesi found.\n";
    exit;
}
echo "Testing with sesi ID: " . $sesi['id'] . " (" . $sesi['kode_sesi'] . ")\n";

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin Pemilik';
$_GET['id_sesi'] = $sesi['id'];

ob_start();
require_once __DIR__ . '/../inventori/cetak_berita_acara_opname.php';
$html = ob_get_clean();

echo "Cetak Berita Acara rendered: " . strlen($html) . " bytes\n";
if (strpos($html, 'Pengelola Stok Gudang') !== false && strpos($html, 'Pemilik Toko') !== false) {
    echo "SUCCESS: Grouping & clean signatures verified!\n";
} else {
    echo "Check output content!\n";
}
