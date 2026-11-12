import json
import os

items = [
    # 1. Antarmuka Utama (27 Antarmuka)
    {
        "id": "ui_01",
        "fig": "Gambar 4.1",
        "title": "Halaman Login Staf",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Staf Internal",
        "badge_color": "bg-slate-100 text-slate-700 border-slate-300",
        "filename": "01_Login_Staf.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Gerbang autentikasi internal satu pintu bagi seluruh staf operasional dan manajerial (Superadmin, Admin, dan Kasir). Mengimplementasikan sistem sesi terpusat dengan pencocokan kata sandi terenkripsi hash serta pengalihan peran secara otomatis."
    },
    {
        "id": "ui_02",
        "fig": "Gambar 4.2",
        "title": "Dashboard Operasional Admin",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "02_Dashboard_Admin.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Pusat kendali operasional harian yang menyajikan kartu metrik ringkas produk aktif, penjualan harian, peringatan dini sisa persediaan minimum (buffer stock), notifikasi kedaluwarsa, grafik volume transaksi harian, dan tombol akses cepat kasir."
    },
    {
        "id": "ui_03",
        "fig": "Gambar 4.3",
        "title": "Terminal Kasir (Point of Sale / POS)",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Kasir & Admin",
        "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "filename": "03_POS_Kasir.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Terminal transaksi penjualan luring di gerai fisik dengan tata letak dua kolom. Kolom kiri menyajikan katalog produk dan pencarian barcode batch FEFO. Kolom kanan memuat rincian keranjang belanja, kalkulasi diskon, metode bayar, dan cetak struk."
    },
    {
        "id": "ui_04",
        "fig": "Gambar 4.4",
        "title": "Pengelolaan Transaksi Penjualan Omnichannel",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "04_Transaksi_Omnichannel.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Pusat manajemen faktur pesanan yang menyatukan riwayat penjualan dari toko fisik (POS) dan pesanan toko daring (E-Commerce). Dilengkapi penyaring saluran, pemantauan status pesanan, verifikasi pembayaran, serta cetak label resi kurir."
    },
    {
        "id": "ui_05",
        "fig": "Gambar 4.5",
        "title": "Pengelolaan Master Produk dan Katalog Medis",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "05_Master_Produk.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Direktori katalog induk untuk seluruh komoditas obat dan alat kesehatan. Menampilkan foto barang, SKU induk, pengelompokan komoditas, deskripsi spesifikasi, batasan hari peringatan kedaluwarsa, dan status ketersediaan publikasi."
    },
    {
        "id": "ui_06",
        "fig": "Gambar 4.6",
        "title": "Konfigurasi Satuan dan Multi-UOM",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "06_Satuan_UOM.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Pusat penetapan tingkatan unit pengukuran bertingkat (multi-UOM) yang mengonfigurasi hubungan matematis antara kemasan besar (Dus/Box) dengan satuan eceran (Strip/Botol/Pcs) beserta harga jual masing-masing tingkatan."
    },
    {
        "id": "ui_07",
        "fig": "Gambar 4.7",
        "title": "Master Data Rekanan Pemasok (Supplier)",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "07_Direktori_Supplier.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Direktori manajemen profil mitra distributor logistik resmi yang memasok produk obat dan alat kesehatan, memuat informasi nama perusahaan distributor, narahubung resmi, nomor telepon, alamat kantor, dan status kemitraan aktif."
    },
    {
        "id": "ui_08",
        "fig": "Gambar 4.8",
        "title": "Penerimaan Logistik dan Barang Masuk",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "08_Penerimaan_Barang.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Antarmuka pencatatan pasokan persediaan yang masuk dari distributor ke gudang. Dilengkapi masukan nomor faktur supplier, tanggal kedaluwarsa, nomor batch, nomor seri unik untuk alkes, serta pembaruan otomatis saldo persediaan."
    },
    {
        "id": "ui_09",
        "fig": "Gambar 4.9",
        "title": "Mutasi Stok Manual Persediaan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "09_Mutasi_Stok.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Antarmuka pengelolaan penyesuaian kuantitas persediaan di luar transaksi kasir, digunakan untuk mendokumentasikan penarikan barang rusak, pemusnahan barang kedaluwarsa, atau penyesuaian selisih sampling secara akuntabel."
    },
    {
        "id": "ui_10",
        "fig": "Gambar 4.10",
        "title": "Kartu Riwayat Mutasi Stok (Inventory Ledger)",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "10_Kartu_Stok.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Buku besar mutasi persediaan yang mencatat rekam jejak kronologis setiap pergerakan komoditas medis, memuat saldo awal, kuantitas masuk, kuantitas keluar, saldo akhir, nomor bukti referensi, dan kanal transaksi terkait."
    },
    {
        "id": "ui_11",
        "fig": "Gambar 4.11",
        "title": "Stok Opname dan Pemeriksaan Fisik Persediaan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "11_Stok_Opname.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Antarmuka verifikasi dan rekonsiliasi hasil pencacahan fisik persediaan di rak toko, dilengkapi tab penyaring teratur (Perlu Mutasi, Sesuai/Klop, Sudah Dimutasi, Semua Item), deteksi anomali selisih, dan eksekusi mutasi penyesuaian."
    },
    {
        "id": "ui_12",
        "fig": "Gambar 4.12",
        "title": "Konversi Satuan dan Pemecahan Kemasan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "12_Konversi_Satuan.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Modul operasional pemecahan kemasan besar (Dus/Box) menjadi kemasan eceran siap jual (Strip/Botol/Pcs) secara atomik, menjaga konsistensi nomor batch dan tanggal kedaluwarsa antar-kemasan secara otomatis."
    },
    {
        "id": "ui_13",
        "fig": "Gambar 4.13",
        "title": "Dashboard Eksekutif Manajerial",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "13_Dashboard_Superadmin.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Panel kendali pimpinan tertinggi (Pemilik Toko) yang menyajikan metrik makro finansial dan logistik: total valuasi aset persediaan, akumulasi pendapatan kotor dan bersih, perbandingan rasio penjualan daring vs luring, dan kendali sistem."
    },
    {
        "id": "ui_14",
        "fig": "Gambar 4.14",
        "title": "Laporan Penjualan Barang Omnichannel",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "14_Laporan_Penjualan.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Modul rekapitulasi penjualan berkala yang menggabungkan transaksi fisik kasir dan pesanan daring, dilengkapi penyaring rentang tanggal, grafik performa komoditas terlaris, serta ekspor format dokumen cetak resmi."
    },
    {
        "id": "ui_15",
        "fig": "Gambar 4.15",
        "title": "Laporan Persediaan dan Ketersediaan Barang",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "15_Laporan_Persediaan.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Panel valuasi dan posisi persediaan yang memetakan seluruh komoditas aktif di gerai fisik dan gudang, menyajikan saldo fisik siap jual, saldo teralokasi pesanan daring (reserved), dan estimasi total aset persediaan."
    },
    {
        "id": "ui_16",
        "fig": "Gambar 4.16",
        "title": "Laporan Logistik Medis (Pemantauan Kedaluwarsa FEFO)",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "16_Laporan_Logistik_Medis.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Modul pengawasan masa simpan obat yang mengelompokkan persediaan ke dalam zona urgensi (Merah: kedaluwarsa, Kuning: mendekati kedaluwarsa, Hijau: aman) guna mendukung prioritas pengeluaran berbasis FEFO."
    },
    {
        "id": "ui_17",
        "fig": "Gambar 4.17",
        "title": "Laporan Penerimaan Pasokan Barang Masuk",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "17_Laporan_Penerimaan_Barang.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Modul pelaporan verifikasi pasokan logistik masuk (inbound) yang merekapitulasi seluruh kiriman barang dari distributor, memuat nomor surat jalan supplier, rincian batch/SN, dan total nilai perolehan barang."
    },
    {
        "id": "ui_18",
        "fig": "Gambar 4.18",
        "title": "Laporan Mutasi Stok dan Konversi Satuan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "18_Laporan_Mutasi_Konversi.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Modul rekapitulasi pencatatan pergerakan persediaan non-penjualan yang menyatukan mutasi manual (barang rusak, kedaluwarsa, penyesuaian opname) dan histori aktivitas pemecahan satuan kemasan bertingkat."
    },
    {
        "id": "ui_19",
        "fig": "Gambar 4.19",
        "title": "Laporan Deteksi Anomali Pengeluaran FEFO",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "17_Laporan_Anomali_FEFO.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Panel pengawasan kepatuhan protokol logistik yang mendeteksi setiap kejadian di mana kasir mengeluarkan batch obat bertanggal kedaluwarsa lebih jauh padahal masih terdapat batch ber-kedaluwarsa lebih dekat di rak toko."
    },
    {
        "id": "ui_20",
        "fig": "Gambar 4.20",
        "title": "Manajemen Pengguna dan Penetapan Hak Akses",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "18_Manajemen_Pengguna.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Panel kendali hak akses berbasis peran (RBAC) untuk pembuatan akun baru, pembaruan kata sandi, dan pengelolaan status aktif/nonaktif bagi peran Superadmin, Admin, Kasir, dan Pelanggan."
    },
    {
        "id": "ui_21",
        "fig": "Gambar 4.21",
        "title": "Pengaturan Toko Daring dan Konten Web",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "19_Pengaturan_Web.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Panel kustomisasi identitas gerai dan kanal e-commerce, mencakup pengaturan nama toko, alamat operasional gerai, koordinat peta digital, nomor kontak WhatsApp, ketentuan layanan, dan spanduk promosi."
    },
    {
        "id": "ui_22",
        "fig": "Gambar 4.22",
        "title": "Pendaftaran dan Masuk Akun Pelanggan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Pelanggan",
        "badge_color": "bg-teal-50 text-teal-700 border-teal-200",
        "filename": "20_Login_Pelanggan.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Gerbang autentikasi khusus konsumen toko online dengan tata letak dua tab terintegrasi (Masuk dan Registrasi Akun Baru), dilengkapi validasi alamat email unik dan perlindungan kata sandi aman."
    },
    {
        "id": "ui_23",
        "fig": "Gambar 4.23",
        "title": "Profil dan Pemantauan Pesanan Pelanggan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Pelanggan",
        "badge_color": "bg-teal-50 text-teal-700 border-teal-200",
        "filename": "21_Profil_Pelanggan.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Pusat informasi akun pelanggan yang memuat riwayat seluruh pesanan daring, pelacakan tahapan pengiriman pesanan secara langsung (real-time tracking), pengelolaan buku alamat pengiriman, dan pembaruan profil."
    },
    {
        "id": "ui_24",
        "fig": "Gambar 4.24",
        "title": "Beranda Toko Daring (E-Commerce)",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Pelanggan",
        "badge_color": "bg-teal-50 text-teal-700 border-teal-200",
        "filename": "22_Beranda_Ecommerce.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Halaman etalase belanja digital pelanggan yang menyajikan katalog produk obat dan alkes interaktif, navigasi kategori cepat, bilah pencarian cerdas, spanduk pengumuman layanan, dan kartu produk informatif."
    },
    {
        "id": "ui_25",
        "fig": "Gambar 4.25",
        "title": "Rincian Informasi dan Spesifikasi Produk",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Pelanggan",
        "badge_color": "bg-teal-50 text-teal-700 border-teal-200",
        "filename": "23_Detail_Produk.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Halaman deskripsi komprehensif produk medis yang menyajikan galeri foto resolusi tinggi, pilihan variasi kemasan, status ketersediaan stok fisik siap kirim, indikasi medis/spesifikasi teknis, dan tombol tambah ke keranjang."
    },
    {
        "id": "ui_26",
        "fig": "Gambar 4.26",
        "title": "Panel Keranjang Belanja Pelanggan",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Pelanggan",
        "badge_color": "bg-teal-50 text-teal-700 border-teal-200",
        "filename": "24_Keranjang_Belanja.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Antarmuka tinjauan barang belanjaan sebelum checkout, memfasilitasi pengubahan kuantitas beli dengan validasi ketersediaan stok sistem, penghapusan item, kalkulasi otomatis subtotal, dan tombol lanjut pembayaran."
    },
    {
        "id": "ui_27",
        "fig": "Gambar 4.27",
        "title": "Pembayaran dan Pengiriman Pesanan (Checkout)",
        "category": "utama",
        "category_label": "Antarmuka Utama",
        "role_badge": "Pelanggan",
        "badge_color": "bg-teal-50 text-teal-700 border-teal-200",
        "filename": "25_Checkout_Pembayaran.png",
        "folder": "01_Antarmuka_Utama",
        "desc": "Formulir penyelesaian transaksi belanja daring pelanggan, memadukan pemilihan metode penyerahan (Kurir Ekspedisi atau Ambil di Toko), penentuan titik lokasi koordinat peta, kalkulasi ongkos kirim, dan gerbang pembayaran."
    },

    # 2. Formulir Logistik & Master (3 Formulir)
    {
        "id": "ui_28",
        "fig": "Gambar 4.28",
        "title": "Formulir Input Master Produk Baru",
        "category": "formulir",
        "category_label": "Formulir Logistik & Master",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "34_Form_Input_Master_Produk.png",
        "folder": "02_Formulir_Logistik",
        "desc": "Jendela sembulan (modal dialog) pendaftaran komoditas produk induk baru, meliputi kode produk, klasifikasi kelompok barang (Obat atau Alkes), batas hari peringatan kedaluwarsa, dan penetapan satuan kemasan bertingkat multi-UOM."
    },
    {
        "id": "ui_29",
        "fig": "Gambar 4.29",
        "title": "Formulir Eksekusi Konversi Satuan Multi-UOM",
        "category": "formulir",
        "category_label": "Formulir Logistik & Master",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "35_Form_Konversi_UOM.png",
        "folder": "02_Formulir_Logistik",
        "desc": "Formulir kerja staf untuk pemecahan kemasan besar ke kemasan eceran siap jual di gerai fisik. Menampilkan rasio konversi baku, saldo fisik dus yang tersedia, kalkulasi estimasi unit eceran, dan eksekusi atomik mutasi stok."
    },
    {
        "id": "ui_30",
        "fig": "Gambar 4.30",
        "title": "Formulir Penyesuaian Mutasi Stok Manual",
        "category": "formulir",
        "category_label": "Formulir Logistik & Master",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "36_Form_Mutasi_Stok.png",
        "folder": "02_Formulir_Logistik",
        "desc": "Formulir penyesuaian kuantitas persediaan di luar transaksi kasir untuk mencatat barang rusak, pemusnahan kedaluwarsa, atau penyesuaian sampling berkala dengan pencatatan alasan resmi dan nomor bukti referensi."
    },

    # 3. Stok Opname Multi-Tahap (4 Tahap)
    {
        "id": "ui_31",
        "fig": "Gambar 4.31",
        "title": "Tahap Inisiasi Sesi & Pembekuan Server",
        "category": "opname",
        "category_label": "Stok Opname & Pemeriksaan Fisik",
        "role_badge": "Superadmin",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "30_Stok_Opname_Mulai_Sesi.png",
        "folder": "03_Stok_Opname",
        "desc": "Jendela konfirmasi pembekuan operasional terpusat (Server Freeze) oleh Superadmin. Mengunci kasir POS offline, menonaktifkan checkout e-commerce daring, dan membekukan konversi UOM guna mencegah selisih semu selama pemeriksaan fisik."
    },
    {
        "id": "ui_32",
        "fig": "Gambar 4.32",
        "title": "Tahap Pencacahan Fisik Rak Metode Hitung Buta",
        "category": "opname",
        "category_label": "Stok Opname & Pemeriksaan Fisik",
        "role_badge": "Kasir Lapangan",
        "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "filename": "31_Stok_Opname_Blind_Count_Kasir.png",
        "folder": "03_Stok_Opname",
        "desc": "Lembar kerja pencacahan fisik kasir tanpa kolom saldo buku sistem (strict blind count). Menyajikan 3 kolom fisik (Baik, Rusak, Kedaluwarsa) dengan penguncian otomatis kolom kedaluwarsa untuk batch yang belum lewat masa simpannya."
    },
    {
        "id": "ui_33",
        "fig": "Gambar 4.33",
        "title": "Tahap Verifikasi dan Rekonsiliasi Selisih Fisik",
        "category": "opname",
        "category_label": "Stok Opname & Pemeriksaan Fisik",
        "role_badge": "Admin Toko",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "32_Stok_Opname_Verifikasi_Admin.png",
        "folder": "03_Stok_Opname",
        "desc": "Antarmuka verifikasi hasil hitung fisik dengan tab navigasi bersih (Perlu Mutasi, Sesuai/Klop, Sudah Dimutasi, Semua Item), penyaringan otomatis barang cocok, dan tombol mutasi penyesuaian selisih secara langsung."
    },
    {
        "id": "ui_34",
        "fig": "Gambar 4.34",
        "title": "Dokumen Resmi Berita Acara Hasil Pemeriksaan Fisik",
        "category": "opname",
        "category_label": "Stok Opname & Pemeriksaan Fisik",
        "role_badge": "Superadmin & Admin",
        "badge_color": "bg-slate-100 text-slate-800 border-slate-300",
        "filename": "33_Cetak_Berita_Acara_Opname.png",
        "folder": "03_Stok_Opname",
        "desc": "Dokumen resmi cetak A4 Lanskap 10 kolom dengan kop surat resmi ZenCare Medical, penyorotan selisih fisik bergaris batas merah tebal, rekapitulasi estimasi nilai kerugian persediaan, dan 3 kolom tanda tangan pengesahan berjenjang."
    },

    # 4. Dokumen Transaksi & Fisik (4 Dokumen)
    {
        "id": "ui_35",
        "fig": "Gambar 4.35",
        "title": "Struk Transaksi Kasir POS Slip Termal",
        "category": "dokumen",
        "category_label": "Dokumen Transaksi & Cetak",
        "role_badge": "Kasir Toko",
        "badge_color": "bg-emerald-50 text-emerald-700 border-emerald-200",
        "filename": "26_Cetak_Struk_Kasir.png",
        "folder": "04_Dokumen_Transaksi_Fisik",
        "desc": "Lembar bukti belanja konsumen gerai fisik format kertas printer termal 80mm/58mm. Mencantumkan identitas toko, nomor faktur POS, kasir bertugas, rincian obat/alkes beserta nomor batch FEFO & serial number, subtotal, bayar, dan kembalian."
    },
    {
        "id": "ui_36",
        "fig": "Gambar 4.36",
        "title": "Faktur Penjualan Resmi Toko ZenCare Medical Format A4",
        "category": "dokumen",
        "category_label": "Dokumen Transaksi & Cetak",
        "role_badge": "Admin Penjualan",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "27_Cetak_Invoice_Penjualan.png",
        "folder": "04_Dokumen_Transaksi_Fisik",
        "desc": "Dokumen tagihan dan bukti transaksi resmi berukuran standar A4 untuk pesanan grosir, institusi klinik, atau arsip keuangan toko. Dilengkapi kop surat resmi, identitas pembeli, rincian batch/ED, tarif ongkos kirim, dan kolom tanda tangan."
    },
    {
        "id": "ui_37",
        "fig": "Gambar 4.37",
        "title": "Label Pengiriman Kurir Logistik E-Commerce",
        "category": "dokumen",
        "category_label": "Dokumen Transaksi & Cetak",
        "role_badge": "Admin Logistik",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "28_Cetak_Label_Pengiriman.png",
        "folder": "04_Dokumen_Transaksi_Fisik",
        "desc": "Stiker pengiriman paket logistik ukuran 100mm x 150mm dilengkapi barcode resi pengiriman, kode QR pelacakan status pesanan daring konsumen, rincian alamat tujuan penerima, berat volumetrik paket, dan instruksi penanganan barang peka."
    },
    {
        "id": "ui_38",
        "fig": "Gambar 4.38",
        "title": "Nota Penerimaan Logistik dan Barang Masuk",
        "category": "dokumen",
        "category_label": "Dokumen Transaksi & Cetak",
        "role_badge": "Admin Gudang",
        "badge_color": "bg-blue-50 text-blue-700 border-blue-200",
        "filename": "29_Cetak_Nota_Penerimaan.png",
        "folder": "04_Dokumen_Transaksi_Fisik",
        "desc": "Dokumen serah terima fisik barang masuk dari distributor logistik ke gudang ZenCare Medical format A4. Memuat nomor bukti penerimaan, nomor surat jalan distributor, tanggal terima, rincian nomor batch/ED, dan paraf verifikasi segel barang."
    },

    # 5. Lembar Cetak Laporan Resmi (7 Laporan)
    {
        "id": "ui_39",
        "fig": "Gambar 4.39",
        "title": "Lembar Cetak Laporan Penjualan Barang Omnichannel",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "37_Cetak_Laporan_Penjualan.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak resmi rekapitulasi penjualan dua saluran (POS dan E-Commerce). Menyajikan total penerimaan kotor, diskon, omzet bersih, kuantitas produk terjual, metode bayar, dan rincian transaksi kronologis."
    },
    {
        "id": "ui_40",
        "fig": "Gambar 4.40",
        "title": "Lembar Cetak Laporan Persediaan dan Ketersediaan Barang",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "38_Cetak_Laporan_Persediaan.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak resmi posisi stok barang di gerai dan gudang. Menyajikan akumulasi kuantitas fisik, nilai perolehan persediaan, pemisahan saldo dus dan eceran, serta stok teralokasi pesanan daring (reserved stock)."
    },
    {
        "id": "ui_41",
        "fig": "Gambar 4.41",
        "title": "Lembar Cetak Laporan Kartu Riwayat Mutasi Stok",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "39_Cetak_Laporan_Kartu_Stok.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak buku riwayat mutasi persediaan (inventory ledger) suatu entitas barang dalam rentang periode. Menampilkan saldo awal, aliran mutasi masuk, aliran mutasi keluar, saldo akhir, nomor bukti referensi, dan paraf pengesahan."
    },
    {
        "id": "ui_42",
        "fig": "Gambar 4.42",
        "title": "Lembar Cetak Laporan Logistik Medis FEFO",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "40_Cetak_Laporan_Logistik_Medis.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak pemantauan masa simpan obat berbasis zona urgensi (Merah, Kuning, Hijau) guna menegakkan aturan bisnis FEFO (First Expired, First Out) dan mitigasi risiko kerugian obat kedaluwarsa di gerai fisik."
    },
    {
        "id": "ui_43",
        "fig": "Gambar 4.43",
        "title": "Lembar Cetak Laporan Penerimaan Pasokan Logistik",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "41_Cetak_Laporan_Penerimaan.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak rekapitulasi pasokan barang masuk dari distributor resmi ke toko. Memuat nomor faktur distributor, waktu terima, identitas staf penerima, rincian batch/ED obat, dan total nilai pembelian pasokan barang."
    },
    {
        "id": "ui_44",
        "fig": "Gambar 4.44",
        "title": "Lembar Cetak Laporan Mutasi Stok dan Konversi Satuan",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "42_Cetak_Laporan_Mutasi_Konversi.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak dokumentasi pergerakan persediaan non-penjualan di internal toko: penyesuaian manual (barang rusak, kedaluwarsa, selisih) dan rekapitulasi aktivitas konversi pemecahan satuan kemasan besar ke eceran."
    },
    {
        "id": "ui_45",
        "fig": "Gambar 4.45",
        "title": "Lembar Cetak Laporan Deteksi Anomali FEFO",
        "category": "laporan",
        "category_label": "Lembar Cetak Laporan",
        "role_badge": "Manajemen Toko",
        "badge_color": "bg-purple-50 text-purple-700 border-purple-200",
        "filename": "43_Cetak_Laporan_Anomali_FEFO.png",
        "folder": "05_Lembar_Cetak_Laporan",
        "desc": "Lembar cetak laporan pengawasan kepatuhan protokol FEFO yang merekam insiden pengeluaran nomor batch ber-kedaluwarsa lebih jauh di kasir, mencatat nomor faktur, kasir bertugas, batch yang diambil, dan alasan petugas."
    }
]

