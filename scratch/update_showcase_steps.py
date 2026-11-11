# update_showcase_steps.py

new_bagian_3 = '''    <!-- ============================================================================== -->
    <!-- BAGIAN 3: SHOWCASE TANGKAPAN LAYAR LANGKAH DEMI LANGKAH (STEP-BY-STEP)           -->
    <!-- ============================================================================== -->
    <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-zcBrd pb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <h2 class="text-sm font-bold text-zcTxt uppercase tracking-wide">Rekaman Visual Pengujian Langkah demi Langkah (Step-by-Step Testing Log)</h2>
            </div>
            <span class="text-[11px] text-zcMut font-medium">Dokumentasi Uji Coba Black-Box Fungsional</span>
        </div>

        <!-- LANGKAH 1: INISIASI SESI & CENTRAL SERVER FREEZE -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-slate-900 text-white font-bold flex items-center justify-center text-xs">1</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 1: Inisiasi Sesi Opname &amp; Central Server Freeze</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Server Status: LOCKED (423)</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Admin atau Superadmin membuka modul Stok Opname dan menekan tombol <em>"Mulai Sesi Stok Opname Baru"</em>. Begitu sesi aktif, sistem secara terpusat mengambil *snapshot* seluruh 121 entitas persediaan fisik dan membekukan operasional kasir POS serta checkout toko daring (*Central Server Freeze*) guna mencegah inkonsistensi data persediaan di tengah proses pencacahan rak.
            </p>
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl shadow-2xs space-y-2.5">
                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-rose-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div class="text-xs">
                            <strong class="text-rose-900 font-bold block">SESI STOK OPNAME AKTIF: OPN-20261110-001 [Tahap 1: Penghitungan Fisik]</strong>
                            <span class="text-rose-700 text-[11px]">Seluruh transaksi kasir POS offline dan toko online e-commerce terkunci di server demi konsistensi audit.</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-white text-rose-700 border border-rose-300 rounded-md text-[10px] font-bold shrink-0">Terminal POS Locked</span>
                </div>
                <div class="text-[11px] text-slate-500 flex items-center justify-between px-1">
                    <span>Aktor: <strong>Admin / Superadmin</strong></span>
                    <span class="font-mono">121 Entitas Fisik Berhasil Di-Snapshot</span>
                </div>
            </div>
        </div>

        <!-- LANGKAH 2: FORMULIR PENCACAHAN FISIK BUTA KASIR (BLIND COUNT MULTI-KONDISI) -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-4 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-600 text-white font-bold flex items-center justify-center text-xs">2</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 2: Pencacahan Fisik Rak Secara Mandiri (Strict Blind Count Multi-Kondisi)</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Peran Aktor: Kasir Lapangan</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Staf Kasir berdiri di depan rak etalase dan memasukkan kuantitas riil barang yang dipegang ke dalam 3 kolom kondisi: <strong>Kondisi Baik (Layak Jual)</strong>, <strong>Kondisi Rusak (Pecah/Sobek)</strong>, dan <strong>Kondisi Expired (Kedaluwarsa)</strong>. Sesuai prinsip <em>Blind Count</em>, Kasir <strong>tidak tahu berapa stok di komputer dan tidak menginput barang 'Hilang'</strong> (karena kasir hanya mencatat apa yang ada di depannya). Saldo sistem, reserved, dan selisih dirahasiakan total.
            </p>

            <!-- 2A: Tampilan Awal Sebelum Input (Semua 0 / Kosong & Dirahasiakan) -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-slate-800 block">2A. Tampilan Layar Kasir Saat Memulai Pencacahan (Form Masih Kosong):</span>
                <div class="p-3 bg-white border border-slate-200 rounded-xl overflow-x-auto text-[11px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b text-slate-600 font-bold">
                            <tr>
                                <th class="py-2 px-2.5">Produk &amp; Variasi</th>
                                <th class="py-2 px-2 text-center">Satuan</th>
                                <th class="py-2 px-2 text-center bg-emerald-50 text-emerald-800">Kondisi Baik</th>
                                <th class="py-2 px-2 text-center bg-amber-50 text-amber-800">Kondisi Rusak</th>
                                <th class="py-2 px-2 text-center bg-rose-50 text-rose-800">Kondisi Expired</th>
                                <th class="py-2 px-2 text-center text-slate-400">Stok Sistem Komputer</th>
                                <th class="py-2 px-2 text-center text-slate-400">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-slate-700">
                            <tr>
                                <td class="py-2 px-2.5 font-semibold">Cefadroxil 500mg (CFD-500-26)</td>
                                <td class="py-2 px-2 text-center">Dus/Box</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">[0]</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">[0]</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">[0]</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400 italic bg-slate-50">Dirahasiakan (Blind)</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400 italic bg-slate-50">Dirahasiakan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2B: Tampilan Setelah Kasir Menginput Kondisi Fisik Riil Sesuai Temuan Lapangan -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-slate-800 block">2B. Tampilan Setelah Kasir Menginput Kuantitas Fisik Riil di Rak:</span>
                <div class="p-3 bg-white border border-slate-200 rounded-xl overflow-x-auto text-[11px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b text-slate-600 font-bold">
                            <tr>
                                <th class="py-2 px-2.5">Produk &amp; Temuan Lapangan Kasir</th>
                                <th class="py-2 px-2 text-center bg-emerald-50 text-emerald-800">Fisik Baik</th>
                                <th class="py-2 px-2 text-center bg-amber-50 text-amber-800">Fisik Rusak</th>
                                <th class="py-2 px-2 text-center bg-rose-50 text-rose-800">Fisik Expired</th>
                                <th class="py-2 px-2 text-center">Total Fisik Ditemukan</th>
                                <th class="py-2 px-2 text-center">Status Entri Kasir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-slate-800">
                            <!-- 1. Cefadroxil Dosen U4 -->
                            <tr>
                                <td class="py-2 px-2.5">
                                    <div class="font-bold text-slate-900">Cefadroxil 500mg Kapsul</div>
                                    <span class="text-[10px] text-slate-500">Temuan Kasir: 5 bagus, 3 kemasan sobek/rusak, 2 lewat ED</span>
                                </td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-emerald-700 bg-emerald-50/30">5 Strip</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-amber-700 bg-amber-50/30">3 Strip</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-700 bg-rose-50/30">2 Strip</td>
                                <td class="py-2 px-2 text-center font-mono font-bold">10 Strip</td>
                                <td class="py-2 px-2 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tersimpan</span></td>
                            </tr>
                            <!-- 2. Sanmol Sirup ED -->
                            <tr>
                                <td class="py-2 px-2.5">
                                    <div class="font-bold text-slate-900">Sanmol Sirup Anak 60ml</div>
                                    <span class="text-[10px] text-rose-600 font-semibold">Batch Kedaluwarsa: Opsi Baik Terkunci 0. Kasir temukan 12 botol</span>
                                </td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400 bg-slate-100">[Kunci 0]</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">0</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-700 bg-rose-50/30">12 Botol</td>
                                <td class="py-2 px-2 text-center font-mono font-bold">12 Botol</td>
                                <td class="py-2 px-2 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tersimpan</span></td>
                            </tr>
                            <!-- 3. Amoxicillin Hilang -->
                            <tr>
                                <td class="py-2 px-2.5">
                                    <div class="font-bold text-slate-900">Amoxicillin 500mg</div>
                                    <span class="text-[10px] text-slate-500">Temuan Kasir: Hanya menemukan 23 strip di rak</span>
                                </td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-emerald-700 bg-emerald-50/30">23 Strip</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">0</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">0</td>
                                <td class="py-2 px-2 text-center font-mono font-bold">23 Strip</td>
                                <td class="py-2 px-2 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tersimpan</span></td>
                            </tr>
                            <!-- 4. Omron Alkes SN -->
                            <tr>
                                <td class="py-2 px-2.5">
                                    <div class="font-bold text-slate-900">Tensimeter Digital Omron (Alkes SN)</div>
                                    <span class="text-[10px] text-slate-500">Temuan Kasir: Unit etalase kosong (tidak ada unit fisik)</span>
                                </td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-slate-500">0 Unit</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">0</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">[Alkes: -]</td>
                                <td class="py-2 px-2 text-center font-mono font-bold">0 Unit</td>
                                <td class="py-2 px-2 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tersimpan</span></td>
                            </tr>
                            <!-- 5. Betadine Surplus -->
                            <tr>
                                <td class="py-2 px-2.5">
                                    <div class="font-bold text-slate-900">Betadine Solution 15ml</div>
                                    <span class="text-[10px] text-slate-500">Temuan Kasir: Menemukan 20 botol fisik di rak bawah</span>
                                </td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-emerald-700 bg-emerald-50/30">20 Botol</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">0</td>
                                <td class="py-2 px-2 text-center font-mono text-slate-400">0</td>
                                <td class="py-2 px-2 text-center font-mono font-bold">20 Botol</td>
                                <td class="py-2 px-2 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tersimpan</span></td>
                            </tr>
                            <!-- 6. 116 Barang Cocok -->
                            <tr class="bg-slate-50">
                                <td class="py-2 px-2.5 italic text-slate-600">Sebanyak 116 Entitas Persediaan Lainnya (Paracetamol, Vit C, dll)</td>
                                <td colspan="3" class="py-2 px-2 text-center font-medium text-emerald-700">Diinput Kasir Sesuai Kuantitas Fisik Rak</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-slate-700">Pas</td>
                                <td class="py-2 px-2 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">116 Tersimpan</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LANGKAH 3: PEMANTAUAN PROGRES OLEH ADMIN GUDANG & TUTUP PENGHITUNGAN -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs">3</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 3: Pemantauan Progres Kasir &amp; Penutupan Penghitungan Fisik</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-blue-800 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">Peran Aktor: Admin Gudang</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Sesuai SOP pengendalian internal, staf gudang (Admin) <strong>dilarang melakukan penghitungan fisik</strong> untuk mencegah manipulasi stok. Pada tahap Penghitungan, Admin memantau halaman progres pencatatan kasir. Setelah kasir selesai mengisi seluruh rak (121/121 baris), Admin menekan tombol <strong>"Tutup Penghitungan &amp; Buka Verifikasi"</strong> untuk mengunci form kasir dan melangkah ke rekonsiliasi.
            </p>
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl shadow-2xs space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800">Progres Pencacahan Lapangan oleh Kasir:</span>
                    <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">121 / 121 Baris Selesai Dihitung (100%)</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-600 h-2 rounded-full w-full"></div>
                </div>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-600">
                        Seluruh entitas fisik telah dicatat oleh kasir. Siap dikunci dan dialihkan ke verifikasi selisih.
                    </div>
                    <button type="button" class="px-3.5 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg shadow-2xs shrink-0 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- LANGKAH 4: ANTARMUKA VERIFIKASI ANOMALI & KARTU ESTIMASI KERUGIAN -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-4 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center text-xs">4</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 4: Antarmuka Verifikasi Anomali Selisih &amp; Estimasi Nilai Kerugian</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-indigo-800 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200">Tahap 2: Verifikasi &amp; Rekonsiliasi</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Setelah penghitungan ditutup, Admin atau Superadmin masuk ke antarmuka Verifikasi. Dua fitur utama langsung aktif:
                (1) <strong>Penyaringan Otomatis Anomali (Auto-Filter):</strong> Sebanyak 116 barang cocok (selisih 0) otomatis disembunyikan sehingga tabel kerja hanya menampilkan 5 barang anomali.
                (2) <strong>Kartu Estimasi Nilai Acuan Kerugian Persediaan:</strong> Sistem otomatis menghitung total nilai kerugian fisik berdasarkan perkalian kuantitas terdampak (Rusak, Expired, Hilang) dengan harga acuan produk.
            </p>

            <!-- Kartu Estimasi Nilai Acuan Kerugian Persediaan -->
            <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-2xs space-y-3">
                <div class="flex items-center justify-between border-b pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                        <strong class="text-xs font-bold text-slate-900 uppercase tracking-wide">Estimasi Nilai Acuan Dampak Persediaan (Non-Akuntansi)</strong>
                    </div>
                    <span class="text-[10px] text-slate-400 italic">Nilai acuan operasional manajerial internal</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 text-xs">
                    <div class="p-2.5 bg-slate-50 border rounded-lg">
                        <span class="text-[10px] text-slate-500 font-semibold block">Kerugian Barang Rusak:</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Rp 45.000 (3 Strip Cefadroxil)</span>
                    </div>
                    <div class="p-2.5 bg-slate-50 border rounded-lg">
                        <span class="text-[10px] text-slate-500 font-semibold block">Kerugian Barang Expired:</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Rp 274.000 (2 Cef + 12 Sanmol)</span>
                    </div>
                    <div class="p-2.5 bg-slate-50 border rounded-lg">
                        <span class="text-[10px] text-slate-500 font-semibold block">Kerugian Barang Hilang:</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Rp 585.000 (2 Amox + 3 Sanmol + 1 Omron)</span>
                    </div>
                    <div class="p-2.5 bg-slate-100 border border-slate-300 rounded-lg">
                        <span class="text-[10px] text-slate-600 font-bold block">Total Nilai Acuan Kerugian:</span>
                        <span class="text-sm font-black text-slate-900 mt-0.5 block">Rp 904.000</span>
                    </div>
                </div>
            </div>

            <!-- Tabel Fokus 5 Anomali Terfilter Otomatis -->
            <div class="space-y-1.5">
                <div class="p-2.5 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-between text-xs text-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                        <span><strong>Fokus Anomali Terfilter:</strong> Menampilkan <strong>5 barang berselisih</strong> (Sebanyak <strong>116 barang cocok/0 selisih disembunyikan otomatis</strong>).</span>
                    </div>
                    <span class="text-[11px] font-bold text-slate-600">5 Item Perlu Tindakan Mutasi</span>
                </div>

                <div class="p-3 bg-white border border-slate-200 rounded-xl overflow-x-auto text-[11px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b text-slate-600 font-bold">
                            <tr>
                                <th class="py-2 px-2.5">Produk &amp; Variasi</th>
                                <th class="py-2 px-2 text-center">Fisik Baik</th>
                                <th class="py-2 px-2 text-center text-amber-700">Fisik Rusak</th>
                                <th class="py-2 px-2 text-center text-rose-700">Fisik Expired</th>
                                <th class="py-2 px-2 text-center">Stok Sistem</th>
                                <th class="py-2 px-2 text-center">Selisih Buku</th>
                                <th class="py-2 px-2 text-center">Rincian Analisis Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-slate-800">
                            <!-- 1. Cefadroxil -->
                            <tr>
                                <td class="py-2 px-2.5 font-bold">Cefadroxil 500mg (CFD-500-26)</td>
                                <td class="py-2 px-2 text-center font-mono">5</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-amber-700">3</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-700">2</td>
                                <td class="py-2 px-2 text-center font-mono">10</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-600">-5 (Fisik Baik 5 vs Sistem 10)</td>
                                <td class="py-2 px-2 text-slate-600">Total fisik pas 10: 3 Rusak &amp; 2 Expired siap dimutasi</td>
                            </tr>
                            <!-- 2. Sanmol Sirup -->
                            <tr>
                                <td class="py-2 px-2.5 font-bold">Sanmol Sirup Anak 60ml</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-700">12</td>
                                <td class="py-2 px-2 text-center font-mono">15</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-600">-15 (Batch Kedaluwarsa)</td>
                                <td class="py-2 px-2 text-slate-600">12 Expired di rak + 3 botol defisit hilang tanpa jejak</td>
                            </tr>
                            <!-- 3. Amoxicillin -->
                            <tr>
                                <td class="py-2 px-2.5 font-bold">Amoxicillin 500mg</td>
                                <td class="py-2 px-2 text-center font-mono">23</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">25</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-600">-2 Strip (Defisit)</td>
                                <td class="py-2 px-2 text-slate-600">2 strip tidak ada di rak $\rightarrow$ mutasi hilang</td>
                            </tr>
                            <!-- 4. Omron Alkes SN -->
                            <tr>
                                <td class="py-2 px-2.5 font-bold">Tensimeter Omron (SN: OMR-7120-109)</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">-</td>
                                <td class="py-2 px-2 text-center font-mono">1</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-rose-600">-1 Unit (Defisit)</td>
                                <td class="py-2 px-2 text-slate-600">Unit SN tidak ada di rak $\rightarrow$ mutasi hilang SN</td>
                            </tr>
                            <!-- 5. Betadine -->
                            <tr>
                                <td class="py-2 px-2.5 font-bold">Betadine Solution 15ml</td>
                                <td class="py-2 px-2 text-center font-mono">20</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">0</td>
                                <td class="py-2 px-2 text-center font-mono">18</td>
                                <td class="py-2 px-2 text-center font-mono font-bold text-blue-600">+2 Botol (Surplus)</td>
                                <td class="py-2 px-2 text-slate-600">Kelebihan kirim supplier $\rightarrow$ koreksi surplus</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LANGKAH 5: EKSEKUSI REKONSILIASI MUTASI 1-KLIK & MODAL -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-xs">5</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 5: Eksekusi Rekonsiliasi Mutasi 1-Klik oleh Admin</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Tindakan Eksekusi Mutasi</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Admin memvalidasi usulan kondisi dari kasir dan mengeksekusi mutasi dengan 1 klik atau melalui modal penyesuaian:
                (1) Klik <strong>"Mutasi Rusak"</strong> untuk memotong stok 3 Strip Cefadroxil.
                (2) Klik <strong>"Mutasi Expired"</strong> untuk memotong 2 Strip Cefadroxil dan 12 Botol Sanmol Sirup.
                (3) Klik <strong>"Mutasi Hilang"</strong> untuk mencatat 2 Strip Amoxicillin, 3 Botol Sanmol, dan 1 Unit Tensimeter Omron dengan memasukkan alasan wajib.
                (4) Klik <strong>"Koreksi Lebih"</strong> untuk menambah saldo 2 Botol Betadine.
                <em>Catatan:</em> Jika kasir salah input, Admin berhak merevisi angka usulan sebelum tombol mutasi ditekan.
            </p>
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl shadow-2xs text-[11px] space-y-2">
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between">
                    <span class="font-bold text-emerald-900">Hasil Eksekusi Rekonsiliasi Admin:</span>
                    <span class="font-mono text-emerald-800 font-bold">5 / 5 Anomali Berhasil Dimutasi</span>
                </div>
                <div class="space-y-1 font-mono text-[10px]">
                    <div class="p-1.5 bg-slate-50 border rounded flex justify-between">
                        <span>[1-Klik] Mutasi Barang Rusak: Cefadroxil 500mg</span>
                        <span class="text-amber-800 font-bold">Terpotong -3 Strip | Kartu Stok: Barang Rusak</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 border rounded flex justify-between">
                        <span>[1-Klik] Mutasi Barang Expired: Sanmol Sirup Anak 60ml</span>
                        <span class="text-rose-800 font-bold">Terpotong -12 Botol | Kartu Stok: Barang Kedaluwarsa</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 border rounded flex justify-between">
                        <span>[Modal Alasan] Mutasi Barang Hilang: Amoxicillin 500mg</span>
                        <span class="text-slate-800 font-bold">Terpotong -2 Strip | Keterangan: Hilang di rak</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 border rounded flex justify-between">
                        <span>[Modal Alasan] Mutasi Barang Hilang SN: Tensimeter Omron</span>
                        <span class="text-slate-800 font-bold">Terpotong -1 Unit (OMR-7120-109) | Status SN: Hilang</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 border rounded flex justify-between">
                        <span>[Modal Alasan] Koreksi Surplus: Betadine Solution 15ml</span>
                        <span class="text-blue-800 font-bold">Bertambah +2 Botol | Keterangan: Kelebihan kirim supplier</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- LANGKAH 6: SINKRONISASI KARTU STOK & SELESAI SESI (BUKA KUNCI SERVER) -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-700 text-white font-bold flex items-center justify-center text-xs">6</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 6: Sinkronisasi Buku Besar Kartu Stok &amp; Pembukaan Kunci Sistem</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Audit Trail Selesai</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Setelah 100% selisih berhasil direkonsiliasi, tombol <strong>"Selesaikan Sesi (Buka Kunci)"</strong> menjadi aktif. Admin atau Superadmin menekan tombol ini untuk menyelesaikan sesi. Server secara terpusat mencabut pembekuan transaksi (*Server Unfreeze*). Kasir POS offline dan transaksi checkout e-commerce normal kembali dengan saldo persediaan fisik baru yang telah tersinkronisasi sempurna.
            </p>
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl shadow-2xs space-y-2 text-[11px]">
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <strong class="text-emerald-950">SESI OPNAME BERHASIL DISELESAIKAN: OPN-20261110-001</strong>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px]">Status: Selesai</span>
                </div>
                <div class="text-[11px] text-slate-600 flex items-center justify-between px-1">
                    <span>Kasir POS Offline: <strong class="text-emerald-700">Aktif Normal</strong></span>
                    <span>Checkout Toko Daring: <strong class="text-emerald-700">Aktif Normal</strong></span>
                    <span>Kartu Stok: <strong class="text-slate-800">5 Mutasi Terbukukan</strong></span>
                </div>
            </div>
        </div>

        <!-- LANGKAH 7: UTILITAS RESET DATABASE DEMO -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-slate-800 text-white font-bold flex items-center justify-center text-xs">7</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 7: Utilitas Reset Database Demo (Baseline Clean State)</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">Perkakas Khusus Demo</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Untuk kebutuhan demonstrasi seminar/sidang, penguji atau mahasiswa dapat mengembalikan basis data ke kondisi awal bersih (*fresh state*) kapan saja dengan menekan tombol <strong>"[Utilitas Demo] Reset Data Demo"</strong> pada antarmuka aplikasi atau dengan mengimpor berkas <code>database/Demo_Bersih.sql</code> melalui phpMyAdmin. Seluruh sesi uji coba, log mutasi demo, dan perubahan saldo dibersihkan seketika tanpa merusak struktur database.
            </p>
            <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-2xs flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span class="text-slate-700 font-semibold">Tersedia untuk peran: <strong>Admin</strong> dan <strong>Superadmin</strong></span>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[10px] font-bold border border-slate-300">File Dump: database/Demo_Bersih.sql</span>
            </div>
        </div>

    </div>
'''

with open('inventori/dokumentasi_testing.php', 'r', encoding='utf-8') as f:
    code = f.read()

m_start = '<!-- BAGIAN 3: SHOWCASE TANGKAPAN LAYAR LANGKAH DEMI LANGKAH            -->'
m_end = '<?php\nlayoutFooter();\n?>'

pos_s = code.find(m_start)
pos_e = code.find(m_end)

if pos_s == -1 or pos_e == -1:
    print(f"Error: Markers not found! pos_s={pos_s}, pos_e={pos_e}")
    exit(1)

new_code = code[:pos_s] + new_bagian_3 + '\n</div>\n\n' + m_end

with open('inventori/dokumentasi_testing.php', 'w', encoding='utf-8') as f:
    f.write(new_code)

print("Successfully updated Bagian 3 in dokumentasi_testing.php with 7 detailed step-by-step visual logs!")

