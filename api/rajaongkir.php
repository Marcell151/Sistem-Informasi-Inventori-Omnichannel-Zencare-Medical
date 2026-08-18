<?php
// File: api/rajaongkir.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? '';

// Helper function to send requests to Komerce API
function callKomerce($path, $method = 'GET', $params = []) {
    if (empty(KOMERCE_API_KEY)) {
        return getMockData($path, $params);
    }

    $url = KOMERCE_BASE_URL . $path;
    $curl = curl_init();
    
    $headers = [
        'key: ' . KOMERCE_API_KEY
    ];

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    } elseif ($method === 'GET' && !empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $options[CURLOPT_URL] = $url;
    $options[CURLOPT_HTTPHEADER] = $headers;

    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode([
            'status' => 'error',
            'message' => 'cURL Error: ' . $err
        ]);
    }

    return $response;
}

// Caching wrapper function
function getCachedResponse($cacheKey, $fetchCallback, $expirySeconds = 2592000) { // Default 30 days
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    
    // Sanitize cache filename
    $safeFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $cacheKey) . '.json';
    $fullPath = $cacheDir . '/' . $safeFilename;
    
    // Check if cache file exists, is valid, and has not expired
    if (file_exists($fullPath) && (time() - filemtime($fullPath)) < $expirySeconds) {
        $cachedContent = file_get_contents($fullPath);
        if ($cachedContent && json_decode($cachedContent) !== null) {
            return $cachedContent;
        }
    }
    
    // Cache miss, execute the fetch callback
    $response = $fetchCallback();
    
    // Save to cache file only if successful API response (meta.code == 200)
    if ($response) {
        $decoded = json_decode($response, true);
        if (
            (isset($decoded['meta']['code']) && $decoded['meta']['code'] == 200) ||
            (isset($decoded['status']) && $decoded['status'] == 'success') ||
            (isset($decoded['data']) && !empty($decoded['data']))
        ) {
            @file_put_contents($fullPath, $response);
        }
    }
    
    return $response;
}

switch ($action) {
    case 'provinces':
        // Cache provinces list for 30 days
        echo getCachedResponse('provinces_list', function() {
            return callKomerce('/destination/province');
        }, 2592000);
        break;

    case 'cities':
        $provinceId = $_GET['province_id'] ?? '';
        if (empty($provinceId)) {
            echo json_encode(['status' => 'error', 'message' => 'province_id is required']);
            break;
        }
        // Cache cities based on province_id for 30 days
        echo getCachedResponse('cities_prov_' . $provinceId, function() use ($provinceId) {
            return callKomerce('/destination/city/' . $provinceId);
        }, 2592000);
        break;

    case 'districts':
        $cityId = $_GET['city_id'] ?? '';
        if (empty($cityId)) {
            echo json_encode(['status' => 'error', 'message' => 'city_id is required']);
            break;
        }
        // Cache districts based on city_id for 30 days
        echo getCachedResponse('districts_city_' . $cityId, function() use ($cityId) {
            return callKomerce('/destination/district/' . $cityId);
        }, 2592000);
        break;

    case 'subdistricts':
        $districtId = $_GET['district_id'] ?? '';
        if (empty($districtId)) {
            echo json_encode(['status' => 'error', 'message' => 'district_id is required']);
            break;
        }
        // Cache subdistricts based on district_id for 30 days
        echo getCachedResponse('subdistricts_dist_' . $districtId, function() use ($districtId) {
            return callKomerce('/destination/sub-district/' . $districtId);
        }, 2592000);
        break;

    case 'search':
        $q = $_GET['q'] ?? '';
        if (strlen($q) < 3) {
            echo json_encode(['status' => 'error', 'message' => 'Search query must be at least 3 characters']);
            break;
        }
        // Cache search query for 7 days to save limit
        $cacheKey = 'search_' . md5(strtolower($q));
        echo getCachedResponse($cacheKey, function() use ($q) {
            return callKomerce('/destination/domestic-destination', 'GET', [
                'search' => $q,
                'limit' => 15,
                'offset' => 0
            ]);
        }, 604800);
        break;

    case 'cost':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'POST method required']);
            break;
        }
        
        $origin = $_POST['origin'] ?? '256'; // Default to Malang City (ID: 256)
        $destination = $_POST['destination'] ?? '';
        $weight = $_POST['weight'] ?? 1000;
        $courier = $_POST['courier'] ?? 'jne';

        if (empty($destination)) {
            echo json_encode(['status' => 'error', 'message' => 'destination ID is required']);
            break;
        }

        // Cache cost calculation queries for 7 days
        // We round weight to the nearest 100g to increase cache hit rate
        $roundedWeight = round($weight / 100) * 100;
        $cacheKey = 'cost_' . $origin . '_' . $destination . '_' . $roundedWeight . '_' . $courier;
        
        echo getCachedResponse($cacheKey, function() use ($origin, $destination, $weight, $courier) {
            return callKomerce('/calculate/domestic-cost', 'POST', [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier
            ]);
        }, 604800);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

// MOCK DATA GENERATOR FOR MISSING API KEY
function getMockData($path, $params) {
    if (strpos($path, '/destination/province') !== false) {
        return json_encode(['data' => [
            ['id' => '10', 'name' => 'DKI JAKARTA'],
            ['id' => '12', 'name' => 'JAWA BARAT'],
            ['id' => '18', 'name' => 'JAWA TENGAH'],
            ['id' => '24', 'name' => 'JAWA TIMUR']
        ]]);
    }
    if (strpos($path, '/destination/city/') !== false) {
        return json_encode(['data' => [
            ['id' => '256', 'name' => 'KOTA MALANG'],
            ['id' => '391', 'name' => 'KABUPATEN MALANG'],
            ['id' => '577', 'name' => 'KOTA SURABAYA'],
            ['id' => '160', 'name' => 'KABUPATEN JEMBER'],
            ['id' => '583', 'name' => 'KABUPATEN SIDOARJO'],
            ['id' => '152', 'name' => 'KOTA JAKARTA PUSAT']
        ]]);
    }
    if (strpos($path, '/calculate/domestic-cost') !== false) {
        $weightKg = max(1, ceil(($params['weight'] ?? 1000) / 1000));
        return json_encode(['data' => [
            ['service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 15000 * $weightKg, 'etd' => '2-3 Hari'],
            ['service' => 'YES', 'description' => 'Yakin Esok Sampai', 'cost' => 24000 * $weightKg, 'etd' => '1 Hari']
        ]]);
    }
    return json_encode(['data' => []]);
}
?>
