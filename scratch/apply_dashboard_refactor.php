<?php
$file = __DIR__ . '/../index.php';
$content = file_get_contents($file);

// -------------------------------------------------------------
// 1. UPDATE KARTU ANOMALI & RETUR DI SUPERADMIN (KONSISTENSI TOTAL)
// -------------------------------------------------------------
$saOldCards = '    <!-- Widget Anomali FEFO Hari Ini (Bimbingan Poin 2) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? \'#fca5a5\' : \'#bbf7d0\' ?>;border-radius:10px;padding:16px 18px;<?= $anomaliHariIniCount > 0 ? \'box-shadow:0 4px 6px -1px rgba(220,38,38,0.1);\' : \'\' ?>">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#16a34a\' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            ANOMALI FEFO (HARI INI)
        </div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#16a34a\' ?>;line-height:1;"><?= number_format($anomaliHariIniCount) ?></div>
        <div style="font-size:11px;color:<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#16a34a\' ?>;margin-top:5px;font-weight:600;">
            <?= $anomaliHariIniCount == 0 ? \'Tertib FEFO (Nihil) ✓\' : \'Penukaran batch terdeteksi ⚠\' ?>
        </div>
    </div>

    <!-- Widget Monitoring Retur Barang (Bimbingan Poin 2) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? \'#fed7aa\' : \'#e4e9f0\' ?>;border-radius:10px;padding:16px 18px;<?= $returMenungguCount > 0 ? \'box-shadow:0 4px 6px -1px rgba(217,119,6,0.1);\' : \'\' ?>">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            MONITORING RETUR PO
        </div>
        <div style="display:flex;align-items:baseline;gap:6px;line-height:1;margin-top:2px;">
            <span style="font-size:24px;font-weight:900;color:#d97706;"><?= $returMenungguCount ?></span>
            <span style="font-size:10px;color:#d97706;font-weight:700;">Tunggu</span>
            <span style="color:#cbd5e1;font-weight:300;">|</span>
            <span style="font-size:24px;font-weight:900;color:#16a34a;"><?= $returSelesaiCount ?></span>
            <span style="font-size:10px;color:#16a34a;font-weight:700;">Selesai</span>
        </div>
        <div style="font-size:11px;color:#64748b;margin-top:7px;">Status retur ke supplier (Klik)</div>
    </div>';

$saNewCards = '    <!-- Widget Anomali FEFO Hari Ini (Superadmin - Desain Seragam) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? \'#fca5a5\' : \'#e4e9f0\' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANOMALI FEFO (HARI INI)</div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#1e293b\' ?>;line-height:1;"><?= number_format($anomaliHariIniCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $anomaliHariIniCount == 0 ? \'Tertib FEFO (Nihil) ✓\' : \'Penukaran batch terdeteksi\' ?></div>
    </div>

    <!-- Widget Monitoring Retur Barang (Superadmin - Desain Seragam) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? \'#fed7aa\' : \'#e4e9f0\' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STATUS RETUR SUPPLIER</div>
        <div style="font-size:30px;font-weight:900;color:<?= $returMenungguCount > 0 ? \'#d97706\' : \'#1e293b\' ?>;line-height:1;"><?= number_format($returMenungguCount) ?> <span style="font-size:13px;font-weight:600;color:#94a3b8;">/ <?= number_format($returSelesaiCount) ?> selesai</span></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $returMenungguCount > 0 ? \'Menunggu ganti barang PO\' : \'Semua retur terselesaikan ✓\' ?></div>
    </div>';

