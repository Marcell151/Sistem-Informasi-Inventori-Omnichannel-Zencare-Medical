<?php
// File: admin/pesanan.php
// Manajemen Pesanan Online E-Commerce & POS – Karyawan & Super Admin
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$activeCabang = $_SESSION['id_cabang'] ?? 1;
if (isset($_GET['cabang'])) $_SESSION['id_cabang'] = $activeCabang = intval($_GET['cabang']);

$msg = ''; $msgType = '';

// Auto-cancel pesanan e-commerce 'Menunggu Pembayaran' yang kadaluarsa & kembalikan stok
$autoCancelled = batalkanPesananKadaluarsa($pdo);
if ($autoCancelled > 0) {
    $cooldownMenit = defined('RESERVASI_COOLDOWN_MENIT') ? RESERVASI_COOLDOWN_MENIT : 1440;
    $cooldownText = ($cooldownMenit < 60) ? ($cooldownMenit . ' menit') : (round($cooldownMenit / 60) . ' jam');
    $msg = "$autoCancelled pesanan e-commerce 'Menunggu Pembayaran' telah dibatalkan otomatis karena melebihi batas waktu $cooldownText, stok fisik & batch FEFO telah dipulihkan.";
    $msgType = 'info';
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update_status') {
    $idPesanan  = intval($_POST['id_penjualan'] ?? 0);
    $newStatus  = $_POST['status_pesanan'] ?? '';
    
    if ($idPesanan && in_array($newStatus, ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Siap Diambil', 'Selesai', 'Dibatalkan'])) {
        $pdo->beginTransaction();
        try {
            // Ambil status lama dan invoice
            $stmtCek = $pdo->prepare("SELECT status_pesanan, no_invoice, metode_pengambilan FROM penjualan WHERE id = ? FOR UPDATE");
            $stmtCek->execute([$idPesanan]);
            $orderLama = $stmtCek->fetch();

            if ($orderLama) {
                $oldStatus = $orderLama['status_pesanan'];
                $noInvoice = $orderLama['no_invoice'];
                $metodePengambilan = $orderLama['metode_pengambilan'];

                // GUARD: Cegah pembatalan jika pesanan sudah Dikirim atau Selesai
                if ($newStatus === 'Dibatalkan' && in_array($oldStatus, ['Dikirim', 'Selesai', 'Siap Diambil'])) {
                    throw new Exception("Pesanan yang sudah pada tahap Pengiriman/Pengambilan tidak dapat dibatalkan.");
                }

                // Update Status
                $pdo->prepare("UPDATE penjualan SET status_pesanan = ? WHERE id = ?")->execute([$newStatus, $idPesanan]);

                // Jika status diubah menjadi Selesai dan metode Pick-up, catat sebagai Keluar di Kartu Stok
                if ($oldStatus !== 'Selesai' && $newStatus === 'Selesai' && $metodePengambilan === 'Pick-up') {
                    $stmtItems = $pdo->prepare("
                        SELECT d.id_variasi, d.qty, d.catatan_logistik, v.rasio_konversi, v.satuan_besar, v.satuan_kecil 
                        FROM detail_penjualan d 
                        JOIN produk_variasi v ON d.id_variasi = v.id 
                        WHERE d.id_penjualan = ?
                    ");
                    $stmtItems->execute([$idPesanan]);
                    $items = $stmtItems->fetchAll();

                    foreach ($items as $item) {
                        $idVar = $item['id_variasi'];
                        $qtyBox = intval($item['qty']);
                        $rasio = intval($item['rasio_konversi']) ?: 1;
                        $qtyPotong = $qtyBox * $rasio;
                        $satBesar = $item['satuan_besar'] ?? 'Karton';
                        $satKecil = $item['satuan_kecil'] ?? 'Unit';

                        $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
                        $satuanTipe = ($rasio > 1) ? 'besar' : 'kecil';
                        $qtyKartu = ($rasio > 1) ? $qtyBox : $qtyPotong;
                        $satLabel = ($satuanTipe === 'besar') ? $satBesar : $satKecil;
                        $sisaStokTampil = ($rasio > 1) ? floor($sisaStok / $rasio) : $sisaStok;

                        $catatan = trim($item['catatan_logistik'] ?? '');
                        $batchList = [];
                        if (!empty($catatan)) {
                            $parts = array_map('trim', explode(',', $catatan));
                            foreach ($parts as $p) {
                                if (preg_match('/^([^\(]+)(?:\((?:(\d+)\s*(?:[A-Za-z]+)?\s*=\s*)?(\d+)(?:x|[^\)]*)?\))?/i', $p, $m)) {
                                    $bName = trim($m[1]);
                                    $bQty = !empty($m[2]) ? intval($m[2]) : (!empty($m[3]) ? intval($m[3]) : 0);
                                    if ($bQty <= 0) $bQty = ($satuanTipe === 'besar') ? $qtyBox : $qtyPotong;
                                    $batchList[] = ['batch' => $bName, 'qty' => $bQty];
                                }
                            }
                        }

                        if (!empty($batchList)) {
                            foreach ($batchList as $b) {
                                $bName = $b['batch'];
                                $bQty  = $b['qty'];
                                $deskripsi = "Konfirmasi Pengambilan di Toko (Pick-up) [Batch: $bName ($bQty $satLabel)]";
                                $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)")
                                    ->execute([$idVar, $satuanTipe, $noInvoice, $bQty, $sisaStokTampil, $deskripsi, $_SESSION['id_user'] ?? 1]);
                            }
                        } else {
                            $deskripsi = 'Konfirmasi Pengambilan di Toko (Pick-up)';
                            $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)")
                                ->execute([$idVar, $satuanTipe, $noInvoice, $qtyKartu, $sisaStokTampil, $deskripsi, $_SESSION['id_user'] ?? 1]);
                        }
                    }
                }

                // Jika status diubah menjadi Dikirim dan metode Kurir, catat sebagai Keluar di Kartu Stok
                if ($oldStatus !== 'Dikirim' && $newStatus === 'Dikirim' && $metodePengambilan === 'Kurir') {
                    $stmtItems = $pdo->prepare("
                        SELECT d.id_variasi, d.qty, d.catatan_logistik, v.rasio_konversi, v.satuan_besar, v.satuan_kecil 
                        FROM detail_penjualan d 
                        JOIN produk_variasi v ON d.id_variasi = v.id 
                        WHERE d.id_penjualan = ?
                    ");
                    $stmtItems->execute([$idPesanan]);
                    $items = $stmtItems->fetchAll();

                    foreach ($items as $item) {
                        $idVar = $item['id_variasi'];
                        $qtyBox = intval($item['qty']);
                        $rasio = intval($item['rasio_konversi']) ?: 1;
                        $qtyPotong = $qtyBox * $rasio;
                        $satBesar = $item['satuan_besar'] ?? 'Karton';
                        $satKecil = $item['satuan_kecil'] ?? 'Unit';

                        $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
                        $satuanTipe = ($rasio > 1) ? 'besar' : 'kecil';
                        $qtyKartu = ($rasio > 1) ? $qtyBox : $qtyPotong;
                        $satLabel = ($satuanTipe === 'besar') ? $satBesar : $satKecil;
                        $sisaStokTampil = ($rasio > 1) ? floor($sisaStok / $rasio) : $sisaStok;

                        $catatan = trim($item['catatan_logistik'] ?? '');
                        $batchList = [];
                        if (!empty($catatan)) {
                            $parts = array_map('trim', explode(',', $catatan));
                            foreach ($parts as $p) {
                                if (preg_match('/^([^\(]+)(?:\((?:(\d+)\s*(?:[A-Za-z]+)?\s*=\s*)?(\d+)(?:x|[^\)]*)?\))?/i', $p, $m)) {
                                    $bName = trim($m[1]);
                                    $bQty = !empty($m[2]) ? intval($m[2]) : (!empty($m[3]) ? intval($m[3]) : 0);
                                    if ($bQty <= 0) $bQty = ($satuanTipe === 'besar') ? $qtyBox : $qtyPotong;
                                    $batchList[] = ['batch' => $bName, 'qty' => $bQty];
                                }
                            }
                        }

                        if (!empty($batchList)) {
                            foreach ($batchList as $b) {
                                $bName = $b['batch'];
                                $bQty  = $b['qty'];
                                $deskripsi = "Pengiriman via Ekspedisi (Kurir) [Batch: $bName ($bQty $satLabel)]";
                                $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)")
                                    ->execute([$idVar, $satuanTipe, $noInvoice, $bQty, $sisaStokTampil, $deskripsi, $_SESSION['id_user'] ?? 1]);
                            }
                        } else {
                            $deskripsi = 'Pengiriman via Ekspedisi (Kurir)';
                            $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)")
                                ->execute([$idVar, $satuanTipe, $noInvoice, $qtyKartu, $sisaStokTampil, $deskripsi, $_SESSION['id_user'] ?? 1]);
                        }
                    }
                }
                // Jika status diubah menjadi Dibatalkan (dan sebelumnya bukan Dibatalkan), kembalikan stok
                if ($oldStatus !== 'Dibatalkan' && $newStatus === 'Dibatalkan') {
                    $stmtItems = $pdo->prepare("
                        SELECT d.id_variasi, d.qty, d.catatan_logistik, v.rasio_konversi, v.satuan_besar, pi.kategori 
                        FROM detail_penjualan d 
                        JOIN produk_variasi v ON d.id_variasi = v.id 
                        JOIN produk_induk pi ON v.id_produk_induk = pi.id
                        WHERE d.id_penjualan = ?
                    ");
                    $stmtItems->execute([$idPesanan]);
                    $items = $stmtItems->fetchAll();

                    foreach ($items as $item) {
                        $idVar = $item['id_variasi'];
                        $qtyBox = intval($item['qty']);
                        $rasio = intval($item['rasio_konversi']) ?: 1;
                        $qtyPotong = $qtyBox * $rasio;
                        $satBesar = $item['satuan_besar'];

                        $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qtyPotong, $idVar]);

                        // Restorasi FEFO dan SN berdasarkan catatan_logistik
                        $catatan = trim($item['catatan_logistik'] ?? '');
                        if (!empty($catatan)) {
                            if ($item['kategori'] === 'Obat') {
                                preg_match_all('/([^\(,\s]+)\s*\((?:.*=\s*)?(\d+)(?:x|[^\)]*)?\)/i', $catatan, $matches, PREG_SET_ORDER);
                                if (empty($matches)) {
                                    preg_match_all('/(.+?)\s*\((\d+)x\)/', $catatan, $matches, PREG_SET_ORDER);
                                }
                                foreach ($matches as $match) {
                                    $batchNo = trim($match[1]);
                                    $batchQty = intval($match[2]);
                                    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa + ? WHERE id_variasi = ? AND no_batch = ?")->execute([$batchQty, $idVar, $batchNo]);
                                }
                            } elseif ($item['kategori'] === 'Alat Kesehatan') {
                                $sns = array_filter(array_map('trim', explode(',', $catatan)));
                                foreach ($sns as $snStr) {
                                    $pdo->prepare("UPDATE unit_serial SET status = 'Tersedia', id_penjualan = NULL WHERE id_variasi = ? AND serial_number = ?")->execute([$idVar, $snStr]);
                                }
                            }
                        }
                    }
                }
                
                // Process Anomalies Log
                if (isset($_POST['anomali_log']) && is_array($_POST['anomali_log'])) {
                    foreach ($_POST['anomali_log'] as $anomaliStr) {
                        $parts = explode('|', $anomaliStr);
                        if (count($parts) >= 4) {
                            $idVarA = intval($parts[0]);
                            $batchSeharusnya = trim($parts[1]);
                            $batchScan = trim($parts[2]);
                            $qtyAn = intval($parts[3]);
                            $kategoriAn = isset($parts[4]) ? trim($parts[4]) : 'Obat';
                            
                            if ($kategoriAn === 'Alat Kesehatan') {
                                $pdo->prepare("UPDATE unit_serial SET status = 'Tersedia', id_penjualan = NULL WHERE serial_number = ? AND id_variasi = ?")->execute([$batchSeharusnya, $idVarA]);
                                $pdo->prepare("UPDATE unit_serial SET status = 'Terjual', id_penjualan = ? WHERE serial_number = ? AND id_variasi = ?")->execute([$idPesanan, $batchScan, $idVarA]);
                                $pdo->prepare("UPDATE detail_penjualan SET catatan_logistik = ? WHERE id_penjualan = ? AND id_variasi = ?")->execute([$batchScan, $idPesanan, $idVarA]);
                            } else {
                                $pdo->prepare("INSERT INTO log_anomali_fefo (id_penjualan, id_user, id_variasi, batch_diambil, batch_seharusnya, qty) VALUES (?, ?, ?, ?, ?, ?)")->execute([$idPesanan, $_SESSION['id_user'] ?? 1, $idVarA, $batchScan, $batchSeharusnya, $qtyAn]);
                            }
                        }
                    }
                }

                $pdo->commit();
                $msg = "Status pesanan ID #$idPesanan berhasil diubah menjadi '$newStatus'.";
                $msgType = 'success';
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal mengubah status: " . $e->getMessage();
            $msgType = 'error';
        }
    }
}

