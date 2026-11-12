import re
import os
import zipfile
import json
from finalize_italicization import process_html_document

# Define all 26 sections and 50 figures
sections = [
    {
        "num": "4.1.1.1",
        "title": "Halaman Login Staf Internal",
        "figures": [
            ("Gambar 4.1", "Tampilan Antarmuka Halaman Login Staf", "01_Login_Staf.png", "Halaman Login Staf")
        ],
        "desc": "Halaman autentikasi utama satu pintu bagi seluruh staf internal toko kesehatan ZenCare Medical yang mencakup peran Superadmin, Admin, dan Kasir. Antarmuka menerapkan formulir masukan nama pengguna (username) dan kata sandi (password), identitas visual resmi ZenCare Medical, serta indikator verifikasi kredensial. Pada sisi backend, sistem memvalidasi kredensial terhadap tabel users menggunakan pencocokan hash terenkripsi. Berdasarkan nilai atribut peran (role), sistem secara otomatis mengarahkan alur kerja ke antarmuka yang tepat: Kasir diarahkan langsung menuju Terminal Kasir guna mempercepat transaksi penjualan luring di gerai fisik, Admin diarahkan ke dashboard operasional toko dan pengelolaan logistik, sedangkan Superadmin diarahkan ke dashboard eksekutif dan konfigurasi manajerial. Gerbang ini terproteksi ketat dari akses luar, di mana akun Pelanggan toko online dicegah masuk melalui formulir staf ini."
    },
    {
        "num": "4.1.1.2",
        "title": "Dashboard Operasional Admin Toko",
        "figures": [
            ("Gambar 4.2", "Tampilan Antarmuka Dashboard Operasional Admin", "02_Dashboard_Admin.png", "Dashboard Operasional Admin")
        ],
        "desc": "Pusat kendali operasional harian bagi staf Admin yang menyajikan ringkasan indikator kinerja toko fisik secara langsung. Antarmuka memuat kartu metrik ringkas yang menampilkan jumlah total produk aktif, total transaksi penjualan hari ini, peringatan jumlah persediaan barang yang menipis di bawah ambang batas minimum (buffer stock), serta notifikasi dini bagi komoditas yang mendekati batas waktu kedaluwarsa. Terdapat tombol akses cepat menuju terminal kasir (Point of Sale) dan formulir penerimaan logistik barang masuk. Antarmuka juga menyediakan grafik tren volume transaksi harian serta tabel rekapitulasi pesanan terbaru yang memerlukan pemrosesan segera. Seluruh metrik dihimpun secara dinamis dari tabel penjualan, produk_variasi, dan stok_batch."
    },
    {
        "num": "4.1.1.3",
        "title": "Terminal Kasir (Point of Sale / POS) dan Struk Pembayaran",
        "figures": [
            ("Gambar 4.3", "Tampilan Antarmuka Terminal Kasir (Point of Sale / POS)", "03_POS_Kasir.png", "Terminal Kasir POS"),
            ("Gambar 4.4", "Tampilan Cetak Struk Kasir POS Format Kertas Termal", "26_Cetak_Struk_Kasir.png", "Struk Kasir POS Termal")
        ],
        "desc": "Antarmuka pelayanan transaksi penjualan luring di gerai fisik toko yang dioperasikan oleh staf Kasir menggunakan tata letak dua panel terpadu guna mempercepat alur transaksi di meja kasir. Panel kiri menyajikan bilah pencarian obat dan alat kesehatan berbasis nama maupun pemindaian kode batang (barcode), penyaring kategori komoditas, serta kartu produk interaktif yang memuat informasi harga satuan eceran dan sisa stok fisik siap jual. Panel kanan memuat keranjang belanja, kalkulasi otomatis diskon, pilihan metode pembayaran tunai maupun non-tunai (QRIS/debit), perhitungan uang kembali, serta tombol penyelesaian transaksi.<br><br>Saat pembayaran disahkan, sistem secara otomatis mengeksekusi algoritma pengeluaran stok berbasis tanggal kedaluwarsa terdekat (FEFO) melalui alokasi tabel pesanan_batch_alokasi, mencatat nomor seri unit alat kesehatan pada unit_serial (jika barang bernomor seri), memperbarui saldo kartu_stok, serta mencetak lembar bukti belanja konsumen berupa Struk Kasir POS (Gambar 4.4). Struk dicetak menggunakan mesin pencetak kertas termal (thermal slip) ukuran 80mm atau 58mm, mencantumkan identitas resmi ZenCare Medical, nomor faktur POS yang unik, waktu transaksi, kasir yang bertugas, rincian produk medis beserta nomor batch dan serial number, harga satuan, subtotal, potongan harga, total bayar, nominal uang tunai, dan nominal kembalian."
    },
    {
        "num": "4.1.1.4",
        "title": "Pengelolaan Transaksi Penjualan Omnichannel, Faktur Resmi, dan Label Pengiriman Kurir",
        "figures": [
            ("Gambar 4.5", "Tampilan Antarmuka Pengelolaan Transaksi Penjualan Omnichannel", "04_Transaksi_Omnichannel.png", "Transaksi Omnichannel"),
            ("Gambar 4.6", "Tampilan Cetak Faktur Penjualan Resmi Toko ZenCare Medical Format A4", "27_Cetak_Invoice_Penjualan.png", "Faktur Penjualan Resmi A4"),
            ("Gambar 4.7", "Tampilan Cetak Label Pengiriman Kurir Logistik E-Commerce", "28_Cetak_Label_Pengiriman.png", "Label Pengiriman E-Commerce")
        ],
        "desc": "Pusat pengelolaan dan sinkronisasi seluruh pesanan pelanggan yang masuk secara serentak dari kanal kasir toko fisik (POS) maupun kanal belanja daring (E-Commerce). Antarmuka menyediakan penyaring status pemrosesan pesanan (Menunggu Pembayaran, Diproses Gudang, Siap Dikirim, dan Selesai), bilah pencarian nomor pesanan, serta tabel daftar transaksi komprehensif.<br><br>Untuk pesanan daring yang memerlukan pengiriman ekspedisi, sistem menyediakan fitur satu klik untuk mencetak Faktur Penjualan Resmi Toko format A4 (Gambar 4.6) dan Label Pengiriman Kurir Logistik (Gambar 4.7). Faktur penjualan diformat dalam standar lembar A4 dengan kop surat resmi ZenCare Medical, nomor faktur, identitas lengkap pembeli, daftar komoditas obat dan alkes beserta kode SKU, nomor batch, tanggal kedaluwarsa, harga satuan, tarif ongkos kirim, kalkulasi perpajakan bila berlaku, dan kolom pengesahan administrasi penjualan. Sementara itu, Label Pengiriman didesain presisi untuk ditempelkan pada kemasan paket logistik, memuat barcode nomor resi, identitas penerima dan nomor kontak, alamat tujuan lengkap, nama ekspedisi, serta instruksi penanganan paket barang medis rapuh/khusus."
    },
    {
        "num": "4.1.1.5",
        "title": "Pengelolaan Master Produk, Formulir Produk Induk, dan Konfigurasi Multi-UOM",
        "figures": [
            ("Gambar 4.8", "Tampilan Antarmuka Direktori Master Produk dan Katalog Medis", "05_Master_Produk.png", "Direktori Master Produk"),
            ("Gambar 4.9", "Tampilan Formulir Input Master Produk Induk Baru", "34_Form_Input_Master_Produk.png", "Form Input Master Produk Induk"),
            ("Gambar 4.10", "Tampilan Formulir Konfigurasi Produk Variasi dan Multi-UOM", "45_Form_Input_Produk_Variasi.png", "Form Konfigurasi Produk Variasi Multi-UOM")
        ],
        "desc": "Modul pengelolaan katalog terpadu untuk seluruh komoditas obat dan alat kesehatan yang diperjualbelikan pada ekosistem ZenCare Medical, mengadopsi arsitektur relasional bertingkat antara entitas produk_induk dan produk_variasi. Antarmuka direktori master produk (Gambar 4.8) menyajikan kartu katalog interaktif yang merinci foto fisik, kode SKU Induk, nama produk, klasifikasi medis, ambang batas hari peringatan kedaluwarsa, serta daftar variasi kemasan terdaftar.<br><br>Alur penambahan komoditas baru diawali dengan membuka Formulir Input Master Produk Induk Baru (Gambar 4.9), di mana staf mendefinisikan atribut induk mencakup SKU induk unik, nama resmi komoditas medis, kategori produk, batas waktu peringatan dini kedaluwarsa (dalam satuan hari), deskripsi indikasi atau spesifikasi teknis, serta unggahan berkas foto produk. Setelah produk induk tersimpan, staf melanjutkan konfigurasi satuan kemasan bertingkat melalui Formulir Konfigurasi Produk Variasi dan Multi-UOM (Gambar 4.10). Pada formulir variasi ini, sistem memfasilitasi penetapan SKU variasi spesifik, nama variasi kemasan, satuan kemasan kecil/eceran (seperti Strip, Botol, atau Pcs), satuan kemasan besar/grosir (seperti Box, Dus, atau Karton), rasio konversi matematis baku antar-satuan (misalnya 1 Box = 10 Strip), harga beli perolehan acuan, harga jual kemasan besar, harga jual eceran kemasan kecil, serta batas minimum persediaan (buffer stock) masing-masing tingkat satuan. Sistem memvalidasi integritas data masukan secara ketat guna memastikan keutuhan kalkulasi stok multi-satuan di seluruh modul operasional."
    },
    {
        "num": "4.1.1.6",
        "title": "Konfigurasi Satuan, Formulir Pemecahan Kemasan, dan Histori Konversi Multi-UOM",
        "figures": [
            ("Gambar 4.11", "Tampilan Antarmuka Konfigurasi Satuan dan Multi-UOM", "06_Satuan_UOM.png", "Konfigurasi Satuan UOM"),
            ("Gambar 4.12", "Tampilan Formulir Eksekusi Konversi Satuan Multi-UOM (Terisi Data)", "35_Form_Konversi_UOM.png", "Formulir Konversi UOM Terisi Data"),
            ("Gambar 4.13", "Tampilan Antarmuka Histori Konversi Satuan dan Pemecahan Kemasan", "12_Konversi_Satuan.png", "Histori Konversi Satuan")
        ],
        "desc": "Modul pengelolaan aturan satuan ukuran dan pelaksanaan pemecahan kemasan logistik di gerai fisik. Antarmuka Konfigurasi Satuan (Gambar 4.11) memuat tabel master satuan pengukuran (Dus, Box, Strip, Botol, Tablet, Tube, Pcs, Unit) yang menjadi fondasi penetapan multi-UOM. Untuk memenuhi permintaan konsumen eceran di toko fisik, staf operasional menggunakan Formulir Eksekusi Konversi Satuan (Gambar 4.12) guna memecah kemasan besar ke kemasan kecil. Seperti ditunjukkan pada formulir aktif yang telah terisi data uji riil, pengguna memilih komoditas obat (Amoxicillin 500mg), memilih batch asal (AMX-2026-01 dengan sisa saldo 4 Box), memasukkan jumlah dus yang dipecah (1 Box), serta melihat kalkulasi otomatis penambahan saldo eceran (+10 Strip) dan notifikasi penerbitan sub-batch baru.<br><br>Sistem mengeksekusi operasi konversi secara atomik: memotong saldo kemasan besar, menambahkan saldo kemasan kecil, mereplikasi nomor batch dan tanggal kedaluwarsa yang identik ke unit eceran, serta mencatat nomor bukti mutasi ke tabel konversi_satuan dan kartu_stok. Seluruh rekam jejak pemecahan kemasan tersaji secara kronologis pada Antarmuka Histori Konversi Satuan (Gambar 4.13), merinci waktu transaksi, nama produk, nomor batch, kuantitas kemasan asal yang dikurangi, serta kuantitas kemasan tujuan yang ditambahkan."
    },
    {
        "num": "4.1.1.7",
        "title": "Direktori Master Pemasok (Supplier) dan Formulir Pendaftaran Mitra Logistik",
        "figures": [
            ("Gambar 4.14", "Tampilan Antarmuka Direktori Rekanan Pemasok (Supplier)", "07_Direktori_Supplier.png", "Direktori Supplier"),
            ("Gambar 4.15", "Tampilan Formulir Pendaftaran Rekanan Pemasok Baru", "47_Form_Tambah_Supplier.png", "Form Tambah Supplier")
        ],
        "desc": "Modul pengelolaan data master rekanan pemasok resmi yang menjadi rantai pengadaan komoditas obat dan alat kesehatan bagi ZenCare Medical. Antarmuka Direktori Supplier (Gambar 4.14) mendokumentasikan profil rekanan distributor medis, nama penanggung jawab (sales representative), nomor kontak telepon, alamat kantor operasional, dan status kemitraan aktif.<br><br>Pendaftaran mitra logistik baru dilakukan melalui Formulir Pendaftaran Rekanan Pemasok Baru (Gambar 4.15). Formulir modal ini memfasilitasi perekaman legalitas distributor mencakup nama badan usaha distributor pasokan medis, narahubung resmi, saluran komunikasi WhatsApp/telepon, alamat surat elektronik, serta alamat kantor pusat dan pergudangan distributor."
    },
    {
        "num": "4.1.1.8",
        "title": "Pengelolaan Penerimaan Pasokan Logistik Barang Masuk, Nota Penerimaan, dan Label Barcode",
        "figures": [
            ("Gambar 4.16", "Tampilan Formulir Input Penerimaan Logistik Pasokan Barang Masuk", "08_Penerimaan_Barang.png", "Form Input Penerimaan Barang Masuk"),
            ("Gambar 4.17", "Tampilan Cetak Dokumen Nota Bukti Penerimaan Logistik Format A4", "29_Cetak_Nota_Penerimaan.png", "Nota Penerimaan Barang Masuk A4"),
            ("Gambar 4.18", "Tampilan Cetak Label Stiker Barcode Batch & Serial Number Gudang", "46_Cetak_Label_Stiker_Batch.png", "Cetak Label Barcode Stiker A4 Grid")
        ],
        "desc": "Modul penerimaan pasokan barang fisik dari distributor ke gerai fisik ZenCare Medical. Alur dimulai melalui Formulir Input Penerimaan Logistik Pasokan Barang Masuk (Gambar 4.16), di mana staf gudang memilih distributor rekanan pengirim, memasukkan nomor faktur distributor, mencatat tanggal kedaluwarsa (expired date), nomor batch pabrikan, kuantitas kemasan besar/kecil yang diterima, harga beli perolehan, dan nomor seri unik alat kesehatan.<br><br>Setelah data penerimaan diverifikasi dan disimpan, sistem secara otomatis membukukan mutasi masuk ke kartu stok dan mencetak Dokumen Nota Bukti Penerimaan Logistik Format A4 (Gambar 4.17) yang memuat nomor bukti internal, nomor faktur distributor, rincian produk, nomor batch/ED, serta kolom tanda tangan paraf verifikasi kondisi fisik segel kemasan oleh kepala logistik.<br><br>Selanjutnya, staf logistik mencetak label penandaan fisik barang melalui antarmuka Cetak Label Stiker Barcode Batch & Serial Number (Gambar 4.18) dalam format lembar stiker A4 (Grid 4x10) maupun kertas termal gulung 50x30mm. Label barcode Code 128 ini ditempelkan langsung pada kemasan produk medis guna mendukung pemindaian cepat di meja kasir POS dan pelacakan dan pemeriksaan rak gudang."
    },
    {
        "num": "4.1.1.9",
        "title": "Pengelolaan dan Formulir Mutasi Stok Manual Persediaan",
        "figures": [
            ("Gambar 4.19", "Tampilan Antarmuka Daftar Mutasi Stok Persediaan", "09_Mutasi_Stok.png", "Daftar Mutasi Stok"),
            ("Gambar 4.20", "Tampilan Formulir Penyesuaian Mutasi Stok Manual", "36_Form_Mutasi_Stok.png", "Formulir Mutasi Stok Manual")
        ],
        "desc": "Modul penyesuaian saldo persediaan secara langsung oleh staf berwenang di luar transaksi kasir maupun penerimaan barang. Antarmuka Daftar Mutasi Stok (Gambar 4.19) menyajikan tabel log kronologis penyesuaian saldo barang, memuat nomor referensi mutasi, waktu peristiwa, nama komoditas obat atau alkes, arah mutasi (Masuk atau Keluar), kuantitas, alasan kategorisasi, dan identitas staf pencatat.<br><br>Formulir Penyesuaian Mutasi Stok Manual (Gambar 4.20) digunakan saat terjadi kejadian operasional khusus seperti kerusakan kemasan di etalase, barang kedaluwarsa yang ditarik dari rak, pemakaian internal gerai, atau koreksi selisih administratif. Seperti ditunjukkan pada formulir yang telah terisi data uji riil, pengguna memilih produk variasi yang disesuaikan (misalnya: <em>Amoxicillin 500mg — Tablet 500mg</em>), memilih arah mutasi (<em>Pengurangan Stok (-)</em>), menentukan nomor angkatan batch terkait (<em>Batch AMX-2026-01</em>), memasukkan kuantitas (<em>2 Strip</em>), memilih alasan kategorisasi baku (<em>Barang Rusak / Kemasan Cacat</em>), serta menuliskan kronologi penyesuaian pada kolom catatan (<em>Penarikan fisik dari rak etalase depan karena kemasan strip penyok saat penataan</em>). Tindakan ini secara atomik memotong saldo fisik pada batch yang bersangkutan dan mencatatkan log mutasi manual secara akuntabel."
    },
    {
        "num": "4.1.1.10",
        "title": "Kartu Riwayat Mutasi Stok (Inventory Ledger) Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.21", "Tampilan Antarmuka Kartu Stok Persediaan (Inventory Ledger)", "10_Kartu_Stok.png", "Kartu Stok Web"),
            ("Gambar 4.22", "Tampilan Cetak Kartu Riwayat Mutasi Stok Barang Format A4", "39_Cetak_Laporan_Kartu_Stok.png", "Cetak Kartu Stok A4")
        ],
        "desc": "Buku besar persediaan (inventory ledger) komprehensif yang melacak mutasi setiap komoditas obat dan alat kesehatan secara mendalam. Antarmuka Web Kartu Stok (Gambar 4.21) menyajikan penyaring produk variasi dan rentang tanggal, ringkasan saldo fisik terkini, serta tabel mutasi yang merinci tanggal transaksi, nomor bukti referensi, jenis transaksi (Penjualan POS, Pesanan E-Commerce, Penerimaan Logistik, Konversi Kemasan, atau Mutasi Manual), kuantitas masuk, kuantitas keluar, dan saldo akhir berjalan.<br><br>Untuk keperluan pertanggungjawaban persediaan dan verifikasi administratif berkala, sistem menyediakan Cetak Kartu Riwayat Mutasi Stok Format A4 (Gambar 4.22). Dokumen cetak memuat kop surat resmi ZenCare Medical, informasi spesifikasi produk, saldo awal periode, tabel mutasi lengkap beserta kanal pergerakan dan identitas petugas yang melakukan pengesahan, serta kolom tanda tangan penanggung jawab logistik."
    },
    {
        "num": "4.1.1.11",
        "title": "Siklus Lengkap Pelaksanaan Stok Opname Terpusat (6 Antarmuka & Tahapan Multi-Aktor)",
        "figures": [
            ("Gambar 4.23", "Tampilan Antarmuka Beranda Riwayat Sesi Stok Opname", "11_Stok_Opname_Riwayat.png", "Beranda Riwayat Stok Opname"),
            ("Gambar 4.24", "Tampilan Antarmuka Tahap 1: Inisiasi Sesi dan Pembekuan Transaksi (Superadmin)", "30_Stok_Opname_Mulai_Sesi.png", "Tahap 1 Inisiasi Sesi"),
            ("Gambar 4.25", "Tampilan Antarmuka Tahap 2: Formulir Pencacahan Fisik Rak Blind Count (Kasir)", "31_Stok_Opname_Blind_Count_Kasir.png", "Tahap 2 Blind Count"),
            ("Gambar 4.26", "Tampilan Antarmuka Tahap 3: Verifikasi dan Rekonsiliasi Anomali Selisih (Admin)", "32_Stok_Opname_Verifikasi_Admin.png", "Tahap 3 Rekonsiliasi Admin"),
            ("Gambar 4.27", "Tampilan Antarmuka Tahap 3 (Lanjutan): Modal Penyesuaian Mutasi Selisih Per Item", "44_Modal_Mutasi_Opname.png", "Modal Mutasi Opname"),
            ("Gambar 4.28", "Tampilan Cetak Dokumen Berita Acara Pemeriksaan Fisik Stok Opname Format A4 Lanskap", "33_Cetak_Berita_Acara_Opname.png", "Cetak Berita Acara Opname")
        ],
        "desc": "Dokumentasi siklus utuh pemeriksaan fisik berkala persediaan persediaan (stok opname) yang dirancang untuk mencegah kecurangan dan memastikan kebenaran saldo buku terhadap kondisi fisik rak nyata melalui 6 (enam) tampilan antarmuka terpadu:<br><br><strong>1. Beranda Riwayat Sesi (Gambar 4.23):</strong> Menampilkan tabel histori seluruh sesi opname yang pernah dilaksanakan, status sesi (Sedang Dihitung, Tahap Verifikasi, atau Selesai), tanggal pelaksanaan, dan nilai acuan estimasi kerugian.<br><br><strong>2. Tahap 1: Inisiasi Sesi & Pembekuan Server (Gambar 4.24):</strong> Hak akses khusus Superadmin. Saat sesi baru dibuka, sistem mengaktifkan mekanisme Freeze Lock di tingkat server yang membekukan transaksi kasir POS, checkout toko daring, pemecahan kemasan konversi, dan penerimaan logistik guna mencegah terjadinya kondisi race condition.<br><br><strong>3. Tahap 2: Pencacahan Fisik Blind Count Kasir (Gambar 4.25):</strong> Kasir lapangan mencatat kuantitas fisik di rak tanpa dapat melihat angka saldo pembukuan sistem (Blind Count). Kasir hanya disajikan daftar barang dan 3 (tiga) kolom kondisi fisik riil: Fisik Baik, Fisik Rusak, dan Fisik Kedaluwarsa. Sistem menerapkan validasi otomatis: kolom 'Fisik Kedaluwarsa' dinonaktifkan untuk nomor batch yang belum melewati tanggal kedaluwarsa, sehingga barang cacat wajib dicatat sebagai 'Fisik Rusak'.<br><br><strong>4. Tahap 3: Verifikasi & Rekonsiliasi Selisih Admin (Gambar 4.26):</strong> Admin toko memverifikasi hasil hitungan fisik terhadap data buku sistem. Sistem secara otomatis menyembunyikan barang yang sesuai (klop) dan hanya menyoroti barang yang terdeteksi anomali selisih (misalnya: terdeteksi 6 komoditas berselisih fisik). Sistem menghitung estimasi nilai acuan kerugian persediaan dari kuantitas fisik yang tercatat Rusak, Expired, dan Hilang berdasarkan rumus baku: (Jumlah Rusak + Jumlah Expired + Jumlah Hilang) &times; Harga Beli (Satuan Terkecil).<br><br><strong>5. Tahap 3 (Lanjutan): Modal Eksekusi Mutasi Selisih (Gambar 4.27):</strong> Untuk setiap barang yang berselisih, Admin membuka kotak dialog mutasi penyesuaian (misalnya: penyesuaian barang hilang selisih -2 Strip Amoxicillin 500mg dengan beban kerugian Rp 8.000). Admin mengonfirmasi alasan penyesuaian, sistem melakukan mutasi atomik, dan menandai status baris menjadi 'Telah Dimutasi'.<br><br><strong>6. Tahap 4: Dokumen Berita Acara A4 Lanskap (Gambar 4.28):</strong> Setelah rekonsiliasi selesai, sistem menerbitkan Berita Acara Stok Opname resmi dalam tata letak A4 Lanskap 10 kolom data: No, Identitas Komoditas & Nama Produk, Tingkat Satuan (Kemasan Besar / Kemasan Kecil), Nomor Batch / Serial Number, Satuan, Stok Sistem, Hitung Fisik, Selisih, Nilai Kerugian, dan Tindakan Mutasi. Bagian bawah lembar memuat 3 (tiga) kolom tanda tangan pengesahan berjenjang: Petugas Pencacah Fisik (Kasir), Petugas Verifikator (Admin Toko), dan Pimpinan Toko (Superadmin)."
    },
    {
        "num": "4.1.1.12",
        "title": "Dashboard Eksekutif Superadmin",
        "figures": [
            ("Gambar 4.29", "Tampilan Antarmuka Dashboard Eksekutif Superadmin", "13_Dashboard_Superadmin.png", "Dashboard Superadmin")
        ],
        "desc": "Pusat kendali level tertinggi bagi pemilik toko (Superadmin) untuk memantau kesehatan bisnis dan integritas inventori ZenCare Medical secara menyeluruh. Antarmuka memuat widget metrik strategis: total pendapatan kotor dan bersih gabungan (POS luring dan e-commerce daring), total valuasi aset persediaan di gudang, ringkasan efektivitas FEFO, tombol kendali pembekuan server (Freeze Lock) darurat, serta grafik komparasi performa penjualan bulanan. Dashboard ini memberikan visibilitas penuh bagi pengambil keputusan strategis."
    },
    {
        "num": "4.1.1.13",
        "title": "Laporan Penjualan Barang Omnichannel Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.30", "Tampilan Antarmuka Laporan Penjualan Barang Omnichannel", "14_Laporan_Penjualan.png", "Laporan Penjualan Web"),
            ("Gambar 4.31", "Tampilan Cetak Dokumen Resmi Laporan Penjualan Barang Format A4", "37_Cetak_Laporan_Penjualan.png", "Cetak Laporan Penjualan A4")
        ],
        "desc": "Modul analitik pendapatan yang mengonsolidasikan transaksi dari gerai kasir fisik dan toko online. Antarmuka web (Gambar 4.30) dilengkapi penyaring rentang tanggal, filter kanal penjualan, grafik batang perbandingan harian, serta tabel transaksi terperinci.<br><br>Tombol cetak menghasilkan Laporan Penjualan Format A4 (Gambar 4.31) yang memuat kop surat resmi ZenCare Medical, ringkasan total omzet kotor, total potongan harga, total pendapatan bersih, kuantitas produk terjual, tabel rincian transaksi kronologis, serta kolom tanda tangan pengesahan pimpinan toko."
    },
    {
        "num": "4.1.1.14",
        "title": "Laporan Persediaan dan Ketersediaan Barang Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.32", "Tampilan Antarmuka Laporan Persediaan dan Ketersediaan Barang", "15_Laporan_Persediaan.png", "Laporan Persediaan Web"),
            ("Gambar 4.33", "Tampilan Cetak Dokumen Resmi Laporan Persediaan Barang Format A4", "38_Cetak_Laporan_Persediaan.png", "Cetak Laporan Persediaan A4")
        ],
        "desc": "Modul pelaporan status fisik barang di gudang dan etalase toko. Antarmuka web (Gambar 4.32) menampilkan kartu ringkasan total jenis barang, total nilai aset persediaan berdasarkan harga beli, filter status stok (Aman, Menipis, Habis), serta tabel rincian per produk variasi.<br><br>Lembar Cetak Laporan Persediaan Format A4 (Gambar 4.33) menyajikan dokumen resmi rekapitulasi persediaan barang, merinci kode barang, nama produk, satuan kemasan, saldo fisik, harga pokok perolehan, total nilai aset, serta kolom tanda tangan pengesahan kepala logistik."
    },
    {
        "num": "4.1.1.15",
        "title": "Laporan Logistik Medis FEFO Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.34", "Tampilan Antarmuka Laporan Logistik Medis dan Pengendalian Kedaluwarsa", "16_Laporan_Logistik_Medis.png", "Laporan Logistik Medis Web"),
            ("Gambar 4.35", "Tampilan Cetak Dokumen Resmi Laporan Logistik Medis FEFO Format A4", "40_Cetak_Laporan_Logistik_Medis.png", "Cetak Laporan Logistik Medis A4")
        ],
        "desc": "Modul pengawasan masa simpan obat dan alat kesehatan berbasis algoritma First Expired First Out. Antarmuka web (Gambar 4.34) mengelompokkan barang ke dalam 3 (tiga) tab visual: Kedaluwarsa Kritis (&le; 30 hari), Peringatan Dini (&le; 90 hari), dan Aman (&gt; 90 hari), dilengkapi indikator sisa hari masa simpan.<br><br>Lembar Cetak Laporan Logistik Medis FEFO Format A4 (Gambar 4.35) menyajikan dokumen resmi evaluasi masa kedaluwarsa, memuat nama produk medis, nomor batch pabrikan, tanggal kedaluwarsa, jumlah stok tersisa, status risiko, rekomendasi tindakan (retur ke distributor atau diskon cuci gudang), serta kolom tanda tangan pengesahan penanggung jawab teknis gerai."
    },
    {
        "num": "4.1.1.16",
        "title": "Laporan Penerimaan Pasokan Barang Masuk Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.36", "Tampilan Antarmuka Laporan Penerimaan Pasokan Barang Masuk", "17_Laporan_Penerimaan_Barang.png", "Laporan Penerimaan Web"),
            ("Gambar 4.37", "Tampilan Cetak Dokumen Resmi Laporan Penerimaan Barang Format A4", "41_Cetak_Laporan_Penerimaan.png", "Cetak Laporan Penerimaan A4")
        ],
        "desc": "Modul rekapitulasi pengadaan barang dari distributor rekanan. Antarmuka web (Gambar 4.36) memuat penyaring tanggal dan distributor, ringkasan total nilai pasokan yang diterima dalam periode tertentu, serta tabel riwayat penerimaan barang.<br><br>Lembar Cetak Laporan Penerimaan Barang Masuk Format A4 (Gambar 4.37) menyajikan dokumen resmi rekapitulasi pasokan masuk, memuat nomor faktur distributor, waktu penerimaan di gudang, nama staf penerima, rincian produk yang dipasok, nomor batch/ED, harga beli perolehan, dan kolom tanda tangan pengesahan bagian pengadaan logistik."
    },
    {
        "num": "4.1.1.17",
        "title": "Laporan Mutasi Stok & Konversi Satuan Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.38", "Tampilan Antarmuka Laporan Mutasi Stok dan Konversi Satuan", "18_Laporan_Mutasi_Konversi.png", "Laporan Mutasi Konversi Web"),
            ("Gambar 4.39", "Tampilan Cetak Dokumen Resmi Laporan Mutasi & Konversi Format A4", "42_Cetak_Laporan_Mutasi_Konversi.png", "Cetak Laporan Mutasi Konversi A4")
        ],
        "desc": "Modul evaluasi pergerakan internal barang, mencakup penyesuaian manual (barang rusak, kedaluwarsa, koreksi hitung) dan pemecahan kemasan multi-UOM. Antarmuka web (Gambar 4.38) menyajikan tab pemisah antara mutasi manual dan konversi satuan, filter rentang waktu, serta rincian alasan perubahan saldo.<br><br>Lembar Cetak Laporan Mutasi & Konversi Format A4 (Gambar 4.39) mencetak dokumen resmi rekapitulasi pergerakan internal, merinci tanggal peristiwa, kode barang, nama produk, kuantitas mutasi, alasan pembebanan, rasio konversi yang diterapkan, identitas petugas pencatat, serta kolom tanda tangan pimpinan."
    },
    {
        "num": "4.1.1.18",
        "title": "Laporan Deteksi Anomali Pengeluaran FEFO Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.40", "Tampilan Antarmuka Laporan Deteksi Anomali Pengeluaran FEFO", "17_Laporan_Anomali_FEFO.png", "Laporan Anomali FEFO Web"),
            ("Gambar 4.41", "Tampilan Cetak Dokumen Resmi Laporan Deteksi Anomali FEFO Format A4", "43_Cetak_Laporan_Anomali_FEFO.png", "Cetak Laporan Anomali FEFO A4")
        ],
        "desc": "Fitur pengawasan mutakhir yang memantau kepatuhan kasir terhadap rekomendasi batch terdekat (FEFO). Jika kasir mengeluarkan batch yang tanggal kedaluwarsanya lebih jauh sementara batch terdekat masih tersedia, sistem mencatat peristiwa tersebut sebagai anomali. Antarmuka web (Gambar 4.40) menyajikan daftar pelanggaran FEFO, alasan petugas saat override di kasir, dan tingkat kepatuhan.<br><br>Lembar Cetak Laporan Deteksi Anomali FEFO Format A4 (Gambar 4.41) mencetak lembar evaluasi resmi yang merinci nomor transaksi, kasir yang bertugas, nama obat yang mengalami loncatan angkatan, nomor batch yang seharusnya dikeluarkan menurut aturan FEFO, nomor batch yang diambil di lapangan, alasan petugas (misalnya permintaan kemasan khusus konsumen), serta kolom tanda tangan evaluasi oleh penanggung jawab teknis gerai."
    },
    {
        "num": "4.1.1.19",
        "title": "Manajemen Pengguna dan Penetapan Hak Akses (RBAC)",
        "figures": [
            ("Gambar 4.42", "Tampilan Antarmuka Manajemen Pengguna dan Hak Akses", "18_Manajemen_Pengguna.png", "Manajemen Pengguna"),
            ("Gambar 4.43", "Tampilan Formulir Pendaftaran Pengguna Baru dan Penetapan Hak Akses Role RBAC", "48_Form_Tambah_User.png", "Form Tambah Pengguna RBAC")
        ],
        "desc": "Modul tata kelola akun staf internal dan pengguna eksternal sistem ZenCare Medical. Antarmuka direktori pengguna (Gambar 4.42) menyajikan tabel seluruh akun pengguna terdaftar yang mencakup peran Superadmin, Admin, Kasir, dan Pelanggan, status keaktifan akun, serta tanggal registrasi.<br><br>Penambahan akun operasional baru dilakukan melalui Formulir Pendaftaran Pengguna Baru dan Penetapan Hak Akses Role RBAC (Gambar 4.43). Formulir ini memfasilitasi perekaman nama pengguna (username unik), nama lengkap staf, alamat email dinas, kata sandi terenkripsi, serta penetapan hak akses peran berbasis Role-Based Access Control (RBAC). Mekanisme ini memastikan isolasi wewenang yang ketat: Kasir hanya diizinkan mengakses terminal penjualan, Admin berwenang mengelola persediaan dan logistik, sedangkan Superadmin memegang kendali penuh terhadap konfigurasi sistem, pembekuan server, dan persetujuan penyesuaian inventori."
    },
    {
        "num": "4.1.1.20",
        "title": "Pengaturan Toko Daring dan Konten Web",
        "figures": [
            ("Gambar 4.44", "Tampilan Antarmuka Pengaturan Toko Daring dan Konten Web", "19_Pengaturan_Web.png", "Pengaturan Web")
        ],
        "desc": "Modul konfigurasi identitas visual dan operasional toko online ZenCare Medical. Admin dapat mengubah nama toko, logo resmi, favicon peramban, banner promosi karusel beranda, nomor kontak WhatsApp layanan pelanggan, alamat email, serta syarat dan ketentuan transaksi. Perubahan pada halaman ini langsung terefleksi secara langsung pada etalase toko daring yang diakses oleh pelanggan publik."
    },
    {
        "num": "4.1.1.21",
        "title": "Pendaftaran dan Masuk Akun Pelanggan",
        "figures": [
            ("Gambar 4.45", "Tampilan Antarmuka Pendaftaran dan Masuk Akun Pelanggan Toko Online", "20_Login_Pelanggan.png", "Login Pelanggan")
        ],
        "desc": "Gerbang autentikasi khusus konsumen toko daring ZenCare Medical yang terpisah dari halaman login staf internal. Antarmuka menyediakan formulir pendaftaran akun baru (nama lengkap, nomor telepon, alamat email, kata sandi) serta formulir masuk akun terdaftar. Sistem menerapkan validasi kata sandi dan proteksi sesi guna melindungi privasi riwayat pesanan serta data profil medis pelanggan."
    },
    {
        "num": "4.1.1.22",
        "title": "Profil dan Pemantauan Pesanan Pelanggan",
        "figures": [
            ("Gambar 4.46", "Tampilan Antarmuka Profil dan Pemantauan Pesanan Pelanggan", "21_Profil_Pelanggan.png", "Profil Pelanggan")
        ],
        "desc": "Portal mandiri pelanggan yang menampilkan informasi biodata pengguna, alamat pengiriman default, serta riwayat seluruh transaksi daring yang pernah dilakukan. Pelanggan dapat memantau status pesanan (Menunggu Pembayaran, Dikemas, Dikirim, Selesai), melacak nomor resi kurir, serta melihat kembali rincian komoditas medis yang dibeli secara transparan."
    },
    {
        "num": "4.1.1.23",
        "title": "Beranda Toko Daring (E-Commerce)",
        "figures": [
            ("Gambar 4.47", "Tampilan Antarmuka Beranda Toko Daring ZenCare Medical", "22_Beranda_Ecommerce.png", "Beranda Ecommerce")
        ],
        "desc": "Halaman muka etalase publik toko online ZenCare Medical yang menyajikan katalog produk obat bebas, alat kesehatan, dan bahan medis habis pakai. Antarmuka dilengkapi banner promosi dinamis, bilah pencarian cepat, navigasi kategori, sorotan produk terlaris, serta indikator stok ketersediaan fisik yang disinkronkan secara langsung dengan saldo gudang toko fisik."
    },
    {
        "num": "4.1.1.24",
        "title": "Rincian Informasi dan Spesifikasi Produk",
        "figures": [
            ("Gambar 4.48", "Tampilan Antarmuka Rincian Informasi dan Spesifikasi Produk", "23_Detail_Produk.png", "Detail Produk")
        ],
        "desc": "Halaman produk spesifik yang menyajikan informasi detail komoditas medis: galeri foto multi-sudut, deskripsi indikasi obat, nomor izin edar Kemenkes/BPOM, petunjuk dosis penggunaan, batas tanggal kedaluwarsa terdekat yang dijamin, pilihan variasi kemasan (eceran/grosir), harga satuan, jumlah pembelian, serta tombol 'Tambah ke Keranjang'."
    },
    {
        "num": "4.1.1.25",
        "title": "Panel Keranjang Belanja Pelanggan",
        "figures": [
            ("Gambar 4.49", "Tampilan Antarmuka Panel Keranjang Belanja Pelanggan", "24_Keranjang_Belanja.png", "Keranjang Belanja")
        ],
        "desc": "Antarmuka penampung sementara produk-produk yang dipilih konsumen sebelum melanjutkan ke tahap pembayaran. Panel menyajikan daftar barang, foto, satuan kemasan yang dipilih, kuantitas yang dapat disesuaikan langsung, subtotal harga, serta kalkulasi ringkasan estimasi total belanja."
    },
    {
        "num": "4.1.1.26",
        "title": "Pembayaran dan Pengiriman Pesanan (Checkout)",
        "figures": [
            ("Gambar 4.50", "Tampilan Antarmuka Pembayaran dan Pengiriman Pesanan (Checkout)", "25_Checkout_Pembayaran.png", "Checkout Pembayaran")
        ],
        "desc": "Tahapan akhir transaksi belanja daring di mana pelanggan memasukkan alamat pengiriman lengkap, memilih opsi layanan kurir ekspedisi logistik, meninjau rincian biaya (subtotal belanja ditambah ongkos kirim), serta memilih gerbang metode pembayaran daring (Transfer Bank, Virtual Account, E-Wallet, atau QRIS). Saat pesanan dikonfirmasi, sistem secara otomatis mereservasi stok produk pada tabel pesanan_batch_alokasi menggunakan prinsip FEFO."
    }
]

