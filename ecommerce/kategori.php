<?php
// File: ecommerce/kategori.php — Katalog Kategori (Pharmify-style)
$pageTitle = "Katalog Produk";
require_once __DIR__ . '/header.php';

$search   = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

$sql = "
    SELECT v.id, i.nama_produk AS nama_induk, v.nama_variasi, i.kategori,
           v.harga_jual_besar AS harga_jual, v.harga_jual_kecil AS harga_eceran,
           v.berat AS berat_gram, i.gambar, v.sku_variasi, i.deskripsi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id 
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
";
$params = [];

if ($search) {
    $sql .= " AND (i.nama_produk LIKE ? OR v.nama_variasi LIKE ? OR v.sku_variasi LIKE ? OR i.deskripsi LIKE ?)";
    $lk = "%$search%";
    array_push($params, $lk, $lk, $lk, $lk);
}
if ($kategori) {
    $sql .= " AND i.kategori = ?";
    $params[] = $kategori;
}
$sql .= " ORDER BY i.kategori ASC, i.nama_produk ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get unique categories for pills
$allCat = $pdo->query("SELECT DISTINCT kategori FROM produk_induk WHERE is_active=1")->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
/* ── Product Card ── */
.prod-card {
    transition: transform .22s cubic-bezier(.4,0,.2,1), box-shadow .22s;
    border: 1.5px solid #e4e9f0;
}
.prod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 48px rgba(26,117,210,.15);
    border-color: #1a75d2;
}
.prod-card:hover .card-img img { transform: scale(1.06); }
.card-img img { transition: transform .3s ease; }
</style>

