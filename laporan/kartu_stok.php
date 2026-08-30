<?php
// File: laporan/kartu_stok.php – Laporan Kartu Stok (Audit Trail Omnichannel)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'admin_cabang', 'kasir']);

$id_variasi = intval($_GET['id_variasi'] ?? 0);
$id_cabang  = intval($_GET['id_cabang']  ?? ($_SESSION['id_cabang'] ?? 0));
$dari       = $_GET['dari'] ?? date('Y-m-01');
$sampai     = $_GET['sampai'] ?? date('Y-m-d');
$jenis_filter = trim($_GET['jenis'] ?? '');

$cabangList  = $pdo->query("SELECT id, nama FROM cabang WHERE is_active=1 ORDER BY id")->fetchAll();
$variasiList = $pdo->query("
    SELECT pv.id, pv.sku_variasi, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS label 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pi.is_active = 1 AND pv.is_active = 1
    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
")->fetchAll();

// Build query
$whereClauses = ["DATE(ks.tanggal) BETWEEN ? AND ?"];
$params = [$dari, $sampai];

if ($id_cabang > 0) {
    $whereClauses[] = "ks.id_cabang = ?";
    $params[] = $id_cabang;
}

if ($id_variasi > 0) {
    $whereClauses[] = "ks.id_variasi = ?";
    $params[] = $id_variasi;
}

if (!empty($jenis_filter)) {
    $whereClauses[] = "ks.jenis_mutasi = ?";
    $params[] = $jenis_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

$stmt = $pdo->prepare("
    SELECT ks.*, 
           pv.sku_variasi, 
           pv.nama_variasi, 
           pi.nama_produk, 
           c.nama AS nama_cabang
    FROM kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN cabang c ON ks.id_cabang = c.id
    WHERE $whereSQL
    ORDER BY ks.tanggal DESC, ks.id DESC
");
$stmt->execute($params);
$movements = $stmt->fetchAll();

// Summary stats
$totalMasuk = 0;
$totalKeluar = 0;
$totalTransaksi = count($movements);

foreach ($movements as $m) {
    if ($m['jenis_mutasi'] === 'Masuk') {
        $totalMasuk += intval($m['qty']);
    } elseif (in_array($m['jenis_mutasi'], ['Keluar', 'Pengembalian/Batal', 'Karantina'])) {
        $totalKeluar += intval($m['qty']);
    }
}

layoutHead('Laporan Kartu Stok');
layoutBodyOpen();
layoutSidebar('laporan_kartu_stok');
layoutHeader('Kartu Stok Omnichannel', 'Audit trail pergerakan stok fisik kasir POS, e-commerce, mutasi, dan penerimaan supplier');
?>

<style>
@media print {
  .no-print { display: none !important; }
  aside, header { display: none !important; }
  body { background: white; }
}
</style>

<!-- Filter Panel -->
<div class="bg-white border border-zcBrd rounded-2xl shadow-sm p-5 mb-6 no-print">
  <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
    <div>
      <label class="block text-xs font-bold text-zcTxt mb-1.5">Cabang</label>
      <select name="id_cabang" class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 bg-white focus:outline-none focus:border-zc">
        <option value="0">Semua Cabang</option>
        <?php foreach ($cabangList as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id']==$id_cabang?'selected':'' ?>><?= htmlspecialchars($c['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="sm:col-span-2">
      <label class="block text-xs font-bold text-zcTxt mb-1.5">Produk / Variasi</label>
      <select name="id_variasi" class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 bg-white focus:outline-none focus:border-zc">
        <option value="0">Semua Produk (Audit Omnichannel)</option>
        <?php foreach ($variasiList as $v): ?>
          <option value="<?= $v['id'] ?>" <?= $v['id']==$id_variasi?'selected':'' ?>>
            <?= htmlspecialchars($v['label']) ?> (<?= htmlspecialchars($v['sku_variasi']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-zcTxt mb-1.5">Dari Tanggal</label>
      <input type="date" name="dari" value="<?= $dari ?>" class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div>
      <label class="block text-xs font-bold text-zcTxt mb-1.5">Sampai Tanggal</label>
      <input type="date" name="sampai" value="<?= $sampai ?>" class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 px-4 py-2.5 bg-zc hover:bg-zcHv text-white text-sm font-bold rounded-xl transition shadow-sm cursor-pointer">
        Tampilkan
      </button>
      <button type="button" onclick="window.print()" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition cursor-pointer">
        Cetak
      </button>
    </div>
  </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm">
    <span class="text-xs font-bold text-zcMut block mb-1">Total Mutasi Tercatat</span>
    <span class="text-2xl font-black text-zcTxt"><?= number_format($totalTransaksi) ?></span>
    <span class="text-xs text-zcMut block mt-1">Peristiwa perpindahan fisik</span>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm">
    <span class="text-xs font-bold text-emerald-700 block mb-1">Total Unit Masuk</span>
    <span class="text-2xl font-black text-emerald-600">+<?= number_format($totalMasuk) ?></span>
    <span class="text-xs text-emerald-700 block mt-1">Penerimaan fisik (Pcs)</span>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm">
    <span class="text-xs font-bold text-rose-700 block mb-1">Total Unit Keluar</span>
    <span class="text-2xl font-black text-rose-600">-<?= number_format($totalKeluar) ?></span>
    <span class="text-xs text-rose-700 block mt-1">Penjualan POS &amp; Web (Pcs)</span>
  </div>
</div>

<!-- Table Container -->
<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-zcBrd bg-slate-50 flex items-center justify-between flex-wrap gap-2">
    <div>
      <h2 class="text-base font-bold text-zcTxt">Tabel Audit Kartu Stok (Omnichannel)</h2>
      <p class="text-xs text-zcMut mt-0.5">Bukti sinkronisasi mutasi fisik kasir offline dan pesanan online e-commerce</p>
    </div>
    <span class="px-3 py-1 bg-zcLt text-zc font-bold text-xs rounded-full border border-zc/20">
      Periode: <?= date('d M Y', strtotime($dari)) ?> &ndash; <?= date('d M Y', strtotime($sampai)) ?>
    </span>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider text-xs">
        <tr>
          <th class="py-3.5 px-5">Waktu &amp; Tanggal</th>
          <th class="py-3.5 px-5">SKU &amp; Nama Produk</th>
          <th class="py-3.5 px-5 text-center">Jenis Mutasi</th>
          <th class="py-3.5 px-5 text-right">Jumlah (Pcs)</th>
          <th class="py-3.5 px-5 text-right">Sisa Stok (Pcs)</th>
          <th class="py-3.5 px-5 text-left">Referensi / Keterangan</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-zcBrd/70">
        <?php if (empty($movements)): ?>
          <tr>
            <td colspan="6" class="py-12 text-center text-sm text-zcMut italic">
              Tidak ada catatan mutasi stok pada parameter filter ini.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($movements as $m): 
            $jenis = trim($m['jenis_mutasi']);
            $qtyVal = intval($m['qty']);
            $sisaVal = intval($m['sisa_stok']);
            $ket = trim($m['keterangan'] ?? '');

            // Badge Color Rules:
            // Hijau: Masuk / Opname tambah
            // Merah: Keluar / Karantina
            // Kuning: Penyesuaian / Transfer
            if ($jenis === 'Masuk') {
                $badgeCls = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                $qtyCls   = 'text-emerald-700 font-bold';
                $qtySign  = '+';
            } elseif (in_array($jenis, ['Keluar', 'Karantina', 'Pengembalian/Batal'])) {
                $badgeCls = 'bg-rose-50 border-rose-200 text-rose-700';
                $qtyCls   = 'text-rose-600 font-bold';
                $qtySign  = '-';
            } else { // Transfer / Penyesuaian / Opname
                $badgeCls = 'bg-amber-50 border-amber-200 text-amber-700';
                $qtyCls   = 'text-amber-700 font-bold';
                $qtySign  = '';
            }

            // Channel Identifier Pill based on Reference Format:
            $isPos = (stripos($ket, 'POS-') !== false || stripos($ket, 'POS') !== false);
            $isWeb = (stripos($ket, 'WEB-') !== false || stripos($ket, 'ZNC-') !== false || stripos($ket, 'Online') !== false);
          ?>
          <tr class="hover:bg-slate-50/70 transition">
            <!-- 1. Waktu & Tanggal -->
            <td class="py-3.5 px-5 whitespace-nowrap text-zcTxt font-mono text-xs">
              <span class="font-bold text-slate-800"><?= date('d/m/Y', strtotime($m['tanggal'])) ?></span>
              <span class="text-zcMut block text-[11px]"><?= date('H:i:s', strtotime($m['tanggal'])) ?> WIB</span>
            </td>

            <!-- 2. SKU & Nama Produk -->
            <td class="py-3.5 px-5">
              <span class="font-mono text-xs text-slate-500 block font-semibold"><?= htmlspecialchars($m['sku_variasi']) ?></span>
              <span class="font-bold text-zcTxt block leading-snug">
                <?= htmlspecialchars($m['nama_produk']) ?> &ndash; <span class="font-medium text-slate-600"><?= htmlspecialchars($m['nama_variasi']) ?></span>
              </span>
              <?php if (!empty($m['nama_cabang'])): ?>
                <span class="inline-block mt-0.5 text-[10px] text-blue-600 font-semibold bg-blue-50 px-2 py-0.5 rounded">
                  <?= htmlspecialchars($m['nama_cabang']) ?>
                </span>
              <?php endif; ?>
            </td>

            <!-- 3. Jenis Mutasi (Badge Warna) -->
            <td class="py-3.5 px-5 text-center whitespace-nowrap">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?= $badgeCls ?>">
                <?= htmlspecialchars($jenis) ?>
              </span>
            </td>

            <!-- 4. Jumlah (Kuantitas fisik dalam Pcs) -->
            <td class="py-3.5 px-5 text-right font-mono <?= $qtyCls ?> whitespace-nowrap">
              <?= $qtySign ?><?= number_format($qtyVal) ?> <span class="text-xs font-normal text-zcMut">Pcs</span>
            </td>

            <!-- 5. Sisa Stok (Saldo akhir fisik dalam Pcs) -->
            <td class="py-3.5 px-5 text-right font-mono font-bold text-zcTxt whitespace-nowrap">
              <?= number_format($sisaVal) ?> <span class="text-xs font-normal text-zcMut">Pcs</span>
            </td>

            <!-- 6. Referensi / Keterangan (Bukti Omnichannel) -->
            <td class="py-3.5 px-5 text-left">
              <?php if ($isPos): ?>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 font-mono text-xs font-bold">
                  <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/></svg>
                  <?= htmlspecialchars($ket) ?>
                </div>
              <?php elseif ($isWeb): ?>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 font-mono text-xs font-bold">
                  <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                  <?= htmlspecialchars($ket) ?>
                </div>
              <?php else: ?>
                <span class="text-zcTxt text-xs font-medium"><?= htmlspecialchars($ket ?: '-') ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php layoutEnd(); ?>
