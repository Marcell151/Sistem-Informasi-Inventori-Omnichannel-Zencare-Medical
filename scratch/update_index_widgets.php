<?php
$content = file_get_contents('index.php');

// 1. Ganti Grid Superadmin ROW 1
$superRowOld = <<<'EOD'
<!-- ROW 1: Widgets Inventori Utama (4 kotak) -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;">
EOD;

$superRowNew = <<<'EOD'
<!-- ROW 1: Widgets Inventori Utama (6 kotak Responsif) -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:18px;">
EOD;

// Widget 5 & 6 Superadmin
$superWidgetsOld = <<<'EOD'
    <!-- Antrean Pesanan Daring -->
    <a href="admin/pesanan.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $pesananMenunggu>0?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANTREAN PESANAN DARING</div>
        <div style="font-size:30px;font-weight:900;color:<?= $pesananMenunggu>0?'#2563eb':'#1e293b' ?>;line-height:1;"><?= number_format($pesananMenunggu) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Status: Menunggu</div>
    </a>
</div>
EOD;

$superWidgetsNew = <<<'EOD'
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
            <?= $anomaliHariIniCount == 0 ? 'Tertib FEFO (Nihil) ✓' : 'Penukaran fisik terdeteksi ⚠' ?>
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

// 2. Ganti Grid Admin Row 1
$adminWidgetsOld = <<<'EOD'
<!-- Row 1: Metrics Admin -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">TOTAL STOK FISIK</div>
        <div style="font-size:30px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($totalStokFisik) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Unit tersedia</div>
    </div>
    <a href="#" onclick="Swal.fire({title:'Informasi', text:'Silakan scroll ke bawah untuk melihat detail pada tabel Peringatan Stok Menipis. Akses laporan penuh hanya untuk Superadmin.', icon:'info'}); return false;" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $stokKritisCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STOK MENIPIS (ROP)</div>
        <div style="font-size:30px;font-weight:900;color:<?= $stokKritisCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($stokKritisCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $stokKritisCount==0?'Semua stok aman ✓':'SKU mencapai ROP' ?></div>
    </a>
    <a href="#" onclick="Swal.fire({title:'Informasi', text:'Silakan scroll ke bawah untuk melihat detail pada tabel Peringatan Kedaluwarsa. Akses laporan penuh hanya untuk Superadmin.', icon:'info'}); return false;" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $expWarningCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">PERINGATAN EXP.</div>
        <div style="font-size:30px;font-weight:900;color:<?= $expWarningCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($expWarningCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Masuk batas peringatan</div>
    </a>
    <a href="admin/pesanan.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $pesananMenunggu>0?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">PESANAN BARU</div>
        <div style="font-size:30px;font-weight:900;color:<?= $pesananMenunggu>0?'#2563eb':'#1e293b' ?>;line-height:1;"><?= number_format($pesananMenunggu) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Status: Menunggu</div>
    </a>
</div>
EOD;

$adminWidgetsNew = <<<'EOD'
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

