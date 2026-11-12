import subprocess
import os

php_exe = r'c:\xampp\php\php.exe'
res = subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/cetak_batch_stiker.php', 'test_stiker.html', 'id_penerimaan=1&mode=a4'], capture_output=True, text=True)
p = r'c:\xampp\htdocs\inventory_zencare\scratch\cache\test_stiker.html'
if os.path.exists(p):
    print('Generated test_stiker.html successfully! Size:', os.path.getsize(p))
    with open(p, 'r', encoding='utf-8') as f:
        print(f.read()[:600])
else:
    print('Failed to generate test_stiker.html:', res.stderr)

