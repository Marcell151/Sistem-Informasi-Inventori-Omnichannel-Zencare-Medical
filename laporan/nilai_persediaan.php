<?php
// File: laporan/nilai_persediaan.php – Laporan Nilai Persediaan (Inventory Valuation)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$id_cabang = intval($_GET['id_cabang'] ?? 0);
$cabangList = $pdo->query("SELECT id, nama FROM cabang WHERE is_active=1 ORDER BY id")->fetchAll();
$cabangWhere = $id_cabang ? "AND sc.id_cabang = $id_cabang" : "";

try {
    $stmt = $pdo->prepare("
        SELECT
            pi.nama_produk,
            pi.kategori,
            pv.nama_variasi,
            pv.sku_variasi,
            pv.harga_jual_kecil AS harga_satuan,
            COALESCE(sc.stok, 0) AS stok,
            (pv.harga_jual_kecil * COALESCE(sc.stok, 0)) AS nilai_total,
            c.nama AS nama_cabang
        FROM produk_variasi pv
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN stok_cabang sc ON sc.id_variasi = pv.id $cabangWhere
        LEFT JOIN cabang c ON c.id = sc.id_cabang
        WHERE pi.is_active = 1
        ORDER BY pi.kategori, pi.nama_produk, pv.nama_variasi, c.nama
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll();
} catch(Exception $e) { $rows = []; }

$grandTotal = array_sum(array_column($rows, 'nilai_total'));
$totalUnit  = array_sum(array_column($rows, 'stok'));
$totalSKU   = count(array_unique(array_column($rows, 'sku_variasi')));

layoutHead('Nilai Persediaan');
layoutBodyOpen();
layoutSidebar('laporan_nilai');
layoutHeader('Nilai Persediaan', 'Total aset inventori berdasarkan harga jual per cabang');
?>

<style>
@media print {
  .no-print { display: none !important; }
  aside, header { display: none !important; }
}
</style>

<!-- Filter -->
<div class="bg-white border border-zcBrd rounded-2xl shadow-sm p-5 mb-6 no-print">
  <form method="GET" class="flex flex-wrap items-end gap-4">
    <div>
      <label class="block text-[11px] font-semibold text-zcMut mb-1.5">Filter Cabang</label>
      <select name="id_cabang" class="text-xs border border-zcBrd rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc">
        <option value="0">Semua Cabang</option>
        <?php foreach ($cabangList as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id']==$id_cabang?'selected':'' ?>><?= htmlspecialchars($c['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="px-5 py-2 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition">Tampilkan</button>
    <button type="button" onclick="window.print()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">Cetak / PDF</button>
  </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm">
    <p class="text-[10px] font-bold text-zcMut uppercase tracking-wider mb-1">Total Nilai Aset</p>
    <p class="text-xl font-bold text-zc">Rp <?= number_format($grandTotal) ?></p>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm">
    <p class="text-[10px] font-bold text-zcMut uppercase tracking-wider mb-1">Total Unit Stok</p>
    <p class="text-xl font-bold text-zcTxt"><?= number_format($totalUnit) ?> unit</p>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm">
    <p class="text-[10px] font-bold text-zcMut uppercase tracking-wider mb-1">Jumlah SKU</p>
    <p class="text-xl font-bold text-zcTxt"><?= $totalSKU ?> SKU</p>
  </div>
</div>

<!-- Inventory Table -->
<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
  <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between">
    <h3 class="text-sm font-bold text-zcTxt">Rincian Nilai Persediaan</h3>
    <span class="text-xs text-zcMut">Per hari ini: <?= date('d M Y') ?></span>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-xs">
      <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider">
        <tr>
          <th class="px-4 py-3 text-left">Produk / Variasi</th>
          <th class="px-4 py-3 text-left">Kategori</th>
          <th class="px-4 py-3 text-left">Cabang</th>
          <th class="px-4 py-3 text-left font-mono">SKU</th>
          <th class="px-4 py-3 text-right">Harga Satuan</th>
          <th class="px-4 py-3 text-right">Stok</th>
          <th class="px-4 py-3 text-right">Nilai</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-zcBrd/60">
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" class="px-4 py-8 text-center text-zcMut italic">Tidak ada data persediaan.</td></tr>
        <?php else: foreach ($rows as $r):
          $lowStock = intval($r['stok']) <= 5;
        ?>
        <tr class="hover:bg-slate-50/60 transition <?= $lowStock ? 'bg-rose-50/30' : '' ?>">
          <td class="px-4 py-3">
            <span class="font-semibold text-zcTxt"><?= htmlspecialchars($r['nama_produk']) ?></span>
            <span class="text-zcMut block text-[10px]"><?= htmlspecialchars($r['nama_variasi']) ?></span>
          </td>
          <td class="px-4 py-3"><span class="px-2 py-0.5 bg-slate-100 rounded-lg text-zcMut"><?= htmlspecialchars($r['kategori']) ?></span></td>
          <td class="px-4 py-3 text-zcMut"><?= htmlspecialchars($r['nama_cabang'] ?? 'N/A') ?></td>
          <td class="px-4 py-3 font-mono text-zcMut"><?= htmlspecialchars($r['sku_variasi']) ?></td>
          <td class="px-4 py-3 text-right">Rp <?= number_format($r['harga_satuan']) ?></td>
          <td class="px-4 py-3 text-right font-semibold <?= $lowStock ? 'text-rose-600' : 'text-zcTxt' ?>"><?= number_format($r['stok']) ?> <?= $lowStock ? '<span class="text-[9px] bg-rose-100 text-rose-600 px-1 rounded">LOW</span>' : '' ?></td>
          <td class="px-4 py-3 text-right font-bold text-emerald-700">Rp <?= number_format($r['nilai_total']) ?></td>
        </tr>
        <?php endforeach; endif; ?>
        <!-- Grand Total -->
        <tr class="bg-zc/5 border-t-2 border-zcBrd font-bold">
          <td colspan="5" class="px-4 py-4 text-zcTxt font-bold">TOTAL NILAI PERSEDIAAN</td>
          <td class="px-4 py-4 text-right text-zcTxt"><?= number_format($totalUnit) ?></td>
          <td class="px-4 py-4 text-right text-zc font-bold text-sm">Rp <?= number_format($grandTotal) ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?php layoutEnd(); ?>
