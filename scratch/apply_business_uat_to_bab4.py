import re
import os
from build_business_uat import uat_modules

# 1. Build the HTML blocks for Sections A through H
sections_html = []
for m in uat_modules:
    tbl_rows = []
    for s in m['scenarios']:
        tr = f"""                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold"><i>{s['code']}</i></td>
                                        <td class="border border-slate-300 px-2 py-1 font-medium">{s['req']}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-slate-700">{s['crit']}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">{s['status']}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center text-xs">{s['role']}</td>
                                    </tr>"""
        tbl_rows.append(tr)
    
    rows_joined = '\n'.join(tbl_rows)
    
    sec_block = f"""                    <!-- {m['letter']}. {m['roman']}: {m['title']} -->
                    <div class="mt-6 mb-4">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                            {m['letter']}. {m['roman']}: {m['title']}
                        </h5>
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel {m['tbl_num']} Matriks {m['tbl_title']}
                        </p>
                        <div class="overflow-x-auto mb-3">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-14">Kode</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-72">Kebutuhan Pengguna / Alur Bisnis</th>
                                        <th class="border border-slate-400 px-2 py-1.5">Kriteria Penerimaan Sistem</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-20">Status</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-28">Penguji</th>
                                    </tr>
                                </thead>
                                <tbody>
{rows_joined}
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            {m['explanation']}
                        </p>
                    </div>"""
    sections_html.append(sec_block)

all_sections_str = '\n\n'.join(sections_html)

