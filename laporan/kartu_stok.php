<?php
// File: laporan/kartu_stok.php - Laporan Kartu Stok (Audit Trail Omnichannel)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$id_variasi = intval($_GET['id_variasi'] ?? 0);
$dari       = $_GET['dari'] ?? date('Y-m-01');
$sampai     = $_GET['sampai'] ?? date('Y-m-d');

$variasiList = $pdo->query("
    SELECT pv.id, pv.sku_variasi, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS label 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pi.is_active = 1 AND pv.is_active = 1
    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
")->fetchAll();

$produkData = null;
$logData = [];

if ($id_variasi > 0) {
    // Info Barang
    $stmtProd = $pdo->prepare("
        SELECT pv.sku_variasi, pi.nama_produk, pv.nama_variasi, 
               pv.satuan_kecil as nama_satuan, pv.stok_minimum, sc.stok
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_toko sc ON pv.id = sc.id_variasi 
        WHERE pv.id = ?
    ");
    $stmtProd->execute([$id_variasi]);
    $produkData = $stmtProd->fetch();

    // Ambil Data Log
    $stmtLog = $pdo->prepare("
        SELECT ks.tanggal, ks.keterangan as deskripsi, ks.jenis_mutasi, ks.qty, ks.sisa_stok, u.nama_lengkap
        FROM kartu_stok ks
        LEFT JOIN users u ON ks.dibuat_oleh = u.id
        WHERE 1=1 AND ks.id_variasi = ?
          AND DATE(ks.tanggal) BETWEEN ? AND ?
        ORDER BY ks.tanggal ASC, ks.id ASC
    ");
    $stmtLog->execute([$id_variasi, $dari, $sampai]);
    $logData = $stmtLog->fetchAll();
}

layoutHead('Kartu Stok');
layoutBodyOpen();
layoutSidebar('laporan_kartu_stok');
layoutHeader('Kartu Stok Barang', 'Audit log fisik keluar/masuk (Pusat - Muharto)');
?>

<div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[250px]">
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Pilih Barang *</label>
            <select name="id_variasi" required class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                <option value="">-- Pilih Barang / SKU --</option>
                <?php foreach($variasiList as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $id_variasi==$v['id']?'selected':'' ?>>
                    <?= htmlspecialchars($v['label']) ?> (<?= htmlspecialchars($v['sku_variasi']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Dari Tanggal</label>
            <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sampai Tanggal</label>
            <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Tampilkan
            </button>
            <?php if ($produkData): ?>
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition flex items-center gap-2 print:hidden">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Format Fisik
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($id_variasi > 0 && $produkData): ?>
<!-- CETAK KARTU STOK FORMAT FISIK -->
<div class="bg-white p-8 md:p-12 shadow-sm rounded-none border border-slate-300 w-full max-w-5xl mx-auto font-sans print:m-0 print:border-none print:shadow-none print:p-0">
    <h1 class="text-center text-xl md:text-2xl font-black mb-10 tracking-wide text-black">Kartu Stok Barang</h1>
    
    <!-- Informasi Header Kartu -->
    <div class="grid grid-cols-2 gap-x-12 mb-6 text-sm font-semibold text-black">
        <div class="grid grid-cols-[130px_10px_auto] gap-y-1">
            <div>Nama Barang</div><div>:</div><div><?= htmlspecialchars($produkData['nama_produk'] . ' - ' . $produkData['nama_variasi']) ?></div>
            <div>Kode Barang</div><div>:</div><div><?= htmlspecialchars($produkData['sku_variasi']) ?></div>
            <div>Satuan Barang</div><div>:</div><div><?= htmlspecialchars($produkData['nama_satuan'] ?? '-') ?></div>
        </div>
        <div class="grid grid-cols-[130px_10px_auto] gap-y-1">
            <div>Minimum Stok</div><div>:</div><div><?= htmlspecialchars($produkData['stok_minimum']) ?></div>
            <div>Stok Riil (Sisa)</div><div>:</div><div class="text-rose-600 font-bold"><?= htmlspecialchars($produkData['stok']) ?></div>
            <div>Lokasi (Toko)</div><div>:</div><div>Pusat (Muharto)</div>
        </div>
    </div>

    <!-- Tabel Kartu Stok -->
    <table class="w-full border-collapse border border-slate-900 text-sm print:text-xs">
        <thead class="bg-emerald-600 text-white border-b-2 border-slate-900">
            <tr>
                <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Tanggal</th>
                <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle">Deskripsi</th>
                <th colspan="3" class="border border-slate-900 px-3 py-1.5 text-center">Barang</th>
                <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Penanggung Jawab</th>
                <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Keterangan</th>
            </tr>
            <tr>
                <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Masuk</th>
                <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Keluar</th>
                <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Sisa</th>
            </tr>
        </thead>
        <tbody class="text-black bg-white">
            <?php if (empty($logData)): ?>
                <tr>
                    <td colspan="7" class="border border-slate-900 px-3 py-8 text-center italic text-slate-500">Tidak ada data pergerakan stok untuk periode yang dipilih.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logData as $log): ?>
                <tr class="hover:bg-slate-50 print:hover:bg-transparent">
                    <td class="border border-slate-900 px-2 py-1.5 text-center whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?></td>
                    <td class="border border-slate-900 px-3 py-1.5"><?= htmlspecialchars($log['deskripsi'] ?? '-') ?></td>
                    <td class="border border-slate-900 px-2 py-1.5 text-center font-bold <?= $log['jenis_mutasi']=='Masuk'?'text-emerald-700':'' ?>"><?= $log['jenis_mutasi']=='Masuk' ? number_format($log['qty']) : '' ?></td>
                    <td class="border border-slate-900 px-2 py-1.5 text-center font-bold <?= $log['jenis_mutasi']=='Keluar'?'text-rose-700':'' ?>"><?= $log['jenis_mutasi']=='Keluar' ? number_format($log['qty']) : '' ?></td>
                    <td class="border border-slate-900 px-2 py-1.5 text-center font-black bg-slate-50"><?= number_format($log['sisa_stok']) ?></td>
                    <td class="border border-slate-900 px-2 py-1.5 text-center text-xs"><?= htmlspecialchars($log['nama_lengkap'] ?? 'Sistem') ?></td>
                    <td class="border border-slate-900 px-2 py-1.5"></td>
                </tr>
                <?php endforeach; ?>
                <!-- Baris Kosong Tambahan untuk Estetika Fisik -->
                <?php for($i=0; $i<5; $i++): ?>
                <tr>
                    <td class="border border-slate-900 px-2 py-3.5 text-center"></td>
                    <td class="border border-slate-900 px-3 py-3.5"></td>
                    <td class="border border-slate-900 px-2 py-3.5 text-center"></td>
                    <td class="border border-slate-900 px-2 py-3.5 text-center"></td>
                    <td class="border border-slate-900 px-2 py-3.5 text-center bg-slate-50"></td>
                    <td class="border border-slate-900 px-2 py-3.5 text-center"></td>
                    <td class="border border-slate-900 px-2 py-3.5"></td>
                </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .print\:m-0, .print\:m-0 * { visibility: visible; }
    .print\:m-0 { position: absolute; left: 0; top: 0; width: 100%; margin: 0 !important; }
}
</style>
<?php endif; ?>

<?php layoutFooter(); ?>
