# -*- coding: utf-8 -*-
"""
Master script to eliminate ALL occurrences of 'apotek' and 'farmasi',
ensure complete role alignment (Superadmin, Admin, Kasir, and Pelanggan),
and regenerate/update all report files.
"""

import os
import re

# Comprehensive list of 27 screens with:
# - ZERO occurrences of 'apotek' (use 'toko kesehatan ZenCare Medical', 'toko fisik', 'gerai fisik', 'gudang')
# - ZERO occurrences of 'farmasi'
# - Explicit inclusion of 'Kasir' along with Superadmin, Admin, and Pelanggan
ui_screens = [
    # Kelompok A: Autentikasi Pengguna
    {
        "num": "Gambar 4.1",
        "sub_num": "1",
        "title": "Implementasi Halaman Login Staf",
        "fig_title": "Tampilan Antarmuka Halaman Login Staf",
        "img_file": "01_Login_Staf.png",
        "group": "A. Implementasi Gerbang Autentikasi Staf",
        "group_intro": "Implementasi gerbang autentikasi staf merealisasikan mekanisme pembatasan hak akses berbasis verifikasi identitas resmi bagi seluruh personel internal. Bagian ini menjamin bahwa hanya staf yang memiliki akun terdaftar dan aktif (Superadmin, Admin, dan Kasir) yang dapat mengakses modul kerja di toko kesehatan ZenCare Medical.",
        "desc": (
            "Halaman autentikasi utama bagi seluruh staf internal toko kesehatan ZenCare Medical yang mencakup tiga peran utama, yaitu Superadmin, Admin, dan Kasir. "
            "Antarmuka ini mengimplementasikan sistem keamanan sesi terpusat dengan formulir masukan nama pengguna (username) dan kata sandi (password), "
            "identitas visual resmi ZenCare Medical, serta indikator verifikasi kredensial. Pada sisi backend, sistem memvalidasi data masukan "
            "terhadap tabel users menggunakan algoritma pencocokan kata sandi terenkripsi hash yang aman. "
            "Berdasarkan nilai atribut peran (role), sistem secara otomatis mengarahkan alur kerja ke antarmuka yang tepat: "
            "staf dengan peran Kasir langsung diarahkan secara otomatis menuju Terminal Kasir (pos/pos.php) guna mempercepat transaksi penjualan di toko fisik; "
            "staf dengan peran Admin diarahkan ke dashboard operasional toko dan logistik gudang (index.php); sedangkan peran Superadmin "
            "diarahkan ke dashboard eksekutif dan konfigurasi manajerial (index.php). "
            "Sistem juga memproteksi gerbang ini dari akses pihak luar; apabila akun Pelanggan mencoba masuk melalui formulir staf ini, "
            "sistem menolak autentikasi dan menginstruksikan pengguna untuk masuk melalui portal khusus pelanggan (login_customer.php)."
        )
    },
    # Kelompok B: Operasional Admin & Kasir
    {
        "num": "Gambar 4.2",
        "sub_num": "2",
        "title": "Implementasi Dashboard Operasional Admin",
        "fig_title": "Tampilan Antarmuka Halaman Dashboard Admin",
        "img_file": "02_Dashboard_Admin.png",
        "group": "B. Implementasi Sistem Operasional Admin dan Kasir",
        "group_intro": "Implementasi sistem operasional merealisasikan seluruh aktivitas kerja harian staf toko, kasir, dan penanggung jawab gudang. Bagian ini mencakup eksekusi transaksi penjualan langsung di kasir (POS), pembaruan data persediaan multi-satuan, pencatatan pasokan obat dan alat kesehatan yang masuk dari distributor, penyesuaian mutasi fisik, hingga pelaksanaan audit stok opname berkala.",
        "desc": (
            "Pusat kendali operasional harian bagi staf Admin yang menyajikan ringkasan indikator kinerja toko fisik secara langsung. "
            "Antarmuka ini memuat kartu metrik ringkas yang menampilkan jumlah total produk aktif, total transaksi penjualan hari ini, "
            "peringatan jumlah persediaan barang yang menipis di bawah ambang batas minimum, serta notifikasi khusus bagi stok "
            "yang mendekati masa kedaluwarsa. Terdapat tombol akses cepat menuju terminal kasir (Point of Sale) dan formulir penerimaan barang masuk. "
            "Selain itu, antarmuka menyediakan diagram tren transaksi mingguan serta tabel rekapitulasi pesanan terbaru yang memerlukan tindak lanjut cepat. "
            "Seluruh data metrik dihimpun secara dinamis dari tabel penjualan, produk_variasi, dan stok_batch."
        )
    },
    {
        "num": "Gambar 4.3",
        "sub_num": "3",
        "title": "Implementasi Terminal Kasir (Point of Sale / POS)",
        "fig_title": "Tampilan Antarmuka Halaman Terminal Kasir (Point of Sale / POS)",
        "img_file": "03_POS_Kasir.png",
        "group": None,
        "desc": (
            "Terminal kasir penjualan langsung di toko fisik yang dioperasikan oleh staf Kasir dengan tata letak dua panel terpadu guna mempercepat alur transaksi. "
            "Panel sebelah kiri menyajikan bilah pencarian obat dan alat kesehatan berbasis nama maupun pemindaian kode batang (barcode), "
            "penyaring kategori medis, serta susunan kartu produk interaktif yang memuat informasi harga satuan eceran dan sisa stok fisik siap jual. "
            "Panel sebelah kanan berfungsi sebagai lembar kerja kasir yang memuat daftar keranjang belanja, kalkulasi otomatis subtotal belanja, "
            "pilihan metode pembayaran tunai maupun non-tunai, perhitungan nominal uang kembali, serta tombol penyelesaian transaksi. "
            "Saat pembayaran disahkan, sistem menjalankan algoritma pengeluaran stok berbasis tanggal kedaluwarsa terdekat (FEFO) secara otomatis "
            "melalui alokasi pada tabel pesanan_batch_alokasi, mencatat nomor seri unit alat kesehatan pada unit_serial (jika barang bernomor seri), "
            "serta memperbarui buku mutasi kartu_stok secara seketika."
        )
    },
    {
        "num": "Gambar 4.4",
        "sub_num": "4",
        "title": "Implementasi Pengelolaan Transaksi Penjualan Omnichannel",
        "fig_title": "Tampilan Antarmuka Halaman Transaksi Penjualan Omnichannel",
        "img_file": "04_Transaksi_Omnichannel.png",
        "group": None,
        "desc": (
            "Pusat kendali rekaman transaksi penjualan yang menyatukan seluruh riwayat pesanan dari saluran kasir toko fisik (POS) "
            "maupun saluran pesanan toko daring (E-Commerce). Antarmuka dilengkapi navigasi penyaring saluran penjualan, penyaring status pemrosesan "
            "(menunggu pembayaran, diproses, dikirim, selesai, dan dibatalkan), pencarian berdasarkan nomor faktur atau nama pemesan, "
            "serta visualisasi lencana status transaksi yang informatif. Staf Admin dapat melihat rincian barang yang dipesan, memperbarui status pengiriman "
            "dengan memasukkan nomor resi ekspedisi atau mengonfirmasi pengambilan langsung di toko fisik (in-store pickup), "
            "serta mencetak lembar nota resmi. Modul ini beroperasi langsung di atas tabel penjualan dan detail_penjualan."
        )
    },
    {
        "num": "Gambar 4.5",
        "sub_num": "5",
        "title": "Implementasi Pengelolaan Master Produk dan Katalog Medis",
        "fig_title": "Tampilan Antarmuka Halaman Master Produk dan Katalog Medis",
        "img_file": "05_Master_Produk.png",
        "group": None,
        "desc": (
            "Modul pengelolaan katalog dasar induk untuk seluruh komoditas obat dan alat kesehatan yang diperjualbelikan. "
            "Menampilkan tabel direktori produk yang memuat foto barang, kode unik produk (SKU Induk), nama produk medis, "
            "pengelompokan kategori kesehatan, deskripsi spesifikasi teknis atau indikasi medis, batasan jumlah hari peringatan kedaluwarsa, "
            "dan status ketersediaan publikasi. Tersedia tombol aksi untuk menambah entitas produk baru melalui jendela formulir modal pop-up "
            "yang telah disterilkan dari gangguan visual, mengubah data barang, maupun menonaktifkan produk. "
            "Data pada antarmuka ini terpusat pada tabel produk_induk dan menjadi rujukan utama bagi penetapan variasi kemasan."
        )
    },
    {
        "num": "Gambar 4.6",
        "sub_num": "6",
        "title": "Implementasi Konfigurasi Satuan dan Multi-UOM",
        "fig_title": "Tampilan Antarmuka Halaman Pengaturan Satuan dan Multi-UOM",
        "img_file": "06_Satuan_UOM.png",
        "group": None,
        "desc": (
            "Modul konfigurasi struktur multi-satuan (Unit of Measure / Multi-UOM) yang menghubungkan satuan kemasan grosir "
            "(seperti dus atau boks) dengan satuan eceran terkecil (seperti strip, botol, tablet, atau saset). "
            "Antarmuka ini menampilkan daftar variasi produk beserta rasio konversi matematisnya, penetapan harga jual yang berbeda "
            "antara transaksi kasir toko fisik dan penjualan daring, batas kuantitas minimum untuk memicu peringatan stok menipis, "
            "serta opsi aktivasi pelacakan nomor seri unik untuk alat kesehatan bernilai tinggi. "
            "Struktur data yang dikonfigurasi pada halaman ini tersimpan dalam tabel produk_variasi untuk menjaga ketepatan kalkulasi stok lintas kemasan."
        )
    },
    {
        "num": "Gambar 4.7",
        "sub_num": "7",
        "title": "Implementasi Direktori Data Supplier",
        "fig_title": "Tampilan Antarmuka Halaman Direktori Supplier",
        "img_file": "07_Direktori_Supplier.png",
        "group": None,
        "desc": (
            "Panel administrasi direktori master data distributor dan pemasok resmi rekanan toko kesehatan ZenCare Medical. "
            "Antarmuka menyajikan tabel terstruktur yang memuat nama instansi distributor, narahubung perwakilan penjualan, "
            "nomor sambungan telepon, alamat surel korespondensi, alamat lengkap kantor atau pergudangan pemasok, "
            "serta status keaktifan kemitraan. Fitur pencarian cepat dan formulir tambah/sunting data memudahkan staf pengadaan "
            "dalam memelihara kebaruan informasi pemasok, yang berfungsi sebagai rujukan wajib pada saat penerimaan pasokan barang pada tabel supplier."
        )
    },
    {
        "num": "Gambar 4.8",
        "sub_num": "8",
        "title": "Implementasi Penerimaan Barang Masuk (Inbound)",
        "fig_title": "Tampilan Antarmuka Halaman Penerimaan Barang Masuk (Inbound)",
        "img_file": "08_Penerimaan_Barang.png",
        "group": None,
        "desc": (
            "Modul pendataan logistik masuk (inbound) yang mencatat pasokan kiriman persediaan obat dan alat kesehatan dari distributor ke gudang toko fisik ZenCare Medical. "
            "Formulir penerimaan mewajibkan penginputan nomor referensi dokumen pengiriman (surat jalan atau faktur distributor), "
            "pemilihan mitra distributor, tanggal fisik penerimaan, berkas nota, serta baris daftar barang dinamis. "
            "Untuk setiap item yang diterima, staf mencatat nomor batch pabrik, tanggal jatuh tempo kedaluwarsa resmi, kuantitas unit, "
            "dan harga beli satuan. Ketika berkas penerimaan disahkan, sistem secara otomatis menambah saldo fisik pada tabel stok_batch dan stok_toko, "
            "mencatat riwayat mutasi masuk pada kartu_stok, serta membukukan dokumen penerimaan pada tabel penerimaan_stok dan penerimaan_detail."
        )
    },
    {
        "num": "Gambar 4.9",
        "sub_num": "9",
        "title": "Implementasi Mutasi Stok Manual",
        "fig_title": "Tampilan Antarmuka Halaman Mutasi Stok Manual",
        "img_file": "09_Mutasi_Stok.png",
        "group": None,
        "desc": (
            "Modul penyesuaian kuantitas persediaan fisik di luar siklus transaksi reguler penjualan maupun penerimaan distributor. "
            "Antarmuka menyediakan formulir penyesuaian yang memuat pemilihan variasi produk, pemilihan nomor batch spesifik, jenis pergerakan "
            "(penambahan stok atau pengurangan stok), jumlah unit yang disesuaikan, serta kolom alasan mutasi (misalnya barang rusak, kemasan bocor, "
            "retur internal, atau penyesuaian selisih administratif). Pada panel sisi kanan, sistem menampilkan tabel riwayat mutasi manual terdahulu "
            "yang memuat keterangan waktu, jumlah, dan nama staf pelaksana, yang tercatat secara permanen pada tabel kartu_stok."
        )
    },
    {
        "num": "Gambar 4.10",
        "sub_num": "10",
        "title": "Implementasi Kartu Stok Audit",
        "fig_title": "Tampilan Antarmuka Halaman Kartu Stok Audit",
        "img_file": "10_Kartu_Stok.png",
        "group": None,
        "desc": (
            "Halaman pemantauan buku besar mutasi persediaan (inventory ledger) yang merekam jejak audit kronologis setiap komoditas medis. "
            "Menampilkan tabel catatan pergerakan barang yang mencakup stempel waktu kejadian, nomor dokumen referensi transaksi, "
            "saluran aktivitas terkait, jenis aksi mutasi, nomor batch produk, kuantitas barang masuk, kuantitas barang keluar, "
            "dan sisa saldo stok akhir yang terverifikasi. Dilengkapi dengan penyaring pilihan produk variasi dan rentang waktu pengamatan, "
            "modul ini menjadi rujukan utama bagi auditor internal untuk menelusuri keabsahan saldo fisik pada tabel kartu_stok."
        )
    },
    {
        "num": "Gambar 4.11",
        "sub_num": "11",
        "title": "Implementasi Stok Opname dan Audit Fisik",
        "fig_title": "Tampilan Antarmuka Halaman Stok Opname dan Audit Fisik",
        "img_file": "11_Stok_Opname.png",
        "group": None,
        "desc": (
            "Modul pelaksanaan kegiatan audit fisik persediaan (stok opname) berkala untuk membandingkan kuantitas persediaan di rak "
            "dengan saldo pembukuan sistem. Menampilkan daftar sesi audit yang sedang berlangsung maupun yang telah dibukukan, "
            "fasilitas inisiasi sesi opname baru oleh staf berwenang, formulir pencatatan hitung fisik per batch dan nomor seri, "
            "serta kalkulasi otomatis terhadap selisih jumlah barang (discrepancy). Apabila hasil verifikasi fisik disetujui, "
            "sistem melakukan penyesuaian saldo secara serentak ke dalam tabel stok_batch, sesi_opname, detail_opname_batch, dan riwayat_hitung_opname."
        )
    },
    {
        "num": "Gambar 4.12",
        "sub_num": "12",
        "title": "Implementasi Konversi Satuan dan Pemecahan Kemasan",
        "fig_title": "Tampilan Antarmuka Halaman Konversi Satuan dan Pemecahan Kemasan",
        "img_file": "12_Konversi_Satuan.png",
        "group": None,
        "desc": (
            "Modul pemrosesan pemecahan kemasan besar menjadi kemasan satuan eceran siap jual (seperti membuka 1 dus menjadi sejumlah botol atau strip). "
            "Staf memilih produk variasi, menentukan nomor batch asal yang akan dipecah, dan menginput jumlah kemasan besar yang dibuka. "
            "Sistem secara otomatis menghitung jumlah kuantitas eceran yang dihasilkan berdasarkan rasio konversi yang telah ditetapkan, "
            "mengurangi saldo kemasan dus pada tabel stok_batch, menambah kuantitas eceran pada batch yang sama, membukukan catatan riwayat pada histori_konversi, "
            "serta memperbarui entri mutasi pada kartu_stok sehingga total nilai persediaan tetap seimbang dan teratur."
        )
    },
    # Kelompok C: Manajerial & Eksekutif Superadmin
    {
        "num": "Gambar 4.13",
        "sub_num": "13",
        "title": "Implementasi Dashboard Eksekutif Superadmin",
        "fig_title": "Tampilan Antarmuka Halaman Dashboard Superadmin",
        "img_file": "13_Dashboard_Superadmin.png",
        "group": "C. Implementasi Sistem Manajerial dan Eksekutif Superadmin",
        "group_intro": "Implementasi sistem manajerial dan eksekutif merealisasikan kebutuhan pimpinan (Superadmin) dalam memantau kinerja operasional dan finansial secara menyeluruh. Bagian ini menyediakan instrumen analisis omzet dan margin laba, valuasi aset persediaan barang, pengawasan kepatuhan pengeluaran stok berbasis tanggal kedaluwarsa (FEFO), audit anomali logistik, pengelolaan wewenang pengguna, serta pengaturan konfigurasi toko daring.",
        "desc": (
            "Pusat pantauan eksekutif bagi pimpinan toko kesehatan ZenCare Medical yang merangkum kesehatan finansial dan operasional usaha. "
            "Menampilkan ringkasan metrik strategis berupa total omzet pendapatan kotor, estimasi laba bersih, total valuasi nilai aset barang di gudang, "
            "serta proporsi kontribusi penjualan saluran toko fisik dibandingkan toko daring. Antarmuka menyajikan grafik visual tren performa penjualan "
            "antarwaktu serta indikator peringatan potensi anomali pengeluaran barang. Informasi tingkat tinggi ini dihitung secara otomatis "
            "melalui agregasi tabel penjualan, detail_penjualan, stok_batch, dan log_anomali_fefo."
        )
    },
    {
        "num": "Gambar 4.14",
        "sub_num": "14",
        "title": "Implementasi Laporan Penjualan Barang",
        "fig_title": "Tampilan Antarmuka Halaman Laporan Penjualan Barang",
        "img_file": "14_Laporan_Penjualan.png",
        "group": None,
        "desc": (
            "Modul rekapitulasi data penjualan komprehensif yang dirancang untuk analisis pendapatan periodik. "
            "Pengguna manajerial dapat memfilter data berdasarkan interval tanggal transaksi dan memilah saluran penjualan tertentu (kasir POS atau toko daring). "
            "Tabel laporan menyajikan perincian faktur mencakup tanggal transaksi, nomor invoice, nama pembeli atau pelanggan, saluran pembelian, "
            "metode pembayaran yang digunakan, total belanja barang, biaya pengiriman, serta status akhir faktur. "
            "Halaman dilengkapi tombol ekspor data dan cetak laporan resmi untuk keperluan rekapitulasi kas dan pembukuan keuangan toko."
        )
    },
    {
        "num": "Gambar 4.15",
        "sub_num": "15",
        "title": "Implementasi Laporan Persediaan dan Valuasi Aset",
        "fig_title": "Tampilan Antarmuka Halaman Laporan Persediaan dan Valuasi Aset",
        "img_file": "15_Laporan_Persediaan.png",
        "group": None,
        "desc": (
            "Modul laporan analitis yang menyajikan nilai modal kerja dan tingkat ketersediaan seluruh persediaan komoditas medis. "
            "Antarmuka merinci data per variasi produk yang mencakup jumlah kuantitas fisik yang tersedia di rak dan gudang, harga beli rata-rata, "
            "harga jual eceran, estimasi total valuasi aset barang, serta status persediaan (kategori aman, menipis, atau habis). "
            "Laporan ini bersumber dari kalkulasi langsung data tabel stok_batch dan produk_variasi, memberikan kepastian kepada manajemen "
            "mengenai perputaran modal barang dan efisiensi pengadaan persediaan."
        )
    },
    {
        "num": "Gambar 4.16",
        "sub_num": "16",
        "title": "Implementasi Laporan Logistik Medis (Kedaluwarsa)",
        "fig_title": "Tampilan Antarmuka Halaman Laporan Logistik Medis (Kedaluwarsa)",
        "img_file": "16_Laporan_Logistik_Medis.png",
        "group": None,
        "desc": (
            "Modul pengawasan kelayakan dan keselamatan produk obat serta komoditas medis berdasarkan batas tanggal kedaluwarsa pabrikan. "
            "Antarmuka mengelompokkan barang ke dalam tiga tingkat kewaspadaan visual: persediaan dalam kondisi aman, persediaan yang memasuki masa kritis "
            "(misalnya kurang dari 30 atau 90 hari sebelum kedaluwarsa), dan persediaan yang telah melewati masa berlaku. "
            "Informasi ini memandu pihak manajemen untuk mengambil langkah strategis yang cepat, seperti pelaksanaan diskon khusus (clearance) "
            "maupun pemisahan barang retur ke distributor, merujuk data pada tabel stok_batch dan produk_induk."
        )
    },
    {
        "num": "Gambar 4.17",
        "sub_num": "17",
        "title": "Implementasi Laporan Penerimaan Barang Masuk",
        "fig_title": "Tampilan Antarmuka Halaman Laporan Penerimaan Barang Masuk",
        "img_file": "17_Laporan_Penerimaan_Barang.png",
        "group": None,
        "desc": (
            "Modul pelaporan audit logistik masuk (inbound) yang merekapitulasi seluruh pasokan barang yang telah diterima dari distributor. "
            "Menampilkan tabel komprehensif yang memuat nomor tanda terima penerimaan, nama instansi distributor pengirim, tanggal kedatangan barang di gudang toko fisik ZenCare Medical, "
            "nama staf yang menerima, rincian produk variasi, nomor batch kiriman, tanggal kedaluwarsa resmi, kuantitas pasokan yang diterima, "
            "serta total nilai nominal pembelian. Dilengkapi fitur pencarian dan penyaring tanggal, laporan ini memudahkan proses pencocokan faktur tagihan "
            "dari distributor dengan pencatatan fisik pada tabel penerimaan_stok dan penerimaan_detail."
        )
    },
    {
        "num": "Gambar 4.18",
        "sub_num": "18",
        "title": "Implementasi Laporan Mutasi dan Konversi Satuan",
        "fig_title": "Tampilan Antarmuka Halaman Laporan Mutasi dan Konversi Satuan",
        "img_file": "18_Laporan_Mutasi_Konversi.png",
        "group": None,
        "desc": (
            "Modul rekapitulasi audit pergerakan persediaan non-penjualan yang menyatukan catatan mutasi manual (seperti barang rusak atau koreksi) "
            "dan riwayat proses konversi pemecahan satuan (pecah kemasan dus menjadi eceran). Laporan menyajikan stempel waktu kegiatan, "
            "nomor referensi dokumen bukti, nama produk variasi medis, nomor batch terkait, kuantitas asal serta kuantitas hasil konversi, "
            "penjelasan alasan perubahan, dan identitas staf operasional yang melakukan tindakan. "
            "Data ditarik secara konsolidatif dari tabel histori_konversi dan kartu_stok guna menjamin ketertiban tata kelola pergudangan."
        )
    },
    {
        "num": "Gambar 4.19",
        "sub_num": "19",
        "title": "Implementasi Laporan Audit Anomali Pengeluaran FEFO",
        "fig_title": "Tampilan Antarmuka Halaman Laporan Audit Anomali Pengeluaran FEFO",
        "img_file": "17_Laporan_Anomali_FEFO.png",
        "group": None,
        "desc": (
            "Panel audit kepatuhan protokol logistik medis yang memantau setiap kejadian penyimpangan dalam pengeluaran barang di kasir maupun gudang. "
            "Apabila terjadi pengeluaran batch produk yang masa kedaluwarsanya lebih panjang mendahului batch produk yang masa kedaluwarsanya lebih dekat, "
            "sistem mendeteksi insiden tersebut secara otomatis sebagai indikasi anomali. Halaman ini menyajikan tabel audit yang memuat tanggal kejadian, "
            "nomor faktur penjualan, nomor batch yang terambil, nomor batch yang semestinya dikeluarkan menurut aturan baku FEFO, jumlah kuantitas barang, "
            "serta status verifikasi penanganan pada tabel log_anomali_fefo guna mempertahankan standar mutu pengelolaan obat."
        )
    },
    {
        "num": "Gambar 4.20",
        "sub_num": "20",
        "title": "Implementasi Manajemen Pengguna Sistem",
        "fig_title": "Tampilan Antarmuka Halaman Manajemen Pengguna Sistem",
        "img_file": "18_Manajemen_Pengguna.png",
        "group": None,
        "desc": (
            "Modul tata kelola akun pengguna sistem yang hanya dapat diakses oleh peran Superadmin untuk menjaga integritas otorisasi. "
            "Menampilkan tabel master akun pengguna yang memuat identitas nama lengkap, nama pengguna (username), alamat surel, "
            "nomor telepon kontak, penugasan hak akses peran (Superadmin, Admin, Kasir, atau Pelanggan), status akun (aktif atau nonaktif), "
            "serta waktu pendaftaran. Melalui antarmuka ini, Superadmin dapat mendaftarkan akun staf baru, memperbarui peran kerja, "
            "melakukan reset kata sandi, atau menonaktifkan akun yang sudah tidak memiliki wewenang pada tabel users."
        )
    },
    {
        "num": "Gambar 4.21",
        "sub_num": "21",
        "title": "Implementasi Pengaturan Toko Daring (Pengaturan Web)",
        "fig_title": "Tampilan Antarmuka Halaman Pengaturan Toko Daring (Pengaturan Web)",
        "img_file": "19_Pengaturan_Web.png",
        "group": None,
        "desc": (
            "Modul administrasi konfigurasi profil usaha dan aset informasi publik toko daring ZenCare Medical. "
            "Antarmuka ini memfasilitasi Superadmin dalam memperbarui nama resmi bisnis toko, deskripsi profil usaha, "
            "alamat operasional gerai dan kantor fisik ZenCare Medical, nomor kontak pelayanan pelanggan (CS), integrasi nomor WhatsApp resmi, "
            "berkas gambar logo identitas toko, serta gambar spanduk promosi yang dipajang di halaman muka situs. "
            "Seluruh konfigurasi ini disimpan secara terpusat pada tabel pengaturan_web sehingga pembaruan data langsung "
            "tercermin secara seragam pada seluruh antarmuka pelanggan."
        )
    },
    # Kelompok D: Pelanggan E-Commerce
    {
        "num": "Gambar 4.22",
        "sub_num": "22",
        "title": "Implementasi Pendaftaran dan Masuk Akun Pelanggan",
        "fig_title": "Tampilan Antarmuka Halaman Masuk dan Pendaftaran Pelanggan",
        "img_file": "20_Login_Pelanggan.png",
        "group": "D. Implementasi Sistem Toko Daring (E-Commerce) Pelanggan",
        "group_intro": "Implementasi toko daring merealisasikan saluran penjualan digital yang menghubungkan pelanggan secara langsung dengan ketersediaan persediaan barang di toko fisik ZenCare Medical. Bagian ini menyediakan layanan pendaftaran akun, katalog produk obat dan alat kesehatan, keranjang belanja interaktif, kalkulasi tarif pengiriman antarkota secara otomatis, hingga integrasi pembayaran digital.",
        "desc": (
            "Gerbang akses bagi masyarakat dan pelanggan toko daring ZenCare Medical yang menyatukan formulir masuk (login) "
            "dan formulir pendaftaran akun baru (register) dalam tampilan visual yang bersih dan ramah pengguna. "
            "Pengguna baru dapat mengisi data diri mencakup nama lengkap, alamat email, nomor telepon, alamat rumah domisili, "
            "nama akun, dan kata sandi rahasia. Setelah formulir diverifikasi, data pengguna direkam ke tabel users "
            "dengan peran otomatis sebagai 'pelanggan' dan pengguna langsung dapat melakukan transaksi belanja kebutuhan kesehatan."
        )
    },
    {
        "num": "Gambar 4.23",
        "sub_num": "23",
        "title": "Implementasi Profil dan Pemantauan Pesanan Pelanggan",
        "fig_title": "Tampilan Antarmuka Halaman Profil dan Riwayat Pesanan Pelanggan",
        "img_file": "21_Profil_Pelanggan.png",
        "group": None,
        "desc": (
            "Halaman panel kendali pribadi pelanggan yang menampilkan profil pengguna terdaftar, alamat tujuan pengiriman barang, "
            "nomor kontak aktif, serta daftar rekapitulasi riwayat transaksi belanja yang pernah dilakukan. "
            "Pelanggan dapat memantau status pemrosesan pesanan secara langsung (menunggu verifikasi pembayaran, pesanan sedang disiapkan staf, "
            "barang dalam proses pengantaran kurir dengan tautan lacak resi, atau pesanan siap diambil di gerai toko fisik ZenCare Medical), "
            "lengkap dengan rincian bukti transaksi dari tabel penjualan dan detail_penjualan."
        )
    },
    {
        "num": "Gambar 4.24",
        "sub_num": "24",
        "title": "Implementasi Beranda Toko Daring (E-Commerce)",
        "fig_title": "Tampilan Antarmuka Halaman Beranda Toko Daring (E-Commerce)",
        "img_file": "22_Beranda_Ecommerce.png",
        "group": None,
        "desc": (
            "Halaman muka etalase digital toko daring ZenCare Medical yang menyambut pengunjung dengan susunan visual yang tertata rapi. "
            "Menampilkan spanduk promosi utama di bagian atas layar, bilah pencarian obat dan alat kesehatan yang responsif, "
            "navigasi filter kategori produk medis, serta susunan katalog produk unggulan dalam format kotak (grid) interaktif. "
            "Setiap kartu produk menyajikan gambar komoditas, nama produk, kategori, kepastian ketersediaan stok fisik, "
            "harga ritel resmi, serta tombol aksi untuk melihat informasi detail atau langsung menambahkan barang ke keranjang belanja."
        )
    },
    {
        "num": "Gambar 4.25",
        "sub_num": "25",
        "title": "Implementasi Rincian Informasi Produk (Detail Produk)",
        "fig_title": "Tampilan Antarmuka Halaman Rincian Informasi Produk (Detail Produk)",
        "img_file": "23_Detail_Produk.png",
        "group": None,
        "desc": (
            "Halaman rincian spesifikasi barang medis yang dipilih oleh pelanggan untuk memberikan transparansi informasi sebelum membeli. "
            "Menampilkan foto komoditas resolusi tinggi, nama produk obat atau alat kesehatan, kepastian status ketersediaan stok fisik terkini di gerai toko fisik ZenCare Medical, "
            "pilihan jenis kemasan variasi (seperti strip, botol, atau boks) dengan harga masing-masing, penjelasan indikasi atau manfaat medis, "
            "petunjuk penyimpanan barang, pengatur jumlah pembelian, serta tombol aksi untuk memasukkan barang ke keranjang belanja maupun checkout instan."
        )
    },
    {
        "num": "Gambar 4.26",
        "sub_num": "26",
        "title": "Implementasi Panel Keranjang Belanja Interaktif",
        "fig_title": "Tampilan Antarmuka Halaman Panel Keranjang Belanja Interaktif",
        "img_file": "24_Keranjang_Belanja.png",
        "group": None,
        "desc": (
            "Antarmuka laci keranjang belanja melayang (slide-out drawer) yang muncul dari sisi kanan layar ketika pelanggan memilih produk, "
            "sehingga pelanggan dapat meninjau belanjaannya tanpa perlu meninggalkan halaman katalog yang sedang dibuka. "
            "Menampilkan daftar item obat dan alkes yang dipilih, pengatur jumlah kuantitas pembelian (+/-), tombol penghapusan barang, "
            "penghitungan subtotal nilai belanjaan secara dinamis, serta tombol navigasi langsung menuju tahapan pengisian alamat dan pembayaran."
        )
    },
    {
        "num": "Gambar 4.27",
        "sub_num": "27",
        "title": "Implementasi Pembayaran dan Pengiriman Pesanan (Checkout)",
        "fig_title": "Tampilan Antarmuka Halaman Pembayaran dan Pengiriman (Checkout)",
        "img_file": "25_Checkout_Pembayaran.png",
        "group": None,
        "desc": (
            "Halaman tahap akhir transaksi daring pelanggan yang memadukan formulir data penerima barang, pilihan metode penyerahan pesanan "
            "(diantar oleh kurir ekspedisi ke alamat tujuan atau diambil langsung secara mandiri di gerai fisik ZenCare Medical / in-store pickup), "
            "penentuan titik lokasi koordinat pengiriman yang akurat menggunakan penanda peta digital interaktif, kalkulasi otomatis tarif ongkos kirim "
            "berdasarkan kota tujuan, ringkasan rincian tagihan belanja, serta tombol pembayaran aman yang terhubung langsung dengan penyedia gerbang pembayaran "
            "digital. Saat transaksi dikonfirmasi, sistem secara otomatis membukukan pesanan baru ke dalam tabel penjualan dan detail_penjualan."
        )
    }
]

