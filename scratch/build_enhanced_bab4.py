import re

# Read current BAB_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    orig = f.read()

# Section 4.1.2 s.d. 4.1.5 HTML block
extra_sections_html = """
                    <!-- ============================================================================== -->
                    <!-- 4.1.2 IMPLEMENTASI FORMULIR PENGELOLAAN MASTER DATA DAN LOGISTIK INVENTORI      -->
                    <!-- ============================================================================== -->
                    <div id="subbab_412" class="mt-8 pt-6 border-t border-slate-200">
                        <div class="flex items-center justify-between border-b pb-2 mb-4">
                            <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                                4.1.2 Implementasi Formulir Pengelolaan Master Data dan Logistik Inventori
                            </h4>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            Pengelolaan logistik inventori pada toko kesehatan ZenCare Medical menuntut ketelitian tinggi pada level formulir masukan data. Setiap pergerakan barang, baik penambahan komoditas baru, pemecahan kemasan grosir ke eceran, hingga penyesuaian kuantitas secara manual, difasilitasi oleh antarmuka formulir interaktif yang dilengkapi validasi data berlapis guna mencegah galat masukan pengguna (<em>human error</em>). Rincian implementasi formulir operasional diuraikan sebagai berikut:
                        </p>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.2.1 Formulir Konfigurasi Master Produk dan Penetapan Multi-UOM
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/34_Form_Input_Master_Produk.png" alt="Tampilan Formulir Input Master Produk Baru" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.28 Tampilan Formulir Input Master Produk Baru
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Antarmuka jendela sembulan (<em>modal dialog</em>) untuk pendaftaran komoditas produk induk baru ke dalam katalog sistem. Formulir ini memuat konfigurasi atribut dasar barang, meliputi kode induk produk, nama komoditas, klasifikasi kelompok barang (Obat-obatan atau Alat Kesehatan), deskripsi indikasi medis atau spesifikasi teknis alat, ambang batas hari peringatan dini masa simpan (<em>expired alert days</em>), serta unggah berkas foto fisik barang. Pada tahap penetapan variasi kemasan bertingkat (<em>multi-UOM</em>), pengguna dapat mendefinisikan satuan kemasan besar (seperti Box, Dus, atau Karton), satuan kemasan kecil (seperti Strip, Botol, Tube, atau Pcs), rasio konversi matematis antar-satuan, harga jual grosir, serta harga jual eceran. Sistem secara ketat memvalidasi kelengkapan bidang isian dan memastikan relasi integritas referensial pada tabel produk_induk dan produk_variasi tetap konsisten.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.2.2 Formulir Eksekusi Konversi Satuan Multi-UOM
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/35_Form_Konversi_UOM.png" alt="Tampilan Formulir Eksekusi Konversi Satuan Multi-UOM" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.29 Tampilan Formulir Eksekusi Konversi Satuan Multi-UOM
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Antarmuka kerja staf gudang dan kasir untuk mengeksekusi pemecahan kemasan besar menjadi kemasan kecil siap jual eceran di gerai fisik. Formulir dilengkapi pemilih produk variasi, informasi rasio konversi baku yang tersimpan di sistem, saldo fisik kemasan besar yang tersedia di rak, serta masukan jumlah dus yang akan dipecah. Saat pengguna mengetikkan kuantitas dus yang dipecah, sistem secara interaktif mengalkulasi estimasi unit eceran yang akan dihasilkan (misalnya: 1 Box Amoxicillin dipecah menjadi 10 Strip). Ketika formulir disubmit, sistem menjalankan operasi atomik: memotong saldo kemasan besar, menambahkan saldo kemasan kecil, mereplikasi data nomor angkatan (<em>batch</em>) dan tanggal kedaluwarsa yang identik ke unit eceran, serta mencatat nomor bukti mutasi ke tabel konversi_satuan dan kartu_stok.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.2.3 Formulir Penyesuaian Mutasi Stok Manual
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/36_Form_Mutasi_Stok.png" alt="Tampilan Formulir Mutasi Stok Manual" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.30 Tampilan Formulir Mutasi Stok Manual
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Antarmuka penyesuaian kuantitas persediaan di luar transaksi reguler penjualan dan penerimaan barang. Formulir ini digunakan oleh Admin untuk mencatat kejadian operasional khusus, seperti penarikan barang rusak, pemusnahan barang kedaluwarsa, penyesuaian selisih sampling fisik berkala, atau retur konsinyasi internal. Pengguna diwajibkan memilih nomor angkatan obat atau nomor seri alat kesehatan yang mengalami mutasi, menentukan arah pergerakan stok (Masuk atau Keluar), memasukkan kuantitas, memilih alasan kategorisasi mutasi yang baku dari sistem, serta menyertakan catatan kronologis pendukung. Seluruh data penyesuaian terekam secara permanen pada tabel mutasi_stok_manual guna menjamin akuntabilitas pengawasan persediaan.
                            </p>
                        </div>
                    </div>

                    <!-- ============================================================================== -->
                    <!-- 4.1.3 IMPLEMENTASI SIKLUS OPERASIONAL STOK OPNAME MULTI-PERAN                   -->
                    <!-- ============================================================================== -->
                    <div id="subbab_413" class="mt-8 pt-6 border-t border-slate-200">
                        <div class="flex items-center justify-between border-b pb-2 mb-4">
                            <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                                4.1.3 Implementasi Siklus Operasional Stok Opname Multi-Peran (Siklus 4 Tahap)
                            </h4>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            Sebagai modul pengendali integritas logistik persediaan yang paling krusial, modul Stok Opname pada ZenCare Medical dirancang beroperasi melalui alur 4 (empat) tahapan kerja yang memisahkan batas tanggung jawab antar-aktor (Superadmin, Kasir, dan Admin). Implementasi visual dari masing-masing tahapan siklus ini diuraikan sebagai berikut:
                        </p>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.3.1 Tahap Pembekuan Transaksi dan Inisiasi Sesi oleh Superadmin
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/30_Stok_Opname_Mulai_Sesi.png" alt="Tampilan Jendela Konfirmasi Pembekuan Transaksi dan Pembukaan Sesi Stok Opname" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.31 Tampilan Jendela Konfirmasi Pembekuan Transaksi dan Pembukaan Sesi Stok Opname
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Tampilan jendela dialog inisiasi sesi pemeriksaan fisik yang secara eksklusif hanya dapat diakses oleh peran Superadmin (Pemilik Toko). Saat tombol mulai sesi ditekan, sistem menampilkan kotak dialog konfirmasi pembekuan operasional terpusat (<em>Server Freeze</em>). Dialog ini memperingatkan bahwa pembukaan sesi akan membekukan seluruh modul yang berpotensi mengubah saldo stok buku: terminal kasir POS luring dikunci dari penerimaan pembayaran, proses <em>checkout</em> toko daring (E-Commerce) dinonaktifkan sementara, formulir konversi satuan UOM dikunci, dan transaksi penerimaan barang ditangguhkan. Langkah ini menjamin saldo buku teoretis berada dalam keadaan statis dan bebas dari distorsi data simultan (<em>race condition</em>) selama staf melakukan penghitungan fisik di rak toko.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.3.2 Tahap Pencacahan Fisik Rak Metode Hitung Buta (Blind Count) oleh Kasir
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/31_Stok_Opname_Blind_Count_Kasir.png" alt="Tampilan Antarmuka Kasir Pencacahan Fisik Rak Metode Hitung Buta" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.32 Tampilan Antarmuka Kasir Pencacahan Fisik Rak Metode Hitung Buta (Blind Count)
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Antarmuka lembar kerja pencacahan fisik yang dibuka oleh staf Kasir di area etalase dan gudang. Sesuai prinsip hitung buta (<em>strict blind count</em>), antarmuka ini secara mutlak menyembunyikan angka saldo sistem, kuantitas stok terpesan (<em>reserved stock</em>), serta kolom kalkulasi selisih. Staf kasir hanya disajikan daftar nama komoditas, identitas nomor angkatan/nomor seri, serta 3 (tiga) kolom isian kondisi fisik riil: Fisik Baik, Fisik Rusak, dan Fisik Kedaluwarsa. Sistem menerapkan validasi protektif cerdas: untuk nomor angkatan obat yang masa berlakunya masih aktif di masa depan, kolom isian 'Fisik Kedaluwarsa' dinonaktifkan secara otomatis, sehingga jika ada barang yang mengalami cacat atau segel terbuka, staf wajib menginputkannya ke kolom 'Fisik Rusak'. Tombol pintas pemindai kode batang mempercepat pelompatan kursor ke baris barang bersangkutan.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.3.3 Tahap Verifikasi dan Rekonsiliasi Selisih Fisik oleh Admin Toko
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/32_Stok_Opname_Verifikasi_Admin.png" alt="Tampilan Antarmuka Admin Verifikasi dan Rekonsiliasi Selisih Fisik" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.33 Tampilan Antarmuka Admin Verifikasi dan Rekonsiliasi Selisih Fisik
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Antarmuka telaah hasil pencacahan fisik yang dapat diakses oleh Admin Toko setelah kasir menyelesaikan proses hitung di lapangan. Antarmuka ini mengusung desain yang bersih dan manusiawi, dilengkapi navigasi tab penyaring yang teratur: <em>Perlu Mutasi</em>, <em>Sesuai (Klop)</em>, <em>Sudah Dimutasi</em>, dan <em>Semua Item</em>. Secara <em>default</em>, sistem menerapkan penyaringan otomatis (<em>auto-filtering</em>) yang langsung menyembunyikan komoditas yang cocok (selisih nol) dan memusatkan pandangan admin hanya pada barang yang terdeteksi memiliki selisih kuantitas. Pada baris barang berselisih, admin disediakan tombol tindakan mutasi langsung dengan pilihan alasan terstruktur (Barang Rusak, Barang Kedaluwarsa, Selisih Kurang/Hilang, atau Koreksi Surplus Lebih) serta rekapitulasi estimasi nilai beban kerugian persediaan yang dihitung otomatis berdasarkan harga perolehan barang.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.3.4 Dokumen Resmi Berita Acara Hasil Pemeriksaan Fisik Persediaan
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/33_Cetak_Berita_Acara_Opname.png" alt="Tampilan Lembar Berita Acara Stok Opname Resmi Format A4 Lanskap" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.34 Tampilan Lembar Berita Acara Stok Opname Resmi Format A4 Lanskap
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar dokumen cetak resmi Berita Acara Hasil Pemeriksaan Fisik (Stok Opname) yang dirancang dalam tata letak A4 Lanskap (<em>landscape</em>) 10 kolom. Dokumen memuat kepala surat (kop surat) resmi ZenCare Medical, nomor referensi sesi, waktu pelaksanaan, ringkasan jumlah barang yang diperiksa, total baris yang cocok, total baris berselisih, serta total akumulasi nilai estimasi kerugian persediaan. Seluruh baris komoditas disajikan secara transparan, di mana baris barang yang mengalami selisih ditandai secara tegas menggunakan batas sel (<em>border</em>) berwarna merah yang tahan cetak printer tanpa bergantung pada opsi latar belakang browser. Bagian bawah lembar memuat 3 (tiga) kolom tanda tangan pengesahan berjenjang: Petugas Pencacah Fisik (Kasir), Petugas Verifikator (Admin Toko), dan Pimpinan / Penanggung Jawab Inventori (Superadmin).
                            </p>
                        </div>
                    </div>

                    <!-- ============================================================================== -->
                    <!-- 4.1.4 IMPLEMENTASI DOKUMEN FISIK DAN OPERASIONAL TRANSAKSI OMNICHANNEL          -->
                    <!-- ============================================================================== -->
                    <div id="subbab_414" class="mt-8 pt-6 border-t border-slate-200">
                        <div class="flex items-center justify-between border-b pb-2 mb-4">
                            <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                                4.1.4 Implementasi Dokumen Fisik dan Operasional Transaksi Omnichannel
                            </h4>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            Sebagai sistem informasi perdagangan dan inventori yang beroperasi di dua saluran (toko fisik dan toko daring), ZenCare Medical menghasilkan berbagai bentuk dokumen transaksi fisik yang siap cetak untuk melayani kebutuhan pelanggan di kasir, kurir logistik pengiriman, maupun penerimaan pasokan barang dari distributor resmi. Rincian dokumen operasional tersebut meliputi:
                        </p>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.4.1 Struk Transaksi Kasir POS Format Kertas Termal
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-2xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/26_Cetak_Struk_Kasir.png" alt="Tampilan Cetak Struk Kasir POS Format Kertas Termal" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.35 Tampilan Cetak Struk Kasir POS Format Kertas Termal
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar bukti belanja konsumen di gerai fisik yang diformat khusus untuk mesin pencetak termal (<em>thermal receipt printer</em>) ukuran 80mm dan 58mm. Struk mencantumkan logo dan alamat gerai ZenCare Medical, nomor faktur POS yang unik, waktu transaksi, nama kasir yang bertugas, rincian barang medis yang dibeli (termasuk pencetakan nomor angkatan obat dan nomor seri alat kesehatan guna kejelasan asal-usul barang), kuantitas, harga satuan, subtotal, potongan harga atau diskon, total akhir pembayaran, nominal uang tunai yang diserahkan, nominal kembalian, serta catatan keramahan layanan bagi konsumen.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.4.2 Faktur Penjualan Resmi Toko ZenCare Medical Format A4
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/27_Cetak_Invoice_Penjualan.png" alt="Tampilan Cetak Faktur Penjualan Resmi Format A4" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.36 Tampilan Cetak Faktur Penjualan Resmi Format A4
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Dokumen tagihan dan bukti transaksi resmi berukuran standar A4 yang dapat dicetak untuk keperluan transaksi bernominal besar, pesanan institusi klinik atau dokter langganan, maupun arsip keuangan toko. Dokumen dilengkapi kepala surat resmi perusahaan, identitas lengkap pembeli atau nama akun pemesan, tabel daftar komoditas yang dibeli beserta kode SKU, nomor angkatan, tanggal kedaluwarsa, harga satuan grosir/eceran, tarif ongkos kirim (untuk pesanan daring), kalkulasi perpajakan bila berlaku, metode pembayaran yang digunakan, serta area tanda tangan staf administrasi penjualan.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.4.3 Label Pengiriman Pesanan Daring dengan Kode Batang dan QR
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-2xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/28_Cetak_Label_Pengiriman.png" alt="Tampilan Cetak Label Pengiriman Kurir E-Commerce" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.37 Tampilan Cetak Label Pengiriman Kurir E-Commerce
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar stiker pengiriman barang pesanan toko daring (E-Commerce) yang dicetak menggunakan printer stiker termal standar logistik (100mm x 150mm) untuk ditempelkan pada kemasan paket luar. Label memuat kode batang (<em>barcode</em>) nomor faktur pesanan untuk pemindaian cepat oleh petugas ekspedisi, kode QR interaktif untuk pelacakan status pengiriman daring oleh konsumen, identitas ekspedisi kurir yang dipilih, rincian data alamat penerima lengkap beserta nomor kontak, berat volumetrik paket medis, daftar ringkas isi paket, serta instruksi khusus penanganan barang peka suhu atau mudah pecah (<em>fragile medical handling</em>).
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.4.4 Nota Penerimaan Logistik dan Barang Masuk
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/29_Cetak_Nota_Penerimaan.png" alt="Tampilan Cetak Nota Penerimaan Barang Masuk Logistik" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.38 Tampilan Cetak Nota Penerimaan Barang Masuk Logistik
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Dokumen serah terima fisik barang masuk dari distributor logistik ke gudang ZenCare Medical. Dokumen mencantumkan nomor bukti penerimaan internal, nomor surat jalan atau faktur distributor pemasok, tanggal barang diterima di gudang, nama staf penerima, nama perusahaan distributor, serta tabel komoditas barang yang diterima. Tabel merinci nama obat atau alat kesehatan, jumlah kemasan besar (dus/karton), rasio isi per kemasan, total unit eceran yang masuk, nomor angkatan produk, tanggal kedaluwarsa, harga beli perolehan per kemasan, serta kolom paraf verifikasi kondisi fisik segel barang oleh kepala gudang.
                            </p>
                        </div>
                    </div>

                    <!-- ============================================================================== -->
                    <!-- 4.1.5 IMPLEMENTASI LEMBAR CETAK LAPORAN OPERASIONAL DAN MANAJERIAL SISTEM       -->
                    <!-- ============================================================================== -->
                    <div id="subbab_415" class="mt-8 pt-6 border-t border-slate-200">
                        <div class="flex items-center justify-between border-b pb-2 mb-4">
                            <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                                4.1.5 Implementasi Lembar Cetak Dokumen Laporan Operasional dan Manajerial Sistem
                            </h4>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            Guna mendukung pertanggungjawaban manajerial dan evaluasi berkala oleh pimpinan toko kesehatan ZenCare Medical, sistem menyediakan modul pelaporan komprehensif yang dilengkapi format cetak resmi (<em>print view</em>) berstandar tata naskah dokumen perusahaan. Seluruh laporan menyajikan kop surat resmi, rentang tanggal pelaporan, rekapitulasi data matematis, tabel transaksi kronologis, serta kolom tanda tangan penanggung jawab. Rincian ketujuh format lembar cetak laporan resmi diuraikan sebagai berikut:
                        </p>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.1 Lembar Cetak Laporan Penjualan Barang Omnichannel
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/37_Cetak_Laporan_Penjualan.png" alt="Tampilan Lembar Cetak Laporan Penjualan Barang Omnichannel" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.39 Tampilan Lembar Cetak Laporan Penjualan Barang Omnichannel
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak resmi rekapitulasi penjualan yang menyatukan kinerja pendapatan dari kasir fisik dan pesanan toko daring. Menampilkan ringkasan metrik total penerimaan kotor, total potongan diskon, total pendapatan bersih, kuantitas unit barang terjual, serta perbandingan volume penjualan antar-kanal. Tabel merinci setiap nomor transaksi penjualan, waktu pembelian, nama kasir atau pemesan, rincian barang medis yang dibeli, harga satuan, metode bayar yang digunakan, dan status transaksi.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.2 Lembar Cetak Laporan Persediaan dan Ketersediaan Barang
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/38_Cetak_Laporan_Persediaan.png" alt="Tampilan Lembar Cetak Laporan Persediaan dan Ketersediaan Barang" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.40 Tampilan Lembar Cetak Laporan Persediaan dan Ketersediaan Barang
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak resmi posisi saldo persediaan barang yang tersedia di rak dan gudang pada saat dokumen dicetak. Dokumen ini menyajikan rekapitulasi total jenis produk aktif, akumulasi kuantitas fisik barang, estimasi valuasi nilai perolehan persediaan, serta pemisahan kuantitas dalam satuan besar (dus) dan satuan kecil (strip/botol). Tabel memuat kode SKU, nama komoditas obat atau alat kesehatan, kategori, jumlah saldo fisik siap jual, jumlah stok yang sedang teralokasi pada pesanan daring (<em>reserved</em>), serta indikator status ketersediaan (Aman, Menipis, atau Habis).
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.3 Lembar Cetak Laporan Kartu Riwayat Mutasi Stok
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/39_Cetak_Laporan_Kartu_Stok.png" alt="Tampilan Lembar Cetak Laporan Kartu Riwayat Mutasi Stok" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.41 Tampilan Lembar Cetak Laporan Kartu Riwayat Mutasi Stok
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak buku riwayat pergerakan stok (<em>inventory ledger</em>) untuk suatu entitas barang tertentu dalam rentang periode yang ditentukan. Menyajikan saldo awal persediaan, aliran mutasi masuk (dari penerimaan distributor, konversi kemasan, atau retur), aliran mutasi keluar (dari penjualan kasir, pemenuhan pesanan e-commerce, atau penyesuaian barang rusak), serta saldo akhir persediaan yang dihitung secara berkesinambungan. Setiap baris mutasi dilengkapi tanggal transaksi, nomor dokumen referensi, kanal pergerakan, dan identitas petugas yang melakukan pengesahan.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.4 Lembar Cetak Laporan Logistik Medis FEFO dan Pemantauan Kedaluwarsa
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/40_Cetak_Laporan_Logistik_Medis.png" alt="Tampilan Lembar Cetak Laporan Logistik Medis FEFO" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.42 Tampilan Lembar Cetak Laporan Logistik Medis FEFO dan Pemantauan Kedaluwarsa
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak khusus pengawasan mutu komoditas medis yang memetakan seluruh nomor angkatan (<em>batch</em>) obat berdasarkan sisa umur simpan produk. Laporan membagi barang ke dalam beberapa zona urgensi: Zona Merah (telah kedaluwarsa), Zona Kuning (mendekati kedaluwarsa dalam 30–90 hari), dan Zona Hijau (masa simpan aman di atas 90 hari). Melalui dokumen ini, pengelola toko dapat mengambil langkah preventif dengan memprioritaskan penjualan obat-obat yang mendekati kedaluwarsa sesuai asas FEFO (<em>First Expired, First Out</em>) atau mengajukan retur ke pihak pemasok sebelum batas waktu berakhir.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.5 Lembar Cetak Laporan Penerimaan Logistik dari Pemasok
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/41_Cetak_Laporan_Penerimaan.png" alt="Tampilan Lembar Cetak Laporan Penerimaan Logistik Masuk" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.43 Tampilan Lembar Cetak Laporan Penerimaan Logistik Masuk
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak rekapitulasi pasokan logistik barang masuk dari distributor resmi ke toko ZenCare Medical. Laporan memuat informasi ringkas total nilai pembelian perolehan, total kuantitas kemasan besar yang diterima, serta total transaksi pengiriman distributor. Tabel mencantumkan nomor faktur distributor, nama rekanan penyedia logistik, waktu penerimaan barang, nama staf penerima, rincian produk yang dipasok, nomor angkatan, tanggal kedaluwarsa, harga perolehan, dan verifikasi kelengkapan fisik pasokan.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.6 Lembar Cetak Laporan Mutasi Stok dan Konversi Satuan
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/42_Cetak_Laporan_Mutasi_Konversi.png" alt="Tampilan Lembar Cetak Laporan Mutasi Stok dan Konversi Satuan" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.44 Tampilan Lembar Cetak Laporan Mutasi Stok dan Konversi Satuan
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak komprehensif yang mendokumentasikan seluruh pergerakan persediaan non-penjualan di internal toko, yang terbagi ke dalam dua rekapitulasi: catatan penyesuaian manual (barang rusak, kedaluwarsa, koreksi opname, atau hilang) serta catatan konversi pemecahan satuan (pemecahan box/dus ke strip/botol eceran). Laporan memuat tanggal peristiwa, kode barang, nama komoditas, jenis pergerakan, kuantitas mutasi, alasan pembebanan, rasio konversi yang diterapkan, serta identitas petugas yang melakukan penginputan.
                            </p>
                        </div>

                        <div class="mt-5 mb-2">
                            <h5 class="text-xs font-bold font-serif text-slate-900">
                                4.1.5.7 Lembar Cetak Laporan Deteksi Anomali Pengeluaran Barang FEFO
                            </h5>
                        </div>
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/43_Cetak_Laporan_Anomali_FEFO.png" alt="Tampilan Lembar Cetak Laporan Deteksi Anomali FEFO" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                Gambar 4.45 Tampilan Lembar Cetak Laporan Deteksi Anomali FEFO
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                Lembar cetak laporan pengawasan kepatuhan protokol logistik medis dalam pendistribusian obat. Sistem secara otomatis mencatat dan mengevaluasi setiap insiden di mana kasir atau staf mengeluarkan nomor angkatan obat yang tanggal kedaluwarsanya lebih jauh padahal masih terdapat angkatan obat yang bertanggal kedaluwarsa lebih dekat di rak toko. Laporan menyajikan daftar nomor transaksi, tanggal transaksi, nama kasir bertugas, nama obat yang mengalami loncatan angkatan, nomor <em>batch</em> yang sebenarnya harus dikeluarkan menurut aturan FEFO, nomor <em>batch</em> yang diambil di lapangan, serta alasan penyimpangan yang diinputkan petugas (misalnya permintaan kemasan khusus oleh konsumen). Dokumen ini menjadi rujukan evaluasi berkala bagi pimpinan untuk menjaga kepatuhan operasional gerai.
                            </p>
                        </div>
                    </div>
"""

