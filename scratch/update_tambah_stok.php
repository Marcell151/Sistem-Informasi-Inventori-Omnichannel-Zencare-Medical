<?php
$path = 'inventori/tambah_stok.php';
$c = file_get_contents($path);

$target = '<button type="submit" class="px-8 py-3 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-md shadow-blue-500/20 active:scale-95 transition flex items-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan &amp; Update Stok
            </button>';

$replace = '<?php if ($sesiOpnameAktif): ?>
                <button type="button" disabled class="px-8 py-3 bg-slate-100 border border-slate-300 text-slate-400 font-bold rounded-xl cursor-not-allowed flex items-center gap-2 shadow-2xs">
                    <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Penerimaan Dibekukan (Stok Opname Aktif)
                </button>
            <?php else: ?>
                <button type="submit" class="px-8 py-3 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-md shadow-blue-500/20 active:scale-95 transition flex items-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan &amp; Update Stok
                </button>
            <?php endif; ?>';

$c_norm = str_replace("\r\n", "\n", $c);
$target_norm = str_replace("\r\n", "\n", $target);
if (strpos($c_norm, $target_norm) !== false) {
    $c_norm = str_replace($target_norm, str_replace("\r\n", "\n", $replace), $c_norm);
    file_put_contents($path, $c_norm);
    echo "Updated $path successfully.\n";
} else {
    echo "Failed to match $path target.\n";
}

