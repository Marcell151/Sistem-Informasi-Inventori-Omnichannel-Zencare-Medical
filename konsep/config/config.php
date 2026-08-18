<?php
// File: konsep/config/config.php
require_once __DIR__ . '/env_loader.php';

// Load environmental variables from root workspace directory (2 levels up)
loadEnv(__DIR__ . '/../../.env');

// 1. DATABASE CONFIGURATION
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'db_ta_mini');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// 2. MIDTRANS API CONFIGURATION (SANDBOX)
define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY') ?: '');
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY') ?: '');
define('MIDTRANS_IS_PRODUCTION', false);
define('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js');
define('MIDTRANS_API_URL', 'https://api.sandbox.midtrans.com');

// 3. RAJAONGKIR KOMERCE API CONFIGURATION
define('KOMERCE_API_KEY', getenv('KOMERCE_API_KEY') ?: '');
define('KOMERCE_BASE_URL', 'https://rajaongkir.komerce.id/api/v1');

// 4. ZENCARE BRANCHES (MALANG AREA)
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

// Delivery fee configuration for internal courier (per KM)
define('INTERNAL_COURIER_RATE_PER_KM', 3000);
define('INTERNAL_COURIER_MIN_FARE', 10000);
?>
