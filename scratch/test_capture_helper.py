import urllib.request
import subprocess
import os
import time

url = 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=admin/master_produk.php'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

# Inject base href
html = html.replace('<head>', '<head><base href="http://localhost/inventory_zencare/">')
temp_html = os.path.abspath('scratch/temp_master_produk.html')
with open(temp_html, 'w', encoding='utf-8') as f:
    f.write(html)

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_file = os.path.abspath(r'lain\File PPT\UI_UX_Asli\05_Master_Produk.png')
file_url = 'file:///' + temp_html.replace('\\', '/')

cmd = [
    chrome,
    '--headless=new',
    '--disable-gpu',
    '--no-sandbox',
    f'--screenshot={out_file}',
    '--window-size=1440,900',
    file_url
]

t0 = time.time()
res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
t1 = time.time()
print('Elapsed:', round(t1 - t0, 2), 's')
print('Stderr:', res.stderr)
print('File size:', os.path.getsize(out_file) if os.path.exists(out_file) else 'Not found')

