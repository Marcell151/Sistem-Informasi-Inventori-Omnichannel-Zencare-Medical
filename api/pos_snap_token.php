<?php
// File: api/pos_snap_token.php
// Menghasilkan Midtrans Dynamic Snap Token untuk POS Kasir ZenCare Medical
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$total = intval($input['total'] ?? 0);
$cart = $input['cart'] ?? [];
$invoiceNo = trim($input['invoice'] ?? ('POS-' . date('Ymd') . '-' . rand(1000, 9999)));

if ($total <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Total transaksi tidak valid']);
    exit;
}

try {
    $itemDetails = [];
    foreach ($cart as $item) {
        $nama = substr($item['name'] ?? 'Item POS', 0, 45);
        $qty = intval($item['qty'] ?? 1);
        $price = intval($item['price'] ?? 0);
        $itemDetails[] = [
            'id' => (string)($item['id'] ?? '1'),
            'price' => $price,
            'quantity' => $qty,
            'name' => $nama
        ];
    }
    
    // Pastikan total item matches gross_amount
    $sum = 0;
    foreach ($itemDetails as $it) {
        $sum += $it['price'] * $it['quantity'];
    }
    if ($sum !== $total) {
        $itemDetails = [
            [
                'id' => 'POS-TOTAL',
                'price' => $total,
                'quantity' => 1,
                'name' => 'Transaksi Kasir POS ' . $invoiceNo
            ]
        ];
    }

    $posExpiryMenit = defined('MIDTRANS_EXPIRY_POS_QRIS_MENIT') ? MIDTRANS_EXPIRY_POS_QRIS_MENIT : 5;

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $posReturnUrl = $protocol . $host . '/inventory_zencare/pos/pos.php';

    $midtransPayload = [
        'transaction_details' => [
            'order_id' => $invoiceNo,
            'gross_amount' => $total,
        ],
        'customer_details' => [
            'first_name' => 'Pelanggan Walk-In Kasir',
            'phone' => '081234567890',
        ],
        'item_details' => $itemDetails,
        'enabled_payments' => ['qris', 'gopay', 'shopeepay'],
        'expiry' => [
            'start_time' => date('Y-m-d H:i:s O'),
            'unit' => 'minute',
            'duration' => $posExpiryMenit
        ],
        'callbacks' => [
            'finish' => $posReturnUrl,
            'unfinish' => $posReturnUrl,
            'error' => $posReturnUrl
        ]
    ];

    $ch = curl_init(MIDTRANS_API_URL . '/snap/v1/transactions');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($midtransPayload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $midtransResponse = curl_exec($ch);
    $midtransError = curl_error($ch);
    curl_close($ch);

    if ($midtransError) {
        throw new Exception("Gagal terhubung ke Midtrans: " . $midtransError);
    }

    $midtransData = json_decode($midtransResponse, true);
    if (!isset($midtransData['token'])) {
        $errMsg = $midtransData['error_messages'][0] ?? json_encode($midtransData);
        throw new Exception("Error dari Midtrans: " . $errMsg);
    }

    echo json_encode([
        'status' => 'success',
        'token' => $midtransData['token'],
        'redirect_url' => $midtransData['redirect_url'] ?? '',
        'order_id' => $invoiceNo,
        'expiry_duration_seconds' => $posExpiryMenit * 60,
        'expiry_duration_minutes' => $posExpiryMenit
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
