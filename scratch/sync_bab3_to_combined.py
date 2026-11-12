with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t3 = f.read()

with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t34 = f.read()

# 1. BMHP in Workflow 2
old_bmhp = """                                <ul class="list-disc pl-6 space-y-1 mt-1 text-slate-700">

                                    <li>Untuk produk <b>Obat</b>, staf wajib menginput Nomor Batch dan Tanggal

                                        Kedaluwarsa.</li>

                                    <li>Untuk produk <b>Alat Kesehatan</b>, staf wajib menginput Nomor Seri (<i>Serial

                                            Number</i>) unik untuk setiap unit fisik.</li>

                                </ul>"""

new_bmhp = """                                <ul class="list-disc pl-6 space-y-1 mt-1 text-slate-700">

                                    <li>Untuk produk <b>Obat</b> serta <b>Alat Kesehatan Habis Pakai / BMHP</b> (seperti strip tes, jarum lancet, alat cek asam urat), staf wajib menginput Nomor Batch dan Tanggal Kedaluwarsa.</li>

                                    <li>Untuk produk <b>Alat Kesehatan ber-unit</b> (seperti Tensimeter Digital, Termometer Digital), staf wajib menginput Nomor Seri (<i>Serial Number</i>) unik untuk setiap unit fisik.</li>

                                </ul>"""

# 2. Sitemap Kasir Intro
old_sm_kasir_intro = """                        <p class="text-justify indent-8 mb-3">

                            Struktur peta situs ini memetakan antarmuka terminal kasir gerai fisik yang dirancang khusus

                            untuk kecepatan transaksi luring (<i>walk-in customer</i>) dan kepatuhan alokasi logistik

                            medis di meja kasir, sebagaimana ditunjukkan pada Gambar 3.6. Alur navigasi ini mencakup 2

                            (dua) komponen utama:

                        </p>"""

new_sm_kasir_intro = """                        <p class="text-justify indent-8 mb-3">

                            Struktur peta situs ini memetakan antarmuka terminal kasir gerai fisik yang dirancang khusus

                            untuk kecepatan transaksi luring (<i>walk-in customer</i>) dan kepatuhan alokasi logistik

                            medis di meja kasir, sebagaimana ditunjukkan pada Gambar 3.6. Alur navigasi ini mencakup 3

                            (tiga) komponen utama:

                        </p>"""

# 3. Sitemap Kasir Mermaid Diagram
old_sm_kasir_mmd = """                                Kasir["Sistem Informasi Inventori Omnichannel<br>ZenCare Medical (Kasir)"] -->

                                K1["Autentikasi"]

                                Kasir --> K2["Terminal Kasir (POS)"]



                                K1 --> K1_1["Login Kasir"]

                                K1 --> K1_2["Logout"]



                                K2 --> K2_1["Pencarian & Scan Barcode"]

                                K2 --> K2_2["Transaksi Dual-UOM & Bongkar Box"]

                                K2 --> K2_3["Rekomendasi FEFO Obat & Validasi SN Alkes"]

                                K2 --> K2_4["Panel Deviasi Anomali FEFO"]

                                K2 --> K2_5["Pembayaran & Cetak Struk Belanja"]

                                K2 --> K2_6["Riwayat Transaksi & Cetak Ulang Struk"]

                                K2 --> K2_7["Indikator Status Freeze Mode"]



                                style Kasir

                                fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold

                                style K1 fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold

                                style K2 fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold"""

