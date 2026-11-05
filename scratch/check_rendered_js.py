import subprocess

php_code = """
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Test Superadmin';
chdir('pos');
ob_start();
include 'pos.php';
$out = ob_get_clean();

$idx1 = strpos($out, 'function toggleBatchTerlewatPanel()');
echo substr($out, $idx1, 2500);
"""

res = subprocess.run([r'c:\xampp\php\php.exe', '-r', php_code], capture_output=True, text=True, cwd=r'c:\xampp\htdocs\inventory_zencare')
with open(r'c:\xampp\htdocs\inventory_zencare\scratch\rendered_js.txt', 'w', encoding='utf-8') as f:
    f.write(res.stdout)
print("Rendered JS length:", len(res.stdout))
