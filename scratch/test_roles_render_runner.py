import subprocess

script = """
require_once 'config/config.php';
require_once 'config/koneksi.php';

function test_render_role($pdo, $role, $userId, $nama) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = strtolower($role);
    $_SESSION['role'] = $role;
    $_SESSION['nama_lengkap'] = $nama;

    ob_start();
    include 'inventori/stok_opname.php';
    $html = ob_get_clean();

    echo "=== ROLE: $role ($nama) ===\\n";
    echo "Length of rendered HTML: " . strlen($html) . " bytes\\n";
    if (strpos($html, 'Fatal error') !== false || strpos($html, 'Warning:') !== false) {
        echo "✗ HAS PHP ERROR/WARNING!\\n";
    } else {
        echo "✓ Zero PHP errors/warnings.\\n";
    }

    if ($role === 'kasir') {
        $hasBlindNotice = (strpos($html, 'PENCACAHAN FISIK RAK (BLIND COUNT)') !== false);
        echo "Has Kasir Blind Count banner: " . ($hasBlindNotice ? 'YES' : 'NO') . "\\n";
        $hasHiddenCell = (strpos($html, 'Dirahasiakan') !== false);
        echo "Has Dirahasiakan indicator: " . ($hasHiddenCell ? 'YES' : 'NO') . "\\n";
    }

    if ($role === 'admin' || $role === 'superadmin') {
        $hasAnomaliTab = (strpos($html, 'Anomali Selisih') !== false);
        echo "Has Anomali Selisih tab: " . ($hasAnomaliTab ? 'YES' : 'NO') . "\\n";
        $hasCategoryDropdown = (strpos($html, 'kategori_beban_utama') !== false);
        echo "Has Category Beban dropdown: " . ($hasCategoryDropdown ? 'YES' : 'NO') . "\\n";
    }
    echo "\\n";
}

test_render_role($pdo, 'kasir', 4, 'Kasir Front Office');
test_render_role($pdo, 'admin', 2, 'Admin Toko');
test_render_role($pdo, 'superadmin', 1, 'Superadmin Pemilik');
"""

with open('scratch/test_roles_render.php', 'w', encoding='utf-8') as f:
    f.write("<?php\n" + script)

res = subprocess.run(['C:\\xampp\\php\\php.exe', 'scratch/test_roles_render.php'], capture_output=True, text=True)
print(res.stdout)
print(res.stderr)