// Fetch orders for active branch (or all branches for super_admin)
$query = "
    SELECT p.*, 'Pusat' AS nama_cabang, u.nama_lengkap AS nama_kasir
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id
";
$query .= " ORDER BY p.id DESC LIMIT 50";

$orders = $pdo->query($query)->fetchAll();

$orderIds = array_column($orders, 'id');
$orderDetails = [];
if (!empty($orderIds)) {
    $inClause = implode(',', $orderIds);
    $qDetails = $pdo->query("
        SELECT dp.*, pv.nama_variasi, pi.nama_produk, pi.kategori,
               pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
               (SELECT CONCAT(DATE_FORMAT(sb.tgl_exp, '%d/%m/%Y'), '|', DATEDIFF(sb.tgl_exp, CURDATE())) 
                FROM stok_batch sb 
                WHERE sb.id_variasi = dp.id_variasi 
                  AND sb.no_batch = SUBSTRING_INDEX(dp.catatan_logistik, ' ', 1) 
                LIMIT 1) AS exp_info,
               (SELECT GROUP_CONCAT(CONCAT(sb.no_batch, '::', DATE_FORMAT(sb.tgl_exp, '%d/%m/%Y'), '|', DATEDIFF(sb.tgl_exp, CURDATE())) SEPARATOR ';;') 
                FROM stok_batch sb 
                WHERE sb.id_variasi = dp.id_variasi) AS all_exp_info,
               (SELECT GROUP_CONCAT(CONCAT(sb.no_batch, '|', sb.tgl_exp) SEPARATOR ',') 
                FROM stok_batch sb 
                WHERE sb.id_variasi = dp.id_variasi AND sb.tgl_exp < CURDATE()) AS expired_batches,
               CASE 
                   WHEN pi.kategori = 'Obat' THEN (SELECT GROUP_CONCAT(DISTINCT no_batch SEPARATOR ',') FROM stok_batch WHERE id_variasi = dp.id_variasi AND no_batch NOT LIKE '%.%')
                   WHEN pi.kategori = 'Alat Kesehatan' THEN (SELECT GROUP_CONCAT(serial_number SEPARATOR ',') FROM unit_serial WHERE id_variasi = dp.id_variasi)
               END AS valid_batches
        FROM detail_penjualan dp
        JOIN produk_variasi pv ON dp.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE dp.id_penjualan IN ($inClause)
    ")->fetchAll();
    foreach ($qDetails as $d) {
        $orderDetails[$d['id_penjualan']][] = $d;
    }
}

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

<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-5 gap-4">
    <div>
        <h2 class="text-base font-bold text-zcTxt">Daftar Transaksi Pesanan</h2>
        <p class="text-xs text-zcMut mt-0.5">Total: <span id="lbl_count"><?= count($orders) ?></span> pesanan terbaru</p>
    </div>
    
    <div class="bg-white border border-zcBrd p-1 rounded-xl flex shadow-sm inline-flex">
        <button onclick="filterType('semua', this)" class="tab-btn px-4 py-2 text-xs font-bold rounded-lg transition bg-slate-800 text-white shadow-md shadow-slate-800/20" data-target="semua">Semua</button>
        <button onclick="filterType('ecommerce', this)" class="tab-btn px-4 py-2 text-xs font-bold rounded-lg transition text-slate-500 hover:text-zcTxt hover:bg-slate-50" data-target="ecommerce">E-Commerce</button>
        <button onclick="filterType('pos', this)" class="tab-btn px-4 py-2 text-xs font-bold rounded-lg transition text-slate-500 hover:text-zcTxt hover:bg-slate-50" data-target="pos">Kasir POS</button>
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
                        <tr class="order-row hover:bg-slate-50/60 transition cursor-pointer" data-tipe="<?= htmlspecialchars($o['tipe_transaksi']) ?>" onclick="toggleDetail(<?= $o['id'] ?>)">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <svg id="icon-<?= $o['id'] ?>" class="w-4 h-4 text-zcMut transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                    <div>
                                        <span class="font-bold text-zcTxt font-mono block"><?= htmlspecialchars($o['no_invoice']) ?></span>
                                        <span class="text-[10px] text-zcMut"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></span>
                                    </div>
                                </div>
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
                                        <select name="status_pesanan" onchange="handleStatusChange(this, <?= $o['id'] ?>)" data-original="<?= $o['status_pesanan'] ?>" class="text-[11px] border border-zcBrd rounded-lg px-2 py-1 bg-white font-medium focus:outline-none focus:border-zc">
                                            <option value="<?= $o['status_pesanan'] ?>" selected><?= $o['status_pesanan'] ?></option>
                                            
                                            <?php if ($o['status_pesanan'] === 'Menunggu Pembayaran'): ?>
                                                <option value="Dibatalkan">Dibatalkan</option>
                                            <?php elseif ($o['status_pesanan'] === 'Diproses'): ?>
                                                <?php if ($o['metode_pengambilan'] === 'Pick-up'): ?>
                                                    <option value="Siap Diambil">Siap Diambil</option>
                                                <?php else: ?>
                                                    <option value="Dikirim">Dikirim</option>
                                                <?php endif; ?>
                                                <option value="Dibatalkan">Dibatalkan (Refund)</option>
                                            <?php elseif (in_array($o['status_pesanan'], ['Dikirim', 'Siap Diambil'])): ?>
                                                <option value="Selesai">Selesai (Force)</option>
                                            <?php endif; ?>
                                        </select>
                                    </form>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= $o['no_invoice'] ?>" target="_blank"
                                        class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-zcBrd rounded-lg transition">Cetak</a>
                                </div>
                            </td>
                        </tr>
                        <tr id="detail-<?= $o['id'] ?>" class="hidden bg-slate-50/50">
                            <td colspan="7" class="px-8 py-4 border-t border-zcBrd/40 shadow-inner">
                                <div class="text-xs">
                                    <h4 class="font-bold text-zcTxt mb-2 uppercase tracking-wide border-b border-zcBrd pb-1 inline-block">Detail Item Pesanan</h4>
                                    <ul class="space-y-2 mt-2">
                                         <?php $items = $orderDetails[$o['id']] ?? []; ?>
                                        <?php foreach($items as $it): 
                                            $satBesar = !empty($it['satuan_besar']) ? $it['satuan_besar'] : 'Karton';
                                            $satKecil = !empty($it['satuan_kecil']) ? $it['satuan_kecil'] : 'Botol';
                                            $rasio = max(1, intval($it['rasio_konversi'] ?? 1));
                                            $isEcommerce = in_array(strtolower($o['tipe_transaksi'] ?? ''), ['ecommerce', 'online']);

                                            // Format badge Qty: Tampilkan kedua satuan untuk transaksi e-commerce (satuan besar)
                                            if ($isEcommerce && $rasio > 1) {
                                                $totalPcs = $it['qty'] * $rasio;
                                                $qtyLabel = $it['qty'] . ' ' . $satBesar . ' (' . number_format($totalPcs) . ' ' . $satKecil . ')';
                                            } else {
                                                $qtyLabel = 'x' . $it['qty'] . ' ' . $satKecil;
                                            }

                                            // Format label fisik logistik agar jelas (misal 1 Karton = 50 Botol)
                                            $rawLogistik = trim($it['catatan_logistik'] ?? '');
                                            $displayLogistik = $rawLogistik;
                                            $cleanBarcodeInstruksi = $rawLogistik ? explode(' ', $rawLogistik)[0] : '';
                                            $cleanBarcodeInstruksi = trim(explode('(', $cleanBarcodeInstruksi)[0]);

                                            // Bangun map expiry per no_batch
                                            $expMap = [];
                                            if (!empty($it['all_exp_info'])) {
                                                foreach (explode(';;', $it['all_exp_info']) as $expRow) {
                                                    $expPair = explode('::', $expRow);
                                                    if (count($expPair) === 2) {
                                                        $expMap[strtoupper(trim($expPair[0]))] = trim($expPair[1]);
                                                    }
                                                }
                                            }

                                            // Ekstrak semua kode batch bersih tanpa embel-embel keterangan satuan
                                            $cleanBatchesList = [];
                                            $packItems = [];
                                            if (!empty($rawLogistik)) {
                                                $parts = array_map('trim', explode(',', $rawLogistik));
                                                $formattedParts = [];
                                                foreach ($parts as $pPart) {
                                                    $bCode = '';
                                                    $qtyTxt = '';
                                                    if (preg_match('/^([^\(]+)\s*\((\d+)x\)$/i', $pPart, $mLog)) {
                                                        $bCode = trim($mLog[1]);
                                                        $pcsVal = intval($mLog[2]);
                                                        if ($isEcommerce && $rasio > 1 && $pcsVal >= $rasio) {
                                                            $dusVal = floor($pcsVal / $rasio);
                                                            $qtyTxt = "{$dusVal} {$satBesar} = {$pcsVal} {$satKecil}";
                                                            $formattedParts[] = $bCode . " ({$qtyTxt})";
                                                        } else {
                                                            $qtyTxt = "{$pcsVal} {$satKecil}";
                                                            $formattedParts[] = $bCode . " ({$qtyTxt})";
                                                        }
                                                    } elseif (preg_match('/^([^\(]+)\s*\((.*?)\)$/i', $pPart, $mLog)) {
                                                        $bCode = trim($mLog[1]);
                                                        $qtyTxt = trim($mLog[2]);
                                                        $formattedParts[] = $pPart;
                                                    } else {
                                                        $bCode = trim($pPart);
                                                        if ($isEcommerce && $rasio > 1) {
                                                            $totalPcs = $it['qty'] * $rasio;
                                                            $qtyTxt = "{$it['qty']} {$satBesar} = {$totalPcs} {$satKecil}";
                                                            $formattedParts[] = $pPart . " ({$qtyTxt})";
                                                        } else {
                                                            $qtyTxt = "{$it['qty']} {$satKecil}";
                                                            $formattedParts[] = $pPart;
                                                        }
                                                    }

                                                    $cb = trim(preg_replace('/\s*\(.*?\)/', '', $bCode));
                                                    $cb = trim(preg_replace('/\[.*?\]/', '', $cb));
                                                    if ($cb) {
                                                        $cleanBatchesList[] = $cb;
                                                        $bExp = $expMap[strtoupper($cb)] ?? ($it['exp_info'] ?? '');
                                                        $packItems[] = [
                                                            'batch' => $cb,
                                                            'qtyText' => $qtyTxt,
                                                            'instruksi' => $cb . ($qtyTxt ? " ({$qtyTxt})" : ""),
                                                            'expInfo' => $bExp
                                                        ];
                                                    }
                                                }
                                                $displayLogistik = implode(', ', $formattedParts);
                                            }

                                            if (empty($packItems) && !empty($cleanBarcodeInstruksi)) {
                                                $bExp = $expMap[strtoupper($cleanBarcodeInstruksi)] ?? ($it['exp_info'] ?? '');
                                                $packItems[] = [
                                                    'batch' => $cleanBarcodeInstruksi,
                                                    'qtyText' => '',
                                                    'instruksi' => $cleanBarcodeInstruksi,
                                                    'expInfo' => $bExp
                                                ];
                                            }
                                            $cleanBatchesAttr = implode(',', $cleanBatchesList);
                                        ?>
                                            <li class="item-packing flex flex-col bg-white p-2.5 rounded-lg border border-zcBrd shadow-sm" 
                                                data-id-variasi="<?= $it['id_variasi'] ?>" 
                                                data-qty="<?= $it['qty'] ?>"
                                                data-batches="<?= htmlspecialchars(strtolower($it['valid_batches'] ?? '')) ?>"
                                                data-kategori="<?= htmlspecialchars($it['kategori']) ?>"
                                                data-instruksi="<?= htmlspecialchars($displayLogistik ?: $cleanBarcodeInstruksi) ?>"
                                                data-clean-batches="<?= htmlspecialchars(strtolower($cleanBatchesAttr ?: $cleanBarcodeInstruksi)) ?>"
                                                data-expired-batches="<?= htmlspecialchars(strtolower($it['expired_batches'] ?? '')) ?>"
                                                data-exp-info="<?= htmlspecialchars($it['exp_info'] ?? '') ?>"
                                                data-pack-items="<?= htmlspecialchars(json_encode($packItems)) ?>">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-bold text-slate-700"><?= htmlspecialchars($it['nama_produk'] . ' - ' . $it['nama_variasi']) ?></span>
                                                    <span class="font-semibold text-slate-700 <?= ($isEcommerce && $rasio > 1) ? 'bg-blue-50 border-blue-200 text-blue-800' : 'bg-slate-100 border-slate-200 text-slate-600' ?> px-2.5 py-0.5 rounded border"><?= htmlspecialchars($qtyLabel) ?></span>
                                                </div>
                                                <?php if(!empty($displayLogistik)): ?>
                                                    <?php 
                                                        $expTxt = '';
                                                        if (!empty($it['exp_info'])) {
                                                            $expParts = explode('|', $it['exp_info']);
                                                            $expTxt = "Exp: " . ($expParts[0] ?? '-') . " / Sisa " . ($expParts[1] ?? '0') . " Hari";
                                                        }
                                                    ?>
                                                    <div class="mt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2 bg-rose-50 border border-rose-200 rounded-md">
                                                        <div class="flex items-center gap-1.5">
                                                            <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                                            <span class="text-[12px] font-bold text-rose-700 tracking-tight">AMBIL FISIK: <span class="font-mono bg-white px-1.5 py-0.5 rounded border border-rose-300"><?= htmlspecialchars($displayLogistik) ?></span></span>
                                                        </div>
                                                        <?php if ($expTxt): ?>
                                                            <span class="text-[11px] text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 w-fit"><?= $expTxt ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <!-- Tombol Aksi Lanjutkan Status Langsung di Detail Pesanan -->
                                    <div class="mt-4 pt-3 border-t border-zcBrd flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="text-[11px] text-zcMut">
                                            Metode Pengambilan: <strong class="text-slate-800"><?= htmlspecialchars($o['metode_pengambilan']) ?></strong> • 
                                            Layanan: <strong class="text-slate-800"><?= htmlspecialchars($o['kurir'] ?? 'Direct') ?> (<?= htmlspecialchars($o['layanan'] ?? 'Standard') ?>)</strong>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <?php if ($o['status_pesanan'] === 'Diproses'): ?>
                                                <?php if ($o['metode_pengambilan'] === 'Pick-up'): ?>
                                                    <button type="button" onclick="triggerStatusFromDetail(<?= $o['id'] ?>, 'Siap Diambil')" 
                                                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                                        Lanjutkan: Siapkan Pesanan untuk Ambil di Toko
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" onclick="triggerStatusFromDetail(<?= $o['id'] ?>, 'Dikirim')" 
                                                        class="px-3.5 py-2 bg-zc hover:bg-zcHv text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                                        Lanjutkan: Siapkan Pengiriman Pesanan (Kirim)
                                                    </button>
                                                <?php endif; ?>
                                            <?php elseif (in_array($o['status_pesanan'], ['Dikirim', 'Siap Diambil'])): ?>
                                                <button type="button" onclick="triggerStatusFromDetail(<?= $o['id'] ?>, 'Selesai')" 
                                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                                    Selesaikan Pesanan
                                                </button>
                                            <?php elseif ($o['status_pesanan'] === 'Menunggu Pembayaran'): ?>
                                                <button type="button" onclick="triggerStatusFromDetail(<?= $o['id'] ?>, 'Dibatalkan')" 
                                                    class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                                    Batalkan Pesanan
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Fix live camera viewport - menghilangkan potongan & garis hitam samping */
#pack_camera_view { width: 100% !important; overflow: hidden !important; border-radius: 0.75rem !important; background: #0f172a !important; }
#pack_camera_view video {
    width: 100% !important;
    height: 100% !important;
    min-height: 280px !important;
    max-height: 360px !important;
    object-fit: cover !important;
    border-radius: 0.75rem !important;
    display: block !important;
    margin: 0 auto !important;
}
#pack_camera_view canvas { border-radius: 0.75rem !important; }
#pack_camera_view #qr-shaded-region { border-radius: 0.75rem !important; }
</style>

