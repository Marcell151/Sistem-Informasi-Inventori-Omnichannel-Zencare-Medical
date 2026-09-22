"""
Script: build_sempro_ppt.py
Membangun PPT Seminar Proposal ZenCare Medical - 15 Slide
Template: Ma Chung style (Teal #4AB5BF sidebar, #2D8996 footer)
"""

from pptx import Presentation
from pptx.util import Inches, Pt, Emu, Cm
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.util import Inches, Pt
import copy, os

# ─── Constants ────────────────────────────────────────────────────────────────
TEAL      = RGBColor(0x4A, 0xB5, 0xBF)
TEAL_DARK = RGBColor(0x2D, 0x89, 0x96)
WHITE     = RGBColor(0xFF, 0xFF, 0xFF)
DARK      = RGBColor(0x1E, 0x29, 0x3B)
GRAY      = RGBColor(0x64, 0x74, 0x8B)
LIGHT_BG  = RGBColor(0xF1, 0xF5, 0xF9)
GREEN     = RGBColor(0x16, 0xA3, 0x4A)
RED       = RGBColor(0xDC, 0x26, 0x26)
YELLOW_BG = RGBColor(0xFF, 0xF9, 0xC4)
YELLOW_DK = RGBColor(0xCA, 0x8A, 0x04)

# Paths
BASE       = r'C:\xampp\htdocs\inventory_zencare\lain\File PPT'
IMG        = {
    'activity_logistik'  : rf'{BASE}\Activity Diagram\Kelola Penerimaan Barang & Validasi Logistik.png',
    'activity_keluar'    : rf'{BASE}\Activity Diagram\Pesanan Luring (Transaksi Kasir (POS)).png',
    'sitemap_admin'      : rf'{BASE}\Sitemap\Sitemap Sistem Informasi Inventori Omnichannel ZenCare Medical (Admin).png',
    'sitemap_superadmin' : rf'{BASE}\Sitemap\Sitemap Sistem Informasi Inventori Omnichannel ZenCare Medical (Superadmin).png',
    'sitemap_pelanggan'  : rf'{BASE}\Sitemap\Sitemap Sistem Informasi Inventori Omnichannel ZenCare Medical (Pelanggan).png',
    'erd'                : rf'{BASE}\ERD Logical Database\Entity Relationship Diagram (ERD) & Logical Database.png',
    'usecase'            : rf'{BASE}\Use Case\TA Use Case.png',
    'workflow_keluar_berjalan' : rf'{BASE}\Workflow\Workflow Barang Keluar (Berjalan).png',
    'workflow_keluar_usulan'   : rf'{BASE}\Workflow\Workflow Barang Keluar (Sistem Usulan).png',
    'workflow_masuk_berjalan'  : rf'{BASE}\Workflow\Workflow Barang Masuk (Berjalan).png',
    'workflow_masuk_usulan'    : rf'{BASE}\Workflow\Workflow Barang Masuk & Logistik (Sistem Usulan).png',
    'wf_penerimaan'      : rf'{BASE}\Wireframe\Penerimaan Barang.png',
    'wf_dashboard_sa'    : rf'{BASE}\Wireframe\Dashboard (Superadmin).png',
    'wf_dashboard_admin' : rf'{BASE}\Wireframe\Dashboard (Admin).png',
    'wf_pos'             : rf'{BASE}\Wireframe\POS.png',
    'wf_checkout'        : rf'{BASE}\Wireframe\Checkout Pembayaran.png',
    'wf_kartu_stok'      : rf'{BASE}\Wireframe\Kartu Stok.png',
}
OUTPUT = rf'{BASE}\Sempro_ZenCare_Final.pptx'

# ─── Slide Setup ──────────────────────────────────────────────────────────────
W = Inches(13.33)
H = Inches(7.50)

prs = Presentation()
prs.slide_width  = W
prs.slide_height = H

blank_layout = prs.slide_layouts[6]  # Blank


def new_slide():
    return prs.slides.add_slide(blank_layout)


def add_rect(slide, l, t, w, h, color, alpha=None):
    shape = slide.shapes.add_shape(1, l, t, w, h)
    shape.fill.solid()
    shape.fill.fore_color.rgb = color
    shape.line.fill.background()
    return shape


def add_textbox(slide, l, t, w, h, text, size=14, bold=False,
                color=DARK, align=PP_ALIGN.LEFT, wrap=True,
                italic=False, line_spacing=None):
    txBox = slide.shapes.add_textbox(l, t, w, h)
    tf    = txBox.text_frame
    tf.word_wrap = wrap
    para = tf.paragraphs[0]
    para.alignment = align
    run = para.add_run()
    run.text = text
    run.font.size  = Pt(size)
    run.font.bold  = bold
    run.font.color.rgb = color
    run.font.italic = italic
    if line_spacing:
        from pptx.util import Pt as Pt2
        from pptx.oxml.ns import qn
        import lxml.etree as etree
    return txBox


