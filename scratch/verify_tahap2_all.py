import re
from pathlib import Path
import pptx

ROOT = Path(r"c:\xampp\htdocs\inventory_zencare")
PPTX_PATH = ROOT / "lain/PPT/Seminar Hasil TA Final.pptx"
HTML_PATH = ROOT / "laporan_ta/PRESENTASI_SEMINAR_HASIL.html"

forbidden = [
    r"\bfarmasi\b", r"\bapotek\b", r"\bhpp\b", r"\bakuntansi\b",
    r"\baudit\b", r"\bbab\s*4\b", r"\bsidang\b", r"\bsempurna\b",
    r"93[.,]75", r"300\s*/\s*320", r"Likert", r"Sangat Layak",
    r"go[\s-]*live", r"upload bukti", r"Admin Kasir",
]

# 1. Verify PPTX
prs = pptx.Presentation(PPTX_PATH)
assert len(prs.slides) == 22, f"Expected 22 slides, got {len(prs.slides)}"

pptx_texts = []
for idx, sl in enumerate(prs.slides, 1):
    for sh in sl.shapes:
        if sh.shape_type == 13:
            _ = sh.image.blob
        if sh.has_text_frame and sh.text.strip():
            pptx_texts.append((idx, sh.text.strip()))
        if sh.has_table:
            for row in sh.table.rows:
                for cell in row.cells:
                    if cell.text.strip():
                        pptx_texts.append((idx, cell.text.strip()))

for pat in forbidden:
    hits = [(i, t[:80]) for i, t in pptx_texts if re.search(pat, t, re.IGNORECASE)]
    assert not hits, f"Forbidden match '{pat}' in PPTX: {hits}"

all_pptx_blob = "\n".join(t for _, t in pptx_texts)
for req in [
    "Perbandingan Penelitian Terdahulu",
    "Toko Obat Murah Bung Tomo",
    "Workflow Barang Keluar (Sistem Usulan)",
    "Workflow Barang Masuk & Logistik (Sistem Usulan)",
    "Sitemap — Pelanggan (E-Commerce)",
    "Sitemap — Kasir POS & Stok Opname",
    "Sitemap — Admin Toko",
    "Sitemap — Superadmin (Pemilik Toko)",
    "Logical Database (Arsitektur Penuh 20 Tabel)",
    "Detail Database (1/2)",
    "Detail Database (2/2)",
    "KEPUTUSAN PENGUJIAN UAT",
    "BERHASIL",
    "Lampiran: Dokumentasi Gerai ZenCare Medical",
    "Terima Kasih",
]:
    assert req in all_pptx_blob, f"Missing expected content in PPTX: {req}"

# Ensure no Activity or Novelty slides remain in PPTX
for unwanted in ["Lampiran 5: Activity", "Lampiran 9: Pembaruan Sistem"]:
    assert unwanted not in all_pptx_blob, f"Unwanted slide remained: {unwanted}"

# 2. Verify PRESENTASI_SEMINAR_HASIL.html
html_txt = HTML_PATH.read_text(encoding="utf-8")
for pat in forbidden:
    m = re.findall(pat, html_txt, re.IGNORECASE)
    assert not m, f"Forbidden match '{pat}' in PRESENTASI HTML: {m}"

imgs = re.findall(r":\s*'\.\./([^']+)'", html_txt)
for rel_p in imgs:
    full_img = ROOT / rel_p
    assert full_img.exists(), f"Missing image on disk: {full_img}"

# Check Slide 20/21 come before Slide 22 Terima Kasih in HTML order
pos_lamp = html_txt.find("Lampiran: Dokumentasi Gerai ZenCare Medical")
pos_thanks = html_txt.find("Terima Kasih")
assert 0 < pos_lamp < pos_thanks, "Lampiran must come before Terima Kasih!"

script_part = html_txt[html_txt.find("<script>"):html_txt.rfind("</script>")]
no_str = re.sub(r"'[^'\n]*'|\"[^\"\n]*\"|`[^`]*`", "", script_part, flags=re.S)
diff_braces = no_str.count("{") - no_str.count("}")
assert diff_braces == 0, f"JS brace mismatch: {diff_braces}"

print(f"PPTX Verified: {PPTX_PATH.name} ({len(prs.slides)} slides, all images readable, 0 forbidden hits)")
print(f"HTML Verified: {HTML_PATH.name} ({len(imgs)} images verified on disk, JS braces balanced=0, 0 forbidden hits)")
