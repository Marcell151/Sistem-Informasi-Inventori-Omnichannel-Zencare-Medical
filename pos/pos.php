<?php
// File: pos/pos.php – Terminal POS Karyawan Offline ZenCare Medical
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireRole(['superadmin', 'admin']);

$idCabangKaryawan = $_SESSION['id_cabang'] ?? 1;

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'bayar_pos') {
    $cartItems = json_decode($_POST['cart_data'] ?? '[]', true);
    if (empty($cartItems)) {
        $msg = "Keranjang kasir kosong!"; $msgType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $totalHarga = 0;
            $invoiceNo  = "POS-" . date('Ymd') . "-" . rand(1000, 9999);
            $metodeBayar = $_POST['metode_pembayaran'] ?? 'Tunai';
            $pdo->prepare("INSERT INTO penjualan (no_invoice,id_user,tipe_transaksi,status_pesanan,total_harga,metode_pembayaran,created_at) VALUES (?,?,'pos','Selesai',0,?,NOW())")
                ->execute([$invoiceNo, $_SESSION['user_id'] ?? 2, $metodeBayar]);
            $idPenjualan = $pdo->lastInsertId();

            foreach ($cartItems as $item) {
                $idVar = intval($item['id']); $qtyInput = intval($item['qty']);
                $satuanTipe = $item['satuan_tipe'] ?? 'kecil'; // 'kecil' or 'besar'
                
                // Fetch current prices and stock from DB instead of trusting client
                $chk   = $pdo->prepare("SELECT v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, sc.stok, pi.kategori FROM produk_variasi v JOIN produk_induk pi ON v.id_produk_induk = pi.id LEFT JOIN stok_toko sc ON sc.id_variasi = v.id  WHERE v.id=? FOR UPDATE");
                $chk->execute([$idVar]);
                $varData = $chk->fetch();
                
                $rasio = intval($varData['rasio_konversi']) ?: 1;
                $effPrice = ($satuanTipe === 'besar') ? floatval($varData['harga_jual_besar']) : floatval($varData['harga_jual_kecil']);
                $qtyPotong = ($satuanTipe === 'besar') ? ($qtyInput * $rasio) : $qtyInput;
                $kategori = $varData['kategori'] ?? '';
                
                if (!$varData || intval($varData['stok']) < $qtyPotong) {
                    throw new Exception("Stok ID $idVar tidak cukup! (Pesan: $qtyPotong, Sisa: " . ($varData['stok'] ?? 0) . ")");
                }

                $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan,id_variasi,qty,harga_satuan) VALUES (?,?,?,?)")->execute([$idPenjualan,$idVar,$qtyInput,$effPrice]);
                $idDetail = $pdo->lastInsertId();

                $catatanLogistik = [];

                // FEFO Logic untuk Obat
                if ($kategori === 'Obat') {
                    $stmtBatch = $pdo->prepare("SELECT id, no_batch, stok_sisa FROM stok_batch WHERE id_variasi = ? AND stok_sisa > 0 ORDER BY tgl_exp ASC FOR UPDATE");
                    $stmtBatch->execute([$idVar]);
                    $batches = $stmtBatch->fetchAll();
                    
                    $sisaPotong = $qtyPotong;
                    foreach ($batches as $b) {
                        if ($sisaPotong <= 0) break;
                        
                        $potongBatch = min($b['stok_sisa'], $sisaPotong);
                        $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$potongBatch, $b['id']]);
                        
                        $catatanLogistik[] = $b['no_batch'] . " ({$potongBatch}x)";
                        $sisaPotong -= $potongBatch;
                    }
                    if ($sisaPotong > 0) {
                        throw new Exception("Stok Batch Obat tidak mencukupi untuk dipotong FEFO secara berurutan.");
                    }
                }

                // SN Auto-Pick untuk Alkes (jika tidak dipilih di UI)
                if ($kategori === 'Alat Kesehatan') {
                    $stmtSn = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia' LIMIT ? FOR UPDATE");
                    $stmtSn->bindValue(1, $idVar, PDO::PARAM_INT);
                    $stmtSn->bindValue(2, $qtyPotong, PDO::PARAM_INT);
                    $stmtSn->execute();
                    $snList = $stmtSn->fetchAll(PDO::FETCH_COLUMN);

                    if (count($snList) < $qtyPotong) {
                        throw new Exception("Jumlah Serial Number tersedia tidak mencukupi untuk Alkes ID $idVar.");
                    }

                    foreach ($snList as $snStr) {
                        $pdo->prepare("UPDATE unit_serial SET status = 'Terjual', id_penjualan = ? WHERE serial_number = ?")
                            ->execute([$idPenjualan, $snStr]);
                        $catatanLogistik[] = $snStr;
                    }
                }

                if (!empty($catatanLogistik)) {
                    $catatanStr = implode(', ', $catatanLogistik);
                    $pdo->prepare("UPDATE detail_penjualan SET catatan_logistik = ? WHERE id = ?")->execute([$catatanStr, $idDetail]);
                }

                $pdo->prepare("UPDATE stok_toko SET stok=stok-? WHERE id_variasi=?")->execute([$qtyPotong,$idVar]);

                $sisaQ = $pdo->prepare("SELECT stok FROM stok_toko WHERE id_variasi=?");
                $sisaQ->execute([$idVar]);
                $sisa = $sisaQ->fetchColumn();
                $satLable = ($satuanTipe === 'besar') ? $varData['satuan_besar'] : $varData['satuan_kecil'];
                $pdo->prepare("INSERT INTO kartu_stok (id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,?,?,?)")
                    ->execute([$idVar,'Keluar',$qtyPotong,$sisa,"Penjualan Luring $invoiceNo"]);
                $totalHarga += $effPrice * $qtyInput;
            }

            $pdo->prepare("UPDATE penjualan SET total_harga=? WHERE id=?")->execute([$totalHarga, $idPenjualan]);

            // Shopee cURL Sync (Backend retained - baca dari config/api_keys.php)
            $apiKeys = file_exists(__DIR__ . '/../config/api_keys.php') ? (require __DIR__ . '/../config/api_keys.php') : [];
            $shopeeApi = (!empty($apiKeys['shopee']['active']) && !empty($apiKeys['shopee']['api_key'])) ? $apiKeys['shopee'] : null;
            if ($shopeeApi) {
                foreach ($cartItems as $item) {
                    $skuQ = $pdo->prepare("SELECT v.sku_variasi, sc.stok FROM produk_variasi v JOIN stok_toko sc ON sc.id_variasi=v.id WHERE v.id=? ");
                    $skuQ->execute([$item['id']]);
                    $skuRow = $skuQ->fetch();
                    if ($skuRow) {
                        $ch = curl_init('https://partner.shopeesz.com/api/v2/product/update_stock');
                        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode(['item_sku' => $skuRow['sku_variasi'], 'stock' => $skuRow['stok']]), CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $shopeeApi['api_key']], CURLOPT_TIMEOUT => 3]);
                        curl_exec($ch); curl_close($ch);
                    }
                }
            }

            $pdo->commit();
            $msg = "Transaksi berhasil! Invoice: <strong>$invoiceNo</strong> | Total: Rp " . number_format($totalHarga, 0, ',', '.') . " | <a href='cetak_invoice.php?no_invoice=$invoiceNo' target='_blank' class='underline font-bold ml-2 text-zc hover:text-zcHv'>Cetak Struk Nota</a>";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal memproses transaksi: " . $e->getMessage(); $msgType = 'error';
        }
    }
}

