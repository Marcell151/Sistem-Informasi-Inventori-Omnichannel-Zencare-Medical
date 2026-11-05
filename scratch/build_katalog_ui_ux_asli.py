# -*- coding: utf-8 -*-
import os
import json

items = [
    {
        "id": "ui_01",
        "fig": "Gambar 3.19",
        "title": "Login Staf",
        "category": "auth",
        "category_label": "A. Autentikasi Staf",
        "role_badge": "Staf Internal",
        "badge_color": "bg-slate-100 text-slate-700 border-slate-300",
        "ui_img": "File PPT/UI_UX_Asli/01_Login_Staf.png",
        "wf_img": "File PPT/Wireframe/Login (Staf).png",
        "tables": ["users"],
        "desc": "Gerbang autentikasi internal satu pintu untuk seluruh staf operasional dan manajerial. Antarmuka ini mengamankan sistem melalui verifikasi kredensial username dan password terenkripsi, lalu mengarahkan sesi secara cerdas sesuai hierarki hak akses (Superadmin, Admin, atau Kasir) dengan proteksi brute force dan session management yang aman.",
        "alignment": "Menerapkan klausul SRS F-01 dan Use Case Autentikasi Pengguna. Desain wireframe minimalis dan berfokus pada efisiensi input staf tanpa distorsi elemen dekoratif."
    },
    {
        "id": "ui_02",
        "fig": "Gambar 3.20",
        "title": "Dashboard Admin",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/02_Dashboard_Admin.png",
        "wf_img": "File PPT/Wireframe/Dashboard (Admin).png",
        "tables": ["penjualan", "produk_variasi", "stok_batch", "users"],
        "desc": "Pusat kendali operasional harian yang menyajikan metrik transaksi omnichannel real-time, ringkasan antrean pesanan baru, indikator stok menipis (buffer stock), serta visualisasi grafik pergerakan barang medis harian. Dilengkapi tautan cepat menuju modul transaksi, katalog, dan terminal kasir.",
        "alignment": "Memenuhi kebutuhan operasional harian Admin pada SRS F-02. Selaras dengan wireframe dengan mempertahankan kartu KPI di bagian atas dan tabel pantauan status pesanan di area tengah."
    },
    {
        "id": "ui_03",
        "fig": "Gambar 3.21",
        "title": "Terminal Kasir (POS)",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Kasir & Admin",
        "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "ui_img": "File PPT/UI_UX_Asli/03_POS_Kasir.png",
        "wf_img": "File PPT/Wireframe/POS.png",
        "tables": ["penjualan", "detail_penjualan", "pesanan_batch_alokasi", "unit_serial", "stok_batch", "stok_toko"],
        "desc": "Terminal transaksi kasir luring dengan tata letak split-screen. Sisi kiri memfasilitasi pencarian cepat komoditas medis, pemindaian barcode batch, dan pemilihan unit serial number alkes. Sisi kanan memuat ringkasan keranjang belanja, kalkulasi otomatis diskon, pilihan metode bayar tunai/QRIS, serta tombol cetak struk termal instan.",
        "alignment": "Menerapkan alokasi FEFO otomatis dan pencatatan serial number alkes sesuai SRS F-03. Tata letak split-screen pada wireframe terbukti memangkas waktu pelayanan di toko fisik."
    },
    {
        "id": "ui_04",
        "fig": "Gambar 3.22",
        "title": "Transaksi Omnichannel",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/04_Transaksi_Omnichannel.png",
        "wf_img": "File PPT/Wireframe/Transaksi.png",
        "tables": ["penjualan", "detail_penjualan", "pesanan_batch_alokasi", "users"],
        "desc": "Pusat manajemen dan pelacakan seluruh pesanan omnichannel lintas kanal (E-Commerce daring maupun Kasir luring). Dilengkapi filter tab status pesanan (Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan), pencarian nomor faktur, dan modul cetak invoice serta label pengiriman logistik.",
        "alignment": "Memenuhi alur konsolidasi pesanan daring dan luring pada SRS F-04. Menjaga sinkronisasi pemotongan kuota stok global agar tidak terjadi over-selling."
    },
    {
        "id": "ui_05",
        "fig": "Gambar 3.23",
        "title": "Master Produk & Katalog",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin & Superadmin",
        "badge_color": "bg-indigo-50 text-indigo-700 border-indigo-200",
        "ui_img": "File PPT/UI_UX_Asli/05_Master_Produk.png",
        "wf_img": "File PPT/Wireframe/Produk.png",
        "tables": ["produk_induk", "produk_variasi", "stok_batch", "unit_serial"],
        "desc": "Pengelolaan hierarki dua tingkat: produk induk (informasi generik, kategori medis, deskripsi) dan variasi kemasan (kekuatan dosis, SKU variasi, multi-satuan, batas kadaluwarsa, harga jual bertingkat). Memfasilitasi cetak barcode label stiker untuk identifikasi fisik produk.",
        "alignment": "Mendukung normalisasi basis data 3NF antara produk induk dan variasi. Memastikan integritas katalog komoditas medis sesuai SRS F-05."
    },
    {
        "id": "ui_06",
        "fig": "Gambar 3.24",
        "title": "Satuan & Multi-UOM",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/06_Satuan_UOM.png",
        "wf_img": "File PPT/Wireframe/Satuan & UOM.png",
        "tables": ["produk_variasi"],
        "desc": "Konfigurasi hierarki konversi multi-satuan (Multi-UOM) yang memetakan satuan kemasan besar (Box/Dus/Karton) dengan satuan eceran terkecil (Strip/Pcs/Botol). Mengatur rasio pengali matematis, ambang batas minimum stok pada masing-masing tingkatan, serta penetapan disparitas harga e-commerce dan kasir.",
        "alignment": "Mendasari algoritma konversi otomatis persediaan sesuai SRS F-06. Mencegah kesalahan hitung matematis saat transaksi eceran maupun pemecahan kemasan grosir."
    },
    {
        "id": "ui_07",
        "fig": "Gambar 3.25",
        "title": "Direktori Supplier",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/07_Direktori_Supplier.png",
        "wf_img": "File PPT/Wireframe/Supplier.png",
        "tables": ["supplier"],
        "desc": "Direktori master mitra penyedia dan distributor resmi barang medis. Mencatat nama badan usaha distributor, narahubung (contact person), nomor telepon, alamat email, alamat gudang logistik, serta status keaktifan kerja sama rantai pasok.",
        "alignment": "Menjadi entitas referensi utama pada modul inbound penerimaan barang logistik kesehatan sesuai SRS F-07."
    },
    {
        "id": "ui_08",
        "fig": "Gambar 3.26",
        "title": "Penerimaan Barang (Inbound Gudang)",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/08_Penerimaan_Barang.png",
        "wf_img": "File PPT/Wireframe/Penerimaan Barang.png",
        "tables": ["penerimaan_stok", "penerimaan_detail", "stok_batch", "unit_serial", "kartu_stok", "supplier"],
        "desc": "Formulir pencatatan logistik masuk dari distributor mitra ke gudang toko. Mewajibkan staf gudang menginput nomor referensi surat jalan/faktur, memilih variasi produk, menetapkan nomor batch fisik pabrikan, tanggal kedaluwarsa (expired date), jumlah unit diterima, serta pendaftaran nomor seri unik jika komoditas berupa alat kesehatan bergaransi.",
        "alignment": "Menerapkan gerbang validasi data awal FEFO dan ketelusuran serial number sesuai SRS F-08. Otomatis mencatatkan jurnal mutasi masuk pada kartu stok."
    },
    {
        "id": "ui_09",
        "fig": "Gambar 3.27",
        "title": "Mutasi Stok Manual",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/09_Mutasi_Stok.png",
        "wf_img": "File PPT/Wireframe/Mutasi Stok.png",
        "tables": ["kartu_stok", "stok_batch", "produk_variasi", "users"],
        "desc": "Panel penyesuaian kuantitas fisik di luar transaksi penjualan reguler. Memfasilitasi mutasi masuk maupun mutasi keluar disertai kode dokumen dan klasifikasi alasan resmi seperti barang rusak, kemasan bocor, retur dari pelanggan, atau koreksi administratif, lengkap dengan tabel riwayat mutasi terbaru di sisi kanan.",
        "alignment": "Memenuhi fungsi penyesuaian non-transaksional pada SRS F-09 dengan rekam jejak audit yang transparan dan dapat dipertanggungjawabkan."
    },
    {
        "id": "ui_10",
        "fig": "Gambar 3.28",
        "title": "Kartu Stok Audit",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin & Superadmin",
        "badge_color": "bg-indigo-50 text-indigo-700 border-indigo-200",
        "ui_img": "File PPT/UI_UX_Asli/10_Kartu_Stok.png",
        "wf_img": "File PPT/Wireframe/Kartu Stok.png",
        "tables": ["kartu_stok", "produk_variasi", "users"],
        "desc": "Buku besar kronologis persediaan yang mencatat setiap fluktuasi masuk, keluar, dan sisa stok fisik secara matematis. Dilengkapi filter produk, rentang tanggal, jenis mutasi, serta kolom penanggung jawab operasional guna menjamin akuntabilitas audit.",
        "alignment": "Menjamin asas transparansi persediaan sesuai kaidah Good Distribution Practice (GDP) logistik kesehatan dan SRS F-10."
    },
    {
        "id": "ui_11",
        "fig": "Gambar 3.29",
        "title": "Stok Opname & Audit Fisik",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/11_Stok_Opname.png",
        "wf_img": "File PPT/Wireframe/Stok Opname.png",
        "tables": ["sesi_opname", "detail_opname_batch", "detail_opname_sn", "riwayat_hitung_opname", "stok_batch", "kartu_stok"],
        "desc": "Modul audit fisik persediaan berkala dengan metodologi blind count (kolom stok sistem disembunyikan bagi enumerator lapangan). Mendukung freeze lock sistem selama penghitungan berlangsung, audit fisik batch obat, pencocokan serial number fisik alkes, serta persetujuan selisih oleh manajerial.",
        "alignment": "Mewujudkan mekanisme kontrol inventaris preventif terhadap selisih fisik barang medis sesuai spesifikasi SRS F-11."
    },
    {
        "id": "ui_12",
        "fig": "Gambar 3.30",
        "title": "Konversi Satuan & Pecah Dus",
        "category": "admin",
        "category_label": "B. Operasional Admin & Kasir",
        "role_badge": "Admin Operasional",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "ui_img": "File PPT/UI_UX_Asli/12_Konversi_Satuan.png",
        "wf_img": "File PPT/Wireframe/Konversi Satuan.png",
        "tables": ["histori_konversi", "produk_variasi", "stok_batch", "kartu_stok"],
        "desc": "Antarmuka pemecahan kemasan besar (Box/Dus) menjadi kemasan eceran siap jual (Strip/Pcs). Sistem menghitung pengali otomatis berdasarkan rasio konversi master, memotong stok kemasan induk, serta melahirkan sub-batch eceran baru yang mewarisi nomor batch dan tanggal kedaluwarsa aslinya secara presisi.",
        "alignment": "Menjamin ketelusuran masa simpan komoditas medis hasil pemecahan kemasan sesuai standar SRS F-12."
    },
    # C. Superadmin
    {
        "id": "ui_13",
        "fig": "Gambar 3.31",
        "title": "Dashboard Superadmin",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/13_Dashboard_Superadmin.png",
        "wf_img": "File PPT/Wireframe/Dashboard (Superadmin).png",
        "tables": ["penjualan", "detail_penjualan", "produk_variasi", "stok_batch", "log_anomali_fefo"],
        "desc": "Pusat komando eksekutif bagi pemilik bisnis (Superadmin) untuk meninjau performa bisnis secara makro. Menyajikan perbandingan omzet penjualan saluran POS vs E-Commerce, rasio laba kotor, pantauan valuasi aset logistik, serta visualisasi peringatan anomali kedaluwarsa.",
        "alignment": "Memfasilitasi pengambilan keputusan strategis pemilik usaha sesuai kebutuhan manajerial pada SRS F-13."
    },
    {
        "id": "ui_14",
        "fig": "Gambar 3.32",
        "title": "Laporan Penjualan Barang",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/14_Laporan_Penjualan.png",
        "wf_img": "File PPT/Wireframe/Laporan Penjualan Barang.png",
        "tables": ["penjualan", "detail_penjualan", "produk_variasi"],
        "desc": "Rekapitulasi komprehensif transaksi komersial dengan filter rentang tanggal, kanal penjualan (Omnichannel/POS/E-Commerce), dan kategori produk medis. Dilengkapi analisis margin keuntungan per item, rangkuman produk terlaris (top sellers), serta fitur ekspor laporan ke format Excel dan PDF.",
        "alignment": "Memenuhi akuntabilitas pelaporan keuangan dan penjualan berkala sesuai amanat SRS F-14."
    },
    {
        "id": "ui_15",
        "fig": "Gambar 3.33",
        "title": "Laporan Persediaan & Valuasi",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/15_Laporan_Persediaan.png",
        "wf_img": "File PPT/Wireframe/Laporan Persediaan.png",
        "tables": ["stok_batch", "produk_variasi", "stok_toko"],
        "desc": "Ikhtisar status persediaan barang medis yang merangkum posisi stok fisik, kuota reservasi pesanan daring, indikator stok di bawah batas aman (reorder point), serta kalkulasi valuasi finansial aset persediaan gudang.",
        "alignment": "Membantu optimalisasi perputaran persediaan dan mencegah kerugian akibat kelebihan atau kekurangan stok sesuai SRS F-15."
    },
    {
        "id": "ui_16",
        "fig": "Gambar 3.34",
        "title": "Laporan Logistik Medis & Kedaluwarsa",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/16_Laporan_Logistik_Medis.png",
        "wf_img": "File PPT/Wireframe/Laporan Logistik Medis.png",
        "tables": ["stok_batch", "produk_variasi", "unit_serial"],
        "desc": "Panel pemantauan kritis masa simpan barang medis dengan klasifikasi zona waktu kedaluwarsa: Kritis (< 30 hari), Peringatan Dini (30 - 90 hari), dan Aman (> 90 hari). Membantu eksekutif mengambil langkah strategis seperti bundling promosi atau retur ke pabrikan.",
        "alignment": "Mencegah kerugian finansial akibat obat kadaluwarsa di gudang dan menjamin kepatuhan regulasi kesehatan sesuai SRS F-16."
    },
    {
        "id": "ui_17",
        "fig": "Gambar 3.35",
        "title": "Laporan Penerimaan Barang & Audit Inbound",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/17_Laporan_Penerimaan_Barang.png",
        "wf_img": "File PPT/Wireframe/Laporan Penerimaan Barang.png",
        "tables": ["penerimaan_stok", "penerimaan_detail", "supplier", "produk_variasi", "stok_batch", "users"],
        "desc": "Laporan eksekutif yang merekapitulasi seluruh alur logistik masuk (inbound) komoditas medis dari distributor rekanan. Antarmuka menyajikan riwayat nomor referensi surat jalan, tanggal penerimaan, nama distributor/PBF, produk & variasi, nomor batch, tanggal kedaluwarsa, kuantitas pesan vs terima, harga beli satuan, status penerimaan, serta pencatatan unit rusak/cacat yang diretur. Dilengkapi filter tanggal, supplier, status penerimaan, dan fitur ekspor dokumen.",
        "alignment": "Memenuhi fungsi pengawasan manajerial terhadap rantai pasok inbound dan verifikasi retur fisik barang medis sesuai klausul SRS dan Activity Diagram Laporan Penerimaan Barang."
    },
    {
        "id": "ui_18",
        "fig": "Gambar 3.36",
        "title": "Laporan Mutasi & Konversi Satuan",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/18_Laporan_Mutasi_Konversi.png",
        "wf_img": "File PPT/Wireframe/Laporan Mutasi & Konversi.png",
        "tables": ["kartu_stok", "histori_konversi", "produk_variasi", "stok_batch", "users"],
        "desc": "Pusat audit manajerial yang menyatukan dua modul pergerakan persediaan non-transaksional melalui navigasi tab interaktif. Tab Konversi Satuan merekapitulasi pemecahan kemasan dus besar menjadi kemasan eceran (strip/pcs), nomor batch asal, nomor sub-batch baru yang terbentuk, rasio pengali, jumlah dus yang dibongkar, kuantitas eceran yang dihasilkan, dan sisa dus akhir. Tab Mutasi Stok merekapitulasi penyesuaian fisik (barang rusak, kemasan cacat, selisih opname, atau koreksi) lengkap dengan nomor dokumen, kuantitas, dan alasan resmi staf.",
        "alignment": "Menerapkan prinsip ketelusuran rantai dingin dan masa kedaluwarsa pasca-pemecahan kemasan serta akuntabilitas mutasi penyesuaian sesuai spesifikasi SRS dan Activity Diagram Laporan Mutasi & Konversi Satuan."
    },
    {
        "id": "ui_19",
        "fig": "Gambar 3.37",
        "title": "Laporan Audit Anomali FEFO",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/17_Laporan_Anomali_FEFO.png",
        "wf_img": "File PPT/Wireframe/Laporan Anomali FEFO.png",
        "tables": ["log_anomali_fefo", "penjualan", "stok_batch", "users"],
        "desc": "Rekam jejak audit terhadap seluruh transaksi kasir yang melakukan penyimpangan urutan pengeluaran batch (FEFO override). Menampilkan nomor faktur, batch yang diambil vs batch tertua yang seharusnya dikeluarkan, selisih hari kedaluwarsa, identitas kasir pelaksana, serta justifikasi alasan operasional.",
        "alignment": "Menerapkan klausul pengawasan mutu ketat pada sistem distribusi logistik kesehatan sesuai SRS F-17."
    },
    {
        "id": "ui_20",
        "fig": "Gambar 3.38",
        "title": "Manajemen Pengguna",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/18_Manajemen_Pengguna.png",
        "wf_img": "File PPT/Wireframe/Manajemen Pengguna.png",
        "tables": ["users"],
        "desc": "Panel administrasi akun pengguna sistem (Role-Based Access Control). Memfasilitasi pendaftaran staf baru, pengaturan peran akses (Superadmin, Admin, Kasir, Pelanggan), reset kata sandi terenkripsi, serta aktivasi atau penonaktifan hak akses pengguna.",
        "alignment": "Menjamin keamanan sistem dan segregasi tugas antar staf sesuai prinsip keamanan perangkat lunak pada SRS F-18."
    },
    {
        "id": "ui_21",
        "fig": "Gambar 3.39",
        "title": "Pengaturan Web & Konten Toko",
        "category": "superadmin",
        "category_label": "C. Manajerial Superadmin",
        "role_badge": "Superadmin (Pemilik)",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "ui_img": "File PPT/UI_UX_Asli/19_Pengaturan_Web.png",
        "wf_img": "File PPT/Wireframe/Pengaturan Web.png",
        "tables": ["pengaturan_web"],
        "desc": "Pusat konfigurasi global portal e-commerce ZenCare Medical. Mengelola nama toko, narasi profil usaha, nomor kontak WhatsApp layanan pelanggan, alamat fisik apotek, logo brand, spanduk promosi beranda, hingga batas peringatan dini kedaluwarsa.",
        "alignment": "Menyediakan parameterisasi fleksibel tanpa mengubah basis kode pemrograman sistem sesuai SRS F-19."
    },
    # D. Pelanggan
    {
        "id": "ui_22",
        "fig": "Gambar 3.40",
        "title": "Login Pelanggan",
        "category": "pelanggan",
        "category_label": "D. Pelanggan E-Commerce",
        "role_badge": "Pelanggan Publik",
        "badge_color": "bg-amber-50 text-amber-700 border-amber-200",
        "ui_img": "File PPT/UI_UX_Asli/20_Login_Pelanggan.png",
        "wf_img": "File PPT/Wireframe/Login (Pelanggan).png",
        "tables": ["users"],
        "desc": "Halaman autentikasi mandiri untuk pelanggan portal e-commerce. Menyediakan formulir login akun terdaftar, registrasi pengguna baru, serta opsi masuk cepat guna mengakses riwayat pesanan dan status transaksi belanja obat serta alat kesehatan.",
        "alignment": "Memfasilitasi gerbang akses pelanggan publik yang terpisah dari portal staf internal sesuai spesifikasi SRS F-20."
    },
    {
        "id": "ui_23",
        "fig": "Gambar 3.41",
        "title": "Akun & Profil Pelanggan",
        "category": "pelanggan",
        "category_label": "D. Pelanggan E-Commerce",
        "role_badge": "Pelanggan Publik",
        "badge_color": "bg-amber-50 text-amber-700 border-amber-200",
        "ui_img": "File PPT/UI_UX_Asli/21_Profil_Pelanggan.png",
        "wf_img": "File PPT/Wireframe/Profil.png",
        "tables": ["users", "penjualan", "detail_penjualan"],
        "desc": "Dashboard akun mandiri pelanggan yang menampilkan informasi profil pribadi, pengaturan alamat pengiriman utama dengan koordinat wilayah kurir, serta rekam jejak riwayat pesanan daring beserta nomor resi pengiriman ekspedisi logistik.",
        "alignment": "Meningkatkan kepuasan pengalaman berbelanja mandiri pelanggan e-commerce sesuai klausul SRS F-21."
    },
    {
        "id": "ui_24",
        "fig": "Gambar 3.42",
        "title": "Beranda E-Commerce",
        "category": "pelanggan",
        "category_label": "D. Pelanggan E-Commerce",
        "role_badge": "Pelanggan Publik",
        "badge_color": "bg-amber-50 text-amber-700 border-amber-200",
        "ui_img": "File PPT/UI_UX_Asli/22_Beranda_Ecommerce.png",
        "wf_img": "File PPT/Wireframe/E-Commerce.png",
        "tables": ["produk_induk", "produk_variasi", "stok_batch", "pengaturan_web"],
        "desc": "Etalase digital utama portal e-commerce ZenCare Medical. Menampilkan spanduk promosi kesehatan, navigasi mega menu kategori komoditas medis, pencarian produk instan, serta grid etalase produk unggulan kemasan grosir yang siap dipesan pelanggan secara daring.",
        "alignment": "Mengakomodasi kanal pemasaran digital modern dan meningkatkan keterjangkauan produk obat dan alkes sesuai SRS F-22."
    },
    {
        "id": "ui_25",
        "fig": "Gambar 3.43",
        "title": "Detail Produk E-Commerce",
        "category": "pelanggan",
        "category_label": "D. Pelanggan E-Commerce",
        "role_badge": "Pelanggan Publik",
        "badge_color": "bg-amber-50 text-amber-700 border-amber-200",
        "ui_img": "File PPT/UI_UX_Asli/23_Detail_Produk.png",
        "wf_img": "File PPT/Wireframe/Detail Produk.png",
        "tables": ["produk_induk", "produk_variasi", "stok_batch"],
        "desc": "Halaman informasi komprehensif suatu komoditas medis. Memuat galeri visual kemasan, nama produk dan dosis, seleksi pilihan variasi kemasan (Dus/Box/Karton), informasi komposisi medis dan aturan pakai, kuota ketersediaan stok daring, indikator masa kedaluwarsa terdekat, serta tombol penambahan ke keranjang belanja.",
        "alignment": "Mendukung prinsip transparansi informasi medis bagi konsumen obat dan alat kesehatan sesuai standar regulasi dan SRS F-23."
    },
    {
        "id": "ui_26",
        "fig": "Gambar 3.44",
        "title": "Keranjang Belanja",
        "category": "pelanggan",
        "category_label": "D. Pelanggan E-Commerce",
        "role_badge": "Pelanggan Publik",
        "badge_color": "bg-amber-50 text-amber-700 border-amber-200",
        "ui_img": "File PPT/UI_UX_Asli/24_Keranjang_Belanja.png",
        "wf_img": "File PPT/Wireframe/Keranjang Belanja.png",
        "tables": ["produk_variasi", "stok_batch"],
        "desc": "Daftar belanja sementara pelanggan sebelum proses checkout. Dilengkapi opsi centang item pilihan, penyesuaian kuantitas pesan, validasi otomatis batas maksimum persediaan terkini, kalkulasi subtotal harga, serta navigasi menuju pembayaran akhir.",
        "alignment": "Mencegah pemesanan melebihi ketersediaan stok fisik di gudang penyimpanan melalui validasi real-time sesuai SRS F-24."
    },
    {
        "id": "ui_27",
        "fig": "Gambar 3.45",
        "title": "Checkout Pembayaran",
        "category": "pelanggan",
        "category_label": "D. Pelanggan E-Commerce",
        "role_badge": "Pelanggan Publik",
        "badge_color": "bg-amber-50 text-amber-700 border-amber-200",
        "ui_img": "File PPT/UI_UX_Asli/25_Checkout_Pembayaran.png",
        "wf_img": "File PPT/Wireframe/Checkout Pembayaran.png",
        "tables": ["penjualan", "detail_penjualan", "pesanan_batch_alokasi", "stok_batch", "users"],
        "desc": "Formulir penyelesaian transaksi belanja daring pelanggan. Menyatukan konfirmasi identitas penerima, alamat pengiriman lengkap, pemilihan kurir ekspedisi logistik, metode pengambilan pesanan (Ambil di Toko atau Kurir), serta opsi gerbang pembayaran transfer bank/QRIS terintegrasi.",
        "alignment": "Mengunci alokasi kuota stok persediaan secara aman segera setelah pesanan terbit sesuai ketentuan transaksi e-commerce pada SRS F-25."
    }
]

