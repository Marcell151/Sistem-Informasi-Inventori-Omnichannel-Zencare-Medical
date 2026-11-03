<?php
// File: ecommerce/footer.php — Pharmify Footer & Slide-Over Cart Drawer
?>
    <!-- ============================================================ -->
    <!-- FOOTER (Pharmify Editorial 4-Column Layout)                  -->
    <!-- ============================================================ -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                
                <!-- Col 1: Brand Info -->
                <div class="space-y-4">
                    <a href="index.php" class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-[#eef6fb] border border-[#cde3f5] flex items-center justify-center overflow-hidden p-1">
                            <img src="/inventory_zencare/gambar/Logo.png" alt="ZenCare Logo" class="w-full h-full object-contain rounded-full">
                        </div>
                        <div>
                            <h2 class="text-xl font-extrabold tracking-tight text-[#0f2d5a] leading-none">ZenCare</h2>
                            <span class="text-[10px] font-bold text-[#1a75d2] uppercase tracking-[0.18em]">Medical Store</span>
                        </div>
                    </a>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pusat distribusi resmi obat-obatan kemasan grosir dan alat kesehatan terpercaya. Berkomitmen menjaga keaslian produk dan kontrol kedaluwarsa berstandar klinis.
                    </p>
                </div>

                <!-- Col 2: Koleksi Kategori -->
                <div>
                    <h4 class="text-xs font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-4">Kategori Produk</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="kategori.php?kategori=Obat" class="text-slate-500 hover:text-[#0f2d5a] transition">Obat-obatan &amp; Farmasi</a></li>
                        <li><a href="kategori.php?kategori=Alat+Kesehatan" class="text-slate-500 hover:text-[#0f2d5a] transition">Alat Kesehatan Medis</a></li>
                        <li><a href="kategori.php?q=Vitamin" class="text-slate-500 hover:text-[#0f2d5a] transition">Vitamin &amp; Suplemen</a></li>
                        <li><a href="kategori.php?q=Sirup" class="text-slate-500 hover:text-[#0f2d5a] transition">Sirup &amp; Suspensi</a></li>
                        <li><a href="kategori.php" class="text-slate-500 hover:text-[#0f2d5a] transition">Semua Koleksi Produk</a></li>
                    </ul>
                </div>

                <!-- Col 3: Panduan & Layanan -->
                <div>
                    <h4 class="text-xs font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-4">Informasi &amp; Layanan</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="tentang_kami.php" class="text-slate-500 hover:text-[#0f2d5a] transition">Tentang ZenCare Medical</a></li>
                        <li><a href="cara_pembelian.php" class="text-slate-500 hover:text-[#0f2d5a] transition">Cara Pembelian &amp; Pembayaran</a></li>
                        <li><a href="cara_pembelian.php" class="text-slate-500 hover:text-[#0f2d5a] transition">Ketentuan Pengiriman &amp; Pick-Up</a></li>
                        <li><a href="profil.php" class="text-slate-500 hover:text-[#0f2d5a] transition">Lacak Status Pesanan</a></li>
                        <li><a href="profil.php" class="text-slate-500 hover:text-[#0f2d5a] transition">Pengaturan Akun Pelanggan</a></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Lokasi -->
                <div>
                    <h4 class="text-xs font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-4">Hubungi Kami</h4>
                    <ul class="space-y-3 text-xs text-slate-500">
                        <li class="flex gap-2.5 items-start">
                            <svg class="w-4 h-4 text-[#0f2d5a] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="leading-relaxed">
                                <strong class="text-[#0f2d5a] block mb-0.5">ZenCare Medical — Cabang Muharto</strong>
                                Jl. Muharto, Kota Malang, Jawa Timur
                            </span>
                        </li>
                        <li class="flex gap-2.5 items-center">
                            <svg class="w-4 h-4 text-[#0f2d5a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex gap-2.5 items-center">
                            <svg class="w-4 h-4 text-[#0f2d5a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>layanan@zencare.id</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-400">&copy; <?= date('Y') ?> ZenCare Medical. Seluruh Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- ============================================================ -->
    <!-- PHARMIFY SLIDE-OVER CART DRAWER (Reference 2)                -->
    <!-- ============================================================ -->

    <!-- Backdrop Overlay -->
    <div id="cart-overlay" class="fixed inset-0 bg-[#0b2545]/50 z-50 hidden opacity-0 transition-opacity duration-300" onclick="toggleCart()"></div>

    <!-- Right Slide-Over Drawer -->
    <div id="cart-drawer" class="fixed top-0 right-0 bottom-0 w-full sm:w-[400px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        
        <!-- Drawer Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-white z-10">
            <div>
                <h2 class="text-base font-bold text-[#0f2d5a]">Keranjang Belanja</h2>
                <p class="text-[11px] text-slate-400 mt-0.5"><span id="cart-drawer-count">0</span> produk di keranjang</p>
            </div>
            <button type="button" onclick="toggleCart()" class="p-1.5 text-slate-400 hover:text-[#0f2d5a] transition cursor-pointer" title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6 bg-white">
            <div id="cart-items" class="space-y-4">
                <!-- Items injected via JS -->
            </div>
            
            <!-- Pharmify Minimalist Empty State (Reference 2) -->
            <div id="cart-empty" class="hidden h-full flex flex-col items-center justify-center text-center py-12">
                <h3 class="text-lg font-extrabold text-[#0f2d5a] mb-2">Keranjang belanja Anda kosong</h3>
                <p class="text-xs text-slate-500 mb-6 max-w-[240px]">Silakan pilih obat atau alat kesehatan yang Anda butuhkan dari katalog kami.</p>
                <button type="button" onclick="toggleCart()" class="px-7 py-3 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-xs font-bold uppercase tracking-widest transition cursor-pointer">
                    LANJUTKAN BELANJA
                </button>
            </div>
        </div>

        <!-- Drawer Footer / Checkout Summary -->
        <div class="p-6 border-t border-slate-200 bg-[#f8fafc]">
            <div class="flex items-center justify-between mb-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700 select-none">
                    <input type="checkbox" id="drawer-check-all" onchange="toggleSelectAllDrawer(this.checked)" class="w-3.5 h-3.5 accent-[#0f2d5a] cursor-pointer">
                    <span>Pilih Semua (<span id="cart-drawer-selected-count">0</span> dipilih)</span>
                </label>
            </div>
            <div class="flex justify-between items-baseline mb-4 pb-3 border-b border-slate-200">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Estimasi:</span>
                <span class="text-xl font-extrabold text-[#0f2d5a]" id="cart-total">Rp 0</span>
            </div>
            <div class="space-y-2.5">
                <button type="button" onclick="checkoutSelectedItems()" id="btn-drawer-checkout" class="block w-full py-3.5 text-center bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    Checkout Sekarang
                </button>
                <a href="cart.php" class="block w-full py-2.5 text-center border border-slate-300 hover:border-[#0f2d5a] text-[#0f2d5a] font-bold text-xs uppercase tracking-wider bg-white transition">
                    Lihat Halaman Keranjang
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[60] flex flex-col gap-3"></div>

    <script>
    // --- Cart State Management (localStorage) ---
    let cartData = JSON.parse(localStorage.getItem('zc_cart') || localStorage.getItem('zencare_cart') || '[]');

    function saveCart() {
        localStorage.setItem('zc_cart', JSON.stringify(cartData));
        localStorage.setItem('zencare_cart', JSON.stringify(cartData));
        renderCart();
        if (typeof renderCartPage === 'function') {
            renderCartPage();
        }
        if (typeof renderCartItems === 'function') {
            renderCartItems();
            if (typeof calculateShippingCost === 'function') calculateShippingCost();
        }
    }

    function toggleCart() {
        const overlay = document.getElementById('cart-overlay');
        const drawer = document.getElementById('cart-drawer');
        const isHidden = drawer.classList.contains('translate-x-full');

        if (isHidden) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            drawer.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden';
            renderCart();
            syncLiveStockWithServer();
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

    function showToast(title, message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        
        const icon = type === 'success' 
            ? '<svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            : '<svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
            
        toast.className = `transform transition-all duration-300 translate-y-8 opacity-0 bg-white border border-slate-200 shadow-lg p-4 w-80 flex gap-3`;
        toast.innerHTML = `
            ${icon}
            <div>
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">${title}</h4>
                <p class="text-xs text-slate-600 mt-0.5">${message}</p>
            </div>
        `;
        
        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-8', 'opacity-0');
        });
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
            cartData[itemIndex].max = maxStokBox;
            cartData[itemIndex].selected = true;
        } else {
            if (qty > maxStokBox) {
                showToast('Stok Tidak Cukup', `Maksimal pembelian untuk item ini adalah ${maxStokBox} ${satB}.`, 'warning');
                qty = maxStokBox;
            } else {
                showToast('Berhasil', `${nama} ditambahkan ke keranjang.`);
            }
            cartData.push({ id, nama, harga, berat, gambar, satuan: satB, rasio, max: maxStokBox, qty, selected: true });
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
        let targetUrl = window.location.pathname.includes('/ecommerce/') ? 'zencare_checkout.php' : 'ecommerce/zencare_checkout.php';
        window.location.href = targetUrl;
    };

    function removeCartItem(id) {
        cartData = cartData.filter(item => item.id != id);
        saveCart();
    }

    function toggleItemSelect(id) {
        let idx = cartData.findIndex(i => i.id == id);
        if (idx > -1) {
            cartData[idx].selected = !(cartData[idx].selected !== false);
            saveCart();
        }
    }

    function toggleSelectAllDrawer(checked) {
        cartData.forEach(item => {
            item.selected = checked;
        });
        saveCart();
    }

    function updateCartItemQty(id, delta) {
        let itemIndex = cartData.findIndex(item => item.id == id);
        if (itemIndex > -1) {
            let item = cartData[itemIndex];
            let maxLimit = parseInt(item.max) || parseInt(item.maxStokBox) || parseInt(item.stokBox) || 0;
            let newQty = item.qty + delta;
            
            if (newQty <= 0) {
                removeCartItem(id);
            } else if (delta > 0 && newQty > maxLimit) {
                showToast('Stok Maksimal', `Stok tersedia hanya ${maxLimit} ${item.satuan || 'Box'}.`, 'warning');
                item.qty = maxLimit;
                saveCart();
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

    function checkoutSelectedItems() {
        let selected = cartData.filter(i => i.selected !== false);
        if (selected.length === 0) {
            showToast('Pilih Produk', 'Centang minimal satu produk untuk melanjutkan checkout.', 'warning');
            return;
        }
        let targetUrl = window.location.pathname.includes('/ecommerce/') ? 'zencare_checkout.php' : 'ecommerce/zencare_checkout.php';
        window.location.href = targetUrl;
    }

    function resolveProductImg(gbr) {
        let isEcommerce = window.location.pathname.includes('/ecommerce/');
        let placeholder = isEcommerce ? '../assets/img/no-image.png' : 'assets/img/no-image.png';
        if (!gbr) return placeholder;
        if (typeof gbr === 'string' && gbr.includes(',')) {
            gbr = gbr.split(',')[0].trim();
        }
        if (gbr.startsWith('http')) return gbr;
        let clean = gbr.replace(/^\.\.\//, '');
        if (!clean) return placeholder;
        if (!clean.includes('/')) {
            clean = 'assets/img/produk/' + clean;
        }
        return isEcommerce ? ('../' + clean) : clean;
    }

    function renderCart() {
        const badge1 = document.getElementById('cart-count-badge');
        const badge2 = document.getElementById('cart-drawer-count');
        const itemsContainer = document.getElementById('cart-items');
        const emptyState = document.getElementById('cart-empty');
        const totalLabel = document.getElementById('cart-total');
        const drawerCheckAll = document.getElementById('drawer-check-all');
        const drawerSelectedCount = document.getElementById('cart-drawer-selected-count');
        const btnCheckout = document.getElementById('btn-drawer-checkout');

        const totalItems = cartData.reduce((sum, item) => sum + item.qty, 0);
        if (badge1) {
            badge1.textContent = totalItems;
            if (totalItems > 0) {
                badge1.classList.remove('hidden');
            } else {
                badge1.classList.add('hidden');
            }
        }
        if (badge2) badge2.textContent = totalItems;

        if (cartData.length === 0) {
            if (itemsContainer) {
                itemsContainer.innerHTML = '';
                itemsContainer.classList.add('hidden');
            }
            if (emptyState) emptyState.classList.remove('hidden');
            if (totalLabel) totalLabel.textContent = 'Rp 0';
            if (drawerCheckAll) drawerCheckAll.checked = false;
            if (drawerSelectedCount) drawerSelectedCount.innerText = '0';
            if (btnCheckout) btnCheckout.disabled = true;
            return;
        }

        if (itemsContainer) itemsContainer.classList.remove('hidden');
        if (emptyState) emptyState.classList.add('hidden');

        let html = '';
        let totalHarga = 0;
        let selectedCount = 0;
        let allSelected = true;

        cartData.forEach(item => {
            const isSelected = (item.selected !== false);
            if (isSelected) {
                totalHarga += (item.harga * item.qty);
                selectedCount += item.qty;
            } else {
                allSelected = false;
            }
            
            const maxStok = parseInt(item.max) || 0;
            const sat = item.satuan || 'Box';
            const imgPath = resolveProductImg(item.gambar);

            html += `
            <div class="pb-4 border-b border-slate-100 flex items-start gap-3 ${isSelected ? '' : 'opacity-60'}">
                <div class="pt-1.5">
                    <input type="checkbox" ${isSelected ? 'checked' : ''} onchange="toggleItemSelect(${item.id})" class="w-3.5 h-3.5 accent-[#0f2d5a] cursor-pointer">
                </div>
                <div class="w-16 h-16 bg-[#f6f8fa] border border-slate-200/70 flex items-center justify-center shrink-0 p-2 overflow-hidden">
                    <img src="${imgPath}" class="w-full h-full object-contain mix-blend-multiply" alt="${item.nama}" onerror="this.src=resolveProductImg('')">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-[#0f2d5a] leading-snug line-clamp-2">${item.nama}</h4>
                            <span class="block text-[10px] text-slate-400 mt-0.5">Stok Tersedia: ${maxStok} ${sat}</span>
                        </div>
                        <button type="button" onclick="removeCartItem(${item.id})" class="text-slate-300 hover:text-rose-600 transition p-0.5 cursor-pointer" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2.5">
                        <div class="text-[#0f2d5a] font-extrabold text-xs">${formatRupiah(item.harga)}</div>
                        <div class="flex items-center border border-slate-200 bg-[#f8fafc] h-7">
                            <button type="button" onclick="updateCartItemQty(${item.id}, -1)" class="w-6 h-full flex items-center justify-center hover:bg-slate-200/60 text-slate-600 font-bold transition cursor-pointer">−</button>
                            <span class="w-7 text-center text-xs font-bold text-[#0f2d5a]">${item.qty}</span>
                            <button type="button" onclick="updateCartItemQty(${item.id}, 1)" ${item.qty >= maxStok ? 'disabled' : ''} class="w-6 h-full flex items-center justify-center font-bold transition ${item.qty >= maxStok ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-200/60 text-slate-600 cursor-pointer'}">+</button>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        if (itemsContainer) itemsContainer.innerHTML = html;
        if (totalLabel) totalLabel.textContent = formatRupiah(totalHarga);
        if (drawerCheckAll) drawerCheckAll.checked = allSelected && cartData.length > 0;
        if (drawerSelectedCount) drawerSelectedCount.innerText = selectedCount;
        if (btnCheckout) {
            btnCheckout.disabled = (selectedCount <= 0);
            btnCheckout.innerText = `CHECKOUT (${selectedCount} PRODUK)`;
        }
    }

    // --- Real-time Stock Sync with POS / Store Database ---
    let isSyncingStock = false;
    async function syncLiveStockWithServer() {
        if (isSyncingStock || !cartData || cartData.length === 0) return;
        isSyncingStock = true;
        try {
            const res = await fetch('/inventory_zencare/api/cek_stok_keranjang.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cartData })
            });
            const data = await res.json();
            if (data.status === 'success') {
                if (data.stock_map) {
                    cartData.forEach(item => {
                        if (data.stock_map[item.id]) {
                            let maxDus = data.stock_map[item.id].max_dus;
                            item.max = maxDus;
                            item.maxStokBox = maxDus;
                            item.stokBox = maxDus;
                            if (item.qty > maxDus) {
                                item.qty = Math.max(1, maxDus);
                            }
                        }
                    });
                }
                if (data.has_adjustments && data.adjusted_items.length > 0) {
                    data.adjusted_items.forEach(adj => {
                        const idx = cartData.findIndex(i => i.id == adj.id);
                        if (idx > -1) {
                            if (adj.new_qty <= 0) {
                                cartData.splice(idx, 1);
                                showToast('Stok Habis di Toko', `Produk ${adj.nama} dihapus dari keranjang karena habis di toko fisik.`, 'warning');
                            } else {
                                cartData[idx].qty = adj.new_qty;
                                cartData[idx].max = adj.max_dus;
                                cartData[idx].maxStokBox = adj.max_dus;
                                cartData[idx].stokBox = adj.max_dus;
                                showToast('Penyesuaian Stok', `Stok ${adj.nama} tersisa ${adj.max_dus} ${adj.satuan}. Jumlah otomatis disesuaikan.`, 'warning');
                            }
                        }
                    });
                }
                saveCart();
            }
        } catch (e) {
            console.warn('Gagal sinkronisasi stok:', e);
        } finally {
            isSyncingStock = false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderCart();
        syncLiveStockWithServer();
    });
    </script>
</body>
</html>
