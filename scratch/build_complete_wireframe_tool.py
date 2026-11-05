import json

pages_data = [
    # A. Login Staf
    {
        'id': 'login_staf',
        'category': 'A. Autentikasi Staf',
        'fig': 'Gambar 3.19',
        'title': 'Wireframe Antarmuka Login Staf',
        'sub': 'Pintu gerbang autentikasi back-end internal',
        'role': 'Staf (Multi-Aktor)',
        'png': 'File PPT/Wireframe/Login (Staf).png',
        'tabel': 'users',
        'desc': 'Antarmuka gerbang masuk tunggal bagi pengguna internal sistem (Superadmin, Admin, dan Kasir). Tata letak dirancang terpusat dengan bidang masukan nama pengguna (username) dan kata sandi (password), serta tombol akses utama menuju panel kerja. Sistem secara otomatis membaca peran pengguna yang berhasil masuk dan mengarahkannya ke halaman kerja sesuai dengan batas wewenangnya.'
    },
    # B. Admin
    {
        'id': 'dashboard_admin',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.20',
        'title': 'Wireframe Antarmuka Dashboard Admin',
        'sub': 'Panel kendali pemantauan operasional harian toko',
        'role': 'Admin',
        'png': 'File PPT/Wireframe/Dashboard (Admin).png',
        'tabel': 'penjualan, stok_toko, stok_batch',
        'desc': 'Panel kendali operasional harian bagi staf admin toko. Bagian atas memuat kartu ringkasan untuk memantau status pesanan yang harus diproses dan pesanan yang menunggu diambil langsung di toko. Di bawahnya terdapat indikator ketersediaan stok fisik, peringatan dini sisa persediaan yang menipis, peringatan obat yang mendekati masa kedaluwarsa, grafik volume penjualan harian, serta tombol akses cepat menuju terminal kasir.'
    },
    {
        'id': 'pos',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.21',
        'title': 'Wireframe Antarmuka Terminal Kasir (Point of Sale / POS)',
        'sub': 'Terminal transaksi luring kasir toko fisik',
        'role': 'Kasir / Admin',
        'png': 'File PPT/Wireframe/POS.png',
        'tabel': 'produk_variasi, penjualan, detail_penjualan, stok_batch',
        'desc': 'Terminal transaksi penjualan luring di toko fisik yang menggunakan tata letak dua kolom. Kolom kiri memuat katalog produk interaktif dengan bilah pencarian nama barang atau pemindaian kode batang (barcode), informasi persediaan siap jual, rekomendasi nomor angkatan (batch) obat terdekat kedaluwarsa sesuai aturan FEFO, serta input nomor seri untuk alat kesehatan. Kolom kanan memuat rincian keranjang belanja, pilihan satuan transaksi (eceran atau dus), pilihan metode pembayaran (tunai, debit, atau QRIS), perhitungan uang bayar dan kembalian, serta tombol penyelesaian transaksi sekaligus cetak struk.'
    },
    {
        'id': 'transaksi',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.22',
        'title': 'Wireframe Antarmuka Transaksi Omnichannel',
        'sub': 'Pusat manajemen pesanan lintas saluran (POS & Daring)',
        'role': 'Admin',
        'png': 'File PPT/Wireframe/Transaksi.png',
        'tabel': 'penjualan, detail_penjualan, pesanan_batch_alokasi',
        'desc': 'Pusat pengelolaan faktur penjualan dari dua saluran (toko fisik dan pesanan daring). Antarmuka dilengkapi tab filter saluran (E-Commerce atau Kasir POS), filter tanggal transaksi, pemantauan tahapan pesanan (Menunggu Bayar, Diproses, Dikirim, Selesai, atau Batal), serta tombol tindakan untuk memverifikasi pesanan, mencetak faktur penjualan, dan mencetak label resi pengiriman kurir.'
    },
    {
        'id': 'produk',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.23',
        'title': 'Wireframe Antarmuka Master Produk',
        'sub': 'Direktori katalog dasar komoditas obat & alat kesehatan',
        'role': 'Admin',
        'png': 'File PPT/Wireframe/Produk.png',
        'tabel': 'produk_induk, produk_variasi',
        'desc': 'Antarmuka direktori katalog dasar untuk mengelola informasi utama komoditas obat dan alat kesehatan. Halaman ini memuat tabel daftar barang (kode produk, nama produk, kategori, spesifikasi medis, aturan penyimpanan, dan batas hari peringatan kedaluwarsa), bilah pencarian cepat, filter kategori, serta tombol untuk membuka formulir penambahan dan pengubahan data produk.'
    },
    {
        'id': 'satuan_uom',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.24',
        'title': 'Wireframe Antarmuka Satuan & Multi-UOM',
        'sub': 'Konfigurasi rasio kemasan besar dus vs eceran',
        'role': 'Admin',
        'png': 'File PPT/Wireframe/Satuan & UOM.png',
        'tabel': 'produk_variasi',
        'desc': 'Panel pengaturan logika multi-satuan (Multi-UOM) yang memetakan hubungan antara kemasan besar dari pabrik (dus atau box) dengan satuan eceran (strip atau botol). Tabel menyajikan angka pengali rasio konversi, penetapan harga jual untuk masing-masing ukuran kemasan, batas stok aman minimum, serta berat barang dalam satuan gram untuk keperluan perhitungan ongkos kirim.'
    },
    {
        'id': 'supplier',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.25',
        'title': 'Wireframe Antarmuka Direktori Supplier',
        'sub': 'Basis data rekanan distributor & penyedia barang medis',
        'role': 'Admin',
        'png': 'File PPT/Wireframe/Supplier.png',
        'tabel': 'supplier',
        'desc': 'Rancangan tata letak direktori mitra logistik dan distributor penyedia obat dan alat kesehatan. Menampilkan daftar instansi distributor rekanan, narahubung resmi, nomor telepon, alamat surel korespondensi, alamat fisik kantor atau gudang, serta status keaktifan hubungan kerja sama.'
    },
    {
        'id': 'penerimaan',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.26',
        'title': 'Wireframe Antarmuka Penerimaan Barang (Inbound)',
        'sub': 'Gerbang pencatatan stok masuk, nomor batch, & expired date',
        'role': 'Admin / Gudang',
        'png': 'File PPT/Wireframe/Penerimaan Barang.png',
        'tabel': 'penerimaan_stok, penerimaan_detail, stok_batch, unit_serial',
        'desc': 'Formulir pencatatan pasokan fisik barang yang tiba di gudang. Memfasilitasi staf dalam memasukkan nomor faktur pengiriman distributor, memilih variasi produk, menginput nomor batch resmi pabrikan, menetapkan tanggal kedaluwarsa, jumlah kuantitas fisik yang diterima, harga beli, serta pendaftaran nomor seri unik jika barang merupakan komoditas alat kesehatan.'
    },
    {
        'id': 'mutasi',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.27',
        'title': 'Wireframe Antarmuka Mutasi Stok Manual',
        'sub': 'Penyesuaian stok non-transaksi (rusak, hilang, koreksi)',
        'role': 'Admin / Gudang',
        'png': 'File PPT/Wireframe/Mutasi Stok.png',
        'tabel': 'kartu_stok, stok_batch',
        'desc': 'Antarmuka formulir penyesuaian persediaan fisik di luar transaksi penjualan reguler. Memfasilitasi staf dalam mencatat mutasi masuk atau keluar persediaan, memilih nomor batch yang disesuaikan, serta mendokumentasikan alasan operasional (seperti barang rusak, kemasan cacat, atau koreksi selisih hitung fisik), lengkap dengan tabel riwayat mutasi terkini pada sisi kanan.'
    },
    {
        'id': 'kartu_stok',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.28',
        'title': 'Wireframe Antarmuka Kartu Stok Audit',
        'sub': 'Buku besar kronologis mutasi & transparansi riwayat fisik',
        'role': 'Admin / Superadmin',
        'png': 'File PPT/Wireframe/Kartu Stok.png',
        'tabel': 'kartu_stok',
        'desc': 'Buku besar kronologis persediaan yang mencatat jejak setiap mutasi masuk, keluar, dan sisa stok fisik. Antarmuka dilengkapi bilah filter rentang tanggal, filter produk, filter jenis mutasi, serta kolom penanggung jawab operasional guna menjamin keterlacakan audit persediaan barang secara transparan.'
    },
    {
        'id': 'stok_opname',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.29',
        'title': 'Wireframe Antarmuka Stok Opname & Audit Fisik Persediaan',
        'sub': 'Pencacahan fisik berkala & pembekuan transaksi (Freeze Lock)',
        'role': 'Admin / Gudang',
        'png': 'File PPT/Wireframe/Stok Opname.png',
        'tabel': 'sesi_opname, detail_opname_batch, detail_opname_sn, riwayat_hitung_opname',
        'desc': 'Panel pelaksanaan pemeriksaan persediaan fisik berkala guna menyinkronkan catatan sistem dengan kondisi nyata di rak toko. Dilengkapi indikator penguncian transaksi operasional sementara (freeze lock) pada kasir POS dan toko daring, kartu ringkasan status sesi, lembar kerja pencacahan fisik obat tanpa melihat saldo buku sistem (metode blind count) untuk menghindari bias perhitungan, pencacahan nomor seri fisik alat kesehatan, tombol eksekusi penyesuaian selisih ke buku besar, serta tombol cetak Berita Acara Opname resmi.'
    },
    {
        'id': 'konversi_satuan',
        'category': 'B. Antarmuka Admin & Kasir',
        'fig': 'Gambar 3.30',
        'title': 'Wireframe Antarmuka Konversi Satuan & Riwayat Pecah Dus',
        'sub': 'Operasional pembongkaran kemasan dus besar ke eceran',
        'role': 'Admin / Kasir',
        'png': 'File PPT/Wireframe/Konversi Satuan.png',
        'tabel': 'histori_konversi, produk_variasi, stok_toko, stok_batch',
        'desc': 'Panel operasional untuk memecah kemasan besar (dus atau box) menjadi satuan eceran (strip atau botol) secara langsung saat stok eceran di rak toko habis. Formulir memuat pilihan nama obat, kotak informasi rasio konversi, pilihan batch dus yang dibongkar, jumlah dus yang dibuka, hasil eceran otomatis, pembentukan nomor batch pecahan baru yang terhubung ke batch induk, pemutakhiran saldo rak secara langsung, serta tabel catatan riwayat pembongkaran kemasan.'
    },
    # C. Superadmin
    {
        'id': 'dashboard_superadmin',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.31',
        'title': 'Wireframe Antarmuka Dashboard Superadmin',
        'sub': 'Pusat pengawasan makro bisnis dan komparasi saluran',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Dashboard (Superadmin).png',
        'tabel': 'penjualan, kartu_stok',
        'desc': 'Pusat pengawasan menyeluruh yang dirancang khusus untuk pemilik usaha. Menampilkan grafik perbandingan volume penjualan antara transaksi toko fisik (luring) dan pesanan toko daring (e-commerce), ringkasan nilai perputaran persediaan barang, serta pemberitahuan kritis logistik tanpa menampilkan detail teknis harian yang rumit.'
    },
    {
        'id': 'lap_penjualan',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.32',
        'title': 'Wireframe Antarmuka Laporan Penjualan Barang',
        'sub': 'Rekapitulasi volume arus barang keluar lintas saluran',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Laporan Penjualan Barang.png',
        'tabel': 'penjualan, detail_penjualan',
        'desc': 'Laporan rekapitulasi jumlah barang keluar dari seluruh saluran penjualan. Dilengkapi filter tanggal dan pilihan saluran penjualan, serta tabel rincian transaksi untuk menganalisis produk yang paling banyak terjual dan melihat kontribusi masing-masing saluran penjualan terhadap omset bisnis.'
    },
    {
        'id': 'lap_persediaan',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.33',
        'title': 'Wireframe Antarmuka Laporan Persediaan',
        'sub': 'Pemantauan aset gudang dengan kalkulasi multi-satuan terpadu',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Laporan Persediaan.png',
        'tabel': 'stok_toko, stok_batch, produk_variasi',
        'desc': 'Laporan pemantauan ketersediaan barang di gudang secara menyeluruh. Menampilkan perhitungan kuantitas dalam format multi-satuan terpadu (menjabarkan sisa kemasan besar dus dan satuan eceran pada satu baris data yang sama), indikator status kecukupan stok, serta perkiraan total aset persediaan fisik.'
    },
    {
        'id': 'lap_logistik_medis',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.34',
        'title': 'Wireframe Antarmuka Laporan Logistik Medis',
        'sub': 'Audit masa simpan batch FEFO dan pelacakan serial alkes',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Laporan Logistik Medis.png',
        'tabel': 'stok_batch, unit_serial',
        'desc': 'Laporan pengawasan khusus komoditas medis yang memisahkan dua fokus utama. Tab Sediaan Obat memantau masa simpan per nomor batch dengan urutan kedaluwarsa terdekat (metode FEFO) beserta peringatan batas aman edar, sedangkan Tab Alat Kesehatan melacak status keberadaan alat berdasarkan nomor seri fisik tunggal (tersedia, terjual, atau dalam klaim garansi).'
    },
    {
        'id': 'lap_penerimaan_barang',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.35',
        'title': 'Wireframe Antarmuka Laporan Penerimaan Barang',
        'sub': 'Audit inbound pasokan obat & alat kesehatan dari distributor',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Laporan Penerimaan Barang.png',
        'tabel': 'penerimaan_stok, penerimaan_detail, supplier, stok_batch',
        'desc': 'Laporan eksekutif yang merekapitulasi seluruh pengiriman barang medis dari distributor resmi (PBF / rekanan). Antarmuka memuat bilah filter rentang tanggal terima, pilihan distributor, dan status penerimaan (sempurna atau retur parsial). Bagian ringkasan menyajikan kartu metrik total faktur inbound, nominal belanja persediaan, total dus diterima, serta kuantitas barang rusak atau diretur. Tabel data memuat nomor referensi surat jalan, tanggal terima, nama distributor, variasi produk, nomor batch pabrikan, tanggal kedaluwarsa, kuantitas pesan dan terima, harga beli, status penerimaan, dan identitas staf penerima guna memastikan transparansi pengadaan sebelum pembayaran disetujui.'
    },
    {
        'id': 'lap_mutasi_konversi',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.36',
        'title': 'Wireframe Antarmuka Laporan Mutasi & Konversi Satuan',
        'sub': 'Audit pemecahan dus (UOM) & penyesuaian non-transaksional',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Laporan Mutasi & Konversi.png',
        'tabel': 'kartu_stok, histori_konversi, produk_variasi, stok_batch',
        'desc': 'Pusat audit manajerial yang menyatukan dua modul pergerakan persediaan non-transaksional melalui navigasi tab interaktif. Tab Konversi Satuan memantau pembongkaran kemasan dus besar menjadi satuan eceran (strip atau pcs), menyajikan nomor batch asal, nomor sub-batch eceran baru yang terbentuk, rasio pengali, jumlah dus yang dibongkar, kuantitas eceran yang dihasilkan, dan sisa dus di rak gudang. Tab Mutasi Stok memantau penyesuaian fisik persediaan di luar transaksi (seperti barang rusak, kedaluwarsa, atau selisih opname fisik) lengkap dengan nomor referensi dokumen, jenis mutasi, kuantitas penyesuaian, dan alasan operasional staf.'
    },
    {
        'id': 'lap_anomali_fefo',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.37',
        'title': 'Wireframe Antarmuka Laporan Audit Anomali FEFO',
        'sub': 'Audit kepatuhan kasir & deviasi pengambilan batch obat',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Laporan Anomali FEFO.png',
        'tabel': 'log_anomali_fefo',
        'desc': 'Laporan audit kepatuhan kasir yang mencatat setiap kejadian ketika petugas kasir mengambil nomor batch obat yang berbeda dari anjuran kedaluwarsa terdekat pada sistem. Menampilkan filter rentang tanggal, tabel daftar kejadian (tanggal, nomor faktur, nama kasir, nomor batch yang diambil, nomor batch anjuran sistem, selisih hari kedaluwarsa, alasan kasir, dan status verifikasi) guna memastikan mutu obat tetap terjaga dan mencegah obat kedaluwarsa tertinggal di rak.'
    },
    {
        'id': 'manajemen_user',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.38',
        'title': 'Wireframe Antarmuka Manajemen Pengguna',
        'sub': 'Administrasi akun dan pembagian wewenang hak akses',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Manajemen Pengguna.png',
        'tabel': 'users',
        'desc': 'Pusat pengelolaan akun pengguna dan hak akses operasional sistem. Menampilkan daftar pengguna internal (Superadmin, Admin, Kasir) dan pelanggan e-commerce, indikator keaktifan akun, formulir pendaftaran staf baru, serta pengaturan kata sandi untuk menegakkan keamanan dan batasan tugas masing-masing pihak.'
    },
    {
        'id': 'pengaturan_web',
        'category': 'C. Antarmuka Superadmin (Pemilik)',
        'fig': 'Gambar 3.39',
        'title': 'Wireframe Antarmuka Pengaturan Web & Konten Toko',
        'sub': 'Panel CMS konfigurasi identitas dan media visual toko',
        'role': 'Superadmin',
        'png': 'File PPT/Wireframe/Pengaturan Web.png',
        'tabel': 'pengaturan_web',
        'desc': 'Panel mandiri untuk memperbarui identitas dan tampilan portal toko daring. Memfasilitasi Superadmin dalam mengubah nama toko, nomor telepon layanan pelanggan, nomor WhatsApp, alamat fisik apotek, tautan gambar logo, serta berkas spanduk promosi beranda tanpa memengaruhi data logistik persediaan.'
    },
    # D. Pelanggan
    {
        'id': 'login_pelanggan',
        'category': 'D. Antarmuka Pelanggan (E-Commerce)',
        'fig': 'Gambar 3.40',
        'title': 'Wireframe Antarmuka Login Pelanggan',
        'sub': 'Pintu masuk autentikasi pengguna toko daring',
        'role': 'Pelanggan',
        'png': 'File PPT/Wireframe/Login (Pelanggan).png',
        'tabel': 'users',
        'desc': 'Pintu masuk bagi pembeli daring untuk memasukkan nama akun dan kata sandi sebelum melanjutkan keranjang belanja ke proses pembayaran. Halaman ini juga menyediakan tautan menuju formulir pendaftaran akun baru bagi pembeli yang belum terdaftar.'
    },
    {
        'id': 'profil_pelanggan',
        'category': 'D. Antarmuka Pelanggan (E-Commerce)',
        'fig': 'Gambar 3.41',
        'title': 'Wireframe Antarmuka Akun & Profil Pelanggan',
        'sub': 'Pengelolaan data diri, alamat, & pelacakan pesanan',
        'role': 'Pelanggan',
        'png': 'File PPT/Wireframe/Profil.png',
        'tabel': 'users, penjualan',
        'desc': 'Panel akun pribadi pelanggan yang memuat formulir pengelolaan data diri dan daftar alamat pengiriman, serta tabel riwayat pesanan belanja daring untuk memantau status pengiriman paket pesanan, mengecek nomor resi kurir, dan melihat bukti transaksi.'
    },
    {
        'id': 'ecommerce_home',
        'category': 'D. Antarmuka Pelanggan (E-Commerce)',
        'fig': 'Gambar 3.42',
        'title': 'Wireframe Antarmuka Beranda E-Commerce',
        'sub': 'Katalog publik etalase produk obat & alat kesehatan',
        'role': 'Pelanggan / Pengunjung',
        'png': 'File PPT/Wireframe/E-Commerce.png',
        'tabel': 'produk_induk, produk_variasi, stok_toko',
        'desc': 'Halaman utama toko daring yang dirancang untuk mempermudah pencarian produk kesehatan. Memuat bilah pencarian obat dan alkes, spanduk promosi utama, daftar kategori produk medis, serta susunan katalog produk unggulan dalam format kotak (grid) interaktif.'
    },
    {
        'id': 'detail_produk',
        'category': 'D. Antarmuka Pelanggan (E-Commerce)',
        'fig': 'Gambar 3.43',
        'title': 'Wireframe Antarmuka Detail Produk',
        'sub': 'Spesifikasi medis, pemilihan variasi kemasan, & stok',
        'role': 'Pelanggan',
        'png': 'File PPT/Wireframe/Detail Produk.png',
        'tabel': 'produk_induk, produk_variasi, stok_batch',
        'desc': 'Halaman rincian barang medis yang menampilkan foto produk, penjelasan manfaat obat, spesifikasi teknis alat kesehatan, harga jual eceran maupun harga grosir dus, status ketersediaan stok fisik terkini, tombol pengatur jumlah beli, serta tombol untuk memasukkan produk ke keranjang belanja.'
    },
    {
        'id': 'cart',
        'category': 'D. Antarmuka Pelanggan (E-Commerce)',
        'fig': 'Gambar 3.44',
        'title': 'Wireframe Antarmuka Keranjang Belanja',
        'sub': 'Panel samping (drawer) ringkasan belanja interaktif',
        'role': 'Pelanggan',
        'png': 'File PPT/Wireframe/Keranjang Belanja.png',
        'tabel': 'produk_variasi, stok_toko',
        'desc': 'Panel keranjang belanja melayang (slide-out drawer) yang menampilkan ringkasan daftar barang yang dipilih, pengaturan jumlah pesanan, penghapusan item, dan perhitungan subtotal belanja tanpa membuat pelanggan berpindah dari halaman katalog produk.'
    },
    {
        'id': 'checkout',
        'category': 'D. Antarmuka Pelanggan (E-Commerce)',
        'fig': 'Gambar 3.45',
        'title': 'Wireframe Antarmuka Checkout Pembayaran',
        'sub': 'Konfirmasi pesanan, integrasi kurir, & pembayaran QRIS',
        'role': 'Pelanggan',
        'png': 'File PPT/Wireframe/Checkout Pembayaran.png',
        'tabel': 'penjualan, detail_penjualan',
        'desc': 'Halaman akhir pemesanan daring yang memfasilitasi pengisian data penerima, integrasi pemilihan kurir ekspedisi berbasis kota tujuan, penentuan titik lokasi pengantaran dengan pin peta digital, pilihan metode pengambilan (diantar kurir atau diambil di toko), kalkulasi otomatis subtotal dan biaya ongkos kirim, serta tombol pembayaran yang terhubung langsung ke gerbang pembayaran digital.'
    }
]

