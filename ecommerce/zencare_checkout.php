<?php
// File: ecommerce/zencare_checkout.php — Modul Checkout & Pembayaran E-Commerce (Pharmify Editorial Style)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

// Force user to login before checkout
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_customer.php?redirect=ecommerce/zencare_checkout.php");
    exit;
}

$sesiOpnameAktif = cekSesiOpnameAktif($pdo);
$activeCabangId  = 1;
$originKotaId    = 256; // Malang
$branchLat       = -7.9881;
$branchLng       = 112.6371;

$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$uStmt->execute([$_SESSION['user_id']]);
$savedUser = $uStmt->fetch();

$apiKeys = file_exists(__DIR__ . '/../config/api_keys.php') ? (require __DIR__ . '/../config/api_keys.php') : [];
$rajaongkirEnabled = !empty($apiKeys['rajaongkir']['active']);

$stmtWeb  = $pdo->query("SELECT * FROM pengaturan_web WHERE id=1");
$webCfg   = $stmtWeb->fetch() ?: [];
$namaToko = $webCfg['nama_toko'] ?? 'ZenCare Medical Store';

$pageTitle = "Checkout Pembayaran";
require_once __DIR__ . '/header.php';
?>

<!-- jQuery, Select2, Leaflet & Midtrans Snap -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>

<style>
  .select2-container--default .select2-selection--single {
    height: 42px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 0px !important;
    padding: 6px 12px !important;
    display: flex !important;
    align-items: center !important;
    background-color: #fff !important;
  }
  .select2-container--default .select2-selection--single:focus {
    border-color: #0f2d5a !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #0f2d5a !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
    padding-left: 0 !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 8px !important;
  }
  .select2-dropdown {
    border: 1px solid #0f2d5a !important;
    border-radius: 0px !important;
    box-shadow: 0 10px 25px -5px rgba(15,45,90,0.12) !important;
    overflow: hidden !important;
  }
  .select2-results__option {
    font-size: 0.75rem !important;
    padding: 8px 12px !important;
  }
  .select2-results__option--highlighted {
    background-color: #0f2d5a !important;
  }
</style>

<!-- ============================================================ -->
<!-- PHARMIFY SUBPAGE MOLECULAR BREADCRUMB BANNER                 -->
<!-- ============================================================ -->
<section class="pharmify-banner py-11 lg:py-14 text-center text-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">Checkout Pembayaran</h1>
        <nav class="flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90">
            <a href="index.php" class="hover:text-white transition">Beranda</a>
            <span>&rsaquo;</span>
            <a href="cart.php" class="hover:text-white transition">Keranjang Belanja</a>
            <span>&rsaquo;</span>
            <span class="text-white font-semibold">Checkout</span>
        </nav>
    </div>
