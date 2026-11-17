import copy, pathlib
from PIL import Image
import pptx
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

SRC_PPTX = pathlib.Path(r"c:\xampp\htdocs\inventory_zencare\lain\PPT\Seminar Proposal TA.pptx")
OUT_PPTX = pathlib.Path(r"c:\xampp\htdocs\inventory_zencare\lain\PPT\Seminar Hasil TA Final.pptx")
ALT_OUT_PPTX = pathlib.Path(r"c:\xampp\htdocs\inventory_zencare\lain\PPT\Seminar Hasil TA Final_Updated.pptx")

ASSETS = pathlib.Path("lain/PPT/Diagram_Seminar_Hasil")
WF_DIR = pathlib.Path("lain/File PPT/Workflow")

NAVY = RGBColor(0x00, 0x50, 0x88)
DARK_NAVY = RGBColor(0x0F, 0x2D, 0x5A)
TEAL = RGBColor(0x11, 0xCA, 0xA0)
TEAL_DARK = RGBColor(0x4D, 0x6F, 0x78)
SLATE = RGBColor(0x33, 0x41, 0x55)
MUTED = RGBColor(0x64, 0x74, 0x8B)
LIGHT_BG = RGBColor(0xF1, 0xF5, 0xF9)
CARD_BORDER = RGBColor(0xCF, 0xD7, 0xE7)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
EMERALD_BG = RGBColor(0xEC, 0xFD, 0xF5)
EMERALD_TXT = RGBColor(0x06, 0x5F, 0x46)

prs = pptx.Presentation(SRC_PPTX)
R_EMBED = "{http://schemas.openxmlformats.org/officeDocument/2006/relationships}embed"


def clone_chrome_slide(prs_obj, src_idx=3, skip_idxs=(6,)):
    """Duplicate a Canva chrome slide while preserving image relationships."""
    src = prs_obj.slides[src_idx]
    new_sl = prs_obj.slides.add_slide(prs_obj.slide_layouts[6])
    sp_tree = new_sl.shapes._spTree
    rid_map = {}

    for si, sh in enumerate(src.shapes):
        if si in skip_idxs:
            continue
        el = copy.deepcopy(sh._element)
        for node in el.iter():
            old_rid = node.attrib.get(R_EMBED)
            if old_rid:
                if old_rid not in rid_map:
                    rel = src.part.rels[old_rid]
                    rid_map[old_rid] = new_sl.part.relate_to(rel.target_part, rel.reltype)
                node.attrib[R_EMBED] = rid_map[old_rid]
        sp_tree.append(el)
    return new_sl


def set_text_keep_style(shape, new_text, align=None, color=None, size_pt=None, bold=None, font_name=None, line_spacing=None):
    tf = shape.text_frame
    r0 = tf.paragraphs[0].runs[0] if tf.paragraphs[0].runs else None
    orig_name = font_name or (r0.font.name if r0 and r0.font.name else "DM Sans")
    orig_size = Pt(size_pt) if size_pt else (r0.font.size if r0 and r0.font.size else Pt(16))
    orig_bold = bold if bold is not None else (r0.font.bold if r0 else None)
    orig_color = color
    if orig_color is None and r0 and r0.font.color and r0.font.color.type == 1:
        orig_color = r0.font.color.rgb

    tf.clear()
    for idx, line in enumerate(new_text.split("\n")):
        p = tf.paragraphs[0] if idx == 0 else tf.add_paragraph()
        if align is not None:
            p.alignment = align
        if line_spacing is not None:
            p.line_spacing = line_spacing
        p.space_after = Pt(4)
        run = p.add_run()
        run.text = line
        run.font.name = orig_name
        run.font.size = orig_size
        if orig_bold is not None:
            run.font.bold = orig_bold
        if orig_color is not None:
            run.font.color.rgb = orig_color


def add_runs_paragraph(tf, segments, first=False, space_after=6, line_spacing=1.18, align=PP_ALIGN.LEFT):
    p = tf.paragraphs[0] if first else tf.add_paragraph()
    p.alignment = align
    p.space_after = Pt(space_after)
    p.line_spacing = line_spacing
    for txt, opts in segments:
        r = p.add_run()
        r.text = txt
        r.font.name = opts.get("font", "DM Sans")
        r.font.size = Pt(opts.get("size", 15))
        r.font.bold = opts.get("bold", False)
        r.font.italic = opts.get("italic", False)
        r.font.color.rgb = opts.get("color", SLATE)
    return p


def add_card(slide, x, y, w, h, fill=WHITE, border=CARD_BORDER, border_pt=1.25, radius=0.04, shape_type=MSO_SHAPE.ROUNDED_RECTANGLE):
    sh = slide.shapes.add_shape(shape_type, Inches(x), Inches(y), Inches(w), Inches(h))
    if shape_type == MSO_SHAPE.ROUNDED_RECTANGLE:
        sh.adjustments[0] = radius
    sh.fill.solid()
    sh.fill.fore_color.rgb = fill
    if border is not None:
        sh.line.color.rgb = border
        sh.line.width = Pt(border_pt)
    else:
        sh.line.fill.background()
    sh.shadow.inherit = False
    return sh


def add_picture_fit(slide, img_path, x, y, w, h, pad=0.06):
    im = Image.open(img_path)
    iw, ih = im.size
    box_w = max(0.2, w - 2 * pad)
    box_h = max(0.2, h - 2 * pad)
    scale = min(box_w / iw, box_h / ih)
    pw = iw * scale
    ph = ih * scale
    px = x + (w - pw) / 2.0
    py = y + (h - ph) / 2.0
    return slide.shapes.add_picture(str(img_path), Inches(px), Inches(py), width=Inches(pw), height=Inches(ph))


def remove_shapes(slide, indices_desc):
    for idx in sorted(indices_desc, reverse=True):
        sp = slide.shapes[idx]._element
        sp.getparent().remove(sp)


def update_badge(slide):
    for sh in slide.shapes:
        if sh.has_text_frame and "SEMINAR PROPOSAL TA" in sh.text:
            set_text_keep_style(sh, "SEMINAR HASIL TA", align=PP_ALIGN.RIGHT, color=NAVY, size_pt=20.2, bold=True, font_name="DM Sans Bold")


def set_slide_title(slide, title_text):
    for sh in slide.shapes:
        if sh.has_text_frame and abs(sh.top - Inches(2.40)) < Inches(0.25) and abs(sh.left - Inches(1.25)) < Inches(0.25):
            set_text_keep_style(sh, title_text, color=NAVY, size_pt=34, bold=True, font_name="Merriweather Bold")
            return


# Clone 5 pristine chrome slides BEFORE mutating idx=3 (Perbandingan Terdahulu):
# 22 -> Daftar Isi
# 23 -> Sitemap Admin Toko
# 24 -> Sitemap Superadmin
# 25 -> Detail Database 1/2
# 26 -> Detail Database 2/2
s_toc = clone_chrome_slide(prs, src_idx=3, skip_idxs=(6,))      # idx 22
s_sm_admin = clone_chrome_slide(prs, src_idx=3, skip_idxs=(6,)) # idx 23
s_sm_super = clone_chrome_slide(prs, src_idx=3, skip_idxs=(6,)) # idx 24
s_erd_z1 = clone_chrome_slide(prs, src_idx=3, skip_idxs=(6,))   # idx 25
s_erd_z2 = clone_chrome_slide(prs, src_idx=3, skip_idxs=(6,))   # idx 26