new_sm_kasir_mmd = """                                Kasir["Sistem Informasi Inventori Omnichannel<br>ZenCare Medical (Kasir)"] -->

                                K1["Autentikasi"]

                                Kasir --> K2["Terminal Kasir (POS)"]

                                Kasir --> K3["Terminal Pencacahan Fisik Rak (Saat Sesi Freeze Aktif)"]



                                K1 --> K1_1["Login Kasir"]

                                K1 --> K1_2["Logout"]



                                K2 --> K2_1["Pencarian & Scan Barcode"]

                                K2 --> K2_2["Transaksi Dual-UOM & Bongkar Box"]

                                K2 --> K2_3["Rekomendasi FEFO Obat & Validasi SN Alkes"]

                                K2 --> K2_4["Panel Deviasi Anomali FEFO"]

                                K2 --> K2_5["Pembayaran & Cetak Struk Belanja"]

                                K2 --> K2_6["Riwayat Transaksi & Cetak Ulang Struk"]

                                K2 --> K2_7["Pengalihan Otomatis Saat Sesi Stok Opname Aktif"]



                                K3 --> K3_1["Pencarian Cepat Barcode/SKU Rak"]

                                K3 --> K3_2["Pencacahan Fisik Tiga Kondisi (Baik, Rusak, Expired)"]

                                K3 --> K3_3["Penguncian Otomatis Batch Kedaluwarsa"]

                                K3 --> K3_4["Simpan Hasil Hitung Fisik (Strict Blind Count)"]



                                style Kasir

                                fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold

                                style K1 fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold

                                style K2 fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold

                                style K3 fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000,font-weight:bold"""

# 4. Sitemap Kasir List Items
old_sm_kasir_list = """                            <li><b>Terminal Kasir (POS):</b> Antarmuka terpadu pelayanan transaksi langsung di gerai

                                fisik. Modul ini memuat pencarian nama produk dan pemindaian <i>barcode</i> kemasan,

                                dukungan fleksibilitas multi-satuan (<i>Dual-UOM</i>) dengan tombol konversi otomatis

                                <i>Bongkar Box</i> saat stok eceran di rak habis, penyajian rekomendasi nomor batch obat

                                terdekat (FEFO) dan validasi nomor seri unik alat kesehatan, panel pencatatan deviasi

                                anomali apabila kasir mengambil batch di luar rekomendasi, modal penyelesaian pembayaran

                                (tunai atau non-tunai) yang langsung memicu pencetakan struk belanja, modal riwayat

                                transaksi harian kasir bersifat <i>read-only</i> untuk keperluan pencetakan ulang struk

                                belanja (<i>reprint receipt</i>) darurat serta rekonsiliasi penutupan shift kasir, serta

                                indikator pembekuan transaksi otomatis (<i>Freeze Mode</i>) ketika sesi stok opname

                                sedang berlangsung. Kasir tidak diberikan wewenang pembatalan transaksi (<i>void</i>)

                                atau manipulasi data penjualan; tata kelola pembatalan dan retur pesanan dipusatkan

                                secara terkendali pada menu pesanan omnichannel di bawah wewenang Admin dan Superadmin.

                            </li>

                        </ol>"""

new_sm_kasir_list = """                            <li><b>Terminal Kasir (POS):</b> Antarmuka terpadu pelayanan transaksi langsung di gerai

                                fisik. Modul ini memuat pencarian nama produk dan pemindaian <i>barcode</i> kemasan,

                                dukungan fleksibilitas multi-satuan (<i>Dual-UOM</i>) dengan tombol konversi otomatis

                                <i>Bongkar Box</i> saat stok eceran di rak habis, penyajian rekomendasi nomor batch obat

                                terdekat (FEFO) dan validasi nomor seri unik alat kesehatan, panel pencatatan deviasi

                                anomali apabila kasir mengambil batch di luar rekomendasi, modal penyelesaian pembayaran

                                (tunai atau non-tunai) yang langsung memicu pencetakan struk belanja, modal riwayat

                                transaksi harian kasir bersifat <i>read-only</i> untuk keperluan pencetakan ulang struk

                                belanja (<i>reprint receipt</i>) darurat serta rekonsiliasi penutupan shift kasir, serta

                                pengalihan otomatis ke antarmuka pencacahan fisik ketika sesi stok opname

                                sedang berlangsung. Kasir tidak diberikan wewenang pembatalan transaksi (<i>void</i>)

                                atau manipulasi data penjualan; tata kelola pembatalan dan retur pesanan dipusatkan

                                secara terkendali pada menu pesanan omnichannel di bawah wewenang Admin dan Superadmin.

                            </li>

                            <li><b>Terminal Pencacahan Fisik Rak (Strict Blind Count):</b> Antarmuka layar penuh khusus pencacahan fisik yang otomatis aktif menggantikan akses POS kasir ketika Superadmin membekukan sistem untuk audit stok opname. Pada antarmuka ini, kasir tidak dapat melihat stok pembukuan sistem maupun status reservasi, dan diwajibkan mencatat kuantitas riil di rak ke dalam 3 kolom kondisi: Kondisi Baik, Kondisi Rusak, dan Kondisi Expired (dengan penguncian otomatis pada batch yang telah melewati tanggal kedaluwarsa). Dilengkapi fasilitas pemindai barcode / pencarian cepat SKU serta penyimpanan parsial berkala.</li>

                        </ol>"""

