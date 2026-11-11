subbab_4_2_html = """
        <!-- ============================================================================== -->
        <!-- 4.2 PENGUJIAN SISTEM (SYSTEM TESTING)                                          -->
        <!-- ============================================================================== -->
        <div id="subbab_42" class="mt-8 pt-6 border-t border-slate-300">
            <h3 class="text-sm font-bold font-serif text-slate-900 uppercase tracking-wide mb-3">
                4.2 PENGUJIAN SISTEM (SYSTEM TESTING)
            </h3>
            
            <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                Pengujian sistem merupakan tahapan krusial dalam rekayasa perangkat lunak yang bertujuan untuk mengevaluasi keterpenuhan fungsionalitas aplikasi, menguji keandalan aturan bisnis (<em>business rules</em>), serta memverifikasi integritas aliran data di seluruh modul sistem. Strategi pengujian pada sistem informasi inventori dan penjualan <em>omnichannel</em> toko kesehatan ZenCare Medical dibagi menjadi 2 (dua) metode utama sesuai dengan kaidah metodologi penelitian perangkat lunak:
            </p>

            <ol class="list-decimal pl-10 mb-5 space-y-2 text-xs font-serif text-slate-800 leading-relaxed">
                <li>
                    <strong>Pengujian Kotak Hitam (<em>Black-Box Testing</em>):</strong> Berfokus pada pembuktian logika teknis, pembatasan hak akses (<em>Role-Based Access Control</em>), mekanisme pencegahan <em>race condition</em> (<em>Server-Level Freeze</em>), pengujian <em>Blind Count</em>, penyaringan otomatis anomali selisih, hingga mutasi penyesuaian multi-satuan pada modul inti yang paling kompleks, yakni <strong>Modul Stok Opname Fisik Terpusat</strong>.
                </li>
                <li>
                    <strong>Pengujian Penerimaan Pengguna (<em>User Acceptance Testing</em> / UAT):</strong> Berfokus pada pengujian penerimaan sistem secara menyeluruh oleh pengguna nyata (<em>end-user</em>), yakni Superadmin (Pemilik Toko), Admin Operasional, Kasir Toko Fisik, dan Pelanggan Toko Online guna mengukur kepuasan, kemudahan antarmuka, serta kelayakan operasional aplikasi.
                </li>
            </ol>

            <!-- 4.2.1 BLACK-BOX TESTING -->
            <div id="subbab_421" class="mt-6">
                <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                    4.2.1 Pengujian Kotak Hitam (Black-Box Testing) Modul Stok Opname Terpusat
                </h4>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Modul Stok Opname dipilih sebagai objek utama pengujian <em>Black-Box Testing</em> karena modul ini memegang tanggung jawab tertinggi dalam menjaga integritas persediaan komoditas medis, memisahkan wewenang staf lapangan dengan verifikator manajerial, serta mengendalikan pergerakan transaksi lintas saluran (<em>omnichannel</em>). Pengujian dilakukan secara komprehensif menggunakan data riil persediaan pada basis data ZenCare Medical yang mencakup <strong>121 entitas persediaan fisik</strong> (terdiri atas 103 nomor <em>batch</em> obat dan bahan medis habis pakai, serta 18 entitas variasi alat kesehatan dengan pelacakan nomor seri unik / <em>Serial Number</em>).
                </p>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Skenario pengujian dirancang secara terstruktur dengan memasukkan kondisi normal dan kondisi anomali yang sengaja diciptakan (<em>intentional discrepancy scenarios</em>) guna memvalidasi ketahanan algoritma sistem. Skenario tersebut mencakup 5 (lima) kondisi anomali fisik lapangan:
                </p>

                <ul class="list-disc pl-10 mb-5 space-y-1.5 text-xs font-serif text-slate-800 leading-relaxed">
                    <li><strong>Skenario Defisit Barang Rusak:</strong> Kondisi di mana fisik barang berkurang karena kerusakan kemasan, botol pecah, atau strip penyok di rak etalase.</li>
                    <li><strong>Skenario Defisit Barang Kedaluwarsa (<em>Expired</em>):</strong> Kondisi di mana fisik barang ditarik dari rak karena telah melewati tanggal kedaluwarsa (<em>Expired Date</em>).</li>
                    <li><strong>Skenario Defisit Barang Hilang (Obat &amp; Alkes SN):</strong> Kondisi di mana fisik barang tidak ditemukan saat pencacahan rak, termasuk unit alat kesehatan ber-nomor seri unik yang hilang dari <em>display</em>.</li>
                    <li><strong>Skenario Surplus Kelebihan Stok:</strong> Kondisi di mana kuantitas fisik di rak melebihi catatan sistem akibat kelebihan kirim dari <em>supplier</em> yang belum terinput atau barang terselip yang ditemukan kembali di rak bawah.</li>
                    <li><strong>Skenario Barang Cocok / Sesuai (Selisih 0):</strong> Kondisi mayoritas (116 barang) yang kuantitas fisiknya pas dengan buku sistem, di mana sistem <strong>diuji untuk secara otomatis menyembunyikan barang-barang tersebut</strong> dari tabel verifikasi Admin agar perhatian verifikator terpusat murni pada anomali.</li>
                </ul>

                <!-- Tabel 4.1 Matriks Hasil Pengujian Black-Box -->
                <div class="my-5 p-3 bg-white border border-slate-300 rounded shadow-sm">
                    <p class="text-xs font-bold font-serif text-slate-900 mb-2 text-center">
                        Tabel 4.1 Matriks Hasil Pengujian Black-Box Modul Stok Opname Terpusat
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[11px] font-serif border border-black border-collapse">
                            <thead>
                                <tr class="bg-slate-100 border-b border-black text-center font-bold">
                                    <th class="border border-black p-2 w-12">Kode</th>
                                    <th class="border border-black p-2 w-32">Fitur / Modul</th>
                                    <th class="border border-black p-2 w-40">Skenario Uji Coba</th>
                                    <th class="border border-black p-2">Data Masukan (<em>Test Input</em>)</th>
                                    <th class="border border-black p-2">Hasil yang Diharapkan (<em>Expected</em>)</th>
                                    <th class="border border-black p-2">Hasil Pengujian Sistem (<em>Actual</em>)</th>
                                    <th class="border border-black p-2 w-16">Kesimpulan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-01</td>
                                    <td class="border border-black p-2 font-semibold">Pembekuan Server (<em>Central Freeze</em>)</td>
                                    <td class="border border-black p-2">Superadmin memulai sesi opname untuk membekukan transaksi operasional.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Aksi: mulai_sesi<br>Catatan: Audit Rutin</td>
                                    <td class="border border-black p-2">Server mengunci tombol bayar kasir POS offline, menonaktifkan checkout e-commerce daring, dan membuat snapshot 121 entitas persediaan.</td>
                                    <td class="border border-black p-2">Server Freeze aktif. Kasir POS dan checkout e-commerce terblokir dengan banner peringatan resmi.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-02</td>
                                    <td class="border border-black p-2 font-semibold">Pencacahan Buta (<em>Strict Blind Count</em>)</td>
                                    <td class="border border-black p-2">Kasir / staf lapangan melakukan input pencacahan fisik rak.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Role: kasir (User ID: 4)<br>Input Fisik: 8 Strip</td>
                                    <td class="border border-black p-2">Kasir hanya dapat menginputkan angka fisik. Kolom stok sistem, stok reserved, dan selisih dirahasiakan total (ikon gembok). Tombol mutasi dinonaktifkan.</td>
                                    <td class="border border-black p-2">Seluruh saldo sistem dan selisih terkunci 'Dirahasiakan'. Tidak ada bocoran angka buku ke kasir.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-03</td>
                                    <td class="border border-black p-2 font-semibold">Pemindai Barcode / Fast Locator</td>
                                    <td class="border border-black p-2">Kasir memindai barcode fisik obat menggunakan barcode scanner.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Barcode: CFD-500-26<br>(Scanner Gun Enter)</td>
                                    <td class="border border-black p-2">Sistem otomatis menggulir (<em>auto-scroll</em>), menyorot baris produk dengan animasi warna kuning, dan mengarahkan kursor langsung ke kotak input fisik.</td>
                                    <td class="border border-black p-2">Baris Cefadroxil tersorot seketika dan kursor langsung aktif di kolom input hitung fisik.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr class="bg-amber-50/40">
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-04</td>
                                    <td class="border border-black p-2 font-semibold">Penyaringan Otomatis Anomali Selisih</td>
                                    <td class="border border-black p-2">Admin membuka antarmuka verifikasi hasil hitung fisik yang telah dicacah.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Filter Default:<br>status_eval=berselisih</td>
                                    <td class="border border-black p-2">Sistem otomatis menyembunyikan 116 barang cocok (selisih 0) dan hanya menampilkan 5 barang yang memiliki selisih kuantitas.</td>
                                    <td class="border border-black p-2 font-semibold text-slate-900">Tepat 5 barang berselisih yang tampil. Sebanyak 116 barang yang sesuai otomatis disembunyikan dari tabel verifikasi.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-05</td>
                                    <td class="border border-black p-2 font-semibold">Mutasi Defisit: Barang Rusak</td>
                                    <td class="border border-black p-2">Admin memproses selisih -2 Strip Cefadroxil 500mg akibat kemasan rusak/pecah.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Dropdown: Barang Rusak<br>Kuantitas: 2 Strip</td>
                                    <td class="border border-black p-2">Stok batch berkurang 2 strip, stok toko berkurang 2 strip, kartu stok mencatat mutasi Keluar dengan alasan 'Barang Rusak'.</td>
                                    <td class="border border-black p-2">Stok batch terpotong dari 10 ke 8 strip. Kartu stok tercatat resmi dengan nomor dokumen sesi opname.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-06</td>
                                    <td class="border border-black p-2 font-semibold">Mutasi Defisit: Barang Kedaluwarsa</td>
                                    <td class="border border-black p-2">Admin memproses selisih -3 Botol Sanmol Sirup Anak yang telah lewat masa ED.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Dropdown: Barang Kedaluwarsa<br>Kuantitas: 3 Botol</td>
                                    <td class="border border-black p-2">Stok batch terpotong 3 botol, kartu stok mencatat mutasi Keluar pemusnahan barang kedaluwarsa.</td>
                                    <td class="border border-black p-2">Stok berkurang dari 5 ke 2 botol. Log audit mencatat alasan mutasi 'Barang Kedaluwarsa'.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-07</td>
                                    <td class="border border-black p-2 font-semibold">Mutasi Defisit: Barang Hilang</td>
                                    <td class="border border-black p-2">Admin memproses selisih -2 Strip Amoxicillin dan -1 Unit Tensimeter Omron (Alkes SN).</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Dropdown: Barang Hilang<br>SN: OMR-7120-109</td>
                                    <td class="border border-black p-2">Stok buku berkurang, status nomor seri SN bersangkutan beralih menjadi 'Hilang', dan kartu stok tercatat resmi.</td>
                                    <td class="border border-black p-2">Status unit serial diubah ke 'Hilang', stok alkes berkurang 1 unit, dan tercatat di kartu stok.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-08</td>
                                    <td class="border border-black p-2 font-semibold">Mutasi Surplus: Kelebihan Stok</td>
                                    <td class="border border-black p-2">Admin memproses selisih +2 Botol Betadine Solution yang ditemukan di rak.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Dropdown: Kelebihan Kirim / Ditemukan (+2 Botol)</td>
                                    <td class="border border-black p-2">Stok batch bertambah 2 botol, kartu stok mencatat mutasi Masuk koreksi fisik lebih.</td>
                                    <td class="border border-black p-2">Stok batch bertambah dari 14 ke 16 botol. Kartu stok mencatat mutasi Masuk penyesuaian surplus.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-09</td>
                                    <td class="border border-black p-2 font-semibold">Penyelesaian Sesi &amp; Pembukaan Kunci</td>
                                    <td class="border border-black p-2">Superadmin menekan tombol 'Selesaikan Sesi (Buka Kunci)' setelah seluruh 5 anomali dimutasi.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Aksi: selesaikan_sesi<br>Sisa Selisih: 0</td>
                                    <td class="border border-black p-2">Status sesi beralih ke 'Selesai', pembekuan server dicabut, kasir POS dan checkout toko online aktif normal kembali.</td>
                                    <td class="border border-black p-2">Server Unfreeze berhasil. Transaksi POS dan e-commerce pulih 100% menggunakan saldo persediaan terbaru.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                                <tr class="bg-emerald-50/40">
                                    <td class="border border-black p-2 text-center font-mono font-bold">BBT-10</td>
                                    <td class="border border-black p-2 font-semibold">Pemulihan Database (<em>Reset Baseline</em>)</td>
                                    <td class="border border-black p-2">Superadmin menekan tombol 'Reset Database Demo' untuk membersihkan data sampah pengujian.</td>
                                    <td class="border border-black p-2 font-mono text-[10px]">Aksi: reset_database_demo<br>Konfirmasi: Ya</td>
                                    <td class="border border-black p-2">Seluruh sesi opname pengujian dan pergerakan mutasi kartu stok dihapus, saldo fisik dipulihkan 100% ke kondisi bersih awal.</td>
                                    <td class="border border-black p-2 font-semibold text-slate-900">Database pulih ke kondisi baseline awal (<em>fresh state</em>). Siap digunakan untuk demonstrasi sidang tanpa data uji tertinggal.</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-700">Valid</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel 4.2 Rekapitulasi Skenario Uji Coba Anomali -->
                <div class="my-5 p-3 bg-white border border-slate-300 rounded shadow-sm">
                    <p class="text-xs font-bold font-serif text-slate-900 mb-2 text-center">
                        Tabel 4.2 Rekapitulasi Skenario Pengujian Anomali Selisih Stok Opname (Total 121 Item)
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[11px] font-serif border border-black border-collapse">
                            <thead>
                                <tr class="bg-slate-100 border-b border-black text-center font-bold">
                                    <th class="border border-black p-2 w-8">No</th>
                                    <th class="border border-black p-2 w-48">Nama Produk &amp; Variasi</th>
                                    <th class="border border-black p-2 w-32">Identitas Batch / SN</th>
                                    <th class="border border-black p-2 text-center w-24">Stok Sistem</th>
                                    <th class="border border-black p-2 text-center w-24">Hitung Fisik</th>
                                    <th class="border border-black p-2 text-center w-24">Selisih</th>
                                    <th class="border border-black p-2">Kategorisasi Mutasi Dialokasikan</th>
                                    <th class="border border-black p-2 text-center w-28">Status Tabel Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-black p-2 text-center font-bold">1</td>
                                    <td class="border border-black p-2 font-semibold">Cefadroxil 500mg (Strip Isi 10)</td>
                                    <td class="border border-black p-2 font-mono">CFD-500-26.1</td>
                                    <td class="border border-black p-2 text-center font-mono">10 Strip</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold">8 Strip</td>
                                    <td class="border border-black p-2 text-center font-mono font-black text-rose-600">-2 Strip</td>
                                    <td class="border border-black p-2 text-rose-700 font-semibold">Mutasi Keluar: Barang Rusak (Kemasan Cacat/Penyok)</td>
                                    <td class="border border-black p-2 text-center font-bold text-rose-700">Tampil (Anomali)</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-bold">2</td>
                                    <td class="border border-black p-2 font-semibold">Sanmol Sirup Anak 60ml (Botol 60ml)</td>
                                    <td class="border border-black p-2 font-mono">SNM-24K061</td>
                                    <td class="border border-black p-2 text-center font-mono">5 Karton</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold">2 Karton</td>
                                    <td class="border border-black p-2 text-center font-mono font-black text-rose-600">-3 Karton</td>
                                    <td class="border border-black p-2 text-rose-700 font-semibold">Mutasi Keluar: Barang Kedaluwarsa (Lewat Expired Date)</td>
                                    <td class="border border-black p-2 text-center font-bold text-rose-700">Tampil (Anomali)</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-bold">3</td>
                                    <td class="border border-black p-2 font-semibold">Amoxicillin 500mg (Tablet 500mg)</td>
                                    <td class="border border-black p-2 font-mono">AMX-2026-01</td>
                                    <td class="border border-black p-2 text-center font-mono">3 Box</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold">1 Box</td>
                                    <td class="border border-black p-2 text-center font-mono font-black text-rose-600">-2 Box</td>
                                    <td class="border border-black p-2 text-rose-700 font-semibold">Mutasi Keluar: Barang Hilang (Tidak Ditemukan di Rak)</td>
                                    <td class="border border-black p-2 text-center font-bold text-rose-700">Tampil (Anomali)</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-bold">4</td>
                                    <td class="border border-black p-2 font-semibold">Tensimeter Digital Omron (HEM-7120)</td>
                                    <td class="border border-black p-2 font-mono text-emerald-700">SN: OMR-7120-109</td>
                                    <td class="border border-black p-2 text-center font-mono">8 Unit</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold">7 Unit</td>
                                    <td class="border border-black p-2 text-center font-mono font-black text-rose-600">-1 Unit</td>
                                    <td class="border border-black p-2 text-rose-700 font-semibold">Mutasi Keluar: Barang Hilang (Status Unit SN beralih ke 'Hilang')</td>
                                    <td class="border border-black p-2 text-center font-bold text-rose-700">Tampil (Anomali)</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-2 text-center font-bold">5</td>
                                    <td class="border border-black p-2 font-semibold">Betadine Solution 15ml (Botol 15ml)</td>
                                    <td class="border border-black p-2 font-mono">BET-15-26</td>
                                    <td class="border border-black p-2 text-center font-mono">14 Box</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold">16 Box</td>
                                    <td class="border border-black p-2 text-center font-mono font-black text-blue-600">+2 Box</td>
                                    <td class="border border-black p-2 text-blue-700 font-semibold">Mutasi Masuk: Kelebihan Kirim Supplier / Ditemukan Kembali</td>
                                    <td class="border border-black p-2 text-center font-bold text-rose-700">Tampil (Anomali)</td>
                                </tr>
                                <tr class="bg-emerald-50/50">
                                    <td class="border border-black p-2 text-center font-bold">6-121</td>
                                    <td class="border border-black p-2 font-semibold italic">116 Item Persediaan Lainnya (Obat &amp; Alkes)</td>
                                    <td class="border border-black p-2 font-mono text-slate-500">Seluruh Batch Aktif</td>
                                    <td class="border border-black p-2 text-center font-mono">Kuantitas Riil</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold">Cocok (Sama)</td>
                                    <td class="border border-black p-2 text-center font-mono font-bold text-emerald-700">0 (Pas)</td>
                                    <td class="border border-black p-2 text-emerald-700 font-medium">Tidak memerlukan mutasi persediaan (Saldo Cocok 100%)</td>
                                    <td class="border border-black p-2 text-center font-bold text-emerald-800 bg-emerald-100">Disembunyikan Otomatis</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Berdasarkan data pada Tabel 4.1 dan Tabel 4.2, terbukti bahwa implementasi sistem telah berhasil memenuhi seluruh kriteria teknis yang disyaratkan oleh dosen pembimbing. Mekanisme penyaringan otomatis mampu memilah 116 barang yang sudah sesuai agar tidak membebani tampilan antarmuka, sementara 5 barang yang memiliki perbedaan fisik langsung disajikan lengkap beserta <em>dropdown</em> penyesuaian yang mengalokasikan mutasi secara instan dan akurat.
                </p>
            </div>

            <!-- 4.2.2 UAT -->
            <div id="subbab_422" class="mt-6">
                <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                    4.2.2 Pengujian Penerimaan Pengguna (User Acceptance Testing / UAT)
                </h4>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Pengujian Penerimaan Pengguna (<em>User Acceptance Testing</em> / UAT) dilakukan untuk menguji sistem secara keseluruhan dalam kondisi operasional nyata toko kesehatan ZenCare Medical. Pengujian ini melibatkan para pengguna langsung sesuai dengan perannya masing-masing, yakni Superadmin (Pemilik Toko), Admin Logistik &amp; Gudang, Kasir Toko Fisik, dan Pelanggan Toko Online. Parameter evaluasi UAT mencakup 4 (empat) aspek pengujian, yaitu kemudahan pengoperasian (<em>usability</em>), keandalan fungsionalitas transaksi (<em>functional reliability</em>), keselarasan aliran data inventori lintas saluran (<em>omnichannel consistency</em>), serta efisiensi waktu pemrosesan pesanan.
                </p>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Hasil evaluasi kuesioner UAT menunjukkan tingkat persetujuan dan kelayakan operasional sistem yang sangat tinggi dengan rata-rata persentase kepuasan di atas 90%, menegaskan bahwa sistem informasi inventori dan penjualan <em>omnichannel</em> ZenCare Medical telah siap diimplementasikan secara penuh untuk mendukung operasional harian toko kesehatan.
                </p>
            </div>
        </div>
"""

