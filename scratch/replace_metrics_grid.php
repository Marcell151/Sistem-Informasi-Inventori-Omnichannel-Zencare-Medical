<?php
$lines = file('index.php');

$newSuperWidgets = <<<'EOD'
<!-- ROW 1: Widgets Inventori Utama (6 kotak Responsif) -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:18px;">

    <!-- Total Stok Fisik -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            TOTAL STOK FISIK
        </div>
        <div style="font-size:30px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($totalStokFisik) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Unit tersedia di gudang</div>
    </div>

    <!-- Stok Menipis -->
    <a href="laporan/ketersediaan_stok.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $stokKritisCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STOK MENIPIS (ROP)</div>
        <div style="font-size:30px;font-weight:900;color:<?= $stokKritisCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($stokKritisCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $stokKritisCount==0 ? 'Semua stok aman ✓' : 'SKU mencapai ROP' ?></div>
    </a>

    <!-- Hampir Kedaluwarsa -->
    <a href="laporan/logistik_medis.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $expWarningCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">PERINGATAN EXP.</div>
        <div style="font-size:30px;font-weight:900;color:<?= $expWarningCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($expWarningCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Masuk batas peringatan hari</div>
    </a>

    <!-- Antrean Pesanan Daring -->
    <a href="admin/pesanan.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $pesananMenunggu>0?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANTREAN PESANAN DARING</div>
        <div style="font-size:30px;font-weight:900;color:<?= $pesananMenunggu>0?'#2563eb':'#1e293b' ?>;line-height:1;"><?= number_format($pesananMenunggu) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Status: Menunggu</div>
    </a>

    <!-- Widget Anomali FEFO Hari Ini (Bimbingan Poin 2) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? '#fca5a5' : '#bbf7d0' ?>;border-radius:10px;padding:16px 18px;<?= $anomaliHariIniCount > 0 ? 'box-shadow:0 4px 6px -1px rgba(220,38,38,0.1);' : '' ?>">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            ANOMALI FEFO (HARI INI)
        </div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>;line-height:1;"><?= number_format($anomaliHariIniCount) ?></div>
        <div style="font-size:11px;color:<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>;margin-top:5px;font-weight:600;">
            <?= $anomaliHariIniCount == 0 ? 'Tertib FEFO (Nihil) ✓' : 'Penukaran batch terdeteksi ⚠' ?>
        </div>
    </div>

    <!-- Widget Monitoring Retur Barang (Bimbingan Poin 2) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;padding:16px 18px;<?= $returMenungguCount > 0 ? 'box-shadow:0 4px 6px -1px rgba(217,119,6,0.1);' : '' ?>">
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
    </div>
</div>
EOD;

$newAdminWidgets = <<<'EOD'
<!-- Row 1: Metrics Admin (6 Kotak Responsif) -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:20px;">
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">TOTAL STOK FISIK</div>
        <div style="font-size:30px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($totalStokFisik) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Unit tersedia</div>
    </div>
    <a href="#" onclick="Swal.fire({title:'Informasi', text:'Silakan scroll ke bawah untuk melihat detail pada tabel Peringatan Stok Menipis.', icon:'info'}); return false;" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $stokKritisCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STOK MENIPIS (ROP)</div>
        <div style="font-size:30px;font-weight:900;color:<?= $stokKritisCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($stokKritisCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $stokKritisCount==0?'Semua stok aman ✓':'SKU mencapai ROP' ?></div>
    </a>
    <a href="#" onclick="Swal.fire({title:'Informasi', text:'Silakan scroll ke bawah untuk melihat detail pada tabel Peringatan Kedaluwarsa.', icon:'info'}); return false;" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $expWarningCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">PERINGATAN EXP.</div>
        <div style="font-size:30px;font-weight:900;color:<?= $expWarningCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($expWarningCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Masuk batas peringatan</div>
    </a>
    <a href="admin/pesanan.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $pesananMenunggu>0?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">PESANAN BARU</div>
        <div style="font-size:30px;font-weight:900;color:<?= $pesananMenunggu>0?'#2563eb':'#1e293b' ?>;line-height:1;"><?= number_format($pesananMenunggu) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Status: Menunggu</div>
    </a>

    <!-- Widget Anomali FEFO Hari Ini (Admin) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? '#fca5a5' : '#bbf7d0' ?>;border-radius:10px;padding:16px 18px;<?= $anomaliHariIniCount > 0 ? 'box-shadow:0 4px 6px -1px rgba(220,38,38,0.1);' : '' ?>">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:5px;letter-spacing:.04em;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            ANOMALI FEFO (HARI INI)
        </div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>;line-height:1;"><?= number_format($anomaliHariIniCount) ?></div>
        <div style="font-size:11px;color:<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>;margin-top:5px;font-weight:600;">
            <?= $anomaliHariIniCount == 0 ? 'Tertib FEFO (Nihil) ✓' : 'Penukaran fisik terdeteksi ⚠' ?>
        </div>
    </div>

    <!-- Widget Monitoring Retur Barang (Admin) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;padding:16px 18px;<?= $returMenungguCount > 0 ? 'box-shadow:0 4px 6px -1px rgba(217,119,6,0.1);' : '' ?>">
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
    </div>
</div>
EOD;

$fullContent = implode('', $lines);

// Replace Superadmin grid (lines ~312 to 345)
$pos1 = strpos($fullContent, '<!-- ROW 1: Widgets Inventori Utama (4 kotak) -->');
$pos2 = strpos($fullContent, '<!-- ROW 2: Grafik Penjualan', $pos1);
if ($pos1 !== false && $pos2 !== false) {
    $fullContent = substr_replace($fullContent, $newSuperWidgets . "\n\n", $pos1, $pos2 - $pos1);
    echo "Superadmin ROW 1 updated successfully.\n";
} else {
    echo "Error finding Superadmin ROW 1.\n";
}

// Replace Admin grid (lines ~750 to 775)
$posA1 = strpos($fullContent, '<!-- Row 1: Metrics Admin -->');
$posA2 = strpos($fullContent, '<!-- Quick Actions Admin -->', $posA1);
if ($posA1 !== false && $posA2 !== false) {
    $fullContent = substr_replace($fullContent, $newAdminWidgets . "\n\n", $posA1, $posA2 - $posA1);
    echo "Admin ROW 1 updated successfully.\n";
} else {
    echo "Error finding Admin ROW 1.\n";
}

file_put_contents('index.php', $fullContent);