# 5. Sitemap Admin Mermaid
old_sm_admin_mmd = """                                A5 --> A5_1["Penerimaan Barang (Batch, ED, SN, Retur, & Barcode)"]

                                A5 --> A5_2["Konversi Satuan UOM & Mutasi Stok Manual"]

                                A5 --> A5_3["Pelaksanaan Pencacahan Stok Opname"]

                                A5 --> A5_4["Buku Kartu Stok & Monitoring Logistik Medis"]"""

new_sm_admin_mmd = """                                A5 --> A5_1["Penerimaan Barang (Batch, ED, SN, Retur, & Barcode)"]

                                A5 --> A5_2["Konversi Satuan UOM & Mutasi Stok Manual"]

                                A5 --> A5_3["Monitoring Progres & Verifikasi Selisih Stok Opname"]

                                A5 --> A5_4["Buku Kartu Stok & Monitoring Logistik Medis"]"""

# 6. Sitemap Admin List
old_sm_admin_list = """                            <li><b>Logistik &amp; Inventori:</b> Pusat kendali fisik barang masuk dan keluar gudang yang

                                terbagi menjadi penerimaan barang dari pemasok dengan validasi ketat identitas medis

                                (Batch, ED, Serial Number, retur barang cacat, dan pencetakan stiker barcode), modul

                                konversi satuan kemasan dus ke eceran beserta mutasi stok penyesuaian manual (barang

                                rusak/garansi), pencatatan lembar cacah fisik saat sesi stok opname berlangsung, serta

                                monitoring ketersediaan buku Kartu Stok terpisah dan direktori logistik medis.</li>"""

new_sm_admin_list = """                            <li><b>Logistik &amp; Inventori:</b> Pusat kendali fisik barang masuk dan keluar gudang yang

                                terbagi menjadi penerimaan barang dari pemasok dengan validasi ketat identitas medis

                                (Batch, ED, Serial Number, retur barang cacat, dan pencetakan stiker barcode), modul

                                konversi satuan kemasan dus ke eceran beserta mutasi stok penyesuaian manual (barang

                                rusak/garansi), monitoring progres penghitungan fisik kasir dan eksekusi verifikasi mutasi selisih anomali saat sesi stok opname berlangsung (tanpa hak membuka/menutup sesi), serta

                                monitoring ketersediaan buku Kartu Stok terpisah dan direktori logistik medis.</li>"""

# 7. Use Case Diagram Actors
old_uc_actor = """                                Pelanggan((Pelanggan)) --> UC1

                                Pelanggan --> UC2

                                Pelanggan --> UC3

                                Pelanggan --> UC4



                                Kasir((Kasir)) --> UC5



                                Admin((Admin Staff)) --> UC6"""

new_uc_actor = """                                Pelanggan((Pelanggan)) --> UC1

                                Pelanggan --> UC2

                                Pelanggan --> UC3

                                Pelanggan --> UC4



                                Kasir((Kasir)) --> UC5

                                Kasir((Kasir)) --> UC11



                                Admin((Admin Staff)) --> UC6"""

