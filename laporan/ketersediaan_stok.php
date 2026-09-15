<?php
// File: laporan/ketersediaan_stok.php – Laporan Monitoring Ketersediaan Fisik Stok
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);
$status_filter = $_GET['status'] ?? 'semua';

$cabangList = [];
$cabangWhere = "";

try {
    $stmt = $pdo->prepare("
        SELECT
            pi.nama_produk,
            pi.kategori,
            pv.id AS id_variasi,
            pv.nama_variasi,
            pv.sku_variasi,
            pv.satuan_kecil,
            pv.satuan_besar,
            pv.rasio_konversi,
            COALESCE(sc.stok, 0) AS stok_pcs,
            1 AS id_cabang,
            'Pusat' AS nama_cabang
        FROM produk_variasi pv
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id $cabangWhere
        WHERE pi.is_active = 1 AND pv.is_active = 1
        ORDER BY pi.kategori ASC, pi.nama_produk ASC, pv.nama_variasi ASC
    ");
    $stmt->execute();
    $rawRows = $stmt->fetchAll();
} catch(Exception $e) { $rawRows = []; }

// Process quantities and statuses
$rows = [];
$totalPcs = 0;
$totalBox = 0;
$stokKritisCount = 0;

foreach ($rawRows as $r) {
    $pcs = intval($r['stok_pcs']);
    $rasio = max(1, intval($r['rasio_konversi'] ?: 1));
    $box = floor($pcs / $rasio);

    if ($pcs <= 0) {
        $status = 'Habis';
        $statusBadge = 'bg-rose-50 border-rose-200 text-rose-700';
    } elseif ($pcs < 10) {
        $status = 'Kritis';
        $statusBadge = 'bg-amber-50 border-amber-200 text-amber-700';
    } else {
        $status = 'Aman';
        $statusBadge = 'bg-emerald-50 border-emerald-200 text-emerald-700';
    }

    if ($pcs < 10) $stokKritisCount++;

    if ($status_filter === 'kritis' && $pcs >= 10) continue;
    if ($status_filter === 'habis' && $pcs > 0) continue;
    if ($status_filter === 'aman' && $pcs < 10) continue;

    $r['stok_box'] = $box;
    $r['status_label'] = $status;
    $r['status_badge'] = $statusBadge;
    $rows[] = $r;

    $totalPcs += $pcs;
    $totalBox += $box;
}

$totalSKU = count(array_unique(array_column($rawRows, 'sku_variasi')));

layoutHead('Ketersediaan Stok Fisik');
layoutBodyOpen();
layoutSidebar('laporan_ketersediaan');
layoutHeader('Ketersediaan Stok Fisik', 'Pemantauan kuantitas fisik persediaan (Pcs & Box) dan status stok kritis per cabang');
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
      <label class="block text-[11px] font-semibold text-zcMut mb-1.5">Pilih Cabang</label>
      <select name="id_cabang" class="text-xs border border-zcBrd rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc">
        <option value="0">Semua Cabang</option>
        <?php foreach ($cabangList as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id']==$id_cabang?'selected':'' ?>><?= htmlspecialchars($c['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-[11px] font-semibold text-zcMut mb-1.5">Status Ketersediaan</label>
      <select name="status" class="text-xs border border-zcBrd rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc">
        <option value="semua" <?= $status_filter==='semua'?'selected':'' ?>>Semua Status</option>
        <option value="kritis" <?= $status_filter==='kritis'?'selected':'' ?>>Stok Menipis / Kritis (&lt; 10 Pcs)</option>
        <option value="habis" <?= $status_filter==='habis'?'selected':'' ?>>Stok Habis (0 Pcs)</option>
        <option value="aman" <?= $status_filter==='aman'?'selected':'' ?>>Stok Aman (&ge; 10 Pcs)</option>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="px-4 py-2 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition">Tampilkan</button>
      <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">Cetak Laporan</button>
    </div>
  </form>
</div>

<!-- Summary Cards Fisik -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
  <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
    <span class="text-[11px] font-semibold text-zcMut block mb-1">Total Unit Fisik</span>
    <span class="text-xl font-bold text-zcTxt"><?= number_format($totalPcs) ?></span>
    <span class="text-[10px] text-zcMut block mt-0.5">Satuan Terkecil (Pcs)</span>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
    <span class="text-[11px] font-semibold text-zcMut block mb-1">Total Kemasan Grosir</span>
    <span class="text-xl font-bold text-zc"><?= number_format($totalBox) ?></span>
    <span class="text-[10px] text-zcMut block mt-0.5">Satuan Kemasan (Box)</span>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
    <span class="text-[11px] font-semibold text-zcMut block mb-1">Total Variasi Terdaftar</span>
    <span class="text-xl font-bold text-zcTxt"><?= $totalSKU ?></span>
    <span class="text-[10px] text-zcMut block mt-0.5">SKU Aktif</span>
  </div>
  <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
    <span class="text-[11px] font-semibold text-rose-600 block mb-1">Item Kritis / Habis</span>
    <span class="text-xl font-bold text-rose-700"><?= $stokKritisCount ?></span>
    <span class="text-[10px] text-rose-500 block mt-0.5">Perlu Pengadaan Ulang</span>
  </div>
</div>

<!-- Table -->
<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-semibold text-xs">
        <tr>
          <th class="py-3 px-4">No</th>
          <th class="py-3 px-4">Kategori</th>
          <th class="py-3 px-4">Nama Produk &amp; Variasi</th>
          <th class="py-3 px-4">SKU</th>
          <th class="py-3 px-4">Lokasi Cabang</th>
          <th class="py-3 px-4 text-center">Rasio Kemasan</th>
          <th class="py-3 px-4 text-right">Stok Fisik (Pcs)</th>
          <th class="py-3 px-4 text-right">Tersedia Grosir</th>
          <th class="py-3 px-4 text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-zcBrd">
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="py-8 text-center text-zcMut">Tidak ada data persediaan fisik yang sesuai filter.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $i => $r): ?>
          <tr class="hover:bg-slate-50 transition">
            <td class="py-3 px-4 text-zcMut"><?= $i + 1 ?></td>
            <td class="py-3 px-4">
              <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[11px] font-bold"><?= htmlspecialchars($r['kategori']) ?></span>
            </td>
            <td class="py-3 px-4">
              <span class="font-bold text-zcTxt block"><?= htmlspecialchars($r['nama_produk']) ?></span>
              <span class="text-zcMut text-xs"><?= htmlspecialchars($r['nama_variasi']) ?></span>
            </td>
            <td class="py-3 px-4 font-mono text-xs text-slate-600"><?= htmlspecialchars($r['sku_variasi']) ?></td>
            <td class="py-3 px-4 font-medium text-slate-700"><?= htmlspecialchars($r['nama_cabang'] ?: 'Gudang Pusat') ?></td>
            <td class="py-3 px-4 text-center text-zcMut font-mono text-xs">
              1 <?= htmlspecialchars($r['satuan_besar'] ?: 'Box') ?> = <?= $r['rasio_konversi'] ?> <?= htmlspecialchars($r['satuan_kecil'] ?: 'Pcs') ?>
            </td>
            <td class="py-3 px-4 text-right font-bold text-zcTxt">
              <?= number_format($r['stok_pcs']) ?> <span class="text-xs font-normal text-zcMut"><?= htmlspecialchars($r['satuan_kecil'] ?: 'Pcs') ?></span>
            </td>
            <td class="py-3 px-4 text-right font-bold text-zc">
              <?= number_format($r['stok_box']) ?> <span class="text-xs font-normal text-zcMut"><?= htmlspecialchars($r['satuan_besar'] ?: 'Box') ?></span>
            </td>
            <td class="py-3 px-4 text-center">
              <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?= $r['status_badge'] ?>">
                <?= $r['status_label'] ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php layoutEnd(); ?>
