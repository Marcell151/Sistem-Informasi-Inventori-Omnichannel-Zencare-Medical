<?php
// File: zencare_store.php
// Modul E-Commerce Mandiri ZenCare Medical (Medical-Tech Production Theme)
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['set_cabang'])) {
    $_SESSION['id_cabang'] = intval($_GET['set_cabang']);
}

if (!isset($_SESSION['id_cabang']) || $_SESSION['id_cabang'] <= 0) {
    $_SESSION['id_cabang'] = 1;
}

$activeCabangId = $_SESSION['id_cabang'];

$stmtCabangAktif = $pdo->prepare("SELECT * FROM cabang WHERE id = ? AND is_active = 1");
$stmtCabangAktif->execute([$activeCabangId]);
$cabangAktif = $stmtCabangAktif->fetch();

$daftarCabang = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

$stmtProduk = $pdo->prepare("
    SELECT v.id, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_produk, 
           i.kategori, v.harga_jual_besar AS harga_jual, v.berat AS berat_gram, v.gambar, 
           i.deskripsi, COALESCE(sc.stok, 0) AS stok_sistem,
           v.tampil_di_online, v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
    ORDER BY v.id ASC
");
$stmtProduk->execute([$activeCabangId]);
$stmtProduk->execute([$activeCabangId]);
$products = $stmtProduk->fetchAll();

// Fetch Web settings
$settings = $pdo->query("SELECT * FROM pengaturan_web WHERE id = 1")->fetch();
if (!$settings) {
    $settings = [
        'nama_toko' => 'ZenCare Medical Store',
        'logo' => '',
        'kontak_wa' => '081234567890',
        'hero_banner' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=1200'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['nama_toko']) ?> - High Precision Medical Equipment</title>

    <!-- Tailwind CSS (Medical-Tech Theme) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              zc:    '#1a75d2',
              zcHv:  '#1562b3',
              zcLt:  '#e8f2ff',
              zcEm:  '#059669',
              zcBrd: '#e4e9f0',
              zcTxt: '#1e293b',
              zcMut: '#64748b',
            }
          }
        }
      }
    </script>