# Now let's integrate this into orig:
# 1. We replace the end of subbab 4.1.1 (closing subbab_411_content) by appending the extra sections before subbab_41_content closes!
target_close_411 = "</div> <!-- Closes subbab_411_content -->"
if target_close_411 in orig:
    new_text = orig.replace(target_close_411, target_close_411 + "\n" + extra_sections_html, 1)
    print("Successfully inserted extra sections!")
else:
    print("ERROR: target_close_411 not found!")
    exit(1)

# 2. Clean forbidden words in new_text:
# Replacements dictionary
replacements = [
    # Specific Audit terms
    ("Tampilan Antarmuka Halaman Kartu Stok Audit", "Tampilan Antarmuka Halaman Kartu Riwayat Mutasi Stok"),
    ("Gambar 4.10 Tampilan Antarmuka Halaman Kartu Stok Audit", "Gambar 4.10 Tampilan Antarmuka Halaman Kartu Riwayat Mutasi Stok"),
    ("merekam jejak audit kronologis setiap komoditas", "merekam rekam jejak kronologis setiap komoditas"),
    ("rujukan utama bagi auditor internal untuk menelusuri", "rujukan utama bagi verifikator internal untuk menelusuri"),
    ("Tampilan Antarmuka Halaman Stok Opname dan Audit Fisik", "Tampilan Antarmuka Halaman Stok Opname dan Pemeriksaan Fisik"),
    ("Gambar 4.11 Tampilan Antarmuka Halaman Stok Opname dan Audit Fisik", "Gambar 4.11 Tampilan Antarmuka Halaman Stok Opname dan Pemeriksaan Fisik"),
    ("kegiatan audit fisik persediaan (stok opname)", "kegiatan pemeriksaan fisik persediaan (stok opname)"),
    ("Modul pelaporan audit logistik masuk (inbound)", "Modul pelaporan verifikasi logistik masuk (inbound)"),
    ("Modul rekapitulasi audit pergerakan persediaan non-penjualan", "Modul rekapitulasi pencatatan pergerakan persediaan non-penjualan"),
    ("4.1.1.19 Laporan Audit Anomali Pengeluaran FEFO", "4.1.1.19 Laporan Deteksi Anomali Pengeluaran FEFO"),
    ("Tampilan Antarmuka Halaman Laporan Audit Anomali Pengeluaran FEFO", "Tampilan Antarmuka Halaman Laporan Deteksi Anomali Pengeluaran FEFO"),
    ("Gambar 4.19 Tampilan Antarmuka Halaman Laporan Audit Anomali Pengeluaran FEFO", "Gambar 4.19 Tampilan Antarmuka Halaman Laporan Deteksi Anomali Pengeluaran FEFO"),
    ("Panel audit kepatuhan protokol logistik medis", "Panel pengawasan kepatuhan protokol logistik medis"),
    ("Catatan: Audit Rutin", "Catatan: Pemeriksaan Rutin"),
    ("Log audit mencatat alasan mutasi", "Catatan log sistem mencatat alasan mutasi"),
    ("rekapitulasi audit", "rekapitulasi pemeriksaan"),
    ("rekam jejak audit (<em>audit trail</em>)", "rekam jejak riwayat mutasi (<em>mutation trail</em>)"),
    ("audit trail", "mutation trail"),
    ("Audit Trail", "Mutation Trail"),
    ("audit konvensional", "pemeriksaan fisik konvensional"),
    ("selama audit berlangsung", "selama pemeriksaan berlangsung"),
    ("audit fisik", "pemeriksaan fisik"),
    ("Audit Fisik", "Pemeriksaan Fisik"),
    ("audit", "pemeriksaan"),
    ("Audit", "Pemeriksaan"),

    # Dosen replacement
    ("disyaratkan oleh dosen pembimbing", "ditetapkan pada kriteria perancangan fungsional"),

    # Sidang replacement
    ("demonstrasi sidang tanpa data uji tertinggal", "demonstrasi operasional tanpa data uji tertinggal"),

    # Bab 4 replacement (ensure uppercase Bab IV or bab_iv)
    ("copyText('full_bab4_content'", "copyText('full_bab_iv_content'"),
    ("id=\"full_bab4_content\"", "id=\"full_bab_iv_content\""),
    ("Subbab 4.1 s.d. 4.1.1.27 Implementasi Sistem", "Subbab 4.1 Hasil s.d. 4.1.5 Lembar Cetak Laporan"),
    ("Salin Semua Bab IV", "Salin Semua Bab IV"),
]

for old, new in replacements:
    new_text = new_text.replace(old, new)

# Update subbab 4.1 header subtitle
new_text = new_text.replace(
    "Implementasi Antarmuka Sistem)",
    "Implementasi Sistem Lengkap)"
)

with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_text)

print("Saved updated BAB_IV_ZENCARE_MEDICAL.html!")