</section>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12 flex-1 w-full bg-white">
    
    <!-- Top Info Bar -->
    <div class="mb-8 pb-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-extrabold text-[#0f2d5a]">Formulir Pengiriman &amp; Konfirmasi Pesanan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data penerima dan pilih layanan ekspedisi atau pengambilan langsung di toko.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold px-3.5 py-1.5 bg-[#eef6fb] text-[#0f2d5a] border border-[#cbe4f5]">
                Gudang Pengirim: <?= htmlspecialchars($cabangAktif['nama'] ?? 'ZenCare Malang') ?>
            </span>
            <a href="cart.php" class="text-xs font-bold text-[#0f2d5a] hover:text-[#1a75d2] border border-slate-300 px-3.5 py-1.5 transition">
                &larr; Kembali ke Keranjang
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
        
        <!-- ============================================================ -->
        <!-- LEFT PANEL: SHIPPING & RECIPIENT FORM (7 Cols)               -->
        <!-- ============================================================ -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- 1. Identitas Penerima -->
            <div class="bg-white border border-slate-200 p-6 sm:p-8">
                <div class="border-b border-slate-200 pb-3.5 mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 bg-[#0f2d5a] text-white text-xs font-extrabold flex items-center justify-center">01</span>
                        <h3 class="text-base font-extrabold text-[#0f2d5a]">Identitas Penerima</h3>
                    </div>
                    <?php if ($savedUser): ?>
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5">
                            Data Profil Terhubung
                        </span>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-[#0f2d5a] mb-1.5">Nama Lengkap Penerima *</label>
                        <input type="text" id="nama_pembeli" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] font-medium focus:outline-none focus:border-[#0f2d5a] transition"
                            value="<?= htmlspecialchars($savedUser['nama_lengkap'] ?? $_SESSION['nama_lengkap'] ?? '') ?>" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div>
                        <label class="block font-bold text-[#0f2d5a] mb-1.5">Nomor HP / WhatsApp Aktif *</label>
                        <input type="text" id="phone" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] font-medium focus:outline-none focus:border-[#0f2d5a] transition"
                            value="<?= htmlspecialchars($savedUser['telepon'] ?? '') ?>" placeholder="Contoh: 081234567890">
                    </div>
                </div>
            </div>

            <!-- 2. Alamat & RajaOngkir API -->
            <div class="bg-white border border-slate-200 p-6 sm:p-8">
                <div class="border-b border-slate-200 pb-3.5 mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 bg-[#0f2d5a] text-white text-xs font-extrabold flex items-center justify-center">02</span>
                        <h3 class="text-base font-extrabold text-[#0f2d5a]">Alamat Tujuan &amp; Metode Pengiriman</h3>
                    </div>
                </div>
                
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-[#0f2d5a] mb-1.5">Provinsi Tujuan *</label>
                            <select id="provinsi_select" onchange="loadCitiesAPI()" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] focus:outline-none focus:border-[#0f2d5a] transition">
                                <option value="">-- Memuat Provinsi... --</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-[#0f2d5a] mb-1.5">Kota / Kabupaten Tujuan *</label>
                            <select id="kota_select" onchange="onCityChange()" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] focus:outline-none focus:border-[#0f2d5a] transition" disabled>
                                <option value="">-- Pilih Provinsi Dahulu --</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-[#0f2d5a] mb-1.5">Alamat Pengiriman Lengkap *</label>
                        <textarea id="alamat_lengkap" rows="2" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] font-medium focus:outline-none focus:border-[#0f2d5a] transition"
                            placeholder="Nama Jalan, Nomor Rumah/Klinik, RT/RW, Kelurahan, Kecamatan, Kode Pos"><?= htmlspecialchars($savedUser['alamat'] ?? '') ?></textarea>
                    </div>

                    <!-- Notice: Area Malang -->
                    <div id="malang_notice" class="hidden p-4 bg-[#eef6fb] border-l-4 border-[#0f2d5a] flex gap-3">
                        <svg class="w-5 h-5 text-[#0f2d5a] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="text-xs text-[#0f2d5a] leading-relaxed">
                            <strong class="block mb-0.5">Area Dalam Kota (Malang) Terdeteksi</strong> 
                            Pengiriman dapat menggunakan Kurir Internal ZenCare Direct atau opsi Ambil Sendiri di Toko (Bebas Ongkir).
                        </div>
                    </div>

                    <!-- Notice: Luar Kota -->
                    <div id="luar_kota_notice" class="hidden p-4 bg-[#f8fafc] border-l-4 border-[#1a75d2] flex gap-3">
                        <svg class="w-5 h-5 text-[#1a75d2] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        <div class="text-xs text-slate-600 leading-relaxed">
                            <strong class="text-[#0f2d5a] block mb-0.5">Area Luar Kota Terdeteksi</strong> 
                            Pengiriman dilayani menggunakan Ekspedisi Nasional (JNE / POS Indonesia / J&amp;T Express) atau Ambil di Toko.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block font-bold text-[#0f2d5a] mb-1.5">Metode / Kurir Pengiriman *</label>
                            <select id="courier_select" onchange="calculateShippingCost()" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] font-semibold focus:outline-none focus:border-[#0f2d5a] transition">
                                <!-- Populated dynamically by JS -->
                            </select>
                        </div>

                        <div id="service_container" class="hidden">
                            <label class="block font-bold text-[#0f2d5a] mb-1.5">Layanan Paket Ekspedisi *</label>
                            <select id="service_select" onchange="onServiceChange()" class="w-full border border-slate-300 px-3.5 py-2.5 bg-white text-[#0f2d5a] font-semibold focus:outline-none focus:border-[#0f2d5a] transition">
                                <option value="">-- Memuat Layanan Paket... --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Leaflet Interactive Map -->
                    <div id="map_panel" class="space-y-2 pt-4 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-[#0f2d5a]">Titik Koordinat Lokasi Pengiriman (Peta Interaktif)</label>
                            <span id="distance_info" class="font-bold text-[#1a75d2]">Jarak: ~0 km</span>
                        </div>
                        <div id="map" class="w-full h-56 border border-slate-300 z-10"></div>
                        <div class="flex justify-between text-[11px] text-slate-500 pt-1">
                            <span>Geser penanda (pin) atau klik pada peta untuk menyesuaikan titik pengantaran.</span>
                            <span>Koordinat: <strong id="disp_lat" class="text-[#0f2d5a]"><?= $savedUser['lat'] ?? $branchLat ?></strong>, <strong id="disp_lng" class="text-[#0f2d5a]"><?= $savedUser['lng'] ?? $branchLng ?></strong></span>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="pt-2">
                            <label class="flex items-center gap-2.5 text-xs font-semibold text-[#0f2d5a] cursor-pointer select-none">
                                <input type="checkbox" id="save_profile_address" checked class="w-4 h-4 accent-[#0f2d5a]">
                                <span>Simpan alamat &amp; titik koordinat ini sebagai alamat utama profil saya</span>
                            </label>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- RIGHT PANEL: ORDER SUMMARY & PAYMENT (5 Cols - Pharmify Box) -->
        <!-- ============================================================ -->
        <div class="lg:col-span-5">
            <div class="bg-[#eef6fb] border border-[#cbe4f5] p-6 sm:p-8 sticky top-24">
                
                <div class="flex items-center justify-between border-b border-[#cbe4f5] pb-3.5 mb-5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 bg-[#0f2d5a] text-white text-xs font-extrabold flex items-center justify-center">03</span>
                        <h3 class="text-base font-extrabold text-[#0f2d5a]">Ringkasan Pesanan</h3>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500">Ubah Jumlah / Hapus</span>
                </div>
                
                <!-- Stock Adjustment Alert -->
                <div id="stock_sync_alert" class="hidden mb-4 p-3.5 bg-amber-50 border border-amber-300 text-amber-900 text-xs">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div id="stock_sync_text" class="flex-1 font-medium leading-relaxed"></div>
                    </div>
                </div>

                <!-- Interactive Editable Cart Summary List -->
                <div id="cart_summary_list" class="space-y-3 max-h-72 overflow-y-auto pr-1 mb-5">
                    <!-- Loaded via JavaScript -->
                </div>

                <!-- Totals Breakdown -->
                <div class="bg-white border border-[#cbe4f5] p-4 space-y-2.5 text-xs mb-5">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Total Berat Pengiriman:</span>
                        <span id="disp_total_weight" class="font-bold text-[#0f2d5a]">0 Gram</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Subtotal Produk:</span>
                        <span id="disp_subtotal" class="font-bold text-[#0f2d5a]">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Biaya Pengiriman:</span>
                        <span id="disp_ongkir" class="font-bold text-[#0f2d5a]">Rp 10.000</span>
                    </div>
                    <div class="flex justify-between items-baseline text-sm font-extrabold text-[#0f2d5a] border-t border-slate-200 pt-3 mt-1">
                        <span class="uppercase tracking-wider text-xs">Total Pembayaran:</span>
                        <span id="disp_grand_total" class="text-xl text-[#0f2d5a]">Rp 0</span>
                    </div>
                </div>

                <!-- Metode Pembayaran Otomatis Midtrans -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">Metode Pembayaran:</span>
                        <span class="text-[10px] font-bold text-[#0f2d5a] bg-white border border-[#cbe4f5] px-2.5 py-0.5">
                            Batas Pembayaran: 2 Jam
                        </span>
                    </div>
                    <div class="border border-[#cbe4f5] bg-white p-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-extrabold text-[#0f2d5a] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#1a75d2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                Midtrans Payment Gateway
                            </span>
                            <span class="text-[10px] font-bold text-[#1a75d2] uppercase tracking-wider">Verifikasi Otomatis</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mb-2.5">
                            Pilih kanal pembayaran secara langsung saat jendela pembayaran muncul:
                        </p>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-bold text-[#0f2d5a]">
                            <span class="px-2 py-1 bg-[#f6f8fa] border border-slate-200">QRIS</span>
                            <span class="px-2 py-1 bg-[#f6f8fa] border border-slate-200">BCA VA</span>
                            <span class="px-2 py-1 bg-[#f6f8fa] border border-slate-200">Mandiri Bill</span>
                            <span class="px-2 py-1 bg-[#f6f8fa] border border-slate-200">BNI / BRI VA</span>
                            <span class="px-2 py-1 bg-[#f6f8fa] border border-slate-200">GoPay / ShopeePay</span>
                        </div>
                    </div>
                </div>

                <!-- Primary Checkout Action -->
                <div class="space-y-2.5">
                    <?php if ($sesiOpnameAktif): ?>
                        <div class="p-4 bg-rose-50 border border-rose-300 text-rose-800 text-xs">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <div>
                                    <strong class="block font-bold text-rose-900">Checkout Ditutup Sementara</strong>
                                    <p class="text-[11px] text-rose-700 mt-0.5 leading-relaxed">
                                        Toko sedang melakukan audit fisik stok berkala (Kode Sesi: <strong><?= htmlspecialchars($sesiOpnameAktif['kode_sesi']) ?></strong>). Transaksi online ditahan sementara demi akurasi inventori.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button disabled id="btn_checkout_sekarang" class="w-full bg-slate-200 text-slate-400 font-bold text-xs uppercase tracking-widest py-4 px-4 cursor-not-allowed flex items-center justify-center gap-2">
                            <span>CHECKOUT TERKUNCI (STOK OPNAME AKTIF)</span>
                        </button>
                    <?php else: ?>
                        <button onclick="prosesCheckout(false)" id="btn_checkout_nanti" style="display: none;" class="w-full bg-white hover:bg-slate-50 text-[#0f2d5a] border border-slate-300 font-bold text-xs uppercase tracking-wider py-3.5 px-4 transition">
                            Buat Pesanan (Bayar Nanti)
                        </button>
                        <button onclick="prosesCheckout(true)" id="btn_checkout_sekarang" class="w-full bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest py-4 px-6 transition flex items-center justify-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            <span>BAYAR SEKARANG</span>
                        </button>
                        <p class="text-[11px] text-center text-slate-500 pt-1">
                            Stok produk otomatis dikunci selama 2 jam setelah jendela pembayaran dibuat.
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</main>

<!-- Script Leaflet JS Map, Cart, & Midtrans Integration -->
<script>
    const ORIGIN_KOTA_ID = "<?= $originKotaId ?>";
    const RAJAONGKIR_ENABLED = <?= $rajaongkirEnabled ? 'true' : 'false' ?>;
    const BRANCH_LAT = <?= $branchLat ?>;
    const BRANCH_LNG = <?= $branchLng ?>;
    const SAVED_LAT = <?= $savedUser['lat'] ?? 'null' ?>;
    const SAVED_LNG = <?= $savedUser['lng'] ?? 'null' ?>;
    const SAVED_KOTA_ID = <?= $savedUser['kota_id'] ?? 'null' ?>;

    const CITY_MAP_COORDS = {
        '391': [-7.9666, 112.6326], // Malang
        '256': [-7.9666, 112.6326], // Malang
        '393': [-7.8702, 112.5271], // Batu
        '577': [-7.2575, 112.7521], // Surabaya
        '583': [-7.4478, 112.7183], // Sidoarjo
        '160': [-8.1721, 113.7001], // Jember
        '531': [-7.6453, 112.9075], // Pasuruan
        '289': [-7.8480, 112.0178], // Kediri
        '10':  [-6.2088, 106.8456], // Jakarta
        '5':   [-6.9175, 107.6191], // Bandung
        '12':  [-6.9932, 110.4203], // Semarang
        '19':  [-7.7956, 110.3695], // Yogyakarta
    };

    let map, marker;
    let selectedLat = (SAVED_LAT !== null) ? SAVED_LAT : BRANCH_LAT;
    let selectedLng = (SAVED_LNG !== null) ? SAVED_LNG : BRANCH_LNG;
    let currentOngkir = 10000;
    let selectedServiceLabel = 'ZenCare Direct Delivery';
    let availableCosts = [];

    function initLeafletMap() {
        map = L.map('map').setView([selectedLat, selectedLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        marker = L.marker([selectedLat, selectedLng], {draggable: true}).addTo(map);
        
        marker.on('dragend', function() {
            let pos = marker.getLatLng();
            onMarkerMove(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            onMarkerMove(e.latlng.lat, e.latlng.lng);
        });
    }

    function onMarkerMove(lat, lng) {
        selectedLat = lat;
        selectedLng = lng;
        document.getElementById('disp_lat').innerText = lat.toFixed(6);
        document.getElementById('disp_lng').innerText = lng.toFixed(6);

        let courierSelect = document.getElementById('courier_select');
        if (courierSelect.value === 'internal') {
            let dist = calculateHaversineDistance(BRANCH_LAT, BRANCH_LNG, lat, lng);
            document.getElementById('distance_info').innerText = 'Jarak: ~' + dist.toFixed(1) + ' km';
            
            let cart = getCheckoutCart();
            let totalWeight = cart.reduce((sum, item) => sum + ((parseInt(item.berat) || 100) * (parseInt(item.qty) || 1)), 0);

            let baseFare = 10000;
            let distFare = dist > 2 ? ((dist - 2) * 3000) : 0;
            let weightKg = Math.ceil(totalWeight / 1000);
            let weightFare = weightKg > 1 ? (weightKg - 1) * 2000 : 0;

            let rawOngkir = baseFare + distFare + weightFare;
            currentOngkir = Math.ceil(rawOngkir / 1000) * 1000;

            let weightLabel = totalWeight >= 1000 ? (totalWeight/1000).toFixed(1) + 'kg' : totalWeight + 'g';
            selectedServiceLabel = 'ZenCare Direct Delivery (' + dist.toFixed(1) + ' km, ' + weightLabel + ')';
            updateTotalsDisplay();
        }
    }

    function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
        let R = 6371;
        let dLat = (lat2-lat1) * Math.PI / 180;
        let dLon = (lon2-lon1) * Math.PI / 180;
        let a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function loadProvincesAPI() {
        let provSelect = document.getElementById('provinsi_select');
        
        fetch('../api/rajaongkir.php?action=provinces')
            .then(res => res.json())
            .then(data => {
                let list = data.data || data.results || [];
                if (Array.isArray(list) && list.length > 0) {
                    let html = '<option value="">-- Pilih Provinsi --</option>';
                    list.forEach(p => {
                        let id = p.province_id || p.id;
                        let name = p.province || p.name;
                        let sel = (name.toLowerCase().includes('jawa timur') || id == 18) ? 'selected' : '';
                        html += `<option value="${id}" ${sel}>${name}</option>`;
                    });
                    provSelect.innerHTML = html;
                    initSelect2Provinsi();
                    loadCitiesAPI();
                } else { throw new Error("No data"); }
            })
            .catch(() => {
                provSelect.innerHTML = `
                    <option value="18" selected>JAWA TIMUR</option>
                    <option value="10">DKI JAKARTA</option>
                    <option value="12">JAWA TENGAH</option>
                    <option value="5">JAWA BARAT</option>
                `;
                initSelect2Provinsi();
                loadCitiesAPI();
            });
    }

    function initSelect2Provinsi() {
        if (window.jQuery && $.fn.select2) {
            $('#provinsi_select').select2({ placeholder: '-- Cari & Pilih Provinsi --', width: '100%' })
                .off('change.select2custom')
                .on('change.select2custom', function() {
                    loadCitiesAPI();
                });
        }
    }

    function loadCitiesAPI() {
        let provId = document.getElementById('provinsi_select').value || '18';
        let isJatim = (provId == '18');
        let citySelect = document.getElementById('kota_select');
        citySelect.disabled = true;
        if (window.jQuery && $.fn.select2) {
            $('#kota_select').prop('disabled', true);
        }
        citySelect.innerHTML = '<option value="">Memuat kota...</option>';

        fetch('../api/rajaongkir.php?action=cities&province_id=' + provId)
            .then(res => res.json())
            .then(data => {
                let list = data.data || data.results || [];
                if (Array.isArray(list) && list.length > 0) {
                    let html = '';
                    list.forEach((c, idx) => {
                        let id = c.city_id || c.id;
                        let name = (c.type ? c.type + ' ' : '') + (c.city_name || c.name);
                        let isSelected = false;
                        if (SAVED_KOTA_ID && SAVED_KOTA_ID == id) {
                            isSelected = true;
                        } else if (!SAVED_KOTA_ID && isJatim && (name.toLowerCase().includes('malang') || id == 391 || id == 256)) {
                            isSelected = true;
                        } else if (!SAVED_KOTA_ID && !isJatim && idx === 0) {
                            isSelected = true;
                        }
                        html += `<option value="${id}" ${isSelected ? 'selected' : ''}>${name}</option>`;
                    });
                    
                    if (window.jQuery && $.fn.select2 && $('#kota_select').hasClass('select2-hidden-accessible')) {
                        $('#kota_select').select2('destroy');
                    }
                    citySelect.innerHTML = html;
                    citySelect.disabled = false;
                    initSelect2Kota();
                    onCityChange();
                } else { throw new Error("No cities"); }
            })
            .catch(() => {
                citySelect.innerHTML = '<option value="">Gagal memuat kota. Silakan coba lagi</option>';
                citySelect.disabled = false;
                initSelect2Kota();
            });
    }

    function initSelect2Kota() {
        if (window.jQuery && $.fn.select2) {
            $('#kota_select').prop('disabled', false).select2({ placeholder: '-- Cari & Pilih Kota --', width: '100%' })
                .off('change.select2custom')
                .on('change.select2custom', function() {
                    onCityChange();
                });
        }
    }

    function onCityChange() {
        let citySelect = document.getElementById('kota_select');
        let cityVal = citySelect.value;
        let cityName = citySelect.options[citySelect.selectedIndex]?.text || '';
        let courierSelect = document.getElementById('courier_select');

        let isMalang = cityName.toLowerCase().includes('malang');

        if (isMalang) {
            document.getElementById('malang_notice').classList.remove('hidden');
            document.getElementById('luar_kota_notice').classList.add('hidden');
            document.getElementById('map_panel').classList.remove('hidden');
            
            courierSelect.innerHTML = `
                <option value="internal">Kurir Internal ZenCare Direct (Khusus Malang)</option>
                <option value="pickup">Ambil Sendiri di Toko (Bebas Ongkir)</option>
            `;
            courierSelect.value = 'internal';

            if (SAVED_LAT === null) {
                selectedLat = BRANCH_LAT;
                selectedLng = BRANCH_LNG;
            }
            if (map && marker) {
                map.setView([selectedLat, selectedLng], 13);
                marker.setLatLng([selectedLat, selectedLng]);
            }
        } else {
            document.getElementById('malang_notice').classList.add('hidden');
            document.getElementById('luar_kota_notice').classList.remove('hidden');
            document.getElementById('map_panel').classList.remove('hidden');

            courierSelect.innerHTML = `
                <option value="jne">Ekspedisi JNE Express</option>
                <option value="pos">Ekspedisi POS Indonesia</option>
                <option value="jnt">Ekspedisi J&T Express</option>
                <option value="pickup">Ambil Sendiri di Toko (Bebas Ongkir)</option>
            `;
            courierSelect.value = 'jne';

            let cityCoord = CITY_MAP_COORDS[cityVal] || [-7.9666, 112.6326];
            selectedLat = cityCoord[0];
            selectedLng = cityCoord[1];
            if (map && marker) {
                map.setView([selectedLat, selectedLng], 12);
                marker.setLatLng([selectedLat, selectedLng]);
            }
            document.getElementById('disp_lat').innerText = selectedLat.toFixed(6);
            document.getElementById('disp_lng').innerText = selectedLng.toFixed(6);
            document.getElementById('distance_info').innerText = 'Lokasi: ' + cityName;
        }

        calculateShippingCost();
    }

    function calculateShippingCost() {
        let courier = document.getElementById('courier_select').value;
        let destCityId = document.getElementById('kota_select').value || '391';
        let serviceContainer = document.getElementById('service_container');
        let mapPanel = document.getElementById('map_panel');

        if (courier === 'internal') {
            serviceContainer.classList.add('hidden');
            mapPanel.classList.remove('hidden');
            onMarkerMove(selectedLat, selectedLng);
            return;
        }
        
        if (courier === 'pickup') {
            serviceContainer.classList.add('hidden');
            mapPanel.classList.add('hidden');
            currentOngkir = 0;
            selectedServiceLabel = 'Ambil Sendiri di Toko';
            updateTotalsDisplay();
            return;
        }

        serviceContainer.classList.remove('hidden');
        mapPanel.classList.remove('hidden');
        let cart = getCheckoutCart();
        let totalWeight = cart.reduce((sum, item) => sum + ((parseInt(item.berat) || 100) * (parseInt(item.qty) || 1)), 0);
        if (totalWeight <= 0) totalWeight = 1000;

        let serviceSelect = document.getElementById('service_select');
        serviceSelect.innerHTML = '<option value="">Kalkulasi Ongkir API RajaOngkir...</option>';

        let citySelectElem = document.getElementById('kota_select');
        let cityNameVal = citySelectElem.options[citySelectElem.selectedIndex]?.text || '';

        let formData = new FormData();
        formData.append('origin', ORIGIN_KOTA_ID);
        formData.append('destination', destCityId);
        formData.append('city_name', cityNameVal);
        formData.append('weight', totalWeight);
        formData.append('courier', courier);

        fetch('../api/rajaongkir.php?action=cost', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            let results = data.data || data.results || [];
            let costs = [];
            
            if (Array.isArray(results) && results.length > 0) {
                if (results[0].costs) {
                    costs = results[0].costs;
                } else if (results[0].service) {
                    costs = results;
                }
            }

            if (costs.length > 0) {
                availableCosts = costs;
                let html = '';
                let isHeavyWeight = (totalWeight >= 10000);
                let recommendedIdx = -1;

                costs.forEach((c, idx) => {
                    let costVal = (typeof c.cost === 'number') ? c.cost : (c.cost && c.cost[0] ? (c.cost[0].value || 0) : 0);
                    let etdStr = c.etd ? ` (Estimasi: ${c.etd})` : (c.cost && c.cost[0] && c.cost[0].etd ? ` (Estimasi: ${c.cost[0].etd} Hari)` : '');
                    let svcName = c.service || c.name || 'Layanan Paket';
                    let descStr = (c.description && c.description !== c.service) ? ` [${c.description}]` : '';

                    let isCargo = /jtr|cargo|kargo|trucking/i.test(svcName + ' ' + (c.description || ''));
                    let isExpress = /yes|super|nextday|ons|express/i.test(svcName + ' ' + (c.description || ''));
                    let isReg = /reg|ez|standar|reguler/i.test(svcName + ' ' + (c.description || ''));

                    let badge = '';
                    if (isHeavyWeight) {
                        if (isCargo) {
                            badge = ' [Rekomendasi Hemat Kargo]';
                            if (recommendedIdx === -1) recommendedIdx = idx;
                        }
                    } else {
                        if (isReg && !isCargo && !isExpress) {
                            badge = ' [Rekomendasi Standar]';
                            if (recommendedIdx === -1) recommendedIdx = idx;
                        }
                    }

                    html += `<option value="${idx}">${svcName}${descStr} - Rp ${costVal.toLocaleString('id-ID')}${etdStr}${badge}</option>`;
                });
                serviceSelect.innerHTML = html;
                if (recommendedIdx !== -1) {
                    serviceSelect.selectedIndex = recommendedIdx;
                }
                onServiceChange();
            } else { throw new Error("No costs returned"); }
        })
        .catch(() => {
            let weightKg = Math.max(1, Math.ceil(totalWeight / 1000));
            let regPrice = 15000 * weightKg;
            let yesPrice = 24000 * weightKg;
            let extraKg = Math.max(0, weightKg - 10);
            let truckPrice = 45000 + (extraKg * 3000);

            let isHeavy = (totalWeight >= 10000);

            availableCosts = [
                { service: 'REG (Reguler ' + weightKg + 'kg)', cost: regPrice, etd: '2-3 Hari' },
                { service: 'YES (Yakin Esok Sampai ' + weightKg + 'kg)', cost: yesPrice, etd: '1 Hari' },
                { service: 'JTR / KARGO (Trucking Logistik)', cost: truckPrice, etd: '4-7 Hari' }
            ];
            let html = `
                <option value="0"${!isHeavy ? ' selected' : ''}>REG (Reguler ${weightKg}kg) - Rp ${regPrice.toLocaleString('id-ID')} (2-3 Hari)${!isHeavy ? ' [Rekomendasi Standar]' : ''}</option>
                <option value="1">YES (Yakin Esok Sampai ${weightKg}kg) - Rp ${yesPrice.toLocaleString('id-ID')} (1 Hari)</option>
                <option value="2"${isHeavy ? ' selected' : ''}>JTR / KARGO (Trucking Logistik) - Rp ${truckPrice.toLocaleString('id-ID')} (4-7 Hari)${isHeavy ? ' [Rekomendasi Hemat Kargo]' : ''}</option>
            `;
            serviceSelect.innerHTML = html;
            serviceSelect.selectedIndex = isHeavy ? 2 : 0;
            onServiceChange();
        });
    }

    function onServiceChange() {
        let idx = document.getElementById('service_select').value;
        if (availableCosts[idx]) {
            let item = availableCosts[idx];
            currentOngkir = (typeof item.cost === 'number') ? item.cost : (item.cost && item.cost[0] ? item.cost[0].value : 15000);
            selectedServiceLabel = item.service || 'Ekspedisi Reguler';
        }
        updateTotalsDisplay();
    }

    function getCheckoutCart() {
        let cart = JSON.parse(localStorage.getItem('zc_cart') || '[]');
        let selected = cart.filter(i => i.selected !== false);
        return (selected.length > 0) ? selected : cart;
    }

    function syncCheckoutStorage(cart) {
        localStorage.setItem('zc_cart', JSON.stringify(cart));
        localStorage.setItem('zencare_cart', JSON.stringify(cart));
        if (typeof cartData !== 'undefined') {
            cartData = cart;
        }
        if (typeof renderCart === 'function') {
            renderCart();
        }
    }

    function updateTotalsDisplay() {
        let cart = getCheckoutCart();
        let subtotal = cart.reduce((sum, i) => sum + ((parseFloat(i.harga) || 0) * (parseInt(i.qty) || 1)), 0);
        let totalWeight = cart.reduce((sum, i) => sum + ((parseInt(i.berat) || 100) * (parseInt(i.qty) || 1)), 0);
        let grandTotal = subtotal + currentOngkir;

        let weightKg = (totalWeight / 1000).toFixed(1);
        let weightBadge = '';
        if (totalWeight >= 10000) {
            weightBadge = `<span class="inline-block text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 ml-1.5 border border-amber-300">Muatan Berat (Kargo)</span>`;
        } else if (totalWeight > 0) {
            weightBadge = `<span class="inline-block text-[10px] bg-[#eef6fb] text-[#0f2d5a] font-semibold px-2 py-0.5 ml-1.5 border border-[#cbe4f5]">Paket Reguler</span>`;
        }

        document.getElementById('disp_total_weight').innerHTML = `${totalWeight.toLocaleString('id-ID')} Gram (${weightKg} Kg) ${weightBadge}`;
        document.getElementById('disp_subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('disp_ongkir').innerText = 'Rp ' + currentOngkir.toLocaleString('id-ID');
        document.getElementById('disp_grand_total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    function updateItemQty(id, delta) {
        let cart = JSON.parse(localStorage.getItem('zc_cart') || '[]');
        let item = cart.find(i => (i.cartId || i.id) == id);
        if (item) {
            let newQty = (parseInt(item.qty) || 1) + delta;
            if (newQty < 1 && delta < 0) {
                alert('Minimal 1. Jika ingin membatalkan item ini, silakan klik tombol hapus (✕).');
                return;
            }
            let maxStok = item.max || item.maxStokBox || item.stokBox || 9999;
            if (delta > 0 && newQty > maxStok) {
                alert('Stok ' + (item.satuan || item.satuan_besar || item.satuan_label || 'unit') + ' hanya tersisa ' + maxStok + '.');
                return;
            }
            item.qty = newQty;
            syncCheckoutStorage(cart);
            renderCartItems();
            calculateShippingCost();
        }
    }

    function removeCheckoutItem(id) {
        let cart = JSON.parse(localStorage.getItem('zc_cart') || '[]');
        cart = cart.filter(i => (i.cartId || i.id) != id);
        syncCheckoutStorage(cart);
        renderCartItems();
        calculateShippingCost();
    }

    function resolveCheckoutImg(gbr) {
        if (!gbr) return '../assets/img/no-image.png';
        if (typeof gbr === 'string' && gbr.includes(',')) {
            gbr = gbr.split(',')[0].trim();
        }
        if (gbr.startsWith('http')) return gbr;
        let clean = gbr.replace(/^\.\.\//, '');
        if (!clean) return '../assets/img/no-image.png';
        if (!clean.includes('/')) {
            return '../assets/img/produk/' + clean;
        }
        return '../' + clean;
    }

    function renderCartItems() {
        let cart = getCheckoutCart();
        let container = document.getElementById('cart_summary_list');

        if (!Array.isArray(cart) || cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 bg-white border border-[#cbe4f5] p-4">
                    <p class="text-xs text-slate-500 mb-3">Tidak ada produk yang dipilih untuk checkout.</p>
                    <a href="cart.php" class="inline-block text-xs font-bold uppercase tracking-wider px-5 py-2.5 bg-[#0f2d5a] text-white">Buka Keranjang</a>
                </div>
            `;
            let btnNanti = document.getElementById('btn_checkout_nanti');
            let btnSekarang = document.getElementById('btn_checkout_sekarang');
            if (btnNanti) btnNanti.disabled = true;
            if (btnSekarang) {
                btnSekarang.disabled = true;
                btnSekarang.className = "w-full bg-slate-200 text-slate-400 font-bold text-xs uppercase tracking-widest py-4 px-4 cursor-not-allowed";
            }
            updateTotalsDisplay();
            return;
        }

        let html = '';
        cart.forEach(item => {
            let price = parseFloat(item.harga) || 0;
            let qty = parseInt(item.qty) || 1;
            let weight = parseInt(item.berat) || 100;
            let satLabel = item.satuan || item.satuan_label || item.satuan_besar || 'Box';
            let itemName = item.nama || item.name || 'Produk';
            let maxStok = parseInt(item.max) || 0;
            let itemImg = resolveCheckoutImg(item.gambar || item.image || '');

            html += `
                <div class="flex items-center justify-between text-xs bg-white border border-[#cbe4f5] p-3 gap-3">
                    <div class="w-14 h-14 bg-[#f6f8fa] border border-slate-200/80 p-1.5 flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="${itemImg}" class="w-full h-full object-contain mix-blend-multiply" alt="${itemName}" onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-[#0f2d5a] block truncate leading-snug">${itemName}</span>
                        <span class="text-slate-500 text-[11px] font-medium">Rp ${price.toLocaleString('id-ID')} / ${satLabel} (${weight}g)</span>
                        <span class="text-[10px] font-semibold ${maxStok <= 0 ? 'text-rose-600' : (maxStok <= 5 ? 'text-amber-600' : 'text-emerald-700')} block mt-0.5">
                            Stok Tersedia: ${maxStok > 0 ? maxStok + ' ' + satLabel : 'Habis'}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="flex items-center border border-slate-200 bg-[#f8fafc] h-7">
                            <button type="button" onclick="updateItemQty('${item.cartId || item.id}', -1)" class="w-6 h-full text-[#0f2d5a] font-bold text-xs hover:bg-slate-200/70 flex items-center justify-center cursor-pointer">−</button>
                            <span class="font-bold text-xs px-2 text-[#0f2d5a]">${qty}</span>
                            <button type="button" onclick="updateItemQty('${item.cartId || item.id}', 1)" class="w-6 h-full text-[#0f2d5a] font-bold text-xs hover:bg-slate-200/70 flex items-center justify-center cursor-pointer">+</button>
                        </div>
                        <button type="button" onclick="removeCheckoutItem('${item.cartId || item.id}')" class="text-slate-400 hover:text-rose-600 font-bold p-1 text-xs cursor-pointer" title="Hapus Produk">✕</button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        updateTotalsDisplay();
    }

    function clearPurchasedFromCart(purchasedItems) {
        try {
            let allCart = JSON.parse(localStorage.getItem('zc_cart') || '[]');
            let purchasedKeys = new Set(purchasedItems.map(p => String(p.cartId || p.id || '')));
            let remainingCart = allCart.filter(item => !purchasedKeys.has(String(item.cartId || item.id || '')));
            syncCheckoutStorage(remainingCart);
        } catch (e) {
            console.error('Error clearing purchased cart items:', e);
        }
    }

    let selectedPayCategory = 'midtrans';

    function prosesCheckout(isPayNow) {
        let nama = document.getElementById('nama_pembeli').value.trim();
        let phone = document.getElementById('phone').value.trim();
        let alamat = document.getElementById('alamat_lengkap').value.trim();
        let provSelect = document.getElementById('provinsi_select');
        let citySelect = document.getElementById('kota_select');
        let courier = document.getElementById('courier_select').value;
        let saveAddress = document.getElementById('save_profile_address')?.checked ?? false;
        let cart = getCheckoutCart();

        if (!nama || !phone || !alamat) {
            alert('Harap lengkapi nama penerima, nomor telepon, dan alamat pengiriman!');
            return;
        }

        if (!Array.isArray(cart) || cart.length === 0) {
            alert('Keranjang belanja kosong!');
            return;
        }

        let btnNanti = document.getElementById('btn_checkout_nanti');
        let btnSekarang = document.getElementById('btn_checkout_sekarang');
        if (btnNanti) btnNanti.disabled = true;
        if (btnSekarang) {
            btnSekarang.disabled = true;
            if (isPayNow) {
                btnSekarang.innerHTML = '<svg class="w-4 h-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg><span>MEMPROSES PEMBAYARAN...</span>';
            }
        }

        let payload = {
            nama_pembeli: nama,
            phone: phone,
            alamat_lengkap: alamat,
            provinsi: provSelect.options[provSelect.selectedIndex]?.text || 'JAWA TIMUR',
            kota: citySelect.options[citySelect.selectedIndex]?.text || 'MALANG',
            kota_id: citySelect.value,
            kecamatan: 'Pusat',
            lat: selectedLat,
            lng: selectedLng,
            save_profile: saveAddress,
            cart: cart,
            kurir: courier,
            layanan: selectedServiceLabel,
            ongkir: currentOngkir,
            kategori_pembayaran: selectedPayCategory
        };

        fetch('../api/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (btnNanti) {
                btnNanti.disabled = false;
                btnNanti.innerText = 'Buat Pesanan (Bayar Nanti)';
            }
            if (btnSekarang) {
                btnSekarang.disabled = false;
                btnSekarang.innerHTML = '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg><span>BAYAR SEKARANG</span>';
            }

            if (data.status === 'success' && data.token) {
                if (isPayNow) {
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            let pType = result && result.payment_type ? result.payment_type : '';
                            fetch('../api/sync_payment.php?order_id=' + encodeURIComponent(data.order_id) + '&payment_type=' + encodeURIComponent(pType))
                                .then(res => res.json())
                                .then(() => {
                                    alert('Pembayaran Berhasil! Order ID: ' + data.order_id);
                                    clearPurchasedFromCart(cart);
                                    window.location.href = 'profil.php';
                                }).catch(() => {
                                    alert('Pembayaran Berhasil! Order ID: ' + data.order_id);
                                    clearPurchasedFromCart(cart);
                                    window.location.href = 'profil.php';
                                });
                        },
                        onPending: function(result) {
                            let pType = result && result.payment_type ? result.payment_type : '';
                            fetch('../api/sync_payment.php?order_id=' + encodeURIComponent(data.order_id) + '&payment_type=' + encodeURIComponent(pType))
                                .finally(() => {
                                    alert('Menunggu Pembayaran. Order ID: ' + data.order_id + (pType ? ' (' + pType.toUpperCase() + ')' : ''));
                                    clearPurchasedFromCart(cart);
                                    window.location.href = 'profil.php';
                                });
                        },
                        onError: function() {
                            alert('Transaksi Dibatalkan / Gagal.');
                            clearPurchasedFromCart(cart);
                            window.location.href = 'profil.php';
                        },
                        onClose: function() {
                            alert('Anda menutup jendela pembayaran. Pesanan Anda tersimpan dan dapat diselesaikan melalui menu Riwayat Pesanan di Akun Pelanggan.');
                            clearPurchasedFromCart(cart);
                            window.location.href = 'profil.php';
                        }
                    });
                } else {
                    alert('Pesanan berhasil dibuat! Anda dapat membayarnya nanti melalui Profil Anda.');
                    clearPurchasedFromCart(cart);
                    window.location.href = 'profil.php';
                }
            } else {
                alert('Gagal Checkout: ' + (data.message || 'Error tidak diketahui'));
            }
        })
        .catch(err => {
            if (btnNanti) {
                btnNanti.disabled = false;
                btnNanti.innerText = 'Buat Pesanan (Bayar Nanti)';
            }
            if (btnSekarang) {
                btnSekarang.disabled = false;
                btnSekarang.innerHTML = '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg><span>BAYAR SEKARANG</span>';
            }
            alert('Terjadi kesalahan jaringan: ' + err);
        });
    }

    async function validateLiveStock() {
        let cart = JSON.parse(localStorage.getItem('zc_cart') || '[]');
        if (!Array.isArray(cart) || cart.length === 0) return;
        try {
            let res = await fetch('../api/cek_stok_keranjang.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart })
            });
            let data = await res.json();
            if (data.status === 'success') {
                let cartNeedsSave = false;
                if (data.stock_map) {
                    cart.forEach(item => {
                        let itemKey = item.cartId || item.id;
                        let stockInfo = data.stock_map[itemKey] || data.stock_map[item.id];
                        if (stockInfo) {
                            item.max = stockInfo.max_dus;
                            if ((!item.gambar || item.gambar.includes('no-image.png')) && stockInfo.gambar) {
                                item.gambar = stockInfo.gambar;
                                item.image = stockInfo.gambar;
                                cartNeedsSave = true;
                            }
                        }
                    });
                    if (cartNeedsSave) {
                        syncCheckoutStorage(cart);
                        renderCartItems();
                    }
                }
                if (data.has_adjustments && data.adjusted_items.length > 0) {
                    let msgList = [];
                    data.adjusted_items.forEach(adj => {
                        let idx = cart.findIndex(i => (i.cartId || i.id) == adj.id);
                        if (idx > -1) {
                            if (adj.new_qty <= 0) {
                                cart.splice(idx, 1);
                                msgList.push(`• <b>${adj.nama}</b>: Habis terjual, produk dihapus dari keranjang.`);
                            } else {
                                cart[idx].qty = adj.new_qty;
                                cart[idx].max = adj.max_dus;
                                msgList.push(`• <b>${adj.nama}</b>: Stok fisik tersisa ${adj.max_dus} ${adj.satuan}. Jumlah disesuaikan menjadi ${adj.new_qty}.`);
                            }
                        }
                    });
                    syncCheckoutStorage(cart);
                    let alertBox = document.getElementById('stock_sync_alert');
                    let alertText = document.getElementById('stock_sync_text');
                    if (alertBox && alertText) {
                        alertText.innerHTML = `<strong>Penyesuaian Stok Terkini:</strong><br>${msgList.join('<br>')}`;
                        alertBox.classList.remove('hidden');
                    }
                    renderCartItems();
                    calculateShippingCost();
                }
            }
        } catch (e) {
            console.warn('Gagal sinkronisasi stok live:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', async function() {
        initLeafletMap();
        renderCartItems();
        loadProvincesAPI();
        await validateLiveStock();
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

