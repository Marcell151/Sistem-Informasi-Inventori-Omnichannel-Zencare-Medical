<?php
// File: ecommerce/produk.php
// Dedicated Medical-Tech Product Detail Page for ZenCare E-Commerce
$productId = intval($_GET['id'] ?? 0);
if ($productId <= 0) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/header.php';

// Fetch the selected product variation
$stmt = $pdo->prepare("
    SELECT v.id, v.id_produk_induk,
           i.nama_produk AS nama_induk,
           v.nama_variasi,
           CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_lengkap,
           i.kategori,
           v.harga_jual_besar AS harga_jual,
           v.harga_jual_kecil AS harga_eceran,
           v.berat AS berat_gram,
           v.gambar,
           v.sku_variasi,
           i.deskripsi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.id = ? AND v.is_active = 1 AND i.is_active = 1
");
$stmt->execute([$activeCabangId, $productId]);
$p = $stmt->fetch();

if (!$p) {
    echo "<div class='max-w-4xl mx-auto py-20 px-4 text-center'>
            <h2 class='text-lg font-bold text-slate-800 mb-2'>Produk Tidak Ditemukan</h2>
            <p class='text-xs text-slate-500 mb-6'>Produk yang Anda cari mungkin telah dinonaktifkan atau dihapus.</p>
            <a href='index.php' class='px-4 py-2 bg-zc text-white text-xs font-bold rounded-xl'>Kembali ke Katalog</a>
          </div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$stokPcs     = intval($p['stok_sistem']);
$rasio       = intval($p['rasio_konversi']) ?: 1;
$stokBox     = floor($stokPcs / $rasio);
$harga       = floatval($p['harga_jual']);
$hargaEceran = floatval($p['harga_eceran']);
$satBesar    = $p['satuan_besar'] ?: 'Box';
$satKecil    = $p['satuan_kecil'] ?: 'Pcs';

// Stock badge styling
if ($stokBox <= 0) {
    $stokClass = 'bg-rose-50 text-rose-700 border-rose-200';
    $stokDot   = 'bg-rose-500';
    $stokLabel = 'Stok Habis di Cabang Ini';
} elseif ($stokBox <= 5) {
    $stokClass = 'bg-amber-50 text-amber-800 border-amber-200';
    $stokDot   = 'bg-amber-400';
    $stokLabel = "Stok Kritis: Tersisa $stokBox $satBesar";
} else {
    $stokClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
    $stokDot   = 'bg-emerald-500';
    $stokLabel = "Tersedia $stokBox $satBesar";
}

// Fetch other variations under the same parent product
$stmtVariants = $pdo->prepare("
    SELECT v.id, v.nama_variasi, v.harga_jual_besar, v.sku_variasi, COALESCE(sc.stok, 0) AS stok_sistem, v.rasio_konversi
    FROM produk_variasi v
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.id_produk_induk = ? AND v.is_active = 1
    ORDER BY v.id ASC
");
$stmtVariants->execute([$activeCabangId, $p['id_produk_induk']]);
$siblings = $stmtVariants->fetchAll();

// Related products in the same category
$stmtRelated = $pdo->prepare("
    SELECT v.id, i.nama_produk AS nama_induk, v.nama_variasi, v.harga_jual_besar AS harga_jual, v.gambar, v.satuan_besar
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE i.kategori = ? AND v.id != ? AND v.is_active = 1
    LIMIT 4
");
$stmtRelated->execute([$p['kategori'], $p['id']]);
$related = $stmtRelated->fetchAll();
?>

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-6 flex-1 w-full">

    <!-- ── Breadcrumb Navigation ── -->
    <nav class="flex items-center gap-2 text-xs text-zcMut mb-6">
        <a href="index.php" class="hover:text-zc transition">Beranda</a>
        <span>/</span>
        <a href="kategori.php?kategori=<?= urlencode($p['kategori']) ?>" class="hover:text-zc transition"><?= htmlspecialchars($p['kategori']) ?></a>
        <span>/</span>
        <span class="text-zcTxt font-bold truncate max-w-xs"><?= htmlspecialchars($p['nama_lengkap']) ?></span>
    </nav>

    <!-- ── Main Product Display Card ── -->
    <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 lg:p-10 shadow-sm mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            
            <!-- LEFT: Product Image Zone (5 cols) -->
            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="relative w-full aspect-square rounded-2xl bg-gradient-to-br from-slate-50 via-blue-50/25 to-slate-100 border border-slate-100 flex items-center justify-center p-8 overflow-hidden shadow-xs">
                    <?php if (!empty($p['gambar'])): ?>
                        <img src="../<?= htmlspecialchars($p['gambar']) ?>"
                             alt="<?= htmlspecialchars($p['nama_lengkap']) ?>"
                             class="w-full h-full object-contain transition-transform duration-300 hover:scale-105"
                             onerror="this.onerror=null; this.src='<?= htmlspecialchars($p['gambar']) ?>';">
                    <?php else: ?>
                        <div class="flex flex-col items-center text-slate-300 gap-3">
                            <div class="w-20 h-20 rounded-2xl bg-white shadow-xs flex items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">ZenCare Medical Product</span>
                        </div>
                    <?php endif; ?>

                    <!-- Category Pill Top Left -->
                    <div class="absolute top-3.5 left-3.5">
                        <span class="px-2.5 py-1 bg-white/95 backdrop-blur-md text-zc text-[10px] font-extrabold rounded-lg border border-zc/20 uppercase tracking-wider shadow-xs">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>
                    </div>

                    <!-- SKU Top Right -->
                    <?php if ($p['sku_variasi']): ?>
                    <div class="absolute top-3.5 right-3.5">
                        <span class="px-2.5 py-1 bg-slate-900/80 text-white text-[10px] font-mono rounded-lg tracking-wider">
                            SKU: <?= htmlspecialchars($p['sku_variasi']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quality Seals -->
                <div class="w-full grid grid-cols-3 gap-2 mt-4 text-center">
                    <div class="p-2 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="block text-emerald-600 font-bold text-xs">100% Asli</span>
                        <span class="text-[9px] text-zcMut">Standar Medis</span>
                    </div>
                    <div class="p-2 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="block text-zc font-bold text-xs">Izin Edar</span>
                        <span class="text-[9px] text-zcMut">Kemenkes RI</span>
                    </div>
                    <div class="p-2 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="block text-slate-700 font-bold text-xs">Grosir B2B</span>
                        <span class="text-[9px] text-zcMut">Kemasan Box</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Product Info & Actions (7 cols) -->
            <div class="lg:col-span-7 flex flex-col">
                
                <!-- Product Titles -->
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs font-bold text-zc uppercase tracking-wider"><?= htmlspecialchars($p['kategori']) ?></span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-xs text-slate-400 font-medium">Distribusi Resmi</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-zcTxt leading-tight">
                        <?= htmlspecialchars($p['nama_induk']) ?>
                    </h1>
                    <p class="text-base text-zcMut font-medium mt-1">
                        Variasi Spesifik: <strong class="text-zcTxt"><?= htmlspecialchars($p['nama_variasi']) ?></strong>
                    </p>
                </div>

                <!-- Price Block -->
                <div class="p-4 bg-slate-50/90 border border-slate-200/80 rounded-2xl mb-5 flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
                    <div>
                        <span class="text-xs text-zcMut block mb-0.5 font-medium">Harga Grosir Resmi (Online):</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-zcTxt tracking-tight">
                                Rp <?= number_format($harga, 0, ',', '.') ?>
                            </span>
                            <span class="text-xs font-bold text-zc">/ <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                    </div>
                    <?php if ($hargaEceran > 0): ?>
                    <div class="sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200">
                        <span class="text-[10px] text-slate-400 block font-medium">Referensi Eceran Apotek/POS:</span>
                        <span class="text-xs font-bold text-slate-600">
                            ~Rp <?= number_format($hargaEceran, 0, ',', '.') ?> <span class="font-normal text-slate-400">/ <?= htmlspecialchars($satKecil) ?></span>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Conversion Highlight Box -->
                <div class="flex items-center justify-between px-4 py-3 bg-amber-50/80 border border-amber-200/70 rounded-xl text-xs mb-5">
                    <div class="flex items-center gap-2 text-amber-900 font-semibold">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                        <span>Konversi Satuan Grosir:</span>
                    </div>
                    <span class="font-extrabold text-amber-900 bg-white px-2.5 py-0.5 rounded-lg border border-amber-200 shadow-xs">
                        1 <?= htmlspecialchars($satBesar) ?> = <?= $rasio ?> <?= htmlspecialchars($satKecil) ?>
                    </span>
                </div>

                <!-- Variation Switcher (if product has other variants) -->
                <?php if (count($siblings) > 1): ?>
                <div class="mb-5">
                    <label class="block text-xs font-extrabold text-zcTxt mb-2">Pilihan Varian Lain Produk Ini:</label>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($siblings as $sib):
                            $isCurrent = ($sib['id'] == $p['id']);
                            $sibRasio  = intval($sib['rasio_konversi']) ?: 1;
                            $sibStokBox = floor(intval($sib['stok_sistem']) / $sibRasio);
                        ?>
                        <a href="produk.php?id=<?= $sib['id'] ?>" 
                           class="px-3 py-2 rounded-xl text-xs font-bold border transition-all flex items-center gap-2 <?= $isCurrent ? 'bg-zc text-white border-zc shadow-sm' : 'bg-white text-zcTxt border-slate-200 hover:border-zc hover:bg-slate-50' ?>">
                            <span><?= htmlspecialchars($sib['nama_variasi']) ?></span>
                            <span class="text-[10px] <?= $isCurrent ? 'text-blue-100' : 'text-zcMut' ?>">
                                Rp <?= number_format($sib['harga_jual_besar'], 0, ',', '.') ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Stock Status Bar -->
                <div class="flex items-center justify-between p-3 rounded-xl border <?= $stokClass ?> text-xs font-semibold mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full <?= $stokDot ?> animate-pulse"></span>
                        <span><?= htmlspecialchars($stokLabel) ?></span>
                    </div>
                    <span class="text-[10px] opacity-80">Cabang: <?= htmlspecialchars($cabangAktif['nama'] ?? 'Utama') ?></span>
                </div>

                <!-- Action Controls (Stepper + Add to Cart & Buy Now) -->
                <?php if ($stokBox >= 1): ?>
                <div class="bg-slate-50 border border-slate-200/80 p-4 rounded-2xl mt-auto mb-4">
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        
                        <!-- Stepper -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-xs font-bold text-zcTxt shrink-0">Jumlah Box:</span>
                            <div class="flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                    class="w-9 h-9 flex items-center justify-center hover:bg-slate-100 text-zcTxt font-bold text-sm transition border-r border-slate-200 cursor-pointer">
                                    &minus;
                                </button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                    class="w-12 text-center text-xs font-bold py-1 bg-transparent focus:outline-none select-none">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                    class="w-9 h-9 flex items-center justify-center hover:bg-slate-100 text-zcTxt font-bold text-sm transition border-l border-slate-200 cursor-pointer">
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="grid grid-cols-2 gap-2 w-full flex-1">
                            <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_lengkap']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-zcLt hover:bg-blue-100 text-zc font-bold text-xs border border-zc/30 transition shadow-xs cursor-pointer active:scale-95">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                                + Keranjang
                            </button>
                            <button onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes($p['nama_lengkap']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-zc hover:bg-zcHv text-white font-bold text-xs shadow-md transition cursor-pointer active:scale-95">
                                Beli Sekarang
                            </button>
                        </div>

                    </div>
                </div>
                <?php else: ?>
                <div class="p-4 bg-slate-100 border border-slate-200 rounded-2xl text-center text-slate-400 font-semibold text-xs mt-auto">
                    Persediaan produk ini saat ini kosong di cabang terpilih. Silakan ganti cabang pengiriman di bagian atas untuk memeriksa stok lokasi lain.
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- ── TABBED CONTENT ZONE (Pharmify Style) ── -->
        <div class="mt-10 pt-4">
            <!-- Tab Headers -->
            <div class="flex gap-4 border-b border-slate-200 mb-6 overflow-x-auto scrollbar-thin">
                <button onclick="switchTab('tab-desc')" id="btn-tab-desc" class="px-4 py-3 text-[13px] font-extrabold text-zc border-b-2 border-zc transition whitespace-nowrap">Deskripsi Produk</button>
                <button onclick="switchTab('tab-specs')" id="btn-tab-specs" class="px-4 py-3 text-[13px] font-bold text-slate-500 hover:text-zc border-b-2 border-transparent transition whitespace-nowrap">Spesifikasi &amp; Indikasi</button>
                <button onclick="switchTab('tab-ship')" id="btn-tab-ship" class="px-4 py-3 text-[13px] font-bold text-slate-500 hover:text-zc border-b-2 border-transparent transition whitespace-nowrap">Informasi Pengiriman</button>
            </div>

            <!-- Tab Panels -->
            <div class="bg-white text-[13px] leading-relaxed text-zcMut">
                
                <!-- Panel: Deskripsi -->
                <div id="tab-desc" class="block animate-fade-in">
                    <h4 class="font-bold text-zcTxt mb-3">Informasi Umum Produk</h4>
                    <p class="whitespace-pre-line mb-4">
                        <?= htmlspecialchars($p['deskripsi'] ?? 'Produk berstandar medis tinggi, disimpan dalam ruangan beriklim kontrol sesuai standar Good Distribution Practice (GDP).') ?>
                    </p>
                    <div class="p-3 bg-blue-50/60 border border-blue-100 rounded-xl text-[11px] text-blue-900 flex items-start gap-2 max-w-2xl">
                        <svg class="w-4 h-4 text-zc shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Untuk pembelian jumlah kontainer/karton besar rumah sakit, hubungi bagian logistik cabang untuk pengiriman kargo khusus.</span>
                    </div>
                </div>

                <!-- Panel: Spesifikasi -->
                <div id="tab-specs" class="hidden animate-fade-in">
                    <h4 class="font-bold text-zcTxt mb-3">Spesifikasi Teknis</h4>
                    <div class="max-w-2xl border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-100 bg-slate-50/50">
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">Kategori Produk</span>
                            <span class="font-bold text-zcTxt"><?= htmlspecialchars($p['kategori']) ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">SKU Variasi</span>
                            <span class="font-mono font-bold text-zcTxt"><?= htmlspecialchars($p['sku_variasi'] ?: '-') ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">Kemasan Besar (Grosir)</span>
                            <span class="font-bold text-zcTxt"><?= htmlspecialchars($satBesar) ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">Kemasan Kecil (Eceran)</span>
                            <span class="font-bold text-zcTxt"><?= htmlspecialchars($satKecil) ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3 bg-amber-50/30">
                            <span class="text-slate-500 font-medium text-amber-900">Rasio Konversi</span>
                            <span class="font-bold text-amber-900">1 <?= htmlspecialchars($satBesar) ?> = <?= $rasio ?> <?= htmlspecialchars($satKecil) ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">Berat Pengiriman</span>
                            <span class="font-bold text-zcTxt"><?= intval($p['berat_gram']) ?> Gram / <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Panel: Pengiriman -->
                <div id="tab-ship" class="hidden animate-fade-in">
                    <h4 class="font-bold text-zcTxt mb-3">Ketentuan Pengiriman B2B</h4>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-4">
                        <li>Pengiriman diproses dari <strong>Cabang <?= htmlspecialchars($cabangAktif['nama'] ?? 'Utama') ?></strong>.</li>
                        <li>Estimasi pengiriman logistik medis menggunakan armada khusus berpendingin (jika diperlukan) memakan waktu 1-3 hari kerja.</li>
                        <li>Pickup In-Store tersedia. Anda bisa mengambil pesanan langsung ke gudang cabang terdekat.</li>
                    </ul>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold mt-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                        Mendukung Ekspedisi Nasional & Instan
                    </div>
                </div>

            </div>
        </div>

        <script>
        function switchTab(tabId) {
            // Hide all
            document.getElementById('tab-desc').classList.add('hidden');
            document.getElementById('tab-desc').classList.remove('block');
            document.getElementById('tab-specs').classList.add('hidden');
            document.getElementById('tab-specs').classList.remove('block');
            document.getElementById('tab-ship').classList.add('hidden');
            document.getElementById('tab-ship').classList.remove('block');
            
            // Reset buttons
            const btns = ['btn-tab-desc', 'btn-tab-specs', 'btn-tab-ship'];
            btns.forEach(id => {
                const b = document.getElementById(id);
                b.classList.remove('text-zc', 'border-zc', 'font-extrabold');
                b.classList.add('text-slate-500', 'border-transparent', 'font-bold');
            });
            
            // Show active
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            
            // Highlight button
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove('text-slate-500', 'border-transparent', 'font-bold');
            activeBtn.classList.add('text-zc', 'border-zc', 'font-extrabold');
        }
        </script>
        <style>
        .animate-fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        </style>

    </div>

    <!-- ── Related Products Carousel/Grid ── -->
    <?php if (!empty($related)): ?>
    <section class="mb-10">
        <h3 class="text-base font-extrabold text-zcTxt mb-4">Produk Terkait di Kategori <?= htmlspecialchars($p['kategori']) ?></h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php foreach ($related as $rel): ?>
            <a href="produk.php?id=<?= $rel['id'] ?>" class="p-3 bg-white rounded-2xl border border-slate-100 hover:border-zc/40 hover:shadow-md transition-all group flex flex-col">
                <div class="h-32 rounded-xl bg-slate-50 p-2 flex items-center justify-center mb-2 overflow-hidden">
                    <?php if (!empty($rel['gambar'])): ?>
                        <img src="../<?= htmlspecialchars($rel['gambar']) ?>" alt="" class="w-full h-full object-contain group-hover:scale-105 transition" onerror="this.onerror=null; this.src='<?= htmlspecialchars($rel['gambar']) ?>';">
                    <?php else: ?>
                        <svg class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <?php endif; ?>
                </div>
                <h4 class="text-xs font-bold text-zcTxt group-hover:text-zc line-clamp-1 mb-1">
                    <?= htmlspecialchars($rel['nama_induk']) ?> - <?= htmlspecialchars($rel['nama_variasi']) ?>
                </h4>
                <span class="text-xs font-extrabold text-zc mt-auto">
                    Rp <?= number_format($rel['harga_jual'], 0, ',', '.') ?> <span class="text-[10px] font-normal text-zcMut">/ <?= htmlspecialchars($rel['satuan_besar'] ?: 'Box') ?></span>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/footer.php'; ?>