for sl in prs.slides:
    update_badge(sl)

# =========================================================
# 1. Cover (slot 0)
# =========================================================
s1 = prs.slides[0]
set_text_keep_style(s1.shapes[14], "Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.", align=PP_ALIGN.CENTER, color=NAVY, size_pt=20.2, bold=True, font_name="DM Sans Bold")

# =========================================================
# 2. Daftar Isi (slot 22)
# =========================================================
set_slide_title(s_toc, "Daftar Isi")
toc_items = [
    ("01", "Latar Belakang, Tujuan & Batasan", "Kendala stok terpisah, tujuan, manfaat, & 3 batasan sistem"),
    ("02", "Penelitian Terdahulu & Metodologi", "Komparasi studi sejenis (gap riset) & metode RAD 4 fase"),
    ("03", "Workflow & Sitemap (4 Peran)", "Alur keluar-masuk terbaru & struktur navigasi tiap pengguna"),
    ("04", "Use Case & Logical Database", "Pemodelan 4 aktor sistem & ERD 20 tabel (full & zoom)"),
    ("05", "Demo Sistem & Hasil Evaluasi (UAT)", "Demonstrasi alur utama & hasil pengujian skenario pengguna"),
    ("06", "Kesimpulan & Lampiran Dokumentasi", "Rangkuman capaian hasil & dokumentasi foto lapangan"),
]
for i, (num, title_t, desc_t) in enumerate(toc_items):
    col = i // 3
    row = i % 3
    cx = 1.25 + col * 8.95
    cy = 3.70 + row * 2.15
    add_card(s_toc, cx, cy, 8.55, 1.80, fill=LIGHT_BG, border=CARD_BORDER)
    add_card(s_toc, cx + 0.25, cy + 0.35, 1.05, 1.05, fill=NAVY, border=None, radius=0.18)
    btb = s_toc.shapes.add_textbox(Inches(cx + 0.25), Inches(cy + 0.56), Inches(1.05), Inches(0.65))
    add_runs_paragraph(btb.text_frame, [(num, {"font": "Merriweather Bold", "size": 26, "bold": True, "color": WHITE})], first=True, align=PP_ALIGN.CENTER)
    ttb = s_toc.shapes.add_textbox(Inches(cx + 1.50), Inches(cy + 0.34), Inches(6.80), Inches(1.20))
    ttb.text_frame.word_wrap = True
    add_runs_paragraph(ttb.text_frame, [(title_t, {"font": "DM Sans Bold", "size": 19, "bold": True, "color": NAVY})], first=True, space_after=4)
    add_runs_paragraph(ttb.text_frame, [(desc_t, {"size": 14.5, "color": SLATE})], space_after=0)

# =========================================================
# 3. Latar Belakang Masalah (slot 1)
# =========================================================
s2 = prs.slides[1]
set_text_keep_style(
    s2.shapes[11],
    "Aplikasi kasir pihak ketiga (stand-alone) belum terhubung dengan penjualan daring, sehingga staf harus mencocokkan stok manual setiap hari dan barang kosong tetap berisiko terpesan daring (overselling).",
    color=SLATE, size_pt=16.0
)
set_text_keep_style(
    s2.shapes[12],
    "Toko daring melayani kemasan besar (Box) sedangkan gerai fisik melayani eceran (Strip/Pcs) dan grosir. Ketiadaan integrasi memaksa konversi manual yang rawan human error serta mengaburkan batas stok kritis.",
    color=SLATE, size_pt=16.0
)
set_text_keep_style(
    s2.shapes[13],
    "Obat belum didata berdasarkan nomor Batch dan kedaluwarsa (FEFO), alat kesehatan belum memiliki rekam Serial Number untuk klaim garansi, serta kemasan pecah eceran belum berlabel barcode fisik di rak.",
    color=SLATE, size_pt=16.0
)
set_text_keep_style(
    s2.shapes[15],
    "Terpisahnya sistem kasir fisik dan penjualan daring menimbulkan selisih stok serta pesanan ganda (overselling), kerumitan konversi multi-satuan secara manual, serta ketiadaan kendali FEFO, pelacakan nomor seri garansi, dan validasi label barcode fisik.",
    color=SLATE, size_pt=16.0
)

# =========================================================
# 4. Tujuan, Manfaat, dan Batasan Masalah (slot 9)
# =========================================================
s_tmb = prs.slides[9]
set_slide_title(s_tmb, "Tujuan, Manfaat, dan Batasan Masalah")
remove_shapes(s_tmb, [4, 5, 6, 9])

add_card(s_tmb, 1.25, 3.35, 8.55, 3.25, fill=LIGHT_BG, border=CARD_BORDER, border_pt=1.5)
add_card(s_tmb, 1.25, 3.35, 8.55, 0.58, fill=NAVY, border=None, radius=0.02)
add_runs_paragraph(s_tmb.shapes.add_textbox(Inches(1.50), Inches(3.43), Inches(8.05), Inches(0.42)).text_frame,
                   [("Tujuan", {"font": "Merriweather Bold", "size": 19, "bold": True, "color": WHITE})], first=True, align=PP_ALIGN.CENTER)
t_body_l = s_tmb.shapes.add_textbox(Inches(1.55), Inches(4.08), Inches(7.95), Inches(2.35))
t_body_l.text_frame.word_wrap = True
add_runs_paragraph(
    t_body_l.text_frame,
    [("Membangun sistem inventori omnichannel terpusat yang menyinkronkan stok kasir fisik (POS) dan e-commerce mandiri secara real-time, mengotomatisasi konversi Multi-UOM, serta menegakkan kendali kedaluwarsa berbasis FEFO dan Serial Number melalui label barcode fisik.",
      {"size": 15, "color": SLATE})],
    first=True, line_spacing=1.28, align=PP_ALIGN.LEFT
)

add_card(s_tmb, 10.20, 3.35, 8.55, 3.25, fill=LIGHT_BG, border=CARD_BORDER, border_pt=1.5)
add_card(s_tmb, 10.20, 3.35, 8.55, 0.58, fill=DARK_NAVY, border=None, radius=0.02)
add_runs_paragraph(s_tmb.shapes.add_textbox(Inches(10.45), Inches(3.43), Inches(8.05), Inches(0.42)).text_frame,
                   [("Manfaat", {"font": "Merriweather Bold", "size": 19, "bold": True, "color": WHITE})], first=True, align=PP_ALIGN.CENTER)
