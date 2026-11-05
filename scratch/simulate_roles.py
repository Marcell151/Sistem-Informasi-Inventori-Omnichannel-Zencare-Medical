import subprocess

for role, uid in [('superadmin', 1), ('kasir', 4)]:
    php_code = f"""
    session_start();
    $_SESSION['user_id'] = {uid};
    $_SESSION['role'] = '{role}';
    $_SESSION['nama_lengkap'] = 'Test {role}';
    chdir('pos');
    ob_start();
    include 'pos.php';
    $out = ob_get_clean();
    echo "ROLE: {role} | USER ID: {uid} | LENGTH: " . strlen($out) . "\\n";
    echo "Has panel_batch_terlewat: " . (strpos($out, 'id=\"panel_batch_terlewat\"') !== false ? 'YES' : 'NO') . "\\n";
    echo "Has Batch Sesuai FEFO: " . (strpos($out, 'Batch Sesuai FEFO') !== false ? 'YES' : 'NO') . "\\n";
    echo "Has Batch Terlewat: " . (strpos($out, 'Batch Terlewat') !== false ? 'YES' : 'NO') . "\\n";
    echo "Has Batch Selesai: " . (strpos($out, 'Batch Selesai') !== false ? 'YES' : 'NO') . "\\n";
    echo "------------------------------------\\n";
    """
    res = subprocess.run([r'c:\xampp\php\php.exe', '-r', php_code], capture_output=True, text=True, cwd=r'c:\xampp\htdocs\inventory_zencare')
    print(res.stdout)