<!-- Modal Live Barcode Scanner Kamera untuk Verifikasi Packing E-Commerce -->
<div id="pack_camera_modal" class="fixed inset-0 z-[10000] hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-200 border border-slate-200" id="pack_camera_modal_content">
        <!-- Header Modal -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-4 flex items-center justify-between text-white">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm leading-tight">Live Barcode Scanner Kamera (E-Commerce)</h3>
                    <p class="text-[11px] text-blue-100">Scan barcode batch obat / nomor seri (SN) langsung via webcam</p>
                </div>
            </div>
            <button type="button" onclick="closePackCameraModal()" class="text-blue-100 hover:text-white transition p-1">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <!-- Camera Viewport Box -->
            <div class="relative bg-slate-900 rounded-xl overflow-hidden shadow-inner border-2 border-slate-700 min-h-[250px] flex items-center justify-center">
                <div id="pack_camera_view" class="w-full"></div>
                <div id="pack_camera_loading" class="text-center p-4 text-white text-xs">
                    <div class="inline-block w-8 h-8 border-4 border-white/20 border-t-white rounded-full animate-spin mb-2"></div>
                    <p>Menghubungkan ke kamera...</p>
                </div>
            </div>

            <!-- Device Selector & Status Badge -->
            <div class="flex items-center justify-between gap-3 text-xs">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Perangkat Kamera:</label>
                    <select id="pack_camera_select" onchange="changePackCamera(this.value)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 font-semibold focus:outline-none focus:border-indigo-500">
                        <option value="">Deteksi kamera...</option>
                    </select>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" id="pack_scan_status_badge">
                        🟢 Scanner Aktif
                    </span>
                </div>
            </div>

            <!-- Checklist Status Barang yang Harus Di-scan -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                <div class="text-[11px] font-bold text-slate-700 mb-2 flex items-center justify-between">
                    <span>Daftar Verifikasi Barang:</span>
                    <span id="pack_progress_text" class="text-indigo-600 font-extrabold text-[11px]">0 / 0 Selesai</span>
                </div>
                <div id="pack_camera_items_list" class="space-y-1.5 max-h-32 overflow-y-auto pr-1 text-xs">
                    <!-- Dinamis terisi dari pendingItems -->
                </div>
            </div>

            <!-- Last Scanned Info Card -->
            <div id="pack_last_scanned_box" class="p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Hasil Scan Terakhir:</span>
                    <span id="pack_last_scanned_code" class="font-mono font-bold text-sm text-slate-800">-</span>
                </div>
                <span id="pack_last_scanned_time" class="text-[11px] text-slate-500 font-medium">Belum ada scan</span>
            </div>
        </div>

        <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
            <span class="text-[11px] text-slate-500">Arahkan barcode kardus / SN ke kotak kamera</span>
            <button type="button" onclick="closePackCameraModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 transition">
                Tutup Scanner
            </button>
        </div>
    </div>