t_body_r = s_tmb.shapes.add_textbox(Inches(10.50), Inches(4.08), Inches(7.95), Inches(2.35))
t_body_r.text_frame.word_wrap = True
add_runs_paragraph(
    t_body_r.text_frame,
    [("Membantu ZenCare Medical meniadakan overselling, menekan kerugian akibat obat kedaluwarsa, mencegah deviasi stok kasir, mempercepat operasional harian, serta menjadi rujukan aplikatif sistem inventori UMKM kesehatan bagi Universitas Ma Chung.",
      {"size": 15, "color": SLATE})],
    first=True, line_spacing=1.28, align=PP_ALIGN.LEFT
)

add_card(s_tmb, 1.25, 6.78, 17.50, 3.60, fill=WHITE, border=NAVY, border_pt=1.5)
add_card(s_tmb, 1.25, 6.78, 17.50, 0.52, fill=NAVY, border=None, radius=0.02)
add_runs_paragraph(s_tmb.shapes.add_textbox(Inches(1.55), Inches(6.85), Inches(16.90), Inches(0.38)).text_frame,
                   [("Batasan Masalah", {"font": "Merriweather Bold", "size": 17.5, "bold": True, "color": WHITE})], first=True, align=PP_ALIGN.CENTER)

batasan_3 = [
    ("1. Sinkronisasi & Lokasi Cabang",
     "Menyinkronkan stok terminal POS dan e-commerce mandiri pada 1 cabang pusat (ZenCare Medical Muharto), tanpa modul pembukuan finansial maupun poin pelanggan."),
    ("2. Kendali FEFO, SN, & Opname",
     "Menerapkan FEFO berbasis Batch untuk obat dan Serial Number garansi alat kesehatan, lengkap dengan cetak stiker fisik serta siklus Stok Opname terpusat."),
    ("3. Otomatisasi Satuan & Sandbox",
     "Pemotongan otomatis ke Base UOM tanpa modul repacking, pre-order, atau refund otomatis. Integrasi Midtrans dan RajaOngkir berjalan pada mode sandbox.")
]
for bi, (bh, bb) in enumerate(batasan_3):
    bx = 1.48 + bi * 5.78
    add_card(s_tmb, bx, 7.45, 5.48, 2.75, fill=LIGHT_BG, border=CARD_BORDER)
    bt = s_tmb.shapes.add_textbox(Inches(bx + 0.18), Inches(7.58), Inches(5.12), Inches(2.50))
    bt.text_frame.word_wrap = True
    add_runs_paragraph(bt.text_frame, [(bh, {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY})], first=True, space_after=4)
    add_runs_paragraph(bt.text_frame, [(bb, {"size": 13.5, "color": SLATE})], space_after=0, line_spacing=1.18)

# =========================================================
# 5. Perbandingan Penelitian Terdahulu (slot 3)
# =========================================================
s_pt = prs.slides[3]
set_slide_title(s_pt, "Perbandingan Penelitian Terdahulu")
remove_shapes(s_pt, [6])

pt_rows = [
    ["Peneliti (Tahun)", "Fokus & Metode Studi", "Platform", "Celah Riset / Pembeda dengan ZenCare Medical"],
    ["Rachman dkk. (2024)\nToko Obat Murah Bung Tomo",
     "Migrasi pencatatan stok obat masuk/keluar dari buku ke web (Waterfall).",
     "React.js, Express.js,\nMySQL",
     "Masih stand-alone (gerai fisik saja) tanpa sinkronisasi toko daring, belum mendukung konversi Multi-UOM maupun pelacakan kedaluwarsa berbasis Batch (FEFO)."],
    ["Nuralisa dkk. (2024)\nToko Obat Amanah Karya Farma",
     "Percepatan pencarian & penyusunan laporan persediaan obat (R&D Waterfall).",
     "PHP Laravel 10,\nMySQL",
     "Berfokus pada pelaporan internal luring; belum terintegrasi penjualan daring (omnichannel), sehingga belum memiliki penguncian anti-overselling lintas kanal."],
    ["Mattegunta (2025)\nRitel Omnichannel Umum",
     "Integrasi stok real-time lintas saluran fisik dan digital (komparasi omnichannel).",
     "POS Terintegrasi\nPlatform Cloud",
     "Diterapkan pada ritel umum tanpa pengawasan karakteristik medis: tidak mewajibkan alokasi FEFO berbasis Batch obat maupun pelacakan Serial Number garansi alkes."],
    ["Sistem Usulan (2026)\nZenCare Medical",
     "Tata kelola inventori & penjualan omnichannel obat serta alkes (Metode RAD).",
     "PHP, MySQL InnoDB,\nMidtrans & RajaOngkir",
     "Menggabungkan POS + E-Commerce real-time (SELECT ... FOR UPDATE), otomasi Multi-UOM, Gatekeeper FEFO & SN + Stiker Barcode, hingga Stok Opname (Freeze Lock & Blind Count)."]
]
pt_sh = s_pt.shapes.add_table(len(pt_rows), 4, Inches(1.15), Inches(3.40), Inches(17.70), Inches(6.95))
pt_tbl = pt_sh.table
for ci, w in enumerate([3.65, 4.15, 2.75, 7.15]):
    pt_tbl.columns[ci].width = Inches(w)

for ri, rdata in enumerate(pt_rows):
    is_hdr = (ri == 0)
    is_us = (ri == len(pt_rows) - 1)
    for ci, val in enumerate(rdata):
        cell = pt_tbl.cell(ri, ci)
        cell.vertical_anchor = MSO_ANCHOR.MIDDLE
        cell.fill.solid()
        cell.fill.fore_color.rgb = NAVY if is_hdr else (EMERALD_BG if is_us else (WHITE if ri % 2 == 1 else LIGHT_BG))
        tf = cell.text_frame
        tf.word_wrap = True
        tf.clear()
        for li, line in enumerate(val.split("\n")):
            p = tf.paragraphs[0] if li == 0 else tf.add_paragraph()
            p.alignment = PP_ALIGN.CENTER if is_hdr else PP_ALIGN.LEFT
            p.line_spacing = 1.15
            r = p.add_run()
            r.text = line
            is_bold_line = is_hdr or is_us or (ci == 0 and li == 0)
            r.font.name = "DM Sans Bold" if is_bold_line else "DM Sans"
            r.font.size = Pt(14 if is_hdr else 13)
            r.font.bold = is_bold_line
            r.font.italic = (ci == 0 and li == 1 and not is_hdr)
            r.font.color.rgb = WHITE if is_hdr else (EMERALD_TXT if (is_us and ci in (0, 3)) else (NAVY if (ci == 0 and li == 0) else SLATE))

# =========================================================
# 6. Metodologi Pengembangan — RAD (slot 4)
# =========================================================
s_rad = prs.slides[4]
set_slide_title(s_rad, "Metodologi Pengembangan")
remove_shapes(s_rad, [6])

