<?php
// File: ecommerce/footer.php — Shared Footer (Redesign v3, Pharmify-inspired)
?>
    <!-- ============================================================ -->
    <!-- NEWSLETTER & SUPPORT STRIP                                    -->
    <!-- ============================================================ -->
    <div class="bg-gradient-to-r from-zcDark via-[#0d3670] to-[#1a75d2] text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-blue-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold mb-0.5">Butuh Bantuan atau Konsultasi?</h3>
                    <p class="text-xs text-blue-200">Hubungi Apoteker & CS kami (08:00 - 21:00 WIB)</p>
                </div>
            </div>
            <a href="https://wa.me/6281234567890" target="_blank" class="shrink-0 inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                Chat WhatsApp Sekarang
            </a>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- FOOTER CONTENT                                                -->
    <!-- ============================================================ -->
    <footer class="bg-white pt-16 pb-8 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            <!-- Col 1: Brand -->
            <div>
                <a href="index.php" class="flex items-center gap-2.5 mb-6">
                    <div class="w-8 h-8 rounded-xl bg-zc flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <span class="text-xl font-extrabold text-[#1e293b] tracking-tight">ZenCare Medical</span>
                </a>
                <p class="text-sm text-[#64748b] leading-relaxed mb-6">
                    Penyedia resmi peralatan medis, alat kesehatan, dan obat-obatan terpercaya di Indonesia. Garansi Kemenkes RI.
                </p>
                <div class="flex items-center gap-3">
                    <a href="#" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-zc hover:text-white transition"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                    <a href="#" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-zc hover:text-white transition"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                </div>
            </div>

            <!-- Col 2: Kategori -->
            <div>
                <h4 class="text-sm font-extrabold text-[#1e293b] mb-4">Kategori Medis</h4>
                <ul class="space-y-2.5">
                    <li><a href="kategori.php?kategori=Obat" class="text-sm text-[#64748b] hover:text-zc transition">Obat-obatan Resep</a></li>
                    <li><a href="kategori.php?kategori=Alat+Kesehatan" class="text-sm text-[#64748b] hover:text-zc transition">Alat Kesehatan Pribadi</a></li>
                    <li><a href="kategori.php?kategori=Vitamin" class="text-sm text-[#64748b] hover:text-zc transition">Vitamin & Suplemen</a></li>
                    <li><a href="kategori.php?kategori=Perawatan" class="text-sm text-[#64748b] hover:text-zc transition">Perawatan Luka</a></li>
                    <li><a href="kategori.php" class="text-sm text-[#64748b] hover:text-zc transition">Lihat Semua Katalog</a></li>
                </ul>
            </div>

            <!-- Col 3: Layanan -->
            <div>
                <h4 class="text-sm font-extrabold text-[#1e293b] mb-4">Layanan Pelanggan</h4>
                <ul class="space-y-2.5">
                    <li><a href="#" class="text-sm text-[#64748b] hover:text-zc transition">Pusat Bantuan (FAQ)</a></li>
                    <li><a href="#" class="text-sm text-[#64748b] hover:text-zc transition">Kebijakan Pengiriman</a></li>
                    <li><a href="#" class="text-sm text-[#64748b] hover:text-zc transition">Kebijakan Pengembalian</a></li>
                    <li><a href="#" class="text-sm text-[#64748b] hover:text-zc transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="text-sm text-[#64748b] hover:text-zc transition">Kebijakan Privasi</a></li>
                </ul>
            </div>

            <!-- Col 4: Cabang -->
            <div>
                <h4 class="text-sm font-extrabold text-[#1e293b] mb-4">Lokasi Pengiriman</h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-zc mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <div>
                            <span class="block text-sm font-bold text-[#1e293b]">Pusat Muharto</span>
                            <span class="block text-xs text-[#64748b] mt-0.5">Jl. Muharto No.1, Malang</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-zc mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <div>
                            <span class="block text-sm font-bold text-[#1e293b]">Cabang Sawojajar</span>
                            <span class="block text-xs text-[#64748b] mt-0.5">Jl. Danau Toba No.10, Malang</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 lg:px-8 border-t border-slate-100 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-[#64748b]">&copy; 2026 ZenCare Medical. Sistem E-Commerce Omnichannel.</p>
            <div class="flex items-center gap-4">
                <img src="https://midtrans.com/assets/img/midtrans-logo.svg" alt="Midtrans" class="h-5 opacity-60 grayscale hover:grayscale-0 transition">
                <svg class="h-6 opacity-60 grayscale hover:grayscale-0 transition" viewBox="0 0 100 30"><text x="0" y="20" font-family="Arial" font-weight="bold" font-size="20" fill="#E20613">Raja</text><text x="45" y="20" font-family="Arial" font-weight="bold" font-size="20" fill="#F47B20">Ongkir</text></svg>
            </div>
        </div>
    </footer>


    <!-- ============================================================ -->
    <!-- RIGHT SIDE CART DRAWER (Slide Panel)                         -->
    <!-- ============================================================ -->
    <div id="cartDrawer" class="fixed inset-y-0 right-0 w-full max-w-sm bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-[100] flex flex-col border-l border-slate-100">
        <!-- Drawer Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-zc flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#1e293b]">Keranjang Belanja</h3>
                    <p class="text-[10px] text-slate-500 font-medium">Pengiriman dari Cabang Aktif</p>
                </div>
            </div>
            <button onclick="toggleCart()" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-rose-500 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Cart Items Container -->
        <div id="cartItemsContainer" class="flex-1 overflow-y-auto p-5 bg-slate-50/50 space-y-3">
            <!-- Items injected by JS -->
        </div>

        <!-- Drawer Footer -->
        <div class="p-5 bg-white border-t border-slate-100 shadow-[0_-4px_10px_-4px_rgba(0,0,0,0.05)]">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold text-[#64748b]">Total Belanja:</span>
                <span id="cartSubtotal" class="text-xl font-extrabold text-[#1e293b]">Rp 0</span>
            </div>
            <a href="zencare_checkout.php" id="btnCheckout" class="flex items-center justify-center w-full py-3.5 bg-zc text-white font-bold rounded-xl hover:bg-zcHv hover:shadow-lg transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                Lanjut ke Pembayaran &rarr;
            </a>
            <div class="mt-4 flex items-center justify-center gap-2 text-[10px] text-slate-400 font-medium">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Pembayaran Aman SSL Terenkripsi
            </div>
        </div>
    </div>

    <!-- Backdrop for Drawer -->
    <div id="cartBackdrop" onclick="toggleCart()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[90] hidden opacity-0 transition-opacity duration-300"></div>

    <!-- Toast Notification (Absolute positioned) -->
    <div id="toast" class="fixed top-24 right-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform translate-x-[120%] transition-transform duration-300 z-[110] max-w-sm">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
        <span id="toastMsg" class="text-sm font-bold">Ditambahkan ke keranjang!</span>
    </div>

    <script>
    // ── CART LOGIC (localStorage) ──
    let cart = JSON.parse(localStorage.getItem('zencare_cart')) || [];

    function updateCartUI() {
        const container = document.getElementById('cartItemsContainer');
        const badge     = document.getElementById('cartCountBadge');
        const subtotal  = document.getElementById('cartSubtotal');
        const btnCO     = document.getElementById('btnCheckout');
        
        let html = '';
        let total = 0;
        let count = 0;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full text-center text-slate-400 space-y-3">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center"><svg class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg></div>
                    <div><p class="text-sm font-bold text-slate-500">Keranjang masih kosong</p><p class="text-xs mt-1">Yuk, mulai belanja kebutuhan medismu!</p></div>
                </div>
            `;
            badge.classList.add('scale-0');
            subtotal.innerText = 'Rp 0';
            btnCO.classList.add('opacity-50', 'pointer-events-none');
            return;
        }

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            count += item.qty;
            
            const imgPath = item.image ? \`../\${item.image}\` : '';
            const imgSrc  = item.image ? \`<img src="\${imgPath}" class="w-full h-full object-contain p-1">\` : \`<svg class="w-6 h-6 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>\`;

            html += \`
            <div class="bg-white border border-slate-100 rounded-2xl p-3 flex gap-3 shadow-sm relative group">
                <button onclick="removeFromCart(\${index})" class="absolute -top-2 -right-2 w-6 h-6 bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition z-10">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
                <div class="w-16 h-16 rounded-xl bg-slate-50 flex items-center justify-center shrink-0 border border-slate-100 overflow-hidden">
                    \${imgSrc}
                </div>
                <div class="flex-1 min-w-0 flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-[#1e293b] line-clamp-2 leading-tight pr-4">\${item.name}</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5">\${item.satuan} &middot; Rp \${item.price.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg">
                            <button onclick="updateCartQty(\${index}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:bg-slate-200 rounded-l-lg transition">−</button>
                            <span class="text-xs font-bold w-6 text-center text-slate-700">\${item.qty}</span>
                            <button onclick="updateCartQty(\${index}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:bg-slate-200 rounded-r-lg transition">+</button>
                        </div>
                        <span class="text-sm font-extrabold text-[#1a75d2]">Rp \${itemTotal.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            </div>\`;
        });

        container.innerHTML = html;
        badge.innerText = count;
        badge.classList.remove('scale-0');
        subtotal.innerText = \`Rp \${total.toLocaleString('id-ID')}\`;
        btnCO.classList.remove('opacity-50', 'pointer-events-none');
    }

    function addToCart(id, name, price, weight, image, satuan, rasio, maxStokBox, addQty = 1) {
        addQty = parseInt(addQty);
        let existing = cart.find(c => c.id == id);
        
        if (existing) {
            if (existing.qty + addQty > maxStokBox) {
                showToast(\`Stok maksimal: \${maxStokBox} \${satuan}\`, true);
                return;
            }
            existing.qty += addQty;
        } else {
            if (addQty > maxStokBox) addQty = maxStokBox;
            cart.push({ id, name, price, weight, image, satuan, rasio, maxStokBox, qty: addQty });
        }
        
        saveCart();
        showToast('Berhasil ditambahkan ke keranjang!');
        
        // Open drawer automatically on desktop
        if(window.innerWidth >= 1024) {
            const drawer = document.getElementById('cartDrawer');
            if(drawer.classList.contains('translate-x-full')) toggleCart();
        }
    }

    function updateCartQty(index, delta) {
        let item = cart[index];
        let newQty = item.qty + delta;
        
        if (newQty <= 0) {
            cart.splice(index, 1);
        } else if (newQty > item.maxStokBox) {
            showToast(\`Stok hanya tersisa \${item.maxStokBox}\`, true);
        } else {
            item.qty = newQty;
        }
        saveCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        saveCart();
    }

    function saveCart() {
        localStorage.setItem('zencare_cart', JSON.stringify(cart));
        updateCartUI();
    }

    // ── UI HELPERS ──
    function toggleCart() {
        const drawer = document.getElementById('cartDrawer');
        const back   = document.getElementById('cartBackdrop');
        if (drawer.classList.contains('translate-x-full')) {
            drawer.classList.remove('translate-x-full');
            back.classList.remove('hidden');
            setTimeout(() => back.classList.remove('opacity-0'), 10);
        } else {
            drawer.classList.add('translate-x-full');
            back.classList.add('opacity-0');
            setTimeout(() => back.classList.add('hidden'), 300);
        }
    }

    let toastTimer;
    function showToast(msg, isErr=false) {
        const t = document.getElementById('toast');
        const tm = document.getElementById('toastMsg');
        tm.innerText = msg;
        
        if(isErr) {
            t.className = "fixed top-24 right-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform transition-transform duration-300 z-[110] max-w-sm";
            t.querySelector('svg').outerHTML = '<svg class="w-5 h-5 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        } else {
            t.className = "fixed top-24 right-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform transition-transform duration-300 z-[110] max-w-sm";
            t.querySelector('svg').outerHTML = '<svg class="w-5 h-5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>';
        }

        t.classList.remove('translate-x-[120%]');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.classList.add('translate-x-[120%]'), 3500);
    }

    // Header scroll effect
    window.addEventListener('scroll', () => {
        if(window.scrollY > 20) {
            document.getElementById('mainHeader').classList.add('shadow-md', 'bg-white/95');
        } else {
            document.getElementById('mainHeader').classList.remove('shadow-md', 'bg-white/95');
        }
    });

    // Qty control handler for product cards
    function adjustQty(id, delta, max) {
        let input = document.getElementById('qty_' + id);
        let val = parseInt(input.value) + delta;
        if(val >= 1 && val <= max) input.value = val;
    }

    function buyNow(id, name, price, weight, image, satuan, rasio, maxStokBox, qty) {
        addToCart(id, name, price, weight, image, satuan, rasio, maxStokBox, qty);
        window.location.href = 'zencare_checkout.php';
    }

    // Init
    updateCartUI();
    </script>
</body>
</html>
