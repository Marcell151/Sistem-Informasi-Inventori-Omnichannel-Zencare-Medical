import re
import os
import subprocess
import tempfile
import shutil

# 1. Metadata for the 8 sections
parts_meta = [
    {
        "letter": "A", "roman": "Bagian I",
        "title": "Modul Autentikasi Staf dan Kontrol Hak Akses (<i>RBAC</i>)",
        "tbl_num": "4.2",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian I: Autentikasi dan Hak Akses (<i>RBAC</i>)",
        "explanation": "Evaluasi pada Bagian I menunjukkan bahwa mekanisme keamanan satu pintu telah memenuhi kebutuhan operasional toko dengan sangat baik (skor capaian 95,0%). Pemilik Toko dan Admin Toko memberikan nilai maksimal (5) atas ketegasan sistem dalam mengarahkan staf langsung ke antarmuka yang sesuai dengan tugas pokoknya. Kasir Toko memberikan nilai 4 pada aspek proteksi navigasi URL karena memerlukan waktu penyesuaian saat pertama kali memahami batasan menu gerai. Secara keseluruhan, modul ini terbukti andal dalam menjaga kerahasiaan data usaha dari akses staf yang tidak berwenang."
    },
    {
        "letter": "B", "roman": "Bagian II",
        "title": "Modul Master Produk Medis, Konfigurasi Multi-Satuan (<i>Multi-UOM</i>), dan Pemasok",
        "tbl_num": "4.3",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian II: Master Data Produk dan Multi-Satuan",
        "explanation": "Evaluasi pada Bagian II mencatatkan tingkat kelayakan 92,5%. Struktur data yang membedakan produk induk dengan variasi kemasan dosis dinilai sangat fleksibel oleh Admin Toko dan Pelanggan dalam mengelola berbagai macam sediaan obat dan alat kesehatan. Kasir Toko dan Pelanggan memberikan nilai 4 pada konversi multi-satuan karena struktur kemasan bertingkat menuntut ketelitian saat meninjau satuan terkecil. Kendati demikian, seluruh responden sepakat bahwa otomasi konversi satuan berhasil meniadakan kesalahan hitung manual antara kemasan box dan strip."
    },
    {
        "letter": "C", "roman": "Bagian III",
        "title": "Modul Transaksi Penjualan Kasir POS dan Alokasi <i>FEFO</i>",
        "tbl_num": "4.4",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian III: Transaksi Kasir POS dan Logika <i>FEFO</i>",
        "explanation": "Evaluasi pada Bagian III menghasilkan skor capaian 95,0%. Kasir Toko sangat mengapresiasi kecepatan pemindaian barcode produk yang secara instan menghitung subtotal belanja dan uang kembalian. Fitur otomasi pengeluaran obat kedaluwarsa terdekat (<i>FEFO</i>) dinilai Pemilik Toko sangat efektif memangkas risiko kerugian barang kedaluwarsa. Kasir memberikan nilai 4 pada dialog peringatan anomali FEFO karena kewajiban mengisi alasan memperlambat sedikit tempo antrean, namun dipahami sebagai langkah proteksi mutlak demi keamanan pasien."
    },
    {
        "letter": "D", "roman": "Bagian IV",
        "title": "Modul Toko Daring (<i>E-Commerce</i>) dan Sinkronisasi Penjualan <i>Omnichannel</i>",
        "tbl_num": "4.5",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian IV: Toko Daring dan Sinkronisasi Penjualan",
        "explanation": "Evaluasi pada Bagian IV memperoleh tingkat kelayakan 92,5%. Konsumen menyatakan antarmuka etalase daring bersih dan memudahkan pemesanan obat bebas serta alat kesehatan mandiri. Admin Toko dan Kasir Toko memberikan nilai 4 pada alur belanja daring karena proses verifikasi bukti transfer manual memerlukan perhatian saat toko fisik sedang ramai. Namun, mekanisme reservasi stok daring (<i>reserved stock</i>) dinilai luar biasa karena terbukti mencegah terjadinya penjualan ganda (<i>race condition</i>) persediaan dengan meja kasir gerai fisik."
    },
    {
        "letter": "E", "roman": "Bagian V",
        "title": "Modul Logistik Penerimaan Pasokan Barang Masuk dan Penandaan Barcode",
        "tbl_num": "4.6",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian V: Penerimaan Pasokan Barang dan Barcode",
        "explanation": "Evaluasi pada Bagian V mencatatkan persentase capaian 95,0%. Admin Toko menilai formulir penerimaan barang sangat informatif dalam mencatat nomor surat jalan, tanggal kedaluwarsa, dan nomor <i>batch</i> pasokan distributor. Pelacakan nomor seri unik (<i>serial number</i>) alat kesehatan juga dinilai Pemilik Toko sangat penting guna memastikan keabsahan klaim garansi produk. Kasir memberikan nilai 4 pada stiker barcode karena memerlukan keselarasan posisi cetak, namun hasil pemindaian barcode di terminal kasir terbukti tajam dan terbaca akurat."
    },
    {
        "letter": "F", "roman": "Bagian VI",
        "title": "Modul Pengelolaan Mutasi Stok Manual dan Buku Besar Kartu Stok",
        "tbl_num": "4.7",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian VI: Mutasi Stok Manual dan Kartu Stok",
        "explanation": "Evaluasi pada Bagian VI memperoleh tingkat kelayakan 92,5%. Pemilik Toko dan Admin Toko menegaskan bahwa formulir mutasi stok manual berhasil menertibkan pencatatan barang rusak, botol pecah, maupun barang kedaluwarsa dengan alasan operasional tertulis yang dapat dipertanggungjawabkan. Kasir Toko dan Pelanggan memberikan nilai 4 terkait rincian teknis buku besar, namun tampilan saldo berjalan (<i>running balance</i>) pada kartu stok terbukti menyajikan riwayat pergerakan barang secara kronologis dan transparan."
    },
    {
        "letter": "G", "roman": "Bagian VII",
        "title": "Modul Siklus Pengawasan Stok Fisik (<i>Stok Opname</i>) Terpusat",
        "tbl_num": "4.8",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian VII: Siklus Pengawasan Stok Fisik Terpusat",
        "explanation": "Evaluasi pada Bagian VII mencapai skor capaian 95,0%. Pemilik Toko menilai fitur pembekuan transaksi server (<i>Freeze Lock</i>) sebagai keunggulan utama dalam mengunci integritas angka persediaan selama pencacahan fisik. Kasir Toko menyatakan formulir pencacahan tanpa saldo buku (<i>Blind Count</i>) memberikan kenyamanan bekerja tanpa tekanan angka sistem. Admin Toko menilai layar verifikasi selisih sangat praktis dalam mengeksekusi mutasi koreksi penyeimbang persediaan secara seketika dan menerbitkan Berita Acara A4 resmi."
    },
    {
        "letter": "H", "roman": "Bagian VIII",
        "title": "Modul Laporan Manajerial, Pengawasan Kedaluwarsa, dan Dasbor Eksekutif",
        "tbl_num": "4.9",
        "tbl_title": "Hasil Evaluasi Kuesioner Penerimaan Pengguna Bagian VIII: Pelaporan Manajerial dan Dasbor",
        "explanation": "Evaluasi pada Bagian VIII mencatatkan tingkat kelayakan 92,5%. Pemilik Toko menilai dasbor eksekutif menyajikan visualisasi data yang komprehensif, terutama ringkasan omzet dan estimasi nilai persediaan. Fitur pengawasan barang mendekati kedaluwarsa (30, 60, dan 90 hari) terbukti membantu manajemen mempercepat pengembalian (*retur*) pasokan ke distributor. Kasir dan Pelanggan memberikan nilai 4 pada opsi pemfilteran tanggal yang mendalam, namun seluruh lembar cetak laporan fisik A4 dinilai rapi dan siap diarsipkan."
    }
]

