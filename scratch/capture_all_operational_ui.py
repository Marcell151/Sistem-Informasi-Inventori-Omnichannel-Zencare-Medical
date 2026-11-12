import os
import subprocess
import time
import tempfile
import shutil

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
os.makedirs(out_dir, exist_ok=True)
cache_dir = os.path.abspath(r'scratch\cache')
os.makedirs(cache_dir, exist_ok=True)

# List of operational screenshots to capture:
# (key, role, target_rel, query, is_print_override, modal_id_to_show, width, height, label)
items = [
    # 1. Dokumen Transaksi & Cetak Fisik
    (
        '26_Cetak_Struk_Kasir', 'kasir', 'pos/cetak_invoice.php',
        'no_invoice=POS-20261101-2875&format=struk', False, None,
        800, 950, 'Struk Kasir POS (Thermal Slip)'
    ),
    (
        '27_Cetak_Invoice_Penjualan', 'admin', 'pos/cetak_invoice.php',
        'no_invoice=POS-20261101-2875&format=a4', False, None,
        1200, 1050, 'Invoice Penjualan Resmi (A4)'
    ),
    (
        '28_Cetak_Label_Pengiriman', 'admin', 'admin/cetak_label_pengiriman.php',
        'no_invoice=WEB-20261101-726', False, None,
        800, 950, 'Label Pengiriman Kurir E-Commerce'
    ),
    (
        '29_Cetak_Nota_Penerimaan', 'admin', 'inventori/cetak_nota_penerimaan.php',
        'id=6', False, None,
        1200, 1000, 'Nota Penerimaan Barang Masuk (A4)'
    ),

    # 2. Stok Opname Multi-Tahap
    (
        '30_Stok_Opname_Mulai_Sesi', 'superadmin', 'inventori/stok_opname.php',
        '', False, 'modal_mulai_sesi',
        1280, 850, 'Superadmin Memulai Sesi & Pembekuan Sistem'
    ),
    (
        '31_Stok_Opname_Blind_Count_Kasir', 'kasir', 'inventori/stok_opname.php',
        '', False, None,
        1440, 900, 'Kasir Pencacahan Fisik Rak secara Blind Count'
    ),
    (
        '32_Stok_Opname_Verifikasi_Admin', 'admin', 'inventori/stok_opname.php',
        '', False, None,
        1440, 900, 'Admin Verifikasi & Rekonsiliasi Selisih Fisik'
    ),
    (
        '33_Cetak_Berita_Acara_Opname', 'admin', 'inventori/cetak_berita_acara_opname.php',
        'id_sesi=7', False, None,
        1440, 900, 'Berita Acara Stok Opname Resmi (A4 Landscape)'
    ),

    # 3. Form Interaktif Master & Inventori
    (
        '34_Form_Input_Master_Produk', 'admin', 'admin/master_produk.php',
        '', False, 'modal_tambah_induk',
        1280, 850, 'Form Input Master Produk Baru'
    ),
    (
        '35_Form_Konversi_UOM', 'admin', 'inventori/konversi_uom.php',
        '', False, None,
        1440, 900, 'Form Konversi Satuan Multi-UOM'
    ),
    (
        '36_Form_Mutasi_Stok', 'admin', 'inventori/proses_mutasi.php',
        '', False, None,
        1440, 900, 'Form Mutasi Stok Manual'
    ),

    # 4. Lembar Cetak Laporan Resmi (7 Laporan)
    (
        '37_Cetak_Laporan_Penjualan', 'superadmin', 'laporan/penjualan.php',
        '', True, None,
        1200, 950, 'Lembar Cetak Laporan Penjualan Barang'
    ),
    (
        '38_Cetak_Laporan_Persediaan', 'superadmin', 'laporan/ketersediaan_stok.php',
        '', True, None,
        1200, 950, 'Lembar Cetak Laporan Persediaan'
    ),
    (
        '39_Cetak_Laporan_Kartu_Stok', 'superadmin', 'laporan/kartu_stok.php',
        'id_variasi=1', True, None,
        1200, 950, 'Lembar Cetak Laporan Kartu Riwayat Mutasi Stok'
    ),
    (
        '40_Cetak_Laporan_Logistik_Medis', 'superadmin', 'laporan/logistik_medis.php',
        '', True, None,
        1200, 950, 'Lembar Cetak Laporan Logistik Medis FEFO'
    ),
    (
        '41_Cetak_Laporan_Penerimaan', 'superadmin', 'laporan/penerimaan_barang.php',
        '', True, None,
        1200, 950, 'Lembar Cetak Laporan Penerimaan Barang Masuk'
    ),
    (
        '42_Cetak_Laporan_Mutasi_Konversi', 'superadmin', 'laporan/mutasi_konversi.php',
        '', True, None,
        1200, 950, 'Lembar Cetak Laporan Mutasi & Konversi'
    ),
    (
        '43_Cetak_Laporan_Anomali_FEFO', 'superadmin', 'laporan/anomali_fefo.php',
        '', True, None,
        1200, 950, 'Lembar Cetak Laporan Deteksi Anomali FEFO'
    ),
]

