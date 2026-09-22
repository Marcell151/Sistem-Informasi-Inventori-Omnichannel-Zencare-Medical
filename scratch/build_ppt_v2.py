"""
Build PPT v2 - Correct approach
- Copy template to new file
- Duplicate slide 2 (content template) for each new slide
- Replace text content only, keep all decorative shapes intact
"""

import copy
import shutil
import os
from lxml import etree
from pptx import Presentation
from pptx.util import Inches, Pt, Emu
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.oxml.ns import qn, nsmap

# ============================================================
# Color palette dari template original
# ============================================================
BLUE_DARK    = RGBColor(0x00, 0x50, 0x88)
TEXT_DARK    = RGBColor(0x33, 0x41, 0x55)
BADGE_GRAY   = RGBColor(0x64, 0x74, 0x8B)
WHITE        = RGBColor(0xFF, 0xFF, 0xFF)
ACCENT_TEAL  = RGBColor(0x00, 0x79, 0x9A)
RED_ACCENT   = RGBColor(0xDC, 0x26, 0x26)
GREEN_ACCENT = RGBColor(0x16, 0xA3, 0x4A)
LIGHT_BG     = RGBColor(0xE8, 0xF4, 0xFF)
LIGHT_GRAY   = RGBColor(0xF1, 0xF5, 0xF9)

SRC  = r'c:\xampp\htdocs\inventory_zencare\lain\File PPT\PPT TA.pptx'
DEST = r'c:\xampp\htdocs\inventory_zencare\lain\File PPT\Presentasi_Sempro_Marcell.pptx'

# ============================================================
# Helpers
# ============================================================
def emu(v): return int(v * 914400)

def duplicate_slide(prs, source_idx):
    """Duplicate a slide in the presentation (safe method)."""
    template_slide = prs.slides[source_idx]
    blank_layout   = prs.slide_layouts[6]  # Blank
    new_slide      = prs.slides.add_slide(blank_layout)
    # Copy all shapes from template
    for shape in template_slide.shapes:
        sp = copy.deepcopy(shape.element)
        new_slide.shapes._spTree.append(sp)
    return new_slide


def clear_content_shapes(slide, keep_names=('Group 2', 'Freeform 10', 'Freeform 15')):
    """Remove content text boxes, keeping only decorative shapes."""
    to_remove = []
    for shape in slide.shapes:
        if shape.name not in keep_names:
            to_remove.append(shape.element)
    for elem in to_remove:
        try:
            elem.getparent().remove(elem)
        except Exception:
            pass


def add_tb(slide, l, t, w, h, text='', size=21, bold=False,
           color=TEXT_DARK, align=PP_ALIGN.LEFT, wrap=True):
    """Add a text box."""
    tb = slide.shapes.add_textbox(emu(l), emu(t), emu(w), emu(h))
    tf = tb.text_frame
    tf.word_wrap = wrap
    para = tf.paragraphs[0]
    para.alignment = align
    run = para.add_run()
    run.text = text
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.color.rgb = color
    return tb


def add_para(tf, text, size=19, bold=False, color=TEXT_DARK,
             space_before=6, space_after=6, align=PP_ALIGN.LEFT, first=False):
    """Append a paragraph to an existing text frame."""
    if first:
        para = tf.paragraphs[0]
    else:
        para = tf.add_paragraph()
    para.alignment = align
    para.space_before = Pt(space_before)
    para.space_after  = Pt(space_after)
    run = para.add_run()
    run.text = text
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.color.rgb = color
    return para


def add_rect(slide, l, t, w, h, fill=None, line=None, lw=1.0):
    """Add a rectangle shape."""
    shape = slide.shapes.add_shape(
        1, emu(l), emu(t), emu(w), emu(h))  # MSO_AUTO_SHAPE_TYPE.RECTANGLE
    if fill:
        shape.fill.solid()
        shape.fill.fore_color.rgb = fill
    else:
        shape.fill.background()
    if line:
        shape.line.color.rgb = line
        shape.line.width = Pt(lw)
    else:
        shape.line.fill.background()
    return shape


def add_rounded_rect(slide, l, t, w, h, fill=None, line=None, lw=1.0, radius_pt=8):
    """Add rounded rectangle."""
    shape = slide.shapes.add_shape(
        5, emu(l), emu(t), emu(w), emu(h))  # 5 = ROUNDED_RECTANGLE
    shape.adjustments[0] = 0.05  # corner radius
    if fill:
        shape.fill.solid()
        shape.fill.fore_color.rgb = fill
    else:
        shape.fill.background()
    if line:
        shape.line.color.rgb = line
        shape.line.width = Pt(lw)
    else:
        shape.line.fill.background()
    return shape


def slide_header(slide, title, badge="SEMINAR PROPOSAL"):
    """Standard slide header: badge + title (existing decorations kept)."""
    # Badge - top right (keep consistent with template position)
    add_tb(slide, 14.20, 0.59, 5.50, 0.47,
           text=badge, size=20, bold=True, color=BADGE_GRAY, align=PP_ALIGN.RIGHT)
    # Title
    add_tb(slide, 1.25, 1.70, 17.50, 0.90,
           text=title, size=40, bold=True, color=BLUE_DARK)
    # Divider
    add_rect(slide, 1.25, 2.78, 17.50, 0.06, fill=BLUE_DARK)


