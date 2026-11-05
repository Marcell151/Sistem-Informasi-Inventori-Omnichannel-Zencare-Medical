import re
from build_full_subbab_3_4 import tables_dict

# Build HTML for Kamus Data
kamus_data_html = []
kamus_data_html.append('''
                    <!-- 3.4.1 Kamus Data (Data Dictionary) -->
                    <div class="mt-8 mb-6" id="sec_3_4_1">
                        <h4 class="text-base font-bold mb-3">3.4.1 Kamus Data (Data Dictionary)</h4>
                        <p class="text-justify indent-8 mb-4">
                            Kamus data disusun untuk mendokumentasikan struktur kunci utama (*Primary Key*), keterkaitan kunci tamu (*Foreign Key*), serta spesifikasi atribut kolom pada ke-20 entitas tabel basis data sistem. Rincian kamus data dikelompokkan ke dalam 5 domain fungsional sebagai berikut:
                        </p>
''')

# Domains mapping for visual sub-headings
domain_groups = {
    'Domain I: Master Pengguna dan Pengaturan Portal': ['users', 'pengaturan_web'],
    'Domain II: Master Katalog Produk Medis dan Multi-Satuan': ['supplier', 'produk_induk', 'produk_variasi'],
    'Domain III: Persediaan Fisik, FEFO, dan Garansi Alat Kesehatan': ['stok_toko', 'stok_batch', 'unit_serial'],
    'Domain IV: Transaksi Penjualan Omnichannel dan Logistik Pasokan Masuk': ['penerimaan_stok', 'penerimaan_detail', 'penjualan', 'detail_penjualan', 'pesanan_batch_alokasi'],
    'Domain V: Pengendalian Mutu, Audit Kartu Stok, dan Sesi Stok Opname': ['kartu_stok', 'histori_konversi', 'log_anomali_fefo', 'sesi_opname', 'detail_opname_batch', 'detail_opname_sn', 'riwayat_hitung_opname']
}

t_map = {t['name']: t for t in tables_dict}

for group_title, t_names in domain_groups.items():
    kamus_data_html.append(f'''
                        <!-- Group: {group_title} -->
                        <div class="mt-6 mb-4">
                            <h5 class="text-sm font-bold text-slate-800 bg-slate-100 px-3 py-1.5 rounded border-l-4 border-blue-600 mb-3">{group_title}</h5>
''')
    for t_name in t_names:
        t = t_map[t_name]
        kamus_data_html.append(f'''
                            <!-- Tabel {t['table_no']} Tabel {t['name']} -->
                            <div class="mb-5 bg-white border border-slate-200 rounded p-3.5 shadow-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <h6 class="text-sm font-bold text-blue-900 font-mono">Tabel {t['table_no']} Tabel {t['name']}</h6>
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded">{t['domain']}</span>
                                </div>
                                <div class="text-xs text-slate-700 space-y-1 mb-3">
                                    <p><b>Kunci Utama (Primary Key):</b> <code class="bg-red-50 text-red-700 px-1.5 py-0.5 rounded font-mono font-bold">{t['pk']}</code></p>
                                    <p><b>Kunci Tamu (Foreign Key):</b> <code class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-mono">{t['fk']}</code></p>
                                    <p class="text-justify"><b>Fungsi Operasional:</b> {t['fungsi']}</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs border-collapse border border-slate-300 font-sans">
                                        <thead class="bg-slate-50 text-slate-700 font-semibold">
                                            <tr>
                                                <th class="border border-slate-300 px-2.5 py-1 text-center w-10">No</th>
                                                <th class="border border-slate-300 px-2.5 py-1 text-left w-48">Nama Kolom (Field)</th>
                                                <th class="border border-slate-300 px-2.5 py-1 text-left w-36">Tipe Data</th>
                                                <th class="border border-slate-300 px-2.5 py-1 text-left">Keterangan / Deskripsi Operasional</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-slate-700">
''')
        for idx, (col_name, col_type, col_desc) in enumerate(t['cols'], 1):
            is_pk = (col_name == t['pk'])
            is_fk = (col_name in t['fk'])
            row_bg = 'bg-red-50/30' if is_pk else ('bg-blue-50/20' if is_fk else ('bg-slate-50/40' if idx % 2 == 0 else 'bg-white'))
            badge = '<span class="text-[9px] bg-red-600 text-white font-bold px-1 py-0.5 rounded ml-1">PK</span>' if is_pk else ('<span class="text-[9px] bg-blue-600 text-white font-bold px-1 py-0.5 rounded ml-1">FK</span>' if is_fk else '')
            kamus_data_html.append(f'''                                            <tr class="hover:bg-blue-50/40 {row_bg}">
                                                <td class="border border-slate-300 px-2.5 py-1 text-center font-bold">{idx}</td>
                                                <td class="border border-slate-300 px-2.5 py-1 font-mono font-semibold text-slate-900">{col_name}{badge}</td>
                                                <td class="border border-slate-300 px-2.5 py-1 font-mono text-slate-600">{col_type}</td>
                                                <td class="border border-slate-300 px-2.5 py-1">{col_desc}</td>
                                            </tr>
''')
        kamus_data_html.append('''                                        </tbody>
                                    </table>
                                </div>
                            </div>
''')
    kamus_data_html.append('                        </div>\n')