<main class="flex-1 w-full bg-[#f8fafc]">

    <!-- HERO BANNER (Dark Navy, Pharmify-style) -->
    <section class="bg-[#0f2d5a] relative py-12 lg:py-16 overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10 flex flex-col md:flex-row items-center gap-8">
            <div class="flex-1 text-white">
                <nav class="flex text-blue-200 text-xs font-semibold mb-4 gap-2">
                    <a href="index.php" class="hover:text-white transition">Beranda</a>
                    <span>/</span>
                    <span class="text-white"><?= $kategori ? htmlspecialchars($kategori) : 'Semua Kategori' ?></span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3">
                    <?php if ($search): ?>
                        Pencarian: "<?= htmlspecialchars($search) ?>"
                    <?php elseif ($kategori): ?>
                        Kategori: <?= htmlspecialchars($kategori) ?>
                    <?php else: ?>
                        Katalog Produk Lengkap
                    <?php endif; ?>
                </h1>
                <p class="text-blue-100 text-sm md:text-base max-w-xl">
                    <?= $kategori ? 'Temukan berbagai produk medis berkualitas tinggi dalam kategori ini.' : 'Jelajahi seluruh koleksi obat-obatan, alat kesehatan, dan perlengkapan medis kami.' ?>
                </p>
            </div>
            
            <div class="w-full md:w-80 shrink-0">
                <div class="bg-white/10 p-5 rounded-2xl border border-white/20 backdrop-blur-sm">
                    <h3 class="text-white font-bold text-sm mb-3">Filter Kategori</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="kategori.php" class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= empty($kategori) ? 'bg-white text-[#0f2d5a]' : 'bg-white/10 text-white hover:bg-white/20' ?>">Semua</a>
                        <?php foreach($allCat as $cat): ?>
                        <a href="kategori.php?kategori=<?= urlencode($cat) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $kategori === $cat ? 'bg-white text-[#0f2d5a]' : 'bg-white/10 text-white hover:bg-white/20' ?>">
                            <?= htmlspecialchars($cat) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10">
        
        <!-- Filter & Sort Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 text-[#1a75d2] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <span class="text-sm font-bold text-[#1e293b] block">Menampilkan <?= count($products) ?> Produk</span>
                    <span class="text-[11px] text-slate-500">Cabang: <?= htmlspecialchars($activeCabangInfo['nama'] ?? 'Semua') ?></span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <span class="text-xs font-bold text-slate-500 hidden sm:inline">Urutkan:</span>
                <select id="sort_select" onchange="sortProducts()" class="flex-1 sm:flex-none text-sm border border-slate-200 rounded-xl px-4 py-2.5 bg-slate-50 focus:outline-none focus:border-[#1a75d2] focus:bg-white text-[#1e293b] font-medium shadow-xs w-full sm:w-48 transition">
                    <option value="default">Relevansi</option>
                    <option value="price_asc">Harga: Rendah ke Tinggi</option>
                    <option value="price_desc">Harga: Tinggi ke Rendah</option>
                    <option value="name_asc">Nama (A-Z)</option>
                    <option value="stok_desc">Stok Terbanyak</option>
                </select>
            </div>
        </div>

        <!-- PRODUCT GRID (Identical to Home) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="product-grid">
            <?php foreach ($products as $p):
                $stokPcs  = intval($p['stok_sistem']);
                $rasio    = intval($p['rasio_konversi']) ?: 1;
                $stokBox  = floor($stokPcs / $rasio);
                $harga    = floatval($p['harga_jual']);
                $hargaEcr = floatval($p['harga_eceran']);
                $satB     = htmlspecialchars($p['satuan_besar'] ?: 'Box');
                $satK     = htmlspecialchars($p['satuan_kecil'] ?: 'Pcs');
                $desc     = trim($p['deskripsi'] ?? '');
                $descSh   = $desc ? mb_strimwidth($desc, 0, 75, '…') : 'Produk medis berkualitas dengan jaminan mutu resmi.';

                if ($stokBox <= 0)     { $stokCls='text-rose-600'; $dotCls='bg-rose-500'; $stokLbl='Habis'; }
                elseif ($stokBox <= 5) { $stokCls='text-amber-700'; $dotCls='bg-amber-400'; $stokLbl="Sisa $stokBox $satB"; }
                else                   { $stokCls='text-emerald-700'; $dotCls='bg-emerald-500'; $stokLbl="$stokBox $satB"; }
            ?>
            <div class="prod-card group bg-white rounded-2xl overflow-hidden flex flex-col"
                 data-name="<?= strtolower(htmlspecialchars($p['nama_induk'])) ?>"
                 data-price="<?= $harga ?>"
                 data-stok="<?= $stokBox ?>"
                 data-id="<?= $p['id'] ?>">

                <!-- Image -->
                <a href="produk.php?id=<?= $p['id'] ?>" class="card-img relative block bg-gradient-to-br from-slate-50 to-blue-50/30 overflow-hidden" style="height:210px;">
                    <?php if (!empty($p['gambar'])): ?>
                        <img src="../<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_induk']) ?>"
                             class="w-full h-full object-contain p-5"
                             onerror="this.onerror=null; this.classList.add('opacity-30');">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12h6M12 9v6"/></svg>
                        </div>
                    <?php endif; ?>

                    <!-- Category badge -->
                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 text-[#1a75d2] text-[10px] font-bold rounded-lg border border-[#1a75d2]/15 shadow-xs">
                        <?= htmlspecialchars($p['kategori']) ?>
                    </span>
                    <!-- Stock badge -->
                    <span class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1 bg-white/95 text-[10px] font-bold rounded-lg shadow-xs <?= $stokCls ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $dotCls ?>"></span>
                        <?= $stokLbl ?>
                    </span>
                </a>

                <!-- Card Body -->
                <div class="p-4 flex flex-col flex-1">
                    <a href="produk.php?id=<?= $p['id'] ?>" class="block mb-3">
                        <h3 class="text-sm font-extrabold text-[#1e293b] leading-snug line-clamp-2 group-hover:text-[#1a75d2] transition-colors">
                            <?= htmlspecialchars($p['nama_induk']) ?>
                        </h3>
                        <span class="text-xs text-[#64748b]"><?= htmlspecialchars($p['nama_variasi']) ?></span>
                    </a>

                    <!-- Specs Mini-table -->
                    <div class="rounded-xl bg-slate-50 border border-slate-100 divide-y divide-slate-100 mb-4 text-xs">
                        <div class="flex gap-2 px-3 py-2">
                            <span class="text-[#64748b] w-[72px] shrink-0 font-medium">Keterangan</span>
                            <span class="font-medium text-[#64748b] line-clamp-2"><?= htmlspecialchars($descSh) ?></span>
                        </div>
                        <div class="flex gap-2 px-3 py-2">
                            <span class="text-[#64748b] w-[72px] shrink-0 font-medium">Kemasan</span>
                            <span class="font-bold text-[#1e293b]">1 <?= $satB ?> = <?= $rasio ?> <?= $satK ?></span>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="mb-4 mt-auto">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-extrabold text-[#1e293b]">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                            <span class="text-xs text-[#64748b] font-medium">/ <?= $satB ?></span>
                        </div>
                        <?php if ($hargaEcr > 0 && $hargaEcr != $harga / $rasio): ?>
                        <div class="text-[10px] text-slate-400 mt-0.5">~Rp <?= number_format($hargaEcr, 0, ',', '.') ?> / <?= $satK ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- CTA Buttons -->
                    <?php if ($stokBox >= 1): ?>
                    <div class="flex items-center gap-2 mb-2.5">
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden flex-1">
                            <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                class="w-9 h-9 flex items-center justify-center hover:bg-slate-100 text-[#1e293b] font-black border-r border-slate-200 transition text-base">−</button>
                            <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                class="flex-1 text-center text-sm font-bold py-2 bg-transparent focus:outline-none select-none">
                            <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                class="w-9 h-9 flex items-center justify-center hover:bg-slate-100 text-[#1e293b] font-black border-l border-slate-200 transition text-base">+</button>
                        </div>
                        <span class="text-[10px] text-slate-400 shrink-0">Max <?= $stokBox ?></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_induk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                            class="flex items-center justify-center gap-1.5 text-xs font-bold py-2.5 rounded-xl bg-[#e8f2ff] hover:bg-blue-100 text-[#1a75d2] border border-[#1a75d2]/20 cursor-pointer active:scale-95 transition">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                            Keranjang
                        </button>
                        <button onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_induk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                            class="flex items-center justify-center gap-1.5 text-xs font-bold py-2.5 rounded-xl bg-[#1a75d2] hover:bg-[#1562b3] text-white cursor-pointer active:scale-95 transition shadow-sm">
                            Beli Sekarang
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="w-full py-3 px-4 rounded-xl bg-slate-100 text-slate-400 text-center text-xs font-semibold border border-slate-200 mt-2">
                        Stok Habis Sementara
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($products)): ?>
            <div class="col-span-full py-24 text-center bg-white rounded-3xl border border-slate-100 shadow-sm">
                <div class="w-20 h-20 rounded-full bg-slate-50 mx-auto mb-5 flex items-center justify-center border border-slate-100">
                    <svg class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-[#1e293b] mb-2">Produk Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto">
                    Maaf, tidak ada produk yang cocok dengan pencarian atau filter Anda di cabang ini.
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="kategori.php" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">Reset Filter</a>
                    <a href="index.php" class="px-6 py-2.5 bg-[#1a75d2] text-white text-sm font-bold rounded-xl hover:bg-[#0f2d5a] transition shadow-md shadow-blue-500/20">Kembali ke Beranda</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Client-side sorting
function sortProducts() {
    const val  = document.getElementById('sort_select').value;
    const grid = document.getElementById('product-grid');
    const cards = Array.from(grid.querySelectorAll('.prod-card'));
    
    cards.sort((a, b) => {
        if (val === 'price_asc')  return +a.dataset.price - +b.dataset.price;
        if (val === 'price_desc') return +b.dataset.price - +a.dataset.price;
        if (val === 'name_asc')   return a.dataset.name.localeCompare(b.dataset.name);
        if (val === 'stok_desc')  return +b.dataset.stok - +a.dataset.stok;
        return +a.dataset.id - +b.dataset.id;
    });
    
    // Smooth reordering transition
    grid.style.opacity = '0';
    setTimeout(() => {
        cards.forEach(c => grid.appendChild(c));
        grid.style.opacity = '1';
    }, 150);
}
document.getElementById('product-grid').style.transition = 'opacity 0.15s ease';
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
