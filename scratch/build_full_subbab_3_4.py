import re

# Comprehensive dictionary for all 20 tables matching db_inventory (demo) 3.sql
tables_dict = [
    {
        'table_no': '3.7',
        'name': 'users',
        'domain': 'Master Pengguna & Otoritas Sistem',
        'pk': 'id',
        'fk': '-',
        'fungsi': 'Menyimpan data identitas akun pengguna, kata sandi terenkripsi (bcrypt), profil kontak, alamat pengiriman pelanggan, ID kota RajaOngkir, serta pembagian peran dan hak akses operasional (Superadmin, Admin, Kasir, dan Pelanggan).',
        'cols': [
            ('id', 'INT', 'Kunci primer (auto increment) pengenal unik pengguna.'),
            ('username', 'VARCHAR(50)', 'Nama pengguna unik untuk proses autentikasi masuk.'),
            ('password', 'VARCHAR(255)', 'Kata sandi akun yang diamankan dengan algoritma hash bcrypt.'),
            ('nama_lengkap', 'VARCHAR(100)', 'Nama lengkap resmi pemilik akun pengguna.'),
            ('role', 'ENUM', "Peran hak akses: 'superadmin', 'admin', 'kasir', 'pelanggan'."),
            ('email', 'VARCHAR(150)', 'Alamat surat elektronik resmi pengguna untuk korespondensi.'),
            ('telepon', 'VARCHAR(20)', 'Nomor kontak telepon atau WhatsApp aktif pengguna.'),
            ('alamat', 'TEXT', 'Alamat domisili utama pelanggan untuk pengiriman pesanan daring.'),
            ('kota_id', 'INT', 'Kode identitas kota tujuan RajaOngkir untuk kalkulasi ongkos kirim.'),
            ('is_active', 'BOOLEAN', 'Status keaktifan akun (1 = aktif, 0 = dinonaktifkan).'),
            ('created_at', 'TIMESTAMP', 'Waktu pencatatan akun pertama kali didaftarkan ke sistem.')
        ]
    },
    {
        'table_no': '3.8',
        'name': 'pengaturan_web',
        'domain': 'Pengaturan Portal Publik',
        'pk': 'id',
        'fk': '-',
        'fungsi': 'Entitas independen yang menyimpan konfigurasi umum antarmuka portal belanja publik e-commerce, mencakup profil identitas toko, logo merek, grafis spanduk promosi, serta kanal kontak bantuan pelanggan.',
        'cols': [
            ('id', 'INT', 'Kunci primer pengenal konfigurasi pengaturan antarmuka web.'),
            ('nama_toko', 'VARCHAR(100)', "Nama resmi toko ritel (nilai bawaan: 'ZenCare Medical')."),
            ('deskripsi', 'TEXT', 'Uraian ringkas profil dan layanan kesehatan toko.'),
            ('telepon', 'VARCHAR(20)', 'Nomor saluran telepon kantor atau toko fisik.'),
            ('whatsapp', 'VARCHAR(20)', 'Nomor resmi layanan pesan instan WhatsApp toko.'),
            ('alamat', 'TEXT', 'Alamat fisik lokasi toko ritel luring.'),
            ('logo', 'VARCHAR(255)', 'Jalur tautan berkas citra logo identitas merek.'),
            ('banner_promosi', 'VARCHAR(255)', 'Jalur tautan berkas citra spanduk promosi beranda web.'),
            ('email_cs', 'VARCHAR(150)', 'Alamat surat elektronik layanan pelanggan (customer service).')
        ]
    },
    {
        'table_no': '3.9',
        'name': 'supplier',
        'domain': 'Master Pemasok Rekanan',
        'pk': 'id',
        'fk': '-',
        'fungsi': 'Menyimpan direktori data profil distributor atau pemasok resmi rekanan yang menyuplai komoditas sediaan obat dan alat kesehatan ke toko fisik dan gudang.',
        'cols': [
            ('id', 'INT', 'Kunci primer identitas unik pemasok barang medis.'),
            ('nama', 'VARCHAR(100)', 'Nama badan usaha atau entitas distributor rekanan pemasok.'),
            ('kontak', 'VARCHAR(50)', 'Nomor telepon penanggung jawab bagian pemasaran atau penjualan.'),
            ('email', 'VARCHAR(150)', 'Alamat surat elektronik resmi distributor pemasok.'),
            ('alamat', 'TEXT', 'Alamat kantor atau gudang pengiriman distributor.'),
            ('is_active', 'BOOLEAN', 'Status kerja sama aktif distributor pemasok.')
        ]
    },
    {
        'table_no': '3.10',
        'name': 'produk_induk',
        'domain': 'Master Katalog Produk',
        'pk': 'id',
        'fk': '-',
        'fungsi': 'Menyimpan profil katalog dasar produk medis yang memuat nama dagang, spesifikasi teknis medis, indikasi penggunaan, kategori komoditas (Obat atau Alat Kesehatan), serta batas peringatan kedaluwarsa.',
        'cols': [
            ('id', 'INT', 'Kunci primer unik produk induk katalog dasar.'),
            ('sku_induk', 'VARCHAR(50)', 'Kode pengenal unik produk induk dari manufaktur.'),
            ('nama_produk', 'VARCHAR(150)', 'Nama dagang komoditas medis resmi.'),
            ('deskripsi', 'TEXT', 'Uraian lengkap indikasi, manfaat, dan deskripsi produk.'),
            ('spesifikasi', 'TEXT', 'Spesifikasi teknis, nomor izin edar Kemenkes atau BPOM.'),
            ('info_pengiriman', 'TEXT', 'Petunjuk penanganan khusus saat proses pengiriman barang.'),
            ('kategori', 'ENUM', "Klasifikasi komoditas medis: 'Obat' atau 'Alat Kesehatan'."),
            ('gambar', 'VARCHAR(255)', 'Jalur berkas foto utama produk pada katalog digital.'),
            ('is_active', 'BOOLEAN', 'Status ketersediaan katalog untuk dipublikasikan.'),
            ('batas_hari_expired', 'INT', 'Ambang batas hari peringatan sebelum tanggal kedaluwarsa tiba.'),
            ('created_at', 'TIMESTAMP', 'Waktu pertama kali katalog produk dicatat ke sistem.')
        ]
    },
    {
        'table_no': '3.11',
        'name': 'produk_variasi',
        'domain': 'Master Logistik & Kemasan (Entitas Poros)',
        'pk': 'id',
        'fk': 'id_produk_induk (produk_induk.id)',
        'fungsi': 'Poros utama data logistik yang mengatur variasi kemasan bertingkat (satuan kecil eceran dan satuan besar dus), rasio konversi unit, penetapan harga jual per satuan, batas stok minimum, dan bobot produk.',
        'cols': [
            ('id', 'INT', 'Kunci primer unik variasi kemasan produk fisik.'),
            ('id_produk_induk', 'INT', 'Kunci tamu merujuk ke tabel katalog dasar produk_induk.id.'),
            ('sku_variasi', 'VARCHAR(50)', 'Kode unik fisik per variasi kemasan (SKU variasi).'),
            ('nama_variasi', 'VARCHAR(100)', 'Nama pembeda variasi (misalnya: Strip, Box 10 Strip).'),
            ('satuan_kecil', 'VARCHAR(50)', 'Satuan terkecil untuk penjualan eceran (Strip, Tablet, Pcs).'),
            ('satuan_besar', 'VARCHAR(50)', 'Satuan kemasan besar dari pabrik (Dus, Box, Karton).'),
            ('rasio_konversi', 'SMALLINT', 'Faktor pengali kuantitas eceran di dalam satu kemasan besar.'),
            ('harga_jual_kecil', 'DECIMAL(12,2)', 'Nilai nominal harga jual eceran per satuan kecil.'),
            ('harga_jual_besar', 'DECIMAL(12,2)', 'Nilai nominal harga jual grosir per satuan besar.'),
            ('stok_minimum_kecil', 'INT', 'Batas ambang peringatan persediaan menipis untuk satuan kecil.'),
            ('stok_minimum_besar', 'INT', 'Batas ambang persediaan minimum kemasan besar di gudang.'),
            ('berat', 'INT', 'Bobot fisik produk dalam gram untuk kalkulasi ongkos kurir.'),
            ('tampil_di_online', 'BOOLEAN', 'Penanda visibilitas produk di katalog e-commerce publik.'),
            ('is_active', 'BOOLEAN', 'Status operasional variasi produk.'),
            ('gambar', 'VARCHAR(500)', 'Jalur tautan berkas foto fisik kemasan variasi.')
        ]
    },
    {
        'table_no': '3.12',
        'name': 'stok_toko',
        'domain': 'Persediaan Fisik Toko',
        'pk': 'id',
        'fk': 'id_variasi (produk_variasi.id)',
        'fungsi': 'Menyimpan saldo total persediaan fisik siap jual yang terakumulasi di toko dan gudang per variasi produk.',
        'cols': [
            ('id', 'INT', 'Kunci primer pencatatan saldo persediaan toko.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke tabel produk_variasi.id.'),
            ('stok', 'INT', 'Total kuantitas gabungan stok fisik aktif yang siap ditransaksikan.')
        ]
    },
    {
        'table_no': '3.13',
        'name': 'stok_batch',
        'domain': 'Logistik Medis FEFO',
        'pk': 'id',
        'fk': 'id_variasi (produk_variasi.id)',
        'fungsi': 'Mengelola kuantitas fisik sediaan obat berdasarkan nomor batch produksi dan tanggal kedaluwarsa guna mengeksekusi logika pengeluaran obat terdekat (First Expired First Out / FEFO).',
        'cols': [
            ('id', 'INT', 'Kunci primer identitas pencatatan nomor batch sediaan obat.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke tabel produk_variasi.id.'),
            ('no_batch', 'VARCHAR(100)', 'Nomor kode batch produksi pabrikan pada kemasan obat.'),
            ('tgl_exp', 'DATE', 'Tanggal masa kedaluwarsa obat untuk urutan prioritas FEFO.'),
            ('stok_sisa', 'INT', 'Sisa kuantitas fisik obat yang masih tersedia pada batch tersebut.'),
            ('created_at', 'TIMESTAMP', 'Waktu nomor batch obat pertama kali dicatat masuk ke sistem.')
        ]
    },
    {
        'table_no': '3.14',
        'name': 'unit_serial',
        'domain': 'Logistik Alat Kesehatan',
        'pk': 'id',
        'fk': 'id_variasi (produk_variasi.id), id_penjualan (penjualan.id)',
        'fungsi': 'Menyimpan nomor seri unik (Serial Number) alat kesehatan secara individual, status ketersediaan fisik, serta riwayat penjaminan garansi resmi.',
        'cols': [
            ('id', 'INT', 'Kunci primer penomoran unik unit individual alat kesehatan.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke tabel produk_variasi.id.'),
            ('serial_number', 'VARCHAR(100)', 'Nomor seri pabrikan yang tertera unik pada fisik alat.'),
            ('status', 'VARCHAR(50)', "Status fisik unit: 'Tersedia', 'Terjual', 'Retur', 'Rusak'."),
            ('id_penjualan', 'INT', 'Kunci tamu relasi ke transaksi penjualan saat unit telah terjual.'),
            ('catatan', 'VARCHAR(255)', 'Keterangan tambahan kondisi fisik atau kelengkapan instrumen.'),
            ('tgl_habis_garansi', 'DATE', 'Batas akhir masa berlaku garansi resmi dari distributor.'),
            ('created_at', 'TIMESTAMP', 'Waktu unit alat kesehatan didaftarkan masuk ke sistem.'),
            ('updated_at', 'TIMESTAMP', 'Waktu pembaruan status unit fisik terakhir.')
        ]
    },
    {
        'table_no': '3.15',
        'name': 'penerimaan_stok',
        'domain': 'Transaksi Pasokan Masuk (Induk)',
        'pk': 'id',
        'fk': 'id_supplier (supplier.id), dibuat_oleh (users.id)',
        'fungsi': 'Kepala dokumen pengadaan barang masuk dari distributor rekanan yang memuat nomor referensi faktur, asal sumber pasokan, tanggal terima fisik, bukti pindai nota, dan identitas staf penerima.',
        'cols': [
            ('id', 'INT', 'Kunci primer dokumen penerimaan pasokan barang.'),
            ('no_referensi', 'VARCHAR(50)', 'Nomor surat jalan atau faktur resmi dari pihak distributor.'),
            ('sumber', 'VARCHAR(50)', "Asal sumber penerimaan: 'Supplier', 'Retur Pelanggan', dll."),
            ('id_supplier', 'INT', 'Kunci tamu relasi ke mitra distributor pada tabel supplier.id.'),
            ('tanggal_terima', 'DATE', 'Tanggal fisik barang dibongkar dan diterima di area gudang.'),
            ('catatan', 'TEXT', 'Catatan penjelasan umum hasil verifikasi fisik barang.'),
            ('file_nota', 'VARCHAR(255)', 'Jalur tautan berkas citra bukti pindai faktur atau surat jalan.'),
            ('dibuat_oleh', 'INT', 'Kunci tamu staf penerima barang pada tabel users.id.'),
            ('created_at', 'TIMESTAMP', 'Waktu pencatatan dokumen penerimaan barang ke sistem.')
        ]
    },
    {
        'table_no': '3.16',
        'name': 'penerimaan_detail',
        'domain': 'Transaksi Pasokan Masuk (Detail)',
        'pk': 'id',
        'fk': 'id_penerimaan (penerimaan_stok.id), id_variasi (produk_variasi.id)',
        'fungsi': 'Rincian item komoditas yang diterima per dokumen pengadaan, mencakup kuantitas pesan, kuantitas terima, kuantitas cacat/rusak, harga beli per satuan, nomor batch, tanggal kedaluwarsa, dan status retur.',
        'cols': [
            ('id', 'INT', 'Kunci primer baris rincian item barang masuk.'),
            ('id_penerimaan', 'INT', 'Kunci tamu merujuk ke dokumen induk penerimaan_stok.id.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke tabel produk_variasi.id.'),
            ('qty_pesan', 'INT', 'Jumlah kuantitas barang yang dipesan pada surat pesanan.'),
            ('qty_terima', 'INT', 'Jumlah kuantitas barang fisik yang lolos uji kelayakan terima.'),
            ('qty_rusak', 'INT', 'Jumlah kuantitas barang yang ditemukan rusak saat penerimaan.'),
            ('harga_beli', 'DECIMAL(15,2)', 'Nilai nominal harga beli neto per satuan barang dari pemasok.'),
            ('no_batch', 'VARCHAR(50)', 'Nomor batch fisik obat yang tertera pada kemasan kiriman.'),
            ('tgl_exp', 'DATE', 'Tanggal kedaluwarsa dari batch obat yang diterima.'),
            ('status_retur', 'VARCHAR(50)', "Status klaim retur barang cacat: 'Tidak Ada', 'Menunggu', dll."),
            ('catatan_retur', 'TEXT', 'Penjelasan perihal kerusakan untuk dasar penukaran ke pemasok.')
        ]
    },
    {
        'table_no': '3.17',
        'name': 'penjualan',
        'domain': 'Transaksi Penjualan Omnichannel (Induk)',
        'pk': 'id',
        'fk': 'id_user (users.id)',
        'fungsi': 'Kepala transaksi penjualan barang terpadu lintas saluran (kasir POS toko fisik dan pesanan e-commerce), mencakup nomor faktur, opsi serah terima, status pemrosesan, total nilai tagihan, dan metode bayar.',
        'cols': [
            ('id', 'INT', 'Kunci primer unik transaksi penjualan omnichannel.'),
            ('no_invoice', 'VARCHAR(50)', 'Nomor faktur penjualan resmi toko (ZNC-YYYYMMDD-XXXX).'),
            ('id_user', 'INT', 'Kunci tamu pembeli atau kasir penanggung jawab pada users.id.'),
            ('tipe_transaksi', 'VARCHAR(50)', "Kanal transaksi: 'POS' (toko fisik) atau 'Online' (e-commerce)."),
            ('metode_pengambilan', 'VARCHAR(50)', "Opsi serah terima barang: 'Ambil di Toko' atau 'Kurir'."),
            ('status_pesanan', 'VARCHAR(50)', "Siklus status: 'Menunggu Pembayaran', 'Diproses', 'Selesai', dll."),
            ('total_harga', 'DECIMAL(12,2)', 'Total akumulasi nilai tagihan belanja transaksi.'),
            ('metode_bayar_pos', 'VARCHAR(50)', 'Cara pembayaran di kasir toko fisik (Tunai, QRIS, Debit).'),
            ('ongkir', 'DECIMAL(10,2)', 'Biaya jasa pengiriman ekspedisi untuk transaksi belanja daring.'),
            ('nama_penerima', 'VARCHAR(100)', 'Nama lengkap penerima pesanan pada pengiriman kurir.'),
            ('telepon_penerima', 'VARCHAR(20)', 'Nomor telepon tujuan penerima paket pesanan.'),
            ('alamat_lengkap', 'TEXT', 'Alamat lengkap tujuan pengantaran paket ekspedisi.'),
            ('kota_tujuan', 'VARCHAR(100)', 'Nama kota/kabupaten tujuan pengiriman barang.'),
            ('kurir', 'VARCHAR(50)', 'Nama penyedia jasa kurir logistik (JNE, POS, TIKI).'),
            ('snap_token', 'VARCHAR(255)', 'Kunci token transaksi pembayaran gerbang Midtrans.'),
            ('paid_at', 'TIMESTAMP', 'Waktu verifikasi pembayaran lunas diterima oleh sistem.'),
            ('kode_pickup', 'VARCHAR(20)', 'Kode sandi unik untuk verifikasi pengambilan barang di toko fisik.'),
            ('created_at', 'TIMESTAMP', 'Waktu pertama kali pesanan penjualan diterbitkan.')
        ]
    },
    {
        'table_no': '3.18',
        'name': 'detail_penjualan',
        'domain': 'Transaksi Penjualan Omnichannel (Detail)',
        'pk': 'id',
        'fk': 'id_penjualan (penjualan.id), id_variasi (produk_variasi.id)',
        'fungsi': 'Baris rincian item komoditas yang terjual per transaksi penjualan, memuat kuantitas barang, harga satuan berlaku, dan catatan identitas fisik nomor batch atau nomor seri yang diserahkan.',
        'cols': [
            ('id', 'INT', 'Kunci primer unik baris rincian item penjualan.'),
            ('id_penjualan', 'INT', 'Kunci tamu merujuk ke dokumen induk penjualan.id.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke produk variasi pada produk_variasi.id.'),
            ('qty', 'INT', 'Banyaknya kuantitas barang yang ditransaksikan.'),
            ('harga_satuan', 'DECIMAL(12,2)', 'Nilai nominal harga jual satuan yang berlaku saat transaksi.'),
            ('catatan_logistik', 'TEXT', 'Catatan identitas fisik nomor batch atau nomor seri yang dibawa.')
        ]
    },
    {
        'table_no': '3.19',
        'name': 'pesanan_batch_alokasi',
        'domain': 'Penguncian Stok Transaksi Daring (Reserved)',
        'pk': 'id',
        'fk': 'id_penjualan (penjualan.id), id_detail_penjualan (detail_penjualan.id), id_batch (stok_batch.id)',
        'fungsi': 'Mengunci alokasi nomor batch fisik sediaan obat sementara waktu untuk pesanan e-commerce yang telah lunas agar kuantitas barang di rak tidak terjual ganda di meja kasir fisik sebelum pesanan dikemas.',
        'cols': [
            ('id', 'INT', 'Kunci primer pencatatan kuncian alokasi nomor batch.'),
            ('id_penjualan', 'INT', 'Kunci tamu merujuk ke transaksi penjualan.id.'),
            ('id_detail_penjualan', 'INT', 'Kunci tamu merujuk ke rincian item detail_penjualan.id.'),
            ('id_batch', 'INT', 'Kunci tamu nomor batch fisik obat yang dikunci (stok_batch.id).'),
            ('qty_satuan_kecil', 'INT', 'Kuantitas obat dalam satuan eceran yang dicadangkan.'),
            ('created_at', 'TIMESTAMP', 'Waktu kuncian stok dialokasikan saat pembayaran lunas terkonfirmasi.')
        ]
    },
    {
        'table_no': '3.20',
        'name': 'kartu_stok',
        'domain': 'Buku Besar Pengawasan Mutasi',
        'pk': 'id',
        'fk': 'id_variasi (produk_variasi.id), id_batch (stok_batch.id), dibuat_oleh (users.id)',
        'fungsi': 'Buku besar pergerakan persediaan yang merekam setiap peristiwa mutasi barang masuk, barang keluar, penyesuaian fisik, dan konversi satuan secara kronologis dan tidak dapat diubah.',
        'cols': [
            ('id', 'INT', 'Kunci primer catatan buku besar mutasi persediaan.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke produk_variasi.id.'),
            ('id_batch', 'INT', 'Kunci tamu nomor batch fisik obat terkait mutasi (opsional).'),
            ('satuan_tipe', 'VARCHAR(50)', "Satuan fisik yang bergerak: 'Kecil' atau 'Besar'."),
            ('jenis_mutasi', 'VARCHAR(50)', "Arah pergerakan fisik barang: 'Masuk', 'Keluar', 'Penyesuaian'."),
            ('kanal', 'VARCHAR(50)', "Saluran pemicu mutasi: 'POS', 'Online', 'Gudang', 'Sistem'."),
            ('alasan_mutasi', 'VARCHAR(50)', 'Latar belakang mutasi (Penjualan, Penerimaan, Retur, Opname, dll).'),
            ('no_ref_dokumen', 'VARCHAR(100)', 'Nomor dokumen rujukan (nomor faktur jual / faktur pengadaan).'),
            ('qty', 'INT', 'Besaran kuantitas mutasi penambahan atau pengurangan saldo.'),
            ('sisa_stok', 'INT', 'Saldo akhir persediaan barang setelah transaksi mutasi dieksekusi.'),
            ('keterangan', 'TEXT', 'Uraian keterangan tambahan perihal mutasi persediaan.'),
            ('dibuat_oleh', 'INT', 'Kunci tamu pengguna penanggung jawab mutasi (users.id).'),
            ('tanggal', 'TIMESTAMP', 'Waktu mutasi persediaan dibukukan secara permanen.')
        ]
    },
    {
        'table_no': '3.21',
        'name': 'histori_konversi',
        'domain': 'Operasional Multi-Satuan Kemasan',
        'pk': 'id',
        'fk': 'id_variasi (produk_variasi.id), dibuat_oleh (users.id)',
        'fungsi': 'Merekam riwayat pembongkaran kemasan dus besar ke kemasan eceran secara otomatis saat stok eceran di rak habis, memastikan sisa dus dan pembentukan nomor batch turunan tercatat akurat.',
        'cols': [
            ('id', 'INT', 'Kunci primer unik riwayat pemecahan kemasan satuan.'),
            ('id_variasi', 'INT', 'Kunci tamu merujuk ke produk_variasi.id.'),
            ('batch_asal', 'VARCHAR(100)', 'Nomor batch kemasan dus besar yang dibongkar.'),
            ('batch_hasil', 'VARCHAR(100)', 'Nomor batch turunan yang dilekatkan pada satuan eceran.'),
            ('qty_box_buka', 'INT', 'Kuantitas kemasan dus besar yang dibuka fisiknya.'),
            ('qty_pcs_hasil', 'INT', 'Total kuantitas satuan eceran baru yang dihasilkan.'),
            ('sisa_dus_akhir', 'INT', 'Sisa saldo kemasan dus besar di rak setelah pembongkaran.'),
            ('dibuat_oleh', 'INT', 'Kunci tamu staf atau kasir yang memicu konversi kemasan (users.id).'),
            ('created_at', 'TIMESTAMP', 'Waktu pelaksanaan pembongkaran kemasan fisik berlangsung.')
        ]
    },
    {
        'table_no': '3.22',
        'name': 'log_anomali_fefo',
        'domain': 'Audit Kepatuhan Kasir FEFO',
        'pk': 'id',
        'fk': 'id_penjualan (penjualan.id), id_user (users.id), id_variasi (produk_variasi.id)',
        'fungsi': 'Mendokumentasikan kejadian saat kasir memindai nomor batch obat yang berbeda dari rekomendasi urutan FEFO sistem beserta alasan operasionalnya untuk keperluan penelaahan audit kepatuhan.',
        'cols': [
            ('id', 'INT', 'Kunci primer catatan deviasi kepatuhan FEFO.'),
            ('id_penjualan', 'INT', 'Kunci tamu merujuk ke transaksi penjualan kasir (penjualan.id).'),
            ('id_user', 'INT', 'Kunci tamu kasir yang melakukan pemindaian fisik (users.id).'),
            ('id_variasi', 'INT', 'Kunci tamu produk obat terkait pada produk_variasi.id.'),
            ('batch_diambil', 'VARCHAR(100)', 'Nomor batch fisik obat yang dipindai nyata oleh kasir.'),
            ('batch_seharusnya', 'VARCHAR(100)', 'Nomor batch obat tertua yang direkomendasikan sistem.'),
            ('qty', 'INT', 'Kuantitas obat yang diambil dari batch non-rekomendasi.'),
            ('satuan_tipe', 'VARCHAR(50)', 'Satuan penjualan obat yang mengalami deviasi pengambilan.'),
            ('status', 'VARCHAR(50)', "Status audit penelaahan: 'Pending', 'Disetujui', 'Disesuaikan'."),
            ('qty_disesuaikan', 'INT', 'Kuantitas penyesuaian yang telah disinkronkan ke kartu stok.'),
            ('created_at', 'TIMESTAMP', 'Waktu insiden deviasi rekomendasi batch FEFO terdeteksi.')
        ]
    },
    {
        'table_no': '3.23',
        'name': 'sesi_opname',
        'domain': 'Audit Stok Opname (Induk Sesi)',
        'pk': 'id',
        'fk': 'dibekukan_oleh (users.id), dibuka_oleh (users.id)',
        'fungsi': 'Mengelola jadwal pelaksanaan audit fisik stok opname berkala, mencatat waktu penguncian operasional sementara (Freeze Lock) pada kasir dan e-commerce, serta otorisasi pembukaan kunci kembali.',
        'cols': [
            ('id', 'INT', 'Kunci primer identitas sesi pelaksanaan stok opname.'),
            ('kode_sesi', 'VARCHAR(50)', 'Kode dokumen unik sesi audit (OPN-YYYYMMDD-XX).'),
            ('status', 'VARCHAR(50)', "Tahapan sesi: 'Dibekukan', 'Pencacahan', 'Rekonsiliasi', 'Selesai'."),
            ('dibekukan_oleh', 'INT', 'Kunci tamu Superadmin yang mengaktifkan penguncian (users.id).'),
            ('dibekukan_pada', 'TIMESTAMP', 'Waktu penguncian operasional sementara mulai diberlakukan.'),
            ('dibuka_oleh', 'INT', 'Kunci tamu Superadmin yang mengesahkan pembukaan kembali kunci.'),
            ('dibuka_pada', 'TIMESTAMP', 'Waktu status pembekuan operasional kasir dicabut kembali.'),
            ('catatan', 'TEXT', 'Catatan lingkup area rak atau komoditas yang diaudit.')
        ]
    },
    {
        'table_no': '3.24',
        'name': 'detail_opname_batch',
        'domain': 'Audit Stok Opname (Lembar Kerja Batch)',
        'pk': 'id',
        'fk': 'id_sesi (sesi_opname.id), id_batch (stok_batch.id), id_variasi (produk_variasi.id), dihitung_oleh (users.id), dimutasi_oleh (users.id)',
        'fungsi': 'Lembar kerja pencacahan fisik obat per nomor batch, menampung hasil hitung fisik mandiri staf, mengkalkulasi selisih secara otomatis terhadap saldo buku sistem, dan mengeksekusi jurnal penyesuaian.',
        'cols': [
            ('id', 'INT', 'Kunci primer baris pencacahan lembar kerja fisik audit.'),
            ('id_sesi', 'INT', 'Kunci tamu merujuk ke sesi audit pada sesi_opname.id.'),
            ('id_batch', 'INT', 'Kunci tamu nomor batch obat yang dicacah (stok_batch.id).'),
            ('id_variasi', 'INT', 'Kunci tamu variasi produk pada produk_variasi.id.'),
            ('jenis_komoditas', 'VARCHAR(50)', "Klasifikasi komoditas: 'Obat' atau 'Alat Kesehatan'."),
            ('satuan_tipe', 'VARCHAR(50)', 'Tipe kemasan fisik yang digunakan saat proses penghitungan.'),
            ('nama_satuan', 'VARCHAR(50)', 'Nama satuan fisik barang yang dicacah (Strip, Box, Botol).'),
            ('stok_tersedia_sistem', 'INT', 'Saldo persediaan aktif bebas menurut catatan buku sistem.'),
            ('stok_reserved', 'INT', 'Saldo pesanan daring yang sedang terkunci di keranjang kirim.'),
            ('stok_sistem_fisik', 'INT', 'Total kuantitas barang yang wajib tersedia di rak fisik gudang.'),
            ('hitung_fisik', 'INT', 'Hasil cacahan fisik nyata yang diinput mandiri oleh petugas.'),
            ('selisih', 'INT', 'Perbedaan matematis kuantitas fisik riil terhadap saldo sistem.'),
            ('status_mutasi', 'VARCHAR(50)', "Tindak lanjut mutasi: 'Belum Disesuaikan', 'Sesuai', 'Tercatat'."),
            ('alasan_mutasi', 'VARCHAR(50)', 'Penyebab terjadinya selisih (Selisih Hitung, Barang Rusak, Hilang).'),
            ('id_kartu_stok', 'INT', 'Kunci tamu nomor bukti jurnal mutasi kartu stok hasil rekonsiliasi.'),
            ('dihitung_oleh', 'INT', 'Kunci tamu staf pelaksana pencacahan fisik di rak (users.id).'),
            ('waktu_hitung', 'TIMESTAMP', 'Waktu hasil cacahan fisik diinput ke lembar kerja sistem.'),
            ('dimutasi_oleh', 'INT', 'Kunci tamu pejabat yang menyetujui mutasi penyesuaian saldo.')
        ]
    },
    {
        'table_no': '3.25',
        'name': 'detail_opname_sn',
        'domain': 'Audit Stok Opname (Verifikasi Serial Alkes)',
        'pk': 'id',
        'fk': 'id_detail_opname (detail_opname_batch.id), id_unit_serial (unit_serial.id)',
        'fungsi': 'Merekam hasil verifikasi fisik individual per unit nomor seri alat kesehatan yang ditemukan saat pemeriksaan audit stok opname di gudang.',
        'cols': [
            ('id', 'INT', 'Kunci primer baris verifikasi nomor seri alat kesehatan.'),
            ('id_detail_opname', 'INT', 'Kunci tamu merujuk ke detail_opname_batch.id.'),
            ('id_unit_serial', 'INT', 'Kunci tamu unit serial terdaftar pada unit_serial.id.'),
            ('serial_number', 'VARCHAR(100)', 'Nomor seri fisik instrumen medis yang dipindai nyata.'),
            ('jenis_tindakan', 'VARCHAR(50)', "Status temuan: 'Sesuai Ada', 'Tidak Ditemukan', 'Unit Baru'."),
            ('created_at', 'TIMESTAMP', 'Waktu pemindaian nomor seri alat kesehatan dicatat.')
        ]
    },
    {
        'table_no': '3.26',
        'name': 'riwayat_hitung_opname',
        'domain': 'Audit Stok Opname (Jejak Rekam Koreksi)',
        'pk': 'id',
        'fk': 'id_detail_opname (detail_opname_batch.id), dihitung_oleh (users.id)',
        'fungsi': 'Merekam riwayat koreksi angka hitung fisik selama sesi audit berlangsung guna mencegah manipulasi angka persediaan dan menjaga keabsahan data audit fisik.',
        'cols': [
            ('id', 'INT', 'Kunci primer catatan jejak koreksi hitungan audit.'),
            ('id_detail_opname', 'INT', 'Kunci tamu lembar kerja pada detail_opname_batch.id.'),
            ('hitung_sebelumnya', 'INT', 'Angka kuantitas cacahan fisik sebelum dilakukan koreksi.'),
            ('hitung_baru', 'INT', 'Angka kuantitas cacahan fisik baru hasil penghitungan ulang.'),
            ('dihitung_oleh', 'INT', 'Kunci tamu staf yang mengoreksi angka hitungan (users.id).'),
            ('alasan_koreksi', 'TEXT', 'Penjelasan perihal alasan diadakannya koreksi angka fisik.'),
            ('waktu_koreksi', 'TIMESTAMP', 'Waktu pencatatan koreksi angka cacahan dibukukan.')
        ]
    }
]

print(f"Total tables mapped in dictionary: {len(tables_dict)}")
