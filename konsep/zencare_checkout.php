<?php
// File: zencare_checkout.php
// Modul Checkout & Pembayaran E-Commerce ZenCare Medical (Brand System v3)
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

$activeCabangId = $_SESSION['id_cabang'] ?? 1;

$stmtCabang = $pdo->prepare("SELECT * FROM cabang WHERE id = ? AND is_active = 1");
$stmtCabang->execute([$activeCabangId]);
$cabangAktif = $stmtCabang->fetch();
$originKotaId = $cabangAktif['kota_id'] ?? 256;

// Determine branch lat/lng coordinates
$branchLat = -7.9881;
$branchLng = 112.6371;
if ($activeCabangId == 2 || (isset($cabangAktif['nama']) && strpos(strtolower($cabangAktif['nama']), 'borobudur') !== false)) {
    $branchLat = -7.9400;
    $branchLng = 112.6258;
}

// Fetch saved user profile data if logged in
$savedUser = null;
if (isset($_SESSION['user_id'])) {
    $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['user_id']]);
    $savedUser = $uStmt->fetch();
}

// Check API RajaOngkir toggle
$chkRajaOngkir = $pdo->prepare("SELECT is_active FROM pengaturan_api WHERE id_cabang=? AND platform='rajaongkir'");
$chkRajaOngkir->execute([$activeCabangId]);
$apiRow = $chkRajaOngkir->fetchColumn();
$rajaongkirEnabled = ($apiRow === false) ? true : (bool)$apiRow;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout – ZenCare Medical Store</title>

    <!-- Tailwind CSS (ZenCare Brand Theme) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              zc:    '#1a75d2',
              zcHv:  '#1562b3',
              zcLt:  '#e8f2ff',
              zcEm:  '#059669',
              zcBrd: '#e4e9f0',
              zcTxt: '#1e293b',
              zcMut: '#64748b',
            }
          }
        }
      }
    </script>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Midtrans Snap Sandbox JS -->
    <script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
