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
                        SELECT d.id_variasi, d.qty, v.rasio_konversi, v.satuan_besar 
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

                        $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();

                        $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)")
                            ->execute([$idVar, $noInvoice, $qtyPotong, $sisaStok, 'Konfirmasi Pengambilan di Toko (Pick-up)', $_SESSION['id_user'] ?? 1]);
                    }
                }

                // Jika status diubah menjadi Dikirim dan metode Kurir, catat sebagai Keluar di Kartu Stok
                if ($oldStatus !== 'Dikirim' && $newStatus === 'Dikirim' && $metodePengambilan === 'Kurir') {
                    $stmtItems = $pdo->prepare("
                        SELECT d.id_variasi, d.qty, v.rasio_konversi, v.satuan_besar 
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

                        $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();

                        $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)")
                            ->execute([$idVar, $noInvoice, $qtyPotong, $sisaStok, 'Pengiriman via Ekspedisi (Kurir)', $_SESSION['id_user'] ?? 1]);
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
                                preg_match_all('/(.+?)\s*\((\d+)x\)/', $catatan, $matches, PREG_SET_ORDER);
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
                        if (count($parts) === 4) {
                            $idVarA = intval($parts[0]);
                            $batchSeharusnya = trim($parts[1]);
                            $batchScan = trim($parts[2]);
                            $qtyAn = intval($parts[3]);
                            
                            $pdo->prepare("INSERT INTO log_anomali_fefo (id_penjualan, id_user, id_variasi, batch_diambil, batch_seharusnya, qty) VALUES (?, ?, ?, ?, ?, ?)")
                                ->execute([$idPesanan, $_SESSION['id_user'] ?? 1, $idVarA, $batchScan, $batchSeharusnya, $qtyAn]);
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
        SELECT dp.*, pv.nama_variasi, pi.nama_produk, pi.kategori
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
                                        <?php foreach($items as $it): ?>
                                            <li class="item-packing flex flex-col bg-white p-2.5 rounded-lg border border-zcBrd shadow-sm" 
                                                data-id-variasi="<?= $it['id_variasi'] ?>" 
                                                data-qty="<?= $it['qty'] ?>">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-bold text-slate-700"><?= htmlspecialchars($it['nama_produk'] . ' - ' . $it['nama_variasi']) ?></span>
                                                    <span class="font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">x<?= $it['qty'] ?></span>
                                                </div>
                                                <?php if(!empty($it['catatan_logistik'])): ?>
                                                    <div class="mt-2 flex items-start gap-2 p-2 bg-rose-50 border border-rose-200 rounded-md">
                                                        <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                                        <span class="text-[13px] font-bold text-rose-700 uppercase tracking-tight">(INSTRUKSI AMBIL FISIK: <?= htmlspecialchars($it['catatan_logistik']) ?>)</span>
                                                    </div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
            var instruksi = '';
            var insSpan = li.querySelector('span.text-rose-700');
            if (insSpan) {
                instruksi = insSpan.innerText.replace('(INSTRUKSI AMBIL FISIK:', '').replace(')', '').trim();
            }
            
            pendingItems.push({
                id_variasi: li.getAttribute('data-id-variasi'),
                qty: li.getAttribute('data-qty'),
                nama: nama,
                instruksi: instruksi,
                verified: false
            });
        });
        
        showPackingModal(newStatus);
        selectEl.value = origStatus; // reset sementara
    } else {
        if(confirm('Ubah status pesanan ini?')) selectEl.form.submit();
        else selectEl.value = origStatus;
    }
}