kamus_data_html.append('                    </div>\n')
full_kamus_html = ''.join(kamus_data_html)

# Now assemble full Subbab 3.4 content
narrative_html = '''            <!-- 3.4 Logical Database -->
            <div class="mb-6 relative group" id="sec_3_4_wrapper">
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
                        class="text-xs text-blue-600 hover:text-blue-800 border border-slate-200 bg-slate-50 px-2 py-1 rounded transition">
                        Salin Subbab 3.4
                    </button>
                </div>
                <div id="sec_3_4">
                    <h3 class="text-base font-bold mb-3">3.4 Logical Database</h3>
                    <p class="text-justify indent-8 mb-4">
                        <i>Logical Data Model</i> (LDM) pada Sistem Informasi Inventori Omnichannel ZenCare Medical dirancang menggunakan basis data relasional yang memetakan <b>20 entitas tabel</b> terintegrasi. Pusat relasi data bertumpu pada tabel <code>produk_variasi</code> yang menjadi poros logistik multi-satuan dan turunan langsung dari <code>produk_induk</code> (katalog informasi dasar obat dan alat kesehatan). Dari <code>produk_variasi</code>, aliran pencatatan fisik persediaan dipisahkan menjadi tiga entitas spesifik: <code>stok_toko</code> untuk akumulasi saldo aktif siap jual, <code>stok_batch</code> untuk sediaan obat berbasis nomor batch dan tanggal kedaluwarsa terdekat (*First Expired First Out* / FEFO), serta <code>unit_serial</code> untuk pelacakan nomor seri unik (*Serial Number*) dan masa berlaku garansi alat kesehatan secara individual.
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

                    <!-- Ringkasan Kamus Data 20 Tabel -->
                    <p class="text-justify indent-8 mb-3">
                        Secara keseluruhan, ke-20 entitas tabel yang membentuk <i>Logical Data Model</i> pada ZenCare Medical dikelompokkan ke dalam 5 (lima) domain fungsional terpadu sebagaimana dirangkum pada Tabel 3.6.
                    </p>

                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-xs border-collapse border border-slate-300 font-sans">
                            <thead class="bg-slate-100 text-slate-800">
                                <tr>
                                    <th class="border border-slate-300 px-3 py-2 text-center w-12">No</th>
                                    <th class="border border-slate-300 px-3 py-2 text-left w-48">Nama Entitas Tabel</th>
                                    <th class="border border-slate-300 px-3 py-2 text-left w-44">Kelompok Domain</th>
                                    <th class="border border-slate-300 px-3 py-2 text-left">Fungsi dan Peran Operasional dalam Sistem</th>
                                </tr>
                            </thead>
                            <tbody class="text-slate-700">
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">1</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">users</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Master Pengguna</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Menyimpan data akun pengguna, kredensial masuk, alamat pengiriman, dan pembagian hak akses (Superadmin, Admin, Kasir, Pelanggan).</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">2</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">supplier</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Master Pemasok</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Direktori data profil distributor rekanan resmi pemasok komoditas obat dan alat kesehatan.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">3</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">produk_induk</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Master Katalog</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Katalog informasi dasar komoditas medis (nama dagang, spesifikasi teknis, kategori, dan batas peringatan kedaluwarsa).</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">4</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">produk_variasi</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Master Logistik (Poros)</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Poros utama logistik persediaan yang mengatur kemasan bertingkat (satuan eceran dan dus), rasio konversi unit, harga jual, dan stok minimum.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">5</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">stok_toko</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Persediaan Fisik</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Menyimpan akumulasi saldo kuantitas persediaan fisik aktif siap jual di toko dan gudang.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">6</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">stok_batch</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Logistik Medis FEFO</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Pengelolaan persediaan sediaan obat berbasis nomor batch dan tanggal kedaluwarsa guna pengeluaran berkaidah FEFO.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">7</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">unit_serial</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Logistik Alat Kesehatan</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Pelacakan nomor seri unik (Serial Number), status fisik individual, dan masa berlaku garansi alat kesehatan.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">8</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">penerimaan_stok</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Transaksi Pasokan Masuk</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Dokumen induk pengadaan barang masuk dari distributor rekanan (nomor faktur, surat jalan, dan tanggal terima fisik).</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">9</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">penerimaan_detail</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Transaksi Pasokan Masuk</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Rincian item barang pasokan yang diterima, harga beli, nomor batch/kedaluwarsa, kuantitas cacat, dan status klaim retur.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">10</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">penjualan</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Transaksi Penjualan</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Dokumen induk transaksi penjualan omnichannel (kasir POS dan pesanan daring e-commerce), metode serah terima, dan pembayaran.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">11</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">detail_penjualan</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Transaksi Penjualan</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Rincian produk terjual per transaksi penjualan, kuantitas barang, harga satuan, dan catatan identitas logistik fisik.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">12</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">pesanan_batch_alokasi</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Penguncian Stok Daring</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Penguncian fisik nomor batch obat untuk pesanan daring lunas agar tidak terjual ganda di kasir fisik sebelum dikirim.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">13</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">kartu_stok</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Buku Besar Mutasi</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Buku besar mutasi persediaan yang merekam setiap transaksi masuk, keluar, dan penyesuaian fisik beserta saldo berjalan.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">14</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">histori_konversi</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Operasional Multi-Satuan</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Rekam jejak pembongkaran kemasan dus besar ke kemasan eceran secara otomatis saat persediaan eceran di rak habis.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">15</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">log_anomali_fefo</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Audit Kepatuhan Kasir</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Rekam jejak pengambilan nomor batch obat oleh kasir yang berbeda dari rekomendasi sistem FEFO untuk keperluan audit.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">16</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">sesi_opname</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Audit Stok Opname</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Induk sesi audit fisik stok opname dan pencatatan waktu pembekuan transaksi operasional sementara (freeze lock).</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">17</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">detail_opname_batch</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Audit Stok Opname</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Lembar kerja pencacahan fisik obat per batch, penampung hasil hitung mandiri staf, dan kalkulasi selisih otomatis.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">18</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">detail_opname_sn</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Audit Stok Opname</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Lembar verifikasi fisik nomor seri individual alat kesehatan yang ditemukan saat pemeriksaan audit di gudang.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">19</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">riwayat_hitung_opname</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Audit Stok Opname</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Rekam jejak riwayat koreksi angka hitung fisik staf selama sesi audit aktif untuk mencegah manipulasi persediaan.</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">20</td>
                                    <td class="border border-slate-300 px-3 py-1.5 font-mono font-bold text-blue-700">pengaturan_web</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Pengaturan Sistem</td>
                                    <td class="border border-slate-300 px-3 py-1.5">Konfigurasi mandiri profil identitas toko, nomor kontak layanan pelanggan, dan elemen visual antarmuka portal publik.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
''' + full_kamus_html + '''
                </div>
            </div>
'''

# Read existing BAB_III_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html_content = f.read()

# Replace between boundary comments
idx_start = html_content.find('<!-- 3.4 Logical Database -->')
idx_end = html_content.find('<!-- Bottom Notification (No Print) -->')
if idx_start != -1 and idx_end != -1:
    new_html = html_content[:idx_start] + narrative_html.strip() + '\n\n' + html_content[idx_end:]
    with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(new_html)
    print("Successfully replaced Subbab 3.4 using boundary indices!")
else:
    print(f"Error: indices not found: start={idx_start}, end={idx_end}")
