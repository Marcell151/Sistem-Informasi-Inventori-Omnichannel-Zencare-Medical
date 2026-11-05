# -*- coding: utf-8 -*-
"""
Script to generate Bab IV content, verify zero occurrences of 'farmasi',
and assemble both standalone BAB_IV_ZENCARE_MEDICAL.html and unified
BAB_III_ZENCARE_MEDICAL.html (Bab III + Bab IV).
"""

import os
import re

ui_screens = [
    # Kelompok A: Autentikasi Pengguna
    {
        "num": "Gambar 4.1",
        "sub_num": "1",
        "title": "Tampilan Antarmuka Halaman Login Staf",
        "img_file": "01_Login_Staf.png",
        "group": "A. Antarmuka Gerbang Autentikasi Staf",
        "group_intro": "Antarmuka gerbang autentikasi merupakan garda depan pengamanan sistem yang membatasi hak akses pengguna internal berdasarkan verifikasi identitas resmi. Implementasi antarmuka ini memastikan bahwa hanya staf yang memiliki wewenang yang dapat mengakses data operasional maupun manajerial toko kesehatan ZenCare Medical.",
        "desc": (
            "Halaman autentikasi utama bagi staf internal apotek (Admin dan Superadmin). "
            "Antarmuka ini menampilkan formulir masukan berupa nama pengguna (username) dan kata sandi (password), "
            "identitas visual toko kesehatan ZenCare Medical, serta penanda status kredensial. Sistem memvalidasi "
            "masukan data terhadap tabel users menggunakan algoritma pencocokan kata sandi yang telah terenkripsi secara aman. "
            "Berdasarkan nilai atribut peran (role), sistem secara otomatis mengarahkan sesi kerja ke antarmuka yang bersesuaian, "
            "yakni dashboard eksekutif untuk Superadmin atau dashboard operasional toko untuk staf Admin. "
            "Halaman ini juga dilengkapi proteksi pengalihan sesi aktif sehingga pengguna yang belum terautentikasi "
            "tidak dapat membuka halaman administratif melalui manipulasi tautan peramban."
        )
    },
    # Kelompok B: Operasional Admin & Kasir
    {
        "num": "Gambar 4.2",
        "sub_num": "2",
        "title": "Tampilan Antarmuka Halaman Dashboard Admin",
        "img_file": "02_Dashboard_Admin.png",
        "group": "B. Antarmuka Operasional Admin dan Kasir",
        "group_intro": "Kelompok antarmuka operasional dirancang untuk memfasilitasi aktivitas rutin harian staf toko dan penanggung jawab gudang. Antarmuka pada kelompok ini berfokus pada kecepatan transaksi kasir, kemudahan pemantauan persediaan obat dan alat kesehatan, ketelitian pencatatan pasokan masuk, serta ketepatan penanganan alur logistik medis.",
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
        "title": "Tampilan Antarmuka Halaman Terminal Kasir (Point of Sale / POS)",
        "img_file": "03_POS_Kasir.png",
        "group": None,
        "desc": (
            "Terminal kasir penjualan langsung di toko fisik yang mengusung tata letak dua panel terpadu guna mempercepat alur pelayanan pelanggan. "
            "Panel sebelah kiri menyajikan bilah pencarian obat dan alat kesehatan berbasis nama maupun pemindaian kode batang (barcode), "
            "penyaring kategori medis, serta susunan kartu produk interaktif yang memuat informasi harga dan sisa stok fisik siap jual. "
            "Panel sebelah kanan berfungsi sebagai lembar kerja transaksi kasir yang memuat daftar keranjang belanja, kalkulasi otomatis subtotal belanja, "
            "pilihan metode pembayaran tunai maupun non-tunai, perhitungan nominal uang kembali, serta tombol penyelesaian transaksi. "
            "Saat pembayaran divalidasi, sistem menjalankan algoritma pengeluaran stok berbasis tanggal kedaluwarsa terdekat (FEFO) secara otomatis "
            "melalui tabel pesanan_batch_alokasi dan memperbarui kartu_stok secara seketika."
        )
    },
    {
        "num": "Gambar 4.4",
        "sub_num": "4",
        "title": "Tampilan Antarmuka Halaman Transaksi Penjualan Omnichannel",
        "img_file": "04_Transaksi_Omnichannel.png",
        "group": None,
        "desc": (
            "Pusat kendali rekaman transaksi penjualan yang menyatukan seluruh riwayat pesanan dari saluran kasir toko luring (POS) "
            "maupun saluran pesanan toko daring (E-Commerce). Antarmuka dilengkapi navigasi penyaring saluran penjualan, penyaring status pemrosesan "
            "(menunggu pembayaran, diproses, dikirim, selesai, dan dibatalkan), pencarian berdasarkan nomor faktur atau nama pemesan, "
            "serta visualisasi lencana status transaksi yang informatif. Staf dapat melihat rincian barang yang dipesan, memperbarui status pengiriman "
            "dengan memasukkan nomor resi ekspedisi atau mengonfirmasi pengambilan langsung di toko (in-store pickup), "
            "serta mencetak lembar nota resmi. Antarmuka ini beroperasi langsung di atas tabel penjualan dan detail_penjualan."
        )
    },
    {
        "num": "Gambar 4.5",
        "sub_num": "5",
        "title": "Tampilan Antarmuka Halaman Master Produk dan Katalog Medis",
        "img_file": "05_Master_Produk.png",
        "group": None,
        "desc": (
            "Antarmuka pengelolaan katalog dasar induk untuk seluruh komoditas obat dan alat kesehatan yang diperjualbelikan. "
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
        "title": "Tampilan Antarmuka Halaman Pengaturan Satuan dan Multi-UOM",
        "img_file": "06_Satuan_UOM.png",
        "group": None,
        "desc": (
            "Antarmuka konfigurasi struktur multi-satuan (Unit of Measure / Multi-UOM) yang menghubungkan satuan kemasan grosir "
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
        "title": "Tampilan Antarmuka Halaman Direktori Supplier",
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
        "title": "Tampilan Antarmuka Halaman Penerimaan Barang Masuk (Inbound)",
        "img_file": "08_Penerimaan_Barang.png",
        "group": None,
        "desc": (
            "Antarmuka pendataan logistik masuk (inbound) yang mencatat pengiriman stok obat dan alat kesehatan dari distributor ke gudang apotek. "
            "Formulir penerimaan mewajibkan penginputan nomor referensi dokumen pengiriman (surat jalan atau faktur distributor), "
            "pemilihan mitra distributor, tanggal fisik penerimaan, berkas nota, serta baris daftar barang dinamis. "
            "Untuk setiap item yang diterima, staf mencatat nomor batch pabrik, tanggal jatuh tempo kedaluwarsa, kuantitas unit, "
            "dan harga beli satuan. Ketika berkas penerimaan disahkan, sistem secara otomatis menambah saldo fisik pada tabel stok_batch dan stok_toko, "
            "mencatat riwayat mutasi masuk pada kartu_stok, serta membukukan dokumen penerimaan pada tabel penerimaan_stok dan penerimaan_detail."
        )
    },
    {
        "num": "Gambar 4.9",
        "sub_num": "9",
        "title": "Tampilan Antarmuka Halaman Mutasi Stok Manual",
        "img_file": "09_Mutasi_Stok.png",
        "group": None,
        "desc": (
            "Antarmuka penyesuaian kuantitas persediaan fisik di luar siklus transaksi reguler penjualan maupun penerimaan distributor. "
            "Antarmuka menyediakan formulir penyesuaian yang memuat pemilihan variasi produk, pemilihan nomor batch spesifik, jenis pergerakan "
            "(penambahan stok atau pengurangan stok), jumlah unit yang disesuaikan, serta kolom alasan mutasi (misalnya barang rusak, kemasan bocor, "
            "retur internal, atau penyesuaian selisih administratif). Pada panel sisi kanan, sistem menampilkan tabel riwayat mutasi manual terdahulu "
            "yang memuat keterangan waktu, jumlah, dan nama staf pelaksana, yang tercatat secara permanen pada tabel kartu_stok."
        )
    },
    {
        "num": "Gambar 4.10",
        "sub_num": "10",
        "title": "Tampilan Antarmuka Halaman Kartu Stok Audit",
        "img_file": "10_Kartu_Stok.png",
        "group": None,
        "desc": (
            "Halaman pemantauan buku besar mutasi persediaan (inventory ledger) yang merekam jejak audit kronologis setiap komoditas medis. "
            "Menampilkan tabel catatan pergerakan barang yang mencakup stempel waktu kejadian, nomor dokumen referensi transaksi, "
            "saluran aktivitas terkait, jenis aksi mutasi, nomor batch produk, kuantitas barang masuk, kuantitas barang keluar, "
            "dan sisa saldo stok akhir yang terverifikasi. Dilengkapi dengan penyaring pilihan produk variasi dan rentang waktu pengamatan, "
            "antarmuka ini menjadi rujukan utama bagi auditor internal untuk menelusuri keabsahan saldo fisik pada tabel kartu_stok."
        )
    },
    {
        "num": "Gambar 4.11",
        "sub_num": "11",
        "title": "Tampilan Antarmuka Halaman Stok Opname dan Audit Fisik",
        "img_file": "11_Stok_Opname.png",
        "group": None,
        "desc": (
            "Antarmuka pelaksanaan kegiatan audit fisik persediaan (stok opname) berkala untuk membandingkan kuantitas persediaan di rak "
            "dengan saldo pembukuan sistem. Menampilkan daftar sesi audit yang sedang berlangsung maupun yang telah dibukukan, "
            "fasilitas inisiasi sesi opname baru oleh staf berwenang, formulir pencatatan hitung fisik per batch dan nomor seri, "
            "serta kalkulasi otomatis terhadap selisih jumlah barang (discrepancy). Apabila hasil verifikasi fisik disetujui, "
            "sistem melakukan penyesuaian saldo secara serentak ke dalam tabel stok_batch, sesi_opname, detail_opname_batch, dan riwayat_hitung_opname."
        )
    },
    {
        "num": "Gambar 4.12",
        "sub_num": "12",
        "title": "Tampilan Antarmuka Halaman Konversi Satuan dan Pemecahan Kemasan",
        "img_file": "12_Konversi_Satuan.png",
        "group": None,
        "desc": (
            "Antarmuka pemrosesan pemecahan kemasan besar menjadi kemasan satuan eceran siap jual (seperti membuka 1 dus menjadi sejumlah botol atau strip). "
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
        "title": "Tampilan Antarmuka Halaman Dashboard Superadmin",
        "img_file": "13_Dashboard_Superadmin.png",
        "group": "C. Antarmuka Manajerial dan Eksekutif Superadmin",
        "group_intro": "Kelompok antarmuka manajerial ditujukan bagi pimpinan dan pengelola strategis (Superadmin). Antarmuka pada kelompok ini berfokus pada penyajian ringkasan analitis kinerja finansial, valuasi nilai modal persediaan, pengawasan kepatuhan pengeluaran stok berdasarkan batas kedaluwarsa (FEFO), audit anomali logistik, hingga pengaturan keamanan akun pengguna sistem.",
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
        "title": "Tampilan Antarmuka Halaman Laporan Penjualan Barang",
        "img_file": "14_Laporan_Penjualan.png",
        "group": None,
        "desc": (
            "Antarmuka rekapitulasi data penjualan komprehensif yang dirancang untuk analisis pendapatan periodik. "
            "Pengguna manajerial dapat memfilter data berdasarkan interval tanggal transaksi dan memilah saluran penjualan tertentu (kasir POS atau toko daring). "
            "Tabel laporan menyajikan perincian faktur mencakup tanggal transaksi, nomor invoice, nama pembeli atau pelanggan, saluran pembelian, "
            "metode pembayaran yang digunakan, total belanja barang, biaya pengiriman, serta status akhir faktur. "
            "Halaman dilengkapi tombol ekspor data dan cetak laporan resmi untuk keperluan rekapitulasi kas dan pembukuan keuangan toko."
        )
    },
    {
        "num": "Gambar 4.15",
        "sub_num": "15",
        "title": "Tampilan Antarmuka Halaman Laporan Persediaan dan Valuasi Aset",
        "img_file": "15_Laporan_Persediaan.png",
        "group": None,
        "desc": (
            "Halaman laporan analitis yang menyajikan nilai modal kerja dan tingkat ketersediaan seluruh persediaan komoditas medis. "
            "Antarmuka merinci data per variasi produk yang mencakup jumlah kuantitas fisik yang tersedia di rak dan gudang, harga beli rata-rata, "
            "harga jual eceran, estimasi total valuasi aset barang, serta status persediaan (kategori aman, menipis, atau habis). "
            "Laporan ini bersumber dari kalkulasi langsung data tabel stok_batch dan produk_variasi, memberikan kepastian kepada manajemen "
            "mengenai perputaran modal barang dan efisiensi pengadaan persediaan."
        )
    },
    {
        "num": "Gambar 4.16",
        "sub_num": "16",
        "title": "Tampilan Antarmuka Halaman Laporan Logistik Medis (Kedaluwarsa)",
        "img_file": "16_Laporan_Logistik_Medis.png",
        "group": None,
        "desc": (
            "Panel pengawasan kelayakan dan keselamatan produk obat serta komoditas medis berdasarkan batas tanggal kedaluwarsa pabrikan. "
            "Antarmuka mengelompokkan barang ke dalam tiga tingkat kewaspadaan visual: persediaan dalam kondisi aman, persediaan yang memasuki masa kritis "
            "(misalnya kurang dari 30 atau 90 hari sebelum kedaluwarsa), dan persediaan yang telah melewati masa berlaku. "
            "Informasi ini memandu pihak manajemen untuk mengambil langkah strategis yang cepat, seperti pelaksanaan diskon khusus (clearance) "
            "maupun pemisahan barang retur ke distributor, merujuk data pada tabel stok_batch dan produk_induk."
        )
    },
    {
        "num": "Gambar 4.17",
        "sub_num": "17",
        "title": "Tampilan Antarmuka Halaman Laporan Penerimaan Barang Masuk",
        "img_file": "17_Laporan_Penerimaan_Barang.png",
        "group": None,
        "desc": (
            "Halaman pelaporan audit logistik masuk (inbound) yang merekapitulasi seluruh pasokan barang yang telah diterima dari distributor. "
            "Menampilkan tabel komprehensif yang memuat nomor tanda terima penerimaan, nama instansi distributor pengirim, tanggal kedatangan barang di apotek, "
            "nama staf yang menerima, rincian produk variasi, nomor batch kiriman, tanggal kedaluwarsa resmi, kuantitas pasokan yang diterima, "
            "serta total nilai nominal pembelian. Dilengkapi fitur pencarian dan penyaring tanggal, laporan ini memudahkan proses pencocokan faktur tagihan "
            "dari distributor dengan pencatatan fisik pada tabel penerimaan_stok dan penerimaan_detail."
        )
    },
    {
        "num": "Gambar 4.18",
        "sub_num": "18",
        "title": "Tampilan Antarmuka Halaman Laporan Mutasi dan Konversi Satuan",
        "img_file": "18_Laporan_Mutasi_Konversi.png",
        "group": None,
        "desc": (
            "Antarmuka rekapitulasi audit pergerakan persediaan non-penjualan yang menyatukan catatan mutasi manual (seperti barang rusak atau koreksi) "
            "dan riwayat proses konversi pemecahan satuan (pecah kemasan dus menjadi eceran). Laporan menyajikan stempel waktu kegiatan, "
            "nomor referensi dokumen bukti, nama produk variasi medis, nomor batch terkait, kuantitas asal serta kuantitas hasil konversi, "
            "penjelasan alasan perubahan, dan identitas staf operasional yang melakukan tindakan. "
            "Data ditarik secara konsolidatif dari tabel histori_konversi dan kartu_stok guna menjamin ketertiban tata kelola pergudangan."
        )
    },
    {
        "num": "Gambar 4.19",
        "sub_num": "19",
        "title": "Tampilan Antarmuka Halaman Laporan Audit Anomali Pengeluaran FEFO",
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
        "title": "Tampilan Antarmuka Halaman Manajemen Pengguna Sistem",
        "img_file": "18_Manajemen_Pengguna.png",
        "group": None,
        "desc": (
            "Antarmuka tata kelola akun pengguna sistem yang hanya dapat diakses oleh peran Superadmin untuk menjaga integritas otorisasi. "
            "Menampilkan tabel master akun pengguna yang memuat identitas nama lengkap, nama pengguna (username), alamat surel, "
            "nomor telepon kontak, penugasan hak akses peran (Superadmin, Admin, atau Pelanggan), status akun (aktif atau nonaktif), "
            "serta waktu pendaftaran. Melalui antarmuka ini, Superadmin dapat mendaftarkan akun staf baru, memperbarui peran kerja, "
            "melakukan reset kata sandi, atau menonaktifkan akun yang sudah tidak memiliki wewenang pada tabel users."
        )
    },
    {
        "num": "Gambar 4.21",
        "sub_num": "21",
        "title": "Tampilan Antarmuka Halaman Pengaturan Toko Daring (Pengaturan Web)",
        "img_file": "19_Pengaturan_Web.png",
        "group": None,
        "desc": (
            "Panel administrasi konfigurasi profil usaha dan aset informasi publik toko daring ZenCare Medical. "
            "Antarmuka ini memfasilitasi Superadmin dalam memperbarui nama resmi bisnis toko, deskripsi profil usaha, "
            "alamat fisik toko dan apotek, nomor kontak pelayanan pelanggan (CS), integrasi nomor WhatsApp resmi, "
            "berkas gambar logo identitas toko, serta gambar spanduk promosi yang dipajang di halaman muka situs. "
            "Seluruh konfigurasi ini disimpan secara terpusat pada tabel pengaturan_web sehingga pembaruan data langsung "
            "tercermin secara seragam pada seluruh antarmuka pelanggan."
        )
    },
    # Kelompok D: Pelanggan E-Commerce
    {
        "num": "Gambar 4.22",
        "sub_num": "22",
        "title": "Tampilan Antarmuka Halaman Masuk dan Pendaftaran Pelanggan",
        "img_file": "20_Login_Pelanggan.png",
        "group": "D. Antarmuka Toko Daring (E-Commerce) Pelanggan",
        "group_intro": "Kelompok antarmuka toko daring (E-Commerce) dirancang khusus untuk memberikan pengalaman belanja kebutuhan kesehatan yang mudah, aman, dan informatif bagi masyarakat luas. Antarmuka ini memfasilitasi pencarian produk, rincian informasi obat dan alat kesehatan, keranjang belanja interaktif, hingga proses pembayaran dan pemilihan kurir pengiriman terintegrasi.",
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
        "title": "Tampilan Antarmuka Halaman Profil dan Riwayat Pesanan Pelanggan",
        "img_file": "21_Profil_Pelanggan.png",
        "group": None,
        "desc": (
            "Halaman panel kendali pribadi pelanggan yang menampilkan profil pengguna terdaftar, alamat tujuan pengiriman barang, "
            "nomor kontak aktif, serta daftar rekapitulasi riwayat transaksi belanja yang pernah dilakukan. "
            "Pelanggan dapat memantau status pemrosesan pesanan secara langsung (menunggu verifikasi pembayaran, pesanan sedang disiapkan staf, "
            "barang dalam proses pengantaran kurir dengan tautan lacak resi, atau pesanan siap diambil di kasir apotek), "
            "lengkap dengan rincian bukti transaksi dari tabel penjualan dan detail_penjualan."
        )
    },
    {
        "num": "Gambar 4.24",
        "sub_num": "24",
        "title": "Tampilan Antarmuka Halaman Beranda Toko Daring (E-Commerce)",
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
        "title": "Tampilan Antarmuka Halaman Rincian Informasi Produk (Detail Produk)",
        "img_file": "23_Detail_Produk.png",
        "group": None,
        "desc": (
            "Halaman rincian spesifikasi barang medis yang dipilih oleh pelanggan untuk memberikan transparansi informasi sebelum membeli. "
            "Menampilkan foto komoditas resolusi tinggi, nama produk obat atau alat kesehatan, kepastian status stok fisik terkini di apotek, "
            "pilihan jenis kemasan variasi (seperti strip, botol, atau boks) dengan harga masing-masing, penjelasan indikasi atau manfaat medis, "
            "petunjuk penyimpanan barang, pengatur jumlah pembelian, serta tombol aksi untuk memasukkan barang ke keranjang belanja maupun checkout instan."
        )
    },
    {
        "num": "Gambar 4.26",
        "sub_num": "26",
        "title": "Tampilan Antarmuka Halaman Panel Keranjang Belanja Interaktif",
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
        "title": "Tampilan Antarmuka Halaman Pembayaran dan Pengiriman (Checkout)",
        "img_file": "25_Checkout_Pembayaran.png",
        "group": None,
        "desc": (
            "Halaman tahap akhir transaksi daring pelanggan yang memadukan formulir data penerima barang, pilihan metode penyerahan pesanan "
            "(diantar oleh kurir ekspedisi ke alamat tujuan atau diambil langsung secara mandiri di toko apotek / in-store pickup), "
            "penentuan titik lokasi koordinat pengiriman yang akurat menggunakan penanda peta digital interaktif, kalkulasi otomatis tarif ongkos kirim "
            "berdasarkan kota tujuan, ringkasan rincian tagihan belanja, serta tombol pembayaran aman yang terhubung langsung dengan penyedia gerbang pembayaran "
            "digital. Saat transaksi dikonfirmasi, sistem secara otomatis membukukan pesanan baru ke dalam tabel penjualan dan detail_penjualan."
        )
    }
]

print("Total UI screens defined:", len(ui_screens))
assert len(ui_screens) == 27, f"Expected 27 screens, got {len(ui_screens)}"

# Check for 'farmasi'
farmasi_count = 0
for s in ui_screens:
    full_text = f"{s['title']} {s.get('group_intro', '')} {s['desc']}".lower()
    if 'farmasi' in full_text:
        farmasi_count += 1
        print(f"Warning: 'farmasi' found in {s['num']}")

print("Occurrences of 'farmasi' in data definitions:", farmasi_count)
assert farmasi_count == 0, "Violated constraint: 'farmasi' must not occur!"

