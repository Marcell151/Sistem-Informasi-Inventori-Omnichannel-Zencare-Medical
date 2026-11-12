import os
import subprocess
from PIL import Image

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
src_html = os.path.abspath('scratch/rendered_berita_acara_test.html')
out_png = os.path.abspath('scratch/ba_screenshot_fixed.png')

cmd = [
    chrome, '--headless=new', '--disable-gpu',
    f'--screenshot={out_png}',
    '--window-size=1400,900',
    f'file:///{src_html.replace(os.sep, "/")}'
]

print("Rendering ba_screenshot_fixed.png...")
res = subprocess.run(cmd, capture_output=True, text=True)
print("Return code:", res.returncode)

if os.path.exists(out_png):
    im = Image.open(out_png)
    print(f"[OK] Rendered ba_screenshot_fixed.png | Size: {im.size} | Bytes: {os.path.getsize(out_png)}")
else:
    print("[FAIL] Render failed! stderr:", res.stderr)

