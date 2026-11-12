import re
import os

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Parse rows for each of the 8 parts
# Find all the 8 sections
# Let's inspect where each section is
sections_raw = [
    {
        "letter": "A",
        "title": "Bagian I: Modul Autentikasi Staf, Kontrol Hak Akses (<i>RBAC</i>), dan Pengaturan Sistem",
        "tbl_num": "4.2",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian I: Modul Autentikasi Staf dan Hak Akses (<i>RBAC</i>)",
        "explanation": """Pengujian pada Bagian I membuktikan bahwa sistem keamanan satu pintu telah mengimplementasikan mekanisme kontrol akses berbasis peran (<i>Role-Based Access Control</i> / <i>RBAC</i>) secara andal. Validasi kata sandi terenkripsi pada tabel <i>users</i> berhasil memisahkan hak akses staf operasional: Kasir Toko diarahkan langsung ke terminal kasir POS, Admin Toko diarahkan ke pengelolaan logistik dan master data, sedangkan Pemilik Toko (<i>Superadmin</i>) memperoleh akses menyeluruh ke <i>dashboard</i> eksekutif. Sistem juga terbukti tangguh menolak manipulasi URL langsung (<i>URL tampering</i>) oleh pengguna tanpa wewenang dan mengarahkannya kembali ke halaman otorisasi. Seluruh 4 skenario pada modul ini diselesaikan dengan status <strong>BERHASIL</strong> tanpa celah keamanan."""
    },
    {
        "letter": "B",
        "title": "Bagian II: Modul Master Produk Medis, Konfigurasi Multi-Satuan (<i>Multi-UOM</i>), dan Rekanan Pemasok",
        "tbl_num": "4.3",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian II: Modul Master Data Produk dan Multi-Satuan (<i>Multi-UOM</i>)",
        "explanation": """Pengujian pada Bagian II memvalidasi keakuratan struktur master data komoditas medis yang membedakan produk induk (tabel <i>produk</i>) dengan variasi kemasan atau dosisnya (tabel <i>produk_variasi</i>). Mekanisme tabel konversi satuan bertingkat (tabel <i>satuan_konversi</i>) terbukti mampu mengonversi kuantitas kemasan besar ke satuan eceran terkecil (<i>base unit</i>) secara matematis tanpa pembulatan desimal yang keliru. Selain itu, perekaman rekanan pemasok pada tabel <i>suppliers</i> serta notifikasi ambang batas stok minimum (<i>safety stock alert</i>) berfungsi akurat dalam memicu peringatan restok bagi staf pengadaan. Seluruh 5 skenario pada modul ini tercatat dengan status <strong>BERHASIL</strong>."""
    },
    {
        "letter": "C",
        "title": "Bagian III: Modul Terminal Kasir Luring (<i>Point of Sale</i> / POS) dan Alokasi <i>FEFO</i>",
        "tbl_num": "4.4",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian III: Modul Transaksi Penjualan Kasir POS dan Algoritma <i>FEFO</i>",
        "explanation": """Pengujian pada Bagian III berfokus pada operasional kasir di gerai fisik. Pengujian membuktikan kecepatan pembacaan barcode produk pada keranjang belanja kasir dan keakuratan algoritma <i>FEFO</i> (<i>First Expired First Out</i>) yang secara otomatis mengalokasikan nomor <i>batch</i> dengan tanggal kedaluwarsa paling awal. Mekanisme dialog peringatan anomali <i>FEFO</i> terbukti aktif memperingatkan kasir jika fisik barang yang dipindai memiliki masa kedaluwarsa yang lebih baru daripada stok yang seharusnya dikeluarkan lebih dahulu. Sistem juga secara konsisten menolak transaksi penjualan jika stok fisik tidak mencukupi, memproses pembayaran multi-kanal secara instan, serta mencetak struk nota belanja resmi. Seluruh 5 skenario pada modul ini berstatus <strong>BERHASIL</strong>."""
    },
    {
        "letter": "D",
        "title": "Bagian IV: Modul Toko Daring (<i>E-Commerce</i>) dan Pengelolaan Penjualan <i>Omnichannel</i>",
        "tbl_num": "4.5",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian IV: Modul Toko Daring (<i>E-Commerce</i>) dan Sinkronisasi <i>Omnichannel</i>",
        "explanation": """Pengujian pada Bagian IV mengevaluasi pengalaman belanja daring pelanggan serta sinkronisasi persediaan lintas kanal. Pelanggan dapat menelusuri katalog obat bebas dan alat kesehatan secara transparan, memilih variasi kemasan eceran, dan memasukkan barang ke keranjang belanja. Sistem secara otomatis mereservasi stok terpesan (<i>reserved stock</i>) pada tabel <i>pesanan_batch_alokasi</i> saat <i>checkout</i> guna mencegah perebutan stok dengan transaksi kasir fisik (<i>race condition</i>). Pelanggan berhasil mengunggah bukti pembayaran yang kemudian diverifikasi oleh Admin Toko, dan saldo persediaan terbukti tersinkronisasi secara seketika (*real-time*) di seluruh saluran. Seluruh 5 skenario pada modul ini berstatus <strong>BERHASIL</strong>."""
    },
    {
        "letter": "E",
        "title": "Bagian V: Modul Logistik Penerimaan Pasokan Barang Masuk dan Penandaan Barcode",
        "tbl_num": "4.6",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian V: Modul Logistik Penerimaan Barang Masuk dan Label Barcode",
        "explanation": """Pengujian pada Bagian V memvalidasi alur pencatatan pengadaan pasokan barang dari rekanan distributor. Formulir penerimaan terbukti mampu mencatat nomor surat jalan, nomor faktur, tanggal kedaluwarsa (<i>expired date</i>), serta nomor <i>batch</i> baru ke dalam tabel <i>penerimaan_barang</i> dan <i>batch_stok</i>. Sistem secara otomatis mengalikan kuantitas satuan masuk ke dalam saldo unit dasar dan mencetak lembar label stiker barcode produk yang dapat langsung dipindai pada terminal kasir fisik. Seluruh 4 skenario pada modul penerimaan barang ini berstatus <strong>BERHASIL</strong>."""
    },
    {
        "letter": "F",
        "title": "Bagian VI: Modul Pengelolaan Mutasi Stok Manual dan Buku Besar Kartu Stok",
        "tbl_num": "4.7",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian VI: Modul Penyesuaian Mutasi Stok Manual dan Kartu Stok",
        "explanation": """Pengujian pada Bagian VI membuktikan transparansi pengelolaan penyesuaian persediaan di luar jalur transaksi penjualan reguler. Formulir mutasi stok manual mampu merekam pemotongan stok akibat barang rusak kemasan, cacat pabrik, maupun barang kedaluwarsa dengan kewajiban mencantumkan alasan operasional tertulis. Seluruh mutasi secara otomatis dicatat ke dalam buku besar tabel <i>kartu_stok</i> dengan kalkulasi saldo berjalan (<i>running balance</i>) yang presisi per variasi dan per <i>batch</i>, serta tersimpan ke dalam riwayat log penyesuaian. Seluruh 3 skenario pada modul ini tercatat dengan status <strong>BERHASIL</strong>."""
    },
    {
        "letter": "G",
        "title": "Bagian VII: Modul Siklus Lengkap Pengawasan Stok Fisik (<i>Stok Opname</i>) Terpusat",
        "tbl_num": "4.8",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian VII: Modul Siklus Pemeriksaan Fisik Persediaan (<i>Stok Opname</i>)",
        "explanation": """Pengujian pada Bagian VII memvalidasi siklus pengawasan fisik persediaan multi-aktor dan multi-tahap. Pengujian membuktikan efektivitas mekanisme pembekuan transaksi (<i>Freeze Lock</i>) oleh Pemilik Toko, proses penghitungan fisik mandiri tanpa melihat saldo buku sistem (<i>Blind Count</i>) oleh Kasir Toko, rekonsiliasi dan verifikasi perbedaan kuantitas (<i>surplus/defisit</i>) oleh Admin Toko, serta eksekusi mutasi koreksi penyeimbang persediaan secara otomatis. Setelah verifikasi rampung, sesi resmi ditutup dan diarsipkan secara permanen guna mencegah manipulasi angka historis. Seluruh 6 skenario pada modul ini berstatus <strong>BERHASIL</strong>."""
    },
    {
        "letter": "H",
        "title": "Bagian VIII: Modul Laporan Manajerial, Pengawasan Kedaluwarsa, dan Dasbor Eksekutif",
        "tbl_num": "4.9",
        "tbl_title": "Hasil Pengujian <i>UAT</i> Bagian VIII: Modul Pelaporan Manajerial dan Dasbor Eksekutif",
        "explanation": """Pengujian pada Bagian VIII menguji keandalan penyajian informasi analitis bagi pengambil keputusan toko. Dasbor eksekutif menyajikan ringkasan omset penjualan harian/bulanan, estimasi nilai aset persediaan, serta peringatan dini barang mendekati kedaluwarsa. Seluruh tabel laporan web (transaksi penjualan, mutasi stok, penerimaan barang, dan arsip stok opname) berhasil difilter berdasarkan rentang tanggal dinamis dan diekspor ke dalam dokumen cetak fisik format A4 standar resmi. Laporan analisis anomali <i>FEFO</i> juga terbukti akurat dalam mendata setiap insiden penyimpangan alur pengeluaran barang di gerai. Seluruh 4 skenario pada modul ini berstatus <strong>BERHASIL</strong>."""
    }
]

