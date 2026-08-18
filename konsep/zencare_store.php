<?php
// File: zencare_store.php
// Modul E-Commerce Mandiri ZenCare Medical (FASE 3)
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

// Handle Branch Selection via URL/Form
if (isset($_GET['set_cabang'])) {
    $_SESSION['id_cabang'] = intval($_GET['set_cabang']);
}

// Default Branch ke Cabang 1 (Muharto) jika belum dipilih
if (!isset($_SESSION['id_cabang']) || $_SESSION['id_cabang'] <= 0) {
    $_SESSION['id_cabang'] = 1;
}

$activeCabangId = $_SESSION['id_cabang'];

// Ambil Informasi Cabang Aktif
$stmtCabangAktif = $pdo->prepare("SELECT * FROM cabang WHERE id = ? AND is_active = 1");
$stmtCabangAktif->execute([$activeCabangId]);
$cabangAktif = $stmtCabangAktif->fetch();

// Ambil Semua Daftar Cabang
$daftarCabang = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

// Ambil Katalog Produk Berdasarkan Stok Cabang Aktif
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
    <!-- Tailwind CSS CDN for Wireframe Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // Strict Grayscale Palette Configuration
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              gray: {
                50: '#fafafa',
                100: '#f4f4f5',
                200: '#e4e4e7',
                300: '#d4d4d8',
                400: '#a1a1aa',
                500: '#71717a',
                600: '#52525b',
                700: '#3f3f46',
                800: '#27272a',
                900: '#18181b',
              }
            }
          }
        }
      }
    </script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">

    <!-- Header Navigation (Strict Grayscale) -->
    <header class="bg-black text-white border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="zencare_store.php" class="text-xl font-bold tracking-wider text-white">ZENCARE <span class="font-normal text-gray-400">MEDICAL</span></a>
                <span class="text-xs px-2 py-1 bg-gray-800 text-gray-300 rounded border border-gray-700">STORE</span>
            </div>

            <!-- Branch Selector Widget in Header -->
            <div class="flex items-center space-x-4">
                <form method="GET" class="flex items-center space-x-2">
                    <label for="set_cabang" class="text-xs text-gray-400 font-medium">Cabang Aktif:</label>
                    <select name="set_cabang" id="set_cabang" onchange="this.form.submit()" class="bg-gray-900 text-white text-xs border border-gray-700 rounded px-2 py-1 focus:outline-none">
                        <?php foreach ($daftarCabang as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $activeCabangId == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <a href="zencare_checkout.php" class="relative inline-flex items-center p-2 bg-gray-800 hover:bg-gray-700 rounded border border-gray-700 transition">
                    <span class="text-sm">🛒 Keranjang & Checkout</span>
                    <span id="cart-badge" class="ml-2 px-1.5 py-0.5 text-xs font-bold bg-white text-black rounded-full">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Branch Status Notice -->
    <div class="bg-gray-200 border-b border-gray-300 py-2 text-center text-xs text-gray-700">
        Menampilkan inventaris &amp; stok real-time untuk: <strong class="text-black"><?= htmlspecialchars($cabangAktif['nama'] ?? 'Cabang Utama') ?></strong> (<?= htmlspecialchars($cabangAktif['alamat'] ?? '') ?>)
    </div>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Hero Wireframe Banner -->
        <div class="bg-gray-900 text-white border border-gray-800 rounded-lg p-8 mb-8">
            <h1 class="text-3xl font-bold tracking-tight mb-2">Solusi Peralatan Medis &amp; Kesehatan Terpercaya</h1>
            <p class="text-gray-400 max-w-2xl text-sm leading-relaxed mb-6">Pusat perlengkapan kesehatan bergaransi. Pilih cabang fisik terdekat Anda untuk pengiriman instan Kurir Internal ZenCare atau kurir ekspedisi nasional.</p>
            <div class="flex items-center space-x-3 text-xs text-gray-400">
                <span class="px-2.5 py-1 bg-gray-800 border border-gray-700 rounded">✓ 100% Produk Original</span>
                <span class="px-2.5 py-1 bg-gray-800 border border-gray-700 rounded">✓ Garansi Resmi</span>
                <span class="px-2.5 py-1 bg-gray-800 border border-gray-700 rounded">✓ Pengiriman Malang Raya &amp; Nasional</span>
            </div>
        </div>

        <!-- Product Catalog Section -->
        <h2 class="text-xl font-bold border-b-2 border-black pb-2 mb-6">Katalog Produk Medis</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $p): ?>
                <?php $stok = intval($p['stok_sistem']); ?>
                <div class="bg-white border border-gray-300 rounded-lg overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="h-48 bg-gray-200 overflow-hidden relative border-b border-gray-200">
                            <img src="<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="w-full h-full object-cover grayscale contrast-125 hover:grayscale-0 transition duration-300">
                            <span class="absolute top-2 right-2 text-xs font-bold px-2 py-0.5 rounded <?= $stok > 0 ? 'bg-black text-white' : 'bg-gray-400 text-gray-800' ?>">
                                Stok: <?= $stok ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <span class="text-xs uppercase font-semibold text-gray-500 tracking-wider"><?= htmlspecialchars($p['kategori']) ?></span>
                            <h3 class="text-sm font-bold text-gray-900 mt-1 line-clamp-2"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                            <p class="text-xs text-gray-500 mt-2 line-clamp-2"><?= htmlspecialchars($p['deskripsi'] ?? 'Peralatan medis standar kesehatan.') ?></p>
                        </div>
                    </div>
                    
                    <div class="p-4 pt-0 border-t border-gray-100 mt-2">
                        <div class="flex items-baseline justify-between mb-3">
                            <span class="text-base font-bold text-black">Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?></span>
                            <span class="text-xs text-gray-500"><?= intval($p['berat_gram']) ?> gr</span>
                        </div>
                        <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $p['harga_jual'] ?>, <?= $p['berat_gram'] ?>, '<?= addslashes($p['gambar']) ?>')" 
                                <?= $stok <= 0 ? 'disabled' : '' ?>
                                class="w-full text-xs font-semibold py-2 px-3 rounded border border-black transition <?= $stok > 0 ? 'bg-black text-white hover:bg-gray-800' : 'bg-gray-200 text-gray-500 border-gray-300 cursor-not-allowed' ?>">
                            <?= $stok > 0 ? '+ Tambah ke Keranjang' : 'Stok Habis' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Cart LocalStorage Helper Script -->
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
            alert('Produk ' + name + ' berhasil ditambahkan ke keranjang!');
        }

        document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>