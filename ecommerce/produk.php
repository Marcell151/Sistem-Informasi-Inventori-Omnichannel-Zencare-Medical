<?php
// File: ecommerce/produk.php — Halaman Detail Produk (Pharmify Editorial Layout)
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
           v.gambar AS gambar,
           v.sku_variasi,
           i.deskripsi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           (SELECT COALESCE(SUM(sb.stok_sisa), 0) 
            FROM stok_batch sb 
            WHERE sb.id_variasi = v.id 
              AND sb.no_batch NOT LIKE '%.%' 
              AND sb.tgl_exp >= CURDATE()
           ) AS stok_batch_utuh,
           v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id 
    WHERE v.id = ? AND v.is_active = 1 AND i.is_active = 1
");
$stmt->execute([$productId]);
$p = $stmt->fetch();

if (!$p) {
    echo "<div class='max-w-4xl mx-auto py-24 px-4 text-center'>
            <h2 class='text-xl font-extrabold text-[#0f2d5a] mb-2'>Produk Tidak Ditemukan</h2>
            <p class='text-xs text-slate-500 mb-6'>Produk yang Anda cari mungkin telah dinonaktifkan atau dihapus.</p>
            <a href='index.php' class='px-6 py-2.5 bg-[#0f2d5a] text-white text-xs font-bold uppercase tracking-wider'>Kembali ke Katalog</a>
          </div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$rasio       = max(1, intval($p['rasio_konversi']));
$stokBox     = ($p['kategori'] === 'Obat')
    ? (int) floor(intval($p['stok_batch_utuh']) / $rasio)
    : (int) floor(intval($p['stok_sistem']) / $rasio);
$harga       = floatval($p['harga_jual']);
$hargaEceran = floatval($p['harga_eceran']);
$satBesar    = $p['satuan_besar'] ?: 'Box';
$satKecil    = $p['satuan_kecil'] ?: 'Pcs';

// Fetch other variations under the same parent product
$stmtVariants = $pdo->prepare("
    SELECT v.id, v.nama_variasi, v.harga_jual_besar, v.sku_variasi, COALESCE(sc.stok, 0) AS stok_sistem,
           (SELECT COALESCE(SUM(sb.stok_sisa), 0) FROM stok_batch sb WHERE sb.id_variasi = v.id AND sb.no_batch NOT LIKE '%.%' AND sb.tgl_exp >= CURDATE()) AS stok_batch_utuh,
           v.rasio_konversi
    FROM produk_variasi v
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id 
    WHERE v.id_produk_induk = ? AND v.is_active = 1
    ORDER BY v.id ASC
");
$stmtVariants->execute([$p['id_produk_induk']]);
$siblings = $stmtVariants->fetchAll();

// Related products in the same category
$stmtRelated = $pdo->prepare("
    SELECT v.id, i.nama_produk AS nama_induk, v.nama_variasi,
           CONCAT(i.nama_produk, ' — ', v.nama_variasi) AS nama_produk,
           i.kategori, v.harga_jual_besar AS harga_jual, v.berat AS berat_gram,
           v.gambar AS gambar, v.satuan_besar, v.satuan_kecil, v.rasio_konversi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           (SELECT COALESCE(SUM(sb.stok_sisa), 0) FROM stok_batch sb WHERE sb.id_variasi = v.id AND sb.no_batch NOT LIKE '%.%' AND sb.tgl_exp >= CURDATE()) AS stok_batch_utuh
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id
    WHERE i.kategori = ? AND v.id != ? AND v.is_active = 1 AND v.tampil_di_online = 1
    LIMIT 4
");
$stmtRelated->execute([$p['kategori'], $p['id']]);
$related = $stmtRelated->fetchAll();

function getProductImageSrc($gbr) {
    if (empty($gbr)) return '../assets/img/no-image.png';
    if (str_starts_with($gbr, 'http')) return $gbr;
    if (str_contains($gbr, '/')) return '../' . $gbr;
    return '../assets/img/produk/' . $gbr;
}

$rawGambar = $p['gambar'] ?? '';
$arrGambar = $rawGambar ? array_filter(array_map('trim', explode(',', $rawGambar))) : [];
$formattedImages = array_values(array_map('getProductImageSrc', $arrGambar));
$totalImages = count($formattedImages);
?>

<main class="flex-1 w-full bg-white">

    <!-- ============================================================ -->
    <!-- 1. PHARMIFY MOLECULAR BREADCRUMB BANNER                      -->
    <!-- ============================================================ -->
    <section class="pharmify-banner py-11 lg:py-14 text-center text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">
                <?= htmlspecialchars($p['nama_induk']) ?>
            </h1>
            <nav class="flex items-center justify-center flex-wrap gap-2 text-xs font-medium text-sky-200/90">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <span>&rsaquo;</span>
                <a href="kategori.php?kategori=<?= urlencode($p['kategori']) ?>" class="hover:text-white transition"><?= htmlspecialchars($p['kategori']) ?></a>
                <span>&rsaquo;</span>
                <span class="text-white font-semibold"><?= htmlspecialchars($p['nama_lengkap']) ?></span>
            </nav>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. MAIN PRODUCT DETAIL 2-COLUMN SHOWCASE (Reference 3)       -->
    <!-- ============================================================ -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

            <!-- LEFT: Product Image Viewport & Thumbnails (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="relative w-full aspect-square bg-[#f6f8fa] border border-slate-200/70 flex items-center justify-center p-8 overflow-hidden group select-none">
                    <?php if ($totalImages > 0): ?>
                        <img id="main_product_image" 
                             src="<?= htmlspecialchars($formattedImages[0]) ?>"
                             alt="<?= htmlspecialchars($p['nama_lengkap']) ?>"
                             onclick="openImageLightbox()"
                             class="w-full h-full object-contain transition-all duration-300 hover:scale-105 mix-blend-multiply cursor-zoom-in"
                             onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                    <?php else: ?>
                        <div class="flex flex-col items-center text-slate-300 gap-2">
                            <svg class="w-16 h-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            <span class="text-xs font-medium text-slate-400">Gambar Tidak Tersedia</span>
                        </div>
                    <?php endif; ?>

                    <?php if ($totalImages > 1): ?>
                    <button type="button" onclick="prevProductSlide(event)" 
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/90 hover:bg-[#0f2d5a] text-[#0f2d5a] hover:text-white border border-slate-200 flex items-center justify-center transition z-20 cursor-pointer" 
                            title="Foto Sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" onclick="nextProductSlide(event)" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/90 hover:bg-[#0f2d5a] text-[#0f2d5a] hover:text-white border border-slate-200 flex items-center justify-center transition z-20 cursor-pointer" 
                            title="Foto Selanjutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div class="absolute bottom-3 right-3 px-2.5 py-1 bg-white/90 text-[#0f2d5a] text-[11px] font-bold border border-slate-200 z-10">
                        <span id="curr_slide_number">1</span> / <span><?= $totalImages ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($totalImages > 1): ?>
                <div id="thumbnail_track" class="w-full flex gap-3 overflow-x-auto pt-3 pb-1 scrollbar-hide">
                    <?php foreach ($formattedImages as $idx => $thumbSrc): ?>
                        <button type="button" 
                             onclick="selectSlide(<?= $idx ?>)"
                             data-idx="<?= $idx ?>"
                             class="thumbnail-card shrink-0 w-20 h-20 border <?= $idx === 0 ? 'border-[#0f2d5a] ring-1 ring-[#0f2d5a] opacity-100' : 'border-slate-200 opacity-60 hover:opacity-100' ?> cursor-pointer bg-[#f6f8fa] p-2 focus:outline-none flex items-center justify-center transition">
                            <img src="<?= htmlspecialchars($thumbSrc) ?>" alt="Sudut <?= $idx + 1 ?>" class="w-full h-full object-contain mix-blend-multiply" onerror="this.src='../assets/img/no-image.png'">
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Lightbox Modal -->
                <div id="image_lightbox_modal" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-xs flex items-center justify-center p-4">
                    <button type="button" onclick="closeImageLightbox()" class="absolute top-4 right-4 w-10 h-10 bg-white/20 hover:bg-white text-white hover:text-black flex items-center justify-center transition text-xl font-bold cursor-pointer z-50">&times;</button>
                    <button type="button" onclick="prevProductSlide(event); syncLightboxImage();" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 hover:bg-white text-white hover:text-black flex items-center justify-center transition text-2xl font-bold cursor-pointer z-50">‹</button>
                    <button type="button" onclick="nextProductSlide(event); syncLightboxImage();" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 hover:bg-white text-white hover:text-black flex items-center justify-center transition text-2xl font-bold cursor-pointer z-50">›</button>
                    <div class="max-w-4xl max-h-[85vh] p-4 flex flex-col items-center">
                        <img id="lightbox_image" src="<?= htmlspecialchars($formattedImages[0]) ?>" alt="Preview" class="max-w-full max-h-[75vh] object-contain bg-white p-4">
                        <div class="mt-3 text-white text-xs font-semibold">
                            <?= htmlspecialchars($p['nama_lengkap']) ?> — Foto <span id="lightbox_counter">1</span> dari <?= $totalImages ?>
                        </div>
                    </div>
                </div>

                <script>
                    const galleryImages = <?= json_encode($formattedImages) ?>;
                    let currentSlide = 0;

                    function selectSlide(idx) {
                        if (!galleryImages.length) return;
                        if (idx < 0) idx = galleryImages.length - 1;
                        if (idx >= galleryImages.length) idx = 0;
                        currentSlide = idx;

                        const mainImg = document.getElementById('main_product_image');
                        if (mainImg) {
                            mainImg.style.opacity = '0.4';
                            setTimeout(() => {
                                mainImg.src = galleryImages[currentSlide];
                                mainImg.style.opacity = '1';
                            }, 100);
                        }

                        const counter = document.getElementById('curr_slide_number');
                        if (counter) counter.innerText = (currentSlide + 1);

                        document.querySelectorAll('.thumbnail-card').forEach((card, i) => {
                            if (i === currentSlide) {
                                card.classList.remove('border-slate-200', 'opacity-60');
                                card.classList.add('border-[#0f2d5a]', 'ring-1', 'ring-[#0f2d5a]', 'opacity-100');
                            } else {
                                card.classList.remove('border-[#0f2d5a]', 'ring-1', 'ring-[#0f2d5a]', 'opacity-100');
                                card.classList.add('border-slate-200', 'opacity-60');
                            }
                        });
                        syncLightboxImage();
                    }
                    function nextProductSlide(e) { if (e) e.stopPropagation(); selectSlide(currentSlide + 1); }
                    function prevProductSlide(e) { if (e) e.stopPropagation(); selectSlide(currentSlide - 1); }
                    function openImageLightbox() {
                        const modal = document.getElementById('image_lightbox_modal');
                        if (modal) { syncLightboxImage(); modal.classList.remove('hidden'); }
                    }
                    function closeImageLightbox() {
                        const modal = document.getElementById('image_lightbox_modal');
                        if (modal) modal.classList.add('hidden');
                    }
                    function syncLightboxImage() {
                        const lbImg = document.getElementById('lightbox_image');
                        const lbCounter = document.getElementById('lightbox_counter');
                        if (lbImg && galleryImages[currentSlide]) lbImg.src = galleryImages[currentSlide];
                        if (lbCounter) lbCounter.innerText = (currentSlide + 1);
                    }
                </script>
                <?php endif; ?>
            </div>

            <!-- RIGHT: Product Editorial Details & Purchase Controls (7 Cols) -->
            <div class="lg:col-span-7">

                <!-- Title & Price -->
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0f2d5a] tracking-tight leading-snug mb-1.5">
                    <?= htmlspecialchars($p['nama_induk']) ?> — <?= htmlspecialchars($p['nama_variasi']) ?>
                </h2>

                <div class="flex items-baseline gap-3 mb-5 pb-5 border-b border-slate-200">
                    <span class="text-2xl font-extrabold text-[#0f2d5a]">
                        Rp <?= number_format($harga, 0, ',', '.') ?>
                    </span>
                    <span class="text-sm font-semibold text-slate-500">/ <?= htmlspecialchars($satBesar) ?></span>

                    <?php if ($p['kategori'] === 'Obat' && $hargaEceran > 0 && $rasio > 1): ?>
                    <span class="text-xs text-slate-400 ml-auto">
                        Referensi Eceran Toko: ~Rp <?= number_format($hargaEceran, 0, ',', '.') ?> / <?= htmlspecialchars($satKecil) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    <?= htmlspecialchars($p['deskripsi'] ?: 'Produk kesehatan resmi berstandar mutu tinggi, disimpan dalam ruang terkontrol sesuai standar distribusi farmasi dan alat kesehatan.') ?>
                </p>

                <!-- Pharmify Aligned Metadata Rows (Reference 3) -->
                <div class="space-y-3 text-sm border-b border-slate-200 pb-6 mb-6">
                    <div class="flex items-center">
                        <span class="w-40 shrink-0 font-bold text-[#0f2d5a]">Ketersediaan:</span>
                        <?php if ($stokBox > 0): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Tersedia (<?= $stokBox ?> <?= htmlspecialchars($satBesar) ?>)
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Stok Habis
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center">
                        <span class="w-40 shrink-0 font-bold text-[#0f2d5a]">Kategori Produk:</span>
                        <span class="text-slate-600"><?= htmlspecialchars($p['kategori']) ?></span>
                    </div>

                    <div class="flex items-center">
                        <span class="w-40 shrink-0 font-bold text-[#0f2d5a]">Kemasan Grosir:</span>
                        <span class="text-slate-700 font-semibold">
                            1 <?= htmlspecialchars($satBesar) ?><?= $rasio > 1 ? " (Isi $rasio " . htmlspecialchars($satKecil) . ")" : "" ?>
                        </span>
                    </div>

                    <div class="flex items-center">
                        <span class="w-40 shrink-0 font-bold text-[#0f2d5a]">Kode SKU:</span>
                        <span class="font-mono text-xs text-slate-600"><?= htmlspecialchars($p['sku_variasi'] ?: '-') ?></span>
                    </div>

                    <div class="flex items-center">
                        <span class="w-40 shrink-0 font-bold text-[#0f2d5a]">Pengiriman:</span>
                        <span class="text-slate-600">Kurir Ekspedisi Nasional &amp; Ambil di Toko (Bebas Ongkir)</span>
                    </div>
                </div>

                <!-- Sibling Variations Switcher -->
                <?php if (count($siblings) > 1): ?>
                <div class="mb-6">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a] mb-2.5">Pilih Variasi Kemasan / Sediaan:</label>
                    <div class="flex flex-wrap gap-2.5">
                        <?php foreach ($siblings as $sib):
                            $isCurrent = ($sib['id'] == $p['id']);
                        ?>
                        <a href="produk.php?id=<?= $sib['id'] ?>" 
                           class="px-4 py-2 text-xs font-bold border transition <?= $isCurrent ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-[#0f2d5a] border-slate-200 hover:border-[#0f2d5a]' ?>">
                            <?= htmlspecialchars($sib['nama_variasi']) ?> — Rp <?= number_format($sib['harga_jual_besar'], 0, ',', '.') ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quantity Stepper & Primary CTA Buttons -->
                <?php if ($stokBox >= 1): ?>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold text-[#0f2d5a]">Jumlah (<?= htmlspecialchars($satBesar) ?>):</span>
                        <div class="flex items-center border border-slate-300 bg-white h-10">
                            <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                class="w-10 h-full flex items-center justify-center hover:bg-slate-100 text-[#0f2d5a] font-bold text-base transition border-r border-slate-300 cursor-pointer">
                                &minus;
                            </button>
                            <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                class="w-14 text-center text-sm font-bold bg-transparent text-[#0f2d5a] focus:outline-none select-none">
                            <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                class="w-10 h-full flex items-center justify-center hover:bg-slate-100 text-[#0f2d5a] font-bold text-base transition border-l border-slate-300 cursor-pointer">
                                +
                            </button>
                        </div>
                        <span class="text-xs text-slate-400">Maks. <?= $stokBox ?> <?= htmlspecialchars($satBesar) ?></span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="button" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_lengkap']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                            class="flex-1 py-3.5 px-6 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            TAMBAH KE KERANJANG
                        </button>
                        <button type="button" onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes($p['nama_lengkap']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                            class="flex-1 py-3.5 px-6 bg-[#eef6fb] hover:bg-[#d8eefb] text-[#0f2d5a] border border-[#0f2d5a]/20 font-bold text-xs uppercase tracking-widest transition cursor-pointer flex items-center justify-center">
                            BELI SEKARANG
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-4 bg-slate-100 border border-slate-200 text-center text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    Stok Produk Sedang Habis
                </div>
                <?php endif; ?>

            </div>

        </div>

        <!-- ============================================================ -->
        <!-- 3. PHARMIFY UNDERLINE TABS (Description, Specs, Shipping)    -->
        <!-- ============================================================ -->
        <div class="mt-16 pt-8 border-t border-slate-200">
            <div class="flex gap-8 border-b border-slate-200 mb-6 overflow-x-auto">
                <button type="button" onclick="switchTab('tab-desc')" id="btn-tab-desc" class="pb-3.5 text-sm font-extrabold text-[#0f2d5a] border-b-2 border-[#0f2d5a] transition whitespace-nowrap cursor-pointer">
                    Deskripsi Produk
                </button>
                <button type="button" onclick="switchTab('tab-specs')" id="btn-tab-specs" class="pb-3.5 text-sm font-bold text-slate-400 hover:text-[#0f2d5a] border-b-2 border-transparent transition whitespace-nowrap cursor-pointer">
                    Spesifikasi Teknis
                </button>
                <button type="button" onclick="switchTab('tab-ship')" id="btn-tab-ship" class="pb-3.5 text-sm font-bold text-slate-400 hover:text-[#0f2d5a] border-b-2 border-transparent transition whitespace-nowrap cursor-pointer">
                    Ketentuan Pengiriman
                </button>
            </div>

            <div class="text-sm leading-relaxed text-slate-600 max-w-4xl">
                <!-- Panel 1: Deskripsi -->
                <div id="tab-desc" class="block">
                    <p class="whitespace-pre-line mb-4">
                        <?= htmlspecialchars($p['deskripsi'] ?? 'Produk berstandar medis tinggi, disimpan dalam ruangan beriklim terkontrol sesuai standar Good Distribution Practice (GDP).') ?>
                    </p>
                    <p class="text-xs text-slate-500">
                        Seluruh produk obat dan alat kesehatan yang dijual secara online melalui ZenCare Medical merupakan kemasan satuan besar utuh bersegel resmi pabrik.
                    </p>
                </div>

                <!-- Panel 2: Spesifikasi -->
                <div id="tab-specs" class="hidden">
                    <div class="max-w-xl border border-slate-200 divide-y divide-slate-200 text-xs">
                        <div class="flex justify-between px-4 py-3 bg-[#f8fafc]">
                            <span class="text-slate-500 font-medium">Kategori</span>
                            <span class="font-bold text-[#0f2d5a]"><?= htmlspecialchars($p['kategori']) ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">SKU Variasi</span>
                            <span class="font-mono font-bold text-[#0f2d5a]"><?= htmlspecialchars($p['sku_variasi'] ?: '-') ?></span>
                        </div>
                        <div class="flex justify-between px-4 py-3 bg-[#f8fafc]">
                            <span class="text-slate-500 font-medium">Satuan Penjualan Online</span>
                            <span class="font-bold text-[#0f2d5a]"><?= htmlspecialchars($satBesar) ?></span>
                        </div>
                        <?php if ($rasio > 1): ?>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-slate-500 font-medium">Isi Kemasan</span>
                            <span class="font-bold text-[#0f2d5a]">1 <?= htmlspecialchars($satBesar) ?> = <?= $rasio ?> <?= htmlspecialchars($satKecil) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between px-4 py-3 bg-[#f8fafc]">
                            <span class="text-slate-500 font-medium">Berat Pengiriman</span>
                            <span class="font-bold text-[#0f2d5a]"><?= intval($p['berat_gram']) ?> Gram / <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Pengiriman -->
                <div id="tab-ship" class="hidden">
                    <ul class="list-disc list-inside space-y-2 text-slate-600">
                        <li>Pengiriman diproses langsung dari <strong>Gudang Utama ZenCare Medical (Cabang Muharto)</strong>.</li>
                        <li>Tersedia layanan kurir ekspedisi nasional (JNE, POS Indonesia, dan TIKI) dengan perhitungan ongkos kirim otomatis.</li>
                        <li>Tersedia opsi <strong>Ambil di Toko (Pick-Up)</strong> tanpa biaya ongkos kirim. Pesanan dapat diambil langsung setelah pembayaran terkonfirmasi.</li>
                    </ul>
                </div>
            </div>
        </div>

        <script>
        function switchTab(tabId) {
            ['tab-desc', 'tab-specs', 'tab-ship'].forEach(id => {
                const panel = document.getElementById(id);
                const btn   = document.getElementById('btn-' + id);
                if (id === tabId) {
                    panel.classList.remove('hidden');
                    panel.classList.add('block');
                    btn.classList.remove('text-slate-400', 'border-transparent', 'font-bold');
                    btn.classList.add('text-[#0f2d5a]', 'border-[#0f2d5a]', 'font-extrabold');
                } else {
                    panel.classList.add('hidden');
                    panel.classList.remove('block');
                    btn.classList.remove('text-[#0f2d5a]', 'border-[#0f2d5a]', 'font-extrabold');
                    btn.classList.add('text-slate-400', 'border-transparent', 'font-bold');
                }
            });
        }
        </script>

        <!-- ============================================================ -->
        <!-- 4. RECOMMENDED PRODUCTS (4-Column Pharmify Grid)             -->
        <!-- ============================================================ -->
        <?php if (!empty($related)): ?>
        <section class="mt-16 pt-12 border-t border-slate-200">
            <div class="text-center mb-8">
                <h3 class="text-xl sm:text-2xl font-extrabold text-[#0f2d5a]">Rekomendasi Produk Terkait</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($related as $rel): 
                    $relGbr = !empty($rel['gambar']) ? trim(explode(',', $rel['gambar'])[0]) : '';
                    $relSrc = getProductImageSrc($relGbr);
                    $relRasio = max(1, intval($rel['rasio_konversi']));
                    $relStokBox = ($rel['kategori'] === 'Obat')
                        ? (int) floor(intval($rel['stok_batch_utuh']) / $relRasio)
                        : (int) floor(intval($rel['stok_sistem']) / $relRasio);
                    $relSatB = $rel['satuan_besar'] ?: 'Box';
                ?>
                <div class="group flex flex-col bg-white">
                    <a href="produk.php?id=<?= $rel['id'] ?>" class="block aspect-square w-full bg-[#f6f8fa] hover:bg-[#eef4fa] transition-colors p-6 mb-3.5 overflow-hidden">
                        <?php if (!empty($relGbr)): ?>
                            <img src="<?= htmlspecialchars($relSrc) ?>" alt="<?= htmlspecialchars($rel['nama_induk']) ?>" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300 mix-blend-multiply" onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                        <?php endif; ?>
                    </a>
                    <a href="produk.php?id=<?= $rel['id'] ?>" class="block mb-1.5">
                        <h4 class="text-sm font-bold text-[#0f2d5a] group-hover:text-[#1a75d2] transition-colors line-clamp-1">
                            <?= htmlspecialchars($rel['nama_induk']) ?>
                        </h4>
                        <p class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($rel['nama_variasi']) ?></p>
                    </a>
                    <div class="text-sm font-extrabold text-[#0f2d5a] mb-3 mt-auto">
                        Rp <?= number_format($rel['harga_jual'], 0, ',', '.') ?> <span class="text-xs font-normal text-slate-400">/ <?= htmlspecialchars($relSatB) ?></span>
                    </div>
                    <?php if ($relStokBox >= 1): ?>
                    <button type="button" onclick="addToCart(<?= $rel['id'] ?>, '<?= addslashes(htmlspecialchars_decode($rel['nama_produk'])) ?>', <?= floatval($rel['harga_jual']) ?>, <?= intval($rel['berat_gram']) ?>, '<?= addslashes($rel['gambar'] ?? '') ?>', '<?= addslashes($relSatB) ?>', <?= $relRasio ?>, <?= $relStokBox ?>, 1)"
                        class="w-full h-9 rounded-full bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[11px] font-bold uppercase tracking-wider transition cursor-pointer">
                        + Keranjang
                    </button>
                    <?php else: ?>
                    <div class="w-full h-9 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[11px] font-bold uppercase">
                        Stok Habis
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
