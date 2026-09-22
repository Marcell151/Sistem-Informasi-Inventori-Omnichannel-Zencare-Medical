"""
Script untuk membuat Presentasi Seminar Proposal 15 Slide
Berdasarkan template PPT TA.pptx milik Marcell Chandra Kenchana
"""

import copy
import shutil
from pptx import Presentation
from pptx.util import Inches, Pt, Emu, Cm
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.oxml.ns import qn
from lxml import etree

# ============================================================
# Color palette dari template
# ============================================================
BLUE_DARK   = RGBColor(0x00, 0x50, 0x88)   # Judul biru gelap
TEXT_DARK   = RGBColor(0x33, 0x41, 0x55)   # Body text
BADGE_GRAY  = RGBColor(0x64, 0x74, 0x8B)   # Watermark label
WHITE       = RGBColor(0xFF, 0xFF, 0xFF)
ACCENT_TEAL = RGBColor(0x00, 0x79, 0x9A)   # Aksen teal biru
RED_ACCENT  = RGBColor(0xDC, 0x26, 0x26)   # Merah untuk silang
GREEN_ACCENT= RGBColor(0x16, 0xA3, 0x4A)   # Hijau untuk centang
LIGHT_BG    = RGBColor(0xF0, 0xF7, 0xFF)   # Background kotak biru muda
BORDER_BLUE = RGBColor(0x00, 0x50, 0x88)

# ============================================================
# Ukuran slide (widescreen 20" x 11.25")
# ============================================================
W = 20.0  # inches
H = 11.25 # inches


def emu(v_inch):
    return int(v_inch * 914400)


def load_template():
    return Presentation(r'c:\xampp\htdocs\inventory_zencare\lain\File PPT\PPT TA.pptx')


def set_text_simple(tf, text, size=21, bold=False, color=TEXT_DARK, align=PP_ALIGN.LEFT):
    """Replace all text in a text frame with simple text."""
    tf.clear()
    para = tf.paragraphs[0]
    para.alignment = align
    run = para.add_run()
    run.text = text
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.color.rgb = color


def add_textbox(slide, left, top, width, height,
                text='', size=21, bold=False,
                color=TEXT_DARK, align=PP_ALIGN.LEFT,
                wrap=True):
    txBox = slide.shapes.add_textbox(emu(left), emu(top), emu(width), emu(height))
    tf = txBox.text_frame
    tf.word_wrap = wrap
    set_text_simple(tf, text, size=size, bold=bold, color=color, align=align)
    return txBox


def add_rect(slide, left, top, width, height, fill_color=None, line_color=None, line_width=None):
    shape = slide.shapes.add_shape(1, emu(left), emu(top), emu(width), emu(height))  # MSO_SHAPE_TYPE.RECTANGLE=1
    if fill_color:
        shape.fill.solid()
        shape.fill.fore_color.rgb = fill_color
    else:
        shape.fill.background()
    if line_color:
        shape.line.color.rgb = line_color
        if line_width:
            shape.line.width = Pt(line_width)
    else:
        shape.line.fill.background()
    return shape


def clone_slide_decor(source_slide, target_slide, watermark_text="SEMINAR PROPOSAL"):
    """Copy background decorative group shapes from source slide to target slide."""
    from pptx.oxml import parse_xml
    # We'll copy the Group 2 (decorative header/footer) and Freeform shapes
    for shape in source_slide.shapes:
        if shape.name in ['Group 2', 'Freeform 15', 'Freeform 10', 'Freeform 9', 'Group 9', 'Group 5']:
            sp_elem = copy.deepcopy(shape.element)
            target_slide.shapes._spTree.append(sp_elem)


def add_slide_header(slide, title, badge="SEMINAR PROPOSAL"):
    """Add standard header: badge top-right and title below decorations."""
    # Badge top right
    add_textbox(slide, 14.0, 0.59, 5.8, 0.47,
                text=badge, size=20, bold=True, color=BADGE_GRAY, align=PP_ALIGN.RIGHT)
    # Title
    add_textbox(slide, 1.25, 1.70, 17.5, 0.78,
                text=title, size=40, bold=True, color=BLUE_DARK, align=PP_ALIGN.LEFT)


def add_divider(slide, top=3.36):
    """Add horizontal divider line."""
    add_rect(slide, 1.25, top, 17.5, 0.04, fill_color=BLUE_DARK)


# ============================================================
# Duplicate slide approach: copy slide 2 (content layout) as base
# ============================================================

def copy_slide(prs, source_index):
    """Duplicate a slide from the presentation."""
    template = prs.slides[source_index]
    blank_layout = prs.slide_layouts[6]  # Blank layout
    slide = prs.slides.add_slide(blank_layout)

    # Copy shapes from template
    for shape in template.shapes:
        sp = copy.deepcopy(shape.element)
        slide.shapes._spTree.append(sp)

    return slide


def clear_content_textboxes(slide, keep_shapes=('Group 2',)):
    """Remove content text boxes leaving only decorative shapes."""
    to_remove = []
    for shape in slide.shapes:
        if shape.name not in keep_shapes and 'Freeform' not in shape.name and 'Group' not in shape.name:
            to_remove.append(shape)
    for shape in to_remove:
        sp = shape.element
        sp.getparent().remove(sp)


# ============================================================
# Main build function
# ============================================================

