<?php
// File: pos/pos.php – Terminal POS Kasir Offline ZenCare Medical
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireRole(['super_admin', 'kasir']);

$idCabangKasir = $_SESSION['id_cabang'] ?? 1;
$stmtCabang = $pdo->prepare("SELECT * FROM cabang WHERE id=? AND is_active=1");
$stmtCabang->execute([$idCabangKasir]);
$cabangKasir = $stmtCabang->fetch();

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
            $pdo->prepare("INSERT INTO penjualan (no_invoice,id_cabang,id_user,tipe_transaksi,status_pesanan,total_harga,created_at) VALUES (?,?,?,'pos','Selesai',0,NOW())")
                ->execute([$invoiceNo, $idCabangKasir, $_SESSION['user_id'] ?? 2]);
            $idPenjualan = $pdo->lastInsertId();

            foreach ($cartItems as $item) {
                $idVar = intval($item['id']); $qtyInput = intval($item['qty']);
                $satuanTipe = $item['satuan_tipe'] ?? 'kecil'; // 'kecil' or 'besar'
                
                // Fetch current prices and stock from DB instead of trusting client
                $chk   = $pdo->prepare("SELECT v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, sc.stok FROM produk_variasi v LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang=? WHERE v.id=? FOR UPDATE");
                $chk->execute([$idCabangKasir, $idVar]);
                $varData = $chk->fetch();
                
                $rasio = intval($varData['rasio_konversi']) ?: 1;
                $effPrice = ($satuanTipe === 'besar') ? floatval($varData['harga_jual_besar']) : floatval($varData['harga_jual_kecil']);
                $qtyPotong = ($satuanTipe === 'besar') ? ($qtyInput * $rasio) : $qtyInput;
                
                if (!$varData || intval($varData['stok']) < $qtyPotong) {
                    throw new Exception("Stok ID $idVar tidak cukup! (Pesan: $qtyPotong, Sisa: " . ($varData['stok'] ?? 0) . ")");
                }

                $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan,id_variasi,qty,harga_satuan) VALUES (?,?,?,?)")->execute([$idPenjualan,$idVar,$qtyInput,$effPrice]);
                $pdo->prepare("UPDATE stok_cabang SET stok=stok-? WHERE id_variasi=? AND id_cabang=?")->execute([$qtyPotong,$idVar,$idCabangKasir]);

                $sisaQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=?");
                $sisaQ->execute([$idVar,$idCabangKasir]);
                $sisa = $sisaQ->fetchColumn();
                $satLable = ($satuanTipe === 'besar') ? $varData['satuan_besar'] : $varData['satuan_kecil'];
                $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Keluar',?,?,?)")
                    ->execute([$idCabangKasir,$idVar,$qtyPotong,$sisa,"POS Offline #$invoiceNo (Beli $qtyInput $satLable)"]);
                $totalHarga += $effPrice * $qtyInput;
            }

            $pdo->prepare("UPDATE penjualan SET total_harga=? WHERE id=?")->execute([$totalHarga, $idPenjualan]);

            // Shopee cURL Sync
            $apiCfg = $pdo->prepare("SELECT * FROM pengaturan_api WHERE platform='shopee' AND is_active=1");
            $apiCfg->execute();
            $shopeeApi = $apiCfg->fetch();
            $syncNote  = '';
            if ($shopeeApi) {
                foreach ($cartItems as $item) {
                    $skuQ = $pdo->prepare("SELECT v.sku_variasi, sc.stok FROM produk_variasi v JOIN stok_cabang sc ON sc.id_variasi=v.id WHERE v.id=? AND sc.id_cabang=?");
                    $skuQ->execute([$item['id'], $idCabangKasir]);
                    $skuRow = $skuQ->fetch();
                    if ($skuRow) {
                        $ch = curl_init('https://partner.shopeesz.com/api/v2/product/update_stock');
                        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode(['item_sku' => $skuRow['sku_variasi'], 'stock' => $skuRow['stok']]), CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $shopeeApi['api_key']], CURLOPT_TIMEOUT => 3]);
                        curl_exec($ch); curl_close($ch);
                    }
                }
                $syncNote = ' | ✅ Shopee Sync API Dikirim';
            }

            $pdo->commit();
            $msg = "✅ Transaksi berhasil! Invoice: <strong>$invoiceNo</strong> | Total: Rp " . number_format($totalHarga, 0, ',', '.') . $syncNote . " | <a href='cetak_invoice.php?no_invoice=$invoiceNo' target='_blank' class='underline font-bold ml-2'>🖨️ Cetak Struk A4</a>";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "❌ Gagal: " . $e->getMessage(); $msgType = 'error';
        }
    }
}