# 8. Use Case Description Kasir
old_uc_kasir_desc = """                                    tidak diberikan wewenang untuk membatalkan transaksi (<i>void</i>), mengubah nilai

                                    harga, atau mengedit data persediaan secara manual guna mencegah potensi kecurangan

                                    kas.</li>

                            </ul>

                        </li>"""

new_uc_kasir_desc = """                                    tidak diberikan wewenang untuk membatalkan transaksi (<i>void</i>), mengubah nilai

                                    harga, atau mengedit data persediaan secara manual guna mencegah potensi kecurangan

                                    kas.</li>

                                <li><b>Kelola Stok Opname (Pencacahan Fisik Rak):</b> Melaksanakan proses penghitungan dan pencacahan fisik persediaan di etalase dan rak penyimpanan (<i>blind count</i>) selama sesi stok opname aktif tanpa melihat angka kuantitas buku sistem guna menjamin integritas dan objektivitas data. Kasir mencatat kuantitas riil ke dalam tiga kolom kondisi fisik (Baik, Rusak, Expired).</li>

                            </ul>

                        </li>"""

# 9. Use Case Description Admin
old_uc_admin_desc = """                                <li><b>Kelola Stok Opname:</b> Melaksanakan proses penghitungan dan pencacahan fisik persediaan di etalase dan rak penyimpanan (<i>blind count</i>) selama sesi stok opname aktif tanpa melihat angka kuantitas buku sistem guna menjamin integritas dan objektivitas verifikasi data persediaan.</li>"""

new_uc_admin_desc = """                                <li><b>Kelola Stok Opname (Verifikasi Selisih &amp; Rekonsiliasi):</b> Memantau progres pengisian fisik kasir selama tahap penghitungan tanpa dapat menginput angka fisik, lalu mengevaluasi baris selisih anomali pada tahap verifikasi (105 item klop disaring otomatis), mengeksekusi mutasi selisih (rusak, kedaluwarsa, hilang, atau kelebihan kirim), serta memilih identitas serial number pada Alkes SN sebelum sesi diajukan untuk diselesaikan oleh Superadmin. Admin tidak memiliki wewenang membuka maupun menutup sesi opname.</li>"""

# 10. Activity Diagram Stok Opname Narrative
old_act_narrative = """                        <h5 class="font-bold text-slate-800 mb-2">H. Kelola Stok Opname &amp; Audit Fisik (Kunci Kasir &amp; Berita Acara)</h5>

                        <p class="text-justify indent-8 mb-3">

                            Diagram aktivitas Kelola Stok Opname memetakan prosedur pemeriksaan fisik persediaan secara berkala yang melibatkan kerja sama antara <b>Superadmin (Pemilik)</b>, <b>Admin (Staff)</b>, dan <b>Sistem</b> guna memastikan kesesuaian antara jumlah barang riil di rak toko dan gudang dengan catatan persediaan di sistem.

                        </p>

                        <p class="text-justify indent-8 mb-4">

                            Proses dimulai ketika Superadmin membuka menu Stok Opname dan menekan tombol <b>"Mulai Sesi Stok Opname"</b>. Sistem seketika mengaktifkan sesi pemeriksaan dan memberlakukan penguncian operasional sementara (<i>Freeze Lock</i>) pada terminal kasir toko dan fitur belanja daring, sehingga tidak ada transaksi masuk atau keluar yang dapat mengubah jumlah persediaan selama proses penghitungan berlangsung. Bersamaan dengan itu, sistem menyediakan lembar formulir penghitungan fisik yang sengaja tidak menampilkan angka stok sistem, agar staf dapat menghitung barang secara mandiri dan objektif.

                        </p>

                        <p class="text-justify indent-8 mb-4">

                            Selanjutnya, Admin melakukan penghitungan fisik barang secara langsung di area etalase dan gudang, memasukkan hasil hitungan ke dalam formulir di sistem, lalu menyimpan data hitungan setiap produk. Sistem secara otomatis menghitung selisih antara hasil hitungan fisik dengan catatan stok di sistem. Alur kemudian mengevaluasi kondisi apakah terdapat selisih kurang atau lebih: apabila ditemukan selisih, Admin memilih atau memasukkan identitas barang yang selisih (Nomor Batch obat atau Nomor Seri alkes) serta memberikan keterangan alasan terjadinya selisih, kemudian sistem mencatat penyesuaian tersebut ke dalam riwayat Kartu Stok sebelum sesi ditutup. Sebaliknya, apabila seluruh barang sudah sesuai dan tidak ada selisih, Admin langsung melakukan penutupan sesi opname.

                        </p>

                        <p class="text-justify indent-8 mb-4">

                            Setelah sesi ditutup, sistem mencabut status penguncian sementara sehingga layanan kasir toko fisik dan belanja daring dapat kembali melayani transaksi secara normal. Alur diakhiri dengan sistem menerbitkan dokumen resmi <b>Berita Acara Stok Opname</b> berformat PDF sebagai bukti pertanggungjawaban hasil pemeriksaan fisik persediaan. Alur ini divisualisasikan secara lengkap pada Gambar 3.17.

                        </p>"""

