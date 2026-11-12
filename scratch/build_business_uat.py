import re
import os

# Data 26 Skenario Kebutuhan Bisnis UAT (8 Bagian)
uat_modules = [
    {
        "letter": "A",
        "roman": "Bagian I",
        "title": "Modul Autentikasi Staf dan Kontrol Hak Akses (<i>RBAC</i>)",
        "tbl_num": "4.2",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian I: Autentikasi dan Hak Akses (<i>RBAC</i>)",
        "explanation": "Pengujian pada Bagian I membuktikan bahwa sistem keamanan satu pintu telah memenuhi kebutuhan operasional toko dalam membatasi wewenang kerja. Pemilik Toko memiliki wewenang penuh pada data manajerial, Admin Toko fokus pada logistik persediaan, dan Kasir Toko terisolasi khusus pada terminal transaksi kasir. Pembatasan ini mencegah staf membuka menu di luar wewenangnya sehingga kerahasiaan data usaha tetap terjaga dengan aman.",
        "scenarios": [
            {
                "code": "UAT-01",
                "req": "Pemisahan wewenang operasional staf toko sesuai pembagian tugas kerja nyata (Kasir, Admin, dan Pemilik Toko).",
                "crit": "Sistem secara otomatis mengarahkan staf ke antarmuka yang tepat sesuai peran wewenangnya saat masuk sistem.",
                "status": "BERHASIL",
                "role": "Semua Staf"
            },
            {
                "code": "UAT-02",
                "req": "Perlindungan data sensitif toko dari akses staf yang tidak memiliki wewenang operasional.",
                "crit": "Sistem menolak akses jika kasir atau staf mencoba membuka menu data manajerial melalui tautan peramban.",
                "status": "BERHASIL",
                "role": "Pemilik / Kasir"
            },
            {
                "code": "UAT-03",
                "req": "Kemudahan pengelolaan akun staf dan pembaruan kata sandi operasional gerai.",
                "crit": "Pemilik Toko dapat mendaftarkan akun staf baru dan mengubah kata sandi dengan verifikasi keamanan yang valid.",
                "status": "BERHASIL",
                "role": "Pemilik Toko"
            }
        ]
    },
    {
        "letter": "B",
        "roman": "Bagian II",
        "title": "Modul Master Produk Medis, Konfigurasi Multi-Satuan (<i>Multi-UOM</i>), dan Pemasok",
        "tbl_num": "4.3",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian II: Master Data Produk dan Multi-Satuan",
        "explanation": "Pengujian pada Bagian II membuktikan bahwa pengelolaan komoditas medis telah memenuhi kebutuhan variasi kemasan toko. Admin Toko dapat menginput produk induk beserta variasi kemasan (box, strip, botol, atau satuan alat kesehatan). Konversi kemasan besar ke eceran terkecil berjalan otomatis tanpa salah hitung rasio, dan sistem secara proaktif memberikan sinyal peringatan saat persediaan mendekati batas aman minimum.",
        "scenarios": [
            {
                "code": "UAT-04",
                "req": "Pencatatan data produk medis yang memiliki berbagai kekuatan dosis dan jenis kemasan (grosir/eceran).",
                "crit": "Produk induk dan variasi dosis/kemasannya tersimpan rapi dalam satu kesatuan data tanpa kerancuan nama produk.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            },
            {
                "code": "UAT-05",
                "req": "Otomasi pemecahan kemasan besar (misal Box) menjadi kemasan eceran (Strip/Tablet) untuk kebutuhan penjualan.",
                "crit": "Stok satuan besar berkurang dan stok satuan kecil bertambah otomatis sesuai rasio kemasan yang ditentukan.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            },
            {
                "code": "UAT-06",
                "req": "Pencatatan data rekanan distributor pemasok resmi dan peringatan dini saat persediaan menipis.",
                "crit": "Data narahubung distributor tercatat lengkap dan sistem menampilkan indikator peringatan saat stok mencapai batas minimum.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            }
        ]
    },
    {
        "letter": "C",
        "roman": "Bagian III",
        "title": "Modul Transaksi Penjualan Kasir POS dan Alokasi <i>FEFO</i>",
        "tbl_num": "4.4",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian III: Transaksi Kasir POS dan Logika <i>FEFO</i>",
        "explanation": "Pengujian pada Bagian III membuktikan kemudahan kasir dalam melayani pembeli secara cepat dan akurat. Pemindaian barcode produk langsung memunculkan informasi harga dan stok. Logika FEFO secara otomatis mengeluarkan obat dengan tanggal kedaluwarsa terdekat tanpa kasir perlu mengingatnya manual, disertai dialog peringatan jika terjadi permintaan khusus pembeli, serta pencetakan struk belanja yang jelas.",
        "scenarios": [
            {
                "code": "UAT-07",
                "req": "Kecepatan dan kemudahan kasir dalam memproses belanja pembeli menggunakan alat pemindai barcode.",
                "crit": "Pemindaian barcode langsung memasukkan produk ke daftar belanja kasir dengan harga satuan yang tepat.",
                "status": "BERHASIL",
                "role": "Kasir Toko"
            },
            {
                "code": "UAT-08",
                "req": "Penjagaan kualitas obat melalui pengeluaran otomatis stok dengan tanggal kedaluwarsa paling dekat (<i>FEFO</i>).",
                "crit": "Sistem secara otomatis mengalokasikan nomor *batch* terdekat tanpa kasir perlu memilih secara manual di layar.",
                "status": "BERHASIL",
                "role": "Kasir Toko"
            },
            {
                "code": "UAT-09",
                "req": "Peringatan dan pencatatan alasan saat pembeli meminta obat dengan masa simpan lebih lama (anomali <i>FEFO</i>).",
                "crit": "Muncul dialog peringatan yang meminta kasir mengisi alasan sebelum transaksi disetujui untuk diproses.",
                "status": "BERHASIL",
                "role": "Kasir Toko"
            },
            {
                "code": "UAT-10",
                "req": "Perhitungan subtotal belanja, kalkulasi uang kembalian, dan pencetakan nota pembayaran kasir.",
                "crit": "Nilai transaksi dan kembalian terhitung presisi, serta nota belanja fisik tercetak rapi untuk pembeli.",
                "status": "BERHASIL",
                "role": "Kasir Toko"
            }
        ]
    },
    {
        "letter": "D",
        "roman": "Bagian IV",
        "title": "Modul Toko Daring (<i>E-Commerce</i>) dan Sinkronisasi Penjualan <i>Omnichannel</i>",
        "tbl_num": "4.5",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian IV: Toko Daring dan Sinkronisasi Penjualan",
        "explanation": "Pengujian pada Bagian IV membuktikan integrasi antara saluran daring dan gerai fisik. Konsumen dapat berbelanja produk kesehatan dengan mudah dari peramban, stok barang langsung direservasi sementara saat pesanan dibuat sehingga tidak terjual ganda di kasir fisik, dan verifikasi pembayaran daring langsung memperbarui saldo persediaan secara seketika (*real-time*).",
        "scenarios": [
            {
                "code": "UAT-11",
                "req": "Kemudahan konsumen melihat katalog obat bebas/alkes, memilih kemasan, dan melakukan pemesanan mandiri.",
                "crit": "Pelanggan dapat memasukkan produk ke keranjang belanja daring dan melihat rincian harga secara transparan.",
                "status": "BERHASIL",
                "role": "Pelanggan"
            },
            {
                "code": "UAT-12",
                "req": "Pencegahan penjualan ganda persediaan antara pesanan daring dan transaksi kasir fisik gerai.",
                "crit": "Stok barang daring langsung direservasi sementara (*reserved stock*) saat pembeli menyelesaikan proses pesanan.",
                "status": "BERHASIL",
                "role": "Admin / Pelanggan"
            },
            {
                "code": "UAT-13",
                "req": "Kemudahan pelanggan mengunggah bukti pembayaran dan staf admin memproses pengiriman pesanan.",
                "crit": "Bukti transfer dapat diunggah dengan mudah oleh pelanggan dan status pesanan diperbarui hingga siap dikirim.",
                "status": "BERHASIL",
                "role": "Pelanggan / Admin"
            }
        ]
    },
    {
        "letter": "E",
        "roman": "Bagian V",
        "title": "Modul Logistik Penerimaan Pasokan Barang Masuk dan Penandaan Barcode",
        "tbl_num": "4.6",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian V: Penerimaan Pasokan Barang dan Barcode",
        "explanation": "Pengujian pada Bagian V membuktikan kemudahan staf logistik dalam mendata barang kiriman dari distributor resmi. Formulir penerimaan mencatat nomor surat jalan, tanggal kedaluwarsa, dan nomor *batch* baru. Sistem juga memfasilitasi pelacakan nomor seri unik alat kesehatan serta mencetak label stiker barcode kemasan yang siap dipindai di meja kasir.",
        "scenarios": [
            {
                "code": "UAT-14",
                "req": "Pencatatan pasokan obat masuk dari distributor lengkap dengan nomor *batch* dan tanggal kedaluwarsa.",
                "crit": "Faktur penerimaan tersimpan lengkap dan saldo persediaan bertambah otomatis ke unit penyimpanan barang.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            },
            {
                "code": "UAT-15",
                "req": "Pencatatan nomor seri unik (*serial number*) untuk setiap unit alat kesehatan guna garansi produk.",
                "crit": "Unit fisik alat kesehatan terdata secara individual sehingga riwayat garansi purnajual dapat ditelusuri.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            },
            {
                "code": "UAT-16",
                "req": "Pencetakan stiker label barcode kemasan guna mempercepat identifikasi barang di rak toko.",
                "crit": "Stiker barcode tercetak jelas dan dapat langsung dipindai dengan akurat oleh alat pemindai kasir.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            }
        ]
    },
    {
        "letter": "F",
        "roman": "Bagian VI",
        "title": "Modul Pengelolaan Mutasi Stok Manual dan Buku Besar Kartu Stok",
        "tbl_num": "4.7",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian VI: Mutasi Stok Manual dan Kartu Stok",
        "explanation": "Pengujian pada Bagian VI membuktikan ketertiban penyesuaian barang di luar penjualan. Staf dapat mencatat pengurangan stok akibat botol pecah, rusak kemasan, atau kedaluwarsa dengan mencantumkan alasan operasional wajib. Setiap pergerakan barang tercatat kronologis pada buku besar kartu stok sehingga posisi saldo persediaan selalu dapat dipertanggungjawabkan.",
        "scenarios": [
            {
                "code": "UAT-17",
                "req": "Pencatatan resmi pengurangan stok akibat barang rusak, botol pecah, atau kedaluwarsa di rak toko.",
                "crit": "Pengurangan fisik tercatat dengan alasan operasional yang jelas tanpa merusak catatan transaksi penjualan.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            },
            {
                "code": "UAT-18",
                "req": "Pemeriksaan riwayat keluar-masuk barang pada buku besar kartu stok untuk penelusuran persediaan.",
                "crit": "Kartu stok menyajikan mutasi masuk, mutasi keluar, saldo akhir berjalan, dan nomor *batch* secara kronologis.",
                "status": "BERHASIL",
                "role": "Admin / Pemilik"
            },
            {
                "code": "UAT-19",
                "req": "Pencetakan lembar fisik kartu stok format A4 untuk keperluan dokumentasi dan pengawasan internal.",
                "crit": "Dokumen kartu stok tercetak rapi sesuai periode tanggal dan siap ditandatangani untuk arsip toko.",
                "status": "BERHASIL",
                "role": "Pemilik Toko"
            }
        ]
    },
    {
        "letter": "G",
        "roman": "Bagian VII",
        "title": "Modul Siklus Pengawasan Stok Fisik (<i>Stok Opname</i>) Terpusat",
        "tbl_num": "4.8",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian VII: Siklus Pengawasan Stok Fisik Terpusat",
        "explanation": "Pengujian pada Bagian VII memvalidasi alur pengawasan fisik tahunan toko kesehatan ZenCare Medical. Pemilik Toko dapat membekukan operasional transaksi agar data tidak bergerak selama penghitungan. Kasir menghitung fisik secara objektif (*blind count*) tanpa melihat angka sistem. Admin Toko memverifikasi selisih dan melakukan mutasi koreksi penyeimbang, diakhiri dengan penerbitan Berita Acara resmi.",
        "scenarios": [
            {
                "code": "UAT-20",
                "req": "Pengamanan proses penghitungan fisik melalui pembekuan transaksi kasir dan belanja daring (*freeze lock*).",
                "crit": "Seluruh transaksi terkunci sementara sehingga angka stok tidak bergeser selama proses pencacahan fisik di rak.",
                "status": "BERHASIL",
                "role": "Pemilik Toko"
            },
            {
                "code": "UAT-21",
                "req": "Objektivitas penghitungan fisik oleh kasir lapangan tanpa terpengaruh catatan angka sistem (*blind count*).",
                "crit": "Layar kasir tidak menampilkan saldo buku sistem; kasir hanya menginput kondisi fisik riil (baik/rusak/kedaluwarsa).",
                "status": "BERHASIL",
                "role": "Kasir Toko"
            },
            {
                "code": "UAT-22",
                "req": "Kemudahan verifikasi selisih persediaan (*surplus/defisit*) dan eksekusi mutasi koreksi penyeimbang.",
                "crit": "Sistem menyaring barang yang cocok dan langsung menampilkan barang berselisih beserta pilihan mutasi koreksi.",
                "status": "BERHASIL",
                "role": "Admin Toko"
            },
            {
                "code": "UAT-23",
                "req": "Penerbitan dokumen resmi Berita Acara stok opname dan pembukaan kembali seluruh transaksi gerai.",
                "crit": "Berita Acara format A4 tercetak memuat nilai selisih dan kunci transaksi kasir/online terbuka kembali normal.",
                "status": "BERHASIL",
                "role": "Pemilik Toko"
            }
        ]
    },
    {
        "letter": "H",
        "roman": "Bagian VIII",
        "title": "Modul Laporan Manajerial, Pengawasan Kedaluwarsa, dan Dasbor Eksekutif",
        "tbl_num": "4.9",
        "tbl_title": "Hasil Evaluasi Kebutuhan Pengguna Bagian VIII: Pelaporan Manajerial dan Dasbor",
        "explanation": "Pengujian pada Bagian VIII membuktikan ketersediaan informasi strategis bagi pengelola usaha toko. Dasbor eksekutif menyajikan indikator omzet harian, nilai total persediaan, dan peringatan dini obat yang akan kedaluwarsa dalam 30–90 hari. Seluruh laporan penjualan dan anomali FEFO dapat difilter fleksibel serta dicetak ke format dokumen fisik resmi.",
        "scenarios": [
            {
                "code": "UAT-24",
                "req": "Pemantauan ringkasan performa penjualan harian/bulanan dan nilai aset persediaan gerai pada dasbor.",
                "crit": "Dasbor eksekutif menampilkan ringkasan omzet, grafik tren transaksi, dan status stok kritis secara seketika.",
                "status": "BERHASIL",
                "role": "Pemilik Toko"
            },
            {
                "code": "UAT-25",
                "req": "Peringatan dini barang mendekati kedaluwarsa (30, 60, dan 90 hari) untuk percepatan retur ke distributor.",
                "crit": "Sistem menyajikan daftar obat yang masa simpannya hampir habis guna mencegah potensi kerugian barang kedaluwarsa.",
                "status": "BERHASIL",
                "role": "Admin / Pemilik"
            },
            {
                "code": "UAT-26",
                "req": "Pencetakan laporan penjualan berkala dan laporan analisis anomali pengeluaran obat (*FEFO*).",
                "crit": "Laporan web dapat difilter berdasarkan rentang tanggal dan dicetak ke dalam dokumen fisik A4 resmi siap arsip.",
                "status": "BERHASIL",
                "role": "Pemilik Toko"
            }
        ]
    }
]

print("Total modules:", len(uat_modules))
total_scenarios = sum(len(m['scenarios']) for m in uat_modules)
print("Total scenarios:", total_scenarios)