</div>

<script>
function filterType(type, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.className = 'tab-btn px-4 py-2 text-xs font-bold rounded-lg transition text-slate-500 hover:text-zcTxt hover:bg-slate-50';
    });
    btn.className = 'tab-btn px-4 py-2 text-xs font-bold rounded-lg transition bg-slate-800 text-white shadow-md shadow-slate-800/20';

    let count = 0;
    document.querySelectorAll('.order-row').forEach(row => {
        const id = row.getAttribute('onclick').match(/\d+/)[0];
        const detailRow = document.getElementById('detail-' + id);
        if (type === 'semua' || row.dataset.tipe === type) {
            row.style.display = '';
            count++;
        } else {
            row.style.display = 'none';
            if (detailRow) detailRow.classList.add('hidden');
        }
    });
    document.getElementById('lbl_count').innerText = count;
}

function toggleDetail(id) {
    const detailRow = document.getElementById('detail-' + id);
    const icon = document.getElementById('icon-' + id);
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
    } else {
        detailRow.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

// LOGIKA PACKING & VERIFIKASI BARCODE E-COMMERCE
let activeOrderId = null;
let activeForm = null;
let pendingItems = [];

function handleStatusChange(selectEl, orderId) {
    var newStatus = selectEl.value;
    var origStatus = selectEl.getAttribute('data-original');
    
    if (newStatus === 'Dikirim' || newStatus === 'Siap Diambil') {
        activeOrderId = orderId;
        activeForm = selectEl.closest('form');
        
        pendingItems = [];
        var ul = document.querySelector('#detail-' + orderId + ' ul');
        if (!ul) {
            alert('Detail item tidak ditemukan.');
            selectEl.value = origStatus;
            return;
        }
        
        var lis = ul.querySelectorAll('li.item-packing');
        lis.forEach(function(li) {
            var nama = li.querySelector('span.font-bold.text-slate-700').innerText;
            var packItemsAttr = li.getAttribute('data-pack-items');
            var packItemsData = [];
            if (packItemsAttr) {
                try {
                    packItemsData = JSON.parse(packItemsAttr);
                } catch(e) {}
            }
            
            var id_variasi = li.getAttribute('data-id-variasi');
            var totalQty = li.getAttribute('data-qty');
            var validBatches = (li.getAttribute('data-batches') || "").split(",").map(s => s.trim().toLowerCase()).filter(Boolean);
            var expiredBatches = (li.getAttribute('data-expired-batches') || '').toLowerCase().split(',').map(s => s.trim()).filter(Boolean);
            var kategori = li.getAttribute('data-kategori') || "";
            
            if (packItemsData && packItemsData.length > 0) {
                packItemsData.forEach(function(pi) {
                    var cb = (pi.batch || '').trim();
                    pendingItems.push({
                        id_variasi: id_variasi,
                        qty: totalQty,
                        nama: nama,
                        instruksi: pi.instruksi || cb,
                        cleanBatch: cb,
                        cleanBatches: cb ? [cb.toLowerCase()] : [],
                        qtyText: pi.qtyText || '',
                        expiredBatches: expiredBatches,
                        expInfo: pi.expInfo || '',
                        validBatches: validBatches,
                        kategori: kategori,
                        verified: false,
                        scannedBatch: null
                    });
                });
            } else {
                var instruksi = li.getAttribute('data-instruksi') || '';
                var cleanBatches = (li.getAttribute('data-clean-batches') || '').toLowerCase().split(',').map(s => s.trim()).filter(Boolean);
                if (!instruksi) {
                    var insSpan = li.querySelector('span.text-rose-700');
                    if (insSpan) {
                        instruksi = insSpan.innerText.replace(/\(AMBIL FISIK:|\(INSTRUKSI AMBIL FISIK:|AMBIL FISIK:/gi, '').replace(/\)/g, '').trim();
                    }
                }
                pendingItems.push({
                    id_variasi: id_variasi,
                    qty: totalQty,
                    nama: nama,
                    instruksi: instruksi,
                    cleanBatch: cleanBatches[0] || '',
                    cleanBatches: cleanBatches,
                    qtyText: '',
                    expiredBatches: expiredBatches,
                    expInfo: li.getAttribute('data-exp-info') || '',
                    validBatches: validBatches,
                    kategori: kategori,
                    verified: false,
                    scannedBatch: null
                });
            }
        });
        
        showPackingModal(newStatus);
        selectEl.value = origStatus; // reset sementara
    } else {
        if(confirm('Ubah status pesanan ini?')) selectEl.form.submit();
        else selectEl.value = origStatus;
    }
}

function triggerStatusFromDetail(orderId, targetStatus) {
    var selectEl = document.querySelector('select[name="status_pesanan"][onchange*="' + orderId + '"]');
    if (selectEl) {
        selectEl.value = targetStatus;
        handleStatusChange(selectEl, orderId);
    }
}

function showPackingModal(targetStatus) {
    let html = `<div class="text-left">`;
    html += `<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-900">Validasi Fisik Paket (Logika FEFO)</h4>
                        <p class="text-xs text-indigo-700 mt-0.5">Wajib men-scan barcode batch kardus terdekat sebelum pesanan <b>${targetStatus}</b>.</p>
                    </div>
                </div>
             </div>`;
             
    html += `<div class="space-y-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar mb-5">`;
    
    pendingItems.forEach((it, idx) => {
        let isVerified = it.verified;
        let borderCls = isVerified ? "border-emerald-500 bg-emerald-50/30" : "border-slate-200 bg-white shadow-sm";
        let iconHtml = isVerified 
            ? `<div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>`
            : `<div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0"><span class="text-slate-400 text-xs font-bold">${idx + 1}</span></div>`;
            
        // Pisahkan nama instruksi dan keterangan Qty
        let cleanInstruksi = it.cleanBatch || it.instruksi.split('(')[0].replace(/\[.*?\]/g, "").trim(); 
        let qtyText = it.qtyText ? `<span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded text-[10px] ml-1 font-bold">${it.qtyText}</span>` : "";
        
        let expBadge = "";
        if (it.expInfo) {
            let expParts = it.expInfo.split('|');
            expBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">⏳ Exp: ${expParts[0]} (${expParts[1]} Hari)</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-200 uppercase">Rekomendasi FEFO</span>`;
        }
            
        let rec = cleanInstruksi ? `<div class="flex items-center gap-1.5 flex-wrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-900 border border-blue-200 tracking-wide font-mono">📦 ${cleanInstruksi}</span> ${qtyText} ${expBadge}</div>` : "<span class=\"text-slate-400 text-xs italic\">Non-Batch</span>";

        let scannedDisplay = "";
        if (isVerified && it.scannedBatch) {
            let isDiff = it.scannedBatch.toLowerCase() !== cleanInstruksi.toLowerCase();
            let isAnomaly = isDiff && it.kategori !== 'Alat Kesehatan';
            let badgeClass = isAnomaly ? "bg-rose-100 text-rose-800 border-rose-300" : "bg-emerald-100 text-emerald-800 border-emerald-300";
            let warningIcon = isAnomaly ? `<svg class="w-3 h-3 text-rose-600 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>` : "";
            let anomalyText = isAnomaly ? `<span class="text-[10px] text-rose-600 font-bold ml-1">(TIDAK SESUAI FEFO)</span>` : (isDiff ? `<span class="text-[10px] text-emerald-600 font-bold ml-1">(DITUKAR)</span>` : "");
            
            scannedDisplay = `<div class="mt-2 flex items-center gap-2">
                <span class="text-[11px] text-slate-500 font-medium">Fisik Ter-scan:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold ${badgeClass} border tracking-wide font-mono">${warningIcon}${it.scannedBatch}</span>
                ${anomalyText}
            </div>`;
        }

        html += `
        <div class="flex items-start gap-3 p-3 rounded-xl border transition-all duration-300 ${borderCls}">
            ${iconHtml}
            <div class="flex-1 min-w-0">
                <h4 class="text-[13px] font-bold text-slate-800 leading-tight truncate">${it.nama}</h4>
                <div class="mt-1.5 flex items-center gap-2">
                    <span class="text-[11px] text-slate-500 font-medium">Rekomendasi:</span>
                    ${rec}
                </div>
                ${scannedDisplay}
            </div>
            <div class="shrink-0 text-right">
                <span class="block text-xs font-bold ${isVerified ? "text-emerald-600" : "text-slate-400"}">${isVerified ? "SELESAI" : "MENUNGGU"}</span>
            </div>
        </div>`;
    });
    html += `</div>`;
    
    html += `<div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" id="pack-scanner" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white transition-colors placeholder-slate-400" placeholder="Arahkan Scanner / Ketik Barcode di sini..." autocomplete="off">
                </div>
                <button type="button" onclick="openPackCameraScanner('${targetStatus}')" class="px-4 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-md shadow-indigo-500/20 active:scale-95 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                    <span>Scan Kamera</span>
                </button>
             </div>`;
             
    html += `</div>`;
    
    let allVerified = pendingItems.every(i => i.verified);
    
    Swal.fire({
        html: html,
        width: 650,
        showCancelButton: true,
        showConfirmButton: allVerified,
        confirmButtonText: "Selesaikan Packing",
        cancelButtonText: "Batal",
        confirmButtonColor: "#10b981",
        allowOutsideClick: false,
        didOpen: () => {
            let inp = document.getElementById("pack-scanner");
            if (inp) {
                inp.focus();
                inp.addEventListener("keypress", function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        let val = this.value.trim().toLowerCase();
                        this.value = "";
                        if (val) handlePackScan(val, targetStatus, false);
                    }
                });
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let selectEl = activeForm.querySelector('select[name="status_pesanan"]');
            if (selectEl) {
                selectEl.value = targetStatus;
            }
            activeForm.submit();
        }
    });
}

// =========================================================================
// WEBCAM SCANNER UNTUK PACKING E-COMMERCE (html5-qrcode)
// =========================================================================
let packHtml5QrCode = null;
let isPackCameraRunning = false;
let packLastBarcode = '';
let packLastTimestamp = 0;
let packCurrentTargetStatus = '';

function updatePackCameraItemsList() {
    let listEl = document.getElementById('pack_camera_items_list');
    let progEl = document.getElementById('pack_progress_text');
    if (!listEl) return;
    
    let verifiedCount = 0;
    let html = '';
    pendingItems.forEach((it) => {
        if (it.verified) verifiedCount++;
        let icon = it.verified ? '✅' : '⏳';
        let cls = it.verified ? 'text-emerald-800 font-bold bg-emerald-50 border-emerald-300' : 'text-slate-700 bg-white border-slate-200';
        let targetLabel = it.cleanBatch ? (it.cleanBatch + (it.qtyText ? ' [' + it.qtyText + ']' : '')) : (it.instruksi || 'Batch/SN');
        let detail = it.verified ? `[Selesai: ${it.scannedBatch}]` : `[Target: ${targetLabel}]`;
        html += `<div class="p-2 rounded-lg border flex items-center justify-between gap-2 ${cls}">
            <span class="truncate flex-1 font-semibold">${icon} ${it.nama}</span>
            <span class="font-mono text-[10.5px] ml-2 shrink-0 font-bold">${detail}</span>
        </div>`;
    });
    listEl.innerHTML = html;
    if (progEl) {
        progEl.innerText = `${verifiedCount} / ${pendingItems.length} Selesai`;
    }
}

async function openPackCameraScanner(targetStatus) {
    packCurrentTargetStatus = targetStatus;
    
    // Tutup popup Swal packing sementara
    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
        Swal.close();
    }
    
    let modal = document.getElementById('pack_camera_modal');
    let content = document.getElementById('pack_camera_modal_content');
    updatePackCameraItemsList();
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
    
    await startPackCamera();
}

