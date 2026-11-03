<?php
// File: ecommerce/cart.php
// Dedicated Standalone Shopping Cart Page (Shopee/Tokopedia Style) – ZenCare Medical
require_once __DIR__ . '/header.php';
?>

<section class="pharmify-banner py-11 lg:py-14 text-center text-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">Keranjang Belanja</h1>
        <nav class="flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90">
            <a href="index.php" class="hover:text-white transition">Beranda</a>
            <span>&rsaquo;</span>
            <span class="text-white font-semibold">Keranjang Belanja</span>
        </nav>
    </div>
</section>

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 flex-1 w-full">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
        <div>
            <h2 class="text-lg font-extrabold text-[#0f2d5a]">Daftar Produk di Keranjang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih produk yang ingin Anda checkout bersamaan atau simpan untuk pembelian berikutnya.</p>
        </div>
        <a href="kategori.php" class="inline-flex items-center gap-2 px-4 py-2 border border-[#0f2d5a] text-xs font-bold text-[#0f2d5a] hover:bg-[#eef6fb] transition">
            &larr; Lanjutkan Belanja
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" id="cart-page-content">
        <!-- LEFT: Items List (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <!-- Select All Control Bar -->
            <div class="bg-[#eef6fb] border border-[#cbe4f5] p-4 flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="check-all" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 accent-[#0f2d5a] cursor-pointer">
                    <span class="text-xs font-extrabold text-[#0f2d5a] uppercase tracking-wider">Pilih Semua (<span id="cart-total-item-count">0</span> produk)</span>
                </label>
                <button type="button" onclick="deleteSelectedItems()" class="text-xs font-bold text-rose-700 hover:bg-rose-50 px-3 py-1.5 border border-transparent hover:border-rose-200 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Terpilih
                </button>
            </div>

            <!-- Items Container -->
            <div id="cart-page-items-list" class="space-y-3">
                <!-- Dynamically generated via JS -->
            </div>

            <!-- Empty State -->
            <div id="cart-page-empty" class="hidden bg-[#f8fafc] border border-slate-200 p-12 text-center">
                <h3 class="text-lg font-extrabold text-[#0f2d5a] mb-1">Keranjang Belanja Anda Kosong</h3>
                <p class="text-xs text-slate-500 mb-6 max-w-sm mx-auto">Anda belum memilih obat atau alat kesehatan. Silakan jelajahi katalog online kami.</p>
                <a href="kategori.php" class="inline-flex items-center gap-2 px-7 py-3 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-xs font-bold uppercase tracking-widest transition">
                    MULAI BELANJA SEKARANG
                </a>
            </div>
        </div>

        <!-- RIGHT: Order Summary Card (4 cols) -->
        <div class="lg:col-span-4 sticky top-24 space-y-4">
            <div class="bg-[#eef6fb] border border-[#cbe4f5] p-6">
                <h3 class="text-base font-extrabold text-[#0f2d5a] border-b border-[#cbe4f5] pb-3 mb-4">
                    Ringkasan Pesanan
                </h3>

                <div class="space-y-3 text-xs mb-5 bg-white border border-[#cbe4f5] p-4">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Produk Terpilih:</span>
                        <span class="font-bold text-[#0f2d5a]"><span id="summary-selected-count">0</span> barang</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Total Estimasi Berat:</span>
                        <span class="font-bold text-[#0f2d5a]"><span id="summary-total-weight">0</span> gram</span>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">Total Harga:</span>
                        <span class="text-xl font-extrabold text-[#0f2d5a]" id="summary-grand-total">Rp 0</span>
                    </div>
                </div>

                <button type="button" onclick="proceedToCheckout()" id="btn-cart-checkout" 
                    class="w-full py-3.5 text-center bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    LANJUTKAN KE CHECKOUT (<span id="btn-checkout-count">0</span>)
                </button>

                <!-- Assurance Badges -->
                <div class="mt-5 pt-4 border-t border-[#cbe4f5] space-y-2 text-[11px] text-slate-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0f2d5a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Stok resmi gudang terlindungi secara real-time</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0f2d5a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Pembayaran otomatis via Midtrans</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function renderCartPage() {
    const listContainer = document.getElementById('cart-page-items-list');
    const emptyState = document.getElementById('cart-page-empty');
    const checkAll = document.getElementById('check-all');
    const totalItemCountEl = document.getElementById('cart-total-item-count');
    const selectedCountEl = document.getElementById('summary-selected-count');
    const totalWeightEl = document.getElementById('summary-total-weight');
    const grandTotalEl = document.getElementById('summary-grand-total');
    const btnCheckoutCount = document.getElementById('btn-checkout-count');
    const btnCheckout = document.getElementById('btn-cart-checkout');

    if (!Array.isArray(cartData) || cartData.length === 0) {
        listContainer.classList.add('hidden');
        emptyState.classList.remove('hidden');
        if (checkAll) checkAll.checked = false;
        totalItemCountEl.innerText = '0';
        selectedCountEl.innerText = '0';
        totalWeightEl.innerText = '0';
        grandTotalEl.innerText = 'Rp 0';
        btnCheckoutCount.innerText = '0';
        if (btnCheckout) btnCheckout.disabled = true;
        return;
    }

    listContainer.classList.remove('hidden');
    emptyState.classList.add('hidden');
    totalItemCountEl.innerText = cartData.length;

    let selectedCount = 0;
    let totalWeight = 0;
    let grandTotal = 0;
    let allSelected = true;

    let html = '';
    cartData.forEach(item => {
        const isSelected = (item.selected !== false);
        if (isSelected) {
            selectedCount += item.qty;
            totalWeight += ((parseInt(item.berat) || 100) * item.qty);
            grandTotal += (item.harga * item.qty);
        } else {
            allSelected = false;
        }

        const maxStok = parseInt(item.max) || 0;
        const sat = item.satuan || 'Box';
        let rawImg = item.gambar || item.image || '';
        const imgPath = (typeof resolveProductImg === 'function') ? resolveProductImg(rawImg) : '../assets/img/no-image.png';

        let stockBadge = '';
        if (maxStok <= 0) {
            stockBadge = `<span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Stok Habis</span>`;
        } else if (maxStok <= 5) {
            stockBadge = `<span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Sisa Stok: ${maxStok} ${sat}</span>`;
        } else {
            stockBadge = `<span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Stok Tersedia: ${maxStok} ${sat}</span>`;
        }

        html += `
        <div class="bg-white border border-slate-200 p-4 sm:p-5 transition hover:border-[#0f2d5a] flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <!-- Checkbox -->
            <div class="shrink-0 flex items-center">
                <input type="checkbox" ${isSelected ? 'checked' : ''} onchange="toggleItemSelect(${item.id})" 
                    class="w-4 h-4 accent-[#0f2d5a] cursor-pointer">
            </div>

            <!-- Product Image -->
            <a href="produk.php?id=${item.id}" class="w-20 h-20 bg-[#f6f8fa] border border-slate-200/80 overflow-hidden shrink-0 flex items-center justify-center p-2">
                <img src="${imgPath}" alt="${item.nama}" class="max-w-full max-h-full object-contain mix-blend-multiply" onerror="this.src='../assets/img/no-image.png'">
            </a>

            <!-- Product Info -->
            <div class="flex-1 min-w-0">
                <a href="produk.php?id=${item.id}" class="text-sm font-bold text-[#0f2d5a] hover:text-[#1a75d2] transition block leading-snug line-clamp-2">
                    ${item.nama}
                </a>
                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                    ${stockBadge}
                    <span class="text-[11px] text-slate-400 font-medium">&bull; Kemasan: ${sat}</span>
                </div>
                <div class="text-xs font-extrabold text-[#0f2d5a] mt-2 sm:hidden">
                    ${formatRupiah(item.harga)} <span class="text-[10px] text-slate-400 font-normal">/ ${sat}</span>
                </div>
            </div>

            <!-- Price Column (Desktop) -->
            <div class="hidden sm:block text-right shrink-0 w-32">
                <span class="text-[10px] text-slate-400 font-medium block">Harga Satuan</span>
                <span class="text-sm font-extrabold text-[#0f2d5a] block">${formatRupiah(item.harga)}</span>
            </div>

            <!-- Qty Stepper -->
            <div class="shrink-0 flex items-center gap-2">
                <div class="flex items-center bg-[#f8fafc] border border-slate-200 h-9">
                    <button type="button" onclick="updateCartItemQty(${item.id}, -1)" class="w-8 h-full flex items-center justify-center hover:bg-slate-200/60 text-[#0f2d5a] font-bold text-sm transition cursor-pointer">−</button>
                    <input type="text" readonly value="${item.qty}" class="w-9 text-center font-bold text-xs text-[#0f2d5a] bg-transparent select-none">
                    <button type="button" onclick="updateCartItemQty(${item.id}, 1)" ${item.qty >= maxStok ? 'disabled' : ''} class="w-8 h-full flex items-center justify-center font-bold text-sm transition ${item.qty >= maxStok ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-200/60 text-[#0f2d5a] cursor-pointer'}">+</button>
                </div>
            </div>

            <!-- Subtotal -->
            <div class="text-right shrink-0 w-32">
                <span class="text-[10px] text-slate-400 font-medium block">Subtotal</span>
                <span class="text-sm font-extrabold text-[#0f2d5a] block">${formatRupiah(item.harga * item.qty)}</span>
            </div>

            <!-- Delete Action -->
            <div class="shrink-0">
                <button type="button" onclick="removeCartItem(${item.id})" class="p-2 text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Hapus Produk">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        `;
    });

    listContainer.innerHTML = html;
    if (checkAll) checkAll.checked = allSelected && cartData.length > 0;
    selectedCountEl.innerText = selectedCount;
    totalWeightEl.innerText = totalWeight.toLocaleString('id-ID');
    grandTotalEl.innerText = formatRupiah(grandTotal);
    btnCheckoutCount.innerText = selectedCount;
    if (btnCheckout) btnCheckout.disabled = (selectedCount <= 0);
}

