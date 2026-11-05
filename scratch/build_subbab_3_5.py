import os
import re

# Comprehensive 20 Tables Data Dictionary for Subbab 3.5
tables_data_3_5 = [
    {
        'sub_no': '3.5.1',
        'table_no': '3.1',
        'name': 'users',
        'domain': 'Master Pengguna',
        'pk': 'id_user',
        'fk': '-',
        'fungsi': 'Tabel master yang berfungsi untuk merekam dan mengelola data pengguna sistem beserta pembagian hak akses operasional. Entitas ini memisahkan wewenang pengguna internal (Superadmin, Admin, dan Kasir) dengan pengguna eksternal (Pelanggan), serta mengamankan kredensial akun melalui algoritma enkripsi hash bcrypt.',
        'cols': [
            ('id_user', 'Integer', '11', 'Primary Key, Auto Increment. ID unik akun pengguna.'),
            ('username', 'Varchar', '50', 'Nama pengguna unik untuk proses autentikasi masuk (Unique).'),
            ('password', 'Varchar', '255', 'Kata sandi akun yang diamankan dengan enkripsi hash bcrypt.'),
            ('nama_lengkap', 'Varchar', '100', 'Nama lengkap resmi pemilik akun pengguna atau pelanggan.'),
            ('role', 'Enum', '-', "Hak akses sesi ('superadmin', 'admin', 'kasir', 'pelanggan')."),
            ('email', 'Varchar', '150', 'Alamat surat elektronik resmi untuk korespondensi akun.'),
            ('telepon', 'Varchar', '20', 'Nomor kontak telepon atau WhatsApp aktif pengguna.'),
            ('alamat', 'Text', '-', 'Alamat domisili utama pelanggan untuk pengiriman pesanan daring.'),
            ('kota_id', 'Integer', '11', 'ID referensi kota destinasi untuk integrasi API ongkir RajaOngkir.'),
            ('is_active', 'Boolean', '1', 'Indikator status keaktifan akun (1 = Aktif, 0 = Nonaktif).'),
            ('created_at', 'Timestamp', '-', 'Waktu pendaftaran akun pertama kali dicatat oleh sistem.')
        ]
    },
    {
        'sub_no': '3.5.2',
        'table_no': '3.2',
        'name': 'pengaturan_web',
        'domain': 'Pengaturan Sistem',
        'pk': 'id_pengaturan',
        'fk': '-',
        'fungsi': 'Entitas independen yang bertugas menyimpan variabel konfigurasi global portal e-commerce. Tabel ini memusatkan pengelolaan identitas bisnis toko ritel, aset visual spanduk, tautan logo, hingga saluran kontak layanan pelanggan agar dapat dikelola mandiri tanpa membebani struktur logistik.',
        'cols': [
            ('id_pengaturan', 'Integer', '11', 'Primary Key, Auto Increment. ID unik konfigurasi web.'),
            ('nama_toko', 'Varchar', '100', 'Nama identitas resmi toko ritel e-commerce (ZenCare Medical).'),
            ('deskripsi', 'Text', '-', 'Penjelasan ringkas profil bisnis dan layanan kesehatan toko.'),
            ('telepon', 'Varchar', '20', 'Nomor kontak saluran telepon pusat layanan toko.'),
            ('whatsapp', 'Varchar', '20', 'Nomor resmi layanan pesan instan WhatsApp toko.'),
            ('alamat', 'Text', '-', 'Alamat fisik operasional gerai luring toko obat dan alkes.'),
            ('logo', 'Varchar', '255', 'Direktori tautan berkas citra logo identitas merek.'),
            ('banner_promosi', 'Varchar', '255', 'Direktori tautan berkas citra spanduk promosi beranda.'),
            ('email_cs', 'Varchar', '150', 'Alamat surel resmi pusat bantuan layanan pelanggan.')
        ]
    },
    {
        'sub_no': '3.5.3',
        'table_no': '3.3',
        'name': 'supplier',
        'domain': 'Master Pemasok',
        'pk': 'id_supplier',
        'fk': '-',
        'fungsi': 'Tabel direktori master yang mencatat profil distributor atau pemasok resmi rekanan komoditas obat dan alat kesehatan. Entitas ini menjadi rujukan dinamis bagi dokumen penerimaan pasokan masuk guna melacak asal-usul ketersediaan fisik barang di gudang.',
        'cols': [
            ('id_supplier', 'Integer', '11', 'Primary Key, Auto Increment. ID unik distributor pemasok.'),
            ('nama', 'Varchar', '100', 'Nama instansi distributor resmi rekanan pemasok komoditas.'),
            ('kontak', 'Varchar', '50', 'Nomor telepon perwakilan sales atau kantor distributor.'),
            ('email', 'Varchar', '150', 'Alamat surat elektronik korespondensi pihak distributor.'),
            ('alamat', 'Text', '-', 'Alamat lengkap kantor atau gudang pusat distributor rekanan.'),
            ('is_active', 'Boolean', '1', 'Indikator keaktifan jalinan kerja sama pasokan (1 = Aktif).')
        ]
    },
    {
        'sub_no': '3.5.4',
        'table_no': '3.4',
        'name': 'produk_induk',
        'domain': 'Master Katalog',
        'pk': 'id_produk_induk',
        'fk': '-',
        'fungsi': 'Entitas ini menyimpan profil katalog dasar dari seluruh komoditas medis yang dipasarkan. Data ini memusatkan informasi umum seperti nama dagang, indikasi medis, spesifikasi teknis alat, kategori barang, hingga batas hari peringatan kedaluwarsa sistem.',
        'cols': [
            ('id_produk_induk', 'Integer', '11', 'Primary Key, Auto Increment. ID induk katalog produk.'),
            ('sku_induk', 'Varchar', '50', 'Kode identitas katalog dasar produk medis (Unique).'),
            ('nama_produk', 'Varchar', '150', 'Nama dagang umum komoditas obat atau alat kesehatan.'),
            ('deskripsi', 'Text', '-', 'Uraian ringkas indikasi penggunaan dan manfaat produk.'),
            ('spesifikasi', 'Text', '-', 'Komposisi zat aktif medis atau spesifikasi teknis alkes.'),
            ('info_pengiriman', 'Text', '-', 'Keterangan perlakuan khusus penyimpanan dan pengiriman.'),
            ('kategori', 'Enum', '-', "Klasifikasi kategori utama ('Obat', 'Alat Kesehatan')."),
            ('gambar', 'Varchar', '255', 'Direktori tautan berkas citra utama katalog produk.'),
            ('is_active', 'Boolean', '1', 'Indikator status penayangan katalog produk (1 = Aktif).'),
            ('batas_hari_expired', 'Integer', '11', 'Ambang batas hari sebelum kedaluwarsa untuk peringatan dini.'),
            ('created_at', 'Timestamp', '-', 'Waktu data katalog produk pertama kali direkam sistem.')
        ]
    },
    {
        'sub_no': '3.5.5',
        'table_no': '3.5',
        'name': 'produk_variasi',
        'domain': 'Master Logistik (Poros)',
        'pk': 'id_variasi',
        'fk': 'id_produk_induk',
        'fungsi': 'Entitas poros logistik yang mengatur sistem multi-satuan (Multi-UOM) dari sebuah katalog produk induk. Tabel ini mendefinisikan hubungan konversi antara kemasan besar (dus/box) dengan eceran (strip/pcs), harga jual per kemasan, stok minimum, serta bobot kirim.',
        'cols': [
            ('id_variasi', 'Integer', '11', 'Primary Key, Auto Increment. ID unik variasi kemasan fisik.'),
            ('id_produk_induk', 'Integer', '11', 'Foreign Key merujuk ke tabel katalog dasar produk_induk.id_produk_induk.'),
            ('sku_variasi', 'Varchar', '50', 'Kode SKU fisik per variasi kemasan spesifik (Unique).'),
            ('nama_variasi', 'Varchar', '100', 'Label nama variasi kemasan (Strip, Box 10 Strip, Unit).'),
            ('satuan_kecil', 'Varchar', '50', 'Label satuan transaksi eceran (Strip, Tablet, Pcs).'),
            ('satuan_besar', 'Varchar', '50', 'Label satuan kemasan besar dari pabrik (Dus, Box, Karton).'),
            ('rasio_konversi', 'Smallint', '6', 'Angka pengali konversi kuantitas dari satuan besar ke eceran.'),
            ('harga_jual_kecil', 'Decimal', '12,2', 'Ketetapan harga jual resmi per satuan eceran kecil.'),
            ('harga_jual_besar', 'Decimal', '12,2', 'Ketetapan harga jual resmi per kemasan satuan besar.'),
            ('stok_minimum_kecil', 'Integer', '11', 'Batas peringatan persediaan minimum untuk satuan eceran.'),
            ('stok_minimum_besar', 'Integer', '11', 'Batas peringatan persediaan minimum untuk kemasan dus.'),
            ('berat', 'Integer', '11', 'Berat fisik produk dalam gram untuk kalkulasi ongkos kirim.'),
            ('tampil_di_online', 'Boolean', '1', 'Izin penayangan variasi pada etalase toko daring e-commerce.'),
            ('is_active', 'Boolean', '1', 'Indikator keaktifan operasional variasi kemasan produk.'),
            ('gambar', 'Varchar', '500', 'Direktori tautan gambar khusus variasi kemasan produk.')
        ]
    },
    {
        'sub_no': '3.5.6',
        'table_no': '3.6',
        'name': 'stok_toko',
        'domain': 'Persediaan Fisik',
        'pk': 'id_stok_toko',
        'fk': 'id_variasi',
        'fungsi': 'Entitas ini merekam angka akumulasi total dari ketersediaan persediaan fisik barang yang siap jual di toko dan gudang. Data ini diakses secara cepat oleh sistem kasir POS dan etalase daring tanpa perlu melakukan kalkulasi menyeluruh pada data riwayat batch.',
        'cols': [
            ('id_stok_toko', 'Integer', '11', 'Primary Key, Auto Increment. ID unik rekaman stok toko.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('stok', 'Integer', '11', 'Total akumulasi saldo kuantitas fisik aktif siap jual.')
        ]
    },
    {
        'sub_no': '3.5.7',
        'table_no': '3.7',
        'name': 'stok_batch',
        'domain': 'Logistik Medis FEFO',
        'pk': 'id_stok_batch',
        'fk': 'id_variasi',
        'fungsi': 'Tabel khusus manajemen logistik sediaan obat berbasis nomor batch pabrik dan tanggal kedaluwarsa fisik. Entitas ini menjadi rujukan utama eksekusi logika pengeluaran barang terdekat (First Expired First Out / FEFO) secara akurat.',
        'cols': [
            ('id_stok_batch', 'Integer', '11', 'Primary Key, Auto Increment. ID unik rekaman batch sediaan obat.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('no_batch', 'Varchar', '100', 'Nomor batch resmi produksi dari pabrik produsen obat.'),
            ('tgl_exp', 'Date', '-', 'Tanggal kedaluwarsa fisik sebagai parameter utama FEFO.'),
            ('stok_sisa', 'Integer', '11', 'Sisa kuantitas fisik obat yang tersedia pada nomor batch ini.'),
            ('created_at', 'Timestamp', '-', 'Waktu pencatatan nomor batch pertama kali ke sistem.')
        ]
    },
    {
        'sub_no': '3.5.8',
        'table_no': '3.8',
        'name': 'unit_serial',
        'domain': 'Logistik Alat Kesehatan',
        'pk': 'id_unit_serial',
        'fk': 'id_variasi, id_penjualan',
        'fungsi': 'Tabel logistik spesifik komoditas Alat Kesehatan. Entitas ini mengelola pelacakan aset berharga secara individual berdasarkan nomor seri (Serial Number / SN) unik cetakan pabrik guna mendukung audit ketersediaan unit tunggal dan validasi klaim masa garansi.',
        'cols': [
            ('id_unit_serial', 'Integer', '11', 'Primary Key, Auto Increment. ID unik rekaman unit berseri.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('serial_number', 'Varchar', '150', 'Nomor seri unik cetakan pabrik per unit alkes (Unique).'),
            ('status', 'Enum', '-', "Indikator ketersediaan ('Tersedia', 'Terjual', 'Retur/Rusak', 'Hilang')."),
            ('id_penjualan', 'Integer', '11', 'Foreign Key ke penjualan.id_penjualan (terisi otomatis saat terjual).'),
            ('catatan', 'Varchar', '255', 'Riwayat kondisi fisik, pengujian alat, atau klaim garansi.'),
            ('tgl_habis_garansi', 'Date', '-', 'Tanggal batas berakhirnya masa berlaku garansi resmi.'),
            ('created_at', 'Timestamp', '-', 'Waktu nomor seri pertama kali didaftarkan ke sistem.'),
            ('updated_at', 'Timestamp', '-', 'Waktu status keberadaan fisik terakhir diperbarui.')
        ]
    },
    {
        'sub_no': '3.5.9',
        'table_no': '3.9',
        'name': 'penerimaan_stok',
        'domain': 'Transaksi Pasokan Masuk',
        'pk': 'id_penerimaan',
        'fk': 'id_supplier, dibuat_oleh',
        'fungsi': 'Menampung data dokumen induk atas aktivitas pasokan atau pengadaan barang fisik masuk ke gudang. Tabel ini merekam nomor referensi faktur resmi distributor rekanan, tanggal kedatangan fisik, berkas digital pindaian faktur, dan staf penerima.',
        'cols': [
            ('id_penerimaan', 'Integer', '11', 'Primary Key, Auto Increment. ID dokumen induk penerimaan.'),
            ('no_referensi', 'Varchar', '50', 'Nomor surat jalan atau faktur resmi dari distributor.'),
            ('sumber', 'Enum', '-', "Metode pengadaan ('PO', 'Pembelian Langsung')."),
            ('id_supplier', 'Integer', '11', 'Foreign Key merujuk ke tabel supplier.id_supplier rekanan.'),
            ('tanggal_terima', 'Date', '-', 'Tanggal fisik serah terima barang di lokasi gudang.'),
            ('catatan', 'Text', '-', 'Catatan administratif tambahan dari staf penerima gudang.'),
            ('file_nota', 'Varchar', '255', 'Direktori berkas digital hasil pindai nota fisik faktur.'),
            ('dibuat_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (staf gudang pelaksana penerimaan).'),
            ('created_at', 'Timestamp', '-', 'Waktu perekaman dokumen penerimaan ke basis data.')
        ]
    },
    {
        'sub_no': '3.5.10',
        'table_no': '3.10',
        'name': 'penerimaan_detail',
        'domain': 'Transaksi Pasokan Masuk',
        'pk': 'id_penerimaan_detail',
        'fk': 'id_penerimaan, id_variasi',
        'fungsi': 'Mencatat rincian barang pasokan masuk dari dokumen penerimaan induk. Di dalam entitas inilah identitas batch dan tanggal kedaluwarsa dicatat pertama kali, serta menampung verifikasi barang cacat untuk pengajuan klaim penggantian retur ke distributor.',
        'cols': [
            ('id_penerimaan_detail', 'Integer', '11', 'Primary Key, Auto Increment. ID baris rincian barang masuk.'),
            ('id_penerimaan', 'Integer', '11', 'Foreign Key merujuk ke tabel penerimaan_stok.id_penerimaan.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('qty_pesan', 'Integer', '11', 'Jumlah kuantitas barang yang dipesan dalam dokumen PO.'),
            ('qty_terima', 'Integer', '11', 'Jumlah kuantitas fisik barang masuk yang lolos verifikasi.'),
            ('qty_rusak', 'Integer', '11', 'Jumlah kuantitas barang cacat/rusak fisik saat diterima.'),
            ('harga_beli', 'Decimal', '15,2', 'Rekaman harga pembelian netto per satuan dari distributor.'),
            ('no_batch', 'Varchar', '50', 'Nomor batch pabrik yang tertera pada kemasan barang.'),
            ('tgl_exp', 'Date', '-', 'Tanggal batas kedaluwarsa fisik barang yang diterima.'),
            ('status_retur', 'Varchar', '30', 'Status pengajuan retur barang rusak ke distributor.'),
            ('qty_pengganti_diterima', 'Integer', '11', 'Kuantitas barang pengganti yang telah diterima dari rekanan.'),
            ('no_batch_pengganti', 'Varchar', '50', 'Nomor batch baru barang pengganti hasil klaim retur.'),
            ('tgl_exp_pengganti', 'Date', '-', 'Tanggal kedaluwarsa barang pengganti hasil retur.'),
            ('tgl_selesai_retur', 'Datetime', '-', 'Waktu penyelesaian klaim penggantian barang retur.'),
            ('catatan_retur', 'Text', '-', 'Penjelasan teknis kerusakan barang dan proses klaim retur.')
        ]
    },
    {
        'sub_no': '3.5.11',
        'table_no': '3.11',
        'name': 'penjualan',
        'domain': 'Transaksi Penjualan',
        'pk': 'id_penjualan',
        'fk': 'id_user',
        'fungsi': 'Menampung data dokumen induk untuk seluruh transaksi penjualan omnichannel (kasir POS luring dan pesanan belanja e-commerce). Tabel ini mencatat status siklus pesanan, alamat pengiriman kurir ekspedisi, total tagihan, serta verifikasi pelunasan Payment Gateway.',
        'cols': [
            ('id_penjualan', 'Integer', '11', 'Primary Key, Auto Increment. ID unik dokumen penjualan.'),
            ('no_invoice', 'Varchar', '50', 'Nomor faktur penjualan resmi toko (ZNC-YYYYMMDD-XXXX).'),
            ('id_user', 'Integer', '11', 'Foreign Key ke users.id_user (pelanggan pembeli atau kasir).'),
            ('tipe_transaksi', 'Enum', '-', "Pemetaan saluran asal transaksi ('pos', 'ecommerce')."),
            ('metode_pengambilan', 'Enum', '-', "Jalur serah terima barang ('Kurir', 'Pick-up')."),
            ('status_pesanan', 'Enum', '-', 'Tahapan siklus pesanan (Menunggu Bayar, Diproses, Dikirim, Selesai, Batal).'),
            ('total_harga', 'Decimal', '12,2', 'Total nilai tagihan pembelanjaan barang.'),
            ('metode_bayar_pos', 'Varchar', '50', 'Metode pembayaran kasir luring (Tunai, Debit, QRIS).'),
            ('ongkir', 'Decimal', '10,2', 'Beban biaya logistik kurir ekspedisi hasil kalkulasi API.'),
            ('nama_penerima', 'Varchar', '100', 'Nama lengkap penerima pesanan paket pengiriman.'),
            ('telepon_penerima', 'Varchar', '20', 'Nomor kontak telepon penerima paket pengiriman.'),
            ('alamat_lengkap', 'Text', '-', 'Alamat lengkap destinasi pengantaran paket kurir.'),
            ('kota_tujuan', 'Varchar', '100', 'Nama kota tujuan destinasi pengiriman paket pesanan.'),
            ('kurir', 'Varchar', '50', 'Label perusahaan kurir ekspedisi mitra pengiriman.'),
            ('layanan', 'Varchar', '50', 'Klasifikasi paket layanan pengiriman yang dipilih pelanggan.'),
            ('snap_token', 'Varchar', '255', 'Token transaksi Payment Gateway Midtrans untuk pembayaran daring.'),
            ('payment_method_ecommerce', 'Varchar', '50', 'Kanal pembayaran daring hasil callback otomatis gateway.'),
            ('paid_at', 'Timestamp', '-', 'Bukti stempel waktu pelunasan pembayaran terverifikasi.'),
            ('kode_pickup', 'Varchar', '20', 'Kode verifikasi rahasia bagi pelanggan opsi Pick-up in Store.'),
            ('created_at', 'Timestamp', '-', 'Waktu pembentukan transaksi pesanan pertama kali.'),
            ('updated_at', 'Timestamp', '-', 'Waktu pembaruan siklus pesanan terakhir dibukukan.')
        ]
    },
    {
        'sub_no': '3.5.12',
        'table_no': '3.12',
        'name': 'detail_penjualan',
        'domain': 'Transaksi Penjualan',
        'pk': 'id_detail_penjualan',
        'fk': 'id_penjualan, id_variasi',
        'fungsi': 'Merekam rincian produk apa saja yang dibeli pada satu nota faktur penjualan. Entitas ini menyimpan kuantitas produk, ketetapan harga satuan saat transaksi disepakati, serta catatan identitas alokasi nomor batch atau nomor seri alat yang terpotong.',
        'cols': [
            ('id_detail_penjualan', 'Integer', '11', 'Primary Key, Auto Increment. ID baris rincian transaksi.'),
            ('id_penjualan', 'Integer', '11', 'Foreign Key merujuk ke dokumen induk penjualan.id_penjualan.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('qty', 'Integer', '11', 'Kuantitas produk yang dibeli pada transaksi penjualan.'),
            ('harga_satuan', 'Decimal', '12,2', 'Nilai harga satuan produk saat transaksi dieksekusi.'),
            ('catatan_logistik', 'Text', '-', 'Rekaman riwayat alokasi nomor batch atau nomor seri alkes.')
        ]
    },
    {
        'sub_no': '3.5.13',
        'table_no': '3.13',
        'name': 'pesanan_batch_alokasi',
        'domain': 'Penguncian Stok Daring (Baru)',
        'pk': 'id_alokasi_batch',
        'fk': 'id_penjualan, id_detail_penjualan, id_batch',
        'fungsi': 'Entitas baru yang bertugas mengunci stok fisik nomor batch obat spesifik untuk pesanan daring yang telah terverifikasi lunas. Penguncian sementara ini menjamin kuantitas di rak toko tidak terjual ganda di meja kasir luring sebelum pesanan dikemas dan diserahkan ke kurir ekspedisi.',
        'cols': [
            ('id_alokasi_batch', 'Integer', '11', 'Primary Key, Auto Increment. ID unik alokasi batch daring.'),
            ('id_penjualan', 'Integer', '11', 'Foreign Key merujuk ke dokumen induk penjualan.id_penjualan.'),
            ('id_detail_penjualan', 'Integer', '11', 'Foreign Key merujuk ke detail_penjualan.id_detail_penjualan.'),
            ('id_batch', 'Integer', '11', 'Foreign Key merujuk ke stok_batch.id_stok_batch yang dialokasikan.'),
            ('qty_satuan_kecil', 'Integer', '11', 'Jumlah kuantitas eceran yang dikunci untuk pesanan daring.'),
            ('created_at', 'Timestamp', '-', 'Waktu penguncian alokasi fisik dicatat oleh sistem.')
        ]
    },
    {
        'sub_no': '3.5.14',
        'table_no': '3.14',
        'name': 'kartu_stok',
        'domain': 'Buku Besar Mutasi',
        'pk': 'id_kartu_stok',
        'fk': 'id_variasi, dibuat_oleh',
        'fungsi': 'Buku besar persediaan yang bertugas merekam jejak audit (audit trail) secara permanen. Tabel ini merangkum pergerakan kuantitas masuk, keluar, dan penyesuaian fisik beserta saldo berjalan guna menghasilkan pertanggungjawaban persediaan yang transparan dan akuntabel.',
        'cols': [
            ('id_kartu_stok', 'Integer', '11', 'Primary Key, Auto Increment. ID unik rekaman mutasi stok.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('id_batch', 'Integer', '11', 'Foreign Key merujuk ke stok_batch.id_stok_batch yang bermutasi.'),
            ('satuan_tipe', 'Enum', '-', "Jenis satuan mutasi persediaan ('besar', 'kecil')."),
            ('jenis_mutasi', 'Enum', '-', "Arah mutasi fisik persediaan ('Masuk', 'Keluar', 'Penyesuaian')."),
            ('kanal', 'Enum', '-', "Sumber pemicu mutasi ('POS', 'E-Commerce', 'Manual', 'Penerimaan', 'PO')."),
            ('alasan_mutasi', 'Enum', '-', 'Klasifikasi alasan pergerakan barang (Penerimaan, Kasir, Opname, Rusak, dll.).'),
            ('no_ref_dokumen', 'Varchar', '100', 'Nomor dokumen referensi transaksi pendukung mutasi.'),
            ('qty', 'Integer', '11', 'Besaran volume pergerakan kuantitas fisik persediaan.'),
            ('sisa_stok', 'Integer', '11', 'Saldo persediaan akhir setelah aktivitas mutasi dibukukan.'),
            ('keterangan', 'Text', '-', 'Penjelasan tambahan atas kondisi pergerakan fisik barang.'),
            ('dibuat_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (staf pelaksana mutasi).'),
            ('tanggal', 'Timestamp', '-', 'Stempel waktu pencatatan mutasi ke buku besar sistem.')
        ]
    },
    {
        'sub_no': '3.5.15',
        'table_no': '3.15',
        'name': 'histori_konversi',
        'domain': 'Operasional Multi-Satuan (Baru)',
        'pk': 'id_histori_konversi',
        'fk': 'id_variasi, dibuat_oleh',
        'fungsi': 'Entitas baru yang merekam riwayat pembongkaran kemasan dus besar menjadi satuan eceran secara instan saat stok eceran di rak habis. Tabel ini mendokumentasikan nomor batch asal, nomor batch turunan baru yang terbentuk, kuantitas hasil pecahan, dan sisa dus.',
        'cols': [
            ('id_histori_konversi', 'Integer', '11', 'Primary Key, Auto Increment. ID riwayat pembongkaran dus.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('batch_asal', 'Varchar', '100', 'Nomor batch kemasan dus besar yang dibongkar.'),
            ('batch_hasil', 'Varchar', '100', 'Nomor batch turunan baru yang dihasilkan untuk eceran.'),
            ('qty_box_buka', 'Integer', '11', 'Jumlah kemasan dus besar yang dibongkar menjadi eceran.'),
            ('qty_pcs_hasil', 'Integer', '11', 'Jumlah total satuan eceran yang berhasil dihasilkan.'),
            ('sisa_dus_akhir', 'Integer', '11', 'Sisa saldo kemasan dus besar setelah proses pembongkaran.'),
            ('dibuat_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (kasir atau staf yang mengeksekusi).'),
            ('created_at', 'Timestamp', '-', 'Waktu pelaksanaan konversi kemasan dicatat oleh sistem.')
        ]
    },
    {
        'sub_no': '3.5.16',
        'table_no': '3.16',
        'name': 'log_anomali_fefo',
        'domain': 'Audit Kepatuhan Kasir (Baru)',
        'pk': 'id_log_anomali',
        'fk': 'id_penjualan, id_user, id_variasi',
        'fungsi': 'Entitas baru yang mendokumentasikan deviasi kasir saat mengambil nomor batch obat yang berbeda dari rekomendasi sistem FEFO. Catatan ini memuat nomor batch yang diambil, batch seharusnya, kuantitas, dan alasan operasional untuk keperluan audit kepatuhan manajerial.',
        'cols': [
            ('id_log_anomali', 'Integer', '11', 'Primary Key, Auto Increment. ID rekaman deviasi kasir.'),
            ('id_penjualan', 'Integer', '11', 'Foreign Key ke penjualan.id_penjualan tempat transaksi deviasi terjadi.'),
            ('id_user', 'Integer', '11', 'Foreign Key ke users.id_user (kasir yang memindai nomor batch).'),
            ('id_variasi', 'Integer', '11', 'Foreign Key ke produk_variasi.id_variasi obat yang mengalami deviasi.'),
            ('batch_diambil', 'Varchar', '100', 'Nomor batch fisik yang diambil secara nyata oleh kasir.'),
            ('batch_seharusnya', 'Varchar', '100', 'Nomor batch yang direkomendasikan sistem urutan FEFO.'),
            ('qty', 'Integer', '11', 'Jumlah kuantitas obat yang diambil di luar urutan sistem.'),
            ('satuan_tipe', 'Varchar', '20', "Jenis satuan transaksi saat deviasi terjadi ('kecil', 'besar')."),
            ('status', 'Enum', '-', "Status tindak lanjut anomali ('terlewat', 'sebagian', 'disesuaikan')."),
            ('qty_disesuaikan', 'Integer', '11', 'Jumlah kuantitas yang telah diselesaikan rekonsiliasinya.'),
            ('resolved_in_penjualan', 'Integer', '11', 'ID transaksi penjualan tempat anomali berhasil diselesaikan.'),
            ('resolved_at', 'Datetime', '-', 'Waktu penyelesaian dan verifikasi anomali logistik.'),
            ('created_at', 'Timestamp', '-', 'Waktu pencatatan kejadian deviasi pemilihan nomor batch.')
        ]
    },
    {
        'sub_no': '3.5.17',
        'table_no': '3.17',
        'name': 'sesi_opname',
        'domain': 'Audit Stok Opname (Baru)',
        'pk': 'id_sesi_opname',
        'fk': 'dibekukan_oleh, dibuka_oleh',
        'fungsi': 'Entitas baru yang mengelola jadwal dan siklus pelaksanaan audit fisik stok opname berkala. Tabel ini mencatat status pembekuan transaksi sementara (freeze lock) pada kasir POS dan e-commerce guna menjamin keabsahan angka cacahan fisik.',
        'cols': [
            ('id_sesi_opname', 'Integer', '11', 'Primary Key, Auto Increment. ID unik sesi pelaksanaan stok opname.'),
            ('kode_sesi', 'Varchar', '50', 'Kode dokumen resmi sesi audit opname (OPN-YYYYMMDD-XX).'),
            ('status', 'Enum', '-', "Tahapan siklus sesi audit ('Aktif', 'Selesai', 'Dibatalkan')."),
            ('dibekukan_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (Superadmin pembeku sistem operasional).'),
            ('dibekukan_pada', 'Timestamp', '-', 'Waktu dimulainya pembekuan transaksi operasional sementara.'),
            ('dibuka_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (Superadmin pembuka kunci operasional).'),
            ('dibuka_pada', 'Timestamp', '-', 'Waktu pembukaan kembali operasional kasir dan e-commerce.'),
            ('catatan', 'Text', '-', 'Catatan manajerial mengenai ruang lingkup pelaksanaan audit.')
        ]
    },
    {
        'sub_no': '3.5.18',
        'table_no': '3.18',
        'name': 'detail_opname_batch',
        'domain': 'Audit Stok Opname (Baru)',
        'pk': 'id_detail_opname',
        'fk': 'id_sesi, id_batch, id_variasi, dihitung_oleh, dimutasi_oleh',
        'fungsi': 'Entitas baru yang bertindak sebagai lembar kerja pencacahan fisik obat per nomor batch. Tabel ini menampung hasil hitung mandiri staf tanpa melihat angka buku sistem (blind count), mengkalkulasi selisih secara otomatis, dan mengeksekusi penyesuaian saldo.',
        'cols': [
            ('id_detail_opname', 'Integer', '11', 'Primary Key, Auto Increment. ID lembar kerja pencacahan per batch.'),
            ('id_sesi', 'Integer', '11', 'Foreign Key merujuk ke dokumen sesi_opname.id_sesi_opname.'),
            ('id_batch', 'Integer', '11', 'Foreign Key merujuk ke stok_batch.id_stok_batch yang dicacah fisiknya.'),
            ('id_variasi', 'Integer', '11', 'Foreign Key merujuk ke tabel produk_variasi.id_variasi.'),
            ('jenis_komoditas', 'Enum', '-', "Klasifikasi jenis komoditas ('Obat', 'Alkes')."),
            ('satuan_tipe', 'Enum', '-', "Tipe satuan pemeriksaan persediaan ('besar', 'kecil')."),
            ('nama_satuan', 'Varchar', '50', 'Label satuan fisik barang yang dihitung (Pcs, Box, Strip).'),
            ('stok_tersedia_sistem', 'Integer', '11', 'Saldo persediaan aktif bebas menurut catatan buku sistem.'),
            ('stok_reserved', 'Integer', '11', 'Kuantitas persediaan yang sedang terkunci pesanan daring.'),
            ('stok_sistem_fisik', 'Integer', '11', 'Total saldo buku fisik yang harus ditemukan di rak toko.'),
            ('hitung_fisik', 'Integer', '11', 'Angka kuantitas nyata hasil pencacahan staf di rak toko.'),
            ('selisih', 'Integer', '11', 'Selisih kuantitas antara hitung fisik dengan stok buku sistem.'),
            ('status_mutasi', 'Enum', '-', "Status eksekusi mutasi ('Belum Dimutasi', 'Sudah Dimutasi', 'Sesuai')."),
            ('alasan_mutasi', 'Varchar', '255', 'Alasan penetapan mutasi penyesuaian (Selisih Kurang/Lebih).'),
            ('keterangan_mutasi', 'Text', '-', 'Catatan penjelasan detail mengenai selisih fisik yang terjadi.'),
            ('id_kartu_stok', 'Integer', '11', 'Foreign Key ke kartu_stok.id_kartu_stok yang terbentuk dari penyesuaian.'),
            ('dihitung_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (staf pelaksana pencacahan fisik).'),
            ('waktu_hitung', 'Timestamp', '-', 'Waktu staf mengunggah angka hitungan fisik ke sistem.'),
            ('dimutasi_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (pejabat penyetuju mutasi saldo).'),
            ('waktu_mutasi', 'Timestamp', '-', 'Waktu pembukuan penyesuaian saldo persediaan dieksekusi.')
        ]
    },
    {
        'sub_no': '3.5.19',
        'table_no': '3.19',
        'name': 'detail_opname_sn',
        'domain': 'Audit Stok Opname (Baru)',
        'pk': 'id_opname_sn',
        'fk': 'id_detail_opname, id_unit_serial',
        'fungsi': 'Entitas baru yang mencatat verifikasi fisik individual per unit nomor seri alat kesehatan yang ditemukan saat pemeriksaan gudang, mengidentifikasi kondisi barang (normal, rusak, hilang, atau ditemukan baru).',
        'cols': [
            ('id_opname_sn', 'Integer', '11', 'Primary Key, Auto Increment. ID baris pemeriksaan serial alkes.'),
            ('id_detail_opname', 'Integer', '11', 'Foreign Key merujuk ke lembar detail_opname_batch.id_detail_opname.'),
            ('id_unit_serial', 'Integer', '11', 'Foreign Key merujuk ke rekaman aset unit_serial.id_unit_serial.'),
            ('serial_number', 'Varchar', '100', 'Nomor seri fisik alat kesehatan yang dipindai saat audit.'),
            ('jenis_tindakan', 'Enum', '-', "Kondisi fisik unit ('Kurang_Rusak', 'Kurang_Hilang', 'Tambah_Ditemukan')."),
            ('created_at', 'Timestamp', '-', 'Waktu pemindaian nomor seri alat kesehatan dicatat sistem.')
        ]
    },
    {
        'sub_no': '3.5.20',
        'table_no': '3.20',
        'name': 'riwayat_hitung_opname',
        'domain': 'Audit Stok Opname (Baru)',
        'pk': 'id_riwayat_hitung',
        'fk': 'id_detail_opname, dihitung_oleh',
        'fungsi': 'Entitas baru yang merekam riwayat koreksi atau perhitungan ulang fisik selama sesi audit aktif berlangsung beserta alasan perubahannya guna mencegah manipulasi angka persediaan dan menjaga integritas hasil audit.',
        'cols': [
            ('id_riwayat_hitung', 'Integer', '11', 'Primary Key, Auto Increment. ID rekaman riwayat koreksi cacahan.'),
            ('id_detail_opname', 'Integer', '11', 'Foreign Key merujuk ke lembar detail_opname_batch.id_detail_opname.'),
            ('hitung_sebelumnya', 'Integer', '11', 'Angka hitungan fisik awal sebelum dilakukan koreksi ulang.'),
            ('hitung_baru', 'Integer', '11', 'Angka hitungan fisik baru hasil pencacahan ulang staf.'),
            ('dihitung_oleh', 'Integer', '11', 'Foreign Key ke users.id_user (staf yang melakukan koreksi angka).'),
            ('alasan_koreksi', 'Text', '-', 'Penjelasan argumentatif perihal dilakukannya perbaikan hitungan.'),
            ('waktu_koreksi', 'Timestamp', '-', 'Waktu perubahan angka pencacahan dibukukan ke sistem.')
        ]
    }
]

print(f'Total tables defined: {len(tables_data_3_5)}')


def get_subbab_3_4_5_html():
    html_parts = []
    
    # Subbab 3.4 Logical Database
    html_parts.append("""        <!-- 3.4 Logical Database -->
        <div class="mb-8 relative group" id="sec_3_4_wrapper">
            <div class="no-print flex justify-end gap-2 mb-2">
                <a href="../lain/logical_database_zencare.html" target="_blank"
                    class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Visual Schema &amp; DBML Logical Database (20 Tabel)
                </a>
                <button onclick="copyText('sec_3_4', this)"
                    class="text-xs text-blue-600 hover:text-blue-800 border border-slate-200 bg-slate-50 px-2 py-1 rounded transition font-sans">
                    Salin Subbab 3.4
                </button>
            </div>
            <div id="sec_3_4">
                <h3 class="text-base font-bold mb-3 font-serif">3.4 Logical Database</h3>
                <p class="text-justify indent-8 mb-4">
                    <i>Logical Data Model</i> (LDM) pada Sistem Informasi Inventori Omnichannel ZenCare Medical dirancang menggunakan basis data relasional yang memetakan <b>20 entitas tabel</b> terintegrasi. Pusat relasi data bertumpu pada tabel <code>produk_variasi</code> yang menjadi poros logistik multi-satuan dan turunan langsung dari <code>produk_induk</code> (katalog informasi dasar obat dan alat kesehatan). Dari <code>produk_variasi</code>, aliran pencatatan fisik persediaan dipisahkan menjadi tiga entitas spesifik: <code>stok_toko</code> untuk akumulasi saldo aktif siap jual, <code>stok_batch</code> untuk sediaan obat berbasis nomor batch dan tanggal kedaluwarsa terdekat (<i>First Expired First Out</i> / FEFO), serta <code>unit_serial</code> untuk pelacakan nomor seri unik (<i>Serial Number</i>) dan masa berlaku garansi alat kesehatan secara individual.
                </p>
                <p class="text-justify indent-8 mb-4">
                    Pencatatan arus masuk dan keluar barang menerapkan skema tabel induk dan rincian. Pasokan masuk dari distributor diakomodasi oleh <code>penerimaan_stok</code> dan <code>penerimaan_detail</code> yang terhubung dinamis ke tabel <code>supplier</code> sehingga satu jenis produk dapat dipasok oleh banyak distributor rekanan. Sementara itu, arus barang keluar lintas saluran (kasir toko fisik dan pesanan belanja <i>e-commerce</i>) ditampung pada tabel <code>penjualan</code> dan <code>detail_penjualan</code>. Khusus pesanan daring, entitas <code>pesanan_batch_alokasi</code> bertugas mengunci nomor batch fisik obat sementara sebelum barang dikemas agar tidak terjual ganda di meja kasir. Seluruh transaksi ini diikat ke tabel <code>users</code> guna mengidentifikasi staf penanggung jawab berdasarkan perannya (Superadmin, Admin, Kasir, dan Pelanggan).
                </p>
                <p class="text-justify indent-8 mb-4">
                    Seluruh pergerakan barang bermuara pada <code>kartu_stok</code> sebagai buku besar mutasi yang mencatat riwayat pergerakan fisik dan saldo berjalan secara permanen. Pengendalian mutu operasional diperkuat oleh <code>histori_konversi</code> untuk rekam jejak pembongkaran kemasan dus ke eceran secara otomatis saat persediaan rak habis, serta <code>log_anomali_fefo</code> untuk mendokumentasikan deviasi pemilihan nomor batch di kasir demi kebutuhan audit kepatuhan. Pengendalian intern berkala didukung modul stok opname yang mencakup <code>sesi_opname</code> (pembekuan transaksi kasir sementara), <code>detail_opname_batch</code> (lembar hitung fisik obat dan kalkulasi selisih), <code>detail_opname_sn</code> (verifikasi alkes berseri), serta <code>riwayat_hitung_opname</code> (rekam jejak koreksi hitungan). Sebagai pelengkap, entitas mandiri <code>pengaturan_web</code> mengelola informasi portal publik tanpa mencampuri relasi logistik. Struktur relasional ke-20 entitas tabel disajikan pada Gambar 3.18.
                </p>

                <!-- Diagram Gambar 3.18 Logical Data Model -->
                <div class="diagram-container text-center mb-6">
                    <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                        <img src="../lain/Gambar_3.18_Logical_Database_Model.svg" alt="Logical Data Model ZenCare Medical" class="mx-auto max-w-full h-auto">
                    </div>
                    <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.18</b> Logical Data Model (LDM) Sistem Informasi Inventori Omnichannel ZenCare Medical (20 Entitas Tabel)</p>
                </div>
            </div>
        </div>
""")
    
    # Subbab 3.5 Kamus Data
    html_parts.append("""        <!-- 3.5 Kamus Data -->
        <div class="mb-8 relative group" id="sec_3_5_wrapper">
            <div class="no-print flex justify-end gap-2 mb-2">
                <a href="../lain/kamus_data_zencare.html" target="_blank"
                    class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Kamus Data Interaktif (20 Tabel)
                </a>
                <button onclick="copyText('sec_3_5', this)"
                    class="text-xs text-blue-600 hover:text-blue-800 border border-slate-200 bg-slate-50 px-2 py-1 rounded transition font-sans">
                    Salin Subbab 3.5
                </button>
            </div>
            <div id="sec_3_5">
                <h3 class="text-base font-bold mb-3 font-serif">3.5 Kamus Data</h3>
                <p class="text-justify indent-8 mb-4">
                    Kamus data disusun untuk mendokumentasikan secara rinci struktur fisik setiap entitas tabel, penetapan kunci utama (<i>Primary Key</i>), keterkaitan relasi kunci tamu (<i>Foreign Key</i>), serta spesifikasi teknis atribut kolom yang digunakan dalam basis data Sistem Informasi Inventori Omnichannel ZenCare Medical. Perancangan basis data terintegrasi ini mencakup <b>20 entitas tabel</b> yang merefleksikan seluruh kebutuhan alur operasional inventori medis, transaksi penjualan omnichannel, dan modul audit persediaan secara presisi sebagai berikut:
                </p>
""")
    
    for t in tables_data_3_5:
        sub_title = f"{t['sub_no']} Tabel {t['name']}"
        tbl_title = f"Tabel {t['table_no']} Tabel {t['name']}"
        
        html_parts.append(f"""                <!-- {sub_title} -->
                <div class="mb-8" id="sec_{t['name']}">
                    <h4 class="text-sm font-bold font-serif mb-2">{sub_title}</h4>
                    <div class="text-xs mb-3 space-y-1 font-serif leading-relaxed">
                        <p><b>Primary Key:</b> {t['pk']}</p>
                        <p><b>Foreign Key:</b> {t['fk']}</p>
                        <p class="text-justify"><b>Fungsi:</b> {t['fungsi']}</p>
                    </div>
                    <p class="text-xs font-bold font-serif mb-1">{tbl_title}</p>
                    <table class="w-full text-xs border-collapse border border-black mb-6 font-serif">
                        <thead>
                            <tr class="bg-slate-100 text-black">
                                <th class="border border-black px-2 py-1.5 text-center font-bold" style="width: 6%;">No</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 25%;">Nama Field</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 17%;">Tipe Data</th>
                                <th class="border border-black px-2 py-1.5 text-center font-bold" style="width: 12%;">Panjang</th>
                                <th class="border border-black px-2 py-1.5 text-left font-bold" style="width: 40%;">Keterangan / Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
""")
        for idx, (col_name, col_type, col_len, col_desc) in enumerate(t['cols'], 1):
            is_pk = (col_name == t['pk'])
            is_fk = (col_name in t['fk'])
            badge = ' <span class="text-red-700 font-bold font-sans text-[10px]">(PK)</span>' if is_pk else (' <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span>' if is_fk else '')
            
            html_parts.append(f"""                            <tr>
                                <td class="border border-black px-2 py-1 text-center font-bold">{idx}</td>
                                <td class="border border-black px-2 py-1 font-mono font-semibold">{col_name}{badge}</td>
                                <td class="border border-black px-2 py-1">{col_type}</td>
                                <td class="border border-black px-2 py-1 text-center">{col_len}</td>
                                <td class="border border-black px-2 py-1">{col_desc}</td>
                            </tr>
""")
        html_parts.append("""                        </tbody>
                    </table>
                </div>
""")
    
    html_parts.append("""            </div>
        </div>
""")
    return ''.join(html_parts)

if __name__ == '__main__':
    with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
        html = f.read()

    # Find insertion point after Gambar 3.17
    marker = '<p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.17</b> Activity Diagram Kelola Stok Opname &amp; Audit Fisik (Kunci Kasir &amp; Berita Acara)</p>\n                        </div>\n                    </div>\n                </div>\n            </div>'
    idx_sub_3_3_end = html.find(marker)
    if idx_sub_3_3_end == -1:
        idx_gbr = html.find('Gambar 3.17</b> Activity Diagram Kelola Stok Opname')
        close_divs = '</div>\n                    </div>\n                </div>\n            </div>'
        idx_close = html.find(close_divs, idx_gbr)
        idx_sub_3_3_end = idx_close + len(close_divs)
    else:
        idx_sub_3_3_end += len(marker)

    idx_bottom = html.find('<!-- Bottom Notification (No Print) -->')
    
    content = get_subbab_3_4_5_html()
    
    # Wrap properly before closing paper
    final_paper_html = '\n\n' + content + '\n    </div> <!-- Closes full_bab3_content paper -->\n</div> <!-- Closes outer container -->\n\n'
    
    new_html = html[:idx_sub_3_3_end] + final_paper_html + html[idx_bottom:]
    with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(new_html)
    print("Successfully built clean paper Subbab 3.4 & 3.5 in BAB_III_ZENCARE_MEDICAL.html!")
