<?php
// File: ecommerce/cara_pembelian.php — Panduan Cara Pembelian & Alur Omnichannel (Pharmify Editorial Style)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Cara Pembelian";
require_once __DIR__ . '/header.php';
?>

<main class="bg-white flex-1">
    
    <!-- Pharmify Subpage Molecular Banner -->
    <section class="pharmify-banner py-11 lg:py-14 text-center text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">Cara Pembelian</h1>
            <nav class="flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-white font-semibold">Panduan Cara Belanja</span>
            </nav>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 lg:px-8 py-8 sm:py-12 pb-24 md:pb-12 space-y-8 sm:space-y-10">

        <!-- 2 Opsi Metode Pemenuhan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-[#eef6fb] p-6 border border-[#cbe4f5]">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#1a75d2] block mb-1">OPSI PENGIRIMAN 1</span>
                <h3 class="font-extrabold text-[#0f2d5a] text-base mb-2">Kirim via Kurir Ekspedisi Nasional</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pesanan dikemas aman menggunakan pelindung kemasan standar medis dan dikirim ke alamat Anda melalui kurir JNE, POS Indonesia, atau TIKI dengan perhitungan tarif otomatis.
                </p>
            </div>
            <div class="bg-[#eef6fb] p-6 border border-[#cbe4f5]">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#0d9488] block mb-1">OPSI PENGIRIMAN 2</span>
                <h3 class="font-extrabold text-[#0f2d5a] text-base mb-2">Ambil Langsung di Toko (Pick-Up)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Bebas biaya ongkos kirim. Pesanan disiapkan langsung oleh staf gudang kami di Cabang Muharto. Anda cukup datang dan menunjukkan nomor invoice pesanan.
                </p>
            </div>
        </div>

        <!-- 5 Langkah Mudah -->
        <div class="border border-slate-200 p-6 sm:p-10 bg-white">
            <div class="border-b border-slate-200 pb-4 mb-8">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#1a75d2] block mb-1">ALUR PEMESANAN</span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-[#0f2d5a]">5 Langkah Berbelanja di ZenCare Medical</h2>
            </div>

            <div class="space-y-8">

                <!-- STEP 1 -->
                <div class="flex gap-5 items-start">
                    <div class="w-9 h-9 bg-[#0f2d5a] text-white font-extrabold text-sm flex items-center justify-center shrink-0">
                        01
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-[#0f2d5a] mb-1.5">Pilih Produk &amp; Variasi Kemasan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-3">
                            Jelajahi katalog obat-obatan dan alat kesehatan resmi kami. Gunakan fitur pencarian atau saringan kategori untuk menemukan produk dengan cepat.
                        </p>
                        <div class="p-4 bg-[#f6f9fc] border-l-4 border-[#0f2d5a] text-xs text-slate-700 leading-relaxed">
                            <strong class="text-[#0f2d5a]">Ketentuan Kemasan Grosir Online:</strong> Penjualan daring di ZenCare Medical melayani satuan kemasan utuh pabrik (seperti Box, Dus, atau Botol) guna menjamin keutuhan segel selama pengiriman.
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="flex gap-5 items-start">
                    <div class="w-9 h-9 bg-[#0f2d5a] text-white font-extrabold text-sm flex items-center justify-center shrink-0">
                        02
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-[#0f2d5a] mb-1.5">Tambahkan ke Keranjang Belanja</h3>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Tentukan jumlah satuan besar yang diinginkan, kemudian klik tombol <strong>"+ Keranjang"</strong> atau <strong>"Beli Sekarang"</strong>. Anda dapat meninjau daftar belanja melalui panel keranjang di sudut kanan atas.
                        </p>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="flex gap-5 items-start">
                    <div class="w-9 h-9 bg-[#0f2d5a] text-white font-extrabold text-sm flex items-center justify-center shrink-0">
                        03
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-[#0f2d5a] mb-1.5">Pilih Metode Pengiriman pada Halaman Checkout</h3>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Pilih pengiriman ekspedisi ke alamat tujuan Anda (lengkap dengan estimasi ongkos kirim) atau pilih opsi <strong>Ambil di Toko (Pick-Up)</strong> tanpa biaya pengiriman.
                        </p>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="flex gap-5 items-start">
                    <div class="w-9 h-9 bg-[#0f2d5a] text-white font-extrabold text-sm flex items-center justify-center shrink-0">
                        04
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-[#0f2d5a] mb-1.5">Pembayaran &amp; Reservasi Stok Otomatis (Batas 2 Jam)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-3">
                            Selesaikan pembayaran melalui Midtrans (Virtual Account BCA/Mandiri/BRI/BNI, QRIS, GoPay, ShopeePay, atau Kartu Kredit).
                        </p>
                        <div class="p-4 bg-[#eef6fb] border border-[#cbe4f5] text-xs text-[#0f2d5a] leading-relaxed">
                            <strong>Sistem Penguncian Stok (Reserved Stock):</strong> Saat pesanan dibuat dengan status <em>Menunggu Pembayaran</em>, sistem mengunci stok batch FEFO dan unit serial Anda selama maksimal <strong>2 jam</strong>. Apabila melewati batas waktu tersebut tanpa pembayaran, pesanan otomatis dibatalkan dan stok dikembalikan ke etalase.
                        </div>
                    </div>
                </div>

                <!-- STEP 5 -->
                <div class="flex gap-5 items-start">
                    <div class="w-9 h-9 bg-[#0f2d5a] text-white font-extrabold text-sm flex items-center justify-center shrink-0">
                        05
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-[#0f2d5a] mb-1.5">Pantau Status Pesanan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Lacak tahapan pemrosesan pesanan mulai dari <em>Diproses</em>, <em>Dikirim</em> / <em>Siap Diambil</em>, hingga <em>Selesai</em> beserta nomor resi pengiriman melalui halaman <strong>Akun &amp; Riwayat Pesanan</strong>.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Bottom CTA -->
            <div class="mt-8 sm:mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <span class="text-xs text-slate-500">Siap memesan kebutuhan obat dan alat kesehatan Anda?</span>
                <div class="flex flex-col min-[380px]:flex-row gap-3 w-full sm:w-auto">
                    <a href="kategori.php" class="flex-1 sm:flex-initial text-center px-6 py-3 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition">
                        LIHAT KATALOG
                    </a>
                    <a href="https://wa.me/62895343436025?text=Halo%20ZenCare%20Medical,%20saya%20ingin%20bertanya%20mengenai%20cara%20pembelian" target="_blank" class="flex-1 sm:flex-initial text-center px-6 py-3 border border-[#0f2d5a] text-[#0f2d5a] hover:bg-[#eef6fb] font-bold text-xs uppercase tracking-wider transition">
                        HUBUNGI KAMI
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
