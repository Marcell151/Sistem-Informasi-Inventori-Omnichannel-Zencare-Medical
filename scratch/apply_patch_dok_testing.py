# Script to update inventori/dokumentasi_testing.php with clean corporate design,
# zero academic/dosen phrases, fixed HTML tags, and desktop browser mockup for Langkah 4.

with open('inventori/dokumentasi_testing.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove extra </div> at line 225
content = content.replace('''            <div class="p-3 bg-slate-100 border border-slate-300 rounded-lg flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">Total Akumulasi Beban Kerugian Selisih Fisik:</span>
                <span class="text-base font-bold text-rose-700 font-mono">Rp 904.000</span>
            </div>
        </div>
    </div>
    </div>''', '''            <div class="p-3 bg-slate-100 border border-slate-300 rounded-lg flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">Total Akumulasi Beban Kerugian Selisih Fisik:</span>
                <span class="text-base font-bold text-rose-700 font-mono">Rp 904.000</span>
            </div>
        </div>
    </div>''')

# 2. Clean comment (BAB 4) -> MATRIKS PENGUJIAN BLACK-BOX TESTING
content = content.replace(
    '<!-- BAGIAN 2: TABEL MATRIKS PENGUJIAN BLACK-BOX TESTING (BAB 4)       -->',
    '<!-- BAGIAN 2: TABEL MATRIKS PENGUJIAN BLACK-BOX TESTING               -->'
)

# 3. Replace Langkah 4 with the Desktop Browser Mockup Frame
old_langkah_4_start = '<!-- LANGKAH 4: ANTARMUKA VERIFIKASI ANOMALI & KARTU ESTIMASI KERUGIAN -->'
old_langkah_5_start = '<!-- LANGKAH 5: EKSEKUSI REKONSILIASI MUTASI 1-KLIK & MODAL -->'

idx4 = content.find(old_langkah_4_start)
idx5 = content.find(old_langkah_5_start)

if idx4 != -1 and idx5 != -1:
    new_langkah_4 = '''<!-- LANGKAH 4: ANTARMUKA VERIFIKASI ANOMALI & KARTU ESTIMASI KERUGIAN -->
        <div class="border border-slate-200 rounded-2xl p-5 space-y-4 bg-slate-50/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center text-xs">4</span>
                    <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah 4: Antarmuka Verifikasi Anomali Selisih &amp; Estimasi Nilai Kerugian</h3>
                </div>
                <span class="text-[11px] font-mono font-bold text-indigo-800 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200">Peran Aktor: Admin / Superadmin</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Setelah tahap penghitungan fisik ditutup, verifikator (Admin atau Superadmin) membuka antarmuka verifikasi. Sistem secara otomatis menjalankan <strong>Penyaringan Otomatis (Filter Focus)</strong>: Sebanyak 116 produk yang cocok (selisih 0) langsung disembunyikan dari tabel, sehingga verifikator dapat langsung memusatkan perhatian pada <strong>5 produk anomali berselisih</strong> untuk dieksekusi mutasinya. Estimasi nilai kerugian dihitung otomatis dari kuantitas defisit dikalikan harga modal beli (HPP).
            </p>

            <!-- Mockup Browser Desktop Window Frame untuk Langkah 4 -->
            <div class="border border-slate-300 rounded-xl shadow-md overflow-hidden bg-slate-100">
                <!-- Title Bar -->
                <div class="bg-slate-200 px-3 py-2 border-b border-slate-300 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-rose-400 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
                    </div>
                    <div class="bg-white px-4 py-1 rounded text-[11px] font-mono text-slate-600 border border-slate-300 flex items-center gap-2 max-w-md w-full justify-center shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>http://localhost/inventory_zencare/inventori/stok_opname.php [Tahap: Verifikasi &amp; Rekonsiliasi]</span>
                    </div>
                    <span class="text-[10px] text-slate-500 font-semibold hidden sm:inline">Peran: Verifikator (Admin / Superadmin)</span>
                </div>

                <!-- Browser Canvas Body -->
                <div class="bg-white p-3.5 space-y-3">
                    <!-- Filter Status & Notification -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2 text-slate-800">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600 shrink-0"></span>
                            <span><strong>Penyaringan Otomatis Fokus Anomali:</strong> Menampilkan <strong>5 barang berselisih</strong> (Sebanyak <strong>116 barang cocok / 0 selisih disembunyikan otomatis</strong>).</span>
                        </div>
                        <span class="text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 shrink-0">5 Item Perlu Mutasi</span>
                    </div>

                    <!-- Ringkasan Valuasi Kerugian di Header Verifikasi -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[10px] text-slate-500 font-semibold block uppercase">Beban Rusak</span>
                            <span class="text-xs font-bold text-slate-900 mt-0.5 block">Rp 45.000</span>
                            <span class="text-[10px] text-slate-400">3 Strip Cefadroxil</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[10px] text-slate-500 font-semibold block uppercase">Beban Expired</span>
                            <span class="text-xs font-bold text-slate-900 mt-0.5 block">Rp 274.000</span>
                            <span class="text-[10px] text-slate-400">2 Cef + 12 Sanmol</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[10px] text-slate-500 font-semibold block uppercase">Beban Hilang</span>
                            <span class="text-xs font-bold text-slate-900 mt-0.5 block">Rp 585.000</span>
                            <span class="text-[10px] text-slate-400">6 Dus Amox + 1 Omr</span>
                        </div>
                        <div class="p-2.5 bg-slate-100 border border-slate-300 rounded-lg">
                            <span class="text-[10px] text-slate-600 font-bold block uppercase">Total Kerugian HPP</span>
                            <span class="text-sm font-black text-rose-700 mt-0.5 block font-mono">Rp 904.000</span>
                            <span class="text-[10px] text-emerald-700 font-semibold">Surplus: Rp 0 (Bukan Rugi)</span>
                        </div>
                    </div>

                    <!-- Tabel 5 Baris Anomali dengan Outline Merah Tebal -->
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left border-collapse text-[11px]">
                            <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 font-bold">
                                <tr>
                                    <th class="py-2.5 px-3">Produk &amp; Batch / Serial Number</th>
                                    <th class="py-2.5 px-2 text-center w-16">Satuan</th>
                                    <th class="py-2.5 px-2 text-center w-16">Sistem</th>
                                    <th class="py-2.5 px-2 text-center w-28">Fisik (B/R/E)</th>
                                    <th class="py-2.5 px-2 text-center w-24">Selisih</th>
                                    <th class="py-2.5 px-2 text-right w-24">Harga Modal</th>
                                    <th class="py-2.5 px-2 text-right w-24">Subtotal Rugi</th>
                                    <th class="py-2.5 px-3 text-center w-44">Aksi Mutasi Rekonsiliasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <!-- Row 1: Cefadroxil (Box Merah Outline) -->
                                <tr class="bg-rose-50/70 border-2 border-rose-500">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px] uppercase">Uji Anomali</span>
                                            <strong class="text-slate-900">Cefadroxil 500mg Kapsul</strong>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">Batch: CEF-202401 &bull; Satuan: Strip (Dus isi 10)</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-center text-slate-600">Strip</td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-slate-700">10</td>
                                    <td class="py-2.5 px-2 text-center font-mono text-[10px]">
                                        <span class="text-emerald-700 font-bold">5 Baik</span>, 
                                        <span class="text-amber-700 font-bold">3 Rsk</span>, 
                                        <span class="text-rose-700 font-bold">2 Exp</span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-rose-600">-5 Strip</td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-600 text-[10px]">Rp 9.000</td>
                                    <td class="py-2.5 px-2 text-right font-mono font-bold text-rose-700">Rp 45.000</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1 flex-wrap">
                                            <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-bold text-[9px] shadow-2xs">Mutasi Rusak 3</span>
                                            <span class="px-2 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px] shadow-2xs">Mutasi Exp 2</span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Row 2: Sanmol Sirup (Box Merah Outline) -->
                                <tr class="bg-rose-50/70 border-2 border-rose-500">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px] uppercase">Uji Anomali</span>
                                            <strong class="text-slate-900">Sanmol Sirup Anak 60ml</strong>
                                        </div>
                                        <div class="text-[10px] text-rose-700 font-mono mt-0.5">Batch: SNM-202309 (Kedaluwarsa) &bull; Satuan: Botol</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-center text-slate-600">Botol</td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-slate-700">15</td>
                                    <td class="py-2.5 px-2 text-center font-mono text-[10px]">
                                        <span class="text-slate-400">0 Baik</span>, 
                                        <span class="text-slate-400">0 Rsk</span>, 
                                        <span class="text-rose-700 font-bold">12 Exp</span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-rose-600">-15 Botol</td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-600 text-[10px]">Rp 18.267</td>
                                    <td class="py-2.5 px-2 text-right font-mono font-bold text-rose-700">Rp 274.000</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1 flex-wrap">
                                            <span class="px-2 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px] shadow-2xs">Mutasi Exp 12</span>
                                            <span class="px-2 py-0.5 rounded bg-slate-700 text-white font-bold text-[9px] shadow-2xs">Mutasi Hilang 3</span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Row 3: Amoxicillin (Box Merah Outline) -->
                                <tr class="bg-rose-50/70 border-2 border-rose-500">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px] uppercase">Uji Anomali</span>
                                            <strong class="text-slate-900">Amoxicillin 500mg</strong>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">Batch: AMX-202405 &bull; Satuan: Dus (Isi 10 Strip)</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-center text-slate-600">Dus</td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-slate-700">29</td>
                                    <td class="py-2.5 px-2 text-center font-mono text-[10px]">
                                        <span class="text-emerald-700 font-bold">23 Baik</span>, 
                                        <span class="text-slate-400">0 Rsk</span>, 
                                        <span class="text-slate-400">0 Exp</span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-rose-600">-6 Dus (Defisit)</td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-600 text-[10px]">Rp 97.500</td>
                                    <td class="py-2.5 px-2 text-right font-mono font-bold text-rose-700">Rp 585.000</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2.5 py-1 rounded bg-slate-800 text-white font-bold text-[10px] shadow-2xs">Mutasi Hilang 6 Dus</span>
                                    </td>
                                </tr>

                                <!-- Row 4: Omron Tensimeter (Box Merah Outline) -->
                                <tr class="bg-rose-50/70 border-2 border-rose-500">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px] uppercase">Uji Anomali</span>
                                            <strong class="text-slate-900">Tensimeter Digital Omron</strong>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">Komoditas: Alkes (Serial Number) &bull; SN: OMR-7120-109</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-center text-slate-600">Unit</td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-slate-700">1</td>
                                    <td class="py-2.5 px-2 text-center font-mono text-[10px]">
                                        <span class="text-slate-400">0 Fisik di Rak</span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-rose-600">-1 Unit (Defisit)</td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-600 text-[10px]">Rp 450.000</td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-700 font-semibold text-[10px]">Unit SN Hilang</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2.5 py-1 rounded bg-slate-800 text-white font-bold text-[10px] shadow-2xs">Pilih SN &amp; Mutasi Hilang</span>
                                    </td>
                                </tr>

                                <!-- Row 5: Betadine Solution (Box Merah Outline) -->
                                <tr class="bg-rose-50/70 border-2 border-rose-500">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-blue-600 text-white font-bold text-[9px] uppercase">Uji Anomali</span>
                                            <strong class="text-slate-900">Betadine Solution 15ml</strong>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">Batch: BTD-202401 &bull; Satuan: Botol</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-center text-slate-600">Botol</td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-slate-700">18</td>
                                    <td class="py-2.5 px-2 text-center font-mono text-[10px]">
                                        <span class="text-emerald-700 font-bold">20 Baik</span>, 
                                        <span class="text-slate-400">0 Rsk</span>, 
                                        <span class="text-slate-400">0 Exp</span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-blue-600">+2 Botol (Surplus)</td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-600 text-[10px]">Rp 22.000</td>
                                    <td class="py-2.5 px-2 text-right font-mono font-bold text-emerald-700">Rp 0 (Surplus)</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2.5 py-1 rounded bg-blue-600 text-white font-bold text-[10px] shadow-2xs">Koreksi Surplus (+2)</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-300 font-bold">
                                <tr>
                                    <td colspan="6" class="py-2.5 px-3 text-right uppercase text-[10px] text-slate-600">
                                        Total Akumulasi Kerugian Defisit Persediaan (HPP Modal):
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-mono text-rose-700 text-xs">
                                        Rp 904.000
                                    </td>
                                    <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">
                                        5 / 5 Anomali Siap Rekonsiliasi
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-[11px] text-slate-500 flex items-center justify-between pt-1">
                        <span>* Garis kotak border merah tebal menandai 5 baris produk berselisih yang perlu ditindaklanjuti.</span>
                        <span class="font-semibold text-slate-700">116 produk lainnya yang cocok (0 selisih) disembunyikan otomatis oleh sistem</span>
                    </div>
                </div>
            </div>
        </div>

        '''
    content = content[:idx4] + new_langkah_4 + content[idx5:]

with open('inventori/dokumentasi_testing.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated inventori/dokumentasi_testing.php successfully!")

