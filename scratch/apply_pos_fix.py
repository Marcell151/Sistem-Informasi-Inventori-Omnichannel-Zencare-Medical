# -*- coding: utf-8 -*-
import re

pos_path = r"c:\xampp\htdocs\inventory_zencare\pos\pos.php"

with open(pos_path, 'r', encoding='utf-8') as f:
    code = f.read()

# 1. Update requireRole
code = code.replace(
    "requireRole(['superadmin', 'kasir']);",
    "requireRole(['superadmin', 'admin', 'kasir']);"
)

# 2. Update Anomali FEFO query to be store-wide
old_query = '''// Query Anomali FEFO Hari Ini untuk Peringatan Panel Kasir
$kasirUserId = $_SESSION["user_id"] ?? 0;
$userRole = $_SESSION["role"] ?? "kasir";

if ($userRole === "superadmin") {
    $stmtPendingKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE status IN ('terlewat', 'sebagian')");
    $anomaliPendingKasir = (int)$stmtPendingKasir->fetchColumn();

    $stmtTotalKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE (status IN ('terlewat', 'sebagian') OR DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))");
    $anomaliHariIniKasir = (int)$stmtTotalKasir->fetchColumn();

    $stmtListAnomaliKasir = $pdo->query("
        SELECT la.*, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
               pv.satuan_kecil, pv.satuan_besar,
               p.no_invoice, p_res.no_invoice AS no_invoice_resolved,
               u.nama_lengkap AS nama_kasir
        FROM log_anomali_fefo la
        JOIN produk_variasi pv ON la.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN penjualan p ON la.id_penjualan = p.id
        LEFT JOIN penjualan p_res ON la.resolved_in_penjualan = p_res.id
        LEFT JOIN users u ON la.id_user = u.id
        WHERE (la.status IN ('terlewat', 'sebagian') OR DATE(la.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))
        ORDER BY (la.status = 'terlewat') DESC, (la.status = 'sebagian') DESC, la.created_at DESC
    ");
    $listAnomaliKasir = $stmtListAnomaliKasir->fetchAll();
} else {
    $stmtPendingKasir = $pdo->prepare("SELECT COUNT(*) FROM log_anomali_fefo WHERE status IN ('terlewat', 'sebagian') AND id_user = ?");
    $stmtPendingKasir->execute([$kasirUserId]);
    $anomaliPendingKasir = (int)$stmtPendingKasir->fetchColumn();

    $stmtTotalKasir = $pdo->prepare("SELECT COUNT(*) FROM log_anomali_fefo WHERE (status IN ('terlewat', 'sebagian') OR DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AND id_user = ?");
    $stmtTotalKasir->execute([$kasirUserId]);
    $anomaliHariIniKasir = (int)$stmtTotalKasir->fetchColumn();

    $stmtListAnomaliKasir = $pdo->prepare("
        SELECT la.*, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
               pv.satuan_kecil, pv.satuan_besar,
               p.no_invoice, p_res.no_invoice AS no_invoice_resolved,
               u.nama_lengkap AS nama_kasir
        FROM log_anomali_fefo la
        JOIN produk_variasi pv ON la.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN penjualan p ON la.id_penjualan = p.id
        LEFT JOIN penjualan p_res ON la.resolved_in_penjualan = p_res.id
        LEFT JOIN users u ON la.id_user = u.id
        WHERE (la.status IN ('terlewat', 'sebagian') OR DATE(la.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AND la.id_user = ?
        ORDER BY (la.status = 'terlewat') DESC, (la.status = 'sebagian') DESC, la.created_at DESC
    ");
    $stmtListAnomaliKasir->execute([$kasirUserId]);
    $listAnomaliKasir = $stmtListAnomaliKasir->fetchAll();
}'''