# 2. Table 4.1 Profil Peran Penguji
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
                                        <th class="border border-slate-400 px-2 py-1.5">Fokus Evaluasi Kebutuhan Pengguna</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">1</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Pemilik Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">superadmin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pimpinan manajerial tertinggi dan pengambil keputusan usaha.</td>
                                        <td class="border border-slate-300 px-2 py-1">Dashboard eksekutif, pembekuan dan penutupan <i>stok opname</i>, kontrol hak akses staf, dan laporan manajerial.</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">2</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Admin Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">admin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pengelolaan persediaan, penerimaan logistik, dan verifikasi opname.</td>
                                        <td class="border border-slate-300 px-2 py-1">Master produk &amp; multi-UOM, penerimaan pasokan, konversi kemasan, verifikasi selisih fisik, dan kartu stok.</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">3</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Kasir Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">kasir</td>
                                        <td class="border border-slate-300 px-2 py-1">Pelayanan transaksi meja kasir dan pencacahan fisik rak etalase.</td>
                                        <td class="border border-slate-300 px-2 py-1">Terminal kasir POS, pemindaian <i>barcode</i>, alokasi <i>FEFO</i> otomatis, cetak struk nota, pencacahan fisik (<i>Blind Count</i>).</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">4</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Pelanggan Toko Daring</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">pelanggan</td>
                                        <td class="border border-slate-300 px-2 py-1">Konsumen pembeli produk kesehatan pada etalase toko <i>online</i>.</td>
                                        <td class="border border-slate-300 px-2 py-1">Katalog daring, keranjang belanja, proses <i>checkout</i>, upload bukti transfer bayar, dan kejelasan informasi variasi produk.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>"""

# 3. Section I: Rekapitulasi Hasil Pengujian dan Evaluasi Kelayakan Pengguna
gambar_451 = """                    <!-- GAMBAR 4.51 BLANGKO KOSONG INSTRUMEN UAT -->
                    <div class="my-6 border border-slate-300 rounded p-3 bg-slate-50 text-center">
                        <div class="mb-2 text-center">
                            <img src="../lain/File PPT/UI_UX_Asli/51_Blangko_Instrumen_UAT.png" alt="Blangko Kosong Instrumen UAT Format Resmi" class="mx-auto rounded border border-slate-300 max-h-[500px] object-contain shadow-xs">
                        </div>
                        <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                            Gambar 4.51 Tampilan Lembar Blangko Kosong Instrumen Pengujian Penerimaan Pengguna (<i>UAT</i>) Format Cetak Resmi
                        </p>
                    </div>"""

# Read Likert Table and Discussion from BAB_IV
with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_cur = f.read()

idx_likert_start = text_cur.find('<p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">\n                        Pengukuran tingkat kepuasan dan kelayakan')
if idx_likert_start == -1:
    idx_likert_start = text_cur.find('Pengukuran tingkat kepuasan dan kelayakan operasional sistem diuji')
    idx_likert_start = text_cur.rfind('<p class="text-xs font-serif text-slate-800', 0, idx_likert_start)

idx_bottom_notification = text_cur.find('<!-- Bottom Notification (No Print) -->')
likert_and_discussion = text_cur[idx_likert_start:idx_bottom_notification].rstrip()
while likert_and_discussion.endswith('</div>'):
    likert_and_discussion = likert_and_discussion[:-6].rstrip()

# Ensure references say 26 butir skenario
likert_and_discussion = likert_and_discussion.replace('36 skenario', '26 butir skenario')
likert_and_discussion = likert_and_discussion.replace('36 (tiga puluh enam) skenario', '26 (dua puluh enam) butir skenario')

# Complete 4.2
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
                    Pengujian sistem merupakan tahapan krusial dalam rekayasa perangkat lunak guna membuktikan bahwa Sistem Informasi Inventori dan Penjualan <em>Omnichannel</em> ZenCare Medical telah memenuhi kebutuhan operasional pengguna (<em>user requirements</em>) dan siap diterapkan dalam lingkungan bisnis nyata. Berbeda dengan pengujian fungsionalitas kode internal, pengujian penerimaan pengguna (<em>User Acceptance Testing</em> / <i>UAT</i>) berfokus langsung pada sudut pandang pengguna akhir (<em>end-user</em>) dan proses bisnis toko: menguji kemudahan penggunaan, kesesuaian alur kerja pelayanan obat dan alat kesehatan, ketepatan logika penanganan persediaan berbasis <i>FEFO</i>, keandalan pengawasan stok fisik, serta sinkronisasi penjualan lintas kanal.
                </p>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pengujian diselenggarakan langsung di gerai operasional toko kesehatan ZenCare Medical pada tanggal 12 November 2026 dengan melibatkan 4 (empat) peran pengguna yang merepresentasikan seluruh tingkatan operasional usaha: <strong>Pemilik Toko</strong> (pemegang hak akses <em>superadmin</em>), <strong>Admin Toko</strong> (pemegang hak akses <em>admin</em> logistik dan pengadaan), <strong>Kasir Toko</strong> (pemegang hak akses <em>kasir</em> meja penjualan dan pencacahan fisik), serta <strong>Pelanggan Toko Daring</strong> (konsumen pengguna etalase belanja daring). Rincian tanggung jawab dan lingkup evaluasi tiap peran dirangkum pada Tabel 4.1.
                </p>

{table_41}

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Evaluasi kebutuhan pengguna dirinci ke dalam 8 (delapan) bagian modul operasional sistem yang mencakup 26 (dua puluh enam) butir skenario alur kerja nyata. Setiap bagian disajikan secara terfokus dalam bentuk matriks evaluasi kebutuhan beserta penjelasan analisisnya pada uraian berikut:
                </p>

{all_sections_str}

                    <!-- I. REKAPITULASI HASIL PENGUJIAN DAN EVALUASI KELAYAKAN PENGGUNA -->
                    <div class="mt-8 pt-4 border-t border-slate-300">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-3">
                            I. Rekapitulasi Hasil Pengujian dan Evaluasi Kelayakan Pengguna
                        </h5>

{gambar_451}

{likert_and_discussion}
                    </div>

            </div>
        </div>
"""

# Apply to BAB_IV_ZENCARE_MEDICAL.html
idx_42_start_b4 = text_cur.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_42_start_b4 == -1:
    idx_42_start_b4 = text_cur.find('<div id="subbab_42"')
assert idx_42_start_b4 != -1

updated_b4 = text_cur[:idx_42_start_b4] + complete_42 + "\n    " + text_cur[idx_bottom_notification:]

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_b4)
print("Updated laporan_ta\\BAB_IV_ZENCARE_MEDICAL.html with clean business UAT!")

# Apply to BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_comb = f.read()

idx_42_start_comb = text_comb.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_42_start_comb == -1:
    idx_42_start_comb = text_comb.find('<div id="subbab_42"')
assert idx_42_start_comb != -1

idx_bottom_comb = text_comb.find('<!-- Bottom Notification (No Print) -->')
assert idx_bottom_comb != -1

updated_comb = text_comb[:idx_42_start_comb] + complete_42 + "\n    " + text_comb[idx_bottom_comb:]

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_comb)
print("Updated laporan_ta\\BAB_III_DAN_IV_ZENCARE_MEDICAL.html with clean business UAT!")

