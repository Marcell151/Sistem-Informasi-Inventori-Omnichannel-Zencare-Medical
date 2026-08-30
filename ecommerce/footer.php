<?php
// File: ecommerce/footer.php
// Shared Footer & Core JavaScript for ZenCare Medical E-Commerce
?>
    <!-- Shared Footer -->
    <footer class="bg-slate-900 text-slate-400 mt-auto border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Col 1: Brand Info -->
                <div class="md:col-span-1">
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-zc flex items-center justify-center text-white font-bold">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </div>
                        <span class="text-white font-bold text-base tracking-tight"><?= htmlspecialchars($namaToko ?? 'ZenCare Medical') ?></span>
                    </div>
                    <p class="text-slate-400 leading-relaxed mb-4 text-sm">
                        Toko resmi alat kesehatan dan obat-obatan berkualitas. Melayani pembelian kemasan grosir dengan pengiriman ke seluruh Indonesia.
                    </p>
                    <div class="flex items-center gap-2 text-slate-300">
                        <svg class="w-4 h-4 text-white/70 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span class="text-sm font-medium">Izin Resmi Kemenkes RI</span>
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">E-Commerce</h4>
                    <ul class="space-y-2 text-[11px]">
                        <li><a href="index.php" class="hover:text-white transition">Katalog Beranda</a></li>
                        <li><a href="kategori.php" class="hover:text-white transition">Daftar Semua Kategori</a></li>
                        <li><a href="../zencare_checkout.php" class="hover:text-white transition">Pemeriksaan Keranjang (Checkout)</a></li>
                        <li><a href="profil.php" class="hover:text-white transition">Status &amp; Riwayat Pesanan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-3">Layanan &amp; Jaminan</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 13l4 4L19 7"/></svg>
                            100% Produk Original &amp; Tersegel
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 13l4 4L19 7"/></svg>
                            Stok Tersedia di Semua Cabang
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 13l4 4L19 7"/></svg>
                            Pembayaran Aman &amp; Terenkripsi
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 13l4 4L19 7"/></svg>
                            Ongkos Kirim Transparan
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-3">Lokasi Cabang</h4>
                    <p class="text-slate-300 font-semibold mb-1 text-sm">
                        <?= htmlspecialchars($cabangAktif['nama'] ?? 'Cabang Utama') ?>
                    </p>
                    <p class="text-slate-400 leading-relaxed text-sm mb-3">
                        <?= htmlspecialchars($cabangAktif['alamat'] ?? 'Jawa Timur, Indonesia') ?>
                    </p>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Layanan Tersedia
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($namaToko ?? 'ZenCare Medical') ?>. Toko Online Alat Kesehatan &amp; Obat.</p>
                <div class="flex items-center gap-4 text-slate-500">
                    <a href="../login.php" class="hover:text-slate-300 transition">Portal Staf</a>
                    <span>&bull;</span>
                    <a href="profil.php" class="hover:text-slate-300 transition">Akun Saya</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 pointer-events-none transition-all duration-300 ease-out">
        <div class="bg-white text-zcTxt text-sm font-semibold px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-200 flex items-center gap-3 max-w-xs">
            <div class="w-7 h-7 rounded-full border-2 border-emerald-500 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 13l4 4L19 7"/></svg>
            </div>
            <span id="toast-msg" class="leading-snug">Berhasil ditambahkan ke keranjang!</span>
        </div>
    </div>

    <!-- Core E-Commerce Cart & Interaction Script -->
    <script>
    // ─── LocalStorage Cart Helpers ───
    function getCart() {
        return JSON.parse(localStorage.getItem('zencare_cart') || '[]');
    }

    function saveCart(cart) {
        localStorage.setItem('zencare_cart', JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        const cart = getCart();
        const total = cart.reduce((s, i) => s + (parseInt(i.qty) || 1), 0);
        const badge = document.getElementById('cart-badge');
        if (badge) {
            const old = parseInt(badge.innerText) || 0;
            badge.innerText = total;
            if (total !== old) {
                badge.classList.add('pop');
                setTimeout(() => badge.classList.remove('pop'), 300);
            }
        }
    }

    function showToast(msg) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        if (!toast) return;
        toastMsg.innerText = msg;
        toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
        }, 2800);
    }

    // Add item to cart with stock validation
    function addToCart(id, name, price, weight, img, satuan_besar, rasio, maxStokBox, qtyToAdd = 1) {
        let cart = getCart();
        let item = cart.find(i => (i.cartId || i.id) == id);
        qtyToAdd = parseInt(qtyToAdd) || 1;

        const curQty = item ? parseInt(item.qty) : 0;
        if (curQty + qtyToAdd > maxStokBox) {
            alert('Stok tidak mencukupi! Tersedia hanya ' + maxStokBox + ' ' + (satuan_besar || 'Box') + '. Di keranjang Anda sudah ada: ' + curQty + ' ' + (satuan_besar || 'Box'));
            return false;
        }

        if (item) {
            item.qty = curQty + qtyToAdd;
            item.maxStokBox = maxStokBox;
            item.satuan_besar = satuan_besar;
            item.satuan_label = satuan_besar;
            item.rasio_konversi = rasio;
        } else {
            cart.push({
                id: id,
                cartId: id,
                name: name,
                price: parseFloat(price),
                weight: parseInt(weight) || 100,
                img: img,
                satuan_besar: satuan_besar,
                satuan_label: satuan_besar,
                rasio: parseInt(rasio) || 1,
                maxStokBox: maxStokBox,
                qty: qtyToAdd
            });
        }

        saveCart(cart);
        showToast(qtyToAdd + ' ' + (satuan_besar || 'Box') + ' "' + name + '" masuk keranjang!');
        return true;
    }

    // Buy Now: add to cart and immediately redirect to checkout
    function buyNow(id, name, price, weight, img, satuan_besar, rasio, maxStokBox, qtyToAdd = 1) {
        const added = addToCart(id, name, price, weight, img, satuan_besar, rasio, maxStokBox, qtyToAdd);
        if (added) {
            window.location.href = '../zencare_checkout.php';
        }
    }

    // Quantity adjuster for stepper inputs
    function adjustQty(id, delta, maxStok) {
        const input = document.getElementById('qty_' + id);
        if (!input) return;
        let cur = parseInt(input.value) || 1;
        cur = Math.max(1, Math.min(maxStok, cur + delta));
        input.value = cur;
    }

    // Initialize badge on page load
    document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>
