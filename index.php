<?php
// File: index.php - Dashboard Operasional ZenCare Medical (v3.0 - Minimalist)
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/layout.php';

requireRole(['superadmin', 'admin']);

$isSuperadmin = ($_SESSION['role'] === 'superadmin');
$namaUser     = $_SESSION['nama_lengkap'] ?? 'Pengguna';

// ─── WIDGET DATA BERSAMA (Admin & Superadmin) ─────────────────────────────
$totalStokFisik   = (int)$pdo->query("SELECT COALESCE(SUM(stok),0) FROM stok_toko")->fetchColumn();
$stokKritisCount  = (int)$pdo->query("SELECT COUNT(*) FROM stok_toko WHERE stok < 5")->fetchColumn();
$expWarningCount  = (int)$pdo->query("SELECT COUNT(*) FROM stok_batch WHERE stok_sisa > 0 AND tgl_exp <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
$pesananMenunggu  = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE status_pesanan IN ('Menunggu Pembayaran','Diproses')")->fetchColumn();
$transaksiHariIni = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE DATE(created_at)=CURDATE() AND status_pesanan NOT IN ('Dibatalkan')")->fetchColumn();

// ─── DATA SUPERADMIN (Semua berbasis Kuantitas/Unit — tanpa Rupiah) ────────
if ($isSuperadmin) {
    // Volume penjualan hari ini — terpisah per kanal (unit qty)
    $row = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN p.tipe_transaksi='pos'       THEN dp.qty ELSE 0 END),0) AS qty_pos,
            COALESCE(SUM(CASE WHEN p.tipe_transaksi='ecommerce' THEN dp.qty ELSE 0 END),0) AS qty_online
        FROM penjualan p
        JOIN detail_penjualan dp ON dp.id_penjualan = p.id
        WHERE DATE(p.created_at) = CURDATE()
          AND p.status_pesanan NOT IN ('Dibatalkan','Menunggu Pembayaran')
    ")->fetch();
    $volPosHariIni    = (int)($row['qty_pos']    ?? 0);
    $volOnlineHariIni = (int)($row['qty_online'] ?? 0);

    // Pergerakan inventaris bulan ini (unit masuk vs keluar dari kartu_stok)
    $mutasiBulan = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN jenis_mutasi='Masuk'  THEN qty ELSE 0 END),0) AS total_masuk,
            COALESCE(SUM(CASE WHEN jenis_mutasi='Keluar' THEN qty ELSE 0 END),0) AS total_keluar
        FROM kartu_stok
        WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())
    ")->fetch();

    // Total produk & stok menipis detail
    $totalProdukAktif = (int)$pdo->query("SELECT COUNT(*) FROM produk_variasi WHERE is_active=1")->fetchColumn();
    $totalSupplier    = (int)$pdo->query("SELECT COUNT(*) FROM supplier WHERE is_active=1")->fetchColumn();

    // Stok menipis detail
    $stokMenipis = $pdo->query("
        SELECT CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, sc.stok, pv.satuan_kecil
        FROM stok_toko sc
        JOIN produk_variasi pv ON sc.id_variasi=pv.id
        JOIN produk_induk pi ON pv.id_produk_induk=pi.id
        WHERE sc.stok < 5 AND pv.is_active=1
        ORDER BY sc.stok ASC LIMIT 10
    ")->fetchAll();

    // Top 5 produk terlaris bulan ini (berdasarkan qty unit terjual)
    $produkTerlaris = $pdo->query("
        SELECT CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
               SUM(dp.qty) AS total_qty, pi.kategori
        FROM detail_penjualan dp
        JOIN produk_variasi pv ON dp.id_variasi=pv.id
        JOIN produk_induk pi ON pv.id_produk_induk=pi.id
        JOIN penjualan p ON dp.id_penjualan=p.id
        WHERE MONTH(p.created_at)=MONTH(CURDATE()) AND YEAR(p.created_at)=YEAR(CURDATE())
          AND p.status_pesanan NOT IN ('Dibatalkan')
        GROUP BY dp.id_variasi ORDER BY total_qty DESC LIMIT 5
    ")->fetchAll();

    // Log audit mutasi terbaru (hanya mutasi manual)
    $auditMutasi = $pdo->query("
        SELECT ks.tanggal, ks.jenis_mutasi, ks.qty, ks.sisa_stok, ks.keterangan,
               CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
               u.nama_lengkap AS operator, ks.kanal, ks.alasan_mutasi
        FROM kartu_stok ks
        JOIN produk_variasi pv ON ks.id_variasi=pv.id
        JOIN produk_induk pi ON pv.id_produk_induk=pi.id
        LEFT JOIN users u ON ks.dibuat_oleh=u.id
        ORDER BY ks.id DESC LIMIT 10
    ")->fetchAll();

    // Data Grafik: Volume Penjualan 7 Hari Terakhir (POS vs E-Commerce)
    $grafikData = $pdo->query("
        SELECT 
            DATE(p.created_at) as tgl,
            COALESCE(SUM(CASE WHEN p.tipe_transaksi='pos' THEN dp.qty ELSE 0 END), 0) as qty_pos,
            COALESCE(SUM(CASE WHEN p.tipe_transaksi='ecommerce' THEN dp.qty ELSE 0 END), 0) as qty_online
        FROM penjualan p
        JOIN detail_penjualan dp ON dp.id_penjualan = p.id
        WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND p.status_pesanan NOT IN ('Dibatalkan','Menunggu Pembayaran')
        GROUP BY DATE(p.created_at)
        ORDER BY tgl ASC
    ")->fetchAll();

    // Fill missing dates
    $chartLabels = [];
    $chartDataPos = [];
    $chartDataOnline = [];
    for ($i = 6; $i >= 0; $i--) {
        $dateStr = date('Y-m-d', strtotime("-$i days"));
        $chartLabels[] = date('d M', strtotime($dateStr));
        $found = false;
        foreach ($grafikData as $row) {
            if ($row['tgl'] === $dateStr) {
                $chartDataPos[] = (int)$row['qty_pos'];
                $chartDataOnline[] = (int)$row['qty_online'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $chartDataPos[] = 0;
            $chartDataOnline[] = 0;
        }
    }
}

// ─── DATA ADMIN & SHARED ──────────────────────────────────────────────────
// Stok menipis (shared, needed for admin panel too)
$kartuTerbaru = $pdo->query("
    SELECT ks.tanggal, ks.jenis_mutasi, ks.qty, ks.sisa_stok,
           CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk, ks.keterangan
    FROM kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi=pv.id
    JOIN produk_induk pi ON pv.id_produk_induk=pi.id
    ORDER BY ks.id DESC LIMIT 8
")->fetchAll();

$pesananProses = $pdo->query("
    SELECT p.no_invoice, p.status_pesanan, p.tipe_transaksi, p.total_harga, p.created_at,
           u.nama_lengkap AS nama_pelanggan
    FROM penjualan p LEFT JOIN users u ON p.id_user=u.id
    WHERE p.status_pesanan IN ('Menunggu Pembayaran','Diproses')
    ORDER BY p.id DESC LIMIT 5
")->fetchAll();

$pesananPickup = $pdo->query("
    SELECT p.no_invoice, p.created_at, p.paid_at, u.nama_lengkap AS nama_pelanggan,
           DATEDIFF(CURDATE(), DATE(COALESCE(p.paid_at, p.created_at))) AS hari_tunggu
    FROM penjualan p
    LEFT JOIN users u ON p.id_user=u.id
    WHERE p.tipe_transaksi = 'ecommerce' 
      AND p.metode_pengambilan = 'Pick-up' 
      AND p.status_pesanan = 'Siap Diambil'
    ORDER BY hari_tunggu DESC
    LIMIT 5
")->fetchAll();

layoutHead('Dashboard');
layoutBodyOpen();
layoutSidebar('dashboard');
layoutHeader(
    $isSuperadmin ? 'Dashboard Manajerial Inventori' : 'Dashboard Operasional',
    $isSuperadmin ? 'Monitoring fisik persediaan & kinerja operasional — Pusat (Muharto).' : 'Ringkasan operasional kasir & inventori hari ini.'
);
?>
<!-- ============================================================ -->
<!-- ACTIONABLE ORDERS (Prioritas Utama) -->
<!-- ============================================================ -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
    <!-- Pesanan Menunggu Proses -->
    <div style="background:#fff;border:1px solid <?= !empty($pesananProses)?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;overflow:hidden;<?= !empty($pesananProses)?'box-shadow:0 4px 6px -1px rgba(59,130,246,0.1);':'' ?>">
        <div style="padding:13px 16px;border-bottom:1px solid <?= !empty($pesananProses)?'#bfdbfe':'#f1f5f9' ?>;background:<?= !empty($pesananProses)?'#eff6ff':'#fff' ?>;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:<?= !empty($pesananProses)?'#1e3a8a':'#1e293b' ?>;">Pesanan Menunggu Proses</div>
            <a href="admin/pesanan.php" style="font-size:11px;color:#1a75d2;text-decoration:none;font-weight:600;">Kelola Semua →</a>
        </div>
        <?php if (empty($pesananProses)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Antrean kosong ✓</div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">NO. INVOICE</th>
                    <th style="padding:7px 8px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">PELANGGAN</th>
                    <th style="padding:7px 8px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">KANAL</th>
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">STATUS</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pesananProses as $p):
                $sc = ['Menunggu Pembayaran'=>'background:#fef3c7;color:#92400e','Diproses'=>'background:#dbeafe;color:#1e40af'];
                $bgStyle = $sc[$p['status_pesanan']] ?? 'background:#f1f5f9;color:#334155';
            ?>
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:8px 16px;font-weight:600;color:#1e293b;font-family:monospace;"><?= htmlspecialchars($p['no_invoice']) ?></td>
                <td style="padding:8px;color:#334155;"><?= htmlspecialchars($p['nama_pelanggan'] ?? 'Walk-in') ?></td>
                <td style="padding:8px;color:#64748b;"><?= strtoupper($p['tipe_transaksi']) ?></td>
                <td style="padding:8px 16px;"><span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;<?= $bgStyle ?>"><?= $p['status_pesanan'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Pesanan Siap Diambil -->
    <div style="background:#fff;border:1px solid <?= !empty($pesananPickup)?'#fde047':'#e4e9f0' ?>;border-radius:10px;overflow:hidden;<?= !empty($pesananPickup)?'box-shadow:0 4px 6px -1px rgba(234,179,8,0.1);':'' ?>">
        <div style="padding:13px 16px;border-bottom:1px solid <?= !empty($pesananPickup)?'#fde047':'#f1f5f9' ?>;background:<?= !empty($pesananPickup)?'#fefce8':'#fff' ?>;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:<?= !empty($pesananPickup)?'#854d0e':'#1e293b' ?>;">Menunggu Pengambilan (Pick-up)</div>
            <a href="admin/pesanan.php" style="font-size:11px;color:#1a75d2;text-decoration:none;font-weight:600;">Kelola Semua →</a>
        </div>
        <?php if (empty($pesananPickup)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Tidak ada pesanan Pick-up yang menunggu diambil ✓</div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">NO. INVOICE</th>
                    <th style="padding:7px 8px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">PELANGGAN</th>
                    <th style="padding:7px 8px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">MULAI RESERVASI</th>
                    <th style="padding:7px 16px;text-align:right;font-size:10px;color:#64748b;font-weight:600;">LAMA MENUNGGU</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pesananPickup as $pk): ?>
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:8px 16px;font-weight:600;color:#1e293b;font-family:monospace;"><?= htmlspecialchars($pk['no_invoice']) ?></td>
                <td style="padding:8px;color:#334155;"><?= htmlspecialchars($pk['nama_pelanggan'] ?? 'Pelanggan') ?></td>
                <td style="padding:8px;color:#64748b;"><?= date('d/m/Y H:i', strtotime($pk['paid_at'] ?? $pk['created_at'])) ?></td>
                <td style="padding:8px 16px;text-align:right;">
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;<?= $pk['hari_tunggu'] > 3 ? 'background:#fee2e2;color:#991b1b' : 'background:#f1f5f9;color:#334155' ?>">
                        <?= $pk['hari_tunggu'] ?> Hari
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php if ($isSuperadmin): ?>
<!-- ============================================================ -->
<!-- SUPERADMIN DASHBOARD — Berbasis Kuantitas Fisik, tanpa Rupiah -->
<!-- ============================================================ -->

<!-- ROW 1: Widgets Inventori Utama (4 kotak) -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;">

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
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STOK MENIPIS (&lt; 5 UNIT)</div>
        <div style="font-size:30px;font-weight:900;color:<?= $stokKritisCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($stokKritisCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $stokKritisCount==0 ? 'Semua stok aman ✓' : 'SKU perlu restock' ?></div>
    </a>

    <!-- Hampir Kedaluwarsa -->
    <a href="laporan/logistik_medis.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $expWarningCount>0?'#fde68a':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">BATCH HAMPIR EXP.</div>
        <div style="font-size:30px;font-weight:900;color:<?= $expWarningCount>0?'#d97706':'#1e293b' ?>;line-height:1;"><?= number_format($expWarningCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Batch ≤ 30 hari</div>
    </a>

    <!-- Antrean Pesanan Daring -->
    <a href="admin/pesanan.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $pesananMenunggu>0?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANTREAN PESANAN DARING</div>
        <div style="font-size:30px;font-weight:900;color:<?= $pesananMenunggu>0?'#2563eb':'#1e293b' ?>;line-height:1;"><?= number_format($pesananMenunggu) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Status: Menunggu</div>
    </a>
</div>

<!-- ROW 2: Grafik Penjualan & Volume Hari Ini -->
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
</div>

<!-- Quick Actions -->
<div style="display:flex;gap:10px;margin-bottom:20px;">
    <a href="admin/pesanan.php" style="background:#1a75d2;color:#fff;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;display:flex;align-items:center;gap:6px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/></svg>
        Kelola Pesanan
    </a>
    <a href="laporan/ketersediaan_stok.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Laporan Stok</a>
    <a href="laporan/logistik_medis.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Logistik Medis</a>
    <a href="laporan/kartu_stok.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Kartu Stok</a>
    <a href="zencare_store.php" target="_blank" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Buka Store</a>
</div>

<!-- ROW 3: Stok Menipis + Log Audit Kartu Stok -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">

    <!-- Panel: Stok Menipis -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:6px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $stokKritisCount>0?'#dc2626':'#64748b' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Stok Menipis (&lt;5 Unit)
            </div>
            <span style="font-size:10px;color:#94a3b8;">Pusat (Muharto)</span>
        </div>
        <?php if (empty($stokMenipis)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Semua item stok aman (≥ 5 unit) ✓</div>
        <?php else: ?>
        <?php foreach ($stokMenipis as $s): ?>
        <div style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:12px;color:#334155;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($s['nama']) ?></span>
            <span style="font-size:12px;font-weight:700;color:<?= $s['stok']==0?'#dc2626':'#f59e0b' ?>;margin-left:12px;white-space:nowrap;"><?= $s['stok'] ?> <span style="font-weight:400;color:#94a3b8;"><?= htmlspecialchars($s['satuan_kecil']) ?></span></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Panel: Top 5 Produk Terlaris (qty/unit) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Top 5 Produk Terlaris Bulan Ini</div>
            <span style="font-size:10px;color:#94a3b8;">berdasarkan unit terjual</span>
        </div>
        <?php if (empty($produkTerlaris)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Belum ada data transaksi bulan ini.</div>
        <?php else: 
            $maxQty = max(array_column($produkTerlaris, 'total_qty')) ?: 1;
        ?>
        <?php foreach ($produkTerlaris as $i => $p): 
            $pct = round(($p['total_qty'] / $maxQty) * 100);
        ?>
        <div style="padding:10px 16px;border-bottom:1px solid #f8fafc;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                <div style="display:flex;align-items:center;gap:7px;flex:1;min-width:0;">
                    <span style="font-size:10px;font-weight:700;color:#94a3b8;width:14px;flex-shrink:0;"><?= $i+1 ?>.</span>
                    <span style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['nama_produk']) ?></span>
                </div>
                <span style="font-size:12px;font-weight:700;color:#1a75d2;white-space:nowrap;margin-left:10px;"><?= number_format($p['total_qty']) ?> unit</span>
            </div>
            <div style="height:4px;background:#f1f5f9;border-radius:4px;overflow:hidden;">
                <div style="height:4px;background:#1a75d2;border-radius:4px;width:<?= $pct ?>%;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ROW 4: Ringkasan Pergerakan Inventaris + Log Audit Mutasi -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;">

    <!-- Log Audit Mutasi & Kartu Stok Terbaru -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Log Audit Mutasi &amp; Kartu Stok Terbaru</div>
            <a href="laporan/kartu_stok.php" style="font-size:11px;color:#1a75d2;text-decoration:none;font-weight:600;">Lihat Semua →</a>
        </div>
        <?php if (empty($auditMutasi)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Belum ada aktivitas pergerakan stok.</div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">PRODUK</th>
                    <th style="padding:7px 8px;text-align:center;font-size:10px;color:#64748b;font-weight:600;">KANAL</th>
                    <th style="padding:7px 8px;text-align:right;font-size:10px;color:#64748b;font-weight:600;">QTY</th>
                    <th style="padding:7px 8px;text-align:right;font-size:10px;color:#64748b;font-weight:600;">SISA</th>
                    <th style="padding:7px 16px;text-align:right;font-size:10px;color:#64748b;font-weight:600;">WAKTU</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($auditMutasi as $a): ?>
            <tr style="border-top:1px solid #f1f5f9;hover:background:#f8fafc;">
                <td style="padding:8px 16px;">
                    <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px;"><?= htmlspecialchars($a['nama_produk']) ?></div>
                    <div style="font-size:10px;color:#94a3b8;"><?= htmlspecialchars($a['operator'] ?? 'Sistem') ?></div>
                </td>
                <td style="padding:8px;text-align:center;">
                    <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#64748b;"><?= htmlspecialchars($a['kanal'] ?? '-') ?></span>
                </td>
                <td style="padding:8px;text-align:right;font-weight:700;color:<?= $a['jenis_mutasi']==='Masuk'?'#16a34a':'#dc2626' ?>;">
                    <?= $a['jenis_mutasi']==='Masuk'?'+':'-' ?><?= number_format($a['qty']) ?>
                </td>
                <td style="padding:8px;text-align:right;color:#64748b;"><?= number_format($a['sisa_stok']) ?></td>
                <td style="padding:8px 16px;text-align:right;color:#94a3b8;font-size:10px;"><?= date('d/m H:i', strtotime($a['tanggal'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Ringkasan Pergerakan Inventaris Bulan Ini -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:18px;">
        <div style="font-size:12px;font-weight:700;color:#1e293b;margin-bottom:16px;">Pergerakan Inventaris Bulan Ini</div>

        <div style="padding-bottom:14px;margin-bottom:14px;border-bottom:1px solid #f1f5f9;">
            <div style="font-size:10px;color:#94a3b8;font-weight:600;margin-bottom:4px;letter-spacing:.03em;">TOTAL BARANG MASUK</div>
            <div style="font-size:26px;font-weight:900;color:#16a34a;line-height:1;">+<?= number_format($mutasiBulan['total_masuk'] ?? 0) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">unit diterima ke gudang</div>
        </div>

        <div style="padding-bottom:14px;margin-bottom:14px;border-bottom:1px solid #f1f5f9;">
            <div style="font-size:10px;color:#94a3b8;font-weight:600;margin-bottom:4px;letter-spacing:.03em;">TOTAL BARANG KELUAR</div>
            <div style="font-size:26px;font-weight:900;color:#dc2626;line-height:1;">-<?= number_format($mutasiBulan['total_keluar'] ?? 0) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">unit keluar dari gudang</div>
        </div>

        <div>
            <div style="font-size:10px;color:#94a3b8;font-weight:600;margin-bottom:4px;letter-spacing:.03em;">PERUBAHAN BERSIH STOK (UNIT)</div>
            <?php 
                $saldo = ($mutasiBulan['total_masuk'] ?? 0) - ($mutasiBulan['total_keluar'] ?? 0);
                $saldoColor = $saldo >= 0 ? '#16a34a' : '#dc2626';
            ?>
            <div style="font-size:26px;font-weight:900;color:<?= $saldoColor ?>;line-height:1;"><?= $saldo >= 0 ? '+' : '' ?><?= number_format($saldo) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">selisih barang masuk dikurangi barang keluar</div>
        </div>
    </div>
</div>


<!-- Script untuk Chart.js (Hanya Superadmin) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    {
                        label: 'Luring (POS)',
                        data: <?= json_encode($chartDataPos) ?>,
                        backgroundColor: '#1a75d2',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Daring (E-Commerce)',
                        data: <?= json_encode($chartDataOnline) ?>,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, usePointStyle: true, font: { size: 11, family: "'Inter', sans-serif" } }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' Unit';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10, family: "'Inter', sans-serif" } },
                        grid: { color: '#f1f5f9' },
                        title: { display: true, text: 'Kuantitas (Unit)', font: { size: 10, family: "'Inter', sans-serif" } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: "'Inter', sans-serif" } }
                    }
                }
            }
        });
    }
});
</script>

<?php else: ?>
<!-- ============================================================ -->
<!-- ADMIN DASHBOARD                                               -->
<!-- ============================================================ -->

<!-- Row 1: Metrics Admin -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">TOTAL STOK FISIK</div>
        <div style="font-size:30px;font-weight:900;color:#1e293b;line-height:1;"><?= number_format($totalStokFisik) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Unit tersedia</div>
    </div>
    <a href="laporan/ketersediaan_stok.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $stokKritisCount>0?'#fca5a5':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STOK MENIPIS</div>
        <div style="font-size:30px;font-weight:900;color:<?= $stokKritisCount>0?'#dc2626':'#1e293b' ?>;line-height:1;"><?= number_format($stokKritisCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $stokKritisCount==0?'Semua stok aman ✓':'SKU < 5 unit' ?></div>
    </a>
    <a href="laporan/logistik_medis.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $expWarningCount>0?'#fde68a':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">HAMPIR KEDALUWARSA</div>
        <div style="font-size:30px;font-weight:900;color:<?= $expWarningCount>0?'#d97706':'#1e293b' ?>;line-height:1;"><?= number_format($expWarningCount) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Batch ≤ 30 hari</div>
    </a>
    <a href="admin/pesanan.php" style="text-decoration:none;display:block;background:#fff;border:1px solid <?= $pesananMenunggu>0?'#bfdbfe':'#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">PESANAN BARU</div>
        <div style="font-size:30px;font-weight:900;color:<?= $pesananMenunggu>0?'#2563eb':'#1e293b' ?>;line-height:1;"><?= number_format($pesananMenunggu) ?></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Status: Menunggu</div>
    </a>
</div>

<!-- Quick Actions Admin -->
<div style="display:flex;gap:10px;margin-bottom:20px;">
    <a href="pos/pos.php" style="background:#1a75d2;color:#fff;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;display:flex;align-items:center;gap:6px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h0M2 9.5h20"/></svg>
        Buka Terminal POS
    </a>
    <a href="inventori/tambah_stok.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">+ Terima Stok Baru</a>
    <a href="admin/pesanan.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Kelola Pesanan</a>
    <a href="zencare_store.php" target="_blank" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Buka Store</a>
</div>

<!-- Row 2: Stok Menipis + Obat Kedaluwarsa + Audit Kartu Stok -->
<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;">
    <!-- Stok Menipis (Admin) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Stok Menipis (&lt;5 Unit)</div>
            <span style="font-size:10px;color:#94a3b8;">Pusat (Muharto)</span>
        </div>
        <?php
        $stokMenipis2 = $pdo->query("
            SELECT CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, sc.stok, pv.satuan_kecil
            FROM stok_toko sc JOIN produk_variasi pv ON sc.id_variasi=pv.id
            JOIN produk_induk pi ON pv.id_produk_induk=pi.id
            WHERE sc.stok < 5 AND pv.is_active=1 ORDER BY sc.stok ASC LIMIT 8
        ")->fetchAll();
        ?>
        <?php if (empty($stokMenipis2)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Semua item stok aman (≥ 5 unit) ✓</div>
        <?php else: ?>
        <?php foreach ($stokMenipis2 as $s): ?>
        <div style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:12px;color:#334155;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($s['nama']) ?></span>
            <span style="font-size:12px;font-weight:700;color:<?= $s['stok']==0?'#dc2626':'#f59e0b' ?>;margin-left:12px;"><?= $s['stok'] ?> <?= htmlspecialchars($s['satuan_kecil']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Obat Hampir Kedaluwarsa (Admin) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Obat Hampir Kedaluwarsa (≤ 30 Hari)</div>
            <span style="font-size:10px;color:#94a3b8;">Batch Aktif</span>
        </div>
        <?php
        $expList = $pdo->query("
            SELECT CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, sb.no_batch, sb.tgl_exp, sb.stok_sisa, pv.satuan_kecil
            FROM stok_batch sb
            JOIN produk_variasi pv ON sb.id_variasi = pv.id
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE sb.stok_sisa > 0 AND sb.tgl_exp <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY sb.tgl_exp ASC LIMIT 8
        ")->fetchAll();
        ?>
        <?php if (empty($expList)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Tidak ada obat yang mendekati masa kedaluwarsa.</div>
        <?php else: ?>
        <?php foreach ($expList as $e): ?>
        <div style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($e['nama']) ?></div>
                <div style="font-size:10px;color:#d97706;margin-top:1px;font-weight:600;">Batch: <?= htmlspecialchars($e['no_batch']) ?> (Exp: <?= date('d/m/Y', strtotime($e['tgl_exp'])) ?>)</div>
            </div>
            <span style="font-size:12px;font-weight:700;color:#dc2626;margin-left:12px;white-space:nowrap;">
                <?= $e['stok_sisa'] ?> <?= htmlspecialchars($e['satuan_kecil']) ?>
            </span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Audit Kartu Stok -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Audit Kartu Stok Terbaru</div>
            <a href="laporan/kartu_stok.php" style="font-size:11px;color:#1a75d2;text-decoration:none;font-weight:600;">Lihat Semua →</a>
        </div>
        <?php if (empty($kartuTerbaru)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Belum ada aktivitas stok.</div>
        <?php else: ?>
        <?php foreach ($kartuTerbaru as $k): ?>
        <div style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($k['nama_produk']) ?></div>
                <div style="font-size:10px;color:#94a3b8;margin-top:1px;"><?= date('d/m H:i', strtotime($k['tanggal'])) ?></div>
            </div>
            <span style="font-size:12px;font-weight:700;color:<?= $k['jenis_mutasi']==='Masuk'?'#16a34a':'#dc2626' ?>;margin-left:12px;white-space:nowrap;">
                <?= $k['jenis_mutasi']==='Masuk'?'+':'-' ?><?= number_format($k['qty']) ?>
            </span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>



<?php layoutFooter(); ?>