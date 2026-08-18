/* File: assets/js/app.js */

// 1. STATE MANAGEMENT
let cart = [];
let map = null;
let buyerMarker = null;
let currentOngkir = 0;
let namaLayananKurir = "";
let selectedDestinationId = ""; // Destination ID for RajaOngkir
let selectedLocationText = "";
let isMalangArea = false;

const CABANG_MUHARTO = { lat: -7.9881, lng: 112.6371, nama: 'Cabang Muharto' };
const CABANG_BOROBUDUR = { lat: -7.9400, lng: 112.6258, nama: 'Cabang Borobudur' };
const TARIF_PER_KM = 3000;
const TARIF_MINIMAL = 10000;

// Load cart from localStorage on init
document.addEventListener('DOMContentLoaded', () => {
    const savedCart = localStorage.getItem('zencare_cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartUI();
    }

    // Load Provinces for cascading dropdowns
    loadProvinces();

    // Autocomplete location search
    initAutocompleteSearch();
});

// 2. SHOPPING CART LOGIC
function addToCart(id, nama, harga, berat, gambar) {
    const existingItem = cart.find(item => item.id === id);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cart.push({ id, nama, harga, berat, gambar, qty: 1 });
    }
    
    // Add micro-animation effect to cart button
    const cartBtn = document.querySelector('.cart-icon-btn');
    cartBtn.classList.add('pulse');
    setTimeout(() => cartBtn.classList.remove('pulse'), 300);

    saveCart();
    updateCartUI();
    openCartPanel();
}

function updateQty(id, delta) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty <= 0) {
            removeFromCart(id);
            return;
        }
    }
    saveCart();
    updateCartUI();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    updateCartUI();
}

function saveCart() {
    localStorage.setItem('zencare_cart', JSON.stringify(cart));
}

function updateCartUI() {
    const badge = document.getElementById('cart-badge');
    const container = document.getElementById('cart-items-container');
    const totalItemsText = document.getElementById('total-items-text');
    
    let totalItems = 0;
    let subtotal = 0;
    let totalBerat = 0;
    
    container.innerHTML = '';
    
    cart.forEach(item => {
        totalItems += item.qty;
        subtotal += (item.harga * item.qty);
        totalBerat += (item.berat * item.qty);
        
        const itemHtml = `
            <div class="cart-item">
                <img src="${item.gambar}" alt="${item.nama}" class="cart-item-img">
                <div class="cart-item-info">
                    <h4 class="cart-item-title">${item.nama}</h4>
                    <p class="cart-item-price">${formatRupiah(item.harga)}</p>
                    <div class="cart-item-qty">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${item.id})">
                    ✕
                </button>
            </div>
        `;
        container.innerHTML += itemHtml;
    });

    badge.innerText = totalItems;
    totalItemsText.innerText = totalItems + " Item";
    
    document.getElementById('cart-subtotal').innerText = formatRupiah(subtotal);
    
    // Update summary in Checkout Panel if open
    updateCheckoutSummary(subtotal, totalBerat);
}

function openCartPanel() {
    document.getElementById('cart-overlay').classList.add('open');
}

function closeCartPanel() {
    document.getElementById('cart-overlay').classList.remove('open');
}

// 3. CHECKOUT VIEW CONTROL
function showCheckoutPanel() {
    if (cart.length === 0) {
        alert("Keranjang belanja Anda kosong!");
        return;
    }
    closeCartPanel();
    const panel = document.getElementById('checkout-panel');
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth' });

    // Initialize Map after layout settles
    setTimeout(initMap, 300);
}

