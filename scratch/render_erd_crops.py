import subprocess, tempfile
from pathlib import Path

SVG_PATH = Path(r"c:\xampp\htdocs\inventory_zencare\lain\Gambar_3.18_Logical_Database_Model.svg")
OUT_DIR = Path(r"c:\xampp\htdocs\inventory_zencare\lain\PPT\Diagram_Seminar_Hasil")
EDGE = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

svg_raw = SVG_PATH.read_text(encoding="utf-8")

# (filename, viewBox_x, viewBox_y, viewBox_w, viewBox_h, scale, fade_ids)
crops = [
    ("db_crop_1a_pasokan_kartu.png", 15, 35, 765, 745, 2, ["penjualan", "detail_penjualan"]),
    ("db_crop_1b_penjualan.png",     15, 495, 765, 845, 2, ["kartu_stok", "penerimaan_stok"]),
    ("db_crop_2a_master_batch.png",  845, 35, 715, 875, 2, ["sesi_opname", "detail_opname_batch"]),
    ("db_crop_2b_opname_audit.png",  845, 425, 1115, 1045, 2, ["stok_batch"]),
]

with tempfile.TemporaryDirectory() as tmp:
    tmp_dir = Path(tmp)
    for fname, vx, vy, vw, vh, scale, fade_ids in crops:
        patched = svg_raw.replace(
            'viewBox="0 0 2050 1540"',
            f'viewBox="{vx} {vy} {vw} {vh}"',
            1,
        )
        fade_css = ", ".join(f"#table_{tid}" for tid in fade_ids) + " { opacity: 0.10; }" if fade_ids else ""
        html_file = tmp_dir / f"{fname}.html"
        html_file.write_text(
            f"""<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
html, body {{ margin: 0; padding: 0; width: {vw}px; height: {vh}px; overflow: hidden; background: #fff; }}
svg {{ display: block; width: {vw}px; height: {vh}px; }}
{fade_css}
</style></head><body>{patched}</body></html>""",
            encoding="utf-8",
        )
        out_png = OUT_DIR / fname
        cmd = [
            EDGE,
            "--headless",
            "--disable-gpu",
            "--hide-scrollbars",
            f"--force-device-scale-factor={scale}",
            f"--window-size={vw},{vh}",
            f"--screenshot={out_png}",
            html_file.as_uri(),
        ]
        subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        print("Rendered:", out_png.name, f"({vw*scale}x{vh*scale} px)")
