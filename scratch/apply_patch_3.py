with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update <tr> to add data-barcode-search
tr_old = """                                <!-- Baris Sub-Item / Batch / Unit Fisik di Bawah Produk Tersebut -->
                                <?php foreach ($grp['items'] as $it): 
                                    $isCalculated = ($it['hitung_fisik'] !== null);
                                    $selisih = intval($it['selisih'] ?? 0);
                                    $isDefisit = ($isCalculated && intval($it['hitung_fisik']) < intval($it['stok_reserved']));
                                ?>
                                <tr class="hover:bg-slate-50/70 transition <?= $isDefisit ? 'bg-rose-50/40' : '' ?>">"""

tr_new = """                                <!-- Baris Sub-Item / Batch / Unit Fisik di Bawah Produk Tersebut -->
                                <?php foreach ($grp['items'] as $it): 
                                    $isCalculated = ($it['hitung_fisik'] !== null);
                                    $selisih = intval($it['selisih'] ?? 0);
                                    $isDefisit = ($isCalculated && intval($it['hitung_fisik']) < intval($it['stok_reserved']));
                                    $searchableStr = htmlspecialchars($it['nama_produk'] . ' ' . $it['nama_variasi'] . ' ' . ($it['sku_variasi'] ?? '') . ' ' . ($it['no_batch'] ?? ''));
                                ?>
                                <tr class="hover:bg-slate-50/70 transition <?= $isDefisit ? 'bg-rose-50/40' : '' ?>" data-barcode-search="<?= $searchableStr ?>">"""

assert tr_old in content, "tr_old not found!"
content = content.replace(tr_old, tr_new, 1)

# 2. Update table cells (Hitung fisik, Stok fisik sistem, Reserved, Selisih, Status, Tindakan mutasi)
cells_old = """                                    <!-- KOLOM HITUNG FISIK (BLIND COUNT) -->
                                    <td class="py-3 px-3 text-center bg-amber-50/30 border-x border-amber-100">
                                        <?php if (!$isCalculated): ?>
                                            <!-- Mode Input Hitung Buta Awal -->
                                            <div class="flex items-center justify-center gap-1.5 m-0">
                                                <input type="number" 
                                                       name="bulk_hitung_fisik[<?= $it['id'] ?>]" 
                                                       id="input_fisik_<?= $it['id'] ?>"
                                                       form="form_bulk_simpan"
                                                       data-id="<?= $it['id'] ?>"
                                                       data-sistem="<?= $it['stok_sistem_fisik'] ?>"
                                                       data-komoditas="<?= $it['jenis_komoditas'] ?>"
                                                       data-satuan-tipe="<?= $it['satuan_tipe'] ?>"
                                                       min="0" 
                                                       placeholder="" 
                                                       class="input-hitung-fisik w-20 border-2 border-amber-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-amber-600 bg-white shadow-2xs">
                                                <button type="button" onclick="simpanSatuBaris(<?= $it['id'] ?>)" class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[10px] font-bold shadow-2xs transition cursor-pointer" title="Simpan baris ini saja">Simpan</button>
                                            </div>
                                        <?php else: ?>
                                            <!-- Nilai Sudah Dihitung -->
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-sm font-black font-mono text-slate-900"><?= $it['hitung_fisik'] ?></span>
                                                <span class="text-[10px] text-slate-500"><?= htmlspecialchars($it['nama_satuan']) ?></span>
                                                <?php if ($it['status_mutasi'] !== 'Sudah Dimutasi'): ?>
                                                    <button onclick="openModalRecount(<?= $it['id'] ?>, <?= $it['hitung_fisik'] ?>, '<?= addslashes($it['nama_produk']) ?>')" class="text-slate-400 hover:text-zc p-1 rounded hover:bg-slate-100 transition inline-flex items-center cursor-pointer" title="Koreksi / Hitung Ulang">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">Oleh: <?= htmlspecialchars($it['nama_penghitung'] ?? 'User') ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM STOK FISIK SISTEM (Baru Terbuka Setelah Simpan Fisik) -->
                                    <td class="py-3 px-3 text-center font-mono">
                                        <?php if ($isCalculated): ?>
                                            <span class="font-bold text-slate-800"><?= $it['stok_sistem_fisik'] ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM RESERVED -->
                                    <td class="py-3 px-3 text-center font-mono">
                                        <?php if ($isCalculated): ?>
                                            <span class="text-slate-600 <?= $it['stok_reserved'] > 0 ? 'font-bold text-amber-700' : '' ?>"><?= $it['stok_reserved'] ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM SELISIH -->
                                    <td class="py-3 px-3 text-center font-mono font-bold">
                                        <?php if ($isCalculated): ?>
                                            <?php if ($selisih === 0): ?>
                                                <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full text-[11px]">0 (Pas)</span>
                                            <?php elseif ($selisih > 0): ?>
                                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full text-[11px]">+<?= $selisih ?></span>
                                            <?php else: ?>
                                                <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full text-[11px]"><?= $selisih ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM STATUS -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <?php if (!$isCalculated): ?>
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 whitespace-nowrap leading-none">Belum Dihitung</span>
                                        <?php elseif ($it['status_mutasi'] === 'Sudah Dimutasi'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap leading-none">
                                                <svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>Sudah Dimutasi</span>
                                            </span>
                                            <div class="text-[9px] text-slate-400 mt-1"><?= htmlspecialchars($it['alasan_mutasi']) ?></div>
                                        <?php elseif ($selisih === 0): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap leading-none">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>Sesuai</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 whitespace-nowrap leading-none">Perlu Mutasi</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM TINDAKAN MUTASI -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <?php if (!$isCalculated): ?>
                                            <span class="text-[11px] text-slate-400 italic">Isi hitung fisik dulu</span>
                                        <?php elseif ($selisih === 0): ?>
                                            <span class="text-[11px] text-emerald-600 font-semibold">Tidak ada selisih</span>
                                        <?php elseif ($it['status_mutasi'] === 'Sudah Dimutasi'): ?>
                                            <span class="text-[11px] text-slate-600 font-medium">Selesai (<?= htmlspecialchars($it['alasan_mutasi']) ?>)</span>
                                        <?php else: ?>
                                            <!-- Tombol Buka Modal Mutasi Baris -->
                                            <button onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2.5 py-1.5 rounded-xl bg-zc hover:bg-zcHv text-white text-[11px] font-bold transition shadow-2xs inline-flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m10 8 4 4-4 4"/></svg>
                                                <span>Mutasi Selisih</span>
                                            </button>
                                        <?php endif; ?>
                                    </td>"""