from build_true_uat_system import indicators

# 2. Build HTML for Bab IV sections A through H
sections_bab4_html = []
for p_idx, meta in enumerate(parts_meta):
    # Filter 2 indicators for this part
    part_inds = [ind for ind in indicators if ind['part_idx'] == p_idx]
    
    rows_tr = []
    for ind in part_inds:
        tr = f"""                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono font-bold">UAT-{ind['no']:02d}</td>
                                        <td class="border border-slate-300 px-2.5 py-1.5 font-medium">{ind['statement']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center">{ind['p']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center">{ind['a']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center">{ind['k']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center">{ind['c']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">{ind['total']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono">{ind['pct']}</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-semibold text-emerald-800">{ind['kat']}</td>
                                    </tr>"""
        rows_tr.append(tr)
    
    # Subtotal row
    sub_total = sum(i['total'] for i in part_inds)
    sub_pct = f"{(sub_total / 40) * 100:.1f}%"
    sub_row = f"""                                    <tr class="bg-slate-50 font-bold">
                                        <td colspan="6" class="border border-slate-300 px-2.5 py-1 text-right text-slate-800">Subtotal Capaian {meta['roman']} (Maksimal: 40)</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-slate-900">{sub_total}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold text-emerald-700">{sub_pct}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-800">Sesuai</td>
                                    </tr>"""
    rows_tr.append(sub_row)
    
    rows_joined = '\n'.join(rows_tr)
    
    sec_html = f"""                    <!-- {meta['letter']}. {meta['roman']}: {meta['title']} -->
                    <div class="mt-6 mb-4">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                            {meta['letter']}. {meta['roman']}: {meta['title']}
                        </h5>
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel {meta['tbl_num']} {meta['tbl_title']}
                        </p>
                        <div class="overflow-x-auto mb-3">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-12" rowspan="2">Kode</th>
                                        <th class="border border-slate-400 px-2 py-1.5" rowspan="2">Indikator Pernyataan Evaluasi Kebutuhan Pengguna</th>
                                        <th class="border border-slate-400 px-2 py-1" colspan="4">Skor Peran Penguji</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-16" rowspan="2">Total Skor</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-20" rowspan="2">% Capaian</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24" rowspan="2">Status</th>
                                    </tr>
                                    <tr>
                                        <th class="border border-slate-400 px-1 py-1 text-[10px] w-12">Pemilik</th>
                                        <th class="border border-slate-400 px-1 py-1 text-[10px] w-12">Admin</th>
                                        <th class="border border-slate-400 px-1 py-1 text-[10px] w-12">Kasir</th>
                                        <th class="border border-slate-400 px-1 py-1 text-[10px] w-14">Pelanggan</th>
                                    </tr>
                                </thead>
                                <tbody>
{rows_joined}
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            {meta['explanation']}
                        </p>
                    </div>"""
    sections_bab4_html.append(sec_html)

