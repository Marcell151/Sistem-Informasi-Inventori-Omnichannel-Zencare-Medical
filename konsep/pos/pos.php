<?php
// File: pos/pos.php
// Modul POS Kasir Offline & Integrasi Shopee (FASE 4)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id_cabang'])) {
    $_SESSION['id_cabang'] = 1;
}

$idCabangKasir = $_SESSION['id_cabang'];

$stmtCabang = $pdo->prepare("SELECT * FROM cabang WHERE id = ? AND is_active = 1");
$stmtCabang->execute([$idCabangKasir]);
$cabangKasir = $stmtCabang->fetch();

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'bayar_pos') {
    $cartDataJson = $_POST['cart_data'] ?? '[]';
    $cartItems = json_decode($cartDataJson, true);
    
    if (empty($cartItems)) {
        $message = "Keranjang kasir kosong!";
        $messageType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $totalHarga = 0;
            $invoiceNo = "POS-" . date('Ymd') . "-" . rand(1000, 9999);
            
            $stmtJual = $pdo->prepare("INSERT INTO penjualan (no_invoice, id_cabang, id_user, tipe_transaksi, status_pesanan, total_harga, created_at) VALUES (?, ?, ?, 'pos', 'Selesai', 0, NOW())");
            $stmtJual->execute([$invoiceNo, $idCabangKasir, $_SESSION['user_id'] ?? 2]);
            $idPenjualan = $pdo->lastInsertId();

            $stmtDetail = $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan) VALUES (?, ?, ?, ?)");
            $stmtStok = $pdo->prepare("UPDATE stok_cabang SET stok = stok - ? WHERE id_variasi = ? AND id_cabang = ?");
            $stmtSisa = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Keluar', ?, ?, ?)");

            foreach ($cartItems as $item) {
                $idVar = intval($item['id']);
                $qty = intval($item['qty']);
                $harga = floatval($item['harga']);
                $subtotal = $harga * $qty;
                $totalHarga += $subtotal;

                $stmtCheck = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ? FOR UPDATE");
                $stmtCheck->execute([$idVar, $idCabangKasir]);
                $stokAda = $stmtCheck->fetchColumn();

                if ($stokAda === false || intval($stokAda) < $qty) {
                    throw new Exception("Stok untuk item ID $idVar tidak mencukupi di cabang ini! Tersisa: " . ($stokAda ?? 0));
                }

                $stmtDetail->execute([$idPenjualan, $idVar, $qty, $harga]);
                $stmtStok->execute([$qty, $idVar, $idCabangKasir]);

                $stmtSisa->execute([$idVar, $idCabangKasir]);
                $sisaStok = $stmtSisa->fetchColumn();

                $stmtKartu->execute([$idCabangKasir, $idVar, $qty, $sisaStok, "Penjualan POS Offline Invoice #$invoiceNo"]);
            }

            $stmtUpTotal = $pdo->prepare("UPDATE penjualan SET total_harga = ? WHERE id = ?");
            $stmtUpTotal->execute([$totalHarga, $idPenjualan]);

            // cURL Request Update Stok Shopee jika API Toggle ON
            $stmtApi = $pdo->prepare("SELECT * FROM pengaturan_api WHERE id_cabang = ? AND platform = 'shopee' AND is_active = 1");
            $stmtApi->execute([$idCabangKasir]);
            $shopeeApiConfig = $stmtApi->fetch();

            $shopeeSyncMessage = "";
            if ($shopeeApiConfig) {
                foreach ($cartItems as $item) {
                    $idVar = intval($item['id']);
                    $stmtSku = $pdo->prepare("SELECT v.sku_variasi, sc.stok FROM produk_variasi v JOIN stok_cabang sc ON sc.id_variasi = v.id WHERE v.id = ? AND sc.id_cabang = ?");
                    $stmtSku->execute([$idVar, $idCabangKasir]);
                    $skuInfo = $stmtSku->fetch();

                    if ($skuInfo) {
                        $shopeePayload = json_encode([
                            'item_sku' => $skuInfo['sku_variasi'],
                            'stock' => intval($skuInfo['stok']),
                            'timestamp' => time()
                        ]);

                        $ch = curl_init('https://partner.shopeesz.com/api/v2/product/update_stock');
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $shopeePayload);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $shopeeApiConfig['api_key']
                        ]);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                        curl_exec($ch);
                        curl_close($ch);
                    }
                }
                $shopeeSyncMessage = " | Synchronized to Shopee Sandbox API (Toggle ON)";
            }

            $pdo->commit();
            $message = "Transaksi POS Berhasil! Invoice: $invoiceNo (Total: Rp " . number_format($totalHarga, 0, ',', '.') . ")" . $shopeeSyncMessage . " <a href='cetak_invoice.php?no_invoice=$invoiceNo' target='_blank' class='underline font-bold ml-2'>[🖨️ Cetak Struk]</a>";
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Gagal memproses transaksi kasir: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$stmtKatalog = $pdo->prepare("
    SELECT v.id, v.sku_variasi, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_produk,
           v.harga, COALESCE(sc.stok, 0) AS stok_sistem, i.kategori
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.is_active = 1 AND i.is_active = 1
    ORDER BY i.nama_produk ASC
");
$stmtKatalog->execute([$idCabangKasir]);
$katalogKasir = $stmtKatalog->fetchAll();

$stmtApiCheck = $pdo->prepare("SELECT is_active FROM pengaturan_api WHERE id_cabang = ? AND platform = 'shopee'");
$stmtApiCheck->execute([$idCabangKasir]);
$shopeeStatus = $stmtApiCheck->fetchColumn() == 1 ? 'ACTIVE (ON)' : 'OFFLINE (OFF)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenCare POS - Kasir Terminal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              gray: {
                50: '#fafafa', 100: '#f4f4f5', 200: '#e4e4e7', 300: '#d4d4d8',
                400: '#a1a1aa', 500: '#71717a', 600: '#52525b', 700: '#3f3f46',
                800: '#27272a', 900: '#18181b',
              }
            }
          }
        }
      }
    </script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">

    <header class="bg-black text-white border-b border-gray-800 px-6 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-lg font-bold tracking-wider">ZENCARE POS <span class="font-normal text-gray-400">TERMINAL</span></h1>
            <p class="text-xs text-gray-400">Kasir Aktif: <strong class="text-white"><?= htmlspecialchars($cabangKasir['nama'] ?? 'Cabang') ?></strong> | Shopee Sync: <span class="font-bold underline"><?= $shopeeStatus ?></span></p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="../index.php" class="text-xs bg-gray-800 hover:bg-gray-700 text-white border border-gray-700 px-3 py-1.5 rounded transition">Dashboard Utama</a>
            <a href="../zencare_store.php" class="text-xs bg-white text-black font-bold px-3 py-1.5 rounded transition hover:bg-gray-200">Buka Store</a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 text-xs font-semibold rounded border <?= $messageType === 'success' ? 'bg-gray-900 text-white border-black' : 'bg-gray-300 text-black border-gray-400' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-7 bg-white border border-gray-300 rounded-lg p-5">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-black">Katalog Stok Cabang</h2>
                    <input type="text" id="pos_search" onkeyup="filterPosProducts()" placeholder="Cari SKU / Nama Barang..." class="text-xs border border-gray-300 rounded px-3 py-1.5 w-64 focus:outline-none focus:border-black">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-[500px] overflow-y-auto pr-1" id="product_grid">
                    <?php foreach ($katalogKasir as $item): ?>
                        <?php $stok = intval($item['stok_sistem']); ?>
                        <div class="product-card bg-gray-50 border border-gray-200 p-3 rounded hover:border-black transition flex flex-col justify-between" data-search="<?= strtolower(htmlspecialchars($item['nama_produk'] . ' ' . $item['sku_variasi'])) ?>">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-500 block"><?= htmlspecialchars($item['sku_variasi']) ?></span>
                                <h3 class="text-xs font-bold text-gray-900 line-clamp-2 mt-0.5"><?= htmlspecialchars($item['nama_produk']) ?></h3>
                            </div>
                            <div class="mt-3 pt-2 border-t border-gray-200 flex justify-between items-center">
                                <div>
                                    <span class="text-xs font-bold text-black block">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                    <span class="text-[10px] text-gray-500">Stok: <?= $stok ?></span>
                                </div>
                                <button onclick="addToPosCart(<?= $item['id'] ?>, '<?= addslashes($item['nama_produk']) ?>', <?= $item['harga'] ?>, <?= $stok ?>)"
                                        <?= $stok <= 0 ? 'disabled' : '' ?>
                                        class="text-xs font-bold px-2.5 py-1 rounded border border-black <?= $stok > 0 ? 'bg-black text-white hover:bg-gray-800' : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed' ?>">
                                    + Pilih
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:col-span-5 bg-white border border-gray-300 rounded-lg p-5 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-black border-b border-gray-200 pb-3 mb-4">Struk / Billing Kasir</h2>

                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b border-gray-300 text-gray-700">
                                    <th class="p-2">Item</th>
                                    <th class="p-2 text-center">Qty</th>
                                    <th class="p-2 text-right">Subtotal</th>
                                    <th class="p-2 text-center">#</th>
                                </tr>
                            </thead>
                            <tbody id="pos_cart_body">
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-400 italic">Belum ada item dipilih.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-baseline mb-4">
                        <span class="text-sm font-bold text-gray-700">TOTAL BAYAR:</span>
                        <span id="pos_total_display" class="text-xl font-bold text-black">Rp 0</span>
                    </div>

                    <form method="POST" onsubmit="return validatePosPay()">
                        <input type="hidden" name="aksi" value="bayar_pos">
                        <input type="hidden" name="cart_data" id="cart_data_input">
                        <button type="submit" id="btn_pos_pay" disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 rounded border border-gray-400 cursor-not-allowed transition">
                            Proses Pembayaran Kasir
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <script>
        let posCart = [];

        function addToPosCart(id, name, price, maxStock) {
            let existing = posCart.find(item => item.id === id);
            if (existing) {
                if (existing.qty + 1 > maxStock) {
                    alert('Stok barang di cabang ini tidak mencukupi untuk ditambah lagi!');
                    return;
                }
                existing.qty += 1;
            } else {
                posCart.push({ id: id, name: name, harga: price, qty: 1, maxStock: maxStock });
            }
            renderPosCart();
        }

        function changePosQty(id, delta) {
            let item = posCart.find(i => i.id === id);
            if (item) {
                if (delta > 0 && item.qty + delta > item.maxStock) {
                    alert('Stok terbatas!');
                    return;
                }
                item.qty += delta;
                if (item.qty <= 0) {
                    posCart = posCart.filter(i => i.id !== id);
                }
            }
            renderPosCart();
        }

        function removePosItem(id) {
            posCart = posCart.filter(i => i.id !== id);
            renderPosCart();
        }

        function renderPosCart() {
            let tbody = document.getElementById('pos_cart_body');
            let total = 0;

            if (posCart.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400 italic">Belum ada item dipilih.</td></tr>';
                document.getElementById('pos_total_display').innerText = 'Rp 0';
                document.getElementById('cart_data_input').value = '[]';
                
                let btn = document.getElementById('btn_pos_pay');
                btn.disabled = true;
                btn.className = 'w-full bg-gray-300 text-gray-500 font-bold py-3 rounded border border-gray-400 cursor-not-allowed transition';
                return;
            }

            let html = '';
            posCart.forEach(item => {
                let subtotal = item.harga * item.qty;
                total += subtotal;
                html += `
                    <tr class="border-b border-gray-200">
                        <td class="p-2 font-bold text-gray-900">${item.name}</td>
                        <td class="p-2 text-center">
                            <button onclick="changePosQty(${item.id}, -1)" class="px-1.5 py-0.5 bg-gray-200 text-black font-bold rounded border border-gray-300">-</button>
                            <span class="mx-1 font-semibold">${item.qty}</span>
                            <button onclick="changePosQty(${item.id}, 1)" class="px-1.5 py-0.5 bg-gray-200 text-black font-bold rounded border border-gray-300">+</button>
                        </td>
                        <td class="p-2 text-right font-semibold">Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td class="p-2 text-center">
                            <button onclick="removePosItem(${item.id})" class="text-xs text-red-600 font-bold hover:underline">✕</button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('pos_total_display').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('cart_data_input').value = JSON.stringify(posCart);

            let btn = document.getElementById('btn_pos_pay');
            btn.disabled = false;
            btn.className = 'w-full bg-black text-white font-bold py-3 rounded border border-black hover:bg-gray-800 transition cursor-pointer';
        }

        function validatePosPay() {
            if (posCart.length === 0) {
                alert('Keranjang kasir kosong!');
                return false;
            }
            return confirm('Konfirmasi transaksi POS kasir dan cetak nota?');
        }

        function filterPosProducts() {
            let q = document.getElementById('pos_search').value.toLowerCase();
            let cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                let text = card.getAttribute('data-search');
                if (text.includes(q)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