cells_new = """                                    <!-- KOLOM HITUNG FISIK (BLIND COUNT) -->
                                    <td class="py-3 px-3 text-center bg-amber-50/30 border-x border-amber-100">
                                        <?php if (!$isCalculated): ?>
                                            <!-- Mode Input Hitung Buta Awal -->
                                            <div class="flex items-center justify-center gap-1.5 m-0">
                                                <input type="number" 
                                                       name="bulk_hitung_fisik[<?= $it['id'] ?>]" 
                                                       id="input_fisik_<?= $it['id'] ?>"
                                                       form="form_bulk_simpan"
                                                       data-id="<?= $it['id'] ?>"
                                                       data-sistem="<?= $isKasir ? '' : $it['stok_sistem_fisik'] ?>"
                                                       data-komoditas="<?= $it['jenis_komoditas'] ?>"
                                                       data-satuan-tipe="<?= $it['satuan_tipe'] ?>"
                                                       min="0" 
                                                       placeholder="" 
                                                       class="input-hitung-fisik w-20 border-2 border-amber-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-amber-600 bg-white shadow-2xs">
                                                <button type="button" onclick="simpanSatuBaris(<?= $it['id'] ?>)" class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[10px] font-bold shadow-2xs transition cursor-pointer" title="Simpan baris ini saja">Simpan</button>
                                            </div>
                                        <?php else: ?>
                                            <!-- Nilai Sudah Dihitung -->
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-sm font-black font-mono text-slate-900"><?= $it['hitung_fisik'] ?></span>
                                                <span class="text-[10px] text-slate-500"><?= htmlspecialchars($it['nama_satuan']) ?></span>
                                                <?php if ($it['status_mutasi'] !== 'Sudah Dimutasi' && !$isKasir): ?>
                                                    <button onclick="openModalRecount(<?= $it['id'] ?>, <?= $it['hitung_fisik'] ?>, '<?= addslashes($it['nama_produk']) ?>')" class="text-slate-400 hover:text-zc p-1 rounded hover:bg-slate-100 transition inline-flex items-center cursor-pointer" title="Koreksi / Hitung Ulang">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">Oleh: <?= htmlspecialchars($it['nama_penghitung'] ?? 'User') ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM STOK FISIK SISTEM (Dirahasiakan untuk Kasir / Blind Count) -->
                                    <td class="py-3 px-3 text-center font-mono">
                                        <?php if ($isKasir): ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Dirahasiakan untuk Kasir (Blind Count)">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Dirahasiakan</span>
                                            </span>
                                        <?php elseif ($isCalculated): ?>
                                            <span class="font-bold text-slate-800"><?= $it['stok_sistem_fisik'] ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM RESERVED -->
                                    <td class="py-3 px-3 text-center font-mono">
                                        <?php if ($isKasir): ?>
                                            <span class="text-slate-400 text-[11px] font-mono">Dirahasiakan</span>
                                        <?php elseif ($isCalculated): ?>
                                            <span class="text-slate-600 <?= $it['stok_reserved'] > 0 ? 'font-bold text-amber-700' : '' ?>"><?= $it['stok_reserved'] ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM SELISIH -->
                                    <td class="py-3 px-3 text-center font-mono font-bold">
                                        <?php if ($isKasir): ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Dirahasiakan untuk Kasir (Blind Count)">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Dirahasiakan</span>
                                            </span>
                                        <?php elseif ($isCalculated): ?>
                                            <?php if ($selisih === 0): ?>
                                                <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full text-[11px]">0 (Pas)</span>
                                            <?php elseif ($selisih > 0): ?>
                                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full text-[11px]">+<?= $selisih ?></span>
                                            <?php else: ?>
                                                <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full text-[11px]"><?= $selisih ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM STATUS -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <?php if ($isKasir): ?>
                                            <?php if (!$isCalculated): ?>
                                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 whitespace-nowrap leading-none">Belum Dihitung</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap leading-none">
                                                    ✓ Fisik Tercatat
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!$isCalculated): ?>
                                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 whitespace-nowrap leading-none">Belum Dihitung</span>
                                            <?php elseif ($it['status_mutasi'] === 'Sudah Dimutasi'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap leading-none">
                                                    <svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <span>Sudah Dimutasi</span>
                                                </span>
                                                <div class="text-[9px] text-slate-400 mt-1"><?= htmlspecialchars($it['alasan_mutasi']) ?></div>
                                            <?php elseif ($selisih === 0): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap leading-none">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <span>Sesuai</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 whitespace-nowrap leading-none">Perlu Mutasi</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM TINDAKAN MUTASI -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <?php if ($isKasir): ?>
                                            <span class="text-[11px] text-slate-400 italic">Khusus Verifikator</span>
                                        <?php elseif (!$isCalculated): ?>
                                            <span class="text-[11px] text-slate-400 italic">Isi hitung fisik dulu</span>
                                        <?php elseif ($selisih === 0): ?>
                                            <span class="text-[11px] text-emerald-600 font-semibold">Tidak ada selisih</span>
                                        <?php elseif ($it['status_mutasi'] === 'Sudah Dimutasi'): ?>
                                            <span class="text-[11px] text-slate-600 font-medium">Selesai (<?= htmlspecialchars($it['alasan_mutasi']) ?>)</span>
                                        <?php else: ?>
                                            <!-- Tombol Buka Modal Mutasi Baris -->
                                            <button onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2.5 py-1.5 rounded-xl bg-zc hover:bg-zcHv text-white text-[11px] font-bold transition shadow-2xs inline-flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m10 8 4 4-4 4"/></svg>
                                                <span>Mutasi Selisih</span>
                                            </button>
                                        <?php endif; ?>
                                    </td>"""