new_act_narrative = """                        <h5 class="font-bold text-slate-800 mb-2">H. Kelola Stok Opname &amp; Audit Fisik (Kunci Kasir &amp; Berita Acara)</h5>

                        <p class="text-justify indent-8 mb-3">

                            Diagram aktivitas Kelola Stok Opname memetakan prosedur pemeriksaan fisik persediaan secara berkala yang menerapkan prinsip pemisahan wewenang operasional (<i>segregation of duties</i>) antara <b>Superadmin (Pemilik)</b>, <b>Kasir (Pencacah Lapangan)</b>, <b>Admin Toko (Verifikator Anomali)</b>, dan <b>Sistem</b> guna memastikan keabsahan dan objektivitas data persediaan riil di rak etalase dan gudang dengan catatan pembukuan sistem.

                        </p>

                        <p class="text-justify indent-8 mb-4">

                            Proses dimulai ketika Superadmin membuka menu Stok Opname dan menekan tombol <b>"Mulai Sesi Stok Opname Baru"</b>. Sistem seketika mengaktifkan sesi pemeriksaan, mengambil snapshot 111 entitas fisik, dan memberlakukan penguncian operasional sementara (<i>Freeze Lock</i>) pada terminal kasir POS dan fitur belanja daring, sehingga tidak ada transaksi masuk atau keluar yang dapat mengubah saldo persediaan selama audit berlangsung. Saat kasir masuk ke sistem, akses POS otomatis dialihkan ke antarmuka <b>Terminal Pencacahan Fisik Rak (Strict Blind Count)</b> tanpa menampilkan angka stok sistem maupun jumlah pesanan yang direservasi. Kasir melakukan pencacahan fisik rak secara objektif dengan memasukkan kuantitas ke dalam 3 kolom kondisi fisik: <b>Kondisi Baik</b>, <b>Kondisi Rusak</b>, dan <b>Kondisi Expired</b> (batch yang telah melewati tanggal kedaluwarsa otomatis terkunci pada kolom Baik). Data hitungan fisik dapat disimpan secara berkala (parsial) ke pangkalan data.

                        </p>

                        <p class="text-justify indent-8 mb-4">

                            Setelah seluruh baris selesai dihitung oleh tim kasir, Admin Toko membuka tahap <b>Verifikasi Selisih</b>. Pada tahap ini, sistem secara otomatis menyaring tampilan: sebanyak 105 barang yang cocok (0 selisih) disembunyikan agar Admin langsung fokus pada barang yang berselisih. Admin mengevaluasi selisih fisik dan mengeksekusi mutasi penyesuaian melalui dialog konfirmasi kategori mutasi (Barang Rusak, Barang Kedaluwarsa, Barang Hilang, atau Kelebihan Kirim Supplier dengan beban kerugian Rp 0) serta memilih nomor seri fisik yang terdampak pada komoditas Alkes SN. Sistem mencatatkan mutasi tersebut secara otomatis ke dalam buku besar Kartu Stok dan memperbarui status unit serial.

                        </p>

                        <p class="text-justify indent-8 mb-4">

                            Setelah seluruh anomali selesai direkonsiliasi, Superadmin menekan tombol <b>"Selesaikan Sesi (Buka Kunci Server)"</b> untuk resmi mengakhiri audit. Sistem mencabut status penguncian sementara sehingga transaksi kasir POS dan pemesanan daring kembali aktif melayani pembeli. Alur diakhiri dengan penerbitan dokumen resmi <b>Berita Acara Hasil Pemeriksaan Fisik (Stok Opname)</b> berformat formal yang menampilkan seluruh 111 barang dengan bingkai merah tegas pada baris-baris yang mengalami selisih fisik beserta lembar tanda tangan pengesahan oleh Pengelola Stok dan Pemilik Toko. Alur ini divisualisasikan secara lengkap pada Gambar 3.17.

                        </p>"""