# Read existing section HTML blocks from BAB_IV_ZENCARE_MEDICAL.html
# Let's extract the tables generated
sections_html = []
for meta in sections_raw:
    tbl_num = meta['tbl_num']
    m = re.search(rf'<!-- {meta["letter"]}\..*?-->.*?<div class="mt-6 mb-4">.*?Tabel {tbl_num}.*?</div>\s*</div>', text, re.DOTALL)
    if not m:
        # try alternative search
        idx_t = text.find(f'Tabel {tbl_num}')
        idx_div_start = text.rfind('<div class="mt-6 mb-4">', 0, idx_t)
        idx_div_end = text.find('</div>', text.find('</p>', text.find('</table>', idx_t))) + 6
        sec_block = f"<!-- {meta['letter']}. {meta['title']} -->\n" + text[idx_div_start:idx_div_end]
    else:
        sec_block = m.group(0)
    sections_html.append(sec_block)

all_sections_str = '\n\n'.join(sections_html)

# Extract Table 4.1 (Profil Penguji)
idx_t41 = text.find('Tabel 4.1 Profil Peran Penguji Sistem')
idx_t41_start = text.rfind('<div class="my-4">', 0, idx_t41)
idx_t41_end = text.find('</div>', text.find('</table>', idx_t41)) + 6
table_41_html = text[idx_t41_start:idx_t41_end]

