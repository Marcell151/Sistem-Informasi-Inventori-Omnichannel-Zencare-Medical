import re
from build_subbab_3_5 import tables_data_3_5

# Read BAB_III_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Locate Gambar 3.17 block end
marker = '<p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.17</b> Activity Diagram Kelola Stok Opname &amp; Audit Fisik (Kunci Kasir &amp; Berita Acara)</p>\n                        </div>\n                    </div>\n                </div>\n            </div>'

idx_sub_3_3_end = html.find(marker)
if idx_sub_3_3_end == -1:
    # Try more flexible match
    idx_gbr = html.find('Gambar 3.17</b> Activity Diagram Kelola Stok Opname')
    if idx_gbr == -1:
        print('Error: Gambar 3.17 not found!')
        exit(1)
    # Find closing divs after Gambar 3.17
    close_divs = '</div>\n                    </div>\n                </div>\n            </div>'
    idx_close = html.find(close_divs, idx_gbr)
    if idx_close == -1:
        print('Error: closing divs after Gambar 3.17 not found!')
        exit(1)
    idx_sub_3_3_end = idx_close + len(close_divs)
else:
    idx_sub_3_3_end += len(marker)

print('Found end of Subbab 3.3 at index:', idx_sub_3_3_end)

# Locate Bottom Notification
idx_bottom = html.find('<!-- Bottom Notification (No Print) -->')
if idx_bottom == -1:
    print('Error: Bottom Notification not found!')
    exit(1)

print('Found bottom notification at index:', idx_bottom)

# Assemble Subbab 3.4 and Subbab 3.5 in pure academic paper style
paper_parts = []

# Subbab 3.4 Logical Database
paper_parts.append('''

        <!-- 3.4 Logical Database -->
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
''')

# Subbab 3.5 Kamus Data
paper_parts.append('''
        <!-- 3.5 Kamus Data -->
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
''')

# 20 Tables in Subbab 3.5
for t in tables_data_3_5:
    sub_title = f"{t['sub_no']} Tabel {t['name']}"
    tbl_title = f"Tabel {t['table_no']} Tabel {t['name']}"
    
    paper_parts.append(f'''
                <!-- {sub_title} -->
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
''')
    for idx, (col_name, col_type, col_len, col_desc) in enumerate(t['cols'], 1):
        is_pk = (col_name == t['pk'])
        is_fk = (col_name in t['fk'])
        badge = ' <span class="text-red-700 font-bold font-sans text-[10px]">(PK)</span>' if is_pk else (' <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span>' if is_fk else '')
        
        paper_parts.append(f'''                            <tr>
                                <td class="border border-black px-2 py-1 text-center font-bold">{idx}</td>
                                <td class="border border-black px-2 py-1 font-mono font-semibold">{col_name}{badge}</td>
                                <td class="border border-black px-2 py-1">{col_type}</td>
                                <td class="border border-black px-2 py-1 text-center">{col_len}</td>
                                <td class="border border-black px-2 py-1">{col_desc}</td>
                            </tr>
''')
    paper_parts.append('''                        </tbody>
                    </table>
                </div>
''')

paper_parts.append('''            </div>
        </div>

    </div> <!-- Closes full_bab3_content paper -->
</div> <!-- Closes outer container -->
''')

final_paper_html = ''.join(paper_parts)

# Construct updated BAB_III_ZENCARE_MEDICAL.html
new_html = html[:idx_sub_3_3_end] + final_paper_html + '\n\n' + html[idx_bottom:]

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_html)

print('Successfully re-injected Subbab 3.4 and Subbab 3.5 INSIDE full_bab3_content paper!')

