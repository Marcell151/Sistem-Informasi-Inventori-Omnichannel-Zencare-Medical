# -*- coding: utf-8 -*-
# Python script to build Subbab 3.6 Wireframe with 27 interfaces and inject into laporan_ta/BAB_III_ZENCARE_MEDICAL.html
import os
import re

def generate_subbab_3_6_html():
    wf_items = [
        # A. Login Staf
        {
            'section': 'A. Login Staf',
            'fig': 'Gambar 3.19',
            'title': 'Wireframe Antarmuka Login Staf',
            'img': '../lain/File PPT/Wireframe/Login (Staf).png',
            'desc': 'Antarmuka gerbang masuk tunggal bagi pengguna internal sistem (Superadmin, Admin, dan Kasir). Tata letak dirancang terpusat dengan bidang masukan nama pengguna (username) dan kata sandi (password), serta tombol akses utama menuju panel kerja. Sistem secara otomatis membaca peran pengguna yang berhasil masuk dan mengarahkannya ke halaman kerja sesuai dengan batas wewenangnya.'
        },
        # B. Admin
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '1. Dashboard Admin',
            'fig': 'Gambar 3.20',
            'title': 'Wireframe Antarmuka Dashboard Admin',
            'img': '../lain/File PPT/Wireframe/Dashboard (Admin).png',
            'desc': 'Panel kendali operasional harian bagi staf admin toko. Bagian atas memuat kartu ringkasan untuk memantau status pesanan yang harus diproses dan pesanan yang menunggu diambil langsung di toko. Di bawahnya terdapat indikator ketersediaan stok fisik, peringatan dini sisa persediaan yang menipis, peringatan obat yang mendekati masa kedaluwarsa, grafik volume penjualan harian, serta tombol akses cepat menuju terminal kasir.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '2. Terminal Kasir (Point of Sale / POS)',
            'fig': 'Gambar 3.21',
            'title': 'Wireframe Antarmuka Terminal Kasir (Point of Sale / POS)',
            'img': '../lain/File PPT/Wireframe/POS.png',
            'desc': 'Terminal transaksi penjualan luring di toko fisik yang menggunakan tata letak dua kolom. Kolom kiri memuat katalog produk interaktif dengan bilah pencarian nama barang atau pemindaian kode batang (barcode), informasi persediaan siap jual, rekomendasi nomor angkatan (batch) obat terdekat kedaluwarsa sesuai aturan FEFO, serta input nomor seri untuk alat kesehatan. Kolom kanan memuat rincian keranjang belanja, pilihan satuan transaksi (eceran atau dus), pilihan metode pembayaran (tunai, debit, atau QRIS), perhitungan uang bayar dan kembalian, serta tombol penyelesaian transaksi sekaligus cetak struk.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '3. Transaksi Omnichannel',
            'fig': 'Gambar 3.22',
            'title': 'Wireframe Antarmuka Transaksi Omnichannel',
            'img': '../lain/File PPT/Wireframe/Transaksi.png',
            'desc': 'Pusat pengelolaan faktur penjualan dari dua saluran (toko fisik dan pesanan daring). Antarmuka dilengkapi tab filter saluran (E-Commerce atau Kasir POS), filter tanggal transaksi, pemantauan tahapan pesanan (Menunggu Bayar, Diproses, Dikirim, Selesai, atau Batal), serta tombol tindakan untuk memverifikasi pesanan, mencetak faktur penjualan, dan mencetak label resi pengiriman kurir.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '4. Master Produk',
            'fig': 'Gambar 3.23',
            'title': 'Wireframe Antarmuka Master Produk',
            'img': '../lain/File PPT/Wireframe/Produk.png',
            'desc': 'Antarmuka direktori katalog dasar untuk mengelola informasi utama komoditas obat dan alat kesehatan. Halaman ini memuat tabel daftar barang (kode produk, nama produk, kategori, spesifikasi medis, aturan penyimpanan, dan batas hari peringatan kedaluwarsa), bilah pencarian cepat, filter kategori, serta tombol untuk membuka formulir penambahan dan pengubahan data produk.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '5. Satuan & Konversi Multi-UOM',
            'fig': 'Gambar 3.24',
            'title': 'Wireframe Antarmuka Satuan & Konversi Multi-UOM',
            'img': '../lain/File PPT/Wireframe/Satuan & UOM.png',
            'desc': 'Panel pengaturan logika multi-satuan (Multi-UOM) yang memetakan hubungan antara kemasan besar dari pabrik (dus atau box) dengan satuan eceran (strip atau botol). Tabel menyajikan angka pengali rasio konversi, penetapan harga jual untuk masing-masing ukuran kemasan, batas stok aman minimum, serta berat barang dalam satuan gram untuk keperluan perhitungan ongkos kirim.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '6. Direktori Supplier',
            'fig': 'Gambar 3.25',
            'title': 'Wireframe Antarmuka Direktori Supplier',
            'img': '../lain/File PPT/Wireframe/Supplier.png',
            'desc': 'Rancangan tata letak direktori mitra logistik dan distributor penyedia obat dan alat kesehatan. Menampilkan daftar instansi distributor rekanan, narahubung resmi, nomor telepon, alamat surel korespondensi, alamat fisik kantor atau gudang, serta status keaktifan hubungan kerja sama.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '7. Penerimaan Barang (Inbound)',
            'fig': 'Gambar 3.26',
            'title': 'Wireframe Antarmuka Penerimaan Barang (Inbound)',
            'img': '../lain/File PPT/Wireframe/Penerimaan Barang.png',
            'desc': 'Formulir pencatatan pasokan fisik barang yang tiba di gudang. Memfasilitasi staf dalam memasukkan nomor faktur pengiriman distributor, memilih variasi produk, menginput nomor batch resmi pabrikan, menetapkan tanggal kedaluwarsa, jumlah kuantitas fisik yang diterima, harga beli, serta pendaftaran nomor seri unik jika barang merupakan komoditas alat kesehatan.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '8. Mutasi Stok Manual',
            'fig': 'Gambar 3.27',
            'title': 'Wireframe Antarmuka Mutasi Stok Manual',
            'img': '../lain/File PPT/Wireframe/Mutasi Stok.png',
            'desc': 'Antarmuka formulir penyesuaian persediaan fisik di luar transaksi penjualan reguler. Memfasilitasi staf dalam mencatat mutasi masuk atau keluar persediaan, memilih nomor batch yang disesuaikan, serta mendokumentasikan alasan operasional (seperti barang rusak, kemasan cacat, atau koreksi selisih hitung fisik), lengkap dengan tabel riwayat mutasi terkini pada sisi kanan.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '9. Kartu Stok Audit',
            'fig': 'Gambar 3.28',
            'title': 'Wireframe Antarmuka Kartu Stok Audit',
            'img': '../lain/File PPT/Wireframe/Kartu Stok.png',
            'desc': 'Buku besar kronologis persediaan yang mencatat jejak setiap mutasi masuk, keluar, dan sisa stok fisik. Antarmuka dilengkapi bilah filter rentang tanggal, filter produk, filter jenis mutasi, serta kolom penanggung jawab operasional guna menjamin keterlacakan audit persediaan barang secara transparan.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '10. Stok Opname & Audit Fisik',
            'fig': 'Gambar 3.29',
            'title': 'Wireframe Antarmuka Stok Opname & Audit Fisik',
            'img': '../lain/File PPT/Wireframe/Stok Opname.png',
            'desc': 'Antarmuka audit fisik persediaan berkala dengan metode perhitungan buta (blind count). Saat sesi audit aktif, sistem menampilkan kartu metrik progres dan spanduk pembekuan transaksi sementara (freeze lock). Kolom stok buku pada tabel lapangan disembunyikan bagi petugas lapangan guna mencegah manipulasi data. Dilengkapi tab pencocokan nomor seri fisik alat kesehatan serta modul persetujuan penyesuaian selisih oleh manajerial.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '11. Konversi Satuan & Pecah Dus',
            'fig': 'Gambar 3.30',
            'title': 'Wireframe Antarmuka Konversi Satuan & Pecah Dus',
            'img': '../lain/File PPT/Wireframe/Konversi Satuan.png',
            'desc': 'Antarmuka operasional untuk memecah kemasan besar (dus atau box) menjadi kemasan eceran siap jual (strip atau pcs) secara instan. Menampilkan kartu informasi rasio konversi master, formulir pemecahan kemasan (memilih batch dus asal dan jumlah dus yang dibuka), kalkulasi otomatis hasil eceran baru, pembuatan nomor sub-batch turunan yang mewarisi tanggal kedaluwarsa aslinya secara akurat, serta tabel riwayat pemecahan kemasan pada sisi kanan.'
        },
        # C. Superadmin
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '1. Dashboard Superadmin',
            'fig': 'Gambar 3.31',
            'title': 'Wireframe Antarmuka Dashboard Superadmin',
            'img': '../lain/File PPT/Wireframe/Dashboard (Superadmin).png',
            'desc': 'Pusat pengawasan makro bagi pemilik usaha untuk meninjau performa bisnis secara keseluruhan. Bagian atas memuat kartu indikator omzet kotor, laba bersih, estimasi valuasi aset gudang, dan jumlah anomali pengeluaran FEFO kasir. Bagian tengah menampilkan grafik perbandingan volume penjualan saluran toko fisik (POS) melawan pesanan daring (E-Commerce), serta tabel rekapitulasi transaksi bernilai tinggi.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '2. Laporan Penjualan Barang',
            'fig': 'Gambar 3.32',
            'title': 'Wireframe Antarmuka Laporan Penjualan Barang',
            'img': '../lain/File PPT/Wireframe/Laporan Penjualan Barang.png',
            'desc': 'Laporan rekapitulasi transaksi komersial berkala yang dilengkapi filter rentang tanggal, filter kanal penjualan (Semua, POS, atau E-Commerce), dan bilah pencarian nomor faktur. Menyajikan kartu ringkasan omzet, modal pokok penjualan, laba kotor, tabel rincian transaksi per item, serta tombol ekspor dokumen cetak.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '3. Laporan Persediaan',
            'fig': 'Gambar 3.33',
            'title': 'Wireframe Antarmuka Laporan Persediaan',
            'img': '../lain/File PPT/Wireframe/Laporan Persediaan.png',
            'desc': 'Laporan pemantauan ketersediaan barang di gudang secara menyeluruh. Menampilkan perhitungan kuantitas dalam format multi-satuan terpadu (menjabarkan sisa kemasan besar dus dan satuan eceran pada satu baris data yang sama), indikator status kecukupan stok, serta perkiraan total aset persediaan fisik.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '4. Laporan Logistik Medis (FEFO & Serial Number)',
            'fig': 'Gambar 3.34',
            'title': 'Wireframe Antarmuka Laporan Logistik Medis',
            'img': '../lain/File PPT/Wireframe/Laporan Logistik Medis.png',
            'desc': 'Laporan pengawasan khusus komoditas medis yang memisahkan dua fokus utama. Tab Sediaan Obat memantau masa simpan per nomor batch dengan urutan kedaluwarsa terdekat (metode FEFO) beserta peringatan batas aman edar, sedangkan Tab Alat Kesehatan melacak status keberadaan alat berdasarkan nomor seri fisik tunggal (tersedia, terjual, atau dalam klaim garansi).'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '5. Laporan Penerimaan Barang',
            'fig': 'Gambar 3.35',
            'title': 'Wireframe Antarmuka Laporan Penerimaan Barang',
            'img': '../lain/File PPT/Wireframe/Laporan Penerimaan Barang.png',
            'desc': 'Laporan eksekutif yang merekapitulasi seluruh pengiriman barang medis dari distributor resmi (PBF / rekanan). Antarmuka memuat bilah filter rentang tanggal terima, pilihan distributor, dan status penerimaan (sempurna atau retur parsial). Bagian ringkasan menyajikan kartu metrik total faktur inbound, nominal belanja persediaan, total dus diterima, serta kuantitas barang rusak atau diretur. Tabel data memuat nomor referensi surat jalan, tanggal terima, nama distributor, variasi produk, nomor batch pabrikan, tanggal kedaluwarsa, kuantitas pesan dan terima, harga beli, status penerimaan, dan identitas staf penerima guna memastikan transparansi pengadaan sebelum pembayaran disetujui.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '6. Laporan Mutasi & Konversi Satuan',
            'fig': 'Gambar 3.36',
            'title': 'Wireframe Antarmuka Laporan Mutasi & Konversi Satuan',
            'img': '../lain/File PPT/Wireframe/Laporan Mutasi & Konversi.png',
            'desc': 'Pusat audit manajerial yang menyatukan dua modul pergerakan persediaan non-transaksional melalui navigasi tab interaktif. Tab Konversi Satuan memantau pembongkaran kemasan dus besar menjadi satuan eceran (strip atau pcs), menyajikan nomor batch asal, nomor sub-batch eceran baru yang terbentuk, rasio pengali, jumlah dus yang dibongkar, kuantitas eceran yang dihasilkan, dan sisa dus di rak gudang. Tab Mutasi Stok memantau penyesuaian fisik persediaan di luar transaksi (seperti barang rusak, kedaluwarsa, atau selisih opname fisik) lengkap dengan nomor referensi dokumen, jenis mutasi, kuantitas penyesuaian, dan alasan operasional staf.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '7. Laporan Audit Anomali FEFO',
            'fig': 'Gambar 3.37',
            'title': 'Wireframe Antarmuka Laporan Audit Anomali FEFO',
            'img': '../lain/File PPT/Wireframe/Laporan Anomali FEFO.png',
            'desc': 'Laporan audit kepatuhan kasir yang mencatat setiap kejadian ketika petugas kasir mengambil nomor batch obat yang berbeda dari anjuran kedaluwarsa terdekat pada sistem. Menampilkan filter rentang tanggal, tabel daftar kejadian (tanggal, nomor faktur, nama kasir, nomor batch yang diambil, nomor batch anjuran sistem, selisih hari kedaluwarsa, alasan kasir, dan status verifikasi) guna memastikan mutu obat tetap terjaga dan mencegah obat kedaluwarsa tertinggal di rak.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '8. Manajemen Pengguna',
            'fig': 'Gambar 3.38',
            'title': 'Wireframe Antarmuka Manajemen Pengguna',
            'img': '../lain/File PPT/Wireframe/Manajemen Pengguna.png',
            'desc': 'Pusat pengelolaan akun pengguna dan hak akses operasional sistem. Menampilkan daftar pengguna internal (Superadmin, Admin, Kasir) dan pelanggan e-commerce, indikator keaktifan akun, formulir pendaftaran staf baru, serta pengaturan kata sandi untuk menegakkan keamanan dan batasan tugas masing-masing pihak.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '9. Pengaturan Web & Konten Toko',
            'fig': 'Gambar 3.39',
            'title': 'Wireframe Antarmuka Pengaturan Web & Konten Toko',
            'img': '../lain/File PPT/Wireframe/Pengaturan Web.png',
            'desc': 'Panel mandiri untuk memperbarui identitas dan tampilan portal toko daring. Memfasilitasi Superadmin dalam mengubah nama toko, nomor telepon layanan pelanggan, nomor WhatsApp, alamat fisik apotek, tautan gambar logo, serta berkas spanduk promosi beranda tanpa memengaruhi data logistik persediaan.'
        },
        # D. Pelanggan
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '1. Login & Registrasi Pelanggan',
            'fig': 'Gambar 3.40',
            'title': 'Wireframe Antarmuka Login Pelanggan',
            'img': '../lain/File PPT/Wireframe/Login (Pelanggan).png',
            'desc': 'Pintu masuk bagi pembeli daring untuk memasukkan nama akun dan kata sandi sebelum melanjutkan keranjang belanja ke proses pembayaran. Halaman ini juga menyediakan tautan menuju formulir pendaftaran akun baru bagi pembeli yang belum terdaftar.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '2. Akun & Profil Pelanggan',
            'fig': 'Gambar 3.41',
            'title': 'Wireframe Antarmuka Akun & Profil Pelanggan',
            'img': '../lain/File PPT/Wireframe/Profil.png',
            'desc': 'Panel akun pribadi pelanggan yang memuat formulir pengelolaan data diri dan daftar alamat pengiriman, serta tabel riwayat pesanan belanja daring untuk memantau status pengiriman paket pesanan, mengecek nomor resi kurir, dan melihat bukti transaksi.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '3. Beranda E-Commerce',
            'fig': 'Gambar 3.42',
            'title': 'Wireframe Antarmuka Beranda E-Commerce',
            'img': '../lain/File PPT/Wireframe/E-Commerce.png',
            'desc': 'Halaman utama toko daring yang dirancang untuk mempermudah pencarian produk kesehatan. Memuat bilah pencarian obat dan alkes, spanduk promosi utama, daftar kategori produk medis, serta susunan katalog produk unggulan dalam format kotak (grid) interaktif.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '4. Detail Produk',
            'fig': 'Gambar 3.43',
            'title': 'Wireframe Antarmuka Detail Produk',
            'img': '../lain/File PPT/Wireframe/Detail Produk.png',
            'desc': 'Halaman rincian barang medis yang menampilkan foto produk, penjelasan manfaat obat, spesifikasi teknis alat kesehatan, harga jual eceran maupun harga grosir dus, status ketersediaan stok fisik terkini, tombol pengatur jumlah beli, serta tombol untuk memasukkan produk ke keranjang belanja.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '5. Keranjang Belanja (Slide-Out Cart)',
            'fig': 'Gambar 3.44',
            'title': 'Wireframe Antarmuka Keranjang Belanja',
            'img': '../lain/File PPT/Wireframe/Keranjang Belanja.png',
            'desc': 'Panel keranjang belanja melayang (slide-out drawer) yang menampilkan ringkasan daftar barang yang dipilih, pengaturan jumlah pesanan, penghapusan item, dan perhitungan subtotal belanja tanpa membuat pelanggan berpindah dari halaman katalog produk.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '6. Checkout Pembayaran',
            'fig': 'Gambar 3.45',
            'title': 'Wireframe Antarmuka Checkout Pembayaran',
            'img': '../lain/File PPT/Wireframe/Checkout Pembayaran.png',
            'desc': 'Halaman akhir pemesanan daring yang memfasilitasi pengisian data penerima, integrasi pemilihan kurir ekspedisi berbasis kota tujuan, penentuan titik lokasi pengantaran dengan pin peta digital, pilihan metode pengambilan (diantar kurir atau diambil di toko), kalkulasi otomatis subtotal dan biaya ongkos kirim, serta tombol pembayaran yang terhubung langsung ke gerbang pembayaran digital.'
        }
    ]

    html = []
    html.append('''
        <!-- 3.6 Wireframe -->
        <div class="mb-8 relative group" id="sec_3_6_wrapper">
            <div class="no-print flex justify-end gap-2 mb-2 flex-wrap">
                <a href="../lain/katalog_ui_ux_asli_zencare.html" target="_blank"
                    class="text-xs text-emerald-800 hover:text-emerald-950 border border-emerald-300 bg-emerald-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Buka Komparasi UI/UX Asli vs Wireframe (27 Halaman)
                </a>
                <a href="../lain/wireframe_zencare.html" target="_blank"
                    class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Wireframe Interaktif (27 Halaman)
                </a>
                <button onclick="copyText('sec_3_6', this)"
                    class="text-xs text-blue-600 hover:text-blue-800 border border-slate-200 bg-slate-50 px-2 py-1 rounded transition font-sans">
                    Salin Subbab 3.6
                </button>
            </div>
            <div id="sec_3_6">
                <h3 class="text-base font-bold mb-3 font-serif">3.6 Wireframe</h3>
                <p class="text-justify indent-8 mb-4">
                    Perancangan wireframe merupakan tahap penerjemahan kebutuhan fungsional dan arsitektur informasi ke dalam tata letak visual dasar antarmuka pengguna tanpa terdistraksi oleh unsur tipografi dekoratif atau palet warna estetika. Pendekatan ini memastikan perancangan murni berfokus pada hierarki navigasi, efisiensi alur operasional kasir, kepatuhan audit logistik kesehatan, serta kenyamanan pengalaman berbelanja pelanggan. Perancangan antarmuka pada sistem ZenCare Medical dirancang ke dalam 27 (dua puluh tujuh) rancangan wireframe yang mencakup seluruh gerbang autentikasi, panel operasional kasir dan inventori, panel audit manajerial, hingga portal belanja mandiri pelanggan e-commerce.
                </p>
    ''')

    current_section = None
    for item in wf_items:
        sec = item['section']
        if sec != current_section:
            current_section = sec
            html.append(f'''
                <div class="mt-6 mb-4">
                    <h4 class="text-sm font-bold font-serif text-slate-900 border-b border-black pb-1 uppercase tracking-wide">
                        {sec}
                    </h4>
                </div>
            ''')

        sub = item.get('sub_title', '')
        if sub:
            html.append(f'''
                <div class="mt-4 mb-2">
                    <h5 class="text-xs font-bold font-serif text-slate-800 italic">
                        {sub}
                    </h5>
                </div>
            ''')

        html.append(f'''
                <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                    <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                        <img src="{item['img']}" alt="{item['title']}" class="w-full h-auto object-contain mx-auto" loading="lazy">
                    </div>
                    <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                        {item['fig']} {item['title']}
                    </p>
                    <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                        {item['desc']}
                    </p>
                </div>
        ''')

    html.append('''
            </div>
        </div>
    ''')

    return '\n'.join(html)