def build_bab4_html():
    html = []
    html.append('''
        <!-- Title of Chapter -->
        <div class="text-center mb-10">
            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>
            <h2 class="text-lg sm:text-xl font-bold uppercase tracking-wide mt-1">HASIL DAN PEMBAHASAN</h2>
        </div>

        <!-- 4.1 Hasil -->
        <div class="mt-8 mb-6">
            <div class="flex items-center justify-between border-b pb-2">
                <h3 class="text-sm font-bold font-serif text-slate-900">4.1 Hasil</h3>
                <button onclick="copyText('subbab_41_content', this)" class="no-print px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded border border-slate-300 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    Salin Subbab 4.1
                </button>
            </div>
            
            <div id="subbab_41_content" class="mt-3">
                <p class="text-xs font-serif text-slate-800 text-justify leading-relaxed indent-8 mb-4">
                    Subbab ini memaparkan hasil dari implementasi sistem informasi inventori dan penjualan omnichannel pada toko kesehatan ZenCare Medical yang telah dibangun berdasarkan analisis kebutuhan serta spesifikasi perancangan pada bab-bab sebelumnya. Hasil pengembangan sistem ini diwujudkan melalui perangkat lunak berbasis web yang mengintegrasikan alur transaksi kasir toko fisik (Point of Sale), saluran toko daring (E-Commerce), pengelolaan persediaan obat dan alat kesehatan multi-satuan, penerapan aturan pengeluaran barang berbasis tanggal kedaluwarsa (FEFO), hingga instrumen pelaporan eksekutif terpadu guna mendukung transparansi dan efisiensi operasional usaha.
                </p>

                <!-- 4.1.1 Implementasi Sistem -->
                <div class="mt-6">
                    <div class="flex items-center justify-between border-b pb-1 mb-3">
                        <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">4.1.1 Implementasi Sistem</h4>
                        <button onclick="copyText('subbab_411_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            Salin Subbab 4.1.1
                        </button>
                    </div>

                    <div id="subbab_411_content">
                        <p class="text-xs font-serif text-slate-800 text-justify leading-relaxed indent-8 mb-4">
                            Implementasi sistem merupakan tahap perwujudan konkret dari rancangan logika basis data, alur aktivitas kerja, dan aturan bisnis yang telah dirumuskan sebelumnya ke dalam bentuk modul-modul fungsional perangkat lunak yang beroperasi secara utuh. Setiap modul dirancang untuk saling terhubung dalam satu arsitektur terintegrasi, memastikan pergerakan stok barang, mutasi transaksi penjualan, dan pencatatan logistik tercatat secara akurat ke dalam basis data. Perwujudan fungsional sistem ini dibuktikan melalui pengoperasian antarmuka pengguna pada masing-masing modul yang dikelompokkan ke dalam empat ranah utama, yaitu: (1) Implementasi Gerbang Autentikasi Staf, (2) Implementasi Sistem Operasional Admin dan Kasir, (3) Implementasi Sistem Manajerial dan Eksekutif Superadmin, serta (4) Implementasi Sistem Toko Daring (E-Commerce) Pelanggan. Rincian implementasi pada masing-masing fungsi sistem diuraikan sebagai berikut:
                        </p>
    ''')

    for s in ui_screens:
        # Group heading if any
        if s.get('group'):
            html.append(f'''
                        <div class="mt-6 mb-3 pt-3 border-t border-slate-200">
                            <h5 class="text-xs font-bold font-serif text-slate-900 tracking-wide uppercase">
                                {s['group']}
                            </h5>
                            <p class="text-xs font-serif text-slate-700 text-justify leading-relaxed indent-8 mt-1.5 mb-3">
                                {s['group_intro']}
                            </p>
                        </div>
            ''')

        # Subsection header
        html.append(f'''
                        <div class="mt-4 mb-2">
                            <h6 class="text-xs font-bold font-serif text-slate-800 italic">
                                {s['sub_num']}. {s['title']}
                            </h6>
                        </div>

                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/{s['img_file']}" alt="{s['fig_title']}" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                {s['num']} {s['fig_title']}
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                {s['desc']}
                            </p>
                        </div>
        ''')

    html.append('''
                    </div> <!-- Closes subbab_411_content -->
                </div> <!-- Closes 4.1.1 -->
            </div> <!-- Closes subbab_41_content -->
        </div> <!-- Closes 4.1 -->
    ''')
    return "\n".join(html)

