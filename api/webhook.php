<?php
// File: api/webhook.php
// Skenario 2: Webhook Sinkronisasi Stok dari Midtrans (Online to Offline)

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

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
    $stmtSelect = $pdo->prepare("SELECT status_pesanan FROM penjualan WHERE no_invoice = ? FOR UPDATE");
    $stmtSelect->execute([$orderId]);
    $order = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order ID $orderId tidak ditemukan di database.");
    }

    $oldStatus = $order['status_pesanan'];

    // Update status penjualan
    $stmtUpdate = $pdo->prepare("UPDATE penjualan SET status_pesanan = ? WHERE no_invoice = ?");
    $stmtUpdate->execute([$newStatus, $orderId]);

    // Ambil detail item dan rasio konversi beserta catatan logistik
    $stmtItems = $pdo->prepare("SELECT d.id_variasi, d.qty, d.catatan_logistik, v.rasio_konversi, v.satuan_besar, pi.kategori 
                                FROM detail_penjualan d 
                                JOIN produk_variasi v ON d.id_variasi = v.id 
                                JOIN produk_induk pi ON v.id_produk_induk = pi.id
                                WHERE d.id_penjualan = (SELECT id FROM penjualan WHERE no_invoice = ?)");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Pengurangan Stok (TIDAK DILAKUKAN LAGI KARENA SUDAH DIPOTONG SAAT CHECKOUT)
    // Pengembalian Stok (Jika dibatalkan)
    if ($oldStatus !== 'Dibatalkan' && $newStatus === 'Dibatalkan') {
        foreach ($items as $item) {
            $idVar = $item['id_variasi'];
            $qtyBox = intval($item['qty']);
            $rasio = intval($item['rasio_konversi']) ?: 1;
            $qtyPotong = $qtyBox * $rasio;
            $satBesar = $item['satuan_besar'];

            $stmtStok = $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?");
            $stmtStok->execute([$qtyPotong, $idVar]);

            // Restorasi FEFO dan SN berdasarkan catatan_logistik
            $catatan = trim($item['catatan_logistik'] ?? '');
            if (!empty($catatan)) {
                if ($item['kategori'] === 'Obat') {
                    preg_match_all('/(.+?)\s*\((\d+)x\)/', $catatan, $matches, PREG_SET_ORDER);
                    foreach ($matches as $match) {
                        $batchNo = trim($match[1]);
                        $batchQty = intval($match[2]);
                        $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa + ? WHERE id_variasi = ? AND no_batch = ?")->execute([$batchQty, $idVar, $batchNo]);
                    }
                } elseif ($item['kategori'] === 'Alat Kesehatan') {
                    $sns = array_filter(array_map('trim', explode(',', $catatan)));
                    foreach ($sns as $snStr) {
                        $pdo->prepare("UPDATE unit_serial SET status = 'Tersedia', id_penjualan = NULL WHERE id_variasi = ? AND serial_number = ?")->execute([$idVar, $snStr]);
                    }
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => "Order $orderId updated to $newStatus"]);

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
