<?php
$file = __DIR__ . '/../pos/pos.php';
$content = file_get_contents($file);

// 1. Tambah query anomali kasir di bagian atas setelah $katalog
$queryTarget = '$shopeeOn = false;';
$queryReplacement = '$shopeeOn = false;

// Query Anomali FEFO Hari Ini untuk Peringatan Panel Kasir
$kasirUserId = $_SESSION["user_id"] ?? 0;
$userRole = $_SESSION["role"] ?? "kasir";

if ($userRole === "superadmin") {
    $stmtAnomaliKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE DATE(created_at) = CURDATE()");
    $anomaliHariIniKasir = (int)$stmtAnomaliKasir->fetchColumn();

    $stmtListAnomaliKasir = $pdo->query("
        SELECT la.*, CONCAT(pi.nama_produk, \' - \', pv.nama_variasi) AS nama_produk, p.no_invoice, u.nama_lengkap AS nama_kasir
        FROM log_anomali_fefo la
        JOIN produk_variasi pv ON la.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN penjualan p ON la.id_penjualan = p.id
        LEFT JOIN users u ON la.id_user = u.id
        WHERE DATE(la.created_at) = CURDATE()
        ORDER BY la.created_at DESC
    ");
    $listAnomaliKasir = $stmtListAnomaliKasir->fetchAll();
} else {
    $stmtAnomaliKasir = $pdo->prepare("SELECT COUNT(*) FROM log_anomali_fefo WHERE DATE(created_at) = CURDATE() AND id_user = ?");
    $stmtAnomaliKasir->execute([$kasirUserId]);
    $anomaliHariIniKasir = (int)$stmtAnomaliKasir->fetchColumn();

    $stmtListAnomaliKasir = $pdo->prepare("
        SELECT la.*, CONCAT(pi.nama_produk, \' - \', pv.nama_variasi) AS nama_produk, p.no_invoice, u.nama_lengkap AS nama_kasir
        FROM log_anomali_fefo la
        JOIN produk_variasi pv ON la.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN penjualan p ON la.id_penjualan = p.id
        LEFT JOIN users u ON la.id_user = u.id
        WHERE DATE(la.created_at) = CURDATE() AND la.id_user = ?
        ORDER BY la.created_at DESC
    ");
    $stmtListAnomaliKasir->execute([$kasirUserId]);
    $listAnomaliKasir = $stmtListAnomaliKasir->fetchAll();
}';

if (strpos($content, $queryTarget) !== false && strpos($content, '$anomaliHariIniKasir') === false) {
    $content = str_replace($queryTarget, $queryReplacement, $content);
    echo "Query anomali kasir added successfully.\n";
} else {
    echo "Query anomali kasir already present or target not found.\n";
}

// 2. Status Pill di Header POS
$headerTarget = "<?= htmlspecialchars(\$_SESSION['nama_lengkap'] ?? 'Kasir') ?></span>";
$headerReplacement = "<?= htmlspecialchars(\$_SESSION['nama_lengkap'] ?? 'Kasir') ?></span>
                    <span class=\"text-slate-300\">&#8226;</span>
                    <button type=\"button\" onclick=\"openKasirAnomaliModal()\" class=\"inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold transition <?= \$anomaliHariIniKasir > 0 ? 'bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200 hover:bg-emerald-200' ?>\" title=\"Klik untuk cek status kepatuhan FEFO shift hari ini\">
                        <span class=\"w-2 h-2 rounded-full <?= \$anomaliHariIniKasir > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' ?>\"></span>
                        <span>Shift FEFO: <?= \$anomaliHariIniKasir > 0 ? \$anomaliHariIniKasir . ' Anomali' : 'Taat FEFO' ?></span>
                    </button>";

if (strpos($content, $headerTarget) !== false && strpos($content, 'openKasirAnomaliModal()') === false) {
    $content = str_replace($headerTarget, $headerReplacement, $content);
    echo "Header status pill added successfully.\n";
} else {
    echo "Header status pill already present or target not found.\n";
}

// 3. Alert Banner di atas grid produk
$bannerTarget = '<div class="max-w-7xl mx-auto px-4 py-5">';
$bannerReplacement = '<div class="max-w-7xl mx-auto px-4 py-5">

        <?php if ($anomaliHariIniKasir > 0): ?>
            <!-- Panel Peringatan Kasir Terkait Anomali FEFO Hari Ini -->
            <div id="banner_kasir_anomali" class="mb-4 p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-amber-900 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg flex-shrink-0 shadow-md shadow-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold tracking-tight uppercase text-amber-800 flex items-center gap-2">
                            <span>Panel Peringatan Kasir – Anomali FEFO Hari Ini</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[10px]"><?= $anomaliHariIniKasir ?> Kejadian</span>
                        </div>
                        <div class="text-xs font-medium text-amber-700 mt-0.5">
                            Terdeteksi transaksi kasir yang mengeluarkan batch obat di luar urutan FEFO terdekat. Pastikan selalu utamakan batch rekomendasi sistem teratas kecuali ada persetujuan khusus!
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="button" onclick="openKasirAnomaliModal()" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat Rincian
                    </button>
                    <button type="button" onclick="document.getElementById(\'banner_kasir_anomali\').style.display=\'none\'" class="text-amber-500 hover:text-amber-800 p-1.5 rounded-lg hover:bg-amber-100 transition" title="Tutup pemberitahuan">
                        ✕
                    </button>
                </div>
            </div>
        <?php endif; ?>';

if (strpos($content, $bannerTarget) !== false && strpos($content, 'id="banner_kasir_anomali"') === false) {
    $content = str_replace($bannerTarget, $bannerReplacement, $content);
    echo "Banner alert added successfully.\n";
} else {
    echo "Banner alert already present or target not found.\n";
}

// 4. Modal Script untuk openKasirAnomaliModal()
$jsTarget = '</body>';
$jsReplacement = '<script>
function openKasirAnomaliModal() {
    <?php
    $rowsHtml = "";
    if (empty($listAnomaliKasir)) {
        $rowsHtml = "<tr><td colspan=\'6\' class=\'px-4 py-8 text-center text-slate-400 font-semibold\'>Belum ada catatan anomali FEFO hari ini. Layanan kasir mematuhi urutan FEFO secara sempurna! 👍</td></tr>";
    } else {
        foreach ($listAnomaliKasir as $i => $row) {
            $num = $i + 1;
            $inv = htmlspecialchars($row[\'no_invoice\'] ?? \'-\');
            $prod = htmlspecialchars($row[\'nama_produk\'] ?? \'-\');
            $seharusnya = htmlspecialchars($row[\'batch_seharusnya\'] ?? \'-\');
            $diambil = htmlspecialchars($row[\'batch_diambil\'] ?? \'-\');
            $qty = number_format($row[\'qty\'], 0, \',\', \'.\');
            $jam = date(\'H:i\', strtotime($row[\'created_at\']));
            $kasir = htmlspecialchars($row[\'nama_kasir\'] ?? \'Kasir\');

            $rowsHtml .= "<tr class=\'border-b border-slate-100 hover:bg-slate-50 transition\'>";
            $rowsHtml .= "<td class=\'px-3 py-2 text-center text-slate-500 font-mono text-xs\'>{$num}</td>";
            $rowsHtml .= "<td class=\'px-3 py-2 font-mono font-bold text-xs text-slate-800\'>{$inv}<div class=\'text-[10px] text-slate-400 font-normal\'>{$jam} WIB - {$kasir}</div></td>";
            $rowsHtml .= "<td class=\'px-3 py-2 font-semibold text-xs text-slate-700\'>{$prod}</td>";
            $rowsHtml .= "<td class=\'px-3 py-2 text-center\'><span class=\'px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-xs font-bold\'>{$seharusnya}</span></td>";
            $rowsHtml .= "<td class=\'px-3 py-2 text-center\'><span class=\'px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-xs font-bold\'>{$diambil}</span></td>";
            $rowsHtml .= "<td class=\'px-3 py-2 text-center font-bold text-xs text-slate-800\'>{$qty}</td>";
            $rowsHtml .= "</tr>";
        }
    }
    ?>
    
    var modalHtml = `
    <div style="text-align:left; font-family: Inter, sans-serif;">
        <div style="margin-bottom:12px; font-size:12px; color:#64748b;">
            Daftar penukaran batch obat yang tercatat menyimpang dari urutan kedaluwarsa terdekat (FEFO) pada operasional kasir hari ini:
        </div>
        <div style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0; position:sticky; top:0; z-index:10;">
                    <tr>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">#</th>
                        <th style="padding:8px 10px; text-align:left; color:#64748b; font-size:11px;">Invoice / Waktu</th>
                        <th style="padding:8px 10px; text-align:left; color:#64748b; font-size:11px;">Nama Obat</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Batch Seharusnya (FEFO)</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Batch Diambil Kasir</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Qty</th>
                    </tr>
                </thead>
                <tbody style="background:#ffffff;">
                    <?= addslashes($rowsHtml) ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px; padding:10px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe; font-size:11px; color:#1e40af; line-height:1.4;">
            <strong>ℹ️ Catatan Standar SOP:</strong> Bila pelanggan menolak batch terdepan karena alasan kemasan atau permintaan khusus, kasir diperbolehkan melayani dengan batch lain, namun sistem otomatis mencatat anomali ini untuk laporan pengawasan inventori.
        </div>
    </div>`;

    Swal.fire({
        title: \'<span style="font-size:18px; font-weight:800; color:#1e293b;">📋 Log Pengawasan Anomali FEFO Hari Ini</span>\',
        html: modalHtml,
        width: 800,
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: \'Tutup Panel\',
        confirmButtonColor: \'#1a75d2\'
    });
}
</script>
</body>';

if (strpos($content, $jsTarget) !== false && strpos($content, 'function openKasirAnomaliModal()') === false) {
    $content = str_replace($jsTarget, $jsReplacement, $content);
    echo "Modal script openKasirAnomaliModal added successfully.\n";
} else {
    echo "Modal script openKasirAnomaliModal already present or target not found.\n";
}

file_put_contents($file, $content);
echo "Done modifying pos/pos.php\n";
