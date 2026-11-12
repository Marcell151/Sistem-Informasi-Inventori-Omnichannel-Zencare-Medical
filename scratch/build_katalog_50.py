import json
import os
import zipfile
import re

items = [
    # 1. Autentikasi Staf
    {
        "id": "ui_01", "fig": "Gambar 4.1", "title": "Halaman Login Staf Internal",
        "category": "auth", "role_badge": "Staf Internal", "badge_color": "bg-slate-100 text-slate-700 border-slate-300",
        "filename": "01_Login_Staf.png", "folder": "01_Autentikasi_Staf",
        "desc": "Gerbang autentikasi internal satu pintu bagi seluruh staf operasional dan manajerial (Superadmin, Admin, dan Kasir). Mengimplementasikan sistem sesi terpusat dengan pencocokan kata sandi terenkripsi hash serta pengalihan peran secara otomatis."
    },

    # 2. Kasir POS & Transaksi Omnichannel
    {
        "id": "ui_02", "fig": "Gambar 4.2", "title": "Dashboard Operasional Admin Toko",
        "category": "pos_trx", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "02_Dashboard_Admin.png", "folder": "02_Kasir_dan_Transaksi",
        "desc": "Pusat kendali harian staf admin yang menyajikan kartu metrik ringkas: ringkasan stok minimum, transaksi harian, notifikasi dini kedaluwarsa obat, grafik volume penjualan, dan pintasan terminal POS kasir."
    },
    {
        "id": "ui_03", "fig": "Gambar 4.3", "title": "Terminal Kasir (Point of Sale / POS)",
        "category": "pos_trx", "role_badge": "Kasir Toko", "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "filename": "03_POS_Kasir.png", "folder": "02_Kasir_dan_Transaksi",
        "desc": "Antarmuka transaksi kasir luring di gerai fisik. Memadukan pencarian produk, pemindaian barcode, kartu produk interaktif, keranjang belanja dinamis, kalkulator kembalian, dan alokasi stok otomatis berbasis FEFO."
    },
    {
        "id": "ui_04", "fig": "Gambar 4.4", "title": "Cetak Struk Kasir POS (Format Kertas Termal)",
        "category": "pos_trx", "role_badge": "Kasir Toko", "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "filename": "26_Cetak_Struk_Kasir.png", "folder": "02_Kasir_dan_Transaksi",
        "desc": "Lembar cetak bukti belanja konsumen gerai fisik pada printer termal (thermal slip) 80mm/58mm. Memuat identitas ZenCare Medical, rincian produk, nomor batch dan serial number, harga, total bayar, dan nominal kembalian."
    },
    {
        "id": "ui_05", "fig": "Gambar 4.5", "title": "Pengelolaan Transaksi Penjualan Omnichannel",
        "category": "pos_trx", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "04_Transaksi_Omnichannel.png", "folder": "02_Kasir_dan_Transaksi",
        "desc": "Tabel konsolidasi seluruh transaksi pesanan lintas kanal (gerai fisik POS dan toko daring e-commerce). Dilengkapi filter status pesanan, pencarian nomor faktur, dan tombol cetak dokumen pengiriman."
    },
    {
        "id": "ui_06", "fig": "Gambar 4.6", "title": "Cetak Faktur Penjualan Resmi Toko Format A4",
        "category": "pos_trx", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "27_Cetak_Invoice_Penjualan.png", "folder": "02_Kasir_dan_Transaksi",
        "desc": "Dokumen faktur komersial resmi ZenCare Medical standar A4 untuk pesanan skala besar atau pengiriman daring. Dilengkapi kop surat resmi, rincian nomor batch, alamat pemesan, rincian biaya, dan kolom tanda tangan."
    },
    {
        "id": "ui_07", "fig": "Gambar 4.7", "title": "Cetak Label Pengiriman Kurir Logistik E-Commerce",
        "category": "pos_trx", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "28_Cetak_Label_Pengiriman.png", "folder": "02_Kasir_dan_Transaksi",
        "desc": "Label resi pengiriman kurir ekspedisi untuk paket pesanan e-commerce. Memuat barcode resi, identitas penerima dan kontak, alamat tujuan lengkap, serta peringatan penanganan barang medis rapuh/khusus."
    },

    # 3. Master Data & Logistik Persediaan
    {
        "id": "ui_08", "fig": "Gambar 4.8", "title": "Direktori Master Produk dan Katalog Medis",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "05_Master_Produk.png", "folder": "03_Master_dan_Logistik",
        "desc": "Pusat katalog seluruh komoditas obat dan alat kesehatan. Menyajikan kartu produk informatif, kode SKU Induk, nama produk, kategori, batasan hari kedaluwarsa, dan status etalase katalog."
    },
    {
        "id": "ui_09", "fig": "Gambar 4.9", "title": "Formulir Input Master Produk Induk Baru",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "34_Form_Input_Master_Produk.png", "folder": "03_Master_dan_Logistik",
        "desc": "Kotak dialog interaktif penambahan komoditas medis baru, mencakup penetapan SKU induk, nama resmi obat/alkes, klasifikasi kategori, batasan peringatan dini kedaluwarsa, deskripsi, dan foto produk."
    },
    {
        "id": "ui_10", "fig": "Gambar 4.10", "title": "Formulir Konfigurasi Produk Variasi dan Multi-UOM",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "45_Form_Input_Produk_Variasi.png", "folder": "03_Master_dan_Logistik",
        "desc": "Formulir konfigurasi kemasan bertingkat untuk produk induk, menetapkan SKU variasi, nama variasi kemasan, satuan besar (Box/Dus), satuan kecil (Strip/Botol/Pcs), rasio konversi baku, harga beli, harga jual grosir & eceran, serta stok pengaman minimum."
    },
    {
        "id": "ui_11", "fig": "Gambar 4.11", "title": "Konfigurasi Satuan dan Multi-UOM",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "06_Satuan_UOM.png", "folder": "03_Master_dan_Logistik",
        "desc": "Tabel master unit pengukuran barang (Dus, Box, Strip, Botol, Tablet, Tube, Pcs, Unit) yang menjadi acuan konversi kuantitas pada transaksi multi-satuan."
    },
    {
        "id": "ui_12", "fig": "Gambar 4.12", "title": "Formulir Eksekusi Konversi Satuan Multi-UOM (Terisi Data)",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "35_Form_Konversi_UOM.png", "folder": "03_Master_dan_Logistik",
        "desc": "Formulir pemecahan kemasan besar ke kemasan kecil terisi data operasional nyata (Amoxicillin 500mg, batch AMX-2026-01, 1 Box = 10 Strip), memvalidasi saldo asal dan mengeksekusi penambahan eceran secara atomik."
    },
    {
        "id": "ui_13", "fig": "Gambar 4.13", "title": "Histori Konversi Satuan dan Pemecahan Kemasan",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "12_Konversi_Satuan.png", "folder": "03_Master_dan_Logistik",
        "desc": "Tabel riwayat kronologis seluruh aktivitas konversi kemasan, merinci tanggal kejadian, operator, komoditas, nomor batch asal & hasil, kuantitas kemasan asal yang dideplesi, dan kuantitas unit eceran hasil konversi."
    },
    {
        "id": "ui_14", "fig": "Gambar 4.14", "title": "Direktori Rekanan Pemasok (Supplier)",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "07_Direktori_Supplier.png", "folder": "03_Master_dan_Logistik",
        "desc": "Direktori master mitra distributor resmi pabrikan medis dan alat kesehatan, memuat nama perusahaan rekanan, narahubung, nomor telepon, alamat kantor operasional, dan status kerja sama."
    },
    {
        "id": "ui_15", "fig": "Gambar 4.15", "title": "Formulir Pendaftaran Rekanan Pemasok Baru",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "47_Form_Tambah_Supplier.png", "folder": "03_Master_dan_Logistik",
        "desc": "Formulir modal pendaftaran profil distributor rekanan baru, merekam nama badan usaha penyedia pasokan medis, nama penanggung jawab sales, kontak telepon, email, dan alamat kantor pergudangan."
    },
    {
        "id": "ui_16", "fig": "Gambar 4.16", "title": "Formulir Input Penerimaan Logistik Pasokan Barang Masuk",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "08_Penerimaan_Barang.png", "folder": "03_Master_dan_Logistik",
        "desc": "Formulir aktif pencatatan pasokan logistik masuk dari distributor ke gudang fisik terisi data riil (PT. Kimia Farma, No Faktur FAK-KF-2026-1188, Amoxicillin 50 Box & Betadine 30 Botol, alokasi batch, ED, dan total faktur)."
    },
    {
        "id": "ui_17", "fig": "Gambar 4.17", "title": "Cetak Nota Dokumen Bukti Penerimaan Logistik Format A4",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "29_Cetak_Nota_Penerimaan.png", "folder": "03_Master_dan_Logistik",
        "desc": "Lembar cetak fisik bukti penerimaan pasokan barang gudang standar A4. Memuat nomor bukti, nama distributor, nomor faktur pemasok, rincian kuantitas kemasan besar/kecil, nomor batch/ED, dan paraf verifikasi segel fisik."
    },
    {
        "id": "ui_18", "fig": "Gambar 4.18", "title": "Cetak Label Stiker Barcode Batch & Serial Number Gudang",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "46_Cetak_Label_Stiker_Batch.png", "folder": "03_Master_dan_Logistik",
        "desc": "Lembar cetak label barcode stiker kelompok (batch printing) format A4 Grid (4x10) dan thermal roll untuk ditempelkan pada fisik produk obat dan alkes guna pemindaian cepat di meja kasir."
    },
    {
        "id": "ui_19", "fig": "Gambar 4.19", "title": "Daftar Mutasi Stok Persediaan Manual",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "09_Mutasi_Stok.png", "folder": "03_Master_dan_Logistik",
        "desc": "Tabel rekapitulasi penyesuaian stok non-penjualan dan non-penerimaan, mencatat tanggal mutasi, nomor referensi, komoditas, jenis penyesuaian (Masuk/Keluar), jumlah kuantitas, dan kategori alasan operasional."
    },
    {
        "id": "ui_20", "fig": "Gambar 4.20", "title": "Formulir Penyesuaian Mutasi Stok Manual (Terisi Data)",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "36_Form_Mutasi_Stok.png", "folder": "03_Master_dan_Logistik",
        "desc": "Formulir aktif penyesuaian manual persediaan terisi data riil operasional: komoditas Amoxicillin 500mg, pengurangan stok 2 Strip pada batch AMX-2026-01 akibat kemasan strip penyok di rak etalase."
    },
    {
        "id": "ui_21", "fig": "Gambar 4.21", "title": "Kartu Riwayat Mutasi Stok Persediaan (Inventory Ledger)",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "10_Kartu_Stok.png", "folder": "03_Master_dan_Logistik",
        "desc": "Buku besar persediaan (inventory ledger) web yang melacak kronologi pergerakan setiap item obat/alkes secara mendalam: saldo awal, kuantitas masuk, kuantitas keluar, saldo akhir berjalan, dan jenis transaksi."
    },
    {
        "id": "ui_22", "fig": "Gambar 4.22", "title": "Cetak Kartu Riwayat Mutasi Stok Barang Format A4",
        "category": "master", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "39_Cetak_Laporan_Kartu_Stok.png", "folder": "03_Master_dan_Logistik",
        "desc": "Lembar cetak buku besar inventori resmi format A4 untuk keperluan verifikasi persediaan fisik. Memuat saldo awal periode, rincian mutasi kronologis, kanal transaksi, dan kolom pengesahan penanggung jawab."
    },

    # 4. Stok Opname Terpusat
    {
        "id": "ui_23", "fig": "Gambar 4.23", "title": "Beranda Riwayat Sesi Stok Opname",
        "category": "opname", "role_badge": "Admin & Superadmin", "badge_color": "bg-indigo-50 text-indigo-700 border-indigo-200",
        "filename": "11_Stok_Opname_Riwayat.png", "folder": "04_Stok_Opname_Terpusat",
        "desc": "Beranda utama modul stok opname yang menyajikan riwayat seluruh sesi pemeriksaan fisik terdahulu, status sesi (Sedang Dihitung, Verifikasi, Selesai), tanggal pelaksanaan, dan nilai estimasi kerugian."
    },
    {
        "id": "ui_24", "fig": "Gambar 4.24", "title": "Tahap 1: Inisiasi Sesi dan Pembekuan Transaksi (Superadmin)",
        "category": "opname", "role_badge": "Superadmin", "badge_color": "bg-rose-50 text-rose-700 border-rose-200",
        "filename": "30_Stok_Opname_Mulai_Sesi.png", "folder": "04_Stok_Opname_Terpusat",
        "desc": "Antarmuka Superadmin untuk membuka sesi stok opname baru dan mengaktifkan mekanisme Freeze Lock di tingkat server guna membekukan seluruh modul transaksi penjualan, konversi, dan penerimaan barang."
    },
    {
        "id": "ui_25", "fig": "Gambar 4.25", "title": "Tahap 2: Formulir Pencacahan Fisik Rak Blind Count (Kasir)",
        "category": "opname", "role_badge": "Kasir Toko", "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "filename": "31_Stok_Opname_Blind_Count_Kasir.png", "folder": "04_Stok_Opname_Terpusat",
        "desc": "Formulir input pencacahan fisik rak oleh staf kasir tanpa dapat melihat angka saldo pembukuan sistem (Blind Count). Memuat 3 kolom kondisi fisik: Fisik Baik, Fisik Rusak, dan Fisik Kedaluwarsa."
    },
    {
        "id": "ui_26", "fig": "Gambar 4.26", "title": "Tahap 3: Verifikasi dan Rekonsiliasi Anomali Selisih (Admin)",
        "category": "opname", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "32_Stok_Opname_Verifikasi_Admin.png", "folder": "04_Stok_Opname_Terpusat",
        "desc": "Antarmuka verifikasi admin yang secara otomatis menyembunyikan barang klop (105 barang) dan menyoroti 6 komoditas yang mengalami anomali selisih fisik beserta estimasi nilai acuan kerugian persediaan."
    },
    {
        "id": "ui_27", "fig": "Gambar 4.27", "title": "Tahap 3 (Lanjutan): Modal Penyesuaian Mutasi Selisih Per Item",
        "category": "opname", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "44_Modal_Mutasi_Opname.png", "folder": "04_Stok_Opname_Terpusat",
        "desc": "Kotak dialog modal eksekusi mutasi penyesuaian selisih untuk setiap item berselisih (misal barang hilang selisih -2 Strip Amoxicillin 500mg bernilai beban Rp 8.000) sebelum sesi dapat ditutup."
    },
    {
        "id": "ui_28", "fig": "Gambar 4.28", "title": "Tahap 4: Cetak Berita Acara Pemeriksaan Fisik A4 Lanskap",
        "category": "opname", "role_badge": "Superadmin", "badge_color": "bg-rose-50 text-rose-700 border-rose-200",
        "filename": "33_Cetak_Berita_Acara_Opname.png", "folder": "04_Stok_Opname_Terpusat",
        "desc": "Dokumen resmi Berita Acara Hasil Pemeriksaan Fisik Persediaan format A4 Lanskap 10 kolom data lengkap. Dilengkapi 3 kolom tanda tangan pengesahan berjenjang: Kasir, Admin, dan Superadmin."
    },

    # 5. Dashboard Eksekutif & Manajemen
    {
        "id": "ui_29", "fig": "Gambar 4.29", "title": "Dashboard Eksekutif Superadmin",
        "category": "dashboard", "role_badge": "Superadmin", "badge_color": "bg-rose-50 text-rose-700 border-rose-200",
        "filename": "13_Dashboard_Superadmin.png", "folder": "05_Dashboard_Eksekutif",
        "desc": "Pusat kendali eksekutif pemilik gerai. Menyajikan omzet total penjualan gabungan, total valuasi aset persediaan, ringkasan kepatuhan FEFO, tombol darurat Freeze Lock, dan grafik tren bulanan."
    },
    {
        "id": "ui_30", "fig": "Gambar 4.42", "title": "Manajemen Pengguna dan Penetapan Hak Akses (RBAC)",
        "category": "dashboard", "role_badge": "Superadmin", "badge_color": "bg-rose-50 text-rose-700 border-rose-200",
        "filename": "18_Manajemen_Pengguna.png", "folder": "05_Dashboard_Eksekutif",
        "desc": "Modul keamanan akun internal dan pelanggan publik. Menampilkan tabel akun pengguna, pembagian peran kerja (Superadmin, Admin, Kasir, Pelanggan), status akun, dan tombol penambahan pengguna."
    },
    {
        "id": "ui_31", "fig": "Gambar 4.43", "title": "Formulir Pendaftaran Pengguna Baru dan Penetapan Hak Akses Role RBAC",
        "category": "dashboard", "role_badge": "Superadmin", "badge_color": "bg-rose-50 text-rose-700 border-rose-200",
        "filename": "48_Form_Tambah_User.png", "folder": "05_Dashboard_Eksekutif",
        "desc": "Formulir modal pendaftaran staf operasional baru dengan penetapan hak akses berbasis peran (RBAC), memfasilitasi perekaman username unik, nama lengkap staf, email, kata sandi, dan role operasional."
    },
    {
        "id": "ui_32", "fig": "Gambar 4.44", "title": "Pengaturan Toko Daring dan Konten Web",
        "category": "dashboard", "role_badge": "Superadmin", "badge_color": "bg-rose-50 text-rose-700 border-rose-200",
        "filename": "19_Pengaturan_Web.png", "folder": "05_Dashboard_Eksekutif",
        "desc": "Panel kustomisasi toko online dan identitas fisik gerai: logo toko, favicon, nomor WhatsApp customer service, banner promosi karusel beranda, dan syarat ketentuan transaksi."
    },

    # 6. Laporan Manajerial & Lembar Cetak
    {
        "id": "ui_33", "fig": "Gambar 4.30", "title": "Laporan Penjualan Barang Omnichannel (Web)",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "14_Laporan_Penjualan.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Laporan omzet penjualan gabungan kasir POS dan toko daring. Menyajikan filter rentang tanggal, filter kanal penjualan, grafik batang perbandingan, dan tabel transaksi terperinci."
    },
    {
        "id": "ui_34", "fig": "Gambar 4.31", "title": "Cetak Laporan Penjualan Barang Resmi Format A4",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "37_Cetak_Laporan_Penjualan.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Lembar cetak resmi rekapitulasi penjualan format A4. Memuat kop surat resmi ZenCare Medical, omzet kotor, total potongan, omzet bersih, kuantitas barang terjual, dan kolom tanda tangan pimpinan."
    },
    {
        "id": "ui_35", "fig": "Gambar 4.32", "title": "Laporan Persediaan dan Ketersediaan Barang (Web)",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "15_Laporan_Persediaan.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Laporan status saldo fisik barang di gudang dan rak etalase. Menyajikan total jenis komoditas, valuasi total persediaan berbasis harga beli, filter status stok, dan tabel rincian variasi."
    },
    {
        "id": "ui_36", "fig": "Gambar 4.33", "title": "Cetak Laporan Persediaan Barang Resmi Format A4",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "38_Cetak_Laporan_Persediaan.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Lembar cetak resmi ringkasan persediaan barang format A4. Merinci kode produk, nama komoditas, kemasan satuan, saldo fisik, harga beli perolehan, total nilai aset, dan kolom pengesahan logistik."
    },
    {
        "id": "ui_37", "fig": "Gambar 4.34", "title": "Laporan Logistik Medis FEFO dan Kedaluwarsa (Web)",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "16_Laporan_Logistik_Medis.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Laporan pengawasan masa simpan obat berbasis FEFO. Membagi produk ke dalam 3 tab visual: Kedaluwarsa Kritis (<=30 hari), Peringatan Dini (<=90 hari), dan Aman (>90 hari)."
    },
    {
        "id": "ui_38", "fig": "Gambar 4.35", "title": "Cetak Laporan Logistik Medis FEFO Resmi Format A4",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "40_Cetak_Laporan_Logistik_Medis.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Dokumen cetak resmi evaluasi masa kedaluwarsa format A4. Memuat nomor batch, tanggal ED, jumlah stok tersisa, status risiko, rekomendasi tindakan retur/diskon, dan tanda tangan penanggung jawab teknis."
    },
    {
        "id": "ui_39", "fig": "Gambar 4.36", "title": "Laporan Penerimaan Pasokan Barang Masuk (Web)",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "17_Laporan_Penerimaan_Barang.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Laporan rekapitulasi pengadaan pasokan dari distributor rekanan. Dilengkapi penyaring periode tanggal, nama distributor, serta ringkasan total nilai pasokan barang masuk."
    },
    {
        "id": "ui_40", "fig": "Gambar 4.37", "title": "Cetak Laporan Penerimaan Pasokan Barang Masuk Format A4",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "41_Cetak_Laporan_Penerimaan.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Dokumen cetak rekapitulasi pasokan masuk resmi format A4. Memuat nomor faktur distributor, waktu penerimaan gudang, staf penerima, rincian produk, nomor batch/ED, dan harga beli perolehan."
    },
    {
        "id": "ui_41", "fig": "Gambar 4.38", "title": "Laporan Mutasi Stok dan Konversi Satuan (Web)",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "18_Laporan_Mutasi_Konversi.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Laporan rekapitulasi pergerakan internal barang yang memadukan dua aktivitas: penyesuaian manual (barang rusak/kedaluwarsa/hilang) dan histori pemecahan kemasan konversi multi-UOM."
    },
    {
        "id": "ui_42", "fig": "Gambar 4.39", "title": "Cetak Laporan Mutasi Stok dan Konversi Satuan Format A4",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "42_Cetak_Laporan_Mutasi_Konversi.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Dokumen cetak rekapitulasi pergerakan internal format A4. Merinci tanggal kejadian, kode barang, nama produk, kuantitas mutasi, alasan pembebanan, rasio konversi yang diterapkan, dan paraf pimpinan."
    },
    {
        "id": "ui_43", "fig": "Gambar 4.40", "title": "Laporan Deteksi Anomali Pengeluaran FEFO (Web)",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "17_Laporan_Anomali_FEFO.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Laporan kepatuhan protokol FEFO kasir. Merekam kejadian pengeluaran obat dengan batch kedaluwarsa lebih jauh padahal batch terdekat masih tersedia di etalase beserta catatan alasan petugas."
    },
    {
        "id": "ui_44", "fig": "Gambar 4.41", "title": "Cetak Laporan Deteksi Anomali Pengeluaran FEFO Format A4",
        "category": "report", "role_badge": "Admin Toko", "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "43_Cetak_Laporan_Anomali_FEFO.png", "folder": "06_Laporan_dan_Cetak_Resmi",
        "desc": "Lembar evaluasi kepatuhan FEFO resmi format A4. Merinci nomor faktur transaksi, kasir bertugas, nama obat, batch yang semestinya keluar, batch yang diambil, alasan override kasir, dan evaluasi pimpinan."
    },

    # 7. Toko Daring E-Commerce
    {
        "id": "ui_45", "fig": "Gambar 4.45", "title": "Pendaftaran dan Masuk Akun Pelanggan",
        "category": "ecom", "role_badge": "Pelanggan", "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "20_Login_Pelanggan.png", "folder": "07_Toko_Daring_Ecommerce",
        "desc": "Halaman autentikasi khusus pembeli daring toko ZenCare Medical. Menyajikan formulir masuk akun terdaftar dan formulir registrasi akun konsumen baru yang terisolasi dari login staf internal."
    },
    {
        "id": "ui_46", "fig": "Gambar 4.46", "title": "Profil dan Pemantauan Pesanan Pelanggan",
        "category": "ecom", "role_badge": "Pelanggan", "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "21_Profil_Pelanggan.png", "folder": "07_Toko_Daring_Ecommerce",
        "desc": "Portal informasi pelanggan daring, memuat biodata pribadi, buku alamat pengiriman, dan pelacakan tahapan pemrosesan pesanan (Menunggu Pembayaran, Diproses Gudang, Dikirim Kurir, Selesai)."
    },
    {
        "id": "ui_47", "fig": "Gambar 4.47", "title": "Beranda Toko Daring (E-Commerce)",
        "category": "ecom", "role_badge": "Pelanggan", "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "22_Beranda_Ecommerce.png", "folder": "07_Toko_Daring_Ecommerce",
        "desc": "Etalase digital pelanggan publik yang menyajikan katalog produk obat bebas dan alat kesehatan. Dilengkapi banner promo, bilah pencarian cerdas, navigasi kategori, dan status stok ketersediaan siap kirim."
    },
    {
        "id": "ui_48", "fig": "Gambar 4.48", "title": "Rincian Informasi dan Spesifikasi Produk",
        "category": "ecom", "role_badge": "Pelanggan", "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "23_Detail_Produk.png", "folder": "07_Toko_Daring_Ecommerce",
        "desc": "Halaman detail komoditas medis, memuat foto fisik multi-sudut, deskripsi indikasi medis/spesifikasi alkes, jaminan batas tanggal ED terdekat, pilihan kemasan eceran/grosir, dan tombol tambah keranjang."
    },
    {
        "id": "ui_49", "fig": "Gambar 4.49", "title": "Panel Keranjang Belanja Pelanggan",
        "category": "ecom", "role_badge": "Pelanggan", "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "24_Keranjang_Belanja.png", "folder": "07_Toko_Daring_Ecommerce",
        "desc": "Panel ringkasan barang belanjaan pilihan pelanggan, memfasilitasi penyesuaian jumlah unit beli dengan validasi stok gudang maksimal, kalkulasi subtotal otomatis, dan tombol lanjut ke checkout."
    },
    {
        "id": "ui_50", "fig": "Gambar 4.50", "title": "Pembayaran dan Pengiriman Pesanan (Checkout)",
        "category": "ecom", "role_badge": "Pelanggan", "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "25_Checkout_Pembayaran.png", "folder": "07_Toko_Daring_Ecommerce",
        "desc": "Halaman checkout akhir pesanan daring, memfasilitasi penentuan alamat tujuan, pilihan kurir pengiriman, kalkulasi ongkos kirim otomatis, gerbang pembayaran daring (VA/QRIS), dan reservasi stok FEFO otomatis."
    }
]