$katalog = $pdo->prepare("
    SELECT v.id, v.sku_variasi, CONCAT(i.nama_produk,' – ',v.nama_variasi) AS nama,
           v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, COALESCE(sc.stok,0) AS stok, i.kategori
    FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi=v.id AND sc.id_cabang=?
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC");
$katalog->execute([$idCabangKasir]);
$katalog = $katalog->fetchAll();

$shopeeOn = $pdo->prepare("SELECT is_active FROM pengaturan_api WHERE platform='shopee'");
$shopeeOn->execute();
$shopeeOn = $shopeeOn->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal POS Kasir – ZenCare Medical</title>
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
    <header class="bg-white border-b border-zcBrd px-6 py-3.5 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-zc text-white flex items-center justify-center">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-zcTxt leading-tight">TERMINAL POS KASIR OFFLINE</h1>
                <p class="text-[10px] text-zcMut">
                    <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Kasir') ?></strong> &bull;
                    Cabang: <strong class="text-zc"><?= htmlspecialchars($cabangKasir['nama'] ?? '–') ?></strong> &bull;
                    Shopee: <span class="font-bold <?= $shopeeOn ? 'text-emerald-600' : 'text-slate-400' ?>"><?= $shopeeOn ? '● ON' : '● OFF' ?></span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="../index.php" class="text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-zcBrd px-3.5 py-2 rounded-lg transition">← Dashboard</a>
            <a href="../zencare_store.php" class="text-xs font-medium bg-zc hover:bg-zcHv text-white px-3.5 py-2 rounded-lg transition">Toko Online</a>
            <a href="../logout.php" class="text-xs font-medium text-rose-600 hover:text-rose-800 bg-rose-50 border border-rose-200 px-3.5 py-2 rounded-lg transition">Keluar</a>
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
                        <?php $stok = intval($item['stok']); ?>
                        <div class="product-card bg-slate-50 border border-zcBrd rounded-2xl p-3.5 hover:border-zc transition flex flex-col"
                             data-search="<?= strtolower($item['nama'] . ' ' . $item['sku_variasi']) ?>">
                            <div class="min-w-0 flex-1 mb-3">
                                <span class="text-[9px] font-mono text-zcMut block"><?= htmlspecialchars($item['sku_variasi']) ?></span>
                                <span class="text-xs font-bold text-zcTxt block leading-snug mt-0.5 line-clamp-2"><?= htmlspecialchars($item['nama']) ?></span>
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
                                    <button onclick="addToPos(<?= $item['id'] ?>, '<?= addslashes($item['nama']) ?>', <?= $item['harga_jual_kecil'] ?>, <?= $item['harga_jual_besar'] ?>, <?= $stok ?>, <?= $item['rasio_konversi'] ?>)"
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
                <div class="px-5 py-4 border-b border-zcBrd">
                    <h2 class="text-sm font-bold text-zcTxt">📦 Billing Transaksi</h2>
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
                    <form method="POST" onsubmit="return validatePay()">
                        <input type="hidden" name="aksi" value="bayar_pos">
                        <input type="hidden" name="cart_data" id="cart_input">
                        <button type="submit" id="btn_pay" disabled
                            class="w-full py-3.5 rounded-xl text-xs font-bold transition shadow-sm bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed">
                            Proses Pembayaran Kasir
                        </button>
                    </form>
                    <button onclick="clearCart()" class="w-full py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6"/></svg> Kosongkan Keranjang
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
    let cart = [];

    function addToPos(id, name, hargaKecil, hargaBesar, maxStockPcs, rasio) {
        let sel = document.getElementById('uom_' + id);
        let uomType = sel.value; // 'kecil' or 'besar'
        let uomLabel = sel.options[sel.selectedIndex].text;
        
        let price = (uomType === 'besar') ? hargaBesar : hargaKecil;
        let qtyMultiplier = (uomType === 'besar') ? rasio : 1;
        let cartId = id + '_' + uomType;

        let found = cart.find(i => i.cartId === cartId);
        if (found) {
            if ((found.qty + 1) * found.rasio > found.maxPcs) { alert('Stok terbatas!'); return; }
            found.qty++;
        } else { 
            if (qtyMultiplier > maxStockPcs) { alert('Stok tidak cukup untuk satuan ini!'); return; }
            cart.push({
                id: id, cartId: cartId, nama: name, harga: price, qty: 1, 
                satuan_tipe: uomType, satuan_label: uomLabel, maxPcs: maxStockPcs, rasio: qtyMultiplier
            }); 
        }
        renderCart();
    }

    function changeQty(cartId, d) {
        let item = cart.find(i => i.cartId === cartId);
        if (!item) return;
        if (d > 0 && (item.qty + d) * item.rasio > item.maxPcs) { alert('Stok terbatas!'); return; }
        item.qty += d;
        if (item.qty <= 0) cart = cart.filter(i => i.cartId !== cartId);
        renderCart();
    }

    function removeItem(cartId) { cart = cart.filter(i => i.cartId !== cartId); renderCart(); }
    function clearCart() { cart = []; renderCart(); }

    function renderCart() {
        const tbody = document.getElementById('pos_cart_body');
        const totalEl = document.getElementById('pos_total');
        const btn = document.getElementById('btn_pay');
        const inp = document.getElementById('cart_input');

        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-zcMut italic">Pilih barang dari katalog...</td></tr>';
            totalEl.innerText = 'Rp 0';
            btn.disabled = true;
            btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed';
            inp.value = '[]'; return;
        }

        let total = 0, html = '';
        cart.forEach(i => {
            const sub = i.harga * i.qty; total += sub;
            html += `<tr class="hover:bg-slate-50/60">
                <td class="px-4 py-3 font-semibold text-zcTxt text-xs">
                    ${i.nama}
                    <span class="block text-[9px] text-zcEm mt-0.5">${i.satuan_label} (Rp ${i.harga.toLocaleString('id-ID')})</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button" onclick="changeQty('${i.cartId}',-1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition">-</button>
                        <span class="font-bold text-xs w-5 text-center">${i.qty}</span>
                        <button type="button" onclick="changeQty('${i.cartId}',1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition">+</button>
                    </div>
                </td>
                <td class="px-4 py-3 text-right font-bold text-xs">Rp ${sub.toLocaleString('id-ID')}</td>
                <td class="px-4 py-3 text-center"><button type="button" onclick="removeItem('${i.cartId}')" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition">✕</button></td>
            </tr>`;
        });

        tbody.innerHTML = html;
        totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
        btn.disabled = false;
        btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-zc hover:bg-zcHv text-white transition shadow-sm cursor-pointer';
        inp.value = JSON.stringify(cart);
    }

    function validatePay() {
        if (!cart.length) { alert('Keranjang kosong!'); return false; }
        return confirm('Konfirmasi transaksi POS kasir?\nTotal: ' + document.getElementById('pos_total').innerText);
    }

    function filterPos() {
        const q = document.getElementById('pos_search').value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.display = c.dataset.search.includes(q) ? '' : 'none';
        });
    }
    </script>
</body>
</html>

