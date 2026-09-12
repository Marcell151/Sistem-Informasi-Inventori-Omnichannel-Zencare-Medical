<?php
// File: api/sync_payment.php
// Endpoint manual untuk sinkronisasi status pembayaran dari Midtrans ke Database lokal
// Sangat berguna untuk environment localhost di mana Webhook Midtrans tidak bisa masuk.

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

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

    // Map status
    $newStatus = 'Menunggu Pembayaran';
    if (in_array($transactionStatus, ['settlement', 'capture'])) {
        $newStatus = 'Diproses';
    } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
        $newStatus = 'Dibatalkan';
    }

    if ($newStatus === 'Menunggu Pembayaran') {
        echo json_encode(['status' => 'success', 'message' => 'Masih Menunggu Pembayaran']);
        exit;
    }

    // 2. Update Database (Logika sama dengan webhook)
    $pdo->beginTransaction();

    $stmtSelect = $pdo->prepare("SELECT status_pesanan FROM penjualan WHERE no_invoice = ? FOR UPDATE");
    $stmtSelect->execute([$orderId]);
    $order = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order ID tidak ditemukan di database.");
    }

    $oldStatus = $order['status_pesanan'];

    if ($oldStatus === $newStatus) {
        // Sudah terupdate
        $pdo->rollBack();
        echo json_encode(['status' => 'success', 'message' => 'Status sudah tersinkronisasi']);
        exit;
    }

    $stmtUpdate = $pdo->prepare("UPDATE penjualan SET status_pesanan = ? WHERE no_invoice = ?");
    $stmtUpdate->execute([$newStatus, $orderId]);

    // Ambil detail item
    $stmtItems = $pdo->prepare("SELECT d.id_variasi, d.qty, v.rasio_konversi, v.satuan_besar FROM detail_penjualan d JOIN produk_variasi v ON d.id_variasi = v.id WHERE d.id_penjualan = (SELECT id FROM penjualan WHERE no_invoice = ?)");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

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

            $stmtSisa = $pdo->prepare("SELECT stok FROM stok_toko WHERE id_variasi = ?");
            $stmtSisa->execute([$idVar]);
            $sisaStok = $stmtSisa->fetchColumn();

            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan) VALUES (?, 'Masuk', 'E-Commerce', 'Retur Barang Rusak', ?, ?, ?, ?)");
            $stmtKartu->execute([$idVar, $orderId, $qtyPotong, $sisaStok, "Refund/Cancel Midtrans: Batal $qtyBox $satBesar"]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => "Order $orderId updated to $newStatus"]);

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
