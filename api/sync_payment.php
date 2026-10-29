<?php
// File: api/sync_payment.php
// Endpoint manual untuk sinkronisasi status pembayaran dari Midtrans ke Database lokal
// Sangat berguna untuk environment localhost di mana Webhook Midtrans tidak bisa masuk.

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/order_helper.php';

$orderId = $_GET['order_id'] ?? '';
if (empty($orderId)) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Ambil status dari Midtrans API
    $ch = curl_init(MIDTRANS_API_URL . '/v2/' . $orderId . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        throw new Exception("Gagal mendapatkan status dari Midtrans (HTTP $httpCode).");
    }

    $midtransData = json_decode($response, true);
    $transactionStatus = $midtransData['transaction_status'] ?? '';
    $pTypeParam = trim($_GET['payment_type'] ?? '');
    $paymentType = $midtransData['payment_type'] ?? $pTypeParam;

    // Map status
    $newStatus = 'Menunggu Pembayaran';
    if (in_array($transactionStatus, ['settlement', 'capture'])) {
        $newStatus = 'Diproses';
    } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
        $newStatus = 'Dibatalkan';
    }

    if ($newStatus === 'Menunggu Pembayaran') {
        // Tetap simpan metode pembayaran jika terdeteksi (agar timer profil.php & batalkanPesananKadaluarsa otomatis menyesuaikan)
        if (!empty($paymentType)) {
            $pdo->prepare("UPDATE penjualan SET payment_method_ecommerce = ? WHERE no_invoice = ?")
                ->execute([$paymentType, $orderId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Masih Menunggu Pembayaran', 'payment_type' => $paymentType]);
        exit;
    }

    // 2. Update Database (Logika sama dengan webhook)
    $pdo->beginTransaction();

    $stmtSelect = $pdo->prepare("SELECT id, status_pesanan, metode_pengambilan FROM penjualan WHERE no_invoice = ? FOR UPDATE");
    $stmtSelect->execute([$orderId]);
    $order = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order ID tidak ditemukan di database.");
    }

    $oldStatus = $order['status_pesanan'];
    $metodePengambilan = $order['metode_pengambilan'];

    if ($oldStatus === $newStatus) {
        // Jika sudah sama, pastikan payment_method_ecommerce terisi
        if (!empty($paymentType)) {
            $pdo->prepare("UPDATE penjualan SET payment_method_ecommerce = COALESCE(payment_method_ecommerce, ?) WHERE no_invoice = ?")
                ->execute([$paymentType, $orderId]);
        }
        $pdo->rollBack();
        echo json_encode(['status' => 'success', 'message' => 'Status sudah tersinkronisasi']);
        exit;
    }

    if ($oldStatus === 'Dibatalkan') {
        // Pesanan sudah dibatalkan lokal karena melewati batas waktu.
        // Jika pembayaran lunas masuk terlambat, JANGAN ubah status ke Diproses dan JANGAN potong stok.
        // Catat waktu bayar agar admin dapat melakukan proses pengembalian dana (refund) manual.
        $pdo->prepare("UPDATE penjualan SET payment_method_ecommerce = COALESCE(payment_method_ecommerce, ?), paid_at = COALESCE(paid_at, NOW()) WHERE no_invoice = ?")
            ->execute([$paymentType, $orderId]);
        $pdo->commit();
        echo json_encode([
            'status' => 'warning',
            'message' => 'Pesanan ini sudah kedaluwarsa/dibatalkan sebelum pembayaran diterima. Silakan hubungi admin toko untuk pengembalian dana.'
        ]);
        exit;
    }

    if ($newStatus === 'Dibatalkan') {
        batalkanSatuPesananInternal($pdo, $order['id']);
        if (!empty($paymentType)) {
            $pdo->prepare("UPDATE penjualan SET payment_method_ecommerce = ? WHERE id = ?")->execute([$paymentType, $order['id']]);
        }
    } else {
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
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