def add_img(slide, path, l, t, w, h):
    if os.path.exists(path):
        slide.shapes.add_picture(path, l, t, w, h)
    else:
        # Placeholder box
        r = add_rect(slide, l, t, w, h, LIGHT_BG)
        add_textbox(slide, l+Inches(0.1), t+h//2-Pt(10), w-Inches(0.2), Inches(0.5),
                    f'[IMG: {os.path.basename(path)}]', size=8, color=GRAY, align=PP_ALIGN.CENTER)


def template_chrome(slide, title_text, slide_num=None, show_sidebar=True):
    """Add the Ma Chung chrome: left teal sidebar, top white area, bottom teal bar"""
    # Left teal sidebar
    if show_sidebar:
        add_rect(slide, 0, 0, Inches(0.30), H, TEAL)

    # Bottom teal bar
    add_rect(slide, 0, Inches(6.88), W, Inches(0.62), TEAL_DARK)

    # Slide number bottom right
    if slide_num:
        add_textbox(slide, Inches(12.5), Inches(6.95), Inches(0.70), Inches(0.40),
                    str(slide_num), size=11, color=WHITE, align=PP_ALIGN.RIGHT)

    # Top thin teal accent line (below title area)
    add_rect(slide, Inches(0.30), Inches(1.15), W - Inches(0.30), Inches(0.04), TEAL)

    # Title textbox
    if title_text:
        tb = slide.shapes.add_textbox(Inches(0.55), Inches(0.22), W - Inches(0.70), Inches(0.90))
        tf = tb.text_frame
        tf.word_wrap = False
        p = tf.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT
        r = p.add_run()
        r.text = title_text
        r.font.size = Pt(22)
        r.font.bold = True
        r.font.color.rgb = TEAL_DARK


def content_area():
    """Returns (left, top, width, height) of the main content area"""
    return Inches(0.45), Inches(1.30), Inches(12.70), Inches(5.45)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 1: COVER
# ─────────────────────────────────────────────────────────────────────────────
s1 = new_slide()
# Left dark teal sidebar (wider for cover)
add_rect(s1, 0, 0, Inches(0.30), H, TEAL)
# Bottom bar
add_rect(s1, 0, Inches(6.40), W, Inches(1.10), TEAL_DARK)

# Thin accent line
add_rect(s1, Inches(0.50), Inches(3.42), Inches(0.80), Inches(0.06), TEAL)
# Horizontal separator
add_rect(s1, Inches(0.50), Inches(4.25), Inches(12.53), Inches(0.04), RGBColor(0xCC, 0xCC, 0xCC))

add_textbox(s1, Inches(0.5), Inches(0.30), Inches(12.5), Inches(0.50),
            'Universitas Ma Chung', size=13, color=TEAL_DARK, bold=False)
add_textbox(s1, Inches(0.5), Inches(0.65), Inches(12.5), Inches(0.45),
            'Program Studi Sistem Informasi', size=12, color=GRAY)

# Main title
tb = s1.shapes.add_textbox(Inches(0.5), Inches(1.15), Inches(12.5), Inches(2.20))
tf = tb.text_frame
tf.word_wrap = True
p = tf.paragraphs[0]
p.alignment = PP_ALIGN.LEFT
r = p.add_run()
r.text = 'RANCANG BANGUN'
r.font.size = Pt(28)
r.font.bold = True
r.font.color.rgb = DARK

from pptx.util import Pt as Pt2
from pptx.oxml.ns import qn
import lxml.etree as etree

p2 = tf.add_paragraph()
p2.alignment = PP_ALIGN.LEFT
r2 = p2.add_run()
r2.text = 'SISTEM INFORMASI INVENTORY OMNICHANNEL'
r2.font.size = Pt(28)
r2.font.bold = True
r2.font.color.rgb = DARK

p3 = tf.add_paragraph()
p3.alignment = PP_ALIGN.LEFT
r3 = p3.add_run()
r3.text = 'ALAT KESEHATAN DAN OBAT'
r3.font.size = Pt(28)
r3.font.bold = True
r3.font.color.rgb = DARK

add_textbox(s1, Inches(0.5), Inches(3.50), Inches(12.0), Inches(0.45),
            'Studi Kasus: ZenCare Medical', size=14, color=TEAL, italic=True)

add_textbox(s1, Inches(0.5), Inches(4.35), Inches(12.0), Inches(0.45),
            'Marcell Chandra Kenchana  ·  NIM: 322310015', size=13, color=DARK, bold=True)
