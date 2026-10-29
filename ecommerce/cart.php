<?php
// File: ecommerce/cart.php
// Dedicated Standalone Shopping Cart Page (Shopee/Tokopedia Style) – ZenCare Medical
require_once __DIR__ . '/header.php';
?>

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-8 flex-1 w-full">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-zcMut mb-6">
        <a href="index.php" class="hover:text-zc transition">Beranda</a>
        <span>/</span>
        <span class="text-zcTxt font-bold">Keranjang Belanja</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-[#0f2d5a] tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-[#1a75d2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Keranjang Belanja Anda
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pilih produk yang ingin Anda beli bersamaan atau simpan untuk pembelian berikutnya.</p>
        </div>
        <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-zc transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Lanjut Belanja Katalog
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" id="cart-page-content">
        <!-- LEFT: Items List (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <!-- Select All Control Bar -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between shadow-xs">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="check-all" onchange="toggleSelectAll(this.checked)" class="w-5 h-5 rounded text-[#1a75d2] border-slate-300 focus:ring-[#1a75d2] cursor-pointer">
                    <span class="text-sm font-bold text-slate-800">Pilih Semua (<span id="cart-total-item-count">0</span> barang)</span>
                </label>
                <button type="button" onclick="deleteSelectedItems()" class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Terpilih
                </button>
            </div>

            <!-- Items Container -->
            <div id="cart-page-items-list" class="space-y-3">
                <!-- Dynamically generated via JS -->
            </div>

            <!-- Empty State -->
            <div id="cart-page-empty" class="hidden bg-white border border-slate-200 rounded-3xl p-12 text-center shadow-xs">
                <div class="w-24 h-24 bg-blue-50 text-[#1a75d2] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="text-lg font-black text-slate-800 mb-1">Keranjang Belanja Anda Kosong</h3>
                <p class="text-xs text-slate-500 mb-6 max-w-sm mx-auto">Anda belum memilih obat atau alat kesehatan. Silakan cari kebutuhan medis Anda di katalog online.</p>
                <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-[#1a75d2] hover:bg-[#1562b3] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                    Mulai Belanja Sekarang
                </a>
            </div>
        </div>

        <!-- RIGHT: Order Summary Card (4 cols) -->
        <div class="lg:col-span-4 sticky top-24 space-y-4">
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                <h3 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 mb-4">
                    Ringkasan Pesanan
                </h3>

                <div class="space-y-3 text-xs mb-5">
                    <div class="flex justify-between text-slate-500">
                        <span>Total Produk Terpilih:</span>
                        <span class="font-bold text-slate-800"><span id="summary-selected-count">0</span> barang</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Total Estimasi Berat:</span>
                        <span class="font-bold text-slate-800"><span id="summary-total-weight">0</span> gram</span>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex justify-between items-baseline">
                        <span class="text-sm font-extrabold text-slate-800">Total Harga:</span>
                        <span class="text-2xl font-black text-[#1a75d2]" id="summary-grand-total">Rp 0</span>
                    </div>
                </div>

                <button type="button" onclick="proceedToCheckout()" id="btn-cart-checkout" 
                    class="w-full py-4 text-center rounded-2xl bg-[#1a75d2] hover:bg-[#1562b3] text-white font-extrabold text-sm shadow-lg shadow-blue-500/25 transition active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    Checkout (<span id="btn-checkout-count">0</span>)
                </button>

                <!-- Assurance Badges -->
                <div class="mt-5 pt-4 border-t border-slate-100 space-y-2 text-[11px] text-slate-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Stok resmi toko obat & alkes terlindungi real-time</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Transaksi pembayaran aman &amp; otomatis</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Pengiriman kurir terintegrasi (JNE, POS, Instant)</span>
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
        if (typeof rawImg === 'string' && rawImg.includes(',')) {
            rawImg = rawImg.split(',')[0].trim();
        }
        const imgPath = rawImg ? (rawImg.startsWith('http') || rawImg.startsWith('../') ? rawImg : `../${rawImg}`) : '../assets/img/no-image.png';

        // Stock status badge
        let stockBadge = '';
        if (maxStok <= 0) {
            stockBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">❌ Stok Habis di Toko</span>`;
        } else if (maxStok <= 5) {
            stockBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">⚠️ Sisa Stok Kritis: ${maxStok} ${sat}</span>`;
        } else {
            stockBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">📦 Sisa Stok: ${maxStok} ${sat}</span>`;
        }

        html += `
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-xs transition hover:border-[#1a75d2]/40 flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <!-- Checkbox -->
            <div class="shrink-0 flex items-center">
                <input type="checkbox" ${isSelected ? 'checked' : ''} onchange="toggleItemSelect(${item.id})" 
                    class="w-5 h-5 rounded text-[#1a75d2] border-slate-300 focus:ring-[#1a75d2] cursor-pointer">
            </div>

            <!-- Product Image -->
            <a href="produk.php?id=${item.id}" class="w-20 h-20 bg-slate-50 border border-slate-100 rounded-xl overflow-hidden shrink-0 flex items-center justify-center p-2">
                <img src="${imgPath}" alt="${item.nama}" class="max-w-full max-h-full object-contain mix-blend-multiply" onerror="this.src='../assets/img/no-image.png'">
            </a>

            <!-- Product Info -->
            <div class="flex-1 min-w-0">
                <a href="produk.php?id=${item.id}" class="text-sm font-extrabold text-slate-800 hover:text-[#1a75d2] transition block leading-snug line-clamp-2">
                    ${item.nama}
                </a>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    ${stockBadge}
                    <span class="text-[10px] text-slate-400 font-semibold">• Satuan: ${sat}</span>
                </div>
                <div class="text-xs font-black text-[#1a75d2] mt-2 sm:hidden">
                    ${formatRupiah(item.harga)} <span class="text-[10px] text-slate-400 font-normal">/ ${sat}</span>
                </div>
            </div>

            <!-- Price Column (Desktop) -->
            <div class="hidden sm:block text-right shrink-0 w-32">
                <span class="text-[10px] text-slate-400 font-medium block">Harga Satuan</span>
                <span class="text-sm font-black text-slate-800 block">${formatRupiah(item.harga)}</span>
            </div>

            <!-- Qty Stepper -->
            <div class="shrink-0 flex items-center gap-2">
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden h-9 shadow-2xs">
                    <button type="button" onclick="updateCartItemQty(${item.id}, -1)" class="w-9 h-full flex items-center justify-center hover:bg-slate-200 text-slate-700 font-extrabold text-sm transition">−</button>
                    <input type="text" readonly value="${item.qty}" class="w-10 text-center font-extrabold text-xs text-slate-800 bg-transparent select-none">
                    <button type="button" onclick="updateCartItemQty(${item.id}, 1)" ${item.qty >= maxStok ? 'disabled' : ''} class="w-9 h-full flex items-center justify-center font-extrabold text-sm transition ${item.qty >= maxStok ? 'opacity-25 bg-slate-100 text-slate-300 cursor-not-allowed pointer-events-none' : 'hover:bg-slate-200 text-slate-700 cursor-pointer'}">+</button>
                </div>
            </div>

            <!-- Subtotal -->
            <div class="text-right shrink-0 w-32">
                <span class="text-[10px] text-slate-400 font-medium block">Total</span>
                <span class="text-sm font-black text-[#1a75d2] block">${formatRupiah(item.harga * item.qty)}</span>
            </div>

            <!-- Delete Action -->
            <div class="shrink-0">
                <button type="button" onclick="removeCartItem(${item.id})" class="p-2 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Produk">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
    window.location.href = '../zencare_checkout.php';
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