def icon_card(slide, x, y, w, h, icon, title, body, body_size=17):
    """Draw a card: rounded bg, icon bar, title, divider, body text."""
    # Card background
    add_rounded_rect(slide, x, y, w, h, fill=LIGHT_BG, line=BLUE_DARK, lw=1.5)
    # Icon strip at top
    add_rect(slide, x, y, w, 0.85, fill=BLUE_DARK)
    # Icon text
    add_tb(slide, x + 0.10, y + 0.05, w - 0.20, 0.75,
           text=icon, size=30, bold=False, color=WHITE, align=PP_ALIGN.CENTER)
    # Title
    add_tb(slide, x + 0.15, y + 0.92, w - 0.30, 0.65,
           text=title, size=18, bold=True, color=BLUE_DARK, align=PP_ALIGN.CENTER)
    # Divider inside card
    add_rect(slide, x + 0.20, y + 1.62, w - 0.40, 0.05, fill=ACCENT_TEAL)
    # Body
    tb = slide.shapes.add_textbox(emu(x + 0.15), emu(y + 1.75),
                                   emu(w - 0.30), emu(h - 1.90))
    tf = tb.text_frame
    tf.word_wrap = True
    para = tf.paragraphs[0]
    run = para.add_run()
    run.text = body
    run.font.size = Pt(body_size)
    run.font.color.rgb = TEXT_DARK