all_sections_str = '\n\n'.join(sections_bab4_html)

# 3. Build Table 4.10: Rekapitulasi Lintas Modul
rekap_rows = []
for p_idx, meta in enumerate(parts_meta):
    part_inds = [ind for ind in indicators if ind['part_idx'] == p_idx]
    sub_total = sum(i['total'] for i in part_inds)
    sub_pct = f"{(sub_total / 40) * 100:.2f}%"
    clean_title = re.sub(r'<[^>]+>', '', meta['title'])
    
    r_tr = f"""                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">{p_idx+1}</td>
                                        <td class="border border-slate-300 px-2.5 py-1 font-semibold">{meta['roman']}: {clean_title}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">2</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">{sub_total}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">40</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono text-emerald-700 font-bold">{sub_pct}</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sesuai</td>
                                    </tr>"""
    rekap_rows.append(r_tr)

rekap_rows_joined = '\n'.join(rekap_rows)

table_410_html = f"""                    <div class="my-4">
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel 4.10 Rekapitulasi Hasil Evaluasi Penerimaan Pengguna (<i>UAT</i>) Lintas Modul Sistem
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-10">No</th>
                                        <th class="border border-slate-400 px-2 py-1.5">Bagian Modul Operasional Sistem</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24">Jml Indikator</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24">Skor Aktual</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24">Skor Maks</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24">% Capaian</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-28">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
{rekap_rows_joined}
                                    <tr class="bg-emerald-50 font-bold text-slate-900 border-t-2 border-slate-400">
                                        <td colspan="2" class="border border-slate-300 px-3 py-1.5 text-right uppercase tracking-wider font-sans">
                                            Grand Total Evaluasi Penerimaan Sistem
                                        </td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono">16</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono text-blue-900 font-black">300</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono">320</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono text-emerald-800 font-black">93.75%</td>
                                        <td class="border border-slate-300 px-2 py-1.5 text-center font-black text-emerald-800 uppercase">Sesuai</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>"""

# 4. Table 4.1 Profil Peran Penguji
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

# 5. Gambar 4.51 and 4.52
gambar_451 = ""

gambar_452 = ""

