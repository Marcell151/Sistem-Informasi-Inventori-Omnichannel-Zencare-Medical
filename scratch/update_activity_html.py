with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Clean audit in line 400
c = c.replace(
    'laporan audit, dan pengaturan sistem',
    'laporan manajerial, dan pengaturan sistem'
)

# 2. Clean audit in line 595
c = c.replace(
    'modul laporan manajerial dan audit persediaan',
    'modul laporan manajerial dan pemantauan persediaan'
)

# 3. Replace old tab_h
old_tab_h_pattern = """        <!-- ================= H. KELOLA STOK OPNAME ================= -->
        <div id="tab_h" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.17 Activity Diagram Kelola Stok Opname (Audit Fisik &amp; Freeze Lock)</h2>
                        <p>Swimlane: Superadmin (Pemilik) &bull; Admin (Staff Logistik) &bull; Sistem Back-Office</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.17_Activity_Stok_Opname.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.17_Activity_Stok_Opname.svg" alt="Activity Diagram Kelola Stok Opname">
                </div>
            </div>
                        <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.H)</h3>
                <p>
                    Diagram aktivitas Kelola Stok Opname memetakan prosedur pemeriksaan fisik persediaan secara berkala yang melibatkan kerja sama antara <b>Superadmin (Pemilik)</b>, <b>Admin (Staff)</b>, dan <b>Sistem</b> guna memastikan kesesuaian antara jumlah barang riil di rak toko dan gudang dengan catatan persediaan di sistem.
                </p>
                <p>
                    Proses dimulai ketika Superadmin membuka menu Stok Opname dan menekan tombol <b>"Mulai Sesi Stok Opname"</b>. Sistem seketika mengaktifkan sesi pemeriksaan dan memberlakukan penguncian operasional sementara (<i>Freeze Lock</i>) pada terminal kasir toko dan fitur belanja daring, sehingga tidak ada transaksi masuk atau keluar yang dapat mengubah jumlah persediaan selama proses penghitungan berlangsung. Bersamaan dengan itu, sistem menyediakan lembar formulir penghitungan fisik yang sengaja tidak menampilkan angka stok sistem, agar staf dapat menghitung barang secara mandiri dan objektif.
                </p>
                <p>
                    Selanjutnya, Admin melakukan penghitungan fisik barang secara langsung di area etalase dan gudang, memasukkan hasil hitungan ke dalam formulir di sistem, lalu menyimpan data hitungan setiap produk. Sistem secara otomatis menghitung selisih antara hasil hitungan fisik dengan catatan stok di sistem. Alur kemudian mengevaluasi kondisi apakah terdapat selisih kurang atau lebih: apabila ditemukan selisih, Admin memilih atau memasukkan identitas barang yang selisih (Nomor Batch obat atau Nomor Seri alkes) serta memberikan keterangan alasan terjadinya selisih, kemudian sistem mencatat penyesuaian tersebut ke dalam riwayat Kartu Stok sebelum sesi ditutup. Sebaliknya, apabila seluruh barang sudah sesuai dan tidak ada selisih, Admin langsung melakukan penutupan sesi opname.
                </p>
                <p>
                    Setelah sesi ditutup, sistem mencabut status penguncian sementara sehingga layanan kasir toko fisik dan belanja daring dapat kembali melayani transaksi secara normal. Alur diakhiri dengan sistem menerbitkan dokumen resmi <b>Berita Acara Stok Opname</b> berformat PDF sebagai bukti pertanggungjawaban hasil pemeriksaan fisik persediaan.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai implementasi <code>inventori/stok_opname.php</code>, penguncian kasir &amp; checkout daring, form penghitungan fisik objektif, dan penerbitan Berita Acara Stok Opname (PDF).
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>"""

