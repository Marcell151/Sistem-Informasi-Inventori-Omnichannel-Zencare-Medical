<?php
// File: config/config.php
require_once __DIR__ . '/env_loader.php';

// Load environmental variables from root workspace directory
loadEnv(__DIR__ . '/../.env');

// Timezone Configuration (WIB / GMT+7)
date_default_timezone_set('Asia/Jakarta');

// Durasi Kedaluwarsa Transaksi Midtrans & Cooldown Reservasi Stok:
// 1. POS Kasir Toko Fisik: Pelanggan walk-in di counter meja kasir (5 Menit / 300 Detik)
define('MIDTRANS_EXPIRY_POS_QRIS_MENIT', 5);

// 2. E-Commerce Online (Semua Metode Pembayaran): Batas waktu tunggal 120 Menit (2 Jam)
define('MIDTRANS_EXPIRY_ECOMMERCE_MENIT', 120);
define('MIDTRANS_EXPIRY_ECOMMERCE_QRIS_MENIT', 120);
define('MIDTRANS_EXPIRY_ECOMMERCE_VA_MENIT', 120);

// Default fallback untuk kompatibilitas fungsi lama
define('RESERVASI_COOLDOWN_MENIT', 120);

// 1. DATABASE CONFIGURATION
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'db_inventory'); // db_inventory = database baru Final TA
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Load optional overrides from config/api_keys.php (managed via admin/pengaturan_api.php)
$apiKeysFile = __DIR__ . '/api_keys.php';
$apiKeysCfg = file_exists($apiKeysFile) ? (require $apiKeysFile) : [];

// 2. MIDTRANS API CONFIGURATION (SANDBOX / PRODUCTION)
$midtransCfg = is_array($apiKeysCfg['midtrans'] ?? null) ? $apiKeysCfg['midtrans'] : [];
$midtransActive = array_key_exists('is_active', $midtransCfg) ? (bool)$midtransCfg['is_active'] : true;
$midtransIsProd = !empty($midtransCfg['is_production']);
define('MIDTRANS_ACTIVE', $midtransActive);

$midtransServerKey = !empty($midtransCfg['server_key']) ? $midtransCfg['server_key'] : getenv('MIDTRANS_SERVER_KEY');
if (empty($midtransServerKey) || strpos($midtransServerKey, 'YOUR_') !== false || strpos($midtransServerKey, 'REPLACE_') !== false) {
    $midtransServerKey = base64_decode('TWlkLXNlcnZlci14RkRBYUtiRVpmdldBUjVwaWlkZGRkTmE=');
}
define('MIDTRANS_SERVER_KEY', $midtransServerKey);

$midtransClientKey = !empty($midtransCfg['client_key']) ? $midtransCfg['client_key'] : getenv('MIDTRANS_CLIENT_KEY');
if (empty($midtransClientKey) || strpos($midtransClientKey, 'YOUR_') !== false || strpos($midtransClientKey, 'REPLACE_') !== false) {
    $midtransClientKey = base64_decode('TWlkLWNsaWVudC1uckxOMDk0bEl6bDM2ZWtN');
}
define('MIDTRANS_CLIENT_KEY', $midtransClientKey);

define('MIDTRANS_IS_PRODUCTION', $midtransIsProd);
define('MIDTRANS_SNAP_URL', $midtransIsProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js');
define('MIDTRANS_API_URL', $midtransIsProd ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com');

// 3. RAJAONGKIR KOMERCE API CONFIGURATION
$rajaCfg = is_array($apiKeysCfg['rajaongkir'] ?? null) ? $apiKeysCfg['rajaongkir'] : [];
$rajaActive = array_key_exists('is_active', $rajaCfg) ? (bool)$rajaCfg['is_active'] : true;
define('RAJAONGKIR_ACTIVE', $rajaActive);

$komerceKey = !empty($rajaCfg['api_key']) ? $rajaCfg['api_key'] : getenv('KOMERCE_API_KEY');
if (empty($komerceKey) || strpos($komerceKey, 'YOUR_') !== false) {
    $komerceKey = base64_decode('Z25ud2R4WlFjNTRiOWQ5OWIyMzRjNDlhUHFOY2l0M2Y=');
}
define('KOMERCE_API_KEY', $komerceKey);
define('KOMERCE_BASE_URL', 'https://rajaongkir.komerce.id/api/v1');

// 4. ZENCARE BRANCH — Sistem Single-Branch View (sesuai batasan TA)
// Fitur multi-cabang disembunyikan di UI, kode tetap ada untuk tidak menghapus riwayat

/* === MULTI-BRANCH CONFIG (DISEMBUNYIKAN, BUKAN DIHAPUS) ===
const ZENCARE_BRANCHES = [
    'muharto'   => [
        'lat' => -7.9881, 
        'lng' => 112.6371, 
        'nama' => 'Zencare Muharto',
        'alamat' => 'Jl. Muharto, Kota Malang'
    ],
    'borobudur' => [
        'lat' => -7.9400, 
        'lng' => 112.6258, 
        'nama' => 'Zencare Borobudur',
        'alamat' => 'Jl. Borobudur, Kota Malang'
    ]
];
=== END MULTI-BRANCH CONFIG === */

// Delivery fee configuration for internal courier (per KM)
define('INTERNAL_COURIER_RATE_PER_KM', 3000);
define('INTERNAL_COURIER_MIN_FARE', 10000);
?>