add_textbox(s1, Inches(0.5), Inches(4.75), Inches(12.0), Inches(0.40),
            'Program Studi Sistem Informasi  ·  Fakultas Teknologi dan Desain  ·  Universitas Ma Chung', size=11, color=GRAY)

add_textbox(s1, Inches(0.5), Inches(5.25), Inches(12.0), Inches(0.35),
            'Dosen Pembimbing:', size=11, color=DARK, bold=True)
add_textbox(s1, Inches(0.5), Inches(5.55), Inches(12.0), Inches(0.35),
            'Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.', size=11, color=GRAY)
add_textbox(s1, Inches(0.5), Inches(5.90), Inches(12.0), Inches(0.35),
            'Penguji: [Ketua Penguji]  |  [Penguji I]  |  [Penguji II]', size=11, color=GRAY)

add_textbox(s1, Inches(0.5), Inches(6.50), Inches(5.0), Inches(0.50),
            'Malang, 2026', size=12, color=WHITE, bold=True)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 2: DAFTAR ISI
# ─────────────────────────────────────────────────────────────────────────────
s2 = new_slide()
template_chrome(s2, 'Daftar Isi', slide_num=2)

cl, ct, cw, ch = content_area()
# 5 boxes in 2 rows
box_w = Inches(2.35)
box_h = Inches(2.10)
gap   = Inches(0.18)
items = [
    ('01', 'Latar Belakang &\nIdentifikasi Masalah'),
    ('02', 'Perbandingan\nPenelitian Terdahulu'),
    ('03', 'Workflow &\nAntarmuka Sistem'),
    ('04', 'Arsitektur Teknis\n& Database'),
    ('05', 'Dokumentasi Lapangan\n& Kesimpulan'),
]
total_w = box_w * 5 + gap * 4
start_x = cl + (cw - total_w) // 2
for i, (num, label) in enumerate(items):
    bx = start_x + i * (box_w + gap)
    by = ct + Inches(1.3)
    add_rect(s2, bx, by, box_w, box_h, TEAL)
    add_textbox(s2, bx, by + Inches(0.25), box_w, Inches(0.60),
                num, size=28, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
    add_textbox(s2, bx + Inches(0.1), by + Inches(0.85), box_w - Inches(0.2), Inches(1.0),
                label, size=12, color=WHITE, align=PP_ALIGN.CENTER)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 3: LATAR BELAKANG & IDENTIFIKASI MASALAH
# ─────────────────────────────────────────────────────────────────────────────
s3 = new_slide()
template_chrome(s3, 'Latar Belakang & Identifikasi Masalah', slide_num=3)

cl, ct, cw, ch = content_area()
col_w = Inches(4.10)
gap_c = Inches(0.15)
cols = [
    ('⚡', 'Kasir Luring & Daring Terpisah',
     'Penggunaan aplikasi POS fisik stand-alone memicu tingginya risiko pesanan ganda (overselling) karena ketersediaan fisik tidak tersinkronisasi otomatis dengan etalase e-commerce.'),
    ('🔢', 'Konversi Multi-Satuan Manual',
     'Ketiadaan fitur konversi satuan memaksa admin menghitung pecahan kemasan grosir (Box) ke eceran (Strip) secara manual sebelum memotong sisa persediaan.'),
    ('🏥', 'Ketiadaan Kendali Logistik Medis',
     'Sistem pencatatan saat ini belum mendata tanggal kedaluwarsa berbasis Batch (FEFO) untuk obat, maupun pelacakan Serial Number (SN) jaminan garansi alat kesehatan.'),
]
for i, (icon, title, body) in enumerate(cols):
    bx = cl + i * (col_w + gap_c)
    by = ct
    # Card background
    add_rect(s3, bx, by, col_w, ch - Inches(0.1), LIGHT_BG)
    # Teal top bar
    add_rect(s3, bx, by, col_w, Inches(0.06), TEAL)
    # Icon
    add_textbox(s3, bx, by + Inches(0.15), col_w, Inches(0.60),
                icon, size=28, align=PP_ALIGN.CENTER, color=TEAL)
    # Title
    add_textbox(s3, bx + Inches(0.15), by + Inches(0.80), col_w - Inches(0.3), Inches(0.55),
                title, size=13, bold=True, color=TEAL_DARK, align=PP_ALIGN.CENTER)
    # Body
    add_textbox(s3, bx + Inches(0.15), by + Inches(1.45), col_w - Inches(0.3), Inches(3.70),
                body, size=11.5, color=DARK, align=PP_ALIGN.LEFT)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 4: BATASAN MASALAH
# ─────────────────────────────────────────────────────────────────────────────
s4 = new_slide()
template_chrome(s4, 'Batasan Masalah', slide_num=4)

cl, ct, cw, ch = content_area()
batasan = [
    ('🎯', 'Fokus Operasional',
     'Sistem murni menyinkronkan inventaris antara terminal POS dan E-Commerce mandiri, tanpa mencakup modul akuntansi keuangan atau laba rugi.'),
    ('📦', 'Logika Pengeluaran Logistik',
     'Pengendalian persediaan menerapkan algoritma First Expired First Out (FEFO) berbasis Batch untuk obat, serta pendataan Serial Number untuk Alat Kesehatan.'),
    ('🔗', 'Infrastruktur Pihak Ketiga',
     'Integrasi Midtrans API (pembayaran) dan RajaOngkir (ongkir) dijalankan secara eksklusif pada lingkungan pengujian (Sandbox Mode).'),
]
bw = Inches(3.95)
gap_b = Inches(0.20)
for i, (icon, title, body) in enumerate(batasan):
    bx = cl + i * (bw + gap_b)
    by = ct + Inches(0.5)
    add_rect(s4, bx, by, bw, Inches(4.40), LIGHT_BG)
    add_rect(s4, bx, by, Inches(0.55), Inches(4.40), TEAL)
    add_textbox(s4, bx, by + Inches(0.80), Inches(0.55), Inches(0.60),
                icon, size=20, align=PP_ALIGN.CENTER, color=WHITE)
    add_textbox(s4, bx + Inches(0.65), by + Inches(0.15), bw - Inches(0.75), Inches(0.55),
                title, size=14, bold=True, color=TEAL_DARK)
    add_textbox(s4, bx + Inches(0.65), by + Inches(0.80), bw - Inches(0.75), Inches(3.40),
                body, size=12, color=DARK)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 5: RESEARCH GAP (PENELITIAN TERDAHULU) - Table
# ─────────────────────────────────────────────────────────────────────────────
s5 = new_slide()
template_chrome(s5, 'Perbandingan Penelitian Terdahulu (Research Gap)', slide_num=5)

cl, ct, cw, ch = content_area()
# Table headers and rows
headers = ['Aspek', 'Rachman dkk.\n(2024)', 'Nuralisa dkk.\n(2024)', 'Mattegunta\n(2025)', 'Sistem Usulan\n(ZenCare Medical)']
rows_data = [
    ['Lingkup Saluran',        'Single-channel\n(Luring)', 'Single-channel\n(Luring)', 'Omnichannel\n(Ritel Umum)', '✅ Omnichannel\n(POS + E-Commerce)'],
    ['Konversi Multi-UOM',     '❌ Tidak Ada',              '❌ Tidak Ada',              '❌ Tidak Ada',              '✅ Dual-UOM Dinamis'],
    ['Pelacakan FEFO/Batch',   '❌ Tidak Ada',              '❌ Tidak Ada',              '❌ Tidak Ada',              '✅ FEFO per Batch'],
    ['Pelacakan Serial Number','❌ Tidak Ada',              '❌ Tidak Ada',              '❌ Tidak Ada',              '✅ SN Alat Kesehatan'],
    ['Segmentasi Medis',       '❌ Umum',                   '❌ Umum',                   '❌ Ritel Umum',             '✅ Obat & Alkes'],
]

col_widths = [Inches(2.35), Inches(2.0), Inches(2.0), Inches(2.0), Inches(2.40)]
row_h = Inches(0.68)
tx = cl
ty = ct

# Header row
hx = tx
for j, (hdr, cw_) in enumerate(zip(headers, col_widths)):
    add_rect(s5, hx, ty, cw_, row_h, TEAL_DARK)
    add_textbox(s5, hx + Inches(0.05), ty + Inches(0.05), cw_ - Inches(0.1), row_h - Inches(0.1),
                hdr, size=10, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
    hx += cw_

# Data rows
for ri, row in enumerate(rows_data):
    ry = ty + (ri + 1) * row_h
    rx = tx
    bg = LIGHT_BG if ri % 2 == 0 else WHITE
    for j, (cell, cw_) in enumerate(zip(row, col_widths)):
        cell_bg = bg
        if j == 4:
            cell_bg = RGBColor(0xE0, 0xF5, 0xF0)  # Light green for our system
        add_rect(s5, rx, ry, cw_, row_h, cell_bg)
        tc = DARK
        if '✅' in cell:
            tc = GREEN
        elif '❌' in cell:
            tc = RED
        add_textbox(s5, rx + Inches(0.05), ry + Inches(0.05), cw_ - Inches(0.1), row_h - Inches(0.1),
                    cell, size=10, color=tc, align=PP_ALIGN.CENTER)
        rx += cw_


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 6: NOVELTY / PEMBARUAN SISTEM
# ─────────────────────────────────────────────────────────────────────────────
s6 = new_slide()
template_chrome(s6, 'Pembaruan Sistem (Novelty)', slide_num=6)

cl, ct, cw, ch = content_area()
half = (cw - Inches(0.3)) / 2

# Left column - BEFORE (red)
add_rect(s6, cl, ct, half, Inches(0.55), RED)
add_textbox(s6, cl, ct, half, Inches(0.55),
            '✖  Kendala Sistem Berjalan', size=14, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
before_items = [
    '● Risiko overselling akibat aplikasi kasir yang terisolasi dari stok E-Commerce.',
    '● Beban kerja tinggi akibat konversi satuan (Box→Strip) dilakukan manual.',
    '● Penumpukan obat kadaluarsa di rak karena tidak ada sistem peringatan FEFO.',
    '● Nomor Seri alkes tidak terdokumentasi, menyulitkan klaim garansi.',
    '● Tidak ada pemisahan wewenang (RBAC) antara Admin Gudang & Manajemen.',
]
by_ = ct + Inches(0.65)
for item in before_items:
    add_rect(s6, cl, by_, half, Inches(0.75), RGBColor(0xFF, 0xF0, 0xF0))
    add_textbox(s6, cl + Inches(0.1), by_ + Inches(0.05), half - Inches(0.2), Inches(0.65),
                item, size=11.5, color=DARK)
    by_ += Inches(0.82)

# Right column - AFTER (green)
rx = cl + half + Inches(0.30)
add_rect(s6, rx, ct, half, Inches(0.55), GREEN)
add_textbox(s6, rx, ct, half, Inches(0.55),
            '✔  Sistem Usulan (ZenCare Medical)', size=14, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
after_items = [
    '● Sinkronisasi basis data tunggal Omnichannel mengunci stok seketika di semua saluran.',
    '● Algoritma konversi Dual-UOM dinamis (rasio_konversi) berjalan otomatis.',
    '● Sistem Gatekeeper: validasi FEFO wajib di setiap transaksi barang masuk & keluar.',
    '● Setiap unit Alat Kesehatan teregistrasi Serial Number unik di basis data.',
    '● RBAC tegas: Admin mencatat fisik, Superadmin mengontrol Master Produk & Harga.',
]
ay_ = ct + Inches(0.65)
for item in after_items:
    add_rect(s6, rx, ay_, half, Inches(0.75), RGBColor(0xF0, 0xFF, 0xF4))
    add_textbox(s6, rx + Inches(0.1), ay_ + Inches(0.05), half - Inches(0.2), Inches(0.65),
                item, size=11.5, color=DARK)
    ay_ += Inches(0.82)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 7: SITEMAP
# ─────────────────────────────────────────────────────────────────────────────
s7 = new_slide()
template_chrome(s7, 'Arsitektur Navigasi (Sitemap)', slide_num=7)

cl, ct, cw, ch = content_area()

# Place all 3 sitemaps side by side
sw = (cw - Inches(0.3)) / 3
add_textbox(s7, cl, ct, sw, Inches(0.35), 'Pelanggan', size=12, bold=True, color=TEAL_DARK, align=PP_ALIGN.CENTER)
add_img(s7, IMG['sitemap_pelanggan'], cl, ct + Inches(0.40), sw, Inches(4.20))

add_textbox(s7, cl + sw + Inches(0.15), ct, sw, Inches(0.35), 'Admin (Staf Gudang/Kasir)', size=12, bold=True, color=TEAL_DARK, align=PP_ALIGN.CENTER)
add_img(s7, IMG['sitemap_admin'], cl + sw + Inches(0.15), ct + Inches(0.40), sw, Inches(4.20))

add_textbox(s7, cl + sw * 2 + Inches(0.30), ct, sw, Inches(0.35), 'Superadmin (Manajemen)', size=12, bold=True, color=TEAL_DARK, align=PP_ALIGN.CENTER)
add_img(s7, IMG['sitemap_superadmin'], cl + sw * 2 + Inches(0.30), ct + Inches(0.40), sw, Inches(4.20))

# Caption
add_rect(s7, cl, ct + Inches(4.75), cw, Inches(0.55), RGBColor(0xF1, 0xF5, 0xF9))
add_textbox(s7, cl + Inches(0.1), ct + Inches(4.80), cw - Inches(0.2), Inches(0.45),
            'Pemetaan antarmuka memisahkan hak akses secara tegas: Pelanggan (E-Commerce), Admin (POS & Penerimaan Barang), Superadmin (Master Produk, Harga, & Pemantauan Dashboard).', 
            size=10, color=GRAY, italic=True)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 8: WORKFLOW BARANG KELUAR
# ─────────────────────────────────────────────────────────────────────────────
s8 = new_slide()
template_chrome(s8, 'Workflow Barang Keluar (Omnichannel)', slide_num=8)

cl, ct, cw, ch = content_area()
half_h = (ch - Inches(0.2)) / 2

# Top: workflow image (before vs after side by side)
w_half = (cw - Inches(0.15)) / 2
add_textbox(s8, cl, ct, w_half, Inches(0.30), 'Sistem Berjalan', size=11, bold=True, color=GRAY)
add_img(s8, IMG['workflow_keluar_berjalan'], cl, ct + Inches(0.30), w_half, Inches(2.40))
add_textbox(s8, cl + w_half + Inches(0.15), ct, w_half, Inches(0.30), 'Sistem Usulan', size=11, bold=True, color=TEAL_DARK)
add_img(s8, IMG['workflow_keluar_usulan'], cl + w_half + Inches(0.15), ct + Inches(0.30), w_half, Inches(2.40))

# Divider
add_rect(s8, cl, ct + Inches(2.85), cw, Inches(0.04), LIGHT_BG)

# Bottom: Wireframe POS + Checkout
bbot = ct + Inches(3.00)
wf_half = (cw - Inches(0.15)) / 2
add_textbox(s8, cl, bbot, wf_half, Inches(0.30), 'Wireframe: Terminal POS (Luring)', size=11, bold=True, color=GRAY)
add_img(s8, IMG['wf_pos'], cl, bbot + Inches(0.30), wf_half, Inches(2.00))
add_textbox(s8, cl + wf_half + Inches(0.15), bbot, wf_half, Inches(0.30), 'Wireframe: Checkout (Daring)', size=11, bold=True, color=GRAY)
add_img(s8, IMG['wf_checkout'], cl + wf_half + Inches(0.15), bbot + Inches(0.30), wf_half, Inches(2.00))

# Caption box
add_rect(s8, cl, ct + ch - Inches(0.55), cw, Inches(0.50), RGBColor(0xE0, 0xF5, 0xF0))
add_textbox(s8, cl + Inches(0.1), ct + ch - Inches(0.55) + Inches(0.06), cw - Inches(0.2), Inches(0.40),
            '🔄 Sistem mengonversi satuan otomatis dan mengunci stok sementara (Reserved) untuk mencegah overselling di semua saluran secara bersamaan.',
            size=10, color=DARK)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 9: WORKFLOW PENERIMAAN BARANG & LOGISTIK
# ─────────────────────────────────────────────────────────────────────────────
s9 = new_slide()
template_chrome(s9, 'Workflow Penerimaan Barang & Logistik', slide_num=9)

cl, ct, cw, ch = content_area()
w_half = (cw - Inches(0.15)) / 2

add_textbox(s9, cl, ct, w_half, Inches(0.30), 'Sistem Berjalan', size=11, bold=True, color=GRAY)
add_img(s9, IMG['workflow_masuk_berjalan'], cl, ct + Inches(0.30), w_half, Inches(2.40))
add_textbox(s9, cl + w_half + Inches(0.15), ct, w_half, Inches(0.30), 'Sistem Usulan', size=11, bold=True, color=TEAL_DARK)
add_img(s9, IMG['workflow_masuk_usulan'], cl + w_half + Inches(0.15), ct + Inches(0.30), w_half, Inches(2.40))

add_rect(s9, cl, ct + Inches(2.85), cw, Inches(0.04), LIGHT_BG)

bbot = ct + Inches(3.00)
add_textbox(s9, cl, bbot, cw / 2, Inches(0.30), 'Wireframe: Form Penerimaan Barang', size=11, bold=True, color=GRAY)
add_img(s9, IMG['wf_penerimaan'], cl, bbot + Inches(0.30), cw / 2, Inches(2.00))

add_textbox(s9, cl + cw / 2 + Inches(0.15), bbot, cw / 2 - Inches(0.15), Inches(0.30), 'Wireframe: Dashboard Superadmin', size=11, bold=True, color=GRAY)
add_img(s9, IMG['wf_dashboard_sa'], cl + cw / 2 + Inches(0.15), bbot + Inches(0.30), cw / 2 - Inches(0.15), Inches(2.00))

add_rect(s9, cl, ct + ch - Inches(0.55), cw, Inches(0.50), RGBColor(0xFF, 0xF9, 0xC4))
add_textbox(s9, cl + Inches(0.1), ct + ch - Inches(0.55) + Inches(0.06), cw - Inches(0.2), Inches(0.40),
            '🔐 Sistem sebagai Gatekeeper: Admin wajib menginput Identitas Logistik (Batch/EXP/SN) & Harga Beli. Superadmin memantau histori pengadaan & harga modal via Dashboard.',
            size=10, color=DARK)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 10: USE CASE DIAGRAM
# ─────────────────────────────────────────────────────────────────────────────
s10 = new_slide()
template_chrome(s10, 'Analisis Kebutuhan Sistem (Use Case Diagram)', slide_num=10)

cl, ct, cw, ch = content_area()
add_img(s10, IMG['usecase'], cl + Inches(1.0), ct, cw - Inches(2.0), ch - Inches(0.5))

add_textbox(s10, cl, ct + ch - Inches(0.45), cw, Inches(0.40),
            'Tiga aktor utama: Pelanggan (E-Commerce), Admin Gudang/Kasir (POS & Logistik), dan Superadmin (Master Produk, Harga, & Laporan Manajerial).',
            size=10, color=GRAY, italic=True, align=PP_ALIGN.CENTER)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 11: ACTIVITY DIAGRAM
# ─────────────────────────────────────────────────────────────────────────────
s11 = new_slide()
template_chrome(s11, 'Activity Diagram: Penerimaan Barang & Validasi Logistik', slide_num=11)

cl, ct, cw, ch = content_area()
img_w = Inches(7.50)
add_img(s11, IMG['activity_logistik'], cl + (cw - img_w) // 2, ct, img_w, ch - Inches(0.5))

add_rect(s11, cl, ct + ch - Inches(0.55), cw, Inches(0.50), RGBColor(0xE0, 0xF5, 0xF0))
add_textbox(s11, cl + Inches(0.1), ct + ch - Inches(0.55) + Inches(0.06), cw - Inches(0.2), Inches(0.40),
            '🔀 Sistem memvalidasi kelengkapan Identitas Logistik (Batch/EXP untuk Obat; Serial Number untuk Alkes). Proses ditolak dan dikembalikan ke form jika data tidak lengkap.',
            size=10, color=DARK)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 12: ERD LOGICAL DATABASE
# ─────────────────────────────────────────────────────────────────────────────
s12 = new_slide()
template_chrome(s12, 'Logical Database: Entity Relationship Diagram (ERD)', slide_num=12)

cl, ct, cw, ch = content_area()
add_img(s12, IMG['erd'], cl, ct, cw, ch - Inches(0.5))

add_textbox(s12, cl, ct + ch - Inches(0.45), cw, Inches(0.40),
            'Arsitektur Relasional 4 Klaster: Klaster Aktor (RBAC) · Klaster Konversi Multi-UOM · Klaster Logistik Medis (Stok & Batch) · Klaster Transaksi & Riwayat Mutasi (Kartu Stok).',
            size=10, color=GRAY, italic=True, align=PP_ALIGN.CENTER)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 13: STRUKTUR TABEL ESENSIAL
# ─────────────────────────────────────────────────────────────────────────────
s13 = new_slide()
template_chrome(s13, 'Struktur Tabel Esensial Database', slide_num=13)

cl, ct, cw, ch = content_area()
half = (cw - Inches(0.25)) / 2

tables_data = [
    {
        'title': 'produk_variasi — Konversi Multi-UOM',
        'color': TEAL,
        'fields': [
            ('sku_variasi', 'VARCHAR', 'Kode unik per varian produk'),
            ('satuan_kecil / satuan_besar', 'VARCHAR', 'Pcs / Box (contoh)'),
            ('rasio_konversi', 'INT', '→ pengunci perkalian matematis'),
            ('harga_jual_kecil / besar', 'DECIMAL', 'Harga jual statis per satuan'),
            ('tampil_di_online', 'TINYINT', 'Toggle visibilitas E-Commerce'),
        ]
    },
    {
        'title': 'stok_batch — Logika FEFO Obat',
        'color': TEAL_DARK,
        'fields': [
            ('id_variasi', 'FK', 'Relasi ke produk_variasi'),
            ('no_batch', 'VARCHAR', 'Nomor batch dari supplier'),
            ('tgl_exp', 'DATE', '→ ORDER BY tgl_exp ASC (FEFO)'),
            ('stok_sisa', 'INT', 'Stok fisik tersisa per batch'),
            ('(kartu_stok.alasan_mutasi)', 'ENUM', 'Jejak audit setiap mutasi'),
        ]
    },
]

for i, tbl in enumerate(tables_data):
    tx = cl + i * (half + Inches(0.25))
    add_rect(s13, tx, ct, half, Inches(0.50), tbl['color'])
    add_textbox(s13, tx + Inches(0.1), ct + Inches(0.05), half - Inches(0.2), Inches(0.42),
                tbl['title'], size=12, bold=True, color=WHITE)
    fy = ct + Inches(0.55)
    for field, dtype, desc in tbl['fields']:
        bg = RGBColor(0xF8, 0xFA, 0xFC) if (tbl['fields'].index((field, dtype, desc)) % 2 == 0) else WHITE
        add_rect(s13, tx, fy, half, Inches(0.82), bg)
        add_textbox(s13, tx + Inches(0.1), fy + Inches(0.04), Inches(2.20), Inches(0.40),
                    field, size=10, bold=True, color=DARK)
        add_textbox(s13, tx + Inches(0.1), fy + Inches(0.44), Inches(0.70), Inches(0.30),
                    dtype, size=8.5, color=WHITE,
                    align=PP_ALIGN.CENTER)
        # dtype badge
        add_rect(s13, tx + Inches(0.1), fy + Inches(0.44), Inches(0.70), Inches(0.28), TEAL)
        add_textbox(s13, tx + Inches(0.1), fy + Inches(0.44), Inches(0.70), Inches(0.28),
                    dtype, size=8, color=WHITE, align=PP_ALIGN.CENTER)
        add_textbox(s13, tx + Inches(0.85), fy + Inches(0.44), half - Inches(0.95), Inches(0.30),
                    desc, size=9.5, color=GRAY)
        fy += Inches(0.88)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 14: KESIMPULAN
# ─────────────────────────────────────────────────────────────────────────────
s14 = new_slide()
template_chrome(s14, 'Kesimpulan', slide_num=14)

cl, ct, cw, ch = content_area()
add_rect(s14, cl, ct, cw, ch - Inches(0.15), LIGHT_BG)
add_rect(s14, cl, ct, Inches(0.08), ch - Inches(0.15), TEAL)

kesimpulan = """Melalui penelitian ini, dirancang sebuah sistem informasi inventori omnichannel berbasis web yang bertujuan menjembatani kesenjangan antara transaksi kasir fisik (POS) dan penjualan daring di ZenCare Medical.

Dengan menerapkan metode pengembangan Rapid Application Development (RAD), sistem ini diharapkan menjadi solusi atas permasalahan overselling dan inefisiensi konversi multi-satuan (Dual-UOM).

Implementasi basis data terpusat dan fitur Gatekeeper berlogika First Expired First Out (FEFO) pada sistem ini diproyeksikan menciptakan tata kelola logistik medis yang jauh lebih akurat, aman, dan transparan — disertai pemisahan wewenang (RBAC) yang tegas antara Staf Operasional (Admin) dan Pihak Manajemen (Superadmin)."""

add_textbox(s14, cl + Inches(0.25), ct + Inches(0.35), cw - Inches(0.35), ch - Inches(0.70),
            kesimpulan, size=14, color=DARK, align=PP_ALIGN.LEFT)

# Badges
badges = ['✅ Omnichannel Terpadu', '✅ FEFO & Serial Number', '✅ RBAC Tegas', '✅ Dashboard Manajerial']
bw_ = Inches(2.90)
bx_ = cl + Inches(0.25)
by_ = ct + ch - Inches(0.65)
for badge in badges:
    add_rect(s14, bx_, by_, bw_, Inches(0.45), TEAL)
    add_textbox(s14, bx_ + Inches(0.05), by_ + Inches(0.05), bw_ - Inches(0.1), Inches(0.38),
                badge, size=11, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
    bx_ += bw_ + Inches(0.12)


# ─────────────────────────────────────────────────────────────────────────────
# SLIDE 15: PENUTUP
# ─────────────────────────────────────────────────────────────────────────────
s15 = new_slide()
# Full teal background
add_rect(s15, 0, 0, W, H, TEAL_DARK)
add_rect(s15, 0, 0, Inches(0.30), H, TEAL)

add_textbox(s15, Inches(0.5), Inches(1.80), W - Inches(1.0), Inches(1.20),
            'TERIMA KASIH', size=52, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
add_rect(s15, Inches(3.5), Inches(3.20), Inches(6.35), Inches(0.06), WHITE)
add_textbox(s15, Inches(0.5), Inches(3.40), W - Inches(1.0), Inches(0.80),
            'Rancang Bangun Sistem Informasi Inventory Omnichannel Alat Kesehatan dan Obat', 
            size=16, color=WHITE, align=PP_ALIGN.CENTER, italic=True)
add_textbox(s15, Inches(0.5), Inches(4.40), W - Inches(1.0), Inches(0.60),
            'Sesi Tanya Jawab (Q&A)', size=22, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
add_textbox(s15, Inches(0.5), Inches(5.20), W - Inches(1.0), Inches(0.50),
            'Marcell Chandra Kenchana  ·  322310015  ·  Universitas Ma Chung', 
            size=13, color=RGBColor(0xB2, 0xEB, 0xF2), align=PP_ALIGN.CENTER)

# ─────────────────────────────────────────────────────────────────────────────
# SAVE
# ─────────────────────────────────────────────────────────────────────────────
prs.save(OUTPUT)
print(f'[OK] PPT berhasil dibuat: {OUTPUT}')
print(f'[OK] Total slides: {len(prs.slides)}')
