<?php
// Simulate rendering as admin vs superadmin
function testRenderRole($testRole) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = $testRole;
    $_SESSION['nama_lengkap'] = ($testRole === 'superadmin' ? 'SuperAdmin (Pemilik)' : 'Admin Toko');

    ob_start();
    include __DIR__ . '/../inventori/stok_opname.php';
    $output = ob_get_clean();

    echo "=== TEST ROLE: $testRole ===\n";
    if (strpos($output, 'TAB NAVIGASI AUDIT KHUSUS SUPERADMIN') !== false) {
        echo "Tabs found: YES\n";
    } else {
        echo "Tabs found: NO\n";
    }

    if (strpos($output, 'Daftar Rekonsiliasi Selisih Fisik (Admin Toko)') !== false) {
        echo "Admin Toko Notice found: YES\n";
    } else {
        echo "Admin Toko Notice found: NO\n";
    }

    if (strpos($output, 'Verifikasi Hilang (0)') !== false) {
        echo "Bug 'Verifikasi Hilang (0)' found: YES (ERROR!)\n";
    } else {
        echo "Bug 'Verifikasi Hilang (0)' found: NO (CORRECT)\n";
    }

    if (strpos($output, 'Klop (Sesuai)') !== false) {
        echo "'Klop (Sesuai)' badge found: YES\n";
    }

    if (strpos($output, 'openModalKoreksiSuperadmin') !== false) {
        echo "Superadmin Koreksi button found: YES\n";
    } else {
        echo "Superadmin Koreksi button found: NO\n";
    }
    echo PHP_EOL;
}

testRenderRole('admin');
testRenderRole('superadmin');
