import json
import os
import re
import zipfile

# 1. Define all 46 figures and sections
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
            ("Gambar 4.7", "Tampilan Cetak Label Pengiriman Kurir Logistik E-Commerce", "28_Cetak_Label_Pengiriman.png", "Label Pengiriman Kurir E-Commerce")
        ],
        "desc": "Pusat manajemen faktur pesanan yang menyatukan seluruh riwayat penjualan dari dua saluran, yakni kasir toko fisik (POS) dan pesanan daring toko online (E-Commerce). Antarmuka dilengkapi navigasi penyaring saluran, penyaring status pemrosesan (menunggu pembayaran, diproses, dikirim, selesai, dan dibatalkan), pencarian nomor faktur atau nama pemesan, serta lencana status transaksi informatif. Staf Admin dapat menelaah rincian pesanan, memperbarui nomor resi pengiriman kurir ekspedisi, atau mengonfirmasi pengambilan mandiri di gerai fisik (in-store pickup).<br><br>Modul ini dilengkapi fasilitas penerbitan dua dokumen fisik resmi, yaitu Faktur Penjualan Resmi (Gambar 4.6) dan Label Pengiriman Kurir Logistik (Gambar 4.7). Faktur penjualan diformat dalam standar lembar A4 dengan kop surat resmi ZenCare Medical, nomor faktur, identitas lengkap pembeli, daftar komoditas obat dan alkes beserta kode SKU, nomor batch, tanggal kedaluwarsa, harga satuan, tarif ongkos kirim, kalkulasi perpajakan bila berlaku, dan kolom pengesahan administrasi penjualan. Sementara itu, Label Pengiriman dicetak menggunakan stiker termal logistik standar (100mm x 150mm) untuk ditempelkan pada kemasan paket luar e-commerce, memuat kode batang barcode resi, kode QR pelacakan status pesanan, alamat penerima lengkap, nomor kontak, berat volumetrik paket, dan label instruksi penanganan barang medis peka suhu/pecah belah."
    },
    {
        "num": "4.1.1.5",
        "title": "Pengelolaan Master Produk dan Formulir Konfigurasi Multi-UOM",
        "figures": [
            ("Gambar 4.8", "Tampilan Antarmuka Direktori Master Produk dan Katalog Medis", "05_Master_Produk.png", "Master Produk"),
            ("Gambar 4.9", "Tampilan Formulir Input Master Produk Baru dan Variasi Multi-UOM", "34_Form_Input_Master_Produk.png", "Form Input Master Produk")
        ],
        "desc": "Modul pengelolaan katalog dasar induk untuk seluruh komoditas obat dan alat kesehatan yang diperjualbelikan. Antarmuka direktori menampilkan tabel barang yang memuat foto fisik, kode unik produk (SKU Induk), nama komoditas, pengelompokan klasifikasi medis, deskripsi spesifikasi teknis/indikasi medis, batasan jumlah hari peringatan kedaluwarsa, dan status publikasi katalog.<br><br>Ketika tombol tambah produk ditekan, sistem menampilkan jendela dialog interaktif Formulir Input Master Produk Baru (Gambar 4.9). Formulir ini memfasilitasi konfigurasi identitas dasar barang serta penetapan variasi kemasan bertingkat (multi-UOM). Pengguna dapat mendefinisikan satuan kemasan besar (seperti Box, Dus, atau Karton), satuan kemasan kecil (seperti Strip, Botol, Tube, atau Pcs), rasio konversi matematis antar-satuan (misalnya 1 Box = 10 Strip), harga beli perolehan, harga jual grosir, dan harga jual eceran. Sistem memvalidasi kelengkapan data masukan dan menjaga integritas referensial basis data pada tabel produk_induk dan produk_variasi."
    },
    {
        "num": "4.1.1.6",
        "title": "Konfigurasi Satuan, Formulir Pemecahan Kemasan, dan Histori Konversi Multi-UOM",
        "figures": [
            ("Gambar 4.10", "Tampilan Antarmuka Konfigurasi Satuan dan Multi-UOM", "06_Satuan_UOM.png", "Konfigurasi Satuan UOM"),
            ("Gambar 4.11", "Tampilan Formulir Eksekusi Konversi Satuan Multi-UOM", "35_Form_Konversi_UOM.png", "Formulir Konversi UOM"),
            ("Gambar 4.12", "Tampilan Antarmuka Histori Konversi Satuan dan Pemecahan Kemasan", "12_Konversi_Satuan.png", "Histori Konversi Satuan")
        ],
        "desc": "Modul pengelolaan aturan satuan ukuran dan pelaksanaan pemecahan kemasan logistik di gerai fisik. Antarmuka Konfigurasi Satuan (Gambar 4.10) memuat tabel master satuan pengukuran (Dus, Box, Strip, Botol, Tablet, Tube, Pcs, Unit) yang menjadi fondasi penetapan multi-UOM. Untuk memenuhi permintaan konsumen eceran di toko fisik, staf operasional menggunakan Formulir Eksekusi Konversi Satuan (Gambar 4.11) guna memecah kemasan besar ke kemasan kecil. Pengguna memilih komoditas, melihat informasi rasio konversi baku, dan memasukkan jumlah dus yang dipecah.<br><br>Sistem mengeksekusi operasi konversi secara atomik: memotong saldo kemasan besar, menambahkan saldo kemasan kecil, mereplikasi nomor batch dan tanggal kedaluwarsa yang identik ke unit eceran, serta mencatat nomor bukti mutasi ke tabel konversi_satuan dan kartu_stok. Seluruh rekam jejak pemecahan kemasan tersaji secara kronologis pada Antarmuka Histori Konversi Satuan (Gambar 4.12), merinci waktu transaksi, nama produk, nomor batch, kuantitas kemasan asal yang dikurangi, serta kuantitas kemasan tujuan yang ditambahkan."
    },
    {
        "num": "4.1.1.7",
        "title": "Master Data Pemasok, Penerimaan Barang, dan Nota Logistik Masuk",
        "figures": [
            ("Gambar 4.13", "Tampilan Antarmuka Direktori Rekanan Pemasok (Supplier)", "07_Direktori_Supplier.png", "Direktori Supplier"),
            ("Gambar 4.14", "Tampilan Antarmuka Penerimaan Logistik dan Barang Masuk", "08_Penerimaan_Barang.png", "Penerimaan Barang"),
            ("Gambar 4.15", "Tampilan Cetak Nota Penerimaan Logistik dan Barang Masuk Format A4", "29_Cetak_Nota_Penerimaan.png", "Nota Penerimaan Barang Masuk A4")
        ],
        "desc": "Rangkaian modul pengelolaan rantai pasok barang masuk dari distributor resmi ke gudang ZenCare Medical. Antarmuka Direktori Supplier (Gambar 4.13) mendokumentasikan profil rekanan distributor, nama penanggung jawab, nomor kontak, alamat kantor, dan status kemitraan aktif. Saat pengiriman pasokan tiba, staf mencatatkannya melalui Antarmuka Penerimaan Barang Masuk (Gambar 4.14) dengan memasukkan nomor surat jalan/faktur distributor, memilih distributor terkait, mencatat tanggal kedaluwarsa, nomor batch, serta nomor seri unik jika komoditas berupa alat kesehatan.<br><br>Setelah data penerimaan disimpan, sistem memperbarui saldo persediaan fisik di gudang, membukukan catatan mutasi masuk ke kartu stok, dan menghasilkan dokumen fisik Nota Penerimaan Barang Masuk Format A4 (Gambar 4.15). Dokumen cetak resmi ini memuat nomor bukti penerimaan internal, nomor faktur distributor, tanggal serah terima, nama staf penerima, rincian kuantitas kemasan besar/kecil, nomor batch/ED, harga beli perolehan, serta kolom paraf verifikasi kondisi fisik segel barang oleh kepala gudang."
    },
    {
        "num": "4.1.1.8",
        "title": "Pengelolaan dan Formulir Mutasi Stok Manual Persediaan",
        "figures": [
            ("Gambar 4.16", "Tampilan Antarmuka Riwayat Mutasi Stok Manual Persediaan", "09_Mutasi_Stok.png", "Daftar Riwayat Mutasi Stok"),
            ("Gambar 4.17", "Tampilan Formulir Penyesuaian Mutasi Stok Manual (Pengisian Data Riil)", "36_Form_Mutasi_Stok.png", "Formulir Mutasi Stok Manual Terisi")
        ],
        "desc": "Modul penyesuaian kuantitas persediaan di luar transaksi reguler kasir dan penerimaan logistik. Antarmuka Riwayat Mutasi Stok (Gambar 4.16) menyajikan tabel log kronologis penyesuaian saldo barang, memuat nomor referensi mutasi, waktu peristiwa, nama komoditas obat atau alkes, arah mutasi (Masuk atau Keluar), kuantitas, alasan kategorisasi, dan identitas staf pencatat.<br><br>Untuk melakukan penyesuaian saldo secara nyata di lapangan, Admin mengoperasikan Formulir Penyesuaian Mutasi Stok Manual (Gambar 4.17). Seperti ditunjukkan pada formulir yang telah terisi data uji riil, pengguna memilih produk variasi yang disesuaikan (misalnya: <em>Amoxicillin 500mg — Tablet 500mg</em>), memilih arah mutasi (<em>Pengurangan Stok (-)</em>), menentukan nomor angkatan batch terkait (<em>Batch AMX-2026-01</em>), memasukkan kuantitas (<em>2 Strip</em>), memilih alasan kategorisasi baku (<em>Barang Rusak / Kemasan Cacat</em>), serta menuliskan kronologi penyesuaian pada kolom catatan (<em>Penarikan fisik dari rak etalase depan karena kemasan strip penyok saat penataan</em>). Tindakan ini secara atomik memotong saldo fisik pada batch yang bersangkutan dan mencatatkan log mutasi manual secara akuntabel."
    },
    {
        "num": "4.1.1.9",
        "title": "Kartu Riwayat Mutasi Stok (Inventory Ledger) Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.18", "Tampilan Antarmuka Kartu Riwayat Mutasi Stok Web", "10_Kartu_Stok.png", "Kartu Stok Web"),
            ("Gambar 4.19", "Tampilan Lembar Cetak Laporan Kartu Riwayat Mutasi Stok Format A4", "39_Cetak_Laporan_Kartu_Stok.png", "Cetak Kartu Stok A4")
        ],
        "desc": "Buku besar mutasi persediaan (inventory ledger) yang merekam jejak kronologis setiap komoditas medis secara berkesinambungan. Antarmuka Web Kartu Stok (Gambar 4.18) menyajikan penyaring produk variasi dan rentang tanggal, ringkasan saldo fisik terkini, serta tabel mutasi yang merinci tanggal transaksi, nomor bukti referensi, jenis transaksi (Penjualan POS, Pesanan E-Commerce, Penerimaan Logistik, Konversi Kemasan, atau Mutasi Manual), kuantitas masuk, kuantitas keluar, dan saldo akhir berjalan.<br><br>Antarmuka ini dilengkapi fitur cetak langsung yang menghasilkan Lembar Cetak Laporan Kartu Riwayat Mutasi Stok Format A4 (Gambar 4.19). Dokumen cetak memuat kop surat resmi ZenCare Medical, informasi spesifikasi produk, saldo awal periode, tabel mutasi lengkap beserta kanal pergerakan dan identitas petugas yang melakukan pengesahan, serta kolom tanda tangan penanggung jawab logistik. Dokumen ini menjadi rujukan utama bagi verifikator internal untuk menelusuri keabsahan saldo fisik di rak etalase."
    },
    {
        "num": "4.1.1.10",
        "title": "Siklus Lengkap Pelaksanaan Stok Opname Terpusat (6 Antarmuka & Tahapan Multi-Aktor)",
        "figures": [
            ("Gambar 4.20", "Tampilan Antarmuka Beranda dan Riwayat Sesi Stok Opname", "11_Stok_Opname_Riwayat.png", "Beranda Riwayat Sesi Opname"),
            ("Gambar 4.21", "Tahap 1 - Inisiasi Sesi Opname dan Pembekuan Transaksi oleh Superadmin", "30_Stok_Opname_Mulai_Sesi.png", "Tahap Inisiasi Freeze Superadmin"),
            ("Gambar 4.22", "Tahap 2 - Pencacahan Fisik Rak Metode Hitung Buta (Blind Count) oleh Kasir", "31_Stok_Opname_Blind_Count_Kasir.png", "Tahap Hitung Buta Kasir"),
            ("Gambar 4.23", "Tahap 3 - Verifikasi dan Rekonsiliasi Selisih Fisik oleh Admin Toko", "32_Stok_Opname_Verifikasi_Admin.png", "Tahap Verifikasi Selisih Admin"),
            ("Gambar 4.24", "Tahap 3 (Lanjutan) - Modal Dialog Eksekusi Mutasi Penyesuaian Selisih Per Item", "44_Modal_Mutasi_Opname.png", "Modal Mutasi Penyesuaian Selisih"),
            ("Gambar 4.25", "Tahap 4 - Dokumen Resmi Berita Acara Hasil Pemeriksaan Fisik Format A4 Lanskap", "33_Cetak_Berita_Acara_Opname.png", "Berita Acara Opname Resmi A4 Lanskap")
        ],
        "desc": "Modul Stok Opname merupakan modul pengendali integritas logistik persediaan yang paling krusial pada ZenCare Medical. Modul ini dirancang beroperasi melalui alur tahapan kerja multi-aktor yang memisahkan batas wewenang secara ketat:<br><br><strong>Beranda dan Riwayat Sesi (Gambar 4.20):</strong> Antarmuka landing page yang menampilkan rekapitulasi sesi opname terdahulu, status pelaksanaan (Selesai atau Dibatalkan), tanggal mulai/selesai, serta tombol kendali bagi Superadmin untuk memulai sesi pemeriksaan baru.<br><br><strong>Tahap 1: Inisiasi Sesi &amp; Pembekuan Transaksi oleh Superadmin (Gambar 4.21):</strong> Superadmin (Pemilik Toko) membuka sesi opname baru melalui jendela konfirmasi pembekuan server (Server Freeze). Sistem secara otomatis mengunci seluruh transaksi yang berpotensi mengubah saldo stok buku: terminal kasir POS luring diblokir dari penerimaan pembayaran, checkout toko daring e-commerce dinonaktifkan sementara, formulir konversi satuan UOM dikunci, dan transaksi penerimaan logistik ditangguhkan guna menjamin saldo buku teoretis berada dalam keadaan statis selama penghitungan fisik berlangsung.<br><br><strong>Tahap 2: Pencacahan Fisik Rak Metode Hitung Buta oleh Kasir (Gambar 4.22):</strong> Staf Kasir melakukan pencacahan fisik di rak etalase dan gudang menggunakan lembar kerja hitung buta (strict blind count). Antarmuka Kasir secara mutlak menyembunyikan angka saldo sistem, kuantitas reserved, dan nilai selisih. Kasir hanya disajikan daftar barang dan 3 (tiga) kolom kondisi fisik riil: Fisik Baik, Fisik Rusak, dan Fisik Kedaluwarsa. Sistem menerapkan validasi otomatis: kolom 'Fisik Kedaluwarsa' dinonaktifkan untuk nomor batch yang belum melewati tanggal kedaluwarsa, sehingga barang cacat wajib dicatat sebagai 'Fisik Rusak'. Fitur pemindai barcode mempercepat pelompatan kursor ke baris barang yang dihitung.<br><br><strong>Tahap 3: Verifikasi dan Rekonsiliasi Selisih Fisik oleh Admin Toko (Gambar 4.23):</strong> Setelah kasir menyelesaikan pencacahan, Admin membuka antarmuka verifikasi yang dilengkapi tab navigasi teratur: <em>Perlu Mutasi</em>, <em>Sesuai (Klop)</em>, <em>Sudah Dimutasi</em>, dan <em>Semua Item</em>. Logika penyaringan otomatis (auto-filtering) langsung menyembunyikan komoditas yang cocok (selisih nol) dan memusatkan perhatian verifikator murni pada barang yang terdeteksi memiliki selisih kuantitas. Admin disajikan kartu metrik estimasi nilai kerugian persediaan yang dikalkulasi otomatis dari harga beli barang.<br><br><strong>Tahap 3 (Lanjutan): Modal Dialog Eksekusi Mutasi Selisih (Gambar 4.24):</strong> Ketika tombol tindakan mutasi ditekan pada baris yang memiliki perbedaan fisik (misalnya: <em>Amoxicillin 500mg selisih -2 Strip</em>), sistem menampilkan jendela dialog verifikasi mutasi. Admin memilih kategori beban mutasi yang tepat (Beban Barang Hilang, Beban Barang Rusak, Beban Barang Expired, atau Koreksi Kelebihan Kirim), menelaah estimasi nominal kerugian perolehan (Rp 8.000), mengisi catatan investigasi kronologis, lalu menekan tombol eksekusi untuk membukukan mutasi stok penyesuaian secara otomatis.<br><br><strong>Tahap 4: Dokumen Resmi Berita Acara Hasil Pemeriksaan Fisik (Gambar 4.25):</strong> Setelah rekonsiliasi selesai, sistem menerbitkan Berita Acara Stok Opname resmi dalam tata letak A4 Lanskap 10 kolom. Dokumen memuat kop surat ZenCare Medical, waktu pelaksanaan, total baris diperiksa, total baris cocok, total baris berselisih, dan akumulasi nilai kerugian. Seluruh baris komoditas disajikan transparan, dengan penandaan selisih menggunakan garis batas sel merah tebal tahan cetak peramban. Bagian bawah lembar memuat 3 (tiga) kolom tanda tangan pengesahan berjenjang: Petugas Pencacah Fisik (Kasir), Petugas Verifikator (Admin Toko), dan Pimpinan Toko (Superadmin)."
    },
    {
        "num": "4.1.1.11",
        "title": "Dashboard Eksekutif Superadmin",
        "figures": [
            ("Gambar 4.26", "Tampilan Antarmuka Dashboard Eksekutif Superadmin", "13_Dashboard_Superadmin.png", "Dashboard Superadmin")
        ],
        "desc": "Panel kendali pimpinan tertinggi (Pemilik Toko) yang menyajikan metrik makro finansial dan logistik toko kesehatan. Antarmuka memuat indikator total valuasi aset persediaan, akumulasi pendapatan kotor dan laba kotor, perbandingan rasio penjualan daring vs luring, grafik performa penjualan mingguan, serta rekapitulasi anomali operasional yang membutuhkan keputusan strategis pimpinan."
    },
    {
        "num": "4.1.1.12",
        "title": "Laporan Penjualan Barang Omnichannel Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.27", "Tampilan Antarmuka Laporan Penjualan Barang Omnichannel Web", "14_Laporan_Penjualan.png", "Laporan Penjualan Web"),
            ("Gambar 4.28", "Tampilan Lembar Cetak Laporan Penjualan Barang Omnichannel Format A4", "37_Cetak_Laporan_Penjualan.png", "Cetak Laporan Penjualan A4")
        ],
        "desc": "Modul rekapitulasi kinerja pendapatan penjualan yang mengintegrasikan data transaksi kasir gerai fisik (POS) dan pesanan toko daring (E-Commerce). Antarmuka Web Laporan Penjualan (Gambar 4.27) dilengkapi penyaring rentang tanggal, filter kanal penjualan, grafik batang perbandingan harian, serta tabel transaksi terperinci.<br><br>Sistem menyediakan format cetak resmi berupa Lembar Cetak Laporan Penjualan Format A4 (Gambar 4.28) yang memuat kop surat resmi ZenCare Medical, ringkasan total omzet kotor, total potongan harga, total pendapatan bersih, kuantitas produk terjual, tabel rincian transaksi kronologis, serta kolom tanda tangan pengesahan pimpinan toko."
    },
    {
        "num": "4.1.1.13",
        "title": "Laporan Persediaan dan Ketersediaan Barang Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.29", "Tampilan Antarmuka Laporan Persediaan dan Ketersediaan Barang Web", "15_Laporan_Persediaan.png", "Laporan Persediaan Web"),
            ("Gambar 4.30", "Tampilan Lembar Cetak Laporan Persediaan dan Ketersediaan Barang Format A4", "38_Cetak_Laporan_Persediaan.png", "Cetak Laporan Persediaan A4")
        ],
        "desc": "Panel evaluasi posisi saldo persediaan komoditas medis yang tersedia di rak gerai dan gudang. Antarmuka Web Laporan Persediaan (Gambar 4.29) menyajikan metrik total aset persediaan, kuantitas fisik siap jual, dan jumlah stok teralokasi pesanan daring (reserved stock) secara real-time.<br><br>Lembar Cetak Laporan Persediaan Format A4 (Gambar 4.30) menyajikan laporan cetak resmi posisi aset logistik toko, merinci kode SKU, nama produk obat atau alat kesehatan, kategori komoditas, jumlah saldo fisik kemasan dus dan eceran, estimasi nilai perolehan persediaan, indikator status ketersediaan (Aman, Menipis, Habis), dan area tanda tangan penanggung jawab logistik."
    },
    {
        "num": "4.1.1.14",
        "title": "Laporan Logistik Medis FEFO Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.31", "Tampilan Antarmuka Laporan Logistik Medis FEFO dan Pemantauan Kedaluwarsa Web", "16_Laporan_Logistik_Medis.png", "Laporan Logistik Medis Web"),
            ("Gambar 4.32", "Tampilan Lembar Cetak Laporan Logistik Medis FEFO Format A4", "40_Cetak_Laporan_Logistik_Medis.png", "Cetak Laporan Logistik Medis A4")
        ],
        "desc": "Modul pengawasan mutu dan masa simpan komoditas medis yang memetakan seluruh nomor angkatan (batch) obat berdasarkan sisa umur simpan produk. Antarmuka Web (Gambar 4.31) mengelompokkan persediaan ke dalam 3 zona urgensi warna: Zona Merah (telah kedaluwarsa), Zona Kuning (mendekati kedaluwarsa dalam 30–90 hari), dan Zona Hijau (masa simpan aman di atas 90 hari).<br><br>Lembar Cetak Laporan Logistik Medis FEFO Format A4 (Gambar 4.32) memuat dokumen resmi rekapitulasi masa kedaluwarsa per batch yang menjadi landasan bagi manajemen untuk memprioritaskan penjualan obat bertanggal kedaluwarsa terdekat sesuai aturan FEFO atau memproses retur ke pihak distributor pemasok sebelum jatuh tempo."
    },
    {
        "num": "4.1.1.15",
        "title": "Laporan Penerimaan Pasokan Barang Masuk Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.33", "Tampilan Antarmuka Laporan Penerimaan Pasokan Barang Masuk Web", "17_Laporan_Penerimaan_Barang.png", "Laporan Penerimaan Barang Web"),
            ("Gambar 4.34", "Tampilan Lembar Cetak Laporan Penerimaan Logistik dari Pemasok Format A4", "41_Cetak_Laporan_Penerimaan.png", "Cetak Laporan Penerimaan A4")
        ],
        "desc": "Modul pelaporan verifikasi logistik masuk (inbound) yang merekapitulasi seluruh pasokan barang yang telah diterima dari distributor rekanan. Antarmuka Web (Gambar 4.33) menyajikan penyaring tanggal penerimaan dan nama supplier serta ringkasan total nilai pembelian logistik.<br><br>Lembar Cetak Laporan Penerimaan Logistik Format A4 (Gambar 4.34) menyajikan dokumen resmi rekapitulasi pasokan masuk, memuat nomor faktur distributor, waktu penerimaan di gudang, nama staf penerima, rincian produk yang dipasok, nomor batch/ED, harga beli perolehan, dan kolom tanda tangan pengesahan bagian pengadaan logistik."
    },
    {
        "num": "4.1.1.16",
        "title": "Laporan Mutasi Stok & Konversi Satuan Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.35", "Tampilan Antarmuka Laporan Mutasi Stok dan Konversi Satuan Web", "18_Laporan_Mutasi_Konversi.png", "Laporan Mutasi Konversi Web"),
            ("Gambar 4.36", "Tampilan Lembar Cetak Laporan Mutasi Stok dan Konversi Satuan Format A4", "42_Cetak_Laporan_Mutasi_Konversi.png", "Cetak Laporan Mutasi Konversi A4")
        ],
        "desc": "Modul rekapitulasi pencatatan pergerakan persediaan non-penjualan di internal toko yang menggabungkan dua aktivitas: penyesuaian manual (barang rusak, kedaluwarsa, atau penyesuaian opname) dan pemecahan satuan kemasan bertingkat. Antarmuka Web (Gambar 4.35) menyajikan tab filter terpisah untuk menelaah masing-masing jenis mutasi.<br><br>Lembar Cetak Laporan Mutasi Stok dan Konversi Format A4 (Gambar 4.36) mencetak dokumen resmi rekapitulasi pergerakan internal, merinci tanggal peristiwa, kode barang, nama produk, kuantitas mutasi, alasan pembebanan, rasio konversi yang diterapkan, identitas petugas pencatat, serta kolom tanda tangan pimpinan."
    },
    {
        "num": "4.1.1.17",
        "title": "Laporan Deteksi Anomali Pengeluaran FEFO Web dan Lembar Cetak Resmi",
        "figures": [
            ("Gambar 4.37", "Tampilan Antarmuka Laporan Deteksi Anomali Pengeluaran Barang FEFO Web", "17_Laporan_Anomali_FEFO.png", "Laporan Anomali FEFO Web"),
            ("Gambar 4.38", "Tampilan Lembar Cetak Laporan Deteksi Anomali FEFO Format A4", "43_Cetak_Laporan_Anomali_FEFO.png", "Cetak Laporan Anomali FEFO A4")
        ],
        "desc": "Panel pengawasan kepatuhan protokol logistik medis dalam pendistribusian obat. Sistem secara otomatis mencatat dan mengevaluasi setiap insiden di mana kasir mengeluarkan nomor angkatan obat yang tanggal kedaluwarsanya lebih jauh padahal masih terdapat angkatan obat yang bertanggal kedaluwarsa lebih dekat di rak toko. Antarmuka Web (Gambar 4.37) menyajikan daftar insiden penyimpangan secara real-time.<br><br>Lembar Cetak Laporan Deteksi Anomali FEFO Format A4 (Gambar 4.38) mencetak lembar evaluasi resmi yang merinci nomor transaksi, kasir yang bertugas, nama obat yang mengalami loncatan angkatan, nomor batch yang seharusnya dikeluarkan menurut aturan FEFO, nomor batch yang diambil di lapangan, alasan petugas (misalnya permintaan kemasan khusus konsumen), serta kolom tanda tangan evaluasi oleh penanggung jawab teknis gerai."
    },
    {
        "num": "4.1.1.18",
        "title": "Manajemen Pengguna dan Penetapan Hak Akses (RBAC)",
        "figures": [
            ("Gambar 4.39", "Tampilan Antarmuka Manajemen Pengguna dan Penetapan Hak Akses", "18_Manajemen_Pengguna.png", "Manajemen Pengguna")
        ],
        "desc": "Panel administrasi keamanan pengguna yang hanya dapat diakses oleh peran Superadmin untuk mengelola akun staf operasional. Antarmuka memuat daftar akun terdaftar dengan atribut nama lengkap, username, peran kerja (Superadmin, Admin, atau Kasir), kontak, dan status akun aktif. Tersedia tombol aksi untuk pendaftaran akun staf baru, pengaturan ulang kata sandi (reset password), dan penonaktifan akun secara instan guna mencegah akses tidak sah saat terjadi perubahan staf operasional toko."
    },
    {
        "num": "4.1.1.19",
        "title": "Pengaturan Toko Daring dan Konten Web",
        "figures": [
            ("Gambar 4.40", "Tampilan Antarmuka Pengaturan Toko Daring dan Konten Web", "19_Pengaturan_Web.png", "Pengaturan Web Toko")
        ],
        "desc": "Panel kustomisasi identitas gerai dan kanal e-commerce, mencakup pengaturan nama toko, alamat operasional gerai, koordinat peta digital, nomor kontak WhatsApp, ketentuan layanan, jam operasional toko fisik, serta spanduk promosi layanan kesehatan. Perubahan data pada panel ini secara dinamis memperbarui informasi toko pada antarmuka publik e-commerce dan kop surat faktur penjualan."
    },
    {
        "num": "4.1.1.20",
        "title": "Pendaftaran dan Masuk Akun Pelanggan",
        "figures": [
            ("Gambar 4.41", "Tampilan Antarmuka Pendaftaran dan Masuk Akun Pelanggan", "20_Login_Pelanggan.png", "Login Pelanggan")
        ],
        "desc": "Gerbang autentikasi khusus konsumen toko online dengan tata letak dua tab terintegrasi (Masuk Akun dan Registrasi Akun Baru). Dilengkapi validasi format email unik, perlindungan kata sandi terenkripsi, dan pesan umpan balik interaktif. Konsumen yang berhasil masuk diberikan sesi belanja pribadi yang mengaitkan keranjang belanja dan histori pesanannya ke basis data."
    },
    {
        "num": "4.1.1.21",
        "title": "Profil dan Pemantauan Pesanan Pelanggan",
        "figures": [
            ("Gambar 4.42", "Tampilan Antarmuka Profil dan Pemantauan Pesanan Pelanggan", "21_Profil_Pelanggan.png", "Profil Pelanggan")
        ],
        "desc": "Pusat informasi akun pelanggan yang memuat riwayat seluruh pesanan daring, pelacakan tahapan pemrosesan pesanan secara langsung (Menunggu Pembayaran, Diproses Gudang, Dikirim Kurir, atau Siap Diambil di Toko), pengelolaan buku alamat pengiriman, dan pembaruan data diri."
    },
    {
        "num": "4.1.1.22",
        "title": "Beranda Toko Daring (E-Commerce)",
        "figures": [
            ("Gambar 4.43", "Tampilan Antarmuka Beranda Toko Daring (E-Commerce)", "22_Beranda_Ecommerce.png", "Beranda E-Commerce")
        ],
        "desc": "Halaman etalase belanja digital pelanggan yang menyajikan katalog produk obat dan alat kesehatan secara interaktif. Dilengkapi navigasi kategori komoditas medis, bilah pencarian cerdas, spanduk pengumuman layanan, serta kartu produk informatif yang memuat foto fisik, harga satuan, status ketersediaan stok siap kirim, dan tombol cepat masukkan ke keranjang belanja."
    },
    {
        "num": "4.1.1.23",
        "title": "Rincian Informasi dan Spesifikasi Produk",
        "figures": [
            ("Gambar 4.44", "Tampilan Antarmuka Rincian Informasi dan Spesifikasi Produk", "23_Detail_Produk.png", "Detail Produk E-Commerce")
        ],
        "desc": "Halaman informasi komprehensif produk medis bagi konsumen, menyajikan galeri foto resolusi tinggi, pilihan variasi kemasan bertingkat, status ketersediaan stok fisik siap kirim, deskripsi indikasi medis atau spesifikasi teknis alat, penentuan kuantitas beli dengan validasi stok maksimal, dan tombol tambah ke keranjang belanja."
    },
    {
        "num": "4.1.1.24",
        "title": "Panel Keranjang Belanja Pelanggan",
        "figures": [
            ("Gambar 4.45", "Tampilan Antarmuka Panel Keranjang Belanja Pelanggan", "24_Keranjang_Belanja.png", "Keranjang Belanja")
        ],
        "desc": "Antarmuka penelaahan barang belanjaan sebelum melanjutkan ke tahap pembayaran. Memfasilitasi pengubahan jumlah pesanan dengan validasi kuota stok persediaan, penghapusan item, kalkulasi otomatis subtotal belanja, serta tombol menuju formulir pembayaran dan pengiriman (checkout)."
    },
    {
        "num": "4.1.1.25",
        "title": "Pembayaran dan Pengiriman Pesanan (Checkout)",
        "figures": [
            ("Gambar 4.46", "Tampilan Antarmuka Pembayaran dan Pengiriman Pesanan (Checkout)", "25_Checkout_Pembayaran.png", "Checkout Pembayaran")
        ],
        "desc": "Halaman penyelesaian transaksi belanja daring pelanggan yang memadukan formulir data penerima barang, pilihan metode penyerahan pesanan (diantar oleh kurir ekspedisi ke alamat tujuan atau diambil langsung secara mandiri di gerai fisik ZenCare Medical / in-store pickup), penentuan titik lokasi koordinat pengiriman yang akurat menggunakan penanda peta digital interaktif, kalkulasi otomatis tarif ongkos kirim berdasarkan kota tujuan, ringkasan rincian tagihan belanja, serta tombol pembayaran aman yang terhubung dengan gerbang pembayaran digital. Saat transaksi disahkan, sistem secara otomatis mengalokasikan stok pada tabel pesanan_batch_alokasi dan membukukan pesanan baru ke tabel penjualan."
    }
]