new_tab_h = """        <!-- ================= H. KELOLA STOK OPNAME ================= -->
        <div id="tab_h" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.17 Activity Diagram Kelola Stok Opname (Freeze Lock, Input Fisik 3 Kondisi, Verifikasi Selisih &amp; Berita Acara)</h2>
                        <p>Swimlane: Superadmin (Pemilik) &bull; Kasir (Pencacah Lapangan) &bull; Admin Toko (Verifikator Anomali) &bull; Sistem Server</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.17_Activity_Stok_Opname.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.17_Activity_Stok_Opname.svg" alt="Activity Diagram Kelola Stok Opname">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.H)</h3>
                <p>
                    Diagram aktivitas Kelola Stok Opname memetakan prosedur pemeriksaan fisik persediaan secara berkala yang menerapkan prinsip pemisahan wewenang operasional (<i>segregation of duties</i>) antara <b>Superadmin (Pemilik)</b>, <b>Kasir (Pencacah Lapangan)</b>, <b>Admin Toko (Verifikator Anomali)</b>, dan <b>Sistem Server</b> guna memastikan keabsahan dan objektivitas data persediaan riil di rak etalase dan gudang dengan catatan pembukuan sistem.
                </p>
                <p>
                    Proses dimulai ketika Superadmin membuka menu Stok Opname dan menekan tombol <b>"Mulai Sesi Stok Opname Baru"</b>. Sistem seketika mengaktifkan sesi pemeriksaan, mengambil snapshot 111 entitas fisik, dan memberlakukan penguncian operasional sementara (<i>Freeze Lock</i>) pada terminal kasir POS dan fitur belanja daring, sehingga tidak ada transaksi masuk atau keluar yang dapat mengubah saldo persediaan selama proses pemeriksaan fisik berlangsung. Saat kasir masuk ke sistem, akses POS otomatis dialihkan ke antarmuka <b>Input Fisik Stok Opname (Blind Count)</b> tanpa menampilkan angka stok sistem maupun jumlah pesanan yang direservasi. Kasir melakukan pencacahan fisik rak secara objektif dengan memasukkan kuantitas ke dalam 3 kolom kondisi fisik: <b>Kondisi Baik</b>, <b>Kondisi Rusak</b>, dan <b>Kondisi Expired</b> (batch yang telah melewati tanggal kedaluwarsa otomatis terkunci pada kolom Baik). Data hitungan fisik dapat disimpan secara berkala (parsial) ke pangkalan data.
                </p>
                <p>
                    Setelah seluruh baris selesai dihitung oleh tim kasir, Admin Toko membuka tahap <b>Verifikasi Selisih</b>. Pada tahap ini, sistem secara otomatis menyaring tampilan: sebanyak 105 barang yang cocok (0 selisih) disembunyikan agar Admin langsung fokus pada barang yang berselisih. Admin mengevaluasi selisih fisik dan mengeksekusi mutasi penyesuaian melalui dialog konfirmasi kategori mutasi (Barang Rusak, Barang Kedaluwarsa, Barang Hilang, atau Kelebihan Kirim Supplier dengan beban kerugian Rp 0) serta memilih nomor seri fisik yang terdampak pada komoditas Alkes SN. Sistem mencatatkan mutasi tersebut secara otomatis ke dalam buku besar Kartu Stok dan memperbarui status unit serial.
                </p>
                <p>
                    Setelah seluruh anomali selesai direkonsiliasi, Superadmin menekan tombol <b>"Selesaikan Sesi (Buka Kunci Server)"</b> untuk resmi mengakhiri sesi stok opname. Sistem mencabut status penguncian sementara sehingga transaksi kasir POS dan pemesanan daring kembali aktif melayani pembeli. Alur diakhiri dengan penerbitan dokumen resmi <b>Berita Acara Hasil Pemeriksaan Fisik (Stok Opname)</b> berformat formal yang menampilkan seluruh 111 barang dengan bingkai merah tegas pada baris-baris yang mengalami selisih fisik beserta lembar tanda tangan pengesahan oleh Pengelola Stok dan Pemilik Toko.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai implementasi <code>inventori/stok_opname.php</code>, pemisahan peran 4 swimlanes, penguncian kasir &amp; checkout daring (Freeze Lock), input fisik 3 kondisi mandiri (Strict Blind Count), filter terfokus baris anomali verifikasi selisih, dan penerbitan Berita Acara Stok Opname resmi bertandatangan.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>"""

if old_tab_h_pattern in c:
    c = c.replace(old_tab_h_pattern, new_tab_h)
    print("Replaced tab_h successfully!")
else:
    print("Warning: old_tab_h_pattern not found directly, using regex replace...")
    import re
    c = re.sub(r'<!-- ================= H\. KELOLA STOK OPNAME ================= -->[\s\S]*?<div id="tab_h"[\s\S]*?</div>\s*</div>', new_tab_h, c)
    print("Replaced with regex!")

with open('lain/semua_activity_diagram_zencare.html', 'w', encoding='utf-8') as f:
    f.write(c)

print("Saved lain/semua_activity_diagram_zencare.html")

