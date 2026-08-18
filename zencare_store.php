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
           i.kategori, v.harga AS harga_jual, v.berat AS berat_gram, v.gambar, 
           i.deskripsi, COALESCE(sc.stok, 0) AS stok_sistem
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.is_active = 1 AND i.is_active = 1
    ORDER BY v.id ASC
");
$stmtProduk->execute([$activeCabangId]);
$products = $stmtProduk->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenCare Medical Store - High Precision Medical Equipment</title>

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
                <div class="w-7 h-7 rounded-lg bg-zc flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <a href="zencare_store.php" class="font-semibold text-zcTxt text-sm">ZenCare <span class="text-zc">Medical Store</span></a>
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
                    <a href="login.php" class="text-xs font-medium text-zcMut hover:text-zcTxt border border-zcBrd px-3 py-1.5 rounded-lg bg-white transition">Masuk</a>
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
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white border border-slate-800 rounded-xl p-8 mb-8 shadow-sm">
            <span class="px-3 py-1 bg-sky-500/20 text-sky-300 border border-sky-500/30 rounded-full text-xs font-bold uppercase tracking-wider inline-block mb-3">🩺 Distributor Resmi Alkes &amp; Medis</span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">Peralatan Kesehatan Bergaransi Resmi</h1>
            <p class="text-slate-300 max-w-2xl text-xs sm:text-sm leading-relaxed mb-6">Pilih cabang terdekat Anda untuk mendapatkan pengiriman instan Kurir Internal ZenCare Malang Raya atau ekspedisi pengiriman nasional.</p>
            <div class="flex flex-wrap gap-2 text-xs text-slate-300">
                <span class="px-3 py-1 bg-slate-800 border border-slate-700 rounded-lg">✓ 100% Produk Original</span>
                <span class="px-3 py-1 bg-slate-800 border border-slate-700 rounded-lg">✓ Garansi Alkes Resmi</span>
                <span class="px-3 py-1 bg-slate-800 border border-slate-700 rounded-lg">✓ Instant Delivery Malang</span>
            </div>
        </div>

        <h2 class="text-lg font-bold border-b-2 border-sky-500 pb-2 mb-6 text-slate-900">Katalog Produk Alat Kesehatan</h2>

        <!-- Product Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $p): ?>
                <?php $stok = intval($p['stok_sistem']); ?>
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden flex flex-col justify-between hover:shadow-md transition duration-200">
                    <div>
                        <div class="h-48 bg-slate-100 overflow-hidden relative border-b border-slate-200">
                            <img src="<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="w-full h-full object-cover">
                            <span class="absolute top-2 right-2 text-xs font-bold px-2.5 py-0.5 rounded-full <?= $stok > 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' ?>">
                                Stok: <?= $stok ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <span class="text-[10px] uppercase font-bold text-sky-600 tracking-wider bg-sky-50 px-2 py-0.5 rounded border border-sky-100 inline-block mb-1"><?= htmlspecialchars($p['kategori']) ?></span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1 line-clamp-2"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2"><?= htmlspecialchars($p['deskripsi'] ?? 'Peralatan medis standar berkualitas.') ?></p>
                        </div>
                    </div>
                    
                    <div class="p-4 pt-0 border-t border-slate-100 mt-2">
                        <div class="flex items-baseline justify-between mb-3">
                            <span class="text-base font-bold text-slate-900">Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?></span>
                            <span class="text-xs text-slate-500"><?= intval($p['berat_gram']) ?> gr</span>
                        </div>
                        <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $p['harga_jual'] ?>, <?= $p['berat_gram'] ?>, '<?= addslashes($p['gambar']) ?>')" 
                                <?= $stok <= 0 ? 'disabled' : '' ?>
                                class="w-full text-xs font-bold py-2.5 px-3 rounded-lg border transition shadow-xs <?= $stok > 0 ? 'bg-sky-500 hover:bg-cyanBrand text-white border-sky-500' : 'bg-slate-200 text-slate-400 border-slate-300 cursor-not-allowed' ?>">
                            <?= $stok > 0 ? '+ Tambah ke Keranjang' : 'Stok Habis' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
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

        function addToCart(id, name, price, weight, image) {
            let cart = getCart();
            let existing = cart.find(item => item.id === id);
            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({ id: id, name: name, price: price, weight: weight, image: image, qty: 1 });
            }
            saveCart(cart);
            alert('Produk ' + name + ' ditambahkan ke keranjang!');
        }

        document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>