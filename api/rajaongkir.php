<?php
// File: api/rajaongkir.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/indonesia_regions.php';

$action = $_GET['action'] ?? '';

// Helper function to send requests to Komerce API
if (!function_exists('callKomerce')) {
function callKomerce($path, $method = 'GET', $params = []) {
    if ((defined('RAJAONGKIR_ACTIVE') && !RAJAONGKIR_ACTIVE) || empty(KOMERCE_API_KEY)) {
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
}

// Caching wrapper function
if (!function_exists('getCachedResponse')) {
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
}

switch ($action) {
    case 'provinces':
        // Cache provinces list for 30 days dengan fallback offline lengkap
        $provRes = getCachedResponse('provinces_list', function() {
            $apiRes = callKomerce('/destination/province');
            $dec = json_decode($apiRes, true);
            if (!empty($dec['data']) && is_array($dec['data'])) {
                return $apiRes;
            }
            return json_encode([
                'meta' => ['message' => 'Success Get Province (Fallback)', 'code' => 200, 'status' => 'success'],
                'data' => getIndonesianProvincesList()
            ]);
        }, 2592000);

        $checkProv = json_decode($provRes, true);
        if (empty($checkProv['data']) || !is_array($checkProv['data'])) {
            $provRes = json_encode([
                'meta' => ['message' => 'Success Get Province (Fallback)', 'code' => 200, 'status' => 'success'],
                'data' => getIndonesianProvincesList()
            ]);
        }
        echo $provRes;
        break;

    case 'cities':
        $provinceId = (int)($_GET['province_id'] ?? 0);
        if ($provinceId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'province_id is required']);
            break;
        }
        
        // Cache cities based on province_id for 30 days dengan fallback offline lengkap
        $cityRes = getCachedResponse('cities_prov_' . $provinceId, function() use ($provinceId) {
            $apiRes = callKomerce('/destination/city/' . $provinceId);
            $dec = json_decode($apiRes, true);
            if (!empty($dec['data']) && is_array($dec['data']) && count($dec['data']) > 0) {
                return $apiRes;
            }
            return json_encode([
                'meta' => ['message' => 'Success Get City Offline Fallback', 'code' => 200, 'status' => 'success'],
                'data' => getIndonesianCitiesByProvince($provinceId)
            ]);
        }, 2592000);

        $checkCity = json_decode($cityRes, true);
        if (empty($checkCity['data']) || !is_array($checkCity['data']) || count($checkCity['data']) === 0) {
            $fallbackList = getIndonesianCitiesByProvince($provinceId);
            $cityRes = json_encode([
                'meta' => ['message' => 'Success Get City Offline Fallback', 'code' => 200, 'status' => 'success'],
                'data' => $fallbackList
            ]);
            @file_put_contents(__DIR__ . '/../cache/cities_prov_' . $provinceId . '.json', $cityRes);
        }
        echo $cityRes;
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
        
        $origin = $_POST['origin'] ?? '46748';
        // 46748 adalah ID Destinasi Resmi Klojen / Kota Malang pada basis data Komerce
        if ($origin === '256' || $origin === '391' || empty($origin)) {
            $origin = '46748';
        }

        $destination = trim($_POST['destination'] ?? '');
        $cityName = trim($_POST['city_name'] ?? '');
        $provinceName = trim($_POST['province_name'] ?? '');
        $weight = max(1, intval($_POST['weight'] ?? 1000));
        $courier = strtolower(trim($_POST['courier'] ?? 'jne'));

        if (empty($destination) && empty($cityName)) {
            echo json_encode(['status' => 'error', 'message' => 'destination ID is required']);
            break;
        }
        if (empty($courier) || in_array($courier, ['internal', 'pickup'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Courier tidak valid untuk kalkulasi ekspedisi RajaOngkir']);
            break;
        }

        // Jika API RajaOngkir dimatikan di Pengaturan Admin, langsung gunakan kalkulator lokal
        if (defined('RAJAONGKIR_ACTIVE') && !RAJAONGKIR_ACTIVE) {
            echo getFallbackShippingCost($cityName, $weight, $courier);
            break;
        }

        // 1. Resolusi ID Subdistrict/Destinasi Komerce yang akurat:
        // Prioritas utama via hierarki City ID -> District -> Subdistrict (bebas salah kota seperti Surabaya vs Surabaya Baru Lampung)
        $targetDest = $destination;
        $resolvedSubdest = '';

        if (ctype_digit($destination) && intval($destination) > 0) {
            $cacheKeyCity = 'dest_city_v2_' . $destination;
            $cachedCityJson = getCachedResponse($cacheKeyCity, function() use ($destination) {
                $distRaw = callKomerce('/destination/district/' . $destination);
                $distDec = json_decode($distRaw, true);
                $firstDistId = !empty($distDec['data'][0]['id']) ? (string)$distDec['data'][0]['id'] : '';
                if ($firstDistId !== '') {
                    $subRaw = callKomerce('/destination/sub-district/' . $firstDistId);
                    $subDec = json_decode($subRaw, true);
                    if (!empty($subDec['data'][0]['id'])) {
                        return json_encode([
                            'status' => 'success',
                            'dest_id' => (string)$subDec['data'][0]['id']
                        ]);
                    }
                }
                return '';
            }, 2592000);
            $cityResDec = json_decode($cachedCityJson, true);
            if (!empty($cityResDec['dest_id'])) {
                $resolvedSubdest = (string)$cityResDec['dest_id'];
            }
        }

        // 2. Fallback pencarian teks jika ID kota offline tidak cocok, prioritaskan city_name & province_name persis
        if ($resolvedSubdest === '' && !empty($cityName)) {
            $cleanCity = trim(preg_replace('/^(KOTA|KABUPATEN|KAB\.?)\s+/i', '', $cityName));
            $cleanProv = trim(preg_replace('/^(PROVINSI|PROV\.?)\s+|\s*\(.*?\)/i', '', $provinceName));
            $cacheKeyText = 'dest_text_v2_' . md5(strtolower($cleanCity . '|' . $cleanProv));

            $cachedTextJson = getCachedResponse($cacheKeyText, function() use ($cleanCity, $cleanProv) {
                $pickBest = function($rows) use ($cleanCity, $cleanProv) {
                    $bestId = '';
                    $bestScore = -1;
                    $targetCityUp = strtoupper($cleanCity);
                    $targetProvUp = strtoupper($cleanProv);
                    foreach ($rows as $row) {
                        $rowCity = strtoupper(trim($row['city_name'] ?? ''));
                        $rowProv = strtoupper(trim($row['province_name'] ?? ''));
                        $rowDist = strtoupper(trim($row['district_name'] ?? ''));
                        $score = 0;
                        if ($rowCity === $targetCityUp) {
                            $score += 100;
                        } elseif ($targetCityUp !== '' && strpos($rowCity, $targetCityUp) !== false) {
                            $score += 30;
                        }
                        if ($targetProvUp !== '' && ($rowProv === $targetProvUp || strpos($rowProv, $targetProvUp) !== false || strpos($targetProvUp, $rowProv) !== false)) {
                            $score += 50;
                        }
                        if ($targetCityUp !== '' && $rowDist === $targetCityUp) {
                            $score += 10;
                        }
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestId = (string)($row['id'] ?? '');
                        }
                    }
                    $minAccept = ($targetProvUp !== '') ? 80 : 100;
                    return ($bestScore >= $minAccept && $bestId !== '') ? $bestId : '';
                };

                $queries = array_values(array_unique(array_filter([
                    $cleanProv !== '' ? ($cleanCity . ' ' . $cleanProv) : '',
                    $cleanCity
                ])));

                foreach ($queries as $qStr) {
                    $raw = callKomerce('/destination/domestic-destination', 'GET', [
                        'search' => $qStr,
                        'limit' => 50
                    ]);
                    $dec = json_decode($raw, true);
                    if (!empty($dec['data']) && is_array($dec['data'])) {
                        $picked = $pickBest($dec['data']);
                        if ($picked !== '') {
                            return json_encode(['status' => 'success', 'dest_id' => $picked]);
                        }
                    }
                }
                return '';
            }, 2592000);

            $textResDec = json_decode($cachedTextJson, true);
            if (!empty($textResDec['dest_id'])) {
                $resolvedSubdest = (string)$textResDec['dest_id'];
            }
        }

        if ($resolvedSubdest !== '') {
            $targetDest = $resolvedSubdest;
        }

        // Cache cost calculation queries for 7 days
        // We round weight to the nearest 100g to increase cache hit rate
        $roundedWeight = max(100, round($weight / 100) * 100);
        $cacheKey = 'cost_v2_' . $origin . '_' . $targetDest . '_' . $roundedWeight . '_' . $courier;
        
        $costResponse = getCachedResponse($cacheKey, function() use ($origin, $targetDest, $weight, $courier, $cityName) {
            $apiRes = callKomerce('/calculate/domestic-cost', 'POST', [
                'origin' => $origin,
                'destination' => $targetDest,
                'weight' => $weight,
                'courier' => $courier
            ]);
            $dec = json_decode($apiRes, true);
            if (!empty($dec['data']) && is_array($dec['data']) && count($dec['data']) > 0) {
                return $apiRes;
            }
            return getFallbackShippingCost($cityName, $weight, $courier);
        }, 604800);

        // Jika response cached ternyata berisi error/limit, gantikan dengan fallback cerdas
        $checkCost = json_decode($costResponse, true);
        if (empty($checkCost['data']) || !is_array($checkCost['data']) || count($checkCost['data']) === 0) {
            $costResponse = getFallbackShippingCost($cityName, $weight, $courier);
            $cacheFile = __DIR__ . '/../cache/' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $cacheKey) . '.json';
            @file_put_contents($cacheFile, $costResponse);
        }

        echo $costResponse;
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

// MOCK DATA GENERATOR FOR MISSING API KEY
if (!function_exists('getMockData')) {
function getMockData($path, $params) {
    if (strpos($path, '/destination/province') !== false) {
        return json_encode([
            'meta' => ['message' => 'Success Get Province (Mock)', 'code' => 200, 'status' => 'success'],
            'data' => getIndonesianProvincesList()
        ]);
    }
    if (strpos($path, '/destination/city/') !== false) {
        preg_match('/\/destination\/city\/(\d+)/', $path, $m);
        $provId = isset($m[1]) ? (int)$m[1] : 18;
        return json_encode([
            'meta' => ['message' => 'Success Get City (Mock)', 'code' => 200, 'status' => 'success'],
            'data' => getIndonesianCitiesByProvince($provId)
        ]);
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
}
?>