# Formula and Discussion Text
calc_and_discussion = f"""                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Berdasarkan data rekapitulasi penilaian pada Tabel 4.10, kalkulasi persentase kelayakan penerapan sistem dihitung menggunakan rumus persentase kelayakan skala Likert:
                    </p>

                    <div class="my-4 p-4 bg-slate-50 border border-slate-300 rounded text-center font-serif text-xs">
                        <p class="font-bold text-slate-900 mb-2">Kalkulasi Persentase Kelayakan Sistem:</p>
                        <p class="font-mono text-sm text-slate-800 mb-1">
                            Persentase Kelayakan = ( Total Skor Aktual / Total Skor Maksimum ) &times; 100%
                        </p>
                        <p class="font-mono text-sm text-slate-800 font-bold mb-2">
                            Persentase Kelayakan = ( 300 / 320 ) &times; 100% = 93.75%
                        </p>
                        <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-xs uppercase tracking-wide">
                            Status: Sesuai Kebutuhan (93.75%)
                        </span>
                    </div>

{gambar_452}

                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Temuan penting dari pelaksanaan evaluasi penerimaan pengguna ini mencakup beberapa poin penguatan sistem:
                    </p>

                    <ul class="list-disc pl-10 mb-4 space-y-1.5 text-xs font-serif text-slate-800 leading-relaxed">
                        <li>
                            <strong>Efektivitas Penjagaan Kualitas Obat:</strong> Kasir Toko dan Admin Toko sangat mengapresiasi alokasi *<i>FEFO</i>* otomatis yang meniadakan keraguan kasir dalam mengambil barang di rak, memangkas potensi kerugian akibat barang kedaluwarsa.
                        </li>
                        <li>
                            <strong>Ketertiban <i>Stok Opname</i>:</strong> Pemilik Toko menegaskan bahwa mekanisme *<i>Freeze Lock</i>* dan *<i>Blind Count</i>* berhasil menertibkan proses penghitungan fisik tahunan menjadi terstruktur dan bebas dari manipulasi angka sistem.
                        </li>
                        <li>
                            <strong>Kelancaran Penjualan Lintas Saluran:</strong> Pelanggan Toko Daring menyatakan antarmuka katalog obat dan alkes sangat informatif, memudahkan pencarian variasi kemasan eceran dan proses transaksi mandiri tanpa bentrok stok dengan gerai fisik.
                        </li>
                        <li>
                            <strong>Kejelasan Histori Buku Besar Persediaan:</strong> Admin Toko merasakan kemudahan luar biasa dalam menelusuri arus mutasi barang masuk, keluar, dan konversi satuan melalui buku besar kartu stok yang transparan.
                        </li>
                        <li>
                            <strong>Otomasi Penyesuaian Mutasi:</strong> Mekanisme penyaringan otomatis selisih opname mempermudah verifikasi manajerial sehingga perbedaan fisik langsung dapat diselesaikan secara instan dan akurat.
                        </li>
                    </ul>

                    <p class="text-xs font-serif text-slate-800 text-justify mb-2 indent-8 leading-relaxed">
                        Dengan perolehan skor akhir <strong>93,75% (Sesuai)</strong> dan pengesahan resmi dari seluruh perwakilan peran penguji, Sistem Informasi Inventori dan Penjualan <em>Omnichannel</em> Toko Kesehatan ZenCare Medical dinyatakan telah memenuhi seluruh spesifikasi kebutuhan bisnis pengguna serta siap dioperasikan penuh pada gerai operasional.
                    </p>"""

# Complete Subbab 4.2 block
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
                    Pengujian sistem merupakan tahapan krusial dalam rekayasa perangkat lunak guna membuktikan bahwa Sistem Informasi Inventori dan Penjualan <em>Omnichannel</em> ZenCare Medical telah memenuhi kebutuhan operasional pengguna (<em>user requirements</em>) dan siap diterapkan dalam lingkungan bisnis nyata. Berbeda dengan pengujian fungsionalitas internal kode program, Pengujian Penerimaan Pengguna (<em>User Acceptance Testing</em> / <i>UAT</i>) berfokus langsung pada sudut pandang pengguna akhir (<em>end-user</em>) dan proses bisnis toko: menguji kemudahan penggunaan, kesesuaian alur kerja pelayanan obat dan alat kesehatan, ketepatan logika penanganan persediaan berbasis <i>FEFO</i>, keandalan pengawasan stok fisik, serta kelancaran transaksi lintas kanal.
                </p>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pengujian diselenggarakan langsung di gerai operasional toko kesehatan ZenCare Medical pada tanggal 12 November 2026 dengan melibatkan 4 (empat) peran pengguna yang merepresentasikan seluruh tingkatan operasional usaha: <strong>Pemilik Toko</strong> (pemegang hak akses <em>superadmin</em>), <strong>Admin Toko</strong> (pemegang hak akses <em>admin</em> logistik dan pengadaan), <strong>Kasir Toko</strong> (pemegang hak akses <em>kasir</em> meja penjualan dan pencacahan fisik), serta <strong>Pelanggan Toko Daring</strong> (konsumen pengguna etalase belanja daring). Rincian tanggung jawab dan lingkup evaluasi tiap peran dirangkum pada Tabel 4.1.
                </p>