def build_presentation():
    prs = load_template()

    # We'll use slide index 1 (Latar Belakang) as content slide template
    # and index 0 as cover template, index 6 as closing template

    # ---- Remove existing slides 2-8 (keep slide 1 as template base) ----
    # We'll work by adding slides then reorganizing

    # Get references before clearing
    cover_slide = prs.slides[0]
    content_template = prs.slides[1]  # Latar Belakang

    xml_slides = prs.slides._sldIdLst
    slide_ids = list(xml_slides)

    # Keep only slide 0 (cover) as reference and remove the rest
    for sid in slide_ids[1:]:
        xml_slides.remove(sid)

    # ================================================================
    # SLIDE 1: COVER (Modify existing)
    # ================================================================
    # Update existing slide 1 content
    slide1 = prs.slides[0]
    for shape in slide1.shapes:
        if shape.name == 'TextBox 4' and shape.has_text_frame:
            set_text_simple(shape.text_frame, 'SEMINAR PROPOSAL', size=20, bold=True, color=BADGE_GRAY, align=PP_ALIGN.RIGHT)
        elif shape.name == 'TextBox 5' and shape.has_text_frame:
            tf = shape.text_frame
            tf.word_wrap = True
            tf.clear()
            para = tf.paragraphs[0]
            para.alignment = PP_ALIGN.CENTER
            run = para.add_run()
            run.text = 'RANCANG BANGUN SISTEM INFORMASI INVENTORY OMNICHANNEL ALAT KESEHATAN DAN OBAT'
            run.font.size = Pt(36)
            run.font.bold = True
            run.font.color.rgb = BLUE_DARK
            # Subtitle line
            p2 = tf.add_paragraph()
            p2.alignment = PP_ALIGN.CENTER
            r2 = p2.add_run()
            r2.text = '(Studi Kasus: ZenCare Medical)'
            r2.font.size = Pt(28)
            r2.font.bold = True
            r2.font.color.rgb = ACCENT_TEAL
        elif shape.name == 'TextBox 6' and shape.has_text_frame:
            tf = shape.text_frame
            tf.clear()
            para = tf.paragraphs[0]
            para.alignment = PP_ALIGN.LEFT
            run = para.add_run()
            run.text = 'Marcell Chandra Kenchana | 322310015'
            run.font.size = Pt(22)
            run.font.bold = True
            run.font.color.rgb = TEXT_DARK
        elif shape.name == 'TextBox 12' and shape.has_text_frame:
            tf = shape.text_frame
            tf.word_wrap = True
            tf.clear()
            lines = [
                'Program Studi Sistem Informasi | Fakultas Teknologi dan Desain',
                'Universitas Ma Chung  •  Malang, 2026',
                '',
                'Dosen Pembimbing: Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.',
            ]
            for i, line in enumerate(lines):
                if i == 0:
                    para = tf.paragraphs[0]
                else:
                    para = tf.add_paragraph()
                run = para.add_run()
                run.text = line
                run.font.size = Pt(18) if i < 2 else Pt(16)
                run.font.bold = (i == 0)
                run.font.color.rgb = TEXT_DARK

    # ================================================================
    # Helper: Add new slide copying decorative elements from content_template
    # ================================================================
    blank_layout = prs.slide_layouts[6]

    def new_content_slide(title_text, badge="SEMINAR PROPOSAL"):
        slide = prs.slides.add_slide(blank_layout)
        # Copy decorative Group 2 and Freeform from content_template
        for shape in content_template.shapes:
            if shape.name in ('Group 2',) or 'Freeform' in shape.name:
                sp = copy.deepcopy(shape.element)
                slide.shapes._spTree.append(sp)
        # Badge
        add_textbox(slide, 14.0, 0.59, 5.80, 0.47,
                    text=badge, size=20, bold=True, color=BADGE_GRAY, align=PP_ALIGN.RIGHT)
        # Title
        add_textbox(slide, 1.25, 1.65, 17.5, 0.90,
                    text=title_text, size=40, bold=True, color=BLUE_DARK)
        # Divider
        add_rect(slide, 1.25, 2.80, 17.5, 0.05, fill_color=BLUE_DARK)
        return slide

    def bullet_para(tf, bold_part, normal_part, size=21, first=False):
        """Add a bullet paragraph: bold label + normal body."""
        if first:
            para = tf.paragraphs[0]
        else:
            para = tf.add_paragraph()
        para.alignment = PP_ALIGN.LEFT
        r1 = para.add_run()
        r1.text = bold_part
        r1.font.size = Pt(size)
        r1.font.bold = True
        r1.font.color.rgb = TEXT_DARK
        r2 = para.add_run()
        r2.text = normal_part
        r2.font.size = Pt(size)
        r2.font.bold = False
        r2.font.color.rgb = TEXT_DARK

    def box_3col(slide, top, height, items):
        """Draw 3 equal-width boxes side by side with title and content."""
        col_w = 5.50
        gap = 0.35
        start_x = 1.25
        for i, (icon, title, body) in enumerate(items):
            x = start_x + i * (col_w + gap)
            # Box background
            box = add_rect(slide, x, top, col_w, height, fill_color=LIGHT_BG, line_color=BORDER_BLUE, line_width=1.5)
            # Icon
            add_textbox(slide, x + 0.15, top + 0.15, col_w - 0.3, 0.6,
                        text=icon, size=28, bold=False, color=ACCENT_TEAL, align=PP_ALIGN.CENTER)
            # Title
            add_textbox(slide, x + 0.15, top + 0.80, col_w - 0.3, 0.55,
                        text=title, size=19, bold=True, color=BLUE_DARK, align=PP_ALIGN.CENTER)
            # Divider in box
            add_rect(slide, x + 0.2, top + 1.42, col_w - 0.4, 0.04, fill_color=ACCENT_TEAL)
            # Body
            txBox = slide.shapes.add_textbox(emu(x + 0.15), emu(top + 1.55), emu(col_w - 0.3), emu(height - 1.70))
            tf = txBox.text_frame
            tf.word_wrap = True
            p = tf.paragraphs[0]
            p.alignment = PP_ALIGN.LEFT
            r = p.add_run()
            r.text = body
            r.font.size = Pt(17)
            r.font.color.rgb = TEXT_DARK

    # ================================================================
    # SLIDE 2: DAFTAR ISI
    # ================================================================
    s2 = new_content_slide('Daftar Isi')
    toc_items = [
        ('📌', '01  Latar Belakang &\nIdentifikasi Masalah', 'Permasalahan overselling, konversi satuan, & logistik medis'),
        ('📚', '02  Penelitian\nTerdahulu', 'Research gap & kebaruan sistem ZenCare'),
        ('🔄', '03  Workflow &\nAntarmuka Sistem', 'Pemodelan alur Omnichannel & validasi penerimaan'),
        ('🗄️', '04  Arsitektur Teknis\n& Database', 'Sitemap, Activity Diagram, dan Logical Database'),
        ('📷', '05  Dokumentasi &\nKesimpulan', 'Validasi observasi lapangan ZenCare Medical'),
    ]
    col_w = 3.30
    gap = 0.15
    start_x = 1.25
    top = 3.00
    for i, (icon, title, desc) in enumerate(toc_items):
        x = start_x + i * (col_w + gap)
        add_rect(s2, x, top, col_w, 6.50, fill_color=LIGHT_BG, line_color=BORDER_BLUE, line_width=1.5)
        add_textbox(s2, x + 0.1, top + 0.25, col_w - 0.2, 0.6,
                    text=icon, size=30, align=PP_ALIGN.CENTER, color=ACCENT_TEAL)
        add_textbox(s2, x + 0.1, top + 1.00, col_w - 0.2, 1.30,
                    text=title, size=18, bold=True, color=BLUE_DARK, align=PP_ALIGN.CENTER)
        add_rect(s2, x + 0.2, top + 2.40, col_w - 0.4, 0.04, fill_color=ACCENT_TEAL)
        add_textbox(s2, x + 0.1, top + 2.60, col_w - 0.2, 3.70,
                    text=desc, size=16, color=TEXT_DARK, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 3: LATAR BELAKANG & IDENTIFIKASI MASALAH
    # ================================================================
    s3 = new_content_slide('Latar Belakang & Identifikasi Masalah')
    box_items_s3 = [
        ('🏪', 'Kasir Luring &\nDaring Terpisah',
         'Penggunaan aplikasi POS fisik yang stand-alone memicu risiko pesanan ganda (overselling). Sisa ketersediaan fisik tidak tersinkronisasi otomatis dengan etalase e-commerce.'),
        ('🔢', 'Konversi\nMulti-Satuan Manual',
         'Ketiadaan fitur konversi satuan memaksa admin menghitung pecahan kemasan grosir (Box) ke eceran (Strip) secara manual sebelum memotong sisa persediaan.'),
        ('💊', 'Ketiadaan Kendali\nLogistik Medis',
         'Sistem belum mendata tanggal kedaluwarsa berbasis Batch (FEFO) untuk obat, maupun pelacakan Serial Number (SN) untuk klaim garansi alat kesehatan.'),
    ]
    box_3col(s3, 3.10, 7.50, box_items_s3)

    # ================================================================
    # SLIDE 4: BATASAN MASALAH
    # ================================================================
    s4 = new_content_slide('Batasan Masalah')
    batasan = [
        ('🎯  Fokus Operasional',
         'Sistem murni menyinkronkan inventaris antara terminal POS dan E-Commerce mandiri untuk mengatasi selisih data, tanpa mencakup modul akuntansi keuangan atau perhitungan laba rugi.'),
        ('📦  Logika Pengeluaran Logistik',
         'Pengendalian persediaan menerapkan algoritma First Expired First Out (FEFO) yang mengikat pada nomor Batch untuk obat, serta pendataan Serial Number untuk Alat Kesehatan.'),
        ('🔌  Infrastruktur Pihak Ketiga',
         'Integrasi layanan gerbang pembayaran (Midtrans API) dan kalkulasi ongkos kirim (RajaOngkir) dijalankan secara eksklusif pada lingkungan pengujian (Sandbox).'),
    ]
    for i, (label, body) in enumerate(batasan):
        y = 3.10 + i * 2.55
        add_rect(s4, 1.25, y, 17.5, 2.30, fill_color=LIGHT_BG, line_color=BORDER_BLUE, line_width=1.5)
        add_textbox(s4, 1.50, y + 0.15, 17.0, 0.55,
                    text=label, size=20, bold=True, color=BLUE_DARK)
        add_textbox(s4, 1.50, y + 0.75, 17.0, 1.40,
                    text=body, size=19, color=TEXT_DARK)

    # ================================================================
    # SLIDE 5: RESEARCH GAP (PENELITIAN TERDAHULU)
    # ================================================================
    s5 = new_content_slide('Research Gap — Penelitian Terdahulu')

    # Table headers
    headers = ['Komponen', 'Rachman dkk. (2024)\n& Nuralisa dkk. (2024)', 'Mattegunta (2025)', 'Sistem Usulan\n(ZenCare Medical)']
    rows_data = [
        ('Platform', 'Web berbasis React.js/PHP Laravel. Single-channel (Luring)', 'POS terintegrasi berbasis cloud', 'Web PHP + MySQL. Omnichannel mandiri (POS + E-Commerce)'),
        ('Sinkronisasi', 'Tidak ada — Stand-alone', 'Real-time, namun ritel umum', 'Real-time, basis data tunggal terpusat'),
        ('Konversi Satuan', 'Tidak ada (Multi-UOM)', 'Tidak dibahas', '✔ Dual-UOM otomatis (Box ↔ Strip)'),
        ('Logistik Medis', 'Tidak ada (tanpa filter kadaluwarsa)', 'Tidak dibahas', '✔ FEFO (Batch) + Serial Number (SN)'),
        ('Research Gap', 'Single-channel, tanpa FEFO & Multi-UOM', 'Tanpa karakteristik logistik medis', '✔ Solusi gap dari kedua penelitian'),
    ]
    col_widths = [2.80, 4.60, 4.00, 5.90]
    col_starts = [1.25, 4.15, 8.85, 12.95]
    header_top = 3.05
    row_height = 1.30

    for ci, (hdr, cw, cx) in enumerate(zip(headers, col_widths, col_starts)):
        bg = BLUE_DARK if ci == 0 else (ACCENT_TEAL if ci == 3 else RGBColor(0x00, 0x50, 0x88))
        bg = BLUE_DARK if ci < 3 else ACCENT_TEAL
        add_rect(s5, cx, header_top, cw, 0.80, fill_color=bg)
        add_textbox(s5, cx + 0.05, header_top + 0.05, cw - 0.10, 0.70,
                    text=hdr, size=15, bold=True, color=WHITE, align=PP_ALIGN.CENTER)

    for ri, row in enumerate(rows_data):
        top = header_top + 0.80 + ri * row_height
        alt_bg = RGBColor(0xE8, 0xF4, 0xFF) if ri % 2 == 0 else WHITE
        for ci, (cell, cw, cx) in enumerate(zip(row, col_widths, col_starts)):
            cell_bg = alt_bg
            if ci == 0:
                cell_bg = RGBColor(0xD1, 0xE8, 0xF7)
            add_rect(s5, cx, top, cw, row_height - 0.05, fill_color=cell_bg, line_color=BORDER_BLUE, line_width=0.5)
            add_textbox(s5, cx + 0.08, top + 0.08, cw - 0.16, row_height - 0.20,
                        text=cell, size=14, bold=(ci == 0), color=TEXT_DARK)

    # ================================================================
    # SLIDE 6: PEMBARUAN SISTEM (NOVELTY)
    # ================================================================
    s6 = new_content_slide('Pembaruan Sistem — Novelty')

    # Kiri: Silang merah
    add_rect(s6, 1.25, 3.00, 8.50, 0.65, fill_color=RED_ACCENT)
    add_textbox(s6, 1.40, 3.05, 8.20, 0.55,
                text='✗  Kendala Sistem Berjalan (As-Is)', size=22, bold=True, color=WHITE)

    problems = [
        'Risiko overselling akibat aplikasi kasir yang berjalan secara terisolasi (stand-alone).',
        'Tingginya beban kerja akibat konversi Multi-UOM yang dilakukan secara manual oleh admin.',
        'Penumpukan obat rusak akibat ketiadaan filter umur produk (FEFO) saat pengambilan di rak.',
    ]
    tb_l = slide.shapes.add_textbox if False else s6.shapes.add_textbox
    txBox = s6.shapes.add_textbox(emu(1.35), emu(3.70), emu(8.35), emu(6.20))
    tf = txBox.text_frame
    tf.word_wrap = True
    for i, prob in enumerate(problems):
        para = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        para.space_after = Pt(12)
        r = para.add_run()
        r.text = f'  •  {prob}'
        r.font.size = Pt(19)
        r.font.color.rgb = TEXT_DARK

    # Kanan: Centang hijau
    add_rect(s6, 10.15, 3.00, 8.60, 0.65, fill_color=GREEN_ACCENT)
    add_textbox(s6, 10.30, 3.05, 8.30, 0.55,
                text='✔  Sistem Usulan (To-Be)', size=22, bold=True, color=WHITE)

    solutions = [
        'Sinkronisasi basis data tunggal Omnichannel (POS & E-Commerce) yang mengunci stok seketika (Reserved) saat transaksi berlangsung.',
        'Algoritma pemotongan stok konversi Dual-UOM yang dinamis dan berjalan otomatis tanpa hitungan manual admin.',
        'Sistem Gatekeeper memaksa validasi FEFO pada setiap barang masuk: Nomor Batch + Tanggal Kedaluwarsa wajib diisi sebelum stok diproses.',
    ]
    txBox2 = s6.shapes.add_textbox(emu(10.25), emu(3.70), emu(8.35), emu(6.20))
    tf2 = txBox2.text_frame
    tf2.word_wrap = True
    for i, sol in enumerate(solutions):
        para = tf2.paragraphs[0] if i == 0 else tf2.add_paragraph()
        para.space_after = Pt(12)
        r = para.add_run()
        r.text = f'  •  {sol}'
        r.font.size = Pt(19)
        r.font.color.rgb = TEXT_DARK

    # Center arrow
    add_textbox(s6, 9.55, 5.80, 0.90, 0.80,
                text='→', size=40, bold=True, color=ACCENT_TEAL, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 7: SITEMAP ARSITEKTUR NAVIGASI
    # ================================================================
    s7 = new_content_slide('Sitemap — Arsitektur Navigasi')
    add_textbox(s7, 1.25, 3.00, 17.5, 0.60,
                text='Struktur Hierarki 3 Aktor:  Pelanggan (Front-End E-Commerce)  |  Admin (Operasional Toko & POS)  |  Superadmin (Laporan & Pengaturan)',
                size=18, bold=False, color=TEXT_DARK)
    # Placeholder boxes for 3 sitemaps
    sitemap_items = [
        ('👤  Pelanggan', 'Front-End\nE-Commerce', 'Autentikasi • Katalog Produk • Keranjang & Checkout • Riwayat Pesanan'),
        ('🧑‍💼  Admin', 'Operasional\nToko & Kasir', 'Dashboard • Master Data • POS (Kasir) • Pesanan Daring • Penerimaan Barang • Mutasi Stok • Kartu Stok'),
        ('👑  Superadmin', 'Manajerial\n& Pengawasan', 'Dashboard Analitik • Laporan Kuantitas & Logistik • Manajemen Pengguna • Pengaturan Sistem (API & Web)'),
    ]
    for i, (actor, zone, modules) in enumerate(sitemap_items):
        x = 1.25 + i * 6.10
        w = 5.85
        add_rect(s7, x, 3.75, w, 0.70, fill_color=BLUE_DARK)
        add_textbox(s7, x + 0.1, 3.80, w - 0.2, 0.60,
                    text=actor, size=20, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
        add_rect(s7, x, 4.50, w, 0.55, fill_color=ACCENT_TEAL)
        add_textbox(s7, x + 0.1, 4.52, w - 0.2, 0.50,
                    text=zone, size=17, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
        add_rect(s7, x, 5.10, w, 5.00, fill_color=LIGHT_BG, line_color=BORDER_BLUE, line_width=1)
        add_textbox(s7, x + 0.15, 5.20, w - 0.30, 4.80,
                    text=modules, size=17, color=TEXT_DARK)
    # Image placeholder note
    add_textbox(s7, 1.25, 9.95, 17.5, 0.60,
                text='[ Masukkan Gambar 3 Hierarki Sitemap di sini untuk memperkuat presentasi ]',
                size=14, bold=False, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 8: WORKFLOW BARANG KELUAR (OMNICHANNEL)
    # ================================================================
    s8 = new_content_slide('Workflow Barang Keluar — Omnichannel')
    add_textbox(s8, 1.25, 3.00, 17.5, 0.55,
                text='Otomatisasi Lintas Saluran: Sistem ZenCare bertindak sebagai jembatan tunggal yang memproses transaksi luring (POS) dan daring (E-Commerce) secara bersamaan.',
                size=18, color=TEXT_DARK)
    # Two columns
    add_rect(s8, 1.25, 3.70, 8.50, 0.60, fill_color=BLUE_DARK)
    add_textbox(s8, 1.35, 3.72, 8.30, 0.55,
                text='🏪  Kasir Fisik (POS)', size=20, bold=True, color=WHITE)
    pos_steps = [
        'Admin memindai label / input SKU produk.',
        'Sistem menampilkan instruksi "Ambil Fisik" (Batch FEFO / SN).',
        'Admin pilih metode bayar (Tunai / Non-Tunai).',
        'Sistem mengonversi satuan dan memotong stok secara permanen.',
        'Sistem mencatat mutasi keluar ke Kartu Stok → Admin cetak struk.',
    ]
    txb = s8.shapes.add_textbox(emu(1.35), emu(4.40), emu(8.30), emu(5.30))
    tf = txb.text_frame; tf.word_wrap = True
    for i, step in enumerate(pos_steps):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        r = p.add_run(); r.text = f'{i+1}. {step}'
        r.font.size = Pt(18); r.font.color.rgb = TEXT_DARK
        p.space_after = Pt(8)

    add_rect(s8, 10.25, 3.70, 8.55, 0.60, fill_color=ACCENT_TEAL)
    add_textbox(s8, 10.35, 3.72, 8.35, 0.55,
                text='🌐  E-Commerce (Daring)', size=20, bold=True, color=WHITE)
    ecom_steps = [
        'Pelanggan checkout → pilih Kurir atau Pick-up in Store.',
        'Sistem meneruskan ke Payment Gateway untuk pembayaran.',
        'Sistem mengalokasikan stok (FEFO untuk Obat / SN untuk Alkes).',
        'Stok dikunci sementara (status: Reserved).',
        'Status diubah "Selesai" → stok dipotong permanen & masuk Kartu Stok.',
    ]
    txb2 = s8.shapes.add_textbox(emu(10.35), emu(4.40), emu(8.30), emu(5.30))
    tf2 = txb2.text_frame; tf2.word_wrap = True
    for i, step in enumerate(ecom_steps):
        p = tf2.paragraphs[0] if i == 0 else tf2.add_paragraph()
        r = p.add_run(); r.text = f'{i+1}. {step}'
        r.font.size = Pt(18); r.font.color.rgb = TEXT_DARK
        p.space_after = Pt(8)

    add_textbox(s8, 1.25, 9.70, 17.5, 0.55,
                text='[ Masukkan Gambar Workflow Barang Keluar / Screenshot Wireframe POS di sini ]',
                size=14, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 9: WORKFLOW PENERIMAAN BARANG & LOGISTIK
    # ================================================================
    s9 = new_content_slide('Workflow Penerimaan Barang & Logistik')
    add_rect(s9, 1.25, 3.05, 17.5, 0.65, fill_color=ACCENT_TEAL)
    add_textbox(s9, 1.35, 3.07, 17.30, 0.60,
                text='⚙  Sistem Sebagai Gatekeeper Logistik: Validasi identitas wajib sebelum stok diizinkan masuk ke Kartu Stok.',
                size=20, bold=True, color=WHITE)

    gate_steps = [
        ('1', 'Admin input dokumen penerimaan:', 'Nomor Referensi (Nota), Kuantitas, Harga Beli.'),
        ('2', 'Percabangan kategori produk:', 'Obat → input Nomor Batch + Tanggal Kedaluwarsa (FEFO).'),
        ('3', '', 'Alat Kesehatan → input Serial Number (SN) setiap unit.'),
        ('4', 'Sistem validasi kelengkapan data:', 'Jika tidak lengkap → tolak & kembalikan form ke admin (looping).'),
        ('5', 'Jika data lolos validasi:', 'Sistem merekam ke Basis Data → tambah stok utama → catat Kartu Stok.'),
        ('6', 'Instruksi cetak label batch:', 'Admin cetak stiker label untuk ditempel pada fisik barang di rak.'),
    ]
    for i, (num, bold_part, body) in enumerate(gate_steps):
        y = 3.85 + i * 1.08
        add_rect(s9, 1.25, y, 0.65, 0.85, fill_color=BLUE_DARK)
        add_textbox(s9, 1.27, y + 0.12, 0.62, 0.60,
                    text=num, size=26, bold=True, color=WHITE, align=PP_ALIGN.CENTER)
        txb = s9.shapes.add_textbox(emu(2.05), emu(y + 0.05), emu(16.40), emu(0.80))
        tf = txb.text_frame; tf.word_wrap = True
        p = tf.paragraphs[0]
        if bold_part:
            r1 = p.add_run(); r1.text = bold_part + '  '; r1.font.bold = True; r1.font.size = Pt(19); r1.font.color.rgb = BLUE_DARK
        r2 = p.add_run(); r2.text = body; r2.font.size = Pt(19); r2.font.color.rgb = TEXT_DARK

    add_textbox(s9, 1.25, 10.35, 17.5, 0.55,
                text='[ Masukkan Gambar Workflow Barang Masuk / Screenshot Wireframe Form Penerimaan di sini ]',
                size=14, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 10: ACTIVITY DIAGRAM (VALIDASI SISTEM)
    # ================================================================
    s10 = new_content_slide('Activity Diagram — Validasi Sistem')
    add_textbox(s10, 1.25, 3.00, 17.5, 0.60,
                text='Pemodelan Logika Pengamanan Data: Alur penerimaan barang menonjolkan percabangan if/else yang memaksa validasi identitas logistik sebelum data diproses.',
                size=18, color=TEXT_DARK)

    # Two column: Obat vs Alkes
    for ci, (label, steps) in enumerate([
        ('💊  Alur: Obat (FEFO Batch)',
         ['Admin buka modul Penerimaan Barang.',
          'Input: No. Referensi, Kuantitas, Harga Beli.',
          'Wajib input: Nomor Batch + Tanggal Kedaluwarsa.',
          'Sistem validasi: data lengkap?',
          '✗ Tidak → tampilkan peringatan, looping ke form.',
          '✔ Ya → simpan ke stok_batch (parameter ORDER BY tgl_exp ASC).',
          'Tambah stok_toko, catat kartu_stok, instruksi cetak label.']),
        ('🩺  Alur: Alat Kesehatan (Serial Number)',
         ['Admin buka modul Penerimaan Barang.',
          'Input: No. Referensi, Kuantitas, Harga Beli.',
          'Wajib input: Serial Number (SN) per unit.',
          'Sistem validasi: SN sudah ada (duplikat)?',
          '✗ Duplikat → tolak, looping ke form.',
          '✔ Unik → simpan ke unit_serial (status: Tersedia).',
          'Tambah stok_toko, catat kartu_stok, instruksi cetak label.']),
    ]):
        x = 1.25 + ci * 9.10
        add_rect(s10, x, 3.75, 8.75, 0.60, fill_color=BLUE_DARK if ci == 0 else ACCENT_TEAL)
        add_textbox(s10, x + 0.1, 3.77, 8.55, 0.55, text=label, size=20, bold=True, color=WHITE)
        txb = s10.shapes.add_textbox(emu(x + 0.15), emu(4.45), emu(8.45), emu(5.60))
        tf = txb.text_frame; tf.word_wrap = True
        for i, step in enumerate(steps):
            p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
            r = p.add_run(); r.text = step; r.font.size = Pt(18); r.font.color.rgb = TEXT_DARK
            p.space_after = Pt(6)

    add_textbox(s10, 1.25, 10.15, 17.5, 0.55,
                text='[ Masukkan Gambar Activity Diagram Penerimaan Barang di sini ]',
                size=14, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 11: LOGICAL DATABASE (ERD)
    # ================================================================
    s11 = new_content_slide('Logical Database — Entity Relationship Diagram (ERD)')
    add_textbox(s11, 1.25, 3.00, 17.5, 0.55,
                text='Arsitektur Relasional 13 Tabel | Pusat relasi: produk_variasi (FK utama: id_variasi)',
                size=18, bold=False, color=TEXT_DARK)

    clusters = [
        ('🔒 Klaster Aktor & RBAC', 'users\n→ role: ENUM (superadmin, admin, pelanggan)\n→ Basis kontrol hak akses seluruh sistem'),
        ('🔄 Klaster Konversi\nMulti-UOM', 'produk_induk → produk_variasi\n→ field rasio_konversi: pengali Box ↔ Strip\n→ harga_jual_kecil & harga_jual_besar'),
        ('📦 Klaster Logistik\nMedis', 'stok_toko (agregat) + stok_batch (FEFO)\n+ unit_serial (Serial Number Alkes)\n→ ORDER BY tgl_exp ASC untuk FEFO'),
        ('📜 Klaster Transaksi\n& Audit Trail', 'penerimaan_stok + penjualan (header)\n+ detail_penjualan + kartu_stok\n→ catatan_logistik: riwayat Batch/SN terpotong'),
    ]
    col_w = 8.45
    for i, (title, body) in enumerate(clusters):
        x = 1.25 + (i % 2) * (col_w + 0.30)
        y = 3.65 + (i // 2) * 3.25
        add_rect(s11, x, y, col_w, 3.10, fill_color=LIGHT_BG, line_color=BORDER_BLUE, line_width=1.5)
        add_textbox(s11, x + 0.15, y + 0.10, col_w - 0.3, 0.65,
                    text=title, size=19, bold=True, color=BLUE_DARK)
        add_rect(s11, x + 0.15, y + 0.80, col_w - 0.30, 0.04, fill_color=ACCENT_TEAL)
        add_textbox(s11, x + 0.15, y + 0.90, col_w - 0.30, 2.05,
                    text=body, size=17, color=TEXT_DARK)

    add_textbox(s11, 1.25, 10.20, 17.5, 0.55,
                text='[ Masukkan Gambar ERD 13 Tabel di sini ]',
                size=14, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ================================================================
    # SLIDE 12: STRUKTUR TABEL ESENSIAL
    # ================================================================
    s12 = new_content_slide('Struktur Tabel Esensial')

    for ci, (title, color_h, rows) in enumerate([
        ('🔄  Tabel produk_variasi — Konversi Multi-UOM', BLUE_DARK,
         [('id_variasi', 'INT PK', 'ID unik variasi produk'),
          ('id_produk_induk', 'INT FK', 'Relasi ke katalog induk'),
          ('satuan_kecil / besar', 'VARCHAR', 'Label eceran (Strip) & grosir (Box)'),
          ('rasio_konversi', 'SMALLINT', '★ Pengali konversi Box → Strip otomatis'),
          ('harga_jual_kecil', 'DECIMAL', 'Harga per satuan eceran (POS)'),
          ('harga_jual_besar', 'DECIMAL', 'Harga per satuan grosir (E-Commerce)'),
          ('tampil_di_online', 'BOOLEAN', 'Izin penayangan di etalase e-commerce')]),
        ('💊  Tabel stok_batch — Logika FEFO Obat', ACCENT_TEAL,
         [('id_stok_batch', 'INT PK', 'ID data batch'),
          ('id_variasi', 'INT FK', 'Relasi ke produk_variasi'),
          ('no_batch', 'VARCHAR', 'Nomor identitas gelombang produksi'),
          ('tgl_exp', 'DATE', '★ Parameter FEFO → ORDER BY tgl_exp ASC'),
          ('stok_sisa', 'INT', 'Sisa kuantitas pada gelombang ini'),
          ('', '', ''),
          ('', '', '')]),
    ]):
        x = 1.25 + ci * 9.10
        w = 8.75
        add_rect(s12, x, 3.05, w, 0.65, fill_color=color_h)
        add_textbox(s12, x + 0.1, 3.07, w - 0.2, 0.60, text=title, size=18, bold=True, color=WHITE)
        # Column headers
        col_xs = [x + 0.05, x + 2.10, x + 3.90]
        col_ws = [1.90, 1.70, 4.70]
        col_labels = ['Field', 'Tipe Data', 'Keterangan']
        for cj, (cx2, cw2, cl) in enumerate(zip(col_xs, col_ws, col_labels)):
            add_rect(s12, cx2, 3.75, cw2, 0.45, fill_color=RGBColor(0xCB, 0xE0, 0xF0))
            add_textbox(s12, cx2 + 0.05, 3.77, cw2 - 0.10, 0.40, text=cl, size=14, bold=True, color=BLUE_DARK)
        for ri, (f, t, k) in enumerate(rows):
            ry = 4.25 + ri * 0.85
            row_bg = LIGHT_BG if ri % 2 == 0 else WHITE
            star = '★ ' in k
            for cj, (cx2, cw2, cell) in enumerate(zip(col_xs, col_ws, [f, t, k])):
                add_rect(s12, cx2, ry, cw2, 0.80, fill_color=RGBColor(0xFF, 0xF9, 0xC4) if star else row_bg, line_color=RGBColor(0xCC, 0xCC, 0xCC), line_width=0.5)
                add_textbox(s12, cx2 + 0.05, ry + 0.05, cw2 - 0.10, 0.70, text=cell, size=13, bold=star and cj == 0, color=TEXT_DARK)

    # ================================================================
    # SLIDE 13: DOKUMENTASI LAPANGAN
    # ================================================================
    s13 = new_content_slide('Dokumentasi Lapangan — Observasi ZenCare Medical')
    add_rect(s13, 1.25, 3.05, 17.5, 0.65, fill_color=ACCENT_TEAL)
    add_textbox(s13, 1.35, 3.07, 17.30, 0.60,
                text='Validasi Observasi: Seluruh perancangan sistem dibangun berdasarkan fakta operasional lapangan.',
                size=20, bold=True, color=WHITE)
    # Photo placeholders
    for i, label in enumerate([
        '[ Foto Observasi 1\nAktivitas Kasir / Rak Apotek ]',
        '[ Foto Observasi 2\nProses Penerimaan Barang Saat Ini ]',
        '[ Foto Bersama Pemilik\nZenCare Medical ]',
    ]):
        x = 1.25 + i * 5.95
        add_rect(s13, x, 3.90, 5.55, 5.30, fill_color=RGBColor(0xE2, 0xEC, 0xF5), line_color=BLUE_DARK, line_width=1.5)
        add_textbox(s13, x + 0.2, 3.90 + 1.80, 5.15, 1.70,
                    text=label, size=16, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    findings = [
        '✔  Wawancara langsung dengan manajemen ZenCare Medical mengonfirmasi risiko overselling akibat sistem yang tidak terhubung.',
        '✔  Observasi kasir membuktikan bahwa konversi Multi-UOM dilakukan secara manual dan rawan kesalahan human error.',
        '✔  Tidak ditemukan pencatatan Nomor Batch maupun tanggal kedaluwarsa pada saat penerimaan barang dari pemasok.',
    ]
    txb = s13.shapes.add_textbox(emu(1.25), emu(9.35), emu(17.5), emu(1.80))
    tf = txb.text_frame; tf.word_wrap = True
    for i, f in enumerate(findings):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        r = p.add_run(); r.text = f; r.font.size = Pt(17); r.font.color.rgb = TEXT_DARK
        p.space_after = Pt(4)

    # ================================================================
    # SLIDE 14: KESIMPULAN
    # ================================================================
    s14 = new_content_slide('Kesimpulan')
    add_rect(s14, 1.25, 3.05, 17.5, 6.80, fill_color=LIGHT_BG, line_color=BORDER_BLUE, line_width=2)
    add_textbox(s14, 1.65, 3.40, 16.70, 0.65,
                text='📌  Simpulan Penelitian', size=24, bold=True, color=BLUE_DARK)
    add_rect(s14, 1.65, 4.10, 16.70, 0.05, fill_color=ACCENT_TEAL)

    kesimpulan = (
        'Melalui penelitian ini, dirancang sebuah sistem informasi inventori omnichannel berbasis web yang bertujuan '
        'untuk menjembatani kesenjangan antara transaksi kasir fisik (POS) dan penjualan daring di ZenCare Medical. '
        'Dengan menerapkan metode Rapid Application Development (RAD), sistem ini diharapkan menjadi solusi atas '
        'permasalahan overselling dan inefisiensi konversi Multi-UOM yang selama ini dilakukan secara manual.\n\n'
        'Ke depannya, implementasi basis data terpusat dan fitur Gatekeeper berlogika First Expired First Out (FEFO) '
        'pada sistem ini diproyeksikan mampu menciptakan tata kelola logistik medis yang jauh lebih akurat, aman, dan transparan.'
    )
    add_textbox(s14, 1.65, 4.30, 16.70, 5.30,
                text=kesimpulan, size=20, color=TEXT_DARK, align=PP_ALIGN.LEFT)

    luaran = '💡  Luaran:  (1) Sistem web omnichannel terintegrasi  |  (2) Otomatisasi Dual-UOM  |  (3) Gatekeeper FEFO & Serial Number  |  (4) Dokumentasi & Laporan Pengujian'
    add_textbox(s14, 1.25, 9.90, 17.5, 0.70,
                text=luaran, size=17, bold=True, color=ACCENT_TEAL)

    # ================================================================
    # SLIDE 15: PENUTUP
    # ================================================================
    # Use closing slide template (index 6 of original = slide 7 with "Terima Kasih")
    close_template = content_template  # reuse content template for decoration
    s15 = prs.slides.add_slide(blank_layout)
    for shape in content_template.shapes:
        if 'Freeform' in shape.name or shape.name in ('Group 2',):
            sp = copy.deepcopy(shape.element)
            s15.shapes._spTree.append(sp)

    add_textbox(s15, 14.0, 0.59, 5.80, 0.47,
                text='SEMINAR PROPOSAL', size=20, bold=True, color=BADGE_GRAY, align=PP_ALIGN.RIGHT)

    # Main closing text centered
    add_textbox(s15, 1.25, 3.00, 17.5, 2.50,
                text='TERIMA KASIH', size=72, bold=True, color=BLUE_DARK, align=PP_ALIGN.CENTER)
    add_rect(s15, 5.0, 5.60, 10.0, 0.08, fill_color=ACCENT_TEAL)
    add_textbox(s15, 1.25, 5.80, 17.5, 0.80,
                text='Sesi Tanya Jawab (Q&A)', size=32, bold=False, color=TEXT_DARK, align=PP_ALIGN.CENTER)

    add_textbox(s15, 1.25, 6.80, 17.5, 0.60,
                text='Marcell Chandra Kenchana  •  322310015  •  Program Studi Sistem Informasi',
                size=20, bold=False, color=BADGE_GRAY, align=PP_ALIGN.CENTER)
    add_textbox(s15, 1.25, 7.50, 17.5, 0.55,
                text='Universitas Ma Chung  •  Malang, 2026',
                size=18, bold=False, color=BADGE_GRAY, align=PP_ALIGN.CENTER)
    add_textbox(s15, 1.25, 8.20, 17.5, 0.55,
                text='Dosen Pembimbing: Dr. Soetam Rizky Wicaksono, S.Kom., MM., MCP., MCTS., MOSM.',
                size=17, bold=False, color=TEXT_DARK, align=PP_ALIGN.CENTER)
    add_textbox(s15, 1.25, 9.00, 17.5, 0.55,
                text='[ Masukkan Logo Universitas Ma Chung di sini ]',
                size=14, bold=False, color=BADGE_GRAY, align=PP_ALIGN.CENTER)

    # ================================================================
    # SAVE
    # ================================================================
    out_path = r'c:\xampp\htdocs\inventory_zencare\lain\File PPT\Presentasi_Sempro_Marcell.pptx'
    prs.save(out_path)
    print(f'SUCCESS! Saved to: {out_path}')
    print(f'Total slides: {len(prs.slides)}')


if __name__ == '__main__':
    build_presentation()