</head>
<body class="bg-[#f5f7fa] text-zcTxt font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-zcBrd sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-zc flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <a href="zencare_store.php" class="font-semibold text-zcTxt text-sm">ZenCare <span class="text-zc">Checkout</span></a>
            </div>
            <a href="zencare_store.php" class="text-xs font-medium text-zcMut hover:text-zcTxt border border-zcBrd px-3 py-1.5 rounded-lg bg-white transition flex items-center gap-1">
                &laquo; Kembali ke Catalog Store
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
        
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-zcTxt">Checkout Pembayaran</h1>
                <p class="text-xs text-zcMut mt-0.5">Lengkapi alamat pengiriman dan pilih ekspedisi kurir</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-zcLt text-zc rounded-full border border-zc/20">
                Pengirim: <?= htmlspecialchars($cabangAktif['nama'] ?? 'ZenCare Malang') ?>
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Panel: Form & Shipping -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- 1. Identitas Penerima -->
                <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-zcTxt border-b border-zcBrd pb-3 mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-zcLt text-zc text-xs font-bold flex items-center justify-center">1</span>
                            Identitas Penerima
                        </div>
                        <?php if ($savedUser): ?>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">✓ Profil Otomatis Terisi</span>
                        <?php endif; ?>
                    </h2>
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-medium text-zcTxt mb-1.5">Nama Lengkap Penerima *</label>
                            <input type="text" id="nama_pembeli" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 transition"
                                value="<?= htmlspecialchars($savedUser['nama_lengkap'] ?? $_SESSION['nama_lengkap'] ?? '') ?>" placeholder="Contoh: Budi Santoso">
                        </div>
                        <div>
                            <label class="block font-medium text-zcTxt mb-1.5">Nomor HP / WhatsApp *</label>
                            <input type="text" id="phone" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 transition"
                                value="<?= htmlspecialchars($savedUser['telepon'] ?? '') ?>" placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                </div>

                <!-- 2. Alamat & RajaOngkir API -->
                <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-zcTxt border-b border-zcBrd pb-3 mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-zcLt text-zc text-xs font-bold flex items-center justify-center">2</span>
                            Alamat &amp; Integrasi Kurir Pengiriman
                        </div>
                        <?php if (!$rajaongkirEnabled): ?>
                            <span class="text-[10px] font-semibold px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full">API RajaOngkir OFF</span>
                        <?php endif; ?>
                    </h2>
                    
                    <div class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-zcTxt mb-1.5">Provinsi Tujuan *</label>
                                <select id="provinsi_select" onchange="loadCitiesAPI()" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc transition">
                                    <option value="">-- Memuat Provinsi... --</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-zcTxt mb-1.5">Kota / Kabupaten Tujuan *</label>
                                <select id="kota_select" onchange="onCityChange()" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc transition" disabled>
                                    <option value="">-- Pilih Provinsi Dahulu --</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-zcTxt mb-1.5">Alamat Lengkap *</label>
                            <textarea id="alamat_lengkap" rows="2" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc transition"
                                placeholder="Jalan, No. Rumah, RT/RW, Kecamatan"><?= htmlspecialchars($savedUser['alamat'] ?? '') ?></textarea>
                        </div>

                        <div id="malang_notice" class="hidden p-3 bg-zcLt border border-zc/20 rounded-xl text-zc text-xs leading-relaxed">
                            💡 <strong>Area Dalam Kota (Malang) Terdeteksi!</strong> Pengiriman menggunakan <strong>Kurir Internal ZenCare Direct</strong> (Lebih cepat &amp; hemat). Pilihan ekspedisi luar kota disembunyikan.
                        </div>

                        <div id="luar_kota_notice" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs leading-relaxed">
                            📦 <strong>Area Luar Kota Terdeteksi!</strong> Pengiriman menggunakan <strong>Ekspedisi Nasional (JNE / POS Indonesia)</strong> via API RajaOngkir. Kurir internal disembunyikan &amp; peta bergeser ke lokasi tujuan.
                        </div>

                        <div>
                            <label class="block font-medium text-zcTxt mb-1.5">Pilihan Kurir Pengiriman *</label>
                            <select id="courier_select" onchange="calculateShippingCost()" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc transition">
                                <!-- Populated dynamically by JS -->
                            </select>
                        </div>

                        <div id="service_container" class="hidden">
                            <label class="block font-medium text-zcTxt mb-1.5">Layanan Paket Ekspedisi *</label>
                            <select id="service_select" onchange="onServiceChange()" class="w-full border border-zcBrd rounded-xl p-2.5 bg-white focus:outline-none focus:border-zc transition">
                                <option value="">-- Memuat Layanan Paket... --</option>
                            </select>
                        </div>

                        <!-- Leaflet Interactive Map -->
                        <div id="map_panel" class="space-y-2 pt-3 border-t border-zcBrd">
                            <label class="block font-semibold text-zcTxt">Titik Penanda Lokasi Pengiriman (Leaflet Map Pin)</label>
                            <div id="map" class="w-full h-52 border border-zcBrd rounded-xl"></div>
                            <div class="flex justify-between text-[11px] text-zcMut pt-1">
                                <span>Lat: <strong id="disp_lat" class="text-zcTxt"><?= $savedUser['lat'] ?? $branchLat ?></strong>, Lng: <strong id="disp_lng" class="text-zcTxt"><?= $savedUser['lng'] ?? $branchLng ?></strong></span>
                                <span id="distance_info" class="font-bold text-zc">Jarak: ~0 km</span>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="pt-2">
                                <label class="flex items-center gap-2 text-xs font-medium text-zcTxt cursor-pointer">
                                    <input type="checkbox" id="save_profile_address" checked class="rounded border-zcBrd text-zc focus:ring-zc">
                                    Simpan alamat &amp; titik peta ini sebagai alamat utama profil saya
                                </label>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>

            <!-- Right Panel: Cart Summary & Midtrans Payment Button -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm sticky top-20">
                    <div class="flex items-center justify-between border-b border-zcBrd pb-3 mb-4">
                        <h2 class="text-sm font-bold text-zcTxt flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-zcLt text-zc text-xs font-bold flex items-center justify-center">3</span>
                            Ringkasan Belanja
                        </h2>
                        <span class="text-[11px] text-zcMut">Edit Qty / Hapus Item</span>
                    </div>
                    
                    <!-- Interactive Editable Cart Summary List -->
                    <div id="cart_summary_list" class="space-y-3 max-h-64 overflow-y-auto pr-1 mb-4">
                        <!-- Loaded via JavaScript -->
                    </div>

                    <div class="border-t border-zcBrd pt-4 space-y-2 text-xs">
                        <div class="flex justify-between text-zcMut">
                            <span>Total Berat:</span>
                            <span id="disp_total_weight" class="font-semibold text-zcTxt">0 Gram</span>
                        </div>
                        <div class="flex justify-between text-zcMut">
                            <span>Subtotal Barang:</span>
                            <span id="disp_subtotal" class="font-semibold text-zcTxt">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-zcMut">
                            <span>Ongkos Kirim:</span>
                            <span id="disp_ongkir" class="font-semibold text-zcTxt">Rp 10.000</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-zcTxt border-t border-zcBrd pt-3">
                            <span>Grand Total:</span>
                            <span id="disp_grand_total" class="text-zcEm text-base">Rp 0</span>
                        </div>
                    </div>

                    <button onclick="prosesCheckoutMidtrans()" id="btn_checkout" class="w-full mt-6 bg-zc hover:bg-zcHv text-white font-semibold text-xs py-3.5 px-4 rounded-xl transition shadow-sm">
                        Bayar Sekarang (Midtrans Snap Sandbox)
                    </button>
                </div>
            </div>

        </div>
    </main>

    <footer class="bg-white border-t border-zcBrd py-4 text-center text-xs text-zcMut mt-8">
        ZenCare Medical Store &bull; Midtrans Sandbox Payment Gateway
    </footer>

    <!-- Script Leaflet JS Map, Cart, & Midtrans Integration -->
    <script>
        const ORIGIN_KOTA_ID = "<?= $originKotaId ?>";
        const RAJAONGKIR_ENABLED = <?= $rajaongkirEnabled ? 'true' : 'false' ?>;
        const BRANCH_LAT = <?= $branchLat ?>;
        const BRANCH_LNG = <?= $branchLng ?>;
        const SAVED_LAT = <?= $savedUser['lat'] ?? 'null' ?>;
        const SAVED_LNG = <?= $savedUser['lng'] ?? 'null' ?>;
        const SAVED_KOTA_ID = <?= $savedUser['kota_id'] ?? 'null' ?>;

        // City Map Coordinates Dictionary
        const CITY_MAP_COORDS = {
            '391': [-7.9666, 112.6326], // Malang
            '256': [-7.9666, 112.6326], // Malang
            '393': [-7.8702, 112.5271], // Batu
            '577': [-7.2575, 112.7521], // Surabaya
            '583': [-7.4478, 112.7183], // Sidoarjo
            '256': [-8.1721, 113.7001], // Jember
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
                
                let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');
                let totalWeight = cart.reduce((sum, item) => sum + ((parseInt(item.weight) || 100) * (parseInt(item.qty) || 1)), 0);

                let baseFare = 10000;
                let distFare = dist > 2 ? Math.round((dist - 2) * 3000) : 0;
                let weightKg = Math.ceil(totalWeight / 1000);
                let weightFare = weightKg > 1 ? (weightKg - 1) * 2000 : 0;

                currentOngkir = baseFare + distFare + weightFare;
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
            
            fetch('api/rajaongkir.php?action=provinces')
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
                    loadCitiesAPI();
                });
        }

        function loadCitiesAPI() {
            let provId = document.getElementById('provinsi_select').value || '18';
            let citySelect = document.getElementById('kota_select');
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">Memuat kota...</option>';

            fetch('api/rajaongkir.php?action=cities&province_id=' + provId)
                .then(res => res.json())
                .then(data => {
                    let list = data.data || data.results || [];
                    if (Array.isArray(list) && list.length > 0) {
                        let html = '<option value="">-- Pilih Kota / Kabupaten --</option>';
                        list.forEach(c => {
                            let id = c.city_id || c.id;
                            let name = (c.type ? c.type + ' ' : '') + (c.city_name || c.name);
                            let isSelected = (SAVED_KOTA_ID && SAVED_KOTA_ID == id) || (!SAVED_KOTA_ID && (name.toLowerCase().includes('malang') || id == 391 || id == 256));
                            html += `<option value="${id}" ${isSelected ? 'selected' : ''}>${name}</option>`;
                        });
                        citySelect.innerHTML = html;
                        citySelect.disabled = false;
                        onCityChange();
                    } else { throw new Error("No cities"); }
                })
                .catch(() => {
                    citySelect.innerHTML = `
                        <option value="391" selected>MALANG</option>
                        <option value="577">SURABAYA</option>
                        <option value="583">SIDOARJO</option>
                        <option value="393">BATU</option>
                    `;
                    citySelect.disabled = false;
                    onCityChange();
                });
        }

        function onCityChange() {
            let citySelect = document.getElementById('kota_select');
            let cityVal = citySelect.value;
            let cityName = citySelect.options[citySelect.selectedIndex]?.text || '';
            let courierSelect = document.getElementById('courier_select');

            let isMalang = cityName.toLowerCase().includes('malang');

            if (isMalang) {
                // Dalam Kota Malang: Hanya Kurir Internal ZenCare Direct
                document.getElementById('malang_notice').classList.remove('hidden');
                document.getElementById('luar_kota_notice').classList.add('hidden');
                
                courierSelect.innerHTML = '<option value="internal">Kurir Internal ZenCare Direct (Khusus Malang)</option>';
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
                // Luar Kota: Sembunyikan Kurir Internal, Tampilkan JNE & POS
                document.getElementById('malang_notice').classList.add('hidden');
                document.getElementById('luar_kota_notice').classList.remove('hidden');

                courierSelect.innerHTML = `
                    <option value="jne">Ekspedisi JNE Express</option>
                    <option value="pos">Ekspedisi POS Indonesia</option>
                `;
                courierSelect.value = 'jne';

                // Automatically center Leaflet map to selected city coordinates
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

            if (courier === 'internal') {
                serviceContainer.classList.add('hidden');
                onMarkerMove(selectedLat, selectedLng);
                return;
            }

            serviceContainer.classList.remove('hidden');
            let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');
            let totalWeight = cart.reduce((sum, item) => sum + ((parseInt(item.weight) || 100) * (parseInt(item.qty) || 1)), 0);
            if (totalWeight <= 0) totalWeight = 1000;

            let serviceSelect = document.getElementById('service_select');
            serviceSelect.innerHTML = '<option value="">Kalkulasi Ongkir API RajaOngkir...</option>';

            let formData = new FormData();
            formData.append('origin', ORIGIN_KOTA_ID);
            formData.append('destination', destCityId);
            formData.append('weight', totalWeight);
            formData.append('courier', courier);

            fetch('api/rajaongkir.php?action=cost', {
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
                    costs.forEach((c, idx) => {
                        let costVal = (typeof c.cost === 'number') ? c.cost : (c.cost && c.cost[0] ? (c.cost[0].value || 0) : 0);
                        let etdStr = c.etd ? ` (Estimasi: ${c.etd})` : (c.cost && c.cost[0] && c.cost[0].etd ? ` (Estimasi: ${c.cost[0].etd} Hari)` : '');
                        let svcName = c.service || c.name || 'Layanan Paket';
                        let descStr = (c.description && c.description !== c.service) ? ` [${c.description}]` : '';
                        html += `<option value="${idx}">${svcName}${descStr} - Rp ${costVal.toLocaleString('id-ID')}${etdStr}</option>`;
                    });
                    serviceSelect.innerHTML = html;
                    onServiceChange();
                } else { throw new Error("No costs returned"); }
            })
            .catch(() => {
                let weightKg = Math.max(1, Math.ceil(totalWeight / 1000));
                let regPrice = 15000 * weightKg;
                let yesPrice = 24000 * weightKg;

                availableCosts = [
                    { service: 'REG (Reguler ' + weightKg + 'kg)', cost: regPrice, etd: '2-3 Hari' },
                    { service: 'YES (Yakin Esok Sampai ' + weightKg + 'kg)', cost: yesPrice, etd: '1 Hari' }
                ];
                let html = `
                    <option value="0">REG (Reguler ${weightKg}kg) - Rp ${regPrice.toLocaleString('id-ID')} (2-3 Hari)</option>
                    <option value="1">YES (Yakin Esok Sampai ${weightKg}kg) - Rp ${yesPrice.toLocaleString('id-ID')} (1 Hari)</option>
                `;
                serviceSelect.innerHTML = html;
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

        function updateTotalsDisplay() {
            let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');
            let subtotal = cart.reduce((sum, i) => sum + ((parseFloat(i.price) || 0) * (parseInt(i.qty) || 1)), 0);
            let totalWeight = cart.reduce((sum, i) => sum + ((parseInt(i.weight) || 100) * (parseInt(i.qty) || 1)), 0);
            let grandTotal = subtotal + currentOngkir;

            document.getElementById('disp_total_weight').innerText = totalWeight + ' Gram';
            document.getElementById('disp_subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('disp_ongkir').innerText = 'Rp ' + currentOngkir.toLocaleString('id-ID');
            document.getElementById('disp_grand_total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        // Cart Management Functions (Edit Qty / Delete Item)
        function updateItemQty(id, delta) {
            let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');
            let item = cart.find(i => i.id === id);
            if (item) {
                let minOrder = parseInt(item.min_order_online) || 1;
                let newQty = (parseInt(item.qty) || 1) + delta;
                if (newQty < minOrder && delta < 0) {
                    alert('Batas minimum pembelian grosir untuk produk ini adalah ' + minOrder + ' Pcs. Jika ingin membatalkan, silakan klik tombol hapus (✕).');
                    return;
                }
                item.qty = newQty;
                localStorage.setItem('zencare_cart', JSON.stringify(cart));
                renderCartItems();
                calculateShippingCost();
            }
        }

        function removeCartItem(id) {
            let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');
            cart = cart.filter(i => i.id !== id);
            localStorage.setItem('zencare_cart', JSON.stringify(cart));
            renderCartItems();
            calculateShippingCost();
        }

        function renderCartItems() {
            let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');
            let container = document.getElementById('cart_summary_list');

            if (!Array.isArray(cart) || cart.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-6">
                        <p class="text-xs text-zcMut mb-3">Keranjang belanja Anda kosong.</p>
                        <a href="zencare_store.php" class="inline-block text-xs font-semibold px-4 py-2 bg-zc text-white rounded-xl">Lihat Produk Store</a>
                    </div>
                `;
                document.getElementById('btn_checkout').disabled = true;
                document.getElementById('btn_checkout').className = "w-full mt-6 bg-slate-200 text-slate-400 font-semibold text-xs py-3.5 px-4 rounded-xl cursor-not-allowed";
                return;
            }

            let html = '';
            cart.forEach(item => {
                let price = parseFloat(item.price) || 0;
                let qty = parseInt(item.qty) || 1;
                let weight = parseInt(item.weight) || 100;
                let sub = price * qty;
                let minOrder = parseInt(item.min_order_online) || 1;

                html += `
                    <div class="flex items-center justify-between text-xs border-b border-zcBrd/60 pb-2.5">
                        <div class="pr-2 min-w-0 flex-1">
                            <span class="font-bold text-zcTxt block truncate leading-snug">${item.name}</span>
                            <span class="text-zcMut text-[11px]">Rp ${price.toLocaleString('id-ID')} (${weight}g)</span>
                            <span class="text-amber-700 bg-amber-50 px-1 py-0.5 rounded text-[9px] inline-block font-medium mt-1">Min: ${minOrder} pcs</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="flex items-center gap-1 bg-slate-100 border border-zcBrd rounded-lg px-1.5 py-0.5">
                                <button onclick="updateItemQty(${item.id}, -1)" class="w-4 h-4 text-zc font-bold text-xs hover:bg-slate-200 rounded flex items-center justify-center">-</button>
                                <span class="font-bold text-xs px-1">${qty}</span>
                                <button onclick="updateItemQty(${item.id}, 1)" class="w-4 h-4 text-zc font-bold text-xs hover:bg-slate-200 rounded flex items-center justify-center">+</button>
                            </div>
                            <button onclick="removeCartItem(${item.id})" class="text-rose-500 hover:text-rose-700 font-bold p-1 text-xs" title="Hapus Barang">✕</button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            updateTotalsDisplay();
        }

        function prosesCheckoutMidtrans() {
            let nama = document.getElementById('nama_pembeli').value.trim();
            let phone = document.getElementById('phone').value.trim();
            let alamat = document.getElementById('alamat_lengkap').value.trim();
            let provSelect = document.getElementById('provinsi_select');
            let citySelect = document.getElementById('kota_select');
            let courier = document.getElementById('courier_select').value;
            let saveAddress = document.getElementById('save_profile_address')?.checked ?? false;
            let cart = JSON.parse(localStorage.getItem('zencare_cart') || '[]');

            if (!nama || !phone || !alamat) {
                alert('Harap lengkapi nama penerima, nomor telepon, dan alamat pengiriman!');
                return;
            }

            if (!Array.isArray(cart) || cart.length === 0) {
                alert('Keranjang belanja kosong!');
                return;
            }

            let btn = document.getElementById('btn_checkout');
            btn.disabled = true;
            btn.innerText = 'Meminta Midtrans Snap Token...';

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
                ongkir: currentOngkir
            };

            fetch('api/checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Bayar Sekarang (Midtrans Snap Sandbox)';

                if (data.status === 'success' && data.token) {
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            alert('Pembayaran Berhasil! Order ID: ' + data.order_id);
                            localStorage.removeItem('zencare_cart');
                            window.location.href = 'zencare_store.php';
                        },
                        onPending: function(result) {
                            alert('Menunggu Pembayaran Midtrans. Order ID: ' + data.order_id);
                            localStorage.removeItem('zencare_cart');
                            window.location.href = 'zencare_store.php';
                        },
                        onError: function(result) {
                            alert('Transaksi Dibatalkan / Gagal.');
                        }
                    });
                } else {
                    alert('Gagal Checkout: ' + (data.message || 'Error tidak diketahui'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Bayar Sekarang (Midtrans Snap Sandbox)';
                alert('Terjadi kesalahan jaringan: ' + err);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initLeafletMap();
            renderCartItems();
            loadProvincesAPI();
        });
    </script>
</body>
</html>
