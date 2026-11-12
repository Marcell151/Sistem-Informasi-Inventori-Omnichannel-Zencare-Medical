text = """3.4	Logical Database
 
Logical Data Model (LDM) pada Sistem Informasi Inventori Omnichannel ZenCare Medical dirancang menggunakan basis data relasional yang memetakan 20 entitas tabel terintegrasi. Pusat relasi data bertumpu pada tabel produk_variasi yang menjadi poros logistik multi-satuan dan turunan langsung dari produk_induk (katalog informasi dasar obat dan alat kesehatan). Dari produk_variasi, aliran pencatatan fisik persediaan dipisahkan menjadi tiga entitas spesifik: stok_toko untuk akumulasi saldo aktif siap jual, stok_batch untuk sediaan obat berbasis nomor batch dan tanggal kedaluwarsa terdekat (First Expired First Out / FEFO), serta unit_serial untuk pelacakan nomor seri unik (Serial Number) dan masa berlaku garansi alat kesehatan secara individual.
Pencatatan arus masuk dan keluar barang menerapkan skema tabel induk dan rincian. Pasokan masuk dari distributor diakomodasi oleh penerimaan_stok dan penerimaan_detail yang terhubung dinamis ke tabel supplier sehingga satu jenis produk dapat dipasok oleh banyak distributor rekanan. Sementara itu, arus barang keluar lintas saluran (kasir toko fisik dan pesanan belanja e-commerce) ditampung pada tabel penjualan dan detail_penjualan. Khusus pesanan daring, entitas pesanan_batch_alokasi bertugas mengunci nomor batch fisik obat sementara sebelum barang dikemas agar tidak terjual ganda di meja kasir. Seluruh transaksi ini diikat ke tabel users guna mengidentifikasi staf penanggung jawab berdasarkan perannya (Superadmin, Admin, Kasir, dan Pelanggan).
Seluruh pergerakan barang bermuara pada kartu_stok sebagai buku besar mutasi yang mencatat riwayat pergerakan fisik dan saldo berjalan secara permanen. Pengendalian mutu operasional diperkuat oleh histori_konversi untuk rekam jejak pembongkaran kemasan dus ke eceran secara otomatis saat persediaan rak habis, serta log_anomali_fefo untuk mendokumentasikan deviasi pemilihan nomor batch di kasir demi kebutuhan evaluasi kepatuhan. Pengendalian intern berkala didukung modul stok opname yang mencakup sesi_opname (pembekuan transaksi kasir sementara), detail_opname_batch (lembar hitung fisik obat dan kalkulasi selisih), detail_opname_sn (verifikasi alkes berseri), serta riwayat_hitung_opname (rekam jejak koreksi hitungan). Sebagai pelengkap, entitas mandiri pengaturan_web mengelola informasi portal publik tanpa mencampuri relasi logistik."""

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("Forbidden found:", found)

# Check all 20 tables in Demo_Bersih.sql vs text
tables = [
    'produk_induk', 'produk_variasi', 'stok_toko', 'stok_batch', 'unit_serial',
    'supplier', 'penerimaan_stok', 'penerimaan_detail', 'penjualan', 'detail_penjualan',
    'pesanan_batch_alokasi', 'users', 'kartu_stok', 'histori_konversi', 'log_anomali_fefo',
    'sesi_opname', 'detail_opname_batch', 'detail_opname_sn', 'riwayat_hitung_opname', 'pengaturan_web'
]
mentioned = [t for t in tables if t in text]
missing = [t for t in tables if t not in text]
print("Mentioned tables:", len(mentioned), mentioned)
print("Missing tables:", len(missing), missing)