// 3. Tambah Tabel Panel Anomali & Retur (Sebelum layoutFooter)
$sectionOld = "<?php layoutFooter(); ?>";
$sectionNew = <<<'EOD'
<!-- ============================================================ -->
<!-- PANEL ANOMALI FEFO HARI INI & MONITORING RETUR SUPPLIER       -->
<!-- ============================================================ -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
    <!-- Panel Anomali FEFO Hari Ini -->
    <div style="background:#fff;border:1px solid <?= $anomaliHariIniCount > 0 ? '#fca5a5' : '#e4e9f0' ?>;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;background:<?= $anomaliHariIniCount > 0 ? '#fef2f2' : '#f8fafc' ?>;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:<?= $anomaliHariIniCount > 0 ? '#991b1b' : '#1e293b' ?>;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $anomaliHariIniCount > 0 ? '#dc2626' : '#16a34a' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Audit SOP: Anomali FEFO Hari Ini
            </div>
            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;<?= $anomaliHariIniCount > 0 ? 'background:#fee2e2;color:#991b1b' : 'background:#dcfce7;color:#166534' ?>">
                <?= $anomaliHariIniCount ?> Kejadian
            </span>
        </div>
        <?php if (empty($listAnomaliHariIni)): ?>
            <div style="padding:28px 16px;text-align:center;color:#16a34a;font-size:12px;font-weight:600;">
                ✓ Seluruh transaksi hari ini tertib mengikuti urutan rekomendasi FEFO (Nihil Anomali).
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#64748b;">
                            <th style="padding:8px 12px;text-align:left;">INVOICE</th>
                            <th style="padding:8px 12px;text-align:left;">PRODUK</th>
                            <th style="padding:8px 12px;text-align:center;">SEHARUSNYA</th>
                            <th style="padding:8px 12px;text-align:center;">DIAMBIL</th>
                            <th style="padding:8px 12px;text-align:right;">QTY</th>
                            <th style="padding:8px 12px;text-align:left;">KASIR / WAKTU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listAnomaliHariIni as $an): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px;font-family:monospace;font-weight:700;color:#1e293b;"><?= htmlspecialchars($an['no_invoice']) ?></td>
                            <td style="padding:8px 12px;color:#334155;font-weight:600;"><?= htmlspecialchars($an['nama_produk']) ?></td>
                            <td style="padding:8px 12px;text-align:center;"><span style="padding:2px 6px;border-radius:4px;background:#dbeafe;color:#1e40af;font-weight:700;font-family:monospace;"><?= htmlspecialchars($an['batch_seharusnya']) ?></span></td>
                            <td style="padding:8px 12px;text-align:center;"><span style="padding:2px 6px;border-radius:4px;background:#fee2e2;color:#991b1b;font-weight:700;font-family:monospace;"><?= htmlspecialchars($an['batch_diambil']) ?></span></td>
                            <td style="padding:8px 12px;text-align:right;font-weight:700;color:#dc2626;"><?= number_format($an['qty']) ?></td>
                            <td style="padding:8px 12px;color:#64748b;"><?= htmlspecialchars($an['operator']) ?> <br><span style="font-size:9px;color:#94a3b8;"><?= date('H:i', strtotime($an['created_at'])) ?> WIB</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel Monitoring Retur Barang ke Supplier -->
    <div style="background:#fff;border:1px solid <?= $returMenungguCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;background:<?= $returMenungguCount > 0 ? '#fffbeb' : '#f8fafc' ?>;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:<?= $returMenungguCount > 0 ? '#92400e' : '#1e293b' ?>;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $returMenungguCount > 0 ? '#d97706' : '#16a34a' ?>" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Status Retur Barang Rusak (Penerimaan PO)
            </div>
            <a href="inventori/tambah_stok.php" style="font-size:11px;color:#1a75d2;text-decoration:none;font-weight:600;">Kelola Retur →</a>
        </div>
        <?php if (empty($listReturMonitoring)): ?>
            <div style="padding:28px 16px;text-align:center;color:#64748b;font-size:12px;">
                Tidak ada data retur barang penerimaan PO aktif ✓
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#64748b;">
                            <th style="padding:8px 12px;text-align:left;">REF. PO</th>
                            <th style="padding:8px 12px;text-align:left;">SUPPLIER</th>
                            <th style="padding:8px 12px;text-align:left;">PRODUK</th>
                            <th style="padding:8px 12px;text-align:right;">QTY</th>
                            <th style="padding:8px 12px;text-align:center;">STATUS RETUR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listReturMonitoring as $rm): 
                            $isWaiting = ($rm['status_retur'] === 'menunggu_pengganti');
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px;font-family:monospace;font-weight:700;color:#1e293b;"><?= htmlspecialchars($rm['no_referensi']) ?></td>
                            <td style="padding:8px 12px;color:#334155;"><?= htmlspecialchars($rm['nama_supplier']) ?></td>
                            <td style="padding:8px 12px;color:#334155;font-weight:600;"><?= htmlspecialchars($rm['nama_produk']) ?></td>
                            <td style="padding:8px 12px;text-align:right;font-weight:700;color:#dc2626;"><?= number_format($rm['qty_rusak']) ?></td>
                            <td style="padding:8px 12px;text-align:center;">
                                <?php if ($isWaiting): ?>
                                    <span style="padding:2px 8px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:700;font-size:10px;border:1px solid #fde68a;">
                                        ⏳ Menunggu Penggantian
                                    </span>
                                <?php else: ?>
                                    <span style="padding:2px 8px;border-radius:4px;background:#dcfce7;color:#166534;font-weight:700;font-size:10px;border:1px solid #bbf7d0;">
                                        ✓ Selesai Diganti (Batch: <?= htmlspecialchars($rm['no_batch_pengganti'] ?: '-') ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Dialog Functions for Dashboard Widgets -->
<script>
function openAnomaliModal() {
    const dataAnomali = <?= json_encode($listAnomaliHariIni) ?>;
    if (!dataAnomali || dataAnomali.length === 0) {
        Swal.fire({
            title: 'Audit FEFO Tertib ✓',
            html: '<div class="text-sm text-slate-600 mt-2">Tidak ada anomali atau penukaran batch fisik yang terjadi hari ini.<br><br><span class="text-xs text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 font-bold inline-block">Seluruh transaksi tertib mengikuti urutan First-Expired, First-Out (FEFO)</span></div>',
            icon: 'success',
            confirmButtonColor: '#1a75d2'
        });
        return;
    }

    let rowsHtml = '';
    dataAnomali.forEach(an => {
        rowsHtml += `
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;font-family:monospace;font-weight:700;color:#1e293b;">${an.no_invoice}</td>
                <td style="padding:8px;text-align:left;color:#334155;font-weight:600;">${an.nama_produk}</td>
                <td style="padding:8px;color:#1e40af;font-family:monospace;font-weight:700;background:#eff6ff;">${an.batch_seharusnya}</td>
                <td style="padding:8px;color:#991b1b;font-family:monospace;font-weight:700;background:#fef2f2;">${an.batch_diambil}</td>
                <td style="padding:8px;text-align:right;font-weight:700;color:#dc2626;">${an.qty}</td>
                <td style="padding:8px;color:#64748b;">${an.operator} (${an.created_at.substring(11, 16)})</td>
            </tr>
        `;
    });

    Swal.fire({
        title: 'Rekap Anomali FEFO Hari Ini',
        html: `
            <div style="text-align:left;font-size:12px;margin-bottom:10px;color:#475569;">
                Daftar transaksi kasir di mana kasir mengambil batch fisik yang berbeda dari rekomendasi FEFO sistem:
            </div>
            <div style="max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #cbd5e1;position:sticky;top:0;">
                        <tr>
                            <th style="padding:7px;">Invoice</th>
                            <th style="padding:7px;text-align:left;">Produk</th>
                            <th style="padding:7px;">Seharusnya</th>
                            <th style="padding:7px;">Diambil (Ditukar)</th>
                            <th style="padding:7px;text-align:right;">Qty</th>
                            <th style="padding:7px;">Kasir</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
            <div style="font-size:11px;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;padding:8px 12px;border-radius:8px;margin-top:12px;text-align:left;font-weight:600;">
                ⚠ Catatan SOP: Penukaran fisik obat harus diminimalkan untuk mencegah resiko obat kedaluwarsa tertinggal di rak.
            </div>
        `,
        width: '780px',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#1a75d2'
    });
}

function openReturModal() {
    const listRetur = <?= json_encode($listReturMonitoring) ?>;
    if (!listRetur || listRetur.length === 0) {
        Swal.fire({
            title: 'Monitoring Retur Bersih ✓',
            text: 'Tidak ada barang rusak penerimaan PO yang sedang menunggu penggantian.',
            icon: 'info',
            confirmButtonColor: '#1a75d2'
        });
        return;
    }

    let rowsHtml = '';
    listRetur.forEach(r => {
        const isWaiting = (r.status_retur === 'menunggu_pengganti');
        const badge = isWaiting 
            ? '<span style="padding:2px 8px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:700;font-size:10px;">⏳ Menunggu Penggantian</span>'
            : `<span style="padding:2px 8px;border-radius:4px;background:#dcfce7;color:#166534;font-weight:700;font-size:10px;">✓ Selesai Diganti (Batch: ${r.no_batch_pengganti || '-'})</span>`;
        rowsHtml += `
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;font-family:monospace;font-weight:700;">${r.no_referensi}</td>
                <td style="padding:8px;text-align:left;">${r.nama_supplier}</td>
                <td style="padding:8px;text-align:left;font-weight:600;">${r.nama_produk}</td>
                <td style="padding:8px;text-align:right;font-weight:700;color:#dc2626;">${r.qty_rusak}</td>
                <td style="padding:8px;">${badge}</td>
            </tr>
        `;
    });

    Swal.fire({
        title: 'Monitoring Retur Barang (Penerimaan PO)',
        html: `
            <div style="text-align:left;font-size:12px;margin-bottom:10px;color:#475569;">
                Status tindak lanjut barang rusak/cacat yang dikembalikan ke supplier:
            </div>
            <div style="max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #cbd5e1;position:sticky;top:0;">
                        <tr>
                            <th style="padding:7px;">Ref. PO</th>
                            <th style="padding:7px;text-align:left;">Supplier</th>
                            <th style="padding:7px;text-align:left;">Produk</th>
                            <th style="padding:7px;text-align:right;">Qty Rusak</th>
                            <th style="padding:7px;">Status Penggantian</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
        `,
        width: '780px',
        showCancelButton: true,
        cancelButtonText: 'Tutup',
        confirmButtonText: 'Buka Halaman Penerimaan & Retur',
        confirmButtonColor: '#1a75d2'
    }).then(res => {
        if (res.isConfirmed) {
            window.location.href = 'inventori/tambah_stok.php';
        }
    });
}
</script>

<?php layoutFooter(); ?>
EOD;

if (strpos($content, $superWidgetsOld) !== false) {
    $content = str_replace($superWidgetsOld, $superWidgetsNew, $content);
    echo "Superadmin widgets updated.\n";
} else {
    echo "Notice: superWidgetsOld not matched directly.\n";
}

if (strpos($content, $superRowOld) !== false) {
    $content = str_replace($superRowOld, $superRowNew, $content);
    echo "Superadmin row updated.\n";
}

if (strpos($content, $adminWidgetsOld) !== false) {
    $content = str_replace($adminWidgetsOld, $adminWidgetsNew, $content);
    echo "Admin widgets updated.\n";
} else {
    echo "Notice: adminWidgetsOld not matched directly.\n";
}

if (strpos($content, 'PANEL ANOMALI FEFO HARI INI') === false) {
    $content = str_replace($sectionOld, $sectionNew, $content);
    echo "Bottom panels and modals injected successfully.\n";
}

file_put_contents('index.php', $content);

