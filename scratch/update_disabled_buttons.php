<?php
// 1. konversi_uom.php
$path1 = 'inventori/konversi_uom.php';
$c1 = file_get_contents($path1);

$target1 = '<button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-[.98]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Proses Konversi (Mutasi Satuan)</span>
                </button>';

$replace1 = '<?php if ($sesiOpnameAktif): ?>
                    <button type="button" disabled class="w-full bg-slate-100 border border-slate-300 text-slate-400 font-bold text-sm py-3 px-4 rounded-xl cursor-not-allowed flex items-center justify-center gap-2 shadow-2xs">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Konversi Dibekukan Sementara (Stok Opname Aktif)</span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-[.98]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Proses Konversi (Mutasi Satuan)</span>
                    </button>
                <?php endif; ?>';

if (strpos($c1, $target1) !== false) {
    $c1 = str_replace($target1, $replace1, $c1);
    file_put_contents($path1, $c1);
    echo "Updated $path1 successfully.\n";
} else {
    // Try normalized newlines
    $c1_norm = str_replace("\r\n", "\n", $c1);
    $target1_norm = str_replace("\r\n", "\n", $target1);
    if (strpos($c1_norm, $target1_norm) !== false) {
        $c1_norm = str_replace($target1_norm, str_replace("\r\n", "\n", $replace1), $c1_norm);
        file_put_contents($path1, $c1_norm);
        echo "Updated $path1 (normalized CRLF) successfully.\n";
    } else {
        echo "Failed to match $path1 target.\n";
    }
}

// 2. proses_mutasi.php
$path2 = 'inventori/proses_mutasi.php';
$c2 = file_get_contents($path2);
$target2 = '<button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-[.98]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
                    <span>Simpan Penyesuaian Stok</span>
                </button>';

$replace2 = '<?php if ($sesiOpnameAktif): ?>
                    <button type="button" disabled class="w-full bg-slate-100 border border-slate-300 text-slate-400 font-bold text-sm py-3 px-4 rounded-xl cursor-not-allowed flex items-center justify-center gap-2 shadow-2xs">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Mutasi Stok Dibekukan Sementara (Stok Opname Aktif)</span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-[.98]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
                        <span>Simpan Penyesuaian Stok</span>
                    </button>
                <?php endif; ?>';

$c2_norm = str_replace("\r\n", "\n", $c2);
$target2_norm = str_replace("\r\n", "\n", $target2);
if (strpos($c2_norm, $target2_norm) !== false) {
    $c2_norm = str_replace($target2_norm, str_replace("\r\n", "\n", $replace2), $c2_norm);
    file_put_contents($path2, $c2_norm);
    echo "Updated $path2 successfully.\n";
} else {
    echo "Failed to match $path2 target.\n";
}