print(f"Total UI/UX items prepared: {len(items)}")

# Helper to generate the HTML
def generate_html(base_img_path, back_link, output_file):
    items_json = json.dumps(items, ensure_ascii=False)

    html = f"""<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Tangkapan Layar UI/UX Sistem - ZenCare Medical</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">
    <!-- JSZip & FileSaver for Batch Download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script>
        tailwind.config = {{
            theme: {{
                extend: {{
                    fontFamily: {{
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Times New Roman', 'Times', 'serif']
                    }},
                    colors: {{
                        zc: '#1a75d2',
                        zcHv: '#0f2d5a',
                        zcNavy: '#0f2d5a',
                        zcLt: '#eef6fb',
                        zcBrd: '#e2e8f0'
                    }}
                }}
            }}
        }}
    </script>
    <style>
        body {{
            background-color: #f8fafc;
            color: #0f172a;
        }}
        .card-preview-container {{
            background: #0f172a;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }}
        .card-preview-container img {{
            transition: transform 0.25s ease;
        }}
        .card-preview-container:hover img {{
            transform: scale(1.02);
        }}
    </style>
</head>
<body class="py-8 px-4 sm:px-6 lg:px-8 min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <div class="max-w-7xl mx-auto w-full mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 font-black text-sm shrink-0">
                    ZC
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                        Galeri Resmi Implementasi Sistem
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">Katalog Tangkapan Layar UI/UX Operasional</h1>
                    <p class="text-xs text-slate-500">Sistem Informasi Inventori Omnichannel ZenCare Medical &bull; Bab Hasil dan Pembahasan</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
                <a href="{back_link}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition border border-slate-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Bab IV
                </a>
                <button onclick="downloadAllZip()" id="btnZipAll" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Semua Gambar (.ZIP)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics & Control Bar -->
    <div class="max-w-7xl mx-auto w-full mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Gambar</div>
                <div class="text-2xl font-black text-slate-900 mt-1" id="statTotal">{len(items)} Berkas</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tangkapan Layar Resolusi Tinggi</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Antarmuka Utama</div>
                <div class="text-2xl font-black text-blue-600 mt-1">27 Gambar</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Selaras Rancangan Bab III</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Siklus Stok Opname</div>
                <div class="text-2xl font-black text-purple-600 mt-1">4 Gambar</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tahap 1 s.d. Berita Acara</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Dokumen &amp; Laporan</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">14 Gambar</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Struk, Faktur, Resi &amp; 7 Laporan</div>
            </div>
        </div>

        <!-- Filter & Search Panel -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto" id="filterPills">
                <button onclick="setFilter('semua')" data-filter="semua" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-900 text-white shadow-xs">
                    Semua ({len(items)})
                </button>
                <button onclick="setFilter('utama')" data-filter="utama" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    Antarmuka Utama (27)
                </button>
                <button onclick="setFilter('opname')" data-filter="opname" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    Stok Opname (4)
                </button>
                <button onclick="setFilter('dokumen')" data-filter="dokumen" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    Dokumen Fisik (4)
                </button>
                <button onclick="setFilter('formulir')" data-filter="formulir" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    Formulir Logistik (3)
                </button>
                <button onclick="setFilter('laporan')" data-filter="laporan" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    Cetak Laporan (7)
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full md:w-72">
                <input type="text" id="searchInput" oninput="handleSearch()" placeholder="Cari nama, gambar (misal: Gambar 4.3), atau peran..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="max-w-7xl mx-auto w-full flex-1">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="galleryGrid">
            <!-- Dynamic Cards Inserted by JS -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-16 bg-white rounded-2xl border border-slate-200 my-6">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center font-bold mb-3">?</div>
            <h3 class="text-sm font-bold text-slate-800">Tidak ada tangkapan layar yang cocok</h3>
            <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian lain atau pilih filter Semua.</p>
        </div>
    </div>

    <!-- Modal Preview Image Full Resolution -->
    <div id="previewModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <span id="modalFigBadge" class="px-2 py-0.5 rounded text-[11px] font-black bg-blue-100 text-blue-800 font-mono"></span>
                    <h3 id="modalTitle" class="text-sm font-bold text-slate-900"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <a id="modalDownloadBtn" href="" download="" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Unduh PNG</span>
                    </a>
                    <button onclick="closeModal()" class="w-8 h-8 rounded-lg hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-900 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-auto p-4 bg-slate-950 flex items-center justify-center">
                <img id="modalImg" src="" alt="Tampilan Penuh" class="max-w-full max-h-[72vh] object-contain mx-auto shadow-md rounded">
            </div>
            <div class="px-5 py-3 border-t border-slate-200 bg-white shrink-0 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
                <p id="modalDesc" class="font-serif leading-relaxed line-clamp-2 text-justify"></p>
                <button onclick="copyModalDesc()" class="shrink-0 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold border border-slate-300 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    <span>Salin Deskripsi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ZIP Progress Modal -->
    <div id="zipProgressModal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 shadow-2xl max-w-sm w-full text-center">
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-700 mx-auto flex items-center justify-center font-black mb-3">
                <svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 mb-1">Mengemas Seluruh Berkas Gambar</h3>
            <p class="text-xs text-slate-500 mb-4" id="zipProgressText">Memulai pengunduhan 45 gambar...</p>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                <div id="zipProgressBar" class="bg-blue-600 h-2 transition-all duration-200" style="width: 0%"></div>
            </div>
            <span class="text-[10px] text-slate-400 font-mono" id="zipProgressPercent">0%</span>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-lg border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span id="toastMsg">Tersalin!</span>
        </div>
    </div>

    <!-- Footer -->
    <footer class="max-w-7xl mx-auto w-full mt-8 pt-6 border-t border-slate-200 text-center text-xs text-slate-400">
        Katalog Dokumentasi UI/UX &bull; Sistem Informasi Inventori Omnichannel ZenCare Medical &bull; Marcell Chandra (322310015)
    </footer>

    <!-- Script Data & Logic -->
    <script>
        const BASE_IMG_PATH = "{base_img_path}";
        const rawItems = {items_json};
        let activeFilter = 'semua';
        let searchQuery = '';
        let currentModalItem = null;

        function renderGallery() {{
            const grid = document.getElementById('galleryGrid');
            const empty = document.getElementById('emptyState');
            grid.innerHTML = '';

            const filtered = rawItems.filter(item => {{
                const matchFilter = (activeFilter === 'semua') || (item.category === activeFilter);
                const q = searchQuery.toLowerCase().trim();
                const matchSearch = !q || 
                    item.title.toLowerCase().includes(q) || 
                    item.fig.toLowerCase().includes(q) || 
                    item.filename.toLowerCase().includes(q) || 
                    item.role_badge.toLowerCase().includes(q) ||
                    item.desc.toLowerCase().includes(q);
                return matchFilter && matchSearch;
            }});

            if (filtered.length === 0) {{
                empty.classList.remove('hidden');
            }} else {{
                empty.classList.add('hidden');
                filtered.forEach(item => {{
                    const fullPath = BASE_IMG_PATH + item.filename;
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition duration-200';
                    card.innerHTML = `
                        <!-- Preview Thumbnail -->
                        <div class="card-preview-container h-48 border-b border-slate-200 cursor-pointer" onclick="openModal('${{item.id}}')">
                            <img src="${{fullPath}}" alt="${{item.title}}" class="max-h-full max-w-full object-contain p-2" loading="lazy">
                            <div class="absolute inset-0 bg-slate-900/30 opacity-0 hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span>Klik untuk Memperbesar</span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-50 text-blue-800 border border-blue-200 font-mono">
                                        ${{item.fig}}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${{item.badge_color}}">
                                        ${{item.role_badge}}
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 leading-snug mb-1.5">
                                    ${{item.title}}
                                </h3>
                                <p class="text-[11px] text-slate-600 font-serif line-clamp-3 leading-relaxed mb-3 text-justify">
                                    ${{item.desc}}
                                </p>
                            </div>

                            <!-- Card Actions -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <div class="text-[10px] text-slate-400 font-mono truncate max-w-[140px]" title="${{item.filename}}">
                                    ${{item.filename}}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button onclick="copyItemDesc('${{item.id}}')" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition" title="Salin Deskripsi Naskah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    </button>
                                    <a href="${{fullPath}}" download="${{item.fig.replace(' ', '_')}}_${{item.filename}}" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-lg transition border border-blue-200 flex items-center gap-1 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Unduh</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.appendChild(card);
                }});
            }}
        }}

        function setFilter(category) {{
            activeFilter = category;
            document.querySelectorAll('.filter-btn').forEach(btn => {{
                if (btn.getAttribute('data-filter') === category) {{
                    btn.className = 'filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-900 text-white shadow-xs';
                }} else {{
                    btn.className = 'filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700';
                }}
            }});
            renderGallery();
        }}

        function handleSearch() {{
            searchQuery = document.getElementById('searchInput').value;
            renderGallery();
        }}

        function openModal(id) {{
            const item = rawItems.find(x => x.id === id);
            if (!item) return;
            currentModalItem = item;
            document.getElementById('modalFigBadge').innerText = item.fig;
            document.getElementById('modalTitle').innerText = item.title;
            const fullPath = BASE_IMG_PATH + item.filename;
            document.getElementById('modalImg').src = fullPath;
            document.getElementById('modalDownloadBtn').href = fullPath;
            document.getElementById('modalDownloadBtn').setAttribute('download', `${{item.fig.replace(' ', '_')}}_${{item.filename}}`);
            document.getElementById('modalDesc').innerText = item.desc;
            document.getElementById('previewModal').classList.remove('hidden');
        }}

        function closeModal() {{
            document.getElementById('previewModal').classList.add('hidden');
            currentModalItem = null;
        }}

        function copyItemDesc(id) {{
            const item = rawItems.find(x => x.id === id);
            if (!item) return;
            navigator.clipboard.writeText(item.desc).then(() => {{
                showToast(`Deskripsi ${{item.fig}} tersalin!`);
            }});
        }}

        function copyModalDesc() {{
            if (!currentModalItem) return;
            navigator.clipboard.writeText(currentModalItem.desc).then(() => {{
                showToast(`Deskripsi ${{currentModalItem.fig}} tersalin!`);
            }});
        }}

        function showToast(msg) {{
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            toastMsg.innerText = msg;
            toast.classList.remove('translate-y-24', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {{
                toast.classList.add('translate-y-24', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }}, 2500);
        }}

        // Batch Download as ZIP
        async function downloadAllZip() {{
            const modal = document.getElementById('zipProgressModal');
            const pText = document.getElementById('zipProgressText');
            const pBar = document.getElementById('zipProgressBar');
            const pPct = document.getElementById('zipProgressPercent');
            
            modal.classList.remove('hidden');
            pBar.style.width = '0%';
            pPct.innerText = '0%';

            try {{
                const zip = new JSZip();
                const total = rawItems.length;

                for (let i = 0; i < total; i++) {{
                    const it = rawItems[i];
                    pText.innerText = `Mengunduh (${{i+1}}/${{total}}): ${{it.filename}}...`;
                    const pct = Math.round(((i + 1) / total) * 100);
                    pBar.style.width = pct + '%';
                    pPct.innerText = pct + '%';

                    const fileUrl = BASE_IMG_PATH + it.filename;
                    const response = await fetch(fileUrl);
                    if (!response.ok) throw new Error(`Gagal mengunduh ${{it.filename}}`);
                    const blob = await response.blob();
                    
                    const zipPath = `${{it.folder}}/${{it.fig.replace(' ', '_')}}_${{it.filename}}`;
                    zip.file(zipPath, blob);
                }}

                pText.innerText = 'Mengompresi seluruh gambar ke arsip ZIP...';
                const content = await zip.generateAsync({{ type: 'blob' }});
                saveAs(content, 'Katalog_UI_UX_ZenCare_Medical.zip');
                showToast('Seluruh tangkapan layar berhasil diunduh dalam ZIP!');
            }} catch (err) {{
                alert('Terjadi kesalahan saat membuat file ZIP: ' + err.message);
            }} finally {{
                setTimeout(() => {{
                    modal.classList.add('hidden');
                }}, 800);
            }}
        }}

        // Close on ESC
        document.addEventListener('keydown', (e) => {{
            if (e.key === 'Escape') closeModal();
        }});

        // Initial render
        renderGallery();
    </script>
</body>
</html>
"""
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(html)
    print(f"Generated {output_file} successfully ({len(html)} bytes)")

# 1. Generate in laporan_ta/
generate_html("../lain/File PPT/UI_UX_Asli/", "BAB_IV_ZENCARE_MEDICAL.html", "laporan_ta/katalog_download_ui_ux_zencare.html")

# 2. Generate in lain/
generate_html("File PPT/UI_UX_Asli/", "../laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "lain/katalog_download_ui_ux_zencare.html")