new_query = '''// Query Anomali FEFO untuk Peringatan Panel Kasir (Toko ZenCare Medical)
$kasirUserId = $_SESSION["user_id"] ?? 0;
$userRole = $_SESSION["role"] ?? "kasir";

$stmtPendingKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE status IN ('terlewat', 'sebagian')");
$anomaliPendingKasir = (int)$stmtPendingKasir->fetchColumn();

$stmtTotalKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE (status IN ('terlewat', 'sebagian') OR DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))");
$anomaliHariIniKasir = (int)$stmtTotalKasir->fetchColumn();

$stmtListAnomaliKasir = $pdo->query("
    SELECT la.*, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
           pv.satuan_kecil, pv.satuan_besar,
           p.no_invoice, p_res.no_invoice AS no_invoice_resolved,
           u.nama_lengkap AS nama_kasir
    FROM log_anomali_fefo la
    JOIN produk_variasi pv ON la.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN penjualan p ON la.id_penjualan = p.id
    LEFT JOIN penjualan p_res ON la.resolved_in_penjualan = p_res.id
    LEFT JOIN users u ON la.id_user = u.id
    WHERE (la.status IN ('terlewat', 'sebagian') OR DATE(la.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))
    ORDER BY (la.status = 'terlewat') DESC, (la.status = 'sebagian') DESC, la.created_at DESC
");
$listAnomaliKasir = $stmtListAnomaliKasir->fetchAll();'''

if old_query in code:
    code = code.replace(old_query, new_query)
    print("Replaced Anomali query successfully!")
else:
    print("Warning: old_query not found exactly, will use regex if needed.")

# 3. Update header bar pills to always be interactive
old_pills = '''                        <?php if ($anomaliPendingKasir > 0): ?>
                        <button type="button" onclick="toggleBatchTerlewatPanel()" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-bold transition bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300" title="Klik untuk membuka rincian deviasi batch obat">
                            <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>Batch Terlewat (<?= $anomaliPendingKasir ?>)</span>
                            <span id="pill_arrow_indicator" class="text-[10px] text-amber-700 font-mono">▾</span>
                        </button>
                        <?php elseif ($anomaliHariIniKasir > 0): ?>
                        <button type="button" onclick="toggleBatchTerlewatPanel()" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300" title="Klik untuk membuka riwayat batch yang sudah disesuaikan">
                            <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                            <span>Batch Selesai (<?= $anomaliHariIniKasir ?>)</span>
                            <span id="pill_arrow_indicator" class="text-[10px] text-slate-500 font-mono">▾</span>
                        </button>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#eef6fb] text-[#0f2d5a] border border-[#cde3f5]" title="Seluruh transaksi mematuhi batch terdepan FEFO">
                            <svg class="w-3.5 h-3.5 text-[#1a75d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                            <span>Batch Sesuai FEFO</span>
                        </span>
                        <?php endif; ?>'''

new_pills = '''                        <?php if ($anomaliPendingKasir > 0): ?>
                        <button type="button" onclick="toggleBatchTerlewatPanel()" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-bold transition bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 cursor-pointer shadow-sm" title="Klik untuk membuka rincian deviasi batch obat">
                            <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>Batch Terlewat (<?= $anomaliPendingKasir ?>)</span>
                            <span id="pill_arrow_indicator" class="text-[10px] text-amber-700 font-mono font-bold">▾</span>
                        </button>
                        <?php elseif ($anomaliHariIniKasir > 0): ?>
                        <button type="button" onclick="toggleBatchTerlewatPanel()" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 cursor-pointer shadow-sm" title="Klik untuk membuka riwayat batch yang sudah disesuaikan">
                            <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                            <span>Batch Selesai (<?= $anomaliHariIniKasir ?>)</span>
                            <span id="pill_arrow_indicator" class="text-[10px] text-slate-500 font-mono font-bold">▾</span>
                        </button>
                        <?php else: ?>
                        <button type="button" onclick="openKasirAnomaliModal()" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#eef6fb] hover:bg-[#dbeafe] text-[#0f2d5a] border border-[#cde3f5] transition cursor-pointer shadow-sm" title="Klik untuk melihat log kepatuhan batch FEFO">
                            <svg class="w-3.5 h-3.5 text-[#1a75d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                            <span>Batch Sesuai FEFO</span>
                        </button>
                        <?php endif; ?>'''

if old_pills in code:
    code = code.replace(old_pills, new_pills)
    print("Replaced header pills successfully!")
else:
    print("Warning: old_pills not matched exactly.")

# 4. Update the Panel header action buttons
old_panel_hdr = '''                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" class="text-xs font-semibold text-[#0f2d5a] bg-white hover:bg-slate-50 px-2.5 py-1 rounded border border-slate-300 transition flex items-center gap-1.5">
                            <span id="label_toggle_drawer">Buka Rincian</span>
                            <span id="icon_toggle_drawer" class="text-xs font-mono text-slate-500">▾</span>
                        </button>
                    </div>'''