async function closePackCameraModal() {
    let modal = document.getElementById('pack_camera_modal');
    let content = document.getElementById('pack_camera_modal_content');
    
    if (packHtml5QrCode && isPackCameraRunning) {
        try {
            await packHtml5QrCode.stop();
        } catch(e) {}
        isPackCameraRunning = false;
    }
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        showPackingModal(packCurrentTargetStatus);
    }, 200);
}

async function startPackCamera(cameraId = null) {
    let loading = document.getElementById('pack_camera_loading');
    let badge = document.getElementById('pack_scan_status_badge');
    if (loading) loading.classList.remove('hidden');
    
    try {
        if (typeof Html5Qrcode === 'undefined') {
            throw new Error('Library Html5Qrcode belum dimuat.');
        }
        if (!packHtml5QrCode) {
            packHtml5QrCode = new Html5Qrcode("pack_camera_view");
        }
        
        const devices = await Html5Qrcode.getCameras();
        let select = document.getElementById('pack_camera_select');
        if (select && devices && devices.length) {
            select.innerHTML = '';
            devices.forEach((dev, idx) => {
                let opt = document.createElement('option');
                opt.value = dev.id;
                opt.text = dev.label || `Kamera ${idx + 1}`;
                if (cameraId && dev.id === cameraId) opt.selected = true;
                select.appendChild(opt);
            });
        }
        
        const chosenCamera = cameraId || (devices && devices.length > 0 ? devices[0].id : { facingMode: "environment" });
        const config = {
            fps: 20,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                let w = Math.min(Math.floor(viewfinderWidth * 0.75), 280);
                let h = Math.min(Math.floor(viewfinderHeight * 0.65), 180);
                return { width: Math.max(w, 200), height: Math.max(h, 120) };
            }
        };
        
        await packHtml5QrCode.start(
            chosenCamera,
            config,
            (decodedText) => {
                onPackCameraSuccess(decodedText);
            },
            (error) => {}
        );
        
        isPackCameraRunning = true;
        if (loading) loading.classList.add('hidden');
        if (badge) {
            badge.innerText = '🟢 Kamera Aktif';
            badge.className = 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
        }
    } catch (err) {
        if (loading) loading.classList.add('hidden');
        if (badge) {
            badge.innerText = '🔴 Gagal Akses Kamera';
            badge.className = 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800';
        }
        Swal.fire({
            title: 'Akses Kamera Terkendala',
            text: 'Pastikan browser Anda mengizinkan akses webcam atau kamera. (' + err.message + ')',
            icon: 'warning'
        }).then(() => {
            closePackCameraModal();
        });
    }
}

