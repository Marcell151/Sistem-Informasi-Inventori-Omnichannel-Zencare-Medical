import os
import subprocess
from PIL import Image

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
wf_dir = os.path.abspath(r'lain\File PPT\Wireframe')

targets = [
    ('scratch/wf_stok_opname.html', 'Stok Opname.png'),
    ('scratch/wf_konversi_satuan.html', 'Konversi Satuan.png'),
    ('scratch/wf_anomali_fefo.html', 'Laporan Anomali FEFO.png')
]

for src_html, out_png_name in targets:
    src_path = os.path.abspath(src_html)
    out_path = os.path.join(wf_dir, out_png_name)
    
    cmd = [
        chrome, '--headless', '--disable-gpu',
        f'--screenshot={out_path}',
        '--window-size=1560,672',
        f'file:///{src_path}'
    ]
    res = subprocess.run(cmd, capture_output=True, text=True)
    if os.path.exists(out_path):
        im = Image.open(out_path)
        print(f"[OK] Rendered {out_png_name:25} | Size: {im.size} | Bytes: {os.path.getsize(out_path)}")
    else:
        print(f"[FAIL] {out_png_name} failed to render!")