# Extract Section I content (from Likert table down to Gambar 4.52 and discussion)
# Let's see what is inside Section I right now
idx_sec_i = text.find('I. Rekapitulasi Hasil Pengujian dan Evaluasi Kelayakan Pengguna')
assert idx_sec_i != -1, "Section I not found"

# Find where Section I ends before closing divs of subbab_42
idx_bottom = text.find('<!-- Bottom Notification (No Print) -->')
sec_i_content = text[idx_sec_i : idx_bottom]

# In sec_i_content, ensure Gambar 4.51 is present!
# If Gambar 4.51 is not in sec_i_content, inject it before Tabel 4.10!
gambar_451_html = """                    <!-- GAMBAR 4.51 BLANGKO KOSONG INSTRUMEN UAT -->
                    <div class="my-6 border border-slate-300 rounded p-3 bg-slate-50 text-center">
                        <div class="mb-2 text-center">
                            <img src="../lain/File PPT/UI_UX_Asli/51_Blangko_Instrumen_UAT.png" alt="Blangko Kosong Instrumen UAT Format Resmi" class="mx-auto rounded border border-slate-300 max-h-[500px] object-contain shadow-xs">
                        </div>
                        <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                            Gambar 4.51 Tampilan Lembar Blangko Kosong Instrumen Pengujian Penerimaan Pengguna (<i>UAT</i>) Format Cetak Resmi
                        </p>
                    </div>"""

if 'Gambar 4.51' not in sec_i_content:
    # Inject right before Tabel 4.10
    idx_t410 = sec_i_content.find('Tabel 4.10')
    idx_target_div = sec_i_content.rfind('<div class="my-4">', 0, idx_t410)
    sec_i_content = sec_i_content[:idx_target_div] + gambar_451_html + "\n\n" + sec_i_content[idx_target_div:]

# Strip trailing closing divs
sec_i_content = sec_i_content.rstrip()
while sec_i_content.endswith('</div>'):
    sec_i_content = sec_i_content[:-6].rstrip()

# Rebuild complete Subbab 4.2
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

{table_41_html}

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pelaksanaan pengujian penerimaan pengguna dirinci ke dalam 8 (delapan) bagian modul fungsional yang merepresentasikan seluruh modul operasional sistem. Setiap bagian diuji melalui skenario spesifik, disajikan dalam bentuk matriks hasil pengujian, dan dievaluasi secara mendalam pada uraian berikut:
                </p>

{all_sections_str}

                    <!-- I. REKAPITULASI HASIL PENGUJIAN DAN EVALUASI KELAYAKAN PENGGUNA -->
                    <div class="mt-8 pt-4 border-t border-slate-300">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-3">
                            {sec_i_content}
                    </div>

            </div>
        </div>
"""

# Apply to BAB_IV_ZENCARE_MEDICAL.html
idx_42_start = text.find('<!-- 4.2 PENGUJIAN SISTEM (UAT) -->')
if idx_42_start == -1:
    idx_42_start = text.find('<div id="subbab_42"')
assert idx_42_start != -1

idx_bottom_bab4 = text.find('<!-- Bottom Notification (No Print) -->')
assert idx_bottom_bab4 != -1

updated_bab4 = text[:idx_42_start] + complete_42 + "\n    " + text[idx_bottom_bab4:]

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_bab4)
print("Updated laporan_ta\\BAB_IV_ZENCARE_MEDICAL.html with Gambar 4.51!")

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
print("Updated laporan_ta\\BAB_III_DAN_IV_ZENCARE_MEDICAL.html with Gambar 4.51!")
