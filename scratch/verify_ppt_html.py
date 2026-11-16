import pathlib, re, subprocess

html_p = pathlib.Path("laporan_ta/PRESENTASI_SEMINAR_HASIL.html")
txt = html_p.read_text(encoding="utf-8")

m = re.search(r'href="([^"]+\.pptx)"', txt)
assert m, "missing pptx href"
pptx_target = (html_p.parent / m.group(1)).resolve()
assert pptx_target.exists(), f"missing pptx target: {pptx_target}"
print("PPTX LINK OK:", pptx_target, round(pptx_target.stat().st_size / 1024, 1), "KB")

count_img = 0
for k, rel in re.findall(r"(\w+):\s*'([^']+)'", txt):
    if rel.startswith("../"):
        tgt = (html_p.parent / rel).resolve()
        assert tgt.exists(), f"MISSING ASSET {k}: {tgt}"
        count_img += 1
print("ALL IMG ASSETS VERIFIED ON DISK:", count_img)

forb = [
    w for w in [
        "farmasi", "apotek", "hpp", "akuntansi", "audit", "sempurna",
        "penerimaan_barang", "transaksi_pos", "pesanan_daring", "keeceran", "SEMINAR PROPOSAL"
    ]
    if re.search(r"\b" + re.escape(w) + r"\b", txt, re.I)
]
print("FORBIDDEN IN HTML:", forb)

# Render top preview screenshot via Chrome headless
out_png = pathlib.Path("scratch/preview_presentasi_html.png").resolve()
CHROME = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
subprocess.run([
    CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars",
    "--virtual-time-budget=4000", "--window-size=1440,1750",
    f"--screenshot={out_png}", html_p.resolve().as_uri()
], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
print("PREVIEW SCREENSHOT:", out_png, out_png.stat().st_size)