rad_quads = [
    ("01", "Requirements Planning", 0.90, 3.55,
     "Mengidentifikasi kendala langsung di ZenCare Medical melalui observasi dan wawancara, mulai dari stok toko fisik dan toko daring yang terpisah, perbedaan satuan kemasan, hingga pencatatan masa kedaluwarsa dan nomor seri."),
    ("02", "User Design", 0.90, 7.05,
     "Merancang ulang alur kerja (Workflow), pemodelan sistem (Sitemap 4 peran, Use Case 4 aktor, dan Activity Diagram), struktur database 20 tabel, serta rancangan tampilan aplikasi."),
    ("03", "Construction", 12.60, 3.55,
     "Membangun aplikasi web secara bertahap dan terpusat dalam satu database, mencakup fitur kasir toko, toko daring (pembayaran Midtrans & ongkos kirim RajaOngkir), penerimaan barang, stok opname, serta cetak struk, stiker barcode, dan laporan A4."),
    ("04", "Cutover & Testing", 12.60, 7.05,
     "Melakukan pengujian langsung bersama pengguna (UAT) pada 4 peran sesuai alur operasional (hasil Berhasil di 8 modul), memasang alat barcode scanner, dan menyiapkan sistem untuk digunakan di toko.")
]
for num, head_t, qx, qy, desc_t in rad_quads:
    add_card(s_rad, qx, qy, 6.45, 0.78, fill=NAVY, border=None, radius=0.45)
    ntb = s_rad.shapes.add_textbox(Inches(qx + 0.25), Inches(qy + 0.14), Inches(0.75), Inches(0.50))
    add_runs_paragraph(ntb.text_frame, [(num, {"font": "DM Sans Bold", "size": 20, "bold": True, "color": TEAL})], first=True)
    htb = s_rad.shapes.add_textbox(Inches(qx + 1.00), Inches(qy + 0.16), Inches(5.20), Inches(0.45))
    add_runs_paragraph(htb.text_frame, [(head_t, {"font": "DM Sans Bold", "size": 19, "bold": True, "color": WHITE})], first=True)
    add_card(s_rad, qx + 0.10, qy + 0.90, 6.25, 2.30, fill=LIGHT_BG, border=CARD_BORDER)
    dtb = s_rad.shapes.add_textbox(Inches(qx + 0.28), Inches(qy + 1.02), Inches(5.90), Inches(2.05))
    dtb.text_frame.word_wrap = True
    add_runs_paragraph(dtb.text_frame, [(desc_t, {"size": 14, "color": SLATE})], first=True, line_spacing=1.22)

rad_nodes = [
    ("R", 8.05, 4.75, NAVY),
    ("A", 10.25, 6.05, TEAL_DARK),
    ("D", 8.05, 7.35, DARK_NAVY),
]
c1 = s_rad.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(9.35), Inches(5.55), Inches(1.40), Inches(0.14))
c1.rotation = 32; c1.fill.solid(); c1.fill.fore_color.rgb = TEAL; c1.line.fill.background()
c2 = s_rad.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(9.35), Inches(7.50), Inches(1.40), Inches(0.14))
c2.rotation = -32; c2.fill.solid(); c2.fill.fore_color.rgb = TEAL; c2.line.fill.background()

for ch, nx, ny, col in rad_nodes:
    add_card(s_rad, nx, ny, 1.55, 1.55, fill=col, border=WHITE, border_pt=3.0, shape_type=MSO_SHAPE.OVAL)
    ctb = s_rad.shapes.add_textbox(Inches(nx), Inches(ny + 0.28), Inches(1.55), Inches(1.00))
    add_runs_paragraph(ctb.text_frame, [(ch, {"font": "Merriweather Bold", "size": 44, "bold": True, "color": WHITE})], first=True, align=PP_ALIGN.CENTER)