# 11. Wireframe Description
old_wf_desc = """                    <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                        Antarmuka audit fisik persediaan berkala dengan metode perhitungan buta (blind count). Saat sesi audit aktif, sistem menampilkan kartu metrik progres dan spanduk pembekuan transaksi sementara (freeze lock). Kolom stok buku pada tabel lapangan disembunyikan bagi petugas lapangan guna mencegah manipulasi data. Dilengkapi tab pencocokan nomor seri fisik alat kesehatan serta modul persetujuan penyesuaian selisih oleh manajerial.
                    </p>"""

new_wf_desc = """                    <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                        Antarmuka pemeriksaan fisik persediaan berkala dengan pemisahan peran operasional: form kasir menggunakan metode pencacahan buta (<i>blind count</i>) layar penuh tanpa angka stok sistem yang memuat 3 kolom kondisi fisik (Baik, Rusak, Expired), sedangkan antarmuka verifikasi Admin/Superadmin menampilkan ringkasan metrik estimasi acuan kerugian berbasis harga beli, penyaringan otomatis 105 barang klop, serta modul eksekusi mutasi anomali selisih (rusak, expired, hilang, kelebihan kirim) dan pelacakan serial number alkes sebelum penutupan sesi resmi oleh pemilik toko.
                    </p>"""

replacements = [
    ("BMHP", old_bmhp, new_bmhp),
    ("Sitemap Kasir Intro", old_sm_kasir_intro, new_sm_kasir_intro),
    ("Sitemap Kasir Diagram", old_sm_kasir_mmd, new_sm_kasir_mmd),
    ("Sitemap Kasir List", old_sm_kasir_list, new_sm_kasir_list),
    ("Sitemap Admin Diagram", old_sm_admin_mmd, new_sm_admin_mmd),
    ("Sitemap Admin List", old_sm_admin_list, new_sm_admin_list),
    ("Use Case Actor", old_uc_actor, new_uc_actor),
    ("Use Case Kasir Desc", old_uc_kasir_desc, new_uc_kasir_desc),
    ("Use Case Admin Desc", old_uc_admin_desc, new_uc_admin_desc),
    ("Activity Narrative", old_act_narrative, new_act_narrative),
    ("Wireframe Desc", old_wf_desc, new_wf_desc),
]

for name, o, n in replacements:
    if o in t34:
        t34 = t34.replace(o, n)
        print(f"Applied {name} to t34")
    else:
        print(f"FAILED to find {name} in t34")

with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(t34)

print("Finished updating BAB_III_DAN_IV_ZENCARE_MEDICAL.html")