html_template = f'''<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wireframe Arsitektur Sistem - ZenCare Medical (Bab 3.6)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=DM+Mono:wght@400;500;600&family=Times+New+Roman&display=swap" rel="stylesheet">
    <style>
        * {{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }}

        body {{
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }}

        /* Top Header Bar */
        .top-navbar {{
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            z-index: 10;
        }}

        .top-title h1 {{
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }}

        .top-title p {{
            font-size: 12px;
            color: #64748b;
        }}

        .nav-links {{
            display: flex;
            gap: 8px;
            align-items: center;
        }}

        .btn-link {{
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }}

        .btn-outline {{
            background: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }}

        .btn-outline:hover {{
            background: #e2e8f0;
            color: #0f172a;
        }}

        .btn-primary {{
            background: #2563eb;
            color: #ffffff;
        }}

        .btn-primary:hover {{
            background: #1d4ed8;
        }}

        /* Main Workspace Split */
        .workspace {{
            display: flex;
            flex: 1;
            overflow: hidden;
        }}

        /* Sidebar Navigation */
        .sidebar {{
            width: 320px;
            background: #ffffff;
            border-right: 1px solid #cbd5e1;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }}

        .sidebar-header {{
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }}

        .search-box {{
            width: 100%;
            padding: 7px 10px;
            font-size: 11.5px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-family: inherit;
        }}

        .search-box:focus {{
            border-color: #2563eb;
        }}

        .sidebar-list {{
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }}

        .cat-header {{
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px 4px 16px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #ffffff;
            position: sticky;
            top: 0;
            z-index: 2;
        }}

        .nav-item {{
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }}

        .nav-item:hover {{
            background: #f1f5f9;
        }}

        .nav-item.active {{
            background: #eff6ff;
            border-left-color: #2563eb;
        }}

        .nav-badge-fig {{
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #475569;
            font-family: monospace;
            flex-shrink: 0;
        }}

        .nav-item.active .nav-badge-fig {{
            background: #dbeafe;
            color: #1e40af;
        }}

        .nav-text-col {{
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }}

        .nav-item-title {{
            font-size: 11.5px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }}

        .nav-item.active .nav-item-title {{
            color: #1d4ed8;
            font-weight: 700;
        }}

        .nav-item-role {{
            font-size: 10px;
            color: #64748b;
        }}

        /* Content Area */
        .viewport {{
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            overflow: hidden;
        }}

        .viewport-topbar {{
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }}

        .vp-title-col h2 {{
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
        }}

        .vp-title-col p {{
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }}

        .vp-actions {{
            display: flex;
            gap: 8px;
            align-items: center;
        }}

        .role-tag {{
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }}

        .viewport-body {{
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }}

        /* Image Display Frame */
        .image-frame {{
            background: #ffffff;
            border: 2px solid #0f172a;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 1100px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px;
        }}

        .image-frame img {{
            width: 100%;
            height: auto;
            display: block;
            border: 1px solid #000;
        }}

        /* Narrative Metadata Card */
        .meta-card {{
            max-width: 1100px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 18px 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            font-family: 'Times New Roman', Times, serif;
        }}

        .meta-header {{
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }}

        .meta-caption {{
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
        }}

        .meta-desc {{
            font-size: 13px;
            line-height: 1.6;
            text-align: justify;
            color: #1e293b;
            margin-bottom: 12px;
        }}

        .meta-table-ref {{
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            color: #475569;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
        }}

        .copy-toast {{
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.2s;
            pointer-events: none;
            z-index: 100;
        }}

        .copy-toast.show {{
            opacity: 1;
            transform: translateY(0);
        }}
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="top-title">
            <h1>Wireframe Arsitektur Sistem &mdash; ZenCare Medical</h1>
            <p>Subbab 3.6 Laporan Tugas Akhir &bull; Katalog 25 Wireframe Omnichannel Lengkap</p>
        </div>
        <div class="nav-links">
            <a href="../laporan_ta/BAB_III_ZENCARE_MEDICAL.html#sec_3_6_wrapper" class="btn-link btn-outline">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Laporan Bab 3
            </a>
            <a href="kamus_data_zencare.html" target="_blank" class="btn-link btn-outline">
                Kamus Data (Subbab 3.5)
            </a>
            <a href="logical_database_zencare.html" target="_blank" class="btn-link btn-outline">
                Logical Database (Subbab 3.4)
            </a>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="workspace">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <input type="text" id="search_input" oninput="filterWireframes()" placeholder="Cari nama halaman / role..." class="search-box">
            </div>
            <div class="sidebar-list" id="sidebar_list">
                <!-- Injected via JavaScript -->
            </div>
        </aside>

        <!-- Viewport Display -->
        <main class="viewport">
            <div class="viewport-topbar">
                <div class="vp-title-col">
                    <h2 id="vp_title">
                        <span id="vp_fig" class="nav-badge-fig">Gambar 3.19</span>
                        <span id="vp_name">Wireframe Antarmuka Login Staf</span>
                    </h2>
                    <p id="vp_sub">Pintu gerbang autentikasi back-end internal</p>
                </div>
                <div class="vp-actions">
                    <span id="vp_role" class="role-tag">Role: Staf</span>
                    <button onclick="copyCaption()" class="btn-link btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Salin Keterangan Gambar
                    </button>
                    <a id="vp_download" href="#" download class="btn-link btn-outline">
                        Unduh PNG
                    </a>
                </div>
            </div>

            <div class="viewport-body">
                <!-- Image Display Box -->
                <div class="image-frame">
                    <img id="vp_img" src="" alt="Wireframe Preview">
                </div>

                <!-- Academic Metadata Card -->
                <div class="meta-card">
                    <div class="meta-header">
                        <span>Deskripsi Fungsional &amp; Tata Letak Antarmuka</span>
                        <span id="vp_index_indicator" style="font-family: monospace;">01 / 25</span>
                    </div>
                    <div class="meta-caption" id="meta_caption">
                        Gambar 3.19 Wireframe Antarmuka Login Staf
                    </div>
                    <p class="meta-desc" id="meta_desc">
                        Deskripsi wireframe...
                    </p>
                    <div class="meta-table-ref">
                        <b>Entitas Basis Data Terkait:</b> <code id="meta_tables">users</code>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="copy-toast" id="toast">Keterangan Gambar berhasil disalin ke clipboard!</div>

    <script>
        const wireframes = {json.dumps(pages_data, ensure_ascii=False, indent=2)};

        let currentIndex = 0;

        function initSidebar() {{
            const list = document.getElementById('sidebar_list');
            list.innerHTML = '';

            let currentCat = '';
            wireframes.forEach((wf, idx) => {{
                if (wf.category !== currentCat) {{
                    currentCat = wf.category;
                    const catEl = document.createElement('div');
                    catEl.className = 'cat-header';
                    catEl.textContent = currentCat;
                    list.appendChild(catEl);
                }}

                const item = document.createElement('div');
                item.className = 'nav-item' + (idx === 0 ? ' active' : '');
                item.id = `nav_item_${{idx}}`;
                item.onclick = () => selectWireframe(idx);
                item.innerHTML = `
                    <span class="nav-badge-fig">${{wf.fig.replace('Gambar ', 'G.')}}</span>
                    <div class="nav-text-col">
                        <span class="nav-item-title">${{wf.title.replace('Wireframe Antarmuka ', '')}}</span>
                        <span class="nav-item-role">${{wf.role}}</span>
                    </div>
                `;
                list.appendChild(item);
            }});
        }}

        function selectWireframe(idx) {{
            currentIndex = idx;
            const wf = wireframes[idx];

            // Update Sidebar active state
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            const activeEl = document.getElementById(`nav_item_${{idx}}`);
            if (activeEl) activeEl.classList.add('active');

            // Update Viewport Topbar
            document.getElementById('vp_fig').textContent = wf.fig;
            document.getElementById('vp_name').textContent = wf.title;
            document.getElementById('vp_sub').textContent = wf.sub;
            document.getElementById('vp_role').textContent = 'Role: ' + wf.role;

            // Update Image
            const imgEl = document.getElementById('vp_img');
            imgEl.src = wf.png;
            imgEl.alt = wf.title;

            // Update Download link
            const dlBtn = document.getElementById('vp_download');
            dlBtn.href = wf.png;
            dlBtn.download = `${{wf.fig}}_${{wf.title.replace(/[^a-zA-Z0-9_-]/g, '_')}}.png`;

            // Update Academic Metadata
            document.getElementById('vp_index_indicator').textContent = `${{String(idx + 1).padStart(2, '0')}} / ${{String(wireframes.length).padStart(2, '0')}}`;
            document.getElementById('meta_caption').textContent = `${{wf.fig}} ${{wf.title}}`;
            document.getElementById('meta_desc').textContent = wf.desc;
            document.getElementById('meta_tables').textContent = wf.tabel;
        }}

        function filterWireframes() {{
            const q = document.getElementById('search_input').value.toLowerCase();
            document.querySelectorAll('.nav-item').forEach((el, idx) => {{
                const wf = wireframes[idx];
                const match = wf.title.toLowerCase().includes(q) || wf.sub.toLowerCase().includes(q) || wf.role.toLowerCase().includes(q) || wf.fig.toLowerCase().includes(q);
                el.style.display = match ? 'flex' : 'none';
            }});
        }}

        function copyCaption() {{
            const wf = wireframes[currentIndex];
            const textToCopy = `${{wf.fig}} ${{wf.title}}\\n${{wf.desc}}\\n(Entitas Terkait: ${{wf.tabel}})`;
            navigator.clipboard.writeText(textToCopy).then(() => {{
                showToast('Keterangan ' + wf.fig + ' berhasil disalin!');
            }});
        }}

        function showToast(msg) {{
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }}

        // Keyboard arrow navigation
        window.addEventListener('keydown', (e) => {{
            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {{
                if (currentIndex < wireframes.length - 1) selectWireframe(currentIndex + 1);
            }} else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {{
                if (currentIndex > 0) selectWireframe(currentIndex - 1);
            }}
        }});

        // Initialize
        initSidebar();
        selectWireframe(0);
    </script>
</body>
</html>'''

with open('lain/wireframe_zencare.html', 'w', encoding='utf-8') as f:
    f.write(html_template)

print(f"Generated comprehensive lain/wireframe_zencare.html with {len(pages_data)} wireframes!")