add_runs_paragraph(
    s_rad.shapes.add_textbox(Inches(7.70), Inches(9.20), Inches(4.55), Inches(0.75)).text_frame,
    [("Rapid Application Development\n(BAB III Subbab 3.1)", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": MUTED})],
    first=True, align=PP_ALIGN.CENTER
)

# =========================================================
# 7. Workflow Barang Keluar (Sistem Usulan) (slot 5)
# =========================================================
s_wf1 = prs.slides[5]
set_slide_title(s_wf1, "Workflow Barang Keluar (Sistem Usulan)")
remove_shapes(s_wf1, [4, 5, 6, 8, 9, 10, 11])

add_card(s_wf1, 1.15, 3.30, 5.60, 7.35, fill=WHITE, border=CARD_BORDER)
add_picture_fit(s_wf1, WF_DIR / "Workflow Barang Keluar (Sistem Usulan).png", 1.25, 3.38, 5.40, 7.18, pad=0.06)

add_card(s_wf1, 7.05, 3.30, 11.80, 7.35, fill=LIGHT_BG, border=CARD_BORDER)
wf1_tb = s_wf1.shapes.add_textbox(Inches(7.45), Inches(3.75), Inches(11.00), Inches(6.50))
wf1_tb.text_frame.word_wrap = True
add_runs_paragraph(wf1_tb.text_frame, [("Alur Barang Keluar (3 Jalur)", {"font": "Merriweather Bold", "size": 19, "bold": True, "color": NAVY})], first=True, space_after=14)
for wh, wb in [
    ("1. Kasir Toko Fisik (POS):",
     "Pelanggan ke kasir \u2192 pindai barcode \u2192 pecah kemasan otomatis bila perlu \u2192 sistem pilih kedaluwarsa terdekat (FEFO) / nomor seri \u2192 stok berkurang & struk tercetak."),
    ("2. Toko Daring (Pengiriman Kurir):",
     "Konsumen checkout kemasan besar & bayar Midtrans \u2192 stok dikunci maks. 2 jam \u2192 Admin kemas, pindai barcode, & input resi."),
    ("3. Toko Daring (Ambil di Toko):",
     "Konsumen pesan & bayar di website \u2192 Admin siapkan sesuai FEFO \u2192 diambil di gerai \u2192 status selesai & Kartu Stok tercatat.")
]:
    add_runs_paragraph(wf1_tb.text_frame, [(wh, {"font": "DM Sans Bold", "size": 16, "bold": True, "color": DARK_NAVY})], space_after=3)
    add_runs_paragraph(wf1_tb.text_frame, [(wb, {"size": 14.5, "color": SLATE})], space_after=16, line_spacing=1.22)

# =========================================================
# 8. Workflow Barang Masuk & Logistik (Sistem Usulan) (slot 6)
# =========================================================
s_wf2 = prs.slides[6]
set_slide_title(s_wf2, "Workflow Barang Masuk & Logistik (Sistem Usulan)")
remove_shapes(s_wf2, [4, 5, 6, 7, 9, 10, 11, 12])

add_card(s_wf2, 1.15, 3.30, 8.20, 7.35, fill=WHITE, border=CARD_BORDER)
add_picture_fit(s_wf2, WF_DIR / "Workflow Barang Masuk & Logistik (Sistem Usulan).png", 1.25, 3.38, 8.00, 7.18, pad=0.06)

add_card(s_wf2, 9.65, 3.30, 9.20, 7.35, fill=LIGHT_BG, border=CARD_BORDER)
wf2_tb = s_wf2.shapes.add_textbox(Inches(10.00), Inches(3.68), Inches(8.50), Inches(6.60))
wf2_tb.text_frame.word_wrap = True
add_runs_paragraph(wf2_tb.text_frame, [("Alur Barang Masuk (4 Tahap)", {"font": "Merriweather Bold", "size": 19, "bold": True, "color": NAVY})], first=True, space_after=12)
for wh, wb in [
    ("1. Pemesanan / Pembelian:",
     "Cek stok menipis \u2192 pesan ke distributor resmi atau beli grosir \u2192 paket & faktur tiba."),
    ("2. Pemeriksaan Fisik:",
     "Cocokkan jumlah fisik dengan faktur/nota dan pisahkan jika ada barang rusak."),
    ("3. Pencatatan Sistem:",
     "Input jumlah masuk, harga beli, batch & tanggal kedaluwarsa, atau nomor seri alat."),
    ("4. Cetak Label & Tata Rak:",
     "Stok & Kartu Stok bertambah \u2192 cetak stiker barcode \u2192 tata rak sesuai urutan kedaluwarsa.")
]:
    add_runs_paragraph(wf2_tb.text_frame, [(wh, {"font": "DM Sans Bold", "size": 15.5, "bold": True, "color": DARK_NAVY})], space_after=2)
    add_runs_paragraph(wf2_tb.text_frame, [(wb, {"size": 14.2, "color": SLATE})], space_after=13, line_spacing=1.20)


# Helper for Opsi B Sitemap slides (Gambar besar kiri + penjelasan fitur terbaru kanan!)
def build_sitemap_slide(slide, title, img_path, role_heading, points, remove_idxs=()):
    set_slide_title(slide, title)
    if remove_idxs:
        remove_shapes(slide, remove_idxs)
    add_card(slide, 1.15, 3.30, 9.55, 7.35, fill=WHITE, border=CARD_BORDER)
    add_picture_fit(slide, img_path, 1.25, 3.38, 9.35, 7.18)

    add_card(slide, 11.00, 3.30, 7.85, 7.35, fill=LIGHT_BG, border=CARD_BORDER)
    tb = slide.shapes.add_textbox(Inches(11.30), Inches(3.55), Inches(7.25), Inches(6.90))
    tb.text_frame.word_wrap = True
    add_runs_paragraph(tb.text_frame, [(role_heading, {"font": "Merriweather Bold", "size": 18, "bold": True, "color": NAVY})], first=True, space_after=10)
    for ph, pb in points:
        add_runs_paragraph(tb.text_frame, [(ph, {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": DARK_NAVY})], space_after=1)
        add_runs_paragraph(tb.text_frame, [(pb, {"size": 13.3, "color": SLATE})], space_after=10, line_spacing=1.16)


# =========================================================
# 9. Sitemap — Pelanggan (slot 7)
# =========================================================
build_sitemap_slide(
    prs.slides[7], "Sitemap — Pelanggan (E-Commerce)", ASSETS / "sitemap_3_5.png",
    "Portal Toko Daring Konsumen",
    [
        ("Masuk Akun & Katalog Produk:", "Mendaftar akun baru, masuk akun, dan melihat produk kemasan besar utuh dengan jumlah stok terkini."),
        ("Keranjang Belanja:", "Mengubah jumlah atau menghapus barang dengan pengecekan stok otomatis sebelum bayar."),
        ("Checkout & Pembayaran:", "Memilih pengiriman kurir atau ambil langsung di toko, lalu membayar secara daring melalui Midtrans."),
        ("Pantau & Batalkan Pesanan:", "Melihat perkembangan status pesanan hingga selesai serta mengajukan pembatalan jika diperlukan.")
    ],
    remove_idxs=[4, 5, 6, 8]
)

# =========================================================
# 10. Sitemap — Kasir POS & Opname (slot 8)
# =========================================================
build_sitemap_slide(
    prs.slides[8], "Sitemap — Kasir POS & Stok Opname", ASSETS / "sitemap_3_6.png",
    "Antarmuka Meja Kasir & Hitung Fisik",
    [
        ("Cari & Pindai Barcode:", "Mencari barang atau memindai stiker barcode kemasan dengan cepat di meja kasir."),
        ("Penjualan & Bongkar Kemasan:", "Melayani jual eceran maupun kemasan besar, termasuk memecah kemasan besar saat stok eceran kosong."),
        ("Pilih Batch Kedaluwarsa & Nomor Seri:", "Sistem mengarahkan produk kedaluwarsa terdekat (FEFO) dan nomor seri alat, serta mencatat alasan jika kasir memilih batch lain."),
        ("Hitung Fisik Stok Opname:", "Saat proses stok opname dibuka, kasir menginput hasil hitungan fisik (Baik, Rusak, Kedaluwarsa) tanpa melihat jumlah stok di sistem.")
    ],
    remove_idxs=[4, 5, 6, 8]
)

# =========================================================
# 11. Sitemap — Admin Toko (slot 23)
# =========================================================
build_sitemap_slide(
    s_sm_admin, "Sitemap — Admin Toko", ASSETS / "sitemap_3_7.png",
    "Pengelolaan Stok, Logistik & Pesanan",
    [
        ("Ringkasan Stok & Data Produk:", "Memantau stok menipis, barang mendekati kedaluwarsa, pesanan masuk, serta mengelola data produk, satuan, dan supplier."),
        ("Kelola Pesanan Daring:", "Menyiapkan barang pesanan daring dan ambil di toko, mencetak label pengiriman dan faktur, serta memproses pembatalan."),
        ("Barang Masuk, Konversi & Mutasi:", "Mencatat barang masuk (batch, kedaluwarsa, harga beli, nomor seri), mencetak stiker barcode, konversi satuan, dan mencatat barang rusak."),
        ("Verifikasi Selisih Opname:", "Mengecek selisih hasil hitungan fisik kasir, melakukan penyesuaian stok ke Kartu Stok, dan mencetak Berita Acara.")
    ]
)

# =========================================================
# 12. Sitemap — Superadmin / Pemilik (slot 24)
# =========================================================
build_sitemap_slide(
    s_sm_super, "Sitemap — Superadmin (Pemilik Toko)", ASSETS / "sitemap_3_8.png",
    "Kendali Pemilik & Laporan Usaha",
    [
        ("Ringkasan Usaha:", "Memantau pendapatan toko fisik dan daring, tren penjualan, produk terlaris, stok menipis, serta status stok opname."),
        ("Atur Harga & Akun Pengguna:", "Menetapkan harga jual eceran dan kemasan besar, mengatur akun staf (Admin dan Kasir), serta pengaturan tampilan toko."),
        ("Kontrol Sesi Stok Opname:", "Membuka penguncian transaksi saat stok opname dimulai, mengatur tahap verifikasi, dan membuka kunci toko kembali."),
        ("6 Laporan Resmi Format A4:", "Mencetak laporan Penjualan, Persediaan, Penerimaan Barang, Logistik Medis, Kartu Stok, dan Catatan Deviasi FEFO.")
    ]
)

# =========================================================
# 13. Use Case (slot 10)
# =========================================================
s_uc = prs.slides[10]
set_slide_title(s_uc, "Use Case")
remove_shapes(s_uc, [4, 7])

add_card(s_uc, 1.15, 3.30, 9.55, 7.35, fill=WHITE, border=CARD_BORDER)
add_picture_fit(s_uc, ASSETS / "use_case.png", 1.25, 3.38, 9.35, 7.18)

add_card(s_uc, 11.00, 3.30, 7.85, 7.35, fill=LIGHT_BG, border=CARD_BORDER)
uc_tb = s_uc.shapes.add_textbox(Inches(11.30), Inches(3.52), Inches(7.25), Inches(6.95))
uc_tb.text_frame.word_wrap = True
for idx, (uh, ub) in enumerate([
    ("Pelanggan (Toko Daring):",
     "Melihat katalog produk kemasan utuh yang stoknya terhubung langsung dengan toko fisik, berbelanja hingga membayar via Midtrans (stok otomatis terkunci sementara), serta memantau atau mengajukan pembatalan pesanan."),
    ("Kasir (POS & Hitung Fisik):",
     "Melayani transaksi langsung di kasir dengan pindai barcode dan pecah kemasan besar, mengikuti arahan barang kedaluwarsa terdekat (FEFO), mencetak struk, serta menginput hasil hitungan fisik saat stok opname."),
    ("Admin Toko (Logistik & Pesanan):",
     "Mencatat penerimaan barang (batch, kedaluwarsa, harga beli, nomor seri) dan cetak stiker barcode, menyiapkan pesanan daring dan label kirim, mencatat mutasi barang rusak, serta memverifikasi selisih stok opname."),
    ("Superadmin (Pemilik Toko):",
     "Mengatur harga jual produk, mengelola akun staf dan pengaturan website, membuka dan menutup penguncian transaksi saat stok opname, memantau ringkasan usaha, serta mencetak 6 laporan resmi A4.")
]):
    add_runs_paragraph(uc_tb.text_frame, [(uh, {"font": "DM Sans Bold", "size": 15, "bold": True, "color": NAVY})], first=(idx == 0), space_after=1)
    add_runs_paragraph(uc_tb.text_frame, [(ub, {"size": 13.4, "color": SLATE})], space_after=11, line_spacing=1.16)

# =========================================================
# 14. Logical Database — Arsitektur Penuh 20 Tabel (slot 15)
# =========================================================
s16 = prs.slides[15]
set_slide_title(s16, "Logical Database (Arsitektur Penuh 20 Tabel)")
remove_shapes(s16, [4, 5, 6, 7])

add_card(s16, 1.15, 3.30, 6.45, 7.45, fill=LIGHT_BG, border=CARD_BORDER)
tb16 = s16.shapes.add_textbox(Inches(1.38), Inches(3.45), Inches(6.00), Inches(7.15))
tb16.text_frame.word_wrap = True
ldb_blocks = [
    ("1. Data Master & Stok Aktif (7 Tabel):",
     "produk_induk dan produk_variasi (satuan besar dan eceran) terhubung ke stok_toko, stok_batch (kendali kedaluwarsa), dan unit_serial (nomor seri alat), ditambah users serta pengaturan_web."),
    ("2. Barang Masuk & Penjualan (6 Tabel):",
     "supplier masuk ke penerimaan_stok dan penerimaan_detail (harga beli). Penjualan kasir dan daring tercatat bersama di penjualan, detail_penjualan, dan pesanan_batch_alokasi."),
    ("3. Kartu Stok & Catatan Khusus (3 Tabel):",
     "Semua barang masuk, keluar, dan rusak tercatat di kartu_stok, dilengkapi histori_konversi (pecah kemasan) dan log_anomali_fefo."),
    ("4. Proses Stok Opname (4 Tabel):",
     "sesi_opname (jadwal & penguncian toko), detail_opname_batch, detail_opname_sn, serta riwayat_hitung_opname — detailnya diperbesar di 2 slide berikutnya.")
]
for idx, (gh, gb) in enumerate(ldb_blocks):
    add_runs_paragraph(tb16.text_frame, [(gh, {"font": "DM Sans Bold", "size": 14, "bold": True, "color": NAVY})], first=(idx == 0), space_after=1)
    add_runs_paragraph(tb16.text_frame, [(gb, {"size": 12.8, "color": SLATE})], space_after=8, line_spacing=1.15)

add_card(s16, 7.80, 1.32, 11.30, 9.10, fill=WHITE, border=CARD_BORDER)
add_picture_fit(s16, pathlib.Path("lain/Gambar_3.18_Logical_Database_Model.png"), 7.90, 1.40, 11.10, 8.94, pad=0.05)

# =========================================================
# 15. Detail Database (1/2) (slot 25)
# =========================================================
set_slide_title(s_erd_z1, "Detail Database (1/2): Pasokan, Transaksi & Kartu Stok")
add_card(s_erd_z1, 1.15, 3.30, 8.70, 5.75, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s_erd_z1.shapes.add_textbox(Inches(1.35), Inches(3.38), Inches(8.30), Inches(0.36)).text_frame,
                   [("Kelompok Barang Masuk & Kartu Stok (5 Tabel)", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s_erd_z1, ASSETS / "db_crop_1a_pasokan_kartu.png", 1.25, 3.74, 8.50, 5.22, pad=0.04)

add_card(s_erd_z1, 10.15, 3.30, 8.70, 5.75, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s_erd_z1.shapes.add_textbox(Inches(10.35), Inches(3.38), Inches(8.30), Inches(0.36)).text_frame,
                   [("Kelompok Transaksi Kasir / Daring & Nomor Seri (5 Tabel)", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s_erd_z1, ASSETS / "db_crop_1b_penjualan.png", 10.25, 3.74, 8.50, 5.22, pad=0.04)

add_card(s_erd_z1, 1.15, 9.20, 17.70, 1.35, fill=LIGHT_BG, border=NAVY, border_pt=1.5)
q1_tb = s_erd_z1.shapes.add_textbox(Inches(1.40), Inches(9.30), Inches(17.20), Inches(1.15))
q1_tb.text_frame.word_wrap = True
add_runs_paragraph(q1_tb.text_frame, [
    ("Penyimpanan Data Utama: ", {"font": "DM Sans Bold", "size": 14, "bold": True, "color": NAVY}),
    ("Transaksi kasir dan toko daring disimpan di ", {"size": 13.5, "color": SLATE}),
    ("penjualan + detail_penjualan", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    (" (kunci stok sementara di ", {"size": 13.5, "color": SLATE}),
    ("pesanan_batch_alokasi", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    ("); Riwayat barang masuk, keluar, dan rusak di ", {"size": 13.5, "color": SLATE}),
    ("kartu_stok", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    ("; Barang masuk di ", {"size": 13.5, "color": SLATE}),
    ("penerimaan_stok + penerimaan_detail", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    ("; Nomor seri alat di ", {"size": 13.5, "color": SLATE}),
    ("unit_serial", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    (".", {"size": 13.5, "color": SLATE}),
], first=True, line_spacing=1.22)

# =========================================================
# 16. Detail Database (2/2) (slot 26)
# =========================================================
set_slide_title(s_erd_z2, "Detail Database (2/2): Master, Batch FEFO & Stok Opname")
add_card(s_erd_z2, 1.15, 3.30, 8.70, 5.75, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s_erd_z2.shapes.add_textbox(Inches(1.35), Inches(3.38), Inches(8.30), Inches(0.36)).text_frame,
                   [("Kelompok Master Produk, Satuan & Stok Batch (5 Tabel)", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s_erd_z2, ASSETS / "db_crop_2a_master_batch.png", 1.25, 3.74, 8.50, 5.22, pad=0.04)

add_card(s_erd_z2, 10.15, 3.30, 8.70, 5.75, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s_erd_z2.shapes.add_textbox(Inches(10.35), Inches(3.38), Inches(8.30), Inches(0.36)).text_frame,
                   [("Kelompok Konversi, Deviasi FEFO & Stok Opname (6 Tabel)", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s_erd_z2, ASSETS / "db_crop_2b_opname_audit.png", 10.25, 3.74, 8.50, 5.22, pad=0.04)

add_card(s_erd_z2, 1.15, 9.20, 17.70, 1.35, fill=LIGHT_BG, border=NAVY, border_pt=1.5)
q2_tb = s_erd_z2.shapes.add_textbox(Inches(1.40), Inches(9.30), Inches(17.20), Inches(1.15))
q2_tb.text_frame.word_wrap = True
add_runs_paragraph(q2_tb.text_frame, [
    ("Penyimpanan Data Utama: ", {"font": "DM Sans Bold", "size": 14, "bold": True, "color": NAVY}),
    ("Data kedaluwarsa FEFO disimpan di ", {"size": 13.5, "color": SLATE}),
    ("stok_batch", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    (" (no_batch, tgl_exp, stok_sisa); Variasi satuan di ", {"size": 13.5, "color": SLATE}),
    ("produk_variasi", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    ("; Pecah kemasan di ", {"size": 13.5, "color": SLATE}),
    ("histori_konversi", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    (" & selisih pilihan batch di ", {"size": 13.5, "color": SLATE}),
    ("log_anomali_fefo", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    ("; Hitung fisik di ", {"size": 13.5, "color": SLATE}),
    ("sesi_opname, detail_opname_batch, detail_opname_sn, & riwayat_hitung_opname", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}),
    (".", {"size": 13.5, "color": SLATE}),
], first=True, line_spacing=1.22)

# =========================================================
# 17. DEMO SISTEM (slot 21)
# =========================================================
s_demo = prs.slides[21]
remove_shapes(s_demo, [1, 4, 5, 6, 7, 8])

hero_tb = s_demo.shapes.add_textbox(Inches(2.00), Inches(2.25), Inches(16.00), Inches(1.40))
add_runs_paragraph(hero_tb.text_frame, [("DEMO SISTEM", {"font": "Merriweather Bold", "size": 66, "bold": True, "color": NAVY})], first=True, align=PP_ALIGN.CENTER)

line_sh = s_demo.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(8.50), Inches(3.80), Inches(3.00), Inches(0.08))
line_sh.fill.solid(); line_sh.fill.fore_color.rgb = TEAL; line_sh.line.fill.background()

sub_tb = s_demo.shapes.add_textbox(Inches(2.50), Inches(4.08), Inches(15.00), Inches(0.55))
add_runs_paragraph(sub_tb.text_frame, [("Demonstrasi Langsung Alur Toko Fisik, Toko Daring, FEFO, & Barcode Scanner", {"font": "DM Sans Bold", "size": 19, "bold": True, "color": MUTED})], first=True, align=PP_ALIGN.CENTER)

demo_steps = [
    ("1. Penerimaan Barang & Stiker Barcode", "Mencatat barang masuk beserta batch, kedaluwarsa/nomor seri, dan harga beli, lalu mencetak stiker barcode untuk ditempel ke barang contoh."),
    ("2. Kasir POS, Scanner & Cek Kedaluwarsa", "Memindai stiker barcode dengan scanner, menguji peringatan saat kasir memilih batch kedaluwarsa lebih lama, lalu mencetak struk."),
    ("3. Bentrok Stok Kasir vs Toko Daring", "Memasukkan stok terakhir ke keranjang toko daring \u2192 dibeli habis di kasir toko \u2192 klik bayar di website \u2192 sistem otomatis menolak sebelum pembayaran."),
    ("4. Pembatalan Pesanan & Label Kirim", "Membuktikan pembatalan pesanan otomatis mengembalikan stok siap jual, lalu mencetak label pengiriman kurir."),
    ("5. Stok Opname & Penyesuaian Stok", "Menunjukkan penguncian kasir saat opname, input hitungan fisik tanpa melihat saldo sistem, verifikasi selisih, dan Berita Acara.")
]
for i, (dh, db) in enumerate(demo_steps):
    dy = 4.90 + i * 1.04
    add_card(s_demo, 2.25, dy, 15.50, 0.90, fill=WHITE, border=CARD_BORDER, border_pt=1.25)
    stb = s_demo.shapes.add_textbox(Inches(2.55), Inches(dy + 0.14), Inches(14.90), Inches(0.65))
    stb.text_frame.word_wrap = True
    add_runs_paragraph(stb.text_frame, [(dh + " — ", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY}), (db, {"size": 15, "color": SLATE})], first=True)

# =========================================================
# 18. Hasil Pengujian Sistem — UAT (slot 20)
# =========================================================
s_uat = prs.slides[20]
set_slide_title(s_uat, "Hasil Pengujian Sistem (UAT)")
remove_shapes(s_uat, [6])

uat_rows = [
    ["No", "Bagian Modul Operasional Sistem", "Jml", "Peran Penguji Terlibat", "Hasil UAT"],
    ["1", "Autentikasi Staf & Kontrol Hak Akses (RBAC)", "2", "Pemilik, Admin, Kasir", "Berhasil"],
    ["2", "Master Produk Medis, Multi-UOM, & Pemasok", "2", "Admin Toko", "Berhasil"],
    ["3", "Terminal Kasir POS & Alokasi FEFO", "2", "Kasir Toko, Pemilik Toko", "Berhasil"],
    ["4", "Toko Daring & Sinkronisasi Omnichannel", "2", "Pelanggan, Admin, Kasir", "Berhasil"],
    ["5", "Penerimaan Pasokan Barang & Label Barcode", "2", "Admin Toko, Kasir Toko", "Berhasil"],
    ["6", "Mutasi Stok Manual & Buku Besar Kartu Stok", "2", "Admin Toko, Pemilik Toko", "Berhasil"],
    ["7", "Siklus Pengawasan Stok Opname Terpusat", "2", "Pemilik, Kasir, Admin", "Berhasil"],
    ["8", "Laporan Manajerial & Dasbor Eksekutif", "2", "Pemilik Toko, Admin Toko", "Berhasil"],
    ["", "REKAPITULASI KESELURUHAN (16 Skenario)", "16", "4 Peran Pengguna", "BERHASIL"],
]
tbl_sh = s_uat.shapes.add_table(len(uat_rows), 5, Inches(1.15), Inches(3.38), Inches(12.25), Inches(6.95))
tbl = tbl_sh.table
for ci, w in enumerate([0.70, 5.45, 0.85, 3.65, 1.60]):
    tbl.columns[ci].width = Inches(w)

for ri, row_data in enumerate(uat_rows):
    is_hdr = (ri == 0)
    is_tot = (ri == len(uat_rows) - 1)
    for ci, val in enumerate(row_data):
        cell = tbl.cell(ri, ci)
        cell.vertical_anchor = MSO_ANCHOR.MIDDLE
        cell.fill.solid()
        cell.fill.fore_color.rgb = NAVY if is_hdr else (EMERALD_BG if is_tot else (WHITE if ri % 2 == 1 else LIGHT_BG))
        tf = cell.text_frame
        tf.word_wrap = True
        tf.clear()
        p = tf.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT if (ci in (1, 3) and not is_hdr) else PP_ALIGN.CENTER
        r = p.add_run()
        r.text = val
        r.font.name = "DM Sans Bold" if (is_hdr or is_tot or ci in (0, 4)) else "DM Sans"
        r.font.size = Pt(13.5 if is_hdr or is_tot else 13)
        r.font.bold = (is_hdr or is_tot or ci in (0, 4))
        r.font.color.rgb = WHITE if is_hdr else (EMERALD_TXT if (is_tot or ci == 4) else SLATE)

add_card(s_uat, 13.65, 3.38, 5.20, 2.25, fill=EMERALD_BG, border=EMERALD_TXT, border_pt=1.5)
sc_tb = s_uat.shapes.add_textbox(Inches(13.85), Inches(3.55), Inches(4.80), Inches(1.90))
sc_tb.text_frame.word_wrap = True
add_runs_paragraph(sc_tb.text_frame, [("KEPUTUSAN PENGUJIAN UAT", {"font": "DM Sans Bold", "size": 14, "bold": True, "color": EMERALD_TXT})], first=True, align=PP_ALIGN.CENTER, space_after=4)
add_runs_paragraph(sc_tb.text_frame, [("BERHASIL", {"font": "Merriweather Bold", "size": 36, "bold": True, "color": EMERALD_TXT})], align=PP_ALIGN.CENTER, space_after=4)
add_runs_paragraph(sc_tb.text_frame, [("16 Skenario • 8 Bagian Modul\nSesuai Kebutuhan 4 Peran Pengguna", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": SLATE})], align=PP_ALIGN.CENTER)

add_card(s_uat, 13.65, 5.85, 5.20, 4.48, fill=LIGHT_BG, border=CARD_BORDER)
fn_tb = s_uat.shapes.add_textbox(Inches(13.90), Inches(6.05), Inches(4.70), Inches(4.15))
fn_tb.text_frame.word_wrap = True
add_runs_paragraph(fn_tb.text_frame, [("Temuan Utama Pengujian:", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True, space_after=8)
for pt_txt in [
    "• Setiap skenario diuji oleh peran pengguna yang relevan langsung dengan modulnya.",
    "• Scanner barcode & alokasi FEFO otomatis mencegah salah ambil obat mendekati ED.",
    "• Freeze Lock & Blind Count menjaga objektivitas stok opname tanpa bias angka.",
    "• Reservasi stok daring mencegah bentrok persediaan dengan kasir gerai fisik."
]:
    add_runs_paragraph(fn_tb.text_frame, [(pt_txt, {"size": 13.2, "color": SLATE})], space_after=8, line_spacing=1.18)

# =========================================================
# 19. Kesimpulan (slot 16)
# =========================================================
s17 = prs.slides[16]
set_text_keep_style(
    s17.shapes[5],
    "Berdasarkan hasil analisis, perancangan, implementasi, dan pengujian, Sistem Informasi Inventori Omnichannel ZenCare Medical berhasil dibangun sebagai sistem terpusat yang menyinkronkan persediaan secara waktu nyata (real-time) antara terminal kasir fisik (POS) dan toko daring (e-commerce) mandiri, sehingga meniadakan selisih pencatatan serta mencegah pesanan ganda (overselling) melalui mekanisme penguncian stok.\n\nOtomatisasi konversi Multi-UOM, pengendalian masa kedaluwarsa berbasis First Expired, First Out (FEFO), pelacakan nomor seri (Serial Number) alat kesehatan, hingga siklus Stok Opname terpusat berjalan akurat dan seluruh 16 skenario User Acceptance Testing (UAT) pada 8 modul operasional dinyatakan Berhasil sesuai kebutuhan pengguna.",
    align=PP_ALIGN.LEFT, color=SLATE, size_pt=16.5, bold=False, font_name="DM Sans", line_spacing=1.26
)

# =========================================================
# 20. Lampiran 1 — Dokumentasi Gerai & Pengambilan Data (slot 18 — SEBELUM Penutup!)
# =========================================================
set_slide_title(prs.slides[18], "Lampiran: Dokumentasi Gerai ZenCare Medical")

# =========================================================
# 21. Lampiran 2 — Dokumentasi Produk, Rak & Barang Datang (slot 19 — SEBELUM Penutup!)
# =========================================================
set_slide_title(prs.slides[19], "Lampiran: Dokumentasi Produk & Pengambilan Data")

# =========================================================
# 22. Penutup / Terima Kasih (slot 17)
# =========================================================
s18 = prs.slides[17]
add_runs_paragraph(s18.shapes.add_textbox(Inches(4.73), Inches(5.45), Inches(10.55), Inches(0.50)).text_frame,
                   [("Sesi Tanya Jawab & Diskusi", {"font": "DM Sans Bold", "size": 21, "bold": True, "color": MUTED})], first=True, align=PP_ALIGN.CENTER)

# =========================================================
# REORDER sldIdLst (22 Slides) & Drop Unused Slots [2, 11, 12, 13, 14]
# =========================================================
sldIdLst = prs.slides._sldIdLst
sldIds = list(sldIdLst)
keep_order = [
    0, 22, 1, 9, 3, 4,
    5, 6,
    7, 8, 23, 24,
    10,
    15, 25, 26,
    21, 20, 16,
    18, 19,
    17
]
drop_idxs = [2, 11, 12, 13, 14]

for el in sldIds:
    sldIdLst.remove(el)
for di in drop_idxs:
    r_id = sldIds[di].rId
    prs.part.drop_rel(r_id)
for idx in keep_order:
    sldIdLst.append(sldIds[idx])

assert len(prs.slides) == 22, f"Expected 22 slides, got {len(prs.slides)}"

try:
    prs.save(OUT_PPTX)
    saved_path = OUT_PPTX
    if ALT_OUT_PPTX.exists():
        ALT_OUT_PPTX.unlink()
except PermissionError:
    prs.save(ALT_OUT_PPTX)
    saved_path = ALT_OUT_PPTX
    print("NOTICE: Seminar Hasil TA Final.pptx locked; saved to:", ALT_OUT_PPTX.name)

print("SAVED:", saved_path, "Slides:", len(prs.slides))
for i, sl in enumerate(prs.slides, 1):
    t = next((sh.text.strip().splitlines()[0] for sh in sl.shapes if sh.has_text_frame and len(sh.text.strip()) > 2 and "SEMINAR HASIL" not in sh.text), "?")
    print(f"  {i:2d}: {t[:75]}")