print(f"Total sections: {len(sections)}")
total_figs = sum(len(s['figures']) for s in sections)
print(f"Total figures: {total_figs}")

if __name__ == '__main__':
    # Build HTML blocks
    html_sections = []
    for sec in sections:
        sec_num = sec["num"]
        sec_title = sec["title"]
        sec_desc = sec["desc"]
        
        figs_html = []
        for fig_label, fig_title, img_fname, alt_text in sec["figures"]:
            f_html = f"""
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/{img_fname}" alt="{alt_text}" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                {fig_label} {fig_title}
                            </p>
                        </div>"""
            figs_html.append(f_html)
        
        joined_figs = "\n".join(figs_html)
        sec_block = f"""
                    <div class="mt-6 mb-2">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                            {sec_num} {sec_title}
                        </h5>
                    </div>
                    {joined_figs}
                    <div class="my-3 px-2">
                        <p class="text-xs font-serif text-slate-700 text-justify indent-8 leading-relaxed">
                            {sec_desc}
                        </p>
                    </div>"""
        html_sections.append(sec_block)

    full_41_content = "\n".join(html_sections)

    intro_text = """
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            Implementasi sistem merupakan tahap perwujudan dari rancangan logika basis data, alur proses bisnis, dan tata letak antarmuka pengguna yang telah dirumuskan pada Bab III ke dalam bentuk modul-modul fungsional perangkat lunak yang beroperasi secara utuh. Guna menyajikan gambaran operasional yang terstruktur, padu, dan mudah dipahami, dokumentasi implementasi sistem dikelompokkan ke dalam 26 (dua puluh enam) subbab fungsional yang berurutan sesuai alur kerja nyata: mulai dari autentikasi staf, operasional kasir dan transaksi penjualan omnichannel, manajemen master data dan logistik persediaan, siklus lengkap pemeriksaan fisik persediaan (stok opname), dashboard eksekutif dan seluruh lembar cetak laporan manajerial resmi, konfigurasi sistem, hingga portal toko daring (e-commerce) pelanggan. Pada setiap subbab, tampilan antarmuka interaktif disandingkan secara langsung dengan dokumen cetak fisik atau formulir operasional terkait agar alur kerja sistem terdokumentasi secara utuh dan terpadu. Rincian implementasi sistem diuraikan sebagai berikut:
                        </p>
"""

    new_subbab_411 = f"""
                <!-- 4.1.1 Implementasi Sistem Terpadu -->
                <div class="mt-4">
                    <div class="flex items-center justify-between border-b pb-2 mb-4">
                        <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                            4.1.1 Implementasi Sistem Terpadu dan Alur Antarmuka Operasional
                        </h4>
                        <button onclick="copyText('subbab_411_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            Salin Subbab 4.1.1
                        </button>
                    </div>

                    <div id="subbab_411_content">
{intro_text}
{full_41_content}
                    </div> <!-- Closes subbab_411_content -->
                </div> <!-- Closes 4.1.1 -->
"""

    # Update BAB_IV_ZENCARE_MEDICAL.html
    with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
        orig_iv = f.read()

    start_marker = '<!-- 4.1.1 Implementasi Sistem Terpadu -->'
    start_idx = orig_iv.find(start_marker)
    if start_idx == -1:
        start_marker = '4.1.1 Implementasi Sistem Terpadu'
        start_idx = orig_iv.find(start_marker)
        start_idx = orig_iv.rfind('<div', 0, start_idx)

    end_marker = '4.2 PENGUJIAN SISTEM'
    end_idx = orig_iv.find(end_marker)
    if end_idx != -1:
        end_idx = orig_iv.rfind('<div', 0, end_idx)

    new_iv = orig_iv[:start_idx] + new_subbab_411 + "\n        </div> <!-- Closes subbab_41_content -->\n        </div> <!-- Closes 4.1 -->\n\n        " + orig_iv[end_idx:]
    new_iv = re.sub(r'\(Subbab 4\.1[^<]*\)', '(Subbab 4.1.1.1 s.d. 4.1.1.26 Implementasi Alur Terpadu Operasional)', new_iv)
    
    new_iv_processed = process_html_document(new_iv)

    with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(new_iv_processed)

    print("Updated and italicized BAB_IV_ZENCARE_MEDICAL.html successfully!")

    # Update BAB_III_DAN_IV_ZENCARE_MEDICAL.html
    with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
        orig_iii_iv = f.read()

    start_idx_34 = orig_iii_iv.find(start_marker)
    if start_idx_34 == -1:
        start_marker_alt = '4.1.1 Implementasi Sistem Terpadu'
        start_idx_34 = orig_iii_iv.find(start_marker_alt)
        start_idx_34 = orig_iii_iv.rfind('<div', 0, start_idx_34)

    end_idx_34 = orig_iii_iv.find(end_marker)
    if end_idx_34 != -1:
        end_idx_34 = orig_iii_iv.rfind('<div', 0, end_idx_34)

    new_iii_iv = orig_iii_iv[:start_idx_34] + new_subbab_411 + "\n        </div> <!-- Closes subbab_41_content -->\n        </div> <!-- Closes 4.1 -->\n\n        " + orig_iii_iv[end_idx_34:]
    new_iii_iv = re.sub(r'\(Subbab 4\.1[^<]*\)', '(Subbab 4.1.1.1 s.d. 4.1.1.26 Implementasi Alur Terpadu Operasional)', new_iii_iv)
    
    new_iii_iv_processed = process_html_document(new_iii_iv)

    with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(new_iii_iv_processed)

    print("Updated and italicized BAB_III_DAN_IV_ZENCARE_MEDICAL.html successfully!")
