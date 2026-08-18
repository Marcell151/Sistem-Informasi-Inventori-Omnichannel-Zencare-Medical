<?php
// File: laporan/kartu_stok.php – Kartu Stok (Stock Ledger) Report
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$id_variasi = intval($_GET['id_variasi'] ?? 0);
$id_cabang  = intval($_GET['id_cabang']  ?? ($_SESSION['id_cabang'] ?? 0));
$dari       = $_GET['dari'] ?? date('Y-m-01');
$sampai     = $_GET['sampai'] ?? date('Y-m-d');

$cabangList  = $pdo->query("SELECT id, nama FROM cabang WHERE is_active=1 ORDER BY id")->fetchAll();
$variasiList = $pdo->query("SELECT pv.id, CONCAT(pi.nama_produk,' - ',pv.nama_variasi) AS label FROM produk_variasi pv JOIN produk_induk pi ON pi.id=pv.id_produk_induk WHERE pi.is_active=1 ORDER BY pi.nama_produk, pv.nama_variasi")->fetchAll();

$movements = [];
$stok_awal = 0;
$namaVariasi = '';

if ($id_variasi && $id_cabang) {
    // Stok awal = stok fisik saat ini di stok_cabang
    $stmtAwal = $pdo->prepare("SELECT COALESCE(stok,0) FROM stok_cabang WHERE id_variasi=? AND id_cabang=?");
    $stmtAwal->execute([$id_variasi, $id_cabang]);
    $stok_awal_fisik = intval($stmtAwal->fetchColumn());

    // Kartu stok dari tabel kartu_stok (no id_user column)
    $stmt = $pdo->prepare("
        SELECT ks.*
        FROM kartu_stok ks
        WHERE ks.id_variasi=? AND ks.id_cabang=? AND DATE(ks.tanggal) BETWEEN ? AND ?
        ORDER BY ks.tanggal ASC
    ");
    $stmt->execute([$id_variasi, $id_cabang, $dari, $sampai]);
    $movements = $stmt->fetchAll();

    // Hitung stok awal periode mundur dari stok fisik sekarang
    $totMasukPeriode  = array_sum(array_map(fn($m) => $m['jenis_mutasi']==='Masuk' ? intval($m['qty']) : 0, $movements));
    $totKeluarPeriode = array_sum(array_map(fn($m) => in_array($m['jenis_mutasi'], ['Keluar','Pengembalian/Batal','Karantina']) ? intval($m['qty']) : 0, $movements));
    $stok_awal = $stok_awal_fisik - ($totMasukPeriode - $totKeluarPeriode);

    foreach ($variasiList as $v) {
        if ($v['id'] == $id_variasi) { $namaVariasi = $v['label']; break; }
    }
}


layoutHead('Kartu Stok');
layoutBodyOpen();
layoutSidebar('laporan_kartu_stok');
layoutHeader('Kartu Stok', 'Buku besar pergerakan stok per produk & cabang');
?>

<style>
@media print {
  .no-print { display: none !important; }
  aside, header { display: none !important; }
  body { background: white; }
}
</style>

<!-- Filter Panel -->
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-6 no-print">
  <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Produk Variasi</label>
      <select name="id_variasi" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-gray-400">
        <option value="">-- Pilih Produk --</option>
        <?php foreach ($variasiList as $v): ?>
          <option value="<?= $v['id'] ?>" <?= $v['id']==$id_variasi?'selected':'' ?>><?= htmlspecialchars($v['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Cabang</label>
      <select name="id_cabang" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-gray-400">
        <option value="">-- Pilih Cabang --</option>
        <?php foreach ($cabangList as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id']==$id_cabang?'selected':'' ?>><?= htmlspecialchars($c['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Dari Tanggal</label>
      <input type="date" name="dari" value="<?= $dari ?>" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-gray-400">
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-gray-500 mb-1.5">Sampai Tanggal</label>
      <input type="date" name="sampai" value="<?= $sampai ?>" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-gray-400">
    </div>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-xl transition">Tampilkan</button>
      <?php if ($id_variasi && $id_cabang): ?>
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">Cetak</button>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php if ($id_variasi && $id_cabang): ?>
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
  <div class="px-6 py-5 border-b border-gray-200 bg-slate-50 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h2 class="text-sm font-bold text-gray-900">KARTU STOK</h2>
      <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($namaVariasi) ?></p>
      <p class="text-[11px] text-gray-500">Cabang: <strong><?php foreach($cabangList as $c){if($c['id']==$id_cabang) echo htmlspecialchars($c['nama']);} ?></strong> &bull; Periode: <strong><?= date('d M Y',strtotime($dari)) ?> - <?= date('d M Y',strtotime($sampai)) ?></strong></p>
    </div>
    <div class="text-right">
      <p class="text-[10px] text-gray-500">Stok Awal Periode</p>
      <p class="text-2xl font-bold text-gray-900"><?= number_format($stok_awal) ?> <span class="text-sm font-normal text-gray-500">unit</span></p>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-xs">
      <thead class="bg-slate-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
        <tr>
          <th class="px-5 py-3 text-left">Tgl/Waktu</th>
          <th class="px-5 py-3 text-left">Keterangan / Referensi</th>
          <th class="px-5 py-3 text-left">Operator</th>
          <th class="px-5 py-3 text-right text-emerald-700">Masuk</th>
          <th class="px-5 py-3 text-right text-rose-700">Keluar</th>
          <th class="px-5 py-3 text-right">Saldo</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-zcBrd/60">
        <tr class="bg-blue-50/50">
          <td class="px-5 py-3 text-gray-500 font-mono"><?= date('d/m/Y',strtotime($dari)) ?></td>
          <td class="px-5 py-3 font-semibold text-gray-900" colspan="2">Saldo Awal Periode</td>
          <td class="px-5 py-3"></td>
          <td class="px-5 py-3"></td>
          <td class="px-5 py-3 text-right font-bold text-gray-900"><?= number_format($stok_awal) ?></td>
        </tr>
        <?php $saldo = $stok_awal; $tot_masuk = 0; $tot_keluar = 0;
        if (empty($movements)): ?>
          <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 italic">Tidak ada pergerakan stok pada periode ini.</td></tr>
        <?php else: foreach ($movements as $m):
          $jenis = $m['jenis_mutasi'];
          $masuk  = in_array($jenis, ['Masuk']) ? intval($m['qty']) : 0;
          $keluar = in_array($jenis, ['Keluar', 'Pengembalian/Batal', 'Karantina']) ? intval($m['qty']) : 0;
          $saldo += $masuk - $keluar;
          $tot_masuk += $masuk; $tot_keluar += $keluar; ?>
          <tr class="hover:bg-slate-50/60 transition">
            <td class="px-5 py-3 text-gray-500 font-mono"><?= date('d/m/Y H:i',strtotime($m['tanggal'])) ?></td>
            <td class="px-5 py-3 font-semibold text-gray-900"><?= htmlspecialchars($m['keterangan'] ?? $jenis) ?></td>
            <td class="px-5 py-3 text-gray-500"><?= htmlspecialchars($m['operator'] ?? '-') ?></td>
            <td class="px-5 py-3 text-right font-semibold text-emerald-700"><?= $masuk > 0 ? '+'.number_format($masuk) : '' ?></td>
            <td class="px-5 py-3 text-right font-semibold text-rose-600"><?= $keluar > 0 ? '-'.number_format($keluar) : '' ?></td>
            <td class="px-5 py-3 text-right font-bold <?= $saldo<0?'text-rose-600':'text-gray-900' ?>"><?= number_format($saldo) ?></td>
          </tr>
        <?php endforeach; endif; ?>
        <tr class="bg-gray-800/5 font-bold">
          <td colspan="3" class="px-5 py-3 text-gray-900 font-bold">Total Periode</td>
          <td class="px-5 py-3 text-right text-emerald-700">+<?= number_format($tot_masuk ?? 0) ?></td>
          <td class="px-5 py-3 text-right text-rose-600">-<?= number_format($tot_keluar ?? 0) ?></td>
          <td class="px-5 py-3 text-right text-gray-900"><?= number_format($saldo) ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-12 text-center">
  <p class="text-sm font-semibold text-gray-500">Pilih Produk Variasi dan Cabang di atas untuk menampilkan Kartu Stok.</p>
  <p class="text-xs text-slate-400 mt-1">Data pergerakan stok ditampilkan secara kronologis lengkap dengan saldo berjalan.</p>
</div>
<?php endif; ?>

<?php layoutEnd(); ?>

