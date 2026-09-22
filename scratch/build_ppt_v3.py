"""
Build Presentasi Seminar Proposal - FRESH START
Membuat file PPTX baru dari nol, tanpa template yang rusak.
Semua gambar dari folder File PPT dimasukkan langsung.
"""

from pptx import Presentation
from pptx.util import Inches, Pt, Emu, Cm
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.oxml.ns import qn
from pptx.util import Emu
import os, copy

# ============================================================
# Image Paths
# ============================================================
BASE = r'c:\xampp\htdocs\inventory_zencare\lain\File PPT'
IMG = {
    'erd':           os.path.join(BASE, r'ERD Logical Database\Entity Relationship Diagram (ERD) & Logical Database.png'),
    'usecase':       os.path.join(BASE, r'Use Case\TA Use Case.png'),
    'sitemap_p':     os.path.join(BASE, r'Sitemap\Sitemap Sistem Informasi Inventori Omnichannel ZenCare Medical - Pelanggan.png'),
    'sitemap_a':     os.path.join(BASE, r'Sitemap\Sitemap Sistem Informasi Inventori Omnichannel ZenCare Medical - Admin.png'),
    'sitemap_sa':    os.path.join(BASE, r'Sitemap\Sitemap Sistem Informasi Inventori Omnichannel ZenCare Medical - Superadmin.png'),
    'act_login':     os.path.join(BASE, r'Activity Diagram\Autentikasi (Login).png'),
    'act_ecom':      os.path.join(BASE, r'Activity Diagram\Pesanan Daring (E-Commerce).png'),
    'act_pos':       os.path.join(BASE, r'Activity Diagram\Pesanan Luring (Transaksi Kasir (POS)).png'),
    'act_terima':    os.path.join(BASE, r'Activity Diagram\Kelola Penerimaan Barang & Validasi Logistik.png'),
    'act_mutasi':    os.path.join(BASE, r'Activity Diagram\Mutasi Stok Manual.png'),
    'act_pesanan':   os.path.join(BASE, r'Activity Diagram\Kelola Pesanan Daring & Pembatalan.png'),
    'act_laporan':   os.path.join(BASE, r'Activity Diagram\Output (Laporan Manajerial & Logistik).png'),
    'wf_keluar_b':   os.path.join(BASE, r'Workflow\Workflow Barang Keluar (Berjalan).png'),
    'wf_keluar_s':   os.path.join(BASE, r'Workflow\Workflow Barang Keluar (Sistem Usulan).png'),
    'wf_masuk_b':    os.path.join(BASE, r'Workflow\Workflow Barang Masuk (Berjalan).png'),
    'wf_masuk_s':    os.path.join(BASE, r'Workflow\Workflow Barang Masuk & Logistik (Sistem Usulan).png'),
    'wf_pos':        os.path.join(BASE, r'Wireframe\POS.png'),
    'wf_dashboard_a':os.path.join(BASE, r'Wireframe\Dashboard (Admin).png'),
    'wf_dashboard_s':os.path.join(BASE, r'Wireframe\Dashboard (Superadmin).png'),
    'wf_penerimaan': os.path.join(BASE, r'Wireframe\Penerimaan Barang.png'),
    'wf_kartu':      os.path.join(BASE, r'Wireframe\Kartu Stok.png'),
    'wf_transaksi':  os.path.join(BASE, r'Wireframe\Transaksi.png'),
    'wf_ecom':       os.path.join(BASE, r'Wireframe\E-Commerce.png'),
    'wf_checkout':   os.path.join(BASE, r'Wireframe\Checkout Pembayaran.png'),
    'wf_produk':     os.path.join(BASE, r'Wireframe\Produk.png'),
    'wf_mutasi':     os.path.join(BASE, r'Wireframe\Mutasi Stok.png'),
    'wf_lapjual':    os.path.join(BASE, r'Wireframe\Laporan Penjualan Barang.png'),
    'wf_laplogistik':os.path.join(BASE, r'Wireframe\Laporan Logistik Medis.png'),
}

DEST = os.path.join(BASE, 'Presentasi_Sempro_Marcell.pptx')

# ============================================================
# Color Palette (Modern Academic Blue)
# ============================================================
C_NAVY      = RGBColor(0x0F, 0x29, 0x5A)   # Deep navy
C_BLUE      = RGBColor(0x00, 0x50, 0x88)   # Main blue
C_TEAL      = RGBColor(0x00, 0x79, 0x9A)   # Accent teal
C_LIGHT     = RGBColor(0xE8, 0xF4, 0xFF)   # Light blue bg
C_LIGHTGRAY = RGBColor(0xF8, 0xFA, 0xFF)   # Very light bg
C_WHITE     = RGBColor(0xFF, 0xFF, 0xFF)
C_TEXT      = RGBColor(0x1E, 0x29, 0x3B)   # Dark text
C_MUTED     = RGBColor(0x64, 0x74, 0x8B)   # Muted text
C_RED       = RGBColor(0xDC, 0x26, 0x26)
C_GREEN     = RGBColor(0x15, 0x80, 0x3D)
C_GOLD      = RGBColor(0xD9, 0x7F, 0x06)
C_LIME_BG   = RGBColor(0xDC, 0xF7, 0xE7)
C_RED_BG    = RGBColor(0xFE, 0xE2, 0xE2)

