import re
import os

# 1. Read canonical UAT HTML
with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    uat_html = f.read()

# Extract the table body
idx_tbl = uat_html.find('id="uat_table"')
idx_tbody = uat_html.find('<tbody>', idx_tbl)
idx_tbody_end = uat_html.find('</tbody>', idx_tbody)
tbody_raw = uat_html[idx_tbody+7 : idx_tbody_end]

# Split by the section header rows
# <tr class="bg-slate-100 font-bold">\s*<td colspan="6"[^>]*>(.*?)</td>\s*</tr>
parts = re.split(r'<tr class="bg-slate-100 font-bold">\s*<td colspan="6"[^>]*>(.*?)</td>\s*</tr>', tbody_raw, flags=re.DOTALL)

# parts[0] is whitespace
# parts[1] is Title 1, parts[2] is Rows 1
# parts[3] is Title 2, parts[4] is Rows 2, etc.

assert len(parts) == 17, f"Expected 17 split parts, got {len(parts)}"

sections_meta = [
    {
        "letter": "A",
        "tbl_num": "4.2",
        "short_title": "Modul Autentikasi Staf dan Kontrol Hak Akses (<i>RBAC</i>)",
        "explanation": "Pengujian pada Bagian I membuktikan bahwa gerbang autentikasi terpusat telah mengimplementasikan mekanisme kontrol akses berbasis peran (<i>Role-Based Access Control</i> / <i>RBAC</i>) secara andal. Validasi kata sandi terenkripsi pada tabel <i>users</i> berhasil memisahkan hak akses staf operasional: Kasir Toko diarahkan langsung ke terminal kasir POS, Admin Toko diarahkan ke pengelolaan logistik dan master data, sedangkan Pemilik Toko (<i>Superadmin</i>) memperoleh wewenang penuh ke <i>dashboard</i> eksekutif. Sistem juga terbukti tangguh menolak manipulasi URL langsung (<i>URL tampering</i>) oleh pengguna tanpa wewenang dan mengarahkannya kembali ke halaman otorisasi. Seluruh 4 skenario pada modul ini tercatat dengan status <strong>BERHASIL</strong> tanpa celah keamanan."
    },
    {
        "letter": "B",
        "tbl_num": "4.3",
        "short_title": "Modul Master Produk Medis, Konfigurasi Multi-Satuan (<i>Multi-UOM</i>), dan Rekanan Pemasok",
        "explanation": "Pengujian pada Bagian II memvalidasi keakuratan struktur master data komoditas medis yang membedakan produk induk (tabel <i>produk</i>) dengan variasi kemasan atau dosisnya (tabel <i>produk_variasi</i>). Mekanisme tabel konversi satuan bertingkat (tabel <i>satuan_konversi</i>) terbukti mampu mengonversi kuantitas kemasan besar ke satuan eceran terkecil (<i>base unit</i>) secara matematis tanpa kesalahan pembulatan desimal. Selain itu, perekaman rekanan pemasok pada tabel <i>suppliers</i> serta notifikasi ambang batas stok minimum (<i>safety stock alert</i>) berfungsi akurat dalam memicu peringatan restok bagi staf pengadaan. Seluruh 5 skenario pada modul ini diselesaikan dengan status <strong>BERHASIL</strong>."
    },
    {
        "letter": "C",
        "tbl_num": "4.4",
        "short_title": "Modul Terminal Kasir Luring (<i>Point of Sale</i> / POS) dan Alokasi <i>FEFO</i>",
        "explanation": "Pengujian pada Bagian III berfokus pada operasional kasir di gerai fisik. Pengujian membuktikan kecepatan pembacaan barcode produk pada keranjang belanja kasir dan keakuratan algoritma <i>FEFO</i> (<i>First Expired First Out</i>) yang secara otomatis mengalokasikan nomor <i>batch</i> dengan tanggal kedaluwarsa paling awal. Mekanisme dialog peringatan anomali <i>FEFO</i> terbukti aktif memperingatkan kasir jika fisik barang yang dipindai memiliki masa kedaluwarsa yang lebih baru daripada stok yang seharusnya dikeluarkan lebih dahulu. Sistem juga secara konsisten menolak transaksi penjualan jika stok fisik tidak mencukupi, memproses pembayaran multi-kanal secara instan, serta mencetak struk nota belanja resmi. Seluruh 5 skenario pada modul ini berstatus <strong>BERHASIL</strong>."
    },
    {
        "letter": "D",
        "tbl_num": "4.5",
        "short_title": "Modul Toko Daring (<i>E-Commerce</i>) dan Sinkronisasi Penjualan <i>Omnichannel</i>",
        "explanation": "Pengujian pada Bagian IV mengevaluasi pengalaman belanja daring pelanggan serta sinkronisasi persediaan lintas kanal. Pelanggan dapat menelusuri katalog obat bebas dan alat kesehatan secara transparan, memilih variasi kemasan eceran, dan memasukkan barang ke keranjang belanja. Sistem secara otomatis mereservasi stok terpesan (<i>reserved stock</i>) pada tabel <i>pesanan_batch_alokasi</i> saat <i>checkout</i> guna mencegah perebutan stok dengan transaksi kasir fisik (<i>race condition</i>). Pelanggan berhasil mengunggah bukti pembayaran yang kemudian diverifikasi oleh Admin Toko, dan saldo persediaan terbukti tersinkronisasi secara seketika (*real-time*) di seluruh saluran. Seluruh 5 skenario pada modul ini berstatus <strong>BERHASIL</strong>."
    },
    {
        "letter": "E",
        "tbl_num": "4.6",
        "short_title": "Modul Logistik Penerimaan Pasokan Barang Masuk dan Penandaan Barcode",
        "explanation": "Pengujian pada Bagian V memvalidasi alur pencatatan pengadaan pasokan barang dari rekanan distributor. Formulir penerimaan terbukti mampu mencatat nomor surat jalan, nomor faktur, tanggal kedaluwarsa (<i>expired date</i>), serta nomor <i>batch</i> baru ke dalam tabel <i>penerimaan_barang</i> dan <i>batch_stok</i>. Sistem secara otomatis mengalikan kuantitas satuan masuk ke dalam saldo unit dasar dan mencetak lembar label stiker barcode produk yang dapat langsung dipindai pada terminal kasir fisik. Seluruh 4 skenario pada modul penerimaan barang ini berstatus <strong>BERHASIL</strong>."
    },
    {
        "letter": "F",
        "tbl_num": "4.7",
        "short_title": "Modul Pengelolaan Mutasi Stok Manual dan Buku Besar Kartu Stok",
        "explanation": "Pengujian pada Bagian VI membuktikan transparansi pengelolaan penyesuaian persediaan di luar jalur transaksi penjualan reguler. Formulir mutasi stok manual mampu merekam pemotongan stok akibat barang rusak kemasan, cacat pabrik, maupun barang kedaluwarsa dengan kewajiban mencantumkan alasan operasional tertulis. Seluruh mutasi secara otomatis dicatat ke dalam buku besar tabel <i>kartu_stok</i> dengan kalkulasi saldo berjalan (<i>running balance</i>) yang presisi per variasi dan per <i>batch</i>, serta tersimpan ke dalam riwayat log penyesuaian. Seluruh 3 skenario pada modul ini tercatat dengan status <strong>BERHASIL</strong>."
    },
    {
        "letter": "G",
        "tbl_num": "4.8",
        "short_title": "Modul Siklus Lengkap Pengawasan Stok Fisik (<i>Stok Opname</i>) Terpusat",
        "explanation": "Pengujian pada Bagian VII memvalidasi siklus pengawasan fisik persediaan multi-aktor dan multi-tahap. Pengujian membuktikan efektivitas mekanisme pembekuan transaksi (<i>Freeze Lock</i>) oleh Pemilik Toko, proses penghitungan fisik mandiri tanpa melihat saldo buku sistem (<i>Blind Count</i>) oleh Kasir Toko, rekonsiliasi dan verifikasi perbedaan kuantitas (<i>surplus/defisit</i>) oleh Admin Toko, serta eksekusi mutasi koreksi penyeimbang persediaan secara otomatis. Setelah verifikasi rampung, sesi resmi ditutup dan diarsipkan secara permanen guna mencegah manipulasi angka historis. Seluruh 6 skenario pada modul ini berstatus <strong>BERHASIL</strong>."
    },
    {
        "letter": "H",
        "tbl_num": "4.9",
        "short_title": "Modul Laporan Manajerial, Pengawasan Kedaluwarsa, dan Dasbor Eksekutif",
        "explanation": "Pengujian pada Bagian VIII menguji keandalan penyajian informasi analitis bagi pengambil keputusan toko. Dasbor eksekutif menyajikan ringkasan omset penjualan harian/bulanan, estimasi nilai aset persediaan, serta peringatan dini barang mendekati kedaluwarsa. Seluruh tabel laporan web (transaksi penjualan, mutasi stok, penerimaan barang, dan arsip stok opname) berhasil difilter berdasarkan rentang tanggal dinamis dan diekspor ke dalam dokumen cetak fisik format A4 standar resmi. Laporan analisis anomali <i>FEFO</i> juga terbukti akurat dalam mendata setiap insiden penyimpangan alur pengeluaran barang di gerai. Seluruh 4 skenario pada modul ini berstatus <strong>BERHASIL</strong>."
    }
]