// -------------------------------------------------------------
// 2. UPDATE KARTU ANOMALI & RETUR DI ADMIN (KONSISTENSI TOTAL)
// -------------------------------------------------------------
$adOldCards = '    <!-- Widget Anomali FEFO Hari Ini (Admin) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? \'#fca5a5\' : \'#bbf7d0\' ?>;border-radius:10px;padding:16px 18px;<?= $anomaliHariIniCount > 0 ? \'box-shadow:0 4px 6px -1px rgba(220,38,38,0.1);\' : \'\' ?>">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#16a34a\' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            ANOMALI FEFO (HARI INI)
        </div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#16a34a\' ?>;line-height:1;"><?= number_format($anomaliHariIniCount) ?></div>
        <div style="font-size:11px;color:<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#16a34a\' ?>;margin-top:5px;font-weight:600;">
            <?= $anomaliHariIniCount == 0 ? \'Tertib FEFO (Nihil) ✓\' : \'Penukaran fisik terdeteksi ⚠\' ?>
        </div>
    </div>

    <!-- Widget Monitoring Retur Barang (Admin) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? \'#fed7aa\' : \'#e4e9f0\' ?>;border-radius:10px;padding:16px 18px;<?= $returMenungguCount > 0 ? \'box-shadow:0 4px 6px -1px rgba(217,119,6,0.1);\' : \'\' ?>">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            MONITORING RETUR PO
        </div>
        <div style="display:flex;align-items:baseline;gap:6px;line-height:1;margin-top:2px;">
            <span style="font-size:24px;font-weight:900;color:#d97706;"><?= $returMenungguCount ?></span>
            <span style="font-size:10px;color:#d97706;font-weight:700;">Tunggu</span>
            <span style="color:#cbd5e1;font-weight:300;">|</span>
            <span style="font-size:24px;font-weight:900;color:#16a34a;"><?= $returSelesaiCount ?></span>
            <span style="font-size:10px;color:#16a34a;font-weight:700;">Selesai</span>
        </div>
        <div style="font-size:11px;color:#64748b;margin-top:7px;">Status retur ke supplier (Klik)</div>
    </div>';

$adNewCards = '    <!-- Widget Anomali FEFO Hari Ini (Admin - Desain Seragam) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? \'#fca5a5\' : \'#e4e9f0\' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANOMALI FEFO (HARI INI)</div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliHariIniCount > 0 ? \'#dc2626\' : \'#1e293b\' ?>;line-height:1;"><?= number_format($anomaliHariIniCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $anomaliHariIniCount == 0 ? \'Tertib FEFO (Nihil) ✓\' : \'Penukaran batch terdeteksi\' ?></div>
    </div>

    <!-- Widget Monitoring Retur Barang (Admin - Desain Seragam) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? \'#fed7aa\' : \'#e4e9f0\' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STATUS RETUR SUPPLIER</div>
        <div style="font-size:30px;font-weight:900;color:<?= $returMenungguCount > 0 ? \'#d97706\' : \'#1e293b\' ?>;line-height:1;"><?= number_format($returMenungguCount) ?> <span style="font-size:13px;font-weight:600;color:#94a3b8;">/ <?= number_format($returSelesaiCount) ?> selesai</span></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $returMenungguCount > 0 ? \'Menunggu ganti barang PO\' : \'Semua retur terselesaikan ✓\' ?></div>
    </div>';

// -------------------------------------------------------------
// 3. GANTI GRAFIK CANVAS DENGAN TABEL REKAP KUANTITAS PENJUALAN
// -------------------------------------------------------------
$oldRow2 = '<!-- ROW 2: Grafik Penjualan & Volume Hari Ini -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px;">

    <!-- Grafik Kuantitas Penjualan -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;">
        <div style="font-size:12px;font-weight:700;color:#1e293b;margin-bottom:12px;">Kuantitas Penjualan (Luring vs Daring) — 7 Hari Terakhir</div>
        <div style="position:relative;height:240px;width:100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Volume & Transaksi Hari Ini (Stacked) -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <!-- Volume POS Hari Ini -->
        <div style="flex:1;background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;letter-spacing:.04em;">VOLUME JUAL POS (HARI INI)</div>
            <div style="font-size:26px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($volPosHariIni) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Unit terjual via Kasir Luring</div>
        </div>

        <!-- Volume E-Commerce Hari Ini -->
        <div style="flex:1;background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;letter-spacing:.04em;">VOLUME JUAL E-COMMERCE</div>
            <div style="font-size:26px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($volOnlineHariIni) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Unit terjual via Toko Online</div>
        </div>

        <!-- Transaksi Total Hari Ini -->
        <div style="flex:1;background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;letter-spacing:.04em;">TOTAL TRANSAKSI (HARI INI)</div>
            <div style="font-size:26px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($transaksiHariIni) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;"><?= number_format($totalProdukAktif) ?> SKU aktif · <?= $totalSupplier ?> supplier</div>
        </div>
    </div>
</div>';

