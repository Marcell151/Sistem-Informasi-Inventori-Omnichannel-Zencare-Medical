with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

start_marker = '<div id="tab_h"'
end_marker = '<script>'

pos_start = c.find(start_marker)
pos_end = c.find(end_marker, pos_start)

print("pos_start:", pos_start, "pos_end:", pos_end)

new_tab_h_full = """<div id="tab_h" class="tab-panel">
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
        </div>

    </div>

    """

c = c[:pos_start] + new_tab_h_full + c[pos_end:]

# Also clean lines 400 and 595
c = c.replace('laporan audit, dan pengaturan sistem', 'laporan manajerial, dan pengaturan sistem')
c = c.replace('modul laporan manajerial dan audit persediaan', 'modul laporan manajerial dan pemantauan persediaan')

with open('lain/semua_activity_diagram_zencare.html', 'w', encoding='utf-8') as f:
    f.write(c)

print("Successfully replaced tab_h and cleaned audit in lain/semua_activity_diagram_zencare.html")