</head>
<body class="bg-[#f5f7fa] text-zcTxt font-sans antialiased">

    <!-- Header Navbar -->
    <header class="bg-white border-b border-zcBrd sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <?php if (!empty($settings['logo'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo']) ?>" class="h-8 w-auto object-contain" alt="Logo">
                <?php else: ?>
                    <div class="w-7 h-7 rounded-lg bg-zc flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                <?php endif; ?>
                <a href="zencare_store.php" class="font-semibold text-zcTxt text-sm"><?= htmlspecialchars($settings['nama_toko']) ?></a>
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" class="flex items-center gap-1.5">
                    <label class="text-xs text-zcMut font-medium">Cabang</label>
                    <select name="set_cabang" id="set_cabang" onchange="this.form.submit()" class="bg-white text-zcTxt text-xs border border-zcBrd rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-zc">
                        <?php foreach ($daftarCabang as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $activeCabangId == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'kasir')): ?>
                    <a href="index.php" class="text-xs font-medium text-zcMut hover:text-zcTxt border border-zcBrd px-3 py-1.5 rounded-lg bg-white transition">Dashboard</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-2 border border-zcBrd px-2.5 py-1 rounded-lg bg-slate-50">
                        <span class="text-xs font-semibold text-zcTxt">👤 <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?></span>
                        <a href="logout.php" class="text-[11px] text-rose-600 hover:underline ml-1">Keluar</a>
                    </div>
                <?php else: ?>
                    <a href="login_customer.php" class="text-xs font-medium text-zcMut hover:text-zcTxt border border-zcBrd px-3 py-1.5 rounded-lg bg-white transition">Masuk</a>
                    <a href="register.php" class="text-xs font-semibold bg-zcLt text-zc hover:bg-zc/10 border border-zc/20 px-3 py-1.5 rounded-lg transition">Daftar Akun</a>
                <?php endif; ?>

                <a href="zencare_checkout.php" class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-zc hover:bg-zcHv text-white text-xs font-semibold rounded-lg transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                    Checkout
                    <span id="cart-badge" class="px-1.5 py-0.5 text-[10px] font-bold bg-white text-zc rounded-full">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Branch Status Notice Bar -->
    <div class="bg-slate-200 border-b border-slate-300 py-2.5 text-center text-xs text-slate-700">
        Menampilkan katalog persediaan barang untuk: <strong class="text-slate-900 font-bold"><?= htmlspecialchars($cabangAktif['nama'] ?? 'Cabang Utama') ?></strong> (<?= htmlspecialchars($cabangAktif['alamat'] ?? '') ?>)
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Hero Banner Slate-900 & Sky Accents -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white border border-slate-800 rounded-xl p-8 mb-8 shadow-sm relative overflow-hidden">
            <?php if (!empty($settings['hero_banner'])): ?>
                <div class="absolute inset-0 opacity-20 mix-blend-overlay">
                    <img src="<?= htmlspecialchars($settings['hero_banner']) ?>" class="w-full h-full object-cover">
                </div>
            <?php endif; ?>
            <div class="relative z-10">
                <span class="px-3 py-1 bg-sky-500/20 text-sky-300 border border-sky-500/30 rounded-full text-xs font-bold uppercase tracking-wider inline-block mb-3">🩺 Distributor Resmi Alkes &amp; Medis</span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">Peralatan Kesehatan Bergaransi Resmi</h1>
                <p class="text-slate-300 max-w-2xl text-xs sm:text-sm leading-relaxed mb-6">Pilih cabang terdekat Anda untuk mendapatkan pengiriman instan Kurir Internal ZenCare Malang Raya atau ekspedisi pengiriman nasional.</p>
                <div class="flex flex-wrap gap-2 text-xs text-slate-300">
                    <span class="px-3 py-1 bg-slate-800/80 border border-slate-700 rounded-lg">✓ 100% Produk Original</span>
                    <span class="px-3 py-1 bg-slate-800/80 border border-slate-700 rounded-lg">✓ Garansi Alkes Resmi</span>
                    <?php if (!empty($settings['kontak_wa'])): ?>
                        <span class="px-3 py-1 bg-emerald-600/80 border border-emerald-500 text-white rounded-lg">✓ Hubungi Kami (WA): <?= htmlspecialchars($settings['kontak_wa']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h2 class="text-lg font-bold border-b-2 border-sky-500 pb-2 mb-6 text-slate-900">Katalog Produk Alat Kesehatan (Grosir E-Commerce)</h2>

        <!-- Product Cards Grid -->
            <?php foreach ($products as $p): ?>
                <?php 
                    $stokFisik = intval($p['stok_sistem']); 
                    $rasio = intval($p['rasio_konversi']) ?: 1;
                    $stokBox = floor($stokFisik / $rasio);
                    $hargaDisplay = $p['harga_jual'];
                    $satKecil = htmlspecialchars($p['satuan_kecil'] ?: 'Pcs');
                    $satBesar = htmlspecialchars($p['satuan_besar'] ?: 'Box');
                ?>
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden flex flex-col justify-between hover:shadow-md transition duration-200">
                    <div>
                        <div class="h-48 bg-slate-100 overflow-hidden relative border-b border-slate-200">
                            <img src="<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="w-full h-full object-cover">
                            <span class="absolute top-2 right-2 text-xs font-bold px-2.5 py-0.5 rounded-full <?= $stokBox > 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' ?>">
                                <?= $stokBox > 0 ? "Stok: $stokBox $satBesar" : 'Stok Tidak Tersedia' ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <span class="text-[10px] uppercase font-bold text-sky-600 tracking-wider bg-sky-50 px-2 py-0.5 rounded border border-sky-100 inline-block mb-1"><?= htmlspecialchars($p['kategori']) ?></span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1 line-clamp-2"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2"><?= htmlspecialchars($p['deskripsi'] ?? 'Peralatan medis standar berkualitas.') ?></p>
                            
                            <div class="mt-3 flex items-center justify-between text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-2 font-medium">
                                <span>📦 1 <?= $satBesar ?> =</span>
                                <strong class="bg-amber-100 px-2 py-0.5 rounded"><?= $rasio ?> <?= $satKecil ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 pt-0 border-t border-slate-100 mt-2">
                        <div class="flex items-baseline justify-between mb-3 mt-3">
                            <div class="flex flex-col">
                                <span class="text-base font-bold text-emerald-600">Rp <?= number_format($hargaDisplay, 0, ',', '.') ?> <span class="text-xs font-normal text-slate-400">/ <?= $satBesar ?></span></span>
                            </div>
                            <span class="text-xs text-slate-500"><?= intval($p['berat_gram']) ?> gr</span>
                        </div>

                        <?php if ($stokBox > 0): ?>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xs text-slate-600">Jumlah (<?= $satBesar ?>):</span>
                                <input type="number" id="qty_input_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" class="w-20 text-center text-xs border border-slate-300 rounded-lg py-1 px-2 focus:ring-1 focus:ring-zc focus:outline-none" onchange="validateQty(this, <?= $stokBox ?>)">
                            </div>
                            <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $hargaDisplay ?>, <?= $p['berat_gram'] ?>, '<?= addslashes($p['gambar']) ?>', <?= $stokBox ?>, <?= $rasio ?>, '<?= $satBesar ?>', '<?= $satKecil ?>')" 
                                    class="w-full text-xs font-bold py-2.5 px-3 rounded-lg border transition shadow-xs bg-sky-500 hover:bg-cyan-600 text-white border-sky-500">
                                + Tambah ke Keranjang
                            </button>
                        <?php else: ?>
                            <div class="text-xs text-center text-rose-600 font-bold bg-rose-50 border border-rose-200 py-2.5 rounded-lg">
                                Stok Tidak Cukup (Perlu minimal <?= $rasio ?> <?= $satKecil ?>)
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function validateQty(input, maxVal) {
            let val = parseInt(input.value) || 1;
            if (val < 1) {
                alert('Minimal pembelian adalah 1');
                input.value = 1;
            } else if (val > maxVal) {
                alert('Jumlah melebihi stok yang tersedia (' + maxVal + ')');
                input.value = maxVal;
            }
        }

        function getCart() {
            return JSON.parse(localStorage.getItem('zencare_cart') || '[]');
        }

        function saveCart(cart) {
            localStorage.setItem('zencare_cart', JSON.stringify(cart));
            updateCartBadge();
        }

        function updateCartBadge() {
            const cart = getCart();
            const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
            document.getElementById('cart-badge').innerText = totalQty;
        }

        function addToCart(id, name, price, weight, image, maxStokBox, rasio, satBesar, satKecil) {
            let qtyInput = document.getElementById('qty_input_' + id);
            let qty = parseInt(qtyInput.value) || 1;

            if (qty < 1) {
                alert('Gagal: Minimum pemesanan adalah 1 ' + satBesar);
                return;
            }

            let cart = getCart();
            let existing = cart.find(item => item.id === id);
            
            let currentCartQty = existing ? existing.qty : 0;
            if (currentCartQty + qty > maxStokBox) {
                alert('Gagal: Stok hanya tersedia ' + maxStokBox + ' ' + satBesar + '. Anda sudah menambahkan ' + currentCartQty + ' ke keranjang.');
                return;
            }

            if (existing) {
                existing.qty += qty;
            } else {
                cart.push({ 
                    id: id, 
                    name: name, 
                    price: price, 
                    weight: weight, 
                    image: image, 
                    qty: qty,
                    satuan_besar: satBesar,
                    satuan_label: satBesar,
                    rasio_konversi: rasio,
                    maxStokBox: maxStokBox,
                    stokBox: maxStokBox
                });
            }
            saveCart(cart);
            alert('Produk ' + name + ' sebanyak ' + qty + ' ' + satBesar + ' berhasil dimasukkan ke keranjang!');
        }

        document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>