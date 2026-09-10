<?php
// File: ecommerce/tentang_kami.php
// Tentang Kami - ZenCare Medical Store
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Tentang Kami";
require_once __DIR__ . '/header.php';
?>

<main class="bg-[#f5f7fa] flex-1">
    
    <!-- Hero Banner -->
    <div class="relative bg-gradient-to-br from-[#0f2d5a] to-[#1a75d2] text-white py-20 lg:py-28 overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAzNHYtNGgtMnY0aC00djJoNHY0aDJ2LTRoNHYtMmgtNHptMC0zMFYwaC0ydjRoLTR2Mmg0djRoMnYtNGg0VjRoLTR6bS0xOCAyNHYtNGgtMnY0SDEydjJoNHY0aDJ2LTRoNHYtMmgtNHoiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvZz48L3N2Zz4=')] opacity-30"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-black mb-4">Tentang ZenCare Medical</h1>
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto">Solusi penyediaan alat kesehatan dan obat-obatan yang terpercaya, cepat, dan terintegrasi untuk kebutuhan klinik maupun individu.</p>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 py-16 -mt-10 relative z-20">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-8 md:p-12 border border-slate-100">
            
            <div class="prose prose-slate prose-blue max-w-none">
                <h2 class="text-2xl font-bold text-[#1e293b] mb-4">Visi Kami</h2>
                <p class="text-slate-600 mb-8 leading-relaxed">
                    Menjadi apotek omnichannel terdepan di Indonesia yang memberikan kemudahan akses alat kesehatan dan farmasi dengan jaminan keaslian, harga kompetitif, dan kecepatan pengiriman. Kami memadukan pengalaman belanja online yang mulus dengan ketersediaan stok fisik yang real-time.
                </p>

                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-center">
                        <div class="w-12 h-12 bg-[#1a75d2] text-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="font-bold text-[#0f2d5a] mb-2">Terjamin Asli</h3>
                        <p class="text-sm text-slate-500">Semua produk memiliki izin edar Kemenkes RI dan bersumber langsung dari distributor resmi.</p>
                    </div>
                    <div class="bg-emerald-50 p-6 rounded-2xl border border-emerald-100 text-center">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-[#0f2d5a] mb-2">Stok Real-time</h3>
                        <p class="text-sm text-slate-500">Sistem inventori kami tersinkronisasi otomatis dengan toko fisik untuk memastikan ketersediaan.</p>
                    </div>
                    <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100 text-center">
                        <div class="w-12 h-12 bg-amber-600 text-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="font-bold text-[#0f2d5a] mb-2">Pengiriman Cepat</h3>
                        <p class="text-sm text-slate-500">Opsi kurir instan dan reguler untuk memastikan alat kesehatan Anda tiba saat dibutuhkan.</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-[#1e293b] mb-4">Layanan Konsultasi Apoteker</h2>
                <p class="text-slate-600 mb-6 leading-relaxed">
                    Kami memahami bahwa memilih alat kesehatan atau obat memerlukan panduan tenaga ahli. Oleh karena itu, ZenCare menyediakan layanan Konsultasi Apoteker via WhatsApp yang siap menjawab pertanyaan Anda mengenai indikasi, dosis, dan cara penggunaan produk.
                </p>
                <a href="https://wa.me/6281234567890?text=Halo%20Apoteker%20ZenCare,%20saya%20ingin%20konsultasi%20mengenai%20produk%20kesehatan" target="_blank" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a5.225 5.225 0 00-.571-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 21a8.96 8.96 0 01-4.588-1.257l-.329-.195-3.411.895.912-3.326-.214-.341A8.961 8.961 0 013 12c0-4.97 4.03-9 9-9s9 4.03 9-9 9-4.03 9-9 9zm0-10.706C8.869 1.294 6.327.031 3.515.031.703.031-1.839 1.294-1.839 4.106c0 2.812 2.542 4.075 5.354 4.075s5.354-1.263 5.354-4.075zM12 2C6.477 2 2 6.477 2 12c0 1.755.454 3.412 1.254 4.856L2 22l5.293-1.189A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
                    Mulai Konsultasi
                </a>

            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/footer.php'; ?>
