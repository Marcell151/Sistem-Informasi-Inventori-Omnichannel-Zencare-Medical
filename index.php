<?php
// File: index.php - Dashboard Operasional ZenCare Medical (v3.0 - Minimalist)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/layout.php';

requireRole(['superadmin', 'admin']);

$isSuperadmin = ($_SESSION['role'] === 'superadmin');
$namaUser     = $_SESSION['nama_lengkap'] ?? 'Pengguna';

// ─── WIDGET DATA BERSAMA (Admin & Superadmin) ─────────────────────────────
$totalStokFisik   = (int)$pdo->query("SELECT COALESCE(SUM(stok),0) FROM stok_toko")->fetchColumn();
$stokKritisCount  = (int)$pdo->query("
    SELECT COUNT(*) FROM (
        SELECT pv.id FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id
        LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id
        WHERE pv.is_active=1
        GROUP BY pv.id, pi.kategori, pv.rasio_konversi, pv.stok_minimum_besar, pv.stok_minimum_kecil, sc.stok
        HAVING (pi.kategori != 'Alat Kesehatan' AND (COALESCE(SUM(CASE WHEN sb.no_batch NOT LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) / pv.rasio_konversi) <= pv.stok_minimum_besar)
            OR (CASE WHEN pi.kategori = 'Alat Kesehatan' THEN COALESCE(sc.stok, 0) ELSE COALESCE(SUM(CASE WHEN sb.no_batch LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) END) <= pv.stok_minimum_kecil
    ) as kritis
")->fetchColumn();
$expWarningCount  = (int)$pdo->query("
    SELECT COUNT(*) FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE sb.stok_sisa > 0 AND sb.tgl_exp <= DATE_ADD(CURDATE(), INTERVAL COALESCE(pi.batas_hari_expired, 30) DAY)
")->fetchColumn();
$pesananMenunggu  = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE status_pesanan IN ('Menunggu Pembayaran','Diproses')")->fetchColumn();
$transaksiHariIni = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE DATE(created_at)=CURDATE() AND status_pesanan NOT IN ('Dibatalkan')")->fetchColumn();

// ─── ANOMALI FEFO & MONITORING RETUR (Bimbingan Dosen Poin 2) ─────────────
$anomaliPendingCount = (int)$pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE status = 'terlewat'")->fetchColumn();
$anomaliSelesaiCount = (int)$pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE status = 'disesuaikan'")->fetchColumn();
$anomaliHariIniCount = (int)$pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE (DATE(created_at) = CURDATE() OR status = 'terlewat')")->fetchColumn();
$listAnomaliHariIni = $pdo->query("
    SELECT a.id, a.id_penjualan, a.batch_diambil, a.batch_seharusnya, a.qty, a.satuan_tipe, a.status, a.created_at, a.resolved_at,
           COALESCE(p.no_invoice, CONCAT('TRX-', a.id_penjualan)) AS no_invoice,
           p_res.no_invoice AS no_invoice_resolved,
           COALESCE(p.tipe_transaksi, 'pos') AS tipe_transaksi,
           CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
           pv.satuan_kecil, pv.satuan_besar,
           COALESCE(u.nama_lengkap, 'Kasir') AS operator
    FROM log_anomali_fefo a
    LEFT JOIN penjualan p ON a.id_penjualan = p.id
    LEFT JOIN penjualan p_res ON a.resolved_in_penjualan = p_res.id
    LEFT JOIN produk_variasi pv ON a.id_variasi = pv.id
    LEFT JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN users u ON a.id_user = u.id
    ORDER BY (a.status = 'terlewat') DESC,
             CASE WHEN a.status = 'terlewat' THEN a.created_at END DESC,
             COALESCE(a.resolved_at, a.created_at) DESC,
             a.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$returMenungguCount = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur = 'menunggu_pengganti'")->fetchColumn();
$returSelesaiCount  = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur = 'selesai_tukar_barang'")->fetchColumn();
$listReturMonitoring = $pdo->query("
    SELECT pd.id, pd.status_retur, pd.qty_rusak, pd.qty_pengganti_diterima, pd.no_batch, pd.no_batch_pengganti, pd.tgl_selesai_retur, pd.catatan_retur,
           ps.no_referensi, ps.tanggal_terima,
           COALESCE(s.nama, 'Supplier') AS nama_supplier,
           CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
           pv.satuan_besar, pv.satuan_kecil
    FROM penerimaan_detail pd
    JOIN penerimaan_stok ps ON pd.id_penerimaan = ps.id
    LEFT JOIN supplier s ON ps.id_supplier = s.id
    JOIN produk_variasi pv ON pd.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pd.status_retur IN ('menunggu_pengganti', 'selesai_tukar_barang')
    ORDER BY (pd.status_retur = 'menunggu_pengganti') DESC,
             CASE WHEN pd.status_retur = 'menunggu_pengganti' THEN ps.tanggal_terima END DESC,
             pd.tgl_selesai_retur DESC,
             pd.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

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

    // Pergerakan barang bulan ini (unit masuk vs keluar dari kartu_stok)
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

    // Stok menipis detail (ROP Logic Update: Pisahkan Eceran vs Dus via Batch, atau pakai stok_toko untuk Alkes)
    $stokMenipis = $pdo->query("
        SELECT pv.id AS id_variasi, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, 
               pv.satuan_kecil, pv.satuan_besar, pv.stok_minimum_kecil, pv.stok_minimum_besar, pv.rasio_konversi, pi.kategori,
               COALESCE(sc.stok, 0) AS stok_total,
               CASE WHEN pi.kategori = 'Alat Kesehatan' THEN FLOOR(COALESCE(sc.stok, 0) / pv.rasio_konversi) * pv.rasio_konversi ELSE COALESCE(SUM(CASE WHEN sb.no_batch NOT LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) END AS stok_pcs_dus,
               CASE WHEN pi.kategori = 'Alat Kesehatan' THEN COALESCE(sc.stok, 0) ELSE COALESCE(SUM(CASE WHEN sb.no_batch LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) END AS stok_pcs_eceran
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id
        LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id
        WHERE pv.is_active = 1
        GROUP BY pv.id, pi.kategori, pv.rasio_konversi, pv.stok_minimum_besar, pv.stok_minimum_kecil, sc.stok
        HAVING (pi.kategori != 'Alat Kesehatan' AND (stok_pcs_dus / pv.rasio_konversi) <= pv.stok_minimum_besar)
            OR stok_pcs_eceran <= pv.stok_minimum_kecil
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
          AND p.tipe_transaksi != 'Mutasi Konversi'
        GROUP BY dp.id_variasi ORDER BY total_qty DESC LIMIT 5
    ")->fetchAll();

    // Log audit mutasi terbaru (hanya mutasi manual)
    $auditMutasi = $pdo->query("
        SELECT ks.tanggal, ks.jenis_mutasi, ks.qty, ks.sisa_stok, ks.keterangan, ks.satuan_tipe,
               CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
               u.nama_lengkap AS operator, ks.kanal, ks.alasan_mutasi, pv.satuan_besar, pv.satuan_kecil
        FROM kartu_stok ks
        JOIN produk_variasi pv ON ks.id_variasi=pv.id
        JOIN produk_induk pi ON pv.id_produk_induk=pi.id
        LEFT JOIN users u ON ks.dibuat_oleh=u.id
        ORDER BY ks.id DESC LIMIT 100
    ")->fetchAll();

    // Riwayat Pengadaan & Beli
    $riwayatPengadaan = $pdo->query("
        SELECT ps.tanggal_terima, ps.no_referensi, ps.sumber, u.nama_lengkap AS penerima,
               CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
               pd.qty_terima, pd.harga_beli, pd.no_batch, pi.kategori
        FROM penerimaan_stok ps
        JOIN penerimaan_detail pd ON ps.id = pd.id_penerimaan
        JOIN produk_variasi pv ON pd.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN users u ON ps.dibuat_oleh = u.id
        ORDER BY ps.id DESC LIMIT 100
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
    SELECT ks.tanggal, ks.jenis_mutasi, ks.qty, ks.sisa_stok, ks.satuan_tipe, ks.alasan_mutasi,
           CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk, ks.keterangan, pv.satuan_besar, pv.satuan_kecil
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

    <!-- Widget Anomali FEFO (Superadmin - Desain Seragam dengan Retur) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliPendingCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANOMALI FEFO</div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliPendingCount > 0 ? '#d97706' : '#1e293b' ?>;line-height:1;"><?= number_format($anomaliPendingCount) ?> <span style="font-size:13px;font-weight:600;color:#94a3b8;">/ <?= number_format($anomaliSelesaiCount) ?> disesuaikan</span></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $anomaliPendingCount > 0 ? 'Menunggu dikeluarkan kasir' : 'Semua deviasi disesuaikan ✓' ?></div>
    </div>

    <!-- Widget Monitoring Retur Barang (Superadmin - Desain Seragam) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STATUS RETUR SUPPLIER</div>
        <div style="font-size:30px;font-weight:900;color:<?= $returMenungguCount > 0 ? '#d97706' : '#1e293b' ?>;line-height:1;"><?= number_format($returMenungguCount) ?> <span style="font-size:13px;font-weight:600;color:#94a3b8;">/ <?= number_format($returSelesaiCount) ?> selesai</span></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $returMenungguCount > 0 ? 'Menunggu ganti barang PO' : 'Semua retur terselesaikan ✓' ?></div>
    </div>
</div>

<!-- ROW 2: Rekap Kuantitas Penjualan (Non-Grafik) & Volume Hari Ini -->
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
    <a href="ecommerce/index.php" target="_blank" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Buka Store</a>
</div>

<!-- ROW 3: Stok Menipis + Log Kartu Stok -->
<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;margin-bottom:14px;">

    <!-- Panel: Stok Menipis -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:6px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $stokKritisCount>0?'#dc2626':'#64748b' ?>" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Peringatan Stok Menipis (ROP)
            </div>
            <span style="font-size:10px;color:#94a3b8;">Pusat (Muharto)</span>
        </div>
        <?php if (empty($stokMenipis)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Semua item stok aman (di atas batas minimum) ✓</div>
        <?php else: ?>
        <div id="rop-superadmin-list">
        <?php foreach ($stokMenipis as $index => $s): 
            $stokDusFisik = floor($s['stok_pcs_dus'] / $s['rasio_konversi']);
            $isEceranMenipis = $s['stok_pcs_eceran'] <= $s['stok_minimum_kecil'];
            $isDusMenipis = $s['kategori'] !== 'Alat Kesehatan' && $stokDusFisik <= $s['stok_minimum_besar'];
        ?>
        <div class="rop-superadmin-item" style="padding:10px 16px;border-bottom:1px solid #f8fafc;display:none;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;"><?= htmlspecialchars($s['nama']) ?></div>
                <div style="font-size:10px;margin-top:3px;display:flex;align-items:center;gap:6px;">
                    <span style="color:#94a3b8;">Sisa Eceran: <span style="font-weight:700;color:<?= $isEceranMenipis ? '#dc2626' : '#10b981' ?>;"><?= $s['stok_pcs_eceran'] ?></span> / <?= $s['stok_minimum_kecil'] ?> <?= htmlspecialchars($s['satuan_kecil']) ?></span>
                    <?php if($s['kategori'] !== 'Alat Kesehatan'): ?>
                    <span style="color:#cbd5e1;">|</span>
                    <span style="color:#94a3b8;">Sisa Grosir: <span style="font-weight:700;color:<?= $isDusMenipis ? '#dc2626' : '#10b981' ?>;"><?= $stokDusFisik ?></span> / <?= $s['stok_minimum_besar'] ?> <?= htmlspecialchars($s['satuan_besar']) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if($isEceranMenipis): ?>
                    <?php if($stokDusFisik > 0 && $s['kategori'] !== 'Alat Kesehatan'): ?>
                        <div style="margin-top:4px;font-size:10px;color:#ea580c;background:#fff7ed;border:1px solid #fed7aa;padding:3px 6px;border-radius:4px;display:inline-block;font-weight:600;">
                            ⚠ Stok Eceran Menipis - Lakukan Konversi (Buka Satuan Besar).
                        </div>
                    <?php else: ?>
                        <div style="margin-top:4px;font-size:10px;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:3px 6px;border-radius:4px;display:inline-block;font-weight:600;">
                            🚨 <?= $s['kategori'] === 'Alat Kesehatan' ? 'Stok Habis!' : 'Eceran & Grosir Habis!' ?> Segera Lakukan Penerimaan Barang.
                        </div>
                    <?php endif; ?>
                <?php elseif($isDusMenipis): ?>
                    <div style="margin-top:4px;font-size:10px;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:3px 6px;border-radius:4px;display:inline-block;font-weight:600;">
                        🚨 Stok Grosir Menipis - Lakukan Penerimaan Barang.
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($isEceranMenipis && $stokDusFisik > 0 && $s['kategori'] !== 'Alat Kesehatan'): ?>
                <a href="inventori/konversi_uom.php" style="font-size:10px;font-weight:700;color:#ea580c;text-decoration:none;background:#ffedd5;padding:5px 10px;border-radius:5px;white-space:nowrap;margin-left:10px;border:1px solid #fed7aa;">Konversi</a>
            <?php elseif($isDusMenipis || ($isEceranMenipis && ($stokDusFisik == 0 || $s['kategori'] === 'Alat Kesehatan'))): ?>
                <a href="inventori/tambah_stok.php" style="font-size:10px;font-weight:700;color:#b91c1c;text-decoration:none;background:#fee2e2;padding:5px 10px;border-radius:5px;white-space:nowrap;margin-left:10px;border:1px solid #fecaca;">Penerimaan Barang</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
        <!-- Paginator UI -->
        <div style="padding:10px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:10px; color:#64748b;">Menampilkan <span id="rop-sa-range">0-0</span> dari <span id="rop-sa-total"><?= count($stokMenipis) ?></span></div>
            <div style="display:flex; gap:5px;">
                <button onclick="prevRopSA()" style="padding:4px 10px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:11px; cursor:pointer; color:#334155;">&lt; Prev</button>
                <button onclick="nextRopSA()" style="padding:4px 10px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:11px; cursor:pointer; color:#334155;">Next &gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

<!-- Obat Hampir Kedaluwarsa (SuperAdmin) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Peringatan Kedaluwarsa</div>
            <span style="font-size:10px;color:#94a3b8;">Batch Aktif</span>
        </div>
        <?php
        $expList = $pdo->query("
            SELECT CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, sb.no_batch, sb.tgl_exp, sb.stok_sisa, pv.satuan_kecil, pi.batas_hari_expired,
                   DATEDIFF(sb.tgl_exp, CURDATE()) AS sisa_hari
            FROM stok_batch sb
            JOIN produk_variasi pv ON sb.id_variasi = pv.id
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE sb.stok_sisa > 0 AND sb.tgl_exp <= DATE_ADD(CURDATE(), INTERVAL COALESCE(pi.batas_hari_expired, 30) DAY)
            ORDER BY sb.tgl_exp ASC LIMIT 100
        ")->fetchAll();
        ?>
        <?php if (empty($expList)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Tidak ada obat yang mendekati masa kedaluwarsa.</div>
        <?php else: ?>
        <div id="exp-superadmin-list">
        <?php foreach ($expList as $index => $e): 
            $isExpired = $e['sisa_hari'] <= 0;
            $color = $isExpired ? '#dc2626' : '#d97706';
            $bg = $isExpired ? '#fee2e2' : '#fef3c7';
            $label = $isExpired ? 'EXPIRED (Sisa: ' . $e['sisa_hari'] . ' Hari)' : 'Sisa: ' . $e['sisa_hari'] . ' Hari';
        ?>
        <div class="exp-superadmin-item" style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:none;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($e['nama']) ?></div>
                <div style="font-size:10px;color:<?= $color ?>;margin-top:1px;font-weight:600;display:flex;align-items:center;gap:4px;">
                    <span style="background:<?= $bg ?>;padding:1px 4px;border-radius:3px;font-size:9px;"><?= $label ?></span> 
                    Batch: <?= htmlspecialchars($e['no_batch']) ?>
                </div>
            </div>
            <span style="font-size:12px;font-weight:700;color:<?= $color ?>;margin-left:12px;white-space:nowrap;">
                <?= $e['stok_sisa'] ?> <?= htmlspecialchars($e['satuan_kecil']) ?>
            </span>
        </div>
        <?php endforeach; ?>
        </div>
        <div style="padding:10px 16px;background:#f8fafc;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:10px;color:#64748b;">Menampilkan <span id="exp-sa-range">1-5</span></span>
            <div style="display:flex;gap:6px;">
                <button onclick="prevExpSA()" style="padding:4px 10px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:11px; cursor:pointer; color:#334155;">&lt; Prev</button>
                <button onclick="nextExpSA()" style="padding:4px 10px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:11px; cursor:pointer; color:#334155;">Next &gt;</button>
            </div>
        </div>
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
        <!-- Paginator UI Kartu Stok Admin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="ks-ad-range">0-0</span> dari <span id="ks-ad-total"><?= count($kartuTerbaru) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevKsAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextKsAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ROW 4: Ringkasan Pergerakan Inventaris + Log Mutasi -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px;">

    <!-- Log Mutasi & Kartu Stok Terbaru -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Riwayat Kartu Stok Terakhir</div>
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
            <?php foreach ($auditMutasi as $i => $a): 
                $satuanLabel = $a['satuan_tipe'] === 'besar' ? $a['satuan_besar'] : $a['satuan_kecil'];
            ?>
            <tr class="ks-superadmin-item" style="border-top:1px solid #f1f5f9;display:none;">
                <td style="padding:8px 16px;">
                    <div style="font-size:12px;color:#334155;font-weight:600;"><?= htmlspecialchars($a['nama_produk']) ?></div>
                    <div style="font-size:10px;color:#64748b;margin-top:2px;"><?= htmlspecialchars($a['alasan_mutasi']) ?> <?= $a['keterangan'] ? '&mdash; '.htmlspecialchars($a['keterangan']) : '' ?></div>
                    <div style="font-size:9px;color:#94a3b8;margin-top:2px;">Oleh: <?= htmlspecialchars($a['operator'] ?? 'Sistem') ?></div>
                </td>
                <td style="padding:8px;text-align:center;">
                    <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#64748b;"><?= htmlspecialchars($a['kanal'] ?? '-') ?></span>
                </td>
                <td style="padding:8px;text-align:right;font-weight:700;color:<?= $a['jenis_mutasi']==='Masuk'?'#16a34a':'#dc2626' ?>;">
                    <?= $a['jenis_mutasi']==='Masuk'?'+':'-' ?><?= number_format($a['qty']) ?> <span style="font-size:10px;font-weight:500;color:#64748b;"><?= $satuanLabel ?></span>
                </td>
                <td style="padding:8px;text-align:right;color:#64748b;"><?= number_format($a['sisa_stok']) ?></td>
                <td style="padding:8px 16px;text-align:right;color:#94a3b8;font-size:10px;"><?= date('d/m H:i', strtotime($a['tanggal'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Paginator UI Kartu Stok Superadmin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="ks-sa-range">0-0</span> dari <span id="ks-sa-total"><?= count($auditMutasi) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevKsSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextKsSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Ringkasan Pergerakan Barang Bulan Ini -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:18px;">
        <div style="font-size:12px;font-weight:700;color:#1e293b;margin-bottom:16px;">Pergerakan Barang Bulan Ini</div>

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

<!-- ROW 5: Riwayat Pengadaan & Beli -->
<div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;margin-bottom:20px;">
    <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:12px;font-weight:700;color:#1e293b;">Riwayat Pengadaan & Beli</div>
    </div>
    <?php if (empty($riwayatPengadaan)): ?>
    <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Belum ada aktivitas pengadaan barang.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:700px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">TANGGAL TERIMA</th>
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">PRODUK</th>
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">SUMBER / REF</th>
                    <th style="padding:7px 16px;text-align:right;font-size:10px;color:#64748b;font-weight:600;">QTY</th>
                    <th style="padding:7px 16px;text-align:right;font-size:10px;color:#64748b;font-weight:600;">HARGA BELI</th>
                    <th style="padding:7px 16px;text-align:left;font-size:10px;color:#64748b;font-weight:600;">IDENTITAS LOGISTIK</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($riwayatPengadaan as $i => $rp): ?>
            <tr class="po-superadmin-item" style="border-top:1px solid #f1f5f9;display:none;">
                <td style="padding:8px 16px;color:#64748b;font-size:11px;">
                    <?= date('d/m/Y H:i', strtotime($rp['tanggal_terima'])) ?><br>
                    <span style="font-size:9px;color:#94a3b8;">Oleh: <?= htmlspecialchars($rp['penerima'] ?? 'Sistem') ?></span>
                </td>
                <td style="padding:8px 16px;">
                    <div style="font-weight:600;color:#334155;"><?= htmlspecialchars($rp['nama_produk']) ?></div>
                </td>
                <td style="padding:8px 16px;color:#64748b;">
                    <?= htmlspecialchars($rp['sumber']) ?><br>
                    <span style="font-size:10px;font-weight:600;color:#1e293b;">Ref: <?= htmlspecialchars($rp['no_referensi'] ?: '-') ?></span>
                </td>
                <td style="padding:8px 16px;text-align:right;font-weight:700;color:#1e293b;">
                    <?= number_format($rp['qty_terima']) ?>
                </td>
                <td style="padding:8px 16px;text-align:right;">
                    <?php if ($rp['harga_beli'] > 0): ?>
                        <span style="font-weight:700;color:#dc2626;">Rp <?= number_format($rp['harga_beli'], 0, ',', '.') ?></span>
                    <?php else: ?>
                        <span style="font-size:10px;color:#94a3b8;font-style:italic;">Tidak diinput</span>
                    <?php endif; ?>
                </td>
                <td style="padding:8px 16px;font-size:10px;color:#64748b;">
                    <?php if ($rp['no_batch']): ?>
                        <span style="background:#fef3c7;color:#d97706;padding:2px 6px;border-radius:4px;font-weight:600;white-space:nowrap;">Batch: <?= htmlspecialchars($rp['no_batch']) ?></span>
                    <?php elseif ($rp['kategori'] === 'Alat Kesehatan'): ?>
                        <span style="background:#e0e7ff;color:#4338ca;padding:2px 6px;border-radius:4px;font-weight:600;display:inline-block;white-space:nowrap;">Ber-SN</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Paginator UI Riwayat Pengadaan Superadmin -->
    <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
        <div style="font-size:9px; color:#64748b;"><span id="po-sa-range">0-0</span> dari <span id="po-sa-total"><?= count($riwayatPengadaan) ?></span></div>
        <div style="display:flex; gap:3px;">
            <button type="button" onclick="prevPoSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
            <button type="button" onclick="nextPoSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php else: ?>
<!-- ============================================================ -->
<!-- ADMIN DASHBOARD                                               -->
<!-- ============================================================ -->

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

    <!-- Widget Anomali FEFO (Admin - Desain Seragam dengan Retur) -->
    <div onclick="openAnomaliModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $anomaliPendingCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">ANOMALI FEFO</div>
        <div style="font-size:30px;font-weight:900;color:<?= $anomaliPendingCount > 0 ? '#d97706' : '#1e293b' ?>;line-height:1;"><?= number_format($anomaliPendingCount) ?> <span style="font-size:13px;font-weight:600;color:#94a3b8;">/ <?= number_format($anomaliSelesaiCount) ?> disesuaikan</span></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $anomaliPendingCount > 0 ? 'Menunggu dikeluarkan kasir' : 'Semua deviasi disesuaikan ✓' ?></div>
    </div>

    <!-- Widget Monitoring Retur Barang (Admin - Desain Seragam) -->
    <div onclick="openReturModal()" style="cursor:pointer;background:#fff;border:1px solid <?= $returMenungguCount > 0 ? '#fed7aa' : '#e4e9f0' ?>;border-radius:10px;padding:16px 18px;">
        <div style="font-size:10px;color:#64748b;font-weight:600;margin-bottom:8px;letter-spacing:.04em;">STATUS RETUR SUPPLIER</div>
        <div style="font-size:30px;font-weight:900;color:<?= $returMenungguCount > 0 ? '#d97706' : '#1e293b' ?>;line-height:1;"><?= number_format($returMenungguCount) ?> <span style="font-size:13px;font-weight:600;color:#94a3b8;">/ <?= number_format($returSelesaiCount) ?> selesai</span></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:5px;"><?= $returMenungguCount > 0 ? 'Menunggu ganti barang PO' : 'Semua retur terselesaikan ✓' ?></div>
    </div>
</div>

<!-- Quick Actions Admin -->
<div style="display:flex;gap:10px;margin-bottom:20px;">
    <a href="inventori/tambah_stok.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">+ Terima Stok Baru</a>
    <a href="admin/pesanan.php" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Kelola Pesanan</a>
    <a href="ecommerce/index.php" target="_blank" style="background:#fff;color:#1e293b;text-decoration:none;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border:1px solid #e4e9f0;">Buka Store</a>
</div>

<!-- Row 2: Stok Menipis + Obat Kedaluwarsa + Log Kartu Stok -->
<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;">
    <!-- Stok Menipis (Admin) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Peringatan Stok Menipis (ROP)</div>
            <span style="font-size:10px;color:#94a3b8;">Pusat (Muharto)</span>
        </div>
        <?php
        $stokMenipis2 = $pdo->query("
            SELECT pv.id AS id_variasi, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, 
               pv.satuan_kecil, pv.satuan_besar, pv.stok_minimum_kecil, pv.stok_minimum_besar, pv.rasio_konversi, pi.kategori,
               COALESCE(sc.stok, 0) AS stok_total,
               CASE WHEN pi.kategori = 'Alat Kesehatan' THEN FLOOR(COALESCE(sc.stok, 0) / pv.rasio_konversi) * pv.rasio_konversi ELSE COALESCE(SUM(CASE WHEN sb.no_batch NOT LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) END AS stok_pcs_dus,
               CASE WHEN pi.kategori = 'Alat Kesehatan' THEN COALESCE(sc.stok, 0) ELSE COALESCE(SUM(CASE WHEN sb.no_batch LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) END AS stok_pcs_eceran
            FROM produk_variasi pv
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id
            LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id
            WHERE pv.is_active = 1
            GROUP BY pv.id, pi.kategori, pv.rasio_konversi, pv.stok_minimum_besar, pv.stok_minimum_kecil, sc.stok
            HAVING (pi.kategori != 'Alat Kesehatan' AND (stok_pcs_dus / pv.rasio_konversi) <= pv.stok_minimum_besar)
                OR stok_pcs_eceran <= pv.stok_minimum_kecil
            LIMIT 100
        ")->fetchAll();
        ?>
        <?php if (empty($stokMenipis2)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Semua item stok aman ✓</div>
        <?php else: ?>
        <div id="rop-admin-list">
        <?php foreach ($stokMenipis2 as $index => $s): 
            $stokDusFisik = floor($s['stok_pcs_dus'] / $s['rasio_konversi']);
            $isEceranMenipis = $s['stok_pcs_eceran'] <= $s['stok_minimum_kecil'];
            $isDusMenipis = $s['kategori'] !== 'Alat Kesehatan' && $stokDusFisik <= $s['stok_minimum_besar'];
        ?>
        <div class="rop-admin-item" style="padding:10px 16px;border-bottom:1px solid #f8fafc;display:none;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;"><?= htmlspecialchars($s['nama']) ?></div>
                <div style="font-size:10px;margin-top:3px;display:flex;align-items:center;gap:6px;">
                    <span style="color:#94a3b8;">Sisa Eceran: <span style="font-weight:700;color:<?= $isEceranMenipis ? '#dc2626' : '#10b981' ?>;"><?= $s['stok_pcs_eceran'] ?></span> / <?= $s['stok_minimum_kecil'] ?> <?= htmlspecialchars($s['satuan_kecil']) ?></span>
                    <?php if($s['kategori'] !== 'Alat Kesehatan'): ?>
                    <span style="color:#cbd5e1;">|</span>
                    <span style="color:#94a3b8;">Sisa Grosir: <span style="font-weight:700;color:<?= $isDusMenipis ? '#dc2626' : '#10b981' ?>;"><?= $stokDusFisik ?></span> / <?= $s['stok_minimum_besar'] ?> <?= htmlspecialchars($s['satuan_besar']) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if($isEceranMenipis): ?>
                    <?php if($stokDusFisik > 0 && $s['kategori'] !== 'Alat Kesehatan'): ?>
                        <div style="margin-top:4px;font-size:10px;color:#ea580c;background:#fff7ed;border:1px solid #fed7aa;padding:3px 6px;border-radius:4px;display:inline-block;font-weight:600;">
                            ⚠ Stok Eceran Menipis - Lakukan Konversi (Buka Satuan Besar).
                        </div>
                    <?php else: ?>
                        <div style="margin-top:4px;font-size:10px;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:3px 6px;border-radius:4px;display:inline-block;font-weight:600;">
                            🚨 <?= $s['kategori'] === 'Alat Kesehatan' ? 'Stok Habis / Menipis!' : 'Eceran & Grosir Habis!' ?> Segera Lakukan Penerimaan Barang.
                        </div>
                    <?php endif; ?>
                <?php elseif($isDusMenipis): ?>
                    <div style="margin-top:4px;font-size:10px;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:3px 6px;border-radius:4px;display:inline-block;font-weight:600;">
                        🚨 Stok Grosir Menipis - Lakukan Penerimaan Barang.
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($isEceranMenipis && $stokDusFisik > 0 && $s['kategori'] !== 'Alat Kesehatan'): ?>
                <a href="inventori/konversi_uom.php" style="font-size:10px;font-weight:700;color:#ea580c;text-decoration:none;background:#ffedd5;padding:5px 10px;border-radius:5px;white-space:nowrap;margin-left:10px;border:1px solid #fed7aa;">Konversi</a>
            <?php elseif($isDusMenipis || ($isEceranMenipis && ($stokDusFisik == 0 || $s['kategori'] === 'Alat Kesehatan'))): ?>
                <a href="inventori/tambah_stok.php" style="font-size:10px;font-weight:700;color:#b91c1c;text-decoration:none;background:#fee2e2;padding:5px 10px;border-radius:5px;white-space:nowrap;margin-left:10px;border:1px solid #fecaca;">Penerimaan Barang</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
        <!-- Paginator UI ROP Admin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="rop-ad-range">0-0</span> dari <span id="rop-ad-total"><?= count($stokMenipis2) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevRopAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextRopAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Obat Hampir Kedaluwarsa (Admin) -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Peringatan Kedaluwarsa</div>
            <span style="font-size:10px;color:#94a3b8;">Batch Aktif</span>
        </div>
        <?php
        $expList = $pdo->query("
            SELECT CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama, sb.no_batch, sb.tgl_exp, sb.stok_sisa, pv.satuan_kecil, pi.batas_hari_expired,
                   DATEDIFF(sb.tgl_exp, CURDATE()) AS sisa_hari
            FROM stok_batch sb
            JOIN produk_variasi pv ON sb.id_variasi = pv.id
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE sb.stok_sisa > 0 AND sb.tgl_exp <= DATE_ADD(CURDATE(), INTERVAL COALESCE(pi.batas_hari_expired, 30) DAY)
            ORDER BY sb.tgl_exp ASC LIMIT 100
        ")->fetchAll();
        ?>
        <?php if (empty($expList)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Tidak ada obat yang mendekati masa kedaluwarsa.</div>
        <?php else: ?>
        <div id="exp-admin-list">
        <?php foreach ($expList as $index => $e): 
            $isExpired = $e['sisa_hari'] <= 0;
            $color = $isExpired ? '#dc2626' : '#d97706';
            $bg = $isExpired ? '#fee2e2' : '#fef3c7';
            $label = $isExpired ? 'EXPIRED (Sisa: ' . $e['sisa_hari'] . ' Hari)' : 'Sisa: ' . $e['sisa_hari'] . ' Hari';
        ?>
        <div class="exp-admin-item" style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:none;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($e['nama']) ?></div>
                <div style="font-size:10px;color:<?= $color ?>;margin-top:1px;font-weight:600;display:flex;align-items:center;gap:4px;">
                    <span style="background:<?= $bg ?>;padding:1px 4px;border-radius:3px;font-size:9px;"><?= $label ?></span> 
                    Batch: <?= htmlspecialchars($e['no_batch']) ?>
                </div>
            </div>
            <span style="font-size:12px;font-weight:700;color:<?= $color ?>;margin-left:12px;white-space:nowrap;">
                <?= $e['stok_sisa'] ?> <?= htmlspecialchars($e['satuan_kecil']) ?>
            </span>
        </div>
        <?php endforeach; ?>
        </div>
        <!-- Paginator UI Exp Admin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="exp-ad-range">0-0</span> dari <span id="exp-ad-total"><?= count($expList) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevExpAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextExpAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Log Kartu Stok -->
    <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;font-weight:700;color:#1e293b;">Riwayat Mutasi Terbaru</div>
            <a href="laporan/kartu_stok.php" style="font-size:11px;color:#1a75d2;text-decoration:none;font-weight:600;">Lihat Semua →</a>
        </div>
        <?php if (empty($kartuTerbaru)): ?>
        <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:12px;">Belum ada aktivitas stok.</div>
        <?php else: ?>
        <?php foreach ($kartuTerbaru as $i => $k): 
            $satuanLabel = $k['satuan_tipe'] === 'besar' ? $k['satuan_besar'] : $k['satuan_kecil'];
        ?>
        <div class="ks-admin-item" style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:none;justify-content:space-between;align-items:center;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#334155;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($k['nama_produk']) ?></div>
                <div style="font-size:10px;color:#64748b;margin-top:2px;"><?= htmlspecialchars($k['alasan_mutasi']) ?> <?= $k['keterangan'] ? '&mdash; '.htmlspecialchars($k['keterangan']) : '' ?></div>
                <div style="font-size:9px;color:#94a3b8;margin-top:2px;"><?= date('d/m H:i', strtotime($k['tanggal'])) ?></div>
            </div>
            <span style="font-size:12px;font-weight:700;color:<?= $k['jenis_mutasi']==='Masuk'?'#16a34a':'#dc2626' ?>;margin-left:12px;white-space:nowrap;">
                <?= $k['jenis_mutasi']==='Masuk'?'+':'-' ?><?= number_format($k['qty']) ?> <span style="font-size:10px;font-weight:500;color:#64748b;"><?= $satuanLabel ?></span>
            </span>
        </div>
        <?php endforeach; ?>
        <!-- Paginator UI Kartu Stok Admin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="ks-ad-range">0-0</span> dari <span id="ks-ad-total"><?= count($kartuTerbaru) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevKsAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextKsAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<script>
// Paginator for SuperAdmin ROP

// Paginator for SuperAdmin Kartu Stok
let ksSaPage = 1;
const ksSaLimit = 5;
const ksSaItems = document.querySelectorAll(".ks-superadmin-item");
if(ksSaItems.length > 0) renderSaKs();

function renderSaKs() {
    let start = (ksSaPage - 1) * ksSaLimit;
    let end = start + ksSaLimit;
    ksSaItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = "table-row";
        else el.style.display = "none";
    });
    let endDisplay = end > ksSaItems.length ? ksSaItems.length : end;
    let startDisplay = ksSaItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById("ks-sa-range");
    if(rangeEl) rangeEl.innerText = startDisplay + "-" + endDisplay;
}
function nextKsSA() {
    if((ksSaPage * ksSaLimit) < ksSaItems.length) { ksSaPage++; renderSaKs(); }
}
function prevKsSA() {
    if(ksSaPage > 1) { ksSaPage--; renderSaKs(); }
}

// Paginator for SuperAdmin Riwayat Pengadaan
let poSaPage = 1;
const poSaLimit = 5;
const poSaItems = document.querySelectorAll(".po-superadmin-item");
if(poSaItems.length > 0) renderSaPo();

function renderSaPo() {
    let start = (poSaPage - 1) * poSaLimit;
    let end = start + poSaLimit;
    poSaItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = "table-row";
        else el.style.display = "none";
    });
    let endDisplay = end > poSaItems.length ? poSaItems.length : end;
    let startDisplay = poSaItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById("po-sa-range");
    if(rangeEl) rangeEl.innerText = startDisplay + "-" + endDisplay;
}
function nextPoSA() {
    if((poSaPage * poSaLimit) < poSaItems.length) { poSaPage++; renderSaPo(); }
}
function prevPoSA() {
    if(poSaPage > 1) { poSaPage--; renderSaPo(); }
}

// Paginator for Admin Kartu Stok
let ksAdPage = 1;
const ksAdLimit = 5;
const ksAdItems = document.querySelectorAll(".ks-admin-item");
if(ksAdItems.length > 0) renderAdKs();

function renderAdKs() {
    let start = (ksAdPage - 1) * ksAdLimit;
    let end = start + ksAdLimit;
    ksAdItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = "flex";
        else el.style.display = "none";
    });
    let endDisplay = end > ksAdItems.length ? ksAdItems.length : end;
    let startDisplay = ksAdItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById("ks-ad-range");
    if(rangeEl) rangeEl.innerText = startDisplay + "-" + endDisplay;
}
function nextKsAD() {
    if((ksAdPage * ksAdLimit) < ksAdItems.length) { ksAdPage++; renderAdKs(); }
}
function prevKsAD() {
    if(ksAdPage > 1) { ksAdPage--; renderAdKs(); }
}

let ropSaPage = 1;
const ropSaLimit = 5;
const saItems = document.querySelectorAll('.rop-superadmin-item');
if(saItems.length > 0) renderSaRop();

function renderSaRop() {
    let start = (ropSaPage - 1) * ropSaLimit;
    let end = start + ropSaLimit;
    
    saItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > saItems.length ? saItems.length : end;
    let startDisplay = saItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('rop-sa-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextRopSA() {
    if(ropSaPage * ropSaLimit < saItems.length) {
        ropSaPage++;
        renderSaRop();
    }
}
function prevRopSA() {
    if(ropSaPage > 1) {
        ropSaPage--;
        renderSaRop();
    }
}


// Paginator for SuperAdmin EXP
let expSaPage = 1;
const expSaLimit = 5;
const expSaItems = document.querySelectorAll('.exp-superadmin-item');
if(expSaItems.length > 0) renderExpSa();

function renderExpSa() {
    let start = (expSaPage - 1) * expSaLimit;
    let end = start + expSaLimit;
    
    expSaItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > expSaItems.length ? expSaItems.length : end;
    let startDisplay = expSaItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('exp-sa-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextExpSA() {
    if(expSaPage * expSaLimit < expSaItems.length) {
        expSaPage++;
        renderExpSa();
    }
}
function prevExpSA() {
    if(expSaPage > 1) {
        expSaPage--;
        renderExpSa();
    }
}

// Paginator for Admin ROP
let ropAdPage = 1;
const ropAdLimit = 5;
const adItems = document.querySelectorAll('.rop-admin-item');
if(adItems.length > 0) renderAdRop();

function renderAdRop() {
    let start = (ropAdPage - 1) * ropAdLimit;
    let end = start + ropAdLimit;
    
    adItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > adItems.length ? adItems.length : end;
    let startDisplay = adItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('rop-ad-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextRopAD() {
    if(ropAdPage * ropAdLimit < adItems.length) {
        ropAdPage++;
        renderAdRop();
    }
}
function prevRopAD() {
    if(ropAdPage > 1) {
        ropAdPage--;
        renderAdRop();
    }
}

// Paginator for Admin EXP
let expAdPage = 1;
const expAdLimit = 5;
const expAdItems = document.querySelectorAll('.exp-admin-item');
if(expAdItems.length > 0) renderExpAd();

function renderExpAd() {
    let start = (expAdPage - 1) * expAdLimit;
    let end = start + expAdLimit;
    
    expAdItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > expAdItems.length ? expAdItems.length : end;
    let startDisplay = expAdItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('exp-ad-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextExpAD() {
    if(expAdPage * expAdLimit < expAdItems.length) {
        expAdPage++;
        renderExpAd();
    }
}
function prevExpAD() {
    if(expAdPage > 1) {
        expAdPage--;
        renderExpAd();
    }
}

</script>

<!-- Modal Dialog Functions for Dashboard Widgets with Interactive Pagination -->
<script>
const dataAnomaliMaster = <?= json_encode($listAnomaliHariIni) ?> || [];
let anomaliModalPage = 1;
const anomaliModalPageSize = 5;

function renderAnomaliModalPage(page) {
    anomaliModalPage = page;
    const total = dataAnomaliMaster.length;
    const totalPages = Math.ceil(total / anomaliModalPageSize) || 1;
    if (anomaliModalPage < 1) anomaliModalPage = 1;
    if (anomaliModalPage > totalPages) anomaliModalPage = totalPages;

    const start = (anomaliModalPage - 1) * anomaliModalPageSize;
    const pageItems = dataAnomaliMaster.slice(start, start + anomaliModalPageSize);

    let rowsHtml = '';
    pageItems.forEach(an => {
        const isBesar = (an.satuan_tipe === 'besar');
        const satLabel = isBesar ? (an.satuan_besar || 'Box') : (an.satuan_kecil || 'Pcs');
        const satBadge = isBesar
            ? '<span style="font-size:9px;padding:2px 6px;border-radius:4px;background:#eef2ff;color:#4338ca;font-weight:600;display:inline-block;margin-top:2px;">Satuan Besar (Grosir)</span>'
            : '<span style="font-size:9px;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-weight:600;display:inline-block;margin-top:2px;">Satuan Kecil (Eceran)</span>';
        
        let statusBadge = '';
        if (an.status === 'disesuaikan') {
            statusBadge = `<span style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:2px 8px;border-radius:12px;font-weight:700;font-size:10px;white-space:nowrap;">✓ Sudah Disesuaikan</span><div style="font-size:9px;color:#64748b;font-family:monospace;margin-top:2px;">di ${an.no_invoice_resolved || '-'}</div>`;
        } else {
            statusBadge = `<span style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:2px 8px;border-radius:12px;font-weight:700;font-size:10px;white-space:nowrap;">⏳ Menunggu Dikeluarkan</span><div style="font-size:9px;color:#b45309;margin-top:2px;">Prioritaskan berikutnya</div>`;
        }

        rowsHtml += `
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;font-family:monospace;font-weight:700;color:#1e293b;">
                    ${an.no_invoice}
                    <div style="font-size:10px;color:#94a3b8;font-weight:normal;">${an.created_at.substring(5, 16)}</div>
                </td>
                <td style="padding:8px;text-align:left;color:#334155;font-weight:600;">
                    ${an.nama_produk}
                    <div>${satBadge}</div>
                </td>
                <td style="padding:8px;color:#1e40af;font-family:monospace;font-weight:700;background:#eff6ff;">${an.batch_seharusnya}</td>
                <td style="padding:8px;color:#991b1b;font-family:monospace;font-weight:700;background:#fef2f2;">${an.batch_diambil}</td>
                <td style="padding:8px;text-align:right;font-weight:700;color:#dc2626;">${Number(an.qty).toLocaleString('id-ID')} <span style="font-size:10px;color:#64748b;font-weight:normal;">${satLabel}</span></td>
                <td style="padding:8px;text-align:center;">${statusBadge}</td>
                <td style="padding:8px;color:#64748b;font-size:10px;">${an.operator}</td>
            </tr>
        `;
    });

    const tbody = document.getElementById('anomali_modal_tbody');
    if (tbody) tbody.innerHTML = rowsHtml;

    const pageInfo = document.getElementById('anomali_page_info');
    if (pageInfo) pageInfo.innerText = `Halaman ${anomaliModalPage} dari ${totalPages} (Total ${total} insiden)`;

    const btnPrev = document.getElementById('anomali_btn_prev');
    const btnNext = document.getElementById('anomali_btn_next');
    if (btnPrev) {
        btnPrev.disabled = (anomaliModalPage <= 1);
        btnPrev.style.opacity = (anomaliModalPage <= 1) ? '0.4' : '1';
        btnPrev.style.cursor = (anomaliModalPage <= 1) ? 'not-allowed' : 'pointer';
    }
    if (btnNext) {
        btnNext.disabled = (anomaliModalPage >= totalPages);
        btnNext.style.opacity = (anomaliModalPage >= totalPages) ? '0.4' : '1';
        btnNext.style.cursor = (anomaliModalPage >= totalPages) ? 'not-allowed' : 'pointer';
    }
}

window.navigateAnomaliModal = function(delta) {
    renderAnomaliModalPage(anomaliModalPage + delta);
};

function openAnomaliModal() {
    if (!dataAnomaliMaster || dataAnomaliMaster.length === 0) {
        Swal.fire({
            title: 'Kepatuhan FEFO Tertib ✓',
            html: '<div class="text-sm text-slate-600 mt-2">Tidak ada anomali atau penukaran batch fisik yang terjadi.<br><br><span class="text-xs text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 font-bold inline-block">Seluruh transaksi tertib mengikuti urutan First-Expired, First-Out (FEFO)</span></div>',
            icon: 'success',
            confirmButtonColor: '#1a75d2'
        });
        return;
    }

    Swal.fire({
        title: 'Rekap Pemantauan Anomali FEFO',
        html: `
            <div style="text-align:left;font-size:12px;margin-bottom:10px;color:#475569;">
                Daftar transaksi kasir di mana terjadi deviasi batch fisik vs rekomendasi FEFO sistem (Menampilkan yang belum selesai terlebih dahulu):
            </div>
            <div style="min-height:200px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #cbd5e1;">
                        <tr>
                            <th style="padding:7px;">Invoice / Waktu</th>
                            <th style="padding:7px;text-align:left;">Produk &amp; Satuan</th>
                            <th style="padding:7px;">Batch FEFO</th>
                            <th style="padding:7px;">Batch Diambil</th>
                            <th style="padding:7px;text-align:right;">Qty</th>
                            <th style="padding:7px;text-align:center;">Status Tindak Lanjut</th>
                            <th style="padding:7px;">Kasir</th>
                        </tr>
                    </thead>
                    <tbody id="anomali_modal_tbody"></tbody>
                </table>
            </div>

            <!-- Navigasi Pagination Modal Anomali -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;background:#f8fafc;padding:6px 12px;border-radius:8px;border:1px solid #e2e8f0;font-size:11px;color:#64748b;">
                <span id="anomali_page_info" style="font-weight:600;">Memuat...</span>
                <div style="display:flex;gap:6px;">
                    <button id="anomali_btn_prev" type="button" onclick="navigateAnomaliModal(-1)" style="padding:4px 10px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;font-weight:600;font-size:11px;color:#334155;">&larr; Sebelumnya</button>
                    <button id="anomali_btn_next" type="button" onclick="navigateAnomaliModal(1)" style="padding:4px 10px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;font-weight:600;font-size:11px;color:#334155;">Selanjutnya &rarr;</button>
                </div>
            </div>

            <div style="font-size:11px;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;padding:8px 12px;border-radius:8px;margin-top:12px;text-align:left;">
                <strong>Alur Rekonsiliasi FEFO:</strong> Deviasi yang terjadi berstatus <em>Menunggu Dikeluarkan</em> sampai batch yang terlewat tersebut berhasil dijual pada transaksi berikutnya, di mana sistem otomatis mengubah statusnya menjadi <em>✓ Sudah Disesuaikan</em>.
            </div>
        `,
        width: '850px',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#1a75d2',
        didOpen: () => {
            renderAnomaliModalPage(1);
        }
    });
}

const listReturMaster = <?= json_encode($listReturMonitoring) ?> || [];
let returModalPage = 1;
const returModalPageSize = 5;

function renderReturModalPage(page) {
    returModalPage = page;
    const total = listReturMaster.length;
    const totalPages = Math.ceil(total / returModalPageSize) || 1;
    if (returModalPage < 1) returModalPage = 1;
    if (returModalPage > totalPages) returModalPage = totalPages;

    const start = (returModalPage - 1) * returModalPageSize;
    const pageItems = listReturMaster.slice(start, start + returModalPageSize);

    let rowsHtml = '';
    pageItems.forEach(r => {
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

    const tbody = document.getElementById('retur_modal_tbody');
    if (tbody) tbody.innerHTML = rowsHtml;

    const pageInfo = document.getElementById('retur_page_info');
    if (pageInfo) pageInfo.innerText = `Halaman ${returModalPage} dari ${totalPages} (Total ${total} data retur)`;

    const btnPrev = document.getElementById('retur_btn_prev');
    const btnNext = document.getElementById('retur_btn_next');
    if (btnPrev) {
        btnPrev.disabled = (returModalPage <= 1);
        btnPrev.style.opacity = (returModalPage <= 1) ? '0.4' : '1';
        btnPrev.style.cursor = (returModalPage <= 1) ? 'not-allowed' : 'pointer';
    }
    if (btnNext) {
        btnNext.disabled = (returModalPage >= totalPages);
        btnNext.style.opacity = (returModalPage >= totalPages) ? '0.4' : '1';
        btnNext.style.cursor = (returModalPage >= totalPages) ? 'not-allowed' : 'pointer';
    }
}

window.navigateReturModal = function(delta) {
    renderReturModalPage(returModalPage + delta);
};

function openReturModal() {
    if (!listReturMaster || listReturMaster.length === 0) {
        Swal.fire({
            title: 'Monitoring Retur Bersih ✓',
            text: 'Tidak ada barang rusak penerimaan PO yang sedang menunggu penggantian.',
            icon: 'info',
            confirmButtonColor: '#1a75d2'
        });
        return;
    }

    Swal.fire({
        title: 'Monitoring Retur Barang (Penerimaan PO)',
        html: `
            <div style="text-align:left;font-size:12px;margin-bottom:10px;color:#475569;">
                Status tindak lanjut barang rusak/cacat yang dikembalikan ke supplier (Menampilkan yang menunggu penggantian terlebih dahulu):
            </div>
            <div style="min-height:180px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #cbd5e1;">
                        <tr>
                            <th style="padding:7px;">Ref. PO</th>
                            <th style="padding:7px;text-align:left;">Supplier</th>
                            <th style="padding:7px;text-align:left;">Produk</th>
                            <th style="padding:7px;text-align:right;">Qty Rusak</th>
                            <th style="padding:7px;">Status Penggantian</th>
                        </tr>
                    </thead>
                    <tbody id="retur_modal_tbody"></tbody>
                </table>
            </div>

            <!-- Navigasi Pagination Modal Retur -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;background:#f8fafc;padding:6px 12px;border-radius:8px;border:1px solid #e2e8f0;font-size:11px;color:#64748b;">
                <span id="retur_page_info" style="font-weight:600;">Memuat...</span>
                <div style="display:flex;gap:6px;">
                    <button id="retur_btn_prev" type="button" onclick="navigateReturModal(-1)" style="padding:4px 10px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;font-weight:600;font-size:11px;color:#334155;">&larr; Sebelumnya</button>
                    <button id="retur_btn_next" type="button" onclick="navigateReturModal(1)" style="padding:4px 10px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;font-weight:600;font-size:11px;color:#334155;">Selanjutnya &rarr;</button>
                </div>
            </div>
        `,
        width: '780px',
        showCancelButton: true,
        cancelButtonText: 'Tutup',
        confirmButtonText: 'Buka Halaman Penerimaan & Retur',
        confirmButtonColor: '#1a75d2',
        didOpen: () => {
            renderReturModalPage(1);
        }
    }).then(res => {
        if (res.isConfirmed) {
            window.location.href = 'inventori/tambah_stok.php';
        }
    });
}
</script>

<?php layoutFooter(); ?>