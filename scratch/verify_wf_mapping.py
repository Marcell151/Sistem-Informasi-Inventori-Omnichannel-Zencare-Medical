import os

base_dir = os.path.abspath('laporan_ta')

wf_mapping = [
    # A. Login Staf
    ('Gambar 3.19', 'Wireframe Antarmuka Login Staf', 'Login (Staf).png'),
    # B. Admin
    ('Gambar 3.20', 'Wireframe Antarmuka Dashboard Admin', 'Dashboard (Admin).png'),
    ('Gambar 3.21', 'Wireframe Antarmuka Terminal Kasir (Point of Sale / POS)', 'POS.png'),
    ('Gambar 3.22', 'Wireframe Antarmuka Kelola Transaksi Omnichannel', 'Transaksi.png'),
    ('Gambar 3.23', 'Wireframe Antarmuka Master Produk', 'Produk.png'),
    ('Gambar 3.24', 'Wireframe Antarmuka Satuan & Konversi Multi-UOM', 'Satuan & UOM.png'),
    ('Gambar 3.25', 'Wireframe Antarmuka Direktori Supplier', 'Supplier.png'),
    ('Gambar 3.26', 'Wireframe Antarmuka Penerimaan Barang Masuk', 'Penerimaan Barang.png'),
    ('Gambar 3.27', 'Wireframe Antarmuka Mutasi Stok Manual', 'Mutasi Stok.png'),
    ('Gambar 3.28', 'Wireframe Antarmuka Kartu Stok (Buku Besar Mutasi)', 'Kartu Stok.png'),
    ('Gambar 3.29', 'Wireframe Antarmuka Stok Opname & Audit Fisik Persediaan', 'Stok Opname.png'),
    ('Gambar 3.30', 'Wireframe Antarmuka Konversi Satuan & Riwayat Pecah Dus', 'Konversi Satuan.png'),
    # C. Superadmin
    ('Gambar 3.31', 'Wireframe Antarmuka Dashboard Superadmin', 'Dashboard (Superadmin).png'),
    ('Gambar 3.32', 'Wireframe Antarmuka Laporan Penjualan Barang', 'Laporan Penjualan Barang.png'),
    ('Gambar 3.33', 'Wireframe Antarmuka Laporan Persediaan', 'Laporan Persediaan.png'),
    ('Gambar 3.34', 'Wireframe Antarmuka Laporan Logistik Medis (FEFO & Serial Number)', 'Laporan Logistik Medis.png'),
    ('Gambar 3.35', 'Wireframe Antarmuka Laporan Audit Anomali FEFO', 'Laporan Anomali FEFO.png'),
    ('Gambar 3.36', 'Wireframe Antarmuka Manajemen Pengguna', 'Manajemen Pengguna.png'),
    ('Gambar 3.37', 'Wireframe Antarmuka Pengaturan Web & Konten Toko', 'Pengaturan Web.png'),
    # D. Pelanggan
    ('Gambar 3.38', 'Wireframe Antarmuka Login Pelanggan', 'Login (Pelanggan).png'),
    ('Gambar 3.39', 'Wireframe Antarmuka Akun & Profil Pelanggan', 'Profil.png'),
    ('Gambar 3.40', 'Wireframe Antarmuka Beranda E-Commerce', 'E-Commerce.png'),
    ('Gambar 3.41', 'Wireframe Antarmuka Detail Produk', 'Detail Produk.png'),
    ('Gambar 3.42', 'Wireframe Antarmuka Keranjang Belanja', 'Keranjang Belanja.png'),
    ('Gambar 3.43', 'Wireframe Antarmuka Checkout Pembayaran', 'Checkout Pembayaran.png'),
]

print(f"Total wireframes mapped: {len(wf_mapping)}")
all_exist = True
for fig_no, caption, fname in wf_mapping:
    rel_path = f'../lain/File PPT/Wireframe/{fname}'
    abs_path = os.path.abspath(os.path.join(base_dir, rel_path))
    exists = os.path.exists(abs_path)
    if not exists:
        all_exist = False
        print(f"  [MISSING] {fig_no}: {fname}")
    else:
        print(f"  [OK] {fig_no}: {caption:65} -> {fname}")

if all_exist:
    print("\n>> ALL 25 WIREFRAME IMAGES EXIST AND RESOLVE CORRECTLY!")

