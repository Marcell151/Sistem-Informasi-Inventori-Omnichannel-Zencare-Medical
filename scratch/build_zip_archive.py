import zipfile
import os

images_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
zip_path_laporan = os.path.abspath(r'laporan_ta\Katalog_UI_UX_ZenCare_Medical.zip')
zip_path_lain = os.path.abspath(r'lain\Katalog_UI_UX_ZenCare_Medical.zip')

# Mapping of file to folder inside zip
mapping = [
    # 01_Autentikasi_Staf
    ("01_Login_Staf.png", "01_Autentikasi_Staf/Gambar_4.1_01_Login_Staf.png"),
    
    # 02_Kasir_dan_Transaksi
    ("02_Dashboard_Admin.png", "02_Kasir_dan_Transaksi/Gambar_4.2_02_Dashboard_Admin.png"),
    ("03_POS_Kasir.png", "02_Kasir_dan_Transaksi/Gambar_4.3_03_POS_Kasir.png"),
    ("26_Cetak_Struk_Kasir.png", "02_Kasir_dan_Transaksi/Gambar_4.4_26_Cetak_Struk_Kasir.png"),
    ("04_Transaksi_Omnichannel.png", "02_Kasir_dan_Transaksi/Gambar_4.5_04_Transaksi_Omnichannel.png"),
    ("27_Cetak_Invoice_Penjualan.png", "02_Kasir_dan_Transaksi/Gambar_4.6_27_Cetak_Invoice_Penjualan.png"),
    ("28_Cetak_Label_Pengiriman.png", "02_Kasir_dan_Transaksi/Gambar_4.7_28_Cetak_Label_Pengiriman.png"),

    # 03_Master_dan_Logistik
    ("05_Master_Produk.png", "03_Master_dan_Logistik/Gambar_4.8_05_Master_Produk.png"),
    ("34_Form_Input_Master_Produk.png", "03_Master_dan_Logistik/Gambar_4.9_34_Form_Input_Master_Produk.png"),
    ("06_Satuan_UOM.png", "03_Master_dan_Logistik/Gambar_4.10_06_Satuan_UOM.png"),
    ("35_Form_Konversi_UOM.png", "03_Master_dan_Logistik/Gambar_4.11_35_Form_Konversi_UOM.png"),
    ("12_Konversi_Satuan.png", "03_Master_dan_Logistik/Gambar_4.12_12_Konversi_Satuan.png"),
    ("07_Direktori_Supplier.png", "03_Master_dan_Logistik/Gambar_4.13_07_Direktori_Supplier.png"),
    ("08_Penerimaan_Barang.png", "03_Master_dan_Logistik/Gambar_4.14_08_Penerimaan_Barang.png"),
    ("29_Cetak_Nota_Penerimaan.png", "03_Master_dan_Logistik/Gambar_4.15_29_Cetak_Nota_Penerimaan.png"),
    ("09_Mutasi_Stok.png", "03_Master_dan_Logistik/Gambar_4.16_09_Mutasi_Stok.png"),
    ("36_Form_Mutasi_Stok.png", "03_Master_dan_Logistik/Gambar_4.17_36_Form_Mutasi_Stok.png"),
    ("10_Kartu_Stok.png", "03_Master_dan_Logistik/Gambar_4.18_10_Kartu_Stok.png"),
    ("39_Cetak_Laporan_Kartu_Stok.png", "03_Master_dan_Logistik/Gambar_4.19_39_Cetak_Laporan_Kartu_Stok.png"),

    # 04_Siklus_Stok_Opname
    ("11_Stok_Opname_Riwayat.png", "04_Siklus_Stok_Opname/Gambar_4.20_Riwayat_Sesi_Opname.png"),
    ("30_Stok_Opname_Mulai_Sesi.png", "04_Siklus_Stok_Opname/Gambar_4.21_Inisiasi_Freeze_Superadmin.png"),
    ("31_Stok_Opname_Blind_Count_Kasir.png", "04_Siklus_Stok_Opname/Gambar_4.22_Blind_Count_Kasir.png"),
    ("32_Stok_Opname_Verifikasi_Admin.png", "04_Siklus_Stok_Opname/Gambar_4.23_Verifikasi_Admin.png"),
    ("44_Modal_Mutasi_Opname.png", "04_Siklus_Stok_Opname/Gambar_4.24_Modal_Mutasi_Selisih.png"),
    ("33_Cetak_Berita_Acara_Opname.png", "04_Siklus_Stok_Opname/Gambar_4.25_Berita_Acara_Opname_A4_Lanskap.png"),

    # 05_Dashboard_dan_Laporan
    ("13_Dashboard_Superadmin.png", "05_Dashboard_dan_Laporan/Gambar_4.26_Dashboard_Superadmin.png"),
    ("14_Laporan_Penjualan.png", "05_Dashboard_dan_Laporan/Gambar_4.27_Laporan_Penjualan_Web.png"),
    ("37_Cetak_Laporan_Penjualan.png", "05_Dashboard_dan_Laporan/Gambar_4.28_Cetak_Laporan_Penjualan_A4.png"),
    ("15_Laporan_Persediaan.png", "05_Dashboard_dan_Laporan/Gambar_4.29_Laporan_Persediaan_Web.png"),
    ("38_Cetak_Laporan_Persediaan.png", "05_Dashboard_dan_Laporan/Gambar_4.30_Cetak_Laporan_Persediaan_A4.png"),
    ("16_Laporan_Logistik_Medis.png", "05_Dashboard_dan_Laporan/Gambar_4.31_Laporan_Logistik_Medis_Web.png"),
    ("40_Cetak_Laporan_Logistik_Medis.png", "05_Dashboard_dan_Laporan/Gambar_4.32_Cetak_Laporan_Logistik_Medis_A4.png"),
    ("17_Laporan_Penerimaan_Barang.png", "05_Dashboard_dan_Laporan/Gambar_4.33_Laporan_Penerimaan_Web.png"),
    ("41_Cetak_Laporan_Penerimaan.png", "05_Dashboard_dan_Laporan/Gambar_4.34_Cetak_Laporan_Penerimaan_A4.png"),
    ("18_Laporan_Mutasi_Konversi.png", "05_Dashboard_dan_Laporan/Gambar_4.35_Laporan_Mutasi_Konversi_Web.png"),
    ("42_Cetak_Laporan_Mutasi_Konversi.png", "05_Dashboard_dan_Laporan/Gambar_4.36_Cetak_Laporan_Mutasi_Konversi_A4.png"),
    ("17_Laporan_Anomali_FEFO.png", "05_Dashboard_dan_Laporan/Gambar_4.37_Laporan_Anomali_FEFO_Web.png"),
    ("43_Cetak_Laporan_Anomali_FEFO.png", "05_Dashboard_dan_Laporan/Gambar_4.38_Cetak_Laporan_Anomali_FEFO_A4.png"),

    # 06_Pengaturan_dan_Pengguna
    ("18_Manajemen_Pengguna.png", "06_Pengaturan_dan_Pengguna/Gambar_4.39_Manajemen_Pengguna.png"),
    ("19_Pengaturan_Web.png", "06_Pengaturan_dan_Pengguna/Gambar_4.40_Pengaturan_Web.png"),

    # 07_Toko_Daring_Ecommerce
    ("20_Login_Pelanggan.png", "07_Toko_Daring_Ecommerce/Gambar_4.41_Login_Pelanggan.png"),
    ("21_Profil_Pelanggan.png", "07_Toko_Daring_Ecommerce/Gambar_4.42_Profil_Pelanggan.png"),
    ("22_Beranda_Ecommerce.png", "07_Toko_Daring_Ecommerce/Gambar_4.43_Beranda_Ecommerce.png"),
    ("23_Detail_Produk.png", "07_Toko_Daring_Ecommerce/Gambar_4.44_Detail_Produk.png"),
    ("24_Keranjang_Belanja.png", "07_Toko_Daring_Ecommerce/Gambar_4.45_Keranjang_Belanja.png"),
    ("25_Checkout_Pembayaran.png", "07_Toko_Daring_Ecommerce/Gambar_4.46_Checkout_Pembayaran.png"),
]

def build_zip(target_zip):
    with zipfile.ZipFile(target_zip, 'w', zipfile.ZIP_DEFLATED) as z:
        for src_name, arc_path in mapping:
            src_full = os.path.join(images_dir, src_name)
            if os.path.exists(src_full):
                z.write(src_full, arc_path)
            else:
                print(f"Warning: {src_full} not found yet")
    print(f"Created {target_zip} ({os.path.getsize(target_zip)} bytes)")

build_zip(zip_path_laporan)
build_zip(zip_path_lain)