$katalog = $pdo->prepare("
    SELECT v.id, v.sku_variasi, CONCAT(i.nama_produk,' – ',v.nama_variasi) AS nama,
           v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, COALESCE(sc.stok,0) AS stok, i.kategori, v.gambar,
           (SELECT GROUP_CONCAT(serial_number ORDER BY created_at ASC SEPARATOR ',') FROM unit_serial WHERE id_variasi = v.id AND status='Tersedia') AS sn_list,
           (SELECT GROUP_CONCAT(no_batch ORDER BY tgl_exp ASC SEPARATOR ',') FROM stok_batch WHERE id_variasi = v.id AND stok_sisa > 0) AS batch_list
    FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi=v.id 
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC");
$katalog->execute();
$katalog = $katalog->fetchAll();

$shopeeOn = false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Terminal POS Karyawan – ZenCare Medical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['"Inter"', 'sans-serif'] },
            colors: { zc:'#1a75d2', zcHv:'#1562b3', zcLt:'#e8f2ff', zcEm:'#059669', zcBrd:'#e4e9f0', zcMut:'#64748b', zcTxt:'#1e293b' }
          }
        }
      }
    </script>
    <style>
        ::-webkit-scrollbar { width:5px; } ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:99px; }
        body { animation: fadeIn .15s ease; } @keyframes fadeIn { from{opacity:.7;} to{opacity:1;} }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-zcTxt">

    <!-- Topbar -->
    <header class="bg-white border-b border-zcBrd px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-zc to-zcHv text-white flex items-center justify-center shadow-lg shadow-zc/30">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-zcTxt tracking-tight">POS ZenCare Medical</h1>
                <div class="flex items-center gap-3 mt-1 text-xs text-zcMut font-medium">
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Kasir') ?></span>
                    <span class="text-slate-300">•</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> <?= htmlspecialchars($cabangKaryawan['nama'] ?? 'Pusat') ?></span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="../index.php" class="text-sm font-semibold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl transition flex items-center gap-2"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Dashboard Utama</a>
            <a href="../ecommerce/index.php" class="text-sm font-semibold bg-zc hover:bg-zcHv text-white px-4 py-2.5 rounded-xl shadow-md shadow-zc/20 transition">Toko Online</a>
            <a href="../logout.php" class="text-sm font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 border border-rose-200 px-4 py-2.5 rounded-xl transition">Keluar</a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-5">

        <?php if ($msg): ?>
            <div class="mb-4 p-4 rounded-2xl border text-xs font-semibold <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- Katalog Produk -->
            <div class="lg:col-span-7 bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-zcBrd">
                    <h2 class="text-sm font-bold text-zcTxt">Katalog Stok Cabang</h2>
                    <input type="text" id="pos_search" onkeyup="filterPos()" placeholder="Cari SKU / nama..."
                        class="text-xs border border-zcBrd rounded-xl px-3 py-2 w-52 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 max-h-[520px] overflow-y-auto" id="product_grid">
                    <?php foreach ($katalog as $item): ?>
                        <?php 
                        $rawGambar = $item['gambar'] ?? '';
                        $arrGambar = $rawGambar ? array_map('trim', explode(',', $rawGambar)) : [];
                        $mainGambar = !empty($arrGambar) ? $arrGambar[0] : '';
                        
                        $imgSrc = '../assets/img/no-image.png';
                        if ($mainGambar) {
                            if (str_starts_with($mainGambar, 'http')) $imgSrc = $mainGambar;
                            else if (str_contains($mainGambar, '/')) $imgSrc = '../' . $mainGambar;
                            else $imgSrc = '../assets/img/produk/' . $mainGambar;
                        }
                        $stok = intval($item['stok']); 
                        ?>
                        <div class="product-card bg-slate-50 border border-zcBrd rounded-2xl p-3.5 hover:border-zc transition flex flex-col"
                             data-search="<?= strtolower($item['nama'] . ' ' . $item['sku_variasi']) ?>"
                             data-sn="<?= strtolower($item['sn_list'] ?? '') ?>"
                             data-batch="<?= strtolower($item['batch_list'] ?? '') ?>">
                            <div class="flex gap-3 mb-3 flex-1 min-w-0">
                                <div class="w-12 h-12 flex-shrink-0 bg-white rounded-lg border border-slate-200 overflow-hidden flex items-center justify-center p-1">
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="img" class="max-w-full max-h-full object-contain mix-blend-multiply" onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="text-[9px] font-mono text-zcMut block"><?= htmlspecialchars($item['sku_variasi']) ?></span>
                                    <span class="text-xs font-bold text-zcTxt block leading-snug mt-0.5 line-clamp-2"><?= htmlspecialchars($item['nama']) ?></span>
                                    
                                    <?php if (!empty($item['batch_list'])): ?>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <?php foreach(array_slice(explode(',', $item['batch_list']), 0, 2) as $b): ?>
                                                <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-bold shadow-sm">B: <?= htmlspecialchars($b) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count(explode(',', $item['batch_list'])) > 2): ?>
                                                <span class="inline-block px-2 py-1 bg-slate-50 text-slate-500 border border-slate-200 rounded text-[10px] font-bold shadow-sm">...</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['sn_list'])): ?>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <?php foreach(array_slice(explode(',', $item['sn_list']), 0, 2) as $sn): ?>
                                                <span class="inline-block px-2 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded text-[10px] font-bold shadow-sm">SN: <?= htmlspecialchars($sn) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count(explode(',', $item['sn_list'])) > 2): ?>
                                                <span class="inline-block px-2 py-1 bg-slate-50 text-slate-500 border border-slate-200 rounded text-[10px] font-bold shadow-sm">...</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pt-2.5 border-t border-zcBrd flex items-end justify-between">
                                <div>
                                    <span class="text-xs font-bold text-zcTxt block">Rp <?= number_format($item['harga_jual_kecil'], 0, ',', '.') ?> <span class="font-normal text-[10px] text-zcMut">/ <?= htmlspecialchars($item['satuan_kecil']) ?></span></span>
                                    <span class="text-[9px] font-semibold text-zcEm block">Grosir: Rp <?= number_format($item['harga_jual_besar'], 0, ',', '.') ?> / <?= htmlspecialchars($item['satuan_besar']) ?></span>
                                    <span class="text-[10px] <?= $stok > 0 ? 'text-emerald-600' : 'text-rose-500' ?> font-semibold">Stok: <?= $stok ?> <?= htmlspecialchars($item['satuan_kecil']) ?></span>
                                </div>
                                <div class="flex flex-col gap-1 items-end">
                                    <select id="uom_<?= $item['id'] ?>" class="text-[10px] border border-zcBrd rounded px-1 py-0.5 bg-white focus:outline-none focus:border-zc w-full" <?= $stok <= 0 ? 'disabled' : '' ?>>
                                        <option value="kecil"><?= htmlspecialchars($item['satuan_kecil']) ?></option>
                                        <option value="besar" <?= $stok < $item['rasio_konversi'] ? 'disabled' : '' ?>><?= htmlspecialchars($item['satuan_besar']) ?></option>
                                    </select>
                                    <button type="button" 
                                        data-id="<?= intval($item['id']) ?>"
                                        data-nama="<?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-hk="<?= floatval($item['harga_jual_kecil']) ?>"
                                        data-hb="<?= floatval($item['harga_jual_besar']) ?>"
                                        data-stok="<?= intval($stok) ?>"
                                        data-rasio="<?= intval($item['rasio_konversi']) ?: 1 ?>"
                                        onclick="addToPos(this)"
                                        <?= $stok <= 0 ? 'disabled' : '' ?>
                                        class="w-full text-[11px] font-bold px-2 py-1 rounded border transition <?= $stok > 0 ? 'bg-zc hover:bg-zcHv text-white border-zc shadow-xs' : 'bg-slate-200 text-slate-400 border-slate-300 cursor-not-allowed' ?>">
                                        + Pilih
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Billing POS -->
            <div class="lg:col-span-5 bg-white border border-zcBrd rounded-2xl shadow-sm flex flex-col">
                <div class="px-5 py-4 border-b border-zcBrd flex items-center gap-2">
                    <h2 class="text-sm font-bold text-zcTxt">Billing Transaksi Karyawan</h2>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-xs" id="pos_cart_table">
                        <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Item</th>
                                <th class="px-4 py-2.5 text-center">Qty</th>
                                <th class="px-4 py-2.5 text-right">Subtotal</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody id="pos_cart_body" class="divide-y divide-zcBorder/60">
                            <tr><td colspan="4" class="px-4 py-10 text-center text-zcMut italic">Pilih barang dari katalog...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t border-zcBrd space-y-3">
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs font-bold text-zcMut uppercase tracking-wider">Total Pembayaran</span>
                        <span id="pos_total" class="text-2xl font-bold text-zcEm">Rp 0</span>
                    </div>
                    <form method="POST" id="form_pos">
                        <input type="hidden" name="aksi" value="bayar_pos">
                        <input type="hidden" name="cart_data" id="cart_input">
                        <input type="hidden" name="metode_pembayaran" id="form_metode_pembayaran" value="Tunai">
                        <button type="button" id="btn_pay" disabled onclick="openPaymentModal()"
                            class="w-full py-3.5 rounded-xl text-xs font-bold transition shadow-sm bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed">
                            Proses Pembayaran
                        </button>
                    </form>
                    <button onclick="clearCart()" class="w-full py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6"/></svg> Kosongkan Keranjang
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Pembayaran POS -->
    <div id="payment_modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden transform transition-all scale-95 opacity-0 duration-200" id="modal_content">
            <div class="bg-zc px-5 py-4 flex items-center justify-between">
                <h3 class="text-white font-bold text-sm tracking-wide">PROSES PEMBAYARAN</h3>
                <button onclick="closePaymentModal()" class="text-zcLt hover:text-white transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div class="text-center p-4 bg-slate-50 rounded-xl border border-zcBrd shadow-inner">
                    <p class="text-xs font-bold text-zcMut uppercase tracking-wider mb-1">Total Tagihan</p>
                    <p class="text-4xl font-black text-zcTxt" id="modal_total_tagihan">Rp 0</p>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-2">Metode Pembayaran</label>
                    <select id="modal_metode" onchange="toggleNominalInput()" class="w-full text-sm font-bold border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-zc focus:ring-4 focus:ring-zcLt transition appearance-none bg-white mb-4">
                        <option value="Tunai">Uang Tunai</option>
                        <option value="QRIS">QRIS / E-Wallet</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Kartu Debit/Kredit">Kartu Debit/Kredit</option>
                    </select>
                </div>
                
                <div id="nominal_section">
                    <label class="block text-xs font-bold text-zcTxt mb-2">Nominal Uang Diberikan (Rp)</label>
                    <input type="text" id="modal_nominal" onkeyup="calcKembalian()" placeholder="0" class="w-full text-2xl font-bold border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-zc focus:ring-4 focus:ring-zcLt transition text-right">
                    <div class="flex gap-2 mt-3">
                        <button type="button" onclick="setNominal(50000)" class="flex-1 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold py-2.5 rounded-lg border border-slate-200 transition shadow-sm">50.000</button>
                        <button type="button" onclick="setNominal(100000)" class="flex-1 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold py-2.5 rounded-lg border border-slate-200 transition shadow-sm">100.000</button>
                        <button type="button" onclick="setNominalUangPas()" class="flex-1 bg-zcLt hover:bg-blue-100 text-zc text-sm font-bold py-2.5 rounded-lg border border-blue-200 transition shadow-sm">Uang Pas</button>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl border-2 border-slate-200 bg-slate-50 transition-colors" id="box_kembalian">
                    <span class="text-sm font-bold text-zcMut" id="lbl_kembalian">Menunggu Pembayaran</span>
                    <span class="text-2xl font-bold text-zcTxt" id="val_kembalian">-</span>
                </div>
                
                <div id="non_tunai_info" class="hidden p-4 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-xs text-center font-medium">
                    Pastikan pembayaran melalui EDC/Transfer/QRIS telah berhasil masuk sebelum menyelesaikan transaksi.
                </div>
            </div>
            <div class="p-5 border-t border-zcBrd bg-slate-50 flex gap-3">
                <button onclick="closePaymentModal()" class="flex-1 py-3.5 rounded-xl text-sm font-bold bg-white text-slate-600 border border-slate-300 hover:bg-slate-100 transition shadow-sm">Batal</button>
                <button id="btn_submit_pay" onclick="submitPayment()" disabled class="flex-[2] py-3.5 rounded-xl text-sm font-bold bg-slate-300 text-slate-500 cursor-not-allowed transition">Selesaikan Transaksi</button>
            </div>
        </div>
    </div>

    <script>
    let cart = [];

    function addToPos(btnElement) {
        try {
            var card = btnElement.closest('.product-card');
            var rawSn = card ? card.getAttribute('data-sn') : '';
            var rawBatch = card ? card.getAttribute('data-batch') : '';
            
            var id = parseInt(btnElement.getAttribute('data-id'));
            var name = btnElement.getAttribute('data-nama');
            var hargaKecil = parseFloat(btnElement.getAttribute('data-hk'));
            var hargaBesar = parseFloat(btnElement.getAttribute('data-hb'));
            var maxStockPcs = parseInt(btnElement.getAttribute('data-stok'));
            var rasio = parseInt(btnElement.getAttribute('data-rasio'));

            var sel = document.getElementById('uom_' + id);
            var uomType = sel.value; // 'kecil' or 'besar'
            var uomLabel = sel.options[sel.selectedIndex].text;
            
            var price = (uomType === 'besar') ? hargaBesar : hargaKecil;
            var qtyMultiplier = (uomType === 'besar') ? rasio : 1;
            var cartId = id + '_' + uomType;

            var found = null;
            for (var i = 0; i < cart.length; i++) {
                if (cart[i].cartId === cartId) { found = cart[i]; break; }
            }

            if (found) {
                if ((found.qty + 1) * found.rasio > found.maxPcs) { alert('Stok terbatas!'); return; }
                found.qty++;
            } else { 
                if (qtyMultiplier > maxStockPcs) { alert('Stok tidak cukup untuk satuan ini!'); return; }
                cart.push({
                    id: id, cartId: cartId, nama: name, harga: price, qty: 1, 
                    satuan_tipe: uomType, satuan_label: uomLabel, maxPcs: maxStockPcs, rasio: qtyMultiplier,
                    sn_list: rawSn, batch_list: rawBatch
                }); 
            }
            renderCart();
        } catch(e) {
            alert("Terjadi Error Javascript di addToPos: " + e.message);
        }
    }

    function changeQty(cartId, d) {
        var item = null;
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].cartId === cartId) { item = cart[i]; break; }
        }
        if (!item) return;
        if (d > 0 && (item.qty + d) * item.rasio > item.maxPcs) { alert('Stok terbatas!'); return; }
        item.qty += d;
        if (item.qty <= 0) cart = cart.filter(function(i) { return i.cartId !== cartId; });
        renderCart();
    }

    function removeItem(cartId) { cart = cart.filter(function(i) { return i.cartId !== cartId; }); renderCart(); }
    function clearCart() { cart = []; renderCart(); }

    function renderCart() {
        var tbody = document.getElementById('pos_cart_body');
        var totalEl = document.getElementById('pos_total');
        var btn = document.getElementById('btn_pay');
        var inp = document.getElementById('cart_input');

        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-zcMut italic">Pilih barang dari katalog...</td></tr>';
            totalEl.innerText = 'Rp 0';
            btn.disabled = true;
            btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed';
            inp.value = '[]'; return;
        }

        var total = 0, html = '';
        cart.forEach(function(i) {
            var sub = i.harga * i.qty; total += sub;
            var fefoText = '';
            if (i.batch_list) {
                var batches = i.batch_list.split(',').slice(0, 2).join(', ');
                var suffix = i.batch_list.split(',').length > 2 ? ' ...' : '';
                fefoText = '<span class="block text-[9px] text-orange-600 font-bold mt-1 rounded bg-orange-50 px-1 py-0.5 inline-block">Ambil (FEFO): ' + batches + suffix + '</span>';
            } else if (i.sn_list) {
                var maxSns = Math.min(i.qty * i.rasio, 2);
                var sns = i.sn_list.split(',').slice(0, maxSns).join(', ');
                var suffix2 = (i.qty * i.rasio > 2) ? ' ...' : '';
                fefoText = '<span class="block text-[9px] text-purple-600 font-bold mt-1 rounded bg-purple-50 px-1 py-0.5 inline-block">Ambil (FIFO): ' + sns + suffix2 + '</span>';
            }
            html += '<tr class="hover:bg-slate-50/60">' +
                '<td class="px-4 py-3 font-semibold text-zcTxt text-xs">' +
                    i.nama +
                    '<span class="block text-[9px] text-zcEm mt-0.5">' + i.satuan_label + ' (Rp ' + i.harga.toLocaleString('id-ID') + ')</span>' +
                    fefoText +
                '</td>' +
                '<td class="px-4 py-3 text-center">' +
                    '<div class="flex items-center justify-center gap-1.5">' +
                        '<button type="button" onclick="changeQty(\'' + i.cartId + '\',-1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition">-</button>' +
                        '<span class="font-bold text-xs w-5 text-center">' + i.qty + '</span>' +
                        '<button type="button" onclick="changeQty(\'' + i.cartId + '\',1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition">+</button>' +
                    '</div>' +
                '</td>' +
                '<td class="px-4 py-3 text-right font-bold text-xs">Rp ' + sub.toLocaleString('id-ID') + '</td>' +
                '<td class="px-4 py-3 text-center"><button type="button" onclick="removeItem(\'' + i.cartId + '\')" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition">✕</button></td>' +
            '</tr>';
        });

        tbody.innerHTML = html;
        totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
        btn.disabled = false;
        btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-zc hover:bg-zcHv text-white transition shadow-sm cursor-pointer';
        inp.value = JSON.stringify(cart);
    }

    // --- Payment UI Logic ---
    var currentTotal = 0;
    var modalInput = document.getElementById('modal_nominal');
    
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function openPaymentModal() {
        if (!cart.length) return;
        currentTotal = 0;
        cart.forEach(function(i) { currentTotal += i.harga * i.qty; });
        
        document.getElementById('modal_total_tagihan').innerText = 'Rp ' + formatRupiah(currentTotal);
        modalInput.value = '';
        calcKembalian();
        
        var modal = document.getElementById('payment_modal');
        var content = document.getElementById('modal_content');
        modal.classList.remove('hidden');
        
        setTimeout(function() {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
            modalInput.focus();
        }, 10);
    }

    function closePaymentModal() {
        var modal = document.getElementById('payment_modal');
        var content = document.getElementById('modal_content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(function() { modal.classList.add('hidden'); }, 200);
    }

    function setNominal(val) {
        modalInput.value = formatRupiah(val);
        calcKembalian();
    }
    
    function setNominalUangPas() {
        modalInput.value = formatRupiah(currentTotal);
        calcKembalian();
    }

    function calcKembalian() {
        var metode = document.getElementById('modal_metode').value;
        var box = document.getElementById('box_kembalian');
        var lbl = document.getElementById('lbl_kembalian');
        var val = document.getElementById('val_kembalian');
        var btn = document.getElementById('btn_submit_pay');

        if (metode !== 'Tunai') {
            box.classList.add('hidden');
            btn.disabled = false;
            btn.className = 'flex-[2] py-3.5 rounded-xl text-sm font-bold bg-zc hover:bg-zcHv text-white transition shadow-sm cursor-pointer shadow-zc/30';
            return;
        }
        
        box.classList.remove('hidden');

        var raw = modalInput.value.replace(/[^0-9]/g, '');
        if(raw) modalInput.value = formatRupiah(raw);
        var nom = parseInt(raw) || 0;
        
        if (nom === 0) {
            box.className = "flex items-center justify-between p-4 rounded-xl border-2 border-slate-200 bg-slate-50 transition-colors";
            lbl.className = "text-sm font-bold text-zcMut";
            lbl.innerText = "Menunggu Nominal";
            val.className = "text-2xl font-bold text-zcMut";
            val.innerText = "-";
            btn.disabled = true;
            btn.className = "flex-[2] py-3.5 rounded-xl text-sm font-bold bg-slate-300 text-slate-500 cursor-not-allowed transition";
        } else if (nom < currentTotal) {
            box.className = "flex items-center justify-between p-4 rounded-xl border-2 border-rose-300 bg-rose-50 transition-colors";
            lbl.className = "text-sm font-bold text-rose-600";
            lbl.innerText = "Kekurangan Bayar";
            val.className = "text-2xl font-black text-rose-600";
            val.innerText = "Rp " + formatRupiah(currentTotal - nom);
            btn.disabled = true;
            btn.className = "flex-[2] py-3.5 rounded-xl text-sm font-bold bg-slate-300 text-slate-500 cursor-not-allowed transition";
        } else {
            box.className = "flex items-center justify-between p-4 rounded-xl border-2 border-emerald-300 bg-emerald-50 transition-colors";
            lbl.className = "text-sm font-bold text-emerald-600";
            lbl.innerText = "Uang Kembalian";
            val.className = "text-2xl font-black text-emerald-600";
            val.innerText = "Rp " + formatRupiah(nom - currentTotal);
            btn.disabled = false;
            btn.className = "flex-[2] py-3.5 rounded-xl text-sm font-bold bg-zc hover:bg-zcHv text-white shadow-lg shadow-zc/30 transition cursor-pointer";
        }
    }

    function toggleNominalInput() {
        var metode = document.getElementById('modal_metode').value;
        var nominalSec = document.getElementById('nominal_section');
        var infoSec = document.getElementById('non_tunai_info');
        
        if (metode === 'Tunai') {
            nominalSec.classList.remove('hidden');
            infoSec.classList.add('hidden');
        } else {
            nominalSec.classList.add('hidden');
            infoSec.classList.remove('hidden');
        }
        calcKembalian();
    }

    function submitPayment() {
        document.getElementById('form_metode_pembayaran').value = document.getElementById('modal_metode').value;
        document.getElementById('btn_submit_pay').innerText = 'Memproses...';
        document.getElementById('form_pos').submit();
    }

    // --- Barcode Scanner Listener ---
    var barcodeString = '';
    var barcodeTimer = null;
    
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        if (e.key === 'Enter') {
            if (barcodeString.length > 2) {
                processBarcode(barcodeString);
            }
            barcodeString = '';
            clearTimeout(barcodeTimer);
            return;
        }
        
        if (e.key.length === 1) { 
            barcodeString += e.key;
            clearTimeout(barcodeTimer);
            barcodeTimer = setTimeout(function() {
                barcodeString = ''; 
            }, 60); 
        }
    });

    function processBarcode(sku) {
        try {
            sku = sku.trim().toLowerCase();
            var found = false;
            var cards = document.getElementsByClassName('product-card');
            
            for (var i = 0; i < cards.length; i++) {
                var c = cards[i];
                var skuSpan = c.querySelector('span.font-mono');
                var dataSn = c.getAttribute('data-sn') || '';
                var dataBatch = c.getAttribute('data-batch') || '';
                
                var isMatch = false;
                if (skuSpan && skuSpan.innerText.toLowerCase() === sku) isMatch = true;
                else if (dataSn.split(',').includes(sku)) isMatch = true;
                else if (dataBatch.split(',').includes(sku)) isMatch = true;

                if (isMatch) {
                    var btn = c.querySelector('button');
                    if (btn && !btn.disabled) {
                        btn.click();
                        found = true;
                        c.classList.add('bg-zcLt', 'border-zc');
                        setTimeout(function() { c.classList.remove('bg-zcLt', 'border-zc'); }, 300);
                        
                        try {
                            var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            var oscillator = audioCtx.createOscillator();
                            oscillator.type = 'sine';
                            oscillator.frequency.setValueAtTime(800, audioCtx.currentTime);
                            oscillator.connect(audioCtx.destination);
                            oscillator.start();
                            oscillator.stop(audioCtx.currentTime + 0.1);
                        } catch(err) {}
                        
                        break;
                    }
                }
            }
            if (!found) {
                alert('SKU Barcode tidak ditemukan atau stok kosong: ' + sku.toUpperCase());
            }
        } catch(e) { alert("Error JS di Barcode: " + e.message); }
    }

    function filterPos() {
        try {
            var q = document.getElementById('pos_search').value.toLowerCase().trim();
            var cards = document.getElementsByClassName('product-card');
            for (var i = 0; i < cards.length; i++) {
                var c = cards[i];
                var matchStr = c.getAttribute('data-search') + ' ' + (c.getAttribute('data-sn') || '') + ' ' + (c.getAttribute('data-batch') || '');
                if (matchStr.indexOf(q) !== -1) {
                    c.style.display = '';
                } else {
                    c.style.display = 'none';
                }
            }
        } catch(e) {
            alert("Error JS di filterPos: " + e.message);
        }
    }
    </script>
</body>
</html>