# ================================================================
# BUILD
# ================================================================
def build():
    # Start from clean copy of original template
    shutil.copy2(SRC, DEST)
    prs = Presentation(DEST)

    # Identify slide templates
    # Slide 0 = Cover (Blank layout)
    # Slide 1 = Content (Latar Belakang - Blank with decorations)
    # We'll use slide 1 as content template for all content slides

    content_tmpl_idx = 1   # "Latar Belakang"

    # ============================================================
    # MODIFY SLIDE 1: COVER
    # ============================================================
    s1 = prs.slides[0]
    for shape in s1.shapes:
        if shape.name == 'TextBox 4':
            shape.text_frame.paragraphs[0].runs[0].text = 'SEMINAR PROPOSAL'
        elif shape.name == 'TextBox 5':
            tf = shape.text_frame
            tf.clear()
            p1 = tf.paragraphs[0]; p1.alignment = PP_ALIGN.CENTER
            r1 = p1.add_run()
            r1.text = 'RANCANG BANGUN SISTEM INFORMASI INVENTORY OMNICHANNEL ALAT KESEHATAN DAN OBAT'
            r1.font.size = Pt(33); r1.font.bold = True; r1.font.color.rgb = BLUE_DARK
            p2 = tf.add_paragraph(); p2.alignment = PP_ALIGN.CENTER
            r2 = p2.add_run()
            r2.text = '(Studi Kasus: ZenCare Medical)'
            r2.font.size = Pt(26); r2.font.bold = True; r2.font.color.rgb = ACCENT_TEAL
        elif shape.name == 'TextBox 6':
            tf = shape.text_frame; tf.clear()
            p = tf.paragraphs[0]; p.alignment = PP_ALIGN.LEFT
            r = p.add_run()
            r.text = 'Marcell Chandra Kenchana  |  NIM: 322310015'
            r.font.size = Pt(22); r.font.bold = True; r.font.color.rgb = TEXT_DARK
        elif shape.name == 'TextBox 12':
            tf = shape.text_frame; tf.clear()
            lines = [
                ('Program Studi Sistem Informasi  |  Fakultas Teknologi dan Desain', 18, True),
                ('Universitas Ma Chung  •  Malang, 2026', 17, False),
                ('', 12, False),
                ('Dosen Pembimbing: Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.', 16, False),
                ('Ketua Penguji: [Nama Ketua Penguji]  |  Penguji 1: [Nama Penguji 1]', 15, False),
            ]
            for i, (txt, sz, bd) in enumerate(lines):
                p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
                r = p.add_run(); r.text = txt
                r.font.size = Pt(sz); r.font.bold = bd; r.font.color.rgb = TEXT_DARK

    # ============================================================
    # Helper: Create new content slide (duplicate template, clear content)
    # ============================================================
    def new_slide(title, badge="SEMINAR PROPOSAL"):
        slide = duplicate_slide(prs, content_tmpl_idx)
        clear_content_shapes(slide, keep_names=('Group 2', 'Freeform 10', 'Freeform 15'))
        slide_header(slide, title, badge)
        return slide

    # ============================================================
    # SLIDE 2: DAFTAR ISI — 5 kotak vertikal
    # ============================================================
    s2 = new_slide('Daftar Isi')
    toc = [
        ('📌', '01', 'Latar Belakang &\nIdentifikasi Masalah', 'Overselling, konversi satuan, logistik medis'),
        ('📚', '02', 'Penelitian\nTerdahulu', 'Research gap & kebaruan sistem'),
        ('🔄', '03', 'Workflow &\nAntarmuka', 'Alur Omnichannel & validasi penerimaan'),
        ('🗄️', '04', 'Arsitektur\n& Database', 'Sitemap, Activity Diagram, ERD'),
        ('📷', '05', 'Dokumentasi &\nKesimpulan', 'Observasi lapangan ZenCare Medical'),
    ]
    cw, gap, sx, top = 3.25, 0.15, 1.25, 2.95
    for i, (icon, num, title, desc) in enumerate(toc):
        x = sx + i * (cw + gap)
        icon_card(s2, x, top, cw, 7.60, icon, title, desc, body_size=16)
        # Number badge
        add_tb(s2, x + cw - 0.75, top + 0.02, 0.70, 0.60,
               text=num, size=18, bold=True, color=ACCENT_TEAL, align=PP_ALIGN.RIGHT)

    # ============================================================
    # SLIDE 3: LATAR BELAKANG — 3 icon cards
    # ============================================================
    s3 = new_slide('Latar Belakang & Identifikasi Masalah')
    cards3 = [
        ('🏪', 'Kasir Luring & Daring\nTerpisah (Stand-Alone)',
         'Aplikasi POS fisik tidak terhubung dengan e-commerce. Setiap hari admin harus mencocokkan sisa stok secara manual, memicu risiko tinggi pesanan ganda (overselling).'),
        ('🔢', 'Konversi Multi-Satuan\nManual (Multi-UOM)',
         'E-commerce berbasis satuan grosir (Box), kasir fisik melayani eceran (Strip). Tanpa integrasi, admin harus menghitung konversi & memotong stok secara manual — rentan human error.'),
        ('💊', 'Ketiadaan Kendali\nLogistik Medis',
         'Tidak ada pencatatan tanggal kedaluwarsa berbasis Batch (FEFO) untuk obat. Tidak ada pelacakan Serial Number (SN) untuk validasi klaim garansi alat kesehatan.'),
    ]
    cw3 = 5.60
    for i, (icon, title, body) in enumerate(cards3):
        x = 1.25 + i * (cw3 + 0.28)
        icon_card(s3, x, 3.00, cw3, 7.55, icon, title, body, body_size=18)

    # ============================================================
    # SLIDE 4: BATASAN MASALAH — 3 horizontal blocks
    # ============================================================
    s4 = new_slide('Batasan Masalah')
    batasan = [
        ('🎯', 'Fokus Operasional',
         'Sistem murni menyinkronkan inventaris antara terminal POS dan E-Commerce mandiri untuk mengatasi selisih data, tanpa mencakup modul akuntansi keuangan atau perhitungan laba rugi.'),
        ('📦', 'Logika Pengeluaran Logistik',
         'Pengendalian persediaan menerapkan algoritma First Expired First Out (FEFO) berbasis Nomor Batch untuk obat, serta pencatatan Serial Number (SN) per unit untuk Alat Kesehatan.'),
        ('🔌', 'Infrastruktur Pihak Ketiga',
         'Integrasi layanan gerbang pembayaran (Midtrans API) dan kalkulasi ongkos kirim (RajaOngkir) dijalankan secara eksklusif pada lingkungan pengujian (Sandbox). E-Commerce dibangun mandiri.'),
    ]
    for i, (icon, label, body) in enumerate(batasan):
        y = 3.00 + i * 2.65
        add_rounded_rect(s4, 1.25, y, 17.50, 2.45, fill=LIGHT_BG, line=BLUE_DARK, lw=1.5)
        # Icon box
        add_rect(s4, 1.25, y, 1.20, 2.45, fill=BLUE_DARK)
        add_tb(s4, 1.27, y + 0.80, 1.16, 0.80,
               text=icon, size=32, color=WHITE, align=PP_ALIGN.CENTER)
        # Label
        add_tb(s4, 2.60, y + 0.12, 15.90, 0.60,
               text=label, size=21, bold=True, color=BLUE_DARK)
        # Divider
        add_rect(s4, 2.60, y + 0.78, 15.90, 0.04, fill=ACCENT_TEAL)
        # Body
        add_tb(s4, 2.60, y + 0.92, 15.90, 1.40,
               text=body, size=18, color=TEXT_DARK)

    # ============================================================
    # SLIDE 5: RESEARCH GAP — Table matrix
    # ============================================================
    s5 = new_slide('Research Gap — Penelitian Terdahulu')

    headers = ['Aspek', 'Rachman (2024)\n& Nuralisa (2024)', 'Mattegunta (2025)', 'Sistem Usulan\n(ZenCare Medical)']
    rows_data = [
        ('Platform', 'Web (React.js / PHP Laravel)\nSingle-channel luring', 'POS + Cloud\nEkosistem ritel umum', 'Web PHP + MySQL\nOmnichannel mandiri'),
        ('Sinkronisasi\nOmnichannel', '✗  Tidak ada\nStand-alone', '✔  Real-time\n(ritel umum)', '✔  Real-time\nBasis data tunggal'),
        ('Konversi\nMulti-UOM', '✗  Tidak ada', '✗  Tidak dibahas', '✔  Dual-UOM otomatis\nBox ↔ Strip'),
        ('Logistik\nMedis (FEFO/SN)', '✗  Tidak ada', '✗  Tidak dibahas', '✔  FEFO Batch (Obat)\n+ Serial Number (Alkes)'),
        ('Research Gap', 'Single-channel, tanpa\nFEFO & Multi-UOM', 'Tanpa karakteristik\nlogistik medis', '★  Solusi gap dari\nkedua penelitian'),
    ]
    col_widths = [2.60, 4.65, 4.30, 5.70]
    col_starts = [1.25]
    for cw in col_widths[:-1]:
        col_starts.append(col_starts[-1] + cw + 0.07)
    hdr_top = 3.05
    row_h   = 1.20

    col_colors = [BLUE_DARK, BLUE_DARK, BLUE_DARK, ACCENT_TEAL]
    for ci, (hdr, cw, cx, cc) in enumerate(zip(headers, col_widths, col_starts, col_colors)):
        add_rect(s5, cx, hdr_top, cw, 0.85, fill=cc)
        add_tb(s5, cx + 0.08, hdr_top + 0.05, cw - 0.16, 0.75,
               text=hdr, size=15, bold=True, color=WHITE, align=PP_ALIGN.CENTER)

    alt1 = RGBColor(0xE3, 0xF2, 0xFF)
    alt2 = RGBColor(0xF5, 0xF9, 0xFF)
    for ri, row in enumerate(rows_data):
        top = hdr_top + 0.85 + ri * row_h
        for ci, (cell, cw, cx) in enumerate(zip(row, col_widths, col_starts)):
            bg = (alt1 if ri % 2 == 0 else alt2)
            if ci == 0:
                bg = RGBColor(0xCB, 0xE0, 0xF5)
            if ci == 3 and ri == len(rows_data) - 1:
                bg = RGBColor(0xD1, 0xF0, 0xE0)  # green tint for gap solution
            add_rect(s5, cx, top, cw, row_h - 0.05, fill=bg, line=RGBColor(0xCC, 0xCC, 0xCC), lw=0.5)
            add_tb(s5, cx + 0.08, top + 0.08, cw - 0.16, row_h - 0.18,
                   text=cell, size=14, bold=(ci == 0), color=TEXT_DARK)

    # ============================================================
    # SLIDE 6: NOVELTY — 2 col (merah vs hijau)
    # ============================================================
    s6 = new_slide('Pembaruan Sistem — Novelty')

    # Left: red column
    add_rect(s6, 1.25, 3.00, 8.80, 0.72, fill=RED_ACCENT)
    add_tb(s6, 1.40, 3.04, 8.50, 0.64,
           text='✗   Kendala Sistem Berjalan (As-Is)', size=21, bold=True, color=WHITE)
    problems = [
        '🔴  Risiko overselling akibat aplikasi kasir yang berjalan secara terisolasi (stand-alone) dari e-commerce.',
        '🔴  Tingginya beban kerja karena konversi Multi-UOM (Box → Strip) dilakukan secara manual oleh admin setiap transaksi.',
        '🔴  Penumpukan obat rusak karena tidak ada filter umur produk (FEFO) saat pengambilan barang di rak penyimpanan.',
        '🔴  Tidak ada pencatatan Serial Number (SN) sehingga klaim garansi alat kesehatan tidak dapat divalidasi secara sistem.',
    ]
    tb_l = s6.shapes.add_textbox(emu(1.35), emu(3.80), emu(8.60), emu(6.70))
    tf_l = tb_l.text_frame; tf_l.word_wrap = True
    for i, p in enumerate(problems):
        add_para(tf_l, p, size=18, first=(i == 0), space_after=14)

    # Right: green column
    add_rect(s6, 10.20, 3.00, 8.80, 0.72, fill=GREEN_ACCENT)
    add_tb(s6, 10.35, 3.04, 8.50, 0.64,
           text='✔   Sistem Usulan (To-Be)', size=21, bold=True, color=WHITE)
    solutions = [
        '🟢  Sinkronisasi basis data tunggal Omnichannel (POS & E-Commerce) mengunci stok seketika (Reserved) saat transaksi berlangsung.',
        '🟢  Algoritma Dual-UOM mengonversi dan memotong stok secara otomatis, mengeliminasi seluruh hitungan manual kasir.',
        '🟢  Sistem Gatekeeper memaksa validasi FEFO: Nomor Batch + Tanggal Kedaluwarsa wajib diisi sebelum stok diizinkan masuk.',
        '🟢  Setiap Serial Number (SN) dicatat secara individual di basis data, mendukung penelusuran garansi secara akurat.',
    ]
    tb_r = s6.shapes.add_textbox(emu(10.30), emu(3.80), emu(8.55), emu(6.70))
    tf_r = tb_r.text_frame; tf_r.word_wrap = True
    for i, p in enumerate(solutions):
        add_para(tf_r, p, size=18, first=(i == 0), space_after=14)

    add_tb(s6, 9.55, 5.90, 0.90, 0.80,
           text='→', size=44, bold=True, color=ACCENT_TEAL, align=PP_ALIGN.CENTER)

    # ============================================================
    # SLIDE 7: SITEMAP
    # ============================================================
    s7 = new_slide('Sitemap — Arsitektur Navigasi')
    add_tb(s7, 1.25, 3.00, 17.50, 0.55,
           text='Hak akses dipisahkan tegas ke 3 lingkungan: Pelanggan (Front-End)  |  Admin (Operasional)  |  Superadmin (Manajerial)',
           size=18, color=TEXT_DARK)

    sitemap_data = [
        ('👤  Pelanggan', ACCENT_TEAL, [
            'Autentikasi & Manajemen Akun',
            'Katalog Produk & Pencarian',
            'Keranjang & Checkout',
            'Pilih Metode: Kurir / Pick-up',
            'Payment Gateway (Midtrans)',
            'Riwayat Pesanan & Invoice',
        ]),
        ('🧑‍💼  Admin (Staff)', BLUE_DARK, [
            'Dashboard (Peringatan Stok & Kadaluwarsa)',
            'Master Data (Produk, Satuan, Supplier)',
            'Kasir / POS (Transaksi Luring)',
            'Kelola Pesanan Daring (E-Commerce)',
            'Penerimaan Barang (Gatekeeper FEFO/SN)',
            'Mutasi Stok & Kartu Stok',
        ]),
        ('👑  Superadmin (Pemilik)', RGBColor(0x7C, 0x3A, 0xED), [
            'Dashboard Analitik (Luring vs Daring)',
            'Semua Akses Admin +',
            'Laporan Kuantitas & Logistik Medis',
            'Manajemen Pengguna (RBAC)',
            'Pengaturan Sistem (API & E-Commerce)',
            'Log Audit & Kartu Stok',
        ]),
    ]
    for i, (actor, color, modules) in enumerate(sitemap_data):
        x = 1.25 + i * 6.10
        w = 5.85
        add_rect(s7, x, 3.65, w, 0.72, fill=color)
        add_tb(s7, x + 0.1, 3.68, w - 0.2, 0.65,
               text=actor, size=19, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
        add_rounded_rect(s7, x, 4.40, w, 6.10, fill=LIGHT_BG, line=color, lw=1.5)
        tb = s7.shapes.add_textbox(emu(x + 0.20), emu(4.55), emu(w - 0.40), emu(5.85))
        tf = tb.text_frame; tf.word_wrap = True
        for j, mod in enumerate(modules):
            add_para(tf, '▸  ' + mod, size=17, first=(j == 0), space_after=10, color=TEXT_DARK)

    add_tb(s7, 1.25, 10.45, 17.50, 0.50,
           text='[ Masukkan Gambar Sitemap di sini ]',
           size=13, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ============================================================
    # SLIDE 8: WORKFLOW BARANG KELUAR
    # ============================================================
    s8 = new_slide('Workflow Barang Keluar — Omnichannel')
    add_rounded_rect(s8, 1.25, 3.00, 17.50, 0.65, fill=RGBColor(0xE0, 0xF2, 0xFF), line=ACCENT_TEAL, lw=1)
    add_tb(s8, 1.40, 3.05, 17.20, 0.55,
           text='⚡  Sistem bertindak sebagai jembatan tunggal: mengonversi satuan otomatis dan mengunci stok (Reserved) untuk mencegah overselling.',
           size=17, color=BLUE_DARK, bold=True)

    for ci, (hdr_color, hdr_label, steps) in enumerate([
        (BLUE_DARK, '🏪  Kasir Fisik (POS) — Transaksi Luring', [
            '1. Admin pindai label / input SKU produk.',
            '2. Sistem tampilkan instruksi "Ambil Fisik" spesifik (Nomor Batch FEFO atau SN).',
            '3. Admin masukkan semua item, tekan "Proses Pembayaran".',
            '4. Pilih metode bayar (Tunai / Non-Tunai). Sistem hitung kembalian jika tunai.',
            '5. Sistem konversi satuan & potong stok permanen (Batch/SN terpilih).',
            '6. Catat mutasi keluar ke Kartu Stok → Admin cetak struk.',
        ]),
        (ACCENT_TEAL, '🌐  E-Commerce (Daring) — Transaksi Online', [
            '1. Pelanggan checkout → pilih metode: Kurir atau Pick-up in Store.',
            '2. Sistem teruskan ke Payment Gateway (Midtrans Sandbox).',
            '3. Sistem alokasikan stok: Obat → FEFO Batch, Alkes → Serial Number.',
            '4. Stok dikunci sementara (status: Reserved) → pesanan "Diproses".',
            '5. Admin ubah status: "Dikirim" → potong stok + catat Kartu Stok (Kurir).',
            '6. Status "Selesai" (Pick-up) → potong stok + catat Kartu Stok.',
        ]),
    ]):
        x = 1.25 + ci * 9.15
        w = 8.85
        add_rect(s8, x, 3.75, w, 0.65, fill=hdr_color)
        add_tb(s8, x + 0.12, 3.77, w - 0.24, 0.60,
               text=hdr_label, size=18, bold=True, color=WHITE)
        tb = s8.shapes.add_textbox(emu(x + 0.15), emu(4.50), emu(w - 0.30), emu(5.60))
        tf = tb.text_frame; tf.word_wrap = True
        for j, step in enumerate(steps):
            add_para(tf, step, size=18, first=(j == 0), space_after=10)

    add_tb(s8, 1.25, 10.20, 17.50, 0.55,
           text='[ Masukkan Gambar Workflow Barang Keluar / Screenshot Wireframe POS di sini ]',
           size=13, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ============================================================
    # SLIDE 9: WORKFLOW PENERIMAAN BARANG
    # ============================================================
    s9 = new_slide('Workflow Penerimaan Barang & Logistik')
    add_rect(s9, 1.25, 3.00, 17.50, 0.68, fill=ACCENT_TEAL)
    add_tb(s9, 1.40, 3.03, 17.20, 0.62,
           text='🔒  Sistem sebagai Gatekeeper Logistik: Data tidak masuk ke stok sebelum identitas medis tervalidasi lengkap.',
           size=19, bold=True, color=WHITE)

    gate_steps = [
        (BLUE_DARK, '1', 'Admin input dokumen penerimaan', 'Nomor Referensi (Nota), Kuantitas, Harga Beli, pilih Supplier.'),
        (BLUE_DARK, '2', 'Percabangan: Kategori Obat', 'Wajib input Nomor Batch + Tanggal Kedaluwarsa → parameter FEFO.'),
        (BLUE_DARK, '3', 'Percabangan: Alat Kesehatan', 'Wajib input Serial Number (SN) per unit → dicatat di tabel unit_serial.'),
        (RED_ACCENT, '4', 'Sistem validasi kelengkapan', 'Jika tidak lengkap → sistem TOLAK, tampilkan peringatan, looping ke form.'),
        (GREEN_ACCENT, '5', 'Data lolos validasi', 'Sistem simpan ke DB → tambah stok_toko → catat mutasi masuk ke kartu_stok.'),
        (GREEN_ACCENT, '6', 'Instruksi cetak label batch', 'Admin cetak stiker label → tempel pada fisik barang → susun di rak sesuai urutan kadaluwarsa.'),
    ]
    for i, (color, num, label, detail) in enumerate(gate_steps):
        y = 3.80 + i * 1.07
        add_rect(s9, 1.25, y, 0.70, 0.90, fill=color)
        add_tb(s9, 1.27, y + 0.15, 0.66, 0.60,
               text=num, size=26, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
        add_rect(s9, 1.97, y, 0.04, 0.90, fill=RGBColor(0xCC, 0xCC, 0xCC))
        add_tb(s9, 2.10, y + 0.05, 4.20, 0.40, text=label, size=16, bold=True, color=BLUE_DARK)
        add_tb(s9, 2.10, y + 0.48, 16.55, 0.40, text=detail, size=16, color=TEXT_DARK)

    add_tb(s9, 1.25, 10.30, 17.50, 0.55,
           text='[ Masukkan Gambar Workflow Barang Masuk / Screenshot Form Penerimaan di sini ]',
           size=13, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ============================================================
    # SLIDE 10: ACTIVITY DIAGRAM
    # ============================================================
    s10 = new_slide('Activity Diagram — Validasi Penerimaan Barang')
    add_tb(s10, 1.25, 3.00, 17.50, 0.55,
           text='Pemodelan logika Gatekeeper: percabangan if/else memastikan sistem menolak data tidak lengkap sebelum stok bertambah.',
           size=18, color=TEXT_DARK)

    for ci, (color, label, steps) in enumerate([
        (BLUE_DARK, '💊  Alur: Obat — Logika FEFO Batch', [
            '1. Admin buka Penerimaan Barang, pilih produk kategori Obat.',
            '2. Input: No. Referensi, Kuantitas, Harga Beli.',
            '3. WAJIB: Nomor Batch + Tanggal Kedaluwarsa.',
            '4. Tekan Simpan → sistem validasi kelengkapan.',
            '✗ Tidak lengkap → peringatan → looping ke form.',
            '✔ Lengkap → simpan ke tabel stok_batch.',
            '   (ORDER BY tgl_exp ASC untuk eksekusi FEFO)',
            '5. Update stok_toko, catat kartu_stok, cetak label.',
        ]),
        (ACCENT_TEAL, '🩺  Alur: Alat Kesehatan — Serial Number', [
            '1. Admin buka Penerimaan Barang, pilih produk Alkes.',
            '2. Input: No. Referensi, Kuantitas, Harga Beli.',
            '3. WAJIB: Serial Number (SN) per unit alat.',
            '4. Tekan Simpan → sistem cek duplikasi SN.',
            '✗ SN duplikat → tolak → looping ke form.',
            '✔ SN unik → simpan ke tabel unit_serial.',
            '   (status: Tersedia → Terjual saat penjualan)',
            '5. Update stok_toko, catat kartu_stok, cetak label.',
        ]),
    ]):
        x = 1.25 + ci * 9.15
        w = 8.85
        add_rect(s10, x, 3.65, w, 0.65, fill=color)
        add_tb(s10, x + 0.12, 3.67, w - 0.24, 0.60, text=label, size=18, bold=True, color=WHITE)
        add_rounded_rect(s10, x, 4.35, w, 6.10, fill=LIGHT_BG, line=color, lw=1.5)
        tb = s10.shapes.add_textbox(emu(x + 0.20), emu(4.50), emu(w - 0.40), emu(5.85))
        tf = tb.text_frame; tf.word_wrap = True
        for j, step in enumerate(steps):
            c = RED_ACCENT if step.startswith('✗') else (GREEN_ACCENT if step.startswith('✔') else TEXT_DARK)
            bd = step.startswith('✗') or step.startswith('✔')
            add_para(tf, step, size=17, first=(j == 0), space_after=7, color=c, bold=bd)

    add_tb(s10, 1.25, 10.40, 17.50, 0.55,
           text='[ Masukkan Gambar Activity Diagram Penerimaan Barang di sini ]',
           size=13, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ============================================================
    # SLIDE 11: ERD / LOGICAL DATABASE
    # ============================================================
    s11 = new_slide('Logical Database — Entity Relationship Diagram')
    add_tb(s11, 1.25, 3.00, 17.50, 0.55,
           text='13 Tabel Relasional  |  Pusat relasi: tabel produk_variasi (FK: id_variasi)  |  Dirancang untuk mengakomodasi kompleksitas logistik medis.',
           size=17, bold=False, color=TEXT_DARK)

    clusters = [
        (BLUE_DARK, '🔒 Klaster Aktor & RBAC',
         'Tabel: users\n\nField role: ENUM (superadmin, admin, pelanggan)\nBasis kontrol hak akses seluruh sistem secara terpusat.'),
        (ACCENT_TEAL, '🔄 Klaster Konversi Multi-UOM',
         'Tabel: produk_induk → produk_variasi\n\nField krusial: rasio_konversi\nPengali otomatis Box ↔ Strip tanpa hitung manual.'),
        (RGBColor(0x7C, 0x3A, 0xED), '📦 Klaster Logistik Medis',
         'Tabel: stok_toko + stok_batch + unit_serial\n\nstok_batch: ORDER BY tgl_exp ASC → FEFO\nunit_serial: status Tersedia/Terjual/Retur-Rusak'),
        (RGBColor(0x0F, 0x76, 0x6E), '📜 Klaster Transaksi & Audit',
         'Tabel: penerimaan_stok, penjualan, detail_penjualan, kartu_stok\n\nkartu_stok: buku besar permanen\ncatatan_logistik: riwayat Batch/SN terpotong'),
    ]
    cw11 = 8.55
    for i, (color, title, body) in enumerate(clusters):
        x = 1.25 + (i % 2) * (cw11 + 0.35)
        y = 3.65 + (i // 2) * 3.35
        add_rounded_rect(s11, x, y, cw11, 3.15, fill=LIGHT_BG, line=color, lw=2)
        add_rect(s11, x, y, cw11, 0.72, fill=color)
        add_tb(s11, x + 0.15, y + 0.08, cw11 - 0.30, 0.60,
               text=title, size=19, bold=True, color=WHITE)
        add_tb(s11, x + 0.20, y + 0.82, cw11 - 0.40, 2.20,
               text=body, size=16, color=TEXT_DARK)

    add_tb(s11, 1.25, 10.30, 17.50, 0.55,
           text='[ Masukkan Gambar ERD & Logical Database (13 Tabel) di sini ]',
           size=13, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ============================================================
    # SLIDE 12: TABEL ESENSIAL
    # ============================================================
    s12 = new_slide('Struktur Tabel Esensial')

    for ci, (color, title, note, field_rows) in enumerate([
        (BLUE_DARK, '🔄  produk_variasi — Konversi Multi-UOM',
         '★  Field rasio_konversi adalah kunci otomatisasi konversi Box → Strip',
         [('id_variasi', 'INT PK', 'ID unik variasi produk'),
          ('id_produk_induk', 'INT FK', 'Relasi ke tabel produk_induk'),
          ('satuan_kecil / besar', 'VARCHAR', 'Label: Strip (eceran) & Box (grosir)'),
          ('rasio_konversi', 'SMALLINT ★', 'Pengali otomatis konversi stok lintas satuan'),
          ('harga_jual_kecil', 'DECIMAL', 'Harga per eceran (POS kasir)'),
          ('harga_jual_besar', 'DECIMAL', 'Harga per grosir (E-Commerce)'),
          ('tampil_di_online', 'BOOLEAN', 'Izin tayang di etalase e-commerce')]),
        (ACCENT_TEAL, '💊  stok_batch — Logika FEFO Obat',
         '★  ORDER BY tgl_exp ASC → pemotongan selalu pada batch kadaluwarsa terdekat',
         [('id_stok_batch', 'INT PK', 'ID data batch produksi'),
          ('id_variasi', 'INT FK', 'Relasi ke tabel produk_variasi'),
          ('no_batch', 'VARCHAR', 'Nomor identitas gelombang produksi'),
          ('tgl_exp', 'DATE ★', 'Parameter FEFO: tanggal kadaluwarsa fisik'),
          ('stok_sisa', 'INT', 'Sisa kuantitas batch ini'),
          ('created_at', 'TIMESTAMP', 'Waktu batch diregistrasi ke sistem'),
          ('', '', '')]),
    ]):
        x = 1.25 + ci * 9.15
        w = 8.85
        add_rect(s12, x, 3.00, w, 0.68, fill=color)
        add_tb(s12, x + 0.12, 3.02, w - 0.24, 0.64, text=title, size=17, bold=True, color=WHITE)
        add_rounded_rect(s12, x, 3.50, w, 0.45, fill=RGBColor(0xFF, 0xF9, 0xC4), line=color, lw=1)
        add_tb(s12, x + 0.15, 3.53, w - 0.30, 0.40, text=note, size=14, bold=True, color=RGBColor(0x78, 0x35, 0x0F))

        # Table header
        col_xs_r = [x + 0.05, x + 2.25, x + 4.05]
        col_ws_r = [2.10, 1.70, 4.90]
        for cj, (cx2, cw2, cl) in enumerate(zip(col_xs_r, col_ws_r, ['Field', 'Tipe', 'Keterangan'])):
            add_rect(s12, cx2, 4.02, cw2, 0.42, fill=RGBColor(0xCC, 0xE5, 0xF5))
            add_tb(s12, cx2 + 0.05, 4.04, cw2 - 0.10, 0.38, text=cl, size=13, bold=True, color=BLUE_DARK)

        for ri, (f, t, k) in enumerate(field_rows):
            ry = 4.48 + ri * 0.87
            is_star = '★' in t
            bg = RGBColor(0xFF, 0xF9, 0xC4) if is_star else (LIGHT_BG if ri % 2 == 0 else WHITE)
            for cj, (cx2, cw2, cell) in enumerate(zip(col_xs_r, col_ws_r, [f, t, k])):
                if cell:
                    add_rect(s12, cx2, ry, cw2, 0.82, fill=bg, line=RGBColor(0xCC, 0xCC, 0xCC), lw=0.5)
                    add_tb(s12, cx2 + 0.05, ry + 0.08, cw2 - 0.10, 0.68,
                           text=cell, size=12, bold=(is_star and cj == 0), color=TEXT_DARK)

    # ============================================================
    # SLIDE 13: DOKUMENTASI LAPANGAN
    # ============================================================
    s13 = new_slide('Dokumentasi Lapangan — Observasi ZenCare Medical')
    add_rect(s13, 1.25, 3.00, 17.50, 0.68, fill=ACCENT_TEAL)
    add_tb(s13, 1.40, 3.03, 17.20, 0.62,
           text='📍  Seluruh analisis kebutuhan dibangun berdasarkan observasi langsung & wawancara di ZenCare Medical Muharto.',
           size=18, bold=True, color=WHITE)

    for i, (ph, label) in enumerate([
        ('[ Foto Observasi\nAktivitas Kasir ]', 'Observasi 1'),
        ('[ Foto Proses\nPenerimaan Barang ]', 'Observasi 2'),
        ('[ Foto Bersama\nPemilik ZenCare ]', 'Bersama Pemilik'),
    ]):
        x = 1.25 + i * 5.95
        add_rounded_rect(s13, x, 3.80, 5.55, 4.80, fill=LIGHT_BG, line=BLUE_DARK, lw=1.5)
        add_tb(s13, x + 0.2, 5.00, 5.15, 1.60, text=ph, size=16, color=BADGE_GRAY, align=PP_ALIGN.CENTER)
        add_rect(s13, x, 8.63, 5.55, 0.40, fill=BLUE_DARK)
        add_tb(s13, x + 0.1, 8.65, 5.35, 0.36, text=label, size=14, bold=True, color=WHITE, align=PP_ALIGN.CENTER)

    findings = [
        '✔  Wawancara manajemen membuktikan overselling akibat sistem kasir yang tidak terhubung dengan e-commerce.',
        '✔  Observasi lapangan mengonfirmasi konversi Multi-UOM dilakukan sepenuhnya secara manual — rentan human error.',
        '✔  Tidak ditemukan pencatatan Nomor Batch maupun tanggal kedaluwarsa pada proses penerimaan barang dari pemasok.',
    ]
    tb = s13.shapes.add_textbox(emu(1.25), emu(9.15), emu(17.50), emu(1.80))
    tf = tb.text_frame; tf.word_wrap = True
    for j, f in enumerate(findings):
        add_para(tf, f, size=17, first=(j == 0), space_after=5)

    # ============================================================
    # SLIDE 14: KESIMPULAN
    # ============================================================
    s14 = new_slide('Kesimpulan')
    add_rounded_rect(s14, 1.25, 3.00, 17.50, 4.30, fill=LIGHT_BG, line=BLUE_DARK, lw=2)
    add_rect(s14, 1.25, 3.00, 17.50, 0.72, fill=BLUE_DARK)
    add_tb(s14, 1.40, 3.04, 17.20, 0.64,
           text='📌  Simpulan Penelitian', size=22, bold=True, color=WHITE)
    add_rect(s14, 1.60, 3.78, 17.15, 0.04, fill=ACCENT_TEAL)
    add_tb(s14, 1.60, 3.90, 16.95, 3.25,
           text=(
               'Melalui penelitian ini, dirancang sebuah sistem informasi inventori omnichannel berbasis web untuk '
               'menjembatani kesenjangan antara transaksi kasir fisik (POS) dan penjualan daring di ZenCare Medical. '
               'Dengan metode Rapid Application Development (RAD), sistem ini menjadi solusi atas permasalahan '
               'overselling dan inefisiensi konversi Multi-UOM yang selama ini dilakukan secara manual.\n\n'
               'Implementasi basis data terpusat dengan fitur Gatekeeper berlogika First Expired First Out (FEFO) '
               'diproyeksikan mampu menciptakan tata kelola logistik medis yang akurat, aman, dan transparan.'
           ), size=19, color=TEXT_DARK)

    # Luaran boxes
    luaran_items = [
        ('🌐', 'Sistem Web\nOmnichannel', 'POS + E-Commerce\ndalam 1 basis data'),
        ('🔄', 'Otomatisasi\nDual-UOM', 'Box ↔ Strip\notomatis'),
        ('💊', 'Gatekeeper\nFEFO & SN', 'Validasi logistik\nmedis wajib'),
        ('📋', 'Dokumentasi\n& UAT', 'Laporan pengujian\nsinkronisasi stok'),
    ]
    add_tb(s14, 1.25, 7.42, 17.50, 0.50,
           text='Luaran Penelitian:', size=18, bold=True, color=BLUE_DARK)
    lw2 = 3.95
    for i, (icon, title, body) in enumerate(luaran_items):
        x = 1.25 + i * (lw2 + 0.15)
        add_rounded_rect(s14, x, 7.95, lw2, 2.75, fill=LIGHT_BG, line=ACCENT_TEAL, lw=1.5)
        add_tb(s14, x + 0.10, 8.02, lw2 - 0.20, 0.55, text=icon + '  ' + title, size=16, bold=True, color=BLUE_DARK)
        add_rect(s14, x + 0.15, 8.60, lw2 - 0.30, 0.04, fill=ACCENT_TEAL)
        add_tb(s14, x + 0.15, 8.70, lw2 - 0.30, 1.85, text=body, size=15, color=TEXT_DARK)

    # ============================================================
    # SLIDE 15: PENUTUP
    # ============================================================
    s15 = duplicate_slide(prs, content_tmpl_idx)
    clear_content_shapes(s15, keep_names=('Group 2', 'Freeform 10', 'Freeform 15'))
    add_tb(s15, 14.20, 0.59, 5.50, 0.47,
           text='SEMINAR PROPOSAL', size=20, bold=True, color=BADGE_GRAY, align=PP_ALIGN.RIGHT)
    add_tb(s15, 1.25, 2.60, 17.50, 2.20,
           text='TERIMA KASIH', size=80, bold=True, color=BLUE_DARK, align=PP_ALIGN.CENTER)
    add_rect(s15, 4.50, 4.90, 11.00, 0.10, fill=ACCENT_TEAL)
    add_tb(s15, 1.25, 5.10, 17.50, 0.90,
           text='Sesi Tanya Jawab  (Q&A)', size=34, color=TEXT_DARK, align=PP_ALIGN.CENTER)
    add_tb(s15, 1.25, 6.20, 17.50, 0.60,
           text='Marcell Chandra Kenchana  •  NIM: 322310015  •  Program Studi Sistem Informasi',
           size=20, color=BADGE_GRAY, align=PP_ALIGN.CENTER)
    add_tb(s15, 1.25, 6.90, 17.50, 0.55,
           text='Universitas Ma Chung  •  Malang, 2026',
           size=18, color=BADGE_GRAY, align=PP_ALIGN.CENTER)
    add_tb(s15, 1.25, 7.55, 17.50, 0.55,
           text='Dosen Pembimbing: Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.',
           size=17, color=TEXT_DARK, align=PP_ALIGN.CENTER)
    add_tb(s15, 1.25, 9.00, 17.50, 0.55,
           text='[ Logo Universitas Ma Chung ]',
           size=14, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ============================================================
    # SAVE
    # ============================================================
    prs.save(DEST)
    print('SUCCESS! Saved to: ' + DEST)
    print('Total slides: ' + str(len(prs.slides)))
    for i, slide in enumerate(prs.slides):
        texts = [s.text_frame.text.strip()[:50] for s in slide.shapes if s.has_text_frame and s.text_frame.text.strip()]
        print('  Slide ' + str(i+1) + ': ' + (texts[0] if texts else '[empty]'))


if __name__ == '__main__':
    build()