# Generate Subbab 4.1 HTML
html_sections = []
all_catalog_items = []
current_img_idx = 1

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
        
        # Determine category for catalog
        cat = "master_logistik"
        folder = "03_Master_dan_Logistik"
        badge_color = "bg-blue-50 text-blue-700 border-blue-200"
        role_b = "Admin Toko"
        
        if "01_" in img_fname:
            cat = "auth"
            folder = "01_Autentikasi_Staf"
            badge_color = "bg-slate-100 text-slate-700 border-slate-300"
            role_b = "Staf Internal"
        elif any(x in img_fname for x in ["02_", "03_", "26_", "04_", "27_", "28_"]):
            cat = "pos_trx"
            folder = "02_Kasir_dan_Transaksi"
            badge_color = "bg-emerald-50 text-emerald-700 border-emerald-200" if "Kasir" in fig_title or "Struk" in fig_title else "bg-blue-50 text-blue-700 border-blue-200"
            role_b = "Kasir & Admin" if "Kasir" in fig_title or "Struk" in fig_title else "Admin Toko"
        elif any(x in img_fname for x in ["Opname", "opname", "30_", "31_", "32_", "33_", "44_"]):
            cat = "opname"
            folder = "04_Siklus_Stok_Opname"
            badge_color = "bg-purple-50 text-purple-700 border-purple-200" if "Superadmin" in fig_title else ("bg-emerald-50 text-emerald-700 border-emerald-200" if "Kasir" in fig_title else "bg-blue-50 text-blue-700 border-blue-200")
            role_b = "Superadmin" if "Superadmin" in fig_title else ("Kasir Lapangan" if "Kasir" in fig_title else "Admin Toko")
        elif any(x in img_fname for x in ["Laporan", "13_", "14_", "15_", "16_", "17_", "18_", "37_", "38_", "40_", "41_", "42_", "43_"]):
            cat = "laporan"
            folder = "05_Dashboard_dan_Laporan"
            badge_color = "bg-purple-50 text-purple-700 border-purple-200"
            role_b = "Superadmin & Manajemen"
        elif any(x in img_fname for x in ["18_Manajemen", "19_Pengaturan"]):
            cat = "pengaturan"
            folder = "06_Pengaturan_dan_Pengguna"
            badge_color = "bg-purple-50 text-purple-700 border-purple-200"
            role_b = "Superadmin"
        elif any(x in img_fname for x in ["20_", "21_", "22_", "23_", "24_", "25_"]):
            cat = "ecommerce"
            folder = "07_Toko_Daring_Ecommerce"
            badge_color = "bg-teal-50 text-teal-700 border-teal-200"
            role_b = "Pelanggan Toko Online"

        all_catalog_items.append({
            "id": f"ui_{current_img_idx:02d}",
            "fig": fig_label,
            "title": fig_title,
            "category": cat,
            "role_badge": role_b,
            "badge_color": badge_color,
            "filename": img_fname,
            "folder": folder,
            "desc": re.sub(r'<[^>]+>', ' ', sec_desc)[:220] + '...'
        })
        current_img_idx += 1
    
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
                            Implementasi sistem merupakan tahap perwujudan dari rancangan logika basis data, alur proses bisnis, dan tata letak antarmuka pengguna yang telah dirumuskan pada Bab III ke dalam bentuk modul-modul fungsional perangkat lunak yang beroperasi secara utuh. Guna menyajikan gambaran operasional yang terstruktur, padu, dan mudah dipahami, dokumentasi implementasi sistem dikelompokkan ke dalam 25 (dua puluh lima) subbab fungsional yang berurutan sesuai alur kerja nyata: mulai dari autentikasi staf, operasional kasir dan transaksi penjualan omnichannel, manajemen master data dan logistik persediaan, siklus lengkap pemeriksaan fisik persediaan (stok opname), dashboard eksekutif dan seluruh lembar cetak laporan manajerial resmi, konfigurasi sistem, hingga portal toko daring (e-commerce) pelanggan. Pada setiap subbab, tampilan antarmuka interaktif disandingkan secara langsung dengan dokumen cetak fisik atau formulir operasional terkait agar alur kerja sistem terdokumentasi secara utuh dan terpadu. Rincian implementasi sistem diuraikan sebagai berikut:
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