bab4_content = build_bab4_html()

# Strict verification of 'apotek' and 'farmasi'
c_apotek = bab4_content.lower().count('apotek')
c_farmasi = bab4_content.lower().count('farmasi')
print(f"Bab IV content verification -> 'apotek': {c_apotek}, 'farmasi': {c_farmasi}")
assert c_apotek == 0, f"Found 'apotek' in Bab IV content!"
assert c_farmasi == 0, f"Found 'farmasi' in Bab IV content!"

# 1. Build Standalone BAB_IV_ZENCARE_MEDICAL.html
standalone_html = f'''<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naskah Bab IV - Hasil dan Pembahasan (ZenCare Medical)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">
    <style>
        body {{
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }}

        .paper {{
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.8;
            font-size: 12pt;
            background: white;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.1);
        }}

        .paper h1,
        .paper h2,
        .paper h3,
        .paper h4,
        .paper h5,
        .paper h6 {{
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }}

        @media print {{
            body {{
                background: white;
                color: black;
                padding: 0;
            }}

            .no-print {{
                display: none !important;
            }}

            .paper {{
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
            }}
        }}
    </style>
    <script>
        function copyText(elementId, btnElement) {{
            const el = document.getElementById(elementId);
            const text = el.innerText || el.textContent;
            navigator.clipboard.writeText(text).then(() => {{
                const originalText = btnElement.innerHTML;
                btnElement.innerHTML = `<svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                btnElement.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                setTimeout(() => {{
                    btnElement.innerHTML = originalText;
                    btnElement.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                }}, 2000);
            }}).catch(err => {{
                alert('Gagal menyalin: ' + err);
            }});
        }}
    </script>
</head>

<body class="py-8 px-4 sm:px-6 lg:px-8">

    <!-- Top Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 no-print">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-1">
                    Naskah Laporan Terverifikasi Sistem
                </span>
                <h1 class="text-xl font-bold text-slate-800">BAB IV: Hasil dan Pembahasan</h1>
                <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Inventori Omnichannel - ZenCare Medical (Subbab 4.1 s.d. 4.1.1 Implementasi Sistem)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="BAB_III_ZENCARE_MEDICAL.html" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Lihat Bab III
                </a>
                <button onclick="copyText('full_bab4_content', this)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    Salin Semua Bab IV
                </button>
                <button onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Main Academic Paper Container -->
    <div id="full_bab4_content" class="paper max-w-4xl mx-auto p-8 sm:p-14 rounded-lg">
        {bab4_content}
    </div>

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori Omnichannel ZenCare Medical
    </div>

</body>
</html>
'''