function initMap() {
    if (map) return;

    // Malang default view
    map = L.map('map').setView([-7.9839, 112.6213], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Markers for Zencare branches
    L.marker([CABANG_MUHARTO.lat, CABANG_MUHARTO.lng])
        .addTo(map)
        .bindPopup(`<b>${CABANG_MUHARTO.nama}</b><br>Jl. Muharto, Malang`)
        .openPopup();

    L.marker([CABANG_BOROBUDUR.lat, CABANG_BOROBUDUR.lng])
        .addTo(map)
        .bindPopup(`<b>${CABANG_BOROBUDUR.nama}</b><br>Jl. Borobudur, Malang`);

    // Map Click Listener
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);

        if (buyerMarker) {
            buyerMarker.setLatLng(e.latlng);
        } else {
            // Place marker with red design pin
            buyerMarker = L.marker(e.latlng, {
                draggable: true
            }).addTo(map);

            buyerMarker.on('dragend', function(event) {
                const marker = event.target;
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat.toFixed(6);
                document.getElementById('longitude').value = position.lng.toFixed(6);
                checkDeliveryLogic();
            });
        }
        checkDeliveryLogic();
    });
}

async function geocodeAndPan(queryText) {
    if (!queryText) return;
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(queryText)}&format=json&limit=1`);
        const data = await res.json();
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lon = parseFloat(data[0].lon);
            if (map) {
                map.setView([lat, lon], 12);
                if (buyerMarker) {
                    buyerMarker.setLatLng([lat, lon]);
                } else {
                    buyerMarker = L.marker([lat, lon], { draggable: true }).addTo(map);
                    buyerMarker.on('dragend', function(event) {
                        const marker = event.target;
                        const position = marker.getLatLng();
                        document.getElementById('latitude').value = position.lat.toFixed(6);
                        document.getElementById('longitude').value = position.lng.toFixed(6);
                        if (isMalangArea) {
                            calculateInternalDistance();
                        }
                    });
                }
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lon.toFixed(6);
                
                if (isMalangArea) {
                    calculateInternalDistance();
                } else {
                    document.getElementById('distance-info').innerText = "";
                }
            }
        }
    } catch(e) {
        console.error("Geocoding failed:", e);
    }
}

// 4. CASCADING RAJAONGKIR SELECT LOGIC
async function loadProvinces() {
    const provSelect = document.getElementById('province');
    try {
        const res = await fetch('api/rajaongkir.php?action=provinces');
        const json = await res.json();
        
        if (json.data) {
            provSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
            json.data.forEach(p => {
                provSelect.innerHTML += `<option value="${p.id}">${p.name}</option>`;
            });
        }
    } catch(e) {
        console.error("Gagal memuat provinsi:", e);
    }
}

async function loadCities() {
    const provSelect = document.getElementById('province');
    const provId = provSelect.value;
    const citySelect = document.getElementById('city');
    const distSelect = document.getElementById('district');
    const subSelect = document.getElementById('subdistrict');

    citySelect.innerHTML = '<option value="">Memuat...</option>';
    distSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
    subSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
    
    citySelect.disabled = true;
    distSelect.disabled = true;
    subSelect.disabled = true;

    if (!provId) return;

    // Pan map to selected province
    const provName = provSelect.options[provSelect.selectedIndex].text;
    geocodeAndPan(provName);

    try {
        const res = await fetch(`api/rajaongkir.php?action=cities&province_id=${provId}`);
        const json = await res.json();
        
        if (json.data) {
            citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
            json.data.forEach(c => {
                citySelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
            citySelect.disabled = false;
        }
    } catch(e) {
        console.error("Gagal memuat kota:", e);
    }
}

async function loadDistricts() {
    const citySelect = document.getElementById('city');
    const cityId = citySelect.value;
    const distSelect = document.getElementById('district');
    const subSelect = document.getElementById('subdistrict');

    distSelect.innerHTML = '<option value="">Memuat...</option>';
    subSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
    
    distSelect.disabled = true;
    subSelect.disabled = true;

    if (!cityId) return;

    const cityName = citySelect.options[citySelect.selectedIndex].text;
    isMalangArea = cityName.toLowerCase().includes('malang');
    
    // Pan map to selected city + province
    const provSelect = document.getElementById('province');
    const provName = provSelect.options[provSelect.selectedIndex].text;
    geocodeAndPan(cityName + ", " + provName);

    try {
        const res = await fetch(`api/rajaongkir.php?action=districts&city_id=${cityId}`);
        const json = await res.json();
        
        if (json.data) {
            distSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
            json.data.forEach(d => {
                distSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
            distSelect.disabled = false;
        }
    } catch(e) {
        console.error("Gagal memuat kecamatan:", e);
    }
    
    checkDeliveryLogic();
}

async function loadSubdistricts() {
    const distSelect = document.getElementById('district');
    const distId = distSelect.value;
    const subSelect = document.getElementById('subdistrict');

    subSelect.innerHTML = '<option value="">Memuat...</option>';
    subSelect.disabled = true;

    if (!distId) return;

    const distName = distSelect.options[distSelect.selectedIndex].text;
    const citySelect = document.getElementById('city');
    const cityName = citySelect.options[citySelect.selectedIndex].text;
    geocodeAndPan(distName + ", " + cityName);

    try {
        const res = await fetch(`api/rajaongkir.php?action=subdistricts&district_id=${distId}`);
        const json = await res.json();
        
        if (json.data) {
            subSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
            json.data.forEach(s => {
                subSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
            subSelect.disabled = false;
            
            // Set default destination ID to subdistrict level
            selectedDestinationId = distId;
        }
    } catch(e) {
        console.error("Gagal memuat kelurahan:", e);
    }
    
    checkDeliveryLogic();
}

// 5. AUTOCOMPLETE LOCATION SEARCH
function initAutocompleteSearch() {
    const searchInput = document.getElementById('search_dest');
    const dropdown = document.getElementById('autocomplete-results');
    let debounceTimer = null;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = searchInput.value.trim();

        if (q.length < 3) {
            dropdown.style.display = 'none';
            return;
        }

        dropdown.innerHTML = '<div style="padding: 10px; color: gray;">Mencari lokasi...</div>';
        dropdown.style.display = 'block';

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`api/rajaongkir.php?action=search&q=${encodeURIComponent(q)}`);
                const json = await res.json();
                dropdown.innerHTML = '';
                
                if (json.data && json.data.length > 0) {
                    json.data.forEach(loc => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerText = loc.label;
                        div.onclick = () => {
                            searchInput.value = loc.label;
                            selectedDestinationId = loc.id;
                            selectedLocationText = loc.label;
                            dropdown.style.display = 'none';
                            
                            // Check if city name contains "Malang"
                            isMalangArea = loc.city_name.toLowerCase().includes('malang');
                            
                            // Pan map to selected location
                            geocodeAndPan(loc.label);
                            
                            checkDeliveryLogic();
                        };
                        dropdown.appendChild(div);
                    });
                } else {
                    dropdown.innerHTML = '<div style="padding: 10px; color: red;">Lokasi tidak ditemukan</div>';
                }
            } catch(e) {
                dropdown.innerHTML = '<div style="padding: 10px; color: red;">Koneksi error</div>';
            }
        }, 400);
    });

    // Close autocomplete when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function parseLocationText(name) {
    if (!name) return;
    const parts = name.split(',').map(p => p.trim());
    if (parts.length >= 2) {
        const cityText = parts[1].toLowerCase();
        if (cityText.includes('malang')) {
            isMalangArea = true;
        }
    }
}

// 6. SMART DELIVERY LOGIC (OMNICHANNEL)
function checkDeliveryLogic() {
    const alertBox = document.getElementById('delivery-alert');
    const courierSelectBox = document.getElementById('courier-select-box');
    const serviceSelectBox = document.getElementById('service-select-box');
    const courierSelect = document.getElementById('kurir');
    
    // Fallback sync checks (handles copy-paste, browser autofill, etc.)
    const searchVal = document.getElementById('search_dest').value.toLowerCase();
    const citySelect = document.getElementById('city');
    let cityText = "";
    if (citySelect && citySelect.selectedIndex > 0) {
        cityText = citySelect.options[citySelect.selectedIndex].text.toLowerCase();
    }
    
    if (searchVal.includes('malang') || cityText.includes('malang')) {
        isMalangArea = true;
    } else {
        if (searchVal.trim() !== "" || cityText.trim() !== "") {
            isMalangArea = false;
        }
    }

    // Reset shipping pricing
    currentOngkir = 0;
    namaLayananKurir = "";

    if (isMalangArea) {
        // Malang Area: Enable Leaflet Mapping for Internal Courier
        alertBox.className = "alert-banner internal";
        alertBox.innerHTML = `
            <span>🎉</span>
            <div>
                <strong>Area Malang Terdeteksi!</strong> Anda memenuhi syarat pengiriman gratis / hemat oleh <strong>Kurir Internal Zencare</strong>.<br>
                <small>Silakan klik alamat tepat Anda pada peta di bawah untuk mengaktifkan kalkulator jarak.</small>
            </div>
        `;
        
        // Setup internal courier options
        if (courierSelect.value !== 'internal') {
            courierSelect.innerHTML = `
                <option value="internal" selected>Kurir Internal Zencare (Malang)</option>
            `;
        }
        courierSelectBox.style.display = 'block';
        serviceSelectBox.style.display = 'none'; // Determined by coordinates
        
        // center map in Malang if not already centered
        if (map) {
            const mapCenter = map.getCenter();
            const distToMalang = Math.sqrt(Math.pow(mapCenter.lat - (-7.9839), 2) + Math.pow(mapCenter.lng - 112.6213, 2));
            if (distToMalang > 0.5) {
                map.setView([-7.9839, 112.6213], 12);
            }
        }

        // Calculate distance if coordinates present
        calculateInternalDistance();
    } else {
        // Outside Malang: Use Commercial Courier (RajaOngkir)
        alertBox.className = "alert-banner info";
        alertBox.innerHTML = `
            <span>📦</span>
            <div>
                <strong>Pengiriman Luar Kota:</strong> Pesanan dikirim via Ekspedisi Logistik Nasional (JNE / POS). Silakan pilih Kurir & Layanan di bawah.
            </div>
        `;

        // Restore national courier options
        if (courierSelect.value === 'internal' || courierSelect.value === '') {
            courierSelect.innerHTML = `
                <option value="">-- Pilih Kurir --</option>
                <option value="jne">JNE Express</option>
                <option value="pos">Pos Indonesia</option>
            `;
        }
        courierSelectBox.style.display = 'block';
        serviceSelectBox.style.display = 'block';
        document.getElementById('distance-info').innerText = "";
        
        // Trigger commercial shipping fee recalculation if selections are complete
        calculateCommercialShipping();
    }
}

// 7. INTERNAL COURIER DISTANCE ROUTING
function calculateInternalDistance() {
    const latVal = document.getElementById('latitude').value;
    const lngVal = document.getElementById('longitude').value;
    const distInfo = document.getElementById('distance-info');

    if (!latVal || !lngVal) {
        distInfo.innerText = "Tentukan titik peta untuk menghitung biaya kurir internal.";
        updateTotalDisplay();
        return;
    }

    const targetLat = parseFloat(latVal);
    const targetLng = parseFloat(lngVal);

    // Calculate distance to Muharto and Borobudur branches using Haversine
    function haversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    const distMuharto = haversineDistance(targetLat, targetLng, CABANG_MUHARTO.lat, CABANG_MUHARTO.lng);
    const distBorobudur = haversineDistance(targetLat, targetLng, CABANG_BOROBUDUR.lat, CABANG_BOROBUDUR.lng);

    const minDistance = Math.min(distMuharto, distBorobudur);
    const originBranch = distMuharto < distBorobudur ? CABANG_MUHARTO.nama : CABANG_BOROBUDUR.nama;

    // Calculate cost based on rate
    let cost = Math.round(minDistance * TARIF_PER_KM);
    if (cost < TARIF_MINIMAL) {
        cost = TARIF_MINIMAL;
    }

    currentOngkir = cost;
    namaLayananKurir = `Delivery (${minDistance.toFixed(1)} km dari ${originBranch})`;
    
    distInfo.innerHTML = `✓ Pengiriman terdekat dari <strong>${originBranch}</strong> (${minDistance.toFixed(1)} KM)<br>Tarif Kurir: <strong>${formatRupiah(cost)}</strong>`;
    
    updateTotalDisplay();
}

// 8. COMMERCIAL SHIPPING COST LOADER
async function calculateCommercialShipping() {
    const kurir = document.getElementById('kurir').value;
    const serviceSelect = document.getElementById('layanan');
    const subtotal = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    const totalBerat = cart.reduce((sum, item) => sum + (item.berat * item.qty), 0);

    serviceSelect.innerHTML = '<option value="">-- Pilih Layanan --</option>';
    currentOngkir = 0;
    namaLayananKurir = "";
    updateTotalDisplay();

    if (!kurir || kurir === 'internal') return;

    // Resolve destination ID
    // Autocomplete destination takes priority, else fall back to Cascading dropdowns (subdistrict -> district -> city)
    let destinationId = selectedDestinationId;
    if (!destinationId) {
        const subSelect = document.getElementById('subdistrict');
        const distSelect = document.getElementById('district');
        const citySelect = document.getElementById('city');

        destinationId = subSelect.value || distSelect.value || citySelect.value;
    }

    if (!destinationId) {
        serviceSelect.innerHTML = '<option value="">Tentukan Alamat/Kota Dahulu</option>';
        return;
    }

    serviceSelect.innerHTML = '<option value="">Mengambil tarif...</option>';

    try {
        const fd = new FormData();
        fd.append('destination', destinationId);
        fd.append('weight', totalBerat);
        fd.append('courier', kurir);

        const res = await fetch('api/rajaongkir.php?action=cost', {
            method: 'POST',
            body: fd
        });
        const json = await res.json();
        
        serviceSelect.innerHTML = '';
        
        if (json.data && json.data.length > 0) {
            json.data.forEach((srv, idx) => {
                // Example structure: srv.service, srv.description, srv.cost, srv.etd
                const option = document.createElement('option');
                option.value = srv.cost;
                option.setAttribute('data-name', srv.service);
                option.innerText = `${srv.service} (${srv.description}) - ${formatRupiah(srv.cost)} (Estimasi: ${srv.etd} Hari)`;
                if (idx === 0) {
                    option.selected = true;
                }
                serviceSelect.appendChild(option);
            });
            
            // Set initial selection
            selectCommercialService();
        } else {
            serviceSelect.innerHTML = '<option value="">Kurir tidak mendukung rute ini</option>';
            alert("Gagal memuat tarif. Kurir tersebut mungkin tidak melayani pengiriman ke kecamatan ini.");
        }
    } catch(e) {
        console.error("Gagal cek ongkir:", e);
        serviceSelect.innerHTML = '<option value="">Koneksi error...</option>';
    }
}

function selectCommercialService() {
    const serviceSelect = document.getElementById('layanan');
    if (serviceSelect.selectedIndex >= 0) {
        const selectedOpt = serviceSelect.options[serviceSelect.selectedIndex];
        currentOngkir = parseInt(selectedOpt.value) || 0;
        namaLayananKurir = selectedOpt.getAttribute('data-name') || "Ekspedisi";
        updateTotalDisplay();
    }
}

// 9. VIEW UPDATE DISPLAY
function updateCheckoutSummary(subtotal, totalBerat) {
    const sumProduk = document.getElementById('sum_produk');
    if (sumProduk) {
        sumProduk.innerText = cart.length + " Macam Barang";
        document.getElementById('sum_harga').innerText = formatRupiah(subtotal);
        document.getElementById('sum_berat').innerText = totalBerat.toLocaleString('id-ID') + " Gram";
        updateTotalDisplay();
    }
}

function updateTotalDisplay() {
    const subtotal = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    const sumOngkir = document.getElementById('sum_ongkir');
    const sumTotal = document.getElementById('sum_total');
    
    if (sumOngkir && sumTotal) {
        sumOngkir.innerText = formatRupiah(currentOngkir);
        sumTotal.innerText = formatRupiah(subtotal + currentOngkir);
    }
}

// 10. MIDTRANS SECURE PROCESSING
async function processSecurePayment() {
    const nama = document.getElementById('buyer_name').value.trim();
    const phone = document.getElementById('buyer_phone').value.trim();
    const alamat = document.getElementById('buyer_address').value.trim();
    
    // Address components from dropdown
    const provSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const distSelect = document.getElementById('district');
    
    const provName = provSelect.selectedIndex > 0 ? provSelect.options[provSelect.selectedIndex].text : '';
    const cityName = citySelect.selectedIndex > 0 ? citySelect.options[citySelect.selectedIndex].text : '';
    const distName = distSelect.selectedIndex > 0 ? distSelect.options[distSelect.selectedIndex].text : '';
    
    // Fall back to autocomplete text if cascading is not completed
    const autocompleteVal = document.getElementById('search_dest').value;

    if (!nama || !phone || !alamat) {
        alert("Mohon lengkapi Nama Penerima, Nomor WhatsApp, dan Alamat Pengiriman!");
        return;
    }

    if (!provName && !autocompleteVal) {
        alert("Mohon ketik lokasi kota tujuan atau pilih melalui menu dropdown wilayah!");
        return;
    }

    const latVal = document.getElementById('latitude').value;
    const lngVal = document.getElementById('longitude').value;
    const kurir = document.getElementById('kurir').value;

    if (kurir === 'internal' && (!latVal || !lngVal)) {
        alert("Wajib klik lokasi persis rumah Anda pada peta untuk pengantaran kurir Zencare area Malang!");
        return;
    }

    if (currentOngkir === 0 && kurir !== 'internal') {
        alert("Tarif ongkir ekspedisi belum termuat atau belum dipilih!");
        return;
    }

    const checkoutBtn = document.getElementById('btn-secure-pay');
    checkoutBtn.disabled = true;
    checkoutBtn.innerText = "Membuat Transaksi...";

    // Build payload for backend
    const payload = {
        nama_pembeli: nama,
        phone: phone,
        alamat_lengkap: alamat,
        provinsi: provName || autocompleteVal,
        kota: cityName || autocompleteVal,
        kecamatan: distName || autocompleteVal,
        lat: latVal ? parseFloat(latVal) : null,
        lng: lngVal ? parseFloat(lngVal) : null,
        cart: cart.map(item => ({ id: item.id, qty: item.qty })),
        kurir: kurir,
        layanan: namaLayananKurir,
        ongkir: currentOngkir
    };

    try {
        const res = await fetch('api/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const responseData = await res.json();
        
        if (responseData.status === 'success') {
            const token = responseData.token;
            
            // Trigger Midtrans Snap.js
            snap.pay(token, {
                onSuccess: function(result) {
                    alert("Pembayaran Berhasil! Terimakasih telah berbelanja di Zencare Medical.");
                    localStorage.removeItem('zencare_cart');
                    window.location.reload();
                },
                onPending: function(result) {
                    alert("Pembayaran tertunda. Silakan selesaikan pembayaran Anda sesuai instruksi Midtrans.");
                    window.location.reload();
                },
                onError: function(result) {
                    alert("Gagal memproses pembayaran. Hubungi admin.");
                    checkoutBtn.disabled = false;
                    checkoutBtn.innerText = "💳 Lanjut Bayar (Midtrans)";
                },
                onClose: function() {
                    alert("Transaksi ditutup sebelum pembayaran diselesaikan.");
                    checkoutBtn.disabled = false;
                    checkoutBtn.innerText = "💳 Lanjut Bayar (Midtrans)";
                }
            });
        } else {
            alert("Error Checkout: " + responseData.message);
            checkoutBtn.disabled = false;
            checkoutBtn.innerText = "💳 Lanjut Bayar (Midtrans)";
        }
    } catch(e) {
        console.error("Gagal checkout secure:", e);
        alert("Terjadi kesalahan koneksi server saat melakukan checkout.");
        checkoutBtn.disabled = false;
        checkoutBtn.innerText = "💳 Lanjut Bayar (Midtrans)";
    }
}

// 11. GENERAL UTILITIES
function formatRupiah(angka) {
    return "Rp " + parseInt(angka).toLocaleString('id-ID');
}
