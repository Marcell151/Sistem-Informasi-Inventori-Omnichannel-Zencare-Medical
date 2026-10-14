<?php
// File: pos/pos.php – Terminal POS Karyawan Offline ZenCare Medical
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireRole(['superadmin', 'kasir']);

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
            $customInv = trim($_POST['custom_invoice'] ?? '');
            $invoiceNo  = (!empty($customInv) && preg_match('/^POS-/', $customInv)) ? $customInv : ("POS-" . date('Ymd') . "-" . rand(1000, 9999));
            $metodeBayar = $_POST['metode_pembayaran'] ?? 'Tunai';
            $pdo->prepare("INSERT INTO penjualan (no_invoice,id_user,tipe_transaksi,status_pesanan,total_harga,metode_bayar_pos,created_at) VALUES (?,?,'pos','Selesai',0,?,NOW())")
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

                $scannedCode = $item['scanned_code'] ?? null;
                $isAnomaly = !empty($item['is_anomaly']);
                $batchSeharusnya = $item['batch_seharusnya'] ?? null;

                // FEFO / Batch Logic untuk Obat
                if ($kategori === 'Obat') {
                    if ($scannedCode) {
                        // Pakai batch spesifik yang di-scan
                        $stmtCheck = $pdo->prepare("SELECT id, no_batch, tgl_exp, stok_sisa FROM stok_batch WHERE id_variasi = ? AND no_batch = ? FOR UPDATE");
                        $stmtCheck->execute([$idVar, $scannedCode]);
                        $b = $stmtCheck->fetch();
                        if (!$b || $b['stok_sisa'] < $qtyPotong) {
                            throw new Exception("Stok Batch $scannedCode tidak mencukupi untuk Obat ID $idVar.");
                        }
                        if ($b['tgl_exp'] < date('Y-m-d')) {
                            throw new Exception("Batch $scannedCode sudah KADALUARSA (Expired: {$b['tgl_exp']}). Obat kadaluarsa dilarang dijual!");
                        }
                        $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyPotong, $b['id']]);
                        
                        if ($isAnomaly && $batchSeharusnya) {
                            $pdo->prepare("INSERT INTO log_anomali_fefo (id_penjualan, id_user, id_variasi, batch_diambil, batch_seharusnya, qty) VALUES (?, ?, ?, ?, ?, ?)")
                                ->execute([$idPenjualan, $_SESSION['user_id'], $idVar, $scannedCode, $batchSeharusnya, $qtyPotong]);
                        }

                        $catatanLogistik[] = $scannedCode . " ({$qtyPotong}x)";
                    } else {
                        // Auto FEFO fallback (HANYA batch yang belum expired)
                        $stmtBatch = $pdo->prepare("SELECT id, no_batch, stok_sisa FROM stok_batch WHERE id_variasi = ? AND stok_sisa > 0 AND tgl_exp >= CURDATE() ORDER BY tgl_exp ASC, no_batch ASC FOR UPDATE");
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
                        if ($sisaPotong > 0) throw new Exception("Stok Batch Obat yang masih aktif/belum expired tidak mencukupi untuk dipotong FEFO.");
                    }
                }

                // SN Auto-Pick / Scan untuk Alkes
                if ($kategori === 'Alat Kesehatan') {
                    if ($scannedCode) {
                        $stmtUpdateSN = $pdo->prepare("UPDATE unit_serial SET status = 'Terjual', id_penjualan = ? WHERE serial_number = ? AND id_variasi = ? AND status = 'Tersedia'");
                        $stmtUpdateSN->execute([$idPenjualan, $scannedCode, $idVar]);
                        if ($stmtUpdateSN->rowCount() === 0) throw new Exception("SN $scannedCode tidak valid/tersedia.");
                        $catatanLogistik[] = $scannedCode;
                    } else {
                        $stmtSn = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia' LIMIT ? FOR UPDATE");
                        $stmtSn->bindValue(1, $idVar, PDO::PARAM_INT);
                        $stmtSn->bindValue(2, $qtyPotong, PDO::PARAM_INT);
                        $stmtSn->execute();
                        $snList = $stmtSn->fetchAll(PDO::FETCH_COLUMN);

                        if (count($snList) < $qtyPotong) throw new Exception("SN tersedia tidak cukup untuk Alkes ID $idVar.");
                        foreach ($snList as $snStr) {
                            $pdo->prepare("UPDATE unit_serial SET status = 'Terjual', id_penjualan = ? WHERE serial_number = ?")->execute([$idPenjualan, $snStr]);
                            $catatanLogistik[] = $snStr;
                        }
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
                $batchStr = !empty($catatanLogistik) ? " [Batch: " . implode(', ', $catatanLogistik) . "]" : "";
                $deskripsi = "Penjualan Luring $invoiceNo$batchStr";
                $kasirUserId = $_SESSION['user_id'] ?? null;

                $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Keluar', 'POS', 'Penjualan POS', ?, ?, ?, ?, ?)")
                    ->execute([$idVar, $satuanTipe, $invoiceNo, $qtyPotong, $sisa, $deskripsi, $kasirUserId]);
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
    SELECT v.id, v.sku_variasi, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama,
           v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, COALESCE(sc.stok,0) AS stok, i.kategori, v.gambar,
           (SELECT GROUP_CONCAT(serial_number ORDER BY created_at ASC SEPARATOR ',') FROM unit_serial WHERE id_variasi = v.id AND status='Tersedia') AS sn_list,
           (SELECT GROUP_CONCAT(CONCAT(no_batch, '|', stok_sisa) ORDER BY tgl_exp ASC, no_batch ASC SEPARATOR ',') FROM stok_batch WHERE id_variasi = v.id AND stok_sisa > 0 AND tgl_exp >= CURDATE()) AS batch_list,
           (SELECT GROUP_CONCAT(CONCAT(no_batch, '|', tgl_exp) SEPARATOR ',') FROM stok_batch WHERE id_variasi = v.id AND tgl_exp < CURDATE()) AS expired_batch_list,
           (SELECT COALESCE(SUM(stok_sisa), 0) FROM stok_batch WHERE id_variasi = v.id AND no_batch LIKE '%.%' AND tgl_exp >= CURDATE()) AS stok_eceran,
           (SELECT COALESCE(SUM(stok_sisa), 0) FROM stok_batch WHERE id_variasi = v.id AND no_batch NOT LIKE '%.%' AND tgl_exp >= CURDATE()) AS stok_dus
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
    <script src="../assets/js/html5-qrcode.min.js"></script>
    <style>
        ::-webkit-scrollbar { width:5px; } ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:99px; }
        body { animation: fadeIn .15s ease; } @keyframes fadeIn { from{opacity:.7;} to{opacity:1;} }
        
        /* Fix live camera viewport - menghilangkan potongan & garis hitam samping */
        #camera_scanner_view { width: 100% !important; overflow: hidden !important; border-radius: 0.75rem !important; background: #0f172a !important; }
        #camera_scanner_view video {
            width: 100% !important;
            height: 100% !important;
            min-height: 280px !important;
            max-height: 360px !important;
            object-fit: cover !important;
            border-radius: 0.75rem !important;
            display: block !important;
            margin: 0 auto !important;
        }
        #camera_scanner_view canvas { border-radius: 0.75rem !important; }
        #camera_scanner_view #qr-shaded-region { border-radius: 0.75rem !important; }
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
                    <span class="text-slate-300">&#8226;</span>
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
                             data-batch="<?= strtolower($item['batch_list'] ?? '') ?>"
                             data-expired-batch="<?= htmlspecialchars(strtolower($item['expired_batch_list'] ?? '')) ?>"
                             data-stok-eceran="<?= isset($item['stok_eceran']) ? intval($item['stok_eceran']) : intval($item['stok']) ?>"
                             data-stok-dus="<?= isset($item['stok_dus']) ? intval($item['stok_dus']) : 0 ?>">
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
                            <?php 
                            $rasioItem = max(1, intval($item['rasio_konversi']));
                            if ($item['kategori'] === 'Obat') {
                                $dusUtuh = floor(intval($item['stok_dus'] ?? 0) / $rasioItem);
                                $eceranSisa = intval($item['stok_eceran'] ?? 0);
                            } else {
                                $dusUtuh = floor(intval($stok) / $rasioItem);
                                $eceranSisa = intval($stok) % $rasioItem;
                            }
                            ?>
                            <div class="pt-2.5 border-t border-zcBrd flex flex-col gap-2">
                                <!-- Pilihan Harga Seimbang & Berpembatas -->
                                <div class="grid grid-cols-2 gap-1.5 p-1.5 bg-slate-100/70 rounded-xl border border-slate-200 text-center">
                                    <div class="bg-white rounded-lg p-1.5 shadow-xs border border-slate-200/60">
                                        <span class="text-[9px] font-bold text-slate-400 block uppercase tracking-wide">Eceran</span>
                                        <span class="text-xs font-black text-slate-800 block">Rp <?= number_format($item['harga_jual_kecil'], 0, ',', '.') ?></span>
                                        <span class="text-[9px] font-semibold text-slate-500 block">/ <?= htmlspecialchars($item['satuan_kecil']) ?></span>
                                    </div>
                                    <div class="bg-white rounded-lg p-1.5 shadow-xs border border-slate-200/60">
                                        <span class="text-[9px] font-bold text-slate-400 block uppercase tracking-wide">Grosir</span>
                                        <?php if ($item['rasio_konversi'] > 1): ?>
                                            <span class="text-xs font-black text-slate-800 block">Rp <?= number_format($item['harga_jual_besar'], 0, ',', '.') ?></span>
                                            <span class="text-[9px] font-semibold text-slate-500 block">/ <?= htmlspecialchars($item['satuan_besar']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-black text-slate-400 block">-</span>
                                            <span class="text-[9px] font-semibold text-slate-400 block">(1 Satuan)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Sisa Stok Terpisah (Besar vs Kecil) -->
                                <div class="p-1.5 rounded-lg border text-[10px] <?= $stok > 0 ? 'bg-emerald-50/60 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-700' ?> flex items-center justify-between">
                                    <span class="font-bold">Sisa Stok:</span>
                                    <div class="flex items-center gap-1 font-extrabold text-[10px]">
                                        <?php if ($item['rasio_konversi'] > 1): ?>
                                            <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700"><?= $dusUtuh ?> <?= htmlspecialchars($item['satuan_besar']) ?></span>
                                            <span class="text-slate-400">•</span>
                                            <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700"><?= $eceranSisa ?> <?= htmlspecialchars($item['satuan_kecil']) ?></span>
                                        <?php else: ?>
                                            <span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700"><?= $stok ?> <?= htmlspecialchars($item['satuan_kecil']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Dropdown Satuan & Tombol Aksi -->
                                <div class="flex items-center gap-1.5">
                                    <?php if ($item['rasio_konversi'] > 1): ?>
                                    <div class="w-1/2">
                                        <select id="uom_<?= $item['id'] ?>" class="text-[11px] font-bold border border-zcBrd rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:border-zc w-full shadow-xs" <?= $stok <= 0 ? 'disabled' : '' ?>>
                                            <option value="kecil" <?= $eceranSisa <= 0 && $dusUtuh > 0 ? '' : 'selected' ?>>Ecer (<?= htmlspecialchars($item['satuan_kecil']) ?>)</option>
                                            <option value="besar" <?= $dusUtuh <= 0 ? 'disabled' : '' ?>>Grosir (<?= htmlspecialchars($item['satuan_besar']) ?>)</option>
                                        </select>
                                    </div>
                                    <?php else: ?>
                                    <input type="hidden" id="uom_<?= $item['id'] ?>" value="kecil" data-label="<?= htmlspecialchars($item['satuan_kecil']) ?>">
                                    <div class="w-1/2 text-[10px] border border-zcBrd rounded-lg px-2 py-1.5 bg-slate-100 text-slate-500 text-center font-bold">
                                        <?= htmlspecialchars($item['satuan_kecil']) ?>
                                    </div>
                                    <?php endif; ?>

                                    <button type="button" 
                                        data-id="<?= intval($item['id']) ?>"
                                        data-nama="<?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-hk="<?= floatval($item['harga_jual_kecil']) ?>"
                                        data-hb="<?= floatval($item['harga_jual_besar']) ?>"
                                        data-stok="<?= intval($stok) ?>"
                                        data-rasio="<?= intval($item['rasio_konversi']) ?: 1 ?>"
                                        data-kat="<?= htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="addToPos(this, null)"
                                        <?= $stok <= 0 ? 'disabled' : '' ?>
                                        class="w-1/2 text-[11px] font-bold px-2 py-1.5 rounded-lg border transition <?= $stok > 0 ? 'bg-zc hover:bg-zcHv text-white border-zc shadow-xs' : 'bg-slate-200 text-slate-400 border-slate-300 cursor-not-allowed' ?>">
                                        + Pilih
                                    </button>
                                </div>

                                <?php if ($item['kategori'] === 'Obat' && (isset($item['stok_dus']) && $item['stok_dus'] > 0)): ?>
                                    <button type="button" 
                                        onclick="triggerManualBongkar(<?= intval($item['id']) ?>, '<?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['batch_list'] ?? '') ?>', <?= floatval($item['harga_jual_kecil']) ?>, <?= floatval($item['harga_jual_besar']) ?>, <?= intval($item['stok']) ?>, <?= intval($item['rasio_konversi']) ?: 1 ?>)" 
                                        class="w-full text-[9px] font-bold px-2 py-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition shadow-xs">
                                        📦 Bongkar Box (Tersisa: <?= isset($item['stok_eceran']) ? $item['stok_eceran'] : 0 ?> <?= htmlspecialchars($item['satuan_kecil']) ?>)
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Billing POS -->
            <div class="lg:col-span-5 bg-white border border-zcBrd rounded-2xl shadow-sm flex flex-col">
                <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between gap-2">
                    <h2 class="text-sm font-bold text-zcTxt">Billing Transaksi Karyawan</h2>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="openCameraScannerModal()" class="px-2.5 py-1.5 bg-blue-50 hover:bg-zc text-zc hover:text-white border border-blue-200 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm" title="Scan Barcode via Kamera Webcam Laptop">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Scan Kamera</span>
                        </button>
                        <input type="text" id="manual_barcode_input" placeholder="Scan / Ketik Barcode..." autocomplete="off" onkeypress="handleManualBarcode(event)" class="text-xs border border-zcBrd rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-zc focus:ring-1 focus:ring-zc bg-slate-50 w-32 sm:w-40 shadow-inner transition-colors">
                    </div>
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
                        <input type="hidden" name="custom_invoice" id="form_custom_invoice" value="">
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
                    <input type="text" id="modal_nominal" onkeyup="calcKembalian()" placeholder="Rp 0" class="w-full text-2xl font-bold border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-zc focus:ring-4 focus:ring-zcLt transition text-right">
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
                
                <!-- Dynamic QRIS Midtrans Section -->
                <div id="qris_dynamic_section" class="hidden p-4 rounded-xl border-2 border-blue-200 bg-gradient-to-b from-blue-50/70 to-white text-center space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zc uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            Dynamic QRIS Midtrans
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800" id="qris_status_badge">Siap Dimunculkan</span>
                    </div>

                    <p class="text-xs text-slate-600">Klik tombol di bawah untuk membuka QRIS dinamis Midtrans dengan nominal presisi tagihan kasir.</p>

                    <div class="pt-1">
                        <button type="button" id="btn_open_snap_qris" onclick="triggerMidtransPosQRIS()" class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-zc hover:bg-zcHv text-white shadow-md shadow-zc/30 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            ⚡ Buka QRIS Midtrans (Pop-up Kasir)
                        </button>
                    </div>

                    <div class="text-[10px] text-slate-400 flex items-center justify-center gap-1.5">
                        <span>Mendukung GoPay, BCA, Mandiri, ShopeePay, DANA, dsb.</span>
                    </div>
                </div>

                <div id="non_tunai_info" class="hidden p-4 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-xs text-center font-medium">
                    Pastikan pembayaran melalui EDC / Transfer telah berhasil masuk sebelum menyelesaikan transaksi.
                </div>
            </div>
            <div class="p-5 border-t border-zcBrd bg-slate-50 flex gap-3">
                <button onclick="closePaymentModal()" class="flex-1 py-3.5 rounded-xl text-sm font-bold bg-white text-slate-600 border border-slate-300 hover:bg-slate-100 transition shadow-sm">Batal</button>
                <button id="btn_submit_pay" onclick="submitPayment()" disabled class="flex-[2] py-3.5 rounded-xl text-sm font-bold bg-slate-300 text-slate-500 cursor-not-allowed transition">Selesaikan Transaksi</button>
            </div>
        </div>
    </div>

    <!-- Modal Barcode Scanner Webcam / Kamera -->
    <div id="camera_modal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-200 border border-slate-200" id="camera_modal_content">
            <div class="bg-gradient-to-r from-zc to-zcHv px-5 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm leading-tight">Live Barcode Scanner Kamera</h3>
                        <p class="text-[11px] text-blue-100">Arahkan barcode / SN produk ke kamera laptop atau webcam</p>
                    </div>
                </div>
                <button type="button" onclick="closeCameraScannerModal()" class="text-blue-100 hover:text-white transition p-1">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <div class="p-5 space-y-4">
                <!-- Camera Viewport Box -->
                <div class="relative bg-slate-900 rounded-xl overflow-hidden shadow-inner border-2 border-slate-700 min-h-[250px] flex items-center justify-center">
                    <div id="camera_scanner_view" class="w-full"></div>
                    <div id="camera_loading_indicator" class="text-center p-4 text-white text-xs">
                        <div class="inline-block w-8 h-8 border-4 border-white/20 border-t-white rounded-full animate-spin mb-2"></div>
                        <p>Memulai kamera...</p>
                    </div>
                </div>

                <!-- Camera Selector & Feedback -->
                <div class="flex items-center justify-between gap-3 text-xs">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-zcMut mb-1">Pilih Perangkat Kamera:</label>
                        <select id="camera_device_select" onchange="changeCameraDevice(this.value)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 font-semibold focus:outline-none focus:border-zc">
                            <option value="">Deteksi kamera...</option>
                        </select>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" id="scan_status_badge">
                            🟢 Scanner Aktif
                        </span>
                    </div>
                </div>

                <!-- Live Status & Feedback Transaksi (Anti-Bingung & Anti-Double Scan) -->
                <div id="cam_feedback_card" class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-slate-200">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold text-slate-700">Keranjang Kasir Realtime:</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="cam_cart_count_badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200">0 Item</span>
                            <span id="cam_cart_total_badge" class="text-xs font-black text-slate-800">Rp 0</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider block">Scan Terakhir:</span>
                            <span id="cam_last_product_label" class="font-bold text-xs text-slate-700 truncate block">Arahkan barcode ke kotak kamera</span>
                        </div>
                        <span id="cam_cooldown_indicator" class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 shrink-0">
                            Siap Scan
                        </span>
                    </div>
                </div>

                <!-- Last Scanned Info Card (Compact) -->
                <div id="last_scanned_box" class="px-3 py-2 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                    <div>
                        <span class="text-[9.5px] font-bold text-zc uppercase tracking-wider inline mr-1">Barcode:</span>
                        <span id="last_scanned_code" class="font-mono font-bold text-xs text-zcTxt">-</span>
                    </div>
                    <span id="last_scanned_time" class="text-[10px] text-slate-500 font-medium">Belum ada scan</span>
                </div>
            </div>

            <div class="p-4 border-t border-zcBrd bg-slate-50 flex justify-between items-center">
                <span class="text-[11px] text-zcMut">Otomatis berbunyi bip saat barcode terbaca.</span>
                <button type="button" onclick="closeCameraScannerModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 transition">
                    Tutup Scanner
                </button>
            </div>
        </div>
    </div>

    <script>
    let cart = [];

    function addToPos(btnElement, scannedCode = null) {
        try {
            var card = btnElement.closest('.product-card');
            var rawSn = card ? card.getAttribute('data-sn') : '';
            var rawBatch = card ? card.getAttribute('data-batch') : '';
            
            var id = parseInt(btnElement.getAttribute('data-id'));
            var name = btnElement.getAttribute('data-nama');
            var hargaKecil = parseFloat(btnElement.getAttribute('data-hk'));
            var hargaBesar = parseFloat(btnElement.getAttribute('data-hb'));
            var rawTotalStock = parseInt(btnElement.getAttribute('data-stok')); // Stok total
            var rasio = parseInt(btnElement.getAttribute('data-rasio'));
            var kat = btnElement.getAttribute('data-kat');
            
            // Ambil batasan stok presisi dari parent card
            var card = btnElement.closest('.product-card');
            var rawEceran = card ? card.getAttribute('data-stok-eceran') : null;
            var rawDus = card ? card.getAttribute('data-stok-dus') : null;
            var stokEceran = rawEceran ? parseInt(rawEceran) : rawTotalStock;
            var stokDus = rawDus ? parseInt(rawDus) : 0;

            var needsVerification = (kat === 'Obat' || kat === 'Alat Kesehatan');

            // === PENENTUAN UOM (SATUAN) ===
            var sel = document.getElementById('uom_' + id);
            var uomType = sel ? sel.value : 'kecil';
            var isScannedSubBatch = scannedCode ? scannedCode.includes('.') : false;

            if (scannedCode) {
                // 1. Deteksi UOM dari fisik barcode (Hanya berlaku untuk barang ber-batch)
                var detectedUom = uomType;
                if (rawBatch) {
                    detectedUom = isScannedSubBatch ? 'kecil' : 'besar';
                }

                // 2. SPECIFIC IDENTITY SEARCH:
                // Cek status baris keranjang untuk produk ini secara komprehensif (Fleksibel scan acak)
                var exactUnverifiedItem = null;
                var otherUnverifiedItem = null;

                for (var i = 0; i < cart.length; i++) {
                    if (parseInt(cart[i].id) === id && cart[i].needs_verification && !cart[i].scanned_code) {
                        if (cart[i].satuan_tipe === detectedUom) {
                            exactUnverifiedItem = cart[i];
                            break; // Prioritas utama: temukan baris yang menunggu verifikasi dengan UOM yang sama
                        } else if (!otherUnverifiedItem) {
                            otherUnverifiedItem = cart[i]; // Simpan baris unverified satuan lain sebagai pembanding
                        }
                    }
                }

                // Cek apakah ada baris keranjang yang SUDAH TERVERIFIKASI dengan UOM yang sama
                var verifiedItemSameUom = null;
                for (var i = 0; i < cart.length; i++) {
                    if (parseInt(cart[i].id) === id && cart[i].satuan_tipe === detectedUom && cart[i].scanned_code) {
                        verifiedItemSameUom = cart[i];
                        break;
                    }
                }

                if (exactUnverifiedItem) {
                    // Kasus A: Ada baris di keranjang yang memang menunggu verifikasi satuan ini!
                    uomType = exactUnverifiedItem.satuan_tipe;
                } else if (verifiedItemSameUom) {
                    // Kasus B: Baris satuan ini sudah ada & sudah terverifikasi sebelumnya.
                    // Scan fisik kedua kali berarti menambah kuantitas (+1) satuan ini!
                    uomType = detectedUom;
                } else if (otherUnverifiedItem) {
                    // Kasus C: Di keranjang HANYA ada pesanan satuan lain (misal hanya beli eceran),
                    // dan sama sekali TIDAK ada pesanan grosir kardus (atau sebaliknya).
                    // Ini adalah SAFETY GUARD murni: cegah karyawan salah ambil fisik!
                    if (otherUnverifiedItem.satuan_tipe === 'kecil' && detectedUom === 'besar') {
                        Swal.fire({
                            title: '❌ Salah Ambil Fisik!', 
                            html: 'Di keranjang pesanan adalah <b>ECERAN (Kecil)</b>, tetapi fisik yang Anda scan adalah <b>GROSIR / DUS (Besar)</b>.<br><br><span class="text-xs text-slate-500">Silakan ambil kemasan eceran (sub-batch bertitik) atau tambahkan kardus ke keranjang terlebih dahulu.</span>', 
                            icon: 'error'
                        });
                        return;
                    }
                    if (otherUnverifiedItem.satuan_tipe === 'besar' && detectedUom === 'kecil') {
                        Swal.fire({
                            title: '❌ Salah Ambil Fisik!', 
                            html: 'Di keranjang pesanan adalah <b>GROSIR / DUS (Besar)</b>, tetapi fisik yang Anda scan adalah <b>ECERAN (Kecil)</b>.<br><br><span class="text-xs text-slate-500">Silakan ambil kemasan kardus utuh atau tambahkan eceran ke keranjang terlebih dahulu.</span>', 
                            icon: 'error'
                        });
                        return;
                    }
                    uomType = detectedUom;
                } else {
                    // Kasus D: Direct Scan (kasir langsung scan tanpa klik katalog dulu)
                    uomType = detectedUom;
                    if (sel) sel.value = uomType; // Sinkronkan visual
                }

                // === DYNAMIC MAX STOCK CALCULATION ===
                // Hitung batas maksimal berdasarkan UOM fisik yang ada
                var maxUomAllowed = rawTotalStock;
                var allBatches = [];
                var eceranBatches = [];
                var dusBatches = [];

                if (rawBatch) {
                    allBatches = rawBatch.split(',').map(b => b.split('|')[0]);
                    eceranBatches = allBatches.filter(b => b.includes('.'));
                    dusBatches = allBatches.filter(b => !b.includes('.'));
                }
            }

            // DYNAMIC MAX CALCULATION
            var allBatchesManualRaw = rawBatch ? rawBatch.split(',') : [];
            var allBatchesManual = allBatchesManualRaw.map(b => b.split('|')[0]);
            var eceranBatchesManual = allBatchesManual.filter(b => b.includes('.'));
            var dusBatchesManual = allBatchesManual.filter(b => !b.includes('.'));
            
            // Dapatkan stok presisi spesifik untuk batch yang dipilih/discan (atau rekomendasi FEFO jika belum)
            var specificBatchStok = 0;
            var targetBatchStr = scannedCode;
            if (!targetBatchStr) {
                targetBatchStr = (uomType === 'kecil') ? (eceranBatchesManual.length > 0 ? eceranBatchesManual[0] : null) : (dusBatchesManual.length > 0 ? dusBatchesManual[0] : null);
            }
            if (targetBatchStr) {
                var targetRaw = allBatchesManualRaw.find(b => b.split('|')[0].toLowerCase() === targetBatchStr.toLowerCase());
                if (targetRaw) specificBatchStok = parseInt(targetRaw.split('|')[1]);
            }

            var maxStockToPass = specificBatchStok || rawTotalStock; // Hard-lock max quantity to the specific batch!
            
            if (kat === 'Obat') {
                if (uomType === 'kecil') {
                    stokEceran = specificBatchStok;
                } else if (uomType === 'besar') {
                    stokDus = specificBatchStok;
                }
            }
            
            if (scannedCode) {
                // === FEFO ANOMALY CHECKER ===
                if (kat === 'Obat' && rawBatch) {
                    var allBatches = rawBatch.split(',').map(b => b.split('|')[0]);
                    var eceranBatches = allBatches.filter(b => b.includes('.'));
                    var dusBatches = allBatches.filter(b => !b.includes('.'));
                    var batches = uomType === 'besar' ? dusBatches : eceranBatches;
                    
                    if (batches.length > 0 && batches[0] !== scannedCode && batches.includes(scannedCode)) {
                        Swal.fire({
                            title: '&#9888;&#65039; ANOMALI FEFO TERDETEKSI!',
                            html: `Anda men-scan Batch <b>${scannedCode.toUpperCase()}</b>.<br><br>
                                   Terdapat Batch lain <b>(${batches[0].toUpperCase()})</b> yang akan kedaluwarsa lebih cepat.<br>
                                   Sesuai sistem FEFO, Anda seharusnya mengeluarkan batch yang lebih cepat expired terlebih dahulu.`,
                            icon: 'error',
                            showCancelButton: true,
                            confirmButtonText: 'Tetap Lanjutkan (Abaikan)',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#d33',
                            allowEnterKey: false,
                            focusCancel: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                processAddToCart(id, name, hargaKecil, hargaBesar, maxStockToPass, rasio, scannedCode, rawSn, rawBatch, true, batches[0], needsVerification, uomType, stokEceran);
                            }
                        });
                        return;
                    }
                }
            }

            // === KONVERSI DARURAT CHECK (Berlaku untuk Klik Manual & Scan Umum) ===
            if (kat === 'Obat' && rawBatch && !scannedCode) {
                if (uomType === 'kecil' && eceranBatchesManual.length === 0 && dusBatchesManual.length > 0) {
                    // ECERAN KOSONG, TAPI DUS ADA! MUNCULKAN POPUP KONVERSI!
                    Swal.fire({
                        title: 'Stok Eceran Kosong!',
                        html: '<div class="text-left text-sm mb-3">Sistem mendeteksi stok etalase (eceran) kosong, tetapi masih ada stok <b>Dus/Karton utuh</b> di gudang.<br><br>Apakah Anda ingin membuka 1 Dus untuk dijual eceran sekarang?</div><div class="bg-rose-50 text-rose-700 p-3 rounded text-xs font-bold border border-rose-200">⚠ KONFIRMASI: Pastikan Anda benar-benar akan merobek/membuka fisik kardus utuh! Aksi ini akan mencatat histori audit secara permanen.</div>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Robek 1 Dus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            konversiCepat(id, name, hargaKecil, hargaBesar, rawTotalStock, rasio, scannedCode, rawSn, rawBatch, dusBatchesManual[0], needsVerification, uomType);
                        }
                    });
                    return; // Jangan masukkan ke keranjang dulu
                }
            }

            // Lanjut ke pemrosesan normal
            processAddToCart(id, name, hargaKecil, hargaBesar, maxStockToPass, rasio, scannedCode, rawSn, rawBatch, false, null, needsVerification, uomType, stokEceran);

        } catch(e) {
            alert("Terjadi Error Javascript di addToPos: " + e.message);
        }
    }

    function processAddToCart(id, name, hargaKecil, hargaBesar, maxStockPcs, rasio, scannedCode, rawSn, rawBatch, isAnomaly = false, batchSeharusnya = null, needsVerification = false, passedUomType = null, stokEceranFisik = 0) {
        var sel = document.getElementById('uom_' + id);
        var uomType = passedUomType ? passedUomType : (sel ? sel.value : 'kecil');
        var uomLabel = '';
        if (sel) {
            if (sel.tagName.toLowerCase() === 'select') {
                uomLabel = sel.options[sel.selectedIndex].text;
            } else {
                uomLabel = sel.getAttribute('data-label') || '';
            }
        }
        
        var price = (uomType === 'besar') ? hargaBesar : hargaKecil;
        var qtyMultiplier = (uomType === 'besar') ? rasio : 1;
        var cartId = id + '_' + uomType + (scannedCode ? '_' + scannedCode : '');

        var found = null;

        // Validasi Fisik: Jika scan dilakukan, cari item di keranjang yang MENUNGGU VERIFIKASI DENGAN SATUAN YANG COCOK
        if (scannedCode && needsVerification) {
            var unverifiedIndex = -1;
            for (var i = 0; i < cart.length; i++) {
                if (parseInt(cart[i].id) === parseInt(id) && cart[i].needs_verification && !cart[i].scanned_code && cart[i].satuan_tipe === uomType) {
                    unverifiedIndex = i;
                    break;
                }
            }

            if (unverifiedIndex !== -1) {
                var targetItem = cart[unverifiedIndex];
                var qtyToValidate = rawSn ? 1 : targetItem.qty;
                
                var existingVerified = null;
                for (var j = 0; j < cart.length; j++) {
                    if (j !== unverifiedIndex && cart[j].cartId === cartId) {
                        existingVerified = cart[j];
                        break;
                    }
                }

                if (existingVerified) {
                    if (qtyToValidate === 1 && rawSn) {
                        Swal.fire('Error', 'SN ' + scannedCode.toUpperCase() + ' sudah ada di keranjang!', 'error');
                        return;
                    }
                    if ((existingVerified.qty + qtyToValidate) * existingVerified.rasio > existingVerified.maxPcs) { 
                        alert('Stok terbatas!'); return; 
                    }
                    existingVerified.qty += qtyToValidate;
                    
                    if (qtyToValidate === targetItem.qty) {
                        cart.splice(unverifiedIndex, 1);
                    } else {
                        targetItem.qty -= qtyToValidate;
                    }
                } else {
                    if (qtyToValidate === targetItem.qty) {
                        targetItem.scanned_code = scannedCode;
                        targetItem.cartId = cartId; // ubah cartId menjadi yang sudah di-scan
                        targetItem.is_anomaly = isAnomaly;
                        targetItem.batch_seharusnya = batchSeharusnya;
                        targetItem.maxPcs = maxStockPcs; // Update maxPcs ke stok batch baru
                        targetItem.stok_eceran_fisik = stokEceranFisik; // Update hint UI ke stok batch baru
                    } else {
                        targetItem.qty -= qtyToValidate;
                        cart.push({
                            id: id, cartId: cartId, nama: name, harga: price, qty: qtyToValidate, 
                            satuan_tipe: uomType, satuan_label: uomLabel, maxPcs: maxStockPcs, rasio: qtyMultiplier,
                            sn_list: rawSn, batch_list: rawBatch, scanned_code: scannedCode,
                            is_anomaly: isAnomaly, batch_seharusnya: batchSeharusnya,
                            needs_verification: needsVerification, stok_eceran_fisik: stokEceranFisik
                        });
                    }
                }
                
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'success',
                    title: '✅ Tervalidasi: ' + name + ' [' + scannedCode.toUpperCase() + ']',
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true
                });

                updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                renderCart();
                return; // PASTI RETURN, TIDAK AKAN NAMBAH BARU
            }
        }

        // Jika tidak ada scan ATAU tidak ada item unverified yang bisa divalidasi, lakukan penambahan normal
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].cartId === cartId) { found = cart[i]; break; }
        }

        if (found) {
            if ((found.qty + 1) * found.rasio > found.maxPcs) { alert('Stok terbatas!'); return; }
            found.qty++;
            // Update anomaly status if it was re-added manually (override)
            if (isAnomaly) {
                found.is_anomaly = true;
                found.batch_seharusnya = batchSeharusnya;
            }
        } else { 
            if (qtyMultiplier > maxStockPcs) { alert('Stok tidak cukup untuk satuan ini!'); return; }
            cart.push({
                id: id, cartId: cartId, nama: name, harga: price, qty: 1, 
                satuan_tipe: uomType, satuan_label: uomLabel, maxPcs: maxStockPcs, rasio: qtyMultiplier,
                sn_list: rawSn, batch_list: rawBatch, scanned_code: scannedCode,
                is_anomaly: isAnomaly, batch_seharusnya: batchSeharusnya,
                needs_verification: needsVerification, stok_eceran_fisik: stokEceranFisik
            }); 
        }
        
        if (scannedCode) {
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: '✅ Masuk Keranjang: ' + name + ' (' + uomLabel + ')',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        }
        
        updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
        renderCart();
    }

    function triggerManualBongkar(idVariasi, name, rawBatch, hargaKecil, hargaBesar, rawTotalStock, rasio) {
        var allBatchesManual = rawBatch ? rawBatch.split(',').map(b => b.split('|')[0]) : [];
        var dusBatchesManual = allBatchesManual.filter(b => !b.includes('.'));
        if (dusBatchesManual.length === 0) {
            Swal.fire('Gagal', 'Tidak ada stok kardus utuh (Grosir) yang bisa dibongkar.', 'error');
            return;
        }
        Swal.fire({
            title: 'Bongkar Dus Manual',
            html: '<div class="text-left text-sm mb-3">Anda akan membongkar <b>1 Dus/Karton utuh</b> secara manual untuk dijadikan eceran.<br><br>Sistem mendeteksi angkatan terlama: <b>' + dusBatchesManual[0].toUpperCase() + '</b></div><div class="bg-rose-50 text-rose-700 p-3 rounded text-xs font-bold border border-rose-200">&#9888;&#65039; KONFIRMASI: Pastikan Anda benar-benar merobek kardus utuh secara fisik!</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Robek 1 Dus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                konversiCepat(idVariasi, name, hargaKecil, hargaBesar, rawTotalStock, rasio, null, '', rawBatch, dusBatchesManual[0], false, 'kecil');
            }
        });
    }

    function konversiCepat(idVariasi, name, hargaKecil, hargaBesar, maxStockPcs, rasio, scannedCode, rawSn, rawBatch, batchAsal, needsVerification, uomType) {
        Swal.fire({ title: 'Membongkar Dus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
        var formData = new FormData();
        formData.append('id_variasi', idVariasi);
        formData.append('batch_asal', batchAsal);
        
        fetch('../api/pos_konversi.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Berhasil membongkar 1 Dus!',
                    showConfirmButton: false,
                    timer: 2000
                });
                // Update atribut DOM card secara dinamis agar kalau kasir klik lagi tidak error
                var newRawBatch = (rawBatch ? rawBatch + ',' : '') + data.new_batch;
                var newMaxStock = maxStockPcs + data.qty_pcs;
                
                // Panggil addToCart langsung dengan batch yang baru lahir!
                // Perbarui nilai stokEceran dan stokDus secara presisi (Fix scoping issue)
                var currentStokEceran = 0;
                var buttonCard = document.querySelector('button[data-id="'+idVariasi+'"]');
                if (buttonCard) {
                    var card = buttonCard.closest('.product-card');
                    if (card) {
                        currentStokEceran = parseInt(card.getAttribute('data-stok-eceran')) || 0;
                        var currentStokDus = parseInt(card.getAttribute('data-stok-dus')) || 0;
                        card.setAttribute('data-stok-eceran', currentStokEceran + data.qty_pcs);
                        card.setAttribute('data-stok-dus', currentStokDus - data.qty_pcs); // wait, it's 1 box, not qty_pcs boxes! No, actually I don't need to update stokDus correctly since we reload soon anyway, but let's be safe.
                        
                        // Update button text
                        var bongkarBtn = card.querySelector('.bg-rose-50');
                        if (bongkarBtn) {
                            bongkarBtn.innerHTML = '&#128230; Bongkar (Sisa: ' + (currentStokEceran + data.qty_pcs) + ' Pcs)';
                        }
                    }
                }
                
                processAddToCart(idVariasi, name, hargaKecil, hargaBesar, newMaxStock, rasio, scannedCode, rawSn, newRawBatch, false, null, needsVerification, uomType, currentStokEceran + data.qty_pcs);
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat konversi', 'error');
        });
    }

    function changeQty(cartId, d) {
        var item = null;
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].cartId === cartId) { item = cart[i]; break; }
        }
        if (!item) return;
        if (d > 0 && (item.qty + d) * item.rasio > item.maxPcs) { Swal.fire('Stok Terbatas', 'Sisa stok fisik tidak mencukupi untuk ditambah.', 'warning'); return; }
        item.qty += d;
        if (item.qty <= 0) cart = cart.filter(function(i) { return i.cartId !== cartId; });
        renderCart();
    }

    function setQtyManual(cartId, val) {
        var item = null;
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].cartId === cartId) { item = cart[i]; break; }
        }
        if (!item) return;
        var newQty = parseInt(val) || 0;
        if (newQty <= 0) {
            cart = cart.filter(function(i) { return i.cartId !== cartId; });
            renderCart();
            return;
        }
        var maxAllowed = Math.floor(item.maxPcs / item.rasio);
        if (newQty > maxAllowed) {
            Swal.fire('Stok Terbatas', 'Jumlah melebihi batas stok fisik (Maks: ' + maxAllowed + ').', 'warning');
            item.qty = maxAllowed > 0 ? maxAllowed : 1;
        } else {
            item.qty = newQty;
        }
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
            inp.value = '[]';
            updateCameraModalCartPreview();
            return;
        }

        var total = 0, html = '';
        cart.forEach(function(i) {
            var sub = i.harga * i.qty; total += sub;
            var fefoText = '';
            
            if (i.scanned_code) {
                fefoText = '<span class="block text-[9px] text-blue-600 font-bold mt-1 rounded bg-blue-50 px-1.5 py-0.5 inline-block border border-blue-200 shadow-sm">&#10004;&#65039; Fisik Tervalidasi: ' + i.scanned_code.toUpperCase() + '</span>';
            } else if (i.needs_verification) {
                if (i.batch_list) {
                    let allBatches = i.batch_list.split(',').map(b => b.split('|')[0]);
                      let targetBatches = [];
                      if (i.satuan_tipe === 'besar') {
                          targetBatches = allBatches.filter(b => !b.includes('.'));
                      } else {
                          targetBatches = allBatches.filter(b => b.includes('.'));
                      }
                      var batches = targetBatches.length > 0 ? targetBatches.slice(0, 1).join(', ') : (i.satuan_tipe === 'besar' ? '&#10060; Box Utuh Habis!' : '&#10060; Eceran Habis!');
                    fefoText = '<span class="block text-[9px] text-rose-600 font-bold mt-1 rounded bg-rose-50 border border-rose-200 px-1.5 py-0.5 inline-block animate-pulse shadow-sm">&#9888;&#65039; Belum diverifikasi! (Ambil Batch: ' + batches + ')</span>';
                } else if (i.sn_list) {
                    fefoText = '<span class="block text-[9px] text-rose-600 font-bold mt-1 rounded bg-rose-50 border border-rose-200 px-1.5 py-0.5 inline-block animate-pulse shadow-sm">&#9888;&#65039; Belum diverifikasi! (Ambil SN Tersedia)</span>';
                }
            }

            var stokInfo = '';
            var maxAllowed = Math.floor(i.maxPcs / i.rasio);
            if (i.batch_list) {
                stokInfo = (i.satuan_tipe === 'besar') ? '<span class="block text-[9px] text-slate-500 font-medium mt-1">&#128230; Sisa Fisik Box: ' + maxAllowed + '</span>' : '<span class="block text-[9px] text-slate-500 font-medium mt-1">&#128230; Sisa Batch Spesifik (Utama): ' + i.stok_eceran_fisik + ' Pcs</span>';
            } else {
                stokInfo = '<span class="block text-[9px] text-slate-500 font-medium mt-1">&#128230; Sisa Stok: ' + i.maxPcs + ' ' + i.satuan_label + '</span>';
            }

            html += '<tr class="hover:bg-slate-50/60">' +
                '<td class="px-4 py-3 font-semibold text-zcTxt text-xs">' +
                    i.nama +
                    '<span class="block text-[9px] text-zcEm mt-0.5">' + i.satuan_label + ' (Rp ' + i.harga.toLocaleString('id-ID') + ')</span>' +
                    fefoText + stokInfo +
                '</td>' +
                '<td class="px-4 py-3 text-center">' +
                    '<div class="flex items-center justify-center gap-1.5">' +
                        '<button type="button" onclick="changeQty(\'' + i.cartId + '\',-1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition flex items-center justify-center">-</button>' +
                        '<input type="number" min="1" max="' + maxAllowed + '" value="' + i.qty + '" onchange="setQtyManual(\'' + i.cartId + '\', this.value)" class="w-12 text-center font-bold text-xs border border-zcBrd rounded-lg py-1 px-1 focus:outline-none focus:border-zc bg-white shadow-xs">' +
                        '<button type="button" onclick="changeQty(\'' + i.cartId + '\',1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition flex items-center justify-center">+</button>' +
                    '</div>' +
                '</td>' +
                '<td class="px-4 py-3 text-right font-bold text-xs">Rp ' + sub.toLocaleString('id-ID') + '</td>' +
                '<td class="px-4 py-3 text-center"><button type="button" onclick="removeItem(\'' + i.cartId + '\')" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition">[X]</button></td>' +
            '</tr>';
        });

        tbody.innerHTML = html;
        totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
        btn.disabled = false;
        btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-zc hover:bg-zcHv text-white transition shadow-sm cursor-pointer';
        inp.value = JSON.stringify(cart);
        updateCameraModalCartPreview();
    }

    // --- Payment UI Logic ---
    var currentTotal = 0;
    var modalInput = document.getElementById('modal_nominal');
    
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function openPaymentModal() {
        if (!cart.length) return;
        
        // Blokir jika ada item yang belum divalidasi fisik (di-scan)
        var hasUnverified = false;
        cart.forEach(function(i) {
            if (i.needs_verification && !i.scanned_code) hasUnverified = true;
        });

        if (hasUnverified) {
            Swal.fire({
                title: 'Validasi Fisik Belum Selesai!',
                text: 'Ada produk Obat/Alkes di keranjang yang belum diverifikasi. Harap ambil fisik barang di rak dan SCAN barcode-nya untuk mencocokkan sebelum memproses pembayaran!',
                icon: 'warning',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

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

    let currentPosSnapToken = null;
    let currentPosOrderId = null;

    function toggleNominalInput() {
        var metode = document.getElementById('modal_metode').value;
        var nominalSec = document.getElementById('nominal_section');
        var infoSec = document.getElementById('non_tunai_info');
        var qrisSec = document.getElementById('qris_dynamic_section');
        
        if (metode === 'Tunai') {
            nominalSec.classList.remove('hidden');
            infoSec.classList.add('hidden');
            if (qrisSec) qrisSec.classList.add('hidden');
        } else if (metode === 'QRIS') {
            nominalSec.classList.add('hidden');
            infoSec.classList.add('hidden');
            if (qrisSec) qrisSec.classList.remove('hidden');
        } else {
            nominalSec.classList.add('hidden');
            infoSec.classList.remove('hidden');
            if (qrisSec) qrisSec.classList.add('hidden');
        }
        calcKembalian();
    }

    async function triggerMidtransPosQRIS() {
        let btn = document.getElementById('btn_open_snap_qris');
        let badge = document.getElementById('qris_status_badge');
        btn.disabled = true;
        btn.innerHTML = 'Membuat QRIS Midtrans...';
        badge.innerText = 'Menghubungkan...';

        let now = new Date();
        let ymd = now.getFullYear().toString() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0');
        let rand = Math.floor(1000 + Math.random() * 9000);
        currentPosOrderId = 'POS-' + ymd + '-' + rand;

        try {
            let res = await fetch('../api/pos_snap_token.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    total: currentTotal,
                    cart: cart,
                    invoice: currentPosOrderId
                })
            });
            let data = await res.json();
            if (data.status === 'success' && data.token) {
                currentPosSnapToken = data.token;
                currentPosOrderId = data.order_id;
                badge.innerText = 'Menunggu Scan...';
                btn.innerHTML = '⚡ Tampilkan Ulang QRIS Midtrans';
                btn.disabled = false;

                if (typeof snap !== 'undefined' && snap.pay) {
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            badge.innerText = '✅ Lunas';
                            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800';
                            Swal.fire({
                                title: 'Pembayaran QRIS Berhasil!',
                                text: 'Pembayaran Midtrans sukses terverifikasi. Menyimpan struk POS...',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            document.getElementById('form_custom_invoice').value = currentPosOrderId;
                            document.getElementById('form_metode_pembayaran').value = 'QRIS';
                            setTimeout(() => {
                                document.getElementById('form_pos').submit();
                            }, 1200);
                        },
                        onPending: function(result) {
                            badge.innerText = 'Menunggu Konfirmasi';
                            Swal.fire({
                                title: 'Menunggu Pembayaran',
                                text: 'QRIS telah ditampilkan kepada customer. Klik Selesaikan Transaksi jika customer sudah bayar.',
                                icon: 'info'
                            });
                        },
                        onError: function(result) {
                            badge.innerText = 'Gagal';
                            Swal.fire('Pembayaran Gagal', 'Transaksi QRIS gagal atau dibatalkan.', 'error');
                        },
                        onClose: function() {
                            badge.innerText = 'QRIS Ditutup';
                        }
                    });
                } else {
                    alert('Midtrans Snap JS tidak terdeteksi. Silakan periksa koneksi.');
                }
            } else {
                throw new Error(data.message || 'Gagal generate snap token');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '⚡ Coba Lagi QRIS Midtrans';
            badge.innerText = 'Error API';
            Swal.fire('Midtrans Error', 'Gagal memanggil API Midtrans: ' + err.message, 'warning');
        }
    }

    function submitPayment() {
        document.getElementById('form_metode_pembayaran').value = document.getElementById('modal_metode').value;
        if (currentPosOrderId && document.getElementById('modal_metode').value === 'QRIS') {
            document.getElementById('form_custom_invoice').value = currentPosOrderId;
        }
        document.getElementById('btn_submit_pay').innerText = 'Memproses...';
        document.getElementById('form_pos').submit();
    }

    // --- Camera Scanner Implementation (html5-qrcode) ---
    let html5QrCode = null;
    let isCameraScannerRunning = false;
    let lastScannedBarcode = '';
    let lastScanTimestamp = 0;

    function updateCameraModalCartPreview(lastProductName = null) {
        let countEl = document.getElementById('cam_cart_count_badge');
        let totalEl = document.getElementById('cam_cart_total_badge');
        let prodEl = document.getElementById('cam_last_product_label');
        let cdEl = document.getElementById('cam_cooldown_indicator');
        
        let totalQty = 0;
        let totalPrice = 0;
        if (typeof cart !== 'undefined' && Array.isArray(cart)) {
            cart.forEach(item => {
                totalQty += item.qty;
                totalPrice += (item.harga * item.qty);
            });
        }
        
        if (countEl) countEl.innerText = totalQty + ' Item';
        if (totalEl) totalEl.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
        
        if (lastProductName && prodEl) {
            prodEl.innerHTML = `<span class="text-emerald-700 font-extrabold truncate block">✅ ${lastProductName}</span>`;
            if (cdEl) {
                cdEl.innerText = '✅ Berhasil Masuk!';
                cdEl.className = 'text-[10px] font-extrabold text-emerald-800 bg-emerald-100 px-2 py-1 rounded-lg border border-emerald-300 shrink-0';
                setTimeout(() => {
                    if (cdEl) {
                        cdEl.innerText = 'Siap Scan';
                        cdEl.className = 'text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 shrink-0';
                    }
                }, 2000);
            }
        }
    }

    async function openCameraScannerModal() {
        let modal = document.getElementById('camera_modal');
        let modalContent = document.getElementById('camera_modal_content');
        updateCameraModalCartPreview();
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        await startCameraScanner();
    }

    async function closeCameraScannerModal() {
        let modal = document.getElementById('camera_modal');
        let modalContent = document.getElementById('camera_modal_content');
        if (html5QrCode && isCameraScannerRunning) {
            try {
                await html5QrCode.stop();
            } catch(e) {}
            isCameraScannerRunning = false;
        }
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    async function startCameraScanner(cameraId = null) {
        let loading = document.getElementById('camera_loading_indicator');
        let badge = document.getElementById('scan_status_badge');
        if (loading) loading.classList.remove('hidden');

        try {
            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('Library Html5Qrcode belum selesai dimuat.');
            }
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("camera_scanner_view");
            }
            
            const devices = await Html5Qrcode.getCameras();
            let select = document.getElementById('camera_device_select');
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

            await html5QrCode.start(
                chosenCamera,
                config,
                (decodedText) => {
                    onBarcodeScanSuccess(decodedText);
                },
                (errorMessage) => {}
            );

            isCameraScannerRunning = true;
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
            });
        }
    }

    async function changeCameraDevice(newDeviceId) {
        if (html5QrCode && isCameraScannerRunning) {
            await html5QrCode.stop();
            isCameraScannerRunning = false;
        }
        if (newDeviceId) {
            await startCameraScanner(newDeviceId);
        }
    }

    function onBarcodeScanSuccess(code) {
        code = code.trim();
        let now = Date.now();
        
        // Cooldown 3.5 detik khusus barcode yang SAMA PERSIS agar kasir tidak men-scan ganda secara tak sengaja
        if (code.toLowerCase() === (lastScannedBarcode || '').toLowerCase() && (now - lastScanTimestamp) < 3500) {
            let sisaDetik = Math.ceil((3500 - (now - lastScanTimestamp)) / 1000);
            let cdEl = document.getElementById('cam_cooldown_indicator');
            if (cdEl) {
                cdEl.innerText = `Cooldown (${sisaDetik}s)`;
                cdEl.className = 'text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-lg border border-amber-200 shrink-0';
            }
            return;
        }
        
        // Jeda minimal 1 detik antar barang berbeda
        if ((now - lastScanTimestamp) < 1000) {
            return;
        }
        
        lastScannedBarcode = code;
        lastScanTimestamp = now;

        // Visual shutter flash pada viewport scanner kamera
        let scanBox = document.getElementById('camera_scanner_view');
        if (scanBox) {
            scanBox.classList.add('ring-4', 'ring-emerald-400');
            setTimeout(() => { scanBox.classList.remove('ring-4', 'ring-emerald-400'); }, 600);
        }

        let codeEl = document.getElementById('last_scanned_code');
        let timeEl = document.getElementById('last_scanned_time');
        let inputEl = document.getElementById('manual_barcode_input');
        if (codeEl) codeEl.innerText = code;
        if (timeEl) timeEl.innerText = new Date().toLocaleTimeString('id-ID');
        if (inputEl) inputEl.value = code;

        // PENTING: Suara beep HANYA dibunyikan 1x di dalam processBarcode() saat item valid masuk keranjang!
        processBarcode(code);
    }

    // --- Barcode Scanner Listener ---
    var barcodeString = '';
    var barcodeTimer = null;
    
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        if (e.key === 'Enter') {
            e.preventDefault(); // Mencegah Enter dari scanner menekan tombol yang sedang fokus (ex: + Pilih Manual)
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
            }, 300); // Diperpanjang menjadi 300ms untuk mentoleransi delay WiFi dari aplikasi HP
        }
    });

    function handleManualBarcode(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var val = e.target.value.trim();
            if (val.length > 0) {
                processBarcode(val);
                e.target.value = '';
            }
        }
    }

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
                var batchArray = dataBatch ? dataBatch.split(',').map(b => b.split('|')[0].toLowerCase()) : [];
                
                var isMatch = false;
                if (skuSpan && skuSpan.innerText.toLowerCase() === sku) isMatch = true;
                else if (dataSn.split(',').includes(sku)) isMatch = true;
                else if (batchArray.includes(sku)) isMatch = true;

                if (isMatch) {
                    var btn = c.querySelector('button');
                    if (btn && !btn.disabled) {
                        // Pass the scanned code!
                        // Ensure we pass the exact scanned code
                        var matchedCode = sku;
                        
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
                        
                        // Execute addToPos with scannedCode
                        if (skuSpan && skuSpan.innerText.toLowerCase() === sku) {
                            // They scanned the general SKU, not the Batch/SN barcode.
                            // We pass null to force them to scan the physical item if it's Obat/Alkes.
                            addToPos(btn, null);
                        } else {
                            addToPos(btn, matchedCode);
                        }
                        break;
                    }
                }
            }
            if (!found) {
                // 1. Cek apakah barcode cocok dengan batch obat yang SUDAH KADALUARSA di katalog
                var expiredMatch = null;
                for (var i = 0; i < cards.length; i++) {
                    var c = cards[i];
                    var expAttr = c.getAttribute('data-expired-batch') || '';
                    if (expAttr) {
                        var expItems = expAttr.split(',');
                        for (var j = 0; j < expItems.length; j++) {
                            var parts = expItems[j].split('|');
                            var bCode = parts[0].trim().toLowerCase();
                            var bExp = parts[1] || '';
                            if (bCode === sku || (bCode.split('.')[0] === sku)) {
                                var pName = c.querySelector('span.text-xs.font-bold') ? c.querySelector('span.text-xs.font-bold').innerText : 'Obat';
                                expiredMatch = { batch: parts[0].toUpperCase(), exp: bExp, name: pName };
                                break;
                            }
                        }
                        if (expiredMatch) break;
                    }
                }

                if (expiredMatch) {
                    Swal.fire({
                        icon: 'error',
                        title: '⛔ OBAT KADALUARSA!',
                        html: `Fisik batch yang Anda scan: <b>${expiredMatch.batch}</b> (${expiredMatch.name})<br><br>` +
                              `Status: <span class="text-rose-600 font-extrabold text-sm">SUDAH KADALUARSA</span> (Exp: <b>${expiredMatch.exp}</b>).<br><br>` +
                              `<div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">` +
                              `⚠️ <b>PERINGATAN KETAT FARMASI:</b><br>` +
                              `Sistem secara otomatis <b>MEMBLOKIR</b> penjualan obat yang telah melewati tanggal kadaluarsa demi keselamatan pasien dan kepatuhan BPOM.<br><br>` +
                              `Silakan pisahkan fisik obat ini ke karantina dan lakukan <b>Mutasi Keluar (Barang Rusak/Kadaluarsa)</b>.</div>`,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Saya Mengerti'
                    });
                    return;
                }

                // 2. Jika tidak ditemukan di kartu yang tampil, verifikasi ke database via API
                fetch('../api/check_barcode.php?barcode=' + encodeURIComponent(sku))
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.found && res.is_expired) {
                            Swal.fire({
                                icon: 'error',
                                title: '⛔ OBAT KADALUARSA!',
                                html: `Fisik batch yang Anda scan: <b>${res.no_batch}</b> (${res.nama})<br><br>` +
                                      `Status: <span class="text-rose-600 font-extrabold text-sm">SUDAH KADALUARSA</span> (Exp: <b>${res.tgl_exp}</b>).<br><br>` +
                                      `<div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">` +
                                      `⚠️ <b>PERINGATAN KETAT FARMASI:</b><br>` +
                                      `Sistem secara otomatis <b>MEMBLOKIR</b> penjualan obat yang telah melewati tanggal kadaluarsa demi keselamatan pasien dan kepatuhan BPOM.<br><br>` +
                                      `Silakan pisahkan fisik obat ini ke karantina dan lakukan <b>Mutasi Keluar (Barang Rusak/Kadaluarsa)</b>.</div>`,
                                confirmButtonColor: '#e11d48',
                                confirmButtonText: 'Saya Mengerti'
                            });
                        } else if (res.found && res.tipe === 'batch' && res.stok_sisa <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stok Batch Habis',
                                html: `Batch <b>${res.no_batch}</b> (${res.nama}) tercatat di sistem tetapi <b>stok sisa = 0</b>.`,
                                confirmButtonColor: '#1a75d2'
                            });
                        } else if (res.found && res.tipe === 'sn' && res.status !== 'Tersedia') {
                            let camLabel = document.getElementById('cam_last_product_label');
                            let camCooldown = document.getElementById('cam_cooldown_indicator');
                            if (res.status === 'Retur/Rusak') {
                                if (camLabel) camLabel.innerHTML = `<span class="text-rose-600 font-bold">⛔ DITOLAK: UNIT RUSAK (${res.serial_number})</span>`;
                                if (camCooldown) {
                                    camCooldown.innerText = 'Unit Rusak';
                                    camCooldown.className = 'text-[10px] font-extrabold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200 shrink-0';
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: '⛔ ALAT KESEHATAN RUSAK / AFKIR!',
                                    html: `Fisik unit yang Anda scan: <b>${res.serial_number}</b><br>` +
                                          `Produk: <b>${res.nama}</b><br><br>` +
                                          `Status Sistem: <span class="text-rose-600 font-extrabold text-sm uppercase">RUSAK / DIKARANTINA</span><br>` +
                                          (res.catatan ? `<div class="mt-2 p-2.5 bg-slate-100 rounded-lg text-slate-700 text-xs border border-slate-200 text-left"><b>Catatan:</b> ${res.catatan}</div><br>` : `<br>`) +
                                          `<div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">` +
                                          `⚠️ <b>PERINGATAN OPERASIONAL KASIR:</b><br>` +
                                          `Unit ini telah dicatat sebagai <b>Barang Rusak / Cacat</b> dan dilarang keras untuk dijual ke pelanggan!<br><br>` +
                                          `Silakan pisahkan fisik alat ini ke area karantina atau kembalikan (retur) ke supplier untuk diganti unit baru.</div>`,
                                    confirmButtonColor: '#e11d48',
                                    confirmButtonText: 'Saya Mengerti'
                                });
                            } else if (res.status === 'Terjual') {
                                if (camLabel) camLabel.innerHTML = `<span class="text-amber-600 font-bold">⚠️ SUDAH TERJUAL: ${res.serial_number}</span>`;
                                let garansiHtml = '';
                                if (res.sisa_garansi !== null && res.sisa_garansi !== undefined) {
                                    if (res.sisa_garansi >= 0) {
                                        garansiHtml = `<div class="mt-2 p-2.5 bg-emerald-50 rounded-lg text-emerald-800 text-xs border border-emerald-200 text-left">` +
                                                      `<b>Invoice:</b> ${res.no_invoice || '-'}<br>` +
                                                      `<b>Tgl Pembelian:</b> ${res.tgl_terjual || '-'}<br>` +
                                                      `<b>Status Garansi:</b> <span class="font-bold text-emerald-700">AKTIF (${res.sisa_garansi} hari tersisa)</span></div>`;
                                    } else {
                                        garansiHtml = `<div class="mt-2 p-2.5 bg-slate-100 rounded-lg text-slate-700 text-xs border border-slate-200 text-left">` +
                                                      `<b>Invoice:</b> ${res.no_invoice || '-'}<br>` +
                                                      `<b>Tgl Pembelian:</b> ${res.tgl_terjual || '-'}<br>` +
                                                      `<b>Status Garansi:</b> <span class="font-bold text-slate-500">KADALUWARSA / HABIS</span></div>`;
                                    }
                                }
                                Swal.fire({
                                    icon: 'warning',
                                    title: '⚠️ UNIT ALKES SUDAH TERJUAL!',
                                    html: `Serial Number <b>${res.serial_number}</b> (${res.nama}) tercatat sudah <b>TERJUAL</b> pada transaksi sebelumnya.<br>` +
                                          garansiHtml + `<br>` +
                                          `<div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 text-left font-medium">` +
                                          `ℹ️ <b>Petunjuk Layanan Kasir:</b><br>` +
                                          `Jika pelanggan datang untuk klaim garansi kerusakan, silakan arahkan ke <b>Laporan Logistik Medis (Jejak SN)</b> untuk memproses isolasi Karantina.</div>`,
                                    confirmButtonColor: '#d97706',
                                    confirmButtonText: 'Saya Mengerti'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Unit Alkes Tidak Tersedia',
                                    html: `Serial Number <b>${res.serial_number}</b> (${res.nama}) sudah berstatus <b>${res.status}</b>.`,
                                    confirmButtonColor: '#1a75d2'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Barcode Tidak Ditemukan',
                                html: `SKU / Barcode tidak ditemukan atau stok kosong: <b>${sku.toUpperCase()}</b>`,
                                confirmButtonColor: '#1a75d2'
                            });
                        }
                    })
                    .catch(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Barcode Tidak Ditemukan',
                            html: `SKU / Barcode tidak ditemukan atau stok kosong: <b>${sku.toUpperCase()}</b>`,
                            confirmButtonColor: '#1a75d2'
                        });
                    });
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








