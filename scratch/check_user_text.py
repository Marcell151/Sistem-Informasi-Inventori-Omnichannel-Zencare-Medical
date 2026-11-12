import re

text = """
Bab ini memaparkan hasil dari implementasi sistem informasi inventori dan penjualan omnichannel pada toko kesehatan ZenCare Medical yang telah dibangun berdasarkan analisis kebutuhan serta spesifikasi rancangan pada bab sebelumnya. Implementasi sistem ini mencakup perwujudan antarmuka pengguna sistem (user interface), pengoperasian alur transaksi kasir toko fisik (Point of Sale), pengelolaan persediaan obat dan alat kesehatan multi-satuan dengan kendali kedaluwarsa berbasis FEFO (First Expired, First Out), integrasi etalase toko daring (E-Commerce), hingga penyajian laporan eksekutif terpadu untuk mendukung transparansi operasional dan pengambilan keputusan bisnis.
4.1	Implementasi Sistem
Implementasi sistem merupakan tahap perwujudan dari rancangan logika basis data, alur proses bisnis, dan tata letak antarmuka pengguna yang telah dirumuskan pada Bab III ke dalam bentuk modul-modul fungsional perangkat lunak yang beroperasi secara utuh. Mulai dari autentikasi staf, operasional kasir dan transaksi penjualan omnichannel, manajemen master data dan logistik persediaan, siklus lengkap pemeriksaan fisik persediaan (stok opname), dashboard eksekutif dan seluruh lembar cetak laporan manajerial resmi, konfigurasi sistem, hingga portal toko daring (e-commerce) pelanggan. Pada setiap subbab, tampilan antarmuka interaktif disandingkan secara langsung dengan dokumen cetak fisik atau formulir operasional terkait agar alur kerja sistem terdokumentasi secara utuh dan terpadu. Rincian implementasi sistem diuraikan sebagai berikut:

4.1.1	 Halaman Login Staf
Halaman autentikasi utama satu pintu bagi seluruh staf internal toko kesehatan ZenCare Medical yang mencakup peran Superadmin, Admin, dan Kasir. Antarmuka menerapkan formulir masukan nama pengguna (username) dan kata sandi (password), identitas visual resmi ZenCare Medical, serta indikator verifikasi kredensial. Pada sisi backend, sistem memvalidasi kredensial terhadap tabel users menggunakan pencocokan hash terenkripsi. Berdasarkan nilai atribut peran (role), sistem secara otomatis mengarahkan alur kerja ke antarmuka yang tepat: Kasir diarahkan langsung menuju Terminal Kasir guna mempercepat transaksi penjualan luring di gerai fisik, Admin diarahkan ke dashboard operasional toko dan pengelolaan logistik, sedangkan Superadmin diarahkan ke dashboard eksekutif dan konfigurasi manajerial. Gerbang ini terproteksi ketat dari akses luar, di mana akun Pelanggan toko online dicegah masuk melalui formulir staf ini.
4.1.2	Dashboard Operasional Admin
Pusat kendali operasional harian bagi staf Admin yang menyajikan ringkasan indikator kinerja toko fisik secara langsung. Antarmuka memuat kartu metrik ringkas yang menampilkan jumlah total produk aktif, total transaksi penjualan hari ini, peringatan jumlah persediaan barang yang menipis di bawah ambang batas minimum, serta notifikasi dini bagi komoditas yang mendekati batas waktu kedaluwarsa. Terdapat tombol akses cepat menuju terminal kasir (Point of Sale) dan formulir penerimaan logistik barang masuk. Antarmuka juga menyediakan grafik tren volume transaksi harian serta tabel rekapitulasi pesanan terbaru yang memerlukan pemrosesan segera. Seluruh metrik dihimpun secara dinamis dari tabel penjualan, produk_variasi, dan stok_batch.

4.1.3	Terminal Kasir (Point of Sale / POS) Dan Struk
Antarmuka pelayanan transaksi penjualan luring di gerai fisik toko yang dioperasikan oleh staf Kasir menggunakan tata letak dua panel terpadu guna mempercepat alur transaksi di meja kasir. Panel kiri menyajikan bilah pencarian obat dan alat kesehatan berbasis nama maupun pemindaian kode batang (barcode), penyaring kategori komoditas, serta kartu produk interaktif yang memuat informasi harga satuan eceran dan sisa stok fisik siap jual. Panel kanan memuat keranjang belanja, kalkulasi otomatis diskon, pilihan metode pembayaran tunai maupun non-tunai (QRIS/debit), perhitungan uang kembali, serta tombol penyelesaian transaksi.
Saat pembayaran disahkan, sistem secara otomatis mengeksekusi algoritma pengeluaran stok berbasis tanggal kedaluwarsa terdekat (FEFO) melalui alokasi tabel pesanan_batch_alokasi, mencatat nomor seri unit alat kesehatan pada unit_serial (jika barang bernomor seri), memperbarui saldo kartu_stok, serta mencetak lembar bukti belanja konsumen berupa Struk Kasir POS (Gambar 4.4). Struk mencantumkan identitas resmi ZenCare Medical, nomor faktur POS yang unik, waktu transaksi, kasir yang bertugas, rincian produk medis beserta nomor batch dan serial number, harga satuan, subtotal, potongan harga, total bayar, nominal uang tunai, dan nominal kembalian.

4.1.4	Pengelolaan Transaksi Penjualan Omnichannel
Pusat manajemen faktur pesanan yang menyatukan seluruh riwayat penjualan dari dua saluran, yakni kasir toko fisik (POS) dan pesanan daring toko online (E-Commerce). Antarmuka dilengkapi navigasi penyaring saluran, penyaring status pemrosesan (menunggu pembayaran, diproses, dikirim, selesai, dan dibatalkan), pencarian nomor faktur atau nama pemesan, serta lencana status transaksi informatif. Staf Admin dapat menelaah rincian pesanan, memperbarui nomor resi pengiriman kurir ekspedisi, atau mengonfirmasi pengambilan mandiri di gerai fisik (in-store pickup).
Modul ini dilengkapi fasilitas penerbitan dua dokumen fisik resmi, yaitu Faktur Penjualan Resmi (Gambar 4.6) dan Label Pengiriman Kurir Logistik (Gambar 4.7). Faktur penjualan diformat dalam standar lembar dengan kop surat resmi ZenCare Medical, nomor faktur, identitas lengkap pembeli, daftar komoditas obat dan alkes beserta kode SKU, nomor batch, tanggal kedaluwarsa, harga satuan, tarif ongkos kirim, kalkulasi perpajakan bila berlaku, dan kolom pengesahan administrasi penjualan. Sementara itu, Label Pengiriman dicetak untuk ditempelkan pada kemasan paket luar e-commerce, memuat kode batang barcode resi, kode QR pelacakan status pesanan, alamat penerima lengkap, nomor kontak, berat volumetrik paket, dan label instruksi penanganan barang medis peka suhu/pecah belah.
4.1.5	Pengelolaan Master Produk
Modul pengelolaan katalog terpadu untuk seluruh komoditas obat dan alat kesehatan yang diperjualbelikan pada ekosistem ZenCare Medical, mengadopsi arsitektur relasional bertingkat antara entitas produk_induk dan produk_variasi. Antarmuka direktori master produk (Gambar 4.8) menyajikan kartu katalog interaktif yang merinci foto fisik, kode SKU Induk, nama produk, klasifikasi medis, ambang batas hari peringatan kedaluwarsa, serta daftar variasi kemasan terdaftar.
Terdapat Formulir Input Master Produk Induk Baru (Gambar 4.9), di mana staf mendefinisikan atribut induk mencakup SKU induk unik, nama resmi komoditas medis, kategori produk, batas waktu peringatan dini kedaluwarsa, deskripsi indikasi atau spesifikasi teknis, serta unggahan berkas foto produk. Setelah produk induk tersimpan, staf melanjutkan konfigurasi satuan kemasan melalui Formulir Konfigurasi Produk Variasi dan Multi-UOM (Gambar 4.10). Pada formulir variasi ini, sistem memfasilitasi penetapan SKU variasi spesifik, nama variasi kemasan, satuan kemasan kecil/eceran, satuan kemasan besar/grosir, rasio konversi, harga beli perolehan acuan, harga jual kemasan besar, harga jual eceran kemasan kecil, serta batas minimum persediaan masing-masing tingkat satuan.
4.1.6	Konfigurasi Satuan dan Multi-UOM
Modul pengelolaan aturan satuan ukuran dan pelaksanaan pemecahan kemasan logistik di gerai fisik. Antarmuka Konfigurasi Satuan (Gambar 4.11) memuat tabel master satuan pengukuran (Dus, Box, Strip, Botol, Tablet, Tube, Pcs, Unit) yang menjadi fondasi penetapan multi-UOM. Untuk memenuhi permintaan konsumen eceran di toko fisik, staf operasional menggunakan Formulir Eksekusi Konversi Satuan (Gambar 4.12) guna memecah kemasan besar ke kemasan kecil. Pengguna memilih komoditas, melihat informasi rasio konversi baku, dan memasukkan jumlah dus yang dipecah.
Sistem mengeksekusi operasi konversi secara atomik: memotong saldo kemasan besar, menambahkan saldo kemasan kecil, mereplikasi nomor batch dan tanggal kedaluwarsa yang identik ke unit eceran, serta mencatat nomor bukti mutasi ke tabel konversi_satuan dan kartu_stok. Seluruh rekam jejak pemecahan kemasan tersaji secara kronologis pada Antarmuka Histori Konversi Satuan (Gambar 4.13), merinci waktu transaksi, nama produk, nomor batch, kuantitas kemasan asal yang dikurangi, serta kuantitas kemasan tujuan yang ditambahkan.
4.1.7	Master Data Supplier
Modul pengelolaan data master rekanan pemasok resmi yang menjadi rantai pengadaan komoditas obat dan alat kesehatan bagi ZenCare Medical. Antarmuka Direktori Supplier (Gambar 4.14) mendokumentasikan profil rekanan distributor medis, nama penanggung jawab (sales representative), nomor kontak telepon, alamat kantor operasional, dan status kemitraan aktif.
Pendaftaran mitra logistik baru dilakukan melalui Formulir Pendaftaran Rekanan Pemasok Baru (Gambar 4.15). Formulir modal ini memfasilitasi perekaman legalitas distributor mencakup nama badan usaha distributor pasokan medis, narahubung resmi, saluran komunikasi WhatsApp/telepon, alamat surat elektronik, serta alamat kantor pusat dan pergudangan distributor.
4.1.8	Penerimaan Barang Masuk
Modul penerimaan pasokan barang fisik dari distributor ke gerai fisik ZenCare Medical. Alur dimulai melalui Formulir Input Penerimaan Logistik Pasokan Barang Masuk (Gambar 4.16), di mana staf gudang memilih distributor rekanan pengirim, memasukkan nomor faktur distributor, mencatat tanggal kedaluwarsa (expired date), nomor batch pabrikan, kuantitas kemasan besar/kecil yang diterima, harga beli perolehan, dan nomor seri unik alat kesehatan.
Setelah data penerimaan diverifikasi dan disimpan, sistem secara otomatis membukukan mutasi masuk ke kartu stok dan mencetak Dokumen Nota Bukti Penerimaan Logistik (Gambar 4.17) yang memuat nomor bukti internal, nomor faktur distributor, rincian produk, nomor batch, serta kolom tanda tangan paraf verifikasi kondisi fisik segel kemasan oleh kepala logistik.
Selanjutnya, staf logistik mencetak label penandaan fisik barang melalui antarmuka Cetak Label Stiker Barcode Batch & Serial Number (Gambar 4.18). Label barcode ini ditempelkan langsung pada kemasan produk medis guna mendukung pemindaian cepat di meja kasir POS dan pelacakan dan pemeriksaan rak gudang.

4.1.9	Mutasi Stok
Modul penyesuaian saldo persediaan secara langsung oleh staf berwenang di luar transaksi kasir maupun penerimaan barang. Antarmuka Daftar Mutasi Stok (Gambar 4.19) menyajikan tabel log kronologis penyesuaian saldo barang, memuat nomor referensi mutasi, waktu peristiwa, nama komoditas obat atau alkes, arah mutasi (Masuk atau Keluar), kuantitas, alasan kategorisasi, dan identitas staf pencatat.
Formulir Penyesuaian Mutasi Stok Manual (Gambar 4.20) digunakan saat terjadi kejadian operasional khusus seperti kerusakan kemasan di etalase, barang kedaluwarsa yang ditarik dari rak, pemakaian internal gerai, atau koreksi selisih administratif. Seperti ditunjukkan pada formulir yang telah terisi data uji riil, pengguna memilih produk variasi yang disesuaikan, memasukkan kuantitas, memilih alasan kategorisasi baku, serta menuliskan kronologi penyesuaian pada kolom catatan. Tindakan ini secara atomik memotong saldo fisik pada batch yang bersangkutan dan mencatatkan log mutasi manual secara akuntabel.
4.1.10	Kartu Stok
Kartu stok persediaan komprehensif yang melacak mutasi setiap komoditas obat dan alat kesehatan secara mendalam. Antarmuka Web Kartu Stok (Gambar 4.21) menyajikan penyaring produk variasi dan rentang tanggal, ringkasan saldo fisik terkini, serta tabel mutasi yang merinci tanggal transaksi, nomor bukti referensi, jenis transaksi (Penjualan POS, Pesanan E-Commerce, Penerimaan Logistik, Konversi Kemasan, atau Mutasi Manual), kuantitas masuk, kuantitas keluar, dan saldo akhir berjalan.
Untuk keperluan pertanggungjawaban persediaan dan verifikasi administratif berkala, sistem menyediakan Cetak Kartu Riwayat Mutasi Stok (Gambar 4.22). Dokumen cetak memuat kop surat resmi ZenCare Medical, informasi spesifikasi produk, saldo awal periode, tabel mutasi lengkap beserta kanal pergerakan dan identitas petugas yang melakukan pengesahan, serta kolom tanda tangan penanggung jawab logistik.
4.1.11	Stok Opname
Dokumentasi siklus utuh pemeriksaan fisik berkala persediaan persediaan (stok opname) yang dirancang untuk mencegah kecurangan dan memastikan kebenaran saldo buku terhadap kondisi fisik rak nyata melalui tampilan antarmuka terpadu:
•	Beranda Riwayat Sesi (Gambar 4.23): Menampilkan tabel histori seluruh sesi opname yang pernah dilaksanakan, status sesi (Sedang Dihitung, Tahap Verifikasi, atau Selesai), tanggal pelaksanaan, dan nilai acuan estimasi kerugian.
•	Inisiasi Sesi & Pembekuan Server Superadmin (Gambar 4.24): Hak akses khusus Superadmin. Saat sesi baru dibuka, sistem mengaktifkan mekanisme Freeze Lock di tingkat server yang membekukan transaksi kasir POS, checkout toko daring, pemecahan kemasan konversi, dan penerimaan logistik guna mencegah terjadinya kondisi race condition.
•	Pencacahan Fisik Blind Count Kasir (Gambar 4.25): Kasir lapangan mencatat kuantitas fisik di rak tanpa dapat melihat angka saldo pembukuan sistem (Blind Count). Kasir hanya disajikan daftar barang dan 3 (tiga) kolom kondisi fisik riil: Fisik Baik, Fisik Rusak, dan Fisik Kedaluwarsa. Sistem menerapkan validasi otomatis: kolom 'Fisik Kedaluwarsa' dinonaktifkan untuk nomor batch yang belum melewati tanggal kedaluwarsa, sehingga barang cacat wajib dicatat sebagai 'Fisik Rusak'.
•	Verifikasi & Rekonsiliasi Selisih Admin (Gambar 4.26): Admin toko memverifikasi hasil hitungan fisik terhadap data buku sistem. Sistem secara otomatis menyembunyikan barang yang sesuai (klop) dan hanya menyoroti barang yang terdeteksi anomali selisih (misalnya: terdeteksi 6 komoditas berselisih fisik). Sistem menghitung estimasi nilai acuan kerugian persediaan dari kuantitas fisik yang tercatat Rusak, Expired, dan Hilang berdasarkan rumus baku: (Jumlah Rusak + Jumlah Expired + Jumlah Hilang) × Harga Beli (Satuan Terkecil).
•	Eksekusi Mutasi Selisih (Gambar 4.27): Untuk setiap barang yang berselisih, Admin membuka kotak dialog mutasi penyesuaian (misalnya: penyesuaian barang hilang selisih -2 Strip Amoxicillin 500mg dengan beban kerugian Rp 8.000). Admin mengonfirmasi alasan penyesuaian, sistem melakukan mutasi atomik, dan menandai status baris menjadi 'Telah Dimutasi'.
•	Dokumen Berita Acara (Gambar 4.28): Setelah rekonsiliasi selesai, sistem menerbitkan Berita Acara Stok Opname resmi dalam tata letak Lanskap 10 kolom data: No, Identitas Komoditas & Nama Produk, Tingkat Satuan (Kemasan Besar / Kemasan Kecil), Nomor Batch / Serial Number, Satuan, Stok Sistem, Hitung Fisik, Selisih, Nilai Kerugian, dan Tindakan Mutasi. Bagian bawah lembar memuat 3 (tiga) kolom tanda tangan pengesahan berjenjang: Petugas Pencacah Fisik (Kasir), Petugas Verifikator (Admin Toko), dan Pimpinan Toko (Superadmin).
4.1.12	Dashboard Eksekutif 
Pusat kendali level tertinggi bagi pemilik toko (Superadmin) untuk memantau kesehatan bisnis dan integritas inventori ZenCare Medical secara menyeluruh. Antarmuka memuat widget metrik strategis: total pendapatan kotor dan bersih gabungan (POS luring dan e-commerce daring), total valuasi aset persediaan di gudang, ringkasan efektivitas FEFO, tombol kendali pembekuan server (Freeze Lock) darurat, serta grafik komparasi performa penjualan bulanan. Dashboard ini memberikan visibilitas penuh bagi pengambil keputusan strategis.

4.1.13	Laporan Penjualan Omnichannel
Modul analitik pendapatan yang mengonsolidasikan transaksi dari gerai kasir fisik dan toko online. Antarmuka web (Gambar 4.30) dilengkapi penyaring rentang tanggal, filter kanal penjualan, grafik batang perbandingan harian, serta tabel transaksi terperinci.
Tombol cetak menghasilkan Laporan Penjualan (Gambar 4.31) yang memuat kop surat resmi ZenCare Medical, ringkasan total omzet kotor, total potongan harga, total pendapatan bersih, kuantitas produk terjual, tabel rincian transaksi kronologis, serta kolom tanda tangan pengesahan pimpinan toko.
4.1.14	Laporan Persediaan
Modul pelaporan status fisik barang di gudang dan etalase toko. Antarmuka web (Gambar 4.32) menampilkan kartu ringkasan total jenis barang, total nilai aset persediaan berdasarkan harga beli, filter status stok (Aman, Menipis, Habis), serta tabel rincian per produk variasi.
Lembar Cetak Laporan Persediaan (Gambar 4.33) menyajikan dokumen resmi rekapitulasi persediaan barang, merinci kode barang, nama produk, satuan kemasan, saldo fisik, harga pokok perolehan, total nilai aset, serta kolom tanda tangan pengesahan kepala logistik.

4.1.15	Laporan Logistik Medis 
Modul pengawasan masa simpan obat dan alat kesehatan berbasis algoritma First Expired First Out. Antarmuka web (Gambar 4.34) mengelompokkan barang ke dalam 3 (tiga) tab visual: Kedaluwarsa Kritis (≤ 30 hari), Peringatan Dini (≤ 90 hari), dan Aman (> 90 hari), dilengkapi indikator sisa hari masa simpan.
Lembar Cetak Laporan Logistik Medis FEFO (Gambar 4.35) menyajikan dokumen resmi evaluasi masa kedaluwarsa, memuat nama produk medis, nomor batch pabrikan, tanggal kedaluwarsa, jumlah stok tersisa, status risiko, rekomendasi tindakan, serta kolom tanda tangan pengesahan penanggung jawab teknis gerai.
4.1.16	Laporan Penerimaan Barang Masuk
Modul rekapitulasi pengadaan barang dari distributor rekanan. Antarmuka web (Gambar 4.36) memuat penyaring tanggal dan distributor, ringkasan total nilai pasokan yang diterima dalam periode tertentu, serta tabel riwayat penerimaan barang.
Lembar Cetak Laporan Penerimaan Barang Masuk (Gambar 4.37) menyajikan dokumen resmi rekapitulasi pasokan masuk, memuat nomor faktur distributor, waktu penerimaan di gudang, nama staf penerima, rincian produk yang dipasok, nomor batch, harga beli perolehan, dan kolom tanda tangan pengesahan bagian pengadaan logistik.

4.1.17	Laporan Mutasi dan Konversi Satuan
Modul evaluasi pergerakan internal barang, mencakup penyesuaian manual (barang rusak, kedaluwarsa, koreksi hitung) dan pemecahan kemasan multi-UOM. Antarmuka web (Gambar 4.38) menyajikan tab pemisah antara mutasi manual dan konversi satuan, filter rentang waktu, serta rincian alasan perubahan saldo.
Lembar Cetak Laporan Mutasi & Konversi (Gambar 4.39) mencetak dokumen resmi rekapitulasi pergerakan internal, merinci tanggal peristiwa, kode barang, nama produk, kuantitas mutasi, alasan pembebanan, rasio konversi yang diterapkan, identitas petugas pencatat, serta kolom tanda tangan pimpinan.
4.1.18	Laporan Anomali Pengeluaran FEFO
Fitur pengawasan mutakhir yang memantau kepatuhan kasir terhadap rekomendasi batch terdekat (FEFO). Jika kasir mengeluarkan batch yang tanggal kedaluwarsanya lebih jauh sementara batch terdekat masih tersedia, sistem mencatat peristiwa tersebut sebagai anomali. Antarmuka web (Gambar 4.40) menyajikan daftar pelanggaran FEFO, alasan petugas saat override di kasir, dan tingkat kepatuhan.
Lembar Cetak Laporan Deteksi Anomali FEFO (Gambar 4.41) mencetak lembar evaluasi resmi yang merinci nomor transaksi, kasir yang bertugas, nama obat yang mengalami loncatan angkatan, nomor batch yang seharusnya dikeluarkan menurut aturan FEFO, nomor batch yang diambil di lapangan, alasan petugas (misalnya permintaan kemasan khusus konsumen), serta kolom tanda tangan evaluasi oleh penanggung jawab teknis gerai.

4.1.19	Manajemen Pengguna Sistem
Modul tata kelola akun staf internal dan pengguna eksternal sistem ZenCare Medical. Antarmuka direktori pengguna (Gambar 4.42) menyajikan tabel seluruh akun pengguna terdaftar yang mencakup peran Superadmin, Admin, Kasir, dan Pelanggan, status keaktifan akun, serta tanggal registrasi.
Penambahan akun operasional baru dilakukan melalui Formulir Pendaftaran Pengguna Baru dan Penetapan Hak Akses Role RBAC (Gambar 4.43). Formulir ini memfasilitasi perekaman nama pengguna (username), nama lengkap staf, alamat email dinas, kata sandi terenkripsi, serta penetapan hak akses peran berbasis Role-Based Access Control (RBAC). Mekanisme ini memastikan isolasi wewenang yang ketat: Kasir hanya diizinkan mengakses terminal penjualan, Admin berwenang mengelola persediaan dan logistik, sedangkan Superadmin memegang kendali penuh terhadap konfigurasi sistem, pembekuan server, dan persetujuan penyesuaian inventori.

4.1.20	Pengaturan Toko Daring
Modul konfigurasi identitas visual dan operasional toko online ZenCare Medical. Admin dapat mengubah nama toko, logo resmi, favicon peramban, banner promosi karusel beranda, nomor kontak WhatsApp layanan pelanggan, alamat email, serta syarat dan ketentuan transaksi. Perubahan pada halaman ini langsung terefleksi secara langsung pada etalase toko daring yang diakses oleh pelanggan publik.
4.1.21	Pendaftaran dan Masuk Akun Pelanggan
Gerbang autentikasi khusus konsumen toko daring ZenCare Medical yang terpisah dari halaman login staf internal. Antarmuka menyediakan formulir pendaftaran akun baru (nama lengkap, nomor telepon, alamat email, kata sandi) serta formulir masuk akun terdaftar. Sistem menerapkan validasi kata sandi dan proteksi sesi guna melindungi privasi riwayat pesanan serta data profil medis pelanggan.

4.1.22	Profil dan Pemantauan Pesanan Pelanggan
Portal mandiri pelanggan yang menampilkan informasi biodata pengguna, alamat pengiriman default, serta riwayat seluruh transaksi daring yang pernah dilakukan. Pelanggan dapat memantau status pesanan (Menunggu Pembayaran, Dikemas, Dikirim, Selesai), melacak nomor resi kurir, serta melihat kembali rincian komoditas medis yang dibeli secara transparan.

4.1.23	Beranda Toko Daring (E-Commerce)
Halaman muka etalase publik toko online ZenCare Medical yang menyajikan katalog produk obat bebas, alat kesehatan, dan bahan medis habis pakai. Antarmuka dilengkapi banner promosi dinamis, bilah pencarian cepat, navigasi kategori, sorotan produk terlaris, serta indikator stok ketersediaan fisik yang disinkronkan secara langsung dengan saldo gudang toko fisik.

4.1.24	Rincian Informasi Produk (Detail Produk)
Halaman produk spesifik yang menyajikan informasi detail komoditas medis: galeri foto multi-sudut, deskripsi indikasi obat, nomor izin edar Kemenkes/BPOM, petunjuk dosis penggunaan, batas tanggal kedaluwarsa terdekat yang dijamin, pilihan variasi kemasan (eceran/grosir), harga satuan, jumlah pembelian, serta tombol 'Tambah ke Keranjang'.

4.1.25	Panel Keranjang Belanja
Antarmuka penampung sementara produk-produk yang dipilih konsumen sebelum melanjutkan ke tahap pembayaran. Panel menyajikan daftar barang, foto, satuan kemasan yang dipilih, kuantitas yang dapat disesuaikan langsung, subtotal harga, serta kalkulasi ringkasan estimasi total belanja.

4.1.26	Pembayaran dan Pengiriman Pesanan (Checkout)
Tahapan akhir transaksi belanja daring di mana pelanggan memasukkan alamat pengiriman lengkap, memilih opsi layanan kurir ekspedisi logistik, meninjau rincian biaya (subtotal belanja ditambah ongkos kirim), serta memilih gerbang metode pembayaran daring (Transfer Bank, Virtual Account, E-Wallet, atau QRIS). Saat pesanan dikonfirmasi, sistem secara otomatis mereservasi stok produk pada tabel pesanan_batch_alokasi menggunakan prinsip FEFO.
"""

forbidden_patterns = [
    r'\bfarmasi\b',
    r'\bapotek\b',
    r'\bHPP\b',
    r'\bAkuntansi\b',
    r'\bAudit\b',
    r'\bDosen\b',
    r'\bBab 4\b',
    r'\bSidang\b',
    r'\bSempurna\b'
]

print("=== CHECKING USER'S TEXT FOR FORBIDDEN WORDS ===")
violations = []
for pat in forbidden_patterns:
    matches = re.findall(pat, text, flags=re.IGNORECASE)
    if matches:
        violations.append((pat, len(matches), matches))

if violations:
    for v in violations:
        print(f"VIOLATION: {v[0]} -> count {v[1]} ({v[2]})")
else:
    print("Zero forbidden words found in user text!")

