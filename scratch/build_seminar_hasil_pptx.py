import pathlib, shutil
from PIL import Image
import pptx
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

SRC_PPTX = pathlib.Path(r"c:\xampp\htdocs\inventory_zencare\lain\PPT\Seminar Proposal TA.pptx")
OUT_PPTX = pathlib.Path(r"c:\xampp\htdocs\inventory_zencare\lain\PPT\Seminar Hasil TA Final.pptx")

ASSETS = pathlib.Path("lain/PPT/Diagram_Seminar_Hasil")
UI_DIR = pathlib.Path("lain/Katalog_UI_UX_ZenCare_Medical")

NAVY = RGBColor(0x00, 0x50, 0x88)
DARK_NAVY = RGBColor(0x0F, 0x2D, 0x5A)
TEAL = RGBColor(0x11, 0xCA, 0xA0)
SLATE = RGBColor(0x33, 0x41, 0x55)
MUTED = RGBColor(0x64, 0x74, 0x8B)
CRIMSON = RGBColor(0x9F, 0x12, 0x39)
LIGHT_BG = RGBColor(0xF1, 0xF5, 0xF9)
CARD_BORDER = RGBColor(0xCF, 0xD7, 0xE7)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
EMERALD_BG = RGBColor(0xEC, 0xFD, 0xF5)
EMERALD_TXT = RGBColor(0x06, 0x5F, 0x46)

shutil.copy2(SRC_PPTX, OUT_PPTX)
prs = pptx.Presentation(OUT_PPTX)


def set_text_keep_style(shape, new_text, align=None, color=None, size_pt=None, bold=None, font_name=None):
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


def add_card(slide, x, y, w, h, fill=WHITE, border=CARD_BORDER, border_pt=1.25, radius=0.04):
    sh = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(x), Inches(y), Inches(w), Inches(h))
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


def update_badge(slide, label="SEMINAR HASIL TA"):
    for sh in slide.shapes:
        if sh.has_text_frame and "SEMINAR PROPOSAL TA" in sh.text:
            set_text_keep_style(sh, label, align=PP_ALIGN.RIGHT, color=NAVY, size_pt=20.2, bold=True, font_name="DM Sans Bold")


def set_slide_title(slide, title_text):
    for sh in slide.shapes:
        if sh.has_text_frame and abs(sh.top - Inches(2.40)) < Inches(0.25) and abs(sh.left - Inches(1.25)) < Inches(0.25):
            set_text_keep_style(sh, title_text, color=NAVY, size_pt=34, bold=True, font_name="Merriweather Bold")
            return


for sl in prs.slides:
    update_badge(sl)

# =========================================================
# CORE SLIDES (1-11)
# =========================================================

# SLOT 0 -> Slide 1: Cover
s1 = prs.slides[0]
set_text_keep_style(s1.shapes[14], "Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.", align=PP_ALIGN.CENTER, color=NAVY, size_pt=20.2, bold=True, font_name="DM Sans Bold")

# SLOT 3 -> Slide 2: Daftar Isi (01-06 ringkas)
s_toc = prs.slides[3]
set_slide_title(s_toc, "Daftar Isi")
remove_shapes(s_toc, [6])

toc_items = [
    ("01", "Latar Belakang Masalah", "Kendala stok terpisah, Multi-UOM, & ketiadaan FEFO/SN/label"),
    ("02", "Tujuan, Manfaat, & Batasan", "Target capaian sistem, manfaat mitra, & ruang lingkup penelitian"),
    ("03", "Metodologi, Peran, & Logika Inti", "Realisasi 4 fase RAD, Use Case 4 aktor, FEFO & anti-race condition"),
    ("04", "Arsitektur Basis Data (ERD)", "Logical Database 20 tabel relasional terintegrasi (Fokus Utama)"),
    ("05", "Demo Sistem & Hasil Evaluasi", "Demonstrasi langsung fitur kunci & pengujian UAT (93,75%)"),
    ("06", "Kesimpulan & Saran", "Rangkuman capaian hasil & rekomendasi pengembangan mendatang"),
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

# SLOT 1 -> Slide 3: Latar Belakang Masalah (murni masalah)
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

# SLOT 2 -> Slide 4: Tujuan, Manfaat, & Batasan Sistem (1 Slide Padat)
s_tm = prs.slides[2]
set_slide_title(s_tm, "Tujuan, Manfaat, dan Batasan Sistem")
remove_shapes(s_tm, [2, 4, 6, 8, 9, 10, 12])

# Left Card: Tujuan Penelitian
add_card(s_tm, 1.25, 3.40, 8.65, 7.00, fill=WHITE, border=NAVY, border_pt=1.5)
add_card(s_tm, 1.25, 3.40, 8.65, 0.70, fill=NAVY, border=None, radius=0.02)
th_l = s_tm.shapes.add_textbox(Inches(1.55), Inches(3.52), Inches(8.05), Inches(0.45))
add_runs_paragraph(th_l.text_frame, [("TUJUAN PENELITIAN (BAB I 1.5)", {"font": "DM Sans Bold", "size": 16.5, "bold": True, "color": WHITE})], first=True)
tb_l = s_tm.shapes.add_textbox(Inches(1.55), Inches(4.32), Inches(8.05), Inches(5.85))
tb_l.text_frame.word_wrap = True
for idx, (pt_h, pt_b) in enumerate([
    ("1. Pusat Data Omnichannel Tunggal:", "Menyinkronkan ketersediaan stok kasir fisik (POS) dan portal toko daring (e-commerce) mandiri dalam satu basis data secara waktu nyata (real-time)."),
    ("2. Otomatisasi Multi-UOM:", "Mengotomatisasi konversi pemotongan persediaan lintas tingkat satuan kemasan (Box ↔ Strip/Pcs) agar saldo fisik dan batas stok pengaman selalu akurat."),
    ("3. Kepatuhan FEFO & Garansi SN:", "Memfasilitasi pengendalian masa kedaluwarsa obat melalui label barcode fisik & deteksi anomali kasir, serta validasi nomor seri alat kesehatan.")
]):
    add_runs_paragraph(tb_l.text_frame, [(pt_h + " ", {"font": "DM Sans Bold", "size": 15.5, "bold": True, "color": NAVY}), (pt_b, {"size": 15, "color": SLATE})], first=(idx == 0), space_after=16, line_spacing=1.22)

# Right Top Card: Manfaat Penelitian
add_card(s_tm, 10.20, 3.40, 8.55, 3.65, fill=LIGHT_BG, border=CARD_BORDER)
th_r = s_tm.shapes.add_textbox(Inches(10.50), Inches(3.55), Inches(7.95), Inches(0.45))
add_runs_paragraph(th_r.text_frame, [("MANFAAT PENELITIAN (BAB I 1.6):", {"font": "DM Sans Bold", "size": 16.5, "bold": True, "color": NAVY})], first=True)
tb_r = s_tm.shapes.add_textbox(Inches(10.50), Inches(4.05), Inches(7.95), Inches(2.85))
tb_r.text_frame.word_wrap = True
for idx, (pt_h, pt_b) in enumerate([
    ("• Bagi ZenCare Medical:", "Meniadakan selisih stok & overselling, menekan kerugian kedaluwarsa, mencegah deviasi kasir, dan mempercepat operasional."),
    ("• Bagi Penulis & Perguruan Tinggi:", "Penerapan metode RAD pada logistik medis nyata serta rujukan aplikatif sistem inventori omnichannel UMKM.")
]):
    add_runs_paragraph(tb_r.text_frame, [(pt_h + " ", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY}), (pt_b, {"size": 14, "color": SLATE})], first=(idx == 0), space_after=10, line_spacing=1.20)

