import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace container_alokasi_defisit HTML
old_defisit_start = '<!-- SECTION ALOKASI ALASAN DEFISIT (Minus) -->'
old_defisit_end = '<!-- SECTION SURPLUS / KELEBIHAN FISIK (Plus) -->'

idx_start = content.find(old_defisit_start)
idx_end = content.find(old_defisit_end)

if idx_start == -1 or idx_end == -1:
    print("Error: Could not locate defisit section in stok_opname.php")
    exit(1)

new_defisit_html = """<!-- SECTION ALOKASI ALASAN DEFISIT (Minus) -->
                        <div id="container_alokasi_defisit" class="space-y-3">
                            <div>
                                <label class="block font-bold text-slate-800 text-xs mb-1">Pilih Kategori Beban Selisih (Wajib):</label>
                                <p class="text-[11px] text-slate-500">Pilih satu alasan utama pembebanan selisih ke Kartu Stok toko:</p>
                            </div>

                            <!-- Pilihan Kategori Utama via Radio Cards yang Jelas & Rapi -->
                            <div class="grid grid-cols-1 gap-2" id="kategori_radio_cards">
                                <!-- Option 1: Rusak -->
                                <label id="card_kat_rusak" onclick="selectKategoriCard('rusak')" class="relative flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="rusak" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-rose-700">1. Mutasi Barang Rusak</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 font-semibold border border-rose-200">Beban Kerusakan</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kemasan pecah, bocor, cacat fisik, atau rusak segel di etalase/gudang.</p>
                                    </div>
                                </label>

                                <!-- Option 2: Hilang -->
                                <label id="card_kat_hilang" onclick="selectKategoriCard('hilang')" class="relative flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="hilang" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-amber-700">2. Mutasi Barang Hilang</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 font-semibold border border-amber-200">Beban Kehilangan</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Fisik tidak ditemukan di rak etalase maupun gudang setelah pencarian ulang.</p>
                                    </div>
                                </label>

                                <!-- Option 3: Kedaluwarsa (Khusus Obat) -->
                                <label id="card_kat_expired" onclick="selectKategoriCard('expired')" class="relative flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="expired" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-purple-700">3. Mutasi Barang Kedaluwarsa</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold border border-purple-200">Beban Expired</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Batch telah melewati batas tanggal kedaluwarsa dan harus dimusnahkan.</p>
                                    </div>
                                </label>

                                <!-- Option 4: Salah Catat -->
                                <label id="card_kat_salah_catat" onclick="selectKategoriCard('salah_catat')" class="relative flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="salah_catat" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-slate-700">4. Koreksi Salah Catat Administrasi</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold border border-slate-200">Koreksi Buku</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kekeliruan input saat penerimaan atau penjualan di masa lalu.</p>
                                    </div>
                                </label>

                                <!-- Option 5: Lainnya -->
                                <label id="card_kat_lainnya" onclick="selectKategoriCard('lainnya')" class="relative flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="lainnya" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-indigo-700">5. Alasan Lainnya (Kustom)</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">Alasan Khusus</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Penyebab khusus di luar opsi di atas (misal sampel uji, terendam air).</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Input Teks Alasan Lainnya -->
                            <div id="box_teks_alasan_lainnya" class="hidden pt-1">
                                <label class="block font-semibold text-slate-700 text-xs mb-1">Tuliskan Keterangan Alasan Khusus:</label>
                                <input type="text" name="teks_alasan_lainnya" id="teks_alasan_lainnya" placeholder="Contoh: Kerusakan Kemasan Akibat Lembap / Sampel Uji..." class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-white focus:ring-2 focus:ring-slate-900">
                            </div>

                            <!-- Banner Konfirmasi Alokasi Penuh Sesuai Kartu yang Dipilih -->
                            <div id="banner_alokasi_penuh" class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-500 block text-[10px] font-semibold uppercase tracking-wider">Pembebanan Defisit:</span>
                                    <span class="font-bold text-slate-900" id="teks_alokasi_penuh">100% dialokasikan ke kategori terpilih</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs" id="badge_alokasi_penuh_qty">- Unit</span>
                            </div>

                            <!-- Opsi Lanjutan: Split Kuantitas Multi-Kategori (Accordion/Toggle Bersih) -->
                            <div class="pt-2 border-t border-slate-200">
                                <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-slate-700 hover:text-slate-900">
                                    <input type="checkbox" id="toggle_split_kategori" onchange="toggleSplitKategoriMode(this.checked)" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer">
                                    <span>Bagi kuantitas ke beberapa alasan berbeda (Split Kuantitas Multi-Kategori)</span>
                                </label>
                                <p class="text-[10px] text-slate-400 pl-6 mt-0.5">Aktifkan hanya jika dalam satu baris selisih terdapat kombinasi penyebab (contoh: 1 unit rusak dan 1 unit hilang).</p>

                                <!-- Box Input Breakdown Kuantitas (Hanya Muncul Jika Toggle Dicentang) -->
                                <div id="box_split_kuantitas" class="hidden mt-3 space-y-2 bg-slate-50 border border-slate-200 p-3 rounded-xl">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-200">
                                        <span class="text-[11px] font-bold text-slate-700">Rincian Pembagian Kuantitas:</span>
                                        <span id="badge_alokasi_counter" class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"></span>
                                    </div>

                                    <!-- 1. Rusak -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-rose-700">Barang Rusak</span>
                                            <span class="block text-[10px] text-slate-400">Kemasan pecah / cacat fisik</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_rusak" id="alokasi_rusak" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 2. Hilang -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-amber-700">Barang Hilang</span>
                                            <span class="block text-[10px] text-slate-400">Tidak ditemukan di rak</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_hilang" id="alokasi_hilang" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 3. Expired -->
                                    <div id="row_alokasi_expired" class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-purple-700">Barang Kedaluwarsa</span>
                                            <span class="block text-[10px] text-slate-400">Lewat tanggal ED</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_expired" id="alokasi_expired" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 4. Salah Catat -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-slate-700">Koreksi Salah Catat</span>
                                            <span class="block text-[10px] text-slate-400">Administrasi masa lalu</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_salah_catat" id="alokasi_salah_catat" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 5. Lainnya -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-indigo-700">Alasan Lainnya</span>
                                            <span class="block text-[10px] text-slate-400">Penyebab khusus</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_lainnya" id="alokasi_lainnya" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        """

content = content[:idx_start] + new_defisit_html + content[idx_end:]
print("✓ defisit section replaced successfully.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

