<?php
// File: laporan/penjualan.php - Laporan Volume Penjualan (Kuantitas Fisik Terjual)
// Tidak memuat Harga/Omzet/HPP. Hanya melacak volume barang keluar melalui Omnichannel.
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$dari   = $_GET['dari']    ?? date('Y-m-01');
$sampai = $_GET['sampai']  ?? date('Y-m-d');
$kanal  = $_GET['kanal']   ?? 'semua';  // semua / pos / online

$kanalWhere = '';
if ($kanal === 'pos')    $kanalWhere = "AND pj.tipe_transaksi = 'pos'";
if ($kanal === 'ecommerce') $kanalWhere = "AND pj.tipe_transaksi = 'ecommerce'";

// Summary stats
$stmtSum = $pdo->prepare("
    SELECT
        COUNT(DISTINCT pj.id) AS total_transaksi,
        COALESCE(SUM(dp.qty), 0) AS total_item_terjual,
        COALESCE(SUM(CASE WHEN pj.tipe_transaksi='pos' THEN dp.qty ELSE 0 END), 0) AS item_pos,
        COALESCE(SUM(CASE WHEN pj.tipe_transaksi='ecommerce' THEN dp.qty ELSE 0 END), 0) AS item_online
    FROM penjualan pj
    JOIN detail_penjualan dp ON pj.id = dp.id_penjualan
    WHERE DATE(pj.created_at) BETWEEN ? AND ?
    $kanalWhere
");
try { $stmtSum->execute([$dari, $sampai]); $summary = $stmtSum->fetch(); }
catch(Exception $e) { $summary = ['total_transaksi'=>0,'total_item_terjual'=>0,'item_pos'=>0,'item_online'=>0]; }

// Daftar produk terjual
try {
    $stmtProd = $pdo->prepare("
        SELECT
            pi.nama_produk, pv.nama_variasi, pi.kategori, pv.sku_variasi,
            SUM(dp.qty) as total_qty,
            SUM(CASE WHEN pj.tipe_transaksi='pos' THEN dp.qty ELSE 0 END) as qty_pos,
            SUM(CASE WHEN pj.tipe_transaksi='ecommerce' THEN dp.qty ELSE 0 END) as qty_online
        FROM penjualan pj
        JOIN detail_penjualan dp ON pj.id = dp.id_penjualan
        JOIN produk_variasi pv ON dp.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE DATE(pj.created_at) BETWEEN ? AND ?
        $kanalWhere
        GROUP BY pv.id
        ORDER BY total_qty DESC
    ");
    $stmtProd->execute([$dari, $sampai]);
    $produkTerjual = $stmtProd->fetchAll();
} catch(Exception $e) { $produkTerjual = []; }

layoutHead('Laporan Penjualan Barang');
layoutBodyOpen();
layoutSidebar('laporan_penjualan');
layoutHeader('Laporan Penjualan Barang', 'Rekapitulasi kuantitas fisik barang yang terjual (Omnichannel) tanpa data nilai finansial.');
?>

<div class="mb-5 p-4 rounded-xl border bg-blue-50 border-blue-200 text-blue-800 text-xs flex gap-3 items-start">
    <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
    <div>
        <strong>Mode Inventori Ketat Aktif</strong>: Laporan ini hanya menampilkan <strong>kuantitas fisik barang keluar</strong> dari transaksi POS dan E-Commerce. Nilai finansial (Omzet, HPP, Margin) telah disembunyikan sesuai batasan sistem Manajemen Fisik Persediaan.
    </div>
</div>

<form method="GET" class="flex flex-wrap items-end gap-3 mb-6 p-5 bg-white border border-zcBrd rounded-2xl shadow-sm">
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Dari Tanggal</label>
        <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sampai Tanggal</label>
        <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kanal Penjualan</label>
        <select name="kanal" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[150px]">
            <option value="semua" <?= $kanal==='semua'?'selected':'' ?>>Semua Kanal</option>
            <option value="pos" <?= $kanal==='pos'?'selected':'' ?>>POS (Kasir Luring)</option>
            <option value="ecommerce" <?= $kanal==='ecommerce'?'selected':'' ?>>Online (E-Commerce)</option>
        </select>
    </div>
    <div class="flex-1 flex justify-end gap-2 print:hidden">
        <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition">
            Tampilkan Data
        </button>
        <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition border border-slate-300 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Laporan
        </button>
    </div>
</form>

<style>
@media print {
    .print\:hidden { display: none !important; }
    aside, header { display: none !important; }
    body { background: white; color: black; }
    .bg-white { box-shadow: none !important; border: none !important; }
    @page { size: portrait; margin: 10mm; }
}
</style>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Total Transaksi</div>
        <div class="text-2xl font-extrabold text-zcTxt"><?= number_format($summary['total_transaksi']??0) ?></div>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Total Item Terjual (Kuantitas)</div>
        <div class="text-2xl font-extrabold text-blue-600"><?= number_format($summary['total_item_terjual']??0) ?> <span class="text-sm font-medium">unit</span></div>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Item Terjual Via POS</div>
        <div class="text-2xl font-extrabold text-emerald-600"><?= number_format($summary['item_pos']??0) ?> <span class="text-sm font-medium">unit</span></div>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Item Terjual Via Online</div>
        <div class="text-2xl font-extrabold text-purple-600"><?= number_format($summary['item_online']??0) ?> <span class="text-sm font-medium">unit</span></div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-zcBrd">
        <h3 class="text-sm font-bold text-zcTxt">Rincian Volume per Produk</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b border-zcBrd">
                <tr>
                    <th class="px-4 py-3 text-left font-bold text-zcMut">Produk</th>
                    <th class="px-4 py-3 text-left font-bold text-zcMut">Kategori</th>
                    <th class="px-4 py-3 text-right font-bold text-zcMut text-blue-600">Total Kuantitas</th>
                    <th class="px-4 py-3 text-right font-bold text-zcMut text-emerald-600">Qty Luring (POS)</th>
                    <th class="px-4 py-3 text-right font-bold text-zcMut text-purple-600">Qty Daring (Online)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd">
                <?php if (empty($produkTerjual)): ?>
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-zcMut italic">Tidak ada pergerakan barang terjual pada periode ini.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($produkTerjual as $p): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-bold text-zcTxt"><?= htmlspecialchars($p['nama_produk']) ?></div>
                            <div class="text-xs text-zcMut"><?= htmlspecialchars($p['nama_variasi']) ?> (<?= htmlspecialchars($p['sku_variasi']) ?>)</div>
                        </td>
                        <td class="px-4 py-3 text-zcMut"><?= htmlspecialchars($p['kategori']) ?></td>
                        <td class="px-4 py-3 text-right font-extrabold text-blue-600 bg-blue-50/30"><?= number_format($p['total_qty']) ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600"><?= number_format($p['qty_pos']) ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-purple-600"><?= number_format($p['qty_online']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let dari = document.querySelector('input[name="dari"]');
    let sampai = document.querySelector('input[name="sampai"]');
    
    function validateDates() {
        sampai.min = dari.value;
        dari.max = sampai.value;
    }

    dari.addEventListener('change', function() {
        if (sampai.value && sampai.value < dari.value) sampai.value = dari.value;
        validateDates();
    });
    
    sampai.addEventListener('change', function() {
        if (dari.value && sampai.value < dari.value) dari.value = sampai.value;
        validateDates();
    });
    
    validateDates();
});
</script>

<?php layoutFooter(); ?>
