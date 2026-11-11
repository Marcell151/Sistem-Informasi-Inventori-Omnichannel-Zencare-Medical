<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

// Mock all items counted
$pdo->exec("UPDATE detail_opname_batch SET hitung_fisik = stok_sistem_fisik, selisih = 0, fisik_baik = stok_sistem_fisik, fisik_rusak = 0, fisik_expired = 0 WHERE id_sesi = 28");

session_start();
$_SESSION['user_id'] = 2; // Admin
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin_toko';
$_SESSION['nama_lengkap'] = 'Admin Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Restore back to uncounted
$pdo->exec("UPDATE detail_opname_batch SET hitung_fisik = NULL, selisih = 0, fisik_baik = NULL, fisik_rusak = NULL, fisik_expired = NULL WHERE id_sesi = 28");

if (strpos($html, '<input type="hidden" name="aksi" value="tutup_penghitungan">') !== false) {
    echo "SUCCESS: Active submit form with aksi=tutup_penghitungan is rendered.\n";
} else {
    echo "FAIL: Active form not rendered.\n";
}

if (strpos($html, '<span>Verifikasi Selisih</span>') !== false) {
    echo "SUCCESS: Active button with 'Verifikasi Selisih' is rendered.\n";
} else {
    echo "FAIL: Active button not rendered.\n";
}