function toggleItemSelect(id) {
    let idx = cartData.findIndex(i => i.id == id);
    if (idx > -1) {
        cartData[idx].selected = !(cartData[idx].selected !== false);
        saveCart();
        renderCartPage();
    }
}

function toggleSelectAll(checked) {
    cartData.forEach(i => {
        i.selected = checked;
    });
    saveCart();
    renderCartPage();
}

function deleteSelectedItems() {
    let selectedCount = cartData.filter(i => i.selected !== false).length;
    if (selectedCount === 0) {
        showToast('Pilih Item', 'Silakan centang item yang ingin dihapus terlebih dahulu.', 'warning');
        return;
    }
    if (confirm(`Hapus ${selectedCount} produk terpilih dari keranjang belanja?`)) {
        cartData = cartData.filter(i => i.selected === false);
        saveCart();
        renderCartPage();
        showToast('Berhasil', 'Produk terpilih telah dihapus.');
    }
}

function proceedToCheckout() {
    let selected = cartData.filter(i => i.selected !== false);
    if (selected.length === 0) {
        showToast('Pilih Produk', 'Centang minimal satu produk untuk melanjutkan pembayaran.', 'warning');
        return;
    }
    window.location.href = 'zencare_checkout.php';
}

document.addEventListener('DOMContentLoaded', () => {
    renderCartPage();
    // Hook into syncLiveStock to refresh cart page
    const originalSync = window.syncLiveStockWithServer;
    if (typeof originalSync === 'function') {
        syncLiveStockWithServer().then(() => renderCartPage());
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