async function changePackCamera(newDeviceId) {
    if (packHtml5QrCode && isPackCameraRunning) {
        await packHtml5QrCode.stop();
        isPackCameraRunning = false;
    }
    if (newDeviceId) {
        await startPackCamera(newDeviceId);
    }
}

function onPackCameraSuccess(code) {
    code = code.trim();
    let now = Date.now();
    if (code === packLastBarcode && (now - packLastTimestamp) < 1800) {
        return;
    }
    packLastBarcode = code;
    packLastTimestamp = now;
    
    // Suara Beep Synthesizer via Web Audio API
    try {
        var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        var osc = audioCtx.createOscillator();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        osc.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.12);
    } catch(e) {}
    
    let codeEl = document.getElementById('pack_last_scanned_code');
    let timeEl = document.getElementById('pack_last_scanned_time');
    if (codeEl) codeEl.innerText = code;
    if (timeEl) timeEl.innerText = new Date().toLocaleTimeString('id-ID');
    
    // Jalankan verifikasi barang
    handlePackScan(code.toLowerCase(), packCurrentTargetStatus, true);
}


function handlePackScan(val, targetStatus, fromCamera = false) {
    // KUNCI GANDA 1: Blokir Eceran untuk Pesanan E-Commerce
    if (val.includes(".")) {
        Swal.fire({
            title: "&#10060; BLOKIR FATAL!",
            html: `Pesanan E-Commerce <b>HANYA</b> melayani <b>Grosir (Kardus/Box)</b>.<br><br>
                   Anda men-scan barang <b>Eceran (Sub-batch .A)</b>.<br>
                   <i>Dilarang keras mem-packing barang eceran untuk pembeli online!</i>`,
            icon: "error"
        }).then(() => { 
            if (!fromCamera) showPackingModal(targetStatus); 
        });
        return;
    }

    // KUNCI GANDA 2: SAFETY GUARD - BLOKIR MUTLAK OBAT KADALUARSA!
    let expiredMatch = null;
    for (let i = 0; i < pendingItems.length; i++) {
        let expList = pendingItems[i].expiredBatches || [];
        for (let j = 0; j < expList.length; j++) {
            let parts = expList[j].split('|');
            let bCode = parts[0].trim().toLowerCase();
            let bExp = parts[1] || '';
            if (bCode === val || bCode.split('.')[0] === val) {
                expiredMatch = { batch: parts[0].toUpperCase(), exp: bExp, name: pendingItems[i].nama };
                break;
            }
        }
        if (expiredMatch) break;
    }

    if (expiredMatch) {
        Swal.fire({
            title: "⛔ OBAT KADALUARSA TERDETEKSI!",
            html: `Fisik batch yang Anda scan: <b>${expiredMatch.batch}</b> (${expiredMatch.name})<br><br>
                   Status: <span class="text-rose-600 font-extrabold text-sm">SUDAH KADALUARSA</span> (Exp: <b>${expiredMatch.exp}</b>).<br><br>
                   <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">
                   ⚠️ <b>SISTEM SAFETY GUARD E-COMMERCE:</b><br>
                   Dilarang keras mem-packing atau mengirimkan obat yang telah melewati masa kadaluarsa kepada pelanggan online demi keselamatan pasien dan regulasi BPOM.<br><br>
                   Sistem secara otomatis <b>MEMBLOKIR</b> batch ini. Silakan pisahkan kardus obat ini ke karantina dan ambil batch yang masih aktif/valid!</div>`,
            icon: "error",
            confirmButtonColor: "#e11d48",
            confirmButtonText: "Saya Mengerti (Ambil Batch Aktif)"
        }).then(() => { 
            if (!fromCamera) showPackingModal(targetStatus); 
        });
        return;
    }

    // Fallback Cek Real-time ke Database via check_barcode API
    fetch(`../api/check_barcode.php?barcode=${encodeURIComponent(val)}`)
        .then(r => r.json())
        .then(res => {
            if (res.found && res.is_expired) {
                Swal.fire({
                    title: "⛔ OBAT KADALUARSA TERDETEKSI!",
                    html: `Fisik batch yang Anda scan: <b>${res.no_batch}</b> (${res.nama})<br><br>
                           Status: <span class="text-rose-600 font-extrabold text-sm">SUDAH KADALUARSA</span> (Exp: <b>${res.tgl_exp}</b>).<br><br>
                           <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">
                           ⚠️ <b>SISTEM SAFETY GUARD E-COMMERCE:</b><br>
                           Dilarang keras mem-packing atau mengirimkan obat yang telah melewati masa kadaluarsa kepada pelanggan online demi keselamatan pasien dan regulasi BPOM.<br><br>
                           Sistem secara otomatis <b>MEMBLOKIR</b> batch ini. Silakan pisahkan kardus obat ini ke karantina dan ambil batch yang masih aktif/valid!</div>`,
                    icon: "error",
                    confirmButtonColor: "#e11d48",
                    confirmButtonText: "Saya Mengerti (Ambil Batch Aktif)"
                }).then(() => { 
                    if (!fromCamera) showPackingModal(targetStatus); 
                });
                return;
            }
            proceedPackingMatch(val, targetStatus, fromCamera);
        })
        .catch(() => {
            proceedPackingMatch(val, targetStatus, fromCamera);
        });
}