rendered_sections = []
for idx in range(8):
    raw_title = parts[idx*2 + 1].strip()
    raw_rows = parts[idx*2 + 2].strip()
    meta = sections_meta[idx]
    
    # Clean and style rows
    # Replace plain <td> with academic styling
    # In raw_rows: 
    # <tr>
    #   <td class="text-center font-mono font-bold">UAT-01</td>
    #   <td>...</td>
    #   ...
    # </tr>
    
    # We want consistent border-slate-300 styling
    cleaned_rows = raw_rows
    # Ensure all td have border border-slate-300 px-2 py-1
    cleaned_rows = re.sub(r'<tr>\s*<td class="text-center font-mono font-bold">(UAT-\d+)</td>', r'<tr>\n                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold"><i>\1</i></td>', cleaned_rows)
    cleaned_rows = re.sub(r'<td>(.*?)</td>', r'<td class="border border-slate-300 px-2 py-1">\1</td>', cleaned_rows)
    cleaned_rows = re.sub(r'<td class="text-center status-col font-bold text-emerald-700">(.*?)</td>', r'<td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">\1</td>', cleaned_rows)
    cleaned_rows = re.sub(r'<td class="text-center penguji-col">(.*?)</td>', r'<td class="border border-slate-300 px-2 py-1 text-center">\1</td>', cleaned_rows)
    
    sec_html = f"""                    <!-- {meta['letter']}. {raw_title} -->
                    <div class="mt-6 mb-4">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                            {meta['letter']}. {raw_title}
                        </h5>
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel {meta['tbl_num']} Matriks Hasil Pengujian Penerimaan Pengguna ({meta['short_title']})
                        </p>
                        <div class="overflow-x-auto mb-3">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-12">Kode</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-36">Fitur yang Diuji</th>
                                        <th class="border border-slate-400 px-2 py-1.5">Aktivitas Prosedur Pengujian</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-52">Hasil Pengujian yang Diharapkan</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-20">Status</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-28">Penguji</th>
                                    </tr>
                                </thead>
                                <tbody>
{cleaned_rows}
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            {meta['explanation']}
                        </p>
                    </div>"""
    rendered_sections.append(sec_html)

