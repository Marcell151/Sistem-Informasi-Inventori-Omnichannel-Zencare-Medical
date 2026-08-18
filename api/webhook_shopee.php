<?php
// File: api/webhook_shopee.php
// Skenario 1: Webhook Sinkronisasi Stok dari Shopee (Online to Offline)

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// 1. Terima & Validasi Payload dari Shopee Sandbox
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);
$signature = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (empty($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Empty payload']);
    exit;
}

if (isset($data['action']) && $data['action'] === 'ORDER_STATUS_UPDATE') {
    $orderStatus = $data['data']['status']; // READY_TO_SHIP, CANCELLED
    $orderId = $data['data']['ordersn'];
    
    // Default Cabang Pusat (ZenCare Muharto = 1)
    $idCabang = 1;
    
    $pdo->beginTransaction();
    try {
        if ($orderStatus === 'READY_TO_SHIP') {
            foreach ($data['data']['items'] as $item) {
                $sku = $item['model_sku'];
                $qty = intval($item['model_quantity_purchased']);
                
                $stmtVar = $pdo->prepare("SELECT id FROM produk_variasi WHERE sku_variasi = ?");
                $stmtVar->execute([$sku]);
                $variasi = $stmtVar->fetch(PDO::FETCH_ASSOC);
                
                if ($variasi) {
                    $idVariasi = $variasi['id'];
                    
                    // Update Stok Cabang
                    $stmtUpdate = $pdo->prepare("UPDATE stok_cabang SET stok = stok - ? WHERE id_variasi = ? AND id_cabang = ?");
                    $stmtUpdate->execute([$qty, $idVariasi, $idCabang]);
                    
                    $stmtSisa = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
                    $stmtSisa->execute([$idVariasi, $idCabang]);
                    $sisaStok = $stmtSisa->fetchColumn();
                    
                    // Audit Log Kartu Stok
                    $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Keluar', ?, ?, ?)");
                    $stmtKartu->execute([$idCabang, $idVariasi, $qty, $sisaStok, "Shopee Sandbox Order: $orderId"]);
                }
            }
            
            $stmtJual = $pdo->prepare("INSERT IGNORE INTO penjualan (no_invoice, id_cabang, tipe_transaksi, status_pesanan, total_harga, created_at) VALUES (?, ?, 'shopee', 'Diproses', 0, NOW())");
            $stmtJual->execute([$orderId, $idCabang]);
            
        } elseif ($orderStatus === 'CANCELLED') {
            foreach ($data['data']['items'] as $item) {
                $sku = $item['model_sku'];
                $qty = intval($item['model_quantity_purchased']);
                
                $stmtVar = $pdo->prepare("SELECT id FROM produk_variasi WHERE sku_variasi = ?");
                $stmtVar->execute([$sku]);
                $variasi = $stmtVar->fetch(PDO::FETCH_ASSOC);
                
                if ($variasi) {
                    $idVariasi = $variasi['id'];
                    
                    $stmtUpdate = $pdo->prepare("UPDATE stok_cabang SET stok = stok + ? WHERE id_variasi = ? AND id_cabang = ?");
                    $stmtUpdate->execute([$qty, $idVariasi, $idCabang]);
                    
                    $stmtSisa = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
                    $stmtSisa->execute([$idVariasi, $idCabang]);
                    $sisaStok = $stmtSisa->fetchColumn();
                    
                    $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Pengembalian/Batal', ?, ?, ?)");
                    $stmtKartu->execute([$idCabang, $idVariasi, $qty, $sisaStok, "Shopee Sandbox Cancelled: $orderId"]);
                }
            }
            
            $stmtStatus = $pdo->prepare("UPDATE penjualan SET status_pesanan = 'Dibatalkan' WHERE no_invoice = ?");
            $stmtStatus->execute([$orderId]);
        }
        
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stok Shopee tersinkronisasi']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'ignored', 'message' => 'Not an order status update']);
}
?>