def update_bab3_file():
    bab3_path = r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html'
    with open(bab3_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find sec_3_6_wrapper and replace until closing of that wrapper
    # The wrapper starts with <!-- 3.6 Wireframe --> or <div class="mb-8 relative group" id="sec_3_6_wrapper">
    start_pattern = r'(<!--\s*3\.6\s*Wireframe\s*-->\s*<div\s+class="mb-8 relative group"\s+id="sec_3_6_wrapper">|<div\s+class="mb-8 relative group"\s+id="sec_3_6_wrapper">)'
    m = re.search(start_pattern, content)
    if not m:
        print("ERROR: Could not find start of sec_3_6_wrapper!")
        return False

    start_idx = m.start()

    # Find the end of sec_3_6_wrapper: it is followed by the closing </div> of the paper or container
    # Let's find id="sec_3_6_wrapper" and track closing divs, or find the next section / end of paper
    # In BAB_III_ZENCARE_MEDICAL.html, sec_3_6 is the LAST section before the end of .paper!
    # Let's find the closing of sec_3_6_wrapper
    end_marker = '</div> <!-- Closes full_bab3_content paper -->'
    end_idx = content.find(end_marker, start_idx)
    if end_idx == -1:
        end_marker = 'Closes full_bab3_content paper'
        idx2 = content.find(end_marker, start_idx)
        if idx2 != -1:
            end_idx = content.rfind('</div>', start_idx, idx2)

    print(f"Start index: {start_idx}, End index: {end_idx}")
    if end_idx == -1:
        print("ERROR: Could not find end of sec_3_6_wrapper!")
        return False

    new_sub36 = generate_subbab_3_6_html().strip()

    # Verify that the slice ends right before bottom notification or paper end
    new_content = content[:start_idx] + new_sub36 + '\n    ' + content[end_idx:]

    with open(bab3_path, 'w', encoding='utf-8') as f:
        f.write(new_content)

    print(f"Successfully updated {bab3_path} with 27 wireframes!")
    return True

if __name__ == '__main__':
    update_bab3_file()
