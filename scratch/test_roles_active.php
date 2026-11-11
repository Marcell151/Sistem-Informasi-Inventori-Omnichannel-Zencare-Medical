<?php
require_once 'config/config.php';
require_once 'config/koneksi.php';
require_once 'config/opname_helper.php';

// 1. Buat sesi aktif sementara untuk tes render
$kodeSesi = 'OPN-RENDER-TEST';
$pdo->query("INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, catatan) VALUES ('$kodeSesi', 'Aktif', 1, NOW(), 'Tes Render UI Role')");
$idSesi = $pdo->lastInsertId();

// Tambah 2 item opname (1 belum dihitung, 1 sudah dihitung dengan selisih -1)
$pdo->query("
    INSERT INTO detail_opname_batch 
    (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
    VALUES 
    ($idSesi, 1, 1, 'Obat', 'kecil', 'Strip', 20, 0, 20, NULL, NULL, 'Belum Dimutasi'),
    ($idSesi, 2, 2, 'Obat', 'besar', 'Dus', 10, 0, 10, 9, -1, 'Belum Dimutasi')
");

function test_render_role($pdo, $role, $userId, $nama) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = strtolower($role);
    $_SESSION['role'] = $role;
    $_SESSION['nama_lengkap'] = $nama;

    cekSesiOpnameAktif($pdo, true); // force refresh

    ob_start();
    include 'inventori/stok_opname.php';
    $html = ob_get_clean();

    echo "=== ROLE: $role ($nama) ===\n";
    echo "Length: " . strlen($html) . " bytes\n";

    if (strpos($html, 'Fatal error') !== false || strpos($html, 'Warning:') !== false) {
        echo "✗ HAS PHP ERROR/WARNING!\n";
    } else {
        echo "✓ Zero PHP errors/warnings.\n";
    }

    if ($role === 'kasir') {
        $hasBlindNotice = (strpos($html, 'PENCACAHAN FISIK RAK (BLIND COUNT)') !== false);
        echo "Has Kasir Blind Count banner: " . ($hasBlindNotice ? 'YES ✓' : 'NO ✗') . "\n";
        $hasDirahasiakan = (strpos($html, 'Dirahasiakan') !== false);
        echo "Has Dirahasiakan indicator: " . ($hasDirahasiakan ? 'YES ✓' : 'NO ✗') . "\n";
    }

    if ($role === 'admin' || $role === 'superadmin') {
        $hasAnomaliTab = (strpos($html, 'Anomali Selisih') !== false);
        echo "Has Anomali Selisih tab: " . ($hasAnomaliTab ? 'YES ✓' : 'NO ✗') . "\n";
        $hasCategoryDropdown = (strpos($html, 'kategori_beban_utama') !== false);
        echo "Has Category Beban dropdown: " . ($hasCategoryDropdown ? 'YES ✓' : 'NO ✗') . "\n";
    }
    echo "\n";
}

test_render_role($pdo, 'kasir', 4, 'Kasir Front Office');
test_render_role($pdo, 'admin', 2, 'Admin Toko');
test_render_role($pdo, 'superadmin', 1, 'Superadmin Pemilik');

// Bersihkan sesi tes
$pdo->query("DELETE FROM detail_opname_batch WHERE id_sesi = $idSesi");
$pdo->query("DELETE FROM sesi_opname WHERE id = $idSesi");
cekSesiOpnameAktif($pdo, true);
echo "Cleaned up test session.\n";

