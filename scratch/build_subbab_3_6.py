# Python script to build Subbab 3.6 Wireframe and inject into laporan_ta/BAB_III_ZENCARE_MEDICAL.html
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
            'desc': 'Antarmuka daftar rekanan pemasok atau distributor resmi obat dan alat kesehatan. Menampilkan tabel informasi nama distributor, nomor kontak perwakilan, alamat kantor atau gudang, status keaktifan kerja sama, serta tombol untuk mendaftarkan mitra baru guna mendukung pencatatan asal barang pada saat pasokan masuk.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '7. Penerimaan Barang Masuk',
            'fig': 'Gambar 3.26',
            'title': 'Wireframe Antarmuka Penerimaan Barang Masuk',
            'img': '../lain/File PPT/Wireframe/Penerimaan Barang.png',
            'desc': 'Formulir pencatatan pasokan barang masuk dari distributor ke gudang toko. Formulir mewajibkan pengisian nomor nota faktur distributor, pilihan nama pemasok, tanggal kedatangan barang, kuantitas yang dipesan, kuantitas yang lolos pemeriksaan fisik, nomor batch resmi dari pabrik, tanggal batas kedaluwarsa, jumlah barang yang cacat atau rusak, serta catatan pengajuan penggantian barang retur.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '8. Mutasi Stok Manual',
            'fig': 'Gambar 3.27',
            'title': 'Wireframe Antarmuka Mutasi Stok Manual',
            'img': '../lain/File PPT/Wireframe/Mutasi Stok.png',
            'desc': 'Formulir penyesuaian jumlah persediaan fisik di luar proses jual beli biasa, seperti pengurangan stok karena barang kedaluwarsa atau penambahan stok dari koreksi fisik. Antarmuka dilengkapi pilihan jenis barang medis, nomor batch terkait, jenis perubahan (bertambah atau berkurang), alasan mutasi, serta tabel ringkasan mutasi manual terakhir pada sisi kanan.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '9. Kartu Stok (Buku Besar Mutasi)',
            'fig': 'Gambar 3.28',
            'title': 'Wireframe Antarmuka Kartu Stok',
            'img': '../lain/File PPT/Wireframe/Kartu Stok.png',
            'desc': 'Halaman buku besar persediaan yang menyajikan riwayat pergerakan fisik barang secara urut waktu dan permanen. Menampilkan filter nama barang, nomor batch, dan rentang tanggal, serta tabel yang memuat kolom jumlah masuk, jumlah keluar, saldo akhir berjalan, nomor dokumen referensi, dan nama petugas pelaksana mutasi untuk menjaga keterbukaan data.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '10. Stok Opname & Audit Fisik Persediaan',
            'fig': 'Gambar 3.29',
            'title': 'Wireframe Antarmuka Stok Opname & Audit Fisik Persediaan',
            'img': '../lain/File PPT/Wireframe/Stok Opname.png',
            'desc': 'Panel pelaksanaan pemeriksaan persediaan fisik berkala guna menyinkronkan catatan sistem dengan kondisi nyata di rak toko. Dilengkapi indikator penguncian transaksi operasional sementara (freeze lock) pada kasir POS dan toko daring, kartu ringkasan status sesi, lembar kerja pencacahan fisik obat tanpa melihat saldo buku sistem (metode blind count) untuk menghindari bias perhitungan, pencacahan nomor seri fisik alat kesehatan, tombol eksekusi penyesuaian selisih ke buku besar, serta tombol cetak Berita Acara Opname resmi.'
        },
        {
            'section': 'B. Antarmuka Admin (Operasional & Logistik Toko)',
            'sub_title': '11. Konversi Satuan & Riwayat Pecah Dus',
            'fig': 'Gambar 3.30',
            'title': 'Wireframe Antarmuka Konversi Satuan & Riwayat Pecah Dus',
            'img': '../lain/File PPT/Wireframe/Konversi Satuan.png',
            'desc': 'Panel operasional untuk memecah kemasan besar (dus atau box) menjadi satuan eceran (strip atau botol) secara langsung saat stok eceran di rak toko habis. Formulir memuat pilihan nama obat, kotak informasi rasio konversi, pilihan batch dus yang dibongkar, jumlah dus yang dibuka, hasil eceran otomatis, pembentukan nomor batch pecahan baru yang terhubung ke batch induk, pemutakhiran saldo rak secara langsung, serta tabel catatan riwayat pembongkaran kemasan.'
        },
        # C. Superadmin
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '1. Dashboard Superadmin',
            'fig': 'Gambar 3.31',
            'title': 'Wireframe Antarmuka Dashboard Superadmin',
            'img': '../lain/File PPT/Wireframe/Dashboard (Superadmin).png',
            'desc': 'Pusat pengawasan menyeluruh yang dirancang khusus untuk pemilik usaha. Menampilkan grafik perbandingan volume penjualan antara transaksi toko fisik (luring) dan pesanan toko daring (e-commerce), ringkasan nilai perputaran persediaan barang, serta pemberitahuan kritis logistik tanpa menampilkan detail teknis harian yang rumit.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '2. Laporan Penjualan Barang',
            'fig': 'Gambar 3.32',
            'title': 'Wireframe Antarmuka Laporan Penjualan Barang',
            'img': '../lain/File PPT/Wireframe/Laporan Penjualan Barang.png',
            'desc': 'Laporan rekapitulasi jumlah barang keluar dari seluruh saluran penjualan. Dilengkapi filter tanggal dan pilihan saluran penjualan, serta tabel rincian transaksi untuk menganalisis produk yang paling banyak terjual dan melihat kontribusi masing-masing saluran penjualan terhadap omset bisnis.'
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
            'sub_title': '5. Laporan Audit Anomali FEFO',
            'fig': 'Gambar 3.35',
            'title': 'Wireframe Antarmuka Laporan Audit Anomali FEFO',
            'img': '../lain/File PPT/Wireframe/Laporan Anomali FEFO.png',
            'desc': 'Laporan audit kepatuhan kasir yang mencatat setiap kejadian ketika petugas kasir mengambil nomor batch obat yang berbeda dari anjuran kedaluwarsa terdekat pada sistem. Menampilkan filter rentang tanggal, tabel daftar kejadian (tanggal, nomor faktur, nama kasir, nomor batch yang diambil, nomor batch anjuran sistem, selisih hari kedaluwarsa, alasan kasir, dan status verifikasi) guna memastikan mutu obat tetap terjaga dan mencegah obat kedaluwarsa tertinggal di rak.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '6. Manajemen Pengguna',
            'fig': 'Gambar 3.36',
            'title': 'Wireframe Antarmuka Manajemen Pengguna',
            'img': '../lain/File PPT/Wireframe/Manajemen Pengguna.png',
            'desc': 'Pusat pengelolaan akun pengguna dan hak akses operasional sistem. Menampilkan daftar pengguna internal (Superadmin, Admin, Kasir) dan pelanggan e-commerce, indikator keaktifan akun, formulir pendaftaran staf baru, serta pengaturan kata sandi untuk menegakkan keamanan dan batasan tugas masing-masing pihak.'
        },
        {
            'section': 'C. Antarmuka Superadmin (Manajerial & Pemilik)',
            'sub_title': '7. Pengaturan Web & Konten Toko',
            'fig': 'Gambar 3.37',
            'title': 'Wireframe Antarmuka Pengaturan Web & Konten Toko',
            'img': '../lain/File PPT/Wireframe/Pengaturan Web.png',
            'desc': 'Panel mandiri untuk memperbarui identitas dan tampilan portal toko daring. Memfasilitasi Superadmin dalam mengubah nama toko, nomor telepon layanan pelanggan, nomor WhatsApp, alamat fisik apotek, tautan gambar logo, serta berkas spanduk promosi beranda tanpa memengaruhi data logistik persediaan.'
        },
        # D. Pelanggan
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '1. Login & Registrasi Pelanggan',
            'fig': 'Gambar 3.38',
            'title': 'Wireframe Antarmuka Login Pelanggan',
            'img': '../lain/File PPT/Wireframe/Login (Pelanggan).png',
            'desc': 'Pintu masuk bagi pembeli daring untuk memasukkan nama akun dan kata sandi sebelum melanjutkan keranjang belanja ke proses pembayaran. Halaman ini juga menyediakan tautan menuju formulir pendaftaran akun baru bagi pembeli yang belum terdaftar.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '2. Akun & Profil Pelanggan',
            'fig': 'Gambar 3.39',
            'title': 'Wireframe Antarmuka Akun & Profil Pelanggan',
            'img': '../lain/File PPT/Wireframe/Profil.png',
            'desc': 'Panel akun pribadi pelanggan yang memuat formulir pengelolaan data diri dan daftar alamat pengiriman, serta tabel riwayat pesanan belanja daring untuk memantau status pengiriman paket pesanan, mengecek nomor resi kurir, dan melihat bukti transaksi.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '3. Beranda E-Commerce',
            'fig': 'Gambar 3.40',
            'title': 'Wireframe Antarmuka Beranda E-Commerce',
            'img': '../lain/File PPT/Wireframe/E-Commerce.png',
            'desc': 'Halaman utama toko daring yang dirancang untuk mempermudah pencarian produk kesehatan. Memuat bilah pencarian obat dan alkes, spanduk promosi utama, daftar kategori produk medis, serta susunan katalog produk unggulan dalam format kotak (grid) interaktif.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '4. Detail Produk',
            'fig': 'Gambar 3.41',
            'title': 'Wireframe Antarmuka Detail Produk',
            'img': '../lain/File PPT/Wireframe/Detail Produk.png',
            'desc': 'Halaman rincian barang medis yang menampilkan foto produk, penjelasan manfaat obat, spesifikasi teknis alat kesehatan, harga jual eceran maupun harga grosir dus, status ketersediaan stok fisik terkini, tombol pengatur jumlah beli, serta tombol untuk memasukkan produk ke keranjang belanja.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '5. Keranjang Belanja (Slide-Out Cart)',
            'fig': 'Gambar 3.42',
            'title': 'Wireframe Antarmuka Keranjang Belanja',
            'img': '../lain/File PPT/Wireframe/Keranjang Belanja.png',
            'desc': 'Panel keranjang belanja melayang (slide-out drawer) yang menampilkan ringkasan daftar barang yang dipilih, pengaturan jumlah pesanan, penghapusan item, dan perhitungan subtotal belanja tanpa membuat pelanggan berpindah dari halaman katalog produk.'
        },
        {
            'section': 'D. Antarmuka Pelanggan (Front-End E-Commerce)',
            'sub_title': '6. Checkout Pembayaran',
            'fig': 'Gambar 3.43',
            'title': 'Wireframe Antarmuka Checkout Pembayaran',
            'img': '../lain/File PPT/Wireframe/Checkout Pembayaran.png',
            'desc': 'Halaman akhir pemesanan daring yang memfasilitasi pengisian data penerima, integrasi pemilihan kurir ekspedisi berbasis kota tujuan, penentuan titik lokasi pengantaran dengan pin peta digital, pilihan metode pengambilan (diantar kurir atau diambil di toko), kalkulasi otomatis subtotal dan biaya ongkos kirim, serta tombol pembayaran yang terhubung langsung ke gerbang pembayaran digital.'
        }
    ]

    # Build HTML
    html = []
    html.append('''
        <!-- 3.6 Wireframe -->
        <div class="mb-8 relative group" id="sec_3_6_wrapper">
            <div class="no-print flex justify-end gap-2 mb-2">
                <a href="../lain/wireframe_zencare.html" target="_blank"
                    class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Wireframe Interaktif (25 Halaman)
                </a>
                <button onclick="copyText('sec_3_6', this)"
                    class="text-xs text-blue-600 hover:text-blue-800 border border-slate-200 bg-slate-50 px-2 py-1 rounded transition font-sans">
                    Salin Subbab 3.6
                </button>
            </div>
            <div id="sec_3_6">
                <h3 class="text-base font-bold mb-3 font-serif">3.6 Wireframe</h3>
                <p class="text-justify indent-8 mb-4">
                    <i>Wireframe</i> merupakan rancangan kerangka visual antarmuka yang memetakan tata letak elemen sistem, hierarki informasi, dan alur interaksi pengguna sebelum melangkah ke tahap pengkodean program. Pendekatan perancangan ini difokuskan pada kejelasan fungsi tombol, penempatan formulir data, keterbacaan tabel logistik, serta alur navigasi pengguna tanpa terpengaruh oleh unsur dekorasi visual seperti warna dan ornamen grafis. Rancangan antarmuka pada Sistem Informasi Inventori Omnichannel ZenCare Medical disesuaikan persis dengan kebutuhan operasional nyata dan struktur pangkalan data relasional 20 entitas tabel, yang terbagi ke dalam 4 (empat) kelompok antarmuka utama sebagai berikut:
                </p>
''')

    current_section = ''
    for item in wf_items:
        sec = item['section']
        if sec != current_section:
            current_section = sec
            html.append(f'''
                <!-- Kelompok: {sec} -->
                <div class="mt-6 mb-4">
                    <h4 class="text-sm font-bold font-serif mb-2">{sec}</h4>
                </div>
''')
        
        # Sub title if any
        sub_title_html = f'<p class="text-xs font-bold font-serif mb-1">{item["sub_title"]}</p>' if 'sub_title' in item else ''
        
        html.append(f'''
                <div class="mb-6" id="wf_{item['fig'].replace(' ', '_').replace('.', '_')}">
                    {sub_title_html}
                    <p class="text-justify indent-8 text-xs mb-3 font-serif leading-relaxed">
                        {item['desc']}
                    </p>
                    <div class="diagram-container text-center mb-6">
                        <div class="overflow-x-auto p-2 bg-white border border-black rounded-none">
                            <img src="{item['img']}" alt="{item['title']}" class="mx-auto max-w-full h-auto" style="border: 1px solid #000;">
                        </div>
                        <p class="text-xs text-slate-600 font-sans mt-2"><b>{item['fig']}</b> {item['title']}</p>
                    </div>
                </div>
''')

    html.append('''            </div>
        </div>
''')
    return ''.join(html)

print("Subbab 3.6 HTML generator prepared!")

