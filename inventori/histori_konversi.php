<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

// Ambil data histori konversi
$stmt = $pdo->query("
    SELECT hk.*, 
           pi.nama_produk, 
           pv.nama_variasi, 
           pv.satuan_besar, 
           pv.satuan_kecil,
           pv.rasio_konversi,
           u.nama_lengkap AS nama_operator,
           sb.id AS id_stok_batch
    FROM histori_konversi hk
    JOIN produk_variasi pv ON hk.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN users u ON hk.dibuat_oleh = u.id
    LEFT JOIN stok_batch sb ON sb.no_batch COLLATE utf8mb4_unicode_ci = hk.batch_hasil COLLATE utf8mb4_unicode_ci AND sb.id_variasi = hk.id_variasi
    ORDER BY hk.created_at DESC
");
$histori = $stmt->fetchAll();

$totalKonversi = count($histori);
$totalBesarKeluar = array_sum(array_column($histori, 'qty_box_buka'));
$totalKecilMasuk = array_sum(array_column($histori, 'qty_pcs_hasil'));

layoutHead('Histori Konversi Satuan');
layoutBodyOpen();
layoutSidebar('histori_konversi');
layoutHeader('Histori Konversi Satuan', 'Laporan jejak audit (Audit Trail) pemecahan/konversi kemasan Satuan Besar menjadi Satuan Kecil.');
?>

<!-- Metric Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Aktivitas Konversi</div>
            <div class="text-2xl font-black text-slate-800 mt-1"><?= number_format($totalKonversi) ?> <span class="text-xs font-semibold text-slate-400">kali</span></div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-zc flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
    </div>
    <div class="bg-white border border-amber-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Satuan Besar Dibongkar</div>
            <div class="text-2xl font-black text-rose-600 mt-1">-<?= number_format($totalBesarKeluar) ?> <span class="text-xs font-semibold text-amber-600">Kemasan (Gudang)</span></div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
    </div>
    <div class="bg-white border border-emerald-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Satuan Kecil Masuk</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">+<?= number_format($totalKecilMasuk) ?> <span class="text-xs font-semibold text-emerald-600">Unit (Etalase)</span></div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
    </div>
</div>

<div class="mb-6 flex justify-end">
    <a href="konversi_uom.php" class="bg-zc hover:bg-zcHv text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2 cursor-pointer active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Konversi Satuan Manual
    </a>
</div>

<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-200 text-xs">
                    <th rowspan="2" class="px-3 py-2.5 text-center align-middle font-bold uppercase tracking-wider text-slate-600 border-r border-slate-200 bg-slate-50/80 w-24">Waktu</th>
                    <th rowspan="2" class="px-3 py-2.5 text-center align-middle font-bold uppercase tracking-wider text-slate-600 border-r border-slate-200 bg-slate-50/80 w-28">Operator</th>
                    <th rowspan="2" class="px-3 py-2.5 text-left align-middle font-bold uppercase tracking-wider text-slate-600 border-r border-slate-200 bg-slate-50/80">Produk & Rasio</th>
                    
                    <!-- KELOMPOK MUTASI SATUAN BESAR -->
                    <th colspan="3" class="px-3 py-2 text-center font-black uppercase tracking-wider text-amber-900 bg-amber-100/70 border-r border-amber-200">
                        <div class="flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Satuan Besar</span>
                        </div>
                    </th>

                    <!-- KELOMPOK MUTASI SATUAN KECIL -->
                    <th colspan="2" class="px-3 py-2 text-center font-black uppercase tracking-wider text-emerald-900 bg-emerald-100/70 border-r border-emerald-200">
                        <div class="flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Satuan Kecil</span>
                        </div>
                    </th>

                    <th rowspan="2" class="px-3 py-2.5 text-center align-middle font-bold uppercase tracking-wider text-slate-600 bg-slate-50/80 w-20">Aksi</th>
                </tr>
                <tr class="text-[11px] font-bold uppercase tracking-wider">
                    <!-- Satuan Besar Columns -->
                    <th class="px-2.5 py-1.5 text-center text-amber-950 bg-amber-50/70 border-r border-amber-200 whitespace-nowrap">Batch Asal</th>
                    <th class="px-2.5 py-1.5 text-center text-amber-950 bg-amber-50/70 border-r border-amber-200 whitespace-nowrap">Keluar</th>
                    <th class="px-2.5 py-1.5 text-center text-amber-950 bg-amber-50/70 border-r border-amber-200 whitespace-nowrap">Sisa Stok</th>

                    <!-- Satuan Kecil Columns -->
                    <th class="px-2.5 py-1.5 text-center text-emerald-950 bg-emerald-50/70 border-r border-emerald-200 whitespace-nowrap">Sub-Batch Hasil</th>
                    <th class="px-2.5 py-1.5 text-center text-emerald-950 bg-emerald-50/70 border-r border-emerald-200 whitespace-nowrap">Masuk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd text-xs bg-white">
                <?php if(empty($histori)): ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-zcMut italic">Belum ada riwayat konversi satuan barang.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($histori as $h): ?>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Waktu -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center border-r border-slate-100">
                            <div class="font-bold text-slate-800"><?= date('d/m/Y', strtotime($h['created_at'])) ?></div>
                            <div class="text-[10px] text-slate-400"><?= date('H:i', strtotime($h['created_at'])) ?> WIB</div>
                        </td>

                        <!-- Operator -->
                        <td class="px-2.5 py-2 text-center border-r border-slate-100">
                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-zcTxt text-[11px] font-semibold border border-zcBrd whitespace-nowrap">
                                <?= htmlspecialchars($h['nama_operator'] ?? 'Sistem') ?>
                            </span>
                        </td>

                        <!-- Produk & Rasio -->
                        <td class="px-3 py-2 border-r border-slate-100">
                            <div class="font-bold text-zcTxt leading-tight"><?= htmlspecialchars($h['nama_produk']) ?></div>
                            <div class="text-[11px] text-zcMut mt-0.5 leading-tight flex items-center gap-1 flex-wrap">
                                <span><?= htmlspecialchars($h['nama_variasi']) ?></span>
                                <span class="text-slate-300">•</span>
                                <span class="text-amber-800 font-semibold bg-amber-50 px-1.5 py-0.2 rounded text-[10px] border border-amber-200 whitespace-nowrap">
                                    1 <?= htmlspecialchars($h['satuan_besar']) ?> = <?= intval($h['rasio_konversi'] ?? 1) ?> <?= htmlspecialchars($h['satuan_kecil']) ?>
                                </span>
                            </div>
                        </td>

                        <!-- Batch Asal (Satuan Besar) -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center border-r border-slate-100">
                            <span class="font-mono text-[11px] font-bold text-amber-950 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                <?= htmlspecialchars($h['batch_asal']) ?>
                            </span>
                        </td>

                        <!-- Jumlah Keluar (Satuan Besar) -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center border-r border-slate-100">
                            <span class="inline-flex items-center gap-0.5 text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded border border-rose-200 text-xs">
                                <svg class="w-3 h-3 text-rose-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                <span>-<?= $h['qty_box_buka'] ?> <?= htmlspecialchars($h['satuan_besar']) ?></span>
                            </span>
                        </td>

                        <!-- Sisa Stok (Satuan Besar) -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center border-r border-slate-100">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                                <span><?= intval($h['sisa_dus_akhir'] ?? 0) ?> <?= htmlspecialchars($h['satuan_besar']) ?></span>
                            </span>
                        </td>

                        <!-- Sub-Batch Hasil (Satuan Kecil) -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center border-r border-slate-100">
                            <span class="font-mono text-[11px] font-bold text-emerald-950 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                <?= htmlspecialchars($h['batch_hasil']) ?>
                            </span>
                        </td>

                        <!-- Jumlah Masuk (Satuan Kecil) -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center border-r border-slate-100">
                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-xs">
                                <svg class="w-3 h-3 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                <span>+<?= $h['qty_pcs_hasil'] ?> <?= htmlspecialchars($h['satuan_kecil']) ?></span>
                            </span>
                        </td>

                        <!-- Aksi Fisik -->
                        <td class="px-2.5 py-2 whitespace-nowrap text-center">
                            <?php if (!empty($h['id_stok_batch'])): ?>
                            <form action="cetak_stiker.php" method="GET" target="_blank">
                                <input type="hidden" name="tipe" value="batch">
                                <input type="hidden" name="id" value="<?= $h['id_stok_batch'] ?>">
                                <button type="submit" class="bg-white hover:bg-slate-50 text-zcTxt font-semibold px-2 py-1 rounded border border-zcBrd text-[11px] transition shadow-2xs flex items-center gap-1 mx-auto cursor-pointer active:scale-95" title="Cetak label barcode sub-batch untuk kemasan kecil">
                                    <svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                    <span>Cetak</span>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-xs text-slate-400 italic">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php layoutFooter(); ?>
