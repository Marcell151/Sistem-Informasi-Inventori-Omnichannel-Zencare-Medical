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

// Map to system status: ENUM('Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan')
$newStatus = 'Menunggu';
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
    $stmtSelect = $pdo->prepare("SELECT id_cabang, status_pesanan FROM penjualan WHERE no_invoice = ? FOR UPDATE");
    $stmtSelect->execute([$orderId]);
    $order = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order ID $orderId tidak ditemukan di database.");
    }

    $oldStatus = $order['status_pesanan'];
    $idCabang = $order['id_cabang'];

    // Update status penjualan
    $stmtUpdate = $pdo->prepare("UPDATE penjualan SET status_pesanan = ? WHERE no_invoice = ?");
    $stmtUpdate->execute([$newStatus, $orderId]);

    // Ambil detail item dan rasio konversi
    $stmtItems = $pdo->prepare("SELECT d.id_variasi, d.qty, v.rasio_konversi, v.satuan_besar FROM detail_penjualan d JOIN produk_variasi v ON d.id_variasi = v.id WHERE d.id_penjualan = (SELECT id FROM penjualan WHERE no_invoice = ?)");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Pengurangan Stok (Jika beralih ke Diproses dan sebelumnya Menunggu)
    if ($oldStatus === 'Menunggu' && $newStatus === 'Diproses') {
        foreach ($items as $item) {
            $idVar = $item['id_variasi'];
            $qtyBox = intval($item['qty']);
            $rasio = intval($item['rasio_konversi']) ?: 1;
            $qtyPotong = $qtyBox * $rasio;
            $satBesar = $item['satuan_besar'];

            $stmtStok = $pdo->prepare("UPDATE stok_cabang SET stok = stok - ? WHERE id_variasi = ? AND id_cabang = ?");
            $stmtStok->execute([$qtyPotong, $idVar, $idCabang]);

            $stmtSisa = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
            $stmtSisa->execute([$idVar, $idCabang]);
            $sisaStok = $stmtSisa->fetchColumn();

            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Keluar', ?, ?, ?)");
            $stmtKartu->execute([$idCabang, $idVar, $qtyPotong, $sisaStok, "Midtrans Payment: $orderId (Online $qtyBox $satBesar)"]);
        }
    }
    // Pengembalian Stok (Jika dibatalkan tapi sebelumnya sudah Diproses/stok terpotong)
    elseif (in_array($oldStatus, ['Diproses', 'Dikirim']) && $newStatus === 'Dibatalkan') {
        foreach ($items as $item) {
            $idVar = $item['id_variasi'];
            $qtyBox = intval($item['qty']);
            $rasio = intval($item['rasio_konversi']) ?: 1;
            $qtyPotong = $qtyBox * $rasio;
            $satBesar = $item['satuan_besar'];

            $stmtStok = $pdo->prepare("UPDATE stok_cabang SET stok = stok + ? WHERE id_variasi = ? AND id_cabang = ?");
            $stmtStok->execute([$qtyPotong, $idVar, $idCabang]);

            $stmtSisa = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
            $stmtSisa->execute([$idVar, $idCabang]);
            $sisaStok = $stmtSisa->fetchColumn();

            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Pengembalian/Batal', ?, ?, ?)");
            $stmtKartu->execute([$idCabang, $idVar, $qtyPotong, $sisaStok, "Midtrans Refund/Cancel: $orderId (Batal $qtyBox $satBesar)"]);
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
