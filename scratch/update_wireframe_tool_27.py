# -*- coding: utf-8 -*-
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

print(f"Total pages: {len(pages_data)}")

# Let's generate wireframe_zencare.html
# Read template from scratch/build_complete_wireframe_tool.py and replace pages_data
with open(r'scratch\build_complete_wireframe_tool.py', 'r', encoding='utf-8') as f:
    orig_code = f.read()

# Replace the data array in the script
import re
new_data_str = "pages_data = " + json.dumps(pages_data, indent=4, ensure_ascii=False)
pattern = r"pages_data = \[\s*# A\. Login Staf[\s\S]*?\]\s*def generate_wireframe_viewer\(\):"
replacement = new_data_str + "\n\ndef generate_wireframe_viewer():"

new_code = re.sub(pattern, replacement, orig_code)

with open(r'scratch\build_complete_wireframe_tool.py', 'w', encoding='utf-8') as f:
    f.write(new_code)

print("Updated scratch/build_complete_wireframe_tool.py")

