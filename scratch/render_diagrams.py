import pathlib, re, subprocess, xml.etree.ElementTree as ET
from PIL import Image, ImageChops

CHROME = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
OUT_DIR = pathlib.Path("scratch/ppt_assets")
OUT_DIR.mkdir(parents=True, exist_ok=True)

def trim_white(img_path, pad=20):
    im = Image.open(img_path).convert("RGB")
    bg = Image.new("RGB", im.size, (255, 255, 255))
    diff = ImageChops.difference(im, bg)
    bbox = diff.getbbox()
    if bbox:
        l = max(0, bbox[0] - pad)
        t = max(0, bbox[1] - pad)
        r = min(im.width, bbox[2] + pad)
        b = min(im.height, bbox[3] + pad)
        im = im.crop((l, t, r, b))
    im.save(img_path)
    return im.size

svgs = {
    "use_case.png": "lain/Gambar_3.9_Use_Case_ZenCare_Medical.svg",
    "act_penerimaan.png": "lain/Gambar_3.14_Activity_Penerimaan_Barang.svg",
    "act_pos.png": "lain/Gambar_3.12_Activity_Pesanan_Kasir_POS.svg",
    "act_ecommerce.png": "lain/Gambar_3.11_Activity_Pesanan_Daring.svg",
    "act_mutasi.png": "lain/Gambar_3.15_Activity_Mutasi_Stok.svg",
    "act_laporan.png": "lain/Gambar_3.16_Activity_Laporan_Manajerial.svg",
    "act_opname.png": "lain/Gambar_3.17_Activity_Stok_Opname.svg",
}

for out_name, svg_rel in svgs.items():
    svg_text = pathlib.Path(svg_rel).read_text(encoding="utf-8")
    m = re.search(r'viewBox="([\d.\s-]+)"', svg_text)
    if m:
        parts = [float(x) for x in m.group(1).split()]
        w, h = parts[2], parts[3]
    else:
        wm = re.search(r'width="([\d.]+)', svg_text)
        hm = re.search(r'height="([\d.]+)', svg_text)
        w = float(wm.group(1)) if wm else 1200
        h = float(hm.group(1)) if hm else 900
    vw = 2000
    vh = int(vw * (h / max(1, w))) + 120
    html_file = OUT_DIR / (out_name + ".html")
    html_file.write_text(
        f'<!DOCTYPE html><html><body style="margin:0;padding:20px;background:#fff;">'
        f'<div style="width:{vw-40}px;">{svg_text}</div></body></html>',
        encoding="utf-8"
    )
    # force svg width=100% height=auto inside style
    out_png = (OUT_DIR / out_name).resolve()
    cmd = [CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars",
           f"--window-size={vw},{vh}", "--force-device-scale-factor=2",
           f"--screenshot={out_png}", html_file.resolve().as_uri()]
    subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    sz = trim_white(out_png)
    html_file.unlink(missing_ok=True)
    print(f"SVG {out_name}: viewBox=({w},{h}) -> PNG={sz}")

# Also render 4 Mermaid sitemaps from BAB_III
b3 = pathlib.Path("laporan_ta/BAB_III_ZENCARE_MEDICAL.html").read_text(encoding="utf-8")
mermaids = re.findall(r'<div class="mermaid">\s*([\s\S]*?)</div>\s*<p[^>]*><b>Gambar 3\.([5678])</b>', b3)
print("Found Mermaid sitemaps:", [g for _, g in mermaids])
for code, gnum in mermaids:
    out_name = f"sitemap_3_{gnum}.png"
    html_file = OUT_DIR / (out_name + ".html")
    html_file.write_text(f"""<!DOCTYPE html><html><head><meta charset="utf-8">
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<style>body{{margin:0;padding:30px;background:#fff;font-family:sans-serif;}} .mermaid svg{{max-width:none !important;}}</style>
</head><body>
<div class="mermaid">\n{code}\n</div>
<script>mermaid.initialize({{startOnLoad:true,theme:'base',themeVariables:{{fontFamily:'Arial, sans-serif',fontSize:'14px'}}}});</script>
</body></html>""", encoding="utf-8")
    out_png = (OUT_DIR / out_name).resolve()
    cmd = [CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars",
           "--virtual-time-budget=5000", "--window-size=2200,2400", "--force-device-scale-factor=2",
           f"--screenshot={out_png}", html_file.resolve().as_uri()]
    subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    sz = trim_white(out_png)
    html_file.unlink(missing_ok=True)
    print(f"Mermaid {out_name}: PNG={sz}")