all_sections_html = '\n\n'.join(rendered_sections)

# Read Likert Table and Discussion
with open(r'scratch\update_subbab_422.py', 'r', encoding='utf-8') as f:
    orig_code = f.read()

# Extract Likert table (Tabel 4.5 in orig_code)
idx_likert_start = orig_code.find('<!-- KUESIONER LIKERT 12 INDIKATOR -->')
idx_likert_end = orig_code.find('Temuan penting dari pelaksanaan pengujian penerimaan pengguna ini mencakup beberapa poin penguatan sistem:')
likert_block = orig_code[idx_likert_start:idx_likert_end]

# Rename Tabel 4.5 to Tabel 4.10
likert_block = likert_block.replace('Tabel 4.5 Hasil Penilaian Kuesioner Penerimaan Pengguna', 'Tabel 4.10 Rekapitulasi Hasil Penilaian Kuesioner Penerimaan Pengguna')

# Extract Formula and Gambar 4.52 and Discussion from current BAB_IV
with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    cur_bab4 = f.read()

idx_formula = cur_bab4.find('Berdasarkan data penilaian pada')
idx_end_sec = cur_bab4.find('<!-- Bottom Notification (No Print) -->')
# We need from formula to end of discussion
# Let's find where the formula starts
idx_calc = cur_bab4.find('Berdasarkan data penilaian pada')
if idx_calc == -1:
    idx_calc = cur_bab4.find('Kalkulasi Persentase Kelayakan:')
