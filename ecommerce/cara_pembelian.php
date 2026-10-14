<?php
// File: ecommerce/cara_pembelian.php
// Panduan Cara Pembelian & Alur Omnichannel - ZenCare Medical Store
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Cara Pembelian";
require_once __DIR__ . '/header.php';
?>

<main class="bg-[#f5f7fa] flex-1">
    
    <!-- Hero Banner -->
    <div class="relative bg-gradient-to-br from-[#0f2d5a] via-[#154689] to-[#1a75d2] text-white py-16 lg:py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAzNHYtNGgtMnY0aC00djJoNHY0aDJ2LTRoNHYtMmgtNHptMC0zMFYwaC0ydjRoLTR2Mmg0djRoMnYtNGg0VjRoLTR6bS0xOCAyNHYtNGgtMnY0SDEydjJoNHY0aDJ2LTRoNHYtMmgtNHoiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvZz48L3N2Zz4=')] opacity-30"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-blue-400/20 text-blue-200 border border-blue-300/20 text-xs font-bold uppercase tracking-wider mb-4">
                Panduan Pelanggan
            </span>
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">Cara Berbelanja di ZenCare Medical</h1>
            <p class="text-base md:text-lg text-blue-100 max-w-2xl mx-auto leading-relaxed">
                Panduan praktis langkah demi langkah berbelanja alat kesehatan dan obat resmi dengan opsi pengiriman ekspedisi atau ambil langsung di outlet fisik kami.
            </p>
        </div>
    </div>

    <!-- Step by Step Container -->
    <div class="max-w-4xl mx-auto px-4 py-12 -mt-10 relative z-20 space-y-6">

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#1a75d2] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-[#0f2d5a] text-sm mb-1">Kirim via Kurir Ekspedisi</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Pesanan dikemas dengan aman dan rapi, lalu dikirim ke alamat Anda melalui JNE, POS Indonesia, atau TIKI dengan tarif ongkir otomatis.</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-[#0f2d5a] text-sm mb-1">Ambil Sendiri di Toko (Pick-up)</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Gratis ongkir 100%. Pesanan langsung disiapkan oleh tim toko kami. Anda cukup datang dan menunjukkan nomor invoice pesanan.</p>
                </div>
            </div>
        </div>

        <!-- Step List -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 md:p-10 border border-slate-100">
            <h2 class="text-xl font-black text-[#0f2d5a] mb-8 pb-4 border-b border-slate-100 flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-[#1a75d2]"></span>
                5 Langkah Mudah Berbelanja
            </h2>

            <div class="space-y-8">

                <!-- STEP 1 -->
                <div class="flex gap-4 md:gap-6 items-start">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-[#1a75d2] text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                        1
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base md:text-lg font-bold text-[#1e293b] mb-1.5">Pilih Produk & Variasi Kemasan</h3>
                        <p class="text-xs md:text-sm text-slate-600 leading-relaxed mb-3">
                            Jelajahi katalog kami yang mencakup <strong>Obat-obatan resmi</strong> dan <strong>Alat Kesehatan berkualitas</strong>. Anda dapat menyaring berdasarkan kategori atau menggunakan kolom pencarian.
                        </p>
                        <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900 leading-relaxed">
                            <strong>💡 Catatan Grosir & E-Commerce:</strong> Penjualan daring di ZenCare Medical dikhususkan untuk kemasan grosir (seperti Box, Dus, atau Karton) guna menjaga keutuhan segel pabrik selama proses pengiriman.
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="flex gap-4 md:gap-6 items-start">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-[#1a75d2] text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                        2
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base md:text-lg font-bold text-[#1e293b] mb-1.5">Masukkan ke Keranjang Belanja</h3>
                        <p class="text-xs md:text-sm text-slate-600 leading-relaxed mb-2">
                            Tentukan jumlah kuantitas yang diinginkan, kemudian klik tombol <strong>"Tambah ke Keranjang"</strong>. Anda dapat melihat ringkasan pesanan dengan mengeklik ikon keranjang di pojok kanan atas layar.
                        </p>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="flex gap-4 md:gap-6 items-start">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-[#1a75d2] text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                        3
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base md:text-lg font-bold text-[#1e293b] mb-1.5">Tentukan Metode Pemenuhan (Kurir vs Ambil di Toko)</h3>
                        <p class="text-xs md:text-sm text-slate-600 leading-relaxed mb-3">
                            Pada halaman Checkout, tentukan bagaimana Anda ingin menerima pesanan Anda:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <div class="font-bold text-[#0f2d5a] mb-1">Opsi 1: Kirim Ekspedisi</div>
                                <p class="text-slate-500">Pilih kurir (JNE, POS, TIKI) & layanan pengiriman. Masukkan alamat lengkap penerima dan nomor telepon aktif.</p>
                            </div>
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <div class="font-bold text-[#0f2d5a] mb-1">Opsi 2: Ambil Sendiri (Pick-up)</div>
                                <p class="text-slate-500">Bebas ongkos kirim. Pesanan akan disiapkan di outlet fisik ZenCare Medical. Anda tinggal datang mengambilnya setelah siap.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="flex gap-4 md:gap-6 items-start">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-[#1a75d2] text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                        4
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base md:text-lg font-bold text-[#1e293b] mb-1.5">Selesaikan Pembayaran & Sistem Reservasi Stok (Cooldown 2 Jam)</h3>
                        <p class="text-xs md:text-sm text-slate-600 leading-relaxed mb-3">
                            Pilih metode pembayaran yang tersedia melalui payment gateway resmi Midtrans (BCA Virtual Account, Mandiri, QRIS, GoPay, Kartu Kredit) atau pilih opsi <strong>Bayar Nanti</strong>.
                        </p>
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 leading-relaxed space-y-2">
                            <div class="flex items-center gap-2 font-bold text-amber-800">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Sistem Penguncian Stok (Reserved Stock) & Batas Waktu 2 Jam
                            </div>
                            <p>
                                Saat Anda checkout dengan status <strong>"Menunggu Pembayaran"</strong>, sistem kami akan langsung <strong>mengunci (reserved) stok barang</strong> Anda agar tidak terbeli oleh pelanggan lain atau transaksi kasir toko.
                            </p>
                            <p class="font-semibold text-amber-950">
                                Anda memiliki waktu maksimal <u>2 JAM</u> untuk menyelesaikan pembayaran. Jika pembayaran tidak dilakukan dalam 2 jam, sistem akan secara otomatis membatalkan pesanan dan mengembalikan stok barang ke inventori publik.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STEP 5 -->
                <div class="flex gap-4 md:gap-6 items-start">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-emerald-600 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20">
                        5
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base md:text-lg font-bold text-[#1e293b] mb-1.5">Pelacakan Pesanan & Konfirmasi Diterima</h3>
                        <p class="text-xs md:text-sm text-slate-600 leading-relaxed mb-2">
                            Anda dapat memantau status pesanan kapan saja melalui menu <strong>Profil Akun & Riwayat Pesanan</strong>:
                        </p>
                        <ul class="text-xs text-slate-600 space-y-1 list-disc list-inside">
                            <li><strong class="text-amber-700">Menunggu Pembayaran:</strong> Menunggu transfer Anda (tersedia tombol Bayar & waktu hitung mundur).</li>
                            <li><strong class="text-blue-700">Diproses:</strong> Pembayaran berhasil diverifikasi, staf kami memverifikasi batch obat & serial number alkes.</li>
                            <li><strong class="text-indigo-700">Dikirim:</strong> Paket diserahkan ke kurir ekspedisi.</li>
                            <li><strong class="text-teal-700">Siap Diambil:</strong> Pesanan Pick-up sudah siap di toko fisik untuk Anda bawa pulang.</li>
                            <li><strong class="text-emerald-700">Selesai:</strong> Barang telah diterima oleh pelanggan dengan baik.</li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- CTA Button -->
            <div class="mt-10 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 text-center sm:text-left">
                    Butuh bantuan atau ingin berkonsultasi mengenai spesifikasi alat kesehatan?
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <a href="kategori.php" class="flex-1 sm:flex-initial text-center px-5 py-2.5 bg-[#1a75d2] hover:bg-[#0f2d5a] text-white font-bold text-xs rounded-xl transition shadow-sm">
                        Mulai Belanja
                    </a>
                    <a href="https://wa.me/6281234567890?text=Halo%20ZenCare%20Medical,%20saya%20ingin%20bertanya%20mengenai%20cara%20pembelian" target="_blank" class="flex-1 sm:flex-initial text-center px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                        Tanya CS via WA
                    </a>
                </div>
            </div>

        </div>

    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