assert cells_old in content, "cells_old not found!"
content = content.replace(cells_old, cells_new, 1)

# 3. Add Dropdown Kategorisasi in Modal Mutasi
modal_defisit_old = """                        <!-- SECTION ALOKASI ALASAN DEFISIT (Minus) -->
                        <div id="container_alokasi_defisit" class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block font-bold text-zcTxt text-xs">Alokasi Alasan Selisih Defisit</label>
                                    <p class="text-[10px] text-zcMut">Tentukan rincian penyebab barang tidak ada / rusak fisik:</p>
                                </div>
                                <span id="badge_alokasi_counter" class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold"></span>
                            </div>"""

modal_defisit_new = """                        <!-- SECTION ALOKASI ALASAN DEFISIT (Minus) -->
                        <div id="container_alokasi_defisit" class="space-y-2.5">
                            <!-- KATEGORISASI ALASAN MUTASI UTAMA (SESUAI ARAHAN DOSEN PEMBIMBING) -->
                            <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl space-y-1.5">
                                <label class="block font-bold text-amber-950 text-xs">Pilih Kategori Beban Selisih (Wajib):</label>
                                <select id="kategori_beban_utama" class="w-full border border-amber-300 rounded-lg p-2 text-xs font-bold bg-white text-slate-800 focus:ring-2 focus:ring-amber-500" onchange="applyKategoriBeban(this.value)">
                                    <option value="rusak">1. Mutasi Barang Rusak (Kemasan pecah / cacat / rusak fisik)</option>
                                    <option value="expired">2. Mutasi Barang Kedaluwarsa (Expired Date terlampaui)</option>
                                    <option value="hilang">3. Mutasi Barang Hilang (Fisik tidak ditemukan di toko)</option>
                                    <option value="salah_catat">4. Mutasi Koreksi Administrasi / Salah Catat</option>
                                    <option value="custom">5. Pembagian Multi-Kategori (Split Alokasi Gabungan)</option>
                                </select>
                                <p class="text-[10px] text-amber-800">Sistem otomatis membebankan kuantitas selisih ke kategori terpilih dan mencatatnya ke Kartu Stok.</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block font-bold text-zcTxt text-xs">Rincian Alokasi Kuantitas Defisit</label>
                                    <p class="text-[10px] text-zcMut">Kuantitas otomatis terisi sesuai kategori di atas (atau sesuaikan jika multi-kategori):</p>
                                </div>
                                <span id="badge_alokasi_counter" class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold"></span>
                            </div>"""

assert modal_defisit_old in content, "modal_defisit_old not found!"
content = content.replace(modal_defisit_old, modal_defisit_new, 1)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Part 3 updated successfully.")