new_panel_hdr = '''                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" onclick="event.stopPropagation(); openKasirAnomaliModal();" class="text-xs font-semibold text-[#1a75d2] hover:text-[#0f2d5a] bg-white hover:bg-blue-50 px-2.5 py-1 rounded border border-slate-300 transition flex items-center gap-1 cursor-pointer" title="Buka dalam tampilan modal popup">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                            <span>Popup Dialog</span>
                        </button>
                        <button type="button" onclick="event.stopPropagation(); toggleBatchTerlewatPanel();" class="text-xs font-semibold text-[#0f2d5a] bg-white hover:bg-slate-50 px-2.5 py-1 rounded border border-slate-300 transition flex items-center gap-1.5 cursor-pointer">
                            <span id="label_toggle_drawer">Buka Rincian</span>
                            <span id="icon_toggle_drawer" class="text-xs font-mono text-slate-500">▾</span>
                        </button>
                    </div>'''

if old_panel_hdr in code:
    code = code.replace(old_panel_hdr, new_panel_hdr)
    print("Replaced panel header successfully!")
else:
    print("Warning: old_panel_hdr not matched exactly.")

# 5. Fix JS toggleBatchTerlewatPanel and openKasirAnomaliModal
# Find function toggleBatchTerlewatPanel() and function openKasirAnomaliModal()
p_js = r'function toggleBatchTerlewatPanel\(\) \{.*?function openKasirAnomaliModal\(\) \{.*?\n\}'
match = re.search(p_js, code, re.DOTALL)
if not match:
    raise ValueError("Could not find toggleBatchTerlewatPanel / openKasirAnomaliModal in pos.php")

