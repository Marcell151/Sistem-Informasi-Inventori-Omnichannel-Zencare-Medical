<?php
// File: admin/pesanan.php
// Manajemen Pesanan Online E-Commerce & POS – Kasir & Super Admin
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'kasir']);

$activeCabang = $_SESSION['id_cabang'] ?? 1;
if (isset($_GET['cabang'])) $_SESSION['id_cabang'] = $activeCabang = intval($_GET['cabang']);

$msg = ''; $msgType = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update_status') {
    $idPesanan  = intval($_POST['id_penjualan'] ?? 0);
    $newStatus  = $_POST['status_pesanan'] ?? '';
    
    if ($idPesanan && in_array($newStatus, ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'])) {
        $pdo->prepare("UPDATE penjualan SET status_pesanan = ? WHERE id = ?")->execute([$newStatus, $idPesanan]);
        $msg = "Status pesanan ID #$idPesanan berhasil diubah menjadi '$newStatus'.";
        $msgType = 'success';
    }
}

// Fetch orders for active branch (or all branches for super_admin)
$query = "
    SELECT p.*, c.nama AS nama_cabang, u.nama_lengkap AS nama_kasir
    FROM penjualan p
    JOIN cabang c ON p.id_cabang = c.id
    LEFT JOIN users u ON p.id_user = u.id
";
if (!isAdmin()) {
    $query .= " WHERE p.id_cabang = " . intval($activeCabang);
}
$query .= " ORDER BY p.id DESC LIMIT 50";

$orders = $pdo->query($query)->fetchAll();

layoutHead('Manajemen Pesanan');
layoutBodyOpen();
layoutSidebar('pesanan');
layoutHeader('Manajemen Pesanan E-Commerce & POS', 'Kelola status pesanan toko online dan cetak dokumen pengiriman');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2
        <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
        <span><?= $msgType === 'success' ? '✅' : '⛔' ?></span>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-base font-bold text-zcTxt">Daftar Transaksi Pesanan</h2>
        <p class="text-xs text-zcMut mt-0.5">Total: <?= count($orders) ?> pesanan terbaru</p>
    </div>
</div>

<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Invoice</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-left">Penerima &amp; Alamat</th>
                    <th class="px-4 py-3 text-left">Kurir</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd/60">
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="px-4 py-8 text-center text-zcMut italic">Belum ada data pesanan.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <?php
                        $typeCls = match($o['tipe_transaksi']) {
                            'ecommerce' => 'bg-sky-100 text-sky-800 border-sky-200',
                            'pos'       => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'shopee'    => 'bg-orange-100 text-orange-800 border-orange-200',
                            default     => 'bg-slate-100 text-slate-800 border-slate-200'
                        };
                        $statusCls = match($o['status_pesanan']) {
                            'Selesai'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'Dikirim'   => 'bg-blue-100 text-blue-800 border-blue-200',
                            'Diproses'  => 'bg-amber-100 text-amber-800 border-amber-200',
                            'Dibatalkan'=> 'bg-rose-100 text-rose-800 border-rose-200',
                            default     => 'bg-slate-100 text-slate-700 border-slate-200'
                        };
                        ?>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-zcTxt font-mono block"><?= htmlspecialchars($o['no_invoice']) ?></span>
                                <span class="text-[10px] text-zcMut"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase <?= $typeCls ?>">
                                    <?= htmlspecialchars($o['tipe_transaksi']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-zcTxt block"><?= htmlspecialchars($o['nama_penerima'] ?? $o['nama_kasir'] ?? 'Pelanggan') ?></span>
                                <span class="text-[11px] text-zcMut truncate block max-w-xs"><?= htmlspecialchars($o['alamat_lengkap'] ?? 'Di Tempat') ?></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-zcTxt block"><?= htmlspecialchars($o['kurir'] ?? 'Direct') ?></span>
                                <span class="text-[10px] text-zcMut"><?= htmlspecialchars($o['layanan'] ?? 'Standard') ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="font-bold text-zcTxt block">Rp <?= number_format($o['total_harga'], 0, ',', '.') ?></span>
                                <?php if ($o['ongkir'] > 0): ?>
                                    <span class="text-[10px] text-zcMut">+Ongkir <?= number_format($o['ongkir'], 0, ',', '.') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $statusCls ?>">
                                    <?= htmlspecialchars($o['status_pesanan']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" class="inline flex items-center gap-1">
                                        <input type="hidden" name="aksi" value="update_status">
                                        <input type="hidden" name="id_penjualan" value="<?= $o['id'] ?>">
                                        <select name="status_pesanan" onchange="this.form.submit()" class="text-[11px] border border-zcBrd rounded-lg px-2 py-1 bg-white font-medium focus:outline-none focus:border-zc">
                                            <option value="Menunggu" <?= $o['status_pesanan'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                            <option value="Diproses" <?= $o['status_pesanan'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                            <option value="Dikirim" <?= $o['status_pesanan'] === 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                            <option value="Selesai" <?= $o['status_pesanan'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                            <option value="Dibatalkan" <?= $o['status_pesanan'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                        </select>
                                    </form>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= $o['no_invoice'] ?>" target="_blank"
                                        class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-zcBrd rounded-lg transition">Cetak</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php layoutEnd(); ?>
