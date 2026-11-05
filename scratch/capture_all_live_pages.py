import os
import subprocess
import time

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')

# List of 25 pages to capture
pages_to_capture = [
    # A. Autentikasi Staf
    ('01_Login_Staf.png', 'http://localhost/inventory_zencare/login.php', 'Gambar 3.19', 'Login Staf'),
    
    # B. Admin
    ('02_Dashboard_Admin.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=index.php', 'Gambar 3.20', 'Dashboard Admin'),
    ('03_POS_Kasir.png', 'http://localhost/inventory_zencare/view_capture.php?role=kasir&page=pos/pos.php', 'Gambar 3.21', 'Terminal Kasir (POS)'),
    ('04_Transaksi_Omnichannel.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=admin/pesanan.php', 'Gambar 3.22', 'Transaksi Omnichannel'),
    ('05_Master_Produk.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=admin/master_produk.php', 'Gambar 3.23', 'Master Produk'),
    ('06_Satuan_UOM.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=admin/master_uom.php', 'Gambar 3.24', 'Satuan & UOM'),
    ('07_Direktori_Supplier.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=admin/master_supplier.php', 'Gambar 3.25', 'Direktori Supplier'),
    ('08_Penerimaan_Barang.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=inventori/tambah_stok.php', 'Gambar 3.26', 'Penerimaan Barang'),
    ('09_Mutasi_Stok.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=inventori/proses_mutasi.php', 'Gambar 3.27', 'Mutasi Stok Manual'),
    ('10_Kartu_Stok.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=laporan/kartu_stok.php', 'Gambar 3.28', 'Kartu Stok'),
    ('11_Stok_Opname.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=inventori/stok_opname.php', 'Gambar 3.29', 'Stok Opname & Audit Fisik'),
    ('12_Konversi_Satuan.png', 'http://localhost/inventory_zencare/view_capture.php?role=admin&page=inventori/konversi_uom.php', 'Gambar 3.30', 'Konversi Satuan & Pecah Dus'),
    
    # C. Superadmin
    ('13_Dashboard_Superadmin.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=index.php', 'Gambar 3.31', 'Dashboard Superadmin'),
    ('14_Laporan_Penjualan.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=laporan/penjualan.php', 'Gambar 3.32', 'Laporan Penjualan Barang'),
    ('15_Laporan_Persediaan.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=laporan/ketersediaan_stok.php', 'Gambar 3.33', 'Laporan Persediaan'),
    ('16_Laporan_Logistik_Medis.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=laporan/logistik_medis.php', 'Gambar 3.34', 'Laporan Logistik Medis'),
    ('17_Laporan_Anomali_FEFO.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=laporan/anomali_fefo.php', 'Gambar 3.35', 'Laporan Audit Anomali FEFO'),
    ('18_Manajemen_Pengguna.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=admin/master_user.php', 'Gambar 3.36', 'Manajemen Pengguna'),
    ('19_Pengaturan_Web.png', 'http://localhost/inventory_zencare/view_capture.php?role=superadmin&page=admin/pengaturan_web.php', 'Gambar 3.37', 'Pengaturan Web & Konten Toko'),
    
    # D. Pelanggan
    ('20_Login_Pelanggan.png', 'http://localhost/inventory_zencare/login_customer.php', 'Gambar 3.38', 'Login Pelanggan'),
    ('21_Profil_Pelanggan.png', 'http://localhost/inventory_zencare/view_capture.php?role=pelanggan&page=ecommerce/profil.php', 'Gambar 3.39', 'Akun & Profil Pelanggan'),
    ('22_Beranda_Ecommerce.png', 'http://localhost/inventory_zencare/ecommerce/index.php', 'Gambar 3.40', 'Beranda E-Commerce'),
    ('23_Detail_Produk.png', 'http://localhost/inventory_zencare/ecommerce/produk.php?id=1', 'Gambar 3.41', 'Detail Produk E-Commerce'),
    ('24_Keranjang_Belanja.png', 'http://localhost/inventory_zencare/ecommerce/cart.php', 'Gambar 3.42', 'Keranjang Belanja'),
    ('25_Checkout_Pembayaran.png', 'http://localhost/inventory_zencare/ecommerce/zencare_checkout.php', 'Gambar 3.43', 'Checkout Pembayaran')
]

print(f"Starting capture of {len(pages_to_capture)} live UI pages...")
success_count = 0

for filename, url, fig, name in pages_to_capture:
    out_file = os.path.join(out_dir, filename)
    cmd = [
        chrome,
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        f'--screenshot={out_file}',
        '--window-size=1440,900',
        url
    ]
    res = subprocess.run(cmd, capture_output=True, text=True)
    if os.path.exists(out_file) and os.path.getsize(out_file) > 1000:
        sz = os.path.getsize(out_file)
        print(f"[OK] {fig} | {name:35} -> {filename} ({sz} bytes)")
        success_count += 1
    else:
        print(f"[FAIL] {fig} | {name:35} -> Failed to capture ({url})")

print(f"\nCapture complete! Successfully captured {success_count} / {len(pages_to_capture)} pages.")