function proceedPackingMatch(val, targetStatus, fromCamera = false) {
    let matchIdx = -1;
    let anomalyIdx = -1;
    let wrongProductIdx = -1;

    // PASS 1: Cari target FEFO / clean batch yang cocok pada SEMUA item yang belum diverifikasi (Bisa scan acak)
    for (let i = 0; i < pendingItems.length; i++) {
        if (!pendingItems[i].verified) {
            let pureBatch = (pendingItems[i].cleanBatch || '').toLowerCase();
            let cleanBatches = pendingItems[i].cleanBatches || [];
            let allExpected = [pureBatch].concat(cleanBatches).filter(Boolean);
            
            if (allExpected.includes(val)) {
                matchIdx = i;
                break;
            }
        }
    }

    // Jika barcode ini sudah selesai diverifikasi sebelumnya di item lain
    if (matchIdx === -1) {
        let alreadyVerified = pendingItems.find(i => i.verified && (
            (i.scannedBatch && i.scannedBatch.toLowerCase() === val) || 
            (i.cleanBatch && i.cleanBatch.toLowerCase() === val)
        ));
        if (alreadyVerified) {
            let nextPending = pendingItems.find(i => !i.verified);
            let nextTarget = nextPending ? (nextPending.cleanBatch || nextPending.instruksi) : '';
            Swal.fire({
                title: "ℹ️ Sudah Terverifikasi",
                html: `Batch/SN <b>${val.toUpperCase()}</b> sudah selesai Anda scan sebelumnya.<br><br>Silakan lanjutkan men-scan batch berikutnya: <b>${nextTarget}</b>.`,
                icon: "info",
                confirmButtonColor: "#3b82f6"
            }).then(() => {
                if (!fromCamera) showPackingModal(targetStatus);
            });
            return;
        }
    }

    // PASS 2: Jika tidak ada exact match target FEFO, cari apakah barcode merupakan batch/SN valid untuk salah satu item (Anomali FEFO / Tukar Batch)
    if (matchIdx === -1) {
        for (let i = 0; i < pendingItems.length; i++) {
            if (!pendingItems[i].verified) {
                let validList = (pendingItems[i].validBatches || []).map(b => b.trim().toLowerCase()).filter(Boolean);
                if (validList.includes(val)) {
                    anomalyIdx = i;
                    break;
                }
            }
        }
    }

    // PASS 3: Jika barcode tidak terdaftar pada item pesanan mana pun yang tersisa
    if (matchIdx === -1 && anomalyIdx === -1) {
        for (let i = 0; i < pendingItems.length; i++) {
            if (!pendingItems[i].verified) {
                wrongProductIdx = i;
                break;
            }
        }
    }

    if (matchIdx !== -1) {
        pendingItems[matchIdx].verified = true;
        pendingItems[matchIdx].scannedBatch = val.toUpperCase();
        
        let allDone = pendingItems.every(i => i.verified);
        if (fromCamera && isPackCameraRunning) {
            updatePackCameraItemsList();
            if (allDone) {
                // Semua barang telah lengkap terverifikasi
                closePackCameraModal();
            } else {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 1600,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: `✅ Terverifikasi: ${val.toUpperCase()} (${pendingItems[matchIdx].nama})`
                });
            }
        } else {
            showPackingModal(targetStatus);
        }
    } else if (anomalyIdx !== -1) {
        if (pendingItems[anomalyIdx].kategori === 'Alat Kesehatan') {
            // SILENT ACCEPT UNTUK ALKES
            pendingItems[anomalyIdx].verified = true;
            pendingItems[anomalyIdx].scannedBatch = val.toUpperCase();
            
            let h = document.createElement("input");
            h.type = "hidden";
            h.name = "anomali_log[]";
            h.value = pendingItems[anomalyIdx].id_variasi + "|" + (pendingItems[anomalyIdx].cleanBatch || pendingItems[anomalyIdx].instruksi) + "|" + val.toUpperCase() + "|" + pendingItems[anomalyIdx].qty + "|" + pendingItems[anomalyIdx].kategori;
            activeForm.appendChild(h);

            let allDone = pendingItems.every(i => i.verified);
            if (fromCamera && isPackCameraRunning) {
                updatePackCameraItemsList();
                if (allDone) {
                    closePackCameraModal();
                } else {
                    const Toast = Swal.mixin({ toast: true, position: 'top', showConfirmButton: false, timer: 1600 });
                    Toast.fire({ icon: 'success', title: `SN Terverifikasi: ${val.toUpperCase()}` });
                }
            } else {
                showPackingModal(targetStatus);
            }
            return;
        }

        let titleAnomali = "&#9888;&#65039; TIDAK SESUAI FEFO!";
        let warnAnomali = "Apakah Anda yakin ingin MENGABAIKAN rekomendasi FEFO dan tetap mem-packing batch ini?";
            
        Swal.fire({
            title: titleAnomali,
            html: `Anda men-scan fisik <b>${val.toUpperCase()}</b>.<br><br>Padahal pesanan tercatat untuk: <b>${pendingItems[anomalyIdx].cleanBatch || pendingItems[anomalyIdx].instruksi}</b>.<br><br>${warnAnomali}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Tetap Lanjutkan (Abaikan)",
            cancelButtonText: "Batal",
            confirmButtonColor: "#d33",
            allowEnterKey: false,
            focusCancel: true
        }).then((res) => {
            if (res.isConfirmed) {
                pendingItems[anomalyIdx].verified = true;
                pendingItems[anomalyIdx].scannedBatch = val.toUpperCase();
                
                let h = document.createElement("input");
                h.type = "hidden";
                h.name = "anomali_log[]";
                h.value = pendingItems[anomalyIdx].id_variasi + "|" + (pendingItems[anomalyIdx].cleanBatch || pendingItems[anomalyIdx].instruksi) + "|" + val.toUpperCase() + "|" + pendingItems[anomalyIdx].qty;
                activeForm.appendChild(h);

                let allDone = pendingItems.every(i => i.verified);
                if (fromCamera && isPackCameraRunning) {
                    updatePackCameraItemsList();
                    if (allDone) closePackCameraModal();
                } else {
                    showPackingModal(targetStatus);
                }
            } else {
                if (!fromCamera) showPackingModal(targetStatus);
            }
        });
    } else if (wrongProductIdx !== -1) {
        Swal.fire({
            title: "&#10060; SALAH PRODUK!",
            html: `Anda men-scan barang yang <b>BERBEDA</b> dari daftar pesanan!<br><br>
                   Pesanan yang harus di-packing: <b>${pendingItems[wrongProductIdx].nama}</b><br>
                   Barcode yang di-scan: <b>${val.toUpperCase()}</b>`,
            icon: "error"
        }).then(() => { 
            if (!fromCamera) showPackingModal(targetStatus); 
        });
    } else {
        Swal.fire({
            icon: "error",
            title: "Tidak Ditemukan",
            text: "Barcode/SN tidak cocok dengan barang manapun yang belum di-scan."
        }).then(() => {
            if (!fromCamera) showPackingModal(targetStatus);
        });
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/html5-qrcode.min.js"></script>

<?php layoutEnd(); ?>