# Right Bottom Card: Batasan Kunci
add_card(s_tm, 10.20, 7.25, 8.55, 3.15, fill=WHITE, border=CARD_BORDER)
bh_r = s_tm.shapes.add_textbox(Inches(10.50), Inches(7.40), Inches(7.95), Inches(0.42))
add_runs_paragraph(bh_r.text_frame, [("BATASAN RUANG LINGKUP (BAB I 1.3):", {"font": "DM Sans Bold", "size": 15.5, "bold": True, "color": DARK_NAVY})], first=True)
bb_r = s_tm.shapes.add_textbox(Inches(10.50), Inches(7.85), Inches(7.95), Inches(2.40))
bb_r.text_frame.word_wrap = True
for idx, b_txt in enumerate([
    "• Terpusat pada gerai ZenCare Medical Muharto; tidak mencakup pembukuan finansial (laba rugi/neraca/piutang) maupun poin pelanggan.",
    "• Kendali FEFO (Batch) & Serial Number disertai cetak label fisik & Stok Opname; tanpa repacking, pre-order, atau refund otomatis.",
    "• Pembayaran Midtrans & estimasi ongkir RajaOngkir berjalan pada mode sandbox."
]):
    add_runs_paragraph(bb_r.text_frame, [(b_txt, {"size": 13.5, "color": SLATE})], first=(idx == 0), space_after=6, line_spacing=1.16)

# SLOT 4 -> Slide 5: Metodologi RAD (Realisasi Progres) & Teknologi Sistem
s_rad = prs.slides[4]
set_slide_title(s_rad, "Metodologi Penelitian (RAD) & Teknologi Sistem")
remove_shapes(s_rad, [6])

rad_cards = [
    ("01. Requirements Planning", "Observasi gerai ZenCare Medical Muharto, wawancara operasional, & identifikasi kendala overselling, konversi Multi-UOM, FEFO, dan garansi SN."),
    ("02. User Design", "Pemodelan Workflow, Sitemap 4 peran, Use Case Diagram (4 aktor), Activity Diagram, skema Logical Database 20 tabel, & perancangan UI."),
    ("03. Construction", "Pembangunan sistem web menggunakan PHP & MySQL InnoDB (POS, E-Commerce, Logistik, Stok Opname) dengan row locking SELECT ... FOR UPDATE."),
    ("04. Implementation & UAT", "Pengujian alur stok lintas saluran, barcode scanner, cetak termal/A4, serta User Acceptance Testing 4 peran pengguna (Skor: 93,75% Sesuai).")
]
for idx, (rh_t, rd_t) in enumerate(rad_cards):
    col = idx % 2
    row = idx // 2
    cx = 1.25 + col * 8.95
    cy = 3.45 + row * 2.55
    add_card(s_rad, cx, cy, 8.55, 2.30, fill=LIGHT_BG, border=CARD_BORDER)
    add_card(s_rad, cx, cy, 0.16, 2.30, fill=NAVY, border=None, radius=0.01)
    rtb = s_rad.shapes.add_textbox(Inches(cx + 0.38), Inches(cy + 0.22), Inches(7.90), Inches(1.88))
    rtb.text_frame.word_wrap = True
    add_runs_paragraph(rtb.text_frame, [(rh_t, {"font": "DM Sans Bold", "size": 17.5, "bold": True, "color": NAVY})], first=True, space_after=6)
    add_runs_paragraph(rtb.text_frame, [(rd_t, {"size": 14.5, "color": SLATE})], space_after=0, line_spacing=1.20)

