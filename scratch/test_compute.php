<?php
// Simulate the exact Javascript logic of computeAllocations
function testComputeAllocations($item, $targetQty) {
    if (!$item['needs_verification']) {
        return ['allocations' => [], 'reason' => 'needs_verification is false'];
    }

    if ($item['kategori'] === 'Obat') {
        $rawList = $item['batch_list'] ?? '';
        $entries = [];
        if ($rawList) {
            foreach (explode(',', $rawList) as $s) {
                $p = explode('|', $s);
                $entries[] = ['code' => trim($p[0]), 'stockPcs' => intval($p[1] ?? 0)];
            }
        }

        $relevant = array_filter($entries, function($b) use ($item) {
            $isEceran = strpos($b['code'], '.') !== false;
            return ($item['satuan_tipe'] === 'kecil' && $isEceran) || ($item['satuan_tipe'] === 'besar' && !$isEceran);
        });
        $relevant = array_values($relevant);

        foreach ($relevant as &$b) {
            $b['stockUom'] = ($item['satuan_tipe'] === 'besar') ? floor($b['stockPcs'] / ($item['rasio'] ?: 1)) : $b['stockPcs'];
        }

        $prevAllocations = $item['allocations'] ?? [];
        $verifiedAllocations = array_filter($prevAllocations, function($al) { return !empty($al['verified']); });

        $newAllocations = [];
        $remainingNeeded = $targetQty;

        foreach ($verifiedAllocations as $val) {
            if ($remainingNeeded <= 0) break;
            $takeVer = min($val['qty'], $remainingNeeded);
            $newAllocations[] = [
                'batch' => $val['batch'],
                'qty' => $takeVer,
                'verified' => true,
                'scanned_batch' => $val['scanned_batch'] ?? $val['batch'],
                'is_anomaly' => $val['is_anomaly'] ?? false,
                'batch_seharusnya' => $val['batch_seharusnya'] ?? null
            ];
            $remainingNeeded -= $takeVer;
        }

        if ($remainingNeeded > 0) {
            $batchUsedMap = [];
            foreach ($newAllocations as $al) {
                $bKey = strtolower($al['scanned_batch'] ?? $al['batch']);
                $batchUsedMap[$bKey] = ($batchUsedMap[$bKey] ?? 0) + $al['qty'];
            }

            foreach ($relevant as $b) {
                if ($remainingNeeded <= 0) break;
                $used = $batchUsedMap[strtolower($b['code'])] ?? 0;
                $avail = $b['stockUom'] - $used;
                if ($avail > 0) {
                    $take = min($avail, $remainingNeeded);
                    $newAllocations[] = [
                        'batch' => $b['code'],
                        'qty' => $take,
                        'verified' => false,
                        'scanned_batch' => null,
                        'is_anomaly' => false,
                        'batch_seharusnya' => null
                    ];
                    $remainingNeeded -= $take;
                    $batchUsedMap[strtolower($b['code'])] = $used + $take;
                }
            }
        }

        return ['allocations' => $newAllocations, 'remainingNeeded' => $remainingNeeded, 'relevant' => $relevant];
    }
    return [];
}

// Test case 1: When needs_verification is false (e.g. from triggerManualBongkar)
$item1 = [
    'kategori' => 'Obat',
    'needs_verification' => false,
    'batch_list' => 'HUFPIL-2027-01|700,HUFPIL-2027-01.A|50',
    'satuan_tipe' => 'kecil',
    'rasio' => 50
];
echo "Test 1 (needs_verification=false): " . json_encode(testComputeAllocations($item1, 9)) . "\n\n";

// Test case 2: When batch_list only has HUFPIL-2027-01|750 (before bongkar or if card had stale batch_list)
$item2 = [
    'kategori' => 'Obat',
    'needs_verification' => true,
    'batch_list' => 'HUFPIL-2027-01|750',
    'satuan_tipe' => 'kecil',
    'rasio' => 50
];
echo "Test 2 (batch_list has no dot): " . json_encode(testComputeAllocations($item2, 9)) . "\n\n";

// Test case 3: When needs_verification=true and batch_list has HUFPIL-2027-01.A|50
$item3 = [
    'kategori' => 'Obat',
    'needs_verification' => true,
    'batch_list' => 'HUFPIL-2027-01|700,HUFPIL-2027-01.A|50',
    'satuan_tipe' => 'kecil',
    'rasio' => 50
];
echo "Test 3 (needs_verification=true & dot batch exists): " . json_encode(testComputeAllocations($item3, 9)) . "\n\n";