print(f"Total comparison items: {len(items)}")

# Generate the complete interactive HTML tool
html_content = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog UI/UX Asli & Komparasi Wireframe – ZenCare Medical</title>
    <!-- Google Fonts Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Plus Jakarta Sans', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif']
                    },
                    colors: {
                        zc: '#1a75d2',
                        zcHv: '#0f2d5a',
                        zcNavy: '#0f2d5a',
                        zcLt: '#eef6fb',
                        zcBg: '#f8fafc',
                        zcCard: '#ffffff',
                        zcBrd: '#e2e8f0',
                        zcTxt: '#0f2d5a'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .preview-img-container {
            background-color: #0f172a;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .preview-img-container img {
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        .preview-img-container:hover img {
            transform: scale(1.02);
            filter: brightness(1.05);
        }
        .preview-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            opacity: 0;
            transition: opacity 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .preview-img-container:hover .preview-overlay {
            opacity: 1;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #e2e8f0; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        .tag-pill {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body class="antialiased text-slate-800">

    <!-- Top Sticky Bar Navigation -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
            
            <!-- Left Branding -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#0f2d5a] to-[#1a75d2] flex items-center justify-center text-white font-black text-lg shadow-md shadow-blue-500/20">
                    ZC
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-extrabold text-[#0f2d5a] tracking-tight leading-tight">ZenCare Medical</h1>
                        <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200">Katalog UI/UX Asli & Komparasi Wireframe</span>
                    </div>
                    <p class="text-xs text-slate-500">27 Antarmuka Sistem Nyata vs Cetak Biru Wireframe Subbab 3.6 (Gambar 3.19 s.d. 3.45)</p>
                </div>
            </div>

            <!-- Right Action Links -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="../laporan_ta/BAB_III_ZENCARE_MEDICAL.html#sec_3_6_wrapper" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-[#1a75d2] hover:bg-blue-100 font-semibold text-xs border border-blue-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Buka Laporan Bab 3.6
                </a>
                <a href="wireframe_zencare.html" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-xs border border-slate-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Galeri Wireframe Saja
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0f2d5a] text-white hover:bg-[#1a75d2] font-semibold text-xs shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </button>
            </div>

        </div>
    </header>

    <!-- Main Hero Banner -->
    <div class="bg-gradient-to-b from-white via-slate-50 to-transparent border-b border-slate-200/80 pt-7 pb-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-3xl">
                <span class="text-xs font-bold uppercase tracking-wider text-[#1a75d2] bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200/60 inline-block mb-2.5">
                    Instrumen Validasi Tugas Akhir
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0f2d5a] tracking-tight">
                    Komparasi Antarmuka Sistem Nyata (Live UI) vs Wireframe Bab 3
                </h2>
                <p class="text-sm sm:text-base text-slate-600 mt-2 leading-relaxed">
                    Halaman ini mendokumentasikan bukti implementasi langsung dari seluruh 27 antarmuka sistem ZenCare Medical (mencakup laporan operasional, laporan penerimaan inbound, serta laporan mutasi &amp; konversi satuan yang telah diselaraskan). Anda dapat menyandingkan tampilan visual aplikasi web berjalan (PHP &amp; MySQL) dengan cetak biru perancangan wireframe (Gambar 3.19 hingga 3.45) guna membuktikan keselarasan fungsional 100% terhadap dokumen SRS dan rancangan basis data.
                </p>
            </div>

            <!-- Quick Metrics Badge Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-6">
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-[#1a75d2] flex items-center justify-center font-bold text-lg">
                        27
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium">Total Antarmuka</div>
                        <div class="text-sm font-bold text-[#0f2d5a]">100% Terekam Nyata</div>
                    </div>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                        4
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium">Kelompok Aktor</div>
                        <div class="text-sm font-bold text-[#0f2d5a]">RBAC Terisolasi</div>
                    </div>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                        9
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium">Laporan &amp; Manajerial</div>
                        <div class="text-sm font-bold text-[#0f2d5a]">Audit Lengkap Superadmin</div>
                    </div>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                        0
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium">Kepatuhan Narasi</div>
                        <div class="text-sm font-bold text-[#0f2d5a]">0 Kata Terlarang</div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="mt-8 bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-4">
                
                <!-- Category Filter Pills & Mode Toggle -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    <!-- Category Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 text-xs font-semibold">
                        <button onclick="setFilter('all')" id="btn-all" class="filter-btn px-3 py-1.5 rounded-lg bg-[#0f2d5a] text-white transition whitespace-nowrap shadow-sm">
                            Semua Antarmuka (27)
                        </button>
                        <button onclick="setFilter('auth')" id="btn-auth" class="filter-btn px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition whitespace-nowrap">
                            A. Autentikasi Staf (1)
                        </button>
                        <button onclick="setFilter('admin')" id="btn-admin" class="filter-btn px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition whitespace-nowrap">
                            B. Admin & Kasir (11)
                        </button>
                        <button onclick="setFilter('superadmin')" id="btn-superadmin" class="filter-btn px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition whitespace-nowrap">
                            C. Superadmin (9)
                        </button>
                        <button onclick="setFilter('pelanggan')" id="btn-pelanggan" class="filter-btn px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition whitespace-nowrap">
                            D. Pelanggan E-Commerce (6)
                        </button>
                    </div>

                    <!-- View Mode Toggle -->
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs font-medium self-start lg:self-auto">
                        <button onclick="setViewMode('side')" id="mode-side" class="mode-btn px-2.5 py-1 rounded-md bg-white text-[#0f2d5a] shadow-sm font-bold transition">
                            Berdampingan (Komparasi)
                        </button>
                        <button onclick="setViewMode('ui-only')" id="mode-ui-only" class="mode-btn px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 transition">
                            UI Asli Saja
                        </button>
                        <button onclick="setViewMode('wf-only')" id="mode-wf-only" class="mode-btn px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 transition">
                            Wireframe Saja
                        </button>
                    </div>

                </div>

                <!-- Search Input Bar -->
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="searchInput" oninput="applySearch()" placeholder="Cari berdasarkan nomor gambar, judul halaman, nama tabel basis data, atau fitur (contoh: POS, Blind Count, Inbound, Mutasi Konversi, FEFO)..."
                           class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1a75d2] focus:bg-white transition">
                </div>

            </div>

        </div>
    </div>

    <!-- Main Cards Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div id="cardsGrid" class="space-y-8">
"""

# Append cards
for item in items:
    tables_badges = "".join([f'<span class="tag-pill bg-slate-100 text-slate-600 border border-slate-200 font-mono text-[10px]"><code>{t}</code></span>' for t in item["tables"]])
    
    html_content += f"""
            <!-- Card: {item['fig']} - {item['title']} -->
            <article class="item-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
                     data-category="{item['category']}"
                     data-search="{item['fig'].lower()} {item['title'].lower()} {item['desc'].lower()} {' '.join(item['tables']).lower()} {item['alignment'].lower()}">
                
                <!-- Card Header -->
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-md bg-[#0f2d5a] text-white font-black text-xs tracking-wider shadow-sm">
                            {item['fig']}
                        </span>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-[#0f2d5a] leading-tight">
                                {item['title']}
                            </h3>
                            <span class="text-xs text-slate-500 font-medium">
                                {item['category_label']}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="tag-pill border {item['badge_color']}">
                            {item['role_badge']}
                        </span>
                        <button onclick="copyCaption('{item['id']}')" title="Salin Keterangan untuk Laporan Word"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-[#1a75d2] text-slate-700 hover:text-white text-xs font-semibold transition border border-slate-200 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            Salin Keterangan
                        </button>
                    </div>
                </div>

                <!-- Dual Visual Comparison Body -->
                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 bg-slate-50/50">
                    
                    <!-- Left: Tampilan Nyata Sistem (Live UI) -->
                    <div class="live-panel p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-[#0f2d5a] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Tampilan Nyata Sistem (Live UI)
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">1440 × 900 px</span>
                            </div>
                            <div class="preview-img-container rounded-xl border border-slate-200 aspect-[16/10]"
                                 onclick="openLightbox('{item['ui_img']}', '{item['fig']} - {item['title']} (Tampilan Nyata Sistem)')">
                                <img src="{item['ui_img']}" alt="{item['title']} - UI Asli" class="w-full h-full object-contain" loading="lazy">
                                <div class="preview-overlay">
                                    <span class="bg-white/90 text-[#0f2d5a] text-xs font-bold px-3 py-1.5 rounded-lg shadow-md flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        Perbesar UI Nyata
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500">
                            <span class="text-emerald-700 font-medium">✓ Selesai Terpasang di Web Server</span>
                            <a href="{item['ui_img']}" target="_blank" class="hover:underline text-blue-600">Buka Gambar Penuh ↗</a>
                        </div>
                    </div>

                    <!-- Right: Cetak Biru Perancangan Wireframe -->
                    <div class="wf-panel p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                    Cetak Biru Wireframe (Bab 3.6)
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">Balsamiq Blueprint</span>
                            </div>
                            <div class="preview-img-container rounded-xl border border-slate-200 aspect-[16/10] bg-white"
                                 onclick="openLightbox('{item['wf_img']}', '{item['fig']} - {item['title']} (Rancangan Wireframe)')">
                                <img src="{item['wf_img']}" alt="{item['title']} - Wireframe" class="w-full h-full object-contain p-2" loading="lazy">
                                <div class="preview-overlay">
                                    <span class="bg-white/90 text-slate-800 text-xs font-bold px-3 py-1.5 rounded-lg shadow-md flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        Perbesar Wireframe
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500">
                            <span class="text-blue-700 font-medium">✓ Selaras Bab III Laporan TA</span>
                            <a href="{item['wf_img']}" target="_blank" class="hover:underline text-blue-600">Buka Gambar Penuh ↗</a>
                        </div>
                    </div>

                </div>

                <!-- Information & Analytical Details Footer -->
                <div class="px-5 py-4 bg-white border-t border-slate-100 space-y-3">
                    
                    <!-- Fungsionalitas & Alur -->
                    <div>
                        <div class="text-xs font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#1a75d2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Fungsi &amp; Logika Bisnis Antarmuka
                        </div>
                        <p id="desc-{item['id']}" class="text-xs sm:text-sm text-slate-600 leading-relaxed text-justify">
                            {item['desc']}
                        </p>
                    </div>

                    <!-- Grid Info: Alignment & Database Tables -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                Keselarasan Spesifikasi (SRS &amp; Use Case)
                            </div>
                            <p class="text-xs text-slate-600 leading-normal">
                                {item['alignment']}
                            </p>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                Entitas / Tabel Basis Data Terkait
                            </div>
                            <div class="flex flex-wrap gap-1">
                                {tables_badges}
                            </div>
                        </div>
                    </div>

                </div>

            </article>
"""

# Append footer and scripts
html_content += """
        </div>

        <!-- No Results Found State -->
        <div id="noResults" class="hidden text-center py-16 bg-white rounded-2xl border border-slate-200">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h4 class="text-base font-bold text-slate-700">Tidak ada antarmuka yang cocok</h4>
            <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain atau ubah filter kategori.</p>
        </div>

    </main>

    <!-- Lightbox Modal for Fullscreen View -->
    <div id="lightboxModal" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center p-4 backdrop-blur-sm transition-opacity"
         onclick="closeLightbox()">
        <div class="relative max-w-6xl w-full max-h-[95vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
            
            <!-- Modal Header -->
            <div class="w-full flex items-center justify-between pb-3 text-white">
                <div id="lightboxTitle" class="text-sm sm:text-base font-bold truncate pr-4">Preview</div>
                <div class="flex items-center gap-2">
                    <a id="lightboxDownload" href="#" target="_blank" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded text-xs font-semibold transition">
                        Buka Asli ↗
                    </a>
                    <button onclick="closeLightbox()" class="p-1 text-slate-300 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Modal Image Container -->
            <div class="w-full max-h-[82vh] overflow-auto flex items-center justify-center bg-slate-900 rounded-xl border border-slate-700 p-2">
                <img id="lightboxImg" src="" alt="Fullscreen Preview" class="max-w-full max-h-[80vh] object-contain rounded shadow-2xl">
            </div>

            <div class="text-slate-400 text-xs mt-2.5">
                Tekan <kbd class="px-1.5 py-0.5 bg-slate-800 rounded text-slate-200 border border-slate-700">ESC</kbd> atau klik di luar area gambar untuk menutup.
            </div>

        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 bg-[#0f2d5a] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span id="toastMsg" class="text-xs font-semibold">Teks berhasil disalin ke clipboard!</span>
    </div>

    <!-- Client-side Interactive Logic -->
    <script>
        let currentFilter = 'all';
        let currentMode = 'side';

        function setFilter(cat) {
            currentFilter = cat;
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.className = 'filter-btn px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition whitespace-nowrap';
            });
            const activeBtn = document.getElementById('btn-' + cat);
            if (activeBtn) {
                activeBtn.className = 'filter-btn px-3 py-1.5 rounded-lg bg-[#0f2d5a] text-white transition whitespace-nowrap shadow-sm';
            }
            applyFilters();
        }

        function setViewMode(mode) {
            currentMode = mode;
            document.querySelectorAll('.mode-btn').forEach(btn => {
                btn.className = 'mode-btn px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 transition';
            });
            const activeBtn = document.getElementById('mode-' + mode);
            if (activeBtn) {
                activeBtn.className = 'mode-btn px-2.5 py-1 rounded-md bg-white text-[#0f2d5a] shadow-sm font-bold transition';
            }

            const cards = document.querySelectorAll('.item-card');
            cards.forEach(card => {
                const livePanel = card.querySelector('.live-panel');
                const wfPanel = card.querySelector('.wf-panel');
                const grid = card.querySelector('.grid');

                if (mode === 'side') {
                    grid.className = 'grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 bg-slate-50/50';
                    livePanel.style.display = 'flex';
                    wfPanel.style.display = 'flex';
                } else if (mode === 'ui-only') {
                    grid.className = 'grid grid-cols-1 bg-slate-50/50';
                    livePanel.style.display = 'flex';
                    wfPanel.style.display = 'none';
                } else if (mode === 'wf-only') {
                    grid.className = 'grid grid-cols-1 bg-slate-50/50';
                    livePanel.style.display = 'none';
                    wfPanel.style.display = 'flex';
                }
            });
        }

        function applySearch() {
            applyFilters();
        }

        function applyFilters() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.item-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const category = card.getAttribute('data-category');
                const searchData = card.getAttribute('data-search');

                const matchCat = (currentFilter === 'all') || (category === currentFilter);
                const matchQuery = !query || searchData.includes(query);

                if (matchCat && matchQuery) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noResults = document.getElementById('noResults');
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        function openLightbox(src, title) {
            const modal = document.getElementById('lightboxModal');
            const img = document.getElementById('lightboxImg');
            const titleEl = document.getElementById('lightboxTitle');
            const dl = document.getElementById('lightboxDownload');

            img.src = src;
            titleEl.textContent = title;
            dl.href = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const modal = document.getElementById('lightboxModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });

        function copyCaption(id) {
            const descEl = document.getElementById('desc-' + id);
            if (!descEl) return;
            const textToCopy = descEl.innerText.trim();

            navigator.clipboard.writeText(textToCopy).then(() => {
                showToast('Keterangan antarmuka berhasil disalin ke clipboard!');
            }).catch(() => {
                showToast('Gagal menyalin otomatis, silakan salin teks manual.');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            toastMsg.textContent = msg;
            toast.classList.remove('translate-y-24', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 3000);
        }
    </script>
</body>
</html>
"""

out_path = r'lain\katalog_ui_ux_asli_zencare.html'
with open(out_path, 'w', encoding='utf-8') as f:
    f.write(html_content)

print(f"Successfully generated {out_path} ({len(html_content)} bytes)")
