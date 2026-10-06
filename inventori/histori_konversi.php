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
           u.nama_lengkap AS nama_operator,
           sb.id AS id_stok_batch
    FROM histori_konversi hk
    JOIN produk_variasi pv ON hk.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN users u ON hk.dibuat_oleh = u.id
    LEFT JOIN stok_batch sb ON sb.no_batch = hk.batch_hasil AND sb.id_variasi = hk.id_variasi
    ORDER BY hk.created_at DESC
");
$histori = $stmt->fetchAll();
layoutHead('Histori Buka Dus');
layoutBodyOpen();
layoutSidebar('histori_konversi');
layoutHeader('Histori Konversi / Buka Dus', 'Laporan jejak audit (Audit Trail) pembongkaran kemasan utuh (grosir) menjadi eceran.');
?>

<div class="mb-6 flex justify-end">
    <a href="konversi_uom.php" class="bg-zc hover:bg-zcHv text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Konversi Dus Manual
    </a>
</div>

<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 border-b border-zcBrd text-xs text-zcMut font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Waktu</th>
                    <th class="px-6 py-4">Operator</th>
                    <th class="px-6 py-4">Produk</th>
                    <th class="px-6 py-4">Batch Asal (Utuh)</th>
                    <th class="px-6 py-4">Mutasi Dus Keluar</th>
                    <th class="px-6 py-4">Batch Baru (Eceran)</th>
                    <th class="px-6 py-4">Mutasi Pcs Masuk</th>
                    <th class="px-6 py-4 text-center">Aksi Fisik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd text-sm">
                <?php if(empty($histori)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zcMut italic">Belum ada riwayat pembongkaran dus.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($histori as $h): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-zcMut font-medium text-xs">
                            <?= date('d M Y, H:i', strtotime($h['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-zcTxt text-xs font-semibold border border-zcBrd">
                                <svg class="w-3.5 h-3.5 text-zcMut" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <?= htmlspecialchars($h['nama_operator']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-zcTxt"><?= htmlspecialchars($h['nama_produk']) ?></div>
                            <div class="text-xs text-zcMut mt-0.5"><?= htmlspecialchars($h['nama_variasi']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-zcTxt font-bold bg-slate-100 px-2 py-1 rounded border border-zcBrd"><?= htmlspecialchars($h['batch_asal']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-rose-600 font-bold bg-rose-50/80 px-2.5 py-1 rounded border border-rose-100">
                                ↓ Keluar <?= $h['qty_box_buka'] ?> <?= htmlspecialchars($h['satuan_besar']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-zc font-bold bg-blue-50/50 px-2 py-1 rounded border border-blue-100/50"><?= htmlspecialchars($h['batch_hasil']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold bg-emerald-50/80 px-2.5 py-1 rounded border border-emerald-100">
                                ↑ Masuk <?= $h['qty_pcs_hasil'] ?> <?= htmlspecialchars($h['satuan_kecil']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <form action="cetak_stiker.php" method="GET" target="_blank">
                                <input type="hidden" name="tipe" value="batch">
                                <input type="hidden" name="id" value="<?= $h['id_stok_batch'] ?>">
                                <button type="submit" class="bg-white hover:bg-slate-50 text-zcTxt font-semibold px-3 py-1.5 rounded-lg border border-zcBrd text-xs transition shadow-sm flex items-center gap-1.5 mx-auto">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                    Cetak
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php layoutFooter(); ?>
