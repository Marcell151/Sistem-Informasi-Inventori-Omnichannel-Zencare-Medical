import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace the Tahap 2 navigation & remove the black bar and redundant banners
old_block_start = "<!-- NAVIGASI TAHAP 2 VERIFIKASI -->"
old_block_end = "<!-- Tabel Rekonsiliasi Anomali & Eksekusi Mutasi -->"

pos1 = content.find(old_block_start)
pos2 = content.find(old_block_end)

print("Found pos1:", pos1, "pos2:", pos2)

new_block = """<!-- NAVIGASI FILTER TAB TAHAP 2 VERIFIKASI (ADMIN & SUPERADMIN) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3 mb-4">
                    <!-- Tab Filter: Perlu Mutasi, Sesuai, Sudah Dimutasi, Semua Item -->
                    <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
                        <!-- Tab 1: Perlu Mutasi (Berselisih) -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=berselisih&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') ? 'bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Fokus verifikasi pada barang yang berselisih">
                            <span>Perlu Mutasi (Berselisih)</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') ? 'bg-rose-200 text-rose-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= max(0, $stats['berselisih'] - $stats['sudah_dimutasi']) ?>
                            </span>
                        </a>

                        <!-- Tab 2: Sesuai / Pas (Klop) -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sesuai&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= $filterSts === 'sesuai' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Daftar barang yang jumlah fisiknya cocok dengan sistem">
                            <span>Sesuai (Klop)</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'sesuai' ? 'bg-emerald-200 text-emerald-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $stats['sesuai'] ?>
                            </span>
                        </a>

                        <!-- Tab 3: Sudah Dimutasi -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sudah_dimutasi&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= $filterSts === 'sudah_dimutasi' ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Barang yang sudah selesai direkonsiliasi dan dicatat ke kartu stok">
                            <span>Sudah Dimutasi</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'sudah_dimutasi' ? 'bg-blue-200 text-blue-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $stats['sudah_dimutasi'] ?>
                            </span>
                        </a>

                        <!-- Tab 4: Semua Item -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= $filterSts === 'semua' ? 'bg-slate-100 text-slate-800 border border-slate-300 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Tampilkan seluruh entitas persediaan">
                            <span>Semua Item</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-200 text-slate-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $stats['total'] ?>
                            </span>
                        </a>
                    </div>

                    <!-- Keterangan Ringkas Jumlah Baris -->
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <strong><?= count($detailItems) ?></strong> dari <?= $stats['total'] ?> baris persediaan
                    </div>
                </div>

                """

if pos1 != -1 and pos2 != -1:
    content = content[:pos1] + new_block + content[pos2:]
    print("Replaced navigation block successfully!")
else:
    print("Could not find block boundaries!")

# 2. Fix the reset button in line 2366
content = content.replace(
    '<input type="hidden" name="aksi" value="reset_database">',
    '<input type="hidden" name="aksi" value="reset_database_demo">'
)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Saved updated stok_opname.php!")

