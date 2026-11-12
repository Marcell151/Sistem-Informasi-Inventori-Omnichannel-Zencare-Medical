import subprocess
import os

php_script = """<?php
$_GET['id'] = 11;
// Mock session user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Super Admin';

ob_start();
include __DIR__ . '/../inventori/cetak_berita_acara_opname.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/rendered_berita_acara_test.html', $html);
echo "Wrote rendered_berita_acara_test.html, length: " . strlen($html) . "\\n";
"""

with open('scratch/render_ba.php', 'w', encoding='utf-8') as f:
    f.write(php_script)

res = subprocess.check_output([r"C:\xampp\php\php.exe", "scratch/render_ba.php"]).decode()
print(res)