# ============================================================
# Helpers
# ============================================================
W_IN, H_IN = 13.33, 7.5  # Standard 16:9

def e(v): return int(v * 914400)

def new_prs():
    prs = Presentation()
    prs.slide_width  = e(W_IN)
    prs.slide_height = e(H_IN)
    return prs

def blank(prs):
    return prs.slides.add_slide(prs.slide_layouts[6])  # Blank

def tb(slide, l, t, w, h, text, sz=18, bold=False,
       color=C_TEXT, align=PP_ALIGN.LEFT, wrap=True, italic=False):
    box = slide.shapes.add_textbox(e(l), e(t), e(w), e(h))
    tf  = box.text_frame
    tf.word_wrap = wrap
    p = tf.paragraphs[0]
    p.alignment = align
    r = p.add_run()
    r.text = text
    r.font.size   = Pt(sz)
    r.font.bold   = bold
    r.font.italic = italic
    r.font.color.rgb = color
    return box

def para(tf, text, sz=16, bold=False, color=C_TEXT,
         sba=4, sbe=4, align=PP_ALIGN.LEFT, italic=False, first=False):
    p = tf.paragraphs[0] if first else tf.add_paragraph()
    p.alignment    = align
    p.space_before = Pt(sba)
    p.space_after  = Pt(sbe)
    r = p.add_run()
    r.text = text
    r.font.size   = Pt(sz)
    r.font.bold   = bold
    r.font.italic = italic
    r.font.color.rgb = color
    return p

def rect(slide, l, t, w, h, fill=None, line=None, lw=1.0, rounded=False):
    stype = 5 if rounded else 1
    s = slide.shapes.add_shape(stype, e(l), e(t), e(w), e(h))
    if rounded:
        s.adjustments[0] = 0.04
    if fill:
        s.fill.solid()
        s.fill.fore_color.rgb = fill
    else:
        s.fill.background()
    if line:
        s.line.color.rgb = line
        s.line.width = Pt(lw)
    else:
        s.line.fill.background()
    return s

def img(slide, path, l, t, w, h):
    if os.path.exists(path):
        slide.shapes.add_picture(path, e(l), e(t), e(w), e(h))
    else:
        # Placeholder box if image missing
        s = rect(slide, l, t, w, h, fill=C_LIGHT, line=C_BLUE, lw=1)
        tb(slide, l+0.1, t + h/2 - 0.15, w-0.2, 0.30,
           '[ Gambar tidak ditemukan ]', sz=10, color=C_MUTED, align=PP_ALIGN.CENTER)

def gradient_header(slide, title, subtitle=''):
    """Full-width header bar: navy→teal gradient simulated with 2 rects."""
    rect(slide, 0, 0, W_IN * 0.6, 0.85, fill=C_NAVY)
    rect(slide, W_IN * 0.6, 0, W_IN * 0.4, 0.85, fill=C_TEAL)
    tb(slide, 0.35, 0.07, W_IN - 3.5, 0.70,
       title, sz=26, bold=True, color=C_WHITE)
    if subtitle:
        tb(slide, W_IN - 3.0, 0.16, 2.80, 0.55,
           subtitle, sz=13, color=C_WHITE, align=PP_ALIGN.RIGHT)

def section_label(slide, text, color=C_BLUE):
    """Small colored label strip at top."""
    rect(slide, 0.35, 0.95, 0.06, 0.35, fill=color)
    tb(slide, 0.48, 0.94, 12.0, 0.38, text, sz=12, bold=True, color=color)

def hline(slide, l, t, w, color=C_TEAL, h=0.04):
    rect(slide, l, t, w, h, fill=color)

def card(slide, l, t, w, h, icon, title, body,
         hdr_color=C_BLUE, body_sz=14, title_sz=15):
    """Rounded card with colored header strip, icon, title, body."""
    rect(slide, l, t, w, h, fill=C_LIGHTGRAY, line=C_BLUE, lw=1.0, rounded=True)
    rect(slide, l, t, w, 0.70, fill=hdr_color, rounded=False)
    # Fix corners on top only by overlapping bottom of header
    rect(slide, l, t + 0.60, w, 0.15, fill=hdr_color)
    tb(slide, l + 0.12, t + 0.08, 0.55, 0.55, icon, sz=24, align=PP_ALIGN.CENTER, color=C_WHITE)
    tb(slide, l + 0.70, t + 0.10, w - 0.80, 0.52,
       title, sz=title_sz, bold=True, color=C_WHITE)
    hline(slide, l + 0.15, t + 0.78, w - 0.30, color=C_TEAL, h=0.03)
    box = slide.shapes.add_textbox(e(l+0.15), e(t+0.88), e(w-0.30), e(h-1.00))
    tf = box.text_frame; tf.word_wrap = True
    p = tf.paragraphs[0]
    r = p.add_run(); r.text = body
    r.font.size = Pt(body_sz); r.font.color.rgb = C_TEXT

