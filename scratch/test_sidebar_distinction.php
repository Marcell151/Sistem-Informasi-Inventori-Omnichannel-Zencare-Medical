<?php
function testRoleSidebar($role) {
    // Run separate php process
    $cmd = 'C:\\xampp\\php\\php.exe -r "session_start(); $_SESSION[\'user_id\'] = 1; $_SESSION[\'role\'] = \'' . $role . '\'; $_SESSION[\'nama_lengkap\'] = \'User\'; ob_start(); include \'inventori/stok_opname.php\'; $h = ob_get_clean(); echo (strpos($h, \'app-sidebar\') !== false ? \'YES\' : \'NO\');"';
    $res = shell_exec($cmd);
    echo "Role: $role => has app-sidebar: " . trim($res) . "\n";
}

testRoleSidebar('superadmin');
testRoleSidebar('admin');
testRoleSidebar('kasir');

