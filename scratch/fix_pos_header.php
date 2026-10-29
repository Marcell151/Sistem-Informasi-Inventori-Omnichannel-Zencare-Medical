<?php
$file = __DIR__ . '/../pos/pos.php';
$content = file_get_contents($file);

$targetBlock = '    <!-- Topbar -->
    <header class="bg-white border-b border-zcBrd px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-zc to-zcHv text-white flex items-center justify-center shadow-lg shadow-zc/30">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-zcTxt tracking-tight">POS ZenCare Medical</h1>
                <div class="flex items-center gap-3 mt-1 text-xs text-zcMut font-medium">
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <?= htmlspecialchars($_SESSION[\'nama_lengkap\'] ?? \'Kasir\') ?></span>
                    <span class="text-slate-300">&#8226;</span>
                    <?php if ($anomaliHariIniKasir > 0): ?>
            <!-- Panel Buka-Tutup Rincian Batch Terlewat Hari Ini (Non-blocking: Kasir Tetap Bebas Transaksi) -->
            <div id="panel_batch_terlewat" class="mb-4 bg-white border border-amber-300 rounded-2xl shadow-sm overflow-hidden transition-all duration-200">
                <!-- Header Bar (Bisa diklik untuk buka/tutup rincian) -->
                <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200 flex items-center justify-between cursor-pointer select-none" onclick="toggleBatchTerlewatPanel()">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-sm flex-shrink-0">
                            ⚠
                        </div>
                        <div>
                            <div class="text-xs font-bold text-amber-900 tracking-tight flex items-center gap-2">
                                <span>Peringatan Deviasi Batch Obat Hari Ini</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[10px]"><?= $anomaliHariIniKasir ?> Kejadian</span>
                            </div>
                            <div class="text-[11px] text-amber-700 mt-0.5">
                                Terdapat transaksi di mana batch fisik yang dikeluarkan berbeda dari rekomendasi FEFO sistem. Buka rincian di bawah untuk memeriksa tanpa mengganggu input kasir.
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" class="text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 px-3 py-1 rounded-lg border border-amber-300 transition flex items-center gap-1">
                            <span id="label_toggle_drawer">Tutup Rincian</span>
                            <span id="icon_toggle_drawer" class="text-sm font-extrabold transform transition-transform duration-200">▴</span>
                        </button>
                    </div>
                </div>

                <!-- Content Drawer: Tabel Rincian Batch Terlewat -->
                <div id="drawer_batch_content" class="p-4 bg-white max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-[11px]">
                                <th class="py-2 px-3 text-center w-10">#</th>
                                <th class="py-2 px-3">Invoice / Jam</th>
                                <th class="py-2 px-3">Nama Obat</th>
                                <th class="py-2 px-3 text-center">Batch Seharusnya (FEFO)</th>
                                <th class="py-2 px-3 text-center">Batch Diambil Kasir</th>
                                <th class="py-2 px-3 text-center">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($listAnomaliKasir as $i => $row): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2 px-3 text-center text-slate-400 font-mono text-[11px]"><?= $i + 1 ?></td>
                                    <td class="py-2 px-3 font-mono font-bold text-slate-800 text-[11px]">
                                        <?= htmlspecialchars($row[\'no_invoice\'] ?? \'-\') ?>
                                        <div class="text-[10px] text-slate-400 font-normal"><?= date(\'H:i\', strtotime($row[\'created_at\'])) ?> WIB</div>
                                    </td>
                                    <td class="py-2 px-3 font-medium"><?= htmlspecialchars($row[\'nama_produk\'] ?? \'-\') ?></td>
                                    <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[11px] font-bold"><?= htmlspecialchars($row[\'batch_seharusnya\'] ?? \'-\') ?></span></td>
                                    <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[11px] font-bold"><?= htmlspecialchars($row[\'batch_diambil\'] ?? \'-\') ?></span></td>
                                    <td class="py-2 px-3 text-center font-bold text-slate-800"><?= number_format($row[\'qty\'], 0, \',\', \'.\') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="mt-3 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-600 flex items-center justify-between">
                        <span>💡 <strong>Catatan Standar SOP:</strong> Panel ini dirancang non-blocking sehingga kasir tetap dapat mengetik, mencari produk, atau scan barcode sambil memeriksa catatan batch terlewat.</span>
                        <button type="button" onclick="document.getElementById(\'panel_batch_terlewat\').style.display=\'none\'" class="text-[10px] text-slate-400 hover:text-slate-600 underline">Sembunyikan Panel</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>';

$replacementBlock = '    <!-- Topbar -->
    <header class="bg-white border-b border-zcBrd px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-zc to-zcHv text-white flex items-center justify-center shadow-lg shadow-zc/30">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-zcTxt tracking-tight">POS ZenCare Medical</h1>
                <div class="flex items-center gap-3 mt-1 text-xs text-zcMut font-medium">
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <?= htmlspecialchars($_SESSION[\'nama_lengkap\'] ?? \'Kasir\') ?></span>
                    <span class="text-slate-300">&#8226;</span>
                    <?php if ($anomaliHariIniKasir > 0): ?>
                    <button type="button" onclick="toggleBatchTerlewatPanel()" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold transition bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200" title="Klik untuk buka/tutup rincian batch terlewat hari ini">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Batch Terlewat: <?= $anomaliHariIniKasir ?></span>
                        <span id="pill_arrow_indicator" class="text-[10px]">▾</span>
                    </button>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Seluruh transaksi mematuhi batch terdepan FEFO">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Batch Tertib ✓</span>
                    </span>
                    <?php endif; ?>
                    <span class="text-slate-300">&#8226;</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Pusat (Muharto)</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="../index.php" class="text-sm font-semibold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-xs"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Dashboard Utama</a>
            <a href="../ecommerce/index.php" target="_blank" class="text-sm font-semibold bg-zc hover:bg-zcHv text-white px-4 py-2.5 rounded-xl shadow-md shadow-zc/20 transition">Toko Online</a>
            <a href="../logout.php" class="text-sm font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 border border-rose-200 px-4 py-2.5 rounded-xl transition">Keluar</a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-5">

        <?php if ($anomaliHariIniKasir > 0): ?>
            <!-- Panel Buka-Tutup Rincian Batch Terlewat Hari Ini (Non-blocking: Kasir Tetap Bebas Transaksi) -->
            <div id="panel_batch_terlewat" class="mb-4 bg-white border border-amber-300 rounded-2xl shadow-sm overflow-hidden transition-all duration-200">
                <!-- Header Bar (Bisa diklik untuk buka/tutup rincian) -->
                <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200 flex items-center justify-between cursor-pointer select-none" onclick="toggleBatchTerlewatPanel()">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-sm flex-shrink-0">
                            ⚠
                        </div>
                        <div>
                            <div class="text-xs font-bold text-amber-900 tracking-tight flex items-center gap-2">
                                <span>Peringatan Deviasi Batch Obat Hari Ini</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[10px]"><?= $anomaliHariIniKasir ?> Kejadian</span>
                            </div>
                            <div class="text-[11px] text-amber-700 mt-0.5">
                                Terdapat transaksi di mana batch fisik yang dikeluarkan berbeda dari rekomendasi FEFO sistem. Buka rincian di bawah untuk memeriksa tanpa mengganggu input kasir.
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" class="text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 px-3 py-1 rounded-lg border border-amber-300 transition flex items-center gap-1">
                            <span id="label_toggle_drawer">Tutup Rincian</span>
                            <span id="icon_toggle_drawer" class="text-sm font-extrabold transform transition-transform duration-200">▴</span>
                        </button>
                    </div>
                </div>

                <!-- Content Drawer: Tabel Rincian Batch Terlewat -->
                <div id="drawer_batch_content" class="p-4 bg-white max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-[11px]">
                                <th class="py-2 px-3 text-center w-10">#</th>
                                <th class="py-2 px-3">Invoice / Jam</th>
                                <th class="py-2 px-3">Nama Obat</th>
                                <th class="py-2 px-3 text-center">Batch Seharusnya (FEFO)</th>
                                <th class="py-2 px-3 text-center">Batch Diambil Kasir</th>
                                <th class="py-2 px-3 text-center">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($listAnomaliKasir as $i => $row): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2 px-3 text-center text-slate-400 font-mono text-[11px]"><?= $i + 1 ?></td>
                                    <td class="py-2 px-3 font-mono font-bold text-slate-800 text-[11px]">
                                        <?= htmlspecialchars($row[\'no_invoice\'] ?? \'-\') ?>
                                        <div class="text-[10px] text-slate-400 font-normal"><?= date(\'H:i\', strtotime($row[\'created_at\'])) ?> WIB</div>
                                    </td>
                                    <td class="py-2 px-3 font-medium"><?= htmlspecialchars($row[\'nama_produk\'] ?? \'-\') ?></td>
                                    <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[11px] font-bold"><?= htmlspecialchars($row[\'batch_seharusnya\'] ?? \'-\') ?></span></td>
                                    <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[11px] font-bold"><?= htmlspecialchars($row[\'batch_diambil\'] ?? \'-\') ?></span></td>
                                    <td class="py-2 px-3 text-center font-bold text-slate-800"><?= number_format($row[\'qty\'], 0, \',\', \'.\') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="mt-3 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-600 flex items-center justify-between">
                        <span>💡 <strong>Catatan Standar SOP:</strong> Panel ini dirancang non-blocking sehingga kasir tetap dapat mengetik, mencari produk, atau scan barcode sambil memeriksa catatan batch terlewat.</span>
                        <button type="button" onclick="document.getElementById(\'panel_batch_terlewat\').style.display=\'none\'" class="text-[10px] text-slate-400 hover:text-slate-600 underline">Sembunyikan Panel</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>';

$normContent = str_replace("\r\n", "\n", $content);
$normTarget = str_replace("\r\n", "\n", $targetBlock);

if (strpos($normContent, $normTarget) !== false) {
    $normContent = str_replace($normTarget, $replacementBlock, $normContent);
    file_put_contents($file, $normContent);
    echo "SUCCESS: pos.php header restored and reconstructed perfectly.\n";
} else {
    echo "FAILED: Target block not found directly, inspecting...\n";
}