{table_41}

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Instrumen pengujian penerimaan pengguna dirancang berbasis butir-butir pernyataan evaluasi kebutuhan operasional yang dinilai menggunakan skala Likert 1 hingga 5 (1: Sangat Tidak Setuju, 2: Tidak Setuju, 3: Cukup / Netral, 4: Setuju, 5: Sangat Setuju). Pengujian dikelompokkan ke dalam 8 (delapan) bagian modul fungsional, di mana setiap bagian disajikan dalam matriks evaluasi kebutuhan tersendiri beserta uraian analisisnya:
                </p>

{all_sections_str}

                    <!-- I. REKAPITULASI HASIL PENGUJIAN DAN EVALUASI KELAYAKAN PENGGUNA -->
                    <div class="mt-8 pt-4 border-t border-slate-300">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-3">
                            I. Rekapitulasi Hasil Pengujian dan Evaluasi Kelayakan Pengguna Keseluruhan
                        </h5>

{gambar_451}

{table_410_html}

{calc_and_discussion}
                    </div>

            </div>
        </div>
"""

# Apply to BAB_IV_ZENCARE_MEDICAL.html
with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b4 = f.read()

idx_start_b4 = text_b4.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_start_b4 == -1:
    idx_start_b4 = text_b4.find('<div id="subbab_42"')
assert idx_start_b4 != -1

idx_end_b4 = text_b4.find('<!-- Bottom Notification (No Print) -->')
assert idx_end_b4 != -1

updated_b4 = text_b4[:idx_start_b4] + complete_42 + "\n    </div> <!-- Closes full_bab_iv_content (paper) -->\n\n    " + text_b4[idx_end_b4:]

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_b4)
print("Saved clean true UAT to BAB_IV_ZENCARE_MEDICAL.html")

# Apply to BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_comb = f.read()

idx_start_comb = text_comb.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_start_comb == -1:
    idx_start_comb = text_comb.find('<div id="subbab_42"')
assert idx_start_comb != -1

idx_end_comb = text_comb.find('<!-- Bottom Notification (No Print) -->')
assert idx_end_comb != -1

updated_comb = text_comb[:idx_start_comb] + complete_42 + "\n    </div> <!-- Closes full_bab_iv_content (paper) -->\n\n    " + text_comb[idx_end_comb:]

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_comb)
print("Saved clean true UAT to BAB_III_DAN_IV_ZENCARE_MEDICAL.html")

# 5. Update INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html and lain
# Build table rows for INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html
instrument_rows = []
for p_idx, meta in enumerate(parts_meta):
    header_tr = f"""                    <tr class="bg-slate-100 font-bold">
                        <td colspan="9" class="text-left font-sans-ui uppercase text-[10px] tracking-wider py-1.5 text-slate-800">
                            {meta['roman']}: {meta['title']}
                        </td>
                    </tr>"""
    instrument_rows.append(header_tr)
    
    part_inds = [ind for ind in indicators if ind['part_idx'] == p_idx]
    for ind in part_inds:
        row_tr = f"""                    <tr>
                        <td class="text-center font-mono font-bold">UAT-{ind['no']:02d}</td>
                        <td class="font-serif text-slate-800">{ind['statement']}</td>
                        <td class="text-center val-col">{ind['p']}</td>
                        <td class="text-center val-col">{ind['a']}</td>
                        <td class="text-center val-col">{ind['k']}</td>
                        <td class="text-center val-col">{ind['c']}</td>
                        <td class="text-center total-col font-bold">{ind['total']}</td>
                        <td class="text-center pct-col font-mono">{ind['pct']}</td>
                        <td class="text-center kat-col font-semibold text-emerald-800">{ind['kat']}</td>
                    </tr>"""
        instrument_rows.append(row_tr)

all_instrument_rows_str = '\n'.join(instrument_rows)

new_instrument_thead = """                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                    <tr>
                        <th class="w-14" rowspan="2">Kode</th>
                        <th rowspan="2">Indikator Pernyataan Evaluasi Penerimaan Pengguna</th>
                        <th colspan="4" class="py-1">Penilaian Peran Penguji</th>
                        <th class="w-16" rowspan="2">Total</th>
                        <th class="w-20" rowspan="2">% Capaian</th>
                        <th class="w-24" rowspan="2">Status</th>
                    </tr>
                    <tr>
                        <th class="w-12 text-[10px] py-1">Pemilik</th>
                        <th class="w-12 text-[10px] py-1">Admin</th>
                        <th class="w-12 text-[10px] py-1">Kasir</th>
                        <th class="w-14 text-[10px] py-1">Pelanggan</th>
                    </tr>
                </thead>"""

for uat_file in [r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', r'lain\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html']:
    with open(uat_file, 'r', encoding='utf-8') as f:
        c = f.read()
    
    # replace thead
    c = re.sub(r'<thead class="bg-slate-100 text-slate-900 font-bold text-center">.*?</thead>', new_instrument_thead, c, flags=re.DOTALL)
    
    # replace tbody
    tb_s = c.find('<tbody>')
    tb_e = c.find('</tbody>', tb_s)
    c = c[:tb_s+7] + "\n" + all_instrument_rows_str + "\n                " + c[tb_e:]
    
    # replace grand total values: 300, 320, 93.75%
    c = re.sub(r'<td class="text-center font-bold sum-r1">.*?</td>', '<td class="text-center font-bold sum-r1">80</td>', c)
    c = re.sub(r'<td class="text-center font-bold sum-r2">.*?</td>', '<td class="text-center font-bold sum-r2">79</td>', c)
    c = re.sub(r'<td class="text-center font-bold sum-r3">.*?</td>', '<td class="text-center font-bold sum-r3">69</td>', c)
    c = re.sub(r'<td class="text-center font-bold sum-r4">.*?</td>', '<td class="text-center font-bold sum-r4">72</td>', c)
    c = re.sub(r'<td class="text-center font-bold sum-grand">.*?</td>', '<td class="text-center font-bold sum-grand">300</td>', c)
    c = re.sub(r'<td class="text-center font-bold font-mono sum-pct">.*?</td>', '<td class="text-center font-bold font-mono sum-pct">93.75%</td>', c)
    
    # text replacement: 16 butir
    c = c.replace('36 skenario', '16 butir indikator penerimaan')
    c = c.replace('26 butir skenario', '16 butir indikator penerimaan')
    c = c.replace('36 butir', '16 butir')
    c = c.replace('26 butir', '16 butir')
    
    with open(uat_file, 'w', encoding='utf-8') as f:
        f.write(c)
    print(f"Updated {uat_file}")

# 6. Re-capture screenshots 51 and 52
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
brain_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

url_uat = 'http://localhost/inventory_zencare/laporan_ta/INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html'

print("Capturing 52_Hasil_Evaluasi_UAT_Terisi.png...")
temp_dir = tempfile.mkdtemp()
out_filled = os.path.join(out_dir, '52_Hasil_Evaluasi_UAT_Terisi.png')
cmd_filled = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_filled}',
    '--window-size=1200,1100', url_uat
]
subprocess.run(cmd_filled)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_filled, os.path.join(brain_dir, 'test_uat_filled.png'))
print(f"Captured 52 ({os.path.getsize(out_filled)} bytes)")

print("Capturing 51_Blangko_Instrumen_UAT.png...")
cache_dir = os.path.abspath(r'scratch\cache')
blank_html_path = os.path.join(cache_dir, 'uat_blank_view.html')

with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text_blank = f.read()

text_blank = text_blank.replace('<body class="bg-slate-100 min-h-screen py-8 px-4">', '<body class="bg-slate-100 min-h-screen py-8 px-4" onload="switchTab(\'kosong\')">')
with open(blank_html_path, 'w', encoding='utf-8') as f:
    f.write(text_blank)

temp_dir = tempfile.mkdtemp()
out_blank = os.path.join(out_dir, '51_Blangko_Instrumen_UAT.png')
cmd_blank = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_blank}',
    '--window-size=1200,1100', 'http://localhost/inventory_zencare/scratch/cache/uat_blank_view.html'
]
subprocess.run(cmd_blank)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_blank, os.path.join(brain_dir, 'test_uat_blank.png'))
print(f"Captured 51 ({os.path.getsize(out_blank)} bytes)")

print("All tasks completed successfully!")