print(f"Starting capture for {len(items)} items...")

for key, role, target_rel, query, is_print_override, modal_id, width, height, label in items:
    html_name = f"{key}.html"
    png_name = f"{key}.png"
    png_path = os.path.join(out_dir, png_name)
    html_path = os.path.join(cache_dir, html_name)

    # 1. Render PHP
    render_cmd = [php_exe, 'scratch/render_page.php', role, target_rel, html_name, query]
    r_res = subprocess.run(render_cmd, capture_output=True, text=True)
    if not os.path.exists(html_path):
        print(f"[ERR] Failed to render {html_name}: {r_res.stderr}")
        continue

    # 2. Modify HTML if needed (print override or modal display)
    with open(html_path, 'r', encoding='utf-8', errors='ignore') as f:
        html_content = f.read()

    modified = False
    if is_print_override:
        print_style = """
<style>
@media screen {
    .print\\:hidden { display: none !important; }
    .hidden.print\\:block, div.hidden.print\\:block, table.hidden.print\\:table { display: block !important; }
    .hidden.print\\:flex, div.hidden.print\\:flex { display: flex !important; }
    #tab_content_besar, #tab_content_kecil { display: block !important; }
    body { background: white !important; margin: 0; padding: 24px; font-family: 'Plus Jakarta Sans', Arial, sans-serif !important; }
}
</style>
"""
        if '</head>' in html_content:
            html_content = html_content.replace('</head>', print_style + '</head>', 1)
            modified = True

    if modal_id:
        modal_style = f"""
<style>
#{modal_id} {{ display: flex !important; }}
</style>
"""
        if '</head>' in html_content:
            html_content = html_content.replace('</head>', modal_style + '</head>', 1)
            modified = True

    if modified:
        with open(html_path, 'w', encoding='utf-8') as f:
            f.write(html_content)

    # 3. Capture with Headless Chrome
    temp_dir = tempfile.mkdtemp()
    cached_url = f"http://localhost/inventory_zencare/scratch/cache/{html_name}"
    chrome_cmd = [
        chrome_exe,
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        f'--user-data-dir={temp_dir}',
        f'--screenshot={png_path}',
        f'--window-size={width},{height}',
        cached_url
    ]

    t0 = time.time()
    try:
        c_res = subprocess.run(chrome_cmd, capture_output=True, text=True, timeout=15)
        elapsed = round(time.time() - t0, 2)
        if os.path.exists(png_path) and os.path.getsize(png_path) > 1000:
            sz = round(os.path.getsize(png_path) / 1024, 1)
            print(f"[OK]   {key:32} -> {png_name:32} ({sz} KB in {elapsed}s) - {label}")
        else:
            print(f"[ERR]  {key:32} -> FAILED to save PNG")
    except subprocess.TimeoutExpired:
        print(f"[TIME] {key:32} -> TIMED OUT")
    finally:
        shutil.rmtree(temp_dir, ignore_errors=True)
        time.sleep(0.3)

print("\nAll operational captures finished successfully!")

