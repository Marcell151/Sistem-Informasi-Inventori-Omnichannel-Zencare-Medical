<?php
$file = __DIR__ . '/../pos/pos.php';
$content = file_get_contents($file);

// 1. UPDATE HEADER STATUS PILL
$oldPillPattern = '/<button\s+type="button"\s+onclick="openKasirAnomaliModal\(\)"\s+class="[^"]*"\s+title="[^"]*">\s*<span\s+class="[^"]*"><\/span>\s*<span>Shift\s+FEFO:[\s\S]*?<\/span>\s*<\/button>/i';

$newPill = '<?php if ($anomaliHariIniKasir > 0): ?>
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
                    <?php endif; ?>';

if (preg_match($oldPillPattern, $content)) {
    $content = preg_replace($oldPillPattern, $newPill, $content);
    echo "Header status pill replaced successfully.\n";
} else {
    echo "Warning: Old pill pattern not matched.\n";
}

// 2. GANTI BANNER STATIK DENGAN NON-BLOCKING COLLAPSIBLE ACCORDION
$oldBannerPattern = '/<\?php\s+if\s*\(\$anomaliHariIniKasir\s*>\s*0\):[\s\S]*?id="banner_kasir_anomali"[\s\S]*?<\?php\s+endif;\s*\?>/i';

$newBanner = '<?php if ($anomaliHariIniKasir > 0): ?>
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

if (preg_match($oldBannerPattern, $content)) {
    $content = preg_replace($oldBannerPattern, $newBanner, $content);
    echo "Collapsible accordion drawer replaced successfully.\n";
} else {
    echo "Warning: Old banner pattern not matched.\n";
}

// 3. TAMBAHKAN FUNCTION JS toggleBatchTerlewatPanel()
$jsFunction = '
function toggleBatchTerlewatPanel() {
    const panel = document.getElementById("panel_batch_terlewat");
    if (!panel) return;
    panel.style.display = "block";
    const drawer = document.getElementById("drawer_batch_content");
    const lbl = document.getElementById("label_toggle_drawer");
    const icon = document.getElementById("icon_toggle_drawer");
    const pillArrow = document.getElementById("pill_arrow_indicator");
    if (!drawer) return;
    if (drawer.style.display === "none") {
        drawer.style.display = "block";
        if (lbl) lbl.innerText = "Tutup Rincian";
        if (icon) icon.innerText = "▴";
        if (pillArrow) pillArrow.innerText = "▴";
    } else {
        drawer.style.display = "none";
        if (lbl) lbl.innerText = "Buka Rincian";
        if (icon) icon.innerText = "▾";
        if (pillArrow) pillArrow.innerText = "▾";
    }
}
';

if (strpos($content, 'function toggleBatchTerlewatPanel()') === false) {
    $content = str_replace('function openKasirAnomaliModal() {', $jsFunction . "\nfunction openKasirAnomaliModal() {", $content);
    echo "Function toggleBatchTerlewatPanel added.\n";
}

file_put_contents($file, $content);
echo "Done modifying pos/pos.php\n";

