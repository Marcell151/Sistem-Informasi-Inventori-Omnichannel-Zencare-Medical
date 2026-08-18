<?php
// File: laporan/penjualan.php – Laporan Penjualan (Sales Report)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$dari   = $_GET['dari']    ?? date('Y-m-01');
$sampai = $_GET['sampai']  ?? date('Y-m-d');
$cabang = intval($_GET['id_cabang'] ?? 0);
$kanal  = $_GET['kanal']   ?? 'semua';  // semua / pos / online

$cabangList = $pdo->query("SELECT id, nama FROM cabang WHERE is_active=1 ORDER BY id")->fetchAll();

// Build filter
$cabangWhere = $cabang ? "AND sc.id_cabang = $cabang" : "";
$kanalWhere  = '';
if ($kanal === 'pos')    $kanalWhere = "AND t.tipe_transaksi = 'pos'";
if ($kanal === 'online') $kanalWhere = "AND t.tipe_transaksi = 'online'";

// Summary stats
$stmtSum = $pdo->prepare("
    SELECT
        COUNT(DISTINCT t.id)                      AS total_transaksi,
        COALESCE(SUM(t.total_harga),0)             AS total_omzet,
        COALESCE(SUM(CASE WHEN t.tipe_transaksi='pos' THEN t.total_harga ELSE 0 END),0) AS omzet_pos,
        COALESCE(SUM(CASE WHEN t.tipe_transaksi='online' THEN t.total_harga ELSE 0 END),0) AS omzet_online
    FROM penjualan t
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    $kanalWhere
");
try { $stmtSum->execute([$dari, $sampai]); $summary = $stmtSum->fetch(); }
catch(Exception $e) { $summary = ['total_transaksi'=>0,'total_omzet'=>0,'omzet_pos'=>0,'omzet_online'=>0]; }

// Top products
try {
    $topProd = $pdo->prepare("
        SELECT pi.nama_produk, pv.nama_variasi,
               SUM(td.qty) AS total_qty,
               SUM(td.qty * td.harga_satuan) AS total_rev
        FROM detail_penjualan td
        JOIN penjualan t ON t.id = td.id_penjualan
        JOIN produk_variasi pv ON pv.id = td.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        GROUP BY td.id_variasi
        ORDER BY total_rev DESC
        LIMIT 10
    ");
    $topProd->execute([$dari, $sampai]);
    $topProducts = $topProd->fetchAll();
} catch(Exception $e) { $topProducts = []; }

// Daily breakdown
try {
    $stmtDaily = $pdo->prepare("
        SELECT DATE(t.created_at) AS tgl,
               COUNT(*) AS jml,
               SUM(t.total_harga) AS omzet
        FROM penjualan t
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        GROUP BY DATE(t.created_at)
        ORDER BY tgl ASC
    ");
    $stmtDaily->execute([$dari, $sampai]);
    $daily = $stmtDaily->fetchAll();
} catch(Exception $e) { $daily = []; }

layoutHead('Laporan Penjualan');
layoutBodyOpen();
layoutSidebar('laporan_penjualan');
layoutHeader('Laporan Penjualan', 'Ringkasan penjualan POS & Online per periode');
?>

<style>
@media print {
  .no-print { display: none !important; }
  aside, header { display: none !important; }
}
</style>

<!-- Filter -->
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-6 no-print">
  <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Dari Tanggal</label>
      <input type="date" name="dari" value="<?= $dari ?>" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-gray-400">
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Sampai Tanggal</label>
      <input type="date" name="sampai" value="<?= $sampai ?>" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-gray-400">
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Cabang</label>
      <select name="id_cabang" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-gray-400">
        <option value="0">Semua Cabang</option>
        <?php foreach ($cabangList as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id']==$cabang?'selected':'' ?>><?= htmlspecialchars($c['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Kanal Penjualan</label>
      <select name="kanal" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-gray-400">
        <option value="semua" <?= $kanal==='semua'?'selected':'' ?>>Semua Kanal</option>
        <option value="pos" <?= $kanal==='pos'?'selected':'' ?>>POS Kasir</option>
        <option value="online" <?= $kanal==='online'?'selected':'' ?>>E-Commerce Online</option>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-xl transition">Tampilkan</button>
      <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">Cetak</button>
    </div>
  </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $cards = [
    ['label'=>'Total Transaksi', 'val'=>number_format($summary['total_transaksi']), 'unit'=>'transaksi', 'color'=>'bg-blue-50 border-blue-200 text-blue-700'],
    ['label'=>'Total Omzet', 'val'=>'Rp '.number_format($summary['total_omzet']), 'unit'=>'', 'color'=>'bg-emerald-50 border-emerald-200 text-emerald-700'],
    ['label'=>'Omzet POS', 'val'=>'Rp '.number_format($summary['omzet_pos']), 'unit'=>'kasir', 'color'=>'bg-violet-50 border-violet-200 text-violet-700'],
    ['label'=>'Omzet Online', 'val'=>'Rp '.number_format($summary['omzet_online']), 'unit'=>'e-commerce', 'color'=>'bg-amber-50 border-amber-200 text-amber-700'],
  ];
  foreach ($cards as $card): ?>
  <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5"><?= $card['label'] ?></p>
    <p class="text-xl font-bold text-gray-900 leading-tight"><?= $card['val'] ?></p>
    <?php if ($card['unit']): ?><p class="text-[10px] text-gray-500 mt-0.5"><?= $card['unit'] ?></p><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <!-- Top Products -->
  <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200"><h3 class="text-sm font-bold text-gray-900">Produk Terlaris</h3></div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead class="bg-slate-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-left">Produk</th>
            <th class="px-4 py-3 text-right">Qty</th>
            <th class="px-4 py-3 text-right">Revenue</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zcBrd/60">
          <?php if (empty($topProducts)): ?>
            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">Tidak ada data transaksi.</td></tr>
          <?php else: foreach ($topProducts as $i => $p): ?>
          <tr class="hover:bg-slate-50/60 transition">
            <td class="px-4 py-3 text-gray-500 font-mono"><?= $i+1 ?></td>
            <td class="px-4 py-3">
              <span class="font-semibold text-gray-900"><?= htmlspecialchars($p['nama_produk']) ?></span>
              <span class="text-gray-500 block text-[10px]"><?= htmlspecialchars($p['nama_variasi']) ?></span>
            </td>
            <td class="px-4 py-3 text-right font-semibold"><?= number_format($p['total_qty']) ?></td>
            <td class="px-4 py-3 text-right font-bold text-emerald-700">Rp <?= number_format($p['total_rev']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Daily Breakdown -->
  <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200"><h3 class="text-sm font-bold text-gray-900">Penjualan Per Hari</h3></div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead class="bg-slate-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-right">Transaksi</th>
            <th class="px-4 py-3 text-right">Omzet</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zcBrd/60">
          <?php if (empty($daily)): ?>
            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 italic">Tidak ada data.</td></tr>
          <?php else: foreach ($daily as $d): ?>
          <tr class="hover:bg-slate-50/60 transition">
            <td class="px-4 py-3 font-mono text-gray-900"><?= date('D, d M Y', strtotime($d['tgl'])) ?></td>
            <td class="px-4 py-3 text-right"><?= number_format($d['jml']) ?></td>
            <td class="px-4 py-3 text-right font-bold text-emerald-700">Rp <?= number_format($d['omzet']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php layoutEnd(); ?>