# Bottom Tech Stack Strip
add_card(s_rad, 1.25, 8.65, 17.50, 1.70, fill=WHITE, border=NAVY, border_pt=1.5)
tt_tb = s_rad.shapes.add_textbox(Inches(1.55), Inches(8.80), Inches(16.90), Inches(1.40))
tt_tb.text_frame.word_wrap = True
add_runs_paragraph(tt_tb.text_frame, [("Teknologi & Infrastruktur Pembangun Sistem:", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True, space_after=6)
add_runs_paragraph(tt_tb.text_frame, [
    ("Backend & Database: ", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": DARK_NAVY}),
    ("PHP 8 & MySQL InnoDB (Transaksional SELECT ... FOR UPDATE)   |   ", {"size": 14.5, "color": SLATE}),
    ("Integrasi & Pemetaan: ", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": DARK_NAVY}),
    ("Midtrans Snap, RajaOngkir, & Leaflet Maps   |   ", {"size": 14.5, "color": SLATE}),
    ("Perangkat Keras & Cetak: ", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": DARK_NAVY}),
    ("Barcode Scanner & CSS Thermal Print (50x30mm, 58/80mm, 100x150mm, A4)", {"size": 14.5, "color": SLATE})
], space_after=0)

# SLOT 10 -> Slide 6: Use Case Diagram (4 Aktor) & Logika Inti
s_uc = prs.slides[10]
set_slide_title(s_uc, "Peran Pengguna (Use Case 4 Aktor) & Logika Inti")
remove_shapes(s_uc, [4, 7])

add_card(s_uc, 1.15, 3.30, 8.95, 7.40, fill=WHITE, border=CARD_BORDER)
add_picture_fit(s_uc, ASSETS / "use_case.png", 1.25, 3.38, 8.75, 7.24)

add_card(s_uc, 10.35, 3.30, 8.50, 4.35, fill=LIGHT_BG, border=CARD_BORDER)
uc_tb = s_uc.shapes.add_textbox(Inches(10.65), Inches(3.48), Inches(7.90), Inches(4.00))
uc_tb.text_frame.word_wrap = True
for idx, (rh, rb) in enumerate([
    ("• Pelanggan (E-Commerce):", "Katalog stok aktual kemasan utuh, checkout (kuncian 2 jam + Midtrans), riwayat & pengajuan batal."),
    ("• Kasir (POS & Opname):", "Penjualan luring scanner barcode, Dual-UOM (Bongkar Box), FEFO, struk termal, & Blind Count saat Opname."),
    ("• Admin (Logistik & Pesanan):", "Penerimaan (Batch/SN + stiker), kelola pesanan & resi/batal, konversi UOM, mutasi, & verifikasi selisih Opname."),
    ("• Superadmin (Pemilik):", "Master Data & harga, akun RBAC, kendali sesi Freeze Lock Stok Opname, dasbor, & 6 laporan A4.")
]):
    add_runs_paragraph(uc_tb.text_frame, [(rh + " ", {"font": "DM Sans Bold", "size": 14.5, "bold": True, "color": NAVY}), (rb, {"size": 13.5, "color": SLATE})], first=(idx == 0), space_after=8, line_spacing=1.18)

add_card(s_uc, 10.35, 7.82, 8.50, 2.88, fill=WHITE, border=NAVY, border_pt=1.5)
ltb = s_uc.shapes.add_textbox(Inches(10.65), Inches(7.96), Inches(7.90), Inches(2.60))
ltb.text_frame.word_wrap = True
add_runs_paragraph(ltb.text_frame, [("3 Logika Inti yang Diterapkan:", {"font": "DM Sans Bold", "size": 15, "bold": True, "color": NAVY})], first=True, space_after=4)
for lh, lb in [
    ("1. FEFO & Anomali:", "ORDER BY tgl_exp ASC FOR UPDATE; blokir kedaluwarsa & rekam deviasi kasir ke log_anomali_fefo."),
    ("2. Multi-UOM Atomik:", "Daring fokus Box utuh (NOT LIKE '%.%'); kasir POS melayani eceran maupun grosir dari 1 saldo stok."),
    ("3. Anti-Race Condition:", "Keranjang non-blocking + SELECT ... FOR UPDATE saat Bayar Sekarang menolak transaksi saat stok habis di POS.")
]:
    add_runs_paragraph(ltb.text_frame, [(lh + " ", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": DARK_NAVY}), (lb, {"size": 13, "color": SLATE})], space_after=4, line_spacing=1.15)

# SLOT 15 -> Slide 7: Logical Database (ERD 20 Tabel — Fokus Utama!)
s16 = prs.slides[15]
set_slide_title(s16, "Arsitektur Basis Data: Logical Database (20 Tabel)")
remove_shapes(s16, [4, 5, 6, 7])

add_card(s16, 1.15, 3.30, 6.25, 7.42, fill=LIGHT_BG, border=CARD_BORDER)
tb16 = s16.shapes.add_textbox(Inches(1.40), Inches(3.48), Inches(5.75), Inches(7.05))
tb16.text_frame.word_wrap = True
groups_db = [
    ("1. Master Data & Pengaturan:", "produk_induk, produk_variasi (rasio Multi-UOM), stok_toko, supplier, users (RBAC 4 peran), pengaturan_web."),
    ("2. Logistik Masuk & Pelacakan:", "penerimaan_stok, penerimaan_detail (Harga Beli), stok_batch (tgl_exp), unit_serial (garansi alkes), histori_konversi."),
    ("3. Omnichannel & Kepatuhan FEFO:", "penjualan & detail_penjualan (POS + E-Commerce terpadu), pesanan_batch_alokasi (kuncian 2 jam), log_anomali_fefo, kartu_stok."),
    ("4. Siklus Stok Opname Terpusat:", "sesi_opname (Freeze Lock), detail_opname_batch, detail_opname_sn, riwayat_hitung_opname.")
]
for idx, (gh, gb) in enumerate(groups_db):
    add_runs_paragraph(tb16.text_frame, [(gh, {"font": "DM Sans Bold", "size": 15, "bold": True, "color": NAVY})], first=(idx == 0), space_after=2)
    add_runs_paragraph(tb16.text_frame, [(gb, {"size": 13.5, "color": SLATE})], space_after=11, line_spacing=1.18)

add_card(s16, 7.65, 1.32, 11.45, 9.10, fill=WHITE, border=CARD_BORDER)
add_picture_fit(s16, pathlib.Path("lain/Gambar_3.18_Logical_Database_Model.png"), 7.75, 1.40, 11.25, 8.94, pad=0.05)

# SLOT 21 -> Slide 8: DEMO SISTEM (Langsung dari ERD!)
s_demo = prs.slides[21]
remove_shapes(s_demo, [1, 4, 5, 6, 7, 8])

hero_tb = s_demo.shapes.add_textbox(Inches(2.00), Inches(2.25), Inches(16.00), Inches(1.40))
add_runs_paragraph(hero_tb.text_frame, [("DEMO SISTEM", {"font": "Merriweather Bold", "size": 66, "bold": True, "color": NAVY})], first=True, align=PP_ALIGN.CENTER)

line_sh = s_demo.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(8.50), Inches(3.80), Inches(3.00), Inches(0.08))
line_sh.fill.solid()
line_sh.fill.fore_color.rgb = TEAL
line_sh.line.fill.background()

sub_tb = s_demo.shapes.add_textbox(Inches(2.50), Inches(4.08), Inches(15.00), Inches(0.55))
add_runs_paragraph(sub_tb.text_frame, [("Demonstrasi Langsung Integrasi Omnichannel, Logika FEFO, & Perangkat Barcode", {"font": "DM Sans Bold", "size": 19, "bold": True, "color": MUTED})], first=True, align=PP_ALIGN.CENTER)

demo_steps = [
    ("1. Penerimaan & Label Fisik", "Input pasokan Batch/Exp/SN + Harga Beli, cetak Stiker Barcode Batch (50x30mm / A4) untuk ditempel ke barang contoh."),
    ("2. Kasir POS, Scanner & FEFO", "Pindai barcode dengan alat scanner, uji dialog peringatan Anomali FEFO, dan cetak Struk Termal POS (80mm / 58mm)."),
    ("3. Rebutan Stok (Race Condition)", "Masukkan stok sisa ke keranjang E-Commerce -> beli habis di Kasir POS -> klik Bayar Sekarang -> sistem menolak sebelum Midtrans."),
    ("4. Pembatalan & Label Kurir", "Uji status Dibatalkan melepaskan kuncian stok kembali aktif, dan cetak Label Pengiriman Stiker Termal (100x150mm / A6)."),
    ("5. Stok Opname & Kode FEFO", "Tunjukkan Freeze Lock, Blind Count, Total Kerugian Harga Beli di Berita Acara, serta kode FEFO di pos/pos.php (161) & api/checkout.php (181).")
]
for i, (dh, db) in enumerate(demo_steps):
    dy = 4.90 + i * 1.04
    add_card(s_demo, 2.25, dy, 15.50, 0.90, fill=WHITE, border=CARD_BORDER, border_pt=1.25)
    stb = s_demo.shapes.add_textbox(Inches(2.55), Inches(dy + 0.14), Inches(14.90), Inches(0.65))
    stb.text_frame.word_wrap = True
    add_runs_paragraph(stb.text_frame, [(dh + " — ", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY}), (db, {"size": 15, "color": SLATE})], first=True)

# SLOT 20 -> Slide 9: Hasil Pengujian & Evaluasi (UAT 93,75%)
s_uat = prs.slides[20]
set_slide_title(s_uat, "Hasil Evaluasi & Pengujian Sistem (UAT)")
remove_shapes(s_uat, [6])

uat_rows = [
    ["No", "Bagian Modul Pengujian (12 November 2026)", "Skor", "Capaian", "Status"],
    ["1", "Autentikasi Staf & Kontrol Hak Akses (RBAC)", "38 / 40", "95,00%", "Sesuai"],
    ["2", "Master Produk, Multi-UOM, & Pemasok", "37 / 40", "92,50%", "Sesuai"],
    ["3", "Terminal Kasir POS & Alokasi FEFO", "38 / 40", "95,00%", "Sesuai"],
    ["4", "Toko Daring & Sinkronisasi Omnichannel", "37 / 40", "92,50%", "Sesuai"],
    ["5", "Penerimaan Pasokan Barang & Label Barcode", "38 / 40", "95,00%", "Sesuai"],
    ["6", "Mutasi Stok Manual & Kartu Stok", "37 / 40", "92,50%", "Sesuai"],
    ["7", "Siklus Pengawasan Stok Opname Terpusat", "38 / 40", "95,00%", "Sesuai"],
    ["8", "Laporan Manajerial & Dasbor Eksekutif", "37 / 40", "92,50%", "Sesuai"],
    ["", "TOTAL KELAYAKAN (4 Peran, 16 Indikator)", "300 / 320", "93,75%", "SESUAI"],
]
tbl_sh = s_uat.shapes.add_table(len(uat_rows), 5, Inches(1.15), Inches(3.38), Inches(12.25), Inches(6.95))
tbl = tbl_sh.table
for ci, w in enumerate([0.75, 6.70, 1.65, 1.60, 1.55]):
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
        tf.clear()
        p = tf.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT if (ci == 1 and not is_hdr) else PP_ALIGN.CENTER
        r = p.add_run()
        r.text = val
        r.font.name = "DM Sans Bold" if (is_hdr or is_tot or ci >= 3) else "DM Sans"
        r.font.size = Pt(14 if is_hdr or is_tot else 13.5)
        r.font.bold = (is_hdr or is_tot or ci in (0, 3, 4))
        r.font.color.rgb = WHITE if is_hdr else (EMERALD_TXT if (is_tot or ci in (3, 4)) else SLATE)

add_card(s_uat, 13.65, 3.38, 5.20, 2.25, fill=EMERALD_BG, border=EMERALD_TXT, border_pt=1.5)
sc_tb = s_uat.shapes.add_textbox(Inches(13.85), Inches(3.55), Inches(4.80), Inches(1.90))
sc_tb.text_frame.word_wrap = True
add_runs_paragraph(sc_tb.text_frame, [("SKOR KELAYAKAN UAT", {"font": "DM Sans Bold", "size": 14, "bold": True, "color": EMERALD_TXT})], first=True, align=PP_ALIGN.CENTER, space_after=4)
add_runs_paragraph(sc_tb.text_frame, [("93,75%", {"font": "Merriweather Bold", "size": 44, "bold": True, "color": EMERALD_TXT})], align=PP_ALIGN.CENTER, space_after=4)
add_runs_paragraph(sc_tb.text_frame, [("Status: Sesuai Kebutuhan Pengguna\n(300 / 320 • 4 Peran Penguji)", {"font": "DM Sans Bold", "size": 13.5, "bold": True, "color": SLATE})], align=PP_ALIGN.CENTER)

add_card(s_uat, 13.65, 5.85, 5.20, 4.48, fill=LIGHT_BG, border=CARD_BORDER)
fn_tb = s_uat.shapes.add_textbox(Inches(13.90), Inches(6.05), Inches(4.70), Inches(4.15))
fn_tb.text_frame.word_wrap = True
add_runs_paragraph(fn_tb.text_frame, [("Ringkasan Evaluasi & Temuan:", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True, space_after=8)
for pt_txt in [
    "• Cakupan Aktor: Pemilik Toko, Admin Toko, Kasir Toko, & Pelanggan Daring.",
    "• Pemindai barcode & FEFO otomatis memangkas risiko barang kedaluwarsa di kasir.",
    "• Freeze Lock & Blind Count menertibkan pemeriksaan fisik stok tanpa manipulasi angka.",
    "• Stok daring sinkron langsung dengan POS dan aman menolak checkout saat stok habis."
]:
    add_runs_paragraph(fn_tb.text_frame, [(pt_txt, {"size": 13.5, "color": SLATE})], space_after=8, line_spacing=1.18)

# SLOT 16 -> Slide 10: Kesimpulan dan Saran (BAB V)
s17 = prs.slides[16]
set_slide_title(s17, "Kesimpulan dan Saran")
remove_shapes(s17, [3, 4, 5])

add_card(s17, 1.15, 3.45, 9.05, 6.95, fill=WHITE, border=NAVY, border_pt=1.5)
add_card(s17, 1.15, 3.45, 9.05, 0.72, fill=NAVY, border=None, radius=0.02)
kh = s17.shapes.add_textbox(Inches(1.45), Inches(3.56), Inches(8.45), Inches(0.50))
add_runs_paragraph(kh.text_frame, [("KESIMPULAN HASIL PENELITIAN (BAB V)", {"font": "DM Sans Bold", "size": 17, "bold": True, "color": WHITE})], first=True)
kb = s17.shapes.add_textbox(Inches(1.45), Inches(4.35), Inches(8.45), Inches(5.80))
kb.text_frame.word_wrap = True
for i, (t1, t2) in enumerate([
    ("1. Sinkronisasi Omnichannel Real-Time:", "Sistem berhasil dibangun sebagai pusat kendali tunggal yang menyinkronkan POS fisik dan e-commerce mandiri, meniadakan selisih stok serta mencegah overselling (termasuk saat race condition)."),
    ("2. Otomatisasi Multi-UOM, FEFO & SN:", "Konversi kemasan grosir ke eceran berjalan atomik, sedangkan kepatuhan FEFO obat dan penelusuran Serial Number alat kesehatan ditegakkan lewat pemindai barcode, stiker fisik, serta deteksi anomali."),
    ("3. Validasi Pengguna & Pengawasan Stok:", "Siklus Stok Opname berbasis Freeze Lock, Blind Count, dan estimasi kerugian Harga Beli teruji tertib, dengan skor kelayakan UAT mencapai 93,75% (Sesuai).")
]):
    add_runs_paragraph(kb.text_frame, [(t1 + " ", {"font": "DM Sans Bold", "size": 15, "bold": True, "color": NAVY}), (t2, {"size": 14.5, "color": SLATE})], first=(i == 0), space_after=14, line_spacing=1.22)

add_card(s17, 10.50, 3.45, 8.35, 6.95, fill=LIGHT_BG, border=CARD_BORDER, border_pt=1.5)
add_card(s17, 10.50, 3.45, 8.35, 0.72, fill=DARK_NAVY, border=None, radius=0.02)
sh_tb = s17.shapes.add_textbox(Inches(10.80), Inches(3.56), Inches(7.75), Inches(0.50))
add_runs_paragraph(sh_tb.text_frame, [("SARAN PENGEMBANGAN MENDATANG", {"font": "DM Sans Bold", "size": 17, "bold": True, "color": WHITE})], first=True)
sb = s17.shapes.add_textbox(Inches(10.80), Inches(4.35), Inches(7.75), Inches(5.80))
sb.text_frame.word_wrap = True
for i, (t1, t2) in enumerate([
    ("1. Transisi ke Lingkungan Live Production:", "Meningkatkan integrasi gerbang pembayaran Midtrans dan API kurir RajaOngkir dari mode sandbox ke operasional penuh beserta pelacakan resi otomatis."),
    ("2. Notifikasi Pesan Instan Otomatis:", "Menambahkan peringatan proaktif ke WhatsApp/pesan instan staf ketika stok menyentuh batas minimum (reorder point) atau obat mendekati masa kedaluwarsa."),
    ("3. Dukungan Aplikasi Mobile Opname:", "Mengembangkan antarmuka bergerak nirkabel untuk mempercepat mobilitas staf dalam memindai rak gudang saat pencacahan fisik stok opname.")
]):
    add_runs_paragraph(sb.text_frame, [(t1 + " ", {"font": "DM Sans Bold", "size": 15, "bold": True, "color": NAVY}), (t2, {"size": 14.5, "color": SLATE})], first=(i == 0), space_after=16, line_spacing=1.22)

# SLOT 17 -> Slide 11: Terima Kasih
s18 = prs.slides[17]
sub_tk = s18.shapes.add_textbox(Inches(4.73), Inches(5.45), Inches(10.55), Inches(0.50))
add_runs_paragraph(sub_tk.text_frame, [("Sesi Tanya Jawab & Diskusi", {"font": "DM Sans Bold", "size": 21, "bold": True, "color": MUTED})], first=True, align=PP_ALIGN.CENTER)

# =========================================================
# LAMPIRAN SLIDES (12-22 — Setelah Slide Terima Kasih, Cadangan Tanya Jawab)
# =========================================================

# SLOT 5 & 6 -> Lampiran 1 & 2: Workflow Keluar & Masuk
set_slide_title(prs.slides[5], "Lampiran 1: Workflow Barang Keluar (Berjalan vs Usulan)")
set_slide_title(prs.slides[6], "Lampiran 2: Workflow Barang Masuk (Berjalan vs Usulan)")

# SLOT 7 & 8 -> Lampiran 3 & 4: Sitemap 4 Peran
s8 = prs.slides[7]
set_slide_title(s8, "Lampiran 3: Sitemap (Pelanggan & Kasir POS)")
remove_shapes(s8, [4, 5, 6, 8])
add_card(s8, 0.95, 3.35, 8.85, 7.25, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s8.shapes.add_textbox(Inches(1.15), Inches(3.45), Inches(8.45), Inches(0.42)).text_frame, [("Sitemap Pelanggan (Gambar 3.5)", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s8, ASSETS / "sitemap_3_5.png", 1.15, 3.92, 8.45, 5.55)
cap_p = s8.shapes.add_textbox(Inches(1.15), Inches(9.52), Inches(8.45), Inches(0.95))
cap_p.text_frame.word_wrap = True
add_runs_paragraph(cap_p.text_frame, [("Katalog kemasan utuh, visibilitas stok waktu nyata, keranjang belanja, checkout Midtrans, & pembatalan pesanan.", {"size": 13.5, "color": SLATE})], first=True)

add_card(s8, 10.20, 3.35, 8.85, 7.25, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s8.shapes.add_textbox(Inches(10.40), Inches(3.45), Inches(8.45), Inches(0.42)).text_frame, [("Sitemap Kasir POS & Opname (Gambar 3.6)", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s8, ASSETS / "sitemap_3_6.png", 10.40, 3.92, 8.45, 5.55)
cap_k = s8.shapes.add_textbox(Inches(10.40), Inches(9.52), Inches(8.45), Inches(0.95))
cap_k.text_frame.word_wrap = True
add_runs_paragraph(cap_k.text_frame, [("Terminal Kasir POS (scan barcode, Dual-UOM, FEFO/SN, anomali) & Input Fisik Blind Count saat Sesi Opname.", {"size": 13.5, "color": SLATE})], first=True)

s9 = prs.slides[8]
set_slide_title(s9, "Lampiran 4: Sitemap (Admin Toko & Superadmin)")
remove_shapes(s9, [4, 5, 6, 8])
add_card(s9, 0.95, 3.35, 8.85, 7.25, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s9.shapes.add_textbox(Inches(1.15), Inches(3.45), Inches(8.45), Inches(0.42)).text_frame, [("Sitemap Admin Toko (Gambar 3.7)", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s9, ASSETS / "sitemap_3_7.png", 1.15, 3.92, 8.45, 5.55)
cap_a = s9.shapes.add_textbox(Inches(1.15), Inches(9.52), Inches(8.45), Inches(0.95))
cap_a.text_frame.word_wrap = True
add_runs_paragraph(cap_a.text_frame, [("Penerimaan barang (Batch/SN + stiker), pesanan omnichannel + resi & batal, konversi UOM, kartu stok, & verifikasi Opname.", {"size": 13.5, "color": SLATE})], first=True)

add_card(s9, 10.20, 3.35, 8.85, 7.25, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s9.shapes.add_textbox(Inches(10.40), Inches(3.45), Inches(8.45), Inches(0.42)).text_frame, [("Sitemap Superadmin (Gambar 3.8)", {"font": "DM Sans Bold", "size": 16, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s9, ASSETS / "sitemap_3_8.png", 10.40, 3.92, 8.45, 5.55)
cap_s = s9.shapes.add_textbox(Inches(10.40), Inches(9.52), Inches(8.45), Inches(0.95))
cap_s.text_frame.word_wrap = True
add_runs_paragraph(cap_s.text_frame, [("Master Harga & Multi-UOM, akun RBAC, konfigurasi API & web, Freeze Lock Opname, & 6 laporan resmi A4.", {"size": 13.5, "color": SLATE})], first=True)

# SLOT 11, 12, 13, 14 -> Lampiran 5-8: Activity Diagrams
def rebuild_activity_slide(slide, title, img_path, bullets, tables_list, remove_idxs):
    set_slide_title(slide, title)
    remove_shapes(slide, remove_idxs)
    add_card(slide, 1.15, 3.30, 8.85, 7.42, fill=WHITE, border=CARD_BORDER)
    add_picture_fit(slide, img_path, 1.25, 3.38, 8.65, 7.26)
    add_card(slide, 10.25, 3.30, 8.55, 4.75, fill=LIGHT_BG, border=CARD_BORDER)
    tb = slide.shapes.add_textbox(Inches(10.55), Inches(3.52), Inches(7.95), Inches(4.35))
    tb.text_frame.word_wrap = True
    for i, (b_head, b_body) in enumerate(bullets):
        add_runs_paragraph(tb.text_frame, [(b_head + " ", {"font": "DM Sans Bold", "size": 15, "bold": True, "color": NAVY}), (b_body, {"size": 14.5, "color": SLATE})], first=(i == 0), space_after=10)
    add_card(slide, 10.25, 8.25, 8.55, 2.47, fill=WHITE, border=NAVY, border_pt=1.5)
    add_runs_paragraph(slide.shapes.add_textbox(Inches(10.55), Inches(8.42), Inches(7.95), Inches(0.40)).text_frame, [("Tabel Basis Data Aktual Terkait:", {"font": "DM Sans Bold", "size": 15.5, "bold": True, "color": NAVY})], first=True)
    t_body = slide.shapes.add_textbox(Inches(10.55), Inches(8.88), Inches(7.95), Inches(1.65))
    t_body.text_frame.word_wrap = True
    add_runs_paragraph(t_body.text_frame, [(" • ".join(tables_list), {"font": "DM Sans Bold", "size": 15, "bold": True, "italic": True, "color": DARK_NAVY})], first=True, line_spacing=1.35)


rebuild_activity_slide(
    prs.slides[11], "Lampiran 5: Activity Penerimaan Barang & Validasi Logistik", ASSETS / "act_penerimaan.png",
    [("Gatekeeper Logistik:", "Admin menginput kuantitas terima/rusak, Harga Beli perolehan, nomor Batch, Tanggal Kedaluwarsa, serta Serial Number."),
     ("Penolakan Data Tidak Lengkap:", "Sistem menolak simpan jika atribut medis wajib atau nomor seri belum lengkap."),
     ("Label Fisik & Buku Besar:", "Memperbarui stok_toko, stok_batch/unit_serial, mencatat kartu_stok, serta mencetak Nota A4 dan Stiker Barcode (50x30mm / A4).")],
    ["penerimaan_stok", "penerimaan_detail", "stok_batch", "unit_serial", "stok_toko", "kartu_stok"], [3, 4, 5, 6, 9, 10, 11, 12]
)
rebuild_activity_slide(
    prs.slides[12], "Lampiran 6: Activity Barang Keluar Kasir POS & FEFO", ASSETS / "act_pos.png",
    [("Transaksi Cepat & Dual-UOM:", "Kasir memindai barcode atau memilih satuan kecil/besar; Bongkar Box otomatis memecah grosir ke eceran."),
     ("Alokasi FEFO & Blokir Kedaluwarsa:", "Query ORDER BY tgl_exp ASC FOR UPDATE di pos/pos.php (baris 161) dan blokir item kedaluwarsa."),
     ("Pengawasan Anomali Kasir:", "Pengambilan batch berbeda memicu alasan wajib ke log_anomali_fefo dan menerbitkan Struk Termal (58/80mm).")],
    ["penjualan", "detail_penjualan", "pesanan_batch_alokasi", "stok_batch", "log_anomali_fefo", "kartu_stok"], [1, 4, 5, 6, 7, 10, 11, 12]
)
rebuild_activity_slide(
    prs.slides[13], "Lampiran 7: Activity E-Commerce & Anti-Race Condition", ASSETS / "act_ecommerce.png",
    [("Keranjang Real-Time & Checkout:", "Keranjang belum memotong DB; cek_stok_keranjang.php otomatis menyesuaikan atau menghapus item jika stok habis."),
     ("Anti-Race Condition & Kuncian FEFO:", "Saat klik Bayar Sekarang (api/checkout.php baris 181), SELECT ... FOR UPDATE mengunci baris sebelum Midtrans."),
     ("Pembatalan & Resi Pengiriman:", "Status Dibatalkan melepas stok kuncian ke stok aktif; pesanan siap kirim dicetak label termal (100x150mm).")],
    ["penjualan", "detail_penjualan", "pesanan_batch_alokasi", "stok_batch", "stok_toko", "unit_serial"], [1, 5, 6, 7, 8, 11, 12, 13]
)
rebuild_activity_slide(
    prs.slides[14], "Lampiran 8: Activity Siklus Stok Opname & Kartu Stok", ASSETS / "act_opname.png",
    [("Tahap 1 — Freeze Lock:", "Superadmin memulai sesi opname; server membekukan sementara POS, checkout daring, konversi UOM, dan penerimaan barang."),
     ("Tahap 2 — Blind Count 3 Kondisi:", "Kasir menghitung fisik rak (Baik, Rusak, Kedaluwarsa) tanpa melihat angka stok buku sistem."),
     ("Tahap 3 & 4 — Harga Beli & Berita Acara:", "Admin memverifikasi selisih, menghitung Total Kerugian memakai Harga Beli perolehan, & menerbitkan Berita Acara A4 Lanskap.")],
    ["sesi_opname", "detail_opname_batch", "detail_opname_sn", "riwayat_hitung_opname", "kartu_stok"], [4, 5, 8, 9, 10, 11]
)

# SLOT 9 -> Lampiran 9: Novelty & Ringkas Implementasi
s_lamp9 = prs.slides[9]
set_slide_title(s_lamp9, "Lampiran 9: Pembaruan (Novelty) & Output Cetakan Sistem")
remove_shapes(s_lamp9, [4, 5, 6, 9])
add_card(s_lamp9, 1.15, 3.30, 8.75, 7.35, fill=WHITE, border=CARD_BORDER)
add_runs_paragraph(s_lamp9.shapes.add_textbox(Inches(1.35), Inches(3.45), Inches(8.35), Inches(0.45)).text_frame, [("Pembaruan terhadap Studi & Sistem Sebelumnya (Novelty):", {"font": "DM Sans Bold", "size": 15.5, "bold": True, "color": NAVY})], first=True)
add_picture_fit(s_lamp9, ASSETS / "tpl_image9.png", 1.30, 4.00, 8.45, 4.65)
add_runs_paragraph(s_lamp9.shapes.add_textbox(Inches(1.35), Inches(8.85), Inches(8.35), Inches(1.55)).text_frame, [("Ditambah siklus Stok Opname (Freeze Lock, Blind Count, Harga Beli), barcode scanner, & proteksi SELECT ... FOR UPDATE.", {"size": 13.5, "color": SLATE})], first=True)

for idx, (lbl, imgp) in enumerate([
    ("Struk Kasir (58/80mm)", UI_DIR / "02_Kasir_dan_Transaksi/Gambar_4_4_26_Cetak_Struk_Kasir.png"),
    ("Label Resi (100x150mm)", UI_DIR / "02_Kasir_dan_Transaksi/Gambar_4_7_28_Cetak_Label_Pengiriman.png"),
    ("Verifikasi Opname", UI_DIR / "04_Stok_Opname_Terpusat/Gambar_4_26_32_Stok_Opname_Verifikasi_Admin.png"),
    ("Berita Acara Opname A4", UI_DIR / "04_Stok_Opname_Terpusat/Gambar_4_28_33_Cetak_Berita_Acara_Opname.png"),
]):
    r = idx // 2
    c = idx % 2
    ux = 10.15 + c * 4.45
    uy = 3.30 + r * 3.75
    add_card(s_lamp9, ux, uy, 4.25, 3.55, fill=WHITE, border=CARD_BORDER)
    add_picture_fit(s_lamp9, imgp, ux + 0.08, uy + 0.08, 4.09, 2.90, pad=0.03)
    add_runs_paragraph(s_lamp9.shapes.add_textbox(Inches(ux + 0.08), Inches(uy + 3.05), Inches(4.09), Inches(0.40)).text_frame, [(lbl, {"font": "DM Sans Bold", "size": 13, "bold": True, "color": NAVY})], first=True, align=PP_ALIGN.CENTER)

# SLOT 18 & 19 -> Lampiran 10 & 11: Dokumentasi Gerai & Produk
set_slide_title(prs.slides[18], "Lampiran 10: Dokumentasi Gerai ZenCare Medical")
set_slide_title(prs.slides[19], "Lampiran 11: Dokumentasi Produk & Rak Gerai")

# =========================================================
# REORDER sldIdLst:
# Core (1-11): 0(Cover), 3(DaftarIsi), 1(LatarBelakang), 2(TujuanManfaatBatasan),
#              4(RAD), 10(UseCase&Logika), 15(ERD), 21(Demo), 20(UAT), 16(Kesimpulan), 17(TerimaKasih)
# Lampiran (12-22): 5, 6, 7, 8, 11, 12, 13, 14, 9, 18, 19
# =========================================================
sldIdLst = prs.slides._sldIdLst
sldIds = list(sldIdLst)
order = [0, 3, 1, 2, 4, 10, 15, 21, 20, 16, 17, 5, 6, 7, 8, 11, 12, 13, 14, 9, 18, 19]
for el in sldIds:
    sldIdLst.remove(el)
for idx in order:
    sldIdLst.append(sldIds[idx])

prs.save(OUT_PPTX)
print("SAVED:", OUT_PPTX, "Slides:", len(prs.slides))
for i, sl in enumerate(prs.slides, 1):
    t = next((sh.text.strip().splitlines()[0] for sh in sl.shapes if sh.has_text_frame and len(sh.text.strip()) > 2 and "SEMINAR HASIL" not in sh.text), "?")
    tag = "CORE" if i <= 11 else "LAMP"
    print(f"  [{tag}] {i:2d}: {t[:75]}")