# Read existing BAB_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    orig = f.read()

start_marker = '<!-- 4.1.1 Implementasi Sistem'
start_idx = orig.find(start_marker)
start_idx = orig.rfind('<div', 0, start_idx)

end_marker = 'id="subbab_42"'
end_idx = orig.find(end_marker)
end_idx = orig.rfind('<div', 0, end_idx)

new_html = orig[:start_idx] + new_subbab_411 + "\n        </div> <!-- Closes subbab_41_content -->\n        </div> <!-- Closes 4.1 -->\n\n        " + orig[end_idx:]

with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_html)

print("Updated laporan_ta/BAB_IV_ZENCARE_MEDICAL.html successfully!")

# Also sync BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    comb = f.read()

comb_b4_start = comb.find('<div class="text-center mb-10">\n            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>')
comb_b4_end = comb.find('<!-- Bottom Notification', comb_b4_start)

b4_inner_start = new_html.find('<div class="text-center mb-10">\n            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>')
b4_inner_end = new_html.find('<!-- Bottom Notification', b4_inner_start)
b4_inner = new_html[b4_inner_start:b4_inner_end].strip()
if b4_inner.endswith('</div>'):
    b4_inner = b4_inner[:-6].strip()

new_comb = comb[:comb_b4_start] + b4_inner + "\n        </div>\n\n    " + comb[comb_b4_end:]
with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_comb)

print("Updated laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html successfully!")