function showPackingModal(targetStatus) {
    let html = `<div style="text-align:left; font-size: 13px;">`;
    html += `<p class="mb-3 font-semibold text-gray-700">Silakan scan fisik barang yang akan dimasukkan ke paket:</p>`;
    html += `<ul class="space-y-2 mb-4" style="max-height: 250px; overflow-y: auto;">`;
    pendingItems.forEach((it) => {
        let badge = it.verified ? `<span class="text-emerald-600 font-bold">✅ Fisik Terverifikasi</span>` : `<span class="text-rose-600 font-bold">⚠️ Menunggu Scan Fisik</span>`;
        let rec = it.instruksi ? `<span class="text-blue-700 bg-blue-50 px-1 rounded border border-blue-200">${it.instruksi}</span>` : '<i>Bebas/Non-Batch</i>';
        
        html += `<li style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <div style="font-weight:bold">${it.nama}</div>
            <div style="font-size: 11px; margin-top:2px;">Target Batch/SN: ${rec}</div>
            <div style="margin-top: 5px">${badge}</div>
        </li>`;
    });
    html += `</ul>`;
    html += `<input type="text" id="pack-scanner" class="swal2-input !mt-0 !text-sm" placeholder="Scan Barcode / SN di sini..." autocomplete="off">`;
    html += `</div>`;

    let allVerified = pendingItems.every(i => i.verified);

    Swal.fire({
        title: 'Verifikasi Picking & Packing',
        html: html,
        width: 600,
        showCancelButton: true,
        showConfirmButton: allVerified,
        confirmButtonText: 'Selesaikan Packing (' + targetStatus + ')',
        cancelButtonText: 'Batal',
        allowOutsideClick: false,
        didOpen: () => {
            let inp = document.getElementById('pack-scanner');
            inp.focus();
            inp.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    let val = this.value.trim().toLowerCase();
                    this.value = '';
                    if (val) handlePackScan(val, targetStatus);
                }
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let sel = activeForm.querySelector('select[name="status_pesanan"]');
            let opts = sel.options;
            for(let i=0; i<opts.length; i++) {
                if(opts[i].value === targetStatus) {
                    sel.selectedIndex = i;
                    break;
                }
            }
            activeForm.submit();
        }
    });
}

function handlePackScan(val, targetStatus) {
    let matchIdx = -1;
    let anomalyIdx = -1;
    let wrongProductIdx = -1;

    // KUNCI GANDA 1: Blokir Eceran untuk Pesanan E-Commerce
    if (val.includes(".")) {
        Swal.fire({
            title: "&#10060; BLOKIR FATAL!",
            html: `Pesanan E-Commerce <b>HANYA</b> melayani <b>Grosir (Kardus/Box)</b>.<br><br>
                   Anda men-scan barang <b>Eceran (Sub-batch .A)</b>.<br>
                   <i>Dilarang keras mem-packing barang eceran untuk pembeli online!</i>`,
            icon: "error"
        }).then(() => { showPackingModal(targetStatus); });
        return;
    }

    for (let i=0; i<pendingItems.length; i++) {
        if (!pendingItems[i].verified) {
            let instruksi = pendingItems[i].instruksi.toLowerCase();
            
            if (instruksi.includes(val)) {
                matchIdx = i;
                break;
            } else if (instruksi !== "") {
                // Ekstrak kode prefix batch (contoh: "huf" dari "huf-2026-00")
                let prefix = instruksi.split("-")[0];
                if (val.startsWith(prefix)) {
                    // Satu keluarga produk (Prefix sama) tapi beda batch = ANOMALI FEFO
                    anomalyIdx = i;
                } else {
                    // Prefix sama sekali beda = SALAH PRODUK
                    wrongProductIdx = i;
                }
            }
        }
    }

    if (matchIdx !== -1) {
        pendingItems[matchIdx].verified = true;
        showPackingModal(targetStatus);
    } else if (anomalyIdx !== -1) {
        Swal.fire({
            title: "&#9888;&#65039; ANOMALI FEFO TERDETEKSI!",
            html: `Anda men-scan fisik <b>${val.toUpperCase()}</b>.<br><br>Padahal sistem merekomendasikan: <b>${pendingItems[anomalyIdx].instruksi}</b>.<br><br>Apakah Anda yakin ingin MENGABAIKAN rekomendasi dan tetap mem-packing barang ini?`,
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
                
                let h = document.createElement("input");
                h.type = "hidden";
                h.name = "anomali_log[]";
                h.value = pendingItems[anomalyIdx].id_variasi + "|" + pendingItems[anomalyIdx].instruksi + "|" + val.toUpperCase() + "|" + pendingItems[anomalyIdx].qty;
                activeForm.appendChild(h);

                showPackingModal(targetStatus);
            } else {
                showPackingModal(targetStatus);
            }
        });
    } else if (wrongProductIdx !== -1) {
        // KUNCI GANDA 2: Blokir Salah Produk Ekstrem
        Swal.fire({
            title: "&#10060; SALAH PRODUK!",
            html: `Anda men-scan barang yang <b>BERBEDA</b> dari daftar pesanan!<br><br>
                   Pesanan yang harus di-packing: <b>${pendingItems[wrongProductIdx].nama}</b><br>
                   Barcode yang di-scan: <b>${val.toUpperCase()}</b>`,
            icon: "error"
        }).then(() => { showPackingModal(targetStatus); });
    } else {
        Swal.fire({
            icon: "error",
            title: "Tidak Ditemukan",
            text: "Barcode/SN tidak cocok dengan barang manapun yang belum di-scan."
        }).then(() => {
            showPackingModal(targetStatus);
        });
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php layoutEnd(); ?>