standalone_path = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html"
with open(standalone_path, 'w', encoding='utf-8') as f:
    f.write(standalone_html)
print("Standalone BAB_IV_ZENCARE_MEDICAL.html generated!")

# 2. Update BAB_III_ZENCARE_MEDICAL.html (and sanitize Bab III occurrences of apotek)
bab3_path = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html"
with open(bab3_path, 'r', encoding='utf-8') as f:
    orig_bab3 = f.read()

# Sanitize any apotek in Bab III portion (before Bab IV injection)
# Line 7273 had: "alamat fisik apotek" -> "alamat operasional toko fisik"
orig_bab3 = orig_bab3.replace("alamat fisik apotek", "alamat operasional toko fisik")
orig_bab3 = orig_bab3.replace("alamat fisik toko dan apotek", "alamat operasional gerai dan kantor fisik ZenCare Medical")
orig_bab3 = orig_bab3.replace("operasional apotek", "operasional toko kesehatan")

# Replace full_bab4_content
p_bab4 = r'(<div id="full_bab4_content" class="paper max-w-4xl mx-auto p-8 sm:p-14 rounded-lg mt-8">)(.*?)(</div> <!-- Closes full_bab4_content paper -->)'
match = re.search(p_bab4, orig_bab3, re.DOTALL)
if not match:
    raise ValueError("Could not find full_bab4_content in BAB_III_ZENCARE_MEDICAL.html")

