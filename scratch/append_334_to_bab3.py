# -*- coding: utf-8 -*-
"""
Script to append Subbab 3.3.4 Activity Diagram to laporan_ta/BAB_III_ZENCARE_MEDICAL.html
"""

import re

file_path = r'laporan_ta/BAB_III_ZENCARE_MEDICAL.html'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

subbab_334_html = '''
            <!-- 3.3.4 Activity Diagram -->
            <div class="mb-6 relative group" id="sec_3_3_4_wrapper">
                <div class="no-print flex justify-end gap-2 mb-2">
                    <a href="../lain/semua_activity_diagram_zencare.html" target="_blank"
                        class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Buka Activity Diagram Visual Interaktif (8 Diagram A-H)
                    </a>
                    <button onclick="copyText('sec_3_3_4', this)"
                        class="text-xs text-blue-600 hover:text-blue-800 border border-slate-200 bg-slate-50 px-2 py-1 rounded transition">
                        Salin Subbab 3.3.4
                    </button>
                </div>
                <div id="sec_3_3_4">
                    <h4 class="text-base font-bold mb-2">3.3.4 Activity Diagram</h4>
                    <p class="text-justify indent-8 mb-4">
                        <i>Activity diagram</i> merupakan pemodelan grafis dalam <i>Unified Modeling Language</i> (UML) yang menggambarkan aliran kendali (<i>workflow</i>) fungsional dari satu aktivitas ke aktivitas lainnya di dalam sistem. Berbeda dengan diagram kasus penggunaan (<i>use case diagram</i>) yang berfokus pada apa yang dilakukan sistem dan siapa pelakunya, <i>activity diagram</i> menekankan pada urutan proses langkah demi langkah, percabangan logika keputusan (<i>decision node</i>), penggabungan aliran (<i>merge node</i>), serta pemisahan tanggung jawab antarentitas pengguna dan sistem menggunakan partisi jalur (<i>swimlanes</i>).
                    </p>
                    <p class="text-justify indent-8 mb-6">
                        Guna menjamin ketepatan implementasi perangkat lunak dan kepatuhan terhadap regulasi tata kelola kefarmasian, perancangan <i>activity diagram</i> pada ZenCare Medical dipetakan ke dalam 8 (delapan) alur aktivitas operasional utama (Diagram A hingga Diagram H), sebagaimana dijabarkan sebagai berikut:
                    </p>

                    <!-- A. Autentikasi (Login) -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">A. Autentikasi (Login)</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas Autentikasi memetakan mekanisme validasi identitas dan penyaluran hak akses pengguna berdasarkan arsitektur <i>Role-Based Access Control</i> (RBAC). Alur masuk dibedakan ke dalam dua titik antarmuka: pengguna internal (Kasir, Admin Toko, dan Superadmin) mengakses melalui formulir login <i>back-office</i>, sedangkan Pelanggan mengakses melalui halaman web <i>e-commerce</i>. Khusus untuk Pelanggan baru yang belum memiliki akun, sistem menyediakan formulir pendaftaran mandiri agar data profil personal dan alamat pengiriman disimpan oleh basis data sebelum pengguna melanjutkan proses masuk.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Saat kredensial (nama pengguna dan kata sandi) dikirimkan, sistem melakukan verifikasi keabsahan data terhadap hash keamanan di basis data. Apabila data tidak sesuai, sistem menerbitkan pesan kesalahan dan mengembalikan pengguna ke formulir input. Sebaliknya, apabila data terverifikasi sah, sistem mengevaluasi peran pengguna dan mengarahkan ke halaman kerja yang sesuai dengan wewenangnya: Kasir diarahkan langsung membuka terminal kasir fisik POS tunggal; Admin diarahkan ke dasbor operasional dan logistik pergudangan; Superadmin diarahkan ke modul kendali manajerial penuh (termasuk manajemen pengguna, laporan audit, dan pengaturan sistem); sementara Pelanggan diarahkan kembali ke beranda belanja, keranjang, dan profil digital. Alur ini divisualisasikan pada Gambar 3.10.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.10_Activity_Autentikasi.svg" alt="Activity Diagram Autentikasi" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.10</b> Activity Diagram Autentikasi (Login Multi-Aktor)</p>
                        </div>
                    </div>

                    <!-- B. Pesanan Daring (E-Commerce) -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">B. Pesanan Daring (E-Commerce Checkout)</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas Pesanan Daring memetakan interaksi transaksi pemesanan produk grosir satuan besar antara Pelanggan, Sistem E-Commerce, dan gerbang pembayaran pihak ketiga (<i>Midtrans</i>). Alur diawali ketika pelanggan menekan tombol <i>checkout</i> dari keranjang belanja. Sistem memuat ringkasan produk dan menampilkan pilihan metode pemenuhan pesanan. Apabila pelanggan memilih pengiriman via kurir ekspedisi, sistem mewajibkan penentuan alamat tujuan untuk mengkalkulasi tarif ongkos kirim otomatis (melalui integrasi API RajaOngkir). Namun, apabila pelanggan memilih opsi Ambil di Toko (<i>Pick-up in Store</i>), tahapan ongkos kirim dilewati dan sistem langsung menetapkan biaya pengiriman sebesar Rp 0.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Setelah total tagihan akhir disajikan, sistem menerbitkan token transaksi <i>Midtrans Snap</i> dan memberikan batas waktu pelunasan selama 2 jam. Pelanggan menyelesaikan pembayaran melalui saluran pilihan (Virtual Account, QRIS, atau transfer bank). Gerbang pembayaran memvalidasi status transaksi; apabila pembayaran gagal atau kedaluwarsa, sistem memperbarui status transaksi menjadi dibatalkan. Sebaliknya, apabila pembayaran tervalidasi sukses (<i>settlement</i>), sistem secara otomatis mengalokasikan persediaan berdasarkan karakteristik komoditas: untuk kategori Obat dialokasikan nomor batch terdekat kedaluwarsa (prinsip FEFO), sedangkan untuk kategori Alat Kesehatan dicadangkan unit dengan nomor seri (SN) aktif. Sistem seketika mengunci fisik produk dengan menandai status stok sebagai <i>Reserved</i> guna mencegah terjadinya penjualan ganda di meja kasir fisik, memperbarui status pesanan menjadi "Diproses", dan menerbitkan faktur penjualan digital (<i>invoice</i>). Alur ini divisualisasikan pada Gambar 3.11.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.11_Activity_Pesanan_Daring.svg" alt="Activity Diagram Pesanan Daring" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.11</b> Activity Diagram Pesanan Daring (E-Commerce &amp; Midtrans)</p>
                        </div>
                    </div>

                    <!-- C. Pesanan Luring (Transaksi Kasir POS) -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">C. Pesanan Luring (Transaksi Kasir / POS)</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas Transaksi Kasir (POS) memetakan alur penjualan langsung secara tatap muka (<i>walk-in customer</i>) di gerai fisik toko. Kasir membuka terminal antarmuka POS terpadu, lalu memindai kode batang (<i>barcode</i>) kemasan atau menginput SKU produk. Sistem merespons dengan menampilkan rincian produk, fleksibilitas pemilihan satuan (<i>Dual-UOM</i>: eceran Strip/Botol vs grosir Box), kuantitas fisik yang tersedia di rak, serta rekomendasi nomor batch obat terdekat sesuai prinsip FEFO (atau nomor seri alkes). Kasir mengambil fisik barang di etalase; apabila stok kemasan eceran kosong di rak, kasir dapat menggunakan tombol instan <i>Bongkar Box</i> untuk mengonversi kemasan dus menjadi eceran secara otomatis.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Pada tahap verifikasi fisik, apabila kasir mengambil nomor batch yang berbeda dari rekomendasi sistem (misalnya akibat pergeseran letak barang oleh pembeli sebelumnya), kasir memilih batch fisik riil tersebut, dan sistem secara otomatis merekam kejadian tersebut ke dalam <b>Log Anomali FEFO</b> tanpa menghentikan proses transaksi agar antrean di meja kasir tidak terganggu. Setelah seluruh barang masuk ke daftar belanja, kasir memproses pembayaran. Pada metode tunai, kasir menginput nominal uang yang diterima, sistem memvalidasi kecukupan uang, dan menghitung nominal kembalian secara otomatis. Pada metode non-tunai (QRIS), tahapan uang kembalian dilewati. Saat transaksi diselesaikan, sistem memotong stok fisik secara permanen di basis data sesuai identitas Batch/SN yang diambil, mencatat mutasi keluar pada riwayat Kartu Stok, merekam transaksi ke riwayat kasir harian, dan mencetak struk belanja thermal. Alur ini divisualisasikan pada Gambar 3.12.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.12_Activity_Pesanan_Kasir_POS.svg" alt="Activity Diagram Transaksi Kasir POS" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.12</b> Activity Diagram Transaksi Kasir (POS, Dual-UOM &amp; FEFO)</p>
                        </div>
                    </div>

                    <!-- D. Kelola Pesanan Daring & Pembatalan -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">D. Kelola Pesanan Daring &amp; Pembatalan</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas ini memetakan proses pengelolaan dan pemenuhan pesanan <i>e-commerce</i> oleh staf operasional (Admin Toko). Alur dimulai ketika Admin membuka menu Transaksi pada tab khusus "E-Commerce" guna meninjau pesanan berstatus "Diproses". Sistem menyajikan detail produk beserta instruksi identitas logistik (Nomor Batch FEFO atau Serial Number) yang harus disiapkan. Admin mencetak lembar instruksi pengambilan fisik (<i>Picking List</i>) dan mengambil barang dari rak penyimpanan gudang.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Selanjutnya, alur terbagi berdasarkan tindakan operasional staf. Apabila pesanan Ambil di Toko tidak kunjung diambil melampaui batas toleransi atau terjadi kondisi pembatalan pesanan, Admin memilih opsi "Batalkan Pesanan". Sistem seketika mengeksekusi pelepasan kuncian stok (<i>Reserved</i>) kembali menjadi kuantitas fisik aktif di rak toko dan memperbarui status menjadi "Dibatalkan". Sebaliknya, pada alur pemenuhan normal, staf mengemas paket. Pada pesanan via kurir ekspedisi, Admin menginput nomor resi, mencetak label pengiriman, dan mengubah status menjadi "Dikirim" (di mana sistem secara permanen memotong stok <i>Reserved</i> menjadi mutasi keluar penjualan pada Kartu Stok). Pada pesanan <i>Pick-up in Store</i>, Admin menyiapkan paket di rak khusus dan mengubah status menjadi "Siap Diambil". Saat pembeli datang ke gerai dan menunjukkan kode unik pengambilan, Admin memverifikasi kode, menyerahkan barang, dan memperbarui status menjadi "Selesai" (mencatat mutasi keluar pada Kartu Stok). Alur ini divisualisasikan pada Gambar 3.13.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg" alt="Activity Diagram Kelola Pesanan Daring" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.13</b> Activity Diagram Kelola Pesanan Daring &amp; Pembatalan (Release Reserved)</p>
                        </div>
                    </div>

                    <!-- E. Kelola Penerimaan Barang & Validasi Logistik -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">E. Kelola Penerimaan Barang &amp; Validasi Logistik Medis</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas ini memetakan alur pendataan pasokan barang masuk dari distributor rekanan dengan menonjolkan fungsi sistem sebagai penjaga gerbang (<i>gatekeeper</i>) kepatuhan regulasi farmasi. Proses dimulai saat Admin membuka menu Penerimaan Barang, memilih nama produk dan pemasok, serta menginput data dasar dokumen yang mencakup Nomor Faktur/Surat Jalan, Tanggal Penerimaan, Kuantitas Diterima, dan Harga Beli Satuan.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Sistem kemudian memisahkan syarat input identitas medis berdasarkan kategori produk: untuk produk "Obat", staf diwajibkan menginput Nomor Batch dan Tanggal Kedaluwarsa (<i>Expiration Date</i>); sedangkan untuk "Alat Kesehatan", staf diwajibkan menginput Nomor Seri Unik (<i>Serial Number</i> / SN). Saat formulir disimpan, sistem memverifikasi format data dan memastikan tanggal kedaluwarsa tidak lampau. Apabila terdeteksi tidak lengkap atau tidak valid, sistem menolak entri data dan menampilkan pesan peringatan agar staf melengkapi ulang formulir. Sebaliknya, apabila data valid, sistem merekam identitas tersebut ke basis data, menambah saldo kuantitas persediaan gudang, mencatat mutasi masuk pada buku besar Kartu Stok, serta menerbitkan perintah cetak stiker label <i>barcode</i> logistik untuk ditempelkan pada fisik kemasan dus sebelum disimpan di rak penyimpanan. Alur ini divisualisasikan pada Gambar 3.14.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.14_Activity_Penerimaan_Barang.svg" alt="Activity Diagram Penerimaan Barang" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.14</b> Activity Diagram Penerimaan Barang &amp; Validasi Identitas Medis (Batch/SN)</p>
                        </div>
                    </div>

                    <!-- F. Mutasi Stok Manual -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">F. Mutasi Stok Manual</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas ini memetakan alur penyesuaian kuantitas fisik persediaan secara manual yang dieksekusi oleh staf gudang guna mengakomodasi pencatatan kasus khusus di luar alur transaksi penjualan reguler, seperti barang rusak di rak pajangan, obat kedaluwarsa di etalase, selisih fisik minor, atau klaim retur barang cacat ke distributor. Alur diawali ketika Admin membuka menu Mutasi Stok, mencari produk target, serta memilih Jenis Mutasi (Penambahan atau Pengurangan) beserta Alasan Penyesuaian medis yang relevan.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Staf diwajibkan memilih target identitas logistik spesifik yang terdampak: nomor batch untuk komoditas obat atau nomor seri untuk alat kesehatan. Setelah menginput kuantitas, nomor referensi dokumen, dan catatan medis penyesuaian, staf menekan tombol proses. Sistem memvalidasi kecukupan saldo fisik pada batch/SN yang dipilih (khusus penyesuaian pengurangan kuantitas). Apabila kuantitas melebihi sisa fisik yang ada di sistem, pemrosesan ditolak guna mencegah terjadinya stok minus. Apabila validasi lolos, sistem memperbarui saldo fisik permanen pada tabel logistik basis data, membukukan mutasi penyesuaian ke dalam Kartu Stok beserta alasan medis yang dicantumkan, dan menampilkan notifikasi keberhasilan pembaruan data. Alur ini divisualisasikan pada Gambar 3.15.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.15_Activity_Mutasi_Stok.svg" alt="Activity Diagram Mutasi Stok Manual" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.15</b> Activity Diagram Mutasi Stok Manual (Penyesuaian Fisik &amp; Kartu Stok)</p>
                        </div>
                    </div>

                    <!-- G. Output (Laporan Manajerial & Logistik) -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">G. Output (Laporan Manajerial &amp; Logistik)</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas ini memetakan alur penarikan rekapitulasi data hasil keluaran (<i>output</i>) manajerial yang hak aksesnya dibatasi secara ketat hanya untuk entitas wewenang tertinggi, yakni <b>Superadmin (Pemilik)</b>. Alur dimulai ketika pengguna mengakses modul menu Laporan. Sistem pertama-tama bertindak sebagai gerbang keamanan (<i>RBAC Gatekeeper</i>) untuk mengevaluasi hak akses sesi pengguna; apabila sesi terdeteksi bukan Superadmin, akses seketika diblokir dan pengguna dialihkan ke dasbor operasional.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Setelah lolos validasi hak akses, Superadmin menentukan pilihan jenis laporan kuantitas medis yang murni berfokus pada volume pergerakan fisik barang tanpa melibatkan perhitungan omset atau laba finansial, meliputi: Laporan Penjualan Omnichannel (komparasi volume penjualan kasir POS vs transaksi daring e-commerce); Laporan Persediaan &amp; Kartu Stok (ringkasan volume fisik total per unit); Laporan Logistik Medis &amp; Audit Deviasi FEFO (jejak serial number alkes, pemantauan obat mendekati batas kedaluwarsa, dan log anomali pengambilan kasir); serta Laporan Riwayat Penerimaan, Mutasi, dan Konversi UOM. Sistem memproses kueri agregasi basis data dan menampilkan data tabular di layar. Superadmin dapat meninjau data atau menekan tombol cetak untuk menerbitkan dokumen resmi berformat PDF sebagai arsip manajerial toko. Alur ini divisualisasikan pada Gambar 3.16.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.16_Activity_Laporan_Manajerial.svg" alt="Activity Diagram Output Laporan Manajerial" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.16</b> Activity Diagram Output Laporan Manajerial &amp; Logistik (RBAC Gatekeeper)</p>
                        </div>
                    </div>

                    <!-- H. Kelola Stok Opname & Audit Fisik -->
                    <div class="mb-8">
                        <h5 class="font-bold text-slate-800 mb-2">H. Kelola Stok Opname &amp; Audit Fisik (System Freeze Lock)</h5>
                        <p class="text-justify indent-8 mb-3">
                            Diagram aktivitas ini memetakan alur audit persediaan fisik periodik (<i>Stock Opname</i>) yang merupakan salah satu fitur pengendalian intern paling krusial di ZenCare Medical. Alur ini melibatkan kerja sama operasional yang ketat antara Superadmin dan Admin Toko guna memastikan validitas saldo fisik di rak etalase dan gudang dengan saldo buku pada sistem secara objektif dan akuntabel.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Proses diawali ketika Superadmin membuka menu Stok Opname, menentukan jadwal periode audit, dan menekan tombol <b>"Buka Sesi Stok Opname"</b>. Sistem seketika menginisiasi sesi audit aktif dan memicu mekanisme <b>System Freeze Lock</b>, yakni penguncian global yang menangguhkan seluruh transaksi keluar pada kasir POS dan fitur pemesanan daring <i>e-commerce</i> guna mencegah terjadinya pergeseran kuantitas selama proses penghitungan berlangsung. Selanjutnya, Admin Toko mengunduh dan mencetak Lembar Hitung Buta (<i>Blind Count Sheet</i>) yang sengaja tidak menampilkan angka saldo buku sistem guna menghindari bias dan manipulasi data. Staf operasional menyisir seluruh etalase toko dan rak gudang untuk menghitung fisik barang secara langsung, menginput hasil cacahan ke lembar kerja sistem, lalu menyimpannya.
                        </p>
                        <p class="text-justify indent-8 mb-4">
                            Sistem secara otomatis mengkalkulasi deviasi selisih (<i>variance</i>) antara hasil hitung fisik dengan saldo buku sistem (menghasilkan status: Sesuai, Selisih Kurang, atau Selisih Lebih). Superadmin meninjau matriks tabel rekonsiliasi varian, mencantumkan catatan justifikasi audit, lalu menekan tombol <b>"Otorisasi Rekonsiliasi &amp; Tutup Sesi"</b>. Sistem melakukan penyesuaian saldo persediaan secara otomatis di basis data mengikuti hasil cacah riil, mencatat jurnal penyesuaian ke buku besar Kartu Stok, melepas kembali status <i>System Freeze Lock</i> sehingga kasir dan pemesanan e-commerce kembali beroperasi normal, serta menerbitkan dokumen resmi <b>Berita Acara Stok Opname</b> bertandatangan lengkap dengan rincian varian selisih untuk diarsipkan bersama. Alur ini divisualisasikan pada Gambar 3.17.
                        </p>
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded">
                                <img src="../lain/Gambar_3.17_Activity_Stok_Opname.svg" alt="Activity Diagram Kelola Stok Opname" class="mx-auto max-w-full h-auto">
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.17</b> Activity Diagram Kelola Stok Opname &amp; Audit Fisik (Freeze Lock &amp; Berita Acara)</p>
                        </div>
                    </div>

                </div>
            </div>
'''

# Find where sec_3_3_3 closes and insert 3.3.4 right before the closing wrapper divs
target_str = '''                        </li>
                    </ol>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Notification (No Print) -->'''

replacement_str = '''                        </li>
                    </ol>
                </div>
            </div>
''' + subbab_334_html + '''
        </div>

    </div>

    <!-- Bottom Notification (No Print) -->'''

if target_str in content:
    content = content.replace(target_str, replacement_str)
    print("Replaced target cleanly.")
else:
    print("Exact target not found, trying regex...")
    pattern = re.compile(r'(</li>\s*</ol>\s*</div>\s*</div>\s*</div>\s*</div>\s*<!-- Bottom Notification)', re.DOTALL)
    # Let's inspect where sec_3_3_3 ends
    pos_sec333 = content.find('sec_3_3_3')
    print("sec_3_3_3 position:", pos_sec333)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Saved BAB_III_ZENCARE_MEDICAL.html!")

