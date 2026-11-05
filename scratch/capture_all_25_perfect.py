import os
import subprocess
import time
import tempfile
import shutil

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
os.makedirs(out_dir, exist_ok=True)

pages = [
    # A. Autentikasi Staf
    ('01_Login_Staf', 'guest', 'login.php', '', 'Gambar 3.19', 'Login Staf'),
    
    # B. Admin
    ('02_Dashboard_Admin', 'admin', 'index.php', '', 'Gambar 3.20', 'Dashboard Admin'),
    ('03_POS_Kasir', 'kasir', 'pos/pos.php', '', 'Gambar 3.21', 'Terminal Kasir (POS)'),
    ('04_Transaksi_Omnichannel', 'admin', 'admin/pesanan.php', '', 'Gambar 3.22', 'Transaksi Omnichannel'),
    ('05_Master_Produk', 'admin', 'admin/master_produk.php', '', 'Gambar 3.23', 'Master Produk'),
    ('06_Satuan_UOM', 'admin', 'admin/master_uom.php', '', 'Gambar 3.24', 'Satuan & UOM'),
    ('07_Direktori_Supplier', 'admin', 'admin/master_supplier.php', '', 'Gambar 3.25', 'Direktori Supplier'),
    ('08_Penerimaan_Barang', 'admin', 'inventori/tambah_stok.php', '', 'Gambar 3.26', 'Penerimaan Barang'),
    ('09_Mutasi_Stok', 'admin', 'inventori/proses_mutasi.php', '', 'Gambar 3.27', 'Mutasi Stok Manual'),
    ('10_Kartu_Stok', 'admin', 'laporan/kartu_stok.php', '', 'Gambar 3.28', 'Kartu Stok'),
    ('11_Stok_Opname', 'admin', 'inventori/stok_opname.php', '', 'Gambar 3.29', 'Stok Opname & Audit Fisik'),
    ('12_Konversi_Satuan', 'admin', 'inventori/konversi_uom.php', '', 'Gambar 3.30', 'Konversi Satuan & Pecah Dus'),
    
    # C. Superadmin
    ('13_Dashboard_Superadmin', 'superadmin', 'index.php', '', 'Gambar 3.31', 'Dashboard Superadmin'),
    ('14_Laporan_Penjualan', 'superadmin', 'laporan/penjualan.php', '', 'Gambar 3.32', 'Laporan Penjualan Barang'),
    ('15_Laporan_Persediaan', 'superadmin', 'laporan/ketersediaan_stok.php', '', 'Gambar 3.33', 'Laporan Persediaan'),
    ('16_Laporan_Logistik_Medis', 'superadmin', 'laporan/logistik_medis.php', '', 'Gambar 3.34', 'Laporan Logistik Medis'),
    ('17_Laporan_Anomali_FEFO', 'superadmin', 'laporan/anomali_fefo.php', '', 'Gambar 3.35', 'Laporan Audit Anomali FEFO'),
    ('18_Manajemen_Pengguna', 'superadmin', 'admin/master_user.php', '', 'Gambar 3.36', 'Manajemen Pengguna'),
    ('19_Pengaturan_Web', 'superadmin', 'admin/pengaturan_web.php', '', 'Gambar 3.37', 'Pengaturan Web & Konten Toko'),
    
    # D. Pelanggan
    ('20_Login_Pelanggan', 'guest', 'login_customer.php', '', 'Gambar 3.38', 'Login Pelanggan'),
    ('21_Profil_Pelanggan', 'pelanggan', 'ecommerce/profil.php', '', 'Gambar 3.39', 'Akun & Profil Pelanggan'),
    ('22_Beranda_Ecommerce', 'pelanggan', 'ecommerce/index.php', '', 'Gambar 3.40', 'Beranda E-Commerce'),
    ('23_Detail_Produk', 'pelanggan', 'ecommerce/produk.php', 'id=1', 'Gambar 3.41', 'Detail Produk E-Commerce'),
    ('24_Keranjang_Belanja', 'pelanggan', 'ecommerce/cart.php', '', 'Gambar 3.42', 'Keranjang Belanja'),
    ('25_Checkout_Pembayaran', 'pelanggan', 'ecommerce/zencare_checkout.php', '', 'Gambar 3.43', 'Checkout Pembayaran')
]

for key, role, target_rel, query, fig, label in pages:
    html_name = f"{key}.html"
    png_name = f"{key}.png"
    png_path = os.path.join(out_dir, png_name)
    
    # If already exists and size > 20KB, skip!
    if os.path.exists(png_path) and os.path.getsize(png_path) > 20000:
        sz = round(os.path.getsize(png_path) / 1024, 1)
        print(f"[SKIP] {fig:11} | {label:32} -> {png_name:28} (already exists: {sz} KB)")
        continue
    
    # Step 1: Render PHP to static HTML
    render_cmd = [php_exe, 'scratch/render_page.php', role, target_rel, html_name, query]
    r_res = subprocess.run(render_cmd, capture_output=True, text=True)
    
    # Step 2: Capture screenshot with Chrome using isolated temp user data dir
    temp_dir = tempfile.mkdtemp()
    cached_url = f"http://localhost/inventory_zencare/scratch/cache/{html_name}"
    chrome_cmd = [
        chrome_exe,
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        f'--user-data-dir={temp_dir}',
        f'--screenshot={png_path}',
        '--window-size=1440,900',
        cached_url
    ]
    
    t0 = time.time()
    try:
        c_res = subprocess.run(chrome_cmd, capture_output=True, text=True, timeout=15)
        elapsed = round(time.time() - t0, 2)
        if os.path.exists(png_path) and os.path.getsize(png_path) > 1000:
            sz = round(os.path.getsize(png_path) / 1024, 1)
            print(f"[OK]   {fig:11} | {label:32} -> {png_name:28} ({sz} KB in {elapsed}s)")
        else:
            print(f"[ERR]  {fig:11} | {label:32} -> FAILED to save PNG")
    except subprocess.TimeoutExpired:
        print(f"[TIME] {fig:11} | {label:32} -> TIMED OUT")
    finally:
        shutil.rmtree(temp_dir, ignore_errors=True)
        time.sleep(0.5)

print("\nProcess finished!")
