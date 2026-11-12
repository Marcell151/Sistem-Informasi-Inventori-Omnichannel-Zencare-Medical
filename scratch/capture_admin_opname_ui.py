import subprocess
import os

# 1. Generate static HTML page of the Admin Toko view
php_code = """<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';
$_GET['status_eval'] = 'berselisih';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Add base tag for assets
$html = str_replace('<head>', '<head><base href="http://localhost/inventory_zencare/inventori/">', $html);
file_put_contents(__DIR__ . '/admin_opname_rendered.html', $html);
echo "Rendered static HTML successfully!\\n";
"""

with open('scratch/gen_admin_html.php', 'w') as f:
    f.write(php_code)

res = subprocess.run(["C:\\xampp\\php\\php.exe", "scratch/gen_admin_html.php"], capture_output=True, text=True)
print("PHP output:", res.stdout, res.stderr)

# 2. Run Chrome headless to take screenshot
chrome_path = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
if not os.path.exists(chrome_path):
    chrome_path = r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"

html_path = os.path.abspath('scratch/admin_opname_rendered.html')
screenshot_path = os.path.abspath('scratch/admin_opname_ui_clean.png')

cmd = [
    chrome_path,
    "--headless=new",
    "--disable-gpu",
    "--window-size=1400,1000",
    f"--screenshot={screenshot_path}",
    f"file:///{html_path}"
]

res2 = subprocess.run(cmd, capture_output=True, text=True)
print("Chrome return code:", res2.returncode)
print("Screenshot exists:", os.path.exists(screenshot_path))