unified_html = orig_bab3[:match.start(2)] + "\n" + bab4_content + "\n    " + orig_bab3[match.end(2):]

with open(bab3_path, 'w', encoding='utf-8') as f:
    f.write(unified_html)
print(f"Unified {bab3_path} updated!")

# 3. Update explicit unified BAB_III_DAN_IV_ZENCARE_MEDICAL.html
unified_explicit_path = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html"
with open(unified_explicit_path, 'w', encoding='utf-8') as f:
    f.write(unified_html)
print(f"Explicit unified {unified_explicit_path} updated!")

# 4. Also sanitize apotek in lain/ directory files
lain_files = [
    r"c:\xampp\htdocs\inventory_zencare\lain\katalog_ui_ux_asli_zencare.html",
    r"c:\xampp\htdocs\inventory_zencare\lain\wireframe_zencare.html",
    r"c:\xampp\htdocs\inventory_zencare\lain\semua_workflow_zencare.html"
]

for lf in lain_files:
    if os.path.exists(lf):
        with open(lf, 'r', encoding='utf-8') as f:
            c = f.read()
        c_mod = c.replace("apotek", "toko kesehatan ZenCare Medical")
        c_mod = c_mod.replace("Apotek", "Toko Kesehatan ZenCare Medical")
        with open(lf, 'w', encoding='utf-8') as f:
            f.write(c_mod)
        print(f"Sanitized 'apotek' in {os.path.basename(lf)}")

print("\nAll files successfully updated and aligned!")

