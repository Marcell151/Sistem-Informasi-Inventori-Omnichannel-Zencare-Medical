import subprocess

php_code = """
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';
chdir('pos');
ob_start();
include 'pos.php';
$out = ob_get_clean();

$idx = strpos($out, 'function openKasirAnomaliModal()');
echo substr($out, $idx, 600);
"""

res = subprocess.run([r'c:\xampp\php\php.exe', '-r', php_code], capture_output=True, text=True, cwd=r'c:\xampp\htdocs\inventory_zencare')
print(res.stdout)