calc_and_discussion = cur_bab4[idx_calc:idx_end_sec]

# Clean up trailing closing divs
calc_and_discussion = calc_and_discussion.rstrip()
while calc_and_discussion.endswith('</div>'):
    calc_and_discussion = calc_and_discussion[:-6].rstrip()

# Ensure reference in formula says Tabel 4.10
calc_and_discussion = re.sub(r'Tabel 4\.[35]', 'Tabel 4.10', calc_and_discussion)

# Gambar 4.51
gambar_451 = """                    <!-- GAMBAR 4.51 BLANGKO KOSONG INSTRUMEN UAT -->
                    <div class="my-6 border border-slate-300 rounded p-3 bg-slate-50 text-center">
                        <div class="mb-2 text-center">
                            <img src="../lain/File PPT/UI_UX_Asli/51_Blangko_Instrumen_UAT.png" alt="Blangko Kosong Instrumen UAT Format Resmi" class="mx-auto rounded border border-slate-300 max-h-[500px] object-contain shadow-xs">
                        </div>
                        <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                            Gambar 4.51 Tampilan Lembar Blangko Kosong Instrumen Pengujian Penerimaan Pengguna (<i>UAT</i>) Format Cetak Resmi
                        </p>
                    </div>"""

# Build Table 4.1 (Profil Penguji)
table_41 = """                    <!-- Tabel Profil Responden Penguji -->
                    <div class="my-4">
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel 4.1 Profil Peran Penguji Sistem (<i>UAT Respondents</i>)
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-10">No</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-44">Peran Penguji</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-28">Hak Akses Sistem</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-48">Tanggung Jawab Operasional</th>
                                        <th class="border border-slate-400 px-2 py-1.5">Fokus Evaluasi Fungsionalitas Modul</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">1</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Pemilik Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">superadmin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pimpinan manajerial tertinggi dan pemilik modal usaha toko kesehatan.</td>
                                        <td class="border border-slate-300 px-2 py-1">Dashboard eksekutif, inisiasi dan penutupan <i>stok opname</i>, pembatalan darurat, penetapan hak akses, rekapitulasi penjualan.</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">2</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Admin Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">admin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pengelolaan persediaan harian, logistik pasokan, konversi kemasan, dan verifikasi opname.</td>
                                        <td class="border border-slate-300 px-2 py-1">Master produk &amp; multi-UOM, penerimaan barang masuk, histori konversi satuan, verifikasi selisih opname, kartu stok, laporan persediaan.</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">3</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Kasir Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">kasir</td>
                                        <td class="border border-slate-300 px-2 py-1">Pelayanan transaksi penjualan luring di meja kasir dan pencacahan stok fisik rak etalase.</td>
                                        <td class="border border-slate-300 px-2 py-1">Terminal kasir POS luring, pemindaian <i>barcode</i>, alokasi stok <i>FEFO</i> otomatis, cetak struk termal, pencacahan fisik rak (<i>Blind Count</i>).</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">4</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Pelanggan Toko Daring</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">pelanggan</td>
                                        <td class="border border-slate-300 px-2 py-1">Pengguna eksternal konsumen toko online kesehatan ZenCare Medical.</td>
                                        <td class="border border-slate-300 px-2 py-1">Katalog produk daring, pencarian obat/alkes, keranjang belanja, proses <i>checkout</i> pesanan, opsi pengiriman kurir logistik / ambil langsung di toko.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>"""

