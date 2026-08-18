<?php
// File: index.php – Dashboard Utama ZenCare Medical
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/layout.php';

requireRole(['super_admin', 'kasir']);
if (!isset($_SESSION['id_cabang'])) $_SESSION['id_cabang'] = 1;

// Branch selector
if (isset($_GET['cabang'])) {
    $_SESSION['id_cabang'] = intval($_GET['cabang']);
}
$activeCabang = $_SESSION['id_cabang'] ?? 1;

// Fetch metrics
try {
    $cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
    $cabangInfo = $pdo->query("SELECT * FROM cabang WHERE id = $activeCabang")->fetch();

    $totalStok = $pdo->prepare("SELECT COALESCE(SUM(stok),0) FROM stok_cabang WHERE id_cabang=?");
    $totalStok->execute([$activeCabang]);
    $totalStok = $totalStok->fetchColumn();

    $stokTipis = $pdo->prepare("SELECT COUNT(*) FROM stok_cabang WHERE id_cabang=? AND stok < 5");
    $stokTipis->execute([$activeCabang]);
    $stokTipis = $stokTipis->fetchColumn();

    $omsetQuery = $pdo->prepare("SELECT COALESCE(SUM(total_harga),0) FROM penjualan WHERE id_cabang=? AND DATE(created_at)=CURDATE() AND status_pesanan!='Dibatalkan'");
    $omsetQuery->execute([$activeCabang]);
    $omset = $omsetQuery->fetchColumn();

    $karantina = $pdo->prepare("SELECT COALESCE(SUM(qty),0) FROM gudang_karantina WHERE id_cabang=?");
    $karantina->execute([$activeCabang]);
    $karantina = $karantina->fetchColumn();

    $lowItems = $pdo->prepare("
        SELECT CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama, sc.stok
        FROM stok_cabang sc
        JOIN produk_variasi v ON sc.id_variasi=v.id
        JOIN produk_induk i ON v.id_produk_induk=i.id
        WHERE sc.id_cabang=? AND sc.stok<5
        ORDER BY sc.stok ASC LIMIT 5");
    $lowItems->execute([$activeCabang]);
    $lowItems = $lowItems->fetchAll();

    $auditLogs = $pdo->prepare("
        SELECT ks.*, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama
        FROM kartu_stok ks
        JOIN produk_variasi v ON ks.id_variasi=v.id
        JOIN produk_induk i ON v.id_produk_induk=i.id
        WHERE ks.id_cabang=?
        ORDER BY ks.tanggal DESC LIMIT 5");
    $auditLogs->execute([$activeCabang]);
    $auditLogs = $auditLogs->fetchAll();

    $stokProduk = $pdo->prepare("
        SELECT v.id, v.sku_variasi, i.nama_produk, v.nama_variasi, i.kategori, v.harga_jual_kecil AS harga,
               COALESCE(sc.stok,0) AS stok
        FROM produk_variasi v
        JOIN produk_induk i ON v.id_produk_induk=i.id
        LEFT JOIN stok_cabang sc ON sc.id_variasi=v.id AND sc.id_cabang=?
        WHERE v.is_active=1 AND i.is_active=1
        ORDER BY i.nama_produk ASC");
    $stokProduk->execute([$activeCabang]);
    $stokProduk = $stokProduk->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// API Statuses
$apiShopee = $pdo->prepare("SELECT is_active FROM pengaturan_api WHERE platform='shopee'");
$apiShopee->execute();
$shopeeOn = $apiShopee->fetchColumn();

layoutHead('Dashboard Inventaris');
layoutBodyOpen();
layoutSidebar('dashboard');
layoutHeader('Dashboard Inventaris & Omnichannel', 'Cabang: ' . ($cabangInfo['nama'] ?? '') . ' – Real-time Single Source of Truth');
?>

<!-- ============================================================ -->
<!-- METRIC CARDS ROW                                              -->
<!-- ============================================================ -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Card 1: Total Stok -->
    <div class="bg-white border border-zcBorder rounded-2xl p-5 flex items-start justify-between shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0"><?= icon('dashboard', 'w-5 h-5') ?></div>
        <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500ed block">Total Stok Fisik</span>
            <span class="text-3xl font-bold text-zcText mt-1 block"><?= number_format($totalStok) ?></span>
            <span class="text-[10px] text-gray-500ed">Unit tersedia</span>
        </div>
    </div>

    <!-- Card 2: Stok Menipis -->
    <div class="bg-white border border-zcBorder rounded-2xl p-5 flex items-start justify-between shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0"><?= icon('chart', 'w-5 h-5') ?></div>
        <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500ed block">Stok Menipis</span>
            <span class="text-3xl font-bold text-rose-600 mt-1 block"><?= $stokTipis ?></span>
            <?php if ($stokTipis > 0): ?>
                <span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 text-[10px] font-bold rounded-full"><?= $stokTipis ?> item kritis</span>
            <?php else: ?>
                <span class="text-[10px] text-emerald-600 font-semibold">Semua stok aman ✓</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card 3: Omset Hari Ini -->
    <div class="bg-white border border-zcBorder rounded-2xl p-5 flex items-start justify-between shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0"><?= icon('report', 'w-5 h-5') ?></div>
        <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500ed block">Omset Hari Ini</span>
            <span class="text-xl font-bold text-zcEm mt-1 block">Rp <?= number_format($omset, 0, ',', '.') ?></span>
            <span class="text-[10px] text-gray-500ed">POS + E-Commerce</span>
        </div>
    </div>

    <!-- Card 4: Karantina -->
    <div class="bg-white border border-zcBorder rounded-2xl p-5 flex items-start justify-between shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0"><?= icon('shield', 'w-5 h-5') ?></div>
        <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500ed block">Gudang Karantina</span>
            <span class="text-3xl font-bold text-amber-600 mt-1 block"><?= number_format($karantina) ?></span>
            <span class="text-[10px] text-gray-500ed">Unit diisolasi</span>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- API STATUS + QUICK ACTIONS BANNER                            -->
<!-- ============================================================ -->
<div class="flex flex-wrap gap-3 mb-6">
    <div class="flex items-center gap-2 px-4 py-2.5 bg-white border border-zcBorder rounded-xl text-xs shadow-sm">
        <span class="w-2 h-2 rounded-full <?= $shopeeOn ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
        <span class="font-semibold text-zcText">Shopee Sync API:</span>
        <span class="font-bold <?= $shopeeOn ? 'text-emerald-600' : 'text-slate-400' ?>"><?= $shopeeOn ? 'ACTIVE (ON)' : 'OFFLINE (OFF)' ?></span>
    </div>
    <div class="flex items-center gap-2 px-4 py-2.5 bg-white border border-zcBorder rounded-xl text-xs shadow-sm">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span class="font-semibold text-zcText">Midtrans Sandbox:</span>
        <span class="font-bold text-emerald-600">ACTIVE</span>
    </div>
    <div class="ml-auto flex gap-2">
        <a href="pos/pos.php" class="flex items-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold rounded-xl transition"><?= icon('pos', 'w-4 h-4') ?> Buka POS Kasir</a>
        <a href="zencare_store.php" class="flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-100 text-gray-800 text-xs font-semibold rounded-xl transition border border-gray-400/30"><?= icon('store', 'w-4 h-4') ?> Buka Store</a>
    </div>
</div>

<!-- ============================================================ -->
<!-- 2-COLUMN WIDGETS: LOW STOCK + AUDIT FEED                     -->
<!-- ============================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    <!-- Low Stock -->
    <div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zcBorder">
            <h3 class="text-sm font-bold text-zcText flex items-center gap-2"><?= icon('chart', 'w-4 h-4 text-rose-500') ?> Stok Menipis (&lt;5 Unit)</h3>
            <span class="text-[11px] text-gray-500ed"><?= htmlspecialchars($cabangInfo['nama'] ?? '') ?></span>
        </div>
        <div class="p-4 space-y-2.5">
            <?php if (empty($lowItems)): ?>
                <p class="text-xs text-gray-500ed italic text-center py-6">Semua item stok aman (≥ 5 unit) ✓</p>
            <?php else: ?>
                <?php foreach ($lowItems as $item): ?>
                    <div class="flex items-center justify-between p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs">
                        <span class="font-semibold text-zcText"><?= htmlspecialchars($item['nama']) ?></span>
                        <span class="px-2.5 py-1 bg-white text-rose-700 font-bold rounded-lg border border-rose-200">Sisa <?= $item['stok'] ?> Unit</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Audit Log Feed -->
    <div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zcBorder">
            <h3 class="text-sm font-bold text-zcText flex items-center gap-2"><?= icon('kartu_stok', 'w-4 h-4 text-amber-500') ?> Audit Kartu Stok Terbaru</h3>
            <a href="inventori/proses_mutasi.php" class="text-[11px] font-medium text-gray-800 hover:underline">Lihat Semua →</a>
        </div>
        <div class="p-4 space-y-2.5">
            <?php if (empty($auditLogs)): ?>
                <p class="text-xs text-gray-500ed italic text-center py-6">Belum ada riwayat perubahan stok.</p>
            <?php else: ?>
                <?php foreach ($auditLogs as $log): ?>
                    <div class="flex items-center justify-between p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs">
                        <div>
                            <div class="font-semibold text-zcText"><?= htmlspecialchars($log['nama']) ?></div>
                            <div class="text-[10px] text-gray-500ed mt-0.5"><?= htmlspecialchars($log['keterangan']) ?> &bull; <?= date('d/m H:i', strtotime($log['tanggal'])) ?></div>
                        </div>
                        <span class="px-2.5 py-1 bg-white text-zcText font-bold rounded-lg border border-amber-200 text-[11px]"><?= $log['jenis_mutasi'] ?>: <?= $log['qty'] ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TABEL INVENTARIS UTAMA                                        -->
<!-- ============================================================ -->
<div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-zcBorder">
        <div>
            <h3 class="text-sm font-bold text-zcText flex items-center gap-2"><?= icon('dashboard', 'w-4 h-4 text-gray-800') ?> Rincian Inventaris Operasional (Single Source of Truth)</h3>
            <p class="text-[11px] text-gray-500ed mt-0.5">Stok real-time tersinkronisasi POS & E-Commerce &bull; Cabang: <strong><?= htmlspecialchars($cabangInfo['nama'] ?? '') ?></strong></p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <input type="text" id="inv_search" onkeyup="filterInv()" placeholder="Cari SKU / Nama..."
                class="text-xs border border-zcBorder rounded-xl px-3 py-2 w-52 focus:outline-none focus:border-zcNavy bg-slate-50">
            <?php if (isAdmin()): ?>
                <a href="admin/master_produk.php" class="text-xs bg-gray-800 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded-xl transition">+ Produk Baru</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs" id="inv_table">
            <thead class="bg-slate-50 border-b border-zcBorder text-gray-500ed uppercase tracking-wider font-bold">
                <tr>
                    <th class="px-5 py-3 text-left">SKU Variasi</th>
                    <th class="px-5 py-3 text-left">Kategori</th>
                    <th class="px-5 py-3 text-left">Nama Produk / Variasi</th>
                    <th class="px-5 py-3 text-right">Harga Jual</th>
                    <th class="px-5 py-3 text-center">Stok</th>
                    <th class="px-5 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBorder/60">
                <?php if (empty($stokProduk)): ?>
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500ed italic">Belum ada produk di cabang ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($stokProduk as $p): ?>
                        <?php $stok = intval($p['stok']); $stokCls = $stok > 5 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($stok > 0 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200'); ?>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5 font-mono font-semibold text-gray-500ed"><?= htmlspecialchars($p['sku_variasi']) ?></td>
                            <td class="px-5 py-3.5"><span class="px-2 py-0.5 bg-slate-100 border border-zcBorder rounded-lg text-[10px] font-semibold"><?= htmlspecialchars($p['kategori']) ?></span></td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-zcText block"><?= htmlspecialchars($p['nama_produk']) ?></span>
                                <span class="text-gray-500ed"><?= htmlspecialchars($p['nama_variasi']) ?></span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                            <td class="px-5 py-3.5 text-center"><span class="px-2.5 py-1 rounded-full font-bold border text-[11px] <?= $stokCls ?>"><?= $stok ?> Unit</span></td>
                            <td class="px-5 py-3.5 text-center font-semibold <?= $stok > 0 ? 'text-emerald-600' : 'text-rose-500' ?>">
                                <?= $stok > 0 ? '✓ Tersedia' : '✕ Habis' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterInv() {
    const q = document.getElementById('inv_search').value.toLowerCase();
    document.querySelectorAll('#inv_table tbody tr').forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

<?php layoutEnd(); ?>
