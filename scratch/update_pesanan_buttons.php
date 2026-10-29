<?php
$file = __DIR__ . '/../admin/pesanan.php';
$content = file_get_contents($file);

// 1. Ganti tombol Cetak tunggal di tabel baris utama
$btnTarget = '<a href="../pos/cetak_invoice.php?no_invoice=<?= $o[\'no_invoice\'] ?>" target="_blank"
                                        class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-zcBrd rounded-lg transition">Cetak</a>';

$btnReplacement = '<div class="inline-flex items-center gap-1 mt-1 sm:mt-0">
                                        <a href="../pos/cetak_invoice.php?no_invoice=<?= $o[\'no_invoice\'] ?>" target="_blank"
                                            title="Cetak Dokumen Invoice Kertas (A4)"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-lg shadow-sm transition">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Invoice</span>
                                        </a>
                                        <a href="cetak_label_pengiriman.php?no_invoice=<?= $o[\'no_invoice\'] ?>" target="_blank"
                                            title="Cetak Stiker Label Pengiriman Thermal (100x150mm / A6)"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg shadow-sm transition">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            <span>Label Kirim</span>
                                        </a>
                                    </div>';

// Cek normalize newline jika perlu
$normalizedContent = str_replace("\r\n", "\n", $content);
$normalizedTarget = str_replace("\r\n", "\n", $btnTarget);

if (strpos($normalizedContent, $normalizedTarget) !== false) {
    $normalizedContent = str_replace($normalizedTarget, $btnReplacement, $normalizedContent);
    echo "Tombol utama tabel pesanan berhasil diupdate.\n";
} else {
    echo "Target tombol utama tidak ditemukan persis, mencoba regex...\n";
    $pattern = '/<a\s+href="\.\.\/pos\/cetak_invoice\.php\?no_invoice=<\?=\s*\$o\[\'no_invoice\'\]\s*\?>"\s+target="_blank"\s+class="[^"]*">Cetak<\/a>/i';
    if (preg_match($pattern, $normalizedContent)) {
        $normalizedContent = preg_replace($pattern, $btnReplacement, $normalizedContent);
        echo "Tombol utama tabel berhasil diupdate via regex.\n";
    } else {
        echo "Regex tombol utama gagal mencocokkan.\n";
    }
}

// 2. Tambah tombol cetak di baris aksi detail pesanan juga
$detailActionTarget = '<div class="flex items-center gap-2">';
$detailActionReplacement = '<div class="flex items-center gap-2">
                                            <!-- Tombol Cetak Dokumen -->
                                            <a href="../pos/cetak_invoice.php?no_invoice=<?= $o[\'no_invoice\'] ?>" target="_blank"
                                                class="px-3 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Cetak Invoice (Kertas)
                                            </a>
                                            <a href="cetak_label_pengiriman.php?no_invoice=<?= $o[\'no_invoice\'] ?>" target="_blank"
                                                class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                Cetak Label Kirim (Stiker Thermal)
                                            </a>';

if (strpos($normalizedContent, 'Cetak Label Kirim (Stiker Thermal)') === false) {
    // Sisipkan sebelum tombol aksi lanjutan di baris 615
    $targetLoc = '<!-- Tombol Aksi Lanjutkan Status Langsung di Detail Pesanan -->';
    if (strpos($normalizedContent, $targetLoc) !== false) {
        $normalizedContent = str_replace($detailActionTarget, $detailActionReplacement, $normalizedContent);
        echo "Tombol cetak detail pesanan berhasil disisipkan.\n";
    }
}

file_put_contents($file, $normalizedContent);
echo "Selesai mengupdate admin/pesanan.php\n";

