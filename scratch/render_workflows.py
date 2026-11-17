import re, subprocess, tempfile
from pathlib import Path
from PIL import Image, ImageChops

HTML_PATH = Path(r"c:\xampp\htdocs\inventory_zencare\lain\semua_workflow_zencare.html")
OUT_DIR = Path(r"c:\xampp\htdocs\inventory_zencare\lain\File PPT\Workflow")
EDGE = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

raw = HTML_PATH.read_text(encoding="utf-8")
style_m = re.search(r"<style>(.*?)</style>", raw, re.S)
css = style_m.group(1)

# Extract the 4 workflow-box sections by splitting on section-title comments/headers
markers = [
    ("1. Workflow Barang Keluar (Sistem Berjalan)", "Workflow Barang Keluar (Berjalan).png", 1800),
    ("2. Workflow Barang Masuk (Sistem Berjalan)", "Workflow Barang Masuk (Berjalan).png", 1800),
    ("3. Workflow Barang Keluar (Sistem Usulan Omnichannel)", "Workflow Barang Keluar (Sistem Usulan).png", 2600),
    ("4. Workflow Barang Masuk &amp; Logistik (Sistem Usulan)", "Workflow Barang Masuk & Logistik (Sistem Usulan).png", 2000),
]

parts = raw.split('<h2 class="section-title">')
assert len(parts) == 5, f"Expected 4 section-title blocks, found {len(parts)-1}"

def trim_white(img_path, pad=24):
    im = Image.open(img_path).convert("RGB")
    bg = Image.new("RGB", im.size, (255, 255, 255))
    diff = ImageChops.difference(im, bg)
    bbox = diff.getbbox()
    if bbox:
        l = max(0, bbox[0] - pad)
        t = max(0, bbox[1] - pad)
        r = min(im.width, bbox[2] + pad)
        b = min(im.height, bbox[3] + pad)
        im.crop((l, t, r, b)).save(img_path)
    return Image.open(img_path).size

with tempfile.TemporaryDirectory() as tmp:
    tmp_dir = Path(tmp)
    for idx, (title_txt, out_name, win_h) in enumerate(markers, 1):
        sec_html = parts[idx]
        # Strip trailing comment for next section and container closing tags
        sec_html = re.sub(r"<!--\s*=+.*?$", "", sec_html, flags=re.S).strip()
        if idx == 4:
            sec_html = sec_html.rsplit("</div>", 1)[0]  # remove .container close
            sec_html = re.sub(r"</body>.*$", "", sec_html, flags=re.S).strip()
        # Keep only from <div class="workflow-box"> onward
        wb_pos = sec_html.find('<div class="workflow-box">')
        wb_html = sec_html[wb_pos:]

        page = tmp_dir / f"wf_{idx}.html"
        page.write_text(
            f"""<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>
{css}
body {{ padding: 12px; margin: 0; background: #ffffff; }}
.workflow-box {{ overflow: visible !important; margin: 0; padding: 8px; }}
.workflow-row-group {{ margin-bottom: 34px; }}
.workflow-row-group:last-child {{ margin-bottom: 4px; }}
</style></head><body>{wb_html}</body></html>""",
            encoding="utf-8",
        )
        out_png = OUT_DIR / out_name
        cmd = [
            EDGE,
            "--headless",
            "--disable-gpu",
            "--hide-scrollbars",
            "--force-device-scale-factor=2",
            f"--window-size=1100,{win_h}",
            f"--screenshot={out_png}",
            page.as_uri(),
        ]
        subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        sz = trim_white(out_png)
        print("Rendered:", out_name, sz)
