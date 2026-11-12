text = """3.3.2	Sitemap
Perancangan sitemap bertujuan untuk memetakan arsitektur informasi, hierarki menu, dan ruang lingkup antarmuka pengguna pada sistem. Berdasarkan pembagian peran (role) pada arsitektur sistem dan pangkalan data, sitemap dikelompokkan ke dalam 4 (empat) entitas pengguna utama:
1.	Sitemap Pelanggan (E-Commerce) 
Struktur sitemap ini memetakan antarmuka mandiri yang diakses secara langsung oleh pembeli daring melalui portal web e-commerce. Alur navigasi ini mencakup 6 (enam) area operasional utama:
1.	Autentikasi:
Merupakan titik masuk dan pusat kendali akses akun pengguna yang mencakup antarmuka pendaftaran akun baru, masuk sistem (login), dan pemulihan akun (lupa kata sandi).
2.	Beranda:
Halaman pembuka yang menyajikan informasi profil toko, promosi komoditas medis, serta panduan alur pemesanan obat dan alat kesehatan daring.


3.	Katalog & Penjelajahan Produk: 
Berfungsi sebagai etalase digital yang bebas diakses publik tanpa keharusan login, dilengkapi fitur pencarian cepat, filter kategori produk (Obat / Alat Kesehatan), serta halaman rincian produk yang menampilkan spesifikasi medis dan ketersediaan stok fisik secara real-time. Seluruh produk obat dan alat kesehatan pada saluran daring dipublikasikan khusus dalam kemasan satuan besar utuh (grosir) bersegel resmi pabrik.
4.	Modul Keranjang Belanja: 
Memfasilitasi pembeli dalam mengelola daftar barang belanjaan, mengubah kuantitas kemasan satuan besar, menghapus produk, serta memantau ringkasan kalkulasi subtotal sementara.
5.	Modul Checkout & Pembayaran: 
Memfasilitasi alur transaksi daring mulai dari pemilihan metode pengambilan barang (menggunakan opsi Pengiriman Kurir Ekspedisi atau Ambil di Toko / Pick-up in Store), pengisian alamat tujuan pengiriman (khusus kurir), peninjauan ringkasan pesanan dan ongkos kirim, hingga penyelesaian transaksi melalui gerbang pembayaran pihak ketiga (Payment Gateway).
6.	Modul Akun Saya: 
Pusat kendali profil pelanggan yang memuat fasilitas pembaruan biodata, pengelolaan buku alamat pengiriman, direktori pelacakan riwayat pesanan lintas status beserta pengunduhan berkas invoice transaksi, serta fitur pembatalan mandiri khusus untuk pesanan yang belum diselesaikan pembayarannya.



2.	Sitemap Kasir (Terminal Kasir POS Toko Fisik)
Struktur sitemap ini memetakan antarmuka meja kasir gerai fisik yang dirancang khusus untuk kecepatan transaksi luring (walk-in customer) dan kepatuhan alokasi logistik medis di kasir. Alur navigasi ini mencakup 2 (dua) komponen operasional:
1.	Autentikasi Kasir:
Gerbang verifikasi masuk petugas kasir untuk memulai sesi operasional penjualan toko serta fasilitas keluar sistem (logout). Pengguna dengan peran kasir langsung diarahkan secara otomatis ke antarmuka terminal POS setelah autentikasi berhasil.
2.	Terminal Kasir (POS):
Antarmuka terpadu pelayanan transaksi langsung di gerai fisik. Modul ini memuat pencarian nama produk dan pemindaian barcode kemasan, dukungan fleksibilitas multi-satuan (Dual-UOM) dengan tombol konversi otomatis Bongkar Box saat stok eceran di rak habis, penyajian rekomendasi nomor batch obat terdekat (FEFO) dan validasi nomor seri unik alat kesehatan, panel pencatatan deviasi anomali apabila kasir mengambil batch di luar rekomendasi, modal penyelesaian pembayaran (tunai atau non-tunai) yang langsung memicu pencetakan struk belanja, modal riwayat transaksi harian kasir bersifat read-only untuk keperluan pencetakan ulang struk belanja (reprint receipt) darurat serta rekonsiliasi penutupan shift kasir, serta pengalihan otomatis ke antarmuka pencacahan fisik ketika sesi stok opname sedang berlangsung. Kasir tidak diberikan wewenang pembatalan transaksi (void) atau manipulasi data penjualan; tata kelola pembatalan dan retur pesanan dipusatkan secara terkendali pada menu pesanan omnichannel di bawah wewenang Admin dan Superadmin.
3.	Terminal Pencacahan Fisik Rak (Strict Blind Count): 
Antarmuka layar penuh khusus pencacahan fisik yang otomatis aktif menggantikan akses POS kasir ketika Superadmin membekukan sistem untuk audit stok opname. Pada antarmuka ini, kasir tidak dapat melihat stok pembukuan sistem maupun status reservasi, dan diwajibkan mencatat kuantitas riil di rak ke dalam 3 kolom kondisi: Kondisi Baik, Kondisi Rusak, dan Kondisi Expired (dengan penguncian otomatis pada batch yang telah melewati tanggal kedaluwarsa). Dilengkapi fasilitas pemindai barcode / pencarian cepat SKU serta penyimpanan parsial berkala.













3.	Sitemap Admin (Operasional) 
Struktur sitemap ini memetakan antarmuka sistem yang bertugas mengelola katalog persediaan, pemenuhan pesanan e-commerce (pengiriman kurir dan pick-up in store), penerimaan barang masuk, mutasi persediaan, hingga pembukuan kartu stok. Modul ini terbagi menjadi 5 (lima) area operasional:
1.	Autentikasi Admin:
Pintu masuk staf operasional dengan otentikasi kata sandi terenkripsi serta fasilitas keluar sesi (logout).
2.	Dashboard Operasional:
Panel pemantauan taktis harian bagi admin toko yang secara dinamis menyajikan indikator persediaan yang memerlukan tindak lanjut segera. Fitur pada panel ini mencakup: ringkasan stok fisik (pemisahan total stok satuan besar utuh dan satuan kecil eceran), peringatan stok kritis persediaan menipis (safety stock / ROP) yang dilengkapi tombol pintas konversi UOM langsung, pengingat obat yang mendekati batas kedaluwarsa (ED warning), daftar antrean pesanan daring baru yang harus disiapkan, daftar reservasi pesanan Pick-up in Store yang menunggu diambil di kasir, ringkasan total transaksi hari ini, Monitoring Log Anomali FEFO (pemantauan status anomali kasir: terlewat / sebagian yang membutuhkan penyesuaian), Monitoring Retur Pembelian Supplier (pemantauan klaim retur barang rusak ke pemasok: menunggu pengganti vs selesai tukar barang / potong faktur), serta log pembukuan kartu stok aktivitas mutasi persediaan terkini.
3.	Modul Master Data: 
Pusat pengelolaan entitas dasar operasional yang mencakup katalog produk (pendaftaran produk baru, pengaturan batas minimum ROP, dan penonaktifan produk), pengelolaan data satuan multi-satuan (Multi-UOM) untuk memetakan hierarki kemasan besar ke eceran, serta direktori data supplier rekanan pengadaan barang.
4.	Modul Manajemen Pesanan Transaksi: 
Pusat pengelolaan pemenuhan pesanan daring toko yang mencakup pemantauan antrean pesanan masuk lintas jalur, lembar instruksi pengambilan fisik barang sesuai aturan FEFO dan nomor seri alkes yang divalidasi pemindaian barcode, pencetakan label pengiriman dan faktur penjualan, pemrosesan pengiriman melalui kurir (input resi) serta verifikasi kode pengambilan di toko (pick-up), hingga pembatalan pesanan dan konfirmasi pengembalian dana (refund).
5.	Modul Logistik & Inventori: 
Pusat kendali fisik barang masuk dan keluar gudang yang terbagi menjadi penerimaan barang dari pemasok dengan validasi ketat identitas medis (Batch, ED, Serial Number, retur barang cacat, dan pencetakan stiker barcode), modul konversi satuan kemasan dus ke eceran beserta mutasi stok penyesuaian manual (barang rusak/garansi), monitoring progres penghitungan fisik kasir dan eksekusi verifikasi mutasi selisih anomali saat sesi stok opname berlangsung (tanpa hak membuka/menutup sesi), serta monitoring ketersediaan buku Kartu Stok terpisah dan direktori logistik medis.
4.	Sitemap Superadmin (Pemilik / Manajerial)
Struktur sitemap ini memetakan kendali tertinggi dalam sistem yang dipegang langsung oleh pemilik usaha (owner) untuk pengawasan strategis pergerakan kuantitas fisik, kebijakan operasional, kepatuhan audit farmasi, dan tata kelola sistem. Modul ini dirancang ke dalam 6 (enam) kelompok manajerial ringkas:
1.	Autentikasi Superadmin: 
Akses masuk khusus pemilik sistem dengan verifikasi terenkripsi untuk membuka hak akses penuh dan fasilitas keluar sistem (logout).
2.	Dashboard Manajerial Eksekutif:
Panel visualisasi analitik terpadu berbasis kuantitas fisik (bebas distorsi finansial) yang mencakup evaluasi volume penjualan (POS vs daring, grafik tren 7 hari, dan 5 produk terlaris), ringkasan metrik inventori fisik (SKU, stok besar, kecil, ROP, dan obat mendekati kedaluwarsa) beserta monitoring logistik, rekam audit pengadaan barang masuk dan log kartu stok terkini, serta indikator status penguncian transaksi (Freeze Lock) sesi stok opname.
3.	Modul Manajemen Pengguna:  
Modul eksklusif pemilik untuk mendaftarkan akun staf baru (Admin maupun Kasir), membatasi hak akses sistem melalui prinsip Role-Based Access Control (RBAC), mereset kata sandi staf, serta menonaktifkan status akun staf.
4.	Modul Otorisasi Master Data & Operasional:
Pengendalian kebijakan sistem yang mencakup penetapan harga jual resmi produk, rasio konversi kemasan Multi-UOM, impor masal pangkalan data persediaan melalui berkas lembar kerja (Excel), pembukaan dan penutupan sesi Stok Opname (pengendalian Freeze Lock, rekonsiliasi selisih stok fisik, dan pengesahan Berita Acara Stok Opname bertandatangan), serta supervisi langsung terhadap operasional kasir POS dan pemenuhan pesanan e-commerce.
5.	Modul Pelaporan Kuantitas & Logistik:
Direktori laporan manajerial terpusat yang berfokus ketat pada jejak pergerakan kuantitas fisik dan kepatuhan medis tanpa variabel akuntansi keuangan, meliputi Laporan Penjualan Omnichannel (komparasi volume fisik kasir POS vs pesanan daring), Laporan Ketersediaan Persediaan & Kartu Stok Fisik, Laporan Logistik Medis & Laporan FEFO, serta Laporan Riwayat Penerimaan, Mutasi Penyesuaian, dan Konversi.
6.	Modul Pengaturan Sistem:
Pusat konfigurasi infrastruktur teknologi informasi yang mencakup pengelolaan kredensial API pihak ketiga (pembayaran Midtrans dan pelacakan kurir RajaOngkir), pengaturan identitas toko dan banner beranda e-commerce, serta tombol pintas peninjauan tampilan toko publik.
"""

import re
forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
for w in forbidden:
    matches = re.findall(r'\b' + re.escape(w) + r'\b', text, re.IGNORECASE)
    if matches:
        print(f"FOUND FORBIDDEN WORD: {w} ({len(matches)} times)")
        for m in re.finditer(r'\b' + re.escape(w) + r'\b', text, re.IGNORECASE):
            start = max(0, m.start() - 40)
            end = min(len(text), m.end() + 40)
            print(f"   Context: ...{text[start:end]}...")

