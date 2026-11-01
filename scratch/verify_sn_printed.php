<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin Pemilik';

require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT id, kode_sesi FROM sesi_opname ORDER BY id DESC LIMIT 1");
$sesi = $stmt->fetch(PDO::FETCH_ASSOC);

$_GET['id_sesi'] = $sesi['id'];

ob_start();
require __DIR__ . '/../inventori/cetak_berita_acara_opname.php';
$html = ob_get_clean();

echo "Rendered " . strlen($html) . " bytes\n";
if (strpos($html, 'SN Fisik Terverifikasi') !== false) {
    echo "PASS: Serial numbers are explicitly listed in Berita Acara!\n";
} else {
    echo "FAIL: SN header not found.\n";
}

// Cek sampel SN Omron
if (strpos($html, 'OMR-TENS-') !== false) {
    echo "PASS: Actual Serial Numbers (e.g. OMR-TENS) appear in printout!\n";
}