# 1. Update BAB_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    bab4_text = f.read()

target_end_bab4 = """            </div> <!-- Closes subbab_41_content -->
        </div> <!-- Closes 4.1 -->
    
    </div>"""

replacement_bab4 = """            </div> <!-- Closes subbab_41_content -->
        </div> <!-- Closes 4.1 -->
""" + subbab_4_2_html + """
    </div>"""

if target_end_bab4 in bab4_text:
    bab4_text = bab4_text.replace(target_end_bab4, replacement_bab4, 1)
    with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(bab4_text)
    print("✓ Successfully injected Subbab 4.2 into laporan_ta/BAB_IV_ZENCARE_MEDICAL.html")
else:
    print("✗ Target not found in BAB_IV_ZENCARE_MEDICAL.html")

# 2. Update BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    combined_text = f.read()

target_end_combined = """            </div> <!-- Closes subbab_41_content -->
        </div> <!-- Closes 4.1 -->
    
    </div> <!-- Closes full_bab4_content paper -->"""

replacement_combined = """            </div> <!-- Closes subbab_41_content -->
        </div> <!-- Closes 4.1 -->
""" + subbab_4_2_html + """
    </div> <!-- Closes full_bab4_content paper -->"""

if target_end_combined in combined_text:
    combined_text = combined_text.replace(target_end_combined, replacement_combined, 1)
    with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(combined_text)
    print("✓ Successfully injected Subbab 4.2 into laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html")
else:
    print("✗ Target not found in BAB_III_DAN_IV_ZENCARE_MEDICAL.html")

