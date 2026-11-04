<?php
// File: ecommerce/tentang_kami.php — Tentang ZenCare Medical (Pharmify Editorial Style)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Tentang Kami";
require_once __DIR__ . '/header.php';
?>

<main class="bg-white flex-1">
    
    <!-- Pharmify Subpage Molecular Banner -->
    <section class="pharmify-banner py-11 lg:py-14 text-center text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">Tentang ZenCare Medical</h1>
            <nav class="flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-white font-semibold">Tentang Kami</span>
            </nav>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 lg:px-8 py-8 sm:py-12 pb-24 md:pb-12 space-y-8 sm:space-y-12">

        <!-- Editorial Intro -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            <div class="md:col-span-7 space-y-4">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#1a75d2] block">DISTRIBUTOR RESMI ALAT KESEHATAN &amp; OBAT</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0f2d5a] leading-tight">
                    Komitmen Mutu &amp; Keaslian Produk untuk Kesehatan Masyarakat
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    ZenCare Medical hadir sebagai pusat penyediaan obat-obatan resmi dan alat kesehatan terstandarisasi yang menghubungkan ketersediaan stok gudang fisik secara langsung dengan kemudahan pemesanan daring.
                </p>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Seluruh produk obat-obatan kami dikelola menggunakan sistem pelacakan nomor batch <strong>FEFO (First Expired, First Out)</strong> dan pencatatan nomor seri unit alat kesehatan untuk memastikan setiap produk yang sampai ke tangan pelanggan memiliki masa kedaluwarsa aman serta jaminan garansi resmi.
                </p>
            </div>
            <div class="md:col-span-5 bg-[#eef6fb] p-8 border border-[#cbe4f5] space-y-5">
                <div>
                    <span class="text-3xl font-extrabold text-[#0f2d5a] block">100%</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#1a75d2]">Produk Resmi &amp; Bersegel Pabrik</span>
                </div>
                <div class="border-t border-[#cbe4f5] pt-4">
                    <span class="text-3xl font-extrabold text-[#0f2d5a] block">FEFO &amp; SN</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#1a75d2]">Kontrol Batch Obat &amp; Serial Alkes</span>
                </div>
                <div class="border-t border-[#cbe4f5] pt-4">
                    <span class="text-3xl font-extrabold text-[#0f2d5a] block">Omnichannel</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#1a75d2]">Sinkronisasi Stok Toko &amp; E-Commerce</span>
                </div>
            </div>
        </div>

        <!-- 3 Pilar Layanan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border border-slate-200 p-6 bg-white">
                <div class="w-10 h-10 bg-[#0f2d5a] text-white flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-[#0f2d5a] mb-2">Jaminan Keaslian Mutu</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Semua produk obat dan alat kesehatan bersumber dari distributor resmi dengan penyimpanan higienis dan kontrol suhu ruang yang terjaga.
                </p>
            </div>

            <div class="border border-slate-200 p-6 bg-white">
                <div class="w-10 h-10 bg-[#0f2d5a] text-white flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-[#0f2d5a] mb-2">Stok Akurat Real-Time</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Ketersediaan stok kemasan box pada katalog online terhubung langsung dengan inventori gudang utama sehingga bebas dari risiko selisih stok.
                </p>
            </div>

            <div class="border border-slate-200 p-6 bg-white">
                <div class="w-10 h-10 bg-[#0f2d5a] text-white flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-[#0f2d5a] mb-2">Pengiriman &amp; Pick-Up</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Mendukung pengiriman ekspedisi ke seluruh Indonesia maupun pengambilan langsung di outlet fisik Cabang Muharto tanpa biaya ongkos kirim.
                </p>
            </div>
        </div>

        <!-- Customer Care Strip -->
        <div class="bg-[#0f2d5a] text-white p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 sm:gap-6">
            <div>
                <h3 class="text-base sm:text-lg font-extrabold mb-1">Layanan Informasi Produk &amp; Pelanggan</h3>
                <p class="text-xs text-sky-200 max-w-xl leading-relaxed">
                    Tim kami siap membantu Anda terkait informasi spesifikasi alat kesehatan, ketersediaan kemasan grosir, maupun panduan pemesanan.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
                <a href="kategori.php" class="text-center px-6 py-3 bg-white text-[#0f2d5a] font-bold text-xs uppercase tracking-widest hover:bg-sky-50 transition">
                    LIHAT KATALOG
                </a>
                <a href="https://wa.me/62895343436025?text=Halo%20CS%20ZenCare%20Medical,%20saya%20ingin%20bertanya%20mengenai%20produk%20kesehatan" target="_blank" class="text-center px-6 py-3 bg-[#1a75d2] hover:bg-[#1562b3] text-white font-bold text-xs uppercase tracking-widest transition">
                    HUBUNGI WHATSAPP
                </a>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