def step_row(slide, y, num, label, detail, num_color=C_BLUE, h=0.62):
    rect(slide, 0.35, y, 0.52, h, fill=num_color, rounded=True)
    tb(slide, 0.36, y + 0.10, 0.50, 0.42, str(num), sz=19, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
    rect(slide, 0.90, y + h/2 - 0.015, 0.05, 0.03, fill=C_MUTED)  # dot connector
    tb(slide, 1.05, y + 0.02, 3.20, 0.28, label, sz=12, bold=True, color=C_BLUE)
    tb(slide, 1.05, y + 0.30, 12.0, 0.28, detail, sz=12, color=C_TEXT)

# ============================================================
# Page builder functions
# ============================================================

def slide_01_cover(prs):
    """Cover slide."""
    s = blank(prs)
    # Full background gradient
    rect(s, 0, 0, W_IN, H_IN, fill=C_NAVY)
    rect(s, 0, 0, 0.18, H_IN, fill=C_TEAL)  # Left accent bar
    rect(s, W_IN - 0.18, 0, 0.18, H_IN, fill=C_TEAL)  # Right accent bar
    # Bottom accent
    rect(s, 0, H_IN - 1.20, W_IN, 1.20, fill=RGBColor(0x07, 0x1A, 0x40))
    rect(s, 0, H_IN - 1.22, W_IN, 0.06, fill=C_TEAL)

    # Logo placeholder
    rect(s, 0.50, 0.30, 1.50, 1.00, fill=RGBColor(0x16, 0x35, 0x70), line=C_TEAL, lw=1, rounded=True)
    tb(s, 0.52, 0.38, 1.46, 0.80, '[ LOGO\nMA CHUNG ]', sz=9, color=C_TEAL, align=PP_ALIGN.CENTER)

    # Tag line
    tb(s, 0.35, 0.32, W_IN - 0.70, 0.38,
       'SEMINAR PROPOSAL TUGAS AKHIR  •  PROGRAM STUDI SISTEM INFORMASI',
       sz=11, color=C_TEAL, align=PP_ALIGN.RIGHT)

    # Main title
    tb(s, 0.50, 1.40, W_IN - 1.00, 2.10,
       'RANCANG BANGUN SISTEM INFORMASI\nINVENTORY OMNICHANNEL ALAT KESEHATAN DAN OBAT',
       sz=30, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
    rect(s, 2.00, 3.55, W_IN - 4.00, 0.06, fill=C_TEAL)
    tb(s, 0.50, 3.65, W_IN - 1.00, 0.55,
       '(Studi Kasus: ZenCare Medical)',
       sz=20, bold=False, italic=True, color=RGBColor(0xA5, 0xD8, 0xFF), align=PP_ALIGN.CENTER)

    # Author block
    tb(s, 0.50, 4.45, W_IN - 1.00, 0.50,
       'Marcell Chandra Kenchana  |  NIM: 322310015',
       sz=18, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
    tb(s, 0.50, 5.00, W_IN - 1.00, 0.38,
       'Program Studi Sistem Informasi  •  Fakultas Teknologi dan Desain  •  Universitas Ma Chung',
       sz=13, color=RGBColor(0xCB, 0xDF, 0xF0), align=PP_ALIGN.CENTER)

    # Bottom info box
    rect(s, 0.35, 5.55, W_IN - 0.70, 1.62, fill=RGBColor(0x07, 0x1A, 0x40))
    tb(s, 0.50, 5.62, W_IN - 1.00, 0.32,
       'Dosen Pembimbing', sz=11, bold=True, color=C_TEAL, align=PP_ALIGN.CENTER)
    tb(s, 0.50, 5.95, W_IN - 1.00, 0.38,
       'Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.',
       sz=14, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
    tb(s, 0.50, 6.38, W_IN - 1.00, 0.30,
       'Ketua Penguji: [Nama]   |   Penguji 1: [Nama]   |   Penguji 2: [Nama]',
       sz=11, color=C_MUTED, align=PP_ALIGN.CENTER)
    tb(s, 0.50, 6.72, W_IN - 1.00, 0.30,
       'Malang, 30 September 2026',
       sz=11, italic=True, color=C_MUTED, align=PP_ALIGN.CENTER)


def slide_02_toc(prs):
    """Daftar Isi - 5 numbered boxes."""
    s = blank(prs)
    gradient_header(s, 'Daftar Isi', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    items = [
        ('01', '📌', 'Latar Belakang &\nIdentifikasi Masalah', 'Masalah operasional:\noverselling, multi-UOM,\nlogistik medis'),
        ('02', '📚', 'Penelitian Terdahulu\n& Research Gap', 'Perbandingan &\nkebaruan sistem\nZenCare'),
        ('03', '🔄', 'Workflow &\nUse Case Sistem', 'Alur bisnis berjalan\nvs. sistem usulan\nomnichannel'),
        ('04', '🗄️', 'Perancangan Sistem\n& Database', 'Sitemap, Activity Diagram,\nERD & Logical Database,\nWireframe'),
        ('05', '✅', 'Kesimpulan &\nLuaran', 'Simpulan penelitian\n& target luaran\nyang diharapkan'),
    ]
    box_w = 2.30
    for i, (num, icon, title, desc) in enumerate(items):
        x = 0.35 + i * (box_w + 0.18)
        # Number badge
        rect(s, x, 1.10, 0.48, 0.40, fill=C_TEAL, rounded=True)
        tb(s, x, 1.13, 0.48, 0.34, num, sz=15, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
        # Card body
        rect(s, x, 1.55, box_w, 5.65, fill=C_LIGHT, line=C_BLUE, lw=1.2, rounded=True)
        # Icon strip
        rect(s, x, 1.55, box_w, 0.75, fill=C_BLUE)
        tb(s, x + 0.05, 1.58, box_w - 0.10, 0.68, icon, sz=28, align=PP_ALIGN.CENTER, color=C_WHITE)
        # Title
        tb(s, x + 0.12, 2.38, box_w - 0.24, 1.20,
           title, sz=13, bold=True, color=C_BLUE, align=PP_ALIGN.CENTER)
        hline(s, x + 0.20, 3.60, box_w - 0.40, color=C_TEAL, h=0.03)
        # Desc
        tb(s, x + 0.12, 3.70, box_w - 0.24, 3.40,
           desc, sz=12, color=C_TEXT, align=PP_ALIGN.CENTER)


def slide_03_latbel(prs):
    """Latar Belakang - 3 card layout."""
    s = blank(prs)
    gradient_header(s, 'Latar Belakang & Identifikasi Masalah', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    cards_data = [
        ('🏪', 'Kasir Luring & E-Commerce\nTidak Terhubung',
         C_BLUE,
         'ZenCare menggunakan aplikasi POS pihak ketiga yang bersifat stand-alone. '
         'Sistem ini tidak terhubung dengan saluran penjualan daring, sehingga sisa '
         'stok fisik tidak tersinkronisasi secara otomatis.\n\n'
         '➤ Risiko overselling sangat tinggi: barang yang sudah habis di toko masih '
         'bisa dipesan oleh pelanggan daring.'),
        ('🔢', 'Konversi Multi-Satuan\nManual (Multi-UOM)',
         C_TEAL,
         'E-commerce berbasis satuan grosir (Box), kasir fisik melayani eceran (Strip/Pcs). '
         'Ketiadaan integrasi memaksa admin menghitung konversi kemasan secara manual '
         'setelah setiap transaksi daring.\n\n'
         '➤ Proses kerja ganda ini sangat rentan terhadap kesalahan manusia (human error).'),
        ('💊', 'Ketiadaan Kendali\nLogistik Medis',
         RGBColor(0x7C, 0x3A, 0xED),
         'Sistem saat ini belum mendata tanggal kedaluwarsa berbasis Batch (FEFO) untuk obat. '
         'Alat kesehatan bernilai tinggi juga belum dicatat berdasarkan Serial Number (SN) '
         'untuk keperluan validasi klaim garansi.\n\n'
         '➤ Tidak ada label fisik (stiker batch) → pengambilan barang tidak tervalidasi sistem.'),
    ]
    cw = (W_IN - 0.90) / 3
    for i, (icon, title, color, body) in enumerate(cards_data):
        x = 0.35 + i * (cw + 0.10)
        card(s, x, 1.05, cw, H_IN - 1.25, icon, title, body,
             hdr_color=color, body_sz=13.5, title_sz=13.5)


def slide_04_batasan(prs):
    """Batasan Masalah - 3 horizontal rows."""
    s = blank(prs)
    gradient_header(s, 'Batasan Masalah', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    items = [
        ('🎯', 'Fokus Operasional', C_BLUE,
         'Sistem menyinkronkan inventaris antara terminal POS dan E-Commerce mandiri. '
         'Tidak mencakup modul akuntansi keuangan (HPP/Laba-Rugi), sistem poin pelanggan, '
         'maupun otomatisasi pengadaan stok (auto-replenishment).'),
        ('📦', 'Logika Pengeluaran Logistik', C_TEAL,
         'Pengendalian persediaan menerapkan First Expired First Out (FEFO) berbasis Nomor Batch '
         'untuk obat-obatan, serta pencatatan Serial Number (SN) per unit untuk Alat Kesehatan. '
         'Sistem mewajibkan validasi kelengkapan data saat penerimaan barang.'),
        ('🔌', 'Infrastruktur & Ruang Lingkup', RGBColor(0x7C, 0x3A, 0xED),
         'E-Commerce dibangun secara mandiri (bukan integrasi marketplace eksternal). '
         'Layanan Payment Gateway (Midtrans) dan ongkos kirim (RajaOngkir) diuji pada lingkungan '
         'Sandbox. Sistem beroperasi pada satu lokasi: ZenCare Medical Muharto.'),
    ]
    for i, (icon, label, color, body) in enumerate(items):
        y = 1.10 + i * 2.05
        # Icon box
        rect(s, 0.35, y, 0.80, 1.75, fill=color, rounded=True)
        tb(s, 0.35, y + 0.50, 0.80, 0.80, icon, sz=30, color=C_WHITE, align=PP_ALIGN.CENTER)
        # Content box
        rect(s, 1.25, y, W_IN - 1.65, 1.75, fill=C_LIGHTGRAY, line=color, lw=1.5, rounded=True)
        tb(s, 1.45, y + 0.12, W_IN - 2.00, 0.45,
           label, sz=16, bold=True, color=color)
        hline(s, 1.45, y + 0.60, W_IN - 2.10, color=color, h=0.03)
        tb(s, 1.45, y + 0.70, W_IN - 2.00, 0.95,
           body, sz=13.5, color=C_TEXT)


def slide_05_gap(prs):
    """Research Gap - table."""
    s = blank(prs)
    gradient_header(s, 'Research Gap — Perbandingan Penelitian Terdahulu', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    cols = ['Aspek', 'Rachman (2024)\n& Nuralisa (2024)', 'Mattegunta (2025)', '★ Sistem Usulan\n(ZenCare Medical)']
    col_w = [1.65, 3.10, 3.10, 4.70]
    col_x = [0.35]
    for w in col_w[:-1]:
        col_x.append(col_x[-1] + w + 0.06)
    col_colors = [C_NAVY, C_BLUE, C_BLUE, C_TEAL]

    # Header row
    for ci, (hdr, cw, cx, cc) in enumerate(zip(cols, col_w, col_x, col_colors)):
        rect(s, cx, 1.05, cw, 0.82, fill=cc, rounded=False)
        tb(s, cx + 0.08, 1.08, cw - 0.16, 0.76,
           hdr, sz=12, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)

    rows = [
        ('Platform', 'Web React.js/\nPHP Laravel', 'POS + Cloud\n(ritel umum)', 'PHP + MySQL\nOmnichannel mandiri'),
        ('Sinkronisasi\nOmnichannel', '✗ Tidak ada\n(stand-alone)', '✔ Real-time\n(ritel umum)', '✔ Real-time\nBasis data tunggal'),
        ('Konversi\nMulti-UOM', '✗ Tidak ada', '✗ Tidak dibahas', '✔ Dual-UOM\nBox ↔ Strip otomatis'),
        ('Logistik Medis\n(FEFO / SN)', '✗ Tidak ada', '✗ Tidak dibahas', '✔ FEFO Batch (Obat)\n+ SN (Alat Kesehatan)'),
        ('Research\nGap', 'Single-channel,\ntanpa FEFO & UOM', 'Tanpa logistik\nmedis spesifik', '★ Solusi gap dari\nkedua penelitian'),
    ]
    rh = 1.00
    for ri, row in enumerate(rows):
        rt = 1.90 + ri * rh
        for ci, (cell, cw, cx) in enumerate(zip(row, col_w, col_x)):
            is_star = ci == 3 and ri == len(rows) - 1
            is_gap  = ri == len(rows) - 1
            if is_star:
                bg = C_LIME_BG
            elif is_gap and ci < 3:
                bg = C_RED_BG
            elif ci == 0:
                bg = C_LIGHT
            else:
                bg = C_WHITE if ri % 2 == 0 else C_LIGHTGRAY
            rect(s, cx, rt, cw, rh - 0.05,
                 fill=bg, line=RGBColor(0xCC, 0xD5, 0xE0), lw=0.5)
            clr = C_GREEN if '✔' in cell else (C_RED if '✗' in cell else C_TEXT)
            tb(s, cx + 0.08, rt + 0.08, cw - 0.16, rh - 0.18,
               cell, sz=12, bold=(ci == 0 or is_star), color=clr)


def slide_06_novelty(prs):
    """Novelty - before/after 2 column."""
    s = blank(prs)
    gradient_header(s, 'Pembaruan Sistem — Novelty (Kebaruan)', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    # LEFT: As-Is
    rect(s, 0.35, 1.05, 6.20, 0.58, fill=C_RED)
    tb(s, 0.50, 1.08, 5.90, 0.52, '✗   Kondisi Sistem Berjalan (As-Is)', sz=15, bold=True, color=C_WHITE)
    rect(s, 0.35, 1.65, 6.20, 5.55, fill=C_RED_BG, line=C_RED, lw=1.2, rounded=True)
    probs = [
        ('🔴', 'Risiko Overselling', 'Aplikasi kasir fisik (stand-alone) tidak terhubung ke e-commerce. Stok tidak tersinkronisasi otomatis.'),
        ('🔴', 'Konversi Manual', 'Admin wajib menghitung sendiri konversi Box → Strip setiap kali ada pesanan daring masuk.'),
        ('🔴', 'Tanpa Filter FEFO', 'Tidak ada pencatatan Batch & kadaluwarsa → penumpukan obat rusak dan risiko keamanan produk.'),
        ('🔴', 'Tanpa Serial Number', 'Alat kesehatan tidak tercatat per unit → validasi klaim garansi pelanggan tidak bisa dilakukan.'),
    ]
    for i, (icon, lbl, detail) in enumerate(probs):
        y = 1.78 + i * 1.35
        rect(s, 0.48, y, 6.02, 1.22, fill=C_WHITE, line=C_RED, lw=0.8, rounded=True)
        tb(s, 0.58, y + 0.08, 0.40, 0.40, icon, sz=18, align=PP_ALIGN.CENTER, color=C_RED)
        tb(s, 1.05, y + 0.05, 5.20, 0.32, lbl, sz=13, bold=True, color=C_RED)
        tb(s, 1.05, y + 0.38, 5.20, 0.80, detail, sz=12, color=C_TEXT)

    # Arrow
    tb(s, 6.62, 3.70, 0.60, 0.60, '→', sz=36, bold=True, color=C_TEAL, align=PP_ALIGN.CENTER)

    # RIGHT: To-Be
    rect(s, 7.28, 1.05, 5.70, 0.58, fill=C_GREEN)
    tb(s, 7.42, 1.08, 5.45, 0.52, '✔   Sistem Usulan (To-Be)', sz=15, bold=True, color=C_WHITE)
    rect(s, 7.28, 1.65, 5.70, 5.55, fill=C_LIME_BG, line=C_GREEN, lw=1.2, rounded=True)
    sols = [
        ('🟢', 'Basis Data Tunggal', 'Satu basis data terpusat memproses transaksi POS & E-Commerce, stok terkunci seketika (Reserved).'),
        ('🟢', 'Dual-UOM Otomatis', 'Algoritma rasio_konversi mengonversi & memotong stok lintas satuan secara otomatis.'),
        ('🟢', 'Gatekeeper FEFO', 'Sistem menolak data tanpa Nomor Batch + Tanggal Kadaluwarsa → pemotongan prioritas tgl_exp ASC.'),
        ('🟢', 'Serial Number (SN)', 'Setiap unit Alat Kesehatan terdaftar di tabel unit_serial → riwayat garansi dapat ditelusuri.'),
    ]
    for i, (icon, lbl, detail) in enumerate(sols):
        y = 1.78 + i * 1.35
        rect(s, 7.40, y, 5.48, 1.22, fill=C_WHITE, line=C_GREEN, lw=0.8, rounded=True)
        tb(s, 7.50, y + 0.08, 0.40, 0.40, icon, sz=18, align=PP_ALIGN.CENTER, color=C_GREEN)
        tb(s, 7.95, y + 0.05, 4.60, 0.32, lbl, sz=13, bold=True, color=C_GREEN)
        tb(s, 7.95, y + 0.38, 4.60, 0.80, detail, sz=12, color=C_TEXT)


def slide_07_usecase(prs):
    """Use Case Diagram."""
    s = blank(prs)
    gradient_header(s, 'Use Case Diagram — Aktor & Fungsi Sistem', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    img(s, IMG['usecase'], 0.35, 1.05, W_IN - 0.70, H_IN - 1.30)


def slide_08_workflow_before(prs):
    """Workflow berjalan (before) - 2 images side by side."""
    s = blank(prs)
    gradient_header(s, 'Workflow Bisnis Berjalan — Kondisi Saat Ini', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    tb(s, 0.35, 1.00, W_IN - 0.70, 0.38,
       'Alur proses saat ini: sistem kasir berdiri sendiri, pemotongan stok dari pesanan daring dilakukan secara manual.',
       sz=13, italic=True, color=C_MUTED)

    half = (W_IN - 0.90) / 2
    # Left: Barang Keluar (Berjalan)
    rect(s, 0.35, 1.42, half, 0.42, fill=C_BLUE)
    tb(s, 0.40, 1.44, half - 0.10, 0.38, '📤  Barang Keluar (Berjalan)', sz=13, bold=True, color=C_WHITE)
    img(s, IMG['wf_keluar_b'], 0.35, 1.87, half, H_IN - 2.15)

    # Right: Barang Masuk (Berjalan)
    rx = 0.35 + half + 0.20
    rect(s, rx, 1.42, half, 0.42, fill=C_TEAL)
    tb(s, rx + 0.05, 1.44, half - 0.10, 0.38, '📥  Barang Masuk (Berjalan)', sz=13, bold=True, color=C_WHITE)
    img(s, IMG['wf_masuk_b'], rx, 1.87, half, H_IN - 2.15)


def slide_09_workflow_after(prs):
    """Workflow usulan (after) - 2 images side by side."""
    s = blank(prs)
    gradient_header(s, 'Workflow Sistem Usulan — Omnichannel Terintegrasi', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    tb(s, 0.35, 1.00, W_IN - 0.70, 0.38,
       'Sistem usulan: satu basis data terpusat menangani POS & E-Commerce, pemotongan stok otomatis dengan algoritma FEFO.',
       sz=13, italic=True, color=C_MUTED)

    half = (W_IN - 0.90) / 2
    rect(s, 0.35, 1.42, half, 0.42, fill=C_BLUE)
    tb(s, 0.40, 1.44, half - 0.10, 0.38, '📤  Barang Keluar (Sistem Usulan)', sz=13, bold=True, color=C_WHITE)
    img(s, IMG['wf_keluar_s'], 0.35, 1.87, half, H_IN - 2.15)

    rx = 0.35 + half + 0.20
    rect(s, rx, 1.42, half, 0.42, fill=C_TEAL)
    tb(s, rx + 0.05, 1.44, half - 0.10, 0.38, '📥  Barang Masuk & Logistik (Sistem Usulan)', sz=13, bold=True, color=C_WHITE)
    img(s, IMG['wf_masuk_s'], rx, 1.87, half, H_IN - 2.15)


def slide_10_sitemap(prs):
    """Sitemap - 3 images (Pelanggan, Admin, Superadmin)."""
    s = blank(prs)
    gradient_header(s, 'Sitemap — Arsitektur Navigasi Sistem (3 Aktor)', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    third = (W_IN - 0.90) / 3
    for i, (key, label, color) in enumerate([
        ('sitemap_p',  '👤  Pelanggan (E-Commerce)', C_BLUE),
        ('sitemap_a',  '🧑‍💼  Admin (Operasional)', C_TEAL),
        ('sitemap_sa', '👑  Superadmin (Manajerial)', C_NAVY),
    ]):
        x = 0.35 + i * (third + 0.10)
        rect(s, x, 1.05, third, 0.42, fill=color)
        tb(s, x + 0.08, 1.07, third - 0.16, 0.38, label, sz=12, bold=True, color=C_WHITE)
        img(s, IMG[key], x, 1.50, third, H_IN - 1.75)


def slide_11_act_diagram(prs):
    """Activity Diagram - 2 key ones side by side."""
    s = blank(prs)
    gradient_header(s, 'Activity Diagram — Penerimaan Barang (Gatekeeper Logistik)', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    tb(s, 0.35, 1.00, W_IN - 0.70, 0.38,
       'Sistem memaksa validasi identitas logistik medis (Batch/SN) sebelum stok diizinkan masuk — prinsip Gatekeeper.',
       sz=13, italic=True, color=C_MUTED)

    img(s, IMG['act_terima'], 0.35, 1.42, W_IN - 0.70, H_IN - 1.70)


def slide_12_act_pos_ecom(prs):
    """Activity Diagram POS + E-Commerce."""
    s = blank(prs)
    gradient_header(s, 'Activity Diagram — Transaksi Kasir (POS) & E-Commerce', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    half = (W_IN - 0.90) / 2
    rect(s, 0.35, 1.05, half, 0.42, fill=C_BLUE)
    tb(s, 0.40, 1.07, half - 0.10, 0.38, '🏪  Transaksi Kasir (POS) — Luring', sz=13, bold=True, color=C_WHITE)
    img(s, IMG['act_pos'], 0.35, 1.50, half, H_IN - 1.75)

    rx = 0.35 + half + 0.20
    rect(s, rx, 1.05, half, 0.42, fill=C_TEAL)
    tb(s, rx + 0.05, 1.07, half - 0.10, 0.38, '🌐  Pesanan Daring (E-Commerce)', sz=13, bold=True, color=C_WHITE)
    img(s, IMG['act_ecom'], rx, 1.50, half, H_IN - 1.75)


def slide_13_erd(prs):
    """ERD & Logical Database."""
    s = blank(prs)
    gradient_header(s, 'Entity Relationship Diagram (ERD) & Logical Database', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    tb(s, 0.35, 1.00, W_IN - 0.70, 0.38,
       '13 tabel relasional  |  Pusat relasi: produk_variasi (FK: id_variasi)  |  4 klaster: Aktor/RBAC, Multi-UOM, Logistik Medis, Transaksi & Audit',
       sz=12, italic=True, color=C_MUTED)

    img(s, IMG['erd'], 0.35, 1.42, W_IN - 0.70, H_IN - 1.70)


def slide_14_wireframe(prs):
    """Wireframe - 6 key screens."""
    s = blank(prs)
    gradient_header(s, 'Wireframe — Rancangan Antarmuka Utama', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    screens = [
        ('wf_pos',       '🏪 POS (Kasir)'),
        ('wf_dashboard_a','📊 Dashboard Admin'),
        ('wf_penerimaan', '📥 Penerimaan Barang'),
        ('wf_ecom',       '🌐 E-Commerce'),
        ('wf_checkout',   '💳 Checkout Pembayaran'),
        ('wf_laplogistik','📋 Laporan Logistik Medis'),
    ]
    sw = (W_IN - 0.90) / 3
    sh = (H_IN - 1.40) / 2
    for i, (key, label) in enumerate(screens):
        col = i % 3
        row = i // 3
        x = 0.35 + col * (sw + 0.08)
        y = 1.05 + row * (sh + 0.08)
        rect(s, x, y + sh - 0.30, sw, 0.30, fill=C_NAVY)
        tb(s, x + 0.05, y + sh - 0.28, sw - 0.10, 0.26,
           label, sz=10, bold=True, color=C_WHITE)
        img(s, IMG[key], x, y, sw, sh - 0.30)


def slide_15_kesimpulan(prs):
    """Kesimpulan & Luaran."""
    s = blank(prs)
    gradient_header(s, 'Kesimpulan & Luaran Penelitian', 'SEMINAR PROPOSAL')
    hline(s, 0.35, 0.92, W_IN - 0.70)

    # Simpulan box
    rect(s, 0.35, 1.05, W_IN - 0.70, 3.20, fill=C_LIGHT, line=C_BLUE, lw=1.5, rounded=True)
    rect(s, 0.35, 1.05, W_IN - 0.70, 0.60, fill=C_BLUE)
    tb(s, 0.50, 1.08, W_IN - 1.00, 0.54, '📌  Simpulan', sz=16, bold=True, color=C_WHITE)
    hline(s, 0.50, 1.72, W_IN - 1.00, color=C_TEAL, h=0.03)
    tb(s, 0.50, 1.82, W_IN - 1.00, 2.30,
       'Dirancang sistem informasi inventori omnichannel berbasis web untuk ZenCare Medical yang menyinkronkan '
       'transaksi kasir fisik (POS) dan penjualan daring (E-Commerce) dalam satu basis data terpusat. '
       'Dengan metode RAD, sistem mengotomatisasi konversi Multi-UOM, menerapkan Gatekeeper FEFO untuk '
       'pelacakan obat berbasis Batch, dan mencatat Serial Number untuk garansi Alat Kesehatan — '
       'menciptakan tata kelola logistik medis yang akurat, aman, dan transparan.',
       sz=14, color=C_TEXT)

    # Luaran boxes
    tb(s, 0.35, 4.38, W_IN - 0.70, 0.38, 'Luaran Penelitian:', sz=14, bold=True, color=C_BLUE)
    luaran = [
        ('🌐', 'Sistem Web\nOmnichannel', 'POS + E-Commerce\n1 basis data'),
        ('🔄', 'Dual-UOM\nOtomatis', 'Box ↔ Strip\ntanpa hitung manual'),
        ('💊', 'Gatekeeper\nFEFO & SN', 'Validasi logistik\nmedis wajib'),
        ('📋', 'Dokumentasi\n& Laporan UAT', 'Pengujian sinkronisasi\nstok omnichannel'),
    ]
    lw = (W_IN - 0.90) / 4
    for i, (icon, title, body) in enumerate(luaran):
        x = 0.35 + i * (lw + 0.08)
        rect(s, x, 4.78, lw, 2.50, fill=C_LIGHTGRAY, line=C_TEAL, lw=1.2, rounded=True)
        rect(s, x, 4.78, lw, 0.62, fill=C_TEAL)
        tb(s, x + 0.08, 4.82, 0.50, 0.52, icon, sz=22, color=C_WHITE, align=PP_ALIGN.CENTER)
        tb(s, x + 0.60, 4.85, lw - 0.68, 0.52, title, sz=11, bold=True, color=C_WHITE)
        tb(s, x + 0.12, 5.48, lw - 0.24, 1.70, body, sz=12, color=C_TEXT)


def slide_16_penutup(prs):
    """Closing slide."""
    s = blank(prs)
    rect(s, 0, 0, W_IN, H_IN, fill=C_NAVY)
    rect(s, 0, 0, 0.18, H_IN, fill=C_TEAL)
    rect(s, W_IN - 0.18, 0, 0.18, H_IN, fill=C_TEAL)
    rect(s, 0, H_IN - 0.90, W_IN, 0.90, fill=RGBColor(0x07, 0x1A, 0x40))
    rect(s, 0, H_IN - 0.92, W_IN, 0.06, fill=C_TEAL)

    # Logo placeholder
    rect(s, 0.50, 0.25, 1.50, 1.00, fill=RGBColor(0x16, 0x35, 0x70), line=C_TEAL, lw=1, rounded=True)
    tb(s, 0.52, 0.33, 1.46, 0.80, '[ LOGO\nMA CHUNG ]', sz=9, color=C_TEAL, align=PP_ALIGN.CENTER)

    tb(s, 0.35, 1.50, W_IN - 0.70, 1.80,
       'TERIMA KASIH', sz=62, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
    rect(s, 3.50, 3.38, W_IN - 7.00, 0.07, fill=C_TEAL)
    tb(s, 0.35, 3.52, W_IN - 0.70, 0.75,
       'Sesi Tanya Jawab  (Q&A)', sz=26, color=RGBColor(0xA5, 0xD8, 0xFF), align=PP_ALIGN.CENTER)

    tb(s, 0.35, 4.55, W_IN - 0.70, 0.45,
       'Marcell Chandra Kenchana  •  NIM: 322310015', sz=16, bold=True, color=C_WHITE, align=PP_ALIGN.CENTER)
    tb(s, 0.35, 5.05, W_IN - 0.70, 0.38,
       'Program Studi Sistem Informasi  •  Fakultas Teknologi dan Desain  •  Universitas Ma Chung', sz=12, color=C_MUTED, align=PP_ALIGN.CENTER)
    tb(s, 0.35, 5.55, W_IN - 0.70, 0.38,
       'Dosen Pembimbing: Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.',
       sz=12, color=RGBColor(0xCB, 0xDF, 0xF0), align=PP_ALIGN.CENTER)
    tb(s, 0.35, 6.70, W_IN - 0.70, 0.35,
       'Malang, 2026', sz=11, italic=True, color=C_MUTED, align=PP_ALIGN.CENTER)


# ============================================================
# MAIN
# ============================================================
def build():
    prs = new_prs()

    print('Building slides...')
    slide_01_cover(prs);          print('  [1/16] Cover')
    slide_02_toc(prs);            print('  [2/16] Daftar Isi')
    slide_03_latbel(prs);         print('  [3/16] Latar Belakang')
    slide_04_batasan(prs);        print('  [4/16] Batasan Masalah')
    slide_05_gap(prs);            print('  [5/16] Research Gap')
    slide_06_novelty(prs);        print('  [6/16] Novelty')
    slide_07_usecase(prs);        print('  [7/16] Use Case Diagram')
    slide_08_workflow_before(prs);print('  [8/16] Workflow Berjalan')
    slide_09_workflow_after(prs); print('  [9/16] Workflow Usulan')
    slide_10_sitemap(prs);        print('  [10/16] Sitemap')
    slide_11_act_diagram(prs);    print('  [11/16] Activity Diagram (Penerimaan)')
    slide_12_act_pos_ecom(prs);   print('  [12/16] Activity Diagram (POS & E-Com)')
    slide_13_erd(prs);            print('  [13/16] ERD')
    slide_14_wireframe(prs);      print('  [14/16] Wireframe')
    slide_15_kesimpulan(prs);     print('  [15/16] Kesimpulan')
    slide_16_penutup(prs);        print('  [16/16] Penutup')

    prs.save(DEST)
    sz = os.path.getsize(DEST)
    print()
    print('SUCCESS! Saved to: ' + DEST)
    print('File size: ' + str(round(sz / 1024)) + ' KB')
    print('Total slides: ' + str(len(prs.slides)))


if __name__ == '__main__':
    build()
