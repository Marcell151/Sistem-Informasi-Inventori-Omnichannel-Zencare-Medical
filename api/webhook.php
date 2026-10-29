<?php
// File: api/webhook.php
// Skenario 2: Webhook Sinkronisasi Stok dari Midtrans (Online to Offline)

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/order_helper.php';

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Empty request body']);
    exit;
}

$orderId       = $input['order_id'] ?? '';
$statusCode    = $input['status_code'] ?? '';
$grossAmount   = $input['gross_amount'] ?? '';
$serverKeySignature = $input['signature_key'] ?? '';

// Verifikasi Signature
$localSignature = hash("sha512", $orderId . $statusCode . $grossAmount . MIDTRANS_SERVER_KEY);
if ($localSignature !== $serverKeySignature) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature key']);
    exit;
}

$transactionStatus = $input['transaction_status'] ?? '';

// Map to system status: ENUM('Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Siap Diambil', 'Selesai', 'Dibatalkan')
$newStatus = 'Menunggu Pembayaran';
if (in_array($transactionStatus, ['settlement', 'capture'])) {
    $newStatus = 'Diproses';
} elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
    $newStatus = 'Dibatalkan';
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();

    // Cek status saat ini
    $stmtSelect = $pdo->prepare("SELECT id, status_pesanan FROM penjualan WHERE no_invoice = ? FOR UPDATE");
    $stmtSelect->execute([$orderId]);
    $order = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order ID $orderId tidak ditemukan di database.");
    }

    $oldStatus = $order['status_pesanan'];
    $paymentType = $input['payment_type'] ?? '';

    if ($oldStatus === 'Dibatalkan') {
        // Pesanan sudah dibatalkan lokal karena melewati batas waktu.
        // Jika pembayaran lunas masuk terlambat, JANGAN ubah status ke Diproses dan JANGAN potong stok fisik.
        // Catat waktu bayar agar admin dapat melakukan proses pengembalian dana (refund) manual.
        $pdo->prepare("UPDATE penjualan SET payment_method_ecommerce = COALESCE(payment_method_ecommerce, ?), paid_at = COALESCE(paid_at, NOW()) WHERE no_invoice = ?")
            ->execute([$paymentType, $orderId]);
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Pesanan sudah dibatalkan lokal. Pembayaran terlambat dicatat untuk refund manual.']);
        exit;
    }

    if ($newStatus === 'Dibatalkan') {
        batalkanSatuPesananInternal($pdo, $order['id']);
        if (!empty($paymentType)) {
            $pdo->prepare("UPDATE penjualan SET payment_method_ecommerce = ? WHERE id = ?")->execute([$paymentType, $order['id']]);
        }
    } else {
        // Update status penjualan, metode pembayaran, dan waktu bayar
        $stmtUpdate = $pdo->prepare("
            UPDATE penjualan 
            SET status_pesanan = ?,
                payment_method_ecommerce = COALESCE(NULLIF(?, ''), payment_method_ecommerce),
                paid_at = CASE WHEN ? IN ('Diproses', 'Selesai') THEN COALESCE(paid_at, NOW()) ELSE paid_at END
            WHERE no_invoice = ?
        ");
        $stmtUpdate->execute([$newStatus, $paymentType, $newStatus, $orderId]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => "Order $orderId updated to $newStatus"]);

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
