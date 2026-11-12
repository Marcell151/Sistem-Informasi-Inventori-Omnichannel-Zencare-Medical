import os
import subprocess
from PIL import Image

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
src_html = os.path.abspath('scratch/wf_stok_opname.html')
out_png = os.path.abspath('lain/File PPT/Wireframe/Stok Opname.png')

cmd = [
    chrome, '--headless', '--disable-gpu',
    f'--screenshot={out_png}',
    '--window-size=1560,672',
    f'file:///{src_html.replace(os.sep, "/")}'
]

print("Rendering Stok Opname.png...")
res = subprocess.run(cmd, capture_output=True, text=True)

if os.path.exists(out_png):
    im = Image.open(out_png)
    print(f"[OK] Rendered Stok Opname.png | Size: {im.size} | Bytes: {os.path.getsize(out_png)}")
else:
    print("[FAIL] Render failed!")