# Build Complete 4.2
complete_42 = f"""        <!-- 4.2 PENGUJIAN SISTEM (UAT) -->
        <div id="subbab_42" class="mt-8 pt-6 border-t border-slate-300">
            <div class="flex items-center justify-between border-b pb-2 mb-4">
                <h3 class="text-sm font-bold font-serif text-slate-900 uppercase tracking-wide">
                    4.2 PENGUJIAN SISTEM DENGAN METODE <i>USER ACCEPTANCE TESTING</i> (<i>UAT</i>)
                </h3>
                <button onclick="copyText('subbab_42_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    <span>Salin Subbab 4.2</span>
                </button>
            </div>

            <div id="subbab_42_content">
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pengujian sistem merupakan tahapan krusial dalam rekayasa perangkat lunak guna membuktikan bahwa Sistem Informasi Inventori dan Penjualan <em>Omnichannel</em> ZenCare Medical telah memenuhi seluruh spesifikasi kebutuhan pengguna (<em>user requirements</em>) dan siap dioperasikan dalam lingkungan bisnis nyata. Dalam penelitian ini, pengujian sistem difokuskan secara komprehensif menggunakan metode Pengujian Penerimaan Pengguna (<em>User Acceptance Testing</em> / <i>UAT</i>). Pengujian ini berfokus langsung pada pembuktian kesesuaian alur kerja operasional nyata, kemudahan antarmuka (<em>usability</em>), ketepatan logika penanganan persediaan medis berbasis <i>FEFO</i>, keandalan mekanisme pembekuan stok opname (<em>freeze lock</em>), dan rekonsiliasi data persediaan lintas kanal.
                </p>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pengujian dilaksanakan langsung di gerai operasional toko kesehatan ZenCare Medical pada tanggal 12 November 2026 dengan melibatkan 4 (empat) peran operasional yang mencakup seluruh tingkatan wewenang sistem: <strong>Pemilik Toko</strong> (pemegang hak akses <em>superadmin</em>), <strong>Admin Toko</strong> (pemegang hak akses <em>admin</em> logistik dan pengadaan), <strong>Kasir Toko</strong> (pemegang hak akses <em>kasir</em> penjualan POS dan pencacahan fisik), serta <strong>Pelanggan Toko Daring</strong> (pengguna etalase belanja daring). Rincian tanggung jawab dan lingkup pengujian tiap peran dirangkum pada Tabel 4.1.
                </p>

{table_41}

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pelaksanaan pengujian penerimaan pengguna dirinci ke dalam 8 (delapan) bagian modul fungsional yang merepresentasikan seluruh modul operasional sistem. Setiap bagian diuji melalui skenario spesifik, disajikan dalam bentuk matriks hasil pengujian, dan dievaluasi secara mendalam pada uraian berikut:
                </p>

{all_sections_html}

                    <!-- I. REKAPITULASI HASIL PENGUJIAN DAN EVALUASI KELAYAKAN PENGGUNA -->
                    <div class="mt-8 pt-4 border-t border-slate-300">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-3">
                            I. Rekapitulasi Hasil Pengujian dan Evaluasi Kelayakan Pengguna
                        </h5>

{gambar_451}

{likert_block}

{calc_and_discussion}
                    </div>

            </div>
        </div>
"""

# Apply to BAB_IV_ZENCARE_MEDICAL.html
idx_start_bab4 = cur_bab4.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_start_bab4 == -1:
    idx_start_bab4 = cur_bab4.find('<div id="subbab_42"')
assert idx_start_bab4 != -1

idx_end_bab4 = cur_bab4.find('<!-- Bottom Notification (No Print) -->')
assert idx_end_bab4 != -1

updated_bab4 = cur_bab4[:idx_start_bab4] + complete_42 + "\n    " + cur_bab4[idx_end_bab4:]

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_bab4)
print("Saved cleanly to laporan_ta\\BAB_IV_ZENCARE_MEDICAL.html")

# Apply to BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    cur_comb = f.read()

idx_start_comb = cur_comb.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_start_comb == -1:
    idx_start_comb = cur_comb.find('<div id="subbab_42"')
assert idx_start_comb != -1

idx_end_comb = cur_comb.find('<!-- Bottom Notification (No Print) -->')
assert idx_end_comb != -1

updated_comb = cur_comb[:idx_start_comb] + complete_42 + "\n    " + cur_comb[idx_end_comb:]

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_comb)
print("Saved cleanly to laporan_ta\\BAB_III_DAN_IV_ZENCARE_MEDICAL.html")

