<?php
// File: ecommerce/footer.php
?>
    <!-- ============================================================ -->
    <!-- FOOTER (Pharmify-Style 4-Column + Newsletter) -->
    <!-- ============================================================ -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <!-- Newsletter Strip -->
        <div class="bg-gradient-to-r from-[#0f2d5a] to-[#1a75d2] text-white">
            <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-extrabold mb-1">Berlangganan Newsletter ZenCare</h4>
                            <p class="text-blue-100 text-sm">Dapatkan info promo alkes dan artikel kesehatan terbaru.</p>
                        </div>
                    </div>
                    <form class="flex w-full md:w-auto gap-2">
                        <input type="email" placeholder="Alamat email Anda..." class="w-full md:w-72 bg-white/10 border border-white/20 text-white placeholder-blue-200 px-4 py-3 rounded-xl focus:outline-none focus:bg-white/20 transition">
                        <button type="button" class="bg-white text-[#1a75d2] font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition shadow-sm">Daftar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-[#1a75d2] flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-[#0f2d5a] leading-none">ZenCare</h2>
                            <span class="text-[10px] font-bold text-[#1a75d2] uppercase tracking-wider">Medical Store</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Distributor resmi alat kesehatan dan obat-obatan terpercaya di Indonesia. Berkomitmen memberikan layanan kesehatan terbaik untuk Anda.
                    </p>
                    <div class="flex gap-3 pt-2">
                        <a href="#" class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-[#1a75d2] hover:border-[#1a75d2] transition"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                        <a href="#" class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-[#1a75d2] hover:border-[#1a75d2] transition"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                        <a href="#" class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-[#1a75d2] hover:border-[#1a75d2] transition"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.597 0 0 .597 0 1.325v21.351C0 23.403.597 24 1.325 24h11.495v-9.294H9.691V11.41h3.129V8.797c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.296h-3.12V24h6.116c.73 0 1.323-.597 1.323-1.325v-21.35C24 .597 23.403 0 22.675 0z"/></svg></a>
                    </div>
                </div>

                <!-- Links 1 -->
                <div>
                    <h4 class="text-sm font-black text-[#1e293b] uppercase tracking-wider mb-5">Kategori Produk</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Alat Kesehatan Medis</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Obat Resep & Bebas</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Vitamin & Suplemen</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Perawatan Luka</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Perlengkapan Bayi</a></li>
                    </ul>
                </div>

                <!-- Links 2 -->
                <div>
                    <h4 class="text-sm font-black text-[#1e293b] uppercase tracking-wider mb-5">Layanan & Jaminan</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Cara Pembelian</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Pengiriman & Logistik</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Kebijakan Retur & Garansi</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="text-sm text-slate-500 hover:text-[#1a75d2] transition">Kebijakan Privasi</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-sm font-black text-[#1e293b] uppercase tracking-wider mb-5">Hubungi Kami</h4>
                    <ul class="space-y-4">
                        <li class="flex gap-3 items-start">
                            <svg class="w-5 h-5 text-[#1a75d2] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-sm text-slate-500 leading-relaxed">
                                <strong class="text-slate-700 block mb-1">ZenCare Pusat Muharto</strong>
                                Jl. Muharto No.1, Malang, Jawa Timur
                            </span>
                        </li>
                        <li class="flex gap-3 items-start">
                            <svg class="w-5 h-5 text-[#1a75d2] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-sm text-slate-500">+62 812-3456-7890</span>
                        </li>
                        <li class="flex gap-3 items-start">
                            <svg class="w-5 h-5 text-[#1a75d2] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="text-sm text-slate-500">cs@zencare.id</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-12 pt-8 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-400">&copy; <?= date('Y') ?> ZenCare Medical. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 mr-2">Pembayaran Terverifikasi:</span>
                    <div class="h-8 bg-slate-100 rounded px-3 flex items-center text-xs font-bold text-slate-500">MIDTRANS</div>
                    <div class="h-8 bg-slate-100 rounded px-3 flex items-center text-xs font-bold text-slate-500">BCA</div>
                    <div class="h-8 bg-slate-100 rounded px-3 flex items-center text-xs font-bold text-slate-500">MANDIRI</div>
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================================ -->
    <!-- CART DRAWER & NOTIFICATIONS -->
    <!-- ============================================================ -->

    <!-- Overlay -->
    <div id="cart-overlay" class="fixed inset-0 bg-[#0f2d5a]/40 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300" onclick="toggleCart()"></div>

    <!-- Cart Sidebar -->
    <div id="cart-drawer" class="fixed top-0 right-0 bottom-0 w-full sm:w-[420px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-white z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#1a75d2]/10 text-[#1a75d2] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-[#1e293b] leading-none">Keranjang Belanja</h2>
                    <p class="text-[11px] text-slate-500 mt-1"><span id="cart-drawer-count">0</span> barang terpilih</p>
                </div>
            </div>
            <button onclick="toggleCart()" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-full transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-5 bg-slate-50">
            <div id="cart-items" class="space-y-3">
                <!-- Items will be injected here via JS -->
            </div>
            
            <!-- Empty State -->
            <div id="cart-empty" class="hidden h-full flex flex-col items-center justify-center text-center py-10">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100 mb-4 text-slate-300">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="text-base font-bold text-[#1e293b] mb-1">Keranjang masih kosong</h3>
                <p class="text-sm text-slate-500">Yuk, isi dengan kebutuhan medis Anda.</p>
                <button onclick="toggleCart()" class="mt-6 px-6 py-2.5 rounded-xl border-2 border-slate-200 text-slate-600 font-bold hover:border-[#1a75d2] hover:text-[#1a75d2] transition">Mulai Belanja</button>
            </div>
        </div>

        <!-- Footer / Checkout -->
        <div class="p-5 border-t border-slate-100 bg-white">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-bold text-slate-500">Total Belanja</span>
                <span class="text-2xl font-black text-[#1e293b]" id="cart-total">Rp 0</span>
            </div>
            <a href="../zencare_checkout.php" class="block w-full py-4 text-center rounded-xl bg-[#1a75d2] hover:bg-[#0f2d5a] text-white font-bold text-base shadow-lg shadow-blue-500/30 transition active:scale-[0.98]">
                Lanjut ke Pembayaran
            </a>
            <p class="text-[10px] text-center text-slate-400 mt-3 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Pembayaran aman terenkripsi & stok terjamin
            </p>
        </div>
    </div>

    <!-- Toast Notification System -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[60] flex flex-col gap-3"></div>

    <script>
    // --- Cart State Management (localStorage) ---
    let cartData = JSON.parse(localStorage.getItem('zc_cart') || '[]');

    function saveCart() {
        localStorage.setItem('zc_cart', JSON.stringify(cartData));
        renderCart();
    }

    function toggleCart() {
        const overlay = document.getElementById('cart-overlay');
        const drawer = document.getElementById('cart-drawer');
        const isHidden = drawer.classList.contains('translate-x-full');

        if (isHidden) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            drawer.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden'; // prevent bg scroll
            renderCart();
        } else {
            overlay.classList.add('opacity-0');
            drawer.classList.add('translate-x-full');
            document.body.style.overflow = '';
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
    }

    // Modern Toast Notification
    function showToast(title, message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        
        const icon = type === 'success' 
            ? '<svg class="w-5 h-5 text-emerald-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            : '<svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
            
        toast.className = `transform transition-all duration-300 translate-y-8 opacity-0 bg-white border border-slate-100 shadow-xl rounded-xl p-4 w-80 flex gap-3`;
        toast.innerHTML = `
            ${icon}
            <div>
                <h4 class="text-sm font-bold text-[#1e293b]">${title}</h4>
                <p class="text-xs text-slate-500 mt-0.5">${message}</p>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-8', 'opacity-0');
        });
        
        // Remove after 3s
        setTimeout(() => {
            toast.classList.add('opacity-0', 'scale-95');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function addToCart(id, nama, harga, berat, gambar, satB, rasio, maxStokBox, qtyInput = 1) {
        let qty = parseInt(qtyInput) || 1;
        let itemIndex = cartData.findIndex(item => item.id == id);

        if (itemIndex > -1) {
            let newQty = cartData[itemIndex].qty + qty;
            if (newQty > maxStokBox) {
                showToast('Stok Tidak Cukup', `Maksimal pembelian untuk item ini adalah ${maxStokBox} ${satB}.`, 'warning');
                cartData[itemIndex].qty = maxStokBox;
            } else {
                cartData[itemIndex].qty = newQty;
                showToast('Berhasil', `${nama} ditambahkan ke keranjang.`);
            }
        } else {
            if (qty > maxStokBox) {
                showToast('Stok Tidak Cukup', `Maksimal pembelian untuk item ini adalah ${maxStokBox} ${satB}.`, 'warning');
                qty = maxStokBox;
            } else {
                showToast('Berhasil', `${nama} ditambahkan ke keranjang.`);
            }
            cartData.push({ id, nama, harga, berat, gambar, satuan: satB, rasio, max: maxStokBox, qty });
        }
        
        saveCart();
    }
    
    window.buyNow = function(id, nama, harga, berat, gambar, satB, rasio, maxStokBox) {
        let qtyInput = 1;
        let qtyEl = document.getElementById('qty_' + id);
        if (qtyEl) {
            qtyInput = parseInt(qtyEl.value) || 1;
        }
        addToCart(id, nama, harga, berat, gambar, satB, rasio, maxStokBox, qtyInput);
        window.location.href = '../zencare_checkout.php';
    };

    function removeCartItem(id) {
        cartData = cartData.filter(item => item.id != id);
        saveCart();
    }

    function updateCartItemQty(id, delta) {
        let itemIndex = cartData.findIndex(item => item.id == id);
        if (itemIndex > -1) {
            let item = cartData[itemIndex];
            let newQty = item.qty + delta;
            
            if (newQty <= 0) {
                removeCartItem(id);
            } else if (newQty > item.max) {
                showToast('Stok Maksimal', `Stok tersedia hanya ${item.max} ${item.satuan}.`, 'warning');
            } else {
                item.qty = newQty;
                saveCart();
            }
        }
    }

    function adjustQty(id, delta, max) {
        let input = document.getElementById('qty_' + id);
        if(!input) return;
        let v = parseInt(input.value) + delta;
        if (v < 1) v = 1;
        if (v > max) v = max;
        input.value = v;
    }

    function renderCart() {
        const badge1 = document.getElementById('cart-count-badge');
        const badge2 = document.getElementById('cart-drawer-count');
        const itemsContainer = document.getElementById('cart-items');
        const emptyState = document.getElementById('cart-empty');
        const totalLabel = document.getElementById('cart-total');

        // Update counts
        const totalItems = cartData.reduce((sum, item) => sum + item.qty, 0);
        badge1.textContent = totalItems;
        if(totalItems > 0) {
            badge1.classList.remove('hidden');
        } else {
            badge1.classList.add('hidden');
        }
        if(badge2) badge2.textContent = totalItems;

        // Toggle Empty State
        if (cartData.length === 0) {
            itemsContainer.innerHTML = '';
            itemsContainer.classList.add('hidden');
            emptyState.classList.remove('hidden');
            totalLabel.textContent = 'Rp 0';
            return;
        }

        itemsContainer.classList.remove('hidden');
        emptyState.classList.add('hidden');

        // Render Items
        let html = '';
        let totalHarga = 0;

        cartData.forEach(item => {
            const subtotal = item.harga * item.qty;
            totalHarga += subtotal;
            
            const imgPath = item.gambar ? `../${item.gambar}` : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjY2JkNWUxIiBzdHJva2Utd2lkdGg9IjEiPjxwYXRoIGQ9Ik0xMiAyMnM4LTQgOC0xMFY1bC04LTMtOCAzdjdjMCA2IDggMTAgOCAxMHoiLz48L3N2Zz4=';

            html += `
            <div class="bg-white rounded-2xl p-3 border border-slate-100 flex gap-4 shadow-sm animate-fade-in">
                <div class="w-20 h-20 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center shrink-0 p-2 overflow-hidden">
                    <img src="${imgPath}" class="w-full h-full object-contain mix-blend-multiply" alt="${item.nama}">
                </div>
                <div class="flex-1 flex flex-col justify-between">
                    <div class="flex justify-between items-start gap-2">
                        <h4 class="text-xs font-bold text-[#1e293b] leading-tight line-clamp-2">${item.nama}</h4>
                        <button onclick="removeCartItem(${item.id})" class="text-slate-300 hover:text-rose-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    <div class="flex items-end justify-between mt-2">
                        <div class="text-[#1a75d2] font-black text-sm">${formatRupiah(item.harga)}</div>
                        
                        <!-- Qty Adjuster -->
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg overflow-hidden h-8">
                            <button onclick="updateCartItemQty(${item.id}, -1)" class="w-8 h-full flex items-center justify-center hover:bg-slate-200 text-slate-600 font-bold transition">−</button>
                            <span class="w-8 text-center text-xs font-bold text-[#1e293b]">${item.qty}</span>
                            <button onclick="updateCartItemQty(${item.id}, 1)" class="w-8 h-full flex items-center justify-center hover:bg-slate-200 text-slate-600 font-bold transition">+</button>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        itemsContainer.innerHTML = html;
        totalLabel.textContent = formatRupiah(totalHarga);
    }

    // Initialize cart badge on load
    document.addEventListener('DOMContentLoaded', renderCart);
    </script>
</body>
</html>