# 1. Build Pre-generated ZIP File
zip_paths = [
    r"C:\xampp\htdocs\inventory_zencare\laporan_ta\Katalog_UI_UX_ZenCare_Medical.zip",
    r"C:\xampp\htdocs\inventory_zencare\lain\Katalog_UI_UX_ZenCare_Medical.zip"
]
src_dir = r"C:\xampp\htdocs\inventory_zencare\lain\File PPT\UI_UX_Asli"

print("Building ZIP archives...")
for zip_p in zip_paths:
    os.makedirs(os.path.dirname(zip_p), exist_ok=True)
    with zipfile.ZipFile(zip_p, 'w', zipfile.ZIP_DEFLATED) as z:
        for item in items:
            f_name = item['filename']
            f_src = os.path.join(src_dir, f_name)
            if os.path.exists(f_src):
                fig_clean = item['fig'].replace(' ', '_').replace('.', '_')
                arc_name = f"{item['folder']}/{fig_clean}_{f_name}"
                z.write(f_src, arc_name)
    print(f"  Rebuilt ZIP: {zip_p} ({os.path.getsize(zip_p)} bytes, {len(items)} files)")

# 2. Build HTML Catalog Downloader
with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\katalog_download_ui_ux_zencare.html', 'r', encoding='utf-8') as f:
    orig_cat = f.read()

items_json_str = json.dumps(items, indent=4)
new_cat = re.sub(r'const items = \[.*?\];', f'const items = {items_json_str};', orig_cat, flags=re.DOTALL)

new_cat = re.sub(r'>46<', '>50<', new_cat)
new_cat = re.sub(r'46 Tangkapan Layar', '50 Tangkapan Layar', new_cat)

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\katalog_download_ui_ux_zencare.html', 'w', encoding='utf-8') as f:
    f.write(new_cat)
print(f"Generated laporan_ta/katalog_download_ui_ux_zencare.html successfully ({len(new_cat)} bytes)")

cat_lain = new_cat.replace('../lain/File PPT/UI_UX_Asli/', 'File PPT/UI_UX_Asli/')
with open(r'C:\xampp\htdocs\inventory_zencare\lain\katalog_download_ui_ux_zencare.html', 'w', encoding='utf-8') as f:
    f.write(cat_lain)
print(f"Generated lain/katalog_download_ui_ux_zencare.html successfully ({len(cat_lain)} bytes)")