$newRow2 = '<!-- ROW 2: Rekap Kuantitas Penjualan (Non-Grafik) & Volume Hari Ini -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px;">

    <!-- Tabel Rekap Faktual Kuantitas Penjualan 7 Hari Terakhir (Pengganti Grafik) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Rekap Kuantitas Penjualan (Luring vs Daring) — 7 Hari Terakhir</div>
            <span style="font-size:10px;color:#94a3b8;font-weight:600;">Satuan: Unit Terjual</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:11px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#64748b;">
                        <th style="padding:7px 10px;text-align:left;font-weight:600;">TANGGAL</th>
                        <th style="padding:7px 10px;text-align:right;font-weight:600;">POS (LURING)</th>
                        <th style="padding:7px 10px;text-align:right;font-weight:600;">E-COMMERCE (DARING)</th>
                        <th style="padding:7px 10px;text-align:right;font-weight:700;">TOTAL HARIAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sumPos = 0; $sumOnline = 0;
                    for ($i = 0; $i < count($chartLabels); $i++): 
                        $pVal = $chartDataPos[$i] ?? 0;
                        $oVal = $chartDataOnline[$i] ?? 0;
                        $totRow = $pVal + $oVal;
                        $sumPos += $pVal; $sumOnline += $oVal;
                    ?>
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:6px 10px;font-weight:600;color:#334155;"><?= htmlspecialchars($chartLabels[$i]) ?></td>
                        <td style="padding:6px 10px;text-align:right;color:#1a75d2;font-weight:600;"><?= number_format($pVal) ?></td>
                        <td style="padding:6px 10px;text-align:right;color:#d97706;font-weight:600;"><?= number_format($oVal) ?></td>
                        <td style="padding:6px 10px;text-align:right;font-weight:700;color:#1e293b;"><?= number_format($totRow) ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:700;">
                        <td style="padding:7px 10px;color:#1e293b;">TOTAL 7 HARI</td>
                        <td style="padding:7px 10px;text-align:right;color:#1a75d2;"><?= number_format($sumPos) ?></td>
                        <td style="padding:7px 10px;text-align:right;color:#d97706;"><?= number_format($sumOnline) ?></td>
                        <td style="padding:7px 10px;text-align:right;color:#1e293b;"><?= number_format($sumPos + $sumOnline) ?> Unit</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Volume & Transaksi Hari Ini (Stacked) -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <!-- Volume POS Hari Ini -->
        <div style="flex:1;background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;letter-spacing:.04em;">VOLUME JUAL POS (HARI INI)</div>
            <div style="font-size:26px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($volPosHariIni) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Unit terjual via Kasir Luring</div>
        </div>

        <!-- Volume E-Commerce Hari Ini -->
        <div style="flex:1;background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;letter-spacing:.04em;">VOLUME JUAL E-COMMERCE</div>
            <div style="font-size:26px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($volOnlineHariIni) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Unit terjual via Toko Online</div>
        </div>

        <!-- Transaksi Total Hari Ini -->
        <div style="flex:1;background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:6px;letter-spacing:.04em;">TOTAL TRANSAKSI (HARI INI)</div>
            <div style="font-size:26px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($transaksiHariIni) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;"><?= number_format($totalProdukAktif) ?> SKU aktif · <?= $totalSupplier ?> supplier</div>
        </div>
    </div>
</div>';

// Normalisasi baris
$normContent = str_replace("\r\n", "\n", $content);
$normSaOld = str_replace("\r\n", "\n", $saOldCards);
$normAdOld = str_replace("\r\n", "\n", $adOldCards);
$normRow2Old = str_replace("\r\n", "\n", $oldRow2);

if (strpos($normContent, $normSaOld) !== false) {
    $normContent = str_replace($normSaOld, $saNewCards, $normContent);
    echo "Superadmin cards updated to uniform clean style.\n";
} else {
    echo "Warning: Superadmin old cards not matched.\n";
}

if (strpos($normContent, $normAdOld) !== false) {
    $normContent = str_replace($normAdOld, $adNewCards, $normContent);
    echo "Admin cards updated to uniform clean style.\n";
} else {
    echo "Warning: Admin old cards not matched.\n";
}

if (strpos($normContent, $normRow2Old) !== false) {
    $normContent = str_replace($normRow2Old, $newRow2, $normContent);
    echo "ROW 2 Chart replaced with tabular data summary.\n";
} else {
    echo "Warning: ROW 2 Chart not matched.\n";
}

file_put_contents($file, $normContent);
echo "Done applying dashboard refactor.\n";