new_js = '''function toggleBatchTerlewatPanel() {
    let panel = document.getElementById("panel_batch_terlewat");
    if (!panel) {
        openKasirAnomaliModal();
        return;
    }
    panel.style.display = "block";
    const drawer = document.getElementById("drawer_batch_content");
    const lbl = document.getElementById("label_toggle_drawer");
    const icon = document.getElementById("icon_toggle_drawer");
    const pillArrow = document.getElementById("pill_arrow_indicator");
    if (!drawer) return;

    const isHidden = (drawer.style.display === "none" || window.getComputedStyle(drawer).display === "none");
    if (isHidden) {
        drawer.style.display = "block";
        if (lbl) lbl.innerText = "Tutup Rincian";
        if (icon) icon.innerText = "▲";
        if (pillArrow) pillArrow.innerText = "▲";
        try {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch(e) {}
    } else {
        drawer.style.display = "none";
        if (lbl) lbl.innerText = "Buka Rincian";
        if (icon) icon.innerText = "▼";
        if (pillArrow) pillArrow.innerText = "▼";
    }
}

function openKasirAnomaliModal() {
    <?php
    $rowsHtml = "";
    if (empty($listAnomaliKasir)) {
        $rowsHtml = "<tr><td colspan='7' class='px-4 py-8 text-center text-slate-400 font-semibold'>Belum ada catatan anomali FEFO. Seluruh transaksi kasir mematuhi urutan batch FEFO secara sempurna.</td></tr>";
    } else {
        foreach ($listAnomaliKasir as $i => $row) {
            $num = $i + 1;
            $inv = htmlspecialchars($row['no_invoice'] ?? '-');
            $prod = htmlspecialchars($row['nama_produk'] ?? '-');
            $seharusnya = htmlspecialchars($row['batch_seharusnya'] ?? '-');
            $diambil = htmlspecialchars($row['batch_diambil'] ?? '-');
            $isBesar = (($row['satuan_tipe'] ?? 'kecil') === 'besar');
            $satLabel = $isBesar ? ($row['satuan_besar'] ?: 'Box') : ($row['satuan_kecil'] ?: 'Pcs');
            $satBadge = $isBesar ? '<span class="px-1.5 py-0.5 rounded text-[9px] bg-indigo-50 text-indigo-700 border border-indigo-200 font-semibold">Satuan Besar (Grosir)</span>' : '<span class="px-1.5 py-0.5 rounded text-[9px] bg-slate-100 text-slate-600 border border-slate-200 font-semibold">Satuan Kecil (Eceran)</span>';
            $qtyTotal = (int)$row['qty'];
            $qtyDis = (int)($row['qty_disesuaikan'] ?? 0);
            $rowStatus = $row['status'] ?? 'terlewat';
            $jam = date('d/m H:i', strtotime($row['created_at']));
            $kasir = htmlspecialchars($row['nama_kasir'] ?? 'Kasir');
            
            if ($rowStatus === 'disesuaikan') {
                $qtyDisplay = "{$qtyTotal}/{$qtyTotal} " . htmlspecialchars($satLabel);
                $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-3 h-3 text-emerald-600 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Disesuaikan (' . $qtyTotal . '/' . $qtyTotal . ')</span><div class="text-[9px] text-slate-500 font-mono mt-0.5">di ' . htmlspecialchars($row['no_invoice_resolved'] ?? '-') . '</div>';
            } elseif ($rowStatus === 'sebagian') {
                $qtyDisplay = "<span class=\\"text-amber-700 font-bold\\">{$qtyDis}/{$qtyTotal}</span> " . htmlspecialchars($satLabel);
                $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-300"><svg class="w-3 h-3 text-amber-600 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Sebagian (' . $qtyDis . '/' . $qtyTotal . ')</span><div class="text-[9px] text-amber-700 font-medium mt-0.5">Sisa ' . ($qtyTotal - $qtyDis) . ' ' . htmlspecialchars($satLabel) . ' terlewat</div>';
            } else {
                $qtyDisplay = "0/{$qtyTotal} " . htmlspecialchars($satLabel);
                $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-300"><svg class="w-3 h-3 text-slate-500 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Menunggu Dikeluarkan</span>';
            }

            $rowsHtml .= "<tr class='border-b border-slate-100 hover:bg-slate-50 transition'>";
            $rowsHtml .= "<td class='px-3 py-2 text-center text-slate-500 font-mono text-xs'>{$num}</td>";
            $rowsHtml .= "<td class='px-3 py-2 font-mono font-bold text-xs text-slate-800'>{$inv}<div class='text-[10px] text-slate-400 font-normal'>{$jam} WIB - {$kasir}</div></td>";
            $rowsHtml .= "<td class='px-3 py-2 font-semibold text-xs text-slate-700'>{$prod}<div class='mt-0.5'>{$satBadge}</div></td>";
            $rowsHtml .= "<td class='px-3 py-2 text-center'><span class='px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-mono text-xs font-bold'>{$seharusnya}</span></td>";
            $rowsHtml .= "<td class='px-3 py-2 text-center'><span class='px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-mono text-xs font-bold'>{$diambil}</span></td>";
            $rowsHtml .= "<td class='px-3 py-2 text-center font-bold text-xs text-slate-800'>{$qtyDisplay}</td>";
            $rowsHtml .= "<td class='px-3 py-2 text-center text-xs'>{$statusBadge}</td>";
            $rowsHtml .= "</tr>";
        }
    }
    ?>

    const modalTableRows = <?= json_encode($rowsHtml) ?>;
    const modalHtml = `
    <div style="text-align:left; font-family: Inter, sans-serif;">
        <div style="margin-bottom:12px; font-size:12px; color:#64748b;">
            Daftar penukaran batch obat yang tercatat menyimpang dari urutan kedaluwarsa terdekat (FEFO) pada operasional kasir:
        </div>
        <div style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0; position:sticky; top:0; z-index:10;">
                    <tr>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">#</th>
                        <th style="padding:8px 10px; text-align:left; color:#64748b; font-size:11px;">Invoice / Waktu</th>
                        <th style="padding:8px 10px; text-align:left; color:#64748b; font-size:11px;">Nama Obat &amp; Satuan</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Batch Seharusnya (FEFO)</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Batch Diambil Kasir</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Qty</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Status Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody style="background:#ffffff;">
                    ${modalTableRows}
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px; padding:10px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; font-size:11px; color:#475569; line-height:1.4;">
            <strong>Catatan Standar SOP:</strong> Bila pelanggan menolak batch terdepan karena alasan kemasan atau permintaan khusus, kasir diperbolehkan melayani dengan batch lain, namun sistem otomatis mencatat anomali ini untuk laporan pengawasan inventori.
        </div>
    </div>`;

    Swal.fire({
        title: '<span style="font-size:16px; font-weight:800; color:#1e293b;">Log Pengawasan Anomali FEFO Hari Ini</span>',
        html: modalHtml,
        width: 820,
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Tutup Panel',
        confirmButtonColor: '#1a75d2'
    });
}'''

code = code[:match.start()] + new_js + code[match.end():]
print("Replaced JavaScript functions successfully!")

with open(pos_path, 'w', encoding='utf-8') as f:
    f.write(code)
print("Updated pos/pos.php successfully!")

